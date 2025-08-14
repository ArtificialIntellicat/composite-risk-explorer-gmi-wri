// available indicators
export type IndicatorKey =
  | 'gmi_score' | 'milex_indicator' | 'personnel_indicator' | 'weapons_indicator'
  | 'wri_score' | 'wri_exposure' | 'wri_vulnerability' | 'wri_susceptibility'
  | 'wri_coping_capacity' | 'wri_adaptive_capacity';

// labels for checkboxes
export const gmiOptions: { label: string; value: IndicatorKey }[] = [
  { label: 'Militarisation Index', value: 'gmi_score' },
  { label: 'Military Expenditure Index', value: 'milex_indicator' },
  { label: 'Military Personnel Index', value: 'personnel_indicator' },
  { label: 'Heavy Weapons Index', value: 'weapons_indicator' },
];

export const wriOptions: { label: string; value: IndicatorKey }[] = [
  { label: 'World Risk Index', value: 'wri_score' },
  { label: 'Exposure', value: 'wri_exposure' },
  { label: 'Vulnerability', value: 'wri_vulnerability' },
  { label: 'Susceptibility', value: 'wri_susceptibility' },
  { label: 'Coping Capacity', value: 'wri_coping_capacity' },
  { label: 'Adaptive Capacity', value: 'wri_adaptive_capacity' },
];

// informative tooltips for checkboxs choices
export const tooltipTexts: Record<string, string> = {
  gmi_score: 'Composite index measuring the overall militarisation level of a country.',
  milex_indicator: 'Index based on military expenditure in relation to GDP and health spending.',
  personnel_indicator: 'Index based on the number of military personnel per capita.',
  weapons_indicator: 'Index based on the volume of heavy weapons in armed forces.',
  wri_score: 'Composite index reflecting a country s disaster risk due to natural hazards.',
  wri_exposure: 'Degree to which a country is exposed to natural hazards.',
  wri_vulnerability: 'Societal susceptibility and capacity to cope with disasters.',
  wri_susceptibility: 'Structural vulnerability of a country (e.g., infrastructure, health).',
  wri_coping_capacity: 'Short-term capabilities to reduce negative disaster impacts.',
  wri_adaptive_capacity: 'Long-term capacity to adapt and transform in response to risks.'
};
