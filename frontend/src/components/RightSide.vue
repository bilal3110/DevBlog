<template>
  <div class="search-panel-sidebar">
    <aside class="search-aside">
      <!-- Trending Box -->
      <div class="trending-box">
        <h3>Trending</h3>
        <ul class="trendlist">
          <li v-for="tag in trending" :key="tag.id" class="tagItem">
            <a :to="{ name: 'tag', params: { tag: tag.tagText } }" class="tagLink">
              <span class="tagText">{{ tag.tagText }}</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- People To Follow -->
      <div class="ToFollowList">
        <h3>People You May Know</h3>
        <ul class="followList">
          <li v-for="peep in follows" :key="peep.id" class="peep">
            <a :to="{ name: 'profile', params: { id: peep.id } }" class="followLink">
              <img :src="peep.avatar" alt="User Avatar" class="Follow-avatar"
                @error="e => e.target.src = 'https://i.pravatar.cc/40'" />
              <span class="followName">{{ peep.name }}</span>
              <!-- <span class="followHandle">{{ peep.handle }}</span> -->
            </a>
          </li>
        </ul>
      </div>
    </aside>
  </div>
</template>

<script setup>
defineProps({
  trending: {
    type: Array,
    default: () => [
      { id: 1, tagText: 'Laravel', href: '/tags/laravel' },
      { id: 2, tagText: 'PHP', href: '/tags/php' },
      { id: 3, tagText: 'AI', href: '/tags/ai' },
      { id: 4, tagText: 'ML', href: '/tags/ml' },
      { id: 5, tagText: 'SaaS', href: '/tags/saas' },
    ],
  },
  follows: {
    type: Array,
    default: () => [
      {
        id: 1,
        name: 'John Doe',
        avatar: 'https://i.pravatar.cc/40?img=1',
      },
      {
        id: 2,
        name: 'Jane Smith',
        avatar: 'https://i.pravatar.cc/40?img=2',
      },
      {
        id: 3,
        name: 'Alex Johnson',
        avatar: 'https://i.pravatar.cc/40?img=3',
      },
    ],
  },
});
</script>

<style scoped>
/* Sidebar container */
.search-panel-sidebar {
  position: fixed;
  top: 0;
  right: 0;
  border-left: 1px solid rgba(255, 255, 255, 0.1);
  height: 100vh;
  width: 250px;
  padding: 20px 40px;
  overflow-y: auto;
  z-index: 1000;
}

.search-panel-sidebar::-webkit-scrollbar {
  background: none;
}

/* Sidebar content */
.search-aside {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Search bar styling */
.search-bar {
  padding: 10px 10px 10px 0px;
}

.search-bar input {
  width: 100%;
  padding: 10px;
  border: 1px solid rgba(255, 255, 255, 0.171);
  border-radius: 6px;
  font-size: 14px;
  background: none;
  transition: border-color 0.3s ease;
  color: var(--text-dark);
}

.search-bar input:focus {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

/* Trending box styling */
.trending-box h3,
.ToFollowList h3 {
  font-size: 1.1em;
  font-weight: 600;
  margin-bottom: 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.171);
  ;
  padding-bottom: 8px;
}

.trendlist,
.followList {
  list-style: none;
  padding: 0;
  margin: 0;
}

.tagItem,
.peep {
  margin-bottom: 12px;
}

.tagLink,
.followLink {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  padding: 8px;
  border-radius: 6px;
  transition: background-color 0.2s ease;
}

.tagLink:hover,
.followLink:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

/* Avatar styling */
.Follow-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Follow name and handle */
.followName {
  font-weight: 500;
  font-size: 14px;
}

.followHandle {
  font-size: 12px;
  color: #6b7280;
}
</style>
