<template>
  <div class="flex flex-col items-center mt-6 text-sm text-gray-800">
    <div
      class="mb-2 font-medium"
      :class="isPredicted ? 'text-blue-800' : 'text-amber-800'"
    >
      {{ isPredicted ? 'Predicted (2023–2050)' : 'Observed (≤2022)' }}
    </div>

    <div class="flex justify-center space-x-4">
      <div
        v-for="(range, idx) in colorRanges"
        :key="idx"
        class="flex items-center space-x-1"
      >
        <div
          :style="{ backgroundColor: range.color }"
          class="w-5 h-3 border border-gray-300"
        />
        <span>{{ range.label }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

type ColorRange = { color: string; label: string; threshold: number | null }

/**
 * We use defineModel() for values we bind with v-model from the parent,
 * even though this component does not modify them. This keeps parent usage
 * consistent with other controls. We keep lastActualYear as a plain prop.
 */
const year = defineModel<number>('year', { default: 2000 })
const colorRanges = defineModel<ColorRange[]>('colorRanges', { default: () => [] })

const props = defineProps<{
  lastActualYear: number
}>()

const isPredicted = computed(() => year.value > props.lastActualYear)
</script>