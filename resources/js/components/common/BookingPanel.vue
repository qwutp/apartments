<template>
  <div class="booking-panel">
    <div class="panel-sticky">
      <div class="total-section">
        <p>Сумма к оплате:</p>
        <p class="amount">{{ totalAmount }} ₽</p>
      </div>

      <div class="dates-section">
        <p>
          <strong>Заезд:</strong> {{ checkInDate }}
        </p>
        <p>
          <strong>Выезд:</strong> {{ checkOutDate }}
        </p>
        <p>
          <strong>Гостей:</strong> {{ guests }}
        </p>
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
      return nights * this.apartment.price
    }
  },
  methods: {
    goToCheckout() {
      window.location.href = `/checkout?apartment=${this.apartment.id}`
    }
  }
}
</script>

<style scoped>
.booking-panel {
  position: relative;
}

.panel-sticky {
  position: sticky;
  top: 20px;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  padding: 20px;
  background: white;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.total-section {
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #E0E0E0;
}

.total-section p:first-child {
  font-size: 14px;
  color: #666;
}

.amount {
  font-size: 24px;
  font-weight: bold;
  color: var(--accent);
  margin-top: 5px;
}

.dates-section {
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #E0E0E0;
  font-size: 13px;
}

.dates-section p {
  margin: 8px 0;
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
