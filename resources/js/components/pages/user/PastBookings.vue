<template>
  <div class="bookings-page">
    <h2>Прошлые бронирования</h2>

    <div v-if="loading" class="state">Загружаем бронирования...</div>
    <div v-else-if="bookings.length === 0" class="empty">
      У вас нет прошлых бронирований
    </div>
    <div v-else class="bookings-list">
      <div v-for="booking in bookings" :key="booking.id" class="booking-card">
        <img v-if="booking.apartment.image" :src="booking.apartment.image" :alt="booking.apartment.name">
        <div class="booking-info">
          <h3>{{ booking.apartment.name }}</h3>
          <p>{{ booking.apartment.address }}</p>
          <p>С {{ formatDate(booking.check_in) }} по {{ formatDate(booking.check_out) }}</p>
          <p>{{ booking.guests }} гостей · {{ formatPrice(booking.total_price) }}</p>
        </div>
        <div class="actions">
          <button
            v-if="booking.can_review"
            class="btn btn-primary"
            @click="addReview(booking.id)"
          >
            Оставить отзыв
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'PastBookings',
  data() {
    return {
      bookings: [],
      loading: false
    }
  },
  mounted() {
    this.fetchBookings()
  },
  methods: {
    async fetchBookings() {
      this.loading = true
      try {
        const response = await axios.get('/api/bookings/past')
        this.bookings = response.data || []
      } catch (error) {
        console.error('Ошибка загрузки прошлых бронирований', error)
        alert('Не удалось загрузить историю бронирований')
      } finally {
        this.loading = false
      }
    },
    addReview(bookingId) {
      this.$router.push(`/review?booking=${bookingId}`)
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('ru-RU')
    },
    formatPrice(price) {
      if (!price) return '0 ₽'
      return new Intl.NumberFormat('ru-RU').format(price) + ' ₽'
    }
  }
}
</script>

<style scoped>
.bookings-page {
  padding: 30px;
}

.bookings-page h2 {
  font-size: 24px;
  margin-bottom: 30px;
}

.state,
.empty {
  text-align: center;
  padding: 60px 20px;
  color: #999;
  font-size: 16px;
}

.bookings-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.booking-card {
  display: flex;
  gap: 20px;
  padding: 20px;
  border: 1px solid #E0E0E0;
  border-radius: 12px;
  align-items: center;
  background: #fff;
}

.booking-card img {
  width: 120px;
  height: 120px;
  object-fit: cover;
  border-radius: 12px;
}

.booking-info {
  flex: 1;
}

.booking-info h3 {
  margin-bottom: 8px;
  font-size: 18px;
}

.booking-info p {
  margin: 4px 0;
  color: #666;
  font-size: 14px;
}

.actions .btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
  background: var(--accent);
  color: #000;
  font-weight: 600;
}
</style>