<template>
  <div class="detail-page">
    <div v-if="loading" class="loading">Загрузка данных апартамента...</div>
    <template v-else>
      <div class="detail-container">
        <div class="left-column">
          <h1>{{ apartment.name || 'Загрузка...' }}</h1>
          
          <div class="gallery" v-if="apartment.images && apartment.images.length > 0">
            <div class="gallery-main">
              <div class="main-image">
                <img 
                  :src="getImageUrl(apartment.images[0])" 
                  :alt="apartment.name"
                  @error="handleImageError"
                >
              </div>
              <div class="thumbnails">
                <img 
                  v-for="(img, idx) in apartment.images.slice(1, 6)"
                  :key="img.id || idx"
                  :src="getImageUrl(img)"
                  :alt="apartment.name"
                  @error="handleImageError"
                  @click="setMainImage(getImageUrl(img))"
                  class="thumbnail"
                >
                <button 
                  v-if="apartment.images && apartment.images.length > 6" 
                  @click="showAllPhotos" 
                  class="show-all-btn"
                >
                  Показать все фото
                </button>
              </div>
            </div>
          </div>
          <div v-else class="gallery-placeholder">
            <p>Изображения не загружены</p>
          </div>

          <div class="info-section">
            <div class="rating-block">
              <span class="rating">⭐ {{ apartment.rating }}</span>
              <span class="reviews-count">{{ apartment.reviews_count }} отзывов</span>
            </div>
          </div>

          <div class="apartment-info">
            <h3>О квартире</h3>
            <div class="info-grid">
              <div class="info-column">
                <div class="info-item">
                  <span class="info-label">Количество комнат:</span>
                  <span class="info-value">{{ apartment.rooms }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Общая площадь:</span>
                  <span class="info-value">{{ apartment.total_area }} м²</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Площадь кухни:</span>
                  <span class="info-value">{{ apartment.kitchen_area || 'не указано' }} м²</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Жилая площадь:</span>
                  <span class="info-value">{{ apartment.living_area || 'не указано' }} м²</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Этаж:</span>
                  <span class="info-value">{{ apartment.floor || 'не указано' }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Балкон или лоджия:</span>
                  <span class="info-value">{{ apartment.balcony || 'нет' }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Санузел:</span>
                  <span class="info-value">{{ apartment.bathroom || 'не указано' }}</span>
                </div>
              </div>
              <div class="info-column">
                <div class="info-item">
                  <span class="info-label">Ремонт:</span>
                  <span class="info-value">{{ apartment.renovation || 'не указано' }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Мебель:</span>
                  <span class="info-value">{{ apartment.furniture || 'нет' }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Техника:</span>
                  <span class="info-value">{{ apartment.appliances || 'нет' }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Интернет и ТВ:</span>
                  <span class="info-value">{{ apartment.has_internet ? 'Wi-Fi, телевидение' : 'нет' }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="rental-conditions">
            <h3>Условия аренды</h3>
            <div class="info-list">
              <div class="info-item">
                <span class="info-label">Залог:</span>
                <span class="info-value">{{ apartment.deposit || 0 }} ₽</span>
              </div>
              <div class="info-item">
                <span class="info-label">Комиссия:</span>
                <span class="info-value">{{ apartment.commission || 0 }}%</span>
              </div>
              <div class="info-item">
                <span class="info-label">По счетчикам:</span>
                <span class="info-value">{{ apartment.meter_based ? 'оплачивается отдельно' : 'включено' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Другие ЖКУ:</span>
                <span class="info-value">{{ apartment.other_utilities ? 'включено в платёж' : 'оплачивается отдельно' }}</span>
              </div>
            </div>
          </div>

          <div class="rules">
            <h3>Правила</h3>
            <div class="info-list">
              <div class="info-item">
                <span class="info-label">Количество гостей:</span>
                <span class="info-value">{{ apartment.max_guests || 'не указано' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Можно с детьми:</span>
                <span class="info-value">{{ apartment.allows_children ? 'да' : 'нет' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Можно с животными:</span>
                <span class="info-value">{{ apartment.allows_pets ? 'да' : 'нет' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Можно курить:</span>
                <span class="info-value">{{ apartment.allows_smoking ? 'да' : 'нет' }}</span>
              </div>
            </div>
          </div>

          <div class="description">
            <h3>Описание</h3>
            <p>{{ apartment.description }}</p>
          </div>

          <div class="location">
            <h3>Расположение</h3>
            <p>{{ apartment.address }}</p>
            <div class="map" id="map-container"></div>
          </div>

          <ReviewsList :apartment-id="apartment.id" :reviews="apartment.reviews" />
        </div>

        <BookingPanel :apartment="apartment" />
      </div>
    </template>
  </div>
</template>

<script>
import SearchBar from '../common/SearchBar.vue'
import ReviewsList from '../common/ReviewsList.vue'
import BookingPanel from '../common/BookingPanel.vue'
import axios from '../../axios.js'

export default {
  components: {
    SearchBar,
    ReviewsList,
    BookingPanel
  },
  data() {
    return {
      apartment: {
        images: [],
        reviews: [],
        name: '',
        address: '',
        rating: 0,
        reviews_count: 0
      },
      loading: true
    }
  },
  mounted() {
    const apartmentId = this.$route.params.id
    if (apartmentId) {
      this.fetchApartment(apartmentId)
    }
  },
  watch: {
    '$route'(to, from) {
      if (to.params.id !== from.params.id) {
        this.fetchApartment(to.params.id)
      }
    },
    apartment: {
      handler(newVal) {
        if (newVal && (newVal.latitude && newVal.longitude || newVal.address)) {
          this.$nextTick(() => {
            this.loadYandexMap()
          })
        }
      },
      deep: true
    }
  },
  methods: {
    async fetchApartment(id) {
      if (!id) return
      
      this.loading = true
      try {
        console.log('Fetching apartment:', id)
        const response = await axios.get(`/api/apartments/${id}`)
        this.apartment = response.data
        console.log('Apartment loaded:', this.apartment)
        
        // Загружаем карту после получения данных
        if (this.apartment.latitude && this.apartment.longitude || this.apartment.address) {
          this.$nextTick(() => {
            this.loadYandexMap()
          })
        }
      } catch (error) {
        console.error('Error fetching apartment:', error)
        alert('Ошибка загрузки данных апартамента: ' + (error.response?.data?.message || error.message))
      } finally {
        this.loading = false
      }
    },
    showAllPhotos() {
      // TODO: Implement photo gallery modal
      console.log('Show all photos')
    },
    
    getImageUrl(img) {
      if (!img) return ''
      // Используем url если есть
      if (img.url) {
        return img.url
      }
      // Если есть image_path, формируем URL
      if (img.image_path) {
        // Если путь уже полный URL, возвращаем его
        if (img.image_path.startsWith('http://') || img.image_path.startsWith('https://')) {
          return img.image_path
        }
        // Иначе формируем URL относительно storage
        return `/storage/${img.image_path.replace(/^storage\//, '')}`
      }
      return ''
    },
    
    setMainImage(url) {
      if (this.apartment.images && this.apartment.images.length > 0) {
        // Перемещаем выбранное изображение на первое место
        const index = this.apartment.images.findIndex(img => {
          const imgUrl = this.getImageUrl(img)
          return imgUrl === url
        })
        if (index > 0) {
          const [selected] = this.apartment.images.splice(index, 1)
          this.apartment.images.unshift(selected)
        }
      }
    },
    
    handleImageError(event) {
      // Предотвращаем бесконечные попытки загрузки
      if (event.target.dataset.errorHandled === 'true') {
        return
      }
      
      event.target.dataset.errorHandled = 'true'
      console.warn('Image failed to load:', event.target.src)
      
      // Пробуем альтернативный путь
      const src = event.target.src
      if (src.includes('http://') || src.includes('https://')) {
        // Если это полный URL, пробуем извлечь путь
        try {
          const url = new URL(src)
          const path = url.pathname
          if (path.includes('/storage/')) {
            // Пробуем относительный путь
            event.target.src = path
            return
          }
        } catch (e) {
          // Игнорируем ошибку парсинга URL
        }
      }
      
      // Если не помогло, показываем placeholder
      const placeholder = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="300"%3E%3Crect fill="%23E0E0E0" width="400" height="300"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" fill="%23999" font-family="Arial" font-size="16"%3EИзображение не загружено%3C/text%3E%3C/svg%3E'
      event.target.src = placeholder
    },
    loadYandexMap() {
      // Проверяем, не загружен ли уже скрипт
      if (document.querySelector('script[src*="api-maps.yandex.ru"]')) {
        if (window.ymaps) {
          window.ymaps.ready(() => {
            this.initYandexMap()
          })
        } else {
          // Ждем загрузки
          const checkYmaps = setInterval(() => {
            if (window.ymaps) {
              clearInterval(checkYmaps)
              window.ymaps.ready(() => {
                this.initYandexMap()
              })
            }
          }, 100)
        }
        return
      }
      
      // Загружаем Яндекс.Карты без API ключа (работает для базовых функций)
      const script = document.createElement('script')
      script.src = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU'
      script.async = true
      script.onload = () => {
        window.ymaps.ready(() => {
          this.initYandexMap()
        })
      }
      document.head.appendChild(script)
    },
    initYandexMap() {
      const mapContainer = document.getElementById('map-container')
      if (!mapContainer) {
        console.error('Map container not found')
        return
      }
      
      // Если есть координаты, используем их
      if (this.apartment.latitude && this.apartment.longitude) {
        const lat = parseFloat(this.apartment.latitude)
        const lon = parseFloat(this.apartment.longitude)
        
        window.ymaps.ready(() => {
          const map = new window.ymaps.Map(mapContainer, {
            center: [lat, lon],
            zoom: 15,
            controls: ['zoomControl', 'fullscreenControl']
          })
          
          const marker = new window.ymaps.Placemark([lat, lon], {
            balloonContent: `<strong>${this.apartment.name}</strong><br>${this.apartment.address}`,
            hintContent: this.apartment.address || this.apartment.name
          }, {
            preset: 'islands#redDotIcon'
          })
          
          map.geoObjects.add(marker)
          this.yandexMap = map
          
          // Открываем балун с информацией
          marker.balloon.open()
        })
      } else if (this.apartment.address) {
        // Если нет координат, но есть адрес - геокодируем
        this.geocodeAddress(this.apartment.address)
      } else {
        // Если ничего нет - показываем карту Москвы
        window.ymaps.ready(() => {
          const map = new window.ymaps.Map(mapContainer, {
            center: [55.7558, 37.6173],
            zoom: 10,
            controls: ['zoomControl', 'fullscreenControl']
          })
          this.yandexMap = map
        })
      }
    },
    geocodeAddress(address) {
      if (!window.ymaps || !address) return
      
      window.ymaps.geocode(address).then(res => {
        const firstGeoObject = res.geoObjects.get(0)
        if (firstGeoObject) {
          const coordinates = firstGeoObject.geometry.getCoordinates()
          this.apartment.latitude = coordinates[0]
          this.apartment.longitude = coordinates[1]
          
          const mapContainer = document.getElementById('map-container')
          if (!mapContainer) return
          
          window.ymaps.ready(() => {
            const map = new window.ymaps.Map(mapContainer, {
              center: coordinates,
              zoom: 15,
              controls: ['zoomControl', 'fullscreenControl']
            })
            
            const marker = new window.ymaps.Placemark(coordinates, {
              balloonContent: `<strong>${this.apartment.name}</strong><br>${address}`,
              hintContent: address
            }, {
              preset: 'islands#redDotIcon'
            })
            
            map.geoObjects.add(marker)
            this.yandexMap = map
            marker.balloon.open()
          })
        }
      }).catch(err => {
        console.error('Geocoding error:', err)
      })
    }
  }
}
</script>

<style scoped>
.detail-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 20px 40px;
  max-width: 1400px;
  margin: 0 auto;
  width: 100%;
}

.detail-container {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 40px;
  align-items: start;
  position: relative;
}

.left-column {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.left-column h1 {
  font-size: 24px;
  margin-bottom: 20px;
}

.gallery {
  margin-bottom: 30px;
}

.gallery-main {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 10px;
  height: 500px;
}

.main-image {
  width: 100%;
  height: 100%;
  overflow: hidden;
  border-radius: 8px;
}

.main-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.thumbnails {
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: repeat(3, 1fr);
  gap: 10px;
  height: 100%;
}

.thumbnail {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 4px;
  cursor: pointer;
  transition: opacity 0.2s;
}

.thumbnail:hover {
  opacity: 0.8;
}

.show-all-btn {
  grid-column: 1 / -1;
  width: 100%;
  height: 100%;
  border: none;
  background: var(--accent);
  color: #000;
  border-radius: 4px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
  font-weight: 500;
  transition: background 0.2s;
}

.show-all-btn:hover {
  background: #B5B8E8;
}

.info-section {
  margin-bottom: 30px;
}

.rating-block {
  display: flex;
  align-items: center;
  gap: 15px;
  font-size: 14px;
  margin-bottom: 20px;
}

.rating {
  font-weight: 600;
  color: #000;
}

.reviews-count {
  color: #666;
}

.apartment-info,
.rental-conditions,
.rules {
  margin-bottom: 30px;
}

.apartment-info h3,
.rental-conditions h3,
.rules h3,
.description h3,
.location h3 {
  font-size: 18px;
  margin-bottom: 20px;
  font-weight: bold;
  color: #000;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.info-column {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.info-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #F0F0F0;
  font-size: 14px;
}

.info-label {
  color: #666;
  font-weight: 400;
}

.info-value {
  color: #000;
  font-weight: 500;
  text-align: right;
}

.description {
  margin-bottom: 30px;
}

.description p {
  line-height: 1.6;
  color: #666;
}

.location {
  margin-bottom: 30px;
}

.map {
  margin-top: 15px;
  border-radius: 8px;
  overflow: hidden;
  height: 400px;
  width: 100%;
  background: #f0f0f0;
  position: relative;
}

.map::before {
  content: 'Загрузка карты...';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: #999;
  z-index: 1;
}

.loading {
  text-align: center;
  padding: 40px;
  color: #999;
  font-size: 16px;
}

.gallery-placeholder {
  padding: 100px 20px;
  text-align: center;
  color: #999;
  border: 2px dashed #E0E0E0;
  border-radius: 8px;
  margin-bottom: 30px;
}

@media (max-width: 1024px) {
  .detail-container {
    grid-template-columns: 1fr;
  }
}
</style>
