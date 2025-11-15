<template>
  <AppLayout>
    <router-view></router-view>
  </AppLayout>
</template>

<script>
import AppLayout from './components/layouts/AppLayout.vue'

export default {
  name: 'App',
  components: {
    AppLayout
  }
}
</script>
<style>
/* Глобальные CSS переменные */
:root {
  --primary: #F5F5F5;
  --accent: #CBCEEA;
  --font-family: 'Unbounded', sans-serif;
}

/* Сброс стилей и базовые настройки */
* {
  margin: 0px;
  padding: 0px;
  box-sizing: border-box;
}

/* Глобальные стили для всего сайта */
body {
  font-family: var(--font-family);
  font-weight: 400px;
  font-size: 14px;
  line-height: 1.5;
  background-color: #fff;
  color: #000;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Наследование шрифта для всех элементов */
#app,
#app * {
  font-family: inherit;
}

/* Стили для текстовых элементов */
h1, h2, h3, h4, h5, h6 {
  font-family: inherit;
  font-weight: 600;
  line-height: 1.2;
}

p, span, div, a, button, input, textarea, select, label {
  font-family: inherit;
}

/* Специфичные настройки для элементов формы */
input, textarea, select, button {
  font-family: inherit;
  font-size: inherit;
}

#app {
  min-height: 100vh;
}
</style>