// Composable for normalizing values and computing color thresholds for choropleth maps

export interface CountryValues {
  [isoCode: string]: number | null | undefined;
}

export interface ColorRange {
  color: string;
  label: string;
  threshold: number | null;
}

/**
 * Normalizes values between 0 and 1.
 * If all values are equal, returns 0.5.
 */
export function normalize(values: CountryValues): CountryValues {
  const valid = Object.values(values).filter((v): v is number => typeof v === 'number')
  const min = Math.min(...valid)
  const max = Math.max(...valid)
  const result: CountryValues = {}

  for (const [key, value] of Object.entries(values)) {
    if (typeof value !== 'number') {
      result[key] = null
    } else if (max === min) {
      result[key] = 0.5
    } else {
      result[key] = (value - min) / (max - min)
    }
  }
  return result
}

/**
 * Generates a color range legend for the choropleth map.
 * Based on percentile thresholds of values.
 */
export function generateColorRanges(values: CountryValues): ColorRange[] {
  const colorPalette = ['#800026', '#BD0026', '#E31A1C', '#FC4E2A', '#FD8D3C']
  const all = Object.values(values).filter((v): v is number => typeof v === 'number')
  if (!all.length) return []

  all.sort((a, b) => a - b)
  const thresholds = [0.2, 0.4, 0.6, 0.8].map(p => all[Math.floor(p * all.length)])

  return [
    { color: colorPalette[0], label: 'Top 20%', threshold: thresholds[3] },
    { color: colorPalette[1], label: '60–80%', threshold: thresholds[2] },
    { color: colorPalette[2], label: '40–60%', threshold: thresholds[1] },
    { color: colorPalette[3], label: '20–40%', threshold: thresholds[0] },
    { color: colorPalette[4], label: 'Bottom 20%', threshold: -Infinity },
    { color: '#ddd', label: 'no data available', threshold: null }
  ]
}

/**
 * Determines the color for a country value based on thresholds.
 */
export function getColor(score: number | null | undefined, colorRanges: ColorRange[]): string {
  if (score == null) return '#ddd'
  for (const range of colorRanges) {
    if (range.threshold == null) continue
    if (score >= range.threshold) return range.color
  }
  return colorRanges[colorRanges.length - 1].color
}