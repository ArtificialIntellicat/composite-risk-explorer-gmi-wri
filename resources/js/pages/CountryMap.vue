<!-- This project really needs to be cleaned and this file needs to be separated into components and modules. Will do this as soon as possible. -->
<template>
  <div class="bg-white shadow-lg max-w-4xl mx-auto p-6 mt-6 rounded-md text-[14px]">
    <!-- Title and Description -->
    <h1 class="text-2xl font-bold text-center mb-3 text-gray-800">
      Composite Risk Explorer: Global Militarisation & Climate Hazards
    </h1>
    <p class="text-center text-gray-800 text-sm mb-6">
      This interactive map combines indicators from the <strong>Global Militarisation Index (GMI)</strong> and the <strong>World Risk Index (WRI)</strong> by country and year.
      <br>
      <span class="text-amber-800 font-medium">Observed data</span> are shown for <strong>2000–2022</strong>. For <strong>2023–2050</strong>, the map displays
      <span class="text-blue-800 font-medium">model-based forecasts</span> built from each country’s historical time series (one model per country and indicator). Forecasts are produced offline and shipped as a static JSON file; details in the <a href="#methods-forecast" class="text-blue-800 underline">Methods</a> section.
    </p>

    <!-- Year slider label with bubble -->
    <label for="yearRange" class="block mb-2 font-semibold text-center text-gray-800 text-lg">
      Year
    </label>
    <div class="relative w-2/3 mx-auto mb-4">
      <div
        class="absolute -top-6 bg-blue-100 text-blue-800 px-3 py-1 rounded shadow text-sm font-medium transition-all duration-200"
        :style="{ left: yearBubbleLeft }"
      >
        {{ selectedYear }}
      </div>
      <input id="yearRange" type="range" :min="MIN_YEAR" :max="MAX_YEAR" v-model="selectedYear" @input="refreshMap" class="w-full z-10 relative" />
      <div class="absolute top-full left-0 w-full flex justify-between text-xs text-gray-700 -mt-1 pointer-events-none">
        <span v-for="year in years" :key="year" class="w-0 text-center">
          {{ year % 5 === 0 ? year : '' }}
        </span>
      </div>
    </div>

    <!-- Indicator checkboxes -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-y-3 gap-x-4 mb-6 text-gray-800 max-w-xl mx-auto">
      <!-- GMI Section -->
      <label class="col-span-full mt-2 mb-1 flex items-center font-bold text-base">
        <input
          type="checkbox"
          class="mr-2"
          value="gmi_score"
          v-model="activeIndicators"
          @change="onMainToggle('gmi')"
        />
        Global Militarisation Index (GMI)
        <span class="ml-1 cursor-help text-blue-900 text-xl" title="The GMI measures the relative weight and importance of a country's military compared to its society.">&#9432;</span>
        <a href="#gmi-info" class="ml-2 text-blue-800 text-xs">Read more ↓</a>
      </label>
      <label
        v-for="option in gmiOptions.filter(o => o.value !== 'gmi_score')"
        :key="option.value"
        class="inline-flex items-center"
        :class="activeIndicators.includes('gmi_score') ? 'opacity-50 cursor-not-allowed' : ''"
      >
        <input
          type="checkbox"
          :disabled="activeIndicators.includes('gmi_score')"
          v-model="activeIndicators"
          :value="option.value"
          @change="onSubToggle('gmi')"
          class="mr-2"
        />
        <span>{{ option.label }}</span>
        <span class="text-blue-600 cursor-help text-lg ml-1" :title="tooltipTexts[option.value]">&#9432;</span>
      </label>

      <!-- WRI Section -->
      <label class="col-span-full mt-4 mb-1 flex items-center font-bold text-base">
        <input
          type="checkbox"
          class="mr-2"
          value="wri_score"
          v-model="activeIndicators"
          @change="onMainToggle('wri')"
        />
        World Risk Index (WRI)
        <span class="ml-1 cursor-help text-blue-900 text-xl" title="The WRI combines exposure to natural hazards and vulnerability to assess disaster risk.">&#9432;</span>
        <a href="#wri-info" class="ml-2 text-blue-800 text-xs">Read more ↓</a>
      </label>
      <label
        v-for="option in wriOptions.filter(o => o.value !== 'wri_score')"
        :key="option.value"
        class="inline-flex items-center"
        :class="activeIndicators.includes('wri_score') ? 'opacity-50 cursor-not-allowed' : ''"
      >
        <input
          type="checkbox"
          :disabled="activeIndicators.includes('wri_score')"
          v-model="activeIndicators"
          :value="option.value"
          @change="onSubToggle('wri')"
          class="mr-2"
        />
        <span>{{ option.label }}</span>
        <span class="text-blue-600 cursor-help text-lg ml-1" :title="tooltipTexts[option.value]">&#9432;</span>
      </label>
    </div>

    <!-- Leaflet Map -->
    <div id="map" style="height: 600px; margin-top: 2rem;"></div>

    <!-- Color legend -->
    <div class="flex flex-col items-center mt-6 text-sm text-gray-800">
      <div class="mb-2 font-medium" :class="selectedYear > LAST_ACTUAL_YEAR ? 'text-blue-800' : 'text-amber-800'">
        {{ selectedYear > LAST_ACTUAL_YEAR ? 'Predicted (2023–2050)' : 'Observed (≤2022)' }}
      </div>
      <div class="flex justify-center space-x-4">
        <div v-for="(range, idx) in colorRanges" :key="idx" class="flex items-center space-x-1">
          <div :style="{ backgroundColor: range.color }" class="w-5 h-3 border border-gray-300"></div>
          <span>{{ range.label }}</span>
        </div>
      </div>
    </div>

    <!-- Reflections & Context -->
    <div class="max-w-3xl mx-auto text-gray-900 text-sm mt-10">

      <!-- GMI Info -->
      <h2 class="text-lg font-semibold mt-6 mb-2">About the Global Militarisation Index (GMI)</h2>
      <p class="mb-2">
        The GMI, published annually by the Bonn International Center for Conversion (BICC), reflects a country’s relative militarisation compared to its society. It combines data on military expenditures, personnel, and heavy weapons in relation to indicators like GDP or health spending. A high GMI score indicates that the military sector plays a disproportionately large role in a country’s resource allocation.
      </p>
      <p class="mb-6">
        <a href="https://gmi.bicc.de" target="_blank" class="text-blue-800 underline">Learn more about GMI at gmi.bicc.de ↗</a>
      </p>

      <!-- WRI Info -->
      <h2 class="text-lg font-semibold mt-6 mb-2">About the World Risk Index (WRI)</h2>
      <p class="mb-2">
        The WRI is published by the Bündnis Entwicklung Hilft and the Institute for International Law of Peace and Armed Conflict (IFHV) at Ruhr University Bochum. It measures the disaster risk for countries based on their exposure to natural hazards and their vulnerability, including susceptibility, coping capacity, and adaptive capacity. It is intended to support disaster risk reduction and climate adaptation planning.
      </p>
      <p class="mb-6">
        <a href="https://www.weltrisikobericht.de" target="_blank" class="text-blue-800 underline">Learn more about WRI at weltrisikobericht.de ↗</a>
      </p>

      <!-- Forecasts (overview) -->
      <h2 id="forecasts" class="text-lg font-semibold mt-6 mb-2">Forecasts (what changes after 2022)</h2>
      <p class="mb-2">
        For <span class="text-amber-800 font-medium">2000–2022</span> the map shows <strong>observed</strong> values (red palette). For
        <span class="text-blue-800 font-medium">2023–2050</span> it switches to <strong>model-based forecasts</strong> (blue palette), computed
        <em>per country and per indicator</em>. These are transparent, scenario-free extrapolations and should be interpreted with care.
      </p>
      <p class="mb-4 text-xs text-gray-700">
        Serving logic: the API returns DB values for ≤2022; for &gt;2022 it reads a static file
        <code>public/data/predictions.json</code> that is built offline and deployed with the app.
      </p>

      <!-- Forecast pipeline – developer notes -->
      <details id="methods-dev" class="mt-4 mb-6">
        <summary class="cursor-pointer text-base font-semibold text-gray-800">Forecast pipeline — developer notes</summary>
        <div class="mt-3 text-sm text-gray-900 space-y-3">
          <p>
            <strong>Data & scope.</strong> Annual series per <code>iso3</code> and metric (~2000–2022). Missing values are coerced to NaN and dropped. We require at least <code>min_points = 3</code> to fit a model.
          </p>
          <p>
            <strong>Model.</strong> ETS (Holt–Winters) with <code>trend="add"</code>, <code>seasonal=None</code> (annual data → no seasonality),
            <code>initialization_method="estimated"</code>, optional <code>damped_trend</code> to tame long-horizon growth. We use a yearly
            <code>PeriodIndex</code> in pandas so <code>statsmodels</code> handles dates cleanly.
          </p>
          <p>
            <strong>Uncertainty.</strong> For each forecast <em>ŷ</em>, we show a simple 95% band: <code>ŷ ± z·σ</code> with <code>z≈1.96</code> and
            <code>σ</code> the sample std. dev. of residuals (<code>ddof=1</code>). This is residual-based only; it ignores parameter/model uncertainty and may under-/over-cover when trends shift.
          </p>
          <p>
            <strong>Output.</strong> Flat JSON rows (one per iso×metric×year) with <code>value</code>, <code>lo_ci</code>, <code>hi_ci</code>, and
            <code>method</code> (<code>ets_additive</code> or <code>ets_additive_damped</code>). Values are pre-rounded to 3 decimals.
          </p>
          <pre class="bg-gray-50 p-3 rounded overflow-x-auto text-xs">
      { "iso3": "DEU", "metric": "gmi_score", "year": 2030,
        "value": 73.579, "lo_ci": 52.599, "hi_ci": 94.558,
        "source": "predicted", "method": "ets_additive_damped", "version": "1.0" }
          </pre>
          <p>
            <strong>Why ETS?</strong> It’s lightweight, explainable, and works on short annual series. Heavier options (ARIMA/auto-ARIMA, Prophet,
            state-space with exogenous drivers) can capture richer dynamics but add tuning cost and opaque assumptions. ETS-additive(+damped) is a practical baseline for this portfolio app.
          </p>
          <p>
            <strong>Notes & quirks.</strong> Occasional AIC/BIC warnings from <code>statsmodels</code> when SSE≈0 are benign here. We don’t hard-clip forecasts to indicator bounds; noisy series can produce wide CIs. Coloring uses within-year normalization (see below), not absolute thresholds.
          </p>
        </div>
      </details>

      <!-- How the combined map score is computed -->
      <h2 id="combination" class="text-lg font-semibold mt-6 mb-2">How the map score is computed</h2>
      <p class="mb-2">
        When you select multiple indicators, the map normalizes each indicator across countries for the selected year using
        min-max scaling and then sums the normalized values:
        <code class="text-xs">norm(x) = (x − min) / (max − min)</code>, then <code>score(country) = Σ<sub>selected indicators</sub> norm(value)</code>.
        If an indicator is missing for a country, it contributes 0 to that sum. Quantile breaks (≈20% bands) drive the legend.
      </p>
      <p class="mb-4 text-xs text-gray-700">
        Implication: colors are comparable <em>within</em> a year and indicator set, but not necessarily across different years or different indicator selections.
      </p>

      <!-- Limitations & next steps -->
      <h2 id="limitations" class="text-lg font-semibold mt-6 mb-2">Limitations & next steps</h2>
      <p class="mb-3">
        Forecasts are straight extrapolations; they do not encode scenarios or policy shocks. Residual-based CIs are indicative only. The map uses the Mercator projection, which exaggerates area near the poles.
      </p>
      <p class="mb-3">
        Potential upgrades: scenario-driven forecasts with exogenous drivers (e.g., GDP, emissions), hierarchical models to share strength across countries, bounded/monotone constraints where appropriate, rolling-origin evaluation for model choice, uncertainty that widens with horizon rather than a constant ±z·σ band, and alternative projections for fairer geography.
      </p>
    </div>

    <!-- Source attribution -->
    <p class="text-center text-gray-700 text-xs">
      Note: Values for 2023–2050 are model-based forecasts; see <a href="#methods-forecast" class="text-blue-800 underline">Methods</a>.
    </p>
    <p class="text-center text-gray-800 text-sm mb-6 mt-6">
      Sources: <a href="https://gmi.bicc.de/ranking-table" class="underline" target="_blank">BICC</a>,
      <a href="https://www.weltrisikobericht.de" class="underline" target="_blank">WorldRiskReport</a>
    </p>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const MIN_YEAR = 2000
const LAST_ACTUAL_YEAR = 2022
const MAX_YEAR = 2050
const activeIndicators = ref(['gmi_score']) // GMI is active by default
const selectedYear = ref(LAST_ACTUAL_YEAR)
const years = Array.from({ length: (MAX_YEAR + 1) - MIN_YEAR }, (_, i) => MIN_YEAR + i)
const yearBubbleLeft = computed(() => {
  const percentage = ((selectedYear.value - MIN_YEAR) / (MAX_YEAR - MIN_YEAR)) * 100
  return `calc(${percentage}% - 1.5rem)`
})

const tooltipTexts = {
  gmi_score: 'Composite index measuring the overall militarisation level of a country.',
  milex_indicator: 'Index based on military expenditure in relation to GDP and health spending.',
  personnel_indicator: 'Index based on the number of military personnel per capita.',
  weapons_indicator: 'Index based on the volume of heavy weapons in armed forces.',
  wri_score: 'Composite index reflecting a country s disaster risk due to natural hazards.',
  wri_exposure: 'Degree to which a country is exposed to natural hazards.',
  wri_vulnerability: 'Societal susceptibility and capacity to cope with disasters.',
  wri_susceptibility: 'Structural vulnerability of a country (e.g., infrastructure, health).',
  wri_coping_capacity: 'Short-term capabilities to reduce negative disaster impacts.',
  wri_adaptive_capacity: 'Long-term capacity to adapt and transform in response to risks.'
}

const gmiOptions = [
  { label: 'Militarisation Index', value: 'gmi_score' },
  { label: 'Military Expenditure Index', value: 'milex_indicator' },
  { label: 'Military Personnel Index', value: 'personnel_indicator' },
  { label: 'Heavy Weapons Index', value: 'weapons_indicator' },
]

const wriOptions = [
  { label: 'World Risk Index', value: 'wri_score' },
  { label: 'Exposure', value: 'wri_exposure' },
  { label: 'Vulnerability', value: 'wri_vulnerability' },
  { label: 'Susceptibility', value: 'wri_susceptibility' },
  { label: 'Coping Capacity', value: 'wri_coping_capacity' },
  { label: 'Adaptive Capacity', value: 'wri_adaptive_capacity' },
]

function onMainToggle(group) {
  const mainValue = group === 'gmi' ? 'gmi_score' : 'wri_score'
  const subOptions = group === 'gmi' ? gmiOptions : wriOptions

  if (activeIndicators.value.includes(mainValue)) {
    // Remove all sub-indicators if main is active
    subOptions.forEach(opt => {
      if (opt.value !== mainValue) {
        activeIndicators.value = activeIndicators.value.filter(v => v !== opt.value)
      }
    })
  }
  refreshMap()
}

function onSubToggle(group) {
  const mainValue = group === 'gmi' ? 'gmi_score' : 'wri_score'
  if (activeIndicators.value.includes(mainValue)) {
    activeIndicators.value = activeIndicators.value.filter(v => v !== mainValue)
  }
  refreshMap()
}

let map = null
let countrySource = {} // iso3 -> 'actual' | 'predicted'
let geoLayer = null
let countryData = {}
let rowByIso = {}
const ACTUAL_COLORS = ['#800026', '#BD0026', '#E31A1C', '#FC4E2A', '#FD8D3C'] // reds for actual values
const PREDICTED_COLORS = ['#08306B', '#08519C', '#2171B5', '#4292C6', '#6BAED6'] // blues for predicted values
function currentPalette() {
  return selectedYear.value > LAST_ACTUAL_YEAR ? PREDICTED_COLORS : ACTUAL_COLORS
}
const colorRanges = ref([])

function normalize(values) {
  const valid = Object.values(values).filter(v => typeof v === 'number')
  const min = Math.min(...valid)
  const max = Math.max(...valid)
  const result = {}

  for (const [k, v] of Object.entries(values)) {
    if (typeof v !== 'number') {
      result[k] = null
    } else if (max === min) {
      result[k] = 0.5 // avoid divide-by-zero, place in middle
    } else {
      result[k] = (v - min) / (max - min)
    }
  }
  return result
}

function generateColorRanges() {
  const all = Object.values(countryData).filter(v => typeof v === 'number')
  if (!all.length) return
  all.sort((a, b) => a - b)
  const thresholds = [0.2, 0.4, 0.6, 0.8].map(p => all[Math.floor(p * all.length)])
  const palette = currentPalette()
  colorRanges.value = [
    { color: palette[0], label: 'Top 20%', threshold: thresholds[3] },
    { color: palette[1], label: '60–80%', threshold: thresholds[2] },
    { color: palette[2], label: '40–60%', threshold: thresholds[1] },
    { color: palette[3], label: '20–40%', threshold: thresholds[0] },
    { color: palette[4], label: 'Bottom 20%', threshold: -Infinity },
    { color: '#ddd', label: 'no data available', threshold: null }
  ]
}


function getColor(score) {
  if (score == null) return '#ddd'
  for (const range of colorRanges.value) {
    if (range.threshold == null) continue
    if (score >= range.threshold) return range.color
  }
  return colorRanges.value[colorRanges.value.length - 1].color
}

function ciLine(iso) {
  if (selectedYear.value <= LAST_ACTUAL_YEAR) return ''
  const metric = activeIndicators.value.length === 1 ? activeIndicators.value[0] : null
  if (!metric) return ''
  const r = rowByIso[iso]
  if (!r) return ''
  const lo = r[`${metric}_lo_ci`]
  const hi = r[`${metric}_hi_ci`]
  if (typeof lo === 'number' && typeof hi === 'number') {
    return `<br>CI (${metric}): ${lo.toFixed(2)} … ${hi.toFixed(2)}`
  }
  return ''
}

async function refreshMap() {
  try {
    const metrics = activeIndicators.value.join(',')
    const res = await fetch(`/api/map-data?year=${selectedYear.value}&metrics=${metrics}`)
    const rows = await res.json()

    countrySource = {}
    rows.forEach(r => {
      const iso = r.iso3?.toUpperCase()
      if (!iso) return
      countrySource[iso] = r.source || (selectedYear.value > LAST_ACTUAL_YEAR ? 'predicted' : 'actual')
    })

    rowByIso = {}
    rows.forEach(r => {
      const iso = r.iso3?.toUpperCase()
      if (!iso) return
      rowByIso[iso] = r
    })

    // rows: [{ iso3, name, year, source, <metric>... }]
    const indicatorMatrix = {}
    activeIndicators.value.forEach(ind => {
      const perIso = {}
      rows.forEach(r => {
        const iso = r.iso3?.toUpperCase()
        perIso[iso] = (typeof r[ind] === 'number') ? r[ind] : null
      })
      indicatorMatrix[ind] = normalize(perIso)
    })

    countryData = {}
    rows.forEach(r => {
      const iso = r.iso3?.toUpperCase()
      if (!iso) return
      if (activeIndicators.value.length === 0) {
        countryData[iso] = null
      } else {
        countryData[iso] = activeIndicators.value.reduce((sum, key) => {
          const val = indicatorMatrix[key]?.[iso] ?? null
          return val != null ? sum + val : sum
        }, 0)
      }
    })

    generateColorRanges()

    if (geoLayer) {
      geoLayer.eachLayer(layer => {
        const iso = layer.feature.properties.ADM0_A3
        const val = countryData[iso]
        layer.setStyle({ fillColor: getColor(val) })
        layer.bindPopup(
          `<strong>${layer.feature.properties.ADMIN}</strong>` +
          `<br>Score (${selectedYear.value}): ${val?.toFixed(2) ?? 'no data'}` +
          `<br>Source: ${countrySource[iso] ?? (selectedYear.value > LAST_ACTUAL_YEAR ? 'predicted' : 'actual')}` +
          ciLine(iso)
        )
      })
    } else {
      const geojson = await fetch('/geo/ne_countries.geojson').then(r => r.json())
      geoLayer = L.geoJSON(geojson, {
        style: feature => {
          const iso = feature.properties.ADM0_A3
          const val = countryData[iso]
          return { fillColor: getColor(val), weight: 1, color: '#555', fillOpacity: 0.7 }
        },
        onEachFeature: (feature, layer) => {
          const iso = feature.properties.ADM0_A3
          const val = countryData[iso]
          layer.bindPopup(
            `<strong>${layer.feature.properties.ADMIN}</strong>` +
            `<br>Score (${selectedYear.value}): ${val?.toFixed(2) ?? 'no data'}` +
            `<br>Source: ${countrySource[iso] ?? (selectedYear.value > LAST_ACTUAL_YEAR ? 'predicted' : 'actual')}` +
            ciLine(iso)
          )
        }
      }).addTo(map)
    }
  } catch (error) {
    console.error('Error refreshing map:', error)
  }
}

onMounted(async () => {
  try {
    map = L.map('map').setView([20, 0], 2)
    L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png', {
      attribution: '© OpenMapTiles © OpenStreetMap contributors'
    }).addTo(map)
    await refreshMap()
  } catch (error) {
    console.error('Error initializing map:', error)
  }
})
</script>

<style scoped>
.country-shape {
  transition: fill 2s ease;
}
</style>
