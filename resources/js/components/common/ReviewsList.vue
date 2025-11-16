<template>
  <div class="reviews-section">
    <h3>Отзывы</h3>
    
    <!-- Форма для создания отзыва -->
    <div v-if="canLeaveReview && !hasUserReview" class="review-form-section">
      <h4>Оставить отзыв</h4>
      <form @submit.prevent="submitReview" class="review-form">
        <div class="rating-input">
          <label>Оценка:</label>
          <select v-model.number="reviewForm.rating" required>
            <option value="">Выберите оценку</option>
            <option :value="5">5 - Отлично</option>
            <option :value="4">4 - Хорошо</option>
            <option :value="3">3 - Удовлетворительно</option>
            <option :value="2">2 - Плохо</option>
            <option :value="1">1 - Очень плохо</option>
          </select>
        </div>
        <div class="comment-input">
          <label>Комментарий:</label>
          <textarea 
            v-model="reviewForm.comment" 
            rows="4" 
            placeholder="Оставьте ваш отзыв..."
            required
            maxlength="1000"
          ></textarea>
          <span class="char-count">{{ reviewForm.comment.length }}/1000</span>
        </div>
        <button type="submit" class="submit-review-btn" :disabled="submitting">
          {{ submitting ? 'Отправка...' : 'Отправить отзыв' }}
        </button>
      </form>
    </div>
    
    <div v-if="visibleReviews.length === 0 && !canLeaveReview" class="no-reviews">
      Пока нет отзывов
    </div>
    <div v-else-if="visibleReviews.length > 0" class="reviews-list">
      <div v-for="review in visibleReviews" :key="review.id" class="review-card">
        <div class="review-header">
          <div>
            <p class="reviewer-name">{{ review.user.name }}</p>
            <p class="review-date">{{ formatDate(review.created_at) }}</p>
          </div>
          <div class="rating">
            <span>⭐ {{ review.rating }}</span>
          </div>
        </div>
        <p class="review-text">{{ review.text }}</p>
      </div>
    </div>
    <button 
      v-if="reviews.length > 2"
      @click="showAllReviews = !showAllReviews"
      class="show-more-btn"
    >
      {{ showAllReviews ? 'Скрыть отзывы' : 'Показать все отзывы' }}
    </button>
  </div>
</template>

<script>
import axios from '../../axios.js'

export default {
  props: {
    apartmentId: {
      type: Number,
      required: true
    },
    reviews: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      showAllReviews: false,
      canLeaveReview: false,
      bookingForReview: null,
      reviewForm: {
        rating: null,
        comment: ''
      },
      submitting: false
    }
  },
  computed: {
    visibleReviews() {
      return this.showAllReviews ? this.reviews : this.reviews.slice(0, 2)
    },
    hasUserReview() {
      // Проверяем, есть ли уже отзыв от текущего пользователя
      return this.reviews.some(review => {
        // Это будет проверено на сервере, но для UI можно проверить
        return false // Пока не знаем ID пользователя, проверка на сервере
      })
    }
  },
  async mounted() {
    await this.checkCanLeaveReview()
  },
  methods: {
    async checkCanLeaveReview() {
      try {
        const authCheck = await axios.get('/api/check-auth')
        if (!authCheck.data.user || authCheck.data.user.role === 'admin') {
          this.canLeaveReview = false
          return
        }
        
        // Получаем список бронирований, на которые можно оставить отзыв
        const response = await axios.get('/api/reviews/bookings')
        const bookings = response.data
        
        // Ищем бронирование для этого апартамента
        this.bookingForReview = bookings.find(b => b.apartment_id === this.apartmentId)
        this.canLeaveReview = !!this.bookingForReview
        
        // Проверяем, есть ли уже отзыв от этого пользователя
        const userReview = this.reviews.find(r => {
          // Проверка будет на сервере, но для UI можно попробовать
          return false
        })
      } catch (error) {
        console.error('Error checking review eligibility:', error)
        this.canLeaveReview = false
      }
    },
    async submitReview() {
      if (!this.bookingForReview) {
        alert('Не найдено бронирование для этого апартамента')
        return
      }
      
      this.submitting = true
      try {
        await axios.get('/sanctum/csrf-cookie')
        
        const response = await axios.post('/api/reviews', {
          booking_id: this.bookingForReview.id,
          rating: this.reviewForm.rating,
          comment: this.reviewForm.comment
        })
        
        if (response.data.success) {
          alert('Отзыв успешно добавлен!')
          // Очищаем форму
          this.reviewForm = {
            rating: null,
            comment: ''
          }
          this.canLeaveReview = false
          // Обновляем страницу для показа нового отзыва
          this.$emit('review-added')
          window.location.reload()
        }
      } catch (error) {
        console.error('Error submitting review:', error)
        if (error.response?.data?.message) {
          alert(error.response.data.message)
        } else {
          alert('Ошибка при отправке отзыва')
        }
      } finally {
        this.submitting = false
      }
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('ru-RU')
    }
  }
}
</script>

<style scoped>
.reviews-section {
  margin-top: 40px;
  margin-bottom: 40px;
}

.reviews-section h3 {
  font-size: 16px;
  margin-bottom: 20px;
  font-weight: bold;
}

.no-reviews {
  text-align: center;
  padding: 40px 0;
  color: #999;
}

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-bottom: 20px;
}

.review-card {
  padding: 15px;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 10px;
  padding-bottom: 10px;
  border-bottom: 1px solid #F0F0F0;
}

.reviewer-name {
  font-weight: bold;
  font-size: 14px;
  margin-bottom: 3px;
}

.review-date {
  font-size: 12px;
  color: #999;
}

.rating {
  font-weight: bold;
}

.review-text {
  font-size: 13px;
  line-height: 1.5;
  color: #333;
}

.show-more-btn {
  width: 100%;
  padding: 10px;
  border: 1px solid var(--accent);
  background: white;
  color: var(--accent);
  border-radius: 6px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
  font-weight: bold;
  transition: all 0.3s;
}

.show-more-btn:hover {
  background: var(--accent);
  color: #000;
}

.review-form-section {
  margin-bottom: 30px;
  padding: 20px;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  background: #F9F9F9;
}

.review-form-section h4 {
  font-size: 16px;
  margin-bottom: 15px;
  font-weight: bold;
}

.review-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.rating-input,
.comment-input {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.rating-input label,
.comment-input label {
  font-size: 14px;
  font-weight: 500;
}

.rating-input select {
  padding: 8px;
  border: 1px solid #E0E0E0;
  border-radius: 4px;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
}

.comment-input textarea {
  padding: 8px;
  border: 1px solid #E0E0E0;
  border-radius: 4px;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
  resize: vertical;
}

.char-count {
  font-size: 12px;
  color: #999;
  text-align: right;
}

.submit-review-btn {
  padding: 10px 20px;
  background: var(--accent);
  color: #000;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
  font-weight: bold;
  transition: all 0.3s;
  align-self: flex-start;
}

.submit-review-btn:hover:not(:disabled) {
  background: #B5B8E8;
}

.submit-review-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
