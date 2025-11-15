<template>
  <div class="user-layout-container">
    <!-- Основной контент личного кабинета -->
    <div class="user-layout">
      <!-- Боковое меню -->
      <div class="sidebar">
        <h3 class="sidebar-title">Личный кабинет</h3>
        <div class="nav-items">
          <div 
            v-for="item in navItems"
            :key="item.id"
            @click="setActiveTab(item.id)"
            :class="['nav-item', { active: activeTab === item.id }]"
          >
            {{ item.label }}
          </div>
        </div>
      </div>

      <!-- Основной контент -->
      <div class="content">
        <ActiveBookings v-if="activeTab === 'active-bookings'" />
        <PastBookings v-else-if="activeTab === 'past-bookings'" />
        <Favorites v-else-if="activeTab === 'favorites'" />
        <UserProfile v-else />
      </div>
    </div>
  </div>
</template>

<script>
import ActiveBookings from '../pages/user/ActiveBookings.vue'
import PastBookings from '../pages/user/PastBookings.vue'
import Favorites from '../pages/user/Favorites.vue'
import UserProfile from '../pages/user/UserProfile.vue'

export default {
  name: 'UserLayout',
  components: {
    ActiveBookings,
    PastBookings,
    Favorites,
    UserProfile
  },
  data() {
    return {
      activeTab: 'active-bookings'
    }
  },
  computed: {
    navItems() {
      return [
        { id: 'active-bookings', label: 'Активные бронирования' },
        { id: 'past-bookings', label: 'Прошлые бронирования' },
        { id: 'favorites', label: 'Избранное' },
        { id: 'profile', label: 'Личные данные' }
      ]
    }
  },
  methods: {
    setActiveTab(tabId) {
      this.activeTab = tabId
    }
  }
}
</script>

<style scoped>
.user-layout-container {
  min-height: calc(100vh - 140px);
  padding: 20px 0;
}

.user-layout {
  display: flex;
  gap: 30px;
  min-height: 80vh;
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

.sidebar {
  width: 300px;
  background: white;
  border: 2px solid #E0E0E0;
  border-radius: 12px;
  padding: 25px;
  height: fit-content;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.sidebar-title {
  font-size: 20px;
  font-weight: bold;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 2px solid var(--accent);
  color: #333;
}

.nav-items {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-item {
  padding: 15px 20px;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.3s ease;
  font-size: 15px;
  border: 1px solid transparent;
}

.nav-item:hover {
  background: #f8f9fa;
  transform: translateX(5px);
  border-color: #E0E0E0;
}

.nav-item.active {
  background: var(--accent);
  color: #000;
  font-weight: bold;
  border-color: var(--accent);
  box-shadow: 0 2px 8px rgba(203, 206, 234, 0.4);
}

.content {
  flex: 1;
  background: white;
  border: 2px solid #E0E0E0;
  border-radius: 12px;
  min-height: 500px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  padding: 20px;
}
</style>