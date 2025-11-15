<template>
  <div class="reviews-section">
    <h3>Отзывы</h3>
    <div v-if="visibleReviews.length === 0" class="no-reviews">
      Пока нет отзывов
    </div>
    <div v-else class="reviews-list">
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
      showAllReviews: false
    }
  },
  computed: {
    visibleReviews() {
      return this.showAllReviews ? this.reviews : this.reviews.slice(0, 2)
    }
  },
  methods: {
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
</style>
