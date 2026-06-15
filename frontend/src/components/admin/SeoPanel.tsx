import { useState } from 'react';
import { ChevronDown, ChevronUp } from 'lucide-react';
import FormField, { fieldClass } from './FormField';
import type { UseFormRegister, FieldErrors, FieldValues, Path } from 'react-hook-form';

interface SeoFields {
  seo_title?: string | null;
  seo_description?: string | null;
}

interface Props<T extends FieldValues> {
  register: UseFormRegister<T>;
  errors: FieldErrors<SeoFields>;
}

export default function SeoPanel<T extends FieldValues & SeoFields>({ register, errors }: Props<T>) {
  const [open, setOpen] = useState(false);

  return (
    <div className="border border-cream rounded-xl overflow-hidden">
      <button
        type="button"
        onClick={() => setOpen(!open)}
        className="w-full flex items-center justify-between px-4 py-3 bg-ivory-warm text-sm font-medium text-charcoal font-body hover:bg-cream transition-colors"
      >
        SEO Settings
        {open ? <ChevronUp size={16} /> : <ChevronDown size={16} />}
      </button>
      {open && (
        <div className="p-4 space-y-4">
          <FormField
            label="Meta Title"
            htmlFor="seo_title"
            error={errors.seo_title?.message as string | undefined}
            hint="Leave blank to use the page title."
          >
            <input
              id="seo_title"
              type="text"
              autoComplete="off"
              className={fieldClass(!!errors.seo_title)}
              {...register('seo_title' as unknown as Path<T>)}
            />
          </FormField>
          <FormField
            label="Meta Description"
            htmlFor="seo_description"
            error={errors.seo_description?.message as string | undefined}
            hint="Recommended: 120–160 characters."
          >
            <textarea
              id="seo_description"
              rows={3}
              autoComplete="off"
              className={fieldClass(!!errors.seo_description)}
              {...register('seo_description' as unknown as Path<T>)}
            />
          </FormField>
        </div>
      )}
    </div>
  );
}
