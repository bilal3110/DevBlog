<template>
  <div class="search-panel-sidebar">
    <aside class="search-aside">
      <!-- Search Input Shortcut -->
      <div class="search-box">
        <div class="search-input-box">
          <i class="fa fa-search"></i>
          <input
            type="text"
            placeholder="Quick search..."
            v-model="quickSearch"
            @keydown.enter="goToSearch"
          />
        </div>
      </div>

      <!-- Trending Box -->
      <div class="trending-box">
        <div class="box-header">
          <h3><i class="fa fa-fire text-accent"></i> Trending Topics</h3>
        </div>

        <div v-if="tagsLoading" class="sidebar-loading">
          <i class="fa fa-spinner fa-spin"></i>
        </div>

        <ul v-else-if="trendingTags.length > 0" class="trendlist">
          <li v-for="tag in trendingTags" :key="tag.id" class="tagItem">
            <router-link :to="`/explore?tag=${tag.slug || tag.name}`" class="tagLink">
              <span class="tagText">#{{ tag.name }}</span>
              <span v-if="tag.post_count" class="tagCount">{{ tag.post_count }} posts</span>
            </router-link>
          </li>
        </ul>

        <div v-else class="empty-sidebar-box">
          <p>No trending tags yet</p>
        </div>
      </div>

      <!-- People To Follow -->
      <div class="ToFollowList">
        <div class="box-header">
          <h3><i class="fa fa-users text-accent"></i> Developers to Follow</h3>
        </div>

        <div v-if="usersLoading" class="sidebar-loading">
          <i class="fa fa-spinner fa-spin"></i>
        </div>

        <ul v-else-if="suggestedUsers.length > 0" class="followList">
          <li v-for="peep in suggestedUsers" :key="peep.id" class="peep-item">
            <router-link :to="`/profile/${peep.id}`" class="peep-link">
              <img
                :src="getImageUrl(peep.avatar, 'https://i.pravatar.cc/150?img=12')"
                alt="Avatar"
                class="follow-avatar"
                @error="e => e.target.src = 'https://i.pravatar.cc/150?img=12'"
              />
              <div class="peep-info">
                <span class="followName">{{ peep.name }}</span>
                <span class="followEmail">{{ peep.email }}</span>
              </div>
            </router-link>

            <button
              v-if="!isCurrentUser(peep.id)"
              @click="toggleFollow(peep)"
              class="btn-quick-follow"
              :class="{ following: peep.is_following }"
            >
              {{ peep.is_following ? 'Following' : 'Follow' }}
            </button>
          </li>
        </ul>

        <div v-else class="empty-sidebar-box">
          <p>No new suggestions</p>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api, { getImageUrl } from '../utils/api';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const quickSearch = ref('');
const trendingTags = ref([]);
const suggestedUsers = ref([]);
const tagsLoading = ref(true);
const usersLoading = ref(true);

const isCurrentUser = (userId) => {
  return authStore.isAuthenticated && authStore.userId === userId;
};

const goToSearch = () => {
  if (quickSearch.value.trim()) {
    router.push(`/explore?q=${encodeURIComponent(quickSearch.value.trim())}`);
    quickSearch.value = '';
  }
};

const fetchTrending = async () => {
  tagsLoading.value = true;
  try {
    const data = await api.get('/tags/trending');
    trendingTags.value = (data.tags || []).slice(0, 6);
  } catch (err) {
    console.error('Failed to load trending tags:', err);
  } finally {
    tagsLoading.value = false;
  }
};

const fetchSuggestedUsers = async () => {
  usersLoading.value = true;
  try {
    const users = await api.get('/users', { limit: 5, exclude_me: 1 });
    suggestedUsers.value = users || [];
  } catch (err) {
    console.error('Failed to load users:', err);
  } finally {
    usersLoading.value = false;
  }
};

const toggleFollow = async (user) => {
  if (!authStore.isAuthenticated) {
    return router.push('/login');
  }
  try {
    const res = await api.post(`/users/follow/${user.id}`);
    user.is_following = res.following;
  } catch (err) {
    console.error('Follow toggle error:', err);
  }
};

onMounted(() => {
  fetchTrending();
  fetchSuggestedUsers();
});
</script>

<style scoped>
.search-panel-sidebar {
  position: fixed;
  top: 0;
  right: 0;
  border-left: 1px solid rgba(255, 255, 255, 0.1);
  height: 100vh;
  width: 280px;
  padding: 20px 24px;
  overflow-y: auto;
  background: var(--bg-dark);
  z-index: 1000;
}

.search-panel-sidebar::-webkit-scrollbar {
  width: 4px;
}

.search-panel-sidebar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}

.search-aside {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.search-input-box {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input-box i {
  position: absolute;
  left: 12px;
  color: #6e7681;
  font-size: 0.85rem;
}

.search-input-box input {
  width: 100%;
  padding: 8px 12px 8px 34px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 20px;
  color: var(--text-dark);
  font-size: 0.85rem;
  transition: all 0.2s;
}

.search-input-box input:focus {
  outline: none;
  border-color: var(--accent);
  background: rgba(255, 255, 255, 0.07);
}

.trending-box,
.ToFollowList {
  background: #0d1117;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 16px;
}

.box-header {
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.box-header h3 {
  font-size: 0.95rem;
  font-weight: 700;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text-dark);
}

.text-accent {
  color: var(--accent);
}

.trendlist,
.followList {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.tagLink {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 8px;
  border-radius: 6px;
  text-decoration: none;
  transition: background-color 0.2s;
}

.tagLink:hover {
  background-color: rgba(255, 255, 255, 0.08);
}

.tagText {
  color: #c9d1d9;
  font-weight: 500;
  font-size: 0.85rem;
}

.tagLink:hover .tagText {
  color: var(--accent);
}

.tagCount {
  font-size: 0.75rem;
  color: #8b949e;
}

.peep-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 6px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}

.peep-item:last-child {
  border-bottom: none;
}

.peep-link {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: inherit;
  flex: 1;
  min-width: 0;
}

.follow-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.peep-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.followName {
  font-weight: 600;
  font-size: 0.82rem;
  color: var(--text-dark);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.followEmail {
  font-size: 0.72rem;
  color: #8b949e;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.btn-quick-follow {
  padding: 4px 10px;
  background: var(--accent);
  color: #000;
  border: none;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  flex-shrink: 0;
  transition: opacity 0.2s;
}

.btn-quick-follow.following {
  background: rgba(255, 255, 255, 0.1);
  color: var(--text-dark);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-quick-follow:hover {
  opacity: 0.9;
}

.sidebar-loading,
.empty-sidebar-box {
  text-align: center;
  padding: 16px;
  color: #8b949e;
  font-size: 0.82rem;
}
</style>
