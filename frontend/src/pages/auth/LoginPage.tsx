import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useNavigate, useLocation, Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { useAuth } from '@/store/AuthContext';
import { extractApiErrors } from '@/utils/api-errors';
import { cn } from '@/utils/cn';

const loginSchema = z.object({
  email:    z.string().email('Please enter a valid email address'),
  password: z.string().min(1, 'Password is required'),
});

type LoginFormValues = z.infer<typeof loginSchema>;

export default function LoginPage() {
  const { login } = useAuth();
  const navigate   = useNavigate();
  const location   = useLocation();
  const from       = (location.state as { from?: Location })?.from?.pathname ?? '/admin';

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<LoginFormValues>({ resolver: zodResolver(loginSchema) });

  const onSubmit = async (data: LoginFormValues) => {
    try {
      await login(data.email, data.password);
      navigate(from, { replace: true });
    } catch (err) {
      const apiErrors = extractApiErrors(err);
      if (apiErrors.email)    setError('email',    { message: apiErrors.email });
      if (apiErrors.password) setError('password', { message: apiErrors.password });
      if (apiErrors._root)    toast.error(apiErrors._root);
    }
  };

  return (
    <div className="bg-white rounded-2xl shadow-soft p-8">
      <h2 className="font-display text-2xl text-charcoal mb-6 text-center">Sign In</h2>

      <form onSubmit={handleSubmit(onSubmit)} noValidate className="space-y-5">
        {/* Email */}
        <div>
          <label htmlFor="email" className="block text-sm font-medium text-charcoal mb-1 font-body">
            Email address
          </label>
          <input
            id="email"
            type="email"
            autoComplete="email"
            className={cn(
              'w-full px-4 py-2.5 rounded-lg border text-sm font-body bg-white transition-colors',
              'focus:outline-none focus:ring-2 focus:ring-bronze focus:border-transparent',
              errors.email
                ? 'border-red-400 bg-red-50'
                : 'border-cream hover:border-taupe'
            )}
            aria-describedby={errors.email ? 'email-error' : undefined}
            aria-invalid={Boolean(errors.email)}
            {...register('email')}
          />
          {errors.email && (
            <p id="email-error" className="mt-1 text-xs text-red-600 font-body" role="alert">
              {errors.email.message}
            </p>
          )}
        </div>

        {/* Password */}
        <div>
          <label htmlFor="password" className="block text-sm font-medium text-charcoal mb-1 font-body">
            Password
          </label>
          <input
            id="password"
            type="password"
            autoComplete="current-password"
            className={cn(
              'w-full px-4 py-2.5 rounded-lg border text-sm font-body bg-white transition-colors',
              'focus:outline-none focus:ring-2 focus:ring-bronze focus:border-transparent',
              errors.password
                ? 'border-red-400 bg-red-50'
                : 'border-cream hover:border-taupe'
            )}
            aria-describedby={errors.password ? 'password-error' : undefined}
            aria-invalid={Boolean(errors.password)}
            {...register('password')}
          />
          {errors.password && (
            <p id="password-error" className="mt-1 text-xs text-red-600 font-body" role="alert">
              {errors.password.message}
            </p>
          )}
        </div>

        {/* Forgot password */}
        <div className="text-right">
          <Link to="/admin/forgot-password" className="font-body text-xs text-taupe hover:text-bronze transition-colors">
            Forgot password?
          </Link>
        </div>

        {/* Submit */}
        <button
          type="submit"
          disabled={isSubmitting}
          className={cn(
            'w-full py-3 rounded-lg text-sm font-medium font-body transition-colors',
            'bg-bronze text-ivory hover:bg-bronze-light',
            'focus:outline-none focus:ring-2 focus:ring-bronze focus:ring-offset-2',
            'disabled:opacity-60 disabled:cursor-not-allowed'
          )}
        >
          {isSubmitting ? (
            <span className="flex items-center justify-center gap-2">
              <span className="w-4 h-4 border-2 border-ivory border-t-transparent rounded-full animate-spin" />
              Signing in…
            </span>
          ) : (
            'Sign in'
          )}
        </button>
      </form>
    </div>
  );
}
