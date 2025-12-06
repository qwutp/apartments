<template>
  <div class="payment-confirmation">
    <div class="confirmation-card" v-if="!loading && booking">
      <div class="header">
        <h1>Подтверждение оплаты</h1>
        <p>Пожалуйста, проверьте детали бронирования перед оплатой</p>
      </div>

      <div class="booking-summary">
        <div class="apartment-info">
          <img v-if="booking.apartment.image" :src="booking.apartment.image" :alt="booking.apartment.name">
          <div class="apartment-text">
            <h3>{{ booking.apartment.name }}</h3>
            <p>{{ booking.apartment.address }}</p>
          </div>
        </div>

        <div class="details-grid">
          <div>
            <span class="label">Дата заезда</span>
            <strong>{{ formatDate(booking.check_in) }}</strong>
          </div>
          <div>
            <span class="label">Дата выезда</span>
            <strong>{{ formatDate(booking.check_out) }}</strong>
          </div>
          <div>
            <span class="label">Гостей</span>
            <strong>{{ booking.guests }}</strong>
          </div>
          <div>
            <span class="label">Ночей</span>
            <strong>{{ nightsCount }}</strong>
          </div>
        </div>

        <div class="total">
          <span>К оплате</span>
          <strong>{{ formatPrice(booking.total_price) }}</strong>
        </div>
      </div>

      <div class="actions">
        <button class="btn btn-secondary" @click="backToApartment">Вернуться к выбору</button>
        <button class="btn btn-primary" :disabled="paying" @click="pay">
          {{ paying ? 'Перенаправляем...' : 'Оплатить и забронировать' }}
        </button>
      </div>
      <p class="safe-note">Оплата проходит в безопасном тестовом окружении ЮKassa.</p>
    </div>

    <div v-else class="state-card">
      <p v-if="loading">Загружаем данные бронирования...</p>
      <p v-else>Бронирование не найдено или доступ запрещен.</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'PaymentConfirmation',
  data() {
    return {
      booking: null,
      loading: true,
      paying: false
    }
  },
  computed: {
    nightsCount() {
      if (!this.booking) return 0
      const start = new Date(this.booking.check_in)
      const end = new Date(this.booking.check_out)
      return Math.max(1, Math.ceil((end - start) / (1000 * 60 * 60 * 24)))
    }
  },
  methods: {
    async fetchBooking() {
      this.loading = true
      try {
        const response = await axios.get(`/api/bookings/${this.$route.params.id}`)
        this.booking = response.data.booking
      } catch (error) {
        console.error('Ошибка загрузки бронирования', error)
        if (error.response?.status === 401) {
          alert('Авторизуйтесь, чтобы продолжить оплату.')
          this.$router.push('/login')
          return
        }
      } finally {
        this.loading = false
      }
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('ru-RU')
    },
    formatPrice(price) {
      if (!price) return '0 ₽'
      return new Intl.NumberFormat('ru-RU').format(price) + ' ₽'
    },
    backToApartment() {
      if (this.booking?.apartment?.id) {
        this.$router.push(`/apartment/${this.booking.apartment.id}`)
      } else {
        this.$router.push('/')
      }
    },
    async pay() {
      if (!this.booking) return
      this.paying = true
      try {
        const response = await axios.post(`/api/bookings/${this.booking.id}/payments`)
        if (response.data?.confirmation_url) {
          window.location.href = response.data.confirmation_url
        } else {
          alert('Не удалось создать оплату. Попробуйте позже.')
        }
      } catch (error) {
        console.error('Ошибка при создании платежа', error)
        if (error.response?.status === 401) {
          alert('Сессия истекла. Пожалуйста, войдите снова.')
          this.$router.push('/login')
          return
        }
        const message = error.response?.data?.message || 'Не удалось начать оплату. Попробуйте позже.'
        alert(message)
      } finally {
        this.paying = false
      }
    }
  },
  mounted() {
    this.fetchBooking()
  }
}
</script>

<style scoped>
.payment-confirmation {
  min-height: calc(100vh - 160px);
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 40px 20px;
  background: #F5F6FA;
}

.confirmation-card {
  width: 100%;
  max-width: 800px;
  background: #fff;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 20px 60px rgba(15, 26, 58, 0.08);
}

.header h1 {
  font-size: 28px;
  margin-bottom: 10px;
}

.header p {
  color: #777;
  margin-bottom: 30px;
}

.apartment-info {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 25px;
}

.apartment-info img {
  width: 120px;
  height: 120px;
  border-radius: 12px;
  object-fit: cover;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 20px;
  margin-bottom: 25px;
}

.label {
  font-size: 12px;
  color: #999;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-radius: 12px;
  background: #F8F9FC;
  font-size: 18px;
}

.total strong {
  font-size: 24px;
  color: var(--accent);
}

.actions {
  margin-top: 30px;
  display: flex;
  gap: 15px;
}

.btn {
  flex: 1;
  padding: 14px;
  border: none;
  border-radius: 10px;
  font-size: 16px;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.btn:hover {
  transform: translateY(-1px);
}

.btn-primary {
  background: var(--accent);
  color: #000;
  font-weight: 600;
}

.btn-secondary {
  background: #F0F0F5;
  color: #555;
}

.safe-note {
  margin-top: 15px;
  font-size: 13px;
  color: #999;
  text-align: center;
}

.state-card {
  background: white;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(15, 26, 58, 0.08);
}

@media (max-width: 768px) {
  .confirmation-card {
    padding: 25px;
  }

  .actions {
    flex-direction: column;
  }
}
</style>

