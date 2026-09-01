(function ($) {
    'use strict';

    $(function () {
        var $form = $('#mec-ai-task-form');
        if (!$form.length || typeof mec_ai_task_runner === 'undefined') return;

        var $task = $('#mec_ai_task_type');
        var $notice = $('#mec-ai-request-notice');
        var $submit = $form.find('.mec-ai-submit');
        var $submitTitle = $('#mec-ai-submit-title');
        var $submitCopy = $('#mec-ai-submit-copy');
        var $preview = $('#mec-ai-category-preview');
        var $previewList = $('#mec-ai-category-preview-list');
        var $result = $('#mec-ai-category-result');
        var $create = $('#mec-ai-create-categories');
        var activeTask = null;
        var defaultSubmitHtml = $submit.html();
        var defaultCreateHtml = $create.html();

        function taskOption() {
            return $task.find('option:selected');
        }

        function resetTaskPanel($panel) {
            $panel.find(':input').each(function () {
                if (this.type === 'checkbox' || this.type === 'radio') this.checked = this.defaultChecked;
                else if (this.tagName === 'SELECT') this.selectedIndex = 0;
                else this.value = '';
            });
        }

        function setTaskPanelState($panel, isActive) {
            $panel.toggleClass('is-active', isActive);
            if (isActive) $panel.removeAttr('hidden');
            else $panel.attr('hidden', 'hidden');

            $panel.find(':input').each(function () {
                var $field = $(this);
                if ($field.data('mecAiPermanentlyDisabled') === undefined) $field.data('mecAiPermanentlyDisabled', this.disabled);
                this.disabled = !isActive || $field.data('mecAiPermanentlyDisabled');
            });
        }

        function clearPreview(clearResult) {
            $preview.attr('hidden', 'hidden');
            $previewList.empty();
            if (clearResult) $result.empty().removeClass('is-success is-error');
        }

        function setNotice(type, message) {
            $notice.removeClass('is-error is-success').addClass(type ? 'is-' + type : '').text(message || '');
        }

        function showTask() {
            var task = $task.val();
            var $option = taskOption();
            var status = $option.data('status') || 'coming_soon';
            var $panels = $form.find('.mec-ai-task-panel');

            if (activeTask && activeTask !== task) resetTaskPanel($panels.filter('[data-task="' + activeTask + '"]'));
            if (activeTask !== task) clearPreview(true);

            $panels.each(function () {
                var $panel = $(this);
                setTaskPanelState($panel, $panel.data('task') === task);
            });

            activeTask = task;
            $('#mec-ai-task-description').text($option.data('description') || '');
            $submit.html(($option.data('actionLabel') || mec_ai_task_runner.coming_soon) + ' <span aria-hidden="true">→</span>');
            $submit.prop('disabled', status !== 'generate_preview');

            if (status === 'generate_preview') {
                $submitTitle.text(mec_ai_task_runner.preview_title);
                $submitCopy.text(mec_ai_task_runner.preview_copy);
            } else {
                $submitTitle.text(mec_ai_task_runner.coming_soon);
                $submitCopy.text(mec_ai_task_runner.coming_soon_copy);
            }
        }

        function selectedPayload() {
            var categories = [];
            $previewList.find('.mec-ai-category-row').each(function () {
                var $row = $(this);
                if (!$row.find('.mec-ai-category-enabled').prop('checked')) return;

                categories.push({
                    name: $.trim($row.find('.mec-ai-category-name').val() || ''),
                    description: $.trim($row.find('.mec-ai-category-description').val() || ''),
                    icon: $.trim($row.find('.mec-ai-category-icon').val() || ''),
                    color: normalizeColor($row.find('.mec-ai-category-color').val() || '')
                });
            });

            return {
                task_type: 'create_category',
                schema_version: 1,
                categories: categories
            };
        }

        function normalizeColor(value) {
            var color = $.trim(value || '');
            if (/^#[0-9a-f]{3}$/i.test(color)) color = '#' + color.charAt(1) + color.charAt(1) + color.charAt(2) + color.charAt(2) + color.charAt(3) + color.charAt(3);
            return /^#[0-9a-f]{6}$/i.test(color) ? color.toUpperCase() : color;
        }

        function initializeColorPicker($color) {
            if (!$.fn.wpColorPicker) return;

            $color.wpColorPicker({
                change: function (event, ui) {
                    if (ui && ui.color) $(event.target).val(ui.color.toString()).trigger('change');
                },
                clear: function (event) {
                    $(event.target).trigger('change');
                }
            });
        }

        function addPreviewRow(category, duplicateNames) {
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
            $fields.append($('<label>').append($('<span>').text(mec_ai_task_runner.icon)).append($icon).append($iconButton));
            $fields.append($('<label>').append($('<span>').text(mec_ai_task_runner.color)).append($color));
            $row.append($fields);
            $previewList.append($row);
            initializeColorPicker($color);
        }

        function renderPreview(payload, duplicateNames) {
            $previewList.empty();
            $result.empty().removeClass('is-success is-error');
            $.each(payload.categories || [], function (_, category) {
                addPreviewRow(category, duplicateNames || []);
            });
            $preview.removeAttr('hidden');
            $preview[0].scrollIntoView({behavior: 'smooth', block: 'start'});
        }

        function setButtonLoading($button, isLoading, html) {
            $button.prop('disabled', isLoading).toggleClass('is-loading', isLoading);
            if (!isLoading && html) $button.html(html);
        }

        function renderResults(response) {
            var $wrap = $('<div>');
            var created = response.created || [];
            var skipped = response.skipped || [];
            var failed = response.failed || [];

            if (created.length) {
                var $created = $('<p>').append($('<strong>').text(mec_ai_task_runner.created + ' (' + created.length + '): '));
                $.each(created, function (index, item) {
                    if (index) $created.append(document.createTextNode(', '));
                    $created.append($('<a>', {href: item.edit_url}).text(item.name));
                });
                $wrap.append($created);
            }
            if (skipped.length) $wrap.append($('<p>').append($('<strong>').text(mec_ai_task_runner.skipped + ' (' + skipped.length + '): ')).append(document.createTextNode(skipped.join(', '))));
            if (failed.length) {
                var messages = $.map(failed, function (item) { return item.name + ': ' + item.message; });
                $wrap.append($('<p>').append($('<strong>').text(mec_ai_task_runner.failed + ' (' + failed.length + '): ')).append(document.createTextNode(messages.join('; '))));
            }

            $result.empty().append($wrap).removeClass('is-success is-error').addClass(failed.length ? 'is-error' : 'is-success');
        }

        $task.on('change', showTask);
        showTask();

        $form.on('submit', function (event) {
            event.preventDefault();
            if ((taskOption().data('status') || '') !== 'generate_preview') return;

            var $prompt = $('#mec_ai_category_prompt');
            if (!$prompt.length || !$.trim($prompt.val() || '')) {
                setNotice('error', mec_ai_task_runner.prompt_required);
                $prompt.trigger('focus');
                return;
            }

            setNotice('', '');
            $submit.prop('disabled', true).addClass('is-loading');

            $.post(mec_ai_task_runner.ajax_url, $form.serialize() + '&action=mec_ai_generate_task_preview&nonce=' + encodeURIComponent(mec_ai_task_runner.nonce))
                .done(function (response) {
                    if (response && response.success && response.payload) {
                        renderPreview(response.payload, response.duplicate_names || []);
                        setNotice('success', response.message || mec_ai_task_runner.preview_ready);
                    } else {
                        setNotice('error', (response && response.message) || mec_ai_task_runner.error);
                    }
                })
                .fail(function () {
                    setNotice('error', mec_ai_task_runner.error);
                })
                .always(function () {
                    $submit.prop('disabled', false).removeClass('is-loading').html(defaultSubmitHtml);
                    showTask();
                });
        });

        $create.on('click', function () {
            var payload = selectedPayload();
            if (!payload.categories.length) {
                setNotice('error', mec_ai_task_runner.select_category);
                return;
            }

            setNotice('', '');
            $result.empty().removeClass('is-success is-error');
            setButtonLoading($create, true);
            $.post(mec_ai_task_runner.ajax_url, {
                action: 'mec_ai_apply_task_preview',
                nonce: mec_ai_task_runner.nonce,
                task_type: 'create_category',
                payload: JSON.stringify(payload)
            })
                .done(function (response) {
                    if (response && response.success) {
                        renderResults(response);
                        clearPreview();
                    } else setNotice('error', (response && response.message) || mec_ai_task_runner.error);
                })
                .fail(function () {
                    setNotice('error', mec_ai_task_runner.error);
                })
                .always(function () {
                    setButtonLoading($create, false, defaultCreateHtml);
                });
        });

        $(document).on('click', '.mec-ai-category-icon-picker', function () {
            var $button = $(this);
            var $target = $button.closest('.mec-ai-category-fields').find('.mec-ai-category-icon');
            var template = $('#mec-ai-icon-picker-template').html();
            if (!template) return;

            var $picker = $(template);
            $('body').append($picker);
            $picker.find('.mec-ai-icon-choice').on('click', function () {
                var icon = $(this).data('icon');
                $target.val(icon).trigger('change');
                $button.find('.mec-ai-icon-preview').attr('class', 'mec-ai-icon-preview ' + icon);
                $button.find('.mec-ai-icon-class').text(icon);
                $picker.remove();
            });
            $picker.find('.mec-ai-close-icon-picker').on('click', function () { $picker.remove(); });
            $picker.on('click', function (event) { if (event.target === this) $picker.remove(); });
            $picker.find('.mec-ai-icon-search').on('input', function () {
                var query = String($(this).val() || '').toLowerCase();
                $picker.find('.mec-ai-icon-choice').each(function () {
                    $(this).toggle(!query || String($(this).data('icon')).toLowerCase().indexOf(query) !== -1);
                });
            }).trigger('focus');
        });
    });
}(jQuery));
