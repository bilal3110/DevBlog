<script setup>
import LeftSide from './components/LeftSide.vue';
import RightSide from './components/RightSide.vue';
</script>

<template>
  <div id="app">
    <LeftSide />
    <div class="main">
      <router-view />
    </div>
    <RightSide />
  </div>
</template>

<style scoped>
#app {
  display: flex;
  min-height: 100vh;
}
</style>
