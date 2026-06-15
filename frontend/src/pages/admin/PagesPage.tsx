import { useEffect, useState } from 'react';
import { Plus, Pencil, Trash2 } from 'lucide-react';
import toast from 'react-hot-toast';
import { useForm, Controller, useFieldArray } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import {
  useAdminPages,
  useAdminPage,
  useCreatePage,
  useUpdatePage,
  useDeletePage,
  type AdminPage,
  type AdminPagePayload,
} from '@/api/admin/pages';
import PageHeader from '@/components/admin/PageHeader';
import DataTable, { type Column } from '@/components/admin/DataTable';
import StatusBadge from '@/components/admin/StatusBadge';
import ConfirmDialog from '@/components/admin/ConfirmDialog';
import FormField, { fieldClass } from '@/components/admin/FormField';
import SlugInput from '@/components/admin/SlugInput';
import RichTextEditor from '@/components/admin/RichTextEditor';
import MediaPicker from '@/components/admin/MediaPicker';
import { getErrorMessage } from '@/utils/api-errors';
import type { MediaRecord } from '@/types/models';

const sectionSchema = z.object({
  section_key: z.string(),
  section_type: z.string(),
  title: z.string().nullable().optional(),
  content: z.string().nullable().optional(),
  media_id: z.number().nullable().optional(),
  media: z.custom<MediaRecord | null>().nullable().optional(),
  settings: z.record(z.unknown()).optional(),
  sort_order: z.number(),
});

const schema = z.object({
  title: z.string().min(1, 'Title is required'),
  slug: z.string().min(1, 'Slug is required'),
  body: z.string().optional(),
  template: z.string().optional(),
  is_published: z.enum(['true', 'false']),
  seo_title: z.string().optional(),
  seo_description: z.string().optional(),
  canonical_url: z.string().optional(),
  og_title: z.string().optional(),
  og_description: z.string().optional(),
  og_image_id: z.number().nullable().optional(),
  og_image: z.custom<MediaRecord | null>().nullable().optional(),
  seo_robots: z.string().optional(),
  schema_markup: z.string().optional(),
  sections: z.array(sectionSchema).optional(),
});

type SectionValue = z.infer<typeof sectionSchema>;
type FormValues = z.infer<typeof schema>;

const ROBOTS_OPTIONS = [
  'index, follow',
  'noindex, follow',
  'index, nofollow',
  'noindex, nofollow',
] as const;

function sectionLabel(key: string): string {
  const map: Record<string, string> = {
    hero_title: 'Hero Title',
    hero_subtitle: 'Hero Subtitle',
    hero_image: 'Hero Image',
    story_heading: 'Story Heading',
    story_body: 'Brand Story',
    mission_heading: 'Mission Heading',
    mission_body: 'Mission Body',
    vision_heading: 'Vision Heading',
    vision_body: 'Vision Body',
    approach_body: 'Our Approach',
    team_heading: 'Team Heading',
    team_body: 'Team Body',
    clients_heading: 'Clients Heading',
    clients_body: 'Clients Body',
    cta_heading: 'CTA Heading',
    cta_body: 'CTA Subtext',
    stats: 'Stats Bar (JSON)',
    policy_intro: 'Policy Introduction',
    policy_content: 'Policy Content',
    policy_contact: 'Policy Contact Block',
    body: 'Page Content',
  };

  return map[key] ?? key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function SectionEditor({
  section,
  index,
  control,
  register,
  setValue,
}: {
  section: SectionValue;
  index: number;
  control: ReturnType<typeof useForm<FormValues>>['control'];
  register: ReturnType<typeof useForm<FormValues>>['register'];
  setValue: ReturnType<typeof useForm<FormValues>>['setValue'];
}) {
  const label = sectionLabel(section.section_key);
  const fieldId = `section-${index}-content`;

  if (section.section_type === 'rich_text') {
    return (
      <div className="border border-cream rounded-lg p-4 space-y-2 bg-white">
        <p className="font-body text-xs font-medium text-charcoal uppercase tracking-wider">{label}</p>
        <Controller
          name={`sections.${index}.content`}
          control={control}
          render={({ field }) => (
            <RichTextEditor
              value={field.value ?? ''}
              onChange={field.onChange}
              placeholder={`Enter ${label.toLowerCase()}...`}
              minHeight={180}
            />
          )}
        />
      </div>
    );
  }

  if (section.section_type === 'image') {
    return (
      <div className="border border-cream rounded-lg p-4 space-y-3 bg-white">
        <p className="font-body text-xs font-medium text-charcoal uppercase tracking-wider">{label}</p>
        <Controller
          name={`sections.${index}.media`}
          control={control}
          render={({ field }) => (
            <MediaPicker
              value={field.value ?? null}
              onChange={(media) => {
                field.onChange(media);
                setValue(`sections.${index}.media_id`, media?.id ?? null, { shouldDirty: true });
              }}
              label={`Select ${label}`}
            />
          )}
        />
      </div>
    );
  }

  if (section.section_type === 'json') {
    return (
      <div className="border border-cream rounded-lg p-4 space-y-2 bg-white">
        <label htmlFor={fieldId} className="font-body text-xs font-medium text-charcoal uppercase tracking-wider block">
          {label}
        </label>
        <textarea
          id={fieldId}
          rows={5}
          autoComplete="off"
          className={fieldClass(false) + ' font-mono text-xs'}
          placeholder='[{"value":"8+","label":"Years of Experience"}]'
          {...register(`sections.${index}.content`)}
        />
        <p className="font-body text-[10px] text-taupe">JSON array of value/label objects.</p>
      </div>
    );
  }

  return (
    <div className="border border-cream rounded-lg p-4 space-y-2 bg-white">
      <label htmlFor={fieldId} className="font-body text-xs font-medium text-charcoal uppercase tracking-wider block">
        {label}
      </label>
      <input
        id={fieldId}
        type="text"
        autoComplete="off"
        className={fieldClass(false)}
        {...register(`sections.${index}.content`)}
      />
    </div>
  );
}

function PageFormModal({ page, onClose }: { page?: AdminPage; onClose: () => void }) {
  const isEdit = !!page;
  const createMut = useCreatePage();
  const updateMut = useUpdatePage(page?.id ?? 0);
  const { data: fullPage } = useAdminPage(page?.id ?? 0);

  const { register, handleSubmit, control, setValue, watch, formState: { errors, isSubmitting } } =
    useForm<FormValues>({
      resolver: zodResolver(schema),
      defaultValues: {
        title: page?.title ?? '',
        slug: page?.slug ?? '',
        body: '',
        template: page?.template ?? '',
        is_published: (page?.status === 'published' ? 'true' : 'false') as 'true' | 'false',
        seo_title: '',
        seo_description: '',
        canonical_url: '',
        og_title: '',
        og_description: '',
        og_image_id: null,
        og_image: null,
        seo_robots: 'index, follow',
        schema_markup: '',
        sections: [],
      },
    });

  const { fields } = useFieldArray({ control, name: 'sections' });
  const [loaded, setLoaded] = useState(false);

  useEffect(() => {
    if (!isEdit || !fullPage || loaded) {
      return;
    }

    setValue('title', fullPage.title ?? '');
    setValue('slug', fullPage.slug ?? '');
    setValue('template', fullPage.template ?? '');
    setValue('is_published', fullPage.status === 'published' ? 'true' : 'false');
    setValue('seo_title', fullPage.seo?.meta_title ?? '');
    setValue('seo_description', fullPage.seo?.meta_description ?? '');
    setValue('canonical_url', fullPage.seo?.canonical_url ?? '');
    setValue('og_title', fullPage.seo?.og_title ?? '');
    setValue('og_description', fullPage.seo?.og_description ?? '');
    setValue('seo_robots', fullPage.seo?.robots ?? 'index, follow');
    setValue('schema_markup', fullPage.seo?.schema_markup ?? '');
    setValue('og_image_id', fullPage.og_image_id ?? null);
    setValue('og_image', fullPage.og_image ?? null);

    const hasStructuredSections = (fullPage.sections ?? []).some((s) => s.section_key && s.section_key !== 'body');
    if (hasStructuredSections) {
      setValue('sections', (fullPage.sections ?? []).map((section) => ({
        section_key: section.section_key ?? 'body',
        section_type: section.section_type ?? 'text',
        title: section.title ?? null,
        content: section.content ?? '',
        media_id: section.media_id ?? null,
        media: section.media ?? null,
        settings: section.settings ?? {},
        sort_order: section.sort_order ?? 0,
      })));
    } else {
      setValue('body', fullPage.sections?.[0]?.content ?? fullPage.body ?? '');
    }

    setLoaded(true);
  }, [fullPage, isEdit, loaded, setValue]);

  const title = watch('title');
  const slug = watch('slug');

  const handleTitleBlur = () => {
    if (!isEdit && !slug && title) {
      setValue('slug', title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''));
    }
  };

  const onSubmit = async (values: FormValues) => {
    const sections = values.sections?.length
      ? values.sections.map(({ media, ...section }) => ({
          ...section,
          content: section.content || null,
          title: section.title || null,
          media_id: section.media_id ?? media?.id ?? null,
          sort_order: Number(section.sort_order ?? 0),
        }))
      : undefined;

    const payload: AdminPagePayload = {
      title: values.title,
      slug: values.slug,
      body: values.body || null,
      template: values.template || null,
      is_published: values.is_published === 'true',
      seo_title: values.seo_title || null,
      seo_description: values.seo_description || null,
      canonical_url: values.canonical_url || null,
      og_title: values.og_title || null,
      og_description: values.og_description || null,
      og_image_id: values.og_image_id ?? values.og_image?.id ?? null,
      seo_robots: values.seo_robots || 'index, follow',
      schema_markup: values.schema_markup || null,
      sections,
    };

    try {
      if (isEdit) {
        await updateMut.mutateAsync(payload);
        toast.success('Page saved.');
      } else {
        await createMut.mutateAsync(payload);
        toast.success('Page created.');
      }
      onClose();
    } catch (err) {
      toast.error(getErrorMessage(err));
    }
  };

  const hasStructuredSections = fields.length > 0;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-black/40" onClick={onClose} />
      <div className="relative bg-white rounded-2xl shadow-soft w-full max-w-3xl p-6 max-h-[92vh] overflow-y-auto">
        <h2 className="font-display text-xl text-charcoal mb-5">{isEdit ? 'Edit' : 'New'} Page</h2>

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          <FormField label="Title" htmlFor="page-title" error={errors.title?.message} required>
            <input
              id="page-title"
              type="text"
              autoComplete="off"
              className={fieldClass(!!errors.title)}
              {...register('title', { onBlur: handleTitleBlur })}
            />
          </FormField>

          <FormField label="Slug" htmlFor="page-slug" error={errors.slug?.message} required>
            <Controller
              name="slug"
              control={control}
              render={({ field }) => (
                <SlugInput
                  id="page-slug"
                  name="slug"
                  value={field.value}
                  onChange={field.onChange}
                  type="page"
                  exceptId={isEdit ? page?.id : undefined}
                />
              )}
            />
          </FormField>

          {hasStructuredSections ? (
            <div className="space-y-3">
              <p className="font-body text-xs uppercase tracking-widest text-taupe">Page Sections</p>
              {fields.map((field, index) => (
                <SectionEditor
                  key={field.id}
                  section={field}
                  index={index}
                  control={control}
                  register={register}
                  setValue={setValue}
                />
              ))}
            </div>
          ) : (
            <FormField label="Body / Content" htmlFor="page-body" hint="Rich text content displayed on the page.">
              <Controller
                name="body"
                control={control}
                render={({ field }) => (
                  <RichTextEditor
                    value={field.value ?? ''}
                    onChange={field.onChange}
                    placeholder="Write the page content here..."
                    minHeight={250}
                  />
                )}
              />
            </FormField>
          )}

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <FormField label="Template" htmlFor="page-template">
              <input
                id="page-template"
                type="text"
                autoComplete="off"
                className={fieldClass(false)}
                placeholder="default"
                {...register('template')}
              />
            </FormField>

            <FormField label="Status" htmlFor="page-status">
              <select id="page-status" className={fieldClass(false)} {...register('is_published')}>
                <option value="true">Published</option>
                <option value="false">Draft</option>
              </select>
            </FormField>
          </div>

          <fieldset className="border border-cream rounded-lg p-4">
            <legend className="text-xs font-medium text-taupe font-body uppercase tracking-wider px-1">SEO</legend>
            <div className="space-y-3">
              <FormField label="SEO Title" htmlFor="page-seo-title">
                <input id="page-seo-title" type="text" autoComplete="off" className={fieldClass(false)} {...register('seo_title')} />
              </FormField>

              <FormField label="SEO Description" htmlFor="page-seo-description">
                <textarea id="page-seo-description" rows={2} autoComplete="off" className={fieldClass(false)} {...register('seo_description')} />
              </FormField>

              <FormField label="Canonical URL" htmlFor="page-canonical-url">
                <input id="page-canonical-url" type="url" autoComplete="url" className={fieldClass(false)} {...register('canonical_url')} />
              </FormField>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <FormField label="Open Graph Title" htmlFor="page-og-title">
                  <input id="page-og-title" type="text" autoComplete="off" className={fieldClass(false)} {...register('og_title')} />
                </FormField>
                <FormField label="Robots" htmlFor="page-seo-robots">
                  <select id="page-seo-robots" className={fieldClass(false)} {...register('seo_robots')}>
                    {ROBOTS_OPTIONS.map((option) => (
                      <option key={option} value={option}>{option}</option>
                    ))}
                  </select>
                </FormField>
              </div>

              <FormField label="Open Graph Description" htmlFor="page-og-description">
                <textarea id="page-og-description" rows={2} autoComplete="off" className={fieldClass(false)} {...register('og_description')} />
              </FormField>

              <FormField label="Open Graph Image" htmlFor={undefined}>
                <Controller
                  name="og_image"
                  control={control}
                  render={({ field }) => (
                    <MediaPicker
                      value={field.value ?? null}
                      onChange={(media) => {
                        field.onChange(media);
                        setValue('og_image_id', media?.id ?? null, { shouldDirty: true });
                      }}
                      label="Select Open Graph Image"
                    />
                  )}
                />
              </FormField>

              <FormField label="Schema Markup JSON-LD" htmlFor="page-schema-markup">
                <textarea
                  id="page-schema-markup"
                  rows={4}
                  autoComplete="off"
                  className={fieldClass(false) + ' font-mono text-xs'}
                  {...register('schema_markup')}
                />
              </FormField>
            </div>
          </fieldset>

          <div className="flex justify-end gap-3 pt-2">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 text-sm font-body text-charcoal border border-cream rounded-lg hover:bg-ivory-warm"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={isSubmitting}
              className="px-4 py-2 text-sm font-body bg-bronze text-ivory rounded-lg hover:bg-bronze-light disabled:opacity-60"
            >
              {isSubmitting ? 'Saving...' : 'Save'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

export default function PagesPage() {
  const [editing, setEditing] = useState<AdminPage | undefined>();
  const [showForm, setShowForm] = useState(false);
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const { data, isLoading } = useAdminPages();
  const deleteMutation = useDeletePage();

  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await deleteMutation.mutateAsync(deleteId);
      toast.success('Page deleted.');
    } catch (err) {
      toast.error(getErrorMessage(err));
    } finally {
      setDeleteId(null);
    }
  };

  const columns: Column<AdminPage>[] = [
    {
      key: 'title',
      header: 'Page',
      render: (pageRecord) => (
        <div>
          <div className="font-medium text-charcoal">{pageRecord.title}</div>
          <div className="text-xs text-taupe font-mono">/{pageRecord.slug}</div>
        </div>
      ),
    },
    {
      key: 'template',
      header: 'Template',
      render: (pageRecord) => pageRecord.template ?? <span className="text-taupe text-xs">default</span>,
    },
    {
      key: 'status',
      header: 'Status',
      render: (pageRecord) => <StatusBadge status={pageRecord.status} />,
    },
    {
      key: 'actions',
      header: '',
      render: (pageRecord) => (
        <div className="flex items-center gap-2 justify-end">
          <button
            type="button"
            onClick={() => { setEditing(pageRecord); setShowForm(true); }}
            className="p-1.5 text-taupe hover:text-charcoal rounded"
            aria-label={`Edit ${pageRecord.title}`}
          >
            <Pencil size={15} />
          </button>
          <button
            type="button"
            onClick={() => setDeleteId(pageRecord.id)}
            className="p-1.5 text-taupe hover:text-red-600 rounded"
            aria-label={`Delete ${pageRecord.title}`}
          >
            <Trash2 size={15} />
          </button>
        </div>
      ),
      className: 'w-24',
    },
  ];

  return (
    <div>
      <PageHeader
        title="Pages"
        subtitle="Manage CMS pages and structured default-page sections."
        actions={
          <button
            type="button"
            onClick={() => { setEditing(undefined); setShowForm(true); }}
            className="flex items-center gap-2 px-4 py-2 bg-bronze text-ivory text-sm font-body rounded-lg hover:bg-bronze-light"
          >
            <Plus size={16} /> New Page
          </button>
        }
      />

      <DataTable
        columns={columns}
        data={data ?? []}
        keyExtractor={(pageRecord) => pageRecord.id}
        loading={isLoading}
        emptyMessage="No pages found."
      />

      {showForm && (
        <PageFormModal
          page={editing}
          onClose={() => { setShowForm(false); setEditing(undefined); }}
        />
      )}

      <ConfirmDialog
        open={deleteId !== null}
        title="Delete page?"
        message="This page will be permanently deleted."
        confirmLabel="Delete"
        onConfirm={handleDelete}
        onCancel={() => setDeleteId(null)}
        loading={deleteMutation.isPending}
      />
    </div>
  );
}
