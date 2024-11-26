// assets/app.js
import './styles/app.css';

import { createApp } from 'vue';

import ProduitsApp from './components/Admin/Produits.vue';
import AccueilAdmin from './components/Admin/AccueilAdmin.vue';
import EncheresApp from './components/Admin/Encheres.vue';
import EnchereUserApp from './components/User/Enchere.vue';


createApp(ProduitsApp).mount('#produits-app');
createApp(AccueilAdmin).mount('#admin-app')
createApp(EncheresApp).mount('#encheres-app')
createApp(EnchereUserApp).mount('#enchere-user-app')