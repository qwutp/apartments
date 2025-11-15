<template>
  <div class="admin-edit-page">
    <div class="page-header">
      <h2>Редактирование апартаментов</h2>
      <button @click="$router.push('/admin/apartments')" class="btn btn-secondary">
        ← Назад к списку
      </button>
    </div>
    
    <div class="page-content">
      <p>Редактирование апартамента ID: {{ apartmentId }}</p>
      <p>Функциональность редактирования в разработке...</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminApartmentEdit',
  data() {
    return {
      apartmentId: null
    }
  },
  mounted() {
    this.apartmentId = this.$route.params.id
    console.log('Редактирование апартамента:', this.apartmentId)
  }
}
</script>

<style scoped>
.admin-edit-page {
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.page-header h2 {
  font-size: 20px;
  color: #000;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
}

.btn-secondary {
  background: #E0E0E0;
  color: #000;
}

.page-content {
  background: white;
  padding: 30px;
  border-radius: 8px;
  border: 1px solid #E0E0E0;
}
</style>