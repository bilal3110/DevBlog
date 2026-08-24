# DevBlogs - Frontend (Vue 3 + Vite)

This is the frontend single-page application for **DevBlogs**, built with Vue 3, Vite, Vue Router 4, Pinia, and Vue Quill.

---

## 🚀 Overview

The frontend communicates with the Laravel REST API backend via a centralized API client (`src/utils/api.js`) and Pinia authentication store (`src/stores/auth.js`).

### Core Features:
- **Authentication:** Token-based authentication with Laravel Sanctum, persistent login state, login & registration views.
- **Feeds & Discovery:** Home feed with tab filtering (`For You`, `My Feed`, `Trending`), Explore page with live search across articles and developers, and trending tag filters.
- **Article Publishing:** Rich text article composer with Quill editor, syntax styling, tag chips, and cover image uploads.
- **Article Details & Discussions:** Dedicated article view with full HTML rendering, optimistic likes toggle, multi-platform sharing, and live comment CRUD operations.
- **Profiles & Settings:** User profile pages with follow/unfollow toggle and article history, plus a profile settings dashboard.
- **Notifications:** In-app notification center for likes, comments, and new followers with unread indicators and mark-as-read actions.
- **Sidebars:** Persistent left navigation sidebar and right sidebar with trending topics and suggested developers.

---

## 🛠️ Project Setup

```bash
# Install dependencies
npm install

# Start development server
npm run dev

# Build for production
npm run build

# Run unit tests
npm run test:unit

# Run linter & automatic fixes
npm run lint

# Format code with Prettier
npm run format
```

---

## 🌐 Environment Variables

Ensure `.env` exists in the `frontend` root:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api
VITE_STORAGE_BASE_URL=http://127.0.0.1:8000/storage
```
