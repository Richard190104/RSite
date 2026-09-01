document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-html-preview-toggle-for]').forEach(function (toggleButton) {
        var fieldId = toggleButton.dataset.htmlPreviewToggleFor;
        var sourceField = document.getElementById(fieldId);
        if (!sourceField) {
            return;
        }

        // The modal (overlay + panel + iframe) is built once per field and
        // kept detached from normal layout flow, so opening it never
        // shifts the form around — it just becomes visible on top.
        var modal = document.createElement('div');
        modal.className = 'admin-html-preview-modal';

        var panel = document.createElement('div');
        panel.className = 'admin-html-preview-modal__panel';

        var header = document.createElement('div');
        header.className = 'admin-html-preview-modal__header';

        var title = document.createElement('span');
        title.textContent = toggleButton.dataset.htmlPreviewTitle || 'Preview';
        header.appendChild(title);

        var closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'admin-html-preview-modal__close';
        closeButton.setAttribute('aria-label', 'Close');
        closeButton.innerHTML = '&times;';
        header.appendChild(closeButton);

        // An <iframe srcdoc> renders the poster HTML in total isolation
        // from the admin theme's own CSS (Milligram/admin-layout would
        // otherwise reset/override things like the poster's own font
        // sizes, and its background color would show through a plain
        // <div> in a way that's misleading about how it'll actually look).
        // No sandbox attribute: the content is already HTML-purified
        // server-side before it's ever saved, and an empty sandbox="" can
        // keep srcdoc from rendering at all in some browsers.
        var frame = document.createElement('iframe');
        frame.className = 'admin-html-preview-modal__frame';

        panel.appendChild(header);
        panel.appendChild(frame);
        modal.appendChild(panel);
        document.body.appendChild(modal);

        function render() {
            var html = sourceField.value.trim();
            frame.srcdoc = html
                ? '<!doctype html><html><head><meta charset="utf-8"><style>'
                    + 'body{margin:0;padding:1rem;font-family:sans-serif;}'
                    + '</style></head><body>' + html + '</body></html>'
                : '<!doctype html><html><body></body></html>';
        }

        function open() {
            render();
            modal.classList.add('is-open');
        }

        function close() {
            modal.classList.remove('is-open');
        }

        toggleButton.addEventListener('click', open);
        closeButton.addEventListener('click', close);
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                close();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                close();
            }
        });

        // Exposed so admin-ai-chat.js can refresh + open the preview
        // immediately after writing a new suggestion into the textarea.
        toggleButton.refreshPreview = render;
        toggleButton.openPreview = open;
    });
});
