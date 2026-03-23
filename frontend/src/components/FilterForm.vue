<template>
  <div class="filter-form">
    <div class="filter-grid">
      <div class="form-group">
        <label>搜尋距離（公尺）<span class="required">*</span></label>
        <input
          v-model.number="filters.distance"
          type="number"
          min="100"
          max="50000"
          step="100"
          placeholder="1000"
          required
        />
      </div>

      <div class="form-group">
        <label>價格區間（元）</label>
        <div class="input-row">
          <input
            v-model.number="filters.priceMin"
            type="number"
            min="0"
            step="50"
            placeholder="最低"
          />
          <span class="range-sep">～</span>
          <input
            v-model.number="filters.priceMax"
            type="number"
            min="0"
            step="50"
            placeholder="最高"
          />
        </div>
      </div>

      <div class="form-group">
        <label>評等區間（1 ～ 5 星）</label>
        <div class="input-row">
          <input
            v-model.number="filters.ratingMin"
            type="number"
            min="1"
            max="5"
            step="0.1"
            placeholder="最低"
          />
          <span class="range-sep">～</span>
          <input
            v-model.number="filters.ratingMax"
            type="number"
            min="1"
            max="5"
            step="0.1"
            placeholder="最高"
          />
        </div>
      </div>

      <div class="form-group">
        <label>最少評論數</label>
        <input
          v-model.number="filters.minReviews"
          type="number"
          min="0"
          placeholder="不限"
        />
      </div>
    </div>

    <button class="btn-search" :disabled="props.loading" @click="handleSearch">
      <span v-if="props.loading">搜尋中...</span>
      <span v-else>幫我選一家！</span>
    </button>
  </div>

  <AlertDialog ref="alertRef" />
</template>

<script setup>
import { ref, reactive } from 'vue'
import AlertDialog from '@/components/AlertDialog.vue'

const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['search'])

const alertRef = ref(null)

const filters = reactive({
  distance: 1000,
  priceMin: null,
  priceMax: null,
  ratingMin: null,
  ratingMax: null,
  minReviews: null,
})

function handleSearch() {
  if (!filters.distance || filters.distance < 100) {
    alertRef.value.show('請輸入搜尋距離（至少 100 公尺）')
    return
  }
  emit('search', { ...filters })
}
</script>

<style scoped>
.filter-form {
  padding: 16px 24px 24px;
}

.filter-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 24px;
}

@media (max-width: 600px) {
  .filter-grid {
    grid-template-columns: 1fr;
  }
}

.required {
  color: var(--primary);
  margin-left: 2px;
}

.input-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.input-row input,
.input-row select {
  flex: 1;
}

.range-sep {
  color: var(--text-secondary);
  flex-shrink: 0;
  font-size: 14px;
}

.btn-search {
  width: 100%;
  padding: 16px;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: var(--radius);
  font-size: 18px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s, transform 0.1s;
  letter-spacing: 1px;
}

.btn-search:hover:not(:disabled) {
  background: var(--primary-hover);
  transform: translateY(-1px);
}

.btn-search:active:not(:disabled) {
  transform: translateY(0);
}

.btn-search:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
