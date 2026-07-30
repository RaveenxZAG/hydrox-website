import './bootstrap';
import Alpine from 'alpinejs';
import SignaturePad from 'signature_pad';

window.Alpine = Alpine;
window.SignaturePad = SignaturePad;

Alpine.data('themeToggle', () => ({
    dark: localStorage.theme === 'dark',
    init() {
        this.apply();
    },
    toggle() {
        this.dark = !this.dark;
        localStorage.theme = this.dark ? 'dark' : 'light';
        this.apply();
    },
    apply() {
        document.documentElement.classList.toggle('dark', this.dark);
    },
}));

Alpine.data('reportBuilder', (existing = {}) => ({
    areas: existing.areas?.length ? existing.areas : [{ area_name: '', description: '', completion_notes: '', photo_notes: '', video_url: '' }],
    issues: existing.issues?.length ? existing.issues : [{ issue_type: '', description: '', severity: 'Low', recommendation: '' }],
    savedAreas: existing.areas ?? [],
    savedIssues: existing.issues ?? [],
    checklistItems: existing.checklist_items ?? [],
    checklist: existing.checklist ?? [],
    checklistInput: '',
    selectedJob: existing.selected_job ?? null,
    jobSearch: existing.selected_job?.job_number ?? '',
    jobResults: [],
    jobResultsOpen: false,
    jobSearching: false,
    jobSearchUrl: existing.job_search_url,
    reportId: existing.report_id,
    fileSelections: {},
    uploadUrl: existing.upload_url,
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,
    removedAreaPhotoIds: [],
    removedIssuePhotoIds: [],
    maxFileUploads: Number(existing.max_file_uploads || 0),
    form: null,
    draftTimer: null,
    submitting: false,
    progress: 0,
    submitMessage: 'Preparing upload...',
    submitTimer: null,
    init() {
        this.form = this.$root.closest('form');
        this.restoreDraft(this.form);
        this.form?.addEventListener('submit', (event) => this.beforeSubmit(event));
        this.form?.addEventListener('input', () => this.scheduleDraftSave(this.form));
        this.form?.addEventListener('change', () => this.scheduleDraftSave(this.form));
        window.addEventListener('beforeunload', (event) => {
            if (this.hasDraft() && !this.submitting) {
                event.preventDefault();
                event.returnValue = '';
            }
        });
    },
    addArea() {
        this.areas.push({ area_name: '', description: '', completion_notes: '', photo_notes: '', video_url: '' });
        this.scheduleDraftSave(this.form);
    },
    addIssue() {
        this.issues.push({ issue_type: '', description: '', severity: 'Low', recommendation: '' });
        this.scheduleDraftSave(this.form);
    },
    remove(list, index) {
        if (this[list].length > 1) {
            this[list].splice(index, 1);
            this.scheduleDraftSave(this.form);
        }
    },
    checklistSuggestions() {
        const query = this.checklistInput.trim().toLowerCase();
        const selected = this.checklist.map((item) => item.toLowerCase());

        return this.checklistItems
            .filter((item) => !selected.includes(item.toLowerCase()))
            .filter((item) => !query || item.toLowerCase().includes(query))
            .slice(0, 6);
    },
    addChecklistItem(item = null) {
        const value = (item ?? this.checklistInput).trim();

        if (!value) return;
        if (!this.checklist.some((existing) => existing.toLowerCase() === value.toLowerCase())) {
            this.checklist.push(value);
        }

        if (!this.checklistItems.some((existing) => existing.toLowerCase() === value.toLowerCase())) {
            this.checklistItems.push(value);
        }

        this.checklistInput = '';
        this.scheduleDraftSave(this.form);
    },
    removeChecklistItem(index) {
        this.checklist.splice(index, 1);
        this.scheduleDraftSave(this.form);
    },
    openJobSearch() {
        this.jobResultsOpen = true;
        this.searchJobs();
    },
    async searchJobs() {
        const query = this.jobSearch.trim();

        if (this.selectedJob && query !== this.selectedJob.job_number) {
            this.selectedJob = null;
        }

        if (!this.jobSearchUrl) return;

        this.jobSearching = true;
        this.jobResultsOpen = true;

        try {
            const url = new URL(this.jobSearchUrl, window.location.origin);
            url.searchParams.set('q', query);
            if (this.reportId) url.searchParams.set('report_id', this.reportId);

            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Job search failed.');

            const data = await response.json();
            this.jobResults = data.jobs ?? [];
        } catch {
            this.jobResults = [];
        } finally {
            this.jobSearching = false;
        }
    },
    selectJob(job) {
        this.selectedJob = job;
        this.jobSearch = job.job_number;
        this.jobResultsOpen = false;

        const technician = this.form?.querySelector('[name="technician"]');
        if (technician && !technician.value && job.technician) {
            technician.value = job.technician;
            technician.dispatchEvent(new Event('input', { bubbles: true }));
        }

        this.scheduleDraftSave(this.form);
    },
    beforeSubmit(event) {
        if (this.hasPendingUploads()) {
            event.preventDefault();
            this.submitting = false;
            this.progress = 0;
            window.alert('Some photos are still uploading. Please wait until every photo says Uploaded, then save again.');
            return;
        }

        if (this.hasFailedUploads()) {
            event.preventDefault();
            this.submitting = false;
            this.progress = 0;
            window.alert('Some photos failed to upload. Please remove them or choose them again before saving.');
            return;
        }

        const selectedFiles = this.selectedFileCount();

        if (this.maxFileUploads > 0 && selectedFiles > this.maxFileUploads) {
            event.preventDefault();
            this.submitting = false;
            this.progress = 0;
            window.alert(`You selected ${selectedFiles} photos, but this server can only receive ${this.maxFileUploads} files in one save. Please upload fewer photos at once or increase max_file_uploads on the server.`);
            return;
        }

        this.saveDraft(this.form);
    },
    startSubmit() {
        if (this.submitting) return;

        this.submitting = true;
        this.progress = 8;
        this.submitMessage = 'Preparing photos...';

        const steps = [
            [24, 'Uploading photos...'],
            [42, 'Saving report details...'],
            [63, 'Building completion report...'],
            [78, 'Generating PDF...'],
            [88, 'Finalising PDF...'],
            [94, 'Almost done...'],
        ];

        let index = 0;
        this.submitTimer = window.setInterval(() => {
            if (index < steps.length) {
                const [progress, message] = steps[index];
                this.progress = progress;
                this.submitMessage = message;
                index += 1;
                return;
            }

            if (this.progress < 97) this.progress += 1;
        }, 700);
    },
    previewFiles(event, target) {
        const input = event.target;
        this.addFiles([...input.files], input.name, input, target);
        input.value = '';
    },
    dropFiles(event, key, input, target) {
        const files = [...event.dataTransfer.files].filter((file) => file.type.startsWith('image/'));
        this.addFiles(files, key, input, target);
    },
    addFiles(files, key, input, target) {
        if (!files.length || !input || !target) return;

        const current = this.fileSelections[key] ?? [];

        files.forEach((file) => {
            const item = {
                file,
                name: file.name,
                previewUrl: URL.createObjectURL(file),
                status: 'uploading',
                token: null,
                error: null,
            };

            current.push(item);
            this.uploadReportPhoto(item, key, input, target);
        });

        this.fileSelections[key] = current;
        this.renderFilePreviews(input, target, key);
        this.scheduleDraftSave(this.form);
    },
    async uploadReportPhoto(item, key, input, target) {
        if (!this.uploadUrl || !this.csrfToken) {
            item.status = 'failed';
            item.error = 'Upload endpoint is not available.';
            this.renderFilePreviews(input, target, key);
            return;
        }

        const body = new FormData();
        body.append('photo', item.file);

        try {
            const response = await fetch(this.uploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                },
                body,
            });

            if (!response.ok) throw new Error('Upload failed.');

            const data = await response.json();
            item.status = 'uploaded';
            item.token = data.token;
            item.name = data.name || item.name;
            item.url = data.url;
        } catch (error) {
            item.status = 'failed';
            item.error = error.message || 'Upload failed.';
        }

        this.renderFilePreviews(input, target, key);
        this.scheduleDraftSave(this.form);
    },
    removeExistingPhoto(photos, photoId, type) {
        const removed = type === 'issue' ? this.removedIssuePhotoIds : this.removedAreaPhotoIds;

        if (!removed.includes(photoId)) removed.push(photoId);

        const index = photos.findIndex((photo) => photo.id === photoId);
        if (index >= 0) photos.splice(index, 1);
    },
    removeSelectedFile(input, target, key, index) {
        this.fileSelections[key].splice(index, 1);
        this.renderFilePreviews(input, target, key);
        this.scheduleDraftSave(this.form);
    },
    selectedFileCount() {
        return 0;
    },
    hasPendingUploads() {
        return Object.values(this.fileSelections).some((files) => files.some((file) => file.status === 'uploading'));
    },
    hasFailedUploads() {
        return Object.values(this.fileSelections).some((files) => files.some((file) => file.status === 'failed'));
    },
    syncFileInput(input, key) {
        const transfer = new DataTransfer();

        (this.fileSelections[key] ?? []).forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
    },
    renderFilePreviews(input, target, key) {
        target.innerHTML = '';
        (this.fileSelections[key] ?? []).forEach((item, index) => {
            const frame = document.createElement('div');
            frame.className = 'relative h-24 w-24 overflow-hidden rounded-lg ring-1 ring-slate-200 dark:ring-slate-700';

            const img = document.createElement('img');
            img.src = item.previewUrl || item.url;
            img.className = 'h-full w-full object-cover';

            const badge = document.createElement('span');
            badge.textContent = item.status === 'uploaded' ? 'Uploaded' : item.status === 'failed' ? 'Failed' : 'Uploading';
            badge.className = `absolute bottom-1 left-1 rounded px-1.5 py-0.5 text-[10px] font-bold text-white ${item.status === 'uploaded' ? 'bg-emerald-600' : item.status === 'failed' ? 'bg-rose-600' : 'bg-cyan-600'}`;

            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = 'x';
            button.setAttribute('aria-label', `Remove ${item.name}`);
            button.className = 'absolute right-1 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-rose-600 text-xs font-bold text-white shadow';
            button.addEventListener('click', () => this.removeSelectedFile(input, target, key, index));

            frame.appendChild(img);
            frame.appendChild(badge);
            frame.appendChild(button);

            if (item.status === 'uploaded' && item.token) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = this.tokenInputName(key);
                hidden.value = JSON.stringify({ token: item.token, name: item.name });
                frame.appendChild(hidden);
            }

            target.appendChild(frame);
        });
    },
    tokenInputName(name) {
        return name
            .replace('[before_photos][]', '[before_photo_tokens][]')
            .replace('[after_photos][]', '[after_photo_tokens][]')
            .replace('[photos][]', '[photo_tokens][]');
    },
    draftKey() {
        return this.$root.dataset.draftKey;
    },
    scheduleDraftSave(form) {
        window.clearTimeout(this.draftTimer);
        this.draftTimer = window.setTimeout(() => this.saveDraft(form), 400);
    },
    saveDraft(form) {
        if (!form || !this.draftKey()) return;

        const fields = {};
        new FormData(form).forEach((value, key) => {
            if (key.includes('photos') || key.includes('signature') || key.includes('_token') || key.includes('_method')) return;
            if (fields[key]) {
                fields[key] = Array.isArray(fields[key]) ? [...fields[key], value] : [fields[key], value];
            } else {
                fields[key] = value;
            }
        });

        localStorage.setItem(this.draftKey(), JSON.stringify({
            areas: this.areas,
            issues: this.issues,
            checklist: this.checklist,
            uploadedFiles: this.serializableUploadedFiles(),
            fields,
            savedAt: new Date().toISOString(),
        }));
    },
    restoreDraft(form) {
        if (!this.draftKey()) return;

        const raw = localStorage.getItem(this.draftKey());
        if (!raw) return;

        try {
            const draft = JSON.parse(raw);
            if (draft.areas?.length) this.areas = this.mergeSavedPhotos(draft.areas, this.savedAreas, ['before_photos', 'after_photos']);
            if (draft.issues?.length) this.issues = this.mergeSavedPhotos(draft.issues, this.savedIssues, ['photos']);
            if (Array.isArray(draft.checklist)) this.checklist = draft.checklist;
            if (draft.uploadedFiles) this.fileSelections = draft.uploadedFiles;

            this.$nextTick(() => {
                Object.entries(draft.fields ?? {}).forEach(([name, value]) => {
                    const values = Array.isArray(value) ? value : [value];
                    this.fieldsByName(form, name).forEach((field, index) => {
                        if (field.type === 'checkbox' || field.type === 'radio') {
                            field.checked = values.includes(field.value);
                        } else if (field.type !== 'file') {
                            field.value = values[index] ?? values[0] ?? '';
                            field.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });
                });
                this.renderRestoredUploads(form);
                window.setTimeout(() => this.renderRestoredUploads(form), 150);
            });
        } catch {
            localStorage.removeItem(this.draftKey());
        }
    },
    mergeSavedPhotos(draftItems, savedItems, photoKeys) {
        const mergedItems = draftItems.map((draftItem, index) => {
            const savedItem = savedItems.find((item) => item.id && String(item.id) === String(draftItem.id))
                || savedItems[index];

            if (!savedItem) return draftItem;

            const mergedItem = { ...draftItem };

            photoKeys.forEach((key) => {
                const savedPhotos = savedItem[key] ?? [];
                const draftPhotos = draftItem[key] ?? [];
                const savedIds = savedPhotos.map((photo) => String(photo.id));
                const newDraftPhotos = draftPhotos.filter((photo) => !photo.id || !savedIds.includes(String(photo.id)));

                mergedItem[key] = [...savedPhotos, ...newDraftPhotos];
            });

            return mergedItem;
        });

        savedItems.forEach((savedItem) => {
            if (savedItem.id && mergedItems.some((item) => String(item.id) === String(savedItem.id))) {
                return;
            }

            if (!savedItem.id && mergedItems.includes(savedItem)) {
                return;
            }

            mergedItems.push(savedItem);
        });

        return mergedItems;
    },
    hasDraft() {
        return this.draftKey() && localStorage.getItem(this.draftKey());
    },
    clearDraft() {
        if (this.draftKey()) localStorage.removeItem(this.draftKey());
    },
    serializableUploadedFiles() {
        return Object.fromEntries(
            Object.entries(this.fileSelections)
                .map(([key, files]) => [
                    key,
                    files
                        .filter((file) => file.status === 'uploaded' && file.token)
                        .map((file) => ({
                            name: file.name,
                            status: 'uploaded',
                            token: file.token,
                            url: file.url,
                            previewUrl: file.url,
                        })),
                ])
                .filter(([, files]) => files.length)
        );
    },
    renderRestoredUploads(form) {
        Object.keys(this.fileSelections).forEach((name) => {
            const input = this.fieldsByName(form, name)
                .find((field) => field.type === 'file');
            const target = input?.nextElementSibling;

            if (input && target) {
                this.renderFilePreviews(input, target, name);
            }
        });
    },
    fieldsByName(form, name) {
        if (!form) return [];

        return [...form.elements].filter((field) => field.name === name);
    },
}));

Alpine.data('onboardingSubmit', () => ({
    submitting: false,
    progress: 0,
    submitMessage: 'Preparing onboarding...',
    submitTimer: null,
    startSubmit() {
        if (this.submitting) return;

        this.submitting = true;
        this.progress = 10;
        this.submitMessage = 'Checking details...';

        const steps = [
            [25, 'Uploading documents...'],
            [48, 'Saving business information...'],
            [68, 'Saving compliance documents...'],
            [84, 'Submitting for review...'],
            [94, 'Almost done...'],
        ];

        let index = 0;
        this.submitTimer = window.setInterval(() => {
            if (index < steps.length) {
                const [progress, message] = steps[index];
                this.progress = progress;
                this.submitMessage = message;
                index += 1;
                return;
            }

            if (this.progress < 97) this.progress += 1;
        }, 700);
    },
}));

Alpine.data('signatureBox', (inputName) => ({
    pad: null,
    inputName,
    init() {
        const canvas = this.$refs.canvas;
        this.pad = new SignaturePad(canvas, { backgroundColor: 'rgba(255,255,255,0)' });
        const resize = () => {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            this.pad.clear();
        };
        resize();
        window.addEventListener('resize', resize);
        this.pad.addEventListener('endStroke', () => {
            this.$refs.input.value = this.pad.toDataURL('image/png');
        });
    },
    clear() {
        this.pad.clear();
        this.$refs.input.value = '';
    },
}));

Alpine.start();

const installFormSafeguards = () => {
    const draftNotice = document.createElement('div');
    draftNotice.className = 'fixed bottom-4 right-4 z-50 hidden rounded-lg bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-lg';
    document.body.appendChild(draftNotice);

    let noticeTimer = null;
    const showNotice = (message) => {
        draftNotice.textContent = message;
        draftNotice.classList.remove('hidden');
        window.clearTimeout(noticeTimer);
        noticeTimer = window.setTimeout(() => draftNotice.classList.add('hidden'), 1800);
    };

    const forms = [...document.querySelectorAll('form')];

    forms.forEach((form) => {
        if (!shouldProtectForm(form)) return;

        const key = formDraftKey(form);
        let dirty = false;
        let saveTimer = null;

        restoreFormDraft(form, key, showNotice);

        const save = () => {
            saveFormDraft(form, key);
            dirty = true;
            showNotice('Draft saved');
        };

        form.addEventListener('input', () => {
            window.clearTimeout(saveTimer);
            saveTimer = window.setTimeout(save, 500);
        });

        form.addEventListener('change', () => {
            window.clearTimeout(saveTimer);
            saveTimer = window.setTimeout(save, 500);
        });

        form.addEventListener('submit', (event) => {
            if (event.defaultPrevented) return;

            localStorage.removeItem(key);
            dirty = false;
            markFormSubmitting(form);
        });

        window.addEventListener('beforeunload', (event) => {
            if (!dirty || form.dataset.submitting === 'true') return;

            event.preventDefault();
            event.returnValue = '';
        });
    });
};

const shouldProtectForm = (form) => {
    const method = (form.getAttribute('method') || 'GET').toUpperCase();
    const action = form.getAttribute('action') || window.location.pathname;

    if (form.dataset.noDraft === 'true') return false;
    if (method === 'GET') return false;
    if (form.querySelector('[data-draft-key]')) return false;
    if (form.querySelector('input[type="password"]')) return false;
    if (action.includes('/logout') || action.includes('/approve') || action.includes('/reject')) return false;

    const spoofedMethod = form.querySelector('input[name="_method"]')?.value?.toUpperCase();
    if (spoofedMethod === 'DELETE') return false;

    return Boolean(form.querySelector('input:not([type="hidden"]):not([type="file"]), textarea, select'));
};

const formDraftKey = (form) => {
    const action = form.getAttribute('action') || window.location.pathname;
    const method = form.querySelector('input[name="_method"]')?.value || form.getAttribute('method') || 'GET';

    return `hydrox-form-draft:${method.toUpperCase()}:${action}`;
};

const saveFormDraft = (form, key) => {
    const fields = {};

    new FormData(form).forEach((value, name) => {
        if (shouldSkipDraftField(form, name)) return;

        if (fields[name]) {
            fields[name] = Array.isArray(fields[name]) ? [...fields[name], value] : [fields[name], value];
        } else {
            fields[name] = value;
        }
    });

    localStorage.setItem(key, JSON.stringify({
        fields,
        savedAt: new Date().toISOString(),
    }));
};

const restoreFormDraft = (form, key, showNotice) => {
    const raw = localStorage.getItem(key);
    if (!raw) return;

    try {
        const draft = JSON.parse(raw);
        const fields = draft.fields ?? {};

        Object.entries(fields).forEach(([name, value]) => {
            const values = Array.isArray(value) ? value : [value];
            const inputs = form.querySelectorAll(`[name="${CSS.escape(name)}"]`);

            inputs.forEach((input, index) => {
                if (input.type === 'file' || input.type === 'password') return;

                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = values.includes(input.value);
                    return;
                }

                input.value = values[index] ?? values[0] ?? '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
        });

        showNotice('Draft restored');
    } catch {
        localStorage.removeItem(key);
    }
};

const shouldSkipDraftField = (form, name) => {
    if (['_token', '_method'].includes(name)) return true;
    if (name.includes('signature')) return true;

    const field = form.querySelector(`[name="${CSS.escape(name)}"]`);
    if (field?.dataset.draftSkip === 'true') return true;

    return ['file', 'password', 'hidden'].includes(field?.type);
};

const markFormSubmitting = (form) => {
    form.dataset.submitting = 'true';
    const submittingText = form.dataset.submittingText || 'Saving...';

    form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]').forEach((button) => {
        const buttonText = button.dataset.submittingText || submittingText;
        button.dataset.originalText = button.value || button.textContent;
        button.disabled = true;

        if (button.tagName === 'INPUT') {
            button.value = buttonText;
        } else {
            button.textContent = buttonText;
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', installFormSafeguards);
} else {
    installFormSafeguards();
}
