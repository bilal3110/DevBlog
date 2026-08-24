<script setup>
import { onMounted } from 'vue';
import { useRoute } from 'vue-router';
import LeftSide from './components/LeftSide.vue';
import RightSide from './components/RightSide.vue';
import { useAuthStore } from './stores/auth';

const route = useRoute();
const authStore = useAuthStore();

onMounted(() => {
  if (authStore.token) {
    authStore.fetchCurrentUser();
  }
});
</script>

<template>
  <div id="app" class="app-layout">
    <LeftSide v-if="!route.meta?.hideNavigation" />
    <main class="main-content-area">
      <router-view />
    </main>
    <RightSide v-if="!route.meta?.hideNavigation && route.name !== 'Login' && route.name !== 'Register'" />
  </div>
</template>

<style scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
  background: var(--bg-dark);
}

.main-content-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-height: 100vh;
  /* margin-left: 250px; */
  margin-right: 280px;
  padding: 0 20px;
}

@media (max-width: 1100px) {
  .main-content-area {
    margin-right: 0;
  }
}

@media (max-width: 768px) {
  .main-content-area {
    margin-left: 0;
    margin-right: 0;
    padding: 0 10px;
  }
}
</style>
