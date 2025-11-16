<template>
  <div class="admin-renters">
    <div class="toolbar">
      <input 
        v-model="searchQuery"
        @input="handleSearch"
        placeholder="Поиск по ФИО, почте или номеру телефона"
        class="search-input"
      >
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ФИО</th>
            <th>Почта</th>
            <th>Номер телефона</th>
            <th>Адрес занимаемых апартаментов</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="5" class="loading-cell">Загрузка...</td>
          </tr>
          <tr v-else-if="filteredRenters.length === 0">
            <td colspan="5" class="empty-cell">Пользователей не найдено</td>
          </tr>
          <tr v-else v-for="user in filteredRenters" :key="user.id">
            <td>{{ formatFullName(user) }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.phone || '-' }}</td>
            <td>{{ user.current_apartment?.address || '-' }}</td>
            <td class="actions">
              <button @click="viewDetails(user.id)" class="btn-icon" title="Просмотр полной информации">👁️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <RenterModal v-if="selectedRenter" :renter="selectedRenter" @close="selectedRenter = null" />
  </div>
</template>

<script>
import axios from '../../../axios.js'
import RenterModal from './RenterModal.vue'

export default {
  components: {
    RenterModal
  },
  data() {
    return {
      renters: [],
      searchQuery: '',
      selectedRenter: null,
      loading: false
    }
  },
  computed: {
    filteredRenters() {
      if (!this.searchQuery.trim()) {
        return this.renters
      }
      
      const query = this.searchQuery.toLowerCase()
      return this.renters.filter(user => {
        const fullName = this.formatFullName(user).toLowerCase()
        return fullName.includes(query) ||
               user.email?.toLowerCase().includes(query) ||
               user.phone?.toLowerCase().includes(query)
      })
    }
  },
  mounted() {
    this.fetchRenters()
  },
  methods: {
    async fetchRenters() {
      this.loading = true
      try {
        const response = await axios.get('/admin/users')
        this.renters = response.data
        console.log('Renters loaded:', this.renters)
      } catch (error) {
        console.error('Error fetching renters:', error)
        alert('Ошибка загрузки списка пользователей: ' + (error.response?.data?.message || error.message))
      } finally {
        this.loading = false
      }
    },
    
    handleSearch() {
      // Поиск выполняется через computed свойство filteredRenters
      // Если нужно искать на сервере, можно добавить debounce и вызывать API
    },
    
    async viewDetails(userId) {
      try {
        const response = await axios.get(`/admin/users/${userId}`)
        this.selectedRenter = response.data
      } catch (error) {
        console.error('Error fetching user details:', error)
        alert('Ошибка загрузки данных пользователя: ' + (error.response?.data?.message || error.message))
      }
    },
    
    formatFullName(user) {
      if (user.full_name && user.full_name.trim()) {
        return user.full_name.trim()
      }
      if (user.last_name || user.first_name || user.patronymic) {
        return [user.last_name, user.first_name, user.patronymic].filter(Boolean).join(' ').trim()
      }
      return user.name || 'Не указано'
    }
  }
}
</script>

<style scoped>
.admin-renters {
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

.loading-cell,
.empty-cell {
  text-align: center;
  padding: 40px;
  color: #999;
}
</style>
