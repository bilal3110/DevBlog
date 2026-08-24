<template>
  <div class="blogs-wrapper">
    <!-- Loading State -->
    <div v-if="loading" class="state-container">
      <div v-for="n in 3" :key="n" class="skeleton-card">
        <div class="skeleton-header">
          <div class="skeleton-avatar"></div>
          <div class="skeleton-lines">
            <div class="skeleton-line short"></div>
            <div class="skeleton-line tiny"></div>
          </div>
        </div>
        <div class="skeleton-body">
          <div class="skeleton-line title"></div>
          <div class="skeleton-line"></div>
          <div class="skeleton-image"></div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="state-container error-state">
      <i class="fa fa-exclamation-circle fa-2x"></i>
      <p>{{ error }}</p>
      <button @click="fetchPosts" class="btn-retry">Try Again</button>
    </div>

    <!-- Empty State -->
    <div v-else-if="postList.length === 0" class="state-container empty-state">
      <i class="fa fa-feather-alt fa-3x"></i>
      <h3>No articles yet</h3>
      <p v-if="tab === 'feed'">You aren't following anyone with recent posts, or they haven't posted yet.</p>
      <p v-else>Be the first developer to publish an article on DevBlogs!</p>
      <router-link to="/createpost" class="btn-create-post">Create an Article</router-link>
    </div>

    <!-- Real Posts List -->
    <div v-else class="blogs-list">
      <article v-for="card in postList" :key="card.id" class="blog-card">
        <!-- Card Header: Author Info -->
        <div class="card-header">
          <router-link :to="`/profile/${card.user?.id || card.user_id}`" class="author-info">
            <img
              :src="getImageUrl(card.user?.avatar, 'https://i.pravatar.cc/150?img=12')"
              alt="User Avatar"
              class="author-avatar"
              @error="e => e.target.src = 'https://i.pravatar.cc/150?img=12'"
            />
            <div class="user-meta">
              <span class="blogUserName">{{ card.user?.name || 'Developer' }}</span>
              <span class="post-date">{{ formatDate(card.created_at) }}</span>
            </div>
          </router-link>

          <!-- Post Tags Pill on Header -->
          <div v-if="card.tags && card.tags.length > 0" class="card-header-tags">
            <router-link
              v-for="tag in card.tags.slice(0, 2)"
              :key="tag.id"
              :to="`/explore?tag=${tag.slug || tag.name}`"
              class="tag-pill"
            >
              #{{ tag.name }}
            </router-link>
          </div>
        </div>

        <!-- Card Content -->
        <div class="blog-content">
          <router-link :to="`/posts/${card.slug}`" class="title-link">
            <h2 class="blogTitle">{{ card.title }}</h2>
          </router-link>

          <div class="blog-excerpt">
            <p>{{ getExcerpt(card.content) }}</p>
          </div>

          <!-- Cover Image -->
          <div v-if="card.cover_image" class="blog-image">
            <router-link :to="`/posts/${card.slug}`">
              <img :src="getImageUrl(card.cover_image)" :alt="card.title" loading="lazy" />
            </router-link>
          </div>

          <!-- Action Buttons -->
          <div class="blog-actions">
            <router-link :to="`/posts/${card.slug}`" class="read-btn">
              Read More
            </router-link>

            <div class="interaction-buttons">
              <!-- Like Button -->
              <button
                @click="toggleLike(card)"
                :class="['action-btn', 'like-btn', { liked: card.is_liked }]"
                title="Like"
              >
                <i :class="card.is_liked ? 'fa fa-heart' : 'far fa-heart'"></i>
                <span>{{ card.like_count ?? (card.like ? card.like.length : 0) }}</span>
              </button>

              <!-- Comment Button -->
              <router-link
                :to="`/posts/${card.slug}#comments-section`"
                class="action-btn comment-btn"
                title="Comments"
              >
                <i class="far fa-comment"></i>
                <span>{{ card.comments_count ?? (card.comments ? card.comments.length : 0) }}</span>
              </router-link>

              <!-- Share Button -->
              <button
                @click="sharePost(card)"
                class="action-btn share-btn"
                title="Share Article"
              >
                <i class="fa fa-share-alt"></i>
                <span>{{ copiedPostId === card.id ? 'Copied!' : 'Share' }}</span>
              </button>
            </div>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api, { getImageUrl } from '../utils/api';
import { useAuthStore } from '../stores/auth';

const props = defineProps({
  tab: {
    type: String,
    default: 'forYou',
  },
  tag: {
    type: String,
    default: '',
  },
  search: {
    type: String,
    default: '',
  },
  posts: {
    type: Array,
    default: null,
  },
});

const router = useRouter();
const authStore = useAuthStore();

const postList = ref([]);
const loading = ref(true);
const error = ref('');
const copiedPostId = ref(null);

const fetchPosts = async () => {
  if (props.posts) {
    postList.value = props.posts;
    loading.value = false;
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    const params = {};
    if (props.tab && props.tab !== 'forYou') {
      params.tab = props.tab;
    }
    if (props.tag) {
      params.tag = props.tag;
    }
    if (props.search) {
      params.search = props.search;
    }

    const data = await api.get('/posts', params);
    postList.value = data || [];
  } catch (err) {
    error.value = err.message || 'Failed to load posts';
  } finally {
    loading.value = false;
  }
};

const toggleLike = async (card) => {
  if (!authStore.isAuthenticated) {
    return router.push('/login');
  }

  const prevLiked = card.is_liked;
  const currentCount = card.like_count ?? 0;
  
  // Optimistic update
  card.is_liked = !prevLiked;
  card.like_count = currentCount + (card.is_liked ? 1 : -1);

  try {
    const res = await api.post(`/posts/likes/create/${card.id}`);
    card.is_liked = res.liked;
    if (res.total_likes !== undefined) {
      card.like_count = res.total_likes;
    }
  } catch (err) {
    // Revert optimistic update on failure
    card.is_liked = prevLiked;
    card.like_count = currentCount;
    console.error('Like toggle failed:', err);
  }
};

const sharePost = async (card) => {
  const postUrl = `${window.location.origin}/posts/${card.slug}`;

  // Track share on backend if user is logged in
  if (authStore.isAuthenticated) {
    api.post(`/post/share/${card.id}`, { platform: 'x/twitter' }).catch(() => {});
  }

  if (navigator.share) {
    try {
      await navigator.share({
        title: card.title,
        text: getExcerpt(card.content),
        url: postUrl,
      });
    } catch {
      // User cancelled or share failed
    }
  } else {
    navigator.clipboard.writeText(postUrl).then(() => {
      copiedPostId.value = card.id;
      setTimeout(() => {
        copiedPostId.value = null;
      }, 1500);
    });
  }
};

const getExcerpt = (content) => {
  if (!content) return '';
  // Strip HTML tags and entities
  const tmp = document.createElement('DIV');
  tmp.innerHTML = content;
  const text = tmp.textContent || tmp.innerText || '';
  return text.length > 160 ? `${text.slice(0, 160)}...` : text;
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const now = new Date();
  const diffTime = Math.abs(now - date);
  const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

  if (diffDays === 0) {
    const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
    if (diffHours === 0) {
      const diffMins = Math.floor(diffTime / (1000 * 60));
      return diffMins <= 1 ? 'Just now' : `${diffMins}m ago`;
    }
    return `${diffHours}h ago`;
  }
  if (diffDays === 1) return 'Yesterday';
  if (diffDays < 7) return `${diffDays}d ago`;

  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined,
  });
};

onMounted(fetchPosts);

watch(
  () => [props.tab, props.tag, props.search],
  () => {
    fetchPosts();
  }
);
</script>

<style scoped>
.blogs-wrapper {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
  max-width: 550px;
  margin: 20px auto 80px;
}

.blogs-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.blog-card {
  width: 100%;
  border-radius: 12px;
  background: #0d1117;
  border: 1px solid rgba(255, 255, 255, 0.12);
  overflow: hidden;
  transition: transform 0.2s ease, border-color 0.2s ease;
}

.blog-card:hover {
  transform: translateY(-2px);
  border-color: rgba(66, 184, 131, 0.4);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.author-info {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: inherit;
}

.author-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  margin-right: 12px;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.user-meta {
  display: flex;
  flex-direction: column;
}

.blogUserName {
  font-weight: 600;
  font-size: 14px;
  color: var(--text-dark);
}

.blogUserName:hover {
  color: var(--accent);
}

.post-date {
  font-size: 12px;
  color: #8b949e;
  margin-top: 2px;
}

.card-header-tags {
  display: flex;
  gap: 6px;
}

.tag-pill {
  font-size: 12px;
  color: var(--accent);
  text-decoration: none;
  background: rgba(66, 184, 131, 0.1);
  padding: 2px 8px;
  border-radius: 12px;
}

.blog-content {
  padding: 16px;
}

.title-link {
  text-decoration: none;
  color: inherit;
}

.blogTitle {
  font-size: 18px;
  font-weight: 700;
  line-height: 1.4;
  margin: 0 0 10px 0;
  color: var(--text-dark);
}

.blogTitle:hover {
  color: var(--accent);
}

.blog-excerpt p {
  line-height: 1.6;
  margin: 0;
  font-size: 14px;
  color: #c9d1d9;
}

.blog-image {
  margin: 14px 0;
  border-radius: 8px;
  overflow: hidden;
  max-height: 220px;
}

.blog-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.blog-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 16px;
  padding-top: 14px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.read-btn {
  background: var(--accent);
  color: #000;
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  transition: opacity 0.2s;
}

.read-btn:hover {
  opacity: 0.9;
}

.interaction-buttons {
  display: flex;
  gap: 14px;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  background: none;
  border: none;
  color: #8b949e;
  cursor: pointer;
  padding: 6px 8px;
  border-radius: 4px;
  font-size: 13px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.action-btn:hover {
  background: rgba(255, 255, 255, 0.08);
  color: var(--text-dark);
}

.like-btn.liked {
  color: #ef4444;
}

.like-btn.liked i {
  color: #ef4444;
}

/* Skeletons & States */
.state-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
  color: #8b949e;
}

.empty-state i,
.error-state i {
  color: var(--accent);
  margin-bottom: 14px;
}

.empty-state h3 {
  color: var(--text-dark);
  margin: 0 0 6px 0;
}

.btn-create-post,
.btn-retry {
  margin-top: 16px;
  padding: 8px 18px;
  background: var(--accent);
  color: #000;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 14px;
  text-decoration: none;
  cursor: pointer;
}

.skeleton-card {
  width: 100%;
  background: #0d1117;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 16px;
}

.skeleton-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 14px;
}

.skeleton-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.06);
}

.skeleton-lines {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.skeleton-line {
  height: 12px;
  background: rgba(255, 255, 255, 0.06);
  border-radius: 4px;
}

.skeleton-line.short {
  width: 40%;
}

.skeleton-line.tiny {
  width: 25%;
}

.skeleton-line.title {
  height: 18px;
  width: 80%;
  margin-bottom: 8px;
}

.skeleton-image {
  height: 120px;
  background: rgba(255, 255, 255, 0.04);
  border-radius: 6px;
  margin-top: 10px;
}
</style>
