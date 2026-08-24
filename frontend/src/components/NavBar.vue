<template>
  <div class="navbar">
    <nav class="nav-menu">
      <ul class="nav-items">
        <li class="nav-item">
          <button
            class="navbar-link"
            :class="{ active: activeTab === 'forYou' }"
            @click="$emit('tab-change', 'forYou')"
          >
            <span class="navText">For You</span>
          </button>
        </li>
        <li class="nav-item">
          <button
            class="navbar-link"
            :class="{ active: activeTab === 'feed' }"
            @click="$emit('tab-change', 'feed')"
          >
            <span class="navText">My Feed</span>
          </button>
        </li>
        <li class="nav-item">
          <button
            class="navbar-link"
            :class="{ active: activeTab === 'trending' }"
            @click="$emit('tab-change', 'trending')"
          >
            <span class="navText">Trending</span>
          </button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script setup>
defineProps({
  activeTab: {
    type: String,
    default: 'forYou',
  },
});

defineEmits(['tab-change']);
</script>

<style scoped>
.navbar {
  position: sticky;
  top: 0;
  height: 56px;
  width: 100%;
  max-width: 550px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  z-index: 100;
  display: flex;
  align-items: center;
  padding: 0 10px;
  background: var(--bg-dark);
  backdrop-filter: blur(10px);
}

.nav-menu {
  width: 100%;
}

.nav-items {
  list-style: none;
  display: flex;
  justify-content: space-around;
  padding: 0;
  margin: 0;
}

.nav-item {
  display: flex;
  align-items: center;
}

.navbar-link {
  background: none;
  border: none;
  color: #8b949e;
  font-size: 14px;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.navbar-link:hover {
  color: var(--text-dark);
  background-color: rgba(255, 255, 255, 0.08);
}

.navbar-link.active {
  color: #000;
  background-color: var(--accent);
}

.navText {
  font-size: 14px;
}
</style>
