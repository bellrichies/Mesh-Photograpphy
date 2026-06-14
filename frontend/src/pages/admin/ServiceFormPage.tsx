import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import toast from 'react-hot-toast';
import { useAdminService, useCreateService, useUpdateService, type AdminServicePayload } from '@/api/admin/services';
import PageHeader from '@/components/admin/PageHeader';
import FormField, { fieldClass } from '@/components/admin/FormField';
import SlugInput from '@/components/admin/SlugInput';
import SeoPanel from '@/components/admin/SeoPanel';
import MediaPicker from '@/components/admin/MediaPicker';
import { getErrorMessage } from '@/utils/api-errors';
import type { MediaRecord } from '@/types/models';

const schema = z.object({
  title:             z.string().min(1, 'Title is required'),
  slug:              z.string().min(1, 'Slug is required'),
  short_description: z.string().nullable().optional(),
  description:       z.string().nullable().optional(),
  price_display:     z.string().nullable().optional(),
  is_published:      z.boolean(),
  sort_order:        z.coerce.number().int().optional(),
  seo_title:         z.string().nullable().optional(),
  seo_description:   z.string().nullable().optional(),
});

type FormValues = z.infer<typeof schema>;

export default function ServiceFormPage() {
  const { id }      = useParams<{ id?: string }>();
  const isEdit      = !!id;
  const serviceId   = Number(id) || 0;
  const navigate    = useNavigate();

  const { data: service } = useAdminService(serviceId);
  const createMutation    = useCreateService();
  const updateMutation    = useUpdateService(serviceId);
  const [cover, setCover] = useState<MediaRecord | null>(null);

  const { register, handleSubmit, control, setValue, watch, formState: { errors, isSubmitting } } =
    useForm<FormValues>({ resolver: zodResolver(schema), defaultValues: { title: '', slug: '', is_published: false, sort_order: 0 } });

  useEffect(() => {
    if (service) {
      setValue('title',             service.title);
      setValue('slug',              service.slug);
      setValue('short_description', service.short_description ?? '');
      setValue('description',       service.description       ?? '');
      setValue('price_display',     service.price_display     ?? '');
      setValue('is_published',      service.status === 'published');
      setValue('sort_order',        service.sort_order ?? 0);
      setValue('seo_title',         (service as { seo_title?: string | null }).seo_title ?? '');
      setValue('seo_description',   (service as { seo_description?: string | null }).seo_description ?? '');
      if (service.cover) setCover(service.cover);
    }
  }, [service, setValue]);

  const titleValue = watch('title');
  useEffect(() => {
    if (!isEdit && titleValue) setValue('slug', titleValue.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''));
  }, [titleValue, isEdit, setValue]);

  const onSubmit = async (values: FormValues) => {
    const payload: AdminServicePayload = {
      ...values,
      short_description: values.short_description ?? null,
      description:       values.description       ?? null,
      price_display:     values.price_display      ?? null,
      seo_title:         values.seo_title          ?? null,
      seo_description:   values.seo_description    ?? null,
      cover_image_id:    cover?.id ?? null,
    };
    try {
      if (isEdit) { await updateMutation.mutateAsync(payload); toast.success('Service saved.'); }
      else        { await createMutation.mutateAsync(payload); toast.success('Service created.'); }
      navigate('/admin/services');
    } catch (err) { toast.error(getErrorMessage(err)); }
  };

  return (
    <div className="max-w-3xl">
      <PageHeader title={isEdit ? 'Edit Service' : 'New Service'} backTo="/admin/services" />
      <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
        <div className="bg-white rounded-xl border border-cream p-6 space-y-5">
          <FormField label="Title" htmlFor="title" error={errors.title?.message} required>
            <input id="title" type="text" className={fieldClass(!!errors.title)} {...register('title')} />
          </FormField>
          <FormField label="Slug" htmlFor="slug" required>
            <Controller name="slug" control={control} render={({ field }) => (
              <SlugInput value={field.value} onChange={field.onChange} type="service" exceptId={isEdit ? serviceId : undefined} error={errors.slug?.message} />
            )} />
          </FormField>
          <FormField label="Short Description" htmlFor="short_description" hint="Shown on the services index page.">
            <textarea id="short_description" rows={2} className={fieldClass(false)} {...register('short_description')} />
          </FormField>
          <FormField label="Full Description" htmlFor="description">
            <textarea id="description" rows={6} className={fieldClass(false)} {...register('description')} />
          </FormField>
          <FormField label="Price Display" htmlFor="price_display" hint='e.g. "Starting from $800"'>
            <input id="price_display" type="text" className={fieldClass(false)} {...register('price_display')} />
          </FormField>
          <FormField label="Sort Order" htmlFor="sort_order">
            <input id="sort_order" type="number" min={0} className={fieldClass(false)} {...register('sort_order')} />
          </FormField>
          <label className="flex items-center gap-2 cursor-pointer font-body text-sm text-charcoal">
            <input type="checkbox" className="rounded border-cream accent-bronze" {...register('is_published')} />
            Published
          </label>
        </div>

        <div className="bg-white rounded-xl border border-cream p-6">
          <h2 className="font-display text-lg text-charcoal mb-4">Cover Image</h2>
          <MediaPicker value={cover} onChange={setCover} />
        </div>

        <div className="bg-white rounded-xl border border-cream p-6">
          <SeoPanel register={register} errors={errors} />
        </div>

        <div className="flex justify-end gap-3">
          <button type="button" onClick={() => navigate('/admin/services')} className="px-5 py-2 text-sm font-body text-charcoal border border-cream rounded-lg hover:bg-ivory-warm">Cancel</button>
          <button type="submit" disabled={isSubmitting} className="px-5 py-2 text-sm font-body bg-bronze text-ivory rounded-lg hover:bg-bronze-light disabled:opacity-60">
            {isSubmitting ? 'Saving…' : isEdit ? 'Save Changes' : 'Create Service'}
          </button>
        </div>
      </form>
    </div>
  );
}
