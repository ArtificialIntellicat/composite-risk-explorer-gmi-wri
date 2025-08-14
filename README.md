# 🌍 Composite Risk Explorer

**Visualizing Global Militarisation and Climate Disaster Risk**

An interactive web app to explore the **Global Militarisation Index (GMI)** and the **World Risk Index (WRI)** by country and year.  
Combine indicators, navigate time (2000–2050), and view results on a world choropleth map with color-vision–friendly palettes.

---

## ✨ Features

- Interactive Leaflet map with per-country popups
- Combine GMI/WRI indicators; normalize and sum per selected year
- Year slider with bubble indicator
- Observed data (≤2022) vs. model-based forecasts (2023–2050)
- Accessible palettes (Viridis/Inferno, Cividis/Plasma) with persistence
- Vue 3 + TypeScript frontend with clean componentization
- Laravel 10 API backend
- Optional Python/ETS forecast pipeline that builds `public/data/predictions.json`

---

## 🧱 Architecture at a glance

```
Laravel (API, routing, DB)  <—>  Vue 3 (SPA)  <—>  Leaflet (map)
                         \——— Optional offline Python (ETS) → predictions.json
```

- For **2000–2022**, values come from the DB via the Laravel API.
- For **2023–2050**, values are read from a static JSON: `public/data/predictions.json` produced offline (ETS/Holt–Winters).

---

## 🗂 Project structure (key parts)

```text
resources/
  js/
    components/
      explorer/
        AccessibilityControls.vue
        AboutSections.vue
        ChoroplethMap.vue        # owns Leaflet lifecycle; exposes color ranges via v-model
        ColorLegend.vue
        IndicatorControls.vue
        IntroSection.vue
        YearSlider.vue
    composables/
      useIndicators.ts           # single source of truth for year/indicators/accessibility
      useChoroplethMap.ts        # init + refresh for Leaflet choropleth (SSR-safe)
    domain/
      constants.ts               # MIN_YEAR, LAST_ACTUAL_YEAR, MAX_YEAR
      indicators.ts              # options + labels + tooltips
    map/
      popup.ts                   # popup HTML (including CI line when predicted)
    palettes/
      scales.ts                  # palette selection + legend binning + color lookup
    utils/
      scores.ts                  # normalize(values), buildCountryScores(...)
    pages/
      CountryMap.vue             # orchestrates the page: composes components & state
      Dashboard.vue
      Welcome.vue
    app.ts
    ssr.ts
public/
  data/
    predictions.json             # built offline by the Python script (optional)
  geo/
    ne_countries.geojson         # world geometry (Natural Earth or equivalent)
routes/
  api.php                        # /api/map-data endpoint
```

> The page now follows a **“container (CountryMap.vue) + presentational components”** pattern.  
> All Leaflet DOM work is encapsulated in `ChoroplethMap.vue`/`useChoroplethMap.ts` and pure helpers (palettes, scores).

---

## 📦 Tech Stack

| Layer            | Tech                                              |
|------------------|----------------------------------------------------|
| Frontend         | Vue 3, TypeScript, Vite, Tailwind CSS             |
| Mapping          | Leaflet                                           |
| Backend          | Laravel 10 (PHP 8.1+), MySQL (or SQLite dev)      |
| Data/Forecasts   | Optional Python (pandas, numpy, statsmodels)      |

---

## 🚀 Quickstart

### Requirements
- Node.js >= 18
- PHP >= 8.1, Composer
- MySQL (or SQLite for local development)
- (Optional) Python >= 3.10 for forecast generation

### Setup

```bash
# 1) Clone
git clone https://github.com/ArtificialIntellicat/composite-risk-explorer-gmi-wri.git
cd composite-risk-explorer-gmi-wri

# 2) Install dependencies
composer install
npm install

# 3) Environment
cp .env.example .env
php artisan key:generate

# 4) Configure database in .env
#   DB_CONNECTION=mysql
#   DB_DATABASE=...
#   DB_USERNAME=...
#   DB_PASSWORD=...

# 5) Migrate & seed
php artisan migrate --seed

# 6) Run dev servers
npm run dev         # Vite
php artisan serve   # Laravel API
```

Open the app (typically at http://localhost:5173 via Vite). The API runs at http://127.0.0.1:8000 by default.

> If using a different port/host, ensure your frontend requests to /api/... are proxied or absolute URLs are configured.

---

## 🔌 API (data fetching)

**Endpoint**: `GET /api/map-data?year=YYYY&metrics=metric1,metric2,...`

- `year`: integer (2000–2050)
- `metrics`: comma-separated indicator keys, e.g. `gmi_score,wri_exposure`

**Response (shape example)**

```json
[
  {
    "iso3": "DEU",
    "name": "Germany",
    "year": 2030,
    "source": "predicted",
    "gmi_score": 73.579,
    "gmi_score_lo_ci": 52.599,
    "gmi_score_hi_ci": 94.558,
    "wri_exposure": 0.42
  }
]
```

- When `year <= 2022`, `source` is typically `"actual"` and CI fields may be absent.
- When `year > 2022`, values and optional CI fields originate from `public/data/predictions.json`.

---

## 🎨 Scoring & Colors

- For a selected year, each chosen indicator is **min–max normalized per year**:  
  `norm(x) = (x − min) / (max − min)`
- A country's composite score is the **sum** across selected indicators (missing → 0).  
- The legend is binned approximately by **quintiles** (20% steps).
- Observed years use a **red family**; predicted years use a **blue or CB-friendly** family depending on user settings.

**Accessibility**: Users can toggle **Color‑vision friendly** mode and pick between **Viridis/Inferno** or **Cividis/Plasma**. The preference is persisted (localStorage) and keyboard accessible.

---

## 🧪 Dev Notes (forecast pipeline, optional)

The app can run entirely with database data (≤2022).  
To extend to 2023–2050, generate forecasts and write `public/data/predictions.json`.

### Python environment
Create `ml/requirements.txt` (example):
```
pandas
numpy
statsmodels
pyyaml
```

Example script outline (`ml/build_predictions.py`):
```text
# Builds ETS forecasts per (iso3, metric) and writes flat JSON rows to public/data/predictions.json.
# - Annual series (~2000–2022), require min_points >= 3
# - ETS: additive trend, no seasonality; optional damped trend
# - 95% intervals via residual std. dev. (parameter uncertainty not modeled)
# Steps:
# 1) Load cleaned series per iso3/metric
# 2) Fit ETS (statsmodels.tsa.holtwinters.ExponentialSmoothing)
# 3) Forecast 2023–2050, compute residual-based CIs, round to 3 decimals
# 4) Write rows: { iso3, metric, year, value, lo_ci, hi_ci, source: "predicted", method, version }
```

Write the file here:
```
public/data/predictions.json
```

> The frontend automatically switches to predicted mode for year > 2022 and displays CI in popups when a single indicator is selected.

---

## ♿ Accessibility

- Color‑vision friendly palettes with monotonic lightness.
- Keyboard-accessible toggle; state persists locally.
- Map popups include CI information for single-indicator predicted years.

---

## 🔒 Security & performance (notes)

- Avoid exposing internal DB/schema details in API responses.
- Validate and clamp query params (year range; allowed metric keys).
- Consider HTTP caching/ETag for `/geo/ne_countries.geojson` and `predictions.json`.
- Lazy/dynamic import of Leaflet ensures SSR safety and faster initial load.

---

## 📝 Scripts

```bash
# Frontend
npm run dev          # Vite dev server
npm run build        # Production build
npm run preview      # Preview production build

# Backend
php artisan serve    # Laravel dev server
php artisan migrate  # Run migrations
php artisan db:seed  # Seed database
```

---

## 🗃 Data sources

- **Global Militarisation Index (GMI)** — https://gmi.bicc.de/ranking-table  
  © Bonn International Center for Conversion (BICC)

- **World Risk Index (WRI)** — https://www.weltrisikobericht.de  
  © Bündnis Entwicklung Hilft & Ruhr-Universität Bochum

Example input locations (if you import locally):
```
storage/app/data/GMI-2023-all-years.xlsx
storage/app/data/worldriskindex/worldriskindex-2022.csv
```

---

## 📜 License

This project is licensed under the [MIT License](LICENSE).

---

## ✨ Author

[@ArtificialIntellicat](https://github.com/ArtificialIntellicat) — if this project helps you, a ⭐ on GitHub makes our day!
