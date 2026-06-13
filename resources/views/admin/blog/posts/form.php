<?php
$post = isset($post) && is_array($post) ? $post : null;
$values = isset($values) && is_array($values) ? $values : [];
$seoValues = isset($seoValues) && is_array($seoValues) ? $seoValues : [];
$authors = isset($authors) && is_array($authors) ? $authors : [];
$categories = isset($categories) && is_array($categories) ? $categories : [];
$tags = isset($tags) && is_array($tags) ? $tags : [];
$galleries = isset($galleries) && is_array($galleries) ? $galleries : [];
$revisions = isset($revisions) && is_array($revisions) ? $revisions : [];
$adminPath = (string) ($adminPath ?? '/admin');
$mode = (string) ($mode ?? 'create');
$postId = $post !== null ? (int) ($post['id'] ?? 0) : 0;
$formAction = $mode === 'create' ? $adminPath . '/blog/posts/store' : $adminPath . '/blog/posts/update/' . $postId;
$tokenKey = (string) ($tokenKey ?? '_token');
$featuredImageId = is_scalar($values['featured_image_id'] ?? '') ? (string) ($values['featured_image_id'] ?? '') : '';
$ogImageMediaId = is_scalar($seoValues['og_image'] ?? '') ? (string) ($seoValues['og_image'] ?? '') : '';
$selectedCategoryIds = isset($values['category_ids']) && is_array($values['category_ids']) ? array_map('strval', $values['category_ids']) : [];
$selectedTagIds = isset($values['tag_ids']) && is_array($values['tag_ids']) ? array_map('strval', $values['tag_ids']) : [];
$postMediaHtml = (string) ($postMediaHtml ?? '');
?>

<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900"><?= $mode === 'create' ? 'Create Blog Post' : 'Edit Blog Post' ?></h2>
                <p class="mt-1 text-sm text-slate-600">Build editorial posts with taxonomy, supporting media, SEO data, and controlled publication states.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?= htmlspecialchars($adminPath . '/blog/categories', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700">Manage Categories</a>
                <a href="<?= htmlspecialchars($adminPath . '/blog/tags', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700">Manage Tags</a>
                <a href="<?= htmlspecialchars($adminPath . '/blog/posts', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700">Back to Posts</a>
            </div>
        </div>

        <div class="mb-5 flex flex-wrap gap-2" data-tabs-nav>
            <button type="button" class="rounded-full border border-slate-900 bg-slate-900 px-4 py-2 text-sm text-white" data-tab-target="content">Content</button>
            <button type="button" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700" data-tab-target="taxonomy">Taxonomy</button>
            <button type="button" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700" data-tab-target="media">Media</button>
            <button type="button" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700" data-tab-target="seo">SEO</button>
            <button type="button" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700" data-tab-target="publish">Publish</button>
        </div>

        <form id="blog-post-editor-form" method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" class="space-y-6">
            <?= csrf_field() ?>

            <div data-tab-panel="content" class="space-y-5">
                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Author</span>
                        <select name="author_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <?php foreach ($authors as $author): ?>
                                <?php $authorId = (int) ($author['id'] ?? 0); ?>
                                <option value="<?= $authorId ?>" <?= (string) ($values['author_id'] ?? '') === (string) $authorId ? 'selected' : '' ?>><?= htmlspecialchars(trim((string) (($author['first_name'] ?? '') . ' ' . ($author['last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Reading Time</p>
                        <p data-autosave-reading-time class="mt-2 text-2xl font-semibold text-slate-900"><?= htmlspecialchars((string) (($values['reading_time'] ?? '') !== '' ? ($values['reading_time'] ?? '') : 'Auto'), ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="mt-1 text-sm text-slate-600">Calculated from the article body on save.</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    <span class="font-medium text-slate-900">Draft Status:</span>
                    <span id="blog-post-autosave-status"><?= $postId > 0 ? 'Waiting for edits.' : 'Autosave becomes available after the first save.' ?></span>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Title</span>
                        <input id="blog-post-title" type="text" name="title" value="<?= htmlspecialchars((string) ($values['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Slug</span>
                        <div class="flex gap-2">
                            <input id="blog-post-slug" type="text" name="slug" value="<?= htmlspecialchars((string) ($values['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            <button type="button" id="generate-blog-post-slug" class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700">Generate</button>
                        </div>
                        <p id="blog-post-slug-feedback" class="mt-1 text-xs text-slate-500">Slug will be normalized and kept unique.</p>
                    </label>
                </div>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Excerpt</span>
                    <textarea name="excerpt" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= htmlspecialchars((string) ($values['excerpt'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Body</span>
                    <textarea name="body_long" rows="18" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Write the full article body here."><?= htmlspecialchars((string) ($values['body_long'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>
            </div>

            <div data-tab-panel="taxonomy" class="hidden space-y-5">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Categories</h3>
                            <p class="mt-1 text-sm text-slate-600">Assign one or more primary editorial categories.</p>
                        </div>
                        <a href="<?= htmlspecialchars($adminPath . '/blog/categories', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700">Edit Categories</a>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        <?php foreach ($categories as $category): ?>
                            <?php $categoryId = (int) ($category['id'] ?? 0); ?>
                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-4">
                                <input type="checkbox" name="category_ids[]" value="<?= $categoryId ?>" class="mt-1 rounded border-slate-300" <?= in_array((string) $categoryId, $selectedCategoryIds, true) ? 'checked' : '' ?>>
                                <span class="block">
                                    <span class="block text-sm font-medium text-slate-900"><?= htmlspecialchars((string) ($category['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="mt-1 block text-xs text-slate-500"><?= htmlspecialchars((string) ($category['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Tags</h3>
                            <p class="mt-1 text-sm text-slate-600">Assign flexible tags that later power related post logic and search.</p>
                        </div>
                        <a href="<?= htmlspecialchars($adminPath . '/blog/tags', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700">Edit Tags</a>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        <?php foreach ($tags as $tag): ?>
                            <?php $tagId = (int) ($tag['id'] ?? 0); ?>
                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-4">
                                <input type="checkbox" name="tag_ids[]" value="<?= $tagId ?>" class="mt-1 rounded border-slate-300" <?= in_array((string) $tagId, $selectedTagIds, true) ? 'checked' : '' ?>>
                                <span class="block">
                                    <span class="block text-sm font-medium text-slate-900"><?= htmlspecialchars((string) ($tag['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="mt-1 block text-xs text-slate-500"><?= htmlspecialchars((string) ($tag['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div data-tab-panel="media" class="hidden space-y-5">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-base font-semibold text-slate-900">Featured Image</h3>
                    <p class="mt-1 text-sm text-slate-600">Pick the hero image used for cards and article presentation.</p>
                    <div class="mt-4">
                        <input id="blog-featured-image-id" type="text" name="featured_image_id" value="<?= htmlspecialchars($featuredImageId, ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Media ID">
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button type="button" class="rounded border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700" data-open-picker data-target-input="blog-featured-image-id" data-target-preview="blog-featured-image-preview">Pick Featured Image</button>
                            <span id="blog-featured-image-preview" class="inline-flex items-center rounded bg-white px-3 py-2 text-xs text-slate-600">Selected media ID: <?= $featuredImageId !== '' ? htmlspecialchars($featuredImageId, ENT_QUOTES, 'UTF-8') : 'none' ?></span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Linked Cover Gallery</span>
                        <select name="cover_gallery_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">No linked cover gallery</option>
                            <?php foreach ($galleries as $gallery): ?>
                                <?php $galleryId = (int) ($gallery['id'] ?? 0); ?>
                                <option value="<?= $galleryId ?>" <?= (string) ($values['cover_gallery_id'] ?? '') === (string) $galleryId ? 'selected' : '' ?>><?= htmlspecialchars((string) ($gallery['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Supporting Media</h3>
                            <p class="mt-1 text-sm text-slate-600">Attach supporting images or assets used inside the article body or editorial gallery strips.</p>
                        </div>
                        <?php if ($postId > 0): ?>
                            <button type="button" id="open-blog-post-media-picker" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Attach Media</button>
                        <?php endif; ?>
                    </div>

                    <?php if ($postId > 0): ?>
                        <div id="blog-post-media-manager"><?= $postMediaHtml ?></div>
                    <?php else: ?>
                        <p class="rounded border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">Save the post first to attach and reorder supporting media.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div data-tab-panel="seo" class="hidden space-y-5">
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Meta Summary</span>
                    <textarea name="meta_summary" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Compact editorial summary for search and archive support."><?= htmlspecialchars((string) ($values['meta_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>

                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Meta Title</span>
                        <input type="text" name="meta_title" value="<?= htmlspecialchars((string) ($seoValues['meta_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">OG Title</span>
                        <input type="text" name="og_title" value="<?= htmlspecialchars((string) ($seoValues['og_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Meta Description</span>
                        <textarea name="meta_description" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= htmlspecialchars((string) ($seoValues['meta_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">OG Description</span>
                        <textarea name="og_description" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= htmlspecialchars((string) ($seoValues['og_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </label>
                </div>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Canonical URL</span>
                    <input type="url" name="canonical_url" value="<?= htmlspecialchars((string) ($seoValues['canonical_url'] ?? ($values['canonical_url'] ?? '')), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </label>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">OG Image</label>
                    <input id="blog-og-image-media-id" type="text" name="og_image_media_id" value="<?= htmlspecialchars($ogImageMediaId, ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Media ID">
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button type="button" class="rounded border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700" data-open-picker data-target-input="blog-og-image-media-id" data-target-preview="blog-og-image-preview">Pick OG Image</button>
                        <span id="blog-og-image-preview" class="inline-flex items-center rounded bg-slate-100 px-3 py-2 text-xs text-slate-600">Selected media ID: <?= $ogImageMediaId !== '' ? htmlspecialchars($ogImageMediaId, ENT_QUOTES, 'UTF-8') : 'none' ?></span>
                    </div>
                </div>
            </div>

            <div data-tab-panel="publish" class="hidden space-y-5">
                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Status</span>
                        <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'scheduled' => 'Scheduled', 'archived' => 'Archived'] as $statusKey => $label): ?>
                                <option value="<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($values['status'] ?? 'draft') === $statusKey ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Visibility</span>
                        <select name="visibility" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <?php foreach (['public' => 'Public', 'unlisted' => 'Unlisted', 'private' => 'Private'] as $key => $label): ?>
                                <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($values['visibility'] ?? 'public') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Featured Post</span>
                        <select name="is_featured" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="0" <?= (string) ($values['is_featured'] ?? '0') === '0' ? 'selected' : '' ?>>No</option>
                            <option value="1" <?= (string) ($values['is_featured'] ?? '0') === '1' ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Allow Comments</span>
                        <select name="allow_comments" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="0" <?= (string) ($values['allow_comments'] ?? '0') === '0' ? 'selected' : '' ?>>Disabled</option>
                            <option value="1" <?= (string) ($values['allow_comments'] ?? '0') === '1' ? 'selected' : '' ?>>Enabled</option>
                        </select>
                    </label>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Published At</span>
                        <input type="text" name="published_at" value="<?= htmlspecialchars((string) ($values['published_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="YYYY-MM-DD HH:MM:SS">
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Scheduled At</span>
                        <input type="text" name="scheduled_at" value="<?= htmlspecialchars((string) ($values['scheduled_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="YYYY-MM-DD HH:MM:SS">
                    </label>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Robots Index</span>
                        <select name="robots_index" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="1" <?= (string) ($seoValues['robots_index'] ?? '1') === '1' ? 'selected' : '' ?>>Index</option>
                            <option value="0" <?= (string) ($seoValues['robots_index'] ?? '1') === '0' ? 'selected' : '' ?>>No Index</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Robots Follow</span>
                        <select name="robots_follow" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="1" <?= (string) ($seoValues['robots_follow'] ?? '1') === '1' ? 'selected' : '' ?>>Follow</option>
                            <option value="0" <?= (string) ($seoValues['robots_follow'] ?? '1') === '0' ? 'selected' : '' ?>>No Follow</option>
                        </select>
                    </label>
                </div>

                <?php if ($revisions !== []): ?>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <h3 class="text-base font-semibold text-slate-900">Revision Snapshots</h3>
                        <div class="mt-4 space-y-3">
                            <?php foreach ($revisions as $revision): ?>
                                <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <p class="text-sm font-medium text-slate-900"><?= htmlspecialchars((string) ($revision['title'] ?? 'Untitled revision'), ENT_QUOTES, 'UTF-8') ?></p>
                                    <p class="mt-1 text-xs text-slate-500"><?= htmlspecialchars(trim((string) (($revision['first_name'] ?? '') . ' ' . ($revision['last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?><?= trim((string) (($revision['first_name'] ?? '') . ' ' . ($revision['last_name'] ?? ''))) !== '' ? ' • ' : '' ?><?= htmlspecialchars((string) ($revision['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="submit" name="publish_action" value="save" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">Save Changes</button>
                <button type="submit" name="publish_action" value="save_draft" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">Save Draft</button>
                <button type="submit" name="publish_action" value="schedule_publish" class="rounded-lg border border-sky-300 px-4 py-2 text-sm font-medium text-sky-700">Schedule Publish</button>
                <button type="submit" name="publish_action" value="publish_now" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Publish Now</button>
                <button type="submit" name="publish_action" value="archive" class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-medium text-rose-700">Archive</button>
            </div>
        </form>
    </div>
</section>

<?php include dirname(__DIR__, 3) . '/components/media-picker.php'; ?>

<script>
    window.MEDIA_LIBRARY_CONFIG = {
        adminPath: <?= json_encode($adminPath) ?>,
        csrfKey: <?= json_encode($tokenKey) ?>,
        csrfToken: <?= json_encode(app_csrf()->token()) ?>,
        pickerOnly: true
    };
    window.BLOG_POST_ADMIN_CONFIG = {
        adminPath: <?= json_encode($adminPath) ?>,
        postId: <?= json_encode($postId) ?>,
        csrfKey: <?= json_encode($tokenKey) ?>,
        csrfToken: <?= json_encode(app_csrf()->token()) ?>,
        autosaveUrl: <?= json_encode($postId > 0 ? $adminPath . '/blog/posts/autosave/' . $postId : '') ?>
    };
</script>
<script src="<?= htmlspecialchars(asset_url('js/admin-media.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(asset_url('js/admin-blog.js'), ENT_QUOTES, 'UTF-8') ?>"></script>