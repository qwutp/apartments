<template>
  <div class="booking-panel">
    <div class="panel-sticky">
      <div class="price-section">
        <div class="price-amount">{{ formatPrice(apartment.price_per_night || apartment.price || 0) }} р</div>
      </div>

      <div class="dates-section">
        <div class="date-item">
          <span class="date-label">Прибытие</span>
          <input 
            type="date" 
            v-model="checkInDate" 
            :min="minDate"
            class="date-input"
            @change="updateDates"
          >
        </div>
        <div class="date-item">
          <span class="date-label">Выезд</span>
          <input 
            type="date" 
            v-model="checkOutDate" 
            :min="minCheckOutDate"
            class="date-input"
            @change="updateDates"
          >
        </div>
        <div class="date-item">
          <span class="date-label">Для кого?</span>
          <input 
            type="number" 
            v-model.number="guests" 
            min="1" 
            :max="apartment.max_guests || 10"
            class="guests-input"
            @change="updateGuests"
          >
        </div>
      </div>
      
      <div v-if="checkInDate && checkOutDate" class="total-section">
        <div class="total-label">Итого:</div>
        <div class="total-amount">{{ formatPrice(totalAmount) }} ₽</div>
      </div>

      <button @click="goToCheckout" class="btn btn-primary">Забронировать</button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    apartment: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      checkInDate: '',
      checkOutDate: '',
      guests: 1
    }
  },
  computed: {
    totalAmount() {
      if (!this.checkInDate || !this.checkOutDate) return 0
      const start = new Date(this.checkInDate)
      const end = new Date(this.checkOutDate)
      const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24))
      return nights * (this.apartment.price_per_night || this.apartment.price || 0)
    },
    minDate() {
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      return today.toISOString().split('T')[0]
    },
    minCheckOutDate() {
      if (!this.checkInDate) return this.minDate
      const checkIn = new Date(this.checkInDate)
      checkIn.setDate(checkIn.getDate() + 1)
      return checkIn.toISOString().split('T')[0]
    }
  },
  methods: {
    goToCheckout() {
      if (!this.checkInDate || !this.checkOutDate) {
        alert('Пожалуйста, выберите даты заезда и выезда')
        return
      }
      if (this.guests < 1) {
        alert('Количество гостей должно быть не менее 1')
        return
      }
      if (this.guests > (this.apartment.max_guests || 10)) {
        alert(`Максимальное количество гостей: ${this.apartment.max_guests || 10}`)
        return
      }
      const params = new URLSearchParams({
        apartment: this.apartment.id,
        check_in: this.checkInDate,
        check_out: this.checkOutDate,
        guests: this.guests
      })
      this.$router.push(`/checkout?${params.toString()}`)
    },
    formatPrice(price) {
      if (!price) return '0'
      return new Intl.NumberFormat('ru-RU').format(price)
    },
    updateDates() {
      // Валидация дат
      if (this.checkInDate && this.checkOutDate) {
        const checkIn = new Date(this.checkInDate)
        const checkOut = new Date(this.checkOutDate)
        if (checkOut <= checkIn) {
          alert('Дата выезда должна быть позже даты заезда')
          this.checkOutDate = ''
        }
      }
    },
    updateGuests() {
      if (this.guests < 1) {
        this.guests = 1
      }
      if (this.guests > (this.apartment.max_guests || 10)) {
        this.guests = this.apartment.max_guests || 10
      }
    }
  },
  mounted() {
    // Получаем параметры из URL, если они есть
    const query = this.$route.query
    if (query.check_in) this.checkInDate = query.check_in
    if (query.check_out) this.checkOutDate = query.check_out
    if (query.guests) this.guests = parseInt(query.guests) || 1
  }
}
</script>

<style scoped>
.booking-panel {
  position: relative;
}

.panel-sticky {
  position: -webkit-sticky; /* Для Safari */
  position: sticky;
  top: 20px;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  padding: 20px;
  background: white;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  z-index: 100;
  align-self: start;
  max-height: calc(100vh - 40px);
  overflow-y: auto;
  transition: top 0.3s ease;
  will-change: transform;
}

.price-section {
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #E0E0E0;
}

.price-amount {
  font-size: 24px;
  font-weight: bold;
  color: #000;
}

.dates-section {
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #E0E0E0;
}

.date-item {
  display: flex;
  flex-direction: column;
  margin-bottom: 15px;
  font-size: 14px;
}

.date-item:last-child {
  margin-bottom: 0;
}

.date-label {
  font-size: 12px;
  color: #666;
  margin-bottom: 5px;
}

.date-value {
  font-size: 14px;
  color: #000;
  font-weight: 500;
}

.date-input,
.guests-input {
  width: 100%;
  padding: 8px;
  border: 1px solid #E0E0E0;
  border-radius: 4px;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
  margin-top: 5px;
}

.date-input:focus,
.guests-input:focus {
  outline: none;
  border-color: var(--accent);
}

.total-section {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #E0E0E0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.total-label {
  font-size: 14px;
  color: #666;
}

.total-amount {
  font-size: 20px;
  font-weight: bold;
  color: #000;
}

.btn {
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
  font-weight: bold;
}

.btn-primary {
  background: var(--accent);
  color: #000;
}

.btn-primary:hover {
  opacity: 0.9;
}
</style>
