document.addEventListener('DOMContentLoaded', function () {
    // Turns any textarea.js-wysiwyg into a TinyMCE rich-text editor
    // (self-hosted, no cloud API key — see webroot/js/vendor/tinymce/).
    // TinyMCE keeps the textarea itself in sync with the editor's HTML on
    // every change and on form submit, so nothing extra is needed to read
    // the value back out server-side. That HTML then goes through the same
    // HtmlSanitizeTrait::sanitizeHtml() every other admin HTML field does
    // (see Admin\PagesController::editPoplatky()) — this is just the editor,
    // not the source of trust for what gets saved.
    if (typeof tinymce === 'undefined' || !document.querySelector('textarea.js-wysiwyg')) {
        return;
    }

    tinymce.init({
        selector: 'textarea.js-wysiwyg',
        base_url: '/js/vendor/tinymce',
        suffix: '.min',
        license_key: 'gpl',
        menubar: false,
        statusbar: false,
        plugins: 'lists link code',
        toolbar: 'undo redo | blocks | bold italic underline | lineheight | bullist numlist | link | removeformat | code',
        // Kept in sync with what HtmlSanitizeTrait::sanitizeHtml() actually
        // keeps (see src/Controller/Admin/HtmlSanitizeTrait.php's
        // HTML.Allowed) — no point offering markup in the editor (or letting
        // it survive a raw-HTML paste via the "code" button) that gets
        // silently stripped again on save. No <style> tag: TinyMCE's parser
        // treats <style> as editor chrome, not content — it never reaches
        // getContent() regardless of valid_elements/xss_sanitization, so
        // page-level styling here has to be inline style="" on each element
        // instead of a shared <style> block with classes.
        valid_elements: 'p[class|style],br[class],strong/b[class],em/i[class],u[class],s[class],'
            + 'a[href|class|style],ul[class],ol[class],li[class],h1[class|style],h2[class|style],'
            + 'h3[class|style],h4[class|style],blockquote[class],img[src|alt|class|style],'
            + 'span[class|style],div[class|style],table[class|style],thead[class],tbody[class],'
            + 'tr[class|style],td[class|style],th[class|style]',
        // TinyMCE 6+ runs its own DOMPurify pass on top of valid_elements,
        // which strips <style> tags and most class attributes outright as a
        // defense-in-depth default aimed at people who never sanitize
        // server-side. We do — every save round-trips through
        // HtmlSanitizeTrait::sanitizeHtml() (HTMLPurifier) regardless of
        // what the editor produces — so this second, unconfigurable layer
        // only serves to silently eat class/<style> content before it ever
        // reaches the server. Safe to disable here specifically because the
        // real sanitization boundary is save-time, not edit-time.
        xss_sanitization: false,
        block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
        // Explicit, not left to CSS: TinyMCE sizes its iframe from the
        // source textarea's own width/height at init time, and a plain
        // <textarea> defaults to ~20 cols wide regardless of its
        // .form-grid__full container — width: '100%' here is what actually
        // makes the editor span the full form width.
        width: '100%',
        height: 320,
        // Browser default <p> margin (~1em top+bottom) makes every Enter
        // look like a huge jump in a 14px editor — line-height isn't the
        // culprit, paragraph spacing is. Match the small gap the public
        // page actually renders (.p-poplatky__notice--wysiwyg p in
        // _poplatky.scss uses margin: 0 0 1.2rem) so what's typed here
        // looks the same as what visitors will see.
        content_style: 'body { font-family: inherit; font-size: 14px; line-height: 1.5; } p { margin: 0 0 12px; }',
    });
});
