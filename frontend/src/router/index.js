import { createRouter, createWebHistory } from 'vue-router'

import HomeView from '../views/HomeView.vue'
import ExploreView from '../views/ExploreView.vue'
import CreatePost from '../views/CreatePostView.vue'
import Notifications from '../views/NotificationsView.vue'

const routes = [
  {path: '/', name: 'Home', component: HomeView},
  {path: '/explore', name: 'Explore', component: ExploreView },
  {path: '/createpost', name: 'CreatePost', component: CreatePost},
  {path: '/notifications', name: 'Notifications', component: Notifications},
]

const router = createRouter({
  history: createWebHistory(),
  routes
})
export default router

