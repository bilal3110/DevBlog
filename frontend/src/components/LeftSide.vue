<template>
  <div class="sidebar-container">
    <aside class="sidebar">
      <!-- Header -->
      <div class="sidebar-header">
        <div class="logo">
          <h3>{{ logo }}</h3>
        </div>
      </div>

      <!-- Menu -->
      <nav class="sidebar-nav">
        <ul class="menu">
          <li v-for="item in menuItems" :key="item.id" class="nav-item">
            <router-link
              :to="item.href"
              class="nav-link"
              :class="{ active: $route.path === item.href }"
            >
              <span :class="item.icon" class="nav-icon"></span>
              <span class="nav-text">{{ item.text }}</span>
            </router-link>
          </li>
        </ul>
      </nav>

      <!-- Footer -->
      <div class="sidebar-footer">
        <div class="user-info">
          <a :href="profile.href">
            <img
              :src="userAvatar"
              alt="User Avatar"
              class="avatar"
              @error="e => e.target.src = 'https://i.pravatar.cc/40'"
            />
          </a>
          <span>{{ userFullName }}</span>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router'

defineProps({
  logo: {
    type: String,
    default: 'StackStories',
  },
  userFullName: {
    type: String,
    default: 'John Doe',
  },
  userAvatar: {
    type: String,
    default: 'https://i.pravatar.cc/40',
  },
  profile: {
    type: Object,
    default: () => ({ href: '/profile' }),
  },
  menuItems: {
    type: Array,
    default: () => [
      { id: 1, text: 'Home', icon: 'fa fa-home', href: '/' },
      { id: 2, text: 'Explore', icon: 'fa fa-search', href: '/explore' },
      { id: 3, text: 'Create', icon: 'fa fa-plus', href: '/createpost' },
      { id: 4, text: 'Notification', icon: 'fa fa-bell', href: '/notifications' },
      { id: 5, text: 'Settings', icon: 'fa fa-cog', href: '/settings' },
      { id: 6, text: 'Logout', icon: 'fa fa-sign-out', href: '/logout' }
    ]
  }
})

const $route = useRoute()
</script>

<style scoped>
.sidebar-container {
  position: relative;

}

.sidebar {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 10px;
  position: fixed;
  top: 0;
  left: 0;
  width: 250px;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  height: 100vh;
  padding: 20px 40px;
  z-index: 10000;
}

.sidebar-header {
  padding: 20px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  min-height: 40px;
}

.logo h3 {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
}

.sidebar-nav {
  padding: 10px 0;
  flex-grow: 1;
}

.menu {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 0;
  list-style: none;
}

.nav-item {
  width: 100%;
  padding: 10px 12px;
  border-radius: 10px;
  transition: background 0.2s;
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

body.light-theme .nav-item:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: var(--text-dark);
  font-weight: 500;
}

.nav-link.active {
  font-weight: bold;
  color: var(--accent);
}

.sidebar-footer {
  padding: 20px 0;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 15px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
}
</style>
