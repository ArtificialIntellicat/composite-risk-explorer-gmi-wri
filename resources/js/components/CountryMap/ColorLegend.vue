<template>
  <div class="flex justify-center mt-6 space-x-4 text-sm text-gray-800">
    <div v-for="(range, idx) in colorRanges" :key="idx" class="flex items-center space-x-1">
      <div :style="{ backgroundColor: range.color }" class="w-5 h-3 border border-gray-300"></div>
      <span>{{ range.label }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ColorRange } from '@/composables/useIndicators'

const props = defineProps<{
  colorRanges: ColorRange[]
}>()
</script>