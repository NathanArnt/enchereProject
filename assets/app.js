// assets/app.js
import './styles/app.css';

import { createApp } from 'vue';

import ProduitsApp from './components/Produits.vue';
import AccueilAdmin from './components/AccueilAdmin.vue';
import EncheresApp from './components/Encheres.vue';

createApp(ProduitsApp).mount('#produits-app');
createApp(AccueilAdmin).mount('#admin-app')
createApp(EncheresApp).mount('#encheres-app')
