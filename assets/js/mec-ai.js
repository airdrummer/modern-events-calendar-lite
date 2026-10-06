(function ($) {
    'use strict';

    $(function () {
        var $form = $('#mec-ai-task-form');
        if (!$form.length || typeof mec_ai_task_runner === 'undefined') return;

        var $tasks = $form.find('input[name="mec_ai_task[type]"]');
        var $notice = $('#mec-ai-request-notice');
        var $submit = $form.find('.mec-ai-submit');
        var $submitTitle = $('#mec-ai-submit-title');
        var $submitCopy = $('#mec-ai-submit-copy');
        var $categoryPreview = $('#mec-ai-category-preview');
        var $categoryList = $('#mec-ai-category-preview-list');
        var $eventPreview = $('#mec-ai-event-preview');
        var $eventContent = $('#mec-ai-event-preview-content');
        var $result = $('#mec-ai-category-result');
        var $createCategories = $('#mec-ai-create-categories');
        var $createEvent = $('#mec-ai-create-event');
        var activeTask = null;
        var eventPreviewToken = '';
        var eventPreviewPayload = null;
        var defaultSubmitHtml = $submit.html();
        var defaultCategoryHtml = $createCategories.html();
        var defaultEventHtml = $createEvent.html();

        function option() { return $tasks.filter(':checked'); }

        function setNotice(type, message) { $notice.removeClass('is-error is-success').addClass(type ? 'is-' + type : '').text(message || ''); }

        function resetPanel($panel) {
            $panel.find(':input').each(function () {
                if (this.type === 'checkbox' || this.type === 'radio') this.checked = this.defaultChecked;
                else if (this.tagName === 'SELECT') this.selectedIndex = 0;
                else this.value = '';
            });
        }

        function setPanelState($panel, active) {
            $panel.toggleClass('is-active', active).prop('hidden', !active);
            $panel.find(':input').each(function () {
                var $field = $(this);
                if ($field.data('mecAiDisabled') === undefined) $field.data('mecAiDisabled', this.disabled);
                this.disabled = !active || $field.data('mecAiDisabled');
            });
        }

        function clearPreviews(clearResults) {
            $categoryPreview.prop('hidden', true);
            $categoryList.empty();
            $eventPreview.prop('hidden', true);
            $eventContent.empty();
            eventPreviewToken = '';
            eventPreviewPayload = null;
            if (clearResults) $result.empty().removeClass('is-success is-error');
        }

        function showTask() {
            var $selected = option();
            var task = $selected.val() || '';
            var status = $selected.data('status') || 'coming_soon';
            var $panels = $form.find('.mec-ai-task-panel');
            $tasks.each(function () { $(this).closest('.mec-ai-task-choice').toggleClass('is-active', this.checked); });
            if (activeTask && activeTask !== task) resetPanel($panels.filter('[data-task="' + activeTask + '"]'));
            if (activeTask !== task) clearPreviews(true);
            $panels.each(function () { setPanelState($(this), $(this).data('task') === task); });
            activeTask = task;
            $('#mec-ai-task-description').text($selected.data('description') || '');
            $submit.html(($selected.data('actionLabel') || mec_ai_task_runner.coming_soon) + ' <i class="mec-sl-arrow-right" aria-hidden="true"></i>').prop('disabled', status !== 'generate_preview');
            if (status === 'generate_preview') {
                $submitTitle.text(task === 'create_event' ? mec_ai_task_runner.event_preview_title : mec_ai_task_runner.preview_title);
                $submitCopy.text(task === 'create_event' ? mec_ai_task_runner.event_preview_copy : mec_ai_task_runner.preview_copy);
            } else {
                $submitTitle.text(mec_ai_task_runner.coming_soon);
                $submitCopy.text(mec_ai_task_runner.coming_soon_copy);
            }
        }

        function normalizeColor(value) {
            var color = $.trim(value || '');
            if (/^#[0-9a-f]{3}$/i.test(color)) color = '#' + color.charAt(1) + color.charAt(1) + color.charAt(2) + color.charAt(2) + color.charAt(3) + color.charAt(3);
            return /^#[0-9a-f]{6}$/i.test(color) ? color.toUpperCase() : color;
        }

        function initializeColorPicker($color) {
            if (!$.fn.wpColorPicker) return;
            $color.wpColorPicker({change: function (event, ui) { if (ui && ui.color) $(event.target).val(ui.color.toString()).trigger('change'); }, clear: function (event) { $(event.target).trigger('change'); }});
        }

        function categoryPayload() {
            var categories = [];
            $categoryList.find('.mec-ai-category-row').each(function () {
                var $row = $(this);
                if (!$row.find('.mec-ai-category-enabled').prop('checked')) return;
                categories.push({name: $.trim($row.find('.mec-ai-category-name').val() || ''), description: $.trim($row.find('.mec-ai-category-description').val() || ''), icon: $.trim($row.find('.mec-ai-category-icon').val() || ''), color: normalizeColor($row.find('.mec-ai-category-color').val() || '')});
            });
            return {task_type: 'create_category', schema_version: 1, categories: categories};
        }

        function addCategoryRow(category, duplicateNames) {
            var duplicate = duplicateNames.indexOf(category.name) !== -1;
            var $row = $('<article>', {'class': 'mec-ai-category-row'}).toggleClass('is-duplicate', duplicate);
            var $heading = $('<div>', {'class': 'mec-ai-category-row-heading'});
            var $toggle = $('<label>', {'class': 'mec-ai-category-select'});
            var $checkbox = $('<input>', {type: 'checkbox', 'class': 'mec-ai-category-enabled'}).prop({checked: !duplicate, disabled: duplicate});
            $toggle.append($checkbox).append($('<span>').text(duplicate ? mec_ai_task_runner.duplicate : mec_ai_task_runner.include));
            $heading.append($toggle);
            if (duplicate) $heading.append($('<span>', {'class': 'mec-ai-duplicate-note'}).text(mec_ai_task_runner.duplicate_note));
            $row.append($heading);
            var $fields = $('<div>', {'class': 'mec-ai-category-fields'});
            var $name = $('<input>', {type: 'hidden', 'class': 'mec-ai-category-name'}).val(category.name);
            var $description = $('<input>', {type: 'hidden', 'class': 'mec-ai-category-description'}).val(category.description);
            var $icon = $('<input>', {type: 'hidden', 'class': 'mec-ai-category-icon'}).val(category.icon);
            var $iconButton = $('<button>', {type: 'button', 'class': 'button mec-ai-category-icon-picker'}).append($('<i>', {'class': 'mec-ai-icon-preview ' + category.icon})).append($('<span>', {'class': 'mec-ai-icon-class'}).text(category.icon));
            var $color = $('<input>', {type: 'text', 'class': 'mec-ai-category-color mec-color-picker', maxlength: 7, placeholder: '#5B6CFF'}).val(category.color);
            $fields.append($('<div>', {'class': 'mec-ai-category-generated'}).append($('<span>').text(mec_ai_task_runner.name)).append($name).append($('<strong>').text(category.name)));
            $fields.append($('<div>', {'class': 'mec-ai-category-generated mec-ai-description-field'}).append($('<span>').text(mec_ai_task_runner.description)).append($description).append($('<p>').text(category.description)));
            var $tools = $('<div>', {'class': 'mec-ai-category-tool-row'});
            $tools.append($('<label>').append($('<span>').text(mec_ai_task_runner.icon)).append($icon).append($iconButton));
            $tools.append($('<label>').append($('<span>').text(mec_ai_task_runner.color)).append($color));
            $fields.append($tools);
            $row.append($fields);
            $categoryList.append($row);
            initializeColorPicker($color);
        }

        function renderCategoryPreview(payload, duplicateNames) {
            $categoryList.empty();
            $.each(payload.categories || [], function (_, category) { addCategoryRow(category, duplicateNames || []); });
            $categoryPreview.prop('hidden', false)[0].scrollIntoView({behavior: 'smooth', block: 'start'});
        }

        function eventValue(label, value) { return value ? $('<div>', {'class': 'mec-ai-event-detail'}).append($('<strong>').text(label)).append($('<span>').text(value)) : $(); }

        function eventEditorField(label, $input, hint) {
            var $field = $('<label>', {'class': 'mec-ai-event-editor-field'}).append($('<span>').text(label)).append($input);
            if (hint) $field.append($('<small>').text(hint));
            return $field;
        }

        function eventTermsInput(field, values) {
            return $('<input>', {type: 'text', 'class': 'mec-ai-event-editor-input', 'data-event-term': field}).val((values || []).join(', '));
        }

        function renderEventEditor(event) {
            var $editor = $('<section>', {'class': 'mec-ai-event-editor'});
            $editor.append($('<h3>').text(mec_ai_task_runner.quick_edits));

            var $details = $('<div>', {'class': 'mec-ai-event-editor-grid'});
            $details.append(eventEditorField(mec_ai_task_runner.title, $('<input>', {type: 'text', 'class': 'mec-ai-event-editor-input', 'data-event-field': 'title'}).val(event.title || '')));
            $details.append(eventEditorField(mec_ai_task_runner.start, $('<div>', {'class': 'mec-ai-event-editor-date'}).append($('<input>', {type: 'date', 'data-event-field': 'start_date'}).val(event.start_date || '')).append($('<input>', {type: 'time', 'data-event-field': 'start_time'}).val(event.start_time || '')).append($('<span>').text(mec_ai_task_runner.end)).append($('<input>', {type: 'date', 'data-event-field': 'end_date'}).val(event.end_date || '')).append($('<input>', {type: 'time', 'data-event-field': 'end_time'}).val(event.end_time || ''))));
            $details.append(eventEditorField(mec_ai_task_runner.categories, eventTermsInput('categories', event.categories), mec_ai_task_runner.terms_hint));
            $details.append(eventEditorField(mec_ai_task_runner.tags, eventTermsInput('tags', event.tags), mec_ai_task_runner.terms_hint));
            $details.append(eventEditorField(mec_ai_task_runner.labels, eventTermsInput('labels', event.labels), mec_ai_task_runner.terms_hint));
            $editor.append($details);

            if ((event.tickets || []).length) {
                var $tickets = $('<div>', {'class': 'mec-ai-event-editor-tickets'}).append($('<h4>').text(mec_ai_task_runner.tickets));
                var $table = $('<div>', {'class': 'mec-ai-event-ticket-grid'});
                $.each(event.tickets, function (index, ticket) {
                    var $row = $('<div>', {'class': 'mec-ai-event-ticket-row'});
                    $row.append($('<strong>').text(ticket.name || ''));
                    $row.append(eventEditorField(mec_ai_task_runner.price, $('<input>', {type: 'number', min: 0, step: '0.01', 'data-ticket-field': 'price', 'data-ticket-index': index}).val(ticket.price || '0')));
                    $row.append(eventEditorField(mec_ai_task_runner.capacity, $('<input>', {type: 'number', min: 0, step: 1, 'data-ticket-field': 'limit', 'data-ticket-index': index}).val(ticket.limit || '0')));
                    $table.append($row);
                });
                $tickets.append($table);
                $editor.append($tickets);
            }

            return $editor;
        }

        function splitEventTerms(value) {
            return $.map(String(value || '').split(','), function (term) { term = $.trim(term); return term || null; });
        }

        function reviewedEventPayload() {
            if (!eventPreviewPayload || !eventPreviewPayload.event) return null;
            var payload = $.extend(true, {}, eventPreviewPayload);
            var event = payload.event;
            $eventContent.find('[data-event-field]').each(function () { event[$(this).data('eventField')] = $.trim($(this).val() || ''); });
            $eventContent.find('[data-event-term]').each(function () { event[$(this).data('eventTerm')] = splitEventTerms($(this).val()); });
            $eventContent.find('[data-ticket-field]').each(function () {
                var $field = $(this);
                var ticket = event.tickets[parseInt($field.data('ticketIndex'), 10)];
                if (ticket) ticket[$field.data('ticketField')] = $.trim($field.val() || '');
            });
            return payload;
        }

        function renderEventPreview(payload, image, warnings) {
            var event = payload.event || {};
            $eventContent.empty();
            $eventContent.append(renderEventEditor(event));
            var $top = $('<div>', {'class': 'mec-ai-event-summary'});
            if (event.content) $top.append($('<div>', {'class': 'mec-ai-event-content'}).html(event.content));
            $top.append(eventValue(mec_ai_task_runner.location, event.location && event.location.name ? event.location.name + (event.location.address ? ' · ' + event.location.address : '') : ''));
            $top.append(eventValue(mec_ai_task_runner.organizer, event.organizer && event.organizer.name ? event.organizer.name : ''));
            if (image && image.url) $top.append($('<figure>', {'class': 'mec-ai-event-image'}).append($('<img>', {src: image.url, alt: image.alt_text || event.title || ''})));
            $eventContent.append($top);
            if ((event.hourly_schedule || []).length) {
                var $schedule = $('<section>', {'class': 'mec-ai-event-list'}).append($('<h3>').text(mec_ai_task_runner.hourly_schedule));
                $.each(event.hourly_schedule, function (_, item) { $schedule.append($('<p>').text(item.start_time + ' — ' + item.end_time + ' · ' + item.title + (item.description ? ': ' + item.description : ''))); });
                $eventContent.append($schedule);
            }
            if ((warnings || []).length) {
                var $warnings = $('<div>', {'class': 'mec-ai-event-warnings'});
                $.each(warnings, function (_, warning) { $warnings.append($('<p>').text(warning)); });
                $eventContent.append($warnings);
            }
            $eventPreview.prop('hidden', false)[0].scrollIntoView({behavior: 'smooth', block: 'start'});
        }

        function setButtonLoading($button, loading, html) { $button.prop('disabled', loading).toggleClass('is-loading', loading); if (!loading && html) $button.html(html); }

        function renderCategoryResults(response) {
            var $wrap = $('<div>');
            var created = response.created || [];
            var skipped = response.skipped || [];
            var failed = response.failed || [];
            if (created.length) {
                var $created = $('<p>').append($('<strong>').text(mec_ai_task_runner.created + ' (' + created.length + '): '));
                $.each(created, function (index, item) { if (index) $created.append(document.createTextNode(', ')); $created.append($('<a>', {href: item.edit_url}).text(item.name)); });
                $wrap.append($created);
            }
            if (skipped.length) $wrap.append($('<p>').append($('<strong>').text(mec_ai_task_runner.skipped + ' (' + skipped.length + '): ')).append(document.createTextNode(skipped.join(', '))));
            if (failed.length) $wrap.append($('<p>').append($('<strong>').text(mec_ai_task_runner.failed + ' (' + failed.length + '): ')).append(document.createTextNode($.map(failed, function (item) { return item.name + ': ' + item.message; }).join('; '))));
            $result.empty().append($wrap).removeClass('is-success is-error').addClass(failed.length ? 'is-error' : 'is-success');
        }

        function renderEventResult(response) {
            var $wrap = $('<p>');
            if (response.event && response.event.edit_url) $wrap.append($('<strong>').text(response.message || '')).append(' ').append($('<a>', {href: response.event.edit_url}).text(response.event.title));
            else $wrap.text(response.message || mec_ai_task_runner.error);
            $.each(response.warnings || [], function (_, warning) { $wrap.append($('<br>')).append(document.createTextNode(warning)); });
            $result.empty().append($wrap).removeClass('is-error').addClass('is-success');
        }

        $tasks.on('change', showTask);
        showTask();

        $form.on('submit', function (event) {
            event.preventDefault();
            if ((option().data('status') || '') !== 'generate_preview') return;
            var currentTask = option().val();
            var $prompt = currentTask === 'create_event' ? $('#mec_ai_event_prompt') : $('#mec_ai_category_prompt');
            if (!$prompt.length || !$.trim($prompt.val() || '')) { setNotice('error', currentTask === 'create_event' ? mec_ai_task_runner.event_prompt_required : mec_ai_task_runner.prompt_required); $prompt.trigger('focus'); return; }
            clearPreviews(true);
            setNotice('', '');
            $submit.prop('disabled', true).addClass('is-loading');
            $.post(mec_ai_task_runner.ajax_url, $form.serialize() + '&action=mec_ai_generate_task_preview&nonce=' + encodeURIComponent(mec_ai_task_runner.nonce)).done(function (response) {
                if (!response || !response.success || !response.payload) { setNotice('error', (response && response.message) || mec_ai_task_runner.error); return; }
                if (currentTask === 'create_event') { eventPreviewToken = response.preview_token || ''; eventPreviewPayload = response.payload; renderEventPreview(response.payload, response.image || {}, response.warnings || []); }
                else renderCategoryPreview(response.payload, response.duplicate_names || []);
                setNotice('success', response.message || mec_ai_task_runner.preview_ready);
            }).fail(function () { setNotice('error', mec_ai_task_runner.error); }).always(function () { $submit.prop('disabled', false).removeClass('is-loading').html(defaultSubmitHtml); showTask(); });
        });

        $createCategories.on('click', function () {
            var payload = categoryPayload();
            if (!payload.categories.length) { setNotice('error', mec_ai_task_runner.select_category); return; }
            setNotice('', '');
            $result.empty().removeClass('is-success is-error');
            setButtonLoading($createCategories, true);
            $.post(mec_ai_task_runner.ajax_url, {action: 'mec_ai_apply_task_preview', nonce: mec_ai_task_runner.nonce, task_type: 'create_category', payload: JSON.stringify(payload)}).done(function (response) {
                if (response && response.success) { renderCategoryResults(response); $categoryPreview.prop('hidden', true); $categoryList.empty(); }
                else setNotice('error', (response && response.message) || mec_ai_task_runner.error);
            }).fail(function () { setNotice('error', mec_ai_task_runner.error); }).always(function () { setButtonLoading($createCategories, false, defaultCategoryHtml); });
        });

        $createEvent.on('click', function () {
            if (!eventPreviewToken) { setNotice('error', mec_ai_task_runner.event_preview_required); return; }
            var payload = reviewedEventPayload();
            if (!payload) { setNotice('error', mec_ai_task_runner.event_preview_required); return; }
            setNotice('', '');
            $result.empty().removeClass('is-success is-error');
            setButtonLoading($createEvent, true);
            $.post(mec_ai_task_runner.ajax_url, {action: 'mec_ai_apply_task_preview', nonce: mec_ai_task_runner.nonce, task_type: 'create_event', preview_token: eventPreviewToken, payload: JSON.stringify(payload)}).done(function (response) {
                if (response && response.success) { renderEventResult(response); $eventPreview.prop('hidden', true); $eventContent.empty(); eventPreviewToken = ''; eventPreviewPayload = null; }
                else setNotice('error', (response && response.message) || mec_ai_task_runner.error);
            }).fail(function () { setNotice('error', mec_ai_task_runner.error); }).always(function () { setButtonLoading($createEvent, false, defaultEventHtml); });
        });

        $(document).on('click', '.mec-ai-category-icon-picker', function () {
            var $button = $(this);
            var $target = $button.closest('.mec-ai-category-fields').find('.mec-ai-category-icon');
            var template = $('#mec-ai-icon-picker-template').html();
            if (!template) return;
            var $picker = $(template);
            $('body').append($picker);
            $picker.find('.mec-ai-icon-choice').on('click', function () { var icon = $(this).data('icon'); $target.val(icon).trigger('change'); $button.find('.mec-ai-icon-preview').attr('class', 'mec-ai-icon-preview ' + icon); $button.find('.mec-ai-icon-class').text(icon); $picker.remove(); });
            $picker.find('.mec-ai-close-icon-picker').on('click', function () { $picker.remove(); });
            $picker.on('click', function (event) { if (event.target === this) $picker.remove(); });
            $picker.find('.mec-ai-icon-search').on('input', function () { var query = String($(this).val() || '').toLowerCase(); $picker.find('.mec-ai-icon-choice').each(function () { $(this).toggle(!query || String($(this).data('icon')).toLowerCase().indexOf(query) !== -1); }); }).trigger('focus');
        });
    });
}(jQuery));
