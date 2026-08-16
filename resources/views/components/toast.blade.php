{{-- Toast system powered by Alpine.js + Livewire events --}}
<div
    x-data="{
        toasts: [],
        addToast(type, title, message) {
            const id = Date.now();
            this.toasts.push({ id, type, title, message });
            setTimeout(() => this.removeToast(id), 4000);
        },
        removeToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    x-on:toast.window="let d = Array.isArray($event.detail) ? $event.detail[0] : $event.detail; addToast(d.type, d.title, d.message)"
    x-on:notify.window="let d = Array.isArray($event.detail) ? $event.detail[0] : $event.detail; addToast(d.type ?? 'info', d.title ?? '', d.message ?? '')"
    class="toast-container"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div class="toast" :class="toast.type">
            <svg class="toast-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <template x-if="toast.type === 'success'">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </template>
                <template x-if="toast.type === 'error'">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </template>
                <template x-if="toast.type === 'warning'">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </template>
                <template x-if="toast.type === 'info' || !['success','error','warning'].includes(toast.type)">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </template>
            </svg>
            <div class="toast-content">
                <div class="toast-title" x-text="toast.title"></div>
                <div class="toast-message" x-text="toast.message"></div>
            </div>
            <button
                x-on:click="removeToast(toast.id)"
                style="background:none;border:none;color:var(--color-muted);cursor:pointer;padding:0;margin-left:8px;"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>
</div>
