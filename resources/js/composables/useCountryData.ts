// --- useCountryData.ts ---
// Composable for handling normalized country indicator values and color scale updates

import { ref, watch, type Ref } from 'vue'
import { normalize, generateColorRanges } from '@/composables/useIndicators'
import type { CountryValues, ColorRange } from '@/composables/useIndicators'

/**
 * Reactive state for normalized country values and corresponding color thresholds.
 */
export function useCountryData(
  rawData: Ref<CountryValues>,
  selectedYear: Ref<number>
) {
  const normalizedValues = ref<CountryValues>({})
  const colorRanges = ref<ColorRange[]>([])

  /**
   * Computes normalized values and color ranges whenever the data or year changes.
   */
  watch([rawData, selectedYear], () => {
    const year = selectedYear.value
    const valuesForYear: CountryValues = {}

    for (const [iso, yearly] of Object.entries(rawData.value)) {
      if (yearly && typeof yearly === 'object' && year in yearly) {
        valuesForYear[iso] = yearly[year] as number
      } else {
        valuesForYear[iso] = null
      }
    }

    const normalized = normalize(valuesForYear)
    normalizedValues.value = normalized
    colorRanges.value = generateColorRanges(normalized)
  }, { immediate: true })

  return {
    normalizedValues,
    colorRanges,
  }
}
