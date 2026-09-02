document.addEventListener('DOMContentLoaded', function () {
    var widget = document.querySelector('.admin-ai-chat');
    if (!widget) {
        return;
    }

    var toggle = widget.querySelector('.admin-ai-chat__toggle');
    var closeButton = widget.querySelector('.admin-ai-chat__close');
    var modeButtons = Array.prototype.slice.call(widget.querySelectorAll('.admin-ai-chat__mode'));
    var messagesEl = widget.querySelector('.admin-ai-chat__messages');
    var form = widget.querySelector('.admin-ai-chat__form');
    var input = widget.querySelector('.admin-ai-chat__input');

    var titleField = widget.dataset.aiChatTitleFrom
        ? document.getElementById(widget.dataset.aiChatTitleFrom)
        : null;
    var descriptionField = widget.dataset.aiChatDescriptionFrom
        ? document.getElementById(widget.dataset.aiChatDescriptionFrom)
        : null;

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.content : '';

    var history = [];

    // Exactly one mode button is active at a time — it determines which
    // field "Use this" writes into and what field_label/mode get sent to
    // the server. Switching modes never sends anything on its own; the
    // admin always has to type and send a message themselves. This is
    // deliberately explicit rather than a single sticky "HTML mode"
    // toggle, so a later plain-text request never silently ends up
    // overwriting a previously-generated poster (or vice versa) just
    // because a mode was left selected from an earlier message.
    // No button starts active — the admin has to pick which field they
    // mean before sending, rather than the widget silently guessing one.
    var activeMode = modeButtons.filter(function (button) {
        return button.classList.contains('is-active');
    })[0] || null;

    function setActiveMode(button) {
        activeMode = button;
        modeButtons.forEach(function (candidate) {
            candidate.classList.toggle('is-active', candidate === button);
        });
    }

    modeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            // Clicking the already-active mode again deselects it, going
            // back to the no-field-picked state instead of being stuck once
            // a mode is chosen.
            setActiveMode(activeMode === button ? null : button);
        });
    });

    function setOpen(isOpen) {
        widget.classList.toggle('is-open', isOpen);
    }

    toggle.addEventListener('click', function () {
        setOpen(!widget.classList.contains('is-open'));
        if (widget.classList.contains('is-open')) {
            input.focus();
        }
    });

    closeButton.addEventListener('click', function () {
        setOpen(false);
    });

    // The HTML target is a plain textarea holding raw poster markup (see
    // admin-html-preview.js) rather than a rich-text editor — a poster's
    // own background/padding/border-radius wouldn't survive being parsed
    // into a WYSIWYG editor's internal document model, so "Use this" just
    // writes the string directly and opens the preview modal with it.
    function findPreviewToggleFor(field) {
        if (!field) {
            return null;
        }

        return document.querySelector('[data-html-preview-toggle-for="' + field.id + '"]');
    }

    function appendMessage(role, text, suggestion) {
        var bubble = document.createElement('div');
        bubble.className = 'admin-ai-chat__message admin-ai-chat__message--' + role;

        var textEl = document.createElement('p');
        textEl.textContent = text;
        bubble.appendChild(textEl);

        // Only a reply the model itself flagged as a ready-to-use draft
        // (suggestion !== null/empty) gets a "Use this" button — a plain
        // answer or clarification never gets one, so it can't be mistaken
        // for something meant to overwrite the field. It always writes
        // into whichever field was active AT THE TIME the message was
        // sent (captured below), not whatever mode is selected by the
        // time the admin clicks the button.
        if (suggestion && activeMode) {
            var targetMode = activeMode;
            var useButton = document.createElement('button');
            useButton.type = 'button';
            useButton.className = 'admin-ai-chat__use';
            useButton.textContent = widget.dataset.aiChatUseLabel || 'Use this';
            useButton.addEventListener('click', function () {
                if (!targetMode) {
                    return;
                }

                var field = document.getElementById(targetMode.dataset.modeTarget);
                if (!field) {
                    return;
                }

                field.value = suggestion;

                if (targetMode.dataset.modeKind === 'html') {
                    var previewToggle = findPreviewToggleFor(field);
                    if (previewToggle && previewToggle.openPreview) {
                        previewToggle.openPreview();
                    }
                }
            });
            bubble.appendChild(useButton);
        }

        messagesEl.appendChild(bubble);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function appendError(message) {
        var bubble = document.createElement('div');
        bubble.className = 'admin-ai-chat__message admin-ai-chat__message--error';
        bubble.textContent = message;
        messagesEl.appendChild(bubble);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function sendMessage(text) {
        text = text.trim();
        if (!text) {
            return;
        }

        appendMessage('user', text);
        history.push({ role: 'user', text: text });
        input.value = '';
        input.disabled = true;

        // No mode buttons at all means this page isn't an add/edit form
        // with a field to draft — the widget acts as a pure navigation
        // helper instead (see Admin\AssistantController::buildNavigationPrompt()),
        // answering "where do I do X" questions instead of drafting text.
        // On a field-mode page where no mode is picked yet, it still chats
        // normally (mode "text") — it just has no field to write "Use this"
        // into until the admin picks one.
        var activeField = activeMode ? document.getElementById(activeMode.dataset.modeTarget) : null;
        var mode = !modeButtons.length ? 'nav' : (activeMode && activeMode.dataset.modeKind === 'html' ? 'html' : 'text');

        fetch(widget.dataset.aiChatUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify({
                messages: history,
                title: titleField ? titleField.value : '',
                existing_text: activeField ? activeField.value : '',
                description_context: descriptionField ? descriptionField.value : '',
                image_url: widget.dataset.aiChatImageUrl || '',
                field_label: activeMode ? (activeMode.dataset.modeFieldLabel || 'description') : '',
                mode: mode,
            }),
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                if (!result.ok || result.data.error) {
                    appendError(result.data.error || 'Nepodarilo sa získať odpoveď.');

                    return;
                }

                appendMessage('assistant', result.data.message, result.data.suggestion);
                history.push({ role: 'assistant', text: result.data.message });
            })
            .catch(function () {
                appendError('Nepodarilo sa spojiť s AI asistentom.');
            })
            .finally(function () {
                input.disabled = false;
                input.focus();
            });
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        sendMessage(input.value);
    });
});
