<template>
  <div class="admin-calendar">
    <div class="calendar-toolbar">
      <select v-model="selectedApartment" @change="onApartmentChange" class="apartment-select">
        <option value="">Выберите апартаменты</option>
        <option v-for="apt in apartments" :key="apt.id" :value="apt.id">
          {{ apt.name }} ({{ apt.address }})
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
        <div v-if="loading" class="loading-message">Загрузка...</div>
        <div v-else-if="filteredBookings.length === 0" class="empty-message">
          Нет бронирований в этом месяце
        </div>
        <div v-else>
          <div v-for="booking in filteredBookings" :key="booking.id" class="booking-item">
            <div class="booking-header">
              <strong>{{ booking.apartment?.name || 'Апартаменты' }}</strong>
              <span :class="['status-badge', booking.status]">
                {{ booking.status === 'paid' ? 'Оплачено' : 'Ожидает оплаты' }}
              </span>
            </div>
            <div class="booking-info">
              <div class="info-row">
                <span class="label">Даты:</span>
                <span>{{ formatDateDisplay(booking.check_in) }} - {{ formatDateDisplay(booking.check_out) }}</span>
              </div>
              <div class="info-row">
                <span class="label">Арендатор:</span>
                <span>{{ formatFullName(booking.user) }}</span>
              </div>
              <div class="info-row">
                <span class="label">Телефон:</span>
                <span>{{ booking.user.phone || '-' }}</span>
              </div>
              <div class="info-row">
                <span class="label">Почта:</span>
                <span>{{ booking.user.email }}</span>
              </div>
              <div class="info-row" v-if="booking.guests">
                <span class="label">Гостей:</span>
                <span>{{ booking.guests }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="bookings-list empty-state">
        <p>Выберите апартаменты для просмотра календаря занятости</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../../axios.js'

export default {
  data() {
    return {
      apartments: [],
      selectedApartment: '',
      bookings: [],
      currentDate: new Date(),
      weekDays: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
      loading: false
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
      
      // Исправляем день недели (0 = воскресенье, нужно чтобы понедельник был 0)
      let firstDayOfWeek = firstDay.getDay()
      if (firstDayOfWeek === 0) firstDayOfWeek = 7 // Воскресенье становится 7
      firstDayOfWeek = firstDayOfWeek - 1 // Понедельник = 0
      
      // Previous month days
      for (let i = firstDayOfWeek - 1; i >= 0; i--) {
        days.push({
          date: prevLastDay.getDate() - i,
          month: month - 1,
          year: month === 0 ? year - 1 : year,
          otherMonth: true,
          status: 'available',
          fullDate: new Date(month === 0 ? year - 1 : year, month - 1, prevLastDay.getDate() - i)
        })
      }
      
      // Current month days
      for (let i = 1; i <= lastDay.getDate(); i++) {
        const dayDate = new Date(year, month, i)
        days.push({
          date: i,
          month: month,
          year: year,
          otherMonth: false,
          status: this.getDayStatus(dayDate),
          fullDate: dayDate
        })
      }
      
      // Next month days
      const remainingDays = 42 - days.length
      for (let i = 1; i <= remainingDays; i++) {
        days.push({
          date: i,
          month: month + 1,
          year: month === 11 ? year + 1 : year,
          otherMonth: true,
          status: 'available',
          fullDate: new Date(month === 11 ? year + 1 : year, month + 1, i)
        })
      }
      
      return days
    },
    filteredBookings() {
      // Фильтруем бронирования для текущего месяца
      const year = this.currentDate.getFullYear()
      const month = this.currentDate.getMonth()
      const startOfMonth = new Date(year, month, 1)
      const endOfMonth = new Date(year, month + 1, 0)
      
      return this.bookings.filter(booking => {
        const checkIn = new Date(booking.check_in)
        const checkOut = new Date(booking.check_out)
        
        // Показываем бронирования, которые пересекаются с текущим месяцем
        return (checkIn <= endOfMonth && checkOut >= startOfMonth)
      }).sort((a, b) => {
        return new Date(a.check_in) - new Date(b.check_in)
      })
    }
  },
  watch: {
    currentDate() {
      // При смене месяца обновляем отображение календаря
    }
  },
  methods: {
    async fetchApartments() {
      try {
        const response = await axios.get('/api/apartments')
        this.apartments = response.data.map(apt => ({
          id: apt.id,
          name: apt.name,
          address: apt.address
        }))
      } catch (error) {
        console.error('Error fetching apartments:', error)
        alert('Ошибка загрузки списка апартаментов')
      }
    },
    
    async fetchBookings() {
      if (!this.selectedApartment) return
      
      this.loading = true
      try {
        const response = await axios.get('/admin/calendar/data', {
          params: {
            apartment_id: this.selectedApartment
          }
        })
        this.bookings = response.data
        console.log('Bookings loaded:', this.bookings)
      } catch (error) {
        console.error('Error fetching bookings:', error)
        // Пробуем альтернативный путь
        try {
          const response = await axios.get('/api/bookings/calendar', {
            params: {
              apartment_id: this.selectedApartment
            }
          })
          this.bookings = response.data
        } catch (error2) {
          alert('Ошибка загрузки данных календаря: ' + (error2.response?.data?.message || error2.message))
        }
      } finally {
        this.loading = false
      }
    },
    
    previousMonth() {
      this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1)
    },
    
    nextMonth() {
      this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1)
    },
    
    getDayStatus(date) {
      if (!this.selectedApartment || this.bookings.length === 0) {
        return 'available'
      }
      
      // Нормализуем дату (убираем время, оставляем только дату)
      const normalizedDate = new Date(date.getFullYear(), date.getMonth(), date.getDate())
      const dateTime = normalizedDate.getTime()
      
      // Проверяем, попадает ли дата в какой-либо период бронирования
      for (const booking of this.bookings) {
        const checkIn = new Date(booking.check_in)
        const checkOut = new Date(booking.check_out)
        
        // Нормализуем даты бронирования
        const normalizedCheckIn = new Date(checkIn.getFullYear(), checkIn.getMonth(), checkIn.getDate())
        const normalizedCheckOut = new Date(checkOut.getFullYear(), checkOut.getMonth(), checkOut.getDate())
        
        const checkInTime = normalizedCheckIn.getTime()
        const checkOutTime = normalizedCheckOut.getTime()
        
        // Проверяем, попадает ли дата в период бронирования (включая день выезда)
        if (dateTime >= checkInTime && dateTime <= checkOutTime) {
          if (booking.status === 'paid') {
            return 'booked' // Занято (оплачено)
          } else if (booking.status === 'pending') {
            return 'reserved' // Забронировано (ожидает оплаты)
          }
        }
      }
      
      return 'available'
    },
    
    formatDate(date) {
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      return `${year}-${month}-${day}`
    },
    
    formatFullName(user) {
      if (user.full_name && user.full_name.trim()) {
        return user.full_name.trim()
      }
      if (user.last_name || user.first_name || user.patronymic) {
        return [user.last_name, user.first_name, user.patronymic].filter(Boolean).join(' ').trim()
      }
      return user.name || 'Не указано'
    },
    
    formatDateDisplay(dateStr) {
      const date = new Date(dateStr)
      const day = String(date.getDate()).padStart(2, '0')
      const month = String(date.getMonth() + 1).padStart(2, '0')
      return `${day}.${month}.${date.getFullYear()}`
    },
    
    onApartmentChange() {
      if (this.selectedApartment) {
        this.fetchBookings()
      } else {
        this.bookings = []
      }
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
  font-weight: bold;
  position: relative;
}

.calendar-day.booked::after {
  content: '';
  position: absolute;
  bottom: 2px;
  left: 50%;
  transform: translateX(-50%);
  width: 6px;
  height: 6px;
  background: #721C24;
  border-radius: 50%;
}

.calendar-day.reserved {
  background: #D1ECF1;
  color: #0C5460;
  font-weight: bold;
  position: relative;
}

.calendar-day.reserved::after {
  content: '';
  position: absolute;
  bottom: 2px;
  left: 50%;
  transform: translateX(-50%);
  width: 6px;
  height: 6px;
  background: #0C5460;
  border-radius: 50%;
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
  padding: 15px;
  border: 1px solid #E0E0E0;
  border-radius: 6px;
  margin-bottom: 12px;
  background: white;
  transition: box-shadow 0.2s;
}

.booking-item:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.booking-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  padding-bottom: 8px;
  border-bottom: 1px solid #F0F0F0;
}

.booking-header strong {
  font-size: 14px;
  color: #333;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: bold;
}

.status-badge.paid {
  background: #D4EDDA;
  color: #155724;
}

.status-badge.pending {
  background: #D1ECF1;
  color: #0C5460;
}

.booking-info {
  font-size: 12px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 6px;
  padding: 4px 0;
}

.info-row .label {
  color: #666;
  font-weight: 500;
}

.info-row span:not(.label) {
  color: #000;
  text-align: right;
}

.loading-message,
.empty-message {
  text-align: center;
  padding: 40px 20px;
  color: #999;
  font-size: 14px;
}

.empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  color: #999;
  font-size: 14px;
  text-align: center;
}

@media (max-width: 1200px) {
  .calendar-container {
    grid-template-columns: 1fr;
  }
}
</style>
