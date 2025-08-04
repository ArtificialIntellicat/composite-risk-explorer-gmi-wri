# 🌍 Composite Risk Explorer

**Visualizing Global Militarisation and Climate Disaster Risk**

Dieses Projekt ist eine interaktive Webanwendung zur Visualisierung des Global Militarisation Index (GMI) und des World Risk Index (WRI). Nutzer:innen können Indikatoren kombinieren, per Zeitslider erkunden und die Ergebnisse auf einer Weltkarte als Choroplethenkarte betrachten.

## 🧩 Features

- Interaktive Weltkarte (Leaflet)
- Auswahl von Indikatoren (GMI, WRI & Subindikatoren)
- Zeitslider für historische Daten (2000–2022)
- Dynamische Farbskalen & Popups
- Lesbare UI mit TailwindCSS
- Sauber strukturierter Code in Vue 3 + TypeScript
- API-Endpunkte über Laravel bereitgestellt

---

## 📦 Tech Stack

| Technologie      | Beschreibung                                  |
|------------------|-----------------------------------------------|
| **Vue 3**        | Frontend-Framework                            |
| **TypeScript**   | Typisierung und Codequalität                  |
| **Vite**         | Frontend-Bundler                              |
| **Laravel 10**   | PHP-Backend / API / Routing                   |
| **Leaflet.js**   | Kartenvisualisierung                          |
| **TailwindCSS**  | Styling                                       |
| **GeoJSON**      | Weltkarte mit ISO-Codes                       |
| **SQLite**       | Datenhaltung für Länder-Indikatoren           |

---

## 🚀 Quickstart

### 🖥 Voraussetzungen

- Node.js `>=18.x`
- PHP `>=8.1`
- Composer
- SQLite
- Git

---

### 🔧 Setup-Anleitung

```bash
# 1. Repository klonen
git clone https://github.com/dein-benutzername/composite-risk-explorer.git
cd composite-risk-explorer

# 2. Abhängigkeiten installieren
composer install
npm install

# 3. .env Datei einrichten
cp .env.example .env
php artisan key:generate

# 4. Datenbank einrichten
# -> Passe die .env an (DB_HOST, DB_DATABASE, ...)
php artisan migrate --seed

# 5. GeoJSON & Länderdaten importieren
# (Optional: eigenen Seeder schreiben)
php artisan import:countries

# 6. Dev-Server starten
npm run dev      # Startet Vite
php artisan serve
