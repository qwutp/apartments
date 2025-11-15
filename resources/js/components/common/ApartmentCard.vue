<template>
  <div class="apartment-card" @click="viewApartment">
    <div class="image-container">
      <img :src="apartment.images && apartment.images[0] ? apartment.images[0].url : '' || '/images/placeholder.jpg'" :alt="apartment.name" class="apartment-image">
      <div class="rating">⭐ {{ apartment.rating || '4.79' }}</div>
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