<template>
  <div class="flex items-center justify-center gap-4 mt-2">
    <!-- Toggle 1: Color-vision friendly mode -->
    <label class="inline-flex items-center gap-2 text-xs text-gray-700">
      <input
        id="cb-mode"
        type="checkbox"
        class="h-4 w-4"
        :checked="colorBlindMode"
        @change="onToggleCBMode"
      />
      <span>Color-vision friendly</span>
    </label>

    <!-- Toggle 2: Palette family (active only when CB mode is on) -->
    <div
      class="flex items-center gap-2 text-xs text-gray-700"
      :class="!colorBlindMode ? 'opacity-50 pointer-events-none' : ''"
      :aria-disabled="!colorBlindMode"
    >
      <span :class="['select-none', useAltCB ? 'text-gray-500' : 'text-gray-900 font-semibold']">
        Viridis/Inferno
      </span>

      <button
        type="button"
        role="switch"
        :aria-checked="useAltCB"
        :tabindex="colorBlindMode ? 0 : -1"
        @click="onToggleFamily"
        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 ease-in-out
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 bg-blue-300"
      >
        <span class="sr-only">Toggle color scale family</span>
        <span
          class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200 ease-in-out"
          :class="useAltCB ? 'translate-x-5' : 'translate-x-0.5'"
        />
      </button>

      <span :class="['select-none', useAltCB ? 'text-gray-900 font-semibold' : 'text-gray-500']">
        Cividis/Plasma
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
/**
 * Vue 3.4+: use defineModel() to create two v-model bindings without manual props/emits.
 * We still emit a separate "changed" event as a simple hook for parent refresh.
 */
const colorBlindMode = defineModel<boolean>('colorBlindMode', { default: false })
const useAltCB = defineModel<boolean>('useAltCB', { default: false })

const emit = defineEmits<{ (e: 'changed'): void }>()

function onToggleCBMode(e: Event) {
  colorBlindMode.value = (e.target as HTMLInputElement).checked
  emit('changed')
}

function onToggleFamily() {
  if (!colorBlindMode.value) return
  useAltCB.value = !useAltCB.value
  emit('changed')
}
</script>