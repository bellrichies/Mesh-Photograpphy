import { useEffect, useState } from 'react';
import { Plus, Trash2 } from 'lucide-react';
import toast from 'react-hot-toast';
import { useAdminSettings, useUpdateSettings, type AdminSettingsData } from '@/api/admin/settings';
import {
  useAdminBlogCategories,
  useAdminBlogTags,
  useCreateBlogCategory,
  useDeleteBlogCategory,
  useCreateBlogTag,
  useDeleteBlogTag,
} from '@/api/admin/blog';
import PageHeader from '@/components/admin/PageHeader';
import FormField, { fieldClass } from '@/components/admin/FormField';
import { getErrorMessage } from '@/utils/api-errors';
import { cn } from '@/utils/cn';

type Tab = 'site' | 'contact' | 'social' | 'seo' | 'categories' | 'tags';

const TABS: { id: Tab; label: string }[] = [
  { id: 'site',       label: 'Site' },
  { id: 'contact',    label: 'Contact' },
  { id: 'social',     label: 'Social Media' },
  { id: 'seo',        label: 'SEO' },
  { id: 'categories', label: 'Categories' },
  { id: 'tags',       label: 'Tags' },
];

// ─── Category & Tag manager ────────────────────────────────────────────────────

function CategoryManager() {
  const { data: categories, isLoading } = useAdminBlogCategories();
  const createMut = useCreateBlogCategory();
  const deleteMut = useDeleteBlogCategory();
  const [name, setName] = useState('');

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    const trimmed = name.trim();
    if (!trimmed) return;
    const slug = trimmed.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    try {
      await createMut.mutateAsync({ name: trimmed, slug });
      setName('');
      toast.success('Category created.');
    } catch (err) { toast.error(getErrorMessage(err)); }
  };

  if (isLoading) return <div className="animate-pulse h-20 bg-cream rounded-lg" />;

  return (
    <div className="space-y-4">
      <form onSubmit={handleCreate} className="flex gap-3">
        <input
          type="text"
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="New category name"
          className={fieldClass(false) + ' flex-1'}
        />
        <button
          type="submit"
          disabled={createMut.isPending || !name.trim()}
          className="flex items-center gap-1.5 px-4 py-2 text-sm font-body bg-bronze text-ivory rounded-lg hover:bg-bronze-light disabled:opacity-60"
        >
          <Plus size={14} /> Add
        </button>
      </form>

      {categories && categories.length > 0 ? (
        <ul className="space-y-2">
          {categories.map((c) => (
            <li key={c.id} className="flex items-center justify-between px-4 py-3 bg-ivory-warm rounded-lg border border-cream">
              <div>
                <span className="font-body text-sm text-charcoal">{c.name}</span>
                <span className="ml-2 text-xs text-taupe font-mono">/{c.slug}</span>
                {c.post_count !== undefined && (
                  <span className="ml-2 text-xs text-taupe">{c.post_count} post{c.post_count !== 1 ? 's' : ''}</span>
                )}
              </div>
              <button
                type="button"
                onClick={() => {
                  deleteMut.mutate(c.id, {
                    onSuccess: () => toast.success('Category deleted.'),
                    onError: (err) => toast.error(getErrorMessage(err)),
                  });
                }}
                className="p-1.5 text-taupe hover:text-red-600 rounded transition-colors"
                aria-label={`Delete ${c.name}`}
              >
                <Trash2 size={14} />
              </button>
            </li>
          ))}
        </ul>
      ) : (
        <p className="text-sm font-body text-taupe text-center py-6">No categories yet.</p>
      )}
    </div>
  );
}

function TagManager() {
  const { data: tags, isLoading } = useAdminBlogTags();
  const createMut = useCreateBlogTag();
  const deleteMut = useDeleteBlogTag();
  const [name, setName] = useState('');

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    const trimmed = name.trim();
    if (!trimmed) return;
    const slug = trimmed.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    try {
      await createMut.mutateAsync({ name: trimmed, slug });
      setName('');
      toast.success('Tag created.');
    } catch (err) { toast.error(getErrorMessage(err)); }
  };

  if (isLoading) return <div className="animate-pulse h-20 bg-cream rounded-lg" />;

  return (
    <div className="space-y-4">
      <form onSubmit={handleCreate} className="flex gap-3">
        <input
          type="text"
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="New tag name"
          className={fieldClass(false) + ' flex-1'}
        />
        <button
          type="submit"
          disabled={createMut.isPending || !name.trim()}
          className="flex items-center gap-1.5 px-4 py-2 text-sm font-body bg-bronze text-ivory rounded-lg hover:bg-bronze-light disabled:opacity-60"
        >
          <Plus size={14} /> Add
        </button>
      </form>

      {tags && tags.length > 0 ? (
        <div className="flex flex-wrap gap-2">
          {tags.map((t) => (
            <div
              key={t.id}
              className="flex items-center gap-1.5 pl-3 pr-1.5 py-1.5 bg-ivory-warm rounded-full border border-cream"
            >
              <span className="font-body text-sm text-charcoal">{t.name}</span>
              {t.post_count !== undefined && (
                <span className="text-xs text-taupe">({t.post_count})</span>
              )}
              <button
                type="button"
                onClick={() => {
                  deleteMut.mutate(t.id, {
                    onSuccess: () => toast.success('Tag deleted.'),
                    onError: (err) => toast.error(getErrorMessage(err)),
                  });
                }}
                className="p-0.5 text-taupe hover:text-red-600 rounded-full transition-colors"
                aria-label={`Delete ${t.name}`}
              >
                <Trash2 size={12} />
              </button>
            </div>
          ))}
        </div>
      ) : (
        <p className="text-sm font-body text-taupe text-center py-6">No tags yet.</p>
      )}
    </div>
  );
}

// ─── Settings field definitions ────────────────────────────────────────────────

type FieldType = 'textarea' | 'url' | 'email' | 'text';

const SITE_FIELDS: { key: string; label: string; type?: FieldType }[] = [
  { key: 'name',        label: 'Site Name' },
  { key: 'tagline',     label: 'Tagline' },
  { key: 'logo_url',    label: 'Logo URL',    type: 'url' },
  { key: 'favicon_url', label: 'Favicon URL', type: 'url' },
];

const CONTACT_FIELDS: { key: string; label: string; type?: FieldType }[] = [
  { key: 'phone',         label: 'Phone' },
  { key: 'email',         label: 'Email',                type: 'email' },
  { key: 'address',       label: 'Address',              type: 'textarea' },
  { key: 'map_embed_url', label: 'Google Maps Embed URL', type: 'url' },
];

const SOCIAL_FIELDS: { key: string; label: string }[] = [
  { key: 'instagram', label: 'Instagram URL' },
  { key: 'facebook',  label: 'Facebook URL' },
  { key: 'twitter',   label: 'Twitter / X URL' },
  { key: 'youtube',   label: 'YouTube URL' },
  { key: 'pinterest', label: 'Pinterest URL' },
];

const SEO_FIELDS: { key: string; label: string; type?: FieldType }[] = [
  { key: 'default_title',       label: 'Default Title' },
  { key: 'default_description', label: 'Default Description', type: 'textarea' },
];

// ─── Main component ────────────────────────────────────────────────────────────

export default function SettingsPage() {
  const { data: settings, isLoading } = useAdminSettings();
  const updateMut = useUpdateSettings();

  const [activeTab, setActiveTab] = useState<Tab>('site');
  const [form, setForm] = useState<AdminSettingsData>({});
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (settings) setForm(settings);
  }, [settings]);

  const handleChange = (group: string, key: string, value: string) => {
    setForm((prev) => ({
      ...prev,
      [group]: { ...(prev[group] ?? {}), [key]: value },
    }));
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      await updateMut.mutateAsync(form);
      toast.success('Settings saved.');
    } catch (err) {
      toast.error(getErrorMessage(err));
    } finally {
      setSaving(false);
    }
  };

  const renderField = (group: string, key: string, label: string, type: FieldType = 'text') => {
    const value = form[group]?.[key] ?? '';
    const inputId = `${group}_${key}`;
    return (
      <FormField key={key} label={label} htmlFor={inputId}>
        {type === 'textarea' ? (
          <textarea
            id={inputId}
            rows={3}
            className={fieldClass(false)}
            value={value}
            onChange={(e) => handleChange(group, key, e.target.value)}
          />
        ) : (
          <input
            id={inputId}
            type={type}
            className={fieldClass(false)}
            value={value}
            onChange={(e) => handleChange(group, key, e.target.value)}
          />
        )}
      </FormField>
    );
  };

  if (isLoading) {
    return (
      <div className="space-y-4 max-w-2xl">
        {[1, 2, 3].map((i) => (
          <div key={i} className="animate-pulse h-40 bg-cream rounded-xl" />
        ))}
      </div>
    );
  }

  const isSettingsTab = ['site', 'contact', 'social', 'seo'].includes(activeTab);

  return (
    <div className="max-w-2xl">
      <PageHeader
        title="Settings"
        actions={
          isSettingsTab ? (
            <button
              onClick={handleSave}
              disabled={saving}
              className="px-4 py-2 bg-bronze text-ivory text-sm font-body rounded-lg hover:bg-bronze-light disabled:opacity-60"
            >
              {saving ? 'Saving…' : 'Save Settings'}
            </button>
          ) : undefined
        }
      />

      {/* Tab bar */}
      <div className="flex gap-1 border-b border-cream mb-6 overflow-x-auto">
        {TABS.map((tab) => (
          <button
            key={tab.id}
            onClick={() => setActiveTab(tab.id)}
            className={cn(
              'px-4 py-2.5 text-sm font-body whitespace-nowrap border-b-2 -mb-px transition-colors',
              activeTab === tab.id
                ? 'border-bronze text-bronze font-medium'
                : 'border-transparent text-taupe hover:text-charcoal'
            )}
          >
            {tab.label}
          </button>
        ))}
      </div>

      {/* Tab panels */}
      <div className="bg-white rounded-xl border border-cream p-6">
        {activeTab === 'site' && (
          <div className="space-y-4">
            <h2 className="font-display text-base text-charcoal mb-4">Site Settings</h2>
            {SITE_FIELDS.map(({ key, label, type }) => renderField('site', key, label, type))}
          </div>
        )}

        {activeTab === 'contact' && (
          <div className="space-y-4">
            <h2 className="font-display text-base text-charcoal mb-1">Contact Information</h2>
            <p className="text-xs text-taupe font-body mb-4">
              These values appear in the footer and contact page. The Google Maps Embed URL is shown as a map on the contact page — paste the embed src URL from Google Maps &gt; Share &gt; Embed a map.
            </p>
            {CONTACT_FIELDS.map(({ key, label, type }) => renderField('contact', key, label, type))}
          </div>
        )}

        {activeTab === 'social' && (
          <div className="space-y-4">
            <h2 className="font-display text-base text-charcoal mb-4">Social Media</h2>
            {SOCIAL_FIELDS.map(({ key, label }) => renderField('social', key, label, 'url'))}
          </div>
        )}

        {activeTab === 'seo' && (
          <div className="space-y-4">
            <h2 className="font-display text-base text-charcoal mb-1">Default SEO</h2>
            <p className="text-xs text-taupe font-body mb-4">
              Fallback values used when a page does not have its own SEO meta.
            </p>
            {SEO_FIELDS.map(({ key, label, type }) => renderField('seo', key, label, type))}
          </div>
        )}

        {activeTab === 'categories' && (
          <div>
            <h2 className="font-display text-base text-charcoal mb-1">Blog Categories</h2>
            <p className="text-xs text-taupe font-body mb-5">
              Manage the categories available when writing blog posts.
            </p>
            <CategoryManager />
          </div>
        )}

        {activeTab === 'tags' && (
          <div>
            <h2 className="font-display text-base text-charcoal mb-1">Blog Tags</h2>
            <p className="text-xs text-taupe font-body mb-5">
              Manage the tags available when writing blog posts.
            </p>
            <TagManager />
          </div>
        )}
      </div>
    </div>
  );
}
