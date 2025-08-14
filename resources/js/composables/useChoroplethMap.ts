import { ref } from 'vue';
import 'leaflet/dist/leaflet.css';

import { fetchMapRows, type MapRow } from '../services/mapApi';
import { pickPalette, buildColorRanges, colorFor } from '../palettes/scales'; // Color palette incl. color-vision accessibility
import { LAST_ACTUAL_YEAR } from '../domain/constants'; // Year for switch between actual and predicted data
import { buildCiText, buildPopupHtml } from '../map/popup' // CI popup for clarfication of insecurity of predicted data (shown when exactly one predicted indicator is chosen)
import { buildIndicatorMatrix, buildCountryScores } from '../utils/scores' // Normalize per indicator, then sum across indicators (pure utils)


type SourceTag = 'actual' | 'predicted';

export function useChoroplethMap() {
  // Keep Leaflet entities as `any` to avoid typing mismatches across setups
  const map = ref<any>(null);
  const geoLayer = ref<any>(null);

  const countryData = ref<Record<string, number | null>>({});
  const countrySource = ref<Record<string, SourceTag>>({});
  const rowByIso = ref<Record<string, MapRow>>({});
  const colorRanges = ref<{ color: string; label: string; threshold: number | null }[]>([]);

  /**
   * Initialize the Leaflet map and add the base GeoJSON layer.
   * Uses a dynamic import so we never fight TS module shapes of Leaflet.
   */
  async function initMap(container: string | HTMLElement) {
    const L: any = (await import('leaflet')).default ?? (await import('leaflet'));

    map.value = L.map(container as any).setView([20, 0], 2);

    L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png', {
      attribution: '© OpenMapTiles © OpenStreetMap contributors',
    }).addTo(map.value);

    const geojsonData = await fetch('/geo/ne_countries.geojson').then((r) => r.json());
    geoLayer.value = L.geoJSON(geojsonData, {
      style: () => ({ weight: 1.25, color: '#444', fillOpacity: 0.8, fillColor: '#ddd' }),
      onEachFeature: (feature: any, layer: any) => {
        layer.bindPopup(`<strong>${feature.properties.ADMIN}</strong>`);
      },
    }).addTo(map.value);
  }

  /**
   * Fetch rows, compute normalized scores, build color ranges, and restyle the layer.
   * Call this when year/indicators/CB settings change.
   */
  async function refreshChoropleth(params: {
    year: number;
    indicators: string[];
    cbMode: boolean;
    useAltCB: boolean;
  }) {
    const { year, indicators, cbMode, useAltCB } = params;

    // Fetch rows
    const rows = await fetchMapRows(year, indicators);

    // Index rows + determine source tags
    countrySource.value = {};
    rowByIso.value = {};
    rows.forEach((r) => {
      const iso = r.iso3?.toUpperCase();
      if (!iso) return;
      rowByIso.value[iso] = r;
      countrySource.value[iso] = (r.source ??
        (year > LAST_ACTUAL_YEAR ? 'predicted' : 'actual')) as SourceTag;
    });

    // Normalize per indicator, then sum across indicators (pure utils)
    const indicatorMatrix = buildIndicatorMatrix(rows, indicators)
    countryData.value = buildCountryScores(rows, indicators, indicatorMatrix)

    // Palette + legend ranges
    const palette = pickPalette(year, { cbMode, useAltCB });
    colorRanges.value = buildColorRanges(countryData.value, palette);

    // Style/update features
    if (!geoLayer.value) return;
    geoLayer.value.eachLayer((layer: any) => {
      const feature: any = layer.feature;
      const iso = feature?.properties?.ADM0_A3 as string | undefined;
      if (!iso) return;

      const val = countryData.value[iso] ?? null;
      const source = (countrySource.value[iso] ??
        (year > LAST_ACTUAL_YEAR ? 'predicted' : 'actual')) as 'actual' | 'predicted';

      const ci = buildCiText(year, indicators, rowByIso.value[iso], LAST_ACTUAL_YEAR);
      const html = buildPopupHtml(feature.properties.ADMIN, year, val, source, ci);

      layer.setStyle({ fillColor: colorFor(colorRanges.value, val) });
      layer.bindPopup(html);
    });
  }

  return {
    colorRanges,
    initMap,
    refreshChoropleth,
  };
}
