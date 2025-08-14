<template>
  <!-- Title -->
  <h1 class="text-2xl font-bold text-center mb-3 text-gray-800">
    Composite Risk Explorer: Global Militarisation & Climate Hazards
  </h1>

  <!-- Intro blurb -->
  <p class="text-center text-gray-800 text-sm mb-6">
    This interactive map allows you to combine indicators from the
    <strong>Global Militarisation Index (GMI)</strong> and the
    <strong>World Risk Index (WRI)</strong> by country and year. <br />
    You can either select one composite index or freely combine individual indicators to explore patterns of militarisation and climate disaster vulnerability. Choose your indicators and a year to see the map update in real time.
    <br />
    <span class="text-amber-800 font-medium">Observed data</span> are shown for <strong>2000–2022</strong>.
    For <strong>2023–2050</strong>, the map displays <span class="text-blue-800 font-medium">model-based forecasts</span>
    built per country &amp; per indicator. Forecasts are produced offline and shipped as a static JSON file; see
    <a href="#forecasts" class="text-blue-800 underline">Forecasts</a>.
  </p>
</template>