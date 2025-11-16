<template>
  <div class="favorites-page">
    <h2>Избранное</h2>
    
    <div v-if="loading" class="loading">Загрузка...</div>
    <div v-else-if="apartments.length === 0" class="empty">
      Вы пока не добавили апартаменты в избранное
    </div>
    <div v-else class="apartments-grid">
      <ApartmentCard 
        v-for="apartment in apartments" 
        :key="apartment.id" 
        :apartment="apartment"
      />
    </div>
  </div>
</template>

<script>
import axios from '../../../axios.js'
import ApartmentCard from '../../common/ApartmentCard.vue'

export default {
  name: 'Favorites',
  components: {
    ApartmentCard
  },
  data() {
    return {
      apartments: [],
      loading: true
    }
  },
  async mounted() {
    await this.fetchFavorites()
  },
  methods: {
    async fetchFavorites() {
      this.loading = true
      try {
        // Сначала обновляем CSRF токен
        try {
          await axios.get('/sanctum/csrf-cookie')
          await new Promise(resolve => setTimeout(resolve, 100))
        } catch (csrfError) {
          console.warn('CSRF cookie error:', csrfError)
        }
        
        // Делаем запрос к API избранного напрямую
        // Если пользователь не авторизован, сервер вернет 401
        const response = await axios.get('/api/favorites')
        const favorites = response.data || []
        
        // Преобразуем данные избранного в формат апартаментов
        this.apartments = favorites
          .filter(fav => fav.apartment)
          .map(fav => fav.apartment)
      } catch (error) {
        console.error('Error fetching favorites:', error)
        console.error('Error details:', {
          status: error.response?.status,
          data: error.response?.data,
          message: error.message
        })
        
        if (error.response?.status === 401 || error.response?.status === 419) {
          // Пробуем обновить сессию и повторить
          try {
            console.log('Attempting to refresh session...')
            await axios.get('/sanctum/csrf-cookie')
            await new Promise(resolve => setTimeout(resolve, 300))
            
            // Повторяем запрос
            console.log('Retrying favorites request...')
            const retryResponse = await axios.get('/api/favorites')
            const favorites = retryResponse.data || []
            this.apartments = favorites
              .filter(fav => fav.apartment)
              .map(fav => fav.apartment)
            console.log('Retry successful!')
            return
          } catch (retryError) {
            console.error('Retry failed:', retryError)
            console.error('Retry error details:', {
              status: retryError.response?.status,
              data: retryError.response?.data
            })
            alert('Сессия истекла. Пожалуйста, войдите в систему заново')
            this.$router.push('/login')
          }
        } else {
          const errorMsg = error.response?.data?.message || error.message || 'Неизвестная ошибка'
          alert('Ошибка при загрузке избранного: ' + errorMsg)
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.favorites-page {
  padding: 30px;
}

.favorites-page h2 {
  font-size: 24px;
  margin-bottom: 30px;
}

.empty {
  text-align: center;
  padding: 60px 20px;
  color: #999;
  font-size: 16px;
}

.apartments-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}
</style>