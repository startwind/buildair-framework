<template>
  <div>
    <h1>Email Verification</h1>
    <p v-if="loading">Verifying your email address…</p>
    <p v-else-if="success" style="color: green;">{{ message }}</p>
    <p v-else style="color: red;">{{ message }}</p>
    <NuxtLink v-if="!loading" to="/login">Go to Login</NuxtLink>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const config = useRuntimeConfig()
const loading = ref(true)
const success = ref(false)
const message = ref('')

onMounted(async () => {
  try {
    const response = await $fetch<{ message: string }>(
      `${config.public.apiBase}/api/auth/verify/${route.params.token}`
    )
    success.value = true
    message.value = response.message ?? 'Email verified successfully!'
  } catch (e: unknown) {
    const err = e as { data?: { error?: string } }
    success.value = false
    message.value = err?.data?.error ?? 'Verification failed. The link may have expired.'
  } finally {
    loading.value = false
  }
})
</script>
