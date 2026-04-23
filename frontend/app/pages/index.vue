<template>
  <div>
    <h1>Dashboard</h1>
    <template v-if="user">
      <p>Welcome, <strong>{{ user.email }}</strong>!</p>
      <p>Account verified: {{ user.isVerified ? 'Yes' : 'No' }}</p>
      <p>Roles: {{ (user.roles as string[]).join(', ') }}</p>
    </template>
    <p v-else>Loading...</p>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })

const { user, fetchUser } = useAuth()

onMounted(async () => {
  await fetchUser()
})
</script>
