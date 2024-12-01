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

      <div class="budget">
        <!-- Formulaire dynamique -->
        <form v-if="isClicked" @submit.prevent="addBudgetMax">
          <input v-model="newBudgetMax" placeholder="Saisir le budget maximum" />
          <button type="submit">Valider</button>
        </form>
      </div>
      <div class="prixEncheri">
        <!-- Formulaire dynamique -->
        <form v-if="isBudgetValidated" @submit.prevent="updatePrixEncheri">
          <input v-model="updatePrix" placeholder="Saisir le prix encheri" />
          <button type="submit">Valider</button>
        </form>
      </div>
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
    const updatePrix = ref(""); // Valeur saisie pour le prix encheri
    const isBudgetValidated = ref(true); // État du formulaire
    const participationId = ref(null);

    // Fonction pour afficher le formulaire pour une enchère spécifique
    const togglePrixEncheri = (enchere) => {
      selectedEnchere.value = enchere;
      isBudgetValidated.value = true;
    };
    
    // Ajouter des logs de débogage avant l'envoi
    const updatePrixEncheri = async () => {
      try {
        // Validation des données
        console.log("Tentative de mise à jour du prix enchéri.");
        if (!updatePrix.value || isNaN(updatePrix.value) || updatePrix.value <= 0) {
          alert("Veuillez saisir un prix valide (nombre positif).");
          console.error("Prix invalide :", updatePrix.value);
          return;
        }
        }

        const result = await response.json();
        console.log("Réponse de l'API :", result);
        alert("Prix enchéri mis à jour avec succès !");
        isBudgetValidated.value = false;
        updatePrix.value = ""; // Réinitialisation du formulaire
      } catch (error) {
        console.error("Erreur lors de la mise à jour :", error);
        alert("Une erreur s'est produite, vérifiez les logs pour plus d'informations.");
      }
    };

    const toggleBudget = (enchere) => {
      selectedEnchere.value = enchere;
      isClicked.value = true;
    };

    const fetchEnchere = async () => {
      try {
        console.log("Chargement des enchères...");
        const response = await fetch("/api/encheresuser");
        if (!response.ok) {
          throw new Error("Erreur lors du chargement des enchères");
        }
        encheres.value = await response.json();
        console.log("Enchères chargées :", encheres.value);
      } catch (error) {
        console.error("Erreur lors du chargement des enchères :", error);
      }
    };

    const addBudgetMax = async () => {
      try {
        console.log("Tentative d'ajout du budget maximum...");
        newParticipation.value.laEnchere = selectedEnchere.value.id;
        newParticipation.value.budgetMaximum = newBudgetMax.value;

        console.log("Données envoyées :", newParticipation.value);

        const response = await fetch("/api/participation/budgetmax/add", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(newParticipation.value),
        });

        if (!response.ok) {
          const errorData = await response.json();
          console.error("Erreur lors de l'ajout :", errorData);
          alert(`Erreur API : ${errorData.error || "Erreur inconnue"}`);
          return;
        }

        const result = await response.json();
        console.log("Participation ajoutée :", result);

        participationId.value = result.id; // ID retourné par l'API
        alert("Budget maximum ajouté avec succès !");
        isClicked.value = false;
        newBudgetMax.value = "";
        selectedEnchere.value = null;
      } catch (error) {
        console.error("Erreur lors de l'ajout du budget maximum :", error);
        alert("Une erreur s'est produite lors de l'ajout du budget maximum.");
      }
    };

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
      togglePrixEncheri,
      updatePrixEncheri,
      isBudgetValidated,
      updatePrix,
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