<template>
  <div>
    <Navbar />
    <div class="encheres-app">
      <h1>Tableau des Enchères</h1>

      <!-- Formulaire pour ajouter une enchère -->
      <form @submit.prevent="submitForm">
        <input v-model="newEnchere.titre" placeholder="Titre" required />
        <textarea v-model="newEnchere.description" placeholder="Description" required></textarea>
        <input type="datetime-local" v-model="newEnchere.dateHeureDebut" required />
        <input type="datetime-local" v-model="newEnchere.dateHeureFin" required />
        <input type="number" v-model.number="newEnchere.prixDebut" placeholder="Prix de départ" required />
        <button type="submit">Ajouter Enchère</button>
      </form>

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
    const newEnchere = ref({
      titre: '',
      description: '',
      dateHeureDebut: '',
      dateHeureFin: '',
      prixDebut: 0,
    });

    const fetchEncheres = async () => {
      try {
        const response = await fetch('/api/encheres');
        if (!response.ok) {
          throw new Error("Erreur lors du chargement des enchères");
        }
        encheres.value = await response.json();
      } catch (error) {
        console.error(error);
      }
    };

    // Formulaire addEnchere Martin
    
    const addEnchere = async () => {
      try {
        const response = await fetch('/api/encheres/add', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(newEnchere.value),
        });

        if (!response.ok) {
          throw new Error("Erreur lors de l'ajout de l'enchère");
        }

        // Recharger les enchères après l'ajout
        await fetchEncheres();

        // Réinitialiser le formulaire
        newEnchere.value = {
          titre: '',
          description: '',
          dateHeureDebut: '',
          dateHeureFin: '',
          prixDebut: 0,
        };
      } catch (error) {
        console.error(error);
      }
    };

    const submitForm = async () => {
      await addEnchere();
    };

    onMounted(() => {
      fetchEncheres();
    });

    return {
      encheres,
      newEnchere,
      submitForm,
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

form {
  display: flex;
  flex-direction: column;
  margin-bottom: 2rem;
}

form input,
form textarea,
form button {
  margin-bottom: 1rem;
  padding: 0.75rem;
}

form button {
  background-color: #3498db;
  color: #fff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

form button:hover {
  background-color: #2980b9;
}
</style>
