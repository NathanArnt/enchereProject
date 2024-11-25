<template>
    <div>
      <Navbar />
      <div class="encheres-app">
        <h1>Tableau des Enchères</h1>
        <div class="table-container">
          <div class="table-header">
            <div>ID</div>
            <div>Titre</div>
            <div>Description</div>
            <div>Date de Début</div>
            <div>Date de Fin</div>
            <div>Prix de Début</div>
            <div>Statut</div>
          </div>
          <div class="table-row" v-for="enchere in encheres" :key="enchere.id">
            <div>{{ enchere.id }}</div>
            <div>{{ enchere.titre }}</div>
            <div>{{ enchere.description }}</div>
            <div>{{ enchere.dateHeureDebut }}</div>
            <div>{{ enchere.dateHeureFin }}</div>
            <div>{{ enchere.prixDebut }}</div>
            <div>{{ enchere.statut }}</div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, onMounted } from 'vue';
  import Navbar from './Navbar.vue';
  
  export default {
    name: 'EncheresApp',
    components: {
      Navbar,
    },
    setup() {
      const encheres = ref([]);
  
      const fetchEncheres = async () => {
        try {
          const response = await fetch('/api/encheres'); // Assurez-vous que cette route est correcte
          if (!response.ok) {
            throw new Error('Erreur lors du chargement des enchères');
          }
          encheres.value = await response.json();
        } catch (error) {
          console.error(error);
        }
      };
  
      onMounted(() => {
        fetchEncheres();
      });
  
      return {
        encheres,
      };
    },
  };
  </script>
  
  <style scoped>
  .encheres-app {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 1rem;
    background: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
  }
  
  .encheres-app h1 {
    text-align: center;
    margin-bottom: 1.5rem;
    color: #34495e;
  }
  
  .table-container {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
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