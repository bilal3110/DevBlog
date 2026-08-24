<template>
  <div class="sidebar-container">
    <aside class="sidebar">
      <!-- Header / Logo -->
      <div class="sidebar-header">
        <router-link to="/" class="logo-link">
          <h3 class="logo-text">Dev<span>Blogs</span></h3>
        </router-link>
      </div>

      <!-- Navigation Menu -->
      <nav class="sidebar-nav">
        <ul class="menu">
          <li class="nav-item">
            <router-link to="/" class="nav-link" :class="{ active: route.path === '/' }">
              <span class="fa fa-home nav-icon"></span>
              <span class="nav-text">Home</span>
            </router-link>
          </li>
          <li class="nav-item">
            <router-link to="/explore" class="nav-link" :class="{ active: route.path === '/explore' }">
              <span class="fa fa-search nav-icon"></span>
              <span class="nav-text">Explore</span>
            </router-link>
          </li>
          <li class="nav-item">
            <router-link to="/createpost" class="nav-link" :class="{ active: route.path === '/createpost' }">
              <span class="fa fa-plus-circle nav-icon"></span>
              <span class="nav-text">Create</span>
            </router-link>
          </li>
          <li class="nav-item">
            <router-link to="/notifications" class="nav-link" :class="{ active: route.path === '/notifications' }">
              <span class="fa fa-bell nav-icon"></span>
              <span class="nav-text">Notifications</span>
            </router-link>
          </li>
          <li v-if="authStore.isAuthenticated" class="nav-item">
            <router-link to="/settings" class="nav-link" :class="{ active: route.path === '/settings' }">
              <span class="fa fa-cog nav-icon"></span>
              <span class="nav-text">Settings</span>
            </router-link>
          </li>

          <!-- Auth Actions -->
          <li v-if="authStore.isAuthenticated" class="nav-item">
            <button @click="handleLogout" class="nav-link logout-btn">
              <span class="fa fa-sign-out-alt nav-icon"></span>
              <span class="nav-text">Logout</span>
            </button>
          </li>
          <li v-else class="nav-item">
            <router-link to="/login" class="nav-link auth-highlight" :class="{ active: route.path === '/login' }">
              <span class="fa fa-sign-in-alt nav-icon"></span>
              <span class="nav-text">Log In</span>
            </router-link>
          </li>
        </ul>
      </nav>

      <!-- Footer / User Info -->
      <div class="sidebar-footer">
        <router-link
          v-if="authStore.isAuthenticated"
          :to="`/profile/${authStore.userId}`"
          class="user-info"
        >
          <img
            :src="authStore.userAvatar"
            alt="User Avatar"
            class="avatar"
            @error="e => e.target.src = 'https://i.pravatar.cc/150?img=12'"
          />
          <span class="user-name-text">{{ authStore.userFullName }}</span>
        </router-link>

        <div v-else class="guest-info">
          <router-link to="/register" class="btn-join">Join Community</router-link>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>

<style scoped>
.sidebar-container {
  width: 300px;
  flex-shrink: 0;
}

.sidebar {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: fixed;
  top: 0;
  left: 0;
  width: 250px;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  height: 100vh;
  padding: 20px 24px;
  background: var(--bg-dark);
  z-index: 1000;
}

.sidebar-header {
  padding: 10px 0 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo-link {
  text-decoration: none;
}

.logo-text {
  font-size: 1.5rem;
  font-weight: 800;
  margin: 0;
  color: var(--text-dark);
}

.logo-text span {
  color: var(--accent);
}

.sidebar-nav {
  padding: 20px 0;
  flex-grow: 1;
}

.menu {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 0;
  margin: 0;
  list-style: none;
}

.nav-item {
  width: 100%;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 14px;
  width: 90%;
  padding: 12px 14px;
  border-radius: 8px;
  text-decoration: none;
  color: #c9d1d9;
  font-weight: 500;
  font-size: 0.95rem;
  background: none;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: left;
}

.nav-icon {
  font-size: 1.1rem;
  width: 20px;
  text-align: center;
}

.nav-link:hover {
  background-color: rgba(255, 255, 255, 0.08);
  color: var(--text-dark);
}

.nav-link.active {
  font-weight: 700;
  color: #000;
  background-color: var(--accent);
}

.nav-link.active .nav-icon {
  color: #000;
}

.logout-btn {
  color: #ef4444;
}

.logout-btn:hover {
  background: rgba(239, 68, 68, 0.15);
  color: #f87171;
}

.auth-highlight {
  color: var(--accent);
  border: 1px solid rgba(66, 184, 131, 0.3);
}

.sidebar-footer {
  padding: 16px 0;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: var(--text-dark);
  padding: 6px;
  border-radius: 8px;
  transition: background 0.2s;
}

.user-info:hover {
  background: rgba(255, 255, 255, 0.08);
}

.avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--accent);
}

.user-name-text {
  font-weight: 600;
  font-size: 0.9rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.guest-info {
  text-align: center;
}

.btn-join {
  display: block;
  padding: 8px 12px;
  background: var(--accent);
  color: #000;
  font-weight: 600;
  border-radius: 6px;
  text-decoration: none;
  font-size: 0.88rem;
}
</style>
