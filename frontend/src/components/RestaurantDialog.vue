<template>
  <Teleport to="body">
    <Transition name="dialog">
      <div
        v-if="store.showDialog"
        class="dialog-backdrop"
        @click.self="store.closeDialog"
      >
        <div class="dialog" role="dialog" aria-modal="true">
          <button class="dialog-close" @click="store.closeDialog" aria-label="關閉">
            &times;
          </button>

          <template v-if="r">
            <div class="dialog-photo">
              <img v-if="r.photo_url" :src="r.photo_url" :alt="r.name" />
              <div v-else class="dialog-photo-placeholder">
                <span>餐廳</span>
              </div>
            </div>

            <div class="dialog-body">
              <h2 class="restaurant-name">{{ r.name }}</h2>

              <div class="badge-row">
                <span v-if="r.rating" class="badge badge-rating">
                  ★ {{ r.rating }}
                </span>
                <span v-if="r.user_ratings_total" class="badge badge-reviews">
                  {{ r.user_ratings_total.toLocaleString() }} 則評論
                </span>
                <span v-if="r.price_level" class="badge badge-price">
                  {{ '$'.repeat(r.price_level) }}
                </span>
              </div>

              <ul class="info-list">
                <li v-if="r.distance">
                  <span class="info-icon">📍</span>
                  距離 {{ r.distance }} 公尺
                </li>
                <li v-if="r.address">
                  <span class="info-icon">🏠</span>
                  {{ r.address }}
                </li>
                <li v-if="r.phone">
                  <span class="info-icon">📞</span>
                  {{ r.phone }}
                </li>
              </ul>
            </div>

            <div class="dialog-actions">
              <button v-if="store.canReselect" class="btn-reselect" @click="store.triggerReselect">
                重新挑選
              </button>
              <button
                class="btn-favorite"
                :class="{ 'is-favorited': favorited }"
                @click="toggleFavorite"
              >
                {{ favorited ? '移除收藏' : '加入最愛' }}
              </button>
              <button class="btn-share" :class="{ copied }" @click="share">
                {{ copied ? '已複製！' : '分享連結' }}
              </button>
              <a
                class="btn-maps"
                :href="`https://www.google.com/maps/place/?q=place_id:${r.place_id}`"
                target="_blank"
                rel="noopener noreferrer"
              >
                Google Maps
              </a>
            </div>
          </template>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRestaurantStore } from '@/stores/restaurant'

const store = useRestaurantStore()
const r = computed(() => store.selectedRestaurant)
const favorited = computed(() => r.value ? store.isFavorite(r.value.place_id) : false)
const copied = ref(false)

async function share() {
  const url = `https://www.google.com/maps/place/?q=place_id:${r.value.place_id}`
  await navigator.clipboard.writeText(url)
  copied.value = true
  setTimeout(() => { copied.value = false }, 2000)
}

async function toggleFavorite() {
  if (!r.value) return
  if (favorited.value) {
    const id = store.getFavoriteId(r.value.place_id)
    if (id) await store.removeFavorite(id)
  } else {
    await store.addFavorite(r.value)
  }
}
</script>

<style scoped>
.dialog-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 16px;
}

.dialog {
  background: var(--card-bg);
  border-radius: 20px;
  width: 100%;
  max-width: 480px;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
}

.dialog-close {
  position: absolute;
  top: 12px;
  right: 16px;
  background: rgba(0, 0, 0, 0.1);
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  font-size: 20px;
  cursor: pointer;
  color: var(--text);
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  z-index: 1;
  transition: background 0.2s;
}

.dialog-close:hover {
  background: rgba(0, 0, 0, 0.2);
}

.dialog-photo {
  width: 100%;
  height: 220px;
  border-radius: 20px 20px 0 0;
  overflow: hidden;
}

.dialog-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.dialog-photo-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, var(--secondary), #fde8d8);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  color: var(--primary);
  font-weight: 700;
}

.dialog-body {
  padding: 20px 24px 8px;
}

.restaurant-name {
  font-size: 22px;
  font-weight: 800;
  color: var(--text);
  margin-bottom: 12px;
  line-height: 1.3;
}

.badge-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 16px;
}

.badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
}

.badge-rating {
  background: #fef3c7;
  color: #d97706;
}

.badge-reviews {
  background: #e0f2fe;
  color: #0284c7;
}

.badge-price {
  background: #dcfce7;
  color: #16a34a;
}

.info-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 4px;
}

.info-list li {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  font-size: 14px;
  color: var(--text-secondary);
  line-height: 1.5;
}

.info-icon {
  flex-shrink: 0;
}

.info-list a {
  color: var(--primary);
  text-decoration: none;
}

.dialog-actions {
  display: flex;
  gap: 8px;
  padding: 16px 24px 24px;
  flex-wrap: wrap;
}

.btn-reselect {
  flex: 1;
  padding: 12px;
  background: var(--secondary);
  color: var(--primary);
  border: 2px solid var(--primary);
  border-radius: var(--radius);
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-reselect:hover {
  background: #fde8d8;
}

.btn-favorite {
  flex: 1;
  padding: 12px;
  background: #fff0f0;
  color: #e53e3e;
  border: 2px solid #e53e3e;
  border-radius: var(--radius);
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
}

.btn-favorite:hover {
  background: #fee2e2;
}

.btn-favorite.is-favorited {
  background: #e53e3e;
  color: #fff;
}

.btn-favorite.is-favorited:hover {
  background: #c53030;
}

.btn-share {
  flex: 1;
  padding: 12px;
  background: #f0fdf4;
  color: #16a34a;
  border: 2px solid #16a34a;
  border-radius: var(--radius);
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
}

.btn-share:hover {
  background: #dcfce7;
}

.btn-share.copied {
  background: #16a34a;
  color: #fff;
}

.btn-maps {
  flex: 1;
  padding: 12px;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: var(--radius);
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  text-align: center;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-maps:hover {
  background: var(--primary-hover);
}

@media (max-width: 480px) {
  .dialog-photo {
    height: 170px;
  }

  .dialog-body {
    padding: 16px 16px 6px;
  }

  .restaurant-name {
    font-size: 18px;
  }

  .dialog-actions {
    flex-direction: column;
    padding: 12px 16px 20px;
  }

  .btn-reselect,
  .btn-favorite,
  .btn-share,
  .btn-maps {
    flex: none;
    width: 100%;
  }
}

/* Transition */
.dialog-enter-active,
.dialog-leave-active {
  transition: opacity 0.2s ease;
}

.dialog-enter-from,
.dialog-leave-to {
  opacity: 0;
}

.dialog-enter-active .dialog,
.dialog-leave-active .dialog {
  transition: transform 0.2s ease;
}

.dialog-enter-from .dialog,
.dialog-leave-to .dialog {
  transform: scale(0.95) translateY(20px);
}
</style>
