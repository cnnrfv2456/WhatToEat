import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api'

export const useRestaurantStore = defineStore('restaurant', () => {
  const selectedRestaurant = ref(null)
  const showDialog = ref(false)
  const history = ref([])
  const favorites = ref([])
  const reselectTrigger = ref(0)
  const canReselect = ref(true)

  async function fetchHistory() {
    const { data } = await api.get('/history')
    history.value = data
  }

  async function saveToHistory(restaurant) {
    const { data } = await api.post('/history', restaurant)
    history.value.unshift(data)
  }

  async function deleteHistory(id) {
    await api.delete(`/history/${id}`)
    history.value = history.value.filter((h) => h.id !== id)
  }

  async function fetchFavorites() {
    const { data } = await api.get('/favorites')
    favorites.value = data
  }

  async function addFavorite(restaurant) {
    const { data } = await api.post('/favorites', restaurant)
    if (!favorites.value.find((f) => f.place_id === data.place_id)) {
      favorites.value.unshift(data)
    }
  }

  async function removeFavorite(id) {
    await api.delete(`/favorites/${id}`)
    favorites.value = favorites.value.filter((f) => f.id !== id)
  }

  function isFavorite(place_id) {
    return favorites.value.some((f) => f.place_id === place_id)
  }

  function getFavoriteId(place_id) {
    return favorites.value.find((f) => f.place_id === place_id)?.id ?? null
  }

  function selectRestaurant(restaurant, reselectable = true) {
    selectedRestaurant.value = restaurant
    canReselect.value = reselectable
    showDialog.value = true
  }

  function closeDialog() {
    showDialog.value = false
  }

  function triggerReselect() {
    showDialog.value = false
    reselectTrigger.value++
  }

  return {
    selectedRestaurant,
    showDialog,
    canReselect,
    history,
    favorites,
    reselectTrigger,
    fetchHistory,
    saveToHistory,
    deleteHistory,
    fetchFavorites,
    addFavorite,
    removeFavorite,
    isFavorite,
    getFavoriteId,
    selectRestaurant,
    closeDialog,
    triggerReselect,
  }
})
