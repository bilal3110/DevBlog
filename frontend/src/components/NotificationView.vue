<template>
  <div class="notifications">
    <div class="n-head">
      <h2>Notifications</h2>
    </div>
    <div class="notificationCard" v-if="notifications.length > 0">
      <div class="n-item" v-for="(notification, index) in notifications" :key="index" @click="handleNotificationClick(notification)">
        <div class="n-content">
          <h3>{{ notification.title }}</h3>
          <p>{{ notification.message }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>

defineProps ({
  notifications: {
    type: Array,
    default: () => [
      { title: 'Notification 1', message: 'This is a notification message', icon: 'fa fa-bell' },
      { title: 'Notification 2', message: 'This is another notification message', icon: 'fa fa-bell' },
      { title: 'Notification 2', message: 'This is another notification message', icon: 'fa fa-bell'}
    ]
  }
})
</script>

<style scoped>
  .notifications{
    display: flex;
    flex-direction: column;
    max-width: 1200px;
  }

  .n-head{
    border-bottom: 1px solid rgba(255, 255, 255, 0.171);
  }

  .notificationCard{
    display: flex;
    flex-direction: column;
    width: 550px;
    padding: 0px 10px;
    border: 1px solid rgba(255, 255, 255, 0.171);
    margin-top: 20px;
    border-radius: 10px;
  }

  .n-item{
    display: flex;
  }


  .n-content{
    width: 100%;
    padding: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.171);
    cursor: pointer;
  }

  .n-content h3{
    font-size: 14px;
    color: var(--accent);
    opacity: 0.9;
  }

  .n-content h3::selection{
    background-color: var(--bg-light);
  }

  .n-content p{
    font-size: 15px;
  }
</style>
