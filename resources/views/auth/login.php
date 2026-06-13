<section>
    <p class="text-xs uppercase tracking-[0.2em] text-zinc-400">Admin Access</p>
    <h1 class="mt-2 font-display text-4xl text-white">Sign In</h1>
    <p class="mt-3 text-sm text-zinc-300">Use your administrator credentials to enter the workspace.</p>

    <form method="post" action="<?= htmlspecialchars(app_href((string) ($adminPath ?? admin_url()) . '/login'), ENT_QUOTES, 'UTF-8') ?>" class="mt-8 space-y-5" novalidate>
        <?= csrf_field() ?>
        <div>
            <label for="login-email" class="mb-1 block text-sm text-zinc-300">Email</label>
            <input id="login-email" type="email" name="email" value="<?= htmlspecialchars((string) old('email', ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-white/20 bg-zinc-900/70 px-3 py-2 text-white outline-none ring-0 placeholder:text-zinc-500 focus:border-ember" placeholder="name@example.com" required autocomplete="email" inputmode="email" aria-describedby="login-email-help">
            <p id="login-email-help" class="mt-2 text-xs leading-6 text-zinc-400">Use the administrator email address assigned to your account.</p>
        </div>
        <div>
            <label for="login-password" class="mb-1 block text-sm text-zinc-300">Password</label>
            <input id="login-password" type="password" name="password" class="w-full rounded-lg border border-white/20 bg-zinc-900/70 px-3 py-2 text-white outline-none ring-0 placeholder:text-zinc-500 focus:border-ember" placeholder="••••••••" required autocomplete="current-password" aria-describedby="login-password-help">
            <p id="login-password-help" class="mt-2 text-xs leading-6 text-zinc-400">Passwords are case-sensitive and session-protected after sign-in.</p>
        </div>
        <button type="submit" class="w-full rounded-lg bg-ember px-4 py-3 font-medium text-zinc-950 transition hover:bg-[#c39a77]">Continue</button>
    </form>
</section>
