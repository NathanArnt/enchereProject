<template>
  <div>
    <Navbar />
    <div class="encheres-app">
      <h1>Gestion des Enchères</h1>
      <form @submit.prevent="saveEnchere">
        <input v-model="currentEnchere.titre" placeholder="Titre de l'enchère" required />
        <DatePicker v-model="currentEnchere.dateHeureDebut" placeholder="Date de Début" :enable-time="true" required />
        <DatePicker v-model="currentEnchere.dateHeureFin" placeholder="Date de Fin" :enable-time="true" required />
        <input v-model.number="currentEnchere.prixDebut" placeholder="Prix de Début" required />
        <select v-model="currentEnchere.produitId" required>
          <option disabled value="">Sélectionnez un produit</option>
          <option v-for="produit in produits" :key="produit.id" :value="produit.id">
            {{ produit.libelle }}
          </option>
        </select>
        <div class="button-group">
          <button type="submit" class="btn btn-success">{{ isEditing ? 'Mettre à jour' : 'Ajouter' }}</button>
          <button type="button" class="btn btn-secondary" @click="cancelEdit" v-if="isEditing">Annuler</button>
        </div>
      </form>

      <div class="table-container">
        <div class="table-header">
          <div>ID</div>
          <div>Titre</div>
          <div>Date de Début</div>
          <div>Date de Fin</div>
          <div>Prix de Début</div>
          <div>Statut</div>
          <div>Produit</div>
          <div>Actions</div>
        </div>
        <div class="table-row" v-for="enchere in encheres" :key="enchere.id">
          <div>{{ enchere.id }}</div>
          <div>{{ enchere.titre }}</div>
          <div>{{ enchere.dateHeureDebut }}</div>
          <div>{{ enchere.dateHeureFin }}</div>
          <div>{{ enchere.prixDebut }}</div>
          <div>{{ enchere.statut }}</div>
          <div>{{ enchere.produitLibelle }}</div>
          <div>
            <button @click="editEnchere(enchere)" class="btn btn-primary">Modifier</button>
            <button @click="deleteEnchere(enchere.id)" class="btn btn-danger">Supprimer</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import Navbar from '../Navbar.vue';
import DatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

export default {
  name: 'EncheresApp', // Nom du composant
  components: {
    Navbar, // Composant de la barre de navigation
    DatePicker, // Composant de sélection de date
  },
  setup() {
    // Utilisation de la composition API de Vue 3
    const encheres = ref([]); // Référence pour stocker les enchères
    const produits = ref([]); // Référence pour stocker les produits
    const currentEnchere = ref({
      id: null,
      titre: '',
      dateHeureDebut: '',
      dateHeureFin: '',
      prixDebut: 0,
      produitId: '',
    }); // Référence pour stocker l'enchère courante

    const isEditing = ref(false); // Référence pour savoir si on est en mode édition

    // Fonction pour récupérer les enchères
    const fetchEncheres = async () => {
      try {
        console.log('Fetching enchères...');
        const response = await fetch('/api/encheres');
        if (!response.ok) {
          throw new Error('Erreur lors du chargement des enchères');
        }
        encheres.value = await response.json();
        console.log('Enchères fetched:', encheres.value);
      } catch (error) {
        console.error('Fetch enchères error:', error);
      }
    };

    // Fonction pour récupérer les produits
    const fetchProduits = async () => {
      try {
        console.log('Fetching produits...');
        const response = await fetch('/api/produits');
        if (!response.ok) {
          throw new Error('Erreur lors du chargement des produits');
        }
        produits.value = await response.json();
        console.log('Produits fetched:', produits.value);
      } catch (error) {
        console.error('Fetch produits error:', error);
      }
    };

    // Fonction pour récupérer les enchères mises à jour
    const fetchUpdatedEncheres = async () => {
      try {
        console.log('Fetching updated enchères...');
        const response = await fetch('/api/encheres');
        if (!response.ok) {
          throw new Error('Erreur lors de la mise à jour des enchères');
        }
        const updatedEncheres = await response.json();
        encheres.value = updatedEncheres;
        console.log('Updated enchères:', encheres.value);
      } catch (error) {
        console.error('Fetch updated enchères error:', error);
      }
    };

    // Fonction pour sauvegarder une enchère
    const saveEnchere = async () => {
      try {
        console.log('Saving enchère...');
        let response;
        const enchereToSave = {
          ...currentEnchere.value,
          dateHeureDebut: new Date(currentEnchere.value.dateHeureDebut).toISOString(),
          dateHeureFin: new Date(currentEnchere.value.dateHeureFin).toISOString(),
        };

        const url = currentEnchere.value.id ? `/api/encheres/update/${currentEnchere.value.id}` : '/api/encheres/add';
        const method = currentEnchere.value.id ? 'PUT' : 'POST';

        response = await fetch(url, {
          method,
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(enchereToSave),
        });

        if (!response.ok) {
          throw new Error("Erreur lors de la sauvegarde de l'enchère");
        }

        await fetchUpdatedEncheres();
        resetForm();
        console.log('Enchère saved and updated enchères fetched.');
      } catch (error) {
        console.error('Save enchère error:', error);
      }
    };

    // Fonction pour supprimer une enchère
    const deleteEnchere = async (id) => {
      try {
        console.log(`Deleting enchère with id ${id}...`);
        const response = await fetch(`/api/encheres/delete/${id}`, {
          method: 'DELETE',
        });

        if (!response.ok) {
          throw new Error('Erreur lors de la suppression de l\'enchère');
        }

        await fetchUpdatedEncheres();
        console.log('Enchère deleted and updated enchères fetched.');
      } catch (error) {
        console.error('Delete enchère error:', error);
      }
    };

    // Fonction pour éditer une enchère
    const editEnchere = (enchere) => {
      console.log('Editing enchère:', enchere);
      currentEnchere.value = {
        id: enchere.id,
        titre: enchere.titre,
        dateHeureDebut: enchere.dateHeureDebut,
        dateHeureFin: enchere.dateHeureFin,
        prixDebut: enchere.prixDebut,
        produitId: produits.value.find(p => p.id === enchere.produitId)?.id || '',
      };
      isEditing.value = true;
    };

    // Fonction pour réinitialiser le formulaire
    const resetForm = () => {
      console.log('Resetting form...');
      currentEnchere.value = {
        id: null,
        titre: '',
        dateHeureDebut: '',
        dateHeureFin: '',
        prixDebut: 0,
        produitId: '',
      };
      isEditing.value = false;
    };

    // Fonction pour annuler l'édition
    const cancelEdit = () => {
      resetForm();
    };

    // Lifecycle hook monté - appelé lorsque le composant est monté
    onMounted(() => {
      console.log('Component mounted. Fetching initial data...');
      fetchEncheres(); // Récupère les enchères initiales
      fetchProduits(); // Récupère les produits initiales
      setInterval(fetchUpdatedEncheres, 10000); // Mettre à jour toutes les 10 secondes
    });

    // Retourne les variables et fonctions pour être utilisées dans le template
    return {
      encheres,
      produits,
      currentEnchere,
      isEditing,
      saveEnchere,
      deleteEnchere,
      editEnchere,
      cancelEdit,
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

form {
  display: flex;
  flex-direction: column;
  margin-bottom: 20px;
}

input, select {
  margin-bottom: 10px;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.button-group {
  display: flex;
  gap: 10px;
}

button {
  padding: 10px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-weight: bold;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.btn-success {
  background-color: #28a745;
  color: white;
}

.btn-success:hover {
  background-color: #218838;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #5a6268;
}

.btn-danger {
  background-color: #dc3545;
  color: white;
}

.btn-danger:hover {
  background-color: #c82333;
}

.table-container {
  display: grid;
  grid-template-columns: repeat(8, 1fr);
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
