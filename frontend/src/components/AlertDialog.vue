<template>
  <Teleport to="body">
    <Transition name="confirm">
      <div v-if="visible" class="confirm-backdrop" @click.self="visible = false">
        <div class="confirm-dialog">
          <p class="confirm-msg">{{ msgText }}</p>
          <div class="confirm-actions">
            <button class="btn-confirm" @click="visible = false">確認</button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'

const visible = ref(false)
const msgText = ref('')

function show(message) {
  msgText.value = message
  visible.value = true
}

defineExpose({ show })
</script>

<style scoped>
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
  color: var(--text);
  margin-bottom: 24px;
  line-height: 1.6;
}

.confirm-actions {
  display: flex;
}

.btn-confirm {
  flex: 1;
  padding: 10px;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-confirm:hover {
  background: var(--primary-hover);
}

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
</style>
