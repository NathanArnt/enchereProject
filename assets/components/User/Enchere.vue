<template>
    <div class="homePage">
      <div class="enchereContainer">
        <div class="card" v-for="enchere in encheres" :key="enchere.id">
          <div class="card-body">
            <div class="img">
              <img src="" alt="img-produit">
            </div>
            <div class="body">
              <div class="head">
                <h1>{{ enchere.leProduit.libelle }}</h1>
                <div>{{ enchere.prixDebut }} €</div>
              </div>
              <div class="desc">Description : {{ enchere.leProduit.description }}</div>
              <div>{{ enchere.statut }}</div>
              <button @click="selectEnchere(enchere)">Enchérir</button>
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
  
        const selectEnchere = (enchere) => {
          
        }
        const fetchEnchere = async () => {
          try {
            const response = await fetch('/api/encheresuser');
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
          selectEnchere,
        };
      },
    };
    </script>
    
  <style scoped>
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
    .homePage .enchereContainer .card .card-body .body button {
      border-radius: 15px;
      padding: 10px;
      background: none;
      border: 2px solid rgb(27, 27, 27);
    }
    .homePage .enchereContainer .card .card-body .body .desc {
      padding: 5px 0;
    }
    .homePage .enchereContainer .card .card-body .body .head {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
  </style>
    