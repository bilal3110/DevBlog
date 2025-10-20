<template>
  <div class="explore">
    <div class="searchbar">
      <form @submit.prevent="handlesearch()">
        <input type="text" placeholder="Search for a user" v-model="searchQuery" />
      </form>
    </div>

    <div class="tag-toggle-buttons">
      <a @click="activeTab = 'top'" :class="activeClass('top')">Top Tags</a>
      <a @click="activeTab = 'all'" :class="activeClass('all')">All Tags</a>
    </div>

    <div class="tags">
      <div v-if="activeTab === 'top'" class="top-tags">
        <div class="tag" v-for="tag in props.topTags" :key="tag.id">
          <a href="#" class="tag-name" @click.prevent="handleTagClick(tag)">
            {{ tag.name }}
          </a>
        </div>
      </div>
      <div v-else-if="activeTab === 'all'" class="all-tags">
        <div class="tag" v-for="tag in props.allTags" :key="tag.id">
          <a href="#" class="tag-name" @click.prevent="handleTagClick(tag)">
            {{ tag.name }}
          </a>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref } from 'vue';

const searchQuery = ref('');
const activeTab = ref('top');

const handlesearch = () => {
  console.log('Search:', searchQuery.value);
};

const handleTagClick = (tag) => {
  console.log('Tag clicked:', tag);
};

// Active Class on Button
const activeClass = (tab) => {
  return tab === activeTab.value ? 'active' : '';
}

const props = defineProps({
  topTags: {
    type: Array,
    default: () => [
      { id: 1, name: 'Laravel' },
      { id: 2, name: 'AI will Replace Programmers??' },
      { id: 3, name: 'SaaS Automation' },
    ]
  },
  allTags: {
    type: Array,
    default: () => [
      { id: 1, name: 'Laravel' },
      { id: 2, name: 'AI will Replace Programmers??' },
      { id: 3, name: 'SaaS Automation' },
      { id: 4, name: 'builtinPublic'},
      { id: 5, name: 'builtinPrivate'},
      { id: 6, name: 'builtinProtected'},
    ]
  }
});
</script>
<style scoped>
.explore {
  max-width: 1200px;
  display: flex;
  flex-direction: column;
}

.searchbar {
  width: 550px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.searchbar {
  padding: 10px 10px 10px 0px;
}

.searchbar input {
  width: 100%;
  padding: 10px;
  border: 1px solid rgba(255, 255, 255, 0.171);
  border-radius: 6px;
  font-size: 14px;
  background: none;
  transition: border-color 0.3s ease;
  color: var(--text-dark);
}

.searchbar input:focus {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.tag-toggle-buttons {
  display: flex;
  gap: 10px;
  padding: 10px;
  justify-content: space-around;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.171);
}

.tag-toggle-buttons a {
  text-decoration: none;
  padding: 5px 20px;
  font-size: 16px;
}

.tag-toggle-buttons a.active {
  color: var(--accent);
}

.tags{
  padding: 10px;
  display: flex;
  gap: 10px;
  flex-direction: column;
  border: 1px solid rgba(255, 255, 255, 0.171);
  margin-top: 10px;
  border-radius: 10px;
}

.top-tags,.all-tags{
  padding: 10px;
  display: flex;
  gap: 10px;
  flex-direction: column;
}

.tag-name {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-dark);
  text-decoration: none;
  cursor: pointer;
  display: inline-block;
  padding: 5px 0;
  transition: color 0.3s ease;
}

.tag-name:hover {
  color: var(--accent);
}
</style>
