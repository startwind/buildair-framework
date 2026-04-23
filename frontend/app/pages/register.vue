<template>
  <div>
    <h1>Register</h1>
    <template v-if="!success">
      <form @submit.prevent="handleRegister">
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
          <label for="password">Password <small>(min. 8 characters)</small></label><br />
          <input
            id="password"
            v-model="password"
            type="password"
            required
            minlength="8"
            autocomplete="new-password"
            style="width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box;"
          />
        </div>
        <p v-if="error" style="color: red;">{{ error }}</p>
        <button type="submit" :disabled="loading" style="padding: 8px 20px;">
          {{ loading ? 'Registering…' : 'Register' }}
        </button>
      </form>
    </template>
    <template v-else>
      <p style="color: green;">{{ successMessage }}</p>
    </template>
    <p style="margin-top: 16px;">
      Already have an account? <NuxtLink to="/login">Login</NuxtLink>
    </p>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'guest' })

const { register } = useAuth()
const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)
const success = ref(false)
const successMessage = ref('')

async function handleRegister() {
  error.value = ''
  loading.value = true
  try {
    const response = await register(email.value, password.value)
    success.value = true
    successMessage.value = response.message ?? 'Registration successful!'
  } catch (e: unknown) {
    const err = e as { data?: { error?: string } }
    error.value = err?.data?.error ?? 'Registration failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>
