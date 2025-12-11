<template>
  <div class="home">
    <SearchBar @search="handleSearch" />
    
    <div class="apartments-container">
      <ApartmentGrid :apartments="filteredApartments" />
    </div>
    
    <div v-if="loading" class="loading">Загрузка апартаментов...</div>
    <div v-if="!loading && filteredApartments.length === 0" class="empty">
      Апартаментов не найдено
    </div>
  </div>
</template>

<script>
import SearchBar from '../common/SearchBar.vue'
import ApartmentGrid from '../common/ApartmentGrid.vue'
import axios from '../../axios.js'

export default {
  components: {
    SearchBar,
    ApartmentGrid
  },
  data() {
    return {
      apartments: [],
      loading: false,
      searchParams: null
    }
  },
  computed: {
    filteredApartments() {
      if (!this.searchParams || (!this.searchParams.check_in && !this.searchParams.check_out && !this.searchParams.guests)) {
        return this.apartments
      }
      // Фильтрация будет происходить на сервере через apiSearch
      return this.apartments
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
        console.log('Loaded apartments for home:', this.apartments)
      } catch (error) {
        console.error('Error fetching apartments:', error)
      } finally {
        this.loading = false
      }
    },
    
    async handleSearch(params) {
      this.searchParams = params
      console.log('Search params:', params)
      
      // Если есть параметры поиска, делаем запрос на сервер
      if (params.check_in || params.check_out || params.guests) {
        this.loading = true
        try {
          const queryParams = new URLSearchParams()
          if (params.check_in) queryParams.append('check_in', params.check_in)
          if (params.check_out) queryParams.append('check_out', params.check_out)
          if (params.guests) queryParams.append('guests', params.guests)
          
          const response = await axios.get(`/api/apartments/search?${queryParams.toString()}`)
          this.apartments = response.data
          console.log('Search results:', this.apartments)
        } catch (error) {
          console.error('Error searching apartments:', error)
          alert('Ошибка при поиске апартаментов')
        } finally {
          this.loading = false
        }
      } else {
        // Если параметров нет, загружаем все апартаменты
        this.fetchApartments()
      }
    }
  }
}
</script>

<style scoped>
.home {
  padding: 20px;
}

.apartments-container {
  max-width: 1300px;
  margin: 0 auto;
  padding: 0 20px;
}

.loading, .empty {
  text-align: center;
  padding: 40px;
  color: #999;
}
</style>