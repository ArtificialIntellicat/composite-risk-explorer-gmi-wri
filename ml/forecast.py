#!/usr/bin/env python3
"""
Forecasts country-level indicators (e.g., GMI/WRI metrics) from a combined CSV
and writes a flat JSON array with yearly predictions up to a chosen horizon.
"""

from __future__ import annotations

import argparse
import json
import math
import os
import sys
from typing import Dict, Iterable, List, Optional, Tuple, Union

import numpy as np
import pandas as pd
from statsmodels.tsa.holtwinters import ExponentialSmoothing

# -------------------------
# Default configuration
# -------------------------

# Last year with observed values in your DB/dataset.
DEFAULT_LAST_ACTUAL_YEAR = 2022

# Final forecast year (inclusive).
DEFAULT_HORIZON = 2050

# Minimum number of observed points required to fit a model safely.
DEFAULT_MIN_POINTS = 3

# Confidence level for residual-based CI. 0.95 -> z ≈ 1.96.
DEFAULT_CI_LEVEL = 0.95

# Whether to damp the additive trend (can help for long horizons).
DEFAULT_DAMPED_TREND = False

# Decimal places to round value/CI in the JSON. Use -1 to disable.
DEFAULT_ROUND = 3

# Only columns that exist in the CSV will be used.
DEFAULT_METRICS: List[str] = [
    "gmi_score",
    "milex_indicator",
    "personnel_indicator",
    "weapons_indicator",
    # "gmi_rank",  # ranks should not be forecast; keep disabled
    "wri_score",
    "wri_exposure",
    "wri_vulnerability",
    "wri_susceptibility",
    "wri_coping_capacity",
    "wri_adaptive_capacity",
]

# -------------------------
# Small utilities
# -------------------------

def _z_from_ci(ci: float) -> float:
    """
    Map a confidence level to a z-multiplier (standard normal).
    We avoid pulling in scipy; a small lookup table is enough here.
    """
    table = {0.90: 1.645, 0.95: 1.96, 0.975: 2.24, 0.99: 2.576}
    return table.get(ci, 1.96)

def _ensure_yearly_period_index(y: pd.Series) -> pd.Series:
    """
    Attach a yearly PeriodIndex (freq='Y').
    - Statsmodels behaves better with explicit date indices.
    - It also makes our intent (annual data) explicit.
    """
    idx = pd.PeriodIndex(y.index.astype(int), freq="Y")
    y2 = y.copy()
    y2.index = idx
    return y2

def _safe_numeric_series(values_by_year: Dict[int, float]) -> pd.Series:
    """
    Build a numeric series from a {year: value} dict:
    - sort by year (important for time models),
    - coerce to float,
    - drop NaNs (missing values).
    """
    if not values_by_year:
        return pd.Series(dtype=float)
    years_sorted = sorted(values_by_year.keys())
    vals = [values_by_year.get(y, np.nan) for y in years_sorted]
    s = pd.Series(vals, index=years_sorted, dtype=float)
    return s.dropna()

def _residual_std(fit, y: pd.Series) -> float:
    """
    Compute a residual standard deviation for CI bands.
    - Prefer fit.resid if available.
    - Fallback: y - fit.fittedvalues.
    - If too few residuals, return 0.0 (no CI band).
    """
    resid = getattr(fit, "resid", None)
    if resid is None or len(resid) <= 1:
        try:
            resid = (y - fit.fittedvalues).astype(float)
        except Exception:
            resid = pd.Series(dtype=float)
    if len(resid) <= 1:
        return 0.0
    return float(np.nanstd(resid, ddof=1))

def _maybe_round(x: Optional[Union[float, int]], nd: Optional[int]) -> Optional[float]:
    """
    Round helper: if x is a number and nd is not None, round(x, nd).
    If nd < 0 was passed by user, we disable rounding (handled outside).
    """
    if x is None or nd is None:
        return x
    try:
        if isinstance(x, (int, float, np.floating)) and not math.isnan(float(x)):
            return round(float(x), nd)
    except Exception:
        pass
    return x

# -------------------------
# Core forecasting routine
# -------------------------

def forecast_series_ets_additive(
    values_by_year: Dict[int, float],
    last_actual_year: int,
    horizon: int,
    min_points: int = DEFAULT_MIN_POINTS,
    ci_level: float = DEFAULT_CI_LEVEL,
    damped_trend: bool = DEFAULT_DAMPED_TREND,
) -> Dict[int, Dict[str, Optional[float]]]:
    """
    Fit ETS (Holt-Winters) with additive trend (no seasonality) to annual data
    and forecast from (last_actual_year+1) .. horizon.

    Returns a dict:
      {year: {"value": float|None, "lo_ci": float|None, "hi_ci": float|None}}
    """
    # 1) Make a clean series
    s = _safe_numeric_series(values_by_year)
    if len(s) < min_points or last_actual_year >= horizon:
        return {}

    # 2) Use a yearly PeriodIndex to avoid index warnings and be explicit
    s = _ensure_yearly_period_index(s)

    # 3) Fit ETS (additive trend). No seasonality for annual data.
    try:
        model = ExponentialSmoothing(
            s,
            trend="add",
            seasonal=None,
            damped_trend=damped_trend,
            initialization_method="estimated",
        )
        fit = model.fit(optimized=True)
    except Exception:
        # With very short or irregular series, statsmodels can fail.
        # We choose to skip rather than break the whole pipeline.
        return {}

    # 4) Predict for the desired Period range
    start_p = pd.Period(str(last_actual_year + 1), freq="Y")
    end_p = pd.Period(str(horizon), freq="Y")
    try:
        pred = fit.predict(start=start_p, end=end_p)
    except Exception:
        return {}

    # 5) Rough, symmetric CI using residual std dev and a z-multiplier.
    z = _z_from_ci(ci_level)
    sigma = _residual_std(fit, s)

    out: Dict[int, Dict[str, Optional[float]]] = {}
    for idx, val in pred.items():
        # idx can be a Period; extract integer year
        year = int(getattr(idx, "year", int(str(idx))))
        v = float(val) if isinstance(val, (int, float, np.floating)) and not math.isnan(val) else None
        if v is None:
            out[year] = {"value": None, "lo_ci": None, "hi_ci": None}
            continue
        if sigma > 0:
            lo = v - z * sigma
            hi = v + z * sigma
        else:
            lo = hi = None
        out[year] = {"value": v, "lo_ci": lo, "hi_ci": hi}

    return out

# -------------------------
# JSON streaming writer
# -------------------------

def _write_json_stream(rows: Iterable[Dict], out_path: str) -> None:
    """
    Stream a JSON array to disk:
      [
        {...},
        {...},
        ...
      ]
    This avoids building a giant list in memory.
    """
    os.makedirs(os.path.dirname(out_path) or ".", exist_ok=True)
    with open(out_path, "w", encoding="utf-8") as f:
        f.write("[")
        first = True
        for row in rows:
            if not first:
                f.write(",")
            else:
                first = False
            json.dump(row, f, ensure_ascii=False)
        f.write("]")

# -------------------------
# Main pipeline
# -------------------------

def run_pipeline(
    src_csv: str,
    out_json: str,
    metrics: List[str],
    last_actual_year: int,
    horizon: int,
    min_points: int,
    ci_level: float,
    damped_trend: bool,
    nd_round: Optional[int],
) -> Tuple[int, int]:
    """
    Read CSV, loop iso3 × metric, forecast, and stream JSON rows.
    Returns (num_isos, num_prediction_rows).
    """
    print(f"[info] reading CSV: {src_csv}")
    df = pd.read_csv(src_csv)

    # Basic schema checks
    required = {"iso3", "year"}
    if not required.issubset(df.columns):
        raise SystemExit(f"CSV must contain at least columns: {', '.join(sorted(required))}")

    # Clean identifiers/types
    df = df.dropna(subset=["iso3", "year"]).copy()
    df["iso3"] = df["iso3"].astype(str).str.upper()
    df["year"] = pd.to_numeric(df["year"], errors="coerce").astype("Int64")
    df = df.dropna(subset=["year"]).copy()
    df["year"] = df["year"].astype(int)

    # Only forecast metrics that actually exist in the CSV
    metrics_present = [m for m in metrics if m in df.columns]
    if not metrics_present:
        raise SystemExit("[error] none of the requested metrics are present in the CSV")

    # Ensure numeric columns for each selected metric
    for m in metrics_present:
        df[m] = pd.to_numeric(df[m], errors="coerce")

    print(f"[info] rows={len(df)} | metrics_present={len(metrics_present)}")
    print(f"[info] horizon={horizon} | last_actual_year={last_actual_year} | min_points={min_points} | ci={ci_level} | round={nd_round}")

    # Generator that yields one output JSON row at a time
    def _rows() -> Iterable[Dict]:
        for iso, g in df.groupby("iso3", sort=True):
            g = g.sort_values("year")
            # Build a per-metric {year: value} dict for this ISO and forecast it
            for m in metrics_present:
                values_by_year = {int(y): (float(v) if pd.notna(v) else np.nan) for y, v in zip(g["year"], g[m])}
                preds = forecast_series_ets_additive(
                    values_by_year=values_by_year,
                    last_actual_year=last_actual_year,
                    horizon=horizon,
                    min_points=min_points,
                    ci_level=ci_level,
                    damped_trend=damped_trend,
                )
                # Stream rows in strict year order
                for year in range(last_actual_year + 1, horizon + 1):
                    d = preds.get(year)
                    if not d:
                        continue
                    yield {
                        "iso3": iso,
                        "metric": m,
                        "year": year,
                        "value": _maybe_round(d["value"], nd_round),
                        "lo_ci": _maybe_round(d["lo_ci"], nd_round),
                        "hi_ci": _maybe_round(d["hi_ci"], nd_round),
                        "source": "predicted",
                        "method": "ets_additive_damped" if damped_trend else "ets_additive",
                        "version": "1.0",
                    }

    # Stream to disk
    _write_json_stream(_rows(), out_json)

    # Simple counters for logging (reloading ~10–12 MB is fine here)
    num_isos = df["iso3"].nunique()
    try:
        _tmp = json.load(open(out_json, "r", encoding="utf-8"))
        num_rows = len(_tmp)
    except Exception:
        num_rows = -1

    return num_isos, num_rows

def parse_args(argv: Optional[List[str]] = None) -> argparse.Namespace:
    """
    CLI supports positional args plus flags. Examples:
      python ml/forecast.py combined.csv storage/app/predictions.json
      python ml/forecast.py combined.csv storage/app/predictions.json --horizon 2050 --ci 0.95 --round 3
    """
    p = argparse.ArgumentParser(description="Forecast annual indicators to a horizon using ETS (additive).")
    p.add_argument("input_csv", help="Path to combined input CSV (must contain iso3, year, and metric columns).")
    p.add_argument("output_json", help="Path to output JSON (flat array).")
    p.add_argument("--metrics", nargs="*", default=DEFAULT_METRICS,
                   help="Subset of metrics to forecast (default: common GMI/WRI metrics).")
    p.add_argument("--last-actual", type=int, default=DEFAULT_LAST_ACTUAL_YEAR, help="Last observed year.")
    p.add_argument("--horizon", type=int, default=DEFAULT_HORIZON, help="Forecast horizon (last year to predict).")
    p.add_argument("--min-points", type=int, default=DEFAULT_MIN_POINTS, help="Minimum data points required to fit.")
    p.add_argument("--ci", type=float, default=DEFAULT_CI_LEVEL, help="Confidence level for residual-based CI.")
    p.add_argument("--damped-trend", action="store_true", default=DEFAULT_DAMPED_TREND,
                   help="Use a damped additive trend (can help with very long horizons).")
    p.add_argument("--round", dest="nd_round", type=int, default=DEFAULT_ROUND,
                   help="Decimal places to round outputs (value/CI). Use -1 to disable rounding.")
    return p.parse_args(argv)

def main(argv: Optional[List[str]] = None) -> int:
    args = parse_args(argv)

    # Quick guardrail: don't allow nonsense ranges
    if args.last_actual >= args.horizon:
        print("[error] last-actual must be < horizon", file=sys.stderr)
        return 2

    # Interpret --round -1 as "no rounding"
    nd_round = None if (args.nd_round is not None and args.nd_round < 0) else args.nd_round

    num_isos, num_rows = run_pipeline(
        src_csv=args.input_csv,
        out_json=args.output_json,
        metrics=args.metrics,
        last_actual_year=args.last_actual,
        horizon=args.horizon,
        min_points=args.min_points,
        ci_level=args.ci,
        damped_trend=args.damped_trend,
        nd_round=nd_round,
    )

    rows_str = str(num_rows) if num_rows >= 0 else "?"
    print(f"[info] processed_isos={num_isos} | predictions={rows_str}")
    print(f"[info] wrote: {args.output_json}")
    return 0

if __name__ == "__main__":
    sys.exit(main())
