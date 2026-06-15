import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import toast from 'react-hot-toast';
import {
  useAdminGallery,
  useCreateGallery,
  useUpdateGallery,
  useAttachGalleryMedia,
  useRemoveGalleryMedia,
  type AdminGalleryPayload,
} from '@/api/admin/galleries';
import PageHeader from '@/components/admin/PageHeader';
import FormField, { fieldClass } from '@/components/admin/FormField';
import SlugInput from '@/components/admin/SlugInput';
import SeoPanel from '@/components/admin/SeoPanel';
import MediaPicker from '@/components/admin/MediaPicker';
import { getErrorMessage } from '@/utils/api-errors';
import { Trash2, Plus, GripVertical } from 'lucide-react';
import type { MediaRecord } from '@/types/models';

const schema = z.object({
  title:           z.string().min(1, 'Title is required'),
  slug:            z.string().min(1, 'Slug is required'),
  description:     z.string().nullable().optional(),
  category:        z.string().nullable().optional(),
  is_published:    z.boolean(),
  is_featured:     z.boolean(),
  sort_order:      z.coerce.number().int().optional(),
  seo_title:       z.string().nullable().optional(),
  seo_description: z.string().nullable().optional(),
});

type FormValues = z.infer<typeof schema>;

export default function GalleryFormPage() {
  const { id }    = useParams<{ id?: string }>();
  const isEdit    = !!id;
  const galleryId = Number(id) || 0;
  const navigate  = useNavigate();

  const { data: gallery }  = useAdminGallery(galleryId);
  const createMutation     = useCreateGallery();
  const updateMutation     = useUpdateGallery(galleryId);
  const attachMedia        = useAttachGalleryMedia(galleryId);
  const removeMedia        = useRemoveGalleryMedia(galleryId);

  const [cover, setCover] = useState<MediaRecord | null>(null);
  const [addingImage, setAddingImage] = useState(false);
  // Caption editor state: mediaId -> caption string
  const [captions, setCaptions] = useState<Record<number, string>>({});

  const {
    register,
    handleSubmit,
    control,
    setValue,
    watch,
    formState: { errors, isSubmitting },
  } = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: {
      title: '', slug: '', is_published: false, is_featured: false, sort_order: 0,
    },
  });

  useEffect(() => {
    if (gallery) {
      setValue('title',           gallery.title);
      setValue('slug',            gallery.slug);
      setValue('description',     gallery.description ?? '');
      setValue('category',        gallery.category   ?? '');
      setValue('is_published',    gallery.status === 'published');
      setValue('is_featured',     gallery.is_featured);
      setValue('sort_order',      gallery.sort_order ?? 0);
      setValue('seo_title',       (gallery as { seo_title?: string | null }).seo_title ?? '');
      setValue('seo_description', (gallery as { seo_description?: string | null }).seo_description ?? '');
      if (gallery.cover) setCover(gallery.cover);
    }
  }, [gallery, setValue]);

  const titleValue = watch('title');
  useEffect(() => {
    if (!isEdit && titleValue) {
      setValue('slug', titleValue.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''));
    }
  }, [titleValue, isEdit, setValue]);

  const onSubmit = async (values: FormValues) => {
    const payload: AdminGalleryPayload = {
      title:           values.title,
      slug:            values.slug,
      description:     values.description ?? null,
      category:        values.category    ?? null,
      is_published:    values.is_published,
      is_featured:     values.is_featured,
      sort_order:      values.sort_order  ?? 0,
      seo_title:       values.seo_title       ?? null,
      seo_description: values.seo_description ?? null,
      cover_image_id:  cover?.id ?? null,
    };

    try {
      if (isEdit) {
        await updateMutation.mutateAsync(payload);
        toast.success('Gallery saved.');
        navigate('/admin/galleries');
      } else {
        const g = await createMutation.mutateAsync(payload);
        toast.success('Gallery created.');
        navigate(`/admin/galleries/${g.id}/edit`);
      }
    } catch (err) {
      toast.error(getErrorMessage(err));
    }
  };

  const handleAddImage = async (media: MediaRecord | null) => {
    if (!media) return;
    setAddingImage(false);
    try {
      await attachMedia.mutateAsync({ media_id: media.id, caption: '' });
      toast.success('Image added.');
    } catch (err) {
      toast.error(getErrorMessage(err));
    }
  };

  const handleRemoveImage = async (mediaId: number) => {
    try {
      await removeMedia.mutateAsync(mediaId);
      toast.success('Image removed.');
    } catch (err) {
      toast.error(getErrorMessage(err));
    }
  };

  const galleryMedia = (gallery as { media?: (MediaRecord & { sort_order: number; caption: string | null })[] } | undefined)?.media ?? [];

  return (
    <div className="max-w-3xl">
      <PageHeader
        title={isEdit ? 'Edit Gallery' : 'New Gallery'}
        backTo="/admin/galleries"
      />

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
        <div className="bg-white rounded-xl border border-cream p-6 space-y-5">
          <h2 className="font-display text-lg text-charcoal">Details</h2>

          <FormField label="Title" htmlFor="title" error={errors.title?.message} required>
            <input id="title" type="text" autoComplete="off" className={fieldClass(!!errors.title)} {...register('title')} />
          </FormField>

          <FormField label="Slug" htmlFor="slug" required>
            <Controller
              name="slug"
              control={control}
              render={({ field }) => (
                <SlugInput
                  value={field.value}
                  onChange={field.onChange}
                  type="gallery"
                  exceptId={isEdit ? galleryId : undefined}
                  error={errors.slug?.message}
                />
              )}
            />
          </FormField>

          <FormField label="Category" htmlFor="category" hint="e.g. wedding, portrait, commercial">
            <input id="category" type="text" autoComplete="off" className={fieldClass(false)} {...register('category')} />
          </FormField>

          <FormField label="Description" htmlFor="description">
            <textarea id="description" rows={3} autoComplete="off" className={fieldClass(false)} {...register('description')} />
          </FormField>

          <FormField label="Sort Order" htmlFor="sort_order" hint="Lower numbers appear first.">
            <input id="sort_order" type="number" min={0} className={fieldClass(false)} {...register('sort_order')} />
          </FormField>

          <div className="flex items-center gap-6">
            <label className="flex items-center gap-2 cursor-pointer font-body text-sm text-charcoal">
              <input id="gallery-is-published" type="checkbox" className="rounded border-cream accent-bronze" {...register('is_published')} />
              Published
            </label>
            <label className="flex items-center gap-2 cursor-pointer font-body text-sm text-charcoal">
              <input id="gallery-is-featured" type="checkbox" className="rounded border-cream accent-bronze" {...register('is_featured')} />
              Featured
            </label>
          </div>
        </div>

        <div className="bg-white rounded-xl border border-cream p-6">
          <h2 className="font-display text-lg text-charcoal mb-4">Cover Image</h2>
          <MediaPicker value={cover} onChange={setCover} label="Select cover image" />
        </div>

        {/* Gallery images — only visible in edit mode (need gallery ID for attachment) */}
        {isEdit && (
          <div className="bg-white rounded-xl border border-cream p-6">
            <div className="flex items-center justify-between mb-4">
              <h2 className="font-display text-lg text-charcoal">
                Gallery Images {galleryMedia.length > 0 && `(${galleryMedia.length})`}
              </h2>
              <button
                type="button"
                onClick={() => setAddingImage(true)}
                disabled={attachMedia.isPending}
                className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-body bg-bronze text-ivory rounded-lg hover:bg-bronze-light disabled:opacity-60"
              >
                <Plus size={13} /> Add Image
              </button>
            </div>

            {galleryMedia.length === 0 ? (
              <div className="border-2 border-dashed border-cream rounded-lg py-10 text-center">
                <p className="font-body text-sm text-taupe">No images yet. Click "Add Image" to start building this gallery.</p>
              </div>
            ) : (
              <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                {galleryMedia.map((m) => (
                  <div
                    key={m.id}
                    className="relative group border border-cream rounded-lg overflow-hidden"
                  >
                    <div className="aspect-square">
                      <img
                        src={m.thumb_url ?? m.url}
                        alt={m.alt_text ?? ''}
                        className="w-full h-full object-cover"
                        width={200}
                        height={200}
                        loading="lazy"
                      />
                    </div>
                    <div className="absolute top-1.5 left-1.5 p-1 cursor-grab opacity-0 group-hover:opacity-100 transition-opacity text-ivory bg-black/40 rounded">
                      <GripVertical size={12} />
                    </div>
                    <button
                      type="button"
                      onClick={() => handleRemoveImage(m.id)}
                      className="absolute top-1.5 right-1.5 p-1 bg-red-600 text-white rounded opacity-0 group-hover:opacity-100 transition-opacity"
                      aria-label="Remove image"
                    >
                      <Trash2 size={12} />
                    </button>
                    {/* Caption */}
                    <div className="p-2 bg-ivory-warm border-t border-cream">
                      <input
                        id={`gallery-caption-${m.id}`}
                        name={`gallery_caption_${m.id}`}
                        type="text"
                        autoComplete="off"
                        placeholder="Caption (optional)"
                        value={captions[m.id] ?? m.caption ?? ''}
                        onChange={(e) => setCaptions((prev) => ({ ...prev, [m.id]: e.target.value }))}
                        className="w-full text-xs font-body text-charcoal bg-transparent border-none outline-none placeholder:text-taupe"
                      />
                    </div>
                  </div>
                ))}
              </div>
            )}

            {/* Inline MediaPicker modal for adding images */}
            {addingImage && (
              <div className="mt-4">
                <MediaPicker
                  value={null}
                  onChange={handleAddImage}
                  label="Select image to add"
                />
                <button
                  type="button"
                  onClick={() => setAddingImage(false)}
                  className="mt-2 text-xs font-body text-taupe hover:text-charcoal"
                >
                  Cancel
                </button>
              </div>
            )}
          </div>
        )}

        {!isEdit && (
          <p className="text-xs font-body text-taupe px-1">
            Save the gallery first, then you can add images from the edit page.
          </p>
        )}

        <div className="bg-white rounded-xl border border-cream p-6">
          <SeoPanel register={register} errors={errors} />
        </div>

        <div className="flex justify-end gap-3">
          <button
            type="button"
            onClick={() => navigate('/admin/galleries')}
            className="px-5 py-2 text-sm font-body text-charcoal border border-cream rounded-lg hover:bg-ivory-warm"
          >
            Cancel
          </button>
          <button
            type="submit"
            disabled={isSubmitting}
            className="px-5 py-2 text-sm font-body bg-bronze text-ivory rounded-lg hover:bg-bronze-light disabled:opacity-60"
          >
            {isSubmitting ? 'Saving…' : isEdit ? 'Save Changes' : 'Create Gallery'}
          </button>
        </div>
      </form>
    </div>
  );
}
