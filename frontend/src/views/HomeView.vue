<template>
  <div class="home-page">
    <header class="header">
      <div class="header-brand">🍜 What to eat</div>
      <div class="header-user">
        <span class="user-name">{{ auth.user?.name }}</span>
        <button class="btn-favorites" @click="showFavorites = true">
          ♥ 最愛
          <span v-if="restaurant.favorites.length > 0" class="favorites-count">
            {{ restaurant.favorites.length }}
          </span>
        </button>
        <button @click="auth.logout" class="btn-logout">登出</button>
      </div>
    </header>

    <main class="main-content">
      <section class="section">
        <GoogleMap ref="mapRef" @location-changed="onLocationChanged" />
      </section>

      <section class="section">
        <h2 class="section-title">篩選條件</h2>
        <FilterForm :loading="searching" @search="onSearch" />
      </section>

      <section class="section">
        <h2 class="section-title">選中歷史紀錄</h2>
        <HistoryList />
      </section>
    </main>

    <RestaurantDialog />
    <FavoritesSidebar :open="showFavorites" @close="showFavorites = false" />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRestaurantStore } from '@/stores/restaurant'
import GoogleMap from '@/components/GoogleMap.vue'
import FilterForm from '@/components/FilterForm.vue'
import RestaurantDialog from '@/components/RestaurantDialog.vue'
import HistoryList from '@/components/HistoryList.vue'
import FavoritesSidebar from '@/components/FavoritesSidebar.vue'

const auth = useAuthStore()
const restaurant = useRestaurantStore()

const mapRef = ref(null)
const userLocation = ref(null)
const searching = ref(false)
const lastFilters = ref(null)
const showFavorites = ref(false)

onMounted(async () => {
  await auth.fetchUser()
  await Promise.all([restaurant.fetchHistory(), restaurant.fetchFavorites()])
})

function onLocationChanged(location) {
  userLocation.value = location
}

async function onSearch(filters) {
  if (!userLocation.value) {
    alert('請先允許位置存取權限，並等待地圖定位完成')
    return
  }

  searching.value = true
  lastFilters.value = filters

  try {
    const results = await mapRef.value.searchNearby(userLocation.value, filters)

    if (results.length === 0) {
      alert('找不到符合條件的餐廳，請調整篩選條件')
      return
    }

    const selected = results[Math.floor(Math.random() * results.length)]
    const phone = await mapRef.value.getDetails(selected.place_id)

    const restaurantData = {
      place_id: selected.place_id,
      name: selected.name,
      address: selected.vicinity || null,
      phone,
      price_level: selected.price_level ?? null,
      rating: selected.rating ?? null,
      user_ratings_total: selected.user_ratings_total ?? null,
      distance: selected.distance,
      photo_url: selected.photo_url || null,
      lat: selected.lat,
      lng: selected.lng,
    }

    restaurant.selectRestaurant(restaurantData)
    await restaurant.saveToHistory(restaurantData)
  } catch (err) {
    console.error(err)
    alert('搜尋失敗，請稍後再試')
  } finally {
    searching.value = false
  }
}

watch(
  () => restaurant.reselectTrigger,
  async (val) => {
    if (val > 0 && lastFilters.value) {
      await onSearch(lastFilters.value)
    }
  }
)
</script>

<style scoped>
.home-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.header {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 0 32px;
  height: 72px;
  background: var(--primary);
  color: #fff;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.header-brand {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  font-size: 34px;
  font-weight: 800;
  letter-spacing: 1px;
  display: flex;
  align-items: center;
  gap: 8px;
  white-space: nowrap;
  pointer-events: none;
}

.header-user {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-name {
  font-size: 14px;
  opacity: 0.9;
}

.btn-favorites {
  position: relative;
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.4);
  padding: 6px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  transition: background 0.2s;
}

.btn-favorites:hover {
  background: rgba(255, 255, 255, 0.35);
}

.favorites-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #e53e3e;
  color: #fff;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 700;
  min-width: 18px;
  height: 18px;
  padding: 0 4px;
  margin-left: 4px;
  vertical-align: middle;
}

.btn-logout {
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.4);
  padding: 6px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  transition: background 0.2s;
}

.btn-logout:hover {
  background: rgba(255, 255, 255, 0.35);
}

.main-content {
  flex: 1;
  max-width: 900px;
  width: 100%;
  margin: 0 auto;
  padding: 24px 16px 48px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.section {
  background: var(--card-bg);
  border-radius: var(--radius);
  overflow: hidden;
  box-shadow: var(--shadow);
}

.section-title {
  font-size: 18px;
  font-weight: 700;
  color: var(--text);
  padding: 20px 24px 0;
}

@media (max-width: 480px) {
  .header {
    padding: 0 12px;
  }

  .header-brand {
    position: static;
    transform: none;
    font-size: 16px;
    flex: 1;
  }

  .header-user {
    gap: 8px;
  }

  .user-name {
    display: none;
  }

  .header {
    padding: 0 14px;
    height: 60px;
  }

  .btn-favorites,
  .btn-logout {
    padding: 6px 10px;
    font-size: 12px;
  }

  .main-content {
    padding: 16px 10px 32px;
    gap: 16px;
  }

  .section-title {
    font-size: 16px;
    padding: 16px 16px 0;
  }
}
</style>
