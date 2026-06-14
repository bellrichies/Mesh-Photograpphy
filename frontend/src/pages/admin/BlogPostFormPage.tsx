import { useState, useEffect, useRef, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import toast from 'react-hot-toast';
import { Clock, History, RotateCcw, Save } from 'lucide-react';
import {
  useAdminBlogPost,
  useAdminBlogCategories,
  useAdminBlogTags,
  useCreateBlogPost,
  useUpdateBlogPost,
  useAutosaveBlogPost,
  useAdminBlogRevisions,
  useRestoreBlogRevision,
  type AdminBlogPostPayload,
} from '@/api/admin/blog';
import PageHeader from '@/components/admin/PageHeader';
import FormField, { fieldClass } from '@/components/admin/FormField';
import SlugInput from '@/components/admin/SlugInput';
import SeoPanel from '@/components/admin/SeoPanel';
import MediaPicker from '@/components/admin/MediaPicker';
import ConfirmDialog from '@/components/admin/ConfirmDialog';
import { getErrorMessage } from '@/utils/api-errors';
import type { MediaRecord } from '@/types/models';

const schema = z.object({
  title:           z.string().min(1, 'Title is required'),
  slug:            z.string().min(1, 'Slug is required'),
  excerpt:         z.string().nullable().optional(),
  body:            z.string().optional(),
  category_id:     z.coerce.number().nullable().optional(),
  is_published:    z.boolean(),
  published_at:    z.string().nullable().optional(),
  seo_title:       z.string().nullable().optional(),
  seo_description: z.string().nullable().optional(),
});

type FormValues = z.infer<typeof schema>;

export default function BlogPostFormPage() {
  const { id }   = useParams<{ id?: string }>();
  const isEdit   = !!id;
  const postId   = Number(id) || 0;
  const navigate = useNavigate();

  const { data: post }        = useAdminBlogPost(postId);
  const { data: categories }  = useAdminBlogCategories();
  const { data: allTags }     = useAdminBlogTags();
  const { data: revisions }   = useAdminBlogRevisions(postId);

  const createMutation   = useCreateBlogPost();
  const updateMutation   = useUpdateBlogPost(postId);
  const autosaveMutation = useAutosaveBlogPost(postId);
  const restoreMutation  = useRestoreBlogRevision(postId);

  const [cover, setCover]           = useState<MediaRecord | null>(null);
  const [selectedTags, setSelectedTags] = useState<number[]>([]);
  const [showRevisions, setShowRevisions] = useState(false);
  const [confirmRevisionId, setConfirmRevisionId] = useState<number | null>(null);
  const autosaveTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

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
      title: '', slug: '', is_published: false, body: '', published_at: null,
    },
  });

  useEffect(() => {
    if (post) {
      setValue('title',           post.title);
      setValue('slug',            post.slug);
      setValue('excerpt',         post.excerpt ?? '');
      setValue('body',            post.body ?? '');
      setValue('category_id',     post.category_id ?? null);
      setValue('is_published',    post.status === 'published');
      setValue('published_at',    post.published_at ?? null);
      setValue('seo_title',       post.seo_title ?? '');
      setValue('seo_description', post.seo_description ?? '');
      if (post.cover) setCover(post.cover);
      if (post.tags) setSelectedTags(post.tags.map((t) => t.id));
    }
  }, [post, setValue]);

  const titleValue = watch('title');
  const bodyValue  = watch('body');
  const isPublished = watch('is_published');

  useEffect(() => {
    if (!isEdit && titleValue) {
      setValue('slug', titleValue.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''));
    }
  }, [titleValue, isEdit, setValue]);

  const triggerAutosave = useCallback((body: string) => {
    if (!isEdit) return;
    if (autosaveTimerRef.current) clearTimeout(autosaveTimerRef.current);
    autosaveTimerRef.current = setTimeout(() => {
      autosaveMutation.mutate(body);
    }, 30_000);
  }, [isEdit, autosaveMutation]);

  useEffect(() => {
    if (bodyValue) triggerAutosave(bodyValue);
    return () => { if (autosaveTimerRef.current) clearTimeout(autosaveTimerRef.current); };
  }, [bodyValue, triggerAutosave]);

  const toggleTag = (tagId: number) => {
    setSelectedTags((prev) =>
      prev.includes(tagId) ? prev.filter((t) => t !== tagId) : [...prev, tagId]
    );
  };

  const handleRestore = async (revisionId: number) => {
    try {
      await restoreMutation.mutateAsync(revisionId);
      toast.success('Revision restored. Reloading post…');
      setConfirmRevisionId(null);
      setShowRevisions(false);
    } catch (err) {
      toast.error(getErrorMessage(err));
    }
  };

  const onSubmit = async (values: FormValues) => {
    const payload: AdminBlogPostPayload & { published_at?: string | null } = {
      title:           values.title,
      slug:            values.slug,
      excerpt:         values.excerpt  ?? null,
      body:            values.body     ?? '',
      category_id:     values.category_id ?? null,
      is_published:    values.is_published,
      published_at:    values.published_at ?? null,
      cover_image_id:  cover?.id ?? null,
      tag_ids:         selectedTags,
      seo_title:       values.seo_title       ?? null,
      seo_description: values.seo_description ?? null,
    };

    try {
      if (isEdit) {
        await updateMutation.mutateAsync(payload);
        toast.success('Post saved.');
        navigate('/admin/blog');
      } else {
        const p = await createMutation.mutateAsync(payload);
        toast.success('Post created.');
        navigate(`/admin/blog/${p.id}/edit`);
      }
    } catch (err) {
      toast.error(getErrorMessage(err));
    }
  };

  return (
    <div className="max-w-4xl">
      <PageHeader
        title={isEdit ? 'Edit Post' : 'New Post'}
        backTo="/admin/blog"
        actions={
          <div className="flex items-center gap-3">
            {isEdit && autosaveMutation.isPending && (
              <span className="text-xs text-taupe font-body flex items-center gap-1">
                <Save size={12} /> Autosaving…
              </span>
            )}
            {isEdit && revisions && revisions.length > 0 && (
              <button
                type="button"
                onClick={() => setShowRevisions((v) => !v)}
                className="flex items-center gap-1.5 text-xs font-body text-taupe hover:text-charcoal transition-colors"
              >
                <History size={14} />
                {revisions.length} revision{revisions.length !== 1 ? 's' : ''}
              </button>
            )}
          </div>
        }
      />

      <div className="flex gap-6">
        <form onSubmit={handleSubmit(onSubmit)} className="flex-1 space-y-6 min-w-0">
          <div className="bg-white rounded-xl border border-cream p-6 space-y-5">
            <h2 className="font-display text-lg text-charcoal">Post Details</h2>

            <FormField label="Title" htmlFor="title" error={errors.title?.message} required>
              <input id="title" type="text" className={fieldClass(!!errors.title)} {...register('title')} />
            </FormField>

            <FormField label="Slug" htmlFor="slug" required>
              <Controller
                name="slug"
                control={control}
                render={({ field }) => (
                  <SlugInput
                    value={field.value}
                    onChange={field.onChange}
                    type="blog_post"
                    exceptId={isEdit ? postId : undefined}
                    error={errors.slug?.message}
                  />
                )}
              />
            </FormField>

            <FormField label="Excerpt" htmlFor="excerpt" hint="Brief summary shown in lists (optional).">
              <textarea id="excerpt" rows={2} className={fieldClass(false)} {...register('excerpt')} />
            </FormField>

            <FormField label="Category" htmlFor="category_id">
              <select id="category_id" className={fieldClass(false)} {...register('category_id')}>
                <option value="">— No category —</option>
                {categories?.map((c) => (
                  <option key={c.id} value={c.id}>{c.name}</option>
                ))}
              </select>
            </FormField>

            {/* Publishing controls */}
            <div className="space-y-3">
              <label className="flex items-center gap-2 cursor-pointer font-body text-sm text-charcoal">
                <input type="checkbox" className="rounded border-cream accent-bronze" {...register('is_published')} />
                Published immediately
              </label>

              {!isPublished && (
                <FormField
                  label="Schedule for"
                  htmlFor="published_at"
                  hint="Leave blank to save as draft."
                >
                  <div className="relative">
                    <Clock size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-taupe pointer-events-none" />
                    <input
                      id="published_at"
                      type="datetime-local"
                      className={`${fieldClass(false)} pl-9`}
                      {...register('published_at')}
                    />
                  </div>
                </FormField>
              )}
            </div>
          </div>

          <div className="bg-white rounded-xl border border-cream p-6">
            <h2 className="font-display text-lg text-charcoal mb-4">Body</h2>
            <p className="text-xs text-taupe font-body mb-2">Enter HTML content. Autosaves every 30 seconds.</p>
            <textarea
              className={fieldClass(false)}
              rows={20}
              {...register('body')}
            />
          </div>

          <div className="bg-white rounded-xl border border-cream p-6">
            <h2 className="font-display text-lg text-charcoal mb-4">Cover Image</h2>
            <MediaPicker value={cover} onChange={setCover} label="Select cover image" />
          </div>

          {allTags && allTags.length > 0 && (
            <div className="bg-white rounded-xl border border-cream p-6">
              <h2 className="font-display text-lg text-charcoal mb-4">Tags</h2>
              <div className="flex flex-wrap gap-2">
                {allTags.map((tag) => (
                  <button
                    key={tag.id}
                    type="button"
                    onClick={() => toggleTag(tag.id)}
                    className={`px-3 py-1 rounded-full text-xs font-body transition-colors ${
                      selectedTags.includes(tag.id)
                        ? 'bg-bronze text-ivory'
                        : 'bg-cream text-charcoal hover:bg-bronze/10'
                    }`}
                  >
                    {tag.name}
                  </button>
                ))}
              </div>
            </div>
          )}

          <div className="bg-white rounded-xl border border-cream p-6">
            <SeoPanel register={register} errors={errors} />
          </div>

          <div className="flex justify-end gap-3">
            <button
              type="button"
              onClick={() => navigate('/admin/blog')}
              className="px-5 py-2 text-sm font-body text-charcoal border border-cream rounded-lg hover:bg-ivory-warm"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={isSubmitting}
              className="px-5 py-2 text-sm font-body bg-bronze text-ivory rounded-lg hover:bg-bronze-light disabled:opacity-60"
            >
              {isSubmitting ? 'Saving…' : isEdit ? 'Save Changes' : 'Create Post'}
            </button>
          </div>
        </form>

        {/* Revision history sidebar */}
        {showRevisions && revisions && (
          <aside className="w-72 shrink-0">
            <div className="bg-white rounded-xl border border-cream p-4 sticky top-6">
              <h3 className="font-display text-base text-charcoal mb-4 flex items-center gap-2">
                <History size={15} /> Revision History
              </h3>
              {revisions.length === 0 ? (
                <p className="font-body text-xs text-taupe">No revisions yet.</p>
              ) : (
                <ul className="space-y-2 max-h-[60vh] overflow-y-auto">
                  {revisions.map((rev) => (
                    <li key={rev.id} className="border border-cream rounded-lg p-3 text-xs font-body">
                      <p className="text-charcoal font-medium line-clamp-1">{rev.title}</p>
                      <p className="text-taupe mt-0.5">
                        {new Date(rev.created_at).toLocaleString()}
                      </p>
                      {rev.saved_by && (
                        <p className="text-taupe">by {rev.saved_by}</p>
                      )}
                      <button
                        type="button"
                        onClick={() => setConfirmRevisionId(rev.id)}
                        className="mt-2 flex items-center gap-1 text-bronze hover:text-bronze-dark transition-colors"
                      >
                        <RotateCcw size={11} /> Restore
                      </button>
                    </li>
                  ))}
                </ul>
              )}
            </div>
          </aside>
        )}
      </div>

      <ConfirmDialog
        open={confirmRevisionId !== null}
        title="Restore revision?"
        message="The current post content will be saved as a new revision before restoring. This cannot be undone."
        confirmLabel="Restore"
        variant="warning"
        loading={restoreMutation.isPending}
        onConfirm={() => confirmRevisionId !== null && handleRestore(confirmRevisionId)}
        onCancel={() => setConfirmRevisionId(null)}
      />
    </div>
  );
}
