<template>
  <div class="bookings-page">
    <h2>Прошлые бронирования</h2>
    
    <div class="empty">
      У вас нет прошлых бронирований
    </div>
  </div>
</template>

<script>
export default {
  name: 'PastBookings',
  data() {
    return {
      bookings: []
    }
  },
  mounted() {
    this.fetchBookings()
  },
  methods: {
    fetchBookings() {
      // Будет загрузка из API
      this.bookings = []
    },
    addReview(bookingId) {
      console.log('Add review for booking:', bookingId)
      this.$router.push(`/review?booking=${bookingId}`)
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
  border-radius: 8px;
  align-items: center;
}

.booking-card img {
  width: 120px;
  height: 120px;
  object-fit: cover;
  border-radius: 8px;
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

.price {
  color: var(--accent);
  font-weight: bold;
  font-size: 16px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
}

.btn-primary {
  background: var(--accent);
  color: #000;
}
</style>