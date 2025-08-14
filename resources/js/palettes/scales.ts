// Color scales incl. palettes for color-blind people (accessibility)
// Differing color scales for actual and predicted data in order to visually calrify that predicted data is only an assumption
import { LAST_ACTUAL_YEAR } from '../domain/constants';

// Standard colors (non-CB)
const ACTUAL = ['#800026','#BD0026','#E31A1C','#FC4E2A','#FD8D3C'];   // reds
const PRED   = ['#08306B','#08519C','#2171B5','#4292C6','#6BAED6'];   // blues

// CB family A: Viridis (future) / Inferno (observed)
const CB_A_PRED = ['#440154','#3b528b','#21918c','#5ec962','#fde725'];
const CB_A_ACT  = ['#000004','#2c115f','#721f81','#f1605d','#fcffa4'];

// CB family B: Cividis (future) / Plasma (observed)
const CB_B_PRED = ['#00204c','#2c4f73','#576d83','#a1a77d','#fdea45'];
const CB_B_ACT  = ['#0d0887','#6a00a8','#b12a90','#e16462','#f0f921'];

/**
 * Choose color according to year and accessibility mode
 */
export function pickPalette(
  year: number,
  opts: { cbMode: boolean; useAltCB: boolean }
): string[] {
  const isPred = year > LAST_ACTUAL_YEAR;
  if (!opts.cbMode) return isPred ? PRED : ACTUAL;
  if (opts.useAltCB) return isPred ? CB_B_PRED : CB_B_ACT;
  return isPred ? CB_A_PRED : CB_A_ACT;
}

/**
 * Build color legends (Top20/…/Bottom20) from scores
 * values: { ISO3 -> score | null }
 */
export function buildColorRanges(
  values: Record<string, number | null | undefined>,
  palette: string[]
): { color: string; label: string; threshold: number | null }[] {
  const all = Object.values(values).filter((v): v is number => typeof v === 'number').sort((a,b) => a-b);
  if (!all.length) {
    // fallback: only "no data"
    return [{ color: '#ddd', label: 'no data', threshold: null }];
  }
  const q = (p: number) => all[Math.floor(p * all.length)];
  const q20 = q(0.2), q40 = q(0.4), q60 = q(0.6), q80 = q(0.8);

  return [
    { color: palette[0], label: 'Top 20%',    threshold: q80 },
    { color: palette[1], label: '60–80%',     threshold: q60 },
    { color: palette[2], label: '40–60%',     threshold: q40 },
    { color: palette[3], label: '20–40%',     threshold: q20 },
    { color: palette[4], label: 'Bottom 20%', threshold: -Infinity },
    { color: '#ddd',     label: 'no data',    threshold: null },
  ];
}

/**
 * Returns correct color for score range.
 */
export function colorFor(
  ranges: { color: string; label: string; threshold: number | null }[],
  score: number | null | undefined
): string {
  if (score == null) return '#ddd';
  for (const r of ranges) {
    if (r.threshold == null) continue;    // skip "no data"
    if (score >= r.threshold) return r.color;
  }
  return ranges.at(-1)?.color ?? '#ddd';
}
