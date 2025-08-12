import json
import sys
from typing import Dict, List

import numpy as np
import pandas as pd
from statsmodels.tsa.holtwinters import ExponentialSmoothing

LAST_ACTUAL_YEAR = 2022
HORIZON = 2050
METRICS: List[str] = [
    "gmi_score", "milex_indicator", "personnel_indicator", "weapons_indicator", "gmi_rank",
    "wri_score", "wri_exposure", "wri_vulnerability", "wri_susceptibility",
    "wri_coping_capacity", "wri_adaptive_capacity",
]

def forecast_series(values_by_year: Dict[int, float], start_year: int, last_year: int) -> Dict[int, Dict[str, float]]:
    years = list(range(start_year, last_year + 1))
    series = pd.Series([values_by_year.get(y, np.nan) for y in years], index=years).dropna()
    if len(series) < 3 or LAST_ACTUAL_YEAR >= HORIZON:
        return {}
    model = ExponentialSmoothing(series, trend="add", seasonal=None, initialization_method="estimated")
    fit = model.fit(optimized=True)
    future_years = list(range(LAST_ACTUAL_YEAR + 1, HORIZON + 1))
    pred = fit.predict(future_years[0], future_years[-1])
    resid = getattr(fit, "resid", pd.Series(dtype=float))
    resid_std = float(resid.std(ddof=1)) if len(resid) > 1 else 0.0
    out: Dict[int, Dict[str, float]] = {}
    for yr in future_years:
        val = float(pred.get(yr, np.nan))
        lo = val - 1.96 * resid_std if resid_std else None
        hi = val + 1.96 * resid_std if resid_std else None
        out[yr] = {"value": val, "lo_ci": lo, "hi_ci": hi}
    return out

def main(src_csv: str, out_json: str):
    print(f"[info] reading CSV: {src_csv}")
    df = pd.read_csv(src_csv)
    if not {"iso3", "year"}.issubset(df.columns):
        raise SystemExit("CSV must contain at least columns: iso3, year")

    df = df.dropna(subset=["iso3", "year"]).copy()
    df["iso3"] = df["iso3"].astype(str).str.upper()
    df["year"] = df["year"].astype(int)

    metrics_present = [m for m in METRICS if m in df.columns]
    print(f"[info] rows={len(df)} | metrics_present={len(metrics_present)}")

    # sicherstellen: numerisch
    for m in metrics_present:
        df[m] = pd.to_numeric(df[m], errors="coerce")

    results: List[Dict] = []
    iso_count = 0
    for iso, g in df.groupby("iso3"):
        g = g.sort_values("year")
        start_year = int(g["year"].min())
        last_year  = int(g["year"].max())
        iso_count += 1

        for m in metrics_present:
            values_by_year = {int(y): (float(v) if pd.notna(v) else np.nan) for y, v in zip(g["year"], g[m])}
            preds = forecast_series(values_by_year, start_year, last_year)
            for year, d in preds.items():
                results.append({
                    "iso3": iso,
                    "metric": m,
                    "year": year,
                    "value": d["value"],
                    "lo_ci": d["lo_ci"],
                    "hi_ci": d["hi_ci"],
                    "source": "predicted",
                    "method": "ets_additive",
                    "version": "1.0",
                })

    print(f"[info] processed_isos={iso_count} | predictions={len(results)}")
    with open(out_json, "w", encoding="utf-8") as f:
        json.dump(results, f, ensure_ascii=False)
    print(f"[info] wrote: {out_json}")

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python ml/forecast.py <input.csv> <output.json>")
        sys.exit(1)
    main(sys.argv[1], sys.argv[2])
