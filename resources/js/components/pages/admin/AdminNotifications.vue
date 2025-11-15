<template>
  <div class="admin-notifications">
    <h2>Уведомления о новых бронированиях</h2>
    <div v-if="notifications.length === 0" class="empty">
      Нет новых уведомлений
    </div>
    <div v-else class="notifications-list">
      <div v-for="notif in notifications" :key="notif.id" class="notification-card">
        <div class="notification-header">
          <div class="user-info">
            <p><strong>{{ notif.booking.user.name }}</strong></p>
            <p>{{ notif.booking.user.email }} | {{ notif.booking.user.phone }}</p>
          </div>
          <div class="apartment-info">
            <p><strong>{{ notif.booking.apartment.name }}</strong></p>
            <p>{{ notif.booking.apartment.address }}</p>
          </div>
        </div>
        
        <div class="notification-body">
          <p>Даты: {{ notif.booking.check_in }} - {{ notif.booking.check_out }}</p>
          <p>Сумма: {{ notif.booking.total_amount }} ₽</p>
          <p class="status">Статус: <span class="paid">Оплачено</span></p>
          <p class="created">Создано: {{ notif.created_at }}</p>
        </div>

        <div class="notification-actions">
          <button @click="rejectBooking(notif.booking.id)" class="btn btn-danger">
            Отказать в брони
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      notifications: []
    }
  },
  mounted() {
    this.fetchNotifications()
  },
  methods: {
    fetchNotifications() {
      // API call
    },
    rejectBooking(bookingId) {
      if (confirm('Отменить бронь?')) {
        // API call to reject
      }
    }
  }
}
</script>

<style scoped>
.admin-notifications {
  padding: 20px;
}

.admin-notifications h2 {
  margin-bottom: 20px;
}

.empty {
  text-align: center;
  padding: 40px;
  color: #999;
}

.notifications-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.notification-card {
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  padding: 20px;
  background: white;
}

.notification-header {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 15px;
  padding-bottom: 15px;
  border-bottom: 1px solid #E0E0E0;
}

.user-info p,
.apartment-info p {
  margin: 3px 0;
  font-size: 13px;
}

.user-info p:first-child,
.apartment-info p:first-child {
  font-weight: bold;
}

.notification-body {
  margin-bottom: 15px;
  font-size: 13px;
}

.notification-body p {
  margin: 5px 0;
}

.status {
  display: flex;
  align-items: center;
  gap: 8px;
}

.paid {
  background: #D4EDDA;
  color: #155724;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: bold;
}

.created {
  color: #999;
  font-size: 12px;
}

.notification-actions {
  display: flex;
  gap: 10px;
}

.btn {
  padding: 8px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
  font-size: 13px;
}

.btn-danger {
  background: #FF6B6B;
  color: white;
}
</style>
