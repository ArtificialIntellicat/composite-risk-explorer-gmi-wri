import { ref, computed, watch } from 'vue'
import { MIN_YEAR, LAST_ACTUAL_YEAR, MAX_YEAR } from '../domain/constants'
import { gmiOptions, wriOptions } from '../domain/indicators' 

type Group = 'gmi' | 'wri'

/**
 * Centralizes UI state (year, indicators, color-blind toggles) and toggle rules.
 * Optionally call `setOnChange(cb)` so all toggle handlers auto-trigger a refresh.
 */
export function useIndicators() {
  // --- state ---
  const selectedYear = ref<number>(LAST_ACTUAL_YEAR)
  const activeIndicators = ref<string[]>(['gmi_score']) // default selection

  const colorBlindMode = ref(false)
  const useAltCB = ref(false) // false: Viridis/Inferno, true: Cividis/Plasma

  // Optional auto-refresh callback wiring
  let afterChange: (() => void) | undefined
  function setOnChange(cb: () => void) { afterChange = cb }

  // Persistence (localStorage)
  try { colorBlindMode.value = localStorage.getItem('cbMode') === '1' } catch {}
  try { useAltCB.value      = localStorage.getItem('cbAlt')  === '1' } catch {}

  watch(colorBlindMode, v => { try { localStorage.setItem('cbMode', v ? '1' : '0') } catch {} })
  watch(useAltCB,      v => { try { localStorage.setItem('cbAlt',  v ? '1' : '0') } catch {} })

  // Toggle rules
  function onMainToggle(group: Group) {
    const mainValue = group === 'gmi' ? 'gmi_score' : 'wri_score'
    const subOptions = group === 'gmi' ? gmiOptions : wriOptions

    if (activeIndicators.value.includes(mainValue)) {
      // If main is active, remove all sub-indicators from the same group
      subOptions.forEach(opt => {
        if (opt.value !== mainValue) {
          activeIndicators.value = activeIndicators.value.filter(v => v !== opt.value)
        }
      })
    }
    afterChange?.()
  }

  function onSubToggle(group: Group) {
    const mainValue = group === 'gmi' ? 'gmi_score' : 'wri_score'
    if (activeIndicators.value.includes(mainValue)) {
      // If any sub is toggled on, ensure the main checkbox is off
      activeIndicators.value = activeIndicators.value.filter(v => v !== mainValue)
    }
    afterChange?.()
  }

  return {
  selectedYear, activeIndicators, colorBlindMode, useAltCB,
  onMainToggle, onSubToggle, setOnChange}
}
