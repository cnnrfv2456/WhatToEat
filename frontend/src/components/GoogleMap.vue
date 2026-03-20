<template>
  <div class="map-wrapper">
    <div ref="mapEl" class="map-el"></div>
    <div v-if="!locationGranted" class="map-overlay">
      <div class="map-overlay-content">
        <p>需要你的位置才能搜尋附近餐廳</p>
        <button class="btn-primary" @click="requestLocation">允許定位</button>
      </div>
    </div>
    <div v-if="locating" class="map-status">定位中...</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Loader } from '@googlemaps/js-api-loader'

const emit = defineEmits(['location-changed'])

const mapEl = ref(null)
const locationGranted = ref(false)
const locating = ref(false)

let googleInstance = null
let map = null
let placesService = null
let userMarker = null
let restaurantMarkers = []

const loader = new Loader({
  apiKey: import.meta.env.VITE_GOOGLE_MAPS_API_KEY || '',
  version: 'weekly',
  libraries: ['places', 'geometry'],
})

onMounted(async () => {
  try {
    googleInstance = await loader.load()
    initMap()
    requestLocation()
  } catch (err) {
    console.error('Google Maps 載入失敗，請確認 API Key 設定', err)
  }
})

function initMap() {
  map = new googleInstance.maps.Map(mapEl.value, {
    center: { lat: 25.033, lng: 121.565 },
    zoom: 15,
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: false,
  })
  placesService = new googleInstance.maps.places.PlacesService(map)
}

function requestLocation() {
  if (!navigator.geolocation) return
  locating.value = true
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      const location = { lat: pos.coords.latitude, lng: pos.coords.longitude }
      locationGranted.value = true
      locating.value = false
      map.setCenter(location)
      map.setZoom(15)
      placeUserMarker(location)
      emit('location-changed', location)
    },
    () => {
      locating.value = false
      locationGranted.value = false
    }
  )
}

function placeUserMarker(location) {
  if (userMarker) userMarker.setMap(null)
  userMarker = new googleInstance.maps.Marker({
    position: location,
    map,
    title: '你在這裡',
    icon: {
      url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
    },
    zIndex: 999,
  })
}

function clearRestaurantMarkers() {
  restaurantMarkers.forEach((m) => m.setMap(null))
  restaurantMarkers = []
}

function placeRestaurantMarkers(results) {
  clearRestaurantMarkers()
  results.forEach((r) => {
    const marker = new googleInstance.maps.Marker({
      position: { lat: r.lat, lng: r.lng },
      map,
      title: r.name,
    })
    restaurantMarkers.push(marker)
  })
}

function amountToPriceLevel(amount) {
  if (amount < 150) return 1
  if (amount < 400) return 2
  if (amount < 800) return 3
  return 4
}

async function searchNearby(userLocation, filters) {
  return new Promise((resolve, reject) => {
    if (!placesService) {
      reject(new Error('Places service not ready'))
      return
    }

    const request = {
      location: new googleInstance.maps.LatLng(userLocation.lat, userLocation.lng),
      radius: filters.distance,
      type: 'restaurant',
    }

    if (filters.priceMin != null) request.minPriceLevel = amountToPriceLevel(filters.priceMin)
    if (filters.priceMax != null) request.maxPriceLevel = amountToPriceLevel(filters.priceMax)

    placesService.nearbySearch(request, (results, status) => {
      if (
        status !== googleInstance.maps.places.PlacesServiceStatus.OK &&
        status !== googleInstance.maps.places.PlacesServiceStatus.ZERO_RESULTS
      ) {
        reject(new Error(`Places API error: ${status}`))
        return
      }

      if (!results || results.length === 0) {
        resolve([])
        return
      }

      const userLatLng = new googleInstance.maps.LatLng(userLocation.lat, userLocation.lng)

      let filtered = results.filter((place) => {
        if (filters.ratingMin != null && (place.rating ?? 0) < filters.ratingMin) return false
        if (filters.ratingMax != null && (place.rating ?? 5) > filters.ratingMax) return false
        if (filters.minReviews != null && (place.user_ratings_total ?? 0) < filters.minReviews)
          return false
        return true
      })

      const processed = filtered.map((place) => {
        const placeLat = place.geometry.location.lat()
        const placeLng = place.geometry.location.lng()
        const distance = Math.round(
          googleInstance.maps.geometry.spherical.computeDistanceBetween(
            userLatLng,
            new googleInstance.maps.LatLng(placeLat, placeLng)
          )
        )
        return {
          place_id: place.place_id,
          name: place.name,
          vicinity: place.vicinity,
          price_level: place.price_level,
          rating: place.rating,
          user_ratings_total: place.user_ratings_total,
          photo_url: place.photos?.[0]?.getUrl({ maxWidth: 600, maxHeight: 400 }) || null,
          lat: placeLat,
          lng: placeLng,
          distance,
        }
      })

      placeRestaurantMarkers(processed)
      resolve(processed)
    })
  })
}

function getDetails(place_id) {
  return new Promise((resolve) => {
    placesService.getDetails(
      { placeId: place_id, fields: ['formatted_phone_number'] },
      (result, status) => {
        if (status === googleInstance.maps.places.PlacesServiceStatus.OK) {
          resolve(result.formatted_phone_number ?? null)
        } else {
          resolve(null)
        }
      }
    )
  })
}

defineExpose({ searchNearby, getDetails })
</script>

<style scoped>
.map-wrapper {
  position: relative;
  width: 100%;
  height: 450px;
}

.map-el {
  width: 100%;
  height: 100%;
}

.map-overlay {
  position: absolute;
  inset: 0;
  background: rgba(255, 248, 245, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.map-overlay-content {
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.map-overlay-content p {
  color: var(--text);
  font-size: 16px;
}

.map-status {
  position: absolute;
  top: 10px;
  left: 50%;
  transform: translateX(-50%);
  background: var(--primary);
  color: #fff;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 13px;
  z-index: 20;
}
</style>
