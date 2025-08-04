<template>
  <div id="map" class="w-full h-[600px] mt-6"></div>
</template>

<script setup lang="ts">
import { onMounted, watch } from 'vue'
import { initMap, renderGeoLayer, updateGeoLayer } from '@/composables/useMap'
import type { CountryValues, ColorRange } from '@/composables/useIndicators'

const props = defineProps<{
  selectedYear: number
  countryData: CountryValues
  colorRanges: ColorRange[]
}>()

let map: L.Map | null = null
let geoLayer: L.GeoJSON | null = null

onMounted(async () => {
  map = initMap('map')
  geoLayer = await renderGeoLayer(map, props.countryData, props.colorRanges, props.selectedYear)
})

watch(
  () => [props.countryData, props.colorRanges, props.selectedYear],
  () => {
    if (map && geoLayer) {
      updateGeoLayer(geoLayer, props.countryData, props.colorRanges, props.selectedYear)
    }
  }
)
</script>

<style scoped>
#map {
  width: 100%;
  height: 600px;
}
</style>