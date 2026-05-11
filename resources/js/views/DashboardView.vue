<template>
  <AppLayout>
    <div class="p-6 space-y-8">
      <!-- Hero Section -->
      <div class="text-center space-y-4 py-8">
        <div class="inline-flex items-center justify-center p-2 bg-primary-100 rounded-full">
          <svg class="w-12 h-12 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <h1 class="text-4xl font-bold text-dark-900">
          Bienvenue sur Rapport Mensuel
        </h1>
        <p class="text-lg text-dark-500 max-w-2xl mx-auto">
          La solution complète pour la gestion des ressources humaines
        </p>
      </div>

      <!-- Features Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
        <FeatureCard
          icon="👥"
          title="Gestion des agents"
          description="Centralisez toutes les informations de vos employés : coordonnées, contrats, départements et managers."
          color="blue"
        />
        <FeatureCard
          icon="📅"
          title="Gestion des congés"
          description="Gérez facilement les demandes de congés, suivez les soldes et approuvez rapidement."
          color="green"
        />
        <FeatureCard
          icon="🕒"
          title="Suivi des heures"
          description="Enregistrez et suivez les heures de travail avec des feuilles d'activité détaillées."
          color="purple"
        />
        <FeatureCard
          icon="📊"
          title="Tableaux de bord"
          description="Visualisez les indicateurs clés et suivez les performances de votre équipe."
          color="orange"
        />
        <FeatureCard
          icon="📋"
          title="Planning"
          description="Organisez les plannings de travail et gérez les absences facilement."
          color="red"
        />
        <FeatureCard
          icon="📈"
          title="Rapports"
          description="Générez des rapports personnalisés pour analyser vos données RH."
          color="teal"
        />
      </div>

      <!-- How It Works Section -->
      <div class="bg-white rounded-xl border border-dark-100 p-8 mt-8">
        <h2 class="text-2xl font-semibold text-dark-900 text-center mb-8">
          Comment ça fonctionne ?
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <StepCard
            number="1"
            title="Créez vos agents"
            description="Ajoutez vos employés avec leurs informations et contrats"
          />
          <StepCard
            number="2"
            title="Gérez les congés"
            description="Les agents font leurs demandes, vous approuvez"
          />
          <StepCard
            number="3"
            title="Suivez les heures"
            description="Saisie quotidienne des heures de travail"
          />
          <StepCard
            number="4"
            title="Analysez"
            description="Consultez les rapports et statistiques"
          />
        </div>
      </div>

      <!-- Information Panels -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        <!-- Leave Info -->
        <div class="bg-gradient-to-br from-primary-50 to-primary-100 rounded-xl p-6 border border-primary-200">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-primary-200 rounded-lg flex items-center justify-center">
              <span class="text-xl">📅</span>
            </div>
            <h3 class="text-lg font-semibold text-primary-900">Gestion des congés</h3>
          </div>
          <div class="space-y-3 text-primary-800">
            <p class="flex items-start gap-2">
              <span class="text-primary-600">✓</span>
              <span>2.5 jours de congés acquis par mois complet travaillé</span>
            </p>
            <p class="flex items-start gap-2">
              <span class="text-primary-600">✓</span>
              <span>Report maximum de 15 jours sur l'année suivante</span>
            </p>
            <p class="flex items-start gap-2">
              <span class="text-primary-600">✓</span>
              <span>Décompte des congés en jours ouvrés (Lundi-Vendredi : 1j, Samedi : 0.5j)</span>
            </p>
            <p class="flex items-start gap-2">
              <span class="text-primary-600">✓</span>
              <span>Validation automatique ou manuelle selon vos règles</span>
            </p>
          </div>
        </div>

        <!-- Hours Tracking Info -->
        <div class="bg-gradient-to-br from-secondary-50 to-secondary-100 rounded-xl p-6 border border-secondary-200">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-secondary-200 rounded-lg flex items-center justify-center">
              <span class="text-xl">🕒</span>
            </div>
            <h3 class="text-lg font-semibold text-secondary-900">Suivi des heures</h3>
          </div>
          <div class="space-y-3 text-secondary-800">
            <p class="flex items-start gap-2">
              <span class="text-secondary-600">✓</span>
              <span>Saisie quotidienne des missions et horaires</span>
            </p>
            <p class="flex items-start gap-2">
              <span class="text-secondary-600">✓</span>
              <span>Tableau de saisie type Excel pour une entrée rapide</span>
            </p>
            <p class="flex items-start gap-2">
              <span class="text-secondary-600">✓</span>
              <span>Calcul automatique des heures par mission</span>
            </p>
            <p class="flex items-start gap-2">
              <span class="text-secondary-600">✓</span>
              <span>Export des données vers CSV/Excel</span>
            </p>
          </div>
        </div>

        <!-- Departments Info -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center">
              <span class="text-xl">🏢</span>
            </div>
            <h3 class="text-lg font-semibold text-green-900">Organisation</h3>
          </div>
          <div class="space-y-3 text-green-800">
            <p class="flex items-start gap-2">
              <span class="text-green-600">✓</span>
              <span>Structurez votre entreprise en départements</span>
            </p>
            <p class="flex items-start gap-2">
              <span class="text-green-600">✓</span>
              <span>Hiérarchie claire avec les managers</span>
            </p>
            <p class="flex items-start gap-2">
              <span class="text-green-600">✓</span>
              <span>Visualisation des équipes par département</span>
            </p>
            <p class="flex items-start gap-2">
              <span class="text-green-600">✓</span>
              <span>Gestion des profils et permissions</span>
            </p>
          </div>
        </div>

        <!-- Support Info -->
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center">
              <span class="text-xl">💡</span>
            </div>
            <h3 class="text-lg font-semibold text-purple-900">Support & Aide</h3>
          </div>
          <div class="space-y-3 text-purple-800">
            <p class="flex items-start gap-2">
              <span class="text-purple-600">✓</span>
              <span>Documentation complète disponible</span>
            </p>
            <p class="flex items-start gap-2">
              <span class="text-purple-600">✓</span>
              <span>Support technique par email</span>
            </p>
            <p class="flex items-start gap-2">
              <span class="text-purple-600">✓</span>
              <span>Formation disponible sur demande</span>
            </p>
            <p class="flex items-start gap-2">
              <span class="text-purple-600">✓</span>
              <span>Mises à jour régulières</span>
            </p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-dark-50 rounded-xl p-6 mt-8">
        <h2 class="text-xl font-semibold text-dark-900 text-center mb-6">
          Accès rapide
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <RouterLink
            to="/agents"
            class="flex flex-col items-center gap-2 p-4 bg-white rounded-lg border border-dark-100 hover:shadow-md transition group"
          >
            <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center group-hover:bg-primary-200 transition">
              <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <span class="text-sm font-medium text-dark-700">Gestion des agents</span>
          </RouterLink>

          <RouterLink
            to="/leaves"
            class="flex flex-col items-center gap-2 p-4 bg-white rounded-lg border border-dark-100 hover:shadow-md transition group"
          >
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center group-hover:bg-green-200 transition">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <span class="text-sm font-medium text-dark-700">Gestion des congés</span>
          </RouterLink>

          <RouterLink
            to="/activity"
            class="flex flex-col items-center gap-2 p-4 bg-white rounded-lg border border-dark-100 hover:shadow-md transition group"
          >
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center group-hover:bg-purple-200 transition">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <span class="text-sm font-medium text-dark-700">Feuille d'activité</span>
          </RouterLink>

          <RouterLink
            to="/departments"
            class="flex flex-col items-center gap-2 p-4 bg-white rounded-lg border border-dark-100 hover:shadow-md transition group"
          >
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center group-hover:bg-orange-200 transition">
              <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <span class="text-sm font-medium text-dark-700">Départements</span>
          </RouterLink>
        </div>
      </div>

      <!-- Footer Info -->
      <div class="text-center text-sm text-dark-400 pt-6 border-t border-dark-100">
        <p>© 2024 HR Platform - Solution complète de gestion des ressources humaines</p>
        <p class="mt-1">Version 1.0 - Tous droits réservés</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppLayout from '@/components/layout/AppLayout.vue'

// Composants simples intégrés dans le template
// Pas besoin de composants externes pour éviter la complexité
</script>

<style scoped>
/* Styles supplémentaires si nécessaire */
</style>
