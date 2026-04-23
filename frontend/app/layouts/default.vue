<template>
  <div style="font-family: sans-serif; max-width: 520px; margin: 60px auto; padding: 0 20px;">
    <nav style="margin-bottom: 24px; border-bottom: 1px solid #ddd; padding-bottom: 12px;">
      <NuxtLink to="/" style="margin-right: 16px;">Home</NuxtLink>
      <template v-if="isAuthenticated">
        <span style="margin-right: 16px; color: #555;">{{ user?.email }}</span>
        <a href="#" @click.prevent="handleLogout">Logout</a>
      </template>
      <template v-else>
        <NuxtLink to="/login" style="margin-right: 16px;">Login</NuxtLink>
        <NuxtLink to="/register">Register</NuxtLink>
      </template>
    </nav>
    <slot />
  </div>
</template>

<script setup lang="ts">
const { isAuthenticated, user, logout } = useAuth()

function handleLogout() {
  logout()
}
</script>
