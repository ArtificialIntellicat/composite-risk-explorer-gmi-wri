<template>
  <div class="bg-white shadow-lg max-w-4xl mx-auto p-6 mt-6 rounded-md text-[14px]">
    <!-- Title & Intro Blurb -->
    <IntroSection />

    <!-- Year selection (bubble & 5-year ticks) -->
    <YearSlider
      v-model:year="selectedYear"
      :min-year="MIN_YEAR"
      :max-year="MAX_YEAR"
    />

    <!-- Indicator selection (main/sub logic is handled in useIndicators) -->
    <IndicatorControls
      v-model:activeIndicators="activeIndicators"
      @main-toggle="onMainToggle"
      @sub-toggle="onSubToggle"
    />

    <!-- Leaflet map wrapper (owns init/refresh; returns legend via v-model) -->
    <ChoroplethMap
      v-model:colorRanges="colorRanges"
      :year="selectedYear"
      :indicators="activeIndicators"
      :cb-mode="colorBlindMode"
      :use-alt-c-b="useAltCB"
    />

    <!-- Legend reads colorRanges from the map -->
    <ColorLegend
      v-model:year="selectedYear"
      :last-actual-year="LAST_ACTUAL_YEAR"
      v-model:colorRanges="colorRanges"
    />

    <!-- Accessibility palette toggles (persisted via useIndicators) -->
    <AccessibilityControls
      v-model:colorBlindMode="colorBlindMode"
      v-model:useAltCB="useAltCB"
    />

    <!-- Collapsible about/forecasts/limitations content -->
    <AboutSections />
  </div>
</template>

<script setup lang="ts">
// Presentational components used in the template
import IntroSection from '../../components/explorer/IntroSection.vue'
import YearSlider from '../../components/explorer/YearSlider.vue'
import IndicatorControls from '../../components/explorer/IndicatorControls.vue'
import ChoroplethMap from '../../components/explorer/ChoroplethMap.vue'
import ColorLegend from '../../components/explorer/ColorLegend.vue'
import AccessibilityControls from '../../components/explorer/AccessibilityControls.vue'
import AboutSections from '../../components/explorer/AboutSections.vue'

// Vue utils
import { ref } from 'vue'

// Domain constants used in the template
import { MIN_YEAR, MAX_YEAR, LAST_ACTUAL_YEAR } from '../../domain/constants'

// Central UI state & rules (year, indicators, accessibility, toggle logic)
import { useIndicators } from '../../composables/useIndicators'

// Keep legend ranges in the parent so we can feed the ColorLegend via v-model.
type ColorRange = { color: string; label: string; threshold: number | null }
const colorRanges = ref<ColorRange[]>([])

// Pull screen state + toggle rules from a single source of truth.
const {
  selectedYear,
  activeIndicators,
  colorBlindMode,
  useAltCB,
  onMainToggle,
  onSubToggle
} = useIndicators()
</script>