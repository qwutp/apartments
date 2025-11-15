<template>
  <div class="admin-renters">
    <div class="toolbar">
      <input 
        v-model="searchQuery"
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
            <th>Апартаменты</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in renters" :key="user.id">
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.phone || '-' }}</td>
            <td>{{ user.current_apartment?.name || '-' }}</td>
            <td class="actions">
              <button @click="viewDetails(user.id)" class="btn-icon">👁️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <RenterModal v-if="selectedRenter" :renter="selectedRenter" @close="selectedRenter = null" />
  </div>
</template>

<script>
export default {
  data() {
    return {
      renters: [],
      searchQuery: '',
      selectedRenter: null
    }
  },
  mounted() {
    this.fetchRenters()
  },
  methods: {
    fetchRenters() {
      // API call
    },
    viewDetails(userId) {
      // Fetch and show details
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
</style>
