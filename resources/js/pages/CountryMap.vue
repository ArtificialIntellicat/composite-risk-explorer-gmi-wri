<!-- This project really needs to be cleaned and this file needs to be separated into components and modules. Will do this as soon as possible. -->
<template>
  <div class="bg-white shadow-lg max-w-4xl mx-auto p-6 mt-6 rounded-md text-[14px]">
    <!-- Title and Description -->
    <h1 class="text-2xl font-bold text-center mb-3 text-gray-800">
      Composite Risk Explorer: Global Militarisation & Climate Hazards
    </h1>
    <p class="text-center text-gray-800 text-sm mb-6">
      This interactive map allows you to combine indicators from the <strong>Global Militarisation Index (GMI)</strong> and the <strong>World Risk Index (WRI)</strong> by country and year. <br>
      You can either select one composite index or freely combine individual indicators to explore patterns of militarisation and climate disaster vulnerability. Choose your indicators and a year to see the map update in real time.
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

      <!-- Reflections & Further Work -->
      <h2 class="text-lg font-semibold mt-6 mb-2">Reflections & Further Work</h2>
      <p class="mb-3">This project combines different global indicators to explore composite vulnerability profiles. It’s important to note that the <strong>Mercator projection</strong> distorts geographic proportions — for example, countries near the poles appear larger. A future update might explore alternative, more realistic map projections.</p>
      <p class="mb-3">This tool currently visualizes historical data, but in the future, one could apply <strong>machine learning models</strong> to make forecasts about militarisation or climate risk under different scenarios. These projections would remain speculative and rely on underlying assumptions and data quality.</p>
      <p class="mb-3">The combination logic here is based on <strong>normalized summation</strong>. There may be other meaningful ways to weight or cluster indicators, such as using <strong>principal component analysis (PCA)</strong> or <strong>unsupervised clustering</strong> to identify country typologies. PCA helps reduce dimensionality by identifying underlying patterns across indicators, while clustering (e.g., k-Means) groups countries with similar vulnerability-militarisation profiles — which could be visualized or used to inform policy insights.</p>
      <p class="mb-3">Accessibility improvements are also a future goal. While the current interface uses semantic HTML and simple controls, future work will include keyboard navigation support, ARIA landmarks, screen reader compatibility, and color palettes suitable for users with <strong>color vision deficiency</strong>. This could be achieved through contrast-friendly palettes or visual textures in the map.</p>
      <p class="mb-6">The application is not yet fully <strong>responsive</strong>, which limits usability on mobile devices. Improving layout adaptability for smaller screens and touch interaction is a priority in the next iteration.</p>

      <!-- Tech Stack & Repo -->
      <h2 class="text-lg font-semibold mt-6 mb-2">Tech Stack & Repository</h2>
      <p class="mb-2">This fullstack web app was built with <strong>Laravel (PHP)</strong> and <strong>Vue.js (Vite, TypeScript)</strong>, using <strong>Inertia.js</strong> to bridge backend and frontend. Interactive geospatial visualization was implemented using <strong>Leaflet.js</strong>. Data is served from a local SQLite database. Tailwind CSS powers the UI styling.</p>
      <p class="mb-2">During deployment on <strong>Heroku</strong>, special attention was paid to runtime stability, asset path resolution, and request proxy headers. Environment-specific adjustments were made to ensure the app remains accessible after build without database failures.</p>
      <p class="mb-2">The application was developed with a strong focus on open data integration, intuitive interaction, and modular design. Based on the BICC’s work on militarisation and the WRI’s climate risk research, the tool creates space for both academic and public engagement.</p>
      <p class="mb-6">
        GitHub Repository: <a href="https://github.com/ArtificialIntellicat/composite-risk-explorer-gmi-wri" target="_blank" class="text-blue-800 underline inline-flex items-center"><svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-4 h-4 mr-1"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.387.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.612-4.042-1.612-.546-1.387-1.333-1.756-1.333-1.756-1.089-.744.084-.729.084-.729 1.205.084 1.84 1.235 1.84 1.235 1.07 1.834 2.807 1.304 3.492.997.108-.775.42-1.305.762-1.605-2.665-.305-5.466-1.333-5.466-5.931 0-1.311.468-2.382 1.236-3.222-.123-.303-.536-1.527.117-3.176 0 0 1.008-.322 3.3 1.23a11.5 11.5 0 0 1 3-.404c1.02.005 2.045.138 3 .404 2.292-1.552 3.297-1.23 3.297-1.23.655 1.649.242 2.873.12 3.176.77.84 1.234 1.911 1.234 3.222 0 4.61-2.807 5.624-5.48 5.921.431.372.816 1.102.816 2.222 0 1.606-.015 2.898-.015 3.293 0 .322.216.694.825.576C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>ArtificialIntellicat/composite-risk-explorer-gmi-wri</a>
      </p>
    </div>

    <!-- Source attribution -->
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
          `<br>Source: ${countrySource[iso] ?? (selectedYear.value > LAST_ACTUAL_YEAR ? 'predicted' : 'actual')}`
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
            `<br>Source: ${countrySource[iso] ?? (selectedYear.value > LAST_ACTUAL_YEAR ? 'predicted' : 'actual')}`
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
