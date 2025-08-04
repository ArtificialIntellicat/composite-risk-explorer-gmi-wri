<template>
  <div class="grid grid-cols-2 md:grid-cols-3 gap-y-3 gap-x-4 mb-6 text-gray-800 max-w-xl mx-auto">
    <!-- Main GMI -->
    <label class="col-span-full mt-2 mb-1 flex items-center font-bold text-base">
      <input
        type="checkbox"
        class="mr-2"
        value="gmi_score"
        :checked="modelValue.includes('gmi_score')"
        @change="toggleMain('gmi')"
      />
      Global Militarisation Index (GMI)
      <span class="ml-1 cursor-help text-blue-900 text-xl" title="The GMI measures the relative weight and importance of a country's military compared to its society.">&#9432;</span>
      <a href="#gmi-info" class="ml-2 text-blue-800 text-xs">Read more ↓</a>
    </label>

    <!-- Sub GMI -->
    <label
      v-for="opt in gmiOptions.filter(o => o.value !== 'gmi_score')"
      :key="opt.value"
      class="inline-flex items-center"
      :class="isGmiMainActive ? 'opacity-50 cursor-not-allowed' : ''"
    >
      <input
        type="checkbox"
        :value="opt.value"
        :disabled="isGmiMainActive"
        :checked="modelValue.includes(opt.value)"
        @change="toggleSub('gmi', opt.value)"
        class="mr-2"
      />
      <span>{{ opt.label }}</span>
      <span class="text-blue-600 cursor-help text-lg ml-1" :title="tooltipTexts[opt.value]">&#9432;</span>
    </label>

    <!-- Main WRI -->
    <label class="col-span-full mt-4 mb-1 flex items-center font-bold text-base">
      <input
        type="checkbox"
        class="mr-2"
        value="wri_score"
        :checked="modelValue.includes('wri_score')"
        @change="toggleMain('wri')"
      />
      World Risk Index (WRI)
      <span class="ml-1 cursor-help text-blue-900 text-xl" title="The WRI combines exposure to natural hazards and vulnerability to assess disaster risk.">&#9432;</span>
      <a href="#wri-info" class="ml-2 text-blue-800 text-xs">Read more ↓</a>
    </label>

    <!-- Sub WRI -->
    <label
      v-for="opt in wriOptions.filter(o => o.value !== 'wri_score')"
      :key="opt.value"
      class="inline-flex items-center"
      :class="isWriMainActive ? 'opacity-50 cursor-not-allowed' : ''"
    >
      <input
        type="checkbox"
        :value="opt.value"
        :disabled="isWriMainActive"
        :checked="modelValue.includes(opt.value)"
        @change="toggleSub('wri', opt.value)"
        class="mr-2"
      />
      <span>{{ opt.label }}</span>
      <span class="text-blue-600 cursor-help text-lg ml-1" :title="tooltipTexts[opt.value]">&#9432;</span>
    </label>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: Array
})

const emit = defineEmits(['update:modelValue'])

const gmiOptions = [
  { label: 'Militarisation Index', value: 'gmi_score' },
  { label: 'Military Expenditure Index', value: 'milex_indicator' },
  { label: 'Military Personnel Index', value: 'personnel_indicator' },
  { label: 'Heavy Weapons Index', value: 'weapons_indicator' }
]

const wriOptions = [
  { label: 'World Risk Index', value: 'wri_score' },
  { label: 'Exposure', value: 'wri_exposure' },
  { label: 'Vulnerability', value: 'wri_vulnerability' },
  { label: 'Susceptibility', value: 'wri_susceptibility' },
  { label: 'Coping Capacity', value: 'wri_coping_capacity' },
  { label: 'Adaptive Capacity', value: 'wri_adaptive_capacity' }
]

const tooltipTexts = {
  gmi_score: 'Composite index measuring the overall militarisation level of a country.',
  milex_indicator: 'Military expenditure relative to GDP and health spending.',
  personnel_indicator: 'Military personnel per 1,000 inhabitants.',
  weapons_indicator: 'Volume of heavy weapons in armed forces.',
  wri_score: 'Composite index of disaster risk from natural hazards.',
  wri_exposure: 'Degree of exposure to natural hazards.',
  wri_vulnerability: 'General social vulnerability to disasters.',
  wri_susceptibility: 'Infrastructure and health-related vulnerability.',
  wri_coping_capacity: 'Short-term coping mechanisms.',
  wri_adaptive_capacity: 'Long-term climate and disaster adaptation capacity.'
}

const isGmiMainActive = computed(() => props.modelValue.includes('gmi_score'))
const isWriMainActive = computed(() => props.modelValue.includes('wri_score'))

function toggleMain(group) {
  const mainValue = group === 'gmi' ? 'gmi_score' : 'wri_score'
  const subValues = (group === 'gmi' ? gmiOptions : wriOptions)
    .map(o => o.value)
    .filter(v => v !== mainValue)

  let newValues = [...props.modelValue]
  if (newValues.includes(mainValue)) {
    // remove all
    newValues = newValues.filter(v => v !== mainValue)
  } else {
    // remove sub and add main
    newValues = newValues.filter(v => !subValues.includes(v))
    newValues.push(mainValue)
  }
  emit('update:modelValue', newValues)
}

function toggleSub(group, subValue) {
  const mainValue = group === 'gmi' ? 'gmi_score' : 'wri_score'
  let newValues = props.modelValue.filter(v => v !== mainValue)

  if (props.modelValue.includes(subValue)) {
    newValues = newValues.filter(v => v !== subValue)
  } else {
    newValues.push(subValue)
  }
  emit('update:modelValue', newValues)
}
</script>