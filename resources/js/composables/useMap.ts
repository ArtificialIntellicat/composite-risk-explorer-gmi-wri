// Composable for initializing and updating a Leaflet map with choropleth rendering using TypeScript best practices

import L, { Map as LeafletMap, GeoJSON, Layer, PathOptions, tileLayer } from 'leaflet'
import { getColor } from '@/composables/useIndicators'
import type { CountryValues, ColorRange } from '@/composables/useIndicators'

interface FeatureProperties {
  ADM0_A3: string;
  ADMIN: string;
}

interface FeatureWithProps {
  properties: FeatureProperties;
}

/**
 * Initializes a Leaflet map instance with default view and basemap.
 * @param containerId The DOM element ID where the map will be rendered.
 * @returns A Leaflet map instance.
 */
export function initMap(containerId: string = 'map'): LeafletMap {
  const map = L.map(containerId).setView([20, 0], 2)
  tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png', {
    attribution: '© OpenMapTiles © OpenStreetMap contributors',
    maxZoom: 18,
  }).addTo(map)
  return map
}

/**
 * Loads and renders the country GeoJSON layer with choropleth fill colors.
 * @param map The Leaflet map instance.
 * @param countryValues An object mapping ISO codes to indicator values.
 * @param colorRanges Color thresholds used to determine fill color.
 * @param year The selected year for display in popups.
 * @returns A Promise resolving to the added GeoJSON layer.
 */
export async function renderGeoLayer(
  map: LeafletMap,
  countryValues: CountryValues,
  colorRanges: ColorRange[],
  year: number
): Promise<GeoJSON> {
  try {
    const response = await fetch('/geo/ne_countries.geojson')
    const geojson = await response.json()

    const geoLayer = L.geoJSON(geojson, {
      style: (feature: any): PathOptions => {
        const iso: string = feature.properties?.ADM0_A3
        const val = iso ? countryValues[iso] : null
        return {
          fillColor: getColor(val ?? null, colorRanges),
          weight: 1,
          color: '#555',
          fillOpacity: 0.7,
        }
      },
      onEachFeature: (feature: any, layer: Layer): void => {
        const iso: string = feature.properties?.ADM0_A3
        const val = iso ? countryValues[iso] : null
        const admin = feature.properties?.ADMIN ?? 'Unknown'
        layer.bindPopup(
          `<strong>${admin}</strong><br>Score (${year}): ${val != null ? val.toFixed(2) : 'no data'}`
        )
      },
    })

    geoLayer.addTo(map)
    return geoLayer
  } catch (error) {
    console.error('Failed to load or render GeoJSON:', error)
    throw error
  }
}

/**
 * Updates an existing GeoJSON layer with new indicator values and popup text.
 * @param geoLayer The previously added GeoJSON layer.
 * @param countryValues Updated ISO-to-score map.
 * @param colorRanges New thresholds for color classification.
 * @param year The selected year for popup labeling.
 */
export function updateGeoLayer(
  geoLayer: GeoJSON,
  countryValues: CountryValues,
  colorRanges: ColorRange[],
  year: number
): void {
  if (!geoLayer) return

  geoLayer.eachLayer((layer) => {
  const feature = (layer as L.GeoJSON).feature as FeatureWithProps | undefined;
  if (!feature) return;

  const iso: string = feature.properties.ADM0_A3;
  const val = iso ? countryValues[iso] : null;
  const admin = feature.properties.ADMIN ?? 'Unknown';

  (layer as L.Path).setStyle({
    fillColor: getColor(val ?? null, colorRanges),
  });

  layer.bindPopup(
    `<strong>${admin}</strong><br>Score (${year}): ${val != null ? val.toFixed(2) : 'no data'}`
  );
});
}
