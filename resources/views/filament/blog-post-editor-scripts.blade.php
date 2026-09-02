{{-- Panel-wide, but harmless on other pages: the CSS only targets the
     rich-text editor's own toolbar, and the autosave JS below only starts
     its timer when the current URL is actually a blog post edit page. --}}
<style>
    .sticky-toolbar-editor .fi-fo-rich-editor-toolbar,
    div[x-data*="richEditorFormComponent"] .ProseMirror-menubar,
    .sticky-toolbar-editor > div > div:first-child {
        position: sticky;
        top: 0;
        z-index: 30;
        background: rgb(var(--gray-950, 3 7 18));
        background: var(--fi-panel-bg, #fff);
    }
</style>

<script>
    (function () {
        let autosaveTimer = null;

        function isBlogPostEditPage() {
            return /\/admin\/blog-posts\/\d+\/edit/.test(window.location.pathname);
        }

        function startAutosave() {
            if (!isBlogPostEditPage() || autosaveTimer) return;
            autosaveTimer = setInterval(function () {
                if (window.Livewire) {
                    window.Livewire.dispatch('autosave-tick');
                }
            }, 30000);
        }

        function stopAutosave() {
            if (autosaveTimer) {
                clearInterval(autosaveTimer);
                autosaveTimer = null;
            }
        }

        document.addEventListener('livewire:navigated', function () {
            stopAutosave();
            startAutosave();
        });
        document.addEventListener('DOMContentLoaded', startAutosave);

        window.addEventListener('autosaved-at', function (e) {
            const time = e.detail?.time ?? e.detail?.[0]?.time ?? '';
            let el = document.getElementById('autosave-indicator');
            if (!el) {
                el = document.createElement('div');
                el.id = 'autosave-indicator';
                el.style.cssText = 'position:fixed;bottom:16px;right:16px;background:#0a0e17;color:#7dd3c0;padding:8px 14px;border-radius:8px;font-size:12px;z-index:9999;opacity:0;transition:opacity .3s;';
                document.body.appendChild(el);
            }
            el.textContent = 'Autosaved at ' + time;
            el.style.opacity = '1';
            clearTimeout(el._hideTimer);
            el._hideTimer = setTimeout(() => { el.style.opacity = '0'; }, 3000);
        });
    })();
</script>
