import './bootstrap';
import {createApp} from 'vue'

import App from './templates/app.vue';
import { createRouter, createWebHistory } from 'vue-router';


import ReviewsPage from './templates/reviews.vue';
import MyAppointmentsPage from './templates/myAppointmant.vue';
import PricesPage from './templates/price.vue';
import Home from "@/templates/home.vue";

const routes = [
    { path: '/', component: Home },
    { path: '/reviews', component: ReviewsPage },
    { path: '/my-records', component: MyAppointmentsPage },
    { path: '/prices', component: PricesPage },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

const app = createApp(App);
app.use(router);
app.mount('#app');
