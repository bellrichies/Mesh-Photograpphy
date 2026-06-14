import { test, expect } from '@playwright/test';

const ADMIN_EMAIL    = process.env.E2E_ADMIN_EMAIL    ?? 'admin@meshphoto.com';
const ADMIN_PASSWORD = process.env.E2E_ADMIN_PASSWORD ?? 'password';

test.describe('Authentication', () => {
  test('login page renders', async ({ page }) => {
    await page.goto('/admin/login');

    await expect(page.getByRole('heading', { name: /sign in/i })).toBeVisible();
    await expect(page.getByLabel(/email/i)).toBeVisible();
    await expect(page.getByLabel(/password/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /sign in/i })).toBeVisible();
  });

  test('shows validation errors for empty submission', async ({ page }) => {
    await page.goto('/admin/login');
    await page.getByRole('button', { name: /sign in/i }).click();

    await expect(page.getByText(/email is required|invalid email/i)).toBeVisible();
  });

  test('shows error for wrong credentials', async ({ page }) => {
    await page.goto('/admin/login');

    await page.getByLabel(/email/i).fill('wrong@example.com');
    await page.getByLabel(/password/i).fill('wrongpassword');
    await page.getByRole('button', { name: /sign in/i }).click();

    await expect(page.getByText(/invalid credentials|unauthorized/i)).toBeVisible();
  });

  test('redirects unauthenticated user from admin to login', async ({ page }) => {
    await page.goto('/admin');

    await expect(page).toHaveURL(/\/admin\/login/);
  });

  test('successful login redirects to dashboard', async ({ page }) => {
    await page.goto('/admin/login');

    await page.getByLabel(/email/i).fill(ADMIN_EMAIL);
    await page.getByLabel(/password/i).fill(ADMIN_PASSWORD);
    await page.getByRole('button', { name: /sign in/i }).click();

    await expect(page).toHaveURL(/\/admin(?:\/dashboard)?/);
    await expect(page.getByText(/dashboard/i)).toBeVisible();
  });

  test('logout clears session and redirects to login', async ({ page }) => {
    // Log in first
    await page.goto('/admin/login');
    await page.getByLabel(/email/i).fill(ADMIN_EMAIL);
    await page.getByLabel(/password/i).fill(ADMIN_PASSWORD);
    await page.getByRole('button', { name: /sign in/i }).click();
    await page.waitForURL(/\/admin/);

    // Trigger logout
    await page.getByRole('button', { name: /logout|sign out/i }).click();

    await expect(page).toHaveURL(/\/admin\/login/);
  });
});
