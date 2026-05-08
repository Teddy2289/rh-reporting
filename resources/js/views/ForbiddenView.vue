<template>
  <div class="min-h-screen flex flex-col items-center justify-center text-center p-6">
    <p class="text-6xl mb-4">🔒</p>
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Accès refusé</h1>
    <p class="text-gray-500 mb-6">Vous n'avez pas les permissions pour accéder à cette page.</p>
    <RouterLink
      :to="{ name: 'dashboard' }"
      class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-blue-700 transition"
    >
      Retour au tableau de bord
    </RouterLink>
  </div>
</template>
