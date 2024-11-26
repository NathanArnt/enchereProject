// assets/app.js
import './styles/app.css';

import { createApp } from 'vue';

import ProduitsApp from './components/Produits.vue';
import EncheresApp from './components/Encheres.vue';

createApp(ProduitsApp).mount('#produits-app');
createApp(EncheresApp).mount('#encheres-app');
