<template>
  <div class="apartment-grid">
    <ApartmentCard 
      v-for="apt in apartments"
      :key="apt.id"
      :apartment="apt"
    />
  </div>
</template>

<script>
import ApartmentCard from './ApartmentCard.vue'

export default {
  components: { ApartmentCard },
  props: {
    apartments: Array
  }
}
</script>

<style scoped>
.apartment-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 25px; /* Увеличили отступ между карточками */
  margin-top: 20px; /* Отступ сверху от поиска */
}

@media (max-width: 1024px) {
  .apartment-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .apartment-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .apartment-grid {
    grid-template-columns: 1fr;
  }
}
</style>