import type { MapRow } from '../services/mapApi'
import { normalize } from './stats' // Normalize indicator scores for computation and comparability

/**
 * Builds a normalized matrix per indicator:
 * result[indicator][ISO3] = normalized value in [0,1] or null
 */
export function buildIndicatorMatrix(
  rows: MapRow[],
  indicators: string[]
): Record<string, Record<string, number | null>> {
  const matrix: Record<string, Record<string, number | null>> = {}

  for (const ind of indicators) {
    const perIso: Record<string, number | null> = {}
    rows.forEach((r) => {
      const iso = r.iso3?.toUpperCase()
      if (!iso) return
      const val = r[ind]
      perIso[iso] = typeof val === 'number' ? (val as number) : null
    })
    matrix[ind] = normalize(perIso)
  }

  return matrix
}

/**
 * Sums normalized indicators per ISO3. If no indicators, returns all nulls.
 */
export function buildCountryScores(
  rows: MapRow[],
  indicators: string[],
  matrix: Record<string, Record<string, number | null>>
): Record<string, number | null> {
  const result: Record<string, number | null> = {}

  rows.forEach((r) => {
    const iso = r.iso3?.toUpperCase()
    if (!iso) return
    if (!indicators.length) {
      result[iso] = null
      return
    }
    let sum = 0
    for (const ind of indicators) {
      const v = matrix[ind]?.[iso]
      if (v != null) sum += v
    }
    result[iso] = sum
  })

  return result
}
