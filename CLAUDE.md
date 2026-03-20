# CLAUDE.md

此檔案提供 Claude Code (claude.ai/code) 在此專案中的操作指引。

## 初始設定

將 `.env.example` 複製為 `.env` 並填入必要的值：
```
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
DB_ROOT_PASSWORD=
GOOGLE_MAPS_API_KEY=
```

## 常用指令

**啟動完整服務：**
```bash
docker-compose up --build
```
應用程式網址：http://localhost:8066

**單獨重建某個服務：**
```bash
docker-compose up --build backend
docker-compose up --build frontend
```

**在容器內執行 Laravel artisan 指令：**
```bash
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan route:list
docker-compose exec backend php artisan tinker
```

**執行 PHP 測試：**
```bash
docker-compose exec backend php artisan test
# 執行單一測試：
docker-compose exec backend php artisan test --filter=TestName
```
測試使用 SQLite `:memory:` 資料庫，並設定 `BCRYPT_ROUNDS=4` 加快速度（設定於 `phpunit.xml`）。

**安裝前端套件或執行 Vite 指令：**
```bash
docker-compose exec frontend npm install
docker-compose exec frontend npm run build
```

## 架構說明

### 請求流程
Nginx 在容器內監聽 port 80，對外綁定為 **8066**（`localhost:8066`），作為反向代理：
- `/api/*` → PHP-FPM `backend:9000`（Laravel 透過 `public/index.php` 處理）
- `/@vite/*` → Vite dev server `frontend:5173`（HMR WebSocket）
- `/*` → Vite dev server `frontend:5173`

### 後端（Laravel 12）
- **認證**：Sanctum token。登入／註冊後回傳 token 存於 `localStorage`，所有受保護路由需要 `Authorization: Bearer <token>`，token 永不過期（null expiration）。
- **Controller**：`AuthController`（register/login/logout/user）、`HistoryController`（歷史紀錄 CRUD）、`FavoriteController`（我的最愛 CRUD；以 `firstOrCreate` 防止 `user_id + place_id` 重複）。`store` 端點回傳 201；刪除回傳 204。
- **Model**：`User` hasMany `History` 和 `Favorite`。兩張表皆冗餘儲存餐廳欄位（非正規化）：`place_id`、`name`、`address`、`phone`、`price_level`、`rating`、`user_ratings_total`、`photo_url`、`lat`、`lng`。History 額外有 `distance decimal(10,2)`；Favorites 無此欄位。
- **最愛唯一約束**：Migration 中對 `(user_id, place_id)` 建立 DB 層級唯一索引，Controller 也使用 `firstOrCreate()`。
- **Entrypoint**：`backend/entrypoint.sh` 在**每次**容器啟動時執行 `key:generate --force` 與 `migrate --force`（冪等操作，不會重複套用）。

### 前端（Vue 3 + Vite）
- **Store**（`src/stores/`）：`auth.js` 管理 token 與使用者狀態（localStorage 持久化）；`restaurant.js` 管理已選餐廳、dialog 狀態、歷史紀錄、我的最愛、`reselectTrigger` 計數器，以及 `canReselect` flag。
- **`canReselect`**：`selectRestaurant(restaurant, reselectable)` 的第二參數，控制 `RestaurantDialog` 是否顯示「重新挑選」按鈕。從搜尋結果開啟時為 `true`；從歷史紀錄或我的最愛開啟時為 `false`。
- **`reselectTrigger`**：透過 `triggerReselect()` 遞增此 ref，讓 `HomeView` 以上次的篩選條件重新執行 `onSearch`。
- **`GoogleMap.vue`**：透過 `@googlemaps/js-api-loader` 載入 Google Maps（libraries: `places`、`geometry`）。對外暴露兩個方法：
  - `searchNearby(userLocation, filters)`：呼叫 Places `nearbySearch` API，客戶端套用評分／評論數篩選，用 Geometry library 計算距離，回傳處理後的結果陣列。
  - `getDetails(place_id)`：呼叫 Places `getDetails` API 取得 `formatted_phone_number`，選定餐廳後由 `HomeView` 呼叫以獲取電話號碼。
- **`HomeView.vue`**：主流程協調者——從 `GoogleMap` 的 `@location-changed` 事件取得位置、呼叫 `searchNearby`、隨機挑選結果、呼叫 `getDetails` 取得電話、存入歷史紀錄、開啟 `RestaurantDialog`。
- **`RestaurantDialog.vue`**：顯示餐廳完整資訊（照片、名稱、評分、評論數、價位、距離、地址、電話）。提供四個操作按鈕：「重新挑選」（`canReselect` 為 true 時才顯示）、加入最愛／移除收藏、分享連結（複製 Google Maps URL 至剪貼簿）、Google Maps 連結。
- **`HistoryList.vue`**：分頁顯示歷史紀錄（每頁 10 筆）。點擊卡片開啟 `RestaurantDialog`；刪除使用自訂彈窗確認，不使用瀏覽器原生 `confirm()`。
- **`FavoritesSidebar.vue`**：側邊抽屜顯示我的最愛清單。點擊項目開啟 `RestaurantDialog`；每個項目可直接移除。
- **API client**（`src/api/index.js`）：Axios instance，base URL 為 `/api`，每個請求自動附加 Bearer token。收到 401 時清除 localStorage token 並強制跳轉至 `/login`。
- **Router**（`src/router/index.js`）：`/` 需要登入；`/login` 與 `/register` 為 guest-only（已登入則跳轉至 `/`）；`/:pathMatch(.*)*` catch-all 導向 `/`。
- **Vite HMR**：設定 `hmr.clientPort: 80`，讓 HMR WebSocket 流量透過 nginx 而非直連 port 5173。

### 價位標籤對應
| price_level | 標籤 |
|-------------|------|
| 1 | 平價（NT$100以下） |
| 2 | 中等（NT$100–300） |
| 3 | 偏貴（NT$300–600） |
| 4 | 高級（NT$600以上） |

### Docker Volumes
- `laravel_app`：nginx 與 backend 共用的具名 volume，用於服務 PHP 檔案。
- `db_data`：MySQL 資料持久化，容器重啟不遺失。
- `./backend` bind-mount 至容器 `/var/www`，`/var/www/vendor` 為匿名 volume 保留容器建立的依賴。
- 前端 `src/` 與 `index.html` bind-mount 以支援開發期間的 hot reload。
