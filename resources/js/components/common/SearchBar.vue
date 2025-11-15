<template>
  <div class="search-container">
    <div class="search-field">
      <label>Дата заезда</label>
      <input type="date" v-model="checkIn">
    </div>
    <div class="search-field">
      <label>Дата выезда</label>
      <input type="date" v-model="checkOut">
    </div>
    <div class="search-field">
      <label>Количество гостей</label>
      <input type="number" v-model="guests" min="1">
    </div>
    <button @click="search" class="btn btn-primary">Найти</button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      checkIn: '',
      checkOut: '',
      guests: 1
    }
  },
  methods: {
    search() {
      this.$emit('search', {
        check_in: this.checkIn,
        check_out: this.checkOut,
        guests: this.guests
      })
    }
  }
}
</script>

<style scoped>
.search-container {
  background: white;
  padding: 25px;
  border-radius: 12px;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  align-items: flex-end;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  border: 1px solid #E0E0E0;
  max-width: 1200px;
  margin: 0 auto;
}

.search-field {
  display: flex;
  flex-direction: column;
}

.search-field label {
  font-size: 13px;
  margin-bottom: 10px;
  color: #666;
  font-weight: 500;
}

.search-field input {
  padding: 12px;
  border: 1px solid #E0E0E0;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.search-field input:focus {
  outline: none;
  border-color: #999;
}

.btn {
  padding: 12px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-primary {
  background: var(--accent);
  color: #000;
}

.btn-primary:hover {
  background: #d0d0d0;
}

@media (max-width: 768px) {
  .search-container {
    grid-template-columns: 1fr;
  }
}
</style>