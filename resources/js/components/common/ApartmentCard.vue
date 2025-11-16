<template>
  <div class="apartment-card" @click="viewApartment">
    <div class="image-container">
      <img 
        :src="getImageUrl()" 
        :alt="apartment.name" 
        class="apartment-image"
        @error="handleImageError"
      >
      <div class="rating">⭐ {{ formatRating(apartment.rating) }}</div>
      <button @click.stop="toggleFavorite" class="favorite-btn" :class="{ favorited: isFavorited }">
        ❤️
      </button>
    </div>
    <div class="apartment-info">
      <div class="apartment-name">{{ apartment.name }}</div>
      <div class="apartment-price">{{ formatPrice(apartment.price_per_night) }} р <span class="price-period">за ночь</span></div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    apartment: Object
  },
  data() {
    return {
      isFavorited: false
    }
  },
  methods: {
    viewApartment() {
      console.log('View apartment:', this.apartment.id)
      this.$router.push(`/apartment/${this.apartment.id}`)
    },
    toggleFavorite(event) {
      event.stopPropagation()
      this.isFavorited = !this.isFavorited
      console.log('Toggle favorite:', this.apartment.id, this.isFavorited)
      // TODO: Add API call to toggle favorite
    },
    formatPrice(price) {
      if (!price) return '0'
      return new Intl.NumberFormat('ru-RU').format(price)
    },
    formatRating(rating) {
      if (!rating || rating === 0) return '0'
      return Number(rating).toFixed(2)
    },
    getImageUrl() {
      if (this.apartment.images && this.apartment.images.length > 0) {
        const img = this.apartment.images[0]
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
      }
      return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="300"%3E%3Crect fill="%23E0E0E0" width="400" height="300"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" fill="%23999" font-family="Arial" font-size="16"%3ENo image%3C/text%3E%3C/svg%3E'
    },
    handleImageError(event) {
      // Предотвращаем бесконечные попытки загрузки
      if (event.target.dataset.errorHandled === 'true') {
        event.target.style.display = 'none'
        return
      }
      
      event.target.dataset.errorHandled = 'true'
      console.warn('Image failed to load:', event.target.src)
      
      // Используем placeholder вместо скрытия
      const placeholder = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="300"%3E%3Crect fill="%23E0E0E0" width="400" height="300"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" fill="%23999" font-family="Arial" font-size="16"%3ENo image%3C/text%3E%3C/svg%3E'
      event.target.src = placeholder
    }
  }
}
</script>

<style scoped>
.apartment-card {
  border: 1px solid #E0E0E0;
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s;
  cursor: pointer;
  background: white;
  margin-bottom: 20px; /* Добавлен отступ снизу */
}

.apartment-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  border-color: #C0C0C0;
}

.image-container {
  position: relative;
  width: 100%;
  height: 200px; /* Увеличили высоту изображения */
}

.apartment-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.rating {
  position: absolute;
  top: 10px;
  left: 10px;
  background: rgba(0,0,0,0.7);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
}

.favorite-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 32px;
  height: 32px;
  background: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  transition: all 0.2s;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.favorite-btn:hover {
  transform: scale(1.1);
}

.favorite-btn.favorited {
  color: #FF385C;
}

.apartment-info {
  padding: 16px;
}

.apartment-name {
  font-weight: 600;
  font-size: 16px;
  margin-bottom: 4px;
  color: #000;
  line-height: 1.3;
}

.apartment-address {
  font-size: 14px;
  color: #666;
  margin-bottom: 2px;
  line-height: 1.3;
}

.apartment-details {
  font-size: 12px;
  color: #666;
}

.rooms, .guests {
  font-weight: 500;
}

.apartment-price {
  font-size: 12px;
  font-weight: 500;
  color: #666;
}

.price-period {
  font-size: 12px;
  font-weight: 400;
  color: #888;
}
</style>