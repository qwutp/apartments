<template>
  <div class="apartment-form">
    <div class="page-header">
      <h2>{{ isEdit ? 'Редактирование апартаментов' : 'Добавление апартаментов' }}</h2>
      <button @click="goBack" class="btn btn-secondary">← Назад к списку</button>
    </div>
    
    <form @submit.prevent="submitForm">
      <div class="form-section">
        <h3>Основная информация</h3>
        <div class="form-group">
          <label>Название *</label>
          <input v-model="form.name" required>
        </div>
        <div class="form-group">
          <label>Адрес *</label>
          <input v-model="form.address" required>
        </div>
        <div class="form-group">
          <label>Цена за ночь (₽) *</label>
          <input v-model.number="form.price_per_night" type="number" required>
        </div>
      </div>

      <div class="form-section">
        <h3>Информация о квартире</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Количество комнат *</label>
            <input v-model.number="form.rooms" type="number" required>
          </div>
          <div class="form-group">
            <label>Общая площадь (м²) *</label>
            <input v-model.number="form.total_area" type="number" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Площадь кухни (м²)</label>
            <input v-model.number="form.kitchen_area" type="number">
          </div>
          <div class="form-group">
            <label>Жилая площадь (м²)</label>
            <input v-model.number="form.living_area" type="number">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Этаж</label>
            <input v-model.number="form.floor" type="number">
          </div>
          <div class="form-group">
            <label>Максимум гостей *</label>
            <input v-model.number="form.max_guests" type="number" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Балкон</label>
            <select v-model="form.balcony">
              <option value="none">Нет</option>
              <option value="balcony">Балкон</option>
              <option value="loggia">Лоджия</option>
            </select>
          </div>
          <div class="form-group">
            <label>Санузел</label>
            <select v-model="form.bathroom">
              <option value="shared">Совмещенный</option>
              <option value="private">Раздельный</option>
              <option value="multiple">Несколько</option>
            </select>
          </div>
        </div>
        
        <!-- Мебель - список с чекбоксами -->
        <div class="checkbox-group">
          <h4>Мебель</h4>
          <div class="checkbox-grid">
            <div v-for="furniture in furnitureOptions" :key="furniture.value" class="checkbox-item">
              <input 
                type="checkbox" 
                :id="`furniture-${furniture.value}`"
                :value="furniture.value"
                v-model="form.furniture"
              >
              <label :for="`furniture-${furniture.value}`">{{ furniture.label }}</label>
            </div>
          </div>
          <div class="custom-input">
            <input 
              v-model="customFurniture" 
              placeholder="Добавить свою мебель"
              @keyup.enter="addCustomFurniture"
            >
            <button type="button" @click="addCustomFurniture" class="btn-small">+</button>
          </div>
        </div>

        <!-- Техника - список с чекбоксами -->
        <div class="checkbox-group">
          <h4>Техника</h4>
          <div class="checkbox-grid">
            <div v-for="appliance in applianceOptions" :key="appliance.value" class="checkbox-item">
              <input 
                type="checkbox" 
                :id="`appliance-${appliance.value}`"
                :value="appliance.value"
                v-model="form.appliances"
              >
              <label :for="`appliance-${appliance.value}`">{{ appliance.label }}</label>
            </div>
          </div>
          <div class="custom-input">
            <input 
              v-model="customAppliance" 
              placeholder="Добавить свою технику"
              @keyup.enter="addCustomAppliance"
            >
            <button type="button" @click="addCustomAppliance" class="btn-small">+</button>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group checkbox">
            <input v-model="form.has_internet" type="checkbox">
            <label>Интернет</label>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Ремонт</label>
            <input v-model="form.renovation" placeholder="косметический, евро">
          </div>
        </div>
      </div>

      <div class="form-section">
        <h3>Условия аренды</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Залог (₽)</label>
            <input v-model.number="form.deposit" type="number">
          </div>
          <div class="form-group">
            <label>Комиссия (%)</label>
            <input v-model.number="form.commission" type="number">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group checkbox">
            <input v-model="form.meter_based" type="checkbox">
            <label>По счетчикам</label>
          </div>
          <div class="form-group">
            <label>Другие ЖКУ</label>
            <input v-model="form.other_utilities" placeholder="Дополнительные услуги">
          </div>
        </div>
      </div>

      <div class="form-section">
        <h3>Правила</h3>
        <div class="form-row">
          <div class="form-group checkbox">
            <input v-model="form.allows_children" type="checkbox">
            <label>Можно с детьми</label>
          </div>
          <div class="form-group checkbox">
            <input v-model="form.allows_pets" type="checkbox">
            <label>Можно с животными</label>
          </div>
          <div class="form-group checkbox">
            <input v-model="form.allows_smoking" type="checkbox">
            <label>Можно курить</label>
          </div>
        </div>
      </div>

      <div class="form-section">
        <h3>Описание</h3>
        <div class="form-group full-width">
          <textarea v-model="form.description" rows="6" placeholder="Подробное описание апартаментов..."></textarea>
        </div>
      </div>

      <div class="form-section">
        <h3>Координаты на карте</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Широта</label>
            <input v-model.number="form.latitude" type="number" step="0.000001">
          </div>
          <div class="form-group">
            <label>Долгота</label>
            <input v-model.number="form.longitude" type="number" step="0.000001">
          </div>
        </div>
      </div>

      <div class="form-section">
        <h3>Изображения</h3>
        <div class="image-upload">
          <input type="file" multiple @change="handleImageUpload" accept="image/*">
          <p>Максимум 10 фотографий</p>
        </div>
        <div v-if="form.images.length > 0" class="images-preview">
          <div v-for="(img, idx) in form.images" :key="idx" class="image-item">
            <img :src="img.preview || img.url" :alt="`Image ${idx}`">
            <button type="button" @click="removeImage(idx)" class="remove-btn">✕</button>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary" :disabled="loading">
          {{ loading ? 'Сохранение...' : (isEdit ? 'Сохранить изменения' : 'Создать апартаменты') }}
        </button>
        <button type="button" @click="goBack" class="btn btn-secondary">Отмена</button>
      </div>
    </form>
  </div>
</template>

<script>
import axios from '../../../axios.js'

export default {
  data() {
    return {
      isEdit: false,
      apartmentId: null,
      loading: false,
      customFurniture: '',
      customAppliance: '',
      furnitureOptions: [
        { value: 'bed', label: 'Кровать' },
        { value: 'wardrobe', label: 'Шкаф' },
        { value: 'sofa', label: 'Диван' },
        { value: 'table', label: 'Стол' },
        { value: 'chairs', label: 'Стулья' },
        { value: 'dresser', label: 'Комод' },
        { value: 'nightstand', label: 'Тумбочка' },
        { value: 'bookshelf', label: 'Книжная полка' },
        { value: 'tv_stand', label: 'ТВ-тумба' },
        { value: 'armchair', label: 'Кресло' }
      ],
      applianceOptions: [
        { value: 'refrigerator', label: 'Холодильник' },
        { value: 'stove', label: 'Плита' },
        { value: 'oven', label: 'Духовка' },
        { value: 'microwave', label: 'Микроволновка' },
        { value: 'dishwasher', label: 'Посудомойка' },
        { value: 'washing_machine', label: 'Стиральная машина' },
        { value: 'tv', label: 'Телевизор' },
        { value: 'air_conditioner', label: 'Кондиционер' },
        { value: 'water_heater', label: 'Водонагреватель' },
        { value: 'coffee_maker', label: 'Кофемашина' }
      ],
      form: {
        name: '',
        address: '',
        price_per_night: 0,
        rooms: 0,
        total_area: 0,
        kitchen_area: 0,
        living_area: 0,
        floor: 0,
        max_guests: 1,
        balcony: 'none',
        furniture: [],
        appliances: [],
        has_internet: false,
        bathroom: 'shared',
        renovation: '',
        deposit: 0,
        commission: 0,
        meter_based: false,
        other_utilities: '',
        allows_children: false,
        allows_pets: false,
        allows_smoking: false,
        description: '',
        latitude: 55.7558,
        longitude: 37.6173,
        images: []
      }
    }
  },
  mounted() {
    console.log('🏠 AdminApartmentForm mounted')
    
    // Используем параметры маршрута
    this.apartmentId = this.$route.params.id
    console.log('Apartment ID from route:', this.apartmentId)
    
    if (this.apartmentId) {
      this.isEdit = true
      this.fetchApartment()
    }
  },
  methods: {
    async fetchApartment() {
      try {
        const response = await axios.get(`/api/apartments/${this.apartmentId}`)
        // Конвертируем строки в массивы для мебели и техники
        const apartmentData = response.data
        if (apartmentData.furniture && typeof apartmentData.furniture === 'string') {
          apartmentData.furniture = apartmentData.furniture.split(',').map(item => item.trim())
        }
        if (apartmentData.appliances && typeof apartmentData.appliances === 'string') {
          apartmentData.appliances = apartmentData.appliances.split(',').map(item => item.trim())
        }
        this.form = { ...apartmentData, images: apartmentData.images || [] }
      } catch (err) {
        console.error('Error fetching apartment:', err)
        alert('Ошибка загрузки данных апартаментов')
      }
    },
    handleImageUpload(event) {
      const files = Array.from(event.target.files)
      if (this.form.images.length + files.length > 10) {
        alert('Максимум 10 фотографий')
        return
      }
      files.forEach(file => {
        const reader = new FileReader()
        reader.onload = (e) => {
          this.form.images.push({
            file,
            preview: e.target.result
          })
        }
        reader.readAsDataURL(file)
      })
    },
    removeImage(idx) {
      this.form.images.splice(idx, 1)
    },
    addCustomFurniture() {
      if (this.customFurniture.trim() && !this.form.furniture.includes(this.customFurniture.trim())) {
        this.form.furniture.push(this.customFurniture.trim())
        this.customFurniture = ''
      }
    },
    addCustomAppliance() {
      if (this.customAppliance.trim() && !this.form.appliances.includes(this.customAppliance.trim())) {
        this.form.appliances.push(this.customAppliance.trim())
        this.customAppliance = ''
      }
    },
    async submitForm() {
  console.log('=== Starting form submission ===')
  
  this.loading = true

  try {
    // Сбрасываем и получаем новый CSRF токен
    csrfToken = null
    await axios.get('/sanctum/csrf-cookie')
    
    const formData = new FormData()
    
    // Добавляем поля...
    formData.append('name', this.form.name || '')
    formData.append('address', this.form.address || '')
    formData.append('price_per_night', this.form.price_per_night || 0)
    formData.append('rooms', this.form.rooms || 1)
    formData.append('total_area', this.form.total_area || 0)
    formData.append('max_guests', this.form.max_guests || 1)
    // ... остальные поля

    const url = this.isEdit 
      ? `/api/apartments/${this.apartmentId}`
      : '/api/apartments'
    
    const method = this.isEdit ? 'put' : 'post'

    console.log('Sending request to:', url)

    const response = await axios[method](url, formData, {
      headers: { 
        'Content-Type': 'multipart/form-data'
      }
    })

    console.log('Response:', response.data)

    if (response.data.success) {
      alert(this.isEdit ? 'Апартаменты успешно обновлены' : 'Апартаменты успешно созданы')
      this.$router.push('/admin/apartments')
    }
  } catch (err) {
    console.error('Full error:', err)
    
    if (err.response?.status === 401) {
      alert('Ошибка авторизации. Пожалуйста, войдите заново.')
      window.location.href = '/login?t=' + Date.now()
    } else if (err.response?.data?.errors) {
      // Обработка ошибок валидации
      const errors = err.response.data.errors
      let errorMessage = 'Ошибки валидации:\n'
      Object.keys(errors).forEach(field => {
        errorMessage += `- ${field}: ${errors[field].join(', ')}\n`
      })
      alert(errorMessage)
    } else {
      alert('Ошибка: ' + (err.response?.data?.message || err.message))
    }
  } finally {
    this.loading = false
  }
},
    goBack() {
      this.$router.push('/admin/apartments')
    }
  }
}
</script>

<style scoped>
.apartment-form {
  padding: 20px;
  max-width: 900px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.page-header h2 {
  font-size: 22px;
  margin: 0;
}

.form-section {
  margin-bottom: 30px;
  padding: 20px;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
}

.form-section h3 {
  margin-bottom: 15px;
  font-size: 16px;
  font-weight: bold;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 5px;
  font-size: 13px;
  font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 10px;
  border: 1px solid #E0E0E0;
  border-radius: 4px;
  font-family: 'Unbounded', sans-serif;
}

.form-group.checkbox {
  flex-direction: row;
  align-items: center;
  gap: 8px;
}

.form-group.checkbox input {
  width: auto;
  margin: 0;
}

.form-group.checkbox label {
  margin: 0;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group.full-width textarea {
  width: 100%;
  resize: vertical;
  min-height: 120px;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin-bottom: 15px;
}

/* Стили для групп чекбоксов */
.checkbox-group {
  margin-bottom: 20px;
}

.checkbox-group h4 {
  margin-bottom: 10px;
  font-size: 14px;
  font-weight: bold;
  color: #333;
}

.checkbox-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 8px;
  margin-bottom: 10px;
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.checkbox-item input[type="checkbox"] {
  width: 16px;
  height: 16px;
}

.checkbox-item label {
  font-size: 13px;
  margin: 0;
  cursor: pointer;
}

.custom-input {
  display: flex;
  gap: 8px;
  margin-top: 10px;
}

.custom-input input {
  flex: 1;
  padding: 8px;
  border: 1px solid #E0E0E0;
  border-radius: 4px;
  font-size: 13px;
}

.btn-small {
  padding: 8px 12px;
  border: 1px solid var(--accent);
  background: white;
  color: var(--accent);
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  font-weight: bold;
}

.btn-small:hover {
  background: var(--accent);
  color: white;
}

.image-upload {
  padding: 20px;
  border: 2px dashed #E0E0E0;
  border-radius: 8px;
  text-align: center;
}

.image-upload input {
  margin-bottom: 10px;
}

.image-upload p {
  font-size: 12px;
  color: #999;
}

.images-preview {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 10px;
  margin-top: 15px;
}

.image-item {
  position: relative;
  width: 100px;
  height: 100px;
}

.image-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 4px;
  border: 1px solid #E0E0E0;
}

.remove-btn {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 24px;
  height: 24px;
  background: #FF6B6B;
  color: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  font-size: 14px;
}

.form-actions {
  display: flex;
  gap: 10px;
  margin-top: 30px;
}

.btn {
  flex: 1;
  padding: 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
  font-weight: bold;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--accent);
  color: #000;
}

.btn-secondary {
  background: #E0E0E0;
  color: #000;
}
</style>