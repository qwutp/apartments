<template>
  <div class="modal-overlay" @click.self="close">
    <div class="modal">
      <div class="modal-header">
        <h2>Информация об арендаторе</h2>
        <button @click="close" class="close-btn">✕</button>
      </div>

      <div class="modal-body">
        <div class="info-section">
          <h3>Личные данные</h3>
          <div class="info-row">
            <span>ФИО:</span>
            <strong>{{ formatFullName() }}</strong>
          </div>
          <div class="info-row">
            <span>Почта:</span>
            <strong>{{ renter.email }}</strong>
          </div>
          <div class="info-row">
            <span>Телефон:</span>
            <strong>{{ renter.phone || '-' }}</strong>
          </div>
        </div>

        <div class="info-section">
          <h3>Документы</h3>
          <div class="info-row">
            <span>Паспорт:</span>
            <strong>{{ formatPassport() }}</strong>
          </div>
        </div>

        <div class="info-section">
          <h3>Статистика</h3>
          <div class="info-row">
            <span>Прошлых бронирований:</span>
            <strong>{{ renter.past_booking_count || renter.booking_count || 0 }}</strong>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button @click="close" class="btn btn-secondary">Закрыть</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    renter: {
      type: Object,
      required: true
    }
  },
  methods: {
    close() {
      this.$emit('close')
    },
    formatFullName() {
      if (this.renter.last_name || this.renter.first_name || this.renter.patronymic) {
        return [this.renter.last_name, this.renter.first_name, this.renter.patronymic].filter(Boolean).join(' ').trim()
      }
      return this.renter.name || 'Не указано'
    },
    formatPassport() {
      if (this.renter.passport_series && this.renter.passport_number) {
        return `${this.renter.passport_series} ${this.renter.passport_number}`
      }
      return '-'
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #E0E0E0;
}

.modal-header h2 {
  font-size: 18px;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
}

.modal-body {
  padding: 20px;
  max-height: 400px;
  overflow-y: auto;
}

.info-section {
  margin-bottom: 20px;
}

.info-section h3 {
  font-size: 14px;
  font-weight: bold;
  margin-bottom: 10px;
  color: #333;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #F0F0F0;
  font-size: 13px;
}

.info-row span {
  color: #666;
}

.info-row strong {
  color: #000;
  font-weight: 600;
}

.modal-footer {
  padding: 15px 20px;
  border-top: 1px solid #E0E0E0;
  display: flex;
  gap: 10px;
}

.btn {
  flex: 1;
  padding: 10px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
  font-size: 13px;
}

.btn-secondary {
  background: #E0E0E0;
  color: #000;
}
</style>
