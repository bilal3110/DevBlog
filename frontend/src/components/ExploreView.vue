<template>
  <div class="explore-container">
    <!-- Search Bar -->
    <div class="search-section">
      <form @submit.prevent="handleSearch" class="search-form">
        <div class="search-input-wrapper">
          <i class="fa fa-search search-icon"></i>
          <input
            type="text"
            placeholder="Search articles, topics, or developers..."
            v-model="searchQuery"
            @input="debounceSearch"
            class="search-input"
          />
          <button v-if="searchQuery" type="button" @click="clearSearch" class="clear-btn">
            <i class="fa fa-times"></i>
          </button>
        </div>
      </form>
    </div>

    <!-- Active Tag Filter Banner -->
    <div v-if="selectedTag" class="active-tag-banner">
      <div class="tag-label">
        <span>Posts tagged with:</span>
        <strong>#{{ selectedTagName }}</strong>
      </div>
      <button @click="clearTagFilter" class="btn-clear-tag">
        <i class="fa fa-times"></i> Clear Filter
      </button>
    </div>

    <!-- Search Results View -->
    <div v-if="searchQuery.trim()" class="search-results-section">
      <div class="search-tabs">
        <button
          @click="searchTab = 'posts'"
          class="tab-btn"
          :class="{ active: searchTab === 'posts' }"
        >
          Articles ({{ postResults.length }})
        </button>
        <button
          @click="searchTab = 'users'"
          class="tab-btn"
          :class="{ active: searchTab === 'users' }"
        >
          Developers ({{ userResults.length }})
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="searching" class="loading-state">
        <i class="fa fa-spinner fa-spin"></i> Searching...
      </div>

      <!-- Post Results -->
      <div v-else-if="searchTab === 'posts'">
        <div v-if="postResults.length === 0" class="no-results">
          <p>No articles matching "<strong>{{ searchQuery }}</strong>"</p>
        </div>
        <div v-else>
          <BlogCard :posts="postResults" />
        </div>
      </div>

      <!-- User Results -->
      <div v-else-if="searchTab === 'users'">
        <div v-if="userResults.length === 0" class="no-results">
          <p>No developers matching "<strong>{{ searchQuery }}</strong>"</p>
        </div>
        <div v-else class="users-list">
          <div v-for="user in userResults" :key="user.id" class="user-search-card">
            <router-link :to="`/profile/${user.id}`" class="user-card-link">
              <img
                :src="getImageUrl(user.avatar, 'https://i.pravatar.cc/150?img=12')"
                alt="Avatar"
                class="user-avatar"
                @error="e => e.target.src = 'https://i.pravatar.cc/150?img=12'"
              />
              <div class="user-text">
                <span class="user-card-name">{{ user.name }}</span>
                <span class="user-card-email">{{ user.email }}</span>
                <p v-if="user.bio" class="user-card-bio">{{ user.bio }}</p>
              </div>
            </router-link>

            <button
              v-if="!isCurrentUser(user.id)"
              @click="toggleFollowUser(user)"
              class="btn-user-follow"
              :class="{ following: user.is_following }"
            >
              {{ user.is_following ? 'Following' : 'Follow' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tag Posts View -->
    <div v-else-if="selectedTag" class="tag-posts-section">
      <div v-if="tagLoading" class="loading-state">
        <i class="fa fa-spinner fa-spin"></i> Loading tagged articles...
      </div>
      <div v-else-if="tagPosts.length === 0" class="no-results">
        <p>No articles found with this tag.</p>
      </div>
      <div v-else>
        <BlogCard :posts="tagPosts" />
      </div>
    </div>

    <!-- Default Explore View: Tags & Discovery -->
    <div v-else class="tags-discovery-section">
      <!-- Tag Toggle Navigation -->
      <div class="tag-toggle-buttons">
        <button
          @click="activeTagTab = 'top'"
          class="tab-link"
          :class="{ active: activeTagTab === 'top' }"
        >
          <i class="fa fa-fire"></i> Trending Tags
        </button>
        <button
          @click="activeTagTab = 'all'"
          class="tab-link"
          :class="{ active: activeTagTab === 'all' }"
        >
          <i class="fa fa-tags"></i> All Tags
        </button>
      </div>

      <!-- Tag Clouds -->
      <div v-if="tagsLoading" class="loading-state">
        <i class="fa fa-spinner fa-spin"></i> Loading tags...
      </div>

      <div v-else class="tags-grid">
        <!-- Top Tags -->
        <div v-if="activeTagTab === 'top'" class="tags-cloud">
          <div
            v-for="tag in topTags"
            :key="tag.id"
            class="tag-item"
            @click="selectTag(tag)"
          >
            <span class="tag-title">#{{ tag.name }}</span>
            <span class="tag-count">{{ tag.post_count || 0 }} posts</span>
          </div>
          <div v-if="topTags.length === 0" class="no-tags">
            <p>No trending tags found.</p>
          </div>
        </div>

        <!-- All Tags -->
        <div v-else-if="activeTagTab === 'all'" class="tags-cloud">
          <div
            v-for="tag in allTags"
            :key="tag.id"
            class="tag-item"
            @click="selectTag(tag)"
          >
            <span class="tag-title">#{{ tag.name }}</span>
          </div>
          <div v-if="allTags.length === 0" class="no-tags">
            <p>No tags created yet.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api, { getImageUrl } from '../utils/api';
import { useAuthStore } from '../stores/auth';
import BlogCard from './BlogCard.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const searchQuery = ref('');
const searchTab = ref('posts');
const searching = ref(false);
const postResults = ref([]);
const userResults = ref([]);

const activeTagTab = ref('top');
const topTags = ref([]);
const allTags = ref([]);
const tagsLoading = ref(false);

const selectedTag = ref(null);
const selectedTagName = ref('');
const tagPosts = ref([]);
const tagLoading = ref(false);

let searchTimeout = null;

const isCurrentUser = (userId) => {
  return authStore.isAuthenticated && authStore.userId === userId;
};

const fetchTags = async () => {
  tagsLoading.value = true;
  try {
    const [topData, allData] = await Promise.all([
      api.get('/tags/trending').catch(() => ({ tags: [] })),
      api.get('/tags').catch(() => ({ tags: [] })),
    ]);
    topTags.value = topData.tags || [];
    allTags.value = allData.tags || [];
  } catch (err) {
    console.error('Failed to load tags:', err);
  } finally {
    tagsLoading.value = false;
  }
};

const debounceSearch = () => {
  clearTimeout(searchTimeout);
  if (!searchQuery.value.trim()) {
    postResults.value = [];
    userResults.value = [];
    return;
  }
  searchTimeout = setTimeout(() => {
    handleSearch();
  }, 350);
};

const handleSearch = async () => {
  const query = searchQuery.value.trim();
  if (!query) return;

  searching.value = true;
  selectedTag.value = null;

  try {
    const [posts, users] = await Promise.all([
      api.get('/posts', { search: query }).catch(() => []),
      api.get('/users', { search: query }).catch(() => []),
    ]);
    postResults.value = posts || [];
    userResults.value = users || [];
  } catch (err) {
    console.error('Search failed:', err);
  } finally {
    searching.value = false;
  }
};

const clearSearch = () => {
  searchQuery.value = '';
  postResults.value = [];
  userResults.value = [];
};

const selectTag = async (tag) => {
  selectedTag.value = tag.slug || tag.id || tag.name;
  selectedTagName.value = tag.name;
  searchQuery.value = '';
  loadTagPosts(tag.id || tag.slug);
};

const loadTagPosts = async (tagIdentifier) => {
  tagLoading.value = true;
  try {
    const res = await api.get(`/tags/posts/${tagIdentifier}`);
    tagPosts.value = res.posts || [];
  } catch (err) {
    console.error('Failed to load tag posts:', err);
  } finally {
    tagLoading.value = false;
  }
};

const clearTagFilter = () => {
  selectedTag.value = null;
  selectedTagName.value = '';
  tagPosts.value = [];
  router.replace({ path: '/explore' });
};

const toggleFollowUser = async (user) => {
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
  fetchTags();
  if (route.query.tag) {
    selectedTag.value = route.query.tag;
    selectedTagName.value = route.query.tag;
    loadTagPosts(route.query.tag);
  }
  if (route.query.q) {
    searchQuery.value = route.query.q;
    handleSearch();
  }
});

watch(
  () => route.query.tag,
  (newTag) => {
    if (newTag) {
      selectedTag.value = newTag;
      selectedTagName.value = newTag;
      loadTagPosts(newTag);
    } else if (!route.query.q) {
      selectedTag.value = null;
      selectedTagName.value = '';
    }
  }
);
</script>

<style scoped>
.explore-container {
  width: 100%;
  max-width: 550px;
  margin: 15px auto 80px;
}

.search-section {
  margin-bottom: 20px;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 14px;
  color: #6e7681;
}

.search-input {
  width: 100%;
  padding: 12px 40px 12px 40px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 24px;
  color: var(--text-dark);
  font-size: 0.95rem;
  transition: all 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: var(--accent);
  background: rgba(255, 255, 255, 0.07);
  box-shadow: 0 0 0 3px rgba(66, 184, 131, 0.15);
}

.clear-btn {
  position: absolute;
  right: 14px;
  background: none;
  border: none;
  color: #8b949e;
  cursor: pointer;
}

.active-tag-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: rgba(66, 184, 131, 0.1);
  border: 1px solid rgba(66, 184, 131, 0.25);
  border-radius: 8px;
  margin-bottom: 20px;
}

.tag-label {
  display: flex;
  gap: 8px;
  color: var(--text-dark);
  font-size: 0.9rem;
}

.tag-label strong {
  color: var(--accent);
}

.btn-clear-tag {
  background: none;
  border: none;
  color: #8b949e;
  font-size: 0.85rem;
  cursor: pointer;
}

.btn-clear-tag:hover {
  color: #ef4444;
}

.search-tabs {
  display: flex;
  gap: 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 20px;
  padding-bottom: 10px;
}

.tab-btn {
  background: none;
  border: none;
  color: #8b949e;
  font-size: 0.9rem;
  font-weight: 600;
  padding: 6px 14px;
  border-radius: 6px;
  cursor: pointer;
}

.tab-btn.active {
  background: rgba(255, 255, 255, 0.1);
  color: var(--accent);
}

.tag-toggle-buttons {
  display: flex;
  justify-content: space-around;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 20px;
}

.tab-link {
  background: none;
  border: none;
  color: #8b949e;
  font-size: 0.95rem;
  font-weight: 600;
  padding: 12px 20px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  border-bottom: 2px solid transparent;
  transition: all 0.2s;
}

.tab-link:hover {
  color: var(--text-dark);
}

.tab-link.active {
  color: var(--accent);
  border-bottom-color: var(--accent);
}

.tags-grid {
  padding: 10px 0;
}

.tags-cloud {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 12px;
}

.tag-item {
  background: #0d1117;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 14px;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  gap: 4px;
  transition: transform 0.2s, border-color 0.2s;
}

.tag-item:hover {
  border-color: var(--accent);
  transform: translateY(-2px);
}

.tag-title {
  font-weight: 600;
  color: var(--text-dark);
  font-size: 0.95rem;
}

.tag-count {
  font-size: 0.78rem;
  color: #8b949e;
}

.users-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.user-search-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #0d1117;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 14px 18px;
}

.user-card-link {
  display: flex;
  align-items: center;
  gap: 14px;
  text-decoration: none;
  color: inherit;
  flex: 1;
}

.user-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.user-text {
  display: flex;
  flex-direction: column;
}

.user-card-name {
  font-weight: 600;
  color: var(--text-dark);
  font-size: 0.95rem;
}

.user-card-email {
  font-size: 0.8rem;
  color: #8b949e;
}

.user-card-bio {
  font-size: 0.82rem;
  color: #c9d1d9;
  margin: 4px 0 0 0;
}

.btn-user-follow {
  padding: 6px 16px;
  background: var(--accent);
  color: #000;
  border: none;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-user-follow.following {
  background: rgba(255, 255, 255, 0.1);
  color: var(--text-dark);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.loading-state,
.no-results,
.no-tags {
  text-align: center;
  padding: 40px 20px;
  color: #8b949e;
}
</style>
