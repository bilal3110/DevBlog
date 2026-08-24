<template>
  <div class="profile-container">
    <div v-if="loading" class="loading-state">
      <i class="fa fa-spinner fa-spin fa-2x"></i>
      <p>Loading profile...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <i class="fa fa-user-times fa-2x"></i>
      <h2>User Not Found</h2>
      <p>{{ error }}</p>
      <router-link to="/" class="btn-back">Back to Home</router-link>
    </div>

    <div v-else-if="user" class="profile-content">
      <!-- Profile Header Card -->
      <div class="profile-card">
        <div class="profile-main">
          <img
            :src="getImageUrl(user.avatar, 'https://i.pravatar.cc/150?img=12')"
            alt="User Avatar"
            class="profile-avatar"
            @error="e => e.target.src = 'https://i.pravatar.cc/150?img=12'"
          />

          <div class="profile-info">
            <div class="name-actions">
              <h1 class="user-name">{{ user.name }}</h1>
              
              <!-- Action Button: Edit or Follow -->
              <router-link v-if="isMe" to="/settings" class="btn-edit-profile">
                <i class="fa fa-cog"></i> Edit Profile
              </router-link>
              <button
                v-else
                @click="toggleFollow"
                class="btn-follow"
                :class="{ following: user.is_following }"
                :disabled="followLoading"
              >
                <i :class="user.is_following ? 'fa fa-user-check' : 'fa fa-user-plus'"></i>
                {{ user.is_following ? 'Following' : 'Follow' }}
              </button>
            </div>

            <p class="user-email">{{ user.email }}</p>

            <p v-if="user.bio" class="user-bio">{{ user.bio }}</p>
            <p v-else class="user-bio empty-bio">No bio provided yet.</p>

            <!-- Social Links -->
            <div class="social-links">
              <a v-if="user.github" :href="user.github" target="_blank" rel="noopener noreferrer" class="social-badge">
                <i class="fab fa-github"></i> GitHub
              </a>
              <a v-if="user.portfolio" :href="user.portfolio" target="_blank" rel="noopener noreferrer" class="social-badge">
                <i class="fa fa-globe"></i> Portfolio
              </a>
            </div>

            <!-- Stats Bar -->
            <div class="stats-row">
              <div class="stat-item">
                <span class="stat-num">{{ userPosts.length }}</span>
                <span class="stat-label">Posts</span>
              </div>
              <div class="stat-item">
                <span class="stat-num">{{ user.followers_count ?? 0 }}</span>
                <span class="stat-label">Followers</span>
              </div>
              <div class="stat-item">
                <span class="stat-num">{{ user.following_count ?? 0 }}</span>
                <span class="stat-label">Following</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- User's Posts Section -->
      <div class="user-posts-section">
        <h2 class="section-title">Articles by {{ isMe ? 'You' : user.name }}</h2>

        <div v-if="postsLoading" class="posts-loading">
          <i class="fa fa-spinner fa-spin"></i> Loading articles...
        </div>

        <div v-else-if="userPosts.length === 0" class="no-posts">
          <i class="fa fa-newspaper fa-2x"></i>
          <p>{{ isMe ? "You haven't written any articles yet." : "This author hasn't published any articles yet." }}</p>
          <router-link v-if="isMe" to="/createpost" class="btn-create">Write your first post</router-link>
        </div>

        <div v-else class="posts-list">
          <article v-for="post in userPosts" :key="post.id" class="post-item">
            <div class="post-item-content">
              <div class="post-item-header">
                <span class="post-item-date">{{ formatDate(post.created_at) }}</span>
                <div v-if="post.tags && post.tags.length > 0" class="post-item-tags">
                  <span v-for="tag in post.tags" :key="tag.id" class="tag-pill">#{{ tag.name }}</span>
                </div>
              </div>

              <router-link :to="`/posts/${post.slug}`" class="post-item-title">
                {{ post.title }}
              </router-link>

              <div class="post-item-footer">
                <div class="post-counts">
                  <span><i class="far fa-heart"></i> {{ post.like_count ?? (post.like ? post.like.length : 0) }}</span>
                  <span><i class="far fa-comment"></i> {{ post.comments_count ?? 0 }}</span>
                </div>
                <router-link :to="`/posts/${post.slug}`" class="read-link">Read full post →</router-link>
              </div>
            </div>

            <div v-if="post.cover_image" class="post-item-thumb">
              <img :src="getImageUrl(post.cover_image)" :alt="post.title" />
            </div>
          </article>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api, { getImageUrl } from '../utils/api';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const user = ref(null);
const userPosts = ref([]);
const loading = ref(true);
const postsLoading = ref(false);
const error = ref('');
const followLoading = ref(false);

const isMe = computed(() => {
  if (!authStore.isAuthenticated) return false;
  if (!route.params.id) return true;
  return Number(route.params.id) === Number(authStore.userId);
});

const fetchProfile = async () => {
  loading.value = true;
  error.value = '';

  try {
    let targetId = route.params.id;
    if (!targetId) {
      if (!authStore.isAuthenticated) {
        return router.push('/login');
      }
      targetId = authStore.userId;
    }

    const userData = await api.get(`/users/${targetId}`);
    user.value = userData;
    fetchUserPosts(targetId);
  } catch (err) {
    error.value = err.message || 'Failed to load user profile.';
  } finally {
    loading.value = false;
  }
};

const fetchUserPosts = async (userId) => {
  postsLoading.value = true;
  try {
    const posts = await api.get('/posts', { user_id: userId });
    userPosts.value = posts || [];
  } catch (err) {
    console.error('Failed to load user posts:', err);
  } finally {
    postsLoading.value = false;
  }
};

const toggleFollow = async () => {
  if (!authStore.isAuthenticated) {
    return router.push('/login');
  }
  if (!user.value) return;

  followLoading.value = true;
  try {
    const res = await api.post(`/users/follow/${user.value.id}`);
    user.value.is_following = res.following;
    if (res.total_followers !== undefined) {
      user.value.followers_count = res.total_followers;
    } else {
      user.value.followers_count = (user.value.followers_count || 0) + (res.following ? 1 : -1);
    }
  } catch (err) {
    alert(err.message || 'Could not update follow state');
  } finally {
    followLoading.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};

onMounted(fetchProfile);

watch(
  () => route.params.id,
  () => {
    fetchProfile();
  }
);
</script>

<style scoped>
.profile-container {
  width: 100%;
  max-width: 680px;
  padding: 30px 15px 80px;
  margin: 0 auto;
}

.loading-state,
.error-state {
  text-align: center;
  padding: 60px 20px;
  color: #8b949e;
}

.btn-back {
  display: inline-block;
  margin-top: 15px;
  padding: 8px 16px;
  background: var(--accent);
  color: #000;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 600;
}

.profile-card {
  background: #0d1117;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  padding: 28px;
  margin-bottom: 30px;
}

.profile-main {
  display: flex;
  gap: 24px;
}

.profile-avatar {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--accent);
  flex-shrink: 0;
}

.profile-info {
  flex: 1;
}

.name-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.user-name {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
  color: var(--text-dark);
}

.user-email {
  font-size: 0.85rem;
  color: #8b949e;
  margin: 0 0 12px 0;
}

.btn-edit-profile {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: var(--text-dark);
  border-radius: 6px;
  text-decoration: none;
  font-size: 0.82rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-edit-profile:hover {
  background: rgba(255, 255, 255, 0.15);
}

.btn-follow {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 16px;
  background: var(--accent);
  color: #000;
  border: none;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-follow.following {
  background: rgba(255, 255, 255, 0.1);
  color: var(--text-dark);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-follow:hover {
  opacity: 0.9;
}

.user-bio {
  font-size: 0.92rem;
  line-height: 1.5;
  color: #c9d1d9;
  margin-bottom: 14px;
}

.empty-bio {
  color: #6e7681;
  font-style: italic;
}

.social-links {
  display: flex;
  gap: 10px;
  margin-bottom: 18px;
}

.social-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  color: #8b949e;
  text-decoration: none;
  font-size: 0.8rem;
  transition: all 0.2s;
}

.social-badge:hover {
  color: var(--accent);
  border-color: var(--accent);
}

.stats-row {
  display: flex;
  gap: 24px;
  padding-top: 14px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.stat-item {
  display: flex;
  flex-direction: column;
}

.stat-num {
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--text-dark);
}

.stat-label {
  font-size: 0.78rem;
  color: #8b949e;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 20px;
}

.posts-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.post-item {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  background: #0d1117;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 20px;
  transition: transform 0.2s, border-color 0.2s;
}

.post-item:hover {
  border-color: rgba(66, 184, 131, 0.4);
  transform: translateY(-2px);
}

.post-item-content {
  flex: 1;
}

.post-item-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
}

.post-item-date {
  font-size: 0.78rem;
  color: #8b949e;
}

.post-item-tags {
  display: flex;
  gap: 6px;
}

.tag-pill {
  font-size: 0.75rem;
  color: var(--accent);
}

.post-item-title {
  font-size: 1.15rem;
  font-weight: 600;
  color: var(--text-dark);
  text-decoration: none;
  display: block;
  margin-bottom: 12px;
  line-height: 1.4;
}

.post-item-title:hover {
  color: var(--accent);
}

.post-item-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
}

.post-counts {
  display: flex;
  gap: 16px;
  color: #8b949e;
}

.read-link {
  color: var(--accent);
  text-decoration: none;
  font-weight: 500;
  font-size: 0.85rem;
}

.post-item-thumb {
  width: 100px;
  height: 80px;
  border-radius: 6px;
  overflow: hidden;
  flex-shrink: 0;
}

.post-item-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-posts {
  text-align: center;
  padding: 40px;
  background: #0d1117;
  border-radius: 10px;
  color: #8b949e;
}

.btn-create {
  display: inline-block;
  margin-top: 12px;
  padding: 8px 16px;
  background: var(--accent);
  color: #000;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 600;
}
</style>
