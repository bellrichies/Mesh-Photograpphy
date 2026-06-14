import { test, expect } from '@playwright/test';

test.describe('Public site', () => {
  test('homepage loads with hero section', async ({ page }) => {
    await page.goto('/');

    await expect(page).toHaveTitle(/mesh photography/i);
    // Hero carousel or hero image should be visible above the fold
    await expect(page.locator('[data-testid="hero"], .hero, header img').first()).toBeVisible();
  });

  test('navbar is present on homepage', async ({ page }) => {
    await page.goto('/');

    await expect(page.getByRole('navigation')).toBeVisible();
  });

  test('footer is present on homepage', async ({ page }) => {
    await page.goto('/');

    await expect(page.getByRole('contentinfo')).toBeVisible();
  });

  test('portfolio page loads gallery grid', async ({ page }) => {
    await page.goto('/portfolio');

    await expect(page).toHaveURL('/portfolio');
    // Either gallery cards or a loading skeleton should appear
    await expect(page.locator('main')).toBeVisible();
  });

  test('blog index page loads', async ({ page }) => {
    await page.goto('/blog');

    await expect(page).toHaveURL('/blog');
    await expect(page.locator('main')).toBeVisible();
  });

  test('services page loads', async ({ page }) => {
    await page.goto('/services');

    await expect(page.locator('main')).toBeVisible();
  });

  test('contact page has form', async ({ page }) => {
    await page.goto('/contact');

    await expect(page.getByRole('form')).toBeVisible();
    await expect(page.getByLabel(/name/i)).toBeVisible();
    await expect(page.getByLabel(/email/i)).toBeVisible();
    await expect(page.getByLabel(/message/i)).toBeVisible();
  });

  test('booking page has form', async ({ page }) => {
    await page.goto('/booking');

    await expect(page.getByRole('form')).toBeVisible();
  });

  test('404 page shows for unknown route', async ({ page }) => {
    const response = await page.goto('/this-route-does-not-exist-xyz');

    // SPA returns 200 from server but renders a 404 UI component
    await expect(page.getByText(/not found|404/i)).toBeVisible();
  });

  test('contact form submission shows success message', async ({ page }) => {
    await page.goto('/contact');

    await page.getByLabel(/name/i).fill('Test User');
    await page.getByLabel(/email/i).fill('testuser@example.com');
    await page.getByLabel(/message/i).fill('This is an automated E2E test message.');
    await page.getByRole('button', { name: /send|submit/i }).click();

    await expect(page.getByText(/thank you|message sent|received/i)).toBeVisible({ timeout: 10_000 });
  });

  test('mobile menu opens and closes', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto('/');

    const hamburger = page.getByRole('button', { name: /menu|navigation|open/i });
    await hamburger.click();

    // Nav links should be visible after opening
    await expect(page.getByRole('link', { name: /portfolio/i })).toBeVisible();

    await hamburger.click();
  });
});
