<template>
  <div class="grid grid-cols-2 md:grid-cols-3 gap-y-3 gap-x-4 mb-6 text-gray-800 max-w-xl mx-auto">
    <!-- GMI section -->
    <label class="col-span-full mt-2 mb-1 flex items-center font-bold text-base">
      <input
        type="checkbox"
        class="mr-2"
        value="gmi_score"
        v-model="activeIndicators"
        @change="emit('main-toggle', 'gmi')"
      />
      Global Militarisation Index (GMI)
      <span
        class="ml-1 cursor-help text-blue-900 text-xl"
        title="The GMI measures the relative weight and importance of a country's military compared to its society."
        >&#9432;</span
      >
      <a href="#gmi-info" class="ml-2 text-blue-800 text-xs">Read more ↓</a>
    </label>

    <label
      v-for="option in gmiSubs"
      :key="option.value"
      class="inline-flex items-center"
      :class="mainGmiActive ? 'opacity-50 cursor-not-allowed' : ''"
    >
      <input
        type="checkbox"
        :disabled="mainGmiActive"
        v-model="activeIndicators"
        :value="option.value"
        @change="emit('sub-toggle', 'gmi')"
        class="mr-2"
      />
      <span>{{ option.label }}</span>
      <span class="text-blue-600 cursor-help text-lg ml-1" :title="tooltipTexts[option.value]">&#9432;</span>
    </label>

    <!-- WRI section -->
    <label class="col-span-full mt-4 mb-1 flex items-center font-bold text-base">
      <input
        type="checkbox"
        class="mr-2"
        value="wri_score"
        v-model="activeIndicators"
        @change="emit('main-toggle', 'wri')"
      />
      World Risk Index (WRI)
      <span
        class="ml-1 cursor-help text-blue-900 text-xl"
        title="The WRI combines exposure to natural hazards and vulnerability to assess disaster risk."
        >&#9432;</span
      >
      <a href="#wri-info" class="ml-2 text-blue-800 text-xs">Read more ↓</a>
    </label>

    <label
      v-for="option in wriSubs"
      :key="option.value"
      class="inline-flex items-center"
      :class="mainWriActive ? 'opacity-50 cursor-not-allowed' : ''"
    >
      <input
        type="checkbox"
        :disabled="mainWriActive"
        v-model="activeIndicators"
        :value="option.value"
        @change="emit('sub-toggle', 'wri')"
        class="mr-2"
      />
      <span>{{ option.label }}</span>
      <span class="text-blue-600 cursor-help text-lg ml-1" :title="tooltipTexts[option.value]">&#9432;</span>
    </label>
  </div>
</template>

<script setup lang="ts">
/**
 * Presentational control for indicator selection.
 * - Uses v-model:activeIndicators (array of strings)
 * - Emits 'main-toggle' and 'sub-toggle' so the parent can apply the central rules from useIndicators()
 * - Imports options/tooltips from the domain module (no parent props required)
 */
import { computed } from 'vue'
import { gmiOptions, wriOptions, tooltipTexts } from '../../domain/indicators'

const activeIndicators = defineModel<string[]>('activeIndicators', { default: () => [] })

const emit = defineEmits<{
  (e: 'main-toggle', group: 'gmi' | 'wri'): void
  (e: 'sub-toggle', group: 'gmi' | 'wri'): void
}>()

// Filter out the main items for the sub lists
const gmiSubs = computed(() => gmiOptions.filter(o => o.value !== 'gmi_score'))
const wriSubs = computed(() => wriOptions.filter(o => o.value !== 'wri_score'))

// Disable sub-items if main is active
const mainGmiActive = computed(() => activeIndicators.value.includes('gmi_score'))
const mainWriActive = computed(() => activeIndicators.value.includes('wri_score'))
</script>