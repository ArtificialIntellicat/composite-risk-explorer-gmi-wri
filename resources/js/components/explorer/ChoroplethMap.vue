<template>
  <!-- Pure container; map is created by the composable -->
  <div ref="container" style="height: 600px; margin-top: 2rem;"></div>
</template>

<script setup lang="ts">
/**
 * ChoroplethMap: owns the Leaflet map lifecycle.
 * - Props: year, indicators, cbMode, useAltCB
 * - Emits v-model:colorRanges to keep Legend in sync with the parent
 * - Internals (init + styling) live in useChoroplethMap()
 */
import { ref, onMounted, watch } from 'vue'
import { useChoroplethMap } from '../../composables/useChoroplethMap'

type ColorRange = { color: string; label: string; threshold: number | null }

const props = defineProps<{
  year: number
  indicators: string[]
  cbMode: boolean
  useAltCB: boolean
}>()

// keep Legend in sync with parent via v-model
const colorRangesModel = defineModel<ColorRange[]>('colorRanges', { default: () => [] })

const container = ref<HTMLElement | null>(null)
const { colorRanges, initMap, refreshChoropleth } = useChoroplethMap()

onMounted(async () => {
  if (!container.value) return
  await initMap(container.value)
  await refreshChoropleth({
    year: props.year,
    indicators: props.indicators,
    cbMode: props.cbMode,
    useAltCB: props.useAltCB,
  })
  colorRangesModel.value = colorRanges.value
})

// refresh the choropleth when any relevant prop changes
watch(
  () => [props.year, props.indicators.slice(), props.cbMode, props.useAltCB],
  async () => {
    await refreshChoropleth({
      year: props.year,
      indicators: props.indicators,
      cbMode: props.cbMode,
      useAltCB: props.useAltCB,
    })
    colorRangesModel.value = colorRanges.value
  }
)
</script>
