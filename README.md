# 🍜 What to eat

告別選擇困難，讓 What to eat 幫你決定今天吃什麼。

根據 GPS 定位搜尋附近餐廳，設定篩選條件後隨機挑選一家，支援歷史紀錄與我的最愛管理。

---

## 功能特色

- 📍 GPS 定位，搜尋附近餐廳
- 🎲 隨機挑選符合條件的餐廳
- 🔍 篩選條件：距離、價格區間、評分區間、最少評論數
- 📋 歷史紀錄：查看過去選過的餐廳、點擊查看詳情
- ❤️ 我的最愛：收藏喜歡的餐廳
- 📞 餐廳電話、地址、距離完整資訊
- 🔗 分享連結（複製 Google Maps URL）
- 📱 RWD 響應式設計

---

## 啟動步驟

### 1. 準備環境變數

```bash
cp .env.example .env
```

編輯 `.env`，填入必要資訊：

```
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
DB_ROOT_PASSWORD=
GOOGLE_MAPS_API_KEY=
```

### 2. 取得 Google Maps API Key

前往 [Google Cloud Console](https://console.cloud.google.com/) 啟用以下 API：

- Maps JavaScript API
- Places API
- Geometry Library（包含在 Maps JavaScript API 中）

### 3. 啟動服務

```bash
docker-compose up --build
```

首次啟動會自動：
- 安裝 Laravel 12 與 Sanctum
- 執行資料庫 migration
- 啟動 Vue 開發伺服器

### 4. 開啟瀏覽器

```
http://localhost:8066
```

---

## 專案架構

```
whatToEat/
├── docker-compose.yml
├── .env.example
├── nginx/
│   └── default.conf              # Nginx 反向代理設定
├── backend/                      # Laravel 12
│   ├── Dockerfile
│   ├── entrypoint.sh
│   ├── routes/api.php
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── HistoryController.php
│   │   │   └── FavoriteController.php
│   │   └── Models/
│   │       ├── User.php
│   │       ├── History.php
│   │       └── Favorite.php
│   └── database/migrations/
└── frontend/                     # Vue 3 + Vite
    ├── Dockerfile
    ├── src/
    │   ├── views/
    │   │   ├── LoginView.vue
    │   │   ├── RegisterView.vue
    │   │   └── HomeView.vue
    │   ├── components/
    │   │   ├── GoogleMap.vue       # 地圖、nearbySearch、getDetails
    │   │   ├── FilterForm.vue      # 篩選條件表單
    │   │   ├── RestaurantDialog.vue # 餐廳詳情 dialog
    │   │   ├── HistoryList.vue     # 歷史紀錄列表
    │   │   └── FavoritesSidebar.vue # 我的最愛側邊欄
    │   ├── stores/
    │   │   ├── auth.js
    │   │   └── restaurant.js
    │   └── api/index.js
    └── ...
```

---

## API 端點

| Method | Path | 說明 |
|--------|------|------|
| POST | /api/auth/register | 註冊 |
| POST | /api/auth/login | 登入 |
| POST | /api/auth/logout | 登出（需登入） |
| GET | /api/user | 取得目前使用者（需登入） |
| GET | /api/history | 取得歷史紀錄（需登入） |
| POST | /api/history | 新增歷史紀錄（需登入） |
| DELETE | /api/history/{id} | 刪除歷史紀錄（需登入） |
| GET | /api/favorites | 取得我的最愛（需登入） |
| POST | /api/favorites | 新增我的最愛（需登入） |
| DELETE | /api/favorites/{id} | 移除我的最愛（需登入） |

---

## 技術棧

| 層級 | 技術 |
|------|------|
| 前端 | Vue 3、Vite、Pinia、Vue Router、Axios |
| 後端 | PHP 8.2、Laravel 12、Laravel Sanctum |
| 資料庫 | MySQL 8.0 |
| 地圖 | Google Maps JavaScript API（Places + Geometry） |
| 基礎設施 | Docker、docker-compose、Nginx |
