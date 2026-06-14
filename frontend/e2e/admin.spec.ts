import { test, expect, type Page } from '@playwright/test';

const ADMIN_EMAIL    = process.env.E2E_ADMIN_EMAIL    ?? 'admin@meshphoto.com';
const ADMIN_PASSWORD = process.env.E2E_ADMIN_PASSWORD ?? 'password';

async function loginAsAdmin(page: Page): Promise<void> {
  await page.goto('/admin/login');
  await page.getByLabel(/email/i).fill(ADMIN_EMAIL);
  await page.getByLabel(/password/i).fill(ADMIN_PASSWORD);
  await page.getByRole('button', { name: /sign in/i }).click();
  await page.waitForURL(/\/admin/);
}

test.describe('Admin dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('dashboard shows metric cards', async ({ page }) => {
    await page.goto('/admin/dashboard');

    // At least one stat card should be visible
    await expect(page.locator('[data-testid="metric-card"], .metric-card, .stat-card').first()).toBeVisible();
  });

  test('sidebar navigation is visible', async ({ page }) => {
    await expect(page.getByRole('navigation')).toBeVisible();
    await expect(page.getByRole('link', { name: /galleries/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /blog/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /bookings/i })).toBeVisible();
  });

  test('galleries list page loads', async ({ page }) => {
    await page.goto('/admin/galleries');

    await expect(page.getByRole('heading', { name: /galleries/i })).toBeVisible();
  });

  test('blog posts list page loads', async ({ page }) => {
    await page.goto('/admin/blog');

    await expect(page.getByRole('heading', { name: /blog/i })).toBeVisible();
  });

  test('inquiries list page loads', async ({ page }) => {
    await page.goto('/admin/inquiries');

    await expect(page.getByRole('heading', { name: /inquiries/i })).toBeVisible();
  });

  test('bookings list page loads', async ({ page }) => {
    await page.goto('/admin/bookings');

    await expect(page.getByRole('heading', { name: /bookings/i })).toBeVisible();
  });

  test('settings page loads', async ({ page }) => {
    await page.goto('/admin/settings');

    await expect(page.getByRole('heading', { name: /settings/i })).toBeVisible();
  });

  test('media library page loads', async ({ page }) => {
    await page.goto('/admin/media');

    await expect(page.getByRole('heading', { name: /media/i })).toBeVisible();
  });

  test('new gallery form is accessible', async ({ page }) => {
    await page.goto('/admin/galleries/new');

    await expect(page.getByLabel(/title/i)).toBeVisible();
    await expect(page.getByLabel(/slug/i)).toBeVisible();
  });

  test('new blog post form is accessible', async ({ page }) => {
    await page.goto('/admin/blog/new');

    await expect(page.getByLabel(/title/i)).toBeVisible();
    await expect(page.getByLabel(/slug/i)).toBeVisible();
  });

  test('destructive action requires confirmation', async ({ page }) => {
    await page.goto('/admin/galleries');

    const deleteButton = page.getByRole('button', { name: /delete/i }).first();

    if (await deleteButton.isVisible()) {
      await deleteButton.click();
      // Confirm dialog should appear
      await expect(page.getByRole('dialog')).toBeVisible();
      // Cancel the deletion
      await page.getByRole('button', { name: /cancel/i }).click();
    } else {
      // No galleries to delete — test is a no-op
      test.skip();
    }
  });
});
