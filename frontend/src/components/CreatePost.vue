<template>
  <div class="createPost">
    <div class="createCard">
      <form @submit.prevent="createPost">
        <div class="form-group">
          <label for="title">Title:</label>
          <input type="text" id="title" v-model="title" placeholder="Enter title" required />
        </div>

        <div class="form-group">
          <label>Content:</label>
          <QuillEditor v-model:content="content" content-type="html" theme="snow" toolbar="full"
            style="min-height: 70vh;" />
        </div>
        <div class="form-group">
          <label for="tags">Tags:</label>
          <input type="text" id="tags" v-model="tagInput" placeholder="Enter a tag and press Space or comma"
            @keyup.space.prevent="addTag" @keyup="checkComma" />

          <div class="tag-list">
            <span v-for="(tag, index) in tags" :key="index" class="tag">
              {{ tag }} <button @click="removeTag(index)">×</button>
            </span>
          </div>
        </div>



        <div class="form-group">
          <label for="image" class="custom-file-label">📷 Upload Image</label>
          <input type="file" id="image" @change="uploadImage" required />
          <p v-if="imageName" class="file-name">Selected: {{ imageName }}</p>
        </div>

        <button type="submit">Create Post</button>
      </form>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue'
import { QuillEditor } from '@vueup/vue-quill'

const title = ref('')
const content = ref('')
const tagInput = ref('')
const tags = ref([])
const image = ref(null)
const imageName = ref('')

// Add tag on Space or comma
const addTag = () => {
  const newTag = tagInput.value.trim()
  if (newTag && !tags.value.includes(newTag)) {
    tags.value.push(newTag)
  }
  tagInput.value = ''
}

const checkComma = (e) => {
  if (e.key === ',') {
    e.preventDefault()
    addTag()
  }
}

const removeTag = (index) => {
  tags.value.splice(index, 1)
}

const uploadImage = (e) => {
  const file = e.target.files[0]
  image.value = file
  imageName.value = file?.name || ''
}

const createPost = () => {
  console.log('Title:', title.value)
  console.log('Content:', content.value)
  console.log('Tags:', tags.value)
  console.log('Image:', image.value)
  // Reset form
  title.value = ''
  content.value = ''
  tags.value = []
  tagInput.value = ''
  image.value = null
  imageName.value = ''

}
</script>



<style lang="css" scoped>
.createPost {
  max-width: 1200px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.createCard {
  padding: 20px;
  width: 500px;
}

.createCard form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.createCard form button {
  background: var(--accent);
  padding: 8px 14px;
  cursor: pointer;
  color: var(--text-dark);
  outline: none;
  border: none;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-group input {
  width: 100%;
  padding: 10px;
  border: 1px solid rgba(255, 255, 255, 0.171);
  border-radius: 6px;
  font-size: 14px;
  background: none;
  transition: border-color 0.3s ease;
  color: var(--text-dark);
}

.form-group input:focus {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.editor {
  width: 100%;
  height: 1000px;
}

#image {
  display: none;
}

.custom-file-label {
  height: 100px;
  display: inline-block;
  padding: 10px 16px;
  color: white;
  font-weight: bold;
  text-align: center;
  padding-top: 15%;
  border-radius: 6px;
  font-size: 22px;
  cursor: pointer;
  transition: background 0.3s;
  border: 1px solid rgba(255, 255, 255, 0.171);
  opacity: 0.7;
}

.custom-file-label:focus {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.file-name {
  margin-top: 6px;
  font-size: 14px;
  color: #555;
}
</style>
