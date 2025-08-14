/**
 * Min-max normalize a record of numeric values (indicator scores) per key.
 * Non-numeric values become null. If max === min, all numeric values map to 0.5.
 */
export function normalize(
  record: Record<string, number | null | undefined>
): Record<string, number | null> {
  const values = Object.values(record).filter((v): v is number => typeof v === 'number');
  if (values.length === 0) {
    return Object.fromEntries(Object.keys(record).map((k) => [k, null]));
  }

  const min = Math.min(...values);
  const max = Math.max(...values);

  return Object.fromEntries(
    Object.entries(record).map(([k, v]) => {
      if (typeof v !== 'number') return [k, null];
      if (max === min) return [k, 0.5]; // avoid divide-by-zero
      return [k, (v - min) / (max - min)];
    })
  );
}
