<template>
  <div class="max-w-3xl mx-auto text-gray-800 text-sm mt-10">
    <!-- GMI Info -->
    <h2 id="gmi-info" class="text-lg font-semibold mt-6 mb-2">About the Global Militarisation Index (GMI)</h2>
    <p class="mb-2">
      The GMI, published annually by the Bonn International Center for Conversion (BICC), reflects a country’s relative militarisation compared to its society.
      It combines data on military expenditures, personnel, and heavy weapons in relation to indicators like GDP or health spending.
      A high GMI score indicates that the military sector plays a disproportionately large role in a country’s resource allocation.
    </p>
    <p class="mb-6">
      <a href="https://gmi.bicc.de" target="_blank" class="text-blue-800 underline">Learn more about GMI at gmi.bicc.de ↗</a>
    </p>

    <!-- WRI Info -->
    <h2 id="wri-info" class="text-lg font-semibold mt-6 mb-2">About the World Risk Index (WRI)</h2>
    <p class="mb-2">
      The WRI is published by the Bündnis Entwicklung Hilft and the Institute for International Law of Peace and Armed Conflict (IFHV) at Ruhr University Bochum.
      It measures the disaster risk for countries based on their exposure to natural hazards and their vulnerability,
      including susceptibility, coping capacity, and adaptive capacity.
      It is intended to support disaster risk reduction and climate adaptation planning.
    </p>
    <p class="mb-6">
      <a href="https://www.weltrisikobericht.de" target="_blank" class="text-blue-800 underline">Learn more about WRI at weltrisikobericht.de ↗</a>
    </p>

    <!-- Reflection -->
    <h2 class="text-lg font-semibold mt-6 mb-2">Reflections & Further Work</h2>
    <p class="mb-3">
      This project combines different global indicators to explore composite vulnerability profiles.
      It’s important to note that the <strong>Mercator projection</strong> distorts geographic proportions — for example, countries near the poles appear larger.
      A future update might explore alternative, more realistic map projections.
    </p>
    <p class="mb-3">
      This tool currently visualizes historical data, but in the future, one could apply <strong>machine learning models</strong> to make forecasts about militarisation or climate risk under different scenarios.
      These projections would remain speculative and rely on underlying assumptions and data quality.
    </p>
    <p class="mb-6">
      The combination logic here is based on <strong>normalized summation</strong>.
      There may be other meaningful ways to weight or cluster indicators, such as using principal component analysis or unsupervised clustering to identify country typologies.
      Exploring such directions could yield valuable insights.
    </p>
  </div>
</template>