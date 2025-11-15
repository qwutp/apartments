<template>
  <div class="admin-calendar">
    <div class="calendar-toolbar">
      <select v-model="selectedApartment" class="apartment-select">
        <option value="">Выберите апартаменты</option>
        <option v-for="apt in apartments" :key="apt.id" :value="apt.id">
          {{ apt.name }}
        </option>
      </select>
      <div class="month-nav">
        <button @click="previousMonth">←</button>
        <span>{{ currentMonth }}</span>
        <button @click="nextMonth">→</button>
      </div>
    </div>

    <div class="calendar-container">
      <div class="calendar">
        <div class="calendar-header">
          <div v-for="day in weekDays" :key="day" class="day-header">
            {{ day }}
          </div>
        </div>
        <div class="calendar-body">
          <div 
            v-for="day in calendarDays" 
            :key="`${day.month}-${day.date}`"
            :class="['calendar-day', { 'other-month': day.otherMonth, [day.status]: true }]"
          >
            {{ day.date }}
          </div>
        </div>
      </div>

      <div class="bookings-list" v-if="selectedApartment">
        <h3>Бронирования</h3>
        <div v-for="booking in bookings" :key="booking.id" class="booking-item">
          <p><strong>{{ booking.apartment.name }}</strong></p>
          <p>{{ booking.check_in }} - {{ booking.check_out }}</p>
          <p>{{ booking.user.name }}</p>
          <p>{{ booking.user.email }} | {{ booking.user.phone }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      apartments: [],
      selectedApartment: '',
      bookings: [],
      currentDate: new Date(),
      weekDays: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']
    }
  },
  computed: {
    currentMonth() {
      const months = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь']
      return `${months[this.currentDate.getMonth()]} ${this.currentDate.getFullYear()}`
    },
    calendarDays() {
      const days = []
      const year = this.currentDate.getFullYear()
      const month = this.currentDate.getMonth()
      
      const firstDay = new Date(year, month, 1)
      const lastDay = new Date(year, month + 1, 0)
      const prevLastDay = new Date(year, month, 0)
      
      // Previous month days
      for (let i = firstDay.getDay() - 1; i >= 0; i--) {
        days.push({
          date: prevLastDay.getDate() - i,
          month: month - 1,
          otherMonth: true,
          status: 'available'
        })
      }
      
      // Current month days
      for (let i = 1; i <= lastDay.getDate(); i++) {
        days.push({
          date: i,
          month: month,
          otherMonth: false,
          status: this.getDayStatus(new Date(year, month, i))
        })
      }
      
      // Next month days
      for (let i = 1; i <= (42 - days.length); i++) {
        days.push({
          date: i,
          month: month + 1,
          otherMonth: true,
          status: 'available'
        })
      }
      
      return days
    }
  },
  methods: {
    previousMonth() {
      this.currentDate.setMonth(this.currentDate.getMonth() - 1)
      this.currentDate = new Date(this.currentDate)
    },
    nextMonth() {
      this.currentDate.setMonth(this.currentDate.getMonth() + 1)
      this.currentDate = new Date(this.currentDate)
    },
    getDayStatus(date) {
      // Check if day has bookings
      return 'available'
    }
  },
  mounted() {
    this.fetchApartments()
  }
}
</script>

<style scoped>
.admin-calendar {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.calendar-toolbar {
  display: flex;
  gap: 20px;
  align-items: center;
}

.apartment-select {
  flex: 1;
  padding: 10px;
  border: 1px solid #E0E0E0;
  border-radius: 4px;
  font-family: 'Unbounded', sans-serif;
}

.month-nav {
  display: flex;
  gap: 10px;
  align-items: center;
}

.month-nav button {
  width: 30px;
  height: 30px;
  border: 1px solid #E0E0E0;
  background: white;
  cursor: pointer;
  border-radius: 4px;
}

.month-nav span {
  min-width: 200px;
  text-align: center;
}

.calendar-container {
  display: grid;
  grid-template-columns: 1fr 350px;
  gap: 20px;
}

.calendar {
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  overflow: hidden;
}

.calendar-header {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  background: #E0E0E0;
}

.day-header {
  background: #F5F5F5;
  padding: 10px;
  text-align: center;
  font-weight: bold;
  font-size: 12px;
}

.calendar-body {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  background: #E0E0E0;
  padding: 1px;
}

.calendar-day {
  background: white;
  padding: 15px;
  text-align: center;
  min-height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 14px;
}

.calendar-day.other-month {
  background: #F9F9F9;
  color: #999;
}

.calendar-day.available:hover {
  background: #F0F0F0;
}

.calendar-day.booked {
  background: #FFE0E0;
  color: #721C24;
}

.calendar-day.reserved {
  background: #D1ECF1;
  color: #0C5460;
}

.bookings-list {
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  padding: 15px;
  max-height: 600px;
  overflow-y: auto;
}

.bookings-list h3 {
  margin-bottom: 15px;
}

.booking-item {
  padding: 12px;
  border: 1px solid #E0E0E0;
  border-radius: 4px;
  margin-bottom: 10px;
  font-size: 12px;
}

.booking-item p {
  margin: 3px 0;
}

@media (max-width: 1200px) {
  .calendar-container {
    grid-template-columns: 1fr;
  }
}
</style>
