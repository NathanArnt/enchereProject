<template>
  <div class="homePage">
    <section class="incoming-section">
      <h2>Enchères à venir</h2>
      <div class="enchereContainer">
        <div class="card" v-for="enchere in incomingEncheres" :key="enchere.id">
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
                  :class="{'btn-terminated': isAuctionClosed(enchere), 'btn-participate': !isAuctionClosed(enchere)}"
                >
                  {{ isAuctionClosed(enchere) ? "Enchère terminée" : "Gérer ma Participation" }}
                </button>
              </div>
            </div>
          </div>
          <div v-if="selectedEnchere && selectedEnchere.id === enchere.id" class="participation-form">
            <div class="budget" v-if="isClicked">
              <!-- Formulaire pour définir le budget maximum -->
              <form @submit.prevent="addBudgetMax">
                <h3>Définir un Budget Maximum</h3>
                <input v-model="newBudgetMax" placeholder="Saisir le budget maximum" required />
                <div class="button-group">
                  <button type="submit" class="btn-success">Valider</button>
                  <button type="button" class="btn-secondary" @click="isClicked = false">Annuler</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <section class="live-section">
      <h2>Enchères en cours</h2>
      <div class="enchereContainer">
        <div class="card" v-for="enchere in liveEncheres" :key="enchere.id">
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
                  :class="{'btn-terminated': isAuctionClosed(enchere), 'btn-participate': !isAuctionClosed(enchere)}"
                >
                  {{ isAuctionClosed(enchere) ? "Enchère terminée" : "Gérer ma Participation" }}
                </button>
              </div>
            </div>
          </div>
          <div v-if="selectedEnchere && selectedEnchere.id === enchere.id" class="participation-form">
            <div class="budget" v-if="isClicked">
              <!-- Formulaire pour définir le budget maximum -->
              <form @submit.prevent="addBudgetMax">
                <h3>Définir un Budget Maximum</h3>
                <input v-model="newBudgetMax" placeholder="Saisir le budget maximum" required />
                <div class="button-group">
                  <button type="submit" class="btn-success">Valider</button>
                  <button type="button" class="btn-secondary" @click="isClicked = false">Annuler</button>
                </div>
              </form>
            </div>

            <div class="prixEncheri" v-if="isBudgetValidated">
              <!-- Formulaire pour mettre à jour le prix enchéri -->
              <form @submit.prevent="updatePrixEncheri">
                <h3>Mettre à jour le Prix Enchéri</h3>
                <input v-model="updatePrix" placeholder="Saisir le prix enchéri" required />
                <div class="button-group">
                  <button type="submit" class="btn-success">Valider</button>
                  <button type="button" class="btn-secondary" @click="isBudgetValidated = false">Annuler</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <section class="over-section">
      <h2>Enchères terminées</h2>
      <div class="enchereContainer">
        <div class="card" v-for="enchere in overEncheres" :key="enchere.id">
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
                  :class="{'btn-terminated': isAuctionClosed(enchere), 'btn-participate': !isAuctionClosed(enchere)}"
                >
                  {{ isAuctionClosed(enchere) ? "Enchère terminée" : "Gérer ma Participation" }}
                </button>
              </div>
            </div>
          </div>
          <div v-if="selectedEnchere && selectedEnchere.id === enchere.id" class="participation-form">
            <div class="budget" v-if="isClicked">
              <!-- Formulaire pour définir le budget maximum -->
              <form @submit.prevent="addBudgetMax">
                <h3>Définir un Budget Maximum</h3>
                <input v-model="newBudgetMax" placeholder="Saisir le budget maximum" required />
                <div class="button-group">
                  <button type="submit" class="btn-success">Valider</button>
                  <button type="button" class="btn-secondary" @click="isClicked = false">Annuler</button>
                </div>
              </form>
            </div>

            <div class="prixEncheri" v-if="isBudgetValidated">
              <!-- Formulaire pour mettre à jour le prix enchéri -->
              <form @submit.prevent="updatePrixEncheri">
                <h3>Mettre à jour le Prix Enchéri</h3>
                <input v-model="updatePrix" placeholder="Saisir le prix enchéri" required />
                <div class="button-group">
                  <button type="submit" class="btn-success">Valider</button>
                  <button type="button" class="btn-secondary" @click="isBudgetValidated = false">Annuler</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
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

    // Filtrer les enchères en fonction de leur statut
    const incomingEncheres = ref([]);
    const liveEncheres = ref([]);
    const overEncheres = ref([]);

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

        // Filtrer les enchères en fonction de leur statut
        incomingEncheres.value = encheres.value.filter(enchere => enchere.statut === 'incoming');
        liveEncheres.value = encheres.value.filter(enchere => enchere.statut === 'live');
        overEncheres.value = encheres.value.filter(enchere => enchere.statut === 'over');
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
      incomingEncheres,
      liveEncheres,
      overEncheres,
    };
  },
};
</script>


<style scoped>
/* Style global de la page */
.homePage {
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(to right, #f9f9f9, #eaeaea);
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* Conteneur des enchères */
.enchereContainer {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: center;
  margin-top: 20px;
}

/* Style de chaque carte */
.card {
  width: 350px;
  border: none;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease;
}

.card:hover {
  transform: scale(1.05);
}

.card-body {
  padding: 15px;
  background: #ffffff;
}

.card .img img {
  width: 100%;
  height: 150px;
  object-fit: cover;
}

/* Style du contenu de la carte */
.body .head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.2rem;
  font-weight: bold;
  margin-bottom: 10px;
}

.body .desc {
  font-size: 1rem;
  color: #666;
  margin-bottom: 15px;
}

.body .statut {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.body .statut button {
  padding: 10px 15px;
  border: none;
  border-radius: 25px;
  background: #007bff;
  color: white;
  cursor: pointer;
  transition: background 0.3s ease;
}

.body .statut .btn-terminated {
  background: #dc3545;
}

.body .statut .btn-terminated:hover {
  background: #c82333;
}

.body .statut .btn-participate {
  background: #28a745;
}

.body .statut .btn-participate:hover {
  background: #218838;
}

.form-container {
  margin-top: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
}

.budget, .prixEncheri {
  background: white;
  padding: 20px;
  border-radius: 15px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  width: 300px;
}

.budget h3, .prixEncheri h3 {
  margin-bottom: 15px;
  font-size: 1.2rem;
  font-weight: bold;
  text-align: center;
}

.budget input, .prixEncheri input {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.button-group {
  display: flex;
  justify-content: space-between;
}

.button-group button {
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-weight: bold;
}

.button-group .btn-success {
  background-color: #28a745;
  color: white;
}

.button-group .btn-success:hover {
  background-color: #218838;
}

.button-group .btn-secondary {
  background-color: #6c757d;
  color: white;
}

.button-group .btn-secondary:hover {
  background-color: #5a6268;
}

/* Sections pour les enchères */
.incoming-section, .live-section, .over-section {
  width: 100%;
  padding: 20px;
}

.incoming-section h2, .live-section h2, .over-section h2 {
  text-align: center;
  margin-bottom: 20px;
  font-size: 2rem;
  color: #333;
  text-transform: uppercase;
}

.participation-form {
  background: #f9f9f9;
  padding: 20px;
  border-radius: 15px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  margin-top: 10px;
}
</style>