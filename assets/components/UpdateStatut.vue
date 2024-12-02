<template>
  <div class="update-status-app"></div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  name: 'StatusUpdater',
  setup() {
    const encheres = ref([]);

    const fetchEncheres = async () => {
      try {
        const response = await fetch('/api/encheres');
        if (!response.ok) {
          throw new Error('Erreur lors du chargement des enchères');
        }
        encheres.value = await response.json();
        updateEnchereStatus();
      } catch (error) {
        console.error(error);
      }
    };

    const updateEnchereStatus = async () => {
      const updatedEncheres = encheres.value.map(async (enchere) => {
        const now = new Date();
        const dateDebut = new Date(enchere.dateHeureDebut);
        const dateFin = new Date(enchere.dateHeureFin);

        let newStatus;
        if (now < dateDebut) {
          newStatus = 'incoming';
        } else if (now >= dateDebut && now < dateFin) {
          newStatus = 'live';
        } else if (now >= dateFin) {
          newStatus = 'over';
        }

        if (enchere.statut !== newStatus) {
          enchere.statut = newStatus;

          // Envoyer la mise à jour du statut au backend
          await fetch(`/api/encheres/update-status/${enchere.id}`, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ statut: newStatus }),
          });
        }
        return enchere;
      });

      encheres.value = await Promise.all(updatedEncheres);
    };

    onMounted(() => {
      fetchEncheres();
      setInterval(updateEnchereStatus, 10000); // Mise à jour toutes les 10 secondes
    });

    return {};
  },
};
</script>

<style scoped>
/* Aucun style n'est nécessaire car ce composant fonctionne en arrière-plan */
</style>
