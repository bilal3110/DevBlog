<template>
  <div class="blogs">
    <div class="blog-card" v-for="card in cards" :key="card.id">
      <div class="card-header">
        <img
          :src="card.author.avatar"
          alt="User Avatar"
          class="Follow-avatar"
          @error="handleImageError"
        />
        <div class="user-info">
          <span class="blogUserName">{{ card.author.name }}</span>
          <span class="post-date">{{ formatDate(card.createdAt) }}</span>
        </div>
      </div>

      <div class="blog-content">
        <div class="blog-heading">
          <h2 class="blogTitle">{{ card.title }}</h2>
        </div>

        <div class="blog-excerpt">
          <p>{{ card.excerpt }}</p>
        </div>

        <div v-if="card.image" class="blog-image">
          <img :src="card.image" :alt="card.title" />
        </div>

        <div class="blog-actions">
          <button class="read-btn" @click="readMore(card.id)">
            Read More
          </button>

          <div class="interaction-buttons">
            <button
              @click="toggleLike(card)"
              :class="['action-btn', 'like-btn', { 'liked': card.isLiked }]"
            >
              <svg class="icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
              </svg>
              <span>{{ card.likes }}</span>
            </button>

            <!-- Comment Button -->
            <button
              @click="openComments(card)"
              class="action-btn comment-btn"
            >
              <svg class="icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h11c.55 0 1-.45 1-1z"/>
              </svg>
              <span>{{ card.comments }}</span>
            </button>

            <!-- Share Button -->
            <button
              @click="sharePost(card)"
              class="action-btn share-btn"
            >
              <svg class="icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.50-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/>
              </svg>
              <span>Share</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BlogCard',
  data() {
    return {
      cards: [
        {
          id: 1,
          title: "Getting Started with Vue.js 3",
          excerpt: "Learn the fundamentals of Vue.js 3 and build amazing web applications with the latest features and composition API.",
          image: "https://picsum.photos/400/200?random=1",
          author: {
            id: 1,
            name: "John Doe",
            avatar: "https://i.pravatar.cc/40?img=1"
          },
          likes: 24,
          comments: 8,
          isLiked: false,
          createdAt: new Date('2024-01-15')
        },
        {
          id: 2,
          title: "Advanced JavaScript Patterns",
          excerpt: "Explore advanced JavaScript patterns and best practices that will make your code more maintainable and efficient.",
          image: "https://picsum.photos/400/200?random=2",
          author: {
            id: 2,
            name: "Jane Smith",
            avatar: "https://i.pravatar.cc/40?img=2"
          },
          likes: 31,
          comments: 12,
          isLiked: true,
          createdAt: new Date('2024-01-12')
        },
        {
          id: 3,
          title: "CSS Grid vs Flexbox",
          excerpt: "Understanding when to use CSS Grid and when to use Flexbox for creating responsive layouts in modern web development.",
          image: "https://picsum.photos/400/200?random=3",
          author: {
            id: 3,
            name: "Mike Johnson",
            avatar: "https://i.pravatar.cc/40?img=3"
          },
          likes: 18,
          comments: 5,
          isLiked: false,
          createdAt: new Date('2024-01-10')
        }
      ]
    }
  },
  methods: {
    toggleLike(card) {
      card.isLiked = !card.isLiked;
      card.likes += card.isLiked ? 1 : -1;
    },

    openComments(card) {
      console.log('Opening comments for:', card.title);
      // Add your comment functionality here
    },

    readMore(cardId) {
      console.log('Read more for card:', cardId);
      // Add navigation logic here without router-link
    },

    sharePost(card) {
      if (navigator.share) {
        navigator.share({
          title: card.title,
          text: card.excerpt,
          url: window.location.origin + `/blog/${card.id}`
        });
      } else {
        const url = window.location.origin + `/blog/${card.id}`;
        navigator.clipboard.writeText(url).then(() => {
          alert('Link copied to clipboard!');
        }).catch(() => {
          // Fallback if clipboard API fails
          console.log('Share URL:', url);
        });
      }
    },

    handleImageError(event) {
      event.target.src = 'https://i.pravatar.cc/40';
    },

    formatDate(date) {
      const now = new Date();
      const diffTime = Math.abs(now - date);
      const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

      if (diffDays === 0) return 'Today';
      if (diffDays === 1) return 'Yesterday';
      if (diffDays < 7) return `${diffDays} days ago`;
      if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
      return date.toLocaleDateString();
    }
  }
}
</script>

<style scoped>
.blogs {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding: 20px;
  max-width: 1200px;
  margin: 50px auto;
}

.blog-card {
  width: 500px;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.blog-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.card-header {
  display: flex;
  align-items: center;
  padding: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.Follow-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: 12px;
  object-fit: cover;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.blogUserName {
  font-weight: 600;
  font-size: 14px;
}

.post-date {
  font-size: 12px;
  color: #6b7280;
  margin-top: 2px;
}

.blog-content {
  padding: 0 16px 16px;
}

.blog-heading {
  margin: 16px 0;
}

.blogTitle {
  font-size: 20px;
  font-weight: 700;
  line-height: 1.4;
  margin: 0;
}

.blog-excerpt {
  margin-bottom: 16px;
}

.blog-excerpt p {
  line-height: 1.6;
  margin: 0;
  font-size: 14px;
}

.blog-image {
  margin: 16px 0;
  border-radius: 8px;
  overflow: hidden;
}

.blog-image img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  display: block;
}

.blog-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.read-btn {
  background: var(--accent);
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.read-btn:hover {
  background: var(--accent)
}

.interaction-buttons {
  display: flex;
  gap: 16px;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 6px 8px;
  border-radius: 4px;
  font-size: 14px;
  transition: all 0.2s ease;
}

.action-btn:hover {
  color: #374151;
}

.like-btn.liked {
  color: #ef4444;
}

.like-btn.liked .icon {
  fill: #ef4444;
}

.icon {
  width: 18px;
  height: 18px;
  fill: currentColor;
}

/* Responsive Design */
@media (max-width: 768px) {
  .blogs {
    grid-template-columns: 1fr;
    padding: 16px;
    gap: 16px;
  }

  .blog-actions {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }

  .interaction-buttons {
    justify-content: center;
  }
}
</style>
