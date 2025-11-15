<template>
  <AppLayout>
    <div class="detail-page">
      <SearchBar />
      <div class="detail-container">
        <div class="left-column">
          <h1>{{ apartment.name }}</h1>
          
          <div class="gallery">
            <div class="main-image">
              <img :src="apartment.images && apartment.images[0] ? apartment.images[0].url : ''" :alt="apartment.name">
            </div>
            <div class="thumbnails">
              <img 
                v-for="(img, idx) in apartment.images.slice(0, 4)"
                :key="idx"
                :src="img.url"
                :alt="apartment.name"
              >
              <button v-if="apartment.images && apartment.images.length > 5" @click="showAllPhotos" class="show-all-btn">
                Показать все фото
              </button>
            </div>
          </div>

          <div class="info-section">
            <div class="rating-block">
              <span class="rating">⭐ {{ apartment.rating }}</span>
              <span class="reviews-count">{{ apartment.reviews_count }} отзывов</span>
            </div>
          </div>

          <div class="apartment-info">
            <h3>О квартире</h3>
            <ul>
              <li>Комнаты: {{ apartment.rooms }}</li>
              <li>Общая площадь: {{ apartment.total_area }} м²</li>
              <li>Кухня: {{ apartment.kitchen_area }} м²</li>
              <li>Жилая: {{ apartment.living_area }} м²</li>
              <li>Этаж: {{ apartment.floor }}</li>
              <li>Балкон или лоджия: {{ apartment.balcony || 'нет' }}</li>
              <li>Санузел: {{ apartment.bathroom || 'не указано' }}</li>
              <li>Ремонт: {{ apartment.renovation || 'не указано' }}</li>
              <li>Мебель: {{ apartment.furniture || 'нет' }}</li>
              <li>Техника: {{ apartment.appliances || 'нет' }}</li>
              <li>Интернет и ТВ: {{ apartment.has_internet ? 'Wi-Fi, телевидение' : 'нет' }}</li>
            </ul>
          </div>

          <div class="rental-conditions">
            <h3>Условия аренды</h3>
            <ul>
              <li>Залог: {{ apartment.deposit }} ₽</li>
              <li>Комиссия: {{ apartment.commission }}%</li>
              <li>По счетчикам: {{ apartment.meter_based ? 'оплачивается отдельно' : 'включено' }}</li>
              <li>Другие ЖККУ: {{ apartment.other_utilities ? 'включено в платёж' : 'оплачивается отдельно' }}</li>
            </ul>
          </div>

          <div class="rules">
            <h3>Правила</h3>
            <ul>
              <li>Количество гостей: {{ apartment.max_guests || 'не указано' }}</li>
              <li>Можно с детьми: {{ apartment.allows_children ? 'да' : 'нет' }}</li>
              <li>Можно с животными: {{ apartment.allows_pets ? 'да' : 'нет' }}</li>
              <li>Можно курить: {{ apartment.allows_smoking ? 'да' : 'нет' }}</li>
            </ul>
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
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '../layouts/AppLayout.vue'
import SearchBar from '../common/SearchBar.vue'
import ReviewsList from '../common/ReviewsList.vue'
import BookingPanel from '../common/BookingPanel.vue'
import axios from 'axios'

export default {
  components: {
    AppLayout,
    SearchBar,
    ReviewsList,
    BookingPanel
  },
  data() {
    return {
      apartment: {
        images: [],
        reviews: []
      }
    }
  },
  mounted() {
    const apartmentId = this.$route.params.id
    this.fetchApartment(apartmentId)
  },
  watch: {
    apartment: {
      handler(newVal) {
        if (newVal && newVal.latitude && newVal.longitude) {
          this.$nextTick(() => {
            this.loadGoogleMaps()
          })
        }
      },
      deep: true
    }
  },
  methods: {
    fetchApartment(id) {
      axios.get(`/api/apartments/${id}`).then(res => {
        this.apartment = res.data
      }).catch(err => {
        console.error('Error fetching apartment:', err)
      })
    },
    showAllPhotos() {
      // TODO: Implement photo gallery modal
      console.log('Show all photos')
    },
    loadGoogleMaps() {
      if (window.google && window.google.maps) {
        this.initMap()
        return
      }
      
      const script = document.createElement('script')
      script.src = `https://maps.googleapis.com/maps/api/js?key=${process.env.VUE_APP_GOOGLE_MAPS_KEY || ''}&callback=initGoogleMap`
      script.async = true
      script.defer = true
      window.initGoogleMap = () => {
        this.initMap()
      }
      document.head.appendChild(script)
    },
    initMap() {
      if (!this.apartment.latitude || !this.apartment.longitude) return
      
      const mapContainer = document.getElementById('map-container')
      if (!mapContainer) return
      
      const map = new google.maps.Map(mapContainer, {
        center: { lat: parseFloat(this.apartment.latitude), lng: parseFloat(this.apartment.longitude) },
        zoom: 15
      })
      
      new google.maps.Marker({
        position: { lat: parseFloat(this.apartment.latitude), lng: parseFloat(this.apartment.longitude) },
        map: map,
        title: this.apartment.name
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
}

.detail-container {
  display: grid;
  grid-template-columns: 1fr 350px;
  gap: 30px;
}

.left-column h1 {
  font-size: 24px;
  margin-bottom: 20px;
}

.gallery {
  display: grid;
  grid-template-rows: 400px 100px;
  gap: 10px;
  margin-bottom: 30px;
}

.main-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
}

.thumbnails {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 10px;
}

.thumbnails img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 4px;
  cursor: pointer;
}

.show-all-btn {
  width: 100%;
  border: none;
  background: var(--accent);
  border-radius: 4px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
}

.info-section {
  margin-bottom: 30px;
}

.rating-block {
  display: flex;
  gap: 15px;
  font-size: 14px;
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
  font-size: 16px;
  margin-bottom: 15px;
  font-weight: bold;
}

ul {
  list-style: none;
}

ul li {
  padding: 8px 0;
  border-bottom: 1px solid #F0F0F0;
  font-size: 14px;
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
}

@media (max-width: 1024px) {
  .detail-container {
    grid-template-columns: 1fr;
  }
}
</style>
