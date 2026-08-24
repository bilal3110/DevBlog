<script setup>
import { ref } from 'vue';
import NavBar from '../components/NavBar.vue';
import BlogCard from '../components/BlogCard.vue';

const currentTab = ref('forYou');

const handleTabChange = (tab) => {
  currentTab.value = tab;
};
</script>

<template>
  <main class="home-main">
    <NavBar :activeTab="currentTab" @tab-change="handleTabChange" />
    <BlogCard :tab="currentTab" />
  </main>
</template>

<style scoped>
.home-main {
  width: 100%;
  max-width: 550px;
}
</style>
