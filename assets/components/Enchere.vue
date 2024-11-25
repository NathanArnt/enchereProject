<template>
  <div class="homePage">
    <div class="enchereContainer">
      <div class="card">
        <div class="card-body" v-for="enchere in encheres" :key="enchere.id">
          <div class="img">
            <img src="" alt="img-produit">
          </div>
          <div class="body">
            <h1>{{ enchere.titre }}</h1>
            <div>{{ enchere.description }}</div>
            <div>Statut : {{ enchere.statut }}</div>
            <div>Prix : {{ enchere.prixDebut }}</div>
          </div>
            
        </div>
      </div>
    </div>
  </div>
</template>
  
  <script>
  import { ref, onMounted } from 'vue';
  
  export default {
    name: 'EnchereApp',
    setup() {
      const encheres = ref([]);
  
      // Fonction pour récupérer les données des villes
      const fetchEnchere = async () => {
        try {
          const response = await fetch('/api/encheres');
          if (!response.ok) {
            throw new Error('Erreur lors du chargement des encheres');
          }
          encheres.value = await response.json();
        } catch (error) {
          console.error(error);
        }
      };
      // Charger les villes au montage du composant
      onMounted(() => {
        fetchEnchere();
      });
  
      return {
        encheres,
      };
    },
  };
  </script>
  
<style>
  .homePage {
    height: 100vh;
    background-color: #fafafa;
  }
  .homePage .enchereContainer {
    width: 1200px;
    padding: 200px 0;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .homePage .enchereContainer .card {
    border: 1px solid rgb(27, 27, 27);
    border-radius: 10px;
    height: 200px;
    width: 230px;
  }
  .homePage .enchereContainer .card .card-body .body {
    border-top: 1px solid rgb(27, 27, 27);
    padding: 10px;
  }
</style>
  