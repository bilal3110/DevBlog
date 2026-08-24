<template>
  <div class="notifications-container">
    <div class="n-head">
      <div class="head-title">
        <h2>Notifications</h2>
        <span v-if="unreadCount > 0" class="unread-pill">{{ unreadCount }} unread</span>
      </div>
      <button
        v-if="notifications.length > 0 && unreadCount > 0"
        @click="markAllAsRead"
        class="btn-mark-all"
        :disabled="markingAll"
      >
        <i class="fa fa-check-double"></i> Mark all read
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <i class="fa fa-spinner fa-spin fa-2x"></i>
      <p>Loading notifications...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="notifications.length === 0" class="empty-state">
      <i class="far fa-bell-slash fa-3x"></i>
      <h3>No notifications yet</h3>
      <p>When people like, comment on your posts, or follow you, you'll see updates here.</p>
    </div>

    <!-- Notifications List -->
    <div v-else class="notificationCard">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        class="n-item"
        :class="{ unread: !notification.is_read }"
        @click="handleNotificationClick(notification)"
      >
        <div class="n-icon">
          <i :class="getIconForType(notification.type)"></i>
        </div>

        <div class="n-content">
          <div class="n-top">
            <h3 class="n-type-title">{{ formatType(notification.type) }}</h3>
            <span class="n-time">{{ formatDate(notification.created_at) }}</span>
          </div>
          <p class="n-message">{{ notification.data || notification.message }}</p>
        </div>

        <div v-if="!notification.is_read" class="unread-dot" title="Unread"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../utils/api';

const notifications = ref([]);
const unreadCount = ref(0);
const loading = ref(true);
const markingAll = ref(false);

const fetchNotifications = async () => {
  loading.value = true;
  try {
    const data = await api.get('/notifications');
    notifications.value = data.notifications || [];
    unreadCount.value = data.unread_count ?? notifications.value.filter(n => !n.is_read).length;
  } catch (err) {
    console.error('Failed to fetch notifications:', err);
  } finally {
    loading.value = false;
  }
};

const handleNotificationClick = async (notification) => {
  if (!notification.is_read) {
    try {
      await api.post(`/notifications/${notification.id}/read`);
      notification.is_read = 1;
      unreadCount.value = Math.max(0, unreadCount.value - 1);
    } catch (err) {
      console.error('Failed to mark notification as read:', err);
    }
  }
};

const markAllAsRead = async () => {
  markingAll.value = true;
  try {
    await api.post('/notifications/read/all');
    notifications.value.forEach(n => n.is_read = 1);
    unreadCount.value = 0;
  } catch (err) {
    console.error('Failed to mark all as read:', err);
  } finally {
    markingAll.value = false;
  }
};

const getIconForType = (type) => {
  switch (type) {
    case 'like':
      return 'fa fa-heart icon-like';
    case 'comment':
      return 'fa fa-comment icon-comment';
    case 'follow':
      return 'fa fa-user-plus icon-follow';
    default:
      return 'fa fa-bell icon-default';
  }
};

const formatType = (type) => {
  if (!type) return 'Notification';
  return type.charAt(0).toUpperCase() + type.slice(1);
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const now = new Date();
  const diffMinutes = Math.floor((now - date) / (1000 * 60));

  if (diffMinutes < 1) return 'Just now';
  if (diffMinutes < 60) return `${diffMinutes}m ago`;
  const diffHours = Math.floor(diffMinutes / 60);
  if (diffHours < 24) return `${diffHours}h ago`;
  const diffDays = Math.floor(diffHours / 24);
  if (diffDays < 7) return `${diffDays}d ago`;

  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

onMounted(fetchNotifications);
</script>

<style scoped>
.notifications-container {
  display: flex;
  flex-direction: column;
  width: 100%;
  max-width: 550px;
  margin: 15px auto 80px;
}

.n-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
  padding-bottom: 12px;
}

.head-title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.head-title h2 {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

.unread-pill {
  background: var(--accent);
  color: #000;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 12px;
}

.btn-mark-all {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: var(--text-dark);
  font-size: 0.82rem;
  font-weight: 500;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}

.btn-mark-all:hover {
  background: rgba(66, 184, 131, 0.15);
  color: var(--accent);
}

.notificationCard {
  display: flex;
  flex-direction: column;
  margin-top: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  overflow: hidden;
  background: #0d1117;
}

.n-item {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  cursor: pointer;
  position: relative;
  transition: background-color 0.2s;
}

.n-item:last-child {
  border-bottom: none;
}

.n-item:hover {
  background: rgba(255, 255, 255, 0.04);
}

.n-item.unread {
  background: rgba(66, 184, 131, 0.05);
}

.n-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.icon-like {
  color: #ef4444;
}

.icon-comment {
  color: #3b82f6;
}

.icon-follow {
  color: var(--accent);
}

.icon-default {
  color: #f59e0b;
}

.n-content {
  flex: 1;
}

.n-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.n-type-title {
  font-size: 0.9rem;
  font-weight: 600;
  margin: 0;
  color: var(--accent);
}

.n-time {
  font-size: 0.78rem;
  color: #8b949e;
}

.n-message {
  font-size: 0.9rem;
  color: #c9d1d9;
  margin: 0;
  line-height: 1.4;
}

.unread-dot {
  width: 8px;
  height: 8px;
  background: var(--accent);
  border-radius: 50%;
  position: absolute;
  top: 20px;
  right: 16px;
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #8b949e;
}

.empty-state i {
  color: #6e7681;
  margin-bottom: 14px;
}

.empty-state h3 {
  color: var(--text-dark);
  margin: 0 0 6px 0;
}
</style>
