import { useState } from 'react';
import { Link, useSearchParams, useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import toast from 'react-hot-toast';
import { authApi } from '@/api/auth';
import { getErrorMessage } from '@/utils/api-errors';
import { cn } from '@/utils/cn';

const schema = z.object({
  password: z.string().min(8, 'Password must be at least 8 characters'),
  confirm:  z.string(),
}).refine((v) => v.password === v.confirm, {
  message: 'Passwords do not match',
  path: ['confirm'],
});

type FormValues = z.infer<typeof schema>;

export default function ResetPasswordPage() {
  const [searchParams]   = useSearchParams();
  const navigate         = useNavigate();
  const token            = searchParams.get('token') ?? '';
  const [done, setDone]  = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<FormValues>({ resolver: zodResolver(schema) });

  const fieldClass = (hasError: boolean) =>
    cn(
      'w-full px-4 py-2.5 rounded-lg border font-body text-sm text-charcoal bg-white',
      'focus:outline-none focus:ring-2 focus:ring-bronze/40 focus:border-bronze',
      hasError ? 'border-red-400' : 'border-cream',
    );

  if (!token) {
    return (
      <div className="w-full max-w-sm text-center">
        <p className="font-body text-sm text-red-500 mb-4">
          Invalid or missing reset token.
        </p>
        <Link to="/admin/forgot-password" className="font-body text-sm text-bronze hover:underline">
          Request a new link
        </Link>
      </div>
    );
  }

  const onSubmit = async (values: FormValues) => {
    try {
      await authApi.resetPassword(token, values.password);
      setDone(true);
      setTimeout(() => navigate('/admin/login', { replace: true }), 3000);
    } catch (err) {
      toast.error(getErrorMessage(err));
    }
  };

  if (done) {
    return (
      <div className="w-full max-w-sm text-center space-y-3">
        <p className="font-body text-sm text-charcoal">
          Your password has been reset. Redirecting to login…
        </p>
        <Link to="/admin/login" className="font-body text-sm text-bronze hover:underline">
          Go to login now
        </Link>
      </div>
    );
  }

  return (
    <div className="w-full max-w-sm">
      <h1 className="font-display text-3xl text-charcoal font-light mb-2 text-center">
        New password
      </h1>
      <p className="font-body text-sm text-taupe text-center mb-8">
        Choose a strong password for your account.
      </p>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
        <div>
          <label htmlFor="password" className="block font-body text-xs text-taupe mb-1">
            New password
          </label>
          <input
            id="password"
            type="password"
            autoComplete="new-password"
            className={fieldClass(!!errors.password)}
            {...register('password')}
          />
          {errors.password && (
            <p className="mt-1 font-body text-xs text-red-500">{errors.password.message}</p>
          )}
        </div>

        <div>
          <label htmlFor="confirm" className="block font-body text-xs text-taupe mb-1">
            Confirm password
          </label>
          <input
            id="confirm"
            type="password"
            autoComplete="new-password"
            className={fieldClass(!!errors.confirm)}
            {...register('confirm')}
          />
          {errors.confirm && (
            <p className="mt-1 font-body text-xs text-red-500">{errors.confirm.message}</p>
          )}
        </div>

        <button
          type="submit"
          disabled={isSubmitting}
          className="w-full py-2.5 bg-bronze text-ivory font-body text-sm rounded-lg hover:bg-bronze-light disabled:opacity-60 transition-colors"
        >
          {isSubmitting ? 'Saving…' : 'Reset password'}
        </button>
      </form>
    </div>
  );
}
