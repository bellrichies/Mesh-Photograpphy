import { useEffect, useState } from 'react';
import toast from 'react-hot-toast';
import { useAdminSettings, useUpdateSettings, type AdminSettingsData } from '@/api/admin/settings';
import PageHeader from '@/components/admin/PageHeader';
import FormField, { fieldClass } from '@/components/admin/FormField';
import { getErrorMessage } from '@/utils/api-errors';

type FieldDef = { key: string; label: string; type?: 'textarea' | 'url' | 'email' | 'text' };

const SECTIONS: Array<{ group: string; label: string; fields: FieldDef[] }> = [
  {
    group: 'site',
    label: 'Site',
    fields: [
      { key: 'name',        label: 'Site Name' },
      { key: 'tagline',     label: 'Tagline' },
      { key: 'logo_url',    label: 'Logo URL',    type: 'url' },
      { key: 'favicon_url', label: 'Favicon URL', type: 'url' },
    ],
  },
  {
    group: 'contact',
    label: 'Contact',
    fields: [
      { key: 'phone',   label: 'Phone' },
      { key: 'email',   label: 'Email',   type: 'email' },
      { key: 'address', label: 'Address', type: 'textarea' },
    ],
  },
  {
    group: 'social',
    label: 'Social Media',
    fields: [
      { key: 'instagram', label: 'Instagram URL', type: 'url' },
      { key: 'facebook',  label: 'Facebook URL',  type: 'url' },
      { key: 'twitter',   label: 'Twitter URL',   type: 'url' },
      { key: 'youtube',   label: 'YouTube URL',   type: 'url' },
      { key: 'pinterest', label: 'Pinterest URL', type: 'url' },
    ],
  },
  {
    group: 'seo',
    label: 'Default SEO',
    fields: [
      { key: 'default_title',       label: 'Default Title' },
      { key: 'default_description', label: 'Default Description', type: 'textarea' },
    ],
  },
];

export default function SettingsPage() {
  const { data: settings, isLoading } = useAdminSettings();
  const updateMut = useUpdateSettings();

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

  if (isLoading) {
    return (
      <div className="space-y-4">
        {[1, 2, 3].map((i) => (
          <div key={i} className="animate-pulse h-40 bg-cream rounded-xl" />
        ))}
      </div>
    );
  }

  return (
    <div className="max-w-2xl">
      <PageHeader
        title="Settings"
        actions={
          <button
            onClick={handleSave}
            disabled={saving}
            className="px-4 py-2 bg-bronze text-ivory text-sm font-body rounded-lg hover:bg-bronze-light disabled:opacity-60"
          >
            {saving ? 'Saving…' : 'Save Settings'}
          </button>
        }
      />

      <div className="space-y-6">
        {SECTIONS.map(({ group, label, fields }) => (
          <section key={group} className="bg-white rounded-xl border border-cream p-6">
            <h2 className="font-display text-base text-charcoal mb-4">{label}</h2>
            <div className="space-y-4">
              {fields.map(({ key, label: fieldLabel, type = 'text' }) => {
                const value = form[group]?.[key] ?? '';
                const inputId = `${group}_${key}`;
                return (
                  <FormField key={key} label={fieldLabel} htmlFor={inputId}>
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
              })}
            </div>
          </section>
        ))}
      </div>
    </div>
  );
}
