import { test, expect } from '@playwright/test'

test.describe('Authentication', () => {
  test('user can register, login and logout', async ({ page }) => {
    const email = `e2e-${Date.now()}@example.com`
    const password = 'password123'

    // --- Register ---
    await page.goto('/register')
    await page.fill('#email', email)
    await page.fill('#password', password)
    await page.click('button[type="submit"]')

    await expect(page.getByText(/registration successful/i)).toBeVisible()

    // --- Login ---
    await page.goto('/login')
    await page.fill('#email', email)
    await page.fill('#password', password)
    await page.click('button[type="submit"]')

    await expect(page).toHaveURL('/')
    await expect(page.getByText('Dashboard')).toBeVisible()
    await expect(page.getByText(email)).toBeVisible()

    // --- Logout ---
    await page.click('a:text("Logout")')

    await expect(page).toHaveURL('/login')
    await expect(page.getByText('Login')).toBeVisible()
  })

  test('login fails with wrong password', async ({ page }) => {
    await page.goto('/login')
    await page.fill('#email', 'nobody@example.com')
    await page.fill('#password', 'wrongpassword')
    await page.click('button[type="submit"]')

    await expect(page.getByText(/invalid credentials|failed/i)).toBeVisible()
    await expect(page).toHaveURL('/login')
  })
})
