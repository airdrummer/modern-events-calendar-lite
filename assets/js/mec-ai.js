(function ($) {
    'use strict';

    $(function () {
        if (typeof mec_ai_task_runner === 'undefined') return;

        $('.mec-ai-runner').each(function () {
        var $root = $(this);
        var $form = $root.find('.mec-ai-task-form');
        if (!$form.length) return;
        var $tasks = $form.find('input[name="mec_ai_task[type]"]');
        var $notice = $root.find('.mec-ai-request-notice');
        var $submit = $form.find('.mec-ai-submit');
        var $submitTitle = $root.find('.mec-ai-submit-title');
        var $submitCopy = $root.find('.mec-ai-submit-copy');
        var $categoryPreview = $root.find('.mec-ai-category-preview-main');
        var $categoryList = $root.find('.mec-ai-category-preview-list').first();
        var $eventPreview = $root.find('.mec-ai-event-preview');
        var $eventContent = $root.find('.mec-ai-event-preview-content');
        var $result = $root.find('.mec-ai-category-result');
        var $createCategories = $root.find('.mec-ai-create-categories');
        var $createEvent = $root.find('.mec-ai-create-event');
        var activeTask = null;
        var eventPreviewToken = '';
        var eventPreviewPayload = null;
        var defaultSubmitHtml = $submit.html();
        var defaultCategoryHtml = $createCategories.html();
        var defaultEventHtml = $createEvent.html();
        var taxonomyTasks = mec_ai_task_runner.taxonomy_tasks || {};
        var $termPreview = $root.find('#mec-ai-taxonomy-preview');
        var $termList = $root.find('.mec-ai-taxonomy-preview-list');
        var $createTerms = $root.find('.mec-ai-create-terms');
        var termPreviewToken = '';
        var previewVersion = 0;
        var generationPending = false;
        var imagePending = false;
        var termsPending = false;

        function option() { return $tasks.filter(':checked, [type="hidden"]').first(); }

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
            previewVersion++;
            termPreviewToken = '';
            $termPreview.prop('hidden', true);
            $termList.empty();
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
            $root.find('#mec-ai-task-description').text($selected.data('description') || '');
            $submit.html(($selected.data('actionLabel') || mec_ai_task_runner.coming_soon) + ' <i class="mec-sl-arrow-right" aria-hidden="true"></i>').prop('disabled', status !== 'generate_preview' || generationPending || termsPending);
            if (status === 'generate_preview') {
                $submitTitle.text(taxonomyTasks[task] ? mec_ai_task_runner.taxonomy_preview_title : (task === 'create_event' ? mec_ai_task_runner.event_preview_title : mec_ai_task_runner.preview_title));
                $submitCopy.text(taxonomyTasks[task] ? mec_ai_task_runner.taxonomy_preview_copy : (task === 'create_event' ? mec_ai_task_runner.event_preview_copy : mec_ai_task_runner.preview_copy));
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

        function updateTermButtons() {
            $createTerms.prop('disabled', imagePending || termsPending);
            $termList.find('.mec-ai-generate-image, .mec-ai-remove-image').prop('disabled', imagePending || termsPending);
        }

        function renderTermPreview(response) {
            var config = taxonomyTasks[response.payload.task_type];
            if (!config) return;
            termPreviewToken = response.preview_token;
            $termList.empty();
            $.each(response.payload.items, function (_, item) {
                var duplicate = (response.duplicate_names || []).indexOf(item.name) !== -1;
                var $row = $('<article>', {'class': 'mec-ai-category-row'}).data('item', item).toggleClass('is-duplicate', duplicate);
                var $check = $('<input>', {type: 'checkbox', 'class': 'mec-ai-term-enabled'}).prop({checked: !duplicate, disabled: duplicate});
                $row.append($('<div>', {'class': 'mec-ai-category-row-heading'}).append($('<label>', {'class': 'mec-ai-category-select'}).append($check).append($('<span>').text(duplicate ? mec_ai_task_runner.duplicate_note : mec_ai_task_runner.include))));
                var $fields = $('<div>', {'class': 'mec-ai-category-fields'});
                $fields.append($('<div>', {'class': 'mec-ai-category-generated'}).append($('<span>').text(mec_ai_task_runner.name)).append($('<strong>').text(item.name)));
                $fields.append($('<div>', {'class': 'mec-ai-category-generated'}).append($('<span>').text(mec_ai_task_runner.description)).append($('<p>').text(item.description)));
                var $grid = $('<div>', {'class': 'mec-ai-taxonomy-field-grid'});
                $.each(config.fields, function (key, field) {
                    var $input;
                    if (field.type === 'select') {
                        $input = $('<select>');
                        $.each(field.options, function (value, label) { $input.append($('<option>', {value: value}).text(label)); });
                    } else {
                        $input = $('<input>', {type: field.type === 'color' ? 'text' : field.type});
                        if (field.type === 'number') $input.attr({min: field.min, max: field.max, step: 'any'});
                    }
                    $input.attr('data-term-field', key).addClass('mec-ai-event-editor-input').val(item[key]).prop('disabled', duplicate);
                    if (field.type === 'color') $input.addClass('mec-ai-category-color');
                    $grid.append($('<label>').append($('<span>').text(field.label)).append($input));
                });
                $fields.append($grid);
                if (config.image_meta && !duplicate) {
                    var $image = $('<div>', {'class': 'mec-ai-taxonomy-image'});
                    if (mec_ai_task_runner.taxonomy_images_available) {
                        $image.append($('<label>').append($('<span>').text(mec_ai_task_runner.image_prompt)).append($('<input>', {type: 'text', maxlength: 2000, 'class': 'mec-ai-image-prompt mec-ai-event-editor-input'})));
                        $image.append($('<img>', {alt: item.name, hidden: true}));
                        $image.append($('<button>', {type: 'button', 'class': 'button mec-ai-generate-image'}).text(mec_ai_task_runner.generate_image));
                        $image.append($('<button>', {type: 'button', 'class': 'button mec-ai-remove-image', hidden: true}).text(mec_ai_task_runner.remove_image));
                        $image.append($('<p>', {'class': 'mec-ai-image-status', role: 'status', 'aria-live': 'polite'}));
                    } else $image.append($('<p>').text(mec_ai_task_runner.taxonomy_image_unavailable));
                    $fields.append($image);
                }
                $row.append($fields);
                $termList.append($row);
                if (!duplicate) $row.find('[data-term-field="color"]').each(function () { initializeColorPicker($(this)); });
            });
            updateTermButtons();
            $termPreview.prop('hidden', false)[0].scrollIntoView({behavior: 'smooth', block: 'start'});
        }

        $termList.on('click', '.mec-ai-generate-image', function () {
            if (imagePending || termsPending || !termPreviewToken) return;
            var $row = $(this).closest('.mec-ai-category-row');
            var item = $row.data('item');
            var version = previewVersion;
            imagePending = true;
            updateTermButtons();
            $row.find('.mec-ai-image-status').empty();
            $(this).addClass('is-loading');
            $.post(mec_ai_task_runner.ajax_url, {
                action: 'mec_ai_generate_taxonomy_image', nonce: mec_ai_task_runner.nonce, task_type: activeTask,
                preview_token: termPreviewToken, item_id: item.id, image_prompt: $row.find('.mec-ai-image-prompt').val()
            }).done(function (response) {
                if (version !== previewVersion) return;
                if (!response || !response.success || !response.image) { $row.find('.mec-ai-image-status').text((response && response.message) || mec_ai_task_runner.error); return; }
                $row.data('hasImage', true).find('img').attr({src: response.image.url, alt: response.image.alt_text}).prop('hidden', false);
                $row.find('.mec-ai-generate-image').text(mec_ai_task_runner.regenerate_image);
                $row.find('.mec-ai-remove-image').prop('hidden', false);
            }).fail(function () {
                if (version === previewVersion) $row.find('.mec-ai-image-status').text(mec_ai_task_runner.error);
            }).always(function () {
                imagePending = false;
                $row.find('.mec-ai-generate-image').removeClass('is-loading');
                updateTermButtons();
            });
        });

        $termList.on('click', '.mec-ai-remove-image', function () {
            if (imagePending || termsPending) return;
            var $row = $(this).closest('.mec-ai-category-row');
            $row.data('hasImage', false).find('img').removeAttr('src').prop('hidden', true);
            $row.find('.mec-ai-generate-image').text(mec_ai_task_runner.generate_image);
            $(this).prop('hidden', true);
        });

        $createTerms.on('click', function () {
            if (imagePending || termsPending || !termPreviewToken) return;
            var items = [];
            $termList.find('.mec-ai-category-row').each(function () {
                var $row = $(this);
                if (!$row.find('.mec-ai-term-enabled').prop('checked')) return;
                var item = $.extend({}, $row.data('item'), {include_image: !!$row.data('hasImage')});
                $row.find('[data-term-field]').each(function () { item[$(this).data('termField')] = $(this).val(); });
                items.push(item);
            });
            if (!items.length) { setNotice('error', mec_ai_task_runner.select_terms); return; }
            var version = previewVersion;
            termsPending = true;
            updateTermButtons();
            $submit.prop('disabled', true);
            $createTerms.addClass('is-loading');
            $.post(mec_ai_task_runner.ajax_url, {
                action: 'mec_ai_apply_task_preview', nonce: mec_ai_task_runner.nonce, task_type: activeTask,
                preview_token: termPreviewToken, payload: JSON.stringify({task_type: activeTask, schema_version: 1, items: items})
            }).done(function (response) {
                if (version !== previewVersion) return;
                if (!response || !response.success) { setNotice('error', (response && response.message) || mec_ai_task_runner.error); return; }
                setNotice('', '');
                renderCategoryResults(response);
                var completed = $.map(response.created || [], function (item) { return item.id; });
                $termList.find('.mec-ai-category-row').each(function () {
                    var item = $(this).data('item');
                    if (completed.indexOf(item.id) !== -1 || (response.skipped || []).indexOf(item.name) !== -1) $(this).remove();
                });
                if (!$termList.find('.mec-ai-category-row').length) $termPreview.prop('hidden', true);
            }).fail(function () {
                if (version === previewVersion) setNotice('error', mec_ai_task_runner.error);
            }).always(function () {
                termsPending = false;
                $createTerms.removeClass('is-loading');
                updateTermButtons();
                showTask();
            });
        });

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
            $.each(response.warnings || [], function (_, warning) { $wrap.append($('<p>').text(warning)); });
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

        function generatePreview(event) {
            event.preventDefault();
            if (generationPending || termsPending || (option().data('status') || '') !== 'generate_preview') return;
            var currentTask = option().val();
            var $prompt = taxonomyTasks[currentTask] ? $root.find('#mec_ai_' + currentTask + '_prompt') : (currentTask === 'create_event' ? $root.find('.mec-ai-event-prompt') : $root.find('#mec_ai_category_prompt'));
            if (!$prompt.length || !$.trim($prompt.val() || '')) { setNotice('error', taxonomyTasks[currentTask] ? mec_ai_task_runner.taxonomy_prompt_required : (currentTask === 'create_event' ? mec_ai_task_runner.event_prompt_required : mec_ai_task_runner.prompt_required)); $prompt.trigger('focus'); return; }
            clearPreviews(true);
            var version = previewVersion;
            generationPending = true;
            setNotice('', '');
            $submit.prop('disabled', true).addClass('is-loading');
            var formData = $form.is('form') ? $form.serialize() : $form.find(':input').serialize();
            $.post(mec_ai_task_runner.ajax_url, formData + '&action=mec_ai_generate_task_preview&nonce=' + encodeURIComponent(mec_ai_task_runner.nonce)).done(function (response) {
                if (version !== previewVersion) return;
                if (!response || !response.success || !response.payload) { setNotice('error', (response && response.message) || mec_ai_task_runner.error); return; }
                if (currentTask === 'create_event') { eventPreviewToken = response.preview_token || ''; eventPreviewPayload = response.payload; renderEventPreview(response.payload, response.image || {}, response.warnings || []); }
                else if (taxonomyTasks[currentTask]) renderTermPreview(response);
                else renderCategoryPreview(response.payload, response.duplicate_names || []);
                setNotice('success', response.message || mec_ai_task_runner.preview_ready);
            }).fail(function () { if (version === previewVersion) setNotice('error', mec_ai_task_runner.error); }).always(function () { generationPending = false; $submit.prop('disabled', false).removeClass('is-loading').html(defaultSubmitHtml); showTask(); });
        }

        $form.on('submit', generatePreview);
        $form.on('click', '.mec-ai-generate-preview', generatePreview);

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
            var version = previewVersion;
            setNotice('', '');
            $result.empty().removeClass('is-success is-error');
            setButtonLoading($createEvent, true);
            $.post(mec_ai_task_runner.ajax_url, {action: 'mec_ai_apply_task_preview', nonce: mec_ai_task_runner.nonce, task_type: 'create_event', preview_token: eventPreviewToken, payload: JSON.stringify(payload)}).done(function (response) {
                if (version !== previewVersion) return;
                if (response && response.success) {
                    if ($root.data('redirectOnCreate') && !(response.warnings || []).length && response.event && response.event.edit_url) {
                        window.location.assign(response.event.edit_url);
                        return;
                    }

                    renderEventResult(response);
                    $eventPreview.prop('hidden', true);
                    $eventContent.empty();
                    eventPreviewToken = '';
                    eventPreviewPayload = null;
                }
                else setNotice('error', (response && response.message) || mec_ai_task_runner.error);
            }).fail(function () { if (version === previewVersion) setNotice('error', mec_ai_task_runner.error); }).always(function () { if (version === previewVersion) setButtonLoading($createEvent, false, defaultEventHtml); });
        });

        $root.on('click', '.mec-ai-category-icon-picker', function () {
            var $button = $(this);
            var $target = $button.closest('.mec-ai-category-fields').find('.mec-ai-category-icon');
            var template = $root.find('.mec-ai-icon-picker-template').html();
            if (!template) return;
            var $picker = $(template);
            $('body').append($picker);
            $picker.find('.mec-ai-icon-choice').on('click', function () { var icon = $(this).data('icon'); $target.val(icon).trigger('change'); $button.find('.mec-ai-icon-preview').attr('class', 'mec-ai-icon-preview ' + icon); $button.find('.mec-ai-icon-class').text(icon); $picker.remove(); });
            $picker.find('.mec-ai-close-icon-picker').on('click', function () { $picker.remove(); });
            $picker.on('click', function (event) { if (event.target === this) $picker.remove(); });
            $picker.find('.mec-ai-icon-search').on('input', function () { var query = String($(this).val() || '').toLowerCase(); $picker.find('.mec-ai-icon-choice').each(function () { $(this).toggle(!query || String($(this).data('icon')).toLowerCase().indexOf(query) !== -1); }); }).trigger('focus');
        });

        $root.data('mecAiReset', function () {
            if ($form.is('form')) $form[0].reset();
            else $form.find('textarea').val('');
            clearPreviews(true);
            setButtonLoading($createEvent, false, defaultEventHtml);
            setNotice('', '');
            showTask();
        });
        });

        $('.mec-ai-event-modal').each(function () {
            var $modal = $(this);
            var modalId = $modal.attr('id');
            var $trigger = $('[data-mec-ai-modal="' + modalId + '"]');
            if (!$.fn.dialog || !$trigger.length) return;

            $modal.dialog({
                autoOpen: false,
                modal: true,
                width: 720,
                maxHeight: Math.round(window.innerHeight * 0.88),
                position: {my: 'center top', at: 'center top+5%', of: window, collision: 'fit'},
                dialogClass: 'mec-ai-dialog mec-ds mec-ai-v2',
                close: function () {
                    var reset = $modal.find('.mec-ai-runner').data('mecAiReset');
                    if (reset) reset();
                },
            });
            $trigger.on('click', function () { $modal.dialog('open'); });
        });
    });
}(jQuery));
