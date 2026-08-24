<template>
  <div class="createPost-wrapper">
    <div class="createCard">
      <div class="card-title-bar">
        <h2>Create New Article</h2>
        <p class="subtitle">Share your knowledge with developers worldwide</p>
      </div>

      <div v-if="errorMessage" class="alert alert-error">
        <i class="fa fa-exclamation-circle"></i>
        <span>{{ errorMessage }}</span>
      </div>

      <form @submit.prevent="handleCreatePost" class="post-form">
        <!-- Title -->
        <div class="form-group">
          <label for="title">Article Title *</label>
          <input
            type="text"
            id="title"
            v-model="title"
            placeholder="e.g. Architecting Scalable Vue 3 & Laravel Applications"
            required
            class="title-input"
          />
        </div>

        <!-- Cover Image -->
        <div class="form-group">
          <label>Cover Image (Optional)</label>
          <div v-if="imagePreview" class="image-preview-box">
            <img :src="imagePreview" alt="Cover Preview" class="preview-img" />
            <button type="button" @click="removeImage" class="btn-remove-img">
              <i class="fa fa-times"></i> Remove
            </button>
          </div>
          <div v-else class="upload-dropzone">
            <label for="cover-image-input" class="dropzone-label">
              <i class="fa fa-cloud-upload-alt fa-2x"></i>
              <span>Click to upload cover image (PNG, JPG, WebP)</span>
            </label>
            <input
              id="cover-image-input"
              type="file"
              accept="image/*"
              @change="onImageSelect"
              style="display: none;"
            />
          </div>
        </div>

        <!-- Tags Input -->
        <div class="form-group">
          <label for="tags">Tags (press Enter, comma, or space to add)</label>
          <div class="tag-input-wrapper">
            <input
              type="text"
              id="tags"
              v-model="tagInput"
              placeholder="e.g. Laravel, Vue, Pinia"
              @keydown.enter.prevent="addTag"
              @keydown.space.prevent="addTag"
              @keyup="checkComma"
              class="tag-field"
            />
            <button type="button" @click="addTag" class="btn-add-tag">Add</button>
          </div>

          <div v-if="tags.length > 0" class="tag-list">
            <span v-for="(tag, index) in tags" :key="index" class="tag-badge">
              #{{ tag }}
              <button type="button" @click="removeTag(index)" class="remove-tag-btn">&times;</button>
            </span>
          </div>
        </div>

        <!-- Rich Text Quill Editor -->
        <div class="form-group editor-group">
          <label>Article Content *</label>
          <QuillEditor
            v-model:content="content"
            content-type="html"
            theme="snow"
            toolbar="full"
            placeholder="Write your article here... code blocks, headings, images, and formatting supported."
            class="quill-editor-instance"
          />
        </div>

        <!-- Submit Button -->
        <div class="form-actions">
          <router-link to="/" class="btn-cancel">Cancel</router-link>
          <button type="submit" class="btn-submit" :disabled="submitting || !title || !content">
            <span v-if="submitting"><i class="fa fa-spinner fa-spin"></i> Publishing...</span>
            <span v-else><i class="fa fa-paper-plane"></i> Publish Article</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { QuillEditor } from '@vueup/vue-quill';
import api from '../utils/api';

const router = useRouter();

const title = ref('');
const content = ref('');
const tagInput = ref('');
const tags = ref([]);
const imageFile = ref(null);
const imagePreview = ref('');

const submitting = ref(false);
const errorMessage = ref('');

const addTag = () => {
  const newTag = tagInput.value.trim().replace(/^[#,]+/, '').replace(/,+$/, '');
  if (newTag && !tags.value.includes(newTag)) {
    tags.value.push(newTag);
  }
  tagInput.value = '';
};

const checkComma = (e) => {
  if (e.key === ',') {
    e.preventDefault();
    addTag();
  }
};

const removeTag = (index) => {
  tags.value.splice(index, 1);
};

const onImageSelect = (e) => {
  const file = e.target.files[0];
  if (file) {
    imageFile.value = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const removeImage = () => {
  imageFile.value = null;
  imagePreview.value = '';
};

const handleCreatePost = async () => {
  if (!title.value.trim()) {
    errorMessage.value = 'Please provide an article title.';
    return;
  }
  if (!content.value.trim() || content.value === '<p><br></p>') {
    errorMessage.value = 'Please provide article content.';
    return;
  }

  submitting.value = true;
  errorMessage.value = '';

  try {
    const formData = new FormData();
    formData.append('title', title.value.trim());
    formData.append('content', content.value);
    if (imageFile.value) {
      formData.append('cover_image', imageFile.value);
    }
    tags.value.forEach((tag, idx) => {
      formData.append(`tags[${idx}]`, tag);
    });

    const response = await api.post('/posts/create', formData);

    if (response.slug) {
      router.push(`/posts/${response.slug}`);
    } else {
      router.push('/');
    }
  } catch (err) {
    errorMessage.value = err.message || 'Failed to create article. Please check your inputs.';
  } finally {
    submitting.value = false;
  }
};
</script>

<style scoped>
.createPost-wrapper {
  width: 100%;
  max-width: 600px;
  margin: 20px auto 80px;
}

.createCard {
  background: #0d1117;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  padding: 28px;
}

.card-title-bar {
  margin-bottom: 20px;
}

.card-title-bar h2 {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 0 4px 0;
}

.subtitle {
  color: #8b949e;
  font-size: 0.88rem;
  margin: 0;
}

.alert-error {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.3);
  border-radius: 8px;
  color: #f87171;
  margin-bottom: 20px;
  font-size: 0.88rem;
}

.post-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 0.88rem;
  font-weight: 600;
  color: #c9d1d9;
}

.title-input {
  width: 100%;
  padding: 12px 14px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  font-size: 1rem;
  color: var(--text-dark);
}

.title-input:focus {
  outline: none;
  border-color: var(--accent);
  background: rgba(255, 255, 255, 0.07);
}

/* Upload Dropzone */
.upload-dropzone {
  border: 1px dashed rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  padding: 24px;
  text-align: center;
  background: rgba(255, 255, 255, 0.02);
  cursor: pointer;
  transition: all 0.2s;
}

.upload-dropzone:hover {
  border-color: var(--accent);
  background: rgba(66, 184, 131, 0.04);
}

.dropzone-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  color: #8b949e;
  cursor: pointer;
  font-size: 0.88rem;
}

.dropzone-label i {
  color: var(--accent);
}

.image-preview-box {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  max-height: 240px;
}

.preview-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.btn-remove-img {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(0, 0, 0, 0.7);
  color: #fff;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 0.8rem;
  cursor: pointer;
}

.btn-remove-img:hover {
  background: rgba(239, 68, 68, 0.8);
}

/* Tags */
.tag-input-wrapper {
  display: flex;
  gap: 8px;
}

.tag-field {
  flex: 1;
  padding: 10px 12px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  color: var(--text-dark);
  font-size: 0.9rem;
}

.tag-field:focus {
  outline: none;
  border-color: var(--accent);
}

.btn-add-tag {
  background: rgba(255, 255, 255, 0.1);
  color: var(--text-dark);
  border: none;
  padding: 0 16px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
}

.btn-add-tag:hover {
  background: rgba(255, 255, 255, 0.18);
}

.tag-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 8px;
}

.tag-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(66, 184, 131, 0.15);
  color: var(--accent);
  padding: 4px 10px;
  border-radius: 16px;
  font-size: 0.82rem;
  font-weight: 500;
}

.remove-tag-btn {
  background: none;
  border: none;
  color: var(--accent);
  font-size: 1.1rem;
  cursor: pointer;
  line-height: 1;
  padding: 0;
}

/* Editor */
.editor-group {
  margin-top: 6px;
}

.quill-editor-instance {
  background: rgba(255, 255, 255, 0.02);
  border-radius: 0 0 8px 8px;
  color: var(--text-dark);
  min-height: 350px;
}

:deep(.ql-toolbar) {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.15) !important;
  border-radius: 8px 8px 0 0;
}

:deep(.ql-container) {
  border: 1px solid rgba(255, 255, 255, 0.15) !important;
  border-top: none !important;
  border-radius: 0 0 8px 8px;
  font-size: 1rem;
}

:deep(.ql-stroke) {
  stroke: #c9d1d9 !important;
}

:deep(.ql-fill) {
  fill: #c9d1d9 !important;
}

:deep(.ql-picker) {
  color: #c9d1d9 !important;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 12px;
  margin-top: 10px;
}

.btn-cancel {
  padding: 10px 18px;
  background: rgba(255, 255, 255, 0.08);
  color: var(--text-dark);
  border-radius: 8px;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
}

.btn-submit {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 22px;
  background: var(--accent);
  color: #000;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-submit:hover:not(:disabled) {
  opacity: 0.9;
  transform: translateY(-1px);
}

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
