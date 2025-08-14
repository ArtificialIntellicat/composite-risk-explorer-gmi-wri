<template>
  <div>
    <label :for="id" class="block mb-2 font-semibold text-center text-gray-800 text-lg">
      Year
    </label>

    <div class="relative w-2/3 mx-auto mb-4">
      <!-- Bubble with current year -->
      <div
        class="absolute -top-6 bg-blue-100 text-blue-800 px-3 py-1 rounded shadow text-sm font-medium transition-all duration-200"
        :style="{ left: bubbleLeft }"
      >
        {{ year }}
      </div>

      <!-- Range input -->
      <input
        :id="id"
        type="range"
        class="w-full z-10 relative"
        :min="minYear"
        :max="maxYear"
        :value="year"
        @input="onInput"
        @change="emit('changed')"
      />

      <!-- Ticks every 5 years -->
      <div class="absolute top-full left-0 w-full flex justify-between text-xs text-gray-700 -mt-1 pointer-events-none">
        <span
          v-for="y in tickYears"
          :key="y"
          class="w-0 text-center"
        >
          {{ y }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
/**
 * Year slider with bubble and 5-year ticks.
 * Uses defineModel() (Vue 3.4+) for v-model:year.
 * Emits "changed" on commit (native change), useful for triggering refreshMap().
 */
import { computed } from 'vue'

const year = defineModel<number>('year', { default: 2000 })

const props = defineProps<{
  minYear: number
  maxYear: number
  id?: string
}>()

const emit = defineEmits<{ (e: 'changed'): void }>()

const id = computed(() => props.id ?? 'yearRange')

/** Compute bubble left position based on current year and min/max. */
const bubbleLeft = computed(() => {
  const pct = ((year.value - props.minYear) / (props.maxYear - props.minYear)) * 100
  // Slight negative translation to roughly center the bubble
  return `calc(${pct}% - 1.5rem)`
})

/** Build tick labels for every 5 years within min..max. */
const tickYears = computed(() => {
  const out: number[] = []
  for (let y = props.minYear; y <= props.maxYear; y++) {
    if (y % 5 === 0) out.push(y)
  }
  return out
})

function onInput(e: Event) {
  year.value = Number((e.target as HTMLInputElement).value)
}
</script>