# DevBlogs

DevBlogs is a full-stack, developer-focused blogging and knowledge-sharing platform built with a **Laravel 10 REST API** backend and a **Vue 3** frontend. It is designed as a collaborative space where developers can publish rich technical articles, discover trending topics, interact through real-time likes and comments, follow other writers, and receive in-app notifications.

---

## 🚀 Key Features

* **Full Authentication & Session Persistence:** Secure token-based authentication using Laravel Sanctum with Pinia state management and persistent browser sessions.
* **Developer Profiles & Settings:** Public profiles (`/profile/:id`) with follower/following metrics, social links (GitHub & Portfolio), published articles history, and profile customization (`/settings`) with avatar uploads.
* **Rich Article Publishing:** Article composer (`/createpost`) with full Quill rich text editing, code block formatting, multi-tag chips, and cover image file uploads.
* **Full Post Detail View:** Dedicated article page (`/posts/:slug`) rendering formatted HTML content, author metadata, tag badges, like counts, and multi-platform sharing.
* **Interactive Comments Thread:** Full CRUD comment system on articles (create, edit, delete) with ownership authorization and real-time counter updates.
* **Optimistic Likes & Follows:** Instant UI updates when toggling likes on posts or following/unfollowing developers with background API synchronization.
* **Dynamic Feed Filtering:** Home feed tabs for **For You** (latest articles), **My Feed** (articles from followed developers only), and **Trending** (most engaged posts).
* **Live Discovery & Search:** Explore view (`/explore`) featuring real-time search for both articles and developers, trending tags (last 7 days), all tags, and tag-filtered post feeds.
* **Social Sharing:** One-click sharing to X (Twitter), LinkedIn, Facebook, WhatsApp, native Web Share API, and clipboard copy with backend share tracking.
* **Notification Center:** Real-time notifications for likes, comments, and new followers with unread badges, mark single as read, and "mark all as read".
* **3-Column Responsive Layout:** Fixed left sidebar navigation, centralized main feed, and right sidebar with trending topics and suggested developers.

---

## 🛠️ Tech Stack

### Backend
* **Framework:** Laravel 10 (PHP 8.1+)
* **Authentication:** Laravel Sanctum (Bearer Tokens)
* **Database & ORM:** MySQL with Eloquent ORM & Migrations
* **File Storage:** Laravel Storage with public symlink (`/storage`)
* **API Documentation:** Scribe
* **Testing & Quality:** PHPUnit & Laravel Pint

### Frontend
* **Framework:** Vue 3 (Composition API)
* **Build Tool:** Vite 7
* **Routing:** Vue Router 4 (with `requiresAuth` and `guestOnly` navigation guards)
* **State Management:** Pinia
* **Rich Text Editor:** Vue Quill (`@vueup/vue-quill`)
* **Styling:** Modern dark theme with CSS custom properties & Font Awesome 6
* **Testing & Quality:** Vitest, Playwright, ESLint, Prettier

---

## 📁 Project Structure

```text
DevBlogs/
|-- backend/                     # Laravel 10 REST API
|   |-- app/
|   |   |-- Http/Controllers/    # UserController, PostController, CommentController, etc.
|   |   |-- Models/              # User, Blog, Tags, Comments, Likes, Follows, Notifications, Share
|   |   `-- Helpers/             # Notify helper
|   |-- config/                  # cors.php, sanctum.php, etc.
|   |-- database/migrations/     # Database schemas
|   |-- routes/                  # api.php, web.php
|   `-- tests/                   # Feature & Unit tests
|-- frontend/                    # Vue 3 + Vite SPA
|   |-- src/
|   |   |-- components/          # BlogCard, CreatePost, ExploreView, LeftSide, RightSide, NavBar, NotificationView
|   |   |-- views/               # HomeView, ExploreView, PostDetailView, CreatePostView, NotificationsView, ProfileView, SettingsView, LoginView, RegisterView
|   |   |-- stores/              # auth.js (Pinia store)
|   |   |-- utils/               # api.js (Centralized API client & getImageUrl helper)
|   |   |-- router/              # index.js (Route definitions & auth guards)
|   |   `-- assets/              # base.css, main.css
|   `-- e2e/                     # Playwright tests
|-- PROJECT_ANALYSIS_AND_INTEGRATION_GUIDE.md # Detailed specification & API reference
`-- README.md
```

---

## 🌐 API Reference

Base URL: `http://127.0.0.1:8000/api`

### Authentication & Users
| Method | Endpoint | Auth | Description |
| :--- | :--- | :--- | :--- |
| `POST` | `/users/create` | Public | Register new user (with optional avatar) |
| `POST` | `/login` | Public | Login and receive Sanctum Bearer token |
| `GET` | `/user` | Sanctum | Get current authenticated user details |
| `POST` | `/logout` | Sanctum | Invalidate user tokens |
| `GET` | `/users` | Public | List / search users (`?search=`, `?limit=`, `?exclude_me=1`) |
| `GET` | `/users/{id}` | Public | Get single user profile with follower stats |
| `MATCH` (`PATCH`/`POST`) | `/users/update/{id}` | Sanctum | Update user profile, password, or avatar |
| `DELETE` | `/users/delete/{id}` | Sanctum | Delete user account (owner only) |

### Blog Posts
| Method | Endpoint | Auth | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/posts` | Public | Get posts (`?tab=feed\|trending`, `?tag=`, `?search=`, `?user_id=`) |
| `GET` | `/posts/show/{slug}` | Public | Get single post details by slug with author, tags, and comments |
| `POST` | `/posts/create` | Sanctum | Create post with title, Quill HTML, cover image, and tags |
| `MATCH` (`PATCH`/`POST`) | `/posts/update/{id}` | Sanctum | Update post title, content, cover image, and tags (author only) |
| `DELETE` | `/posts/delete/{id}` | Sanctum | Delete post and associated assets (author only) |

### Comments & Likes
| Method | Endpoint | Auth | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/posts/comments/show/{id}` | Public | Get comments for a post (`id` = post ID) |
| `POST` | `/posts/comments/create/{id}` | Sanctum | Post a comment on article (`id` = post ID) |
| `PATCH` | `/posts/comments/edit/{id}` | Sanctum | Edit a comment (`id` = comment ID, owner only) |
| `DELETE` | `/posts/comments/delete/{id}` | Sanctum | Delete a comment (`id` = comment ID, owner/author only) |
| `GET` | `/posts/likes/show/{id}` | Public | Get likes list and count for post (`id` = post ID) |
| `POST` | `/posts/likes/create/{id}` | Sanctum | Toggle like / unlike on post (`id` = post ID) |

### Followers, Tags, Notifications & Sharing
| Method | Endpoint | Auth | Description |
| :--- | :--- | :--- | :--- |
| `POST` | `/users/follow/{id}` | Sanctum | Toggle follow / unfollow on developer (`id` = target user ID) |
| `GET` | `/users/followers/{id}` | Public | Get followers list for user |
| `GET` | `/users/following/{id}` | Public | Get following list for user |
| `GET` | `/tags` | Public | List all tags |
| `GET` | `/tags/posts/{id}` | Public | Get posts by tag ID or slug |
| `GET` | `/tags/trending` | Public | Get trending tags (calculated by recent post count) |
| `GET` | `/notifications` | Sanctum | Get all notifications for current user with `unread_count` |
| `POST` | `/notifications/{id}/read` | Sanctum | Mark single notification as read |
| `POST` | `/notifications/read/all` | Sanctum | Mark all notifications as read |
| `POST` | `/post/share/{id}` | Sanctum | Record post share by platform |
| `GET` | `/post/share/link/{id}` | Public | Get canonical shareable link for post |

---

## ⚡ Local Setup Guide

### Prerequisites
* PHP 8.1 or newer
* Composer
* Node.js 18+ and npm
* MySQL / MariaDB (e.g., via Laragon, XAMPP, or native MySQL)

---

### 1. Backend Setup

1. Navigate to the backend directory:
   ```bash
   cd backend
   ```
2. Install Composer dependencies:
   ```bash
   composer install
   ```
3. Create `.env` file and generate application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Configure your database settings in `backend/.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=devblogs
   DB_USERNAME=root
   DB_PASSWORD=
   ```
5. Run migrations and create the storage link:
   ```bash
   php artisan migrate
   php artisan storage:link
   ```
6. Start the Laravel API server:
   ```bash
   php artisan serve
   ```
   *The backend will run at `http://127.0.0.1:8000`.*

---

### 2. Frontend Setup

1. Navigate to the frontend directory:
   ```bash
   cd frontend
   ```
2. Install Node dependencies:
   ```bash
   npm install
   ```
3. Configure environment variables in `frontend/.env`:
   ```env
   VITE_API_BASE_URL=http://127.0.0.1:8000/api
   VITE_STORAGE_BASE_URL=http://127.0.0.1:8000/storage
   ```
4. Start the Vite development server:
   ```bash
   npm run dev
   ```
   *The frontend will run at `http://localhost:5173` (or `http://localhost:5174`).*

---

## 🧪 Testing & Quality Assurance

### Run Backend Tests
```bash
cd backend
php artisan test
```

### Run Frontend Tests, Linting & Build
```bash
cd frontend
npm run test:unit      # Vitest unit tests
npm run lint           # ESLint fix & check
npm run build          # Production Vite build
```

---

## 📄 License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
