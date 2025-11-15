<template>
  <div class="admin-layout">
    <div v-if="loading" class="loading">Проверка доступа...</div>
    <div v-else-if="!hasAccess" class="access-denied">
      <h2>Доступ запрещен</h2>
      <p>Только администраторы могут просматривать эту страницу</p>
      <button @click="$router.push('/')" class="btn btn-primary">На главную</button>
    </div>
    <div v-else class="admin-container">
      <div class="admin-header">
        <h1>Панель администратора</h1>
        <nav class="admin-tabs">
          <router-link 
            v-for="tab in tabs"
            :key="tab.id"
            :to="tab.route"
            class="tab-btn"
            :class="{ active: $route.path === tab.route }"
          >
            {{ tab.label }}
          </router-link>
        </nav>
      </div>
      
      <div class="admin-content-wrapper">
        <router-view></router-view>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../axios.js'

export default {
  name: 'AdminLayout',
  data() {
    return {
      tabs: [
        { id: 'apartments', label: 'Апартаменты', route: '/admin/apartments' },
        { id: 'calendar', label: 'Календарь', route: '/admin/calendar' },
        { id: 'notifications', label: 'Уведомления', route: '/admin/notifications' },
        { id: 'renters', label: 'Арендаторы', route: '/admin/renters' }
      ],
      authUser: null,
      loading: true,
      hasAccess: false
    }
  },
  async mounted() {
    await this.checkAdminAccess()
  },
  methods: {
    async checkAdminAccess() {
      try {
        console.log('🔐 Проверка доступа к админке...')
        const response = await axios.get('/api/check-auth')
        this.authUser = response.data.user
        console.log('👤 Получен пользователь:', this.authUser)
        
        // Проверяем, что пользователь админ
        if (this.authUser && this.authUser.role === 'admin') {
          console.log('✅ Доступ разрешен')
          this.hasAccess = true
          
          // Если мы на /admin, перенаправляем на апартаменты
          if (this.$route.path === '/admin') {
            this.$router.replace('/admin/apartments')
          }
        } else {
          console.log('❌ Доступ запрещен. Роль:', this.authUser?.role)
          this.hasAccess = false
          this.$router.push('/')
        }
      } catch (error) {
        console.error('❌ Ошибка проверки доступа:', error)
        this.hasAccess = false
        this.$router.push('/')
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.admin-layout {
  min-height: calc(100vh - 140px);
  background: #f8f9fa;
  padding: 20px 0;
}

.admin-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.admin-header {
  margin-bottom: 30px;
}

.admin-header h1 {
  font-size: 24px;
  margin-bottom: 20px;
  color: #000;
  text-align: center;
}

.admin-tabs {
  display: flex;
  background: white;
  border: 1px solid #E0E0E0;
  border-radius: 50px;
  padding: 8px;
  gap: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  max-width: 800px;
  margin: 0 auto;
}

.tab-btn {
  flex: 1;
  padding: 12px 24px;
  text-align: center;
  text-decoration: none;
  color: #666;
  border-radius: 30px;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
  background: transparent;
  border: none;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
}

.tab-btn:hover {
  background: #f5f5f5;
  color: #000;
  transform: translateY(-1px);
}

.tab-btn.active {
  background: #E0E0E0;
  color: #000;
  font-weight: 600;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.admin-content-wrapper {
  background: white;
  border-radius: 12px;
  border: 1px solid #E0E0E0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  min-height: 500px;
  padding: 30px;
}

.loading, .access-denied {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  text-align: center;
}

.access-denied h2 {
  color: #dc3545;
  margin-bottom: 10px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
}

.btn-primary {
  background: var(--accent);
  color: #000;
}

/* Адаптивность для мобильных */
@media (max-width: 768px) {
  .admin-tabs {
    flex-direction: column;
    border-radius: 12px;
    padding: 12px;
    gap: 6px;
  }
  
  .tab-btn {
    border-radius: 8px;
    padding: 10px 16px;
  }
  
  .admin-content-wrapper {
    padding: 20px;
    margin: 0 10px;
  }
  
  .admin-container {
    padding: 0 10px;
  }
}
</style>