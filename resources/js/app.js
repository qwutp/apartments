import { createApp } from "vue"
import App from './app.vue' 
import router from './router'
import axios from 'axios'

// Layouts
import AppLayout from "./components/layouts/AppLayout.vue"
import AdminLayout from "./components/layouts/AdminLayout.vue"
import UserLayout from "./components/layouts/UserLayout.vue"

// Common components
import SearchBar from "./components/common/SearchBar.vue"
import ApartmentGrid from "./components/common/ApartmentGrid.vue"
import ApartmentCard from "./components/common/ApartmentCard.vue"
import BookingPanel from "./components/common/BookingPanel.vue"
import ReviewsList from "./components/common/ReviewsList.vue"
import RenterModal from "./components/pages/admin/RenterModal.vue"
import AdminCalendar from "./components/pages/admin/AdminCalendar.vue"
import AdminNotifications from "./components/pages/admin/AdminNotifications.vue"
import AdminRenters from "./components/pages/admin/AdminRenters.vue"

axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Автоматически добавляем CSRF токен ко всем запросам
axios.interceptors.request.use(function (config) {
    // Получаем токен из meta тега
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token
    }
    return config
})

console.log('Starting Vue app...')

const app = createApp(App)

// Глобальная регистрация компонентов
app.component("AppLayout", AppLayout)
app.component("AdminLayout", AdminLayout)
app.component("UserLayout", UserLayout)
app.component("SearchBar", SearchBar)
app.component("ApartmentGrid", ApartmentGrid)
app.component("ApartmentCard", ApartmentCard)
app.component("BookingPanel", BookingPanel)
app.component("ReviewsList", ReviewsList)
app.component("RenterModal", RenterModal)
app.component("AdminCalendar", AdminCalendar)
app.component("AdminNotifications", AdminNotifications) 
app.component("AdminRenters", AdminRenters)

app.use(router)
app.mount("#app")

console.log('Vue app mounted!')