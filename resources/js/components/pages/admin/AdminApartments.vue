<template>
  <div class="admin-apartments">
    <div class="toolbar">
      <input 
        v-model="searchQuery" 
        placeholder="Поиск по названию или адресу"
        class="search-input"
      >
      <button @click="goToCreate" class="btn btn-primary">+ Добавить апартаменты</button>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Название</th>
            <th>Адрес</th>
            <th>Цена за ночь</th>
            <th>Статус</th>
            <th>Комнат</th>
            <th>Гостей</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="apartment in filteredApartments" :key="apartment.id">
            <td>{{ apartment.name }}</td>
            <td>{{ apartment.address }}</td>
            <td>{{ apartment.price_per_night }} ₽</td>
            <td>
              <span :class="['status', apartment.status]">
                {{ getStatusText(apartment.status) }}
              </span>
            </td>
            <td>{{ apartment.rooms }}</td>
            <td>{{ apartment.max_guests }}</td>
            <td class="actions">
              <button @click="editApartment(apartment.id)" class="btn-icon">✏️</button>
              <button 
                v-if="apartment.status === 'available'"
                @click="deleteApartment(apartment.id)" 
                class="btn-icon danger"
              >
                🗑️
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      
      <div v-if="loading" class="loading">Загрузка...</div>
      <div v-if="!loading && filteredApartments.length === 0" class="empty">
        {{ searchQuery ? 'Апартаментов не найдено' : 'Апартаментов нет' }}
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
      searchQuery: '',
      loading: false
    }
  },
  computed: {
    filteredApartments() {
      if (!this.searchQuery) {
        return this.apartments
      }
      
      const query = this.searchQuery.toLowerCase()
      return this.apartments.filter(apt => 
        apt.name.toLowerCase().includes(query) ||
        apt.address.toLowerCase().includes(query)
      )
    }
  },
  mounted() {
    this.fetchApartments()
  },
  methods: {
    async fetchApartments() {
      this.loading = true
      try {
        const response = await axios.get('/api/apartments')
        this.apartments = response.data
        console.log('Loaded apartments:', this.apartments)
      } catch (error) {
        console.error('Error fetching apartments:', error)
        alert('Ошибка загрузки апартаментов')
      } finally {
        this.loading = false
      }
    },
    
    getStatusText(status) {
      const statusMap = {
        'available': 'Свободно',
        'booked': 'Забронировано', 
        'occupied': 'Занято'
      }
      return statusMap[status] || status
    },
    
    goToCreate() {
      console.log('➡️ Creating new apartment')
      this.$router.push('/admin/apartment/create')
    },
  
    editApartment(id) {
      console.log('✏️ Editing apartment:', id)
      this.$router.push(`/admin/apartment/edit/${id}`)
    },
    
    async deleteApartment(id) {
  if (!confirm('Вы уверены, что хотите удалить эти апартаменты?')) {
    return
  }

  try {
    console.log('🗑️ Deleting apartment:', id)
    
    const response = await axios.delete(`/api/apartments/${id}`)
    
    console.log('✅ Delete response:', response.data)
    
    if (response.data.success) {
      this.fetchApartments()
      alert('Апартаменты успешно удалены')
    } else {
      alert('Ошибка при удалении: ' + (response.data.message || 'Неизвестная ошибка'))
    }
    
  } catch (error) {
    console.error('❌ Delete error:', error)
    
    // НЕ обрабатываем 419/401 - axios интерцептор сделает это
    if (error.response?.data?.message && error.response.status !== 419 && error.response.status !== 401) {
      alert('Ошибка: ' + error.response.data.message)
    }
  }
}
  }
}
</script>

<style scoped>
.admin-apartments {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.toolbar {
  display: flex;
  gap: 10px;
}

.search-input {
  flex: 1;
  padding: 10px;
  border: 1px solid #E0E0E0;
  border-radius: 4px;
  font-family: 'Unbounded', sans-serif;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
}

.btn-primary {
  background: var(--accent);
  color: #000;
}

.table-container {
  overflow-x: auto;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

thead {
  background: #F5F5F5;
}

th {
  padding: 12px;
  text-align: left;
  font-weight: bold;
  border-bottom: 1px solid #E0E0E0;
}

td {
  padding: 12px;
  border-bottom: 1px solid #E0E0E0;
}

tr:hover {
  background: #F9F9F9;
}

.status {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: bold;
}

.status.available {
  background: #D4EDDA;
  color: #155724;
}

.status.booked {
  background: #D1ECF1;
  color: #0C5460;
}

.status.occupied {
  background: #F8D7DA;
  color: #721C24;
}

.actions {
  display: flex;
  gap: 8px;
}

.btn-icon {
  width: 30px;
  height: 30px;
  border: none;
  background: white;
  cursor: pointer;
  border-radius: 4px;
  font-size: 16px;
  transition: all 0.2s;
}

.btn-icon:hover {
  background: #E0E0E0;
}

.btn-icon.danger:hover {
  background: #FFE0E0;
}

.loading, .empty {
  text-align: center;
  padding: 40px;
  color: #999;
}
</style>