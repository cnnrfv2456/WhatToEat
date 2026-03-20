<template>
  <div class="history-list">
    <div v-if="store.history.length === 0" class="history-empty">
      <p>還沒有選餐紀錄，趕快試試看吧！</p>
    </div>

    <div v-else class="history-grid">
      <div v-for="item in pagedHistory" :key="item.id" class="history-card" @click="store.selectRestaurant(item, false)">
        <div class="history-photo">
          <img v-if="item.photo_url" :src="item.photo_url" :alt="item.name" />
          <div v-else class="history-photo-placeholder">餐</div>
        </div>

        <div class="history-info">
          <h3 class="history-name">{{ item.name }}</h3>
          <div class="history-meta">
            <span v-if="item.rating" class="meta-item">★ {{ item.rating }}</span>
            <span v-if="item.price_level" class="meta-item">
              {{ priceLabel(item.price_level) }}
            </span>
          </div>
          <p v-if="item.address" class="history-address">{{ item.address }}</p>
          <time class="history-date">{{ formatDate(item.created_at) }}</time>
        </div>

        <button class="btn-delete" @click.stop="pendingDelete = item" title="刪除紀錄">
          &#x2715;
        </button>
      </div>
    </div>

    <div v-if="totalPages > 1" class="pagination">
      <button :disabled="currentPage === 1" @click="currentPage--">&#x2039;</button>
      <span>{{ currentPage }} / {{ totalPages }}</span>
      <button :disabled="currentPage === totalPages" @click="currentPage++">&#x203a;</button>
    </div>
  </div>

  <!-- 刪除確認彈窗 -->
  <Teleport to="body">
    <Transition name="confirm">
      <div v-if="pendingDelete" class="confirm-backdrop" @click.self="pendingDelete = null">
        <div class="confirm-dialog">
          <p class="confirm-msg">確定要刪除</p>
          <p class="confirm-name">「{{ pendingDelete.name }}」</p>
          <p class="confirm-sub">的紀錄嗎？</p>
          <div class="confirm-actions">
            <button class="btn-cancel" @click="pendingDelete = null">取消</button>
            <button class="btn-confirm" @click="doDelete">刪除</button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRestaurantStore } from '@/stores/restaurant'

const store = useRestaurantStore()

const PER_PAGE = 10
const currentPage = ref(1)
const pendingDelete = ref(null)

const totalPages = computed(() => Math.ceil(store.history.length / PER_PAGE))

const pagedHistory = computed(() => {
  const start = (currentPage.value - 1) * PER_PAGE
  return store.history.slice(start, start + PER_PAGE)
})

watch(() => store.history.length, () => { currentPage.value = 1 })

const PRICE_LABELS = [
  '平價（NT$100以下）',
  '中等（NT$100–300）',
  '偏貴（NT$300–600）',
  '高級（NT$600以上）',
]

function priceLabel(level) {
  return PRICE_LABELS[level - 1] ?? '$'.repeat(level)
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

async function doDelete() {
  if (!pendingDelete.value) return
  await store.deleteHistory(pendingDelete.value.id)
  pendingDelete.value = null
}
</script>

<style scoped>
.history-list {
  padding: 16px 24px 24px;
}

.history-empty {
  text-align: center;
  padding: 48px 0;
  color: var(--text-secondary);
  font-size: 15px;
}

.history-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 8px;
}

.history-card {
  display: flex;
  gap: 14px;
  align-items: flex-start;
  padding: 14px;
  border: 1px solid var(--border);
  border-radius: 12px;
  transition: box-shadow 0.2s;
  position: relative;
  cursor: pointer;
}

.history-card:hover {
  box-shadow: var(--shadow);
}

.history-photo {
  width: 70px;
  height: 70px;
  border-radius: 10px;
  overflow: hidden;
  flex-shrink: 0;
}

.history-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.history-photo-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, var(--secondary), #fde8d8);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  font-weight: 700;
  color: var(--primary);
}

.history-info {
  flex: 1;
  min-width: 0;
}

.history-name {
  font-size: 15px;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.history-meta {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 4px;
}

.meta-item {
  font-size: 12px;
  color: var(--primary);
  font-weight: 600;
  background: #fff3ef;
  padding: 2px 8px;
  border-radius: 10px;
}

.history-address {
  font-size: 12px;
  color: var(--text-secondary);
  margin-bottom: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.history-date {
  font-size: 11px;
  color: #aaa;
}

.btn-delete {
  position: absolute;
  top: 10px;
  right: 10px;
  background: none;
  border: none;
  color: #ccc;
  font-size: 16px;
  cursor: pointer;
  padding: 4px;
  line-height: 1;
  transition: color 0.2s;
}

.btn-delete:hover {
  color: #ef4444;
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-top: 16px;
  font-size: 14px;
  color: var(--text-secondary);
}

.pagination button {
  background: none;
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 4px 12px;
  cursor: pointer;
  font-size: 18px;
  line-height: 1;
  color: var(--text);
  transition: background 0.2s;
}

.pagination button:disabled {
  opacity: 0.3;
  cursor: default;
}

.pagination button:not(:disabled):hover {
  background: var(--secondary);
}

/* 確認彈窗 */
.confirm-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 16px;
}

.confirm-dialog {
  background: var(--card-bg);
  border-radius: 16px;
  padding: 28px 28px 20px;
  width: 100%;
  max-width: 320px;
  text-align: center;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
}

.confirm-msg {
  font-size: 15px;
  color: var(--text-secondary);
  margin-bottom: 4px;
}

.confirm-name {
  font-size: 17px;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 4px;
}

.confirm-sub {
  font-size: 15px;
  color: var(--text-secondary);
  margin-bottom: 24px;
}

.confirm-actions {
  display: flex;
  gap: 10px;
}

.btn-cancel {
  flex: 1;
  padding: 10px;
  background: var(--secondary);
  color: var(--text);
  border: 1px solid var(--border);
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-cancel:hover {
  background: #fde8d8;
}

.btn-confirm {
  flex: 1;
  padding: 10px;
  background: #ef4444;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-confirm:hover {
  background: #dc2626;
}

/* Transition */
.confirm-enter-active,
.confirm-leave-active {
  transition: opacity 0.2s ease;
}

.confirm-enter-from,
.confirm-leave-to {
  opacity: 0;
}

.confirm-enter-active .confirm-dialog,
.confirm-leave-active .confirm-dialog {
  transition: transform 0.2s ease;
}

.confirm-enter-from .confirm-dialog,
.confirm-leave-to .confirm-dialog {
  transform: scale(0.93) translateY(12px);
}

@media (max-width: 480px) {
  .history-list {
    padding: 12px 14px 16px;
  }

  .history-card {
    gap: 10px;
    padding: 10px;
  }

  .history-photo {
    width: 56px;
    height: 56px;
  }

  .history-name {
    font-size: 14px;
  }
}
</style>
