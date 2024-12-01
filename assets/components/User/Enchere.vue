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
                <div>{{ enchere.leProduit.libelle }}</div>
                <div>{{ enchere.prixDebut }} €</div>
              </div>
              <div class="desc">Description : {{ enchere.leProduit.description }}</div>
              <div class="statut">
                <div>{{ enchere.statut }}</div>
                <button @click="toggleBudget(enchere)">Budget Max</button>
              </div>
            </div>
          </div>
        </div>
        <!-- Formulaire dynamique -->
        <form v-if="isClicked" @submit.prevent="addBudgetMax">
          <input v-model="newBudgetMax" placeholder="Saisir le budget maximum" />
          <button type="submit">Valider</button>
        </form>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, onMounted } from "vue";
  
  export default {
    name: "EnchereApp",
    setup() {
      const encheres = ref([]); // Liste des enchères
      const newBudgetMax = ref(""); // Valeur saisie pour le budget max
      const isClicked = ref(false); // État du formulaire
      const selectedEnchere = ref(null); // Enchère sélectionnée pour le budget
      const newParticipation = ref({
        laEnchere: "",
        budgetMaximum: "",
        prixEncheri: 0, // Par défaut ou modifiable
      });
  
      // Fonction pour afficher le formulaire pour une enchère spécifique
      const toggleBudget = (enchere) => {
        selectedEnchere.value = enchere;
        isClicked.value = true;
      };
  
      // Charger les enchères
      const fetchEnchere = async () => {
        try {
          const response = await fetch("/api/encheresuser");
          if (!response.ok) {
            throw new Error("Erreur lors du chargement des enchères");
          }
          encheres.value = await response.json();
        } catch (error) {
          console.error(error);
        }

      };
  
      // Ajouter le budget maximum
      const addBudgetMax = async () => {
        try {
          // Remplir les données à envoyer à l'API
          newParticipation.value.laEnchere = selectedEnchere.value.id;
          newParticipation.value.budgetMaximum = newBudgetMax.value;
  
          const response = await fetch("/api/participation/budgetmax/add", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify(newParticipation.value),
          });
  
          if (!response.ok) {
            throw new Error("Erreur lors de l'ajout du budget max");

          }
  
          alert("Budget maximum ajouté avec succès !");
          // Réinitialiser l'état du formulaire
          isClicked.value = false;
          newBudgetMax.value = "";
          selectedEnchere.value = null;
        } catch (error) {
          console.error(error);
        }
      };
  
      // Charger les enchères au montage
      onMounted(() => {
        fetchEnchere();
      });
  
      return {
        encheres,
        newBudgetMax,
        isClicked,
        selectedEnchere,
        toggleBudget,
        addBudgetMax,
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
      height: 220px;
      width: 250px;
    }
    .homePage .enchereContainer .card .card-body .body {
      border-top: 1px solid rgb(27, 27, 27);
      padding: 10px;
    }
    .homePage .enchereContainer .card .card-body .body button {
      border-radius: 15px;
      padding: 12px;
      background: none;
      border: 2px solid rgb(27, 27, 27);
      cursor: pointer;
    }
    .homePage .enchereContainer .card .card-body .body .desc {
      padding: 10px 0;
    }
    .homePage .enchereContainer .card .card-body .body .head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-weight: 700;
      font-size: 1.5rem;
    }
    .homePage .enchereContainer .card .card-body .body .statut {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 700;
        font-size: 1.5rem;
    }
  </style>
  