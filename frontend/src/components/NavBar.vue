<template>
  <div class="navbar">
    <nav class="nav-menu">
      <ul class="nav-items">
        <li v-for="link in list" :key="link.id" class="nav-item">
          <a :to="link.href" class="navbar-link" :class="{ active: $route.path === link.href }">
            <span class="navText">{{ link.navText }}</span>
          </a>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router';

const $route = useRoute();

defineProps({
  list: {
    type: Array,
    default: () => [
      { id: 1, navText: 'My Feed', href: '/feed' },
      { id: 2, navText: 'For You', href: '/forYou'},
      { id: 3, navText: 'Trending', href: '/trending' },
      { id: 4, navText: 'Profile', href: '/profile' },
    ],
  },
});
</script>

<style scoped>
/* Navbar container */
.navbar {
  position: fixed;
  top: 0;
  height: 60px;
  width: 550px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  z-index: 1100;
  display: flex;
  align-items: center;
  padding: 0 20px;
  background: var(--bg-dark);
}

/* Navigation menu */
.nav-menu {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

.nav-items {
  list-style: none;
  display: flex;
  justify-content: center;
  gap: 20px;
  padding: 0;
  margin: 0;
}

.nav-item {
  display: flex;
  align-items: center;
}

.navbar-link {
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  padding: 8px 12px;
  border-radius: 6px;
  transition: background-color 0.2s ease, color 0.2s ease;
  cursor: pointer;
}

.navbar-link:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.navbar-link.active {
  background-color: var(--accent);
}

.navText {
  font-size: 14px;
}

/* Ensure focusable elements for keyboard navigation */
.navbar-link:focus {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

/* Responsive design */
@media (max-width: 768px) {
  .navbar {
    height: 50px;
    padding: 0 15px;
  }

  .nav-items {
    gap: 10px;
  }

  .navbar-link {
    font-size: 12px;
    padding: 6px 8px;
  }
}
</style>
