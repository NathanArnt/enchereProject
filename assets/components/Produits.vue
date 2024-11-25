<template>
  <div>
    <Navbar />
    <div class="produits-app">
      <h1>Tableau des Produits</h1>
      <div class="table-container">
        <div class="table-header">
          <div>ID</div>
          <div>Libelle</div>
          <div>Description</div>
          <div>Prix Plancher</div>
        </div>
        <div class="table-row" v-for="produit in produits" :key="produit.id">
          <div>{{ produit.id }}</div>
          <div>{{ produit.libelle }}</div>
          <div>{{ produit.description }}</div>
          <div>{{ produit.prixPlancher }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import Navbar from './Navbar.vue';

export default {
  name: 'ProduitsApp',
  components: {
    Navbar,
  },
  setup() {
    const produits = ref([]);

    const fetchProduits = async () => {
      try {
        const response = await fetch('/api/produits');
        if (!response.ok) {
          throw new Error('Erreur lors du chargement des produits');
        }
        produits.value = await response.json();
      } catch (error) {
        console.error(error);
      }
    };

    onMounted(() => {
      fetchProduits();
    });

    return {
      produits,
    };
  },
};
</script>

<style scoped>
.produits-app {
  max-width: 1000px;
  margin: 2rem auto;
  padding: 1rem;
  background: #fff;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  border-radius: 10px;
}

.produits-app h1 {
  text-align: center;
  margin-bottom: 1.5rem;
  color: #34495e;
}

.table-container {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
}

.table-header,
.table-row {
  background-color: #ecf0f1;
  border-radius: 5px;
  display: contents;
  font-weight: bold;
  text-align: center;
}

.table-header {
  background-color: #bdc3c7;
  font-weight: bold;
}

.table-row div {
  padding: 0.75rem;
  transition: background-color 0.3s ease;
}

.table-row:nth-child(even) div {
  background-color: #f4f4f4;
}

.table-row div:hover {
  background-color: #dfe6e9;
}

.table-row div {
  border-right: 1px solid #ddd;
}

.table-row div:last-child {
  border-right: none;
}
</style>