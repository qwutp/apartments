<template>
  <div class="apartment-form">
    <h2>{{ isEdit ? 'Редактирование апартаментов' : 'Добавление апартаментов' }}</h2>
    
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
        <h3>Расположение на карте</h3>
        <div class="form-group full-width">
          <label>Адрес</label>
          <input 
            v-model="form.address" 
            type="text" 
            placeholder="Введите адрес или выберите точку на карте ниже"
            @input="searchAddress"
            class="address-input"
          >
          <small class="help-text">Введите адрес для автоматического поиска или кликните на карте для выбора точки</small>
        </div>
        <div class="map-selector">
          <div id="map-selector-container" class="map-container">
            <div class="map-loading" v-if="!mapSelector">
              <p>Загрузка карты...</p>
            </div>
          </div>
          <div class="map-coordinates" v-if="form.latitude && form.longitude">
            <span><strong>Координаты:</strong> {{ form.latitude.toFixed(6) }}, {{ form.longitude.toFixed(6) }}</span>
          </div>
          <div class="map-instructions">
            <p><strong>Инструкция:</strong> Кликните на карте, чтобы установить точку. Перетащите маркер для точной настройки позиции.</p>
          </div>
        </div>
        <div class="form-row" style="margin-top: 10px;">
          <div class="form-group">
            <label>Широта</label>
            <input v-model.number="form.latitude" type="number" step="0.000001" readonly>
          </div>
          <div class="form-group">
            <label>Долгота</label>
            <input v-model.number="form.longitude" type="number" step="0.000001" readonly>
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
          <div v-for="(img, idx) in form.images" :key="img.id || `new-${idx}`" class="image-item">
            <img 
              :src="getImageSrc(img)" 
              :alt="`Image ${idx}`"
              @error="handleImageError"
            >
            <button type="button" @click="removeImage(idx)" class="remove-btn" title="Удалить изображение">✕</button>
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
        furniture: [], // Теперь это массив выбранных значений
        appliances: [], // Теперь это массив выбранных значений
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
        latitude: null,
        longitude: null,
        images: []
      },
      mapSelector: null,
      currentMarker: null,
      addressSearchTimeout: null,
      deletedImageIds: [] // Массив ID изображений, которые нужно удалить
    }
  },
  
  watch: {
    // Сбрасываем список удаленных изображений при переключении режима
    isEdit(newVal) {
      if (!newVal) {
        this.deletedImageIds = []
      }
    }
  },
  
  mounted() {
    console.log('🏠 AdminApartmentForm mounted')
    console.log('Route name:', this.$route.name)
    console.log('Route params:', this.$route.params)
    
    // Определяем режим: создание или редактирование
    if (this.$route.name === 'admin-apartment-create') {
      this.isEdit = false
      this.apartmentId = null
      console.log('Creating new apartment')
    } else if (this.$route.name === 'admin-apartment-edit') {
      this.apartmentId = this.$route.params.id
      this.isEdit = true
      console.log('Editing apartment:', this.apartmentId)
      this.fetchApartment()
    }
    
    // Загружаем карту после монтирования компонента
    this.$nextTick(() => {
      setTimeout(() => {
        this.loadYandexMapSelector()
      }, 500)
    })
  },

methods: {
  goBack() {
    this.$router.push('/admin/apartments')
  },
  
  async fetchApartment() {
    if (!this.apartmentId) return
    
    try {
      console.log('📥 Fetching apartment:', this.apartmentId)
      const response = await axios.get(`/api/apartments/${this.apartmentId}`)
      const apt = response.data
      
      console.log('✅ Apartment data:', apt)
      
      // Заполняем форму данными апартамента
      this.form = {
        name: apt.name || '',
        address: apt.address || '',
        price_per_night: apt.price_per_night || apt.price || 0,
        rooms: apt.rooms || 0,
        total_area: apt.total_area || 0,
        kitchen_area: apt.kitchen_area || 0,
        living_area: apt.living_area || 0,
        floor: apt.floor || 0,
        max_guests: apt.max_guests || 1,
        balcony: apt.balcony || 'none',
        furniture: apt.furniture ? (typeof apt.furniture === 'string' ? apt.furniture.split(', ') : apt.furniture) : [],
        appliances: apt.appliances ? (typeof apt.appliances === 'string' ? apt.appliances.split(', ') : apt.appliances) : [],
        has_internet: apt.has_internet || apt.internet || false,
        bathroom: apt.bathroom || 'shared',
        renovation: apt.renovation || apt.repair || '',
        deposit: apt.deposit || 0,
        commission: apt.commission || 0,
        meter_based: apt.meter_based || apt.counters || false,
        other_utilities: apt.other_utilities || '',
        allows_children: apt.allows_children || apt.children_allowed || false,
        allows_pets: apt.allows_pets || apt.pets_allowed || false,
        allows_smoking: apt.allows_smoking || apt.smoking_allowed || false,
        description: apt.description || '',
        latitude: apt.latitude ? parseFloat(apt.latitude) : null,
        longitude: apt.longitude ? parseFloat(apt.longitude) : null,
        images: apt.images ? apt.images.map(img => ({ 
          id: img.id, 
          url: img.url, 
          file: null,
          image_path: img.image_path || null
        })) : []
      }
      
      console.log('📝 Form filled with data')
      console.log('Loaded images:', this.form.images)
      
      // Сбрасываем список удаленных изображений при загрузке
      this.deletedImageIds = []
      
      // Обновляем карту после загрузки данных
      this.$nextTick(() => {
        setTimeout(() => {
          // Если карта уже инициализирована, обновляем маркер
          if (this.mapSelector) {
            if (this.form.latitude && this.form.longitude) {
              this.addMarker([this.form.latitude, this.form.longitude])
            } else if (this.form.address) {
              this.geocodeAddress(this.form.address).then(() => {
                if (this.form.latitude && this.form.longitude) {
                  this.addMarker([this.form.latitude, this.form.longitude])
                }
              })
            }
          } else {
            // Если карта не инициализирована, загружаем её
            this.loadYandexMapSelector()
          }
        }, 1000)
      })
    } catch (error) {
      console.error('❌ Error fetching apartment:', error)
      alert('Ошибка загрузки данных апартамента: ' + (error.response?.data?.message || error.message))
      this.$router.push('/admin/apartments')
    }
  },
  
  handleImageUpload(event) {
    const files = Array.from(event.target.files)
    
    // Проверяем лимит
    if (this.form.images.length + files.length > 10) {
      alert('Максимум 10 изображений')
      return
    }
    
    files.forEach(file => {
      // Проверяем тип файла
      if (!file.type.startsWith('image/')) {
        alert(`Файл ${file.name} не является изображением`)
        return
      }
      
      // Проверяем размер (2MB)
      if (file.size > 2 * 1024 * 1024) {
        alert(`Файл ${file.name} слишком большой (максимум 2MB)`)
        return
      }
      
      // Создаем preview
      const reader = new FileReader()
      reader.onload = (e) => {
        this.form.images.push({
          file: file,
          preview: e.target.result,
          url: null
        })
      }
      reader.readAsDataURL(file)
    })
    
    // Очищаем input для возможности повторной загрузки того же файла
    event.target.value = ''
  },
  
  removeImage(index) {
    const image = this.form.images[index]
    
    // Если это существующее изображение (есть id), добавляем его ID в список для удаления
    if (image && image.id) {
      if (!this.deletedImageIds.includes(image.id)) {
        this.deletedImageIds.push(image.id)
        console.log('Added image to delete list:', image.id, 'Total deleted:', this.deletedImageIds)
      }
    }
    
    // Удаляем изображение из массива
    this.form.images.splice(index, 1)
    console.log('Removed image from form, remaining:', this.form.images.length)
  },
  
  getImageSrc(img) {
    // Для новых изображений используем preview
    if (img.preview) {
      return img.preview
    }
    // Для существующих изображений используем url
    if (img.url) {
      return img.url
    }
    // Если есть image_path, формируем URL
    if (img.image_path) {
      // Если путь уже полный URL, возвращаем его
      if (img.image_path.startsWith('http://') || img.image_path.startsWith('https://')) {
        return img.image_path
      }
      // Иначе формируем URL относительно storage
      return `/storage/${img.image_path.replace(/^storage\//, '')}`
    }
    return ''
  },
  
  handleImageError(event) {
    // Предотвращаем бесконечные попытки загрузки
    if (event.target.dataset.errorHandled === 'true') {
      return
    }
    
    event.target.dataset.errorHandled = 'true'
    console.warn('Image failed to load in form:', event.target.src)
    
    // Пробуем альтернативный путь
    const src = event.target.src
    if (src.includes('http://') || src.includes('https://')) {
      try {
        const url = new URL(src)
        const path = url.pathname
        if (path.includes('/storage/')) {
          // Пробуем относительный путь
          event.target.src = path
          return
        }
        // Пробуем извлечь путь из полного URL
        const pathMatch = path.match(/\/storage\/(.+)/)
        if (pathMatch) {
          event.target.src = `/storage/${pathMatch[1]}`
          return
        }
      } catch (e) {
        // Игнорируем ошибку парсинга URL
      }
    }
    
    // Если путь содержит /storage/, пробуем разные варианты
    if (src.includes('/storage/')) {
      const pathMatch = src.match(/\/storage\/(.+)/)
      if (pathMatch) {
        // Пробуем без префикса storage
        event.target.src = `/storage/${pathMatch[1]}`
        return
      }
    }
    
    // Если не помогло, показываем placeholder
    const placeholder = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="150"%3E%3Crect fill="%23E0E0E0" width="200" height="150"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" fill="%23999" font-family="Arial" font-size="12"%3EИзображение%3C/text%3E%3C/svg%3E'
    event.target.src = placeholder
  },
  
  loadYandexMapSelector() {
    // Проверяем, не загружен ли уже скрипт
    if (document.querySelector('script[src*="api-maps.yandex.ru"]')) {
      if (window.ymaps) {
        window.ymaps.ready(() => {
          setTimeout(() => this.initYandexMapSelector(), 100)
        })
      } else {
        // Ждем загрузки
        const checkYmaps = setInterval(() => {
          if (window.ymaps) {
            clearInterval(checkYmaps)
            window.ymaps.ready(() => {
              setTimeout(() => this.initYandexMapSelector(), 100)
            })
          }
        }, 100)
        // Таймаут на случай, если карты не загрузятся
        setTimeout(() => clearInterval(checkYmaps), 5000)
      }
      return
    }
    
    // Загружаем Яндекс.Карты без API ключа (работает для базовых функций)
    const script = document.createElement('script')
    script.src = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU'
    script.async = true
    script.defer = true
    script.onload = () => {
      if (window.ymaps) {
        window.ymaps.ready(() => {
          setTimeout(() => this.initYandexMapSelector(), 100)
        })
      }
    }
    script.onerror = () => {
      console.error('Failed to load Yandex Maps')
    }
    document.head.appendChild(script)
  },
  
  initYandexMapSelector() {
    const mapContainer = document.getElementById('map-selector-container')
    if (!mapContainer) {
      console.error('Map selector container not found')
      setTimeout(() => this.initYandexMapSelector(), 500)
      return
    }
    
    // Если карта уже инициализирована, не инициализируем снова
    if (this.mapSelector) {
      console.log('Map already initialized')
      return
    }
    
    // Очищаем контейнер перед инициализацией
    mapContainer.innerHTML = ''
    
    if (!window.ymaps) {
      console.error('Yandex Maps API not loaded, waiting...')
      setTimeout(() => this.initYandexMapSelector(), 500)
      return
    }
    
    window.ymaps.ready(() => {
      // Если есть адрес, но нет координат - геокодируем
      if (this.form.address && !this.form.latitude) {
        this.geocodeAddress(this.form.address).then(() => {
          this.initMapWithMarker(mapContainer)
        }).catch(() => {
          // Если геокодирование не удалось, все равно показываем карту
          this.initMapWithMarker(mapContainer)
        })
      } else {
        this.initMapWithMarker(mapContainer)
      }
    })
  },
  
  initMapWithMarker(mapContainer) {
    if (!window.ymaps || !mapContainer) {
      console.error('Cannot initialize map: ymaps or container not available')
      return
    }
    
    const center = this.form.latitude && this.form.longitude 
      ? [parseFloat(this.form.latitude), parseFloat(this.form.longitude)]
      : [55.7558, 37.6173] // Москва по умолчанию
    
    try {
      const map = new window.ymaps.Map(mapContainer, {
        center: center,
        zoom: this.form.latitude ? 15 : 10,
        controls: ['zoomControl', 'fullscreenControl', 'geolocationControl']
      })
      
      this.mapSelector = map
      
      // Если есть координаты, добавляем маркер
      if (this.form.latitude && this.form.longitude) {
        this.addMarker([parseFloat(this.form.latitude), parseFloat(this.form.longitude)])
      }
      
      // Обработчик клика на карте для установки точки
      map.events.add('click', (e) => {
        const coords = e.get('coords')
        console.log('Map clicked at:', coords)
        this.form.latitude = coords[0]
        this.form.longitude = coords[1]
        this.addMarker(coords)
        this.reverseGeocode(coords)
      })
      
      console.log('Map initialized successfully')
    } catch (error) {
      console.error('Error initializing map:', error)
    }
  },
  
  addMarker(coords) {
    if (!this.mapSelector || !window.ymaps) {
      console.error('Cannot add marker: map or ymaps not available')
      return
    }
    
    // Удаляем предыдущий маркер, если есть
    if (this.currentMarker) {
      this.mapSelector.geoObjects.remove(this.currentMarker)
      this.currentMarker = null
    }
    
    try {
      // Создаем новый маркер
      this.currentMarker = new window.ymaps.Placemark(coords, {
        balloonContent: `
          <div style="padding: 10px;">
            <strong>Местоположение апартаментов</strong><br>
            Координаты: ${coords[0].toFixed(6)}, ${coords[1].toFixed(6)}<br>
            <small>Перетащите маркер для изменения позиции</small>
          </div>
        `,
        hintContent: 'Перетащите для изменения позиции'
      }, {
        preset: 'islands#redDotIcon',
        draggable: true
      })
      
      this.mapSelector.geoObjects.add(this.currentMarker)
      this.mapSelector.setCenter(coords, 15, {
        duration: 300
      })
      
      // Обработчик перетаскивания маркера
      this.currentMarker.events.add('dragend', () => {
        const newCoords = this.currentMarker.geometry.getCoordinates()
        this.form.latitude = newCoords[0]
        this.form.longitude = newCoords[1]
        this.reverseGeocode(newCoords)
      })
      
      // Открываем балун при создании маркера
      this.currentMarker.balloon.open()
      
      console.log('Marker added at:', coords)
    } catch (error) {
      console.error('Error adding marker:', error)
    }
  },
  
  geocodeAddress(address) {
    if (!window.ymaps || !address) return Promise.resolve()
    
    return window.ymaps.geocode(address).then(res => {
      const firstGeoObject = res.geoObjects.get(0)
      if (firstGeoObject) {
        const coordinates = firstGeoObject.geometry.getCoordinates()
        this.form.latitude = coordinates[0]
        this.form.longitude = coordinates[1]
        if (this.mapSelector) {
          this.addMarker(coordinates)
          this.mapSelector.setCenter(coordinates, 15)
        }
      }
    }).catch(err => {
      console.error('Geocoding error:', err)
    })
  },
  
  reverseGeocode(coords) {
    if (!window.ymaps) return
    
    window.ymaps.geocode(coords).then(res => {
      const firstGeoObject = res.geoObjects.get(0)
      if (firstGeoObject) {
        this.form.address = firstGeoObject.getAddressLine()
      }
    })
  },
  
  searchAddress() {
    // Debounce для поиска адреса
    if (this.addressSearchTimeout) {
      clearTimeout(this.addressSearchTimeout)
    }
    this.addressSearchTimeout = setTimeout(() => {
      if (this.form.address && this.form.address.length > 3) {
        this.geocodeAddress(this.form.address).then(() => {
          // После геокодирования обновляем маркер на карте
          if (this.mapSelector && this.form.latitude && this.form.longitude) {
            this.addMarker([this.form.latitude, this.form.longitude])
          }
        })
      }
    }, 800)
  },
  
  async submitForm() {
    console.log('=== DEBUG: Starting form submission ===')
    console.log('Is Edit:', this.isEdit)
    console.log('Apartment ID:', this.apartmentId)
    
    this.loading = true
    
    try {
      console.log('Step 1: Getting CSRF token...')
      // ОБЯЗАТЕЛЬНО получаем CSRF токен
      await axios.get('/sanctum/csrf-cookie')
      console.log('Step 1: CSRF token received')
      
      console.log('Step 2: Checking auth...')
      // Проверяем авторизацию
      const authCheck = await axios.get('/api/check-auth')
      console.log('Auth check before submit:', authCheck.data)
      
      if (!authCheck.data.user || authCheck.data.user.role !== 'admin') {
        alert('Ошибка: у вас нет прав администратора')
        this.loading = false
        return
      }
      
      console.log('Step 3: Building FormData...')
      
      // Проверяем обязательные поля перед отправкой
      if (!this.form.name || !this.form.name.trim()) {
        alert('Пожалуйста, заполните название апартаментов')
        this.loading = false
        return
      }
      if (!this.form.address || !this.form.address.trim()) {
        alert('Пожалуйста, заполните адрес апартаментов')
        this.loading = false
        return
      }
      if (!this.form.price_per_night || this.form.price_per_night <= 0) {
        alert('Пожалуйста, укажите цену за ночь (больше 0)')
        this.loading = false
        return
      }
      if (!this.form.rooms || this.form.rooms < 1) {
        alert('Пожалуйста, укажите количество комнат (минимум 1)')
        this.loading = false
        return
      }
      if (!this.form.total_area || this.form.total_area <= 0) {
        alert('Пожалуйста, укажите общую площадь (больше 0)')
        this.loading = false
        return
      }
      if (!this.form.max_guests || this.form.max_guests < 1) {
        alert('Пожалуйста, укажите максимальное количество гостей (минимум 1)')
        this.loading = false
        return
      }
      
      const formData = new FormData()
      
      // Обязательные поля - ВСЕГДА добавляем, даже если пустые (для валидации)
      const name = String(this.form.name || '').trim()
      const address = String(this.form.address || '').trim()
      const price_per_night = this.form.price_per_night !== null && this.form.price_per_night !== undefined 
        ? String(Number(this.form.price_per_night)) 
        : '0'
      const rooms = this.form.rooms !== null && this.form.rooms !== undefined 
        ? String(Number(this.form.rooms)) 
        : '1'
      const total_area = this.form.total_area !== null && this.form.total_area !== undefined 
        ? String(Number(this.form.total_area)) 
        : '0'
      const max_guests = this.form.max_guests !== null && this.form.max_guests !== undefined 
        ? String(Number(this.form.max_guests)) 
        : '1'
      
      console.log('Form values before append:', {
        name,
        address,
        price_per_night,
        rooms,
        total_area,
        max_guests
      })
      
      formData.append('name', name)
      formData.append('address', address)
      formData.append('price_per_night', price_per_night)
      formData.append('rooms', rooms)
      formData.append('total_area', total_area)
      formData.append('max_guests', max_guests)
      
      // Необязательные числовые поля
      if (this.form.kitchen_area) {
        formData.append('kitchen_area', String(Number(this.form.kitchen_area)))
      }
      if (this.form.living_area) {
        formData.append('living_area', String(Number(this.form.living_area)))
      }
      if (this.form.floor !== null && this.form.floor !== undefined && this.form.floor !== '') {
        formData.append('floor', String(Number(this.form.floor)))
      }
      if (this.form.deposit) {
        formData.append('deposit', String(Number(this.form.deposit)))
      }
      if (this.form.commission) {
        formData.append('commission', String(Number(this.form.commission)))
      }
      if (this.form.latitude) {
        formData.append('latitude', String(Number(this.form.latitude)))
      }
      if (this.form.longitude) {
        formData.append('longitude', String(Number(this.form.longitude)))
      }
      
      // Строковые поля
      if (this.form.balcony) {
        formData.append('balcony', String(this.form.balcony))
      }
      if (this.form.bathroom) {
        formData.append('bathroom', String(this.form.bathroom))
      }
      if (this.form.renovation) {
        formData.append('renovation', String(this.form.renovation))
      }
      if (this.form.furniture) {
        const furnitureStr = Array.isArray(this.form.furniture) 
          ? this.form.furniture.join(', ') 
          : String(this.form.furniture)
        if (furnitureStr) {
          formData.append('furniture', furnitureStr)
        }
      }
      if (this.form.appliances) {
        const appliancesStr = Array.isArray(this.form.appliances) 
          ? this.form.appliances.join(', ') 
          : String(this.form.appliances)
        if (appliancesStr) {
          formData.append('appliances', appliancesStr)
        }
      }
      if (this.form.other_utilities) {
        formData.append('other_utilities', String(this.form.other_utilities))
      }
      if (this.form.description) {
        formData.append('description', String(this.form.description))
      }
      
      // Булевы поля
      formData.append('has_internet', this.form.has_internet ? '1' : '0')
      formData.append('meter_based', this.form.meter_based ? '1' : '0')
      formData.append('allows_children', this.form.allows_children ? '1' : '0')
      formData.append('allows_pets', this.form.allows_pets ? '1' : '0')
      formData.append('allows_smoking', this.form.allows_smoking ? '1' : '0')
      
      // Изображения - используем images[] для Laravel
      this.form.images.forEach((img) => {
        if (img.file) {
          formData.append('images[]', img.file)
        }
      })
      
      // Отправляем список ID изображений для удаления (только при редактировании)
      if (this.isEdit && this.deletedImageIds.length > 0) {
        this.deletedImageIds.forEach((imageId) => {
          formData.append('deleted_images[]', imageId)
        })
      }

      const url = this.isEdit 
        ? `/api/apartments/${this.apartmentId}`
        : '/api/apartments'
      
      console.log('Step 4: Preparing request...')
      console.log('📤 Sending request to:', url, 'isEdit:', this.isEdit)
      console.log('Form values:', {
        name: this.form.name,
        address: this.form.address,
        price_per_night: this.form.price_per_night,
        rooms: this.form.rooms,
        total_area: this.form.total_area,
        max_guests: this.form.max_guests
      })

      // Получаем CSRF токен
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      console.log('CSRF Token for request:', csrfToken ? 'Found' : 'Not found')
      
      console.log('📋 FormData contents:')
      for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1])
      }
      
      console.log('Step 5: Sending request...')
      const startTime = Date.now()
      
      // Для PUT используем axios.put, но с правильными заголовками
      // НЕ устанавливаем Content-Type вручную для FormData - браузер сделает это сам с boundary
      const requestConfig = {
        headers: { 
          'X-CSRF-TOKEN': csrfToken || '',
          'Accept': 'application/json'
        },
        timeout: 60000 // 60 секунд таймаут для загрузки файлов
      }
      
      // Для PUT запросов добавляем _method в FormData (Laravel method spoofing)
      if (this.isEdit) {
        formData.append('_method', 'PUT')
        // Используем POST с _method=PUT
        var response = await axios.post(url, formData, requestConfig)
      } else {
        var response = await axios.post(url, formData, requestConfig)
      }

      const endTime = Date.now()
      console.log(`Step 6: Request completed in ${endTime - startTime}ms`)
      console.log('✅ Response:', response.data)

      if (response.data.success) {
        alert(this.isEdit ? 'Апартаменты успешно обновлены' : 'Апартаменты успешно созданы')
        this.$router.push('/admin/apartments')
      } else {
        alert('Ошибка: ' + (response.data.message || 'Неизвестная ошибка'))
      }
    } catch (err) {
      console.error('❌ Full error:', err)
      console.error('Error code:', err.code)
      console.error('Error message:', err.message)
      console.error('Error response:', err.response?.data)
      console.error('Error status:', err.response?.status)
      
      // Обработка таймаута
      if (err.code === 'ECONNABORTED' || err.message?.includes('timeout')) {
        alert('Превышено время ожидания ответа от сервера. Проверьте подключение к интернету и попробуйте снова.')
        this.loading = false
        return
      }
      
      // Обработка сетевых ошибок
      if (!err.response) {
        alert('Ошибка сети. Проверьте подключение к интернету и попробуйте снова.')
        this.loading = false
        return
      }
      
      if (err.response?.status === 401) {
        alert('Ошибка авторизации. Пожалуйста, войдите заново.')
        this.$router.push('/login')
      } else if (err.response?.status === 403) {
        alert('Доступ запрещен. У вас нет прав администратора для выполнения этого действия.')
      } else if (err.response?.status === 419) {
        alert('Сессия истекла. Перезагрузите страницу.')
        window.location.reload()
      } else if (err.response?.data?.errors) {
        const errors = err.response.data.errors
        let errorMessage = 'Ошибки валидации:\n'
        Object.keys(errors).forEach(field => {
          errorMessage += `- ${field}: ${errors[field].join(', ')}\n`
        })
        alert(errorMessage)
      } else {
        alert('Ошибка: ' + (err.response?.data?.message || err.message || 'Неизвестная ошибка'))
      }
    } finally {
      this.loading = false
    }
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

.apartment-form h2 {
  margin-bottom: 30px;
  font-size: 22px;
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
  width: 28px;
  height: 28px;
  background: #FF6B6B;
  color: white;
  border: 2px solid white;
  border-radius: 50%;
  cursor: pointer;
  font-size: 16px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  transition: all 0.2s;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.remove-btn:hover {
  background: #FF5252;
  transform: scale(1.1);
  box-shadow: 0 3px 6px rgba(0,0,0,0.3);
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

.map-selector {
  margin-top: 15px;
}

.map-container {
  width: 100%;
  height: 400px;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  overflow: hidden;
  background: #f0f0f0;
}

.map-coordinates {
  margin-top: 10px;
  padding: 8px;
  background: #F5F5F5;
  border-radius: 4px;
  font-size: 12px;
  color: #666;
}

.address-input {
  width: 100%;
  padding: 10px;
  border: 1px solid #E0E0E0;
  border-radius: 4px;
  font-family: 'Unbounded', sans-serif;
  margin-bottom: 10px;
}

.help-text {
  display: block;
  margin-top: 5px;
  font-size: 12px;
  color: #666;
}

.map-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #999;
  font-size: 14px;
}

.map-instructions {
  margin-top: 10px;
  padding: 10px;
  background: #F5F5F5;
  border-radius: 4px;
  font-size: 12px;
  color: #666;
}

.map-instructions p {
  margin: 0;
}
</style>