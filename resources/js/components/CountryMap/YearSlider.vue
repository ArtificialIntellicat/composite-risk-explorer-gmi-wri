<template>
  <div class="relative w-2/3 mx-auto mb-4">
    <!-- Floating year bubble -->
    <div
      class="absolute -top-6 bg-blue-100 text-blue-800 px-3 py-1 rounded shadow text-sm font-medium transition-all duration-200"
      :style="{ left: bubblePosition }"
    >
      {{ modelValue }}
    </div>

    <!-- Range input -->
    <input
      id="yearRange"
      type="range"
      min="2000"
      max="2022"
      :value="modelValue"
      @input="$emit('update:modelValue', +$event.target.value)"
      class="w-full z-10 relative"
    />

    <!-- Tick labels -->
    <div
      class="absolute top-full left-0 w-full flex justify-between text-xs text-gray-700 -mt-1 pointer-events-none"
    >
      <span
        v-for="year in years"
        :key="year"
        class="w-0 text-center"
      >
        {{ year % 5 === 0 ? year : '' }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: Number
})

const emit = defineEmits(['update:modelValue'])

const years = Array.from({ length: 2023 - 2000 }, (_, i) => 2000 + i)

const bubblePosition = computed(() => {
  const percentage = ((props.modelValue - 2000) / (2022 - 2000)) * 100
  return `calc(${percentage}% - 1.5rem)`
})
</script>
