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
        <input v-model="currentEnchere.statut" placeholder="Statut" required />
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
  name: 'EncheresApp',
  components: {
    Navbar,
    DatePicker,
  },
  setup() {
    const encheres = ref([]);
    const produits = ref([]);
    const currentEnchere = ref({
      id: null,
      titre: '',
      dateHeureDebut: '',
      dateHeureFin: '',
      prixDebut: 0,
      statut: '',
      produitId: '',
    });

    const isEditing = ref(false);

    const fetchEncheres = async () => {
      try {
        const response = await fetch('/api/encheres');
        if (!response.ok) {
          throw new Error('Erreur lors du chargement des enchères');
        }
        encheres.value = await response.json();
      } catch (error) {
        console.error(error);
      }
    };

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

    const saveEnchere = async () => {
      try {
        let response;
        if (currentEnchere.value.id) {
          response = await fetch(`/api/encheres/update/${currentEnchere.value.id}`, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(currentEnchere.value),
          });

          if (!response.ok) {
            throw new Error('Erreur lors de la mise à jour de l\'enchère');
          }
        } else {
          response = await fetch('/api/encheres/add', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(currentEnchere.value),
          });

          if (!response.ok) {
            throw new Error('Erreur lors de l\'ajout de l\'enchère');
          }
        }

        await fetchEncheres();
        resetForm();
      } catch (error) {
        console.error(error);
      }
    };

    const deleteEnchere = async (id) => {
      try {
        const response = await fetch(`/api/encheres/delete/${id}`, {
          method: 'DELETE',
        });

        if (!response.ok) {
          throw new Error('Erreur lors de la suppression de l\'enchère');
        }

        await fetchEncheres();
      } catch (error) {
        console.error(error);
      }
    };

    const editEnchere = (enchere) => {
      currentEnchere.value = {
        id: enchere.id,
        titre: enchere.titre,
        dateHeureDebut: enchere.dateHeureDebut,
        dateHeureFin: enchere.dateHeureFin,
        prixDebut: enchere.prixDebut,
        statut: enchere.statut,
        produitId: produits.value.find(p => p.libelle === enchere.produitLibelle)?.id || '',
      };
      isEditing.value = true;
    };

    const resetForm = () => {
      currentEnchere.value = {
        id: null,
        titre: '',
        dateHeureDebut: '',
        dateHeureFin: '',
        prixDebut: 0,
        statut: '',
        produitId: '',
      };
      isEditing.value = false;
    };

    const cancelEdit = () => {
      resetForm();
    };

    onMounted(() => {
      fetchEncheres();
      fetchProduits();
    });

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
