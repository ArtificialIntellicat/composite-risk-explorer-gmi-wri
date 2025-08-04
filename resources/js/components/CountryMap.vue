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
      <input
        id="yearRange"
        type="range"
        min="2000"
        max="2022"
        v-model="selectedYear"
        @input="refreshMap"
        class="w-full z-10 relative"
      />
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
    <div class="flex justify-center mt-6 space-x-4 text-sm text-gray-800">
      <div v-for="(range, idx) in colorRanges" :key="idx" class="flex items-center space-x-1">
        <div :style="{ backgroundColor: range.color }" class="w-5 h-3 border border-gray-300"></div>
        <span>{{ range.label }}</span>
      </div>
    </div>

    <!-- Reflections & Context -->
    <div class="max-w-3xl mx-auto text-gray-800 text-sm mt-10">
      <h2 id="gmi-info" class="text-lg font-semibold mt-6 mb-2">About the Global Militarisation Index (GMI)</h2>
      <p class="mb-2">
        The GMI, published annually by the Bonn International Center for Conversion (BICC), reflects a country’s relative militarisation compared to its society. It combines data on military expenditures, personnel, and heavy weapons in relation to indicators like GDP or health spending. A high GMI score indicates that the military sector plays a disproportionately large role in a country’s resource allocation.
      </p>
      <p class="mb-6">
        <a href="https://gmi.bicc.de" target="_blank" class="text-blue-800 underline">Learn more about GMI at gmi.bicc.de ↗</a>
      </p>

      <h2 id="wri-info" class="text-lg font-semibold mt-6 mb-2">About the World Risk Index (WRI)</h2>
      <p class="mb-2">
        The WRI is published by the Bündnis Entwicklung Hilft and the Institute for International Law of Peace and Armed Conflict (IFHV) at Ruhr University Bochum. It measures the disaster risk for countries based on their exposure to natural hazards and their vulnerability, including susceptibility, coping capacity, and adaptive capacity. It is intended to support disaster risk reduction and climate adaptation planning.
      </p>
      <p class="mb-6">
        <a href="https://www.weltrisikobericht.de" target="_blank" class="text-blue-800 underline">Learn more about WRI at weltrisikobericht.de ↗</a>
      </p>

      <h2 class="text-lg font-semibold mt-6 mb-2">Reflections & Further Work</h2>
      <p class="mb-3">
        This project combines different global indicators to explore composite vulnerability profiles. It’s important to note that the <strong>Mercator projection</strong> distorts geographic proportions — for example, countries near the poles appear larger. A future update might explore alternative, more realistic map projections.
      </p>
      <p class="mb-3">
        This tool currently visualizes historical data, but in the future, one could apply <strong>machine learning models</strong> to make forecasts about militarisation or climate risk under different scenarios. These projections would remain speculative and rely on underlying assumptions and data quality.
      </p>
      <p class="mb-6">
        The combination logic here is based on <strong>normalized summation</strong>. There may be other meaningful ways to weight or cluster indicators, such as using principal component analysis or unsupervised clustering to identify country typologies. Exploring such directions could yield valuable insights.
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

const selectedYear = ref(2022)
const activeIndicators = ref(['gmi_score']) // GMI is active by default
const years = Array.from({ length: 2023 - 2000 }, (_, i) => 2000 + i)

const yearBubbleLeft = computed(() => {
  const percentage = ((selectedYear.value - 2000) / (2022 - 2000)) * 100
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
let geoLayer = null
let countryData = {}
const colorRange = ['#800026', '#BD0026', '#E31A1C', '#FC4E2A', '#FD8D3C']
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
  colorRanges.value = [
    { color: colorRange[0], label: 'Top 20%', threshold: thresholds[3] },
    { color: colorRange[1], label: '60–80%', threshold: thresholds[2] },
    { color: colorRange[2], label: '40–60%', threshold: thresholds[1] },
    { color: colorRange[3], label: '20–40%', threshold: thresholds[0] },
    { color: colorRange[4], label: 'Bottom 20%', threshold: -Infinity },
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
    const res = await fetch(`/api/countries/year/${selectedYear.value}`)
    const countries = await res.json()

    const indicatorMatrix = {}
    activeIndicators.value.forEach(ind => {
      indicatorMatrix[ind] = normalize(Object.fromEntries(countries.map(c => [c.iso_code, c[ind]])))
    })

    countryData = {}
    countries.forEach(c => {
      const iso = c.iso_code?.toUpperCase()
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
        layer.bindPopup(`<strong>${layer.feature.properties.ADMIN}</strong><br>Score (${selectedYear.value}): ${val?.toFixed(2) ?? 'no data'}`)
      })
    } else {
      const geojson = await fetch('/geo/ne_countries.geojson').then(r => r.json())
      geoLayer = L.geoJSON(geojson, {
        style: feature => {
          const iso = feature.properties.ADM0_A3
          const val = countryData[iso]
          return {
            fillColor: getColor(val),
            weight: 1,
            color: '#555',
            fillOpacity: 0.7
          }
        },
        onEachFeature: (feature, layer) => {
          const iso = feature.properties.ADM0_A3
          const val = countryData[iso]
          layer.bindPopup(`<strong>${feature.properties.ADMIN}</strong><br>Score (${selectedYear.value}): ${val?.toFixed(2) ?? 'no data'}`)
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
