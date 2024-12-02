<template>
  <div class="homePage">
    <div class="enchereContainer">
      <div class="card" v-for="enchere in encheres" :key="enchere.id">
        <div class="card-body">
          <div class="img">
            <img src="" alt="img-produit" />
          </div>
          <div class="body">
            <div class="head">
              <div>{{ enchere.leProduit.libelle }}</div>
              <div>{{ enchere.prixDebut }} €</div>
            </div>
            <div class="desc">Description : {{ enchere.leProduit.description }}</div>
            <div class="statut">
              <div>{{ enchere.statut }}</div>
              <button 
                @click="toggleParticipation(enchere)" 
                :disabled="isAuctionClosed(enchere)"
              >
                {{ isAuctionClosed(enchere) ? "Enchère terminée" : "Gérer ma Participation" }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="budget">
        <!-- Formulaire pour définir le budget maximum -->
        <form v-if="isClicked" @submit.prevent="addBudgetMax">
          <h3>Définir un Budget Maximum</h3>
          <input v-model="newBudgetMax" placeholder="Saisir le budget maximum" required />
          <button type="submit">Valider</button>
          <button type="button" @click="isClicked = false">Annuler</button>
        </form>
      </div>

      <div class="prixEncheri">
        <!-- Formulaire pour mettre à jour le prix enchéri -->
        <form v-if="isBudgetValidated" @submit.prevent="updatePrixEncheri">
          <h3>Mettre à jour le Prix Enchéri</h3>
          <input v-model="updatePrix" placeholder="Saisir le prix enchéri" required />
          <button type="submit">Valider</button>
          <button type="button" @click="isBudgetValidated = false">Annuler</button>
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
    // État
    const encheres = ref([]); // Liste des enchères
    const newBudgetMax = ref(""); // Budget maximum à définir
    const isClicked = ref(false); // Affichage du formulaire de budget
    const selectedEnchere = ref(null); // Enchère sélectionnée pour interaction
    const selectedParticipation = ref(null); // Participation existante de l'utilisateur
    const updatePrix = ref(""); // Prix enchéri saisi
    const isBudgetValidated = ref(false); // Affichage du formulaire de mise à jour de l'enchère

    // Vérifie si l'enchère est clôturée
    const isAuctionClosed = (enchere) => {
      const now = new Date();
      const endDate = new Date(enchere.dateHeureFin);
      return now > endDate;
    };

    // Fonction pour afficher le formulaire selon la participation
    const toggleParticipation = async (enchere) => {
      if (isAuctionClosed(enchere)) {
        alert("Cette enchère est terminée.");
        return;
      }

      selectedEnchere.value = enchere;

      try {
        // Vérification de la participation existante
        const response = await fetch(`/api/participation/${enchere.id}`);
        if (response.ok) {
          selectedParticipation.value = await response.json();
          isBudgetValidated.value = true; // Peut enchérir directement
          isClicked.value = false;
        } else if (response.status === 404) {
          selectedParticipation.value = null;
          isClicked.value = true; // Doit définir un budget maximum
          isBudgetValidated.value = false;
        } else {
          throw new Error("Erreur lors de la vérification de la participation");
        }
      } catch (error) {
        console.error("Erreur :", error);
        alert("Impossible de récupérer la participation.");
      }
    };

    // Fonction pour ajouter un budget maximum
    const addBudgetMax = async () => {
      try {
        const payload = {
          laEnchere: selectedEnchere.value.id,
          budgetMaximum: newBudgetMax.value,
        };

        const response = await fetch("/api/participation/budgetmax/add", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        });

        if (!response.ok) {
          const errorData = await response.json();
          alert(`Erreur API : ${errorData.error || "Erreur inconnue"}`);
          return;
        }

        const result = await response.json();
        alert("Budget maximum ajouté avec succès !");
        selectedParticipation.value = result; // Stocker la participation créée
        isClicked.value = false; // Masquer le formulaire
        newBudgetMax.value = ""; // Réinitialiser le champ
      } catch (error) {
        console.error("Erreur :", error);
        alert("Une erreur s'est produite lors de l'ajout du budget maximum.");
      }
    };

    // Fonction pour mettre à jour le prix enchéri
    const updatePrixEncheri = async () => {
      if (!selectedParticipation.value) {
        alert("Aucune participation existante pour cette enchère !");
        return;
      }

      try {
        const payload = { prixEncheri: updatePrix.value };

        const response = await fetch(`/api/participation/update/${selectedParticipation.value.id}`, {
          method: "PUT",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        });

        if (!response.ok) {
          const errorData = await response.json();
          alert(`Erreur API : ${errorData.error || "Erreur inconnue"}`);
          return;
        }

        alert("Prix enchéri mis à jour avec succès !");
        updatePrix.value = ""; // Réinitialiser le champ
        isBudgetValidated.value = false; // Masquer le formulaire
      } catch (error) {
        console.error("Erreur :", error);
        alert("Une erreur s'est produite lors de la mise à jour du prix enchéri.");
      }
    };

    // Charger les enchères depuis l'API
    const fetchEnchere = async () => {
      try {
        const response = await fetch("/api/encheresuser");
        if (!response.ok) {
          throw new Error("Erreur lors du chargement des enchères");
        }
        encheres.value = await response.json();
      } catch (error) {
        console.error("Erreur lors du chargement des enchères :", error);
      }
    };

    // Charger les données au montage du composant
    onMounted(() => {
      fetchEnchere();
    });

    return {
      encheres,
      newBudgetMax,
      isClicked,
      selectedEnchere,
      toggleParticipation,
      addBudgetMax,
      updatePrixEncheri,
      isBudgetValidated,
      updatePrix,
      isAuctionClosed,
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
  flex-wrap: wrap;
  gap: 20px;
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
