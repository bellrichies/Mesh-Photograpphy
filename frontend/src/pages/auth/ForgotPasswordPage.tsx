import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { authApi } from '@/api/auth';
import { cn } from '@/utils/cn';

const schema = z.object({
  email: z.string().email('Enter a valid email address'),
});

type FormValues = z.infer<typeof schema>;

export default function ForgotPasswordPage() {
  const [sent, setSent] = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<FormValues>({ resolver: zodResolver(schema) });

  const onSubmit = async (values: FormValues) => {
    try {
      await authApi.requestPasswordReset(values.email);
      setSent(true);
    } catch {
      // Always show the same success message to avoid user enumeration
      setSent(true);
    }
  };

  const fieldClass = cn(
    'w-full px-4 py-2.5 rounded-lg border font-body text-sm text-charcoal bg-white',
    'border-cream focus:outline-none focus:ring-2 focus:ring-bronze/40 focus:border-bronze',
  );

  return (
    <div className="w-full max-w-sm">
      <h1 className="font-display text-3xl text-charcoal font-light mb-2 text-center">
        Reset password
      </h1>
      <p className="font-body text-sm text-taupe text-center mb-8">
        Enter your email and we'll send a reset link.
      </p>

      {sent ? (
        <div className="bg-ivory border border-cream rounded-xl p-6 text-center space-y-3">
          <p className="font-body text-sm text-charcoal">
            If that email address is registered, you'll receive a reset link shortly.
          </p>
          <Link
            to="/admin/login"
            className="inline-block font-body text-sm text-bronze hover:underline"
          >
            Back to login
          </Link>
        </div>
      ) : (
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          <div>
            <label htmlFor="email" className="block font-body text-xs text-taupe mb-1">
              Email address
            </label>
            <input
              id="email"
              type="email"
              autoComplete="email"
              className={fieldClass}
              {...register('email')}
            />
            {errors.email && (
              <p className="mt-1 font-body text-xs text-red-500">{errors.email.message}</p>
            )}
          </div>

          <button
            type="submit"
            disabled={isSubmitting}
            className="w-full py-2.5 bg-bronze text-ivory font-body text-sm rounded-lg hover:bg-bronze-light disabled:opacity-60 transition-colors"
          >
            {isSubmitting ? 'Sending…' : 'Send reset link'}
          </button>

          <p className="text-center font-body text-xs text-taupe">
            <Link to="/admin/login" className="text-bronze hover:underline">
              Back to login
            </Link>
          </p>
        </form>
      )}
    </div>
  );
}
