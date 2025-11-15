<template>
  <div class="favorites-page">
    <h2>Избранное</h2>
    
    <div class="empty">
      Вы пока не добавили апартаменты в избранное
    </div>
  </div>
</template>

<script>
export default {
  name: 'Favorites',
  data() {
    return {
      apartments: []
    }
  },
  mounted() {
    this.fetchFavorites()
  },
  methods: {
    fetchFavorites() {
      // Будет загрузка из API
      this.apartments = []
    }
  }
}
</script>

<style scoped>
.favorites-page {
  padding: 30px;
}

.favorites-page h2 {
  font-size: 24px;
  margin-bottom: 30px;
}

.empty {
  text-align: center;
  padding: 60px 20px;
  color: #999;
  font-size: 16px;
}

.apartments-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}
</style>