<template>
  <div class="max-w-3xl mx-auto text-gray-900 text-sm mt-10 space-y-3">

    <!-- GMI Info (kept as provided) -->
    <details id="gmi-info" class="group">
      <summary class="cursor-pointer text-lg font-semibold text-gray-800 flex items-center">
        <span class="mr-2">About the Global Militarisation Index (GMI)</span>
        <span class="ml-auto text-gray-500 group-open:rotate-90 transition-transform">▸</span>
      </summary>
      <div class="mt-2">
        <p class="mb-2">
          The GMI, published annually by the Bonn International Center for Conversion (BICC), reflects a country’s relative
          militarisation compared to its society. It combines data on military expenditures, personnel, and heavy weapons
          in relation to indicators like GDP or health spending. A high GMI score indicates that the military sector plays
          a disproportionately large role in a country’s resource allocation.
        </p>
        <p class="mb-2">
          <a href="https://gmi.bicc.de" target="_blank" class="text-blue-800 underline">Learn more at gmi.bicc.de ↗</a>
        </p>
      </div>
    </details>

    <!-- WRI Info (kept as provided) -->
    <details id="wri-info" class="group">
      <summary class="cursor-pointer text-lg font-semibold text-gray-800 flex items-center">
        <span class="mr-2">About the World Risk Index (WRI)</span>
        <span class="ml-auto text-gray-500 group-open:rotate-90 transition-transform">▸</span>
      </summary>
      <div class="mt-2">
        <p class="mb-2">
          The WRI is published by the Bündnis Entwicklung Hilft and IFHV (Ruhr University Bochum). It measures disaster risk
          based on exposure to natural hazards and vulnerability (susceptibility, coping capacity, adaptive capacity).
          It supports disaster risk reduction and climate adaptation planning.
        </p>
        <p class="mb-2">
          <a href="https://www.weltrisikobericht.de" target="_blank" class="text-blue-800 underline">weltrisikobericht.de ↗</a>
        </p>
      </div>
    </details>

    <!-- Forecasts overview (clarified) -->
    <details id="forecasts" class="group">
      <summary class="cursor-pointer text-lg font-semibold text-gray-800 flex items-center">
        <span class="mr-2">Forecasts & Predictions after 2022</span>
        <span class="ml-auto text-gray-500 group-open:rotate-90 transition-transform">▸</span>
      </summary>
      <div class="mt-2 space-y-2">
        <p>
          For <span class="text-amber-800 font-medium">2000–2022</span> the map shows <strong>observed</strong> values (red palette).
          For <span class="text-blue-800 font-medium">2023–2050</span> it switches to <strong>model-based forecasts</strong> (blue palette),
          computed <em>per country and per indicator</em>. These are transparent, scenario-free extrapolations.
        </p>
        <p class="text-xs text-gray-700">
          Serving logic: the API returns DB values for ≤2022; for &gt;2022 it reads
          <code>public/data/predictions.json</code> built offline and deployed with the app.
        </p>
      </div>
    </details>

    <!-- Accessibility -->
    <details id="accessibility" class="group">
      <summary class="cursor-pointer text-lg font-semibold text-gray-800 flex items-center">
        <span class="mr-2">Accessibility &amp; Color-Vision Support</span>
        <span class="ml-auto text-gray-500 group-open:rotate-90 transition-transform">▸</span>
      </summary>
      <div class="mt-2 space-y-2">
        <p>
          Use the <em>Color-vision friendly</em> toggle to switch the choropleth to perceptually uniform ramps.
          You can choose between two families: <strong>Viridis/Inferno</strong> or <strong>Cividis/Plasma</strong>.
          Both provide monotonic lightness so classes remain distinguishable for common CVD types.
        </p>
        <p class="text-xs text-gray-700">
          Tip: the setting is keyboard-accessible and persisted locally, so your preference sticks across reloads.
          The legend updates to reflect the active palette.
        </p>
      </div>
    </details>

    <!-- Dev notes (concise bullets) -->
    <details id="methods-dev" class="group mt-2">
      <summary class="cursor-pointer text-lg font-semibold text-gray-800 flex items-center">
        <span>Developer Notes</span>
        <span class="ml-auto text-gray-500 group-open:rotate-90 transition-transform">▸</span>
      </summary>
      <div class="mt-2 text-sm text-gray-900 space-y-2">
        <p><strong>Computation of scores &amp; colors.</strong> Multiple selected indicators are normalized per year using min–max scaling and then summed:
          <code class="text-xs">norm(x) = (x − min) / (max − min)</code>; <code class="text-xs">score(country) = Σ norm(value)</code>. Missing indicators contribute 0.</p>
        <p><strong>Data &amp; scope.</strong> Annual series per <code>iso3</code> and metric (~2000–2022); missing values dropped; require <code>min_points = 3</code>.</p>
        <p><strong>Model.</strong> ETS (Holt–Winters): <code>trend="add"</code>, <code>seasonal=None</code> (annual), estimated init, optional damped trend; Pandas yearly <code>PeriodIndex</code>.</p>
        <p><strong>Uncertainty.</strong> 95% band via residual std. dev.: <code>ŷ ± 1.96·σ</code> (parameter/model uncertainty not included).</p>
        <p><strong>Output.</strong> Flat JSON rows (iso × metric × year): <code>value</code>, <code>lo_ci</code>, <code>hi_ci</code>, <code>method</code> (rounded to 3 decimals).</p>
        <pre class="bg-gray-50 p-3 rounded overflow-x-auto text-xs">
{ "iso3": "DEU", "metric": "gmi_score", "year": 2030,
  "value": 73.579, "lo_ci": 52.599, "hi_ci": 94.558,
  "source": "predicted", "method": "ets_additive_damped", "version": "1.0" }
        </pre>
        <p><strong>Why ETS?</strong> Lightweight, explainable for short annual series. Heavier options (ARIMA/Prophet/state-space) add tuning overhead.</p>
        <p><strong>Notes.</strong> Occasional AIC/BIC warnings when SSE≈0 are benign. Coloring uses within-year normalization (see above).</p>
      </div>
    </details>

    <!-- Limitations & next steps -->
    <details id="limitations" class="group">
      <summary class="cursor-pointer text-lg font-semibold text-gray-800 flex items-center">
        <span class="mr-2">Limitations &amp; Next Steps</span>
        <span class="ml-auto text-gray-500 group-open:rotate-90 transition-transform">▸</span>
      </summary>
      <div class="mt-2 space-y-2">
        <p class="mb-2">
          Forecasts are straight extrapolations; they do not encode scenarios or policy shocks. Residual-based CIs are indicative only.
          The map uses the Mercator projection, which exaggerates area near the poles.
        </p>
        <strong>Potential upgrades:</strong>
        <ul class="list-disc ml-5 space-y-1">
          <li>Switch to a more realistic (e.g., equal-area) world projection for fairer visual comparison.</li>
          <li>Scenario-driven forecasts with exogenous drivers (e.g., GDP, emissions).</li>
          <li>Hierarchical models to share strength across countries; add bounded/monotone constraints where meaningful.</li>
          <li>Rolling-origin evaluation for model selection; explicitly model horizon-dependent uncertainty.</li>
        </ul>
      </div>
    </details>

    <!-- Sources -->
    <p class="text-center text-gray-700 text-xs">
      Note: Values for 2023–2050 are model-based forecasts; see <a href="#forecasts" class="text-blue-800 underline">Forecasts</a>.
    </p>
    <p class="text-center text-gray-800 text-sm mb-6 mt-6">
      Sources:
      <a href="https://gmi.bicc.de/ranking-table" class="underline" target="_blank">BICC</a>,
      <a href="https://www.weltrisikobericht.de" class="underline" target="_blank">WorldRiskReport</a>
    </p>
  </div>
</template>

<script setup lang="ts">
// Presentational only. No script logic needed.
</script>

<style scoped>
/* Keep default summary bullet hidden and show caret rotation on open for consistent UX */
details > summary { list-style: none; }
details > summary::-webkit-details-marker { display: none; }
.group[open] summary .group-open\:rotate-90 { transform: rotate(90deg); }
</style>
