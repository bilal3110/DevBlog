<template>
  <div class="post-detail-container">
    <div v-if="loading" class="loading-box">
      <i class="fa fa-spinner fa-spin fa-2x"></i>
      <p>Loading post...</p>
    </div>

    <div v-else-if="error" class="error-box">
      <i class="fa fa-exclamation-triangle fa-2x"></i>
      <h2>Post Not Found</h2>
      <p>{{ error }}</p>
      <router-link to="/" class="btn-back">← Back to Feed</router-link>
    </div>

    <article v-else-if="post" class="post-article">
      <!-- Post Header -->
      <header class="article-header">
        <div class="author-meta">
          <router-link :to="`/profile/${post.user?.id}`" class="author-link">
            <img
              :src="getImageUrl(post.user?.avatar, 'https://i.pravatar.cc/150?img=12')"
              alt="Author Avatar"
              class="author-avatar"
              @error="e => e.target.src = 'https://i.pravatar.cc/150?img=12'"
            />
            <div class="author-details">
              <span class="author-name">{{ post.user?.name || 'Unknown Author' }}</span>
              <span class="publish-date">{{ formatDate(post.created_at) }}</span>
            </div>
          </router-link>

          <!-- Author Actions (Edit/Delete) -->
          <div v-if="isAuthor" class="author-actions">
            <button @click="openEditModal" class="btn-icon edit-btn" title="Edit Post">
              <i class="fa fa-edit"></i> Edit
            </button>
            <button @click="handleDeletePost" class="btn-icon delete-btn" title="Delete Post">
              <i class="fa fa-trash"></i> Delete
            </button>
          </div>
        </div>

        <h1 class="post-title">{{ post.title }}</h1>

        <!-- Tags -->
        <div v-if="post.tags && post.tags.length > 0" class="tags-container">
          <router-link
            v-for="tag in post.tags"
            :key="tag.id"
            :to="`/explore?tag=${tag.slug || tag.name}`"
            class="tag-badge"
          >
            #{{ tag.name }}
          </router-link>
        </div>
      </header>

      <!-- Cover Image -->
      <div v-if="post.cover_image" class="cover-image-wrapper">
        <img :src="getImageUrl(post.cover_image)" :alt="post.title" class="cover-image" />
      </div>

      <!-- Post Content (Rich HTML from Quill) -->
      <div class="post-body ql-editor" v-html="post.content"></div>

      <!-- Post Actions Footer (Like / Comment / Share) -->
      <footer class="article-footer">
        <div class="stats-actions">
          <button
            @click="handleLikeToggle"
            class="interaction-btn like-btn"
            :class="{ liked: post.is_liked }"
            :disabled="likeLoading"
          >
            <i :class="post.is_liked ? 'fa fa-heart' : 'far fa-heart'"></i>
            <span>{{ post.like_count ?? (post.like ? post.like.length : 0) }}</span>
          </button>

          <a href="#comments-section" class="interaction-btn comment-btn">
            <i class="far fa-comment"></i>
            <span>{{ comments.length }}</span>
          </a>

          <div class="share-dropdown-container">
            <button @click="toggleShareMenu" class="interaction-btn share-btn">
              <i class="fa fa-share-alt"></i>
              <span>Share</span>
            </button>

            <!-- Share Menu Popup -->
            <div v-if="showShareMenu" class="share-menu">
              <button @click="shareTo('x/twitter')" class="share-opt">
                <i class="fab fa-x-twitter"></i> X (Twitter)
              </button>
              <button @click="shareTo('linkedin')" class="share-opt">
                <i class="fab fa-linkedin"></i> LinkedIn
              </button>
              <button @click="shareTo('facebook')" class="share-opt">
                <i class="fab fa-facebook"></i> Facebook
              </button>
              <button @click="shareTo('whatsapp')" class="share-opt">
                <i class="fab fa-whatsapp"></i> WhatsApp
              </button>
              <button @click="copyPostLink" class="share-opt">
                <i class="fa fa-link"></i> {{ copied ? 'Copied!' : 'Copy Link' }}
              </button>
            </div>
          </div>
        </div>
      </footer>

      <!-- Comments Section -->
      <section id="comments-section" class="comments-section">
        <h3>Comments ({{ comments.length }})</h3>

        <!-- Add Comment Form -->
        <div v-if="authStore.isAuthenticated" class="add-comment-box">
          <img
            :src="authStore.userAvatar"
            alt="My Avatar"
            class="my-comment-avatar"
          />
          <form @submit.prevent="handleSubmitComment" class="comment-form">
            <textarea
              v-model="newCommentContent"
              placeholder="Write a constructive response..."
              rows="3"
              required
              class="comment-textarea"
            ></textarea>
            <div class="form-bottom">
              <button type="submit" class="btn-comment" :disabled="submittingComment || !newCommentContent.trim()">
                <span v-if="submittingComment"><i class="fa fa-spinner fa-spin"></i> Posting...</span>
                <span v-else>Post Comment</span>
              </button>
            </div>
          </form>
        </div>
        <div v-else class="login-to-comment">
          <p><router-link :to="`/login?redirect=/posts/${post.slug}`">Log in</router-link> to join the conversation.</p>
        </div>

        <!-- Comments List -->
        <div class="comments-list">
          <div v-if="commentsLoading" class="loading-comments">
            <i class="fa fa-spinner fa-spin"></i> Loading comments...
          </div>

          <div v-else-if="comments.length === 0" class="no-comments">
            <i class="far fa-comments fa-2x"></i>
            <p>No comments yet. Be the first to share your thoughts!</p>
          </div>

          <div v-else v-for="comment in comments" :key="comment.id" class="comment-card">
            <router-link :to="`/profile/${comment.user?.id}`">
              <img
                :src="getImageUrl(comment.user?.avatar, 'https://i.pravatar.cc/150?img=12')"
                alt="User Avatar"
                class="comment-user-avatar"
                @error="e => e.target.src = 'https://i.pravatar.cc/150?img=12'"
              />
            </router-link>

            <div class="comment-body">
              <div class="comment-header">
                <div class="comment-user-meta">
                  <router-link :to="`/profile/${comment.user?.id}`" class="comment-author-name">
                    {{ comment.user?.name || 'User' }}
                  </router-link>
                  <span class="comment-time">{{ formatDate(comment.created_at) }}</span>
                </div>

                <!-- Comment Options (Edit/Delete) -->
                <div v-if="canManageComment(comment)" class="comment-actions">
                  <button
                    v-if="comment.user?.id === authStore.userId"
                    @click="startEditComment(comment)"
                    class="btn-action-text"
                  >
                    Edit
                  </button>
                  <button
                    @click="handleDeleteComment(comment.id)"
                    class="btn-action-text text-danger"
                  >
                    Delete
                  </button>
                </div>
              </div>

              <!-- Editing Mode -->
              <div v-if="editingCommentId === comment.id" class="edit-comment-form">
                <textarea
                  v-model="editCommentContent"
                  rows="2"
                  class="comment-textarea"
                ></textarea>
                <div class="edit-buttons">
                  <button @click="saveEditComment(comment.id)" class="btn-save" :disabled="savingComment">
                    Save
                  </button>
                  <button @click="cancelEditComment" class="btn-cancel">
                    Cancel
                  </button>
                </div>
              </div>

              <!-- Comment Text -->
              <p v-else class="comment-text">{{ comment.content }}</p>
            </div>
          </div>
        </div>
      </section>
    </article>

    <!-- Edit Post Modal -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <h2>Edit Post</h2>
          <button @click="showEditModal = false" class="close-btn">&times;</button>
        </div>
        <form @submit.prevent="handleUpdatePost" class="modal-form">
          <div class="form-group">
            <label>Title</label>
            <input v-model="editPostTitle" type="text" required class="modal-input" />
          </div>
          <div class="form-group">
            <label>Tags (comma separated)</label>
            <input v-model="editPostTags" type="text" placeholder="Laravel, Vue, JavaScript" class="modal-input" />
          </div>
          <div class="form-group">
            <label>Replace Cover Image (Optional)</label>
            <input type="file" accept="image/*" @change="e => editPostCover = e.target.files[0]" />
          </div>
          <div class="modal-actions">
            <button type="button" @click="showEditModal = false" class="btn-cancel">Cancel</button>
            <button type="submit" class="btn-save" :disabled="updatingPost">
              <span v-if="updatingPost"><i class="fa fa-spinner fa-spin"></i> Saving...</span>
              <span v-else>Update Post</span>
            </button>
          </div>
        </form>
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

const post = ref(null);
const loading = ref(true);
const error = ref('');
const likeLoading = ref(false);

const comments = ref([]);
const commentsLoading = ref(false);
const newCommentContent = ref('');
const submittingComment = ref(false);
const editingCommentId = ref(null);
const editCommentContent = ref('');
const savingComment = ref(false);

const showShareMenu = ref(false);
const copied = ref(false);

// Edit Post Modal
const showEditModal = ref(false);
const editPostTitle = ref('');
const editPostTags = ref('');
const editPostCover = ref(null);
const updatingPost = ref(false);

const isAuthor = computed(() => {
  if (!authStore.isAuthenticated || !post.value) return false;
  return post.value.user_id === authStore.userId;
});

const canManageComment = (comment) => {
  if (!authStore.isAuthenticated) return false;
  return comment.user?.id === authStore.userId || isAuthor.value;
};

const fetchPost = async () => {
  loading.value = true;
  error.value = '';
  try {
    const data = await api.get(`/posts/show/${route.params.slug}`);
    post.value = data;
    if (data.id) {
      fetchComments(data.id);
    }
  } catch (err) {
    error.value = err.message || 'Could not find the requested post.';
  } finally {
    loading.value = false;
  }
};

const fetchComments = async (blogId) => {
  commentsLoading.value = true;
  try {
    const data = await api.get(`/posts/comments/show/${blogId}`);
    comments.value = data.comments || [];
  } catch (err) {
    console.error('Failed to load comments:', err);
  } finally {
    commentsLoading.value = false;
  }
};

const handleLikeToggle = async () => {
  if (!authStore.isAuthenticated) {
    return router.push(`/login?redirect=/posts/${post.value.slug}`);
  }
  likeLoading.value = true;
  try {
    const res = await api.post(`/posts/likes/create/${post.value.id}`);
    post.value.is_liked = res.liked;
    if (res.total_likes !== undefined) {
      post.value.like_count = res.total_likes;
    } else {
      post.value.like_count = (post.value.like_count || 0) + (res.liked ? 1 : -1);
    }
  } catch (err) {
    console.error('Error toggling like:', err);
  } finally {
    likeLoading.value = false;
  }
};

const handleSubmitComment = async () => {
  if (!newCommentContent.value.trim()) return;
  submittingComment.value = true;
  try {
    const res = await api.post(`/posts/comments/create/${post.value.id}`, {
      content: newCommentContent.value.trim(),
    });
    if (res.comment) {
      comments.value.unshift(res.comment);
      newCommentContent.value = '';
    }
  } catch (err) {
    alert(err.message || 'Failed to add comment');
  } finally {
    submittingComment.value = false;
  }
};

const startEditComment = (comment) => {
  editingCommentId.value = comment.id;
  editCommentContent.value = comment.content;
};

const cancelEditComment = () => {
  editingCommentId.value = null;
  editCommentContent.value = '';
};

const saveEditComment = async (commentId) => {
  if (!editCommentContent.value.trim()) return;
  savingComment.value = true;
  try {
    const res = await api.patch(`/posts/comments/edit/${commentId}`, {
      content: editCommentContent.value.trim(),
    });
    const idx = comments.value.findIndex((c) => c.id === commentId);
    if (idx !== -1 && res.comment) {
      comments.value[idx].content = res.comment.content;
    }
    cancelEditComment();
  } catch (err) {
    alert(err.message || 'Failed to update comment');
  } finally {
    savingComment.value = false;
  }
};

const handleDeleteComment = async (commentId) => {
  if (!confirm('Are you sure you want to delete this comment?')) return;
  try {
    await api.delete(`/posts/comments/delete/${commentId}`);
    comments.value = comments.value.filter((c) => c.id !== commentId);
  } catch (err) {
    alert(err.message || 'Failed to delete comment');
  }
};

const handleDeletePost = async () => {
  if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) return;
  try {
    await api.delete(`/posts/delete/${post.value.id}`);
    router.push('/');
  } catch (err) {
    alert(err.message || 'Failed to delete post');
  }
};

const openEditModal = () => {
  editPostTitle.value = post.value.title;
  editPostTags.value = (post.value.tags || []).map(t => t.name).join(', ');
  showEditModal.value = true;
};

const handleUpdatePost = async () => {
  updatingPost.value = true;
  try {
    const formData = new FormData();
    formData.append('title', editPostTitle.value);
    formData.append('content', post.value.content);
    if (editPostCover.value) {
      formData.append('cover_image', editPostCover.value);
    }
    const tagsArray = editPostTags.value
      .split(',')
      .map(t => t.trim())
      .filter(t => t.length > 0);
    tagsArray.forEach((tag, idx) => {
      formData.append(`tags[${idx}]`, tag);
    });

    const res = await api.post(`/posts/update/${post.value.id}`, formData);
    showEditModal.value = false;
    if (res.slug && res.slug !== route.params.slug) {
      router.push(`/posts/${res.slug}`);
    } else {
      fetchPost();
    }
  } catch (err) {
    alert(err.message || 'Failed to update post');
  } finally {
    updatingPost.value = false;
  }
};

const toggleShareMenu = () => {
  showShareMenu.value = !showShareMenu.value;
};

const shareTo = async (platform) => {
  showShareMenu.value = false;
  try {
    if (authStore.isAuthenticated) {
      await api.post(`/post/share/${post.value.id}`, { platform });
    }
  } catch (err) {
    console.warn('Could not record share:', err);
  }

  const postUrl = window.location.href;
  const postTitle = encodeURIComponent(post.value.title);
  let shareUrl = '';

  switch (platform) {
    case 'x/twitter':
      shareUrl = `https://twitter.com/intent/tweet?text=${postTitle}&url=${encodeURIComponent(postUrl)}`;
      break;
    case 'linkedin':
      shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(postUrl)}`;
      break;
    case 'facebook':
      shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(postUrl)}`;
      break;
    case 'whatsapp':
      shareUrl = `https://api.whatsapp.com/send?text=${postTitle}%20${encodeURIComponent(postUrl)}`;
      break;
  }

  if (shareUrl) {
    window.open(shareUrl, '_blank', 'width=600,height=400');
  }
};

const copyPostLink = () => {
  navigator.clipboard.writeText(window.location.href).then(() => {
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
      showShareMenu.value = false;
    }, 1500);
  });
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

onMounted(fetchPost);

watch(
  () => route.params.slug,
  (newSlug) => {
    if (newSlug) fetchPost();
  }
);
</script>

<style scoped>
.post-detail-container {
  width: 100%;
  max-width: 680px;
  padding: 30px 15px 80px;
  margin: 0 auto;
}

.loading-box,
.error-box {
  text-align: center;
  padding: 60px 20px;
  color: #8b949e;
}

.error-box h2 {
  color: var(--text-dark);
  margin: 10px 0;
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

.article-header {
  margin-bottom: 24px;
}

.author-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}

.author-link {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: inherit;
}

.author-avatar {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.author-details {
  display: flex;
  flex-direction: column;
}

.author-name {
  font-weight: 600;
  font-size: 0.98rem;
  color: var(--text-dark);
}

.author-name:hover {
  color: var(--accent);
}

.publish-date {
  font-size: 0.8rem;
  color: #8b949e;
}

.author-actions {
  display: flex;
  gap: 8px;
}

.btn-icon {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: var(--text-dark);
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.82rem;
  transition: all 0.2s ease;
}

.edit-btn:hover {
  background: rgba(66, 184, 131, 0.2);
  color: var(--accent);
}

.delete-btn:hover {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
}

.post-title {
  font-size: 2rem;
  font-weight: 800;
  line-height: 1.3;
  margin: 10px 0 16px;
  color: var(--text-dark);
}

.tags-container {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 20px;
}

.tag-badge {
  background: rgba(255, 255, 255, 0.08);
  color: var(--accent);
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.82rem;
  text-decoration: none;
  font-weight: 500;
  transition: background 0.2s;
}

.tag-badge:hover {
  background: rgba(66, 184, 131, 0.2);
}

.cover-image-wrapper {
  margin: 20px 0;
  border-radius: 12px;
  overflow: hidden;
  max-height: 380px;
}

.cover-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.post-body {
  font-size: 1.05rem;
  line-height: 1.75;
  color: #e6edf3;
  margin: 24px 0 40px;
  word-break: break-word;
}

.article-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.12);
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
  padding: 16px 0;
  margin: 30px 0 40px;
}

.stats-actions {
  display: flex;
  gap: 20px;
  align-items: center;
}

.interaction-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: none;
  border: none;
  color: #8b949e;
  font-size: 0.95rem;
  cursor: pointer;
  text-decoration: none;
  padding: 6px 12px;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.interaction-btn:hover {
  background: rgba(255, 255, 255, 0.08);
  color: var(--text-dark);
}

.like-btn.liked {
  color: #ef4444;
}

.like-btn.liked i {
  color: #ef4444;
}

.share-dropdown-container {
  position: relative;
}

.share-menu {
  position: absolute;
  bottom: 100%;
  left: 0;
  background: #161b22;
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  padding: 8px 0;
  min-width: 160px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
  z-index: 100;
  display: flex;
  flex-direction: column;
}

.share-opt {
  display: flex;
  align-items: center;
  gap: 10px;
  background: none;
  border: none;
  color: #c9d1d9;
  padding: 8px 16px;
  font-size: 0.88rem;
  text-align: left;
  cursor: pointer;
  transition: background 0.2s;
}

.share-opt:hover {
  background: rgba(255, 255, 255, 0.08);
  color: var(--accent);
}

/* Comments */
.comments-section h3 {
  font-size: 1.25rem;
  margin-bottom: 20px;
  font-weight: 700;
}

.add-comment-box {
  display: flex;
  gap: 14px;
  margin-bottom: 30px;
}

.my-comment-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.comment-form {
  flex: 1;
}

.comment-textarea {
  width: 100%;
  padding: 12px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  color: var(--text-dark);
  font-size: 0.92rem;
  resize: vertical;
  font-family: inherit;
}

.comment-textarea:focus {
  outline: none;
  border-color: var(--accent);
  background: rgba(255, 255, 255, 0.07);
}

.form-bottom {
  display: flex;
  justify-content: flex-end;
  margin-top: 10px;
}

.btn-comment,
.btn-save {
  background: var(--accent);
  color: #000;
  border: none;
  padding: 8px 18px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.88rem;
  cursor: pointer;
}

.btn-comment:disabled,
.btn-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.login-to-comment {
  padding: 16px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px dashed rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  text-align: center;
  margin-bottom: 30px;
  color: #8b949e;
}

.login-to-comment a {
  color: var(--accent);
  font-weight: 600;
  text-decoration: none;
}

.comment-card {
  display: flex;
  gap: 12px;
  padding: 16px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.comment-user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
}

.comment-body {
  flex: 1;
}

.comment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
}

.comment-user-meta {
  display: flex;
  align-items: center;
  gap: 8px;
}

.comment-author-name {
  font-weight: 600;
  font-size: 0.9rem;
  color: var(--text-dark);
  text-decoration: none;
}

.comment-author-name:hover {
  color: var(--accent);
}

.comment-time {
  font-size: 0.78rem;
  color: #8b949e;
}

.comment-actions {
  display: flex;
  gap: 8px;
}

.btn-action-text {
  background: none;
  border: none;
  color: #8b949e;
  font-size: 0.78rem;
  cursor: pointer;
  padding: 2px 4px;
}

.btn-action-text:hover {
  color: var(--text-dark);
}

.text-danger:hover {
  color: #ef4444;
}

.comment-text {
  font-size: 0.92rem;
  line-height: 1.5;
  color: #c9d1d9;
  margin: 0;
  white-space: pre-wrap;
}

.edit-buttons {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}

.btn-cancel {
  background: rgba(255, 255, 255, 0.1);
  color: var(--text-dark);
  border: none;
  padding: 6px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 20px;
}

.modal-card {
  background: #0d1117;
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  padding: 24px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.modal-header h2 {
  font-size: 1.25rem;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #8b949e;
  cursor: pointer;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.modal-input {
  width: 100%;
  padding: 10px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 6px;
  color: var(--text-dark);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
}
</style>
