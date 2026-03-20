<template>
  <Teleport to="body">
    <Transition name="sidebar">
      <div v-if="props.open" class="sidebar-backdrop" @click.self="emit('close')">
        <aside class="sidebar">
          <div class="sidebar-header">
            <h2 class="sidebar-title">我的最愛</h2>
            <button class="sidebar-close" @click="emit('close')" aria-label="關閉">&times;</button>
          </div>

          <div class="sidebar-body">
            <p v-if="store.favorites.length === 0" class="empty-text">
              還沒有收藏的餐廳，<br />從搜尋結果中加入最愛吧！
            </p>

            <ul v-else class="favorite-list">
              <li
                v-for="f in store.favorites"
                :key="f.id"
                class="favorite-item"
                @click="openRestaurant(f)"
              >
                <div class="favorite-photo">
                  <img v-if="f.photo_url" :src="f.photo_url" :alt="f.name" />
                  <div v-else class="favorite-photo-placeholder">餐</div>
                </div>
                <div class="favorite-info">
                  <div class="favorite-name">{{ f.name }}</div>
                  <div class="favorite-meta">
                    <span v-if="f.rating">★ {{ f.rating }}</span>
                    <span v-if="f.address" class="favorite-address">{{ f.address }}</span>
                  </div>
                </div>
                <button
                  class="favorite-remove"
                  @click.stop="store.removeFavorite(f.id)"
                  aria-label="移除"
                >
                  &times;
                </button>
              </li>
            </ul>
          </div>
        </aside>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { useRestaurantStore } from '@/stores/restaurant'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close'])
const store = useRestaurantStore()

function openRestaurant(f) {
  store.selectRestaurant(f, false)
  emit('close')
}
</script>

<style scoped>
.sidebar-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 200;
  display: flex;
  justify-content: flex-end;
}

.sidebar {
  width: 340px;
  max-width: 90vw;
  height: 100%;
  background: var(--card-bg);
  display: flex;
  flex-direction: column;
  box-shadow: -4px 0 24px rgba(0, 0, 0, 0.15);
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  height: 60px;
  background: var(--primary);
  color: #fff;
  flex-shrink: 0;
}

.sidebar-title {
  font-size: 17px;
  font-weight: 700;
}

.sidebar-close {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  font-size: 20px;
  cursor: pointer;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  transition: background 0.2s;
}

.sidebar-close:hover {
  background: rgba(255, 255, 255, 0.35);
}

.sidebar-body {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
}

.empty-text {
  text-align: center;
  color: var(--text-secondary);
  font-size: 14px;
  line-height: 1.8;
  margin-top: 40px;
}

.favorite-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.favorite-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-radius: var(--radius);
  background: var(--secondary);
  cursor: pointer;
  transition: background 0.15s;
}

.favorite-item:hover {
  background: #fde8d8;
}

.favorite-photo {
  width: 52px;
  height: 52px;
  border-radius: 8px;
  overflow: hidden;
  flex-shrink: 0;
}

.favorite-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.favorite-photo-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, var(--primary), #ff8c60);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 700;
  font-size: 16px;
}

.favorite-info {
  flex: 1;
  min-width: 0;
}

.favorite-name {
  font-size: 14px;
  font-weight: 700;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.favorite-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 3px;
  font-size: 12px;
  color: var(--text-secondary);
}

.favorite-address {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.favorite-remove {
  flex-shrink: 0;
  background: rgba(0, 0, 0, 0.08);
  border: none;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  font-size: 16px;
  cursor: pointer;
  color: var(--text-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  transition: background 0.2s, color 0.2s;
}

.favorite-remove:hover {
  background: #fee2e2;
  color: #e53e3e;
}

/* Transition */
.sidebar-enter-active,
.sidebar-leave-active {
  transition: opacity 0.2s ease;
}

.sidebar-enter-from,
.sidebar-leave-to {
  opacity: 0;
}

.sidebar-enter-active .sidebar,
.sidebar-leave-active .sidebar {
  transition: transform 0.25s ease;
}

.sidebar-enter-from .sidebar,
.sidebar-leave-to .sidebar {
  transform: translateX(100%);
}
</style>
