export type MapRow = {
  iso3: string;
  name?: string;
  year: number;
  source?: 'actual' | 'predicted';
  // dynamic metric fields (e.g., gmi_score, wri_score, ...), plus optional CI fields
  [key: string]: string | number | undefined;
};

/**
 * Fetches map rows for a given year and list of indicators.
 * Example response item: { iso3, year, source, gmi_score, wri_score, ... }
 */
export async function fetchMapRows(year: number, indicators: string[]): Promise<MapRow[]> {
  const qs = new URLSearchParams({
    year: String(year),
    metrics: indicators.join(','),
  });

  const res = await fetch(`/api/map-data?${qs.toString()}`, {
    headers: { Accept: 'application/json' },
  });
  if (!res.ok) {
    throw new Error(`fetchMapRows failed: ${res.status} ${res.statusText}`);
  }
  return (await res.json()) as MapRow[];
}
