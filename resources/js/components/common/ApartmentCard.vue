<template>
  <div class="apartment-card" @click="viewApartment">
    <div class="image-container">
      <img 
        :src="getImageUrl()" 
        :alt="apartment.name"
        class="apartment-image"
        @error="handleImageError"
      />
      <div class="rating">⭐ {{ formatRating(apartment.rating) }}</div>
      <button 
        v-if="!isAdmin"
        @click.stop="toggleFavorite" 
        class="favorite-btn" 
        :class="{ favorited: isFavorited, toggling: togglingFavorite }"
        :disabled="togglingFavorite"
        :title="isFavorited ? 'Удалить из избранного' : 'Добавить в избранное'"
      >
        <span v-if="isFavorited" class="heart-filled">❤️</span>
        <span v-else class="heart-empty">🤍</span>
      </button>
    </div>
    <div class="apartment-info">
      <div class="apartment-name">{{ apartment.name }}</div>
      <div class="apartment-price">{{ formatPrice(apartment.price_per_night) }} р <span class="price-period">в месяц</span></div>
    </div>
  </div>
</template>

<script>
import axios from '../../axios.js'

export default {
  props: {
    apartment: Object
  },
  data() {
    return {
      isFavorited: false,
      isAdmin: false,
      togglingFavorite: false
    }
  },
  async mounted() {
    // Проверяем роль пользователя
    try {
      const response = await axios.get('/api/check-auth')
      const user = response.data?.user
      
      if (user) {
        this.isAdmin = user.role === 'admin'
        
        // Если пользователь авторизован и не админ, проверяем избранное
        if (!this.isAdmin) {
          try {
            await this.checkFavoriteStatus()
          } catch (error) {
            // Игнорируем ошибки при проверке избранного
            console.warn('Could not check favorite status:', error)
          }
        }
      } else {
        this.isAdmin = false
      }
    } catch (error) {
      console.error('Error checking auth:', error)
      this.isAdmin = false
    }
  },
  methods: {
    viewApartment() {
      console.log('View apartment:', this.apartment.id)
      this.$router.push(`/apartment/${this.apartment.id}`)
    },
    async toggleFavorite(event) {
      event.stopPropagation()
      event.preventDefault()
      
      // Блокируем повторные нажатия
      if (this.togglingFavorite) {
        return
      }
      this.togglingFavorite = true
      
      try {
        // Проверяем авторизацию через localStorage сначала (быстрая проверка)
        const cachedUser = localStorage.getItem('authUser')
        if (cachedUser) {
          try {
            const user = JSON.parse(cachedUser)
            if (user.role === 'admin') {
              this.togglingFavorite = false
              return // Админы не могут добавлять в избранное
            }
          } catch (e) {
            // Игнорируем ошибку парсинга
          }
        }
        
        // Обновляем CSRF токен
        try {
          await axios.get('/sanctum/csrf-cookie')
          await new Promise(resolve => setTimeout(resolve, 100))
        } catch (csrfError) {
          console.warn('CSRF cookie error:', csrfError)
        }
        
        // Делаем запрос на добавление/удаление из избранного
        // Если пользователь не авторизован, сервер вернет 401
        const response = await axios.post(`/api/apartments/${this.apartment.id}/favorite`)
        
        if (response.data?.status) {
          this.isFavorited = response.data.status === 'added'
          
          // Визуальная обратная связь
          if (this.isFavorited) {
            console.log('Added to favorites')
          } else {
            console.log('Removed from favorites')
          }
        }
      } catch (error) {
        console.error('Error toggling favorite:', error)
        console.error('Error details:', {
          status: error.response?.status,
          data: error.response?.data,
          message: error.message
        })
        
        if (error.response?.status === 401 || error.response?.status === 419) {
          // Пробуем обновить сессию и повторить запрос
          try {
            console.log('Attempting to refresh session...')
            await axios.get('/sanctum/csrf-cookie')
            await new Promise(resolve => setTimeout(resolve, 300))
            
            // Повторяем запрос
            console.log('Retrying favorite toggle...')
            const retryResponse = await axios.post(`/api/apartments/${this.apartment.id}/favorite`)
            if (retryResponse.data?.status) {
              this.isFavorited = retryResponse.data.status === 'added'
              console.log('Retry successful!')
              return
            }
          } catch (retryError) {
            console.error('Retry failed:', retryError)
            console.error('Retry error details:', {
              status: retryError.response?.status,
              data: retryError.response?.data
            })
            alert('Сессия истекла. Пожалуйста, войдите в систему заново')
            this.$router.push('/login')
          }
        } else if (error.response?.status === 403) {
          alert('Доступ запрещен')
        } else {
          const message = error.response?.data?.message || error.response?.data?.error || 'Ошибка при добавлении в избранное'
          alert(message)
        }
      } finally {
        this.togglingFavorite = false
      }
    },
    async checkFavoriteStatus() {
      try {
        // Проверяем авторизацию перед запросом
        const authCheck = await axios.get('/api/check-auth')
        if (!authCheck.data?.user || authCheck.data.user.role === 'admin') {
          return
        }
        
        const response = await axios.get('/api/favorites')
        const favorites = response.data || []
        this.isFavorited = favorites.some(fav => 
          fav.apartment_id === this.apartment.id || 
          fav.apartment?.id === this.apartment.id
        )
      } catch (error) {
        // Игнорируем ошибки - просто не показываем избранное
        console.warn('Error checking favorite status:', error)
        this.isFavorited = false
      }
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
        // Используем url если есть (приоритет)
        if (img.url) {
          return img.url
        }
        // Если есть image_path, формируем URL
        if (img.image_path) {
          // Если путь уже полный URL, возвращаем его
          if (img.image_path.startsWith('http://') || img.image_path.startsWith('https://')) {
            return img.image_path
          }
          // Убираем лишние слеши
          let path = img.image_path.replace(/^\/+/, '')
          
          // Если путь начинается с images/apartments, используем его напрямую
          if (path.startsWith('images/apartments/')) {
            return `/${path}`
          }
          // Для старых путей из storage
          if (path.startsWith('storage/')) {
            return `/${path}`
          }
          // Если путь не начинается ни с images, ни с storage, пробуем storage
          return `/storage/${path}`
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
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.95);
  border: 2px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  z-index: 10;
}

.favorite-btn:hover:not(:disabled) {
  transform: scale(1.15);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  background: white;
}

.favorite-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.favorite-btn.favorited {
  background: rgba(255, 56, 92, 0.1);
  border-color: #FF385C;
}

.favorite-btn.favorited .heart-filled {
  animation: heartBeat 0.3s ease;
}

.favorite-btn.toggling {
  animation: pulse 0.5s ease infinite;
}

@keyframes heartBeat {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.3); }
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
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