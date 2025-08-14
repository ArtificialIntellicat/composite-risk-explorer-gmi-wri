type SourceTag = 'actual' | 'predicted';

/**
 * Builds the "(optional) CI line" if exactly one indicator is selected and year > lastActualYear.
 * Expects a single row object for the country (as returned by your API).
 */
export function buildCiText(
  year: number,
  indicators: string[],
  row: Record<string, any> | undefined,
  lastActualYear: number
): string {
  if (year <= lastActualYear) return '';
  const metric = indicators.length === 1 ? indicators[0] : null;
  if (!metric || !row) return '';
  const lo = row[`${metric}_lo_ci`];
  const hi = row[`${metric}_hi_ci`];
  if (typeof lo === 'number' && typeof hi === 'number') {
    return `<br>CI (${metric}): ${lo.toFixed(2)} … ${hi.toFixed(2)}`;
  }
  return '';
}

/**
 * Final popup HTML for a country feature.
 */
export function buildPopupHtml(
  countryName: string,
  year: number,
  score: number | null | undefined,
  source: SourceTag,
  ciText: string
): string {
  const scoreText = score != null ? (score as number).toFixed(2) : 'no data';
  return (
    `<strong>${countryName}</strong>` +
    `<br>Score (${year}): ${scoreText}` +
    `<br>Source: ${source}` +
    ciText
  );
}
