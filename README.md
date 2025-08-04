# 🌍 Composite Risk Explorer

**Visualizing Global Militarisation and Climate Disaster Risk**

This project is an interactive web application for visualizing the **Global Militarisation Index (GMI)** and the **World Risk Index (WRI)**. Users can explore and combine various indicators over time and view the results on a global choropleth map.

---

## 🧩 Features

- Interactive world map with choropleth visualization
- Combine GMI and WRI indicators by country and year
- Time slider for historical data (2000–2022)
- Dynamic color scales & country popups
- Clean UI using TailwindCSS
- Vue 3 + TypeScript frontend
- Laravel 10 backend with MySQL database

---

## 📦 Tech Stack

| Technology        | Description                            |
|-------------------|----------------------------------------|
| **Vue 3**         | Frontend framework                     |
| **TypeScript**    | Typing & maintainable code             |
| **Vite**          | Modern frontend bundler                |
| **Laravel 10**    | PHP backend / API / routing            |
| **Leaflet.js**    | Map visualization                      |
| **TailwindCSS**   | Utility-first CSS framework            |
| **MySQL**         | Production database                    |
| **SQLite**        | Optional for local development         |

---

## 🚀 Quickstart

### 🖥 Requirements

- Node.js `>=18.x`
- PHP `>=8.1`
- Composer
- Git
- MySQL (or SQLite for local dev)

---

### 🔧 Setup Guide

```bash
# 1. Clone the repository
git clone https://github.com/ArtificialIntellicat/composite-risk-explorer-gmi-wri.git
cd composite-risk-explorer-gmi-wri

# 2. Install dependencies
composer install
npm install

# 3. Create the environment config
cp .env.example .env.production
php artisan key:generate

# 4. Configure the database
# -> Edit .env.production for DB_CONNECTION, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 5. Run migrations & seed the database
php artisan migrate --seed

# 6. Start the development servers
npm run dev         # starts Vite
php artisan serve   # starts Laravel backend
```

---

## 🗂 Data Sources

- **Global Militarisation Index (GMI)**  
  https://gmi.bicc.de/ranking-table  
  © Bonn International Center for Conversion (BICC)

- **World Risk Index (WRI)**  
  https://www.weltrisikobericht.de  
  © Bündnis Entwicklung Hilft & Ruhr-Universität Bochum

Raw data is expected in `storage/app/data/`, such as:

- `GMI-2023-all-years.xlsx`
- `worldriskindex/` (CSV files per year, e.g. `worldriskindex-2022.csv`)

Data is imported via Laravel artisan seeders.

---

## 📜 License

This project is licensed under the [MIT License](LICENSE).  
You are free to use, modify, and distribute it with attribution.

---

## ✨ Author: [@ArtificialIntellicat](https://github.com/ArtificialIntellicat)

If you like this project, feel free to leave a ⭐ on GitHub!