<template>
  <div>
    <h1>Login</h1>
    <form @submit.prevent="handleLogin">
      <div style="margin-bottom: 12px;">
        <label for="email">Email</label><br />
        <input
          id="email"
          v-model="email"
          type="email"
          required
          autocomplete="email"
          style="width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box;"
        />
      </div>
      <div style="margin-bottom: 12px;">
        <label for="password">Password</label><br />
        <input
          id="password"
          v-model="password"
          type="password"
          required
          autocomplete="current-password"
          style="width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box;"
        />
      </div>
      <p v-if="error" style="color: red;">{{ error }}</p>
      <button type="submit" :disabled="loading" style="padding: 8px 20px;">
        {{ loading ? 'Logging in…' : 'Login' }}
      </button>
    </form>
    <p style="margin-top: 16px;">
      No account yet? <NuxtLink to="/register">Register</NuxtLink>
    </p>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'guest' })

const { login } = useAuth()
const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true
  try {
    await login(email.value, password.value)
    await navigateTo('/')
  } catch (e: unknown) {
    const err = e as { data?: { message?: string }; statusCode?: number }
    if (err?.statusCode === 401) {
      error.value = 'Invalid credentials or account not verified.'
    } else {
      error.value = err?.data?.message ?? 'Login failed. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>
