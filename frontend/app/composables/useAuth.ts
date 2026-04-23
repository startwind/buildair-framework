export const useAuth = () => {
  const token = useCookie<string | null>('auth_token', {
    maxAge: 3600,
    secure: false,
    httpOnly: false,
    sameSite: 'lax',
  })
  const user = useState<Record<string, unknown> | null>('auth_user', () => null)
  const config = useRuntimeConfig()

  const apiBase = computed(() => config.public.apiBase as string)

  async function login(email: string, password: string) {
    const response = await $fetch<{ token: string }>(`${apiBase.value}/api/auth/login`, {
      method: 'POST',
      body: { email, password },
    })
    token.value = response.token
    await fetchUser()
  }

  async function register(email: string, password: string) {
    return $fetch<{ message: string }>(`${apiBase.value}/api/auth/register`, {
      method: 'POST',
      body: { email, password },
    })
  }

  function logout() {
    token.value = null
    user.value = null
    return navigateTo('/login')
  }

  async function fetchUser() {
    if (!token.value) return
    try {
      user.value = await $fetch<Record<string, unknown>>(`${apiBase.value}/api/auth/me`, {
        headers: { Authorization: `Bearer ${token.value}` },
      })
    } catch {
      token.value = null
      user.value = null
    }
  }

  const isAuthenticated = computed(() => !!token.value)

  return { login, register, logout, fetchUser, isAuthenticated, user, token }
}
