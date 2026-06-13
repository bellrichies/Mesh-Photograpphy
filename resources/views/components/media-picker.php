<div id="media-picker-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 p-4">
    <div class="mx-auto mt-4 flex max-h-[calc(100vh-2rem)] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-soft">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 sm:px-5">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Media Picker</h3>
                <p class="mt-1 text-xs text-slate-500">Compact library view with quick search and pagination.</p>
            </div>
            <button type="button" class="rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700" data-picker-close>Close</button>
        </div>

        <div class="border-b border-slate-200 px-4 py-3 sm:px-5">
            <div class="grid gap-2 sm:grid-cols-[minmax(0,1fr),11rem,auto]">
                <input type="text" id="picker-query" class="w-full rounded border border-slate-300 px-3 py-2 text-sm" placeholder="Search media...">
                <select id="picker-type" class="rounded border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All Types</option>
                    <option value="image">Images</option>
                    <option value="video">Videos</option>
                    <option value="document">Documents</option>
                </select>
                <button type="button" id="picker-search" class="rounded bg-slate-900 px-4 py-2 text-sm text-white">Search</button>
            </div>
        </div>

        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-2.5 sm:px-5">
            <p id="picker-pagination-info" class="text-xs text-slate-500">Start searching to pick media.</p>
            <div class="flex items-center gap-2">
                <button type="button" id="picker-prev" class="rounded border border-slate-300 px-3 py-1 text-xs text-slate-700 disabled:cursor-not-allowed disabled:opacity-50">Prev</button>
                <button type="button" id="picker-next" class="rounded border border-slate-300 px-3 py-1 text-xs text-slate-700 disabled:cursor-not-allowed disabled:opacity-50">Next</button>
            </div>
        </div>

        <div id="picker-results" class="min-h-[22rem] overflow-y-auto px-4 py-4 sm:px-5">
            <p class="rounded border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Start searching to pick media.</p>
        </div>
    </div>
</div>
