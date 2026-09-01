// Set datepicker default value.
var datepicker_format = 'yy-mm-dd';

function mecEnhanceDatepickerAccessibility($inputs)
{
    if(!$inputs || !$inputs.length) return;

    $inputs.attr('aria-haspopup', 'dialog');
    $inputs.off('focus.mecDatepickerA11y').on('focus.mecDatepickerA11y', function()
    {
        window.setTimeout(function()
        {
            var $datepicker = jQuery('#ui-datepicker-div');
            if(!$datepicker.length) return;

            $datepicker.attr({
                role: 'dialog',
                'aria-live': 'polite',
                'aria-label': (typeof mecdata !== 'undefined' && mecdata.a11y_calendar_dialog) ? mecdata.a11y_calendar_dialog : 'Calendar date picker'
            });
        }, 0);
    });
}

jQuery(document).ready(function($)
{
    // Image picker on terms menu
    // Image picker on terms menu
    // Several buttons share this handler with different data attributes, so the
    // select callback must close over the CURRENT click's IDs — dispose the old
    // frame and rebuild on each click (reusing one frame would keep stale IDs).
    var mecMediaFrame;
    $('.mec_upload_image_button').click(function(event)
    {
        event.preventDefault();

        if(wp.ajax.settings.url.indexOf('mec_fes=1') === -1) wp.ajax.settings.url = wp.ajax.settings.url + '?mec_fes=1';

        var post_id = $(this).data('post-id');
        if(post_id && post_id !== -1) wp.media.model.settings.post.id = post_id;
        if(post_id === -1) wp.media.model.settings.post.id = null;

        var preview_id = 'mec_thumbnail_img';
        var input_id = 'mec_thumbnail';

        if($(this).data('preview-id')) preview_id = $(this).data('preview-id');
        if($(this).data('input-id')) input_id = $(this).data('input-id');

        if(mecMediaFrame) mecMediaFrame.dispose();

        mecMediaFrame = wp.media();
        mecMediaFrame.on('select', function()
        {
            // Grab the selected attachment.
            var attachment = mecMediaFrame.state().get('selection').first();

            $('#'+preview_id).html('<img src="'+attachment.attributes.url+'" alt="Featured image preview" />');
            $('#'+input_id).val(attachment.attributes.url);

            // Toggle only this pair's remove button (several upload/remove pairs can coexist on one page)
            $('.mec_remove_image_button').filter(function()
            {
                return (($(this).data('preview-id') || 'mec_thumbnail_img') === preview_id);
            }).removeClass('mec-util-hidden');

            // Banner row: hide the "Choose image" button while an image is set
            if(preview_id === 'mec_banner_thumbnail_img') mecSyncThumbnailButtons('banner');

            mecMediaFrame.close();
        });

        mecMediaFrame.open();
    });

    // Image remover on terms menu
    $('.mec_remove_image_button').click(function(event)
    {
        event.preventDefault();

        var preview_id = 'mec_thumbnail_img';
        var input_id = 'mec_thumbnail';

        if($(this).data('preview-id')) preview_id = $(this).data('preview-id');
        if($(this).data('input-id')) input_id = $(this).data('input-id');

        $('#'+preview_id).html('');
        $('#'+input_id).val('');

        $('.mec_remove_image_button').filter(function()
        {
            return (($(this).data('preview-id') || 'mec_thumbnail_img') === preview_id);
        }).addClass('mec-util-hidden');

        // Banner row: bring back the "Choose image" button
        if(preview_id === 'mec_banner_thumbnail_img') mecSyncThumbnailButtons('banner');
    });

    // Keep thumbnail "Choose image" / "Remove image" buttons in sync with the current image state
    function mecSyncThumbnailButtons(which)
    {
        var hasImage = $.trim($('#mec_' + which + '_thumbnail').val()) !== '' || $('#mec_' + which + '_thumbnail_img').children('img').length > 0;
        // Dedicated remove button (.mec_{which}_remove_image_button) or the generic
        // pair (.mec_remove_image_button[data-preview-id]) — the popup has neither.
        var $remove = $('.mec_' + which + '_remove_image_button');
        if(!$remove.length) $remove = $('.mec_remove_image_button').filter(function()
        {
            return (($(this).data('preview-id') || 'mec_thumbnail_img') === 'mec_' + which + '_thumbnail_img');
        });

        $remove.toggleClass('mec-util-hidden', !hasImage);
        $('#mec_' + which + '_thumbnail_button').toggleClass('mec-util-hidden', hasImage && $remove.length > 0);
    }

    // Image picker on add event menu for location
    var mecLocationMediaFrame;
    $('.mec_location_upload_image_button').click(function(event)
    {
        event.preventDefault();

        // Reuse the open frame across clicks (IDs are fixed for this pair)
        if(mecLocationMediaFrame)
        {
            mecLocationMediaFrame.open();
            return;
        }

        mecLocationMediaFrame = wp.media();
        mecLocationMediaFrame.on('select', function()
        {
            // Grab the selected attachment.
            var attachment = mecLocationMediaFrame.state().get('selection').first();

            $('#mec_location_thumbnail_img').html('<img src="'+attachment.attributes.url+'" />');
            $('#mec_location_thumbnail').val(attachment.attributes.url);

            mecSyncThumbnailButtons('location');

            mecLocationMediaFrame.close();
        });

        mecLocationMediaFrame.open();
    });

    // Image remover on add event menu for location
    $('.mec_location_remove_image_button').click(function(event)
    {
        event.preventDefault();

        $('#mec_location_thumbnail_img').html('');
        $('#mec_location_thumbnail').val('');

        mecSyncThumbnailButtons('location');
    });

    // Image picker on add event menu for organizer
    var mecOrganizerMediaFrame;
    $('.mec_organizer_upload_image_button').click(function(event)
    {
        event.preventDefault();

        // Reuse the open frame across clicks (IDs are fixed for this pair)
        if(mecOrganizerMediaFrame)
        {
            mecOrganizerMediaFrame.open();
            return;
        }

        mecOrganizerMediaFrame = wp.media();
        mecOrganizerMediaFrame.on('select', function()
        {
            // Grab the selected attachment.
            var attachment = mecOrganizerMediaFrame.state().get('selection').first();

            $('#mec_organizer_thumbnail_img').html('<img src="'+attachment.attributes.url+'" />');
            $('#mec_organizer_thumbnail').val(attachment.attributes.url);

            mecSyncThumbnailButtons('organizer');

            mecOrganizerMediaFrame.close();
        });

        mecOrganizerMediaFrame.open();
    });

    // Image remover on add event menu for organizer
    $('.mec_organizer_remove_image_button').click(function(event)
    {
        event.preventDefault();

        $('#mec_organizer_thumbnail_img').html('');
        $('#mec_organizer_thumbnail').val('');

        mecSyncThumbnailButtons('organizer');
    });

    // Initial state sync (e.g. edit forms pre-populated by other scripts)
    mecSyncThumbnailButtons('location');
    mecSyncThumbnailButtons('organizer');
    mecSyncThumbnailButtons('banner');

    // Image remover on frontend event submission menu
    $('#mec_fes_remove_image_button').click(function(event)
    {
        event.preventDefault();

        $('#mec_fes_thumbnail_img').html('');
        $('#mec_fes_thumbnail').val('');
        $('#mec_featured_image_file').val('');

        $('#mec_fes_remove_image_button').addClass('mec-util-hidden');
    });

    // Location Image remover on frontend event submission menu
    $('#mec_fes_location_remove_image_button').click(function(event)
    {
        event.preventDefault();

        $('#mec_fes_location_thumbnail_img').html('');
        $('#mec_fes_location_thumbnail').val('');
        $('#mec_fes_location_thumbnail_file').val('');

        $('#mec_fes_location_remove_image_button').addClass('mec-util-hidden');
    });

    // Organizer Image remover on frontend event submission menu
    $('#mec_fes_organizer_remove_image_button').click(function(event)
    {
        event.preventDefault();

        $('#mec_fes_organizer_thumbnail_img').html('');
        $('#mec_fes_organizer_thumbnail').val('');
        $('#mec_fes_organizer_thumbnail_file').val('');

        $('#mec_fes_organizer_remove_image_button').addClass('mec-util-hidden');
    });

    // Sponsor Image remover on frontend event submission menu
    $('#mec_fes_sponsor_remove_image_button').click(function(event)
    {
        event.preventDefault();

        $('#mec_fes_sponsor_thumbnail_img').html('');
        $('#mec_fes_sponsor_thumbnail').val('');
        $('#mec_fes_sponsor_thumbnail_file').val('');

        $('#mec_fes_sponsor_remove_image_button').addClass('mec-util-hidden');
    });

    // Speaker Image remover on frontend event submission menu
    $('#mec_fes_speaker_remove_image_button').click(function(event)
    {
        event.preventDefault();

        $('#mec_fes_speaker_thumbnail_img').html('');
        $('#mec_fes_speaker_thumbnail').val('');
        $('#mec_fes_speaker_thumbnail_file').val('');

        $('#mec_fes_speaker_remove_image_button').addClass('mec-util-hidden');
    });

    var date_splite;
    if(typeof mec_admin_localize !== 'undefined')
    {
        date_splite = mec_admin_localize.datepicker_format.split('&');
        if(date_splite[0] !== undefined && date_splite.length == 2) datepicker_format = date_splite[0];
    }
    else if(typeof mecdata !== 'undefined')
    {
        date_splite = mecdata.datepicker_format.split( '&' );
        if(date_splite[0] !== undefined && date_splite.length == 2) datepicker_format = date_splite[0];
    }

    $('[id^="mec-book-form-btn-step"]').on('click',function()
    {
        setTimeout(function()
        {
            if($.fn.datepicker)
            {
                if('date' !== $('.mec-date-picker').prop('type'))
                {
                    $('.mec-date-picker').datepicker(
                    {
                        changeYear: true,
                        changeMonth: true,
                        dateFormat: datepicker_format,
                        gotoCurrent: true,
                        yearRange: 'c-3:c+5',
                    });
                }

            }
        }, 1000);
    });

    if($.fn.datepicker)
    {
        mecEnhanceDatepickerAccessibility($('.mec-date-picker, .mec_date_picker_dynamic_format, .mec_date_picker, .mec_date_picker_dynamic_format_start, .mec_date_picker_dynamic_format_end, #mec_start_date, #mec_end_date, #mec_date_repeat_end_at_date'));

        $('.mec-date-picker').datepicker(
        {
            changeYear: true,
            changeMonth: true,
            dateFormat: datepicker_format,
            gotoCurrent: true,
            yearRange: 'c-3:c+5',
        });

        $('#mec_start_date').datepicker(
        {
            changeYear: true,
            changeMonth: true,
            dateFormat: datepicker_format,
            gotoCurrent: true,
            yearRange: 'c-3:c+5',
            onSelect: function(value, i)
            {
                const end = $("#mec_end_date");
                if(value !== i.lastVal)
                {
                    end.datepicker("option", "minDate", value);
                }

                if(end.val() === '')
                {
                    end.val(value);
                }

                // Datepicker doesn't natively fire `change` on the input, so
                // trigger it to clear inline validation errors (audit issue #1).
                $(this).trigger('change');
            }
        });

        $('#mec_end_date').datepicker(
        {
            changeYear: true,
            changeMonth: true,
            dateFormat: datepicker_format,
            gotoCurrent: true,
            yearRange: 'c-3:c+5',
            onSelect: function(value, i)
            {
                const start = $("#mec_start_date");
                if(value !== i.lastVal)
                {
                    start.datepicker("option", "maxDate", value);
                }

                if(start.val() === '')
                {
                    start.val(value);
                }

                // Datepicker doesn't natively fire `change` on the input, so
                // trigger it to clear inline validation errors (audit issue #1).
                $(this).trigger('change');
            }
        });

        // ===== Combobox time control (audit R3) =====
        // A typeable field; typing filters a dropdown of every 5-minute slot.
        // The hidden [hour][minutes][ampm] fields stay in sync (looked up LOCALLY
        // inside the wrapper, so cloned rows — tickets — keep working). Save is
        // unchanged.

        function mecTimeLabel(h24, min, is12)
        {
            var pad = function(n){ return (n < 10 ? '0' : '') + n; };
            if(is12)
            {
                var h12 = h24 % 12; if(h12 === 0) h12 = 12;
                return pad(h12) + ':' + pad(min) + ' ' + (h24 < 12 ? 'AM' : 'PM');
            }
            return pad(h24) + ':' + pad(min);
        }

        // Write total-minutes-from-midnight into the hidden fields + sync the field.
        // Used by the duration auto-advance (start/end event time, which have ids).
        function mecWriteTime(prefix, totalMinutes)
        {
            totalMinutes = Math.max(0, Math.min(1439, totalMinutes));
            var hour24 = Math.floor(totalMinutes / 60);
            var min = Math.floor((totalMinutes % 60) / 5) * 5;
            var $h = $('#' + prefix + 'hour');
            if(!$h.length) return;
            var $ampm = $('#' + prefix + 'ampm');
            if($ampm.length)
            {
                var h12 = hour24 % 12; if(h12 === 0) h12 = 12;
                $h.val(h12);
                $ampm.val(hour24 < 12 ? 'AM' : 'PM');
            }
            else $h.val(hour24);
            $('#' + prefix + 'minutes').val(min);
            var $w = $('#' + prefix + 'time');
            if($w.length) $w.val(mecTimeLabel(hour24, min, $ampm.length > 0));
        }

        function mecTimeInitOne($in)
            {
                // Use jQuery .data() (not a class) so that elements cloned via
                // .html() + .append() (MEC's ticket-add flow) are re-initialized.
                if(!$in.length || $in.data('mec-time-init')) return;
                $in.data('mec-time-init', true);

            var $wrap = $in.closest('.mec-time-combo-wrap');
            if(!$wrap.length) return;
            var is12 = parseInt($in.data('format'), 10) !== 24;
            var $dd = $('<div class="mec-time-dd"><ul></ul></div>').appendTo('body');
            var $list = $dd.find('ul');
            var $clear = $wrap.find('.mec-time-clear');
            var hi = -1;

            // Hidden fields are siblings inside the wrapper (local lookup).
            var $h = $wrap.find('.mec-time-hour');
            var $m = $wrap.find('.mec-time-minutes');
            var $ampm = $wrap.find('.mec-time-ampm');
            if(!$h.length) return; // not a combobox instance

            var options = [];
            for(var i = 0; i < 288; i++)
            {
                var total = i * 5;
                options.push({total: total, label: mecTimeLabel(Math.floor(total / 60), total % 60, is12)});
            }

            function toggleClear(){ $clear.toggle(!!$in.val()); }

            function matches(term, label)
            {
                term = (term || '').toLowerCase().trim();
                if(!term) return true;
                if(label.toLowerCase().indexOf(term) > -1) return true;
                var td = term.replace(/\D/g, '');
                return td.length > 0 && label.replace(/\D/g, '').indexOf(td) > -1;
            }

            function render(term)
            {
                $list.empty();
                options.forEach(function(o){ if(matches(term, o.label)) $('<li/>').text(o.label).data('total', o.total).appendTo($list); });
                hi = $list.children().length ? 0 : -1;
                highlight();
            }

            function highlight()
            {
                $list.children().removeClass('mec-time-dd-active');
                if(hi >= 0) $list.children().eq(hi).addClass('mec-time-dd-active');
            }

            function move(delta)
            {
                var n = $list.children().length;
                if(!n) return;
                hi = (hi + delta + n) % n;
                highlight();
                var $el = $list.children().eq(hi);
                if($el.length) $dd.scrollTop($el.position().top + $dd.scrollTop() - 60);
            }

            function open()
            {
                render('');
                var pos = $in.offset();
                $dd.css({ left: pos.left + 'px', top: (pos.top + $in.outerHeight() + 2) + 'px', width: $in.outerWidth() + 'px' }).show();
            }
            function close(){ $dd.hide(); hi = -1; }

            function writeLocal(total)
            {
                var hour24 = Math.floor(total / 60);
                var min = Math.floor((total % 60) / 5) * 5;
                if($ampm.length)
                {
                    var h12 = hour24 % 12; if(h12 === 0) h12 = 12;
                    $h.val(h12);
                    $ampm.val(hour24 < 12 ? 'AM' : 'PM');
                }
                else $h.val(hour24);
                $m.val(min);
                $in.val(mecTimeLabel(hour24, min, is12));
            }

            function readLocalH24()
            {
                var h24 = parseInt($h.val(), 10); if(isNaN(h24)) h24 = 0;
                if($ampm.length){ if($ampm.val() === 'PM' && h24 !== 12) h24 += 12; else if($ampm.val() === 'AM' && h24 === 12) h24 = 0; }
                return h24;
            }

            function choose(total)
            {
                writeLocal(total);
                $in.trigger('change'); // duration auto-advance (#8)
                toggleClear();
                close();
            }

            toggleClear();

            // "Clear" (x) button: empty the text for retyping (hidden value preserved).
            $wrap.on('mousedown', '.mec-time-clear', function(e){ e.preventDefault(); $in.val(''); open(); $in.focus(); toggleClear(); });

            $in.on('focus', function(){ open(); });
            $in.on('click', function(){ if(!$dd.is(':visible')) open(); });
            $in.on('input', function(){ if(!$dd.is(':visible')) $dd.show(); render($in.val()); toggleClear(); });
            $in.on('keydown', function(e)
            {
                if(e.key === 'ArrowDown'){ e.preventDefault(); if(!$dd.is(':visible')) open(); else move(1); }
                else if(e.key === 'ArrowUp'){ e.preventDefault(); move(-1); }
                else if(e.key === 'Enter'){ if(hi >= 0){ e.preventDefault(); var t = $list.children().eq(hi).data('total'); if(t !== undefined) choose(t); } }
                else if(e.key === 'Escape'){ close(); }
                else if(e.key === 'Tab'){ close(); }
            });
            $list.on('mousedown', 'li', function(e){ e.preventDefault(); choose($(this).data('total')); });
            $in.on('blur', function()
            {
                setTimeout(function()
                {
                    close();
                    var val = ($in.val() || '').trim();
                    var found = null;
                    for(var k = 0; k < options.length; k++){ if(options[k].label.toLowerCase() === val.toLowerCase()){ found = options[k]; break; } }
                    if(found) writeLocal(found.total);
                    else
                    {
                        var h24 = readLocalH24();
                        var mm = parseInt($m.val(), 10) || 0;
                        $in.val(mecTimeLabel(h24, mm, is12));
                    }
                    toggleClear();
                }, 150);
            });
        }

        function mecInitTimeCombos(){ $('.mec-time-combo').each(function(){ mecTimeInitOne($(this)); }); }

        mecInitTimeCombos();

        // Re-init comboboxes added dynamically (in_days rows, tickets, appointments, popup modal).
        if(typeof MutationObserver !== 'undefined')
        {
            var mecTimeMO = new MutationObserver(function(mutations)
            {
                for(var i = 0; i < mutations.length; i++)
                {
                    var added = mutations[i].addedNodes;
                    for(var j = 0; j < added.length; j++)
                    {
                        var n = added[j];
                        if(n.nodeType === 1 && ((n.matches && (n.matches('.mec-time-combo') || n.matches('.mec-hourly-time'))) || (n.querySelector && (n.querySelector('.mec-time-combo') || n.querySelector('.mec-hourly-time')))))
                        {
                            setTimeout(function(){ mecInitTimeCombos(); mecInitHourlyTimes(); }, 30);
                            return;
                        }
                    }
                }
            });
            mecTimeMO.observe(document.body, {childList: true, subtree: true});
        }

        // Close dropdowns when clicking outside (dropdown is in <body>, not the wrap).
        var $activeDDs = $(); // track dropdowns created by comboboxes
        $(document).on('mousedown', function(e)
        {
            $('.mec-time-dd').each(function()
            {
                if(this !== e.target && !this.contains(e.target))
                {
                    var $w = $(this);
                    var $combo = $w.prevAll('.mec-time-combo-wrap').first().find('.mec-time-combo');
                    // Simple check: if click is not in this dropdown and not in any combo wrap
                    var inCombo = false;
                    $('.mec-time-combo-wrap').each(function(){ if(this.contains(e.target)) inCombo = true; });
                    $('.mec-hourly-time-wrap').each(function(){ if(this.contains(e.target)) inCombo = true; });
                    if(!inCombo) $w.hide();
                }
            });
        });

        // ===== Hourly schedule string time combobox (audit #5) =====
        // Same UX as the R3 combobox, but the value lives in a single text
        // field (the hourly schedule stores "from"/"to" as raw strings echoed
        // on the front-end), so we read/write the input directly — no hidden
        // fields, no storage change.

        function mecParseTimeString(str, is12)
        {
            str = (str || '').trim().toUpperCase();
            if(!str) return null;
            var m = str.match(/^(\d{1,2})[:.\s](\d{1,2})\s*(AM|PM)?$/);
            if(!m) return null;
            var h = parseInt(m[1], 10);
            var min = parseInt(m[2], 10);
            if(isNaN(h) || isNaN(min) || h < 0 || h > 23 || min < 0 || min > 59) return null;
            var ap = m[3];
            if(ap === 'PM' && h !== 12) h += 12;
            else if(ap === 'AM' && h === 12) h = 0;
            return h * 60 + min;
        }

        function mecHourlyTimeInitOne($in)
        {
            // Use jQuery .data() (not a class) so dynamically added rows are re-initialized.
            if(!$in.length || $in.data('mec-time-init')) return;
            $in.data('mec-time-init', true);

            var $wrap = $in.closest('.mec-hourly-time-wrap');
            if(!$wrap.length) return;
            var is12 = parseInt($in.data('format'), 10) !== 24;
            var $dd = $('<div class="mec-time-dd"><ul></ul></div>').appendTo('body');
            var $list = $dd.find('ul');
            var $clear = $wrap.find('.mec-time-clear');
            var hi = -1;

            var options = [];
            for(var i = 0; i < 288; i++)
            {
                var total = i * 5;
                options.push({total: total, label: mecTimeLabel(Math.floor(total / 60), total % 60, is12)});
            }

            function toggleClear(){ $clear.toggle(!!$in.val()); }

            function matches(term, label)
            {
                term = (term || '').toLowerCase().trim();
                if(!term) return true;
                if(label.toLowerCase().indexOf(term) > -1) return true;
                var td = term.replace(/\D/g, '');
                return td.length > 0 && label.replace(/\D/g, '').indexOf(td) > -1;
            }

            function highlight()
            {
                $list.children().removeClass('mec-time-dd-active');
                if(hi >= 0) $list.children().eq(hi).addClass('mec-time-dd-active');
            }

            function render(term)
            {
                $list.empty();
                options.forEach(function(o){ if(matches(term, o.label)) $('<li/>').text(o.label).data('total', o.total).appendTo($list); });
                hi = $list.children().length ? 0 : -1;
                highlight();
            }

            function move(delta)
            {
                var n = $list.children().length;
                if(!n) return;
                hi = (hi + delta + n) % n;
                highlight();
                var $el = $list.children().eq(hi);
                if($el.length) $dd.scrollTop($el.position().top + $dd.scrollTop() - 60);
            }

            function open()
            {
                render('');
                // Highlight the current value if parseable.
                var cur = mecParseTimeString($in.val(), is12);
                if(cur !== null)
                {
                    var snapped = Math.floor(cur / 5) * 5;
                    $list.children().each(function(){ if($(this).data('total') === snapped){ hi = $(this).index(); return false; } });
                    highlight();
                }
                var pos = $in.offset();
                $dd.css({ left: pos.left + 'px', top: (pos.top + $in.outerHeight() + 2) + 'px', width: $in.outerWidth() + 'px' }).show();
            }
            function close(){ $dd.hide(); hi = -1; }

            function choose(total)
            {
                var hour24 = Math.floor(total / 60);
                var min = total % 60;
                $in.val(mecTimeLabel(hour24, min, is12)).trigger('change');
                toggleClear();
                close();
            }

            toggleClear();

            // "Clear" (x) button: empty the text for retyping.
            $wrap.on('mousedown', '.mec-time-clear', function(e){ e.preventDefault(); $in.val(''); open(); $in.focus(); toggleClear(); });

            $in.on('focus', function(){ open(); });
            $in.on('click', function(){ if(!$dd.is(':visible')) open(); });
            $in.on('input', function(){ if(!$dd.is(':visible')) $dd.show(); render($in.val()); toggleClear(); });
            $in.on('keydown', function(e)
            {
                if(e.key === 'ArrowDown'){ e.preventDefault(); if(!$dd.is(':visible')) open(); else move(1); }
                else if(e.key === 'ArrowUp'){ e.preventDefault(); move(-1); }
                else if(e.key === 'Enter'){ if(hi >= 0){ e.preventDefault(); var t = $list.children().eq(hi).data('total'); if(t !== undefined) choose(t); } }
                else if(e.key === 'Escape'){ close(); }
                else if(e.key === 'Tab'){ close(); }
            });
            $list.on('mousedown', 'li', function(e){ e.preventDefault(); choose($(this).data('total')); });
            $in.on('blur', function()
            {
                setTimeout(function()
                {
                    close();
                    var val = ($in.val() || '').trim();
                    if(!val){ toggleClear(); return; }
                    // Snap typed text to the nearest 5-minute slot when parseable.
                    var total = mecParseTimeString(val, is12);
                    if(total !== null)
                    {
                        var snapped = Math.floor(total / 5) * 5;
                        $in.val(mecTimeLabel(Math.floor(snapped / 60), snapped % 60, is12));
                    }
                    toggleClear();
                }, 150);
            });
        }

        function mecInitHourlyTimes(){ $('.mec-hourly-time').each(function(){ mecHourlyTimeInitOne($(this)); }); }

        mecInitHourlyTimes();

        // ===== Admin event editor: required-field + End >= Start validation =====
        // Audit issue #1: no required-field markers/validation in the admin editor.
        (function()
        {
            var $postForm = $('#post');
            var $startDate = $('#mec_start_date');
            var $endDate = $('#mec_end_date');
            // Only relevant on the admin event editor (#post form with MEC date fields).
            if(!$postForm.length || !$startDate.length) return;

            var mecVL = (typeof mec_admin_localize !== 'undefined') ? mec_admin_localize : {};
            var warningsAcknowledged = false;

            // MEC reusable notice system — modern card with accent bar, type-aware,
            // dismissible. Supports title, message/items, primary + dismiss buttons.
            function mecNotice(opts)
            {
                var o = $.extend({
                    type: 'info',          // error | warning | info | success
                    title: '',
                    message: '',
                    items: [],
                    primaryText: '',
                    primaryAction: null,
                    dismissText: '',
                    container: '#poststuff',
                    id: 'mec-notice-' + Date.now()
                }, opts);

                $('.mec-notice-temp').remove();

                var body = '';
                if(o.title) body += '<div class="mec-notice-title">' + o.title + '</div>';
                if(o.items && o.items.length) body += '<ul class="mec-notice-list">' + o.items.map(function(i){ return '<li>' + i + '</li>'; }).join('') + '</ul>';
                else if(o.message) body += '<p class="mec-notice-text">' + o.message + '</p>';

                if(o.primaryText || o.dismissText)
                {
                    body += '<div class="mec-notice-actions">';
                    if(o.primaryText) body += '<button type="button" class="mec-notice-btn mec-notice-btn--primary mec-notice-primary">' + o.primaryText + '</button>';
                    if(o.dismissText) body += '<button type="button" class="mec-notice-btn mec-notice-btn--ghost mec-notice-dismiss">' + o.dismissText + '</button>';
                    body += '</div>';
                }

                var $n = $('<div/>', {
                    id: o.id,
                    'class': 'mec-notice mec-notice--' + o.type + ' mec-notice-temp',
                    html: '<div class="mec-notice-accent"></div><div class="mec-notice-body">' + body + '</div><div class="mec-notice-close" aria-label="Close" role="button" tabindex="0">&times;</div>'
                });

                $(o.container).prepend($n);
                $('html, body').animate({scrollTop: $n.offset().top - 40}, 200);

                $n.find('.mec-notice-dismiss, .mec-notice-close').on('click', function(){ $n.remove(); });
                if(o.primaryText)
                {
                    $n.find('.mec-notice-primary').on('click', function()
                    {
                        if(typeof o.primaryAction === 'function') o.primaryAction();
                        $n.remove();
                    });
                }
                return $n;
            }

            function mecVLTab(tabId)
            {
                $('#mec_metabox_details .mec-add-event-tabs-link[data-href="' + tabId + '"]').trigger('click');
            }

            function mecVLTime24(prefix)
            {
                var $h = $('#' + prefix + 'hour');
                if(!$h.length) return {hour: 0, min: 0};
                var hour = parseInt($h.val(), 10) || 0;
                var min = ($('#' + prefix + 'minutes').length) ? (parseInt($('#' + prefix + 'minutes').val(), 10) || 0) : 0;
                var $ampm = $('#' + prefix + 'ampm');
                if($ampm.length)
                {
                    if($ampm.val() === 'PM' && hour !== 12) hour += 12;
                    else if($ampm.val() === 'AM' && hour === 12) hour = 0;
                }
                return {hour: hour, min: min};
            }

            // Write a 24h time (in total minutes since midnight) back into the
            // hour/minutes/ampm selects, respecting the active time format.
            function mecVLSetTime24(prefix, totalMinutes)
            {
                if(typeof mecWriteTime === 'function') mecWriteTime(prefix, totalMinutes);
            }

            function mecVLParseDate($field)
            {
                var raw = $.trim($field.val() || '');
                if(!raw) return null;
                try
                {
                    return $.datepicker.parseDate(datepicker_format, raw);
                }
                catch(e)
                {
                    var fb = new Date(raw);
                    return isNaN(fb.getTime()) ? null : fb;
                }
            }

            function mecVLShowError($field, message)
            {
                $field.addClass('mec-field-invalid').attr('aria-invalid', 'true');
                var $err = $field.next('.mec-field-error');
                if(!$err.length)
                {
                    $err = $('<span class="mec-field-error" role="alert"></span>');
                    $field.after($err);
                }
                $err.text(message);
            }

            function mecVLClearError($field)
            {
                $field.removeClass('mec-field-invalid').removeAttr('aria-invalid');
                $field.next('.mec-field-error').remove();
            }

            // Clear the inline error as soon as the user edits the field.
            $startDate.add($endDate).on('input change', function() { mecVLClearError($(this)); });

            // Smart end time: when the start time changes, shift the end time to
            // preserve the event duration (Google Calendar behavior). Audit issue #8.
            var $startWidget = $('#mec_start_time');
            if($startWidget.length)
            {
                // Combobox mode (R3): the visible field is a text input; capture on
                // focus, shift on the 'change' the combobox dispatches on selection.
                var startBefore = null;
                $startWidget.on('focus', function()
                {
                    if($('#mec_allday').is(':checked')) return;
                    var t = mecVLTime24('mec_start_');
                    startBefore = t.hour * 60 + t.min;
                }).on('change', function()
                {
                    if(startBefore === null) return;
                    var t = mecVLTime24('mec_start_');
                    var now = t.hour * 60 + t.min;
                    var delta = now - startBefore;
                    startBefore = now;
                    if(!delta) return;
                    var e = mecVLTime24('mec_end_');
                    mecWriteTime('mec_end_', (e.hour * 60 + e.min) + delta);
                });
            }
            else
            {
                // Legacy 3-select mode: focus/change on the selects.
                var $startSelects = $('#mec_start_hour, #mec_start_minutes, #mec_start_ampm');
                if($startSelects.length)
                {
                    var legacyBefore = null;
                    $startSelects.on('focus', function()
                    {
                        if($('#mec_allday').is(':checked')) return;
                        var t = mecVLTime24('mec_start_');
                        legacyBefore = t.hour * 60 + t.min;
                    }).on('change', function()
                    {
                        if($('#mec_allday').is(':checked')) { legacyBefore = null; return; }
                        if(legacyBefore === null) return;
                        var t = mecVLTime24('mec_start_');
                        var now = t.hour * 60 + t.min;
                        var delta = now - legacyBefore;
                        legacyBefore = now;
                        if(delta === 0) return;
                        var e = mecVLTime24('mec_end_');
                        mecVLSetTime24('mec_end_', (e.hour * 60 + e.min) + delta);
                    });
                }
            }

            $postForm.on('submit', function(e)
            {
                // Clear previous errors before re-validating.
                mecVLClearError($startDate);
                mecVLClearError($endDate);

                // Publish-intent detection: enforce on every submit EXCEPT
                // explicit draft saves and previews. This covers the publish
                // button, Enter-key submits and any programmatic path — the
                // old "publishClicked" flag missed keyboard submits.
                var submitter = (e.originalEvent && e.originalEvent.submitter) ? $(e.originalEvent.submitter) : null;
                if(submitter && submitter.attr('id') === 'save-post') return;
                if($('#wp-preview').val() === 'dopreview') return;

                // Undo WordPress core's URL rewrite: post.js appends
                // "wp-post-new-reload=true" to the address bar on every publish
                // click of an auto-draft, even when the submit ends up blocked.
                // Strip it so the misleading state doesn't linger.
                if(window.history && window.history.replaceState && window.location.href.indexOf('wp-post-new-reload') > -1)
                {
                    window.history.replaceState(null, null, window.location.href.replace(/([?&])wp-post-new-reload=true&?/, '$1').replace(/[?&]$/, ''));
                }

                var startRaw = $.trim($startDate.val() || '');
                var endRaw = $.trim($endDate.val() || '');

                // 1. Start Date is required.
                if(!startRaw)
                {
                    e.preventDefault();
                    mecVLTab('mec_meta_box_date_form');
                    mecVLShowError($startDate, mecVL.start_date_required || 'Please enter a start date for the event.');
                    $startDate.trigger('focus');
                    mecNotice({ type:'error', title:'Start date required', message: mecVL.start_date_required || 'Please enter a start date for the event.', dismissText:'Got it' });
                    return;
                }

                // 2. End must be on or after Start.
                if(endRaw)
                {
                    var sDate = mecVLParseDate($startDate);
                    var eDate = mecVLParseDate($endDate);
                    if(sDate && eDate)
                    {
                        var allday = $('#mec_allday').is(':checked');
                        var sT = allday ? {hour: 0, min: 0} : mecVLTime24('mec_start_');
                        var eT = allday ? {hour: 0, min: 0} : mecVLTime24('mec_end_');
                        var start = new Date(sDate.getFullYear(), sDate.getMonth(), sDate.getDate(), sT.hour, sT.min, 0, 0);
                        var finish = new Date(eDate.getFullYear(), eDate.getMonth(), eDate.getDate(), eT.hour, eT.min, 0, 0);

                        if(finish.getTime() < start.getTime())
                        {
                            e.preventDefault();
                            mecVLTab('mec_meta_box_date_form');
                            mecVLShowError($endDate, mecVL.end_after_start || 'The end date and time must be on or after the start date and time.');
                            $endDate.trigger('focus');
                            mecNotice({ type:'error', title:'End is before start', message: mecVL.end_after_start || 'The end date and time must be on or after the start date and time.', dismissText:'Got it' });
                            return;
                        }
                    }
                }

                // ===== R5: Extended pre-publish checks =====
                var warnings = [];
                var $bookingBox = $('#mec_metabox_booking');
                $('.mec-notice-temp').remove();

                // Only treat as "bookable" if there are real tickets (with names).
                // A blank default ticket row is NOT a real ticket.
                var $realTickets = $('#mec-tickets input[name*="[name]"]').filter(function(){ return $.trim($(this).val()) !== ''; });
                var hasTickets = $realTickets.length > 0;

                // --- BLOCKS (prevent publish) ---
                // B3: certain_weekdays + none checked
                if($('#mec_repeat').is(':checked') && $('#mec_repeat_type').val() === 'certain_weekdays' && $('input[name="mec[date][repeat][certain_weekdays][]"]:checked').length === 0)
                {
                    e.preventDefault(); mecVLTab('mec_meta_box_repeat_form');
                    mecNotice({ type:'error', title:'No weekdays selected', message:'Repeating is set to "Certain Weekdays" but no weekdays are checked.', dismissText:'Got it' });
                    return;
                }
                // B4: occurrences = 0
                var $endRadio = $('#mec_repeat_end_type').length ? $('#mec_repeat_end_type') : $('input[name="mec[date][repeat][end]"]:checked');
                if($endRadio.val() === 'occurrences')
                {
                    var occ = parseInt($('#mec_date_repeat_end_at_occurrences').val(), 10);
                    if(isNaN(occ) || occ <= 0)
                    {
                        e.preventDefault(); mecVLTab('mec_meta_box_repeat_form');
                        mecNotice({ type:'error', title:'Invalid occurrence count', message:'The number of occurrences must be at least 1.', dismissText:'Got it' });
                        return;
                    }
                }

                // --- WARNINGS (allow publish with confirmation) ---
                // Booking-specific warnings only if there are real tickets
                if(hasTickets)
                {
                    // W2: ticket with name but no price
                    $realTickets.each(function(){
                        var $name = $(this);
                        var $row = $name.closest('.mec-ticket-start-time, .wn-ticket-time').parent();
                        var $price = $row.find('input[name*="price"]');
                        if(!$price.length) $price = $name.closest('[class*="ticket"]').find('input[name*="price"]');
                        if($price.length && $.trim($price.val()) === '') warnings.push('A ticket has a name but no price set.');
                    });
                    // W3: cost empty on bookable event with tickets
                    if($.trim($('#mec_cost').val()) === '' && !$('#mec_cost_is_free').is(':checked') && !$('#mec_cost_auto_calculate').is(':checked')) warnings.push('This event has tickets but no cost is displayed.');
                }
                // W4: no featured image
                if(!$('#set-post-thumbnail img').length && !$('#postimagediv img').length) warnings.push('No featured image set.');
                // W5: no content
                var c = (typeof tinymce !== 'undefined' && tinymce.get('content')) ? tinymce.get('content').getContent() : ($('#content').val() || '');
                if($.trim(c.replace(/<[^>]*>/g, '')) === '') warnings.push('No event description/content.');
                // W6: no location
                var lv = $('#mec_location_id').val(); if(!lv || lv === '0' || lv === '1') warnings.push('No location set.');
                // W7: no organizer
                var ov = $('#mec_organizer_id').val(); if(!ov || ov === '0' || ov === '1') warnings.push('No organizer set.');
                // W8: occurrences <= 1
                if($endRadio.val() === 'occurrences'){ var ow = parseInt($('#mec_date_repeat_end_at_occurrences').val(), 10); if(!isNaN(ow) && ow <= 1) warnings.push('Repeat ends after only ' + ow + ' occurrence(s).'); }
                // W10: zero duration
                if(!$('#mec_allday').is(':checked')){ var sT = mecVLTime24('mec_start_'), eT = mecVLTime24('mec_end_'); if(sT && eT && sT.hour === eT.hour && sT.min === eT.min) warnings.push('Start and end times are identical (zero duration).'); }

                if(!warningsAcknowledged && warnings.length > 0)
                {
                    e.preventDefault();
                    mecNotice({
                        type:'warning',
                        title:'Publishing with warnings',
                        items: warnings,
                        primaryText:'Publish anyway',
                        primaryAction: function(){ warningsAcknowledged = true; $('#publish').trigger('click'); },
                        dismissText:'Go back and fix'
                    });
                    return;
                }
            });
        })();

        // ===== Unsaved-changes protection for MEC meta boxes =====
        // Audit issue #10. Warn before navigating away with unsaved MEC edits.
        (function()
        {
            var mecDirty = false;
            var $scope = $('#mec_metabox_details, #mec_metabox_booking, #mec_metabox_visibility, #mec_metabox_gallery');

            // Ignore spurious change/input events fired by 3rd-party init scripts
            // (nice-select, select2, color picker, etc.) before the user interacts.
            var mecUserInteracted = false;
            $('#post').one('mousedown keydown', function() { mecUserInteracted = true; });

            $scope.on('change input', 'input, select, textarea', function() {
                if(mecUserInteracted) mecDirty = true;
            });

            // Clear the dirty flag when the post actually submits. Must run AFTER
            // the validation handler so preventDefault (invalid form) keeps the flag.
            $('#post').on('submit', function(e)
            {
                if(!e.isDefaultPrevented()) mecDirty = false;
            });

            $(window).on('beforeunload', function(e)
            {
                if(mecDirty)
                {
                    e.preventDefault();
                    e.returnValue = '';
                    return '';
                }
            });
        })();

        $('#mec_date_repeat_end_at_date').datepicker(
        {
            changeYear: true,
            changeMonth: true,
            dateFormat: datepicker_format,
            gotoCurrent: true,
            yearRange: 'c-3:c+5',
        });

        $('.mec_date_picker_dynamic_format').datepicker(
        {
            changeYear: true,
            changeMonth: true,
            dateFormat: datepicker_format,
            gotoCurrent: true,
            yearRange: 'c-3:c+5',
        });

        // End Repeat: conditional fields driven by the type <select> (was radios).
        // Date field shows only for "date", occurrences field only for "occurrences".
        $('#mec_repeat_end_type').on('change', function()
        {
            var v = $(this).val();
            $('.mec-end-repeat-date-wrap').toggleClass('mec-is-hidden', v !== 'date');
            $('.mec-end-repeat-occurrences-wrap').toggleClass('mec-is-hidden', v !== 'occurrences');
        });

        $('.mec_date_picker').datepicker(
        {
            changeYear: true,
            changeMonth: true,
            dateFormat: 'yy-mm-dd',
            gotoCurrent: true,
            yearRange: 'c-3:c+5',
        });

        $('.mec_date_picker_dynamic_format_start').datepicker(
        {
            changeYear: true,
            changeMonth: true,
            dateFormat: datepicker_format,
            gotoCurrent: true,
            yearRange: 'c-1:c+5',
            onSelect: function(date)
            {
                var selectedDate;

                try
                {
                    selectedDate = $.datepicker.parseDate(datepicker_format, date);
                }
                catch(e)
                {
                    selectedDate = new Date(date);
                }

                if(!(selectedDate instanceof Date) || isNaN(selectedDate.getTime())) return;

                var endDate = new Date(selectedDate.getTime());

                var $end_picker = $(this).next();
                $end_picker.datepicker("option", "minDate", endDate);
                $end_picker.datepicker("option", "maxDate", '+5y');

                $(this).trigger('change');
            }
        });

        $('.mec_date_picker_dynamic_format_end').datepicker(
        {
            changeYear: true,
            changeMonth: true,
            dateFormat: datepicker_format,
            gotoCurrent: true,
            yearRange: 'c-1:c+5',
        });

        trigger_period_picker();
    }

    // Initialize WP Color Picker
    if($.fn.wpColorPicker) jQuery('.mec-color-picker').wpColorPicker();

    $('#mec_location_id').on('change', function()
    {
        mec_location_toggle();

        // Mirror of the Other Organizers behavior: "Other Locations" is only
        // available once a real main location is selected (not "Hide location" = 1).
        var mec_location_val = parseInt($(this).val());
        if(mec_location_val != 1) $('#mec-additional-location-wrap').show();
        else $('#mec-additional-location-wrap').hide();
    });

    $('#mec_organizer_id').on('change', function()
    {
        mec_organizer_toggle();
        var mec_organizer_val = parseInt($(this).val());

        // Direct ID lookup — the wrap is a sibling of the select's row, so
        // the old ".parent().parent().find(...)" traversal never reached it.
        if(mec_organizer_val != 1) $('#mec-additional-organizer-wrap').show();
        else $('#mec-additional-organizer-wrap').hide();
    });

    mec_location_toggle();
    mec_organizer_toggle();

    $('#mec_repeat').on('change', function()
    {
        mec_repeat_toggle();
    });

    mec_repeat_toggle();

    $('#mec_repeat_type').on('change', function()
    {
        mec_repeat_type_toggle();
    });

    mec_repeat_type_toggle();

    // "This event is free" toggle: sets the cost field to 0 (which the frontend
    // renders as "Free") and locks it, preserving the previous value to restore.
    // Audit issue #15.
    (function()
    {
        var $toggle = $('#mec_cost_is_free');
        var $cost = $('#mec_cost');
        if(!$toggle.length || !$cost.length) return;

        function applyFree()
        {
            if($toggle.is(':checked'))
            {
                $cost.data('prev-cost', $cost.val());
                $cost.val('0').prop('readonly', true).addClass('mec-cost-locked');
            }
            else
            {
                $cost.prop('readonly', false).removeClass('mec-cost-locked');
                var prev = $cost.data('prev-cost');
                if(typeof prev !== 'undefined') $cost.val(prev);
            }
        }

        $toggle.on('change', applyFree);
        applyFree();
    })();

    // ===== Accessibility: ARIA tabs + keyboard nav + focusable tooltips =====
    // Audit issue #3. Enhances the existing tab markup in place (no PHP changes).
    function mecEnhanceTabs(cfg)
    {
        var $ctx = $(cfg.context);
        if(!$ctx.length) return;
        var $tablist = $ctx.find(cfg.tablist);
        var $tabs = $ctx.find(cfg.tab);
        var $panels = $ctx.find(cfg.panel);
        if(!$tabs.length) return;

        $tablist.attr('role', 'tablist');

        $tabs.each(function() {
            var $t = $(this);
            var panelId = $t.attr('data-href');
            var tabId = $t.attr('id') || (panelId + '-tab');
            var active = $t.hasClass('mec-tab-active');
            $t.attr({
                'id': tabId,
                'role': 'tab',
                'aria-controls': panelId,
                'aria-selected': active ? 'true' : 'false',
                'tabindex': active ? '0' : '-1'
            });
        });

        $panels.each(function() {
            var $p = $(this);
            $p.attr({
                'role': 'tabpanel',
                'aria-labelledby': $p.attr('id') + '-tab',
                'aria-hidden': $p.hasClass('mec-tab-active') ? 'false' : 'true'
            });
        });

        function syncTabs()
        {
            $tabs.each(function() {
                var $t = $(this);
                var active = $t.hasClass('mec-tab-active');
                $t.attr('aria-selected', active ? 'true' : 'false');
                $t.attr('tabindex', active ? '0' : '-1');
            });
            $panels.each(function() {
                $(this).attr('aria-hidden', $(this).hasClass('mec-tab-active') ? 'false' : 'true');
            });
        }

        // Keep ARIA state in sync after the existing inline click handlers switch tabs.
        $tablist.on('click', cfg.tab, function() { syncTabs(); });

        // Keyboard navigation (automatic activation): arrows + Home/End.
        $tablist.on('keydown', cfg.tab, function(e) {
            var idx = $tabs.index(this);
            var $target = null;
            switch(e.key) {
                case 'ArrowRight': case 'ArrowDown': $target = $tabs.eq(idx + 1); break;
                case 'ArrowLeft': case 'ArrowUp': $target = $tabs.eq(idx - 1); break;
                case 'Home': $target = $tabs.first(); break;
                case 'End': $target = $tabs.last(); break;
                case 'Enter': case ' ':
                    e.preventDefault();
                    $(this).trigger('click');
                    this.focus();
                    return;
                default: return;
            }
            if($target && $target.length) {
                e.preventDefault();
                $target.trigger('click'); // activate (existing handler runs, then syncTabs)
                $target[0].focus();
            }
        });
    }

    mecEnhanceTabs({
        context: '#mec_metabox_details',
        tablist: '.mec-add-event-tabs-left',
        tab: '.mec-add-event-tabs-link',
        panel: '.mec-event-tab-content'
    });
    mecEnhanceTabs({
        context: '#mec_metabox_booking',
        tablist: '.mec-add-booking-tabs-left',
        tab: '.mec-add-booking-tabs-link',
        panel: '.mec-booking-tab-content'
    });

    // Make hover-only help tooltips keyboard accessible (audit issue #3).
    $('.mec-tooltip').each(function(idx) {
        var $tip = $(this);
        var $box = $tip.find('.box').first();
        var $icon = $tip.find('i').first();
        if(!$box.length || !$icon.length) return;
        var boxId = $box.attr('id') || ('mec-tip-' + idx);
        $box.attr('id', boxId);
        $icon.attr({
            'tabindex': '0',
            'role': 'button',
            'aria-describedby': boxId
        });
        if(!$icon.attr('aria-label') && !$icon.attr('title')) {
            var title = $tip.find('.title').first().text();
            $icon.attr('aria-label', title || 'More information');
        }
    });

    // ===== Recurrence summary + next-5 preview =====
    // Audit issue #6. Live, human-readable rule summary + next occurrences,
    // computed from the event start date (not "today").
    (function()
    {
        var $box = $('#mec-repeat-preview');
        var $body = $box.find('.mec-repeat-preview-body');
        var $form = $('#mec_meta_box_repeat_form');
        if(!$box.length || !$body.length || !$form.length) return;

        var loc = (typeof mec_admin_localize !== 'undefined') ? mec_admin_localize : ((typeof mecdata !== 'undefined') ? mecdata : {});
        var units = loc.interval_units || {};
        var PREVIEW_MAX = 5;

        function parseDate($field)
        {
            var raw = $.trim($field.val() || '');
            if(!raw) return null;
            try { return $.datepicker.parseDate(datepicker_format, raw); }
            catch(e) { var d = new Date(raw); return isNaN(d.getTime()) ? null : d; }
        }

        function fmt(date)
        {
            try { return $.datepicker.formatDate(datepicker_format, date); }
            catch(e) { return date.toDateString(); }
        }

        function addDays(d, n){ var x = new Date(d.getTime()); x.setDate(x.getDate() + n); return x; }
        function addMonths(d, n){ var x = new Date(d.getTime()); x.setMonth(x.getMonth() + n); return x; }
        function addYears(d, n){ var x = new Date(d.getTime()); x.setFullYear(x.getFullYear() + n); return x; }

        // MEC weekday number (1=Mon..7=Sun) -> JS getDay() (0=Sun..6=Sat)
        function mecDayToJs(n){ n = parseInt(n, 10); return n === 7 ? 0 : n; }

        function readEnd()
        {
            var end = ($('#mec_repeat_end_type').length ? $('#mec_repeat_end_type').val() : $('input[name="mec[date][repeat][end]"]:checked').val());
            var endDate = null, maxOcc = null;
            if(end === 'date') endDate = parseDate($('#mec_date_repeat_end_at_date'));
            else if(end === 'occurrences')
            {
                var n = parseInt($('#mec_date_repeat_end_at_occurrences').val(), 10);
                if(n > 0) maxOcc = n;
            }
            return {type: end || 'never', endDate: endDate, maxOcc: maxOcc};
        }

        function generate(type, interval, start, weekdays, end)
        {
            var dates = [];
            var guard = 0;
            // Generate one extra beyond the display cap so the UI can detect
            // truncation and show a "+N more" chip. Only PREVIEW_MAX are shown.
            var max = end.maxOcc ? Math.min(end.maxOcc, PREVIEW_MAX + 1) : (PREVIEW_MAX + 1);
            var endDateMs = end.endDate ? end.endDate.getTime() : null;

            function pushIfValid(d)
            {
                if(endDateMs !== null && d.getTime() > endDateMs) return false;
                dates.push(d);
                return dates.length < max;
            }

            if(type === 'daily' || type === 'weekly')
            {
                var stepDays = (type === 'weekly' ? Math.max(1, interval) * 7 : Math.max(1, interval));
                var d = new Date(start.getTime());
                while(dates.length < max && guard++ < 10000){ if(!pushIfValid(d)) break; d = addDays(d, stepDays); }
            }
            else if(type === 'monthly')
            {
                var n = Math.max(1, interval);
                var d = new Date(start.getTime());
                while(dates.length < max && guard++ < 10000){ if(!pushIfValid(d)) break; d = addMonths(d, n); }
            }
            else if(type === 'yearly')
            {
                var n = Math.max(1, interval);
                var d = new Date(start.getTime());
                while(dates.length < max && guard++ < 10000){ if(!pushIfValid(d)) break; d = addYears(d, n); }
            }
            else if(type === 'weekday' || type === 'weekend' || type === 'certain_weekdays')
            {
                var target;
                if(type === 'weekday') target = [1, 2, 3, 4, 5];
                else if(type === 'weekend') target = [0, 6];
                else target = weekdays.map(mecDayToJs);
                var d = new Date(start.getTime());
                while(dates.length < max && guard++ < 40000)
                {
                    if(target.indexOf(d.getDay()) !== -1){ if(!pushIfValid(d)) break; }
                    if(endDateMs !== null && d.getTime() > endDateMs) break;
                    d = addDays(d, 1);
                }
            }
            return dates;
        }

        function buildSummary(type, interval, start, weekdays, end)
        {
            var s = '';
            var u = {daily: units.daily || 'days', weekly: units.weekly || 'weeks', monthly: units.monthly || 'months', yearly: units.yearly || 'years'};
            if(type === 'daily' || type === 'weekly' || type === 'monthly' || type === 'yearly')
            {
                var n = Math.max(1, interval);
                var unit = u[type];
                s = (n === 1) ? ('Every ' + unit.replace(/s$/, '')) : ('Every ' + n + ' ' + unit);
            }
            else if(type === 'weekday') s = 'Every weekday (Mon-Fri)';
            else if(type === 'weekend') s = 'Every weekend (Sat-Sun)';
            else if(type === 'certain_weekdays') s = weekdays.length ? ('Weekly on ' + weekdays.length + ' day(s)') : 'No weekdays selected';
            else if(type === 'custom_days') s = 'On the custom dates listed above';
            else if(type === 'advanced') s = 'Advanced repeating pattern';
            else s = type;

            if(start) s += ', starting ' + fmt(start);
            if(end.type === 'date' && end.endDate) s += ', until ' + fmt(end.endDate);
            else if(end.type === 'occurrences' && end.maxOcc) s += ', for ' + end.maxOcc + ' occurrence' + (end.maxOcc === 1 ? '' : 's');
            else s += ', no end';
            return s;
        }

        function update()
        {
            if(!$('#mec_repeat').is(':checked'))
            {
                $box.hide();
                return;
            }
            $box.show();

            var type = $('#mec_repeat_type').val();
            var interval = parseInt($('#mec_repeat_interval').val(), 10);
            if(isNaN(interval) || interval < 1) interval = 1;
            var start = parseDate($('#mec_start_date'));
            var weekdays = [];
            $('input[name="mec[date][repeat][certain_weekdays][]"]:checked').each(function(){ weekdays.push($(this).val()); });
            var end = readEnd();

            var html = '<p class="mec-repeat-preview-summary">' + buildSummary(type, interval, start, weekdays, end) + '</p>';

            var previewable = ['daily', 'weekly', 'monthly', 'yearly', 'weekday', 'weekend', 'certain_weekdays'];
            if(start && previewable.indexOf(type) !== -1)
            {
                if(type === 'certain_weekdays' && !weekdays.length)
                {
                    html += '<p class="mec-repeat-preview-empty">' + (loc.repeat_preview_no_weekdays || 'Select weekdays to preview dates.') + '</p>';
                }
                else
                {
                    var dates = generate(type, interval, start, weekdays, end);
                    var total = '';
                    if(end.type === 'occurrences' && end.maxOcc) total = end.maxOcc;
                    else if(end.type === 'date' && dates.length < PREVIEW_MAX) total = dates.length;

                    if(dates.length)
                    {
                        var shown = Math.min(dates.length, PREVIEW_MAX);
                        var items = dates.slice(0, PREVIEW_MAX).map(function(d){ return '<li>' + fmt(d) + '</li>'; }).join('');
                        // Truncation indicator: when more occurrences exist than
                        // shown, append a muted chip so the user understands the 5
                        // dates are samples (resolves "5 shown vs. N total" confusion).
                        if(dates.length > PREVIEW_MAX)
                        {
                            var moreText = '…';
                            if(total !== '' && parseInt(total, 10) > shown)
                            {
                                moreText = (loc.repeat_preview_more || '+ %d more').replace('%d', parseInt(total, 10) - shown);
                            }
                            items += '<li class="mec-repeat-preview-more">' + moreText + '</li>';
                        }
                        html += '<div class="mec-repeat-preview-dates"><span class="mec-repeat-preview-label">' + (loc.repeat_preview_next || 'Next occurrences (from start date)') + ':</span><ul>' + items + '</ul></div>';
                    }
                    if(total !== '') html += '<span class="mec-repeat-preview-count">' + (loc.repeat_preview_total || 'This event will occur %d times in total.').replace('%d', total) + '</span>';
                }
            }
            else if(!start)
            {
                html += '<p class="mec-repeat-preview-empty">' + (loc.repeat_preview_need_start || 'Enter a start date to preview dates.') + '</p>';
            }

            $body.html(html);
        }

        $form.on('change input', 'input, select', update);
        $('#mec_start_date').on('change input', update);
        update();
    })();

    // ===== Currency options live preview (audit #11) =====
    // Mirrors main::render_price() so the sample matches the real front-end.
    (function()
    {
        var $preview = $('#mec_currency_preview');
        if(!$preview.length) return;

        var sample = parseFloat($preview.data('sample'));
        if(isNaN(sample)) sample = 1234.5;
        var defaultSign = $preview.data('default-sign') || '$';

        // Equivalent of PHP number_format(amount, decs, decChar, thouChar).
        function format(amount, decs, decChar, thouChar)
        {
            decs = Math.max(0, parseInt(decs, 10) || 0);
            var s = amount.toFixed(decs);
            var parts = s.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thouChar);
            return parts.length > 1 ? parts[0] + decChar + parts[1] : parts[0];
        }

        function val($el, fallback)
        {
            var v = $el.val();
            return (v === null || v === '') ? fallback : v;
        }

        function update()
        {
            var sign = $.trim($('#mec_currency_currency_symptom').val()) || defaultSign;
            var pos = val($('#mec_currency_currency_sign'), 'before');
            var thou = val($('#mec_currency_thousand_separator'), ',');
            var decChar = val($('#mec_currency_decimal_separator'), '.');
            // "No decimal" checkbox is checked => decimal_separator_status = 0 => no decimals.
            var noDec = $('#mec_currency_decimal_separator_status').is(':checked');
            var decs = noDec ? 0 : Math.max(0, parseInt($('#mec_currency_decimals').val(), 10) || 0);

            var num = format(sample, decs, (noDec ? '' : decChar), thou);
            var result;
            if(pos === 'after') result = num + sign;
            else if(pos === 'after_space') result = num + ' ' + sign;
            else if(pos === 'before_space') result = sign + ' ' + num;
            else result = sign + num;

            $preview.text(result);
        }

        $('#mec_currency_currency_symptom, #mec_currency_currency_sign, #mec_currency_thousand_separator, #mec_currency_decimal_separator, #mec_currency_decimals, #mec_currency_decimal_separator_status').on('input change', update);
        update();
    })();

    // ===== Location duplicate-name guard =====
    // Audit issue #14. Warn when a newly typed location name matches an existing one.
    (function()
    {
        var $name = $('#mec_location_name');
        var $select = $('#mec_location_id');
        if(!$name.length || !$select.length) return;

        var loc = (typeof mec_admin_localize !== 'undefined') ? mec_admin_localize : ((typeof mecdata !== 'undefined') ? mecdata : {});

        // Build {lowercaseName: termId} from existing location options (exclude the special 0 / 1).
        var existing = {};
        $select.find('option').each(function()
        {
            var v = $(this).attr('value');
            if(v === '0' || v === '1') return;
            existing[$.trim($(this).text()).toLowerCase()] = v;
        });

        var $warn = null;
        function clearWarn(){ if($warn){ $warn.remove(); $warn = null; } }

        function showWarn(existingId)
        {
            $warn = $('<div class="mec-location-duplicate-warn" role="alert"></div>');
            $('<span class="mec-location-duplicate-text"></span>')
                .text(loc.location_duplicate_warn || 'A location with this name already exists.')
                .appendTo($warn);
            var $btn = $('<button type="button" class="button mec-location-use-existing"></button>')
                .text(loc.location_use_existing || 'Use existing');
            $btn.on('click', function()
            {
                // Switch the select to the existing location; mec_location_toggle() hides the new form.
                $select.val(existingId).trigger('change');
                clearWarn();
            });
            $warn.append(' ').append($btn);
            $name.closest('.mec-form-row').after($warn);
        }

        $name.on('input change', function()
        {
            clearWarn();
            var val = $.trim($name.val() || '').toLowerCase();
            if(!val) return;
            if(existing.hasOwnProperty(val)) showWarn(existing[val]);
        });
    })();

    $('#mec_bookings_limit_unlimited').on('change', function()
    {
        mec_bookings_unlimited_toggle();
    });

    $('#mec_add_in_days').on('click', function()
    {
        var allday = $(this).data('allday');

        var start = $('#mec_exceptions_in_days_start_date').val();
        if(start === '') return false;

        var end = $('#mec_exceptions_in_days_end_date').val();
        if(end === '') return false;

        var start_hour = $('#mec_exceptions_in_days_start_hour').val();
        if(start_hour.length === 1) start_hour = '0'+start_hour;

        var start_minutes = $('#mec_exceptions_in_days_start_minutes').val();
        if(start_minutes.length === 1) start_minutes = '0'+start_minutes;

        var start_ampm = $('#mec_exceptions_in_days_start_ampm').val();
        if(typeof start_ampm === 'undefined') start_ampm = '';

        var end_hour = $('#mec_exceptions_in_days_end_hour').val();
        if(end_hour.length === 1) end_hour = '0'+end_hour;

        var end_minutes = $('#mec_exceptions_in_days_end_minutes').val();
        if(end_minutes.length === 1) end_minutes = '0'+end_minutes;

        var end_ampm = $('#mec_exceptions_in_days_end_ampm').val();
        if(typeof end_ampm === 'undefined') end_ampm = '';

        var value = start + ':' + end + ':' + start_hour + '-' + start_minutes + '-' + start_ampm + ':' + end_hour + '-' + end_minutes + '-' + end_ampm;
        var label = start + ' <span class="mec-time-picker-label '+(allday ? 'mec-util-hidden' : '')+'">' + start_hour + ':' + start_minutes + ' ' + start_ampm + '</span> - ' + end + ' <span class="mec-time-picker-label '+(allday ? 'mec-util-hidden' : '')+'">' + end_hour + ':' + end_minutes + ' ' + end_ampm + '</span>';

        // Don't add exactly same occurrences
        if($('#mec_in_days input[value="'+value+'"]').length > 0) return false;

        var $key = $('#mec_new_in_days_key');

        var key = $key.val();
        var html = $('#mec_new_in_days_raw').html().replace(/:i:/g, key).replace(/:val:/g, value).replace(/:label:/g, label);

        $('#mec_in_days').append(html);
        $key.val(parseInt(key)+1);
    });

    $('#mec_edit_in_days').on('click', function()
    {
        // Form
        const $form = $('#mec-in-days-form');

        const modify_id = $form.data('modify');
        const $row = $('#mec_in_days_row'+modify_id);

        var allday = $(this).data('allday');

        var start = $('#mec_exceptions_in_days_start_date').val();
        if(start === '') return false;

        var end = $('#mec_exceptions_in_days_end_date').val();
        if(end === '') return false;

        var start_hour = $('#mec_exceptions_in_days_start_hour').val();
        if(start_hour.length === 1) start_hour = '0'+start_hour;

        var start_minutes = $('#mec_exceptions_in_days_start_minutes').val();
        if(start_minutes.length === 1) start_minutes = '0'+start_minutes;

        var start_ampm = $('#mec_exceptions_in_days_start_ampm').val();
        if(typeof start_ampm === 'undefined') start_ampm = '';

        var end_hour = $('#mec_exceptions_in_days_end_hour').val();
        if(end_hour.length === 1) end_hour = '0'+end_hour;

        var end_minutes = $('#mec_exceptions_in_days_end_minutes').val();
        if(end_minutes.length === 1) end_minutes = '0'+end_minutes;

        var end_ampm = $('#mec_exceptions_in_days_end_ampm').val();
        if(typeof end_ampm === 'undefined') end_ampm = '';

        var value = start + ':' + end + ':' + start_hour + '-' + start_minutes + '-' + start_ampm + ':' + end_hour + '-' + end_minutes + '-' + end_ampm;
        var label = start + ' <span class="mec-time-picker-label '+(allday ? 'mec-util-hidden' : '')+'">' + start_hour + ':' + start_minutes + ' ' + start_ampm + '</span> - ' + end + ' <span class="mec-time-picker-label '+(allday ? 'mec-util-hidden' : '')+'">' + end_hour + ':' + end_minutes + ' ' + end_ampm + '</span>';

        $row.find($('input[type=hidden]')).val(value);
        $row.find($('.mec-in-days-day')).html(label);

        // Reset Dates
        $form.parent().find($('input[type=text]')).val('');

        // Modification Mode
        $form.removeClass('mec-in-days-edit-mode').addClass('mec-in-days-add-mode').removeData('modify');
    });

    $('#mec_cancel_in_days').on('click', function()
    {
        // Form
        let $form = $('#mec-in-days-form');

        // Reset Dates
        $form.parent().find($('input[type=text]')).val('');

        // Modification Mode
        $form.removeClass('mec-in-days-edit-mode').addClass('mec-in-days-add-mode').removeData('modify');
    });

    // Keyboard support for Custom Days card action buttons (audit #6).
    $('#mec_in_days').on('keydown', '[role="button"]', function(e)
    {
        if(e.key === 'Enter' || e.key === ' '){ e.preventDefault(); this.click(); }
    });

    $('#mec_add_not_in_days').on('click', function()
    {
        let date = $('#mec_exceptions_not_in_days_date').val();
        if(date === '') return false;

        let d = date.replaceAll('-', '');
        d = d.replaceAll('/', '');
        d = d.replaceAll('.', '');


        let $wrapper = $('#mec_not_in_days');
        let $key = $('#mec_new_not_in_days_key');

        let c = 'mec-date-'+d;
        if($wrapper.find($('.'+c)).length) return;

        let key = $key.val();
        let html = $('#mec_new_not_in_days_raw').html().replace(/:i:/g, key).replace(/:d:/g, d).replace(/:val:/g, date);

        $wrapper.append(html);
        $key.val(parseInt(key)+1);
    });

    $('#mec_add_ticket_button').on('click', function()
    {
        let $key = $('#mec_new_ticket_key');
        let key = $key.val();
        let html = $('#mec_new_ticket_raw').html().replace(/:i:/g, key);

        $('#mec_tickets').append(html);
        $key.val(parseInt(key)+1);

        mec_init_sortable_sections();

        $('.mec_add_price_date_button').off('click').on('click', function()
        {
            mec_handle_add_price_date_button(this);
        });

        $.each($(".mec-select2"), function(i,v)
        {
            if($(v).attr('name').search(":i:") > 0)
            {
                return;
            }

            if(typeof $(v).data('select2-id') == 'undefined')
            {
                $(v).select2();
            }
        });

        trigger_period_picker();
    });

    $('.mec_add_price_date_button').off('click').on('click', function()
    {
        mec_handle_add_price_date_button(this);
    });

    mec_hourly_schedule_add_day_listener();
    mec_init_sortable_sections();

    $('#mec_add_fee_button').on('click', function()
    {
        var key = $('#mec_new_fee_key').val();
        var html = $('#mec_new_fee_raw').html().replace(/:i:/g, key);

        $('#mec_fees_list').append(html);
        $('#mec_new_fee_key').val(parseInt(key)+1);

        mec_init_sortable_sections();
    });

    $('#mec_add_ticket_variation_button').on('click', function()
    {
        var key = $('#mec_new_ticket_variation_key').val();
        var html = $('#mec_new_ticket_variation_raw').html().replace(/:i:/g, key);

        $('#mec_ticket_variations_list').append(html);
        $('#mec_new_ticket_variation_key').val(parseInt(key)+1);

        mec_init_sortable_sections();
    });

    $('.mec-form-row.mec-available-color-row span').on('click', function()
    {
        $('.mec-form-row.mec-available-color-row span').removeClass('color-selected');
        $(this).addClass('color-selected');
    });

    $('#mec_reg_form_field_types button').on('click', function()
    {
        var type = $(this).data('type');

        if (type == 'mec_email') {
            if ($('#mec_reg_form_fields').find('input[value="mec_email"][type="hidden"]').length) {
                return false;
            }
        }

        if (type == 'name') {
            if ($('#mec_reg_form_fields').find('input[value="name"][type="hidden"]').length) {
                return false;
            }
        }

        var key  = $('#mec_new_reg_field_key').val();
        var html = $('#mec_reg_field_'+type).html().replace(/:i:/g, key);

        $('#mec_reg_form_fields').append(html);
        $('#mec_new_reg_field_key').val(parseInt(key)+1);

        // Set onclick listener for add option fields
        mec_reg_fields_option_listeners();
        mec_refresh_booking_condition_editors('reg');
    });

    // Set onclick listener for add option fields
    mec_reg_fields_option_listeners();
    mec_refresh_booking_condition_editors('reg');

    // Advanced Repeating
    $('#mec-advanced-wraper ul > ul > li').click(function()
    {
        if($(this).attr('class') == '') $(this).attr('class', 'mec-active');
        else $(this).attr('class', '');

        $('#mec_date_repeat_advanced').val($('#mec-advanced-wraper div:first-child > ul').find('.mec-active').find('span').text().slice(0, -1));
    });

    $('#mec_event_form_field_types button').on('click', function()
    {
        var type = $(this).data('type');

        var key  = $('#mec_new_event_field_key').val();
        var html = $('#mec_event_field_'+type).html().replace(/:i:/g, key);

        $('#mec_event_form_fields').append(html);
        $('#mec_new_event_field_key').val(parseInt(key)+1);

        // Set onclick listener for add option fields
        mec_event_fields_option_listeners();
    });

    // Set onclick listener for add option fields
    mec_event_fields_option_listeners();

    $('#mec_bfixed_form_field_types button').on('click', function()
    {
        var type = $(this).data('type');

        var key  = $('#mec_new_bfixed_field_key').val();
        var html = $('#mec_bfixed_field_'+type).html().replace(/:i:/g, key);

        $('#mec_bfixed_form_fields').append(html);
        $('#mec_new_bfixed_field_key').val(parseInt(key)+1);

        // Set onclick listener for add option fields
        mec_bfixed_fields_option_listeners();
        mec_refresh_booking_condition_editors('bfixed');
    });

    // Set onclick listener for add option fields
    mec_bfixed_fields_option_listeners();
    mec_refresh_booking_condition_editors('bfixed');

    jQuery(document).on('change', '.mec-booking-condition-enabled, .mec-booking-condition-source, .mec-booking-condition-option', function()
    {
        const $trigger = jQuery(this);
        const $conditionBox = jQuery(this).closest('.mec-booking-condition-box');
        const prefix = String($conditionBox.data('conditionPrefix') || '');

        if($trigger.hasClass('mec-booking-condition-source'))
        {
            $conditionBox.data('currentSourceFieldId', String($trigger.val() || ''));
            $conditionBox.data('currentOptionKey', '');
            $conditionBox.find('.mec-booking-condition-option').val('');
        }
        else if($trigger.hasClass('mec-booking-condition-option'))
        {
            $conditionBox.data('currentOptionKey', String($trigger.val() || ''));
        }

        mec_refresh_booking_condition_editor($conditionBox);
        if(prefix) mec_refresh_booking_condition_editors(prefix);
    });

    jQuery(document).on('input change', '#mec_reg_form_fields input[name*="[label]"], #mec_bfixed_form_fields input[name*="[label]"]', function()
    {
        const $field = jQuery(this).closest('li[id^="mec_reg_fields_"], li[id^="mec_bfixed_fields_"]');
        if($field.attr('id') && $field.attr('id').indexOf('mec_reg_fields_') === 0) mec_refresh_booking_condition_editors('reg');
        if($field.attr('id') && $field.attr('id').indexOf('mec_bfixed_fields_') === 0) mec_refresh_booking_condition_editors('bfixed');
    });

    // Additional Organizers
    mec_additional_organizers_listeners();

    // Additional Locations
    mec_additional_locations_listeners();

    // Show / Hide Password
    $('.mec-show-hide-password').on('click', function()
    {
        var $input = $(this).siblings("input");
        var current = $input.attr('type');

        if(current === 'password') $input.attr('type', 'text');
        else $input.attr('type', 'password');
    });

    // FAQ
    $('#mec_add_faq_button').on('click', function()
    {
        const $key = $('#mec_new_faq_key');
        const key = $key.val();
        const html = $('#mec_new_faq_raw').html().replace(/:i:/g, key);

        $('#mec_faq_list').append(html);
        $key.val(parseInt(key)+1);

        mec_init_sortable_sections();
    });

    // Appointments
    // Move the selector to the metabox header
    var $header = $('#mec_metabox_details .hndle');
    // If .hndle is not found, try .postbox-header (newer WP versions)
    if ($header.length === 0) {
        $header = $('#mec_metabox_details .postbox-header');
    }
    $('.mec-event-appointment-type-wrap').appendTo($header).removeClass('mec-util-hidden').on('click', function(e) {
        e.stopPropagation();
    });

    $('#mec_entity_type_select').on('change', function()
    {
        const entity_type = $(this).val();
        const $fes_form = $('#mec_fes_form');
        const $metabox_details = $('#mec_metabox_details');
        const $appointment_form_wrapper = $('.mec-appointment-form-wrap');

        if (entity_type === 'appointment')
        {
            $fes_form.removeClass('mec-entity-type-event').addClass('mec-entity-type-appointment');
            $metabox_details.removeClass('mec-entity-type-event').addClass('mec-entity-type-appointment');

            $appointment_form_wrapper.removeClass('mec-util-hidden');
        }
        else
        {
            $fes_form.removeClass('mec-entity-type-appointment').addClass('mec-entity-type-event');
            $metabox_details.removeClass('mec-entity-type-appointment').addClass('mec-entity-type-event');

            $appointment_form_wrapper.addClass('mec-util-hidden');
        }
    });

    $('#mec_entity_type_select').trigger('change');

    const $repeatType = $('#mec_appointments_availability_repeat_type');
    if($.fn.datepicker)
    {
        $('#mec_appointments_start_date').datepicker({
            changeYear: true,
            changeMonth: true,
            dateFormat: datepicker_format,
            gotoCurrent: true,
            yearRange: 'c-3:c+5',
        });
    }
    function mec_toggle_repeat_type()
    {
        const val = $repeatType.val();
        if (val === 'no_repeat')
        {
            $('.lsd-apt-days-wrapper').addClass('mec-util-hidden');
            $('.lsd-apt-adjusted-title').addClass('mec-util-hidden');
            $('.lsd-apt-start-date-wrapper').addClass('mec-util-hidden');
        }
        else
        {
            $('.lsd-apt-days-wrapper').removeClass('mec-util-hidden');
            $('.lsd-apt-adjusted-title').removeClass('mec-util-hidden');
            $('.lsd-apt-start-date-wrapper').removeClass('mec-util-hidden');
        }
    }
    $repeatType.on('change', mec_toggle_repeat_type);
    mec_toggle_repeat_type();

    $(document).on('click', '.lsd-apt-day-icon-remove', function()
    {
        const $button = $(this);
        const $timeslot = $button.parent();
        const $timeslots = $button.parents('.lsd-apt-day-timeslots');

        $timeslot.remove();
        if ($timeslots.find('.lsd-apt-day-timeslot-wrapper').length === 0)
        {
            $timeslots.find('.lsd-apt-day-timeslots-unavailable').removeClass('mec-util-hidden');
        }
    });

    $(document).on('click', '.lsd-apt-day-icon-plus', function()
    {
        const $button = $(this);
        let key = $button.data('key');

        const $day = $button.parents('.lsd-apt-day-wrapper');
        const day = $day.data('key');

        const $timeslots = $day.find('.lsd-apt-day-timeslots-wrapper');
        const $template = $('#lsd-apt-day-templates-'+day+'-timeslot');

        key = key + 1;
        const html = $template.html().replace(/:t:/g, key);
        $timeslots.append(html);

        $button.data('key', key);
        $day.find('.lsd-apt-day-timeslots-unavailable').addClass('mec-util-hidden');
    });

    $(document).on('click', '.lsd-apt-day-icon-copy', function ()
    {
        const $button = $(this);
        const $sourceDay = $button.closest('.lsd-apt-day-wrapper');
        const sourceDayKey = $sourceDay.data('key');

        const $weekWrapper = $sourceDay.parent();
        const $sourceTimeslots = $sourceDay.find('.lsd-apt-day-timeslots-wrapper .lsd-apt-day-timeslot-wrapper');

        $weekWrapper.find('.lsd-apt-day-wrapper').each(function ()
        {
            const $targetDay = $(this);

            const targetDayKey = $targetDay.data('key');
            if (targetDayKey === sourceDayKey) return;

            const isActive = $targetDay.find('.lsd-apt-day-timeslots-wrapper .lsd-apt-day-timeslot-wrapper').length > 0;
            if (!isActive) return;

            const $targetWrapper = $targetDay.find('.lsd-apt-day-timeslots-wrapper');
            $targetWrapper.empty();

            $sourceTimeslots.each(function ()
            {
                const $sourceSlot = $(this);
                const $clone = $sourceSlot.clone(true);

                $sourceSlot.find('select').each(function (i) {
                    const value = $(this).val();
                    $clone.find('select').eq(i).val(value);
                });

                $sourceSlot.find('input').each(function (i) {
                    const value = $(this).val();
                    $clone.find('input').eq(i).val(value);
                });

                $clone.find('[name], [id], [for]').each(function ()
                {
                    $.each(this.attributes, function () {
                        if (this.name === 'name' || this.name === 'id' || this.name === 'for') {
                            this.value = this.value
                            .replace(`[availability][${sourceDayKey}]`, `[availability][${targetDayKey}]`)
                            .replace(`availability_${sourceDayKey}_`, `availability_${targetDayKey}_`);
                        }
                    });
                });

                $targetWrapper.append($clone);
            });
        });
    });

    $(document).on('click', '.lsd-apt-adjusted-day-add', function()
    {
        const $wrapper = $('.lsd-apt-adjusted-days-wrapper');
        let key = $wrapper.data('key') || 0;

        key = key + 1;
        let html = $('#lsd-apt-adjusted-template-day').html();
        html = html.replace(/:i:/g, key);

        $wrapper.append(html);
        $wrapper.data('key', key);

        if($.fn.datepicker)
        {
            $wrapper.find('.mec-apt-date-picker').last().datepicker({
                changeYear: true,
                changeMonth: true,
                dateFormat: datepicker_format,
                gotoCurrent: true,
                yearRange: 'c-3:c+5',
            });
        }
    });

    $(document).on('click', '.lsd-apt-adjusted-day-remove', function()
    {
        $(this).closest('.lsd-apt-day-wrapper').remove();
    });

    $(document).on('click', '.lsd-apt-adj-day-icon-plus', function()
    {
        const $button = $(this);
        let key = $button.data('key');
        const $day = $button.closest('.lsd-apt-day-wrapper');
        const day = $day.data('day');
        const $timeslots = $day.find('.lsd-apt-day-timeslots-wrapper');

        key = key + 1;
        let html = $('#lsd-apt-adjusted-template-timeslot').html();

        html = html.replace(/:i:/g, day).replace(/:t:/g, key);
        $timeslots.append(html);

        $button.data('key', key);
        $day.find('.lsd-apt-day-timeslots-unavailable').addClass('mec-util-hidden');
    });
});

function trigger_period_picker()
{
    jQuery('.mec-date-picker-start').datepicker(
    {
        changeYear: true,
        changeMonth: true,
        dateFormat: 'yy-mm-dd',
        gotoCurrent: true,
        yearRange: 'c-1:c+5',
        onSelect: function(date)
        {
            const selectedDate = new Date(date);
            const endDate = new Date(selectedDate.getTime());

            const $end_picker = jQuery(this).next();
            $end_picker.datepicker("option", "minDate", endDate);
            $end_picker.datepicker("option", "maxDate", '+5y');
            jQuery(this).trigger('change');
        }
    });

    jQuery('.mec-date-picker-end').datepicker(
    {
        changeYear: true,
        changeMonth: true,
        dateFormat: 'yy-mm-dd',
        gotoCurrent: true,
        yearRange: 'c-1:c+5',
    });
}

function mec_location_toggle()
{
    if(jQuery('#mec_location_id').val() != '0') jQuery('#mec_location_new_container').hide();
    else jQuery('#mec_location_new_container').show();
}

function mec_organizer_toggle()
{
    if(jQuery('#mec_organizer_id').val() != '0') jQuery('#mec_organizer_new_container').hide();
    else jQuery('#mec_organizer_new_container').show();
}

function mec_repeat_toggle()
{
    if(jQuery('#mec_repeat').is(':checked')) jQuery('.mec-form-repeating-event-row').show();
    else jQuery('.mec-form-repeating-event-row').hide();
}

// Update the "Repeat Interval" unit suffix (days/weeks/months/years) based on
// the selected repeat type. Audit issue #17.
function mec_update_interval_unit()
{
    var $unit = jQuery('#mec_repeat_interval').siblings('.mec-interval-unit');
    if(!$unit.length) return;
    var loc = (typeof mec_admin_localize !== 'undefined') ? mec_admin_localize : ((typeof mecdata !== 'undefined') ? mecdata : {});
    var units = loc.interval_units || {};
    var type = jQuery('#mec_repeat_type').val();
    $unit.text(units[type] || '');
}

function mec_repeat_type_toggle()
{
    var repeat_type = jQuery('#mec_repeat_type').val();

    if(repeat_type == 'certain_weekdays')
    {
        jQuery('#mec_repeat_interval_container').hide();
        jQuery('#mec_repeat_certain_weekdays_container').show();
        jQuery('#mec_exceptions_in_days_container').hide();
        jQuery('#mec_end_wrapper').show();
        jQuery('#mec-advanced-wraper').hide();
    }
    else if(repeat_type == 'custom_days')
    {
        jQuery('#mec_repeat_interval_container').hide();
        jQuery('#mec_repeat_certain_weekdays_container').hide();
        jQuery('#mec_exceptions_in_days_container').show();
        jQuery('#mec_end_wrapper').hide();
        jQuery('#mec-advanced-wraper').hide();
    }
    else if(repeat_type == 'advanced')
    {
        jQuery('#mec_repeat_interval_container').hide();
        jQuery('#mec_repeat_certain_weekdays_container').hide();
        jQuery('#mec_exceptions_in_days_container').hide();
        jQuery('#mec_end_wrapper').show();
        jQuery('#mec-advanced-wraper').show();
    }
    else if(repeat_type != 'daily' && repeat_type != 'weekly' && repeat_type != 'monthly')
    {
        jQuery('#mec_repeat_interval_container').hide();
        jQuery('#mec_repeat_certain_weekdays_container').hide();
        jQuery('#mec_exceptions_in_days_container').hide();
        jQuery('#mec_end_wrapper').show();
        jQuery('#mec-advanced-wraper').hide();
    }
    else
    {
        jQuery('#mec_repeat_interval_container').show();
        jQuery('#mec_repeat_certain_weekdays_container').hide();
        jQuery('#mec_exceptions_in_days_container').hide();
        jQuery('#mec_end_wrapper').show();
        jQuery('#mec-advanced-wraper').hide();
    }

    mec_update_interval_unit();
}

function mec_in_days_remove(i)
{
    var $row = jQuery('#mec_in_days_row'+i);
    // Fade-out animation before removal (audit #6).
    if($row.length)
    {
        $row.addClass('mec-in-days-removing');
        setTimeout(function(){ jQuery('#mec_in_days_row'+i).remove(); }, 180);
    }
}

function mec_in_days_edit(i)
{
    // Date
    let $row = jQuery('#mec_in_days_row'+i);
    let value = $row.find(jQuery('input[type=hidden]')).val();

    const values = value.split(':');
    const start_times = values[2].split('-')
    const end_times = values[3].split('-')

    // Form
    let $form = jQuery('#mec-in-days-form');

    // Set Dates
    jQuery('#mec_exceptions_in_days_start_date').val(values[0]);
    jQuery('#mec_exceptions_in_days_end_date').val(values[1]);

    // Set Times
    jQuery('#mec_exceptions_in_days_start_hour').val(parseInt(start_times[0]));
    jQuery('#mec_exceptions_in_days_start_minutes').val(parseInt(start_times[1]));
    jQuery('#mec_exceptions_in_days_start_ampm').val(start_times[2]);

    jQuery('#mec_exceptions_in_days_end_hour').val(parseInt(end_times[0]));
    jQuery('#mec_exceptions_in_days_end_minutes').val(parseInt(end_times[1]));
    jQuery('#mec_exceptions_in_days_end_ampm').val(end_times[2]);

    // Modification Mode
    $form.removeClass('mec-in-days-add-mode').addClass('mec-in-days-edit-mode').data('modify', i);
}

function mec_not_in_days_remove(i)
{
    jQuery('#mec_not_in_days_row'+i).remove();
}

function mec_bookings_unlimited_toggle()
{
    jQuery('#mec_bookings_limit').toggleClass('mec-util-hidden');
}

function mec_hourly_schedule_add_day_listener()
{
    jQuery('.mec-add-hourly-schedule-day-button').each(function()
    {
        jQuery(this).off('click').on('click', function()
        {
            var k = jQuery(this).data('key');
            var raw = jQuery(this).data('raw');
            var append = jQuery(this).data('append');

            var key = jQuery(k).val();
            var html = jQuery(raw).html().replace(/:d:/g, key).replace(/:dd:/g, parseInt(key)+1);

            jQuery(append).append(html);
            jQuery(k).val(parseInt(key)+1);

            mec_hourly_schedule_listeners();
        });

        mec_hourly_schedule_listeners();
    });
}

function mec_bookings_after_occurrence_cancel_listener()
{
    jQuery('.mec-occurrences-bookings-after-occurrences-cancel').off('change').on('change', function()
    {
        const $dropdown = jQuery(this);
        const value = $dropdown.val();
        const $moveWrapper = $dropdown.next();

        if(value === 'move' || value === 'move_notify')
        {
            $moveWrapper.removeClass('w-hidden');
        }
        else
        {
            $moveWrapper.addClass('w-hidden');
        }
    });
}

function mec_hourly_schedule_listeners()
{
    jQuery('.mec-add-hourly-schedule-button').off('click').on('click', function()
    {
        var prefix = jQuery(this).data('prefix');
        var day = jQuery(this).data('day');
        var $key = jQuery('#'+prefix+'mec_new_hourly_schedule_key'+day);

        var key = $key.val();
        var html = jQuery('#'+prefix+'mec_new_hourly_schedule_raw'+day).html().replace(/:i:/g, key).replace(/:d:/g, day).replace();
        var g_field_id = prefix+"-hourly_schedules-"+day+"-schedules-:i:-description";
        var field_id = prefix+"-hourly_schedules-"+day+"-schedules-"+key+"-description";
        html = html.replace(g_field_id,field_id);
        html = html.replace(':k:',key);
        jQuery('#'+prefix+'mec_hourly_schedules'+day).append(html);
        $key.val(parseInt(key)+1);

        wp.editor.initialize("mec"+field_id,{
            tinymce: {
                wpautop: true,
                plugins : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
                toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',
                toolbar2: 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help'
            },
            quicktags: true,
            mediaButtons: false,
        });
    });

    jQuery(".mec-hourly-schedule-schedules").sortable({
        handle: '.mec_field_sort'
    });
}

function mec_hourly_schedule_remove(day, i, prefix)
{
    jQuery("#"+prefix+"mec_hourly_schedule_row"+day+'_'+i).remove();
}

function mec_hourly_schedule_day_remove(day, prefix)
{
    jQuery("#"+prefix+"mec_meta_box_hourly_schedule_day_"+day).remove();
}

function mec_ticket_remove(i)
{
    jQuery("#mec_ticket_row"+i).remove();
}

function mec_set_event_color(color)
{
    try
    {
        jQuery("#mec_event_color").wpColorPicker('color', '#'+color);
    }
    catch(e)
    {
        jQuery("#mec_event_color").val(color);
    }
}

function mec_remove_fee(key)
{
    jQuery("#mec_fee_row"+key).remove();
}

function mec_remove_ticket_variation(key, id_prefix)
{
    jQuery("#mec_"+id_prefix+"_row"+key).remove();
}

function add_variation_per_ticket(ticket_id)
{
    var $input = jQuery('#mec_new_variation_per_ticket_key');

    var key = $input.val();
    var html = jQuery('#mec_new_variation_per_ticket_raw'+ticket_id).html().replace(/:v:/g, key);

    jQuery('#mec_ticket_variations_list'+ticket_id).append(html);
    $input.val(parseInt(key)+1);

    mec_init_sortable_sections();
}

function mec_init_sortable_instance(selector, options)
{
    jQuery(selector).each(function()
    {
        var $element = jQuery(this);

        if(!$element.length) return;

        if(typeof $element.data('ui-sortable') === 'undefined')
        {
            $element.sortable(options);
        }
        else
        {
            $element.sortable('refresh');
        }
    });
}

function mec_init_sortable_sections()
{
    if(typeof jQuery.fn.sortable === 'undefined') return;

    mec_init_sortable_instance('#mec_tickets', { handle: '.mec_field_sort', items: '> .mec_ticket_row' });
    mec_init_sortable_instance('#mec_fees_list', { handle: '.mec_field_sort', items: '> .mec-box' });
    mec_init_sortable_instance('[id^="mec_ticket_variations_list"]', { handle: '.mec_field_sort', items: '> .mec_ticket_variation_row' });
    mec_init_sortable_instance('#mec_faq_list', { handle: '.mec_field_sort', items: '> .mec_faq_row' });
}

function mec_get_booking_condition_form_fields(prefix)
{
    return jQuery('#mec_' + prefix + '_form_fields > li');
}

function mec_get_booking_condition_field_id($field, prefix)
{
    return String(($field.attr('id') || '').replace('mec_' + prefix + '_fields_', ''));
}

function mec_get_booking_condition_field_type($field, prefix)
{
    const fieldId = mec_get_booking_condition_field_id($field, prefix);
    return String($field.find('input[name="mec[' + prefix + '_fields][' + fieldId + '][type]"]').val() || '');
}

function mec_get_booking_condition_field_label($field, prefix)
{
    const fieldId = mec_get_booking_condition_field_id($field, prefix);
    const $labelInput = $field.find('input[name="mec[' + prefix + '_fields][' + fieldId + '][label]"]').first();
    const fallback = jQuery.trim($field.find('.mec_' + prefix + '_field_type').first().text() || '');

    return jQuery.trim(($labelInput.val() || fallback || ''));
}

function mec_get_booking_condition_field_option_list($field, prefix)
{
    const fieldId = mec_get_booking_condition_field_id($field, prefix);
    const options = [];

    $field.find('input[name^="mec[' + prefix + '_fields][' + fieldId + '][options]"][name$="[label]"]').each(function()
    {
        const name = jQuery(this).attr('name') || '';
        const match = name.match(/\[options\]\[(.*?)\]\[label\]$/);
        if(!match) return;

        options.push({
            key: String(match[1]),
            label: jQuery.trim(jQuery(this).val() || '')
        });
    });

    return options;
}

function mec_booking_condition_field_has_duplicate_options($field, prefix)
{
    const seen = {};
    const options = mec_get_booking_condition_field_option_list($field, prefix);

    for(let i = 0; i < options.length; i++)
    {
        const label = options[i].label;
        if(!label) continue;
        if(seen[label]) return true;

        seen[label] = true;
    }

    return false;
}

function mec_refresh_booking_condition_editor($conditionBox)
{
    if(!$conditionBox || !$conditionBox.length) return;

    const prefix = String($conditionBox.data('conditionPrefix') || '');
    const currentFieldId = String($conditionBox.data('conditionFieldId') || '');
    const $enabled = $conditionBox.find('.mec-booking-condition-enabled');
    const $controls = $conditionBox.find('.mec-booking-condition-controls');
    const $source = $conditionBox.find('.mec-booking-condition-source');
    const $option = $conditionBox.find('.mec-booking-condition-option');
    const $matchType = $conditionBox.find('.mec-booking-condition-match-type');
    const $message = $conditionBox.find('.mec-booking-condition-message');
    const enabled = $enabled.is(':checked');
    const strings = (typeof mec_admin_localize !== 'undefined' && mec_admin_localize.booking_conditions) ? mec_admin_localize.booking_conditions : {};

    $controls.toggleClass('mec-util-hidden', !enabled);
    if(!enabled)
    {
        $message.text('');
        return;
    }

    const currentSourceId = String($source.val() || $conditionBox.data('currentSourceFieldId') || '');
    const currentOptionKey = String($option.val() || $conditionBox.data('currentOptionKey') || '');
    const sourceFields = [];
    const sourceMap = {};

    mec_get_booking_condition_form_fields(prefix).each(function()
    {
        const $field = jQuery(this);
        const fieldId = mec_get_booking_condition_field_id($field, prefix);
        const fieldType = mec_get_booking_condition_field_type($field, prefix);

        if(!fieldId) return;
        if(fieldId === currentFieldId) return false;
        if(jQuery.inArray(fieldType, ['checkbox', 'radio', 'select', 'agreement']) === -1) return;
        if(jQuery.inArray(fieldType, ['checkbox', 'radio', 'select']) !== -1 && mec_booking_condition_field_has_duplicate_options($field, prefix)) return;

        const sourceField = {
            id: fieldId,
            type: fieldType,
            label: mec_get_booking_condition_field_label($field, prefix)
        };

        sourceFields.push(sourceField);
        sourceMap[fieldId] = sourceField;
    });

    $source.empty().append('<option value="">' + (strings.select_field || 'Select a field') + '</option>');
    jQuery.each(sourceFields, function(index, sourceField)
    {
        $source.append('<option value="' + sourceField.id + '">' + sourceField.label + '</option>');
    });

    if(currentSourceId && sourceMap[currentSourceId]) $source.val(currentSourceId);

    const selectedSourceId = String($source.val() || '');
    const selectedSource = sourceMap[selectedSourceId] || null;

    $option.empty();

    if(!selectedSource)
    {
        $matchType.val('');
        $option.prop('disabled', true).append('<option value="">' + (strings.select_option || 'Select an option') + '</option>');
        $message.text(sourceFields.length ? (strings.select_controller || 'Choose a controller field.') : (strings.no_sources || 'No eligible controller fields are available.'));
        $conditionBox.data('currentSourceFieldId', '');
        $conditionBox.data('currentOptionKey', '');
        return;
    }

    if(selectedSource.type === 'agreement')
    {
        $matchType.val('checked');
        $option.prop('disabled', true).append('<option value="">' + (strings.checked_state || 'Checked') + '</option>');
        $message.text((strings.checked_message || 'This field will show when "%s" is checked.').replace('%s', selectedSource.label));
        $conditionBox.data('currentSourceFieldId', selectedSourceId);
        $conditionBox.data('currentOptionKey', '');
        return;
    }

    const $sourceField = jQuery('#mec_' + prefix + '_fields_' + selectedSource.id);
    const options = mec_get_booking_condition_field_option_list($sourceField, prefix);
    const matchType = selectedSource.type === 'checkbox' ? 'contains_option' : 'equals_option';

    $matchType.val(matchType);
    $option.prop('disabled', false).append('<option value="">' + (strings.select_option || 'Select an option') + '</option>');

    jQuery.each(options, function(index, option)
    {
        $option.append('<option value="' + option.key + '">' + option.label + '</option>');
    });

    if(currentOptionKey) $option.val(currentOptionKey);

    if(!$option.val())
    {
        $message.text((strings.select_value || 'Choose the value that should reveal this field.').replace('%s', selectedSource.label));
    }
    else
    {
        $message.text('');
    }

    $conditionBox.data('currentSourceFieldId', selectedSourceId);
    $conditionBox.data('currentOptionKey', String($option.val() || ''));
}

function mec_refresh_booking_condition_editors(prefix)
{
    jQuery('.mec-booking-condition-box[data-condition-prefix="' + prefix + '"]').each(function()
    {
        mec_refresh_booking_condition_editor(jQuery(this));
    });
}

function mec_reg_fields_option_listeners()
{
    jQuery('button.mec-reg-field-add-option').off('click').on('click', function()
    {
        var field_id = jQuery(this).data('field-id');
        var key = jQuery('#mec_new_reg_field_option_key_'+field_id).val();
        var html = jQuery('#mec_reg_field_option').html().replace(/:i:/g, key).replace(/:fi:/g, field_id);

        jQuery('#mec_reg_fields_'+field_id+'_options_container').append(html);
        jQuery('#mec_new_reg_field_option_key_'+field_id).val(parseInt(key)+1);
        mec_refresh_booking_condition_editors('reg');
    });

    if(typeof jQuery.fn.sortable !== 'undefined')
    {
        jQuery("#mec_reg_form_fields").sortable(
        {
            handle: '.mec_reg_field_sort',
            stop: function()
            {
                mec_refresh_booking_condition_editors('reg');
            }
        });

        jQuery(".mec_reg_fields_options_container").sortable(
        {
            handle: '.mec_reg_field_option_sort'
        });

        jQuery(".mec-hourly-schedule-days").sortable(
        {
            handle: 'h4'
        });

        jQuery(".mec-hourly-schedule-schedules").sortable({
            handle: '.mec_field_sort'
        });
    }
}

function mec_reg_fields_option_remove(field_key, key)
{
    jQuery("#mec_reg_fields_option_"+field_key+"_"+key).remove();
    mec_refresh_booking_condition_editors('reg');
}

function mec_reg_fields_remove(key)
{
    jQuery("#mec_reg_fields_"+key).remove();
    mec_refresh_booking_condition_editors('reg');
}

function mec_handle_add_price_date_button(e)
{
    var key = jQuery(e).data('key');
    var p = jQuery('#mec_new_ticket_price_key_'+key).val();
    var html = jQuery('#mec_new_ticket_price_raw_'+key).html().replace(/:i:/g, key).replace(/:j:/g, p);

    jQuery('#mec-ticket-price-dates-'+key).append(html);
    jQuery('#mec_new_ticket_price_key_'+key).val(parseInt(p)+1);
    jQuery('#mec-ticket-price-dates-'+key+' .new_added').datepicker(
    {
        changeYear: true,
        changeMonth: true,
        dateFormat: datepicker_format,
        gotoCurrent: true,
        yearRange: 'c-3:c+5',
    });
}

function mec_ticket_price_remove(ticket_key, price_key)
{
    jQuery("#mec_ticket_price_raw_"+ticket_key+"_"+price_key).remove();
}

function mec_event_fields_option_listeners()
{
    jQuery('button.mec-event-field-add-option').off('click').on('click', function()
    {
        var field_id = jQuery(this).data('field-id');
        var key = jQuery('#mec_new_event_field_option_key_'+field_id).val();
        var html = jQuery('#mec_event_field_option').html().replace(/:i:/g, key).replace(/:fi:/g, field_id);

        jQuery('#mec_event_fields_'+field_id+'_options_container').append(html);
        jQuery('#mec_new_event_field_option_key_'+field_id).val(parseInt(key)+1);
    });

    if(typeof jQuery.fn.sortable !== 'undefined')
    {
        jQuery("#mec_event_form_fields").sortable(
        {
            handle: '.mec_event_field_sort'
        });

        jQuery(".mec_event_fields_options_container").sortable(
        {
            handle: '.mec_event_field_option_sort'
        });
    }
}

function mec_event_fields_option_remove(field_key, key)
{
    jQuery("#mec_event_fields_option_"+field_key+"_"+key).remove();
}

function mec_event_fields_remove(key)
{
    jQuery("#mec_event_fields_"+key).remove();
}

function mec_bfixed_fields_option_listeners()
{
    jQuery('button.mec-bfixed-field-add-option').off('click').on('click', function()
    {
        var field_id = jQuery(this).data('field-id');
        var key = jQuery('#mec_new_bfixed_field_option_key_'+field_id).val();
        var html = jQuery('#mec_bfixed_field_option').html().replace(/:i:/g, key).replace(/:fi:/g, field_id);

        jQuery('#mec_bfixed_fields_'+field_id+'_options_container').append(html);
        jQuery('#mec_new_bfixed_field_option_key_'+field_id).val(parseInt(key)+1);
        mec_refresh_booking_condition_editors('bfixed');
    });

    if(typeof jQuery.fn.sortable !== 'undefined')
    {
        jQuery("#mec_bfixed_form_fields").sortable(
        {
            handle: '.mec_bfixed_field_sort',
            stop: function()
            {
                mec_refresh_booking_condition_editors('bfixed');
            }
        });

        jQuery(".mec_bfixed_fields_options_container").sortable(
        {
            handle: '.mec_bfixed_field_option_sort'
        });
    }
}

function mec_bfixed_fields_option_remove(field_key, key)
{
    jQuery("#mec_bfixed_fields_option_"+field_key+"_"+key).remove();
    mec_refresh_booking_condition_editors('bfixed');
}

function mec_bfixed_fields_remove(key)
{
    jQuery("#mec_bfixed_fields_"+key).remove();
    mec_refresh_booking_condition_editors('bfixed');
}

function mec_additional_organizers_listeners()
{
    jQuery('#mec_additional_organizers_add').off('click').on('click', function()
    {
        var value = jQuery('.mec-additional-organizers select').val();
        if (!value) return;

        // Each organizer can be added only once.
        if (jQuery('.mec-additional-organizers-list input[name="mec[additional_organizer_ids][]"][value="' + value + '"]').length) return;

        var text = jQuery('.mec-additional-organizers select option:selected').text();

        var sortLabel = jQuery(this).data('sort-label');
        var removeLabel = jQuery(this).data('remove-label');

        jQuery('.mec-additional-organizers-list').append('<li><span class="mec-additional-organizer-sort">'+sortLabel+'</span> <span onclick="mec_additional_organizers_remove(this);" class="mec-additional-organizer-remove">'+removeLabel+'</span><input type="hidden" name="mec[additional_organizer_ids][]" value="'+value+'"><span class="mec_orgz_item_name">'+text+'</span></li>');

        mec_additional_organizers_listeners();
    });

    if(typeof jQuery.fn.sortable !== 'undefined')
    {
        jQuery(".mec-additional-organizers-list").sortable(
        {
            handle: '.mec-additional-organizer-sort'
        });
    }
}

function mec_additional_organizers_remove(element)
{
    jQuery(element).parent().remove();
}

function mec_additional_locations_listeners()
{
    jQuery('#mec_additional_locations_add').off('click').on('click', function()
    {
        var value = jQuery('.mec-additional-locations select').val();
        if (!value) return;

        // Each location can be added only once.
        if (jQuery('.mec-additional-locations-list input[name="mec[additional_location_ids][]"][value="' + value + '"]').length) return;

        var text = jQuery('.mec-additional-locations select option:selected').text();

        var sortLabel = jQuery(this).data('sort-label');
        var removeLabel = jQuery(this).data('remove-label');

        jQuery('.mec-additional-locations-list').append('<li><span class="mec-additional-location-sort">'+sortLabel+'</span> <span onclick="mec_additional_locations_remove(this);" class="mec-additional-location-remove">'+removeLabel+'</span><input type="hidden" name="mec[additional_location_ids][]" value="'+value+'"><span class="mec_loc_item_name">'+text+'</span></li>');

        mec_additional_locations_listeners();
    });

    if(typeof jQuery.fn.sortable !== 'undefined')
    {
        jQuery(".mec-additional-locations-list").sortable(
        {
            handle: '.mec-additional-location-sort'
        });
    }
}

function mec_additional_locations_remove(element)
{
    jQuery(element).parent().remove();
}

function mec_faq_remove(key)
{
    jQuery("#mec_faq_row"+key).remove();
}

jQuery(document).on('focus', '.mec_date_picker', function ()
{
    if (!jQuery(this).hasClass('hasDatepicker'))
    {
        jQuery(this).datepicker({
            changeYear: true,
            changeMonth: true,
            dateFormat: 'yy-mm-dd',
            gotoCurrent: true,
            yearRange: 'c-3:c+5',
        });
    }
});

// Ticket availability start/end: live-init + open for rows that missed (or
// were added after) trigger_period_picker(). Two quirks to handle:
// 1) jQuery UI only opens a picker from its own focus handler — one bound
//    BEFORE the current focus event — so we must explicitly show() here.
// 2) The hidden "#mec_new_ticket_raw" template gets its pickers initialized
//    on page load; that serializes a stale "hasDatepicker" class into rows
//    cloned from it, and jQuery UI refuses to attach to such elements
//    (_connectDatepicker bails on the class). So test for the actual
//    instance and strip the stale class before initializing.
jQuery(document).on('focus', '.mec-date-picker-start, .mec-date-picker-end', function ()
{
    var $input = jQuery(this);
    if(!jQuery.fn.datepicker) return;

    if(!$input.data('datepicker'))
    {
        $input.removeClass('hasDatepicker');

        var isStart = $input.hasClass('mec-date-picker-start');
        $input.datepicker({
            changeYear: true,
            changeMonth: true,
            dateFormat: 'yy-mm-dd',
            gotoCurrent: true,
            yearRange: 'c-1:c+5',
            onSelect: isStart ? function(date)
            {
                var endDate = new Date(date);
                var $end_picker = $input.next();
                $end_picker.datepicker("option", "minDate", endDate);
                $end_picker.datepicker("option", "maxDate", '+5y');
                $input.trigger('change');
            } : null,
        });

        // Initialize the sibling end picker eagerly so the start picker's
        // onSelect targets a live instance even before end is ever focused.
        if(isStart)
        {
            var $end = $input.next('.mec-date-picker-end');
            if($end.length && !$end.data('datepicker'))
            {
                $end.removeClass('hasDatepicker');
                $end.datepicker({
                    changeYear: true,
                    changeMonth: true,
                    dateFormat: 'yy-mm-dd',
                    gotoCurrent: true,
                    yearRange: 'c-1:c+5',
                });
            }
        }
    }

    $input.datepicker('show');
});

// Appointment date pickers (.mec-apt-date-picker): only the "Add a date"
// button initializes them, so server-rendered inputs (start date, existing
// adjusted days) never get a picker — and day-copy clones carry a stale
// "hasDatepicker" class without an instance. Live-init + open on focus,
// same pattern as the availability handlers above.
jQuery(document).on('focus', '.mec-apt-date-picker', function ()
{
    var $input = jQuery(this);
    if(!jQuery.fn.datepicker) return;

    if(!$input.data('datepicker'))
    {
        $input.removeClass('hasDatepicker');
        $input.datepicker({
            changeYear: true,
            changeMonth: true,
            dateFormat: datepicker_format,
            gotoCurrent: true,
            yearRange: 'c-3:c+5',
        });
    }

    $input.datepicker('show');
});

// ===== Basic / Advanced toggle (mec-basvanced-toggle) =====
// Delegated so it works wherever events.js loads: the admin editor AND the
// FES front-end form (backend.js, which used to bind this directly, is not
// loaded on the front-end). Replaces the old direct binding in backend.js.
jQuery(document).on('click', '.mec-basvanced-toggle .mec-backend-tab-item', function (e)
{
    e.preventDefault();

    var $item = jQuery(this);
    if($item.hasClass('mec-b-active-tab')) return;

    var $toggle = $item.closest('.mec-basvanced-toggle');
    var wrapper = $toggle.data('for');
    var method = $toggle.data('method');

    $item.parent().find('.mec-b-active-tab').removeClass('mec-b-active-tab');
    $item.addClass('mec-b-active-tab');

    var $wrapper = jQuery(wrapper);
    if(method === 'addition') $wrapper.find('.mec-basvanced-advanced').toggleClass('w-hidden');
    else
    {
        $wrapper.find('.mec-basvanced-basic').toggleClass('w-hidden');
        $wrapper.find('.mec-basvanced-advanced').toggleClass('w-hidden');
    }
});

// ===== Publish-validation fallback (crash-isolated) =====
// The main validation module lives INSIDE jQuery(document).ready and shares
// that callback with a dozen initializers — a runtime error anywhere above
// it would silently prevent its submit handler from ever being bound (no
// error surfaces at click time; the form simply submits). This top-level,
// document-delegated copy guarantees the critical guard — "cannot publish
// without a start date" + the wp-post-new-reload URL cleanup — survives any
// such failure. It is bound on document, so it runs AFTER the main
// form-level handler; if that one already handled the submit it stays
// silent (e.isDefaultPrevented()).
jQuery(document).on('submit', '#post', function (e)
{
    if(e.isDefaultPrevented()) return; // the main validation already handled it

    // Draft saves and previews are exempt from publish validation.
    var submitter = (e.originalEvent && e.originalEvent.submitter) ? jQuery(e.originalEvent.submitter) : null;
    if(submitter && submitter.attr('id') === 'save-post') return;
    if(jQuery('#wp-preview').val() === 'dopreview') return;

    var $startDate = jQuery('#mec_start_date');
    if(!$startDate.length) return;

    // Undo WP core's address-bar rewrite: wp-admin/js/post.js appends
    // "wp-post-new-reload=true" on publish-click of an auto-draft even when
    // the submit ends up blocked, leaving a misleading URL behind.
    if(window.history && window.history.replaceState && window.location.href.indexOf('wp-post-new-reload') > -1)
    {
        window.history.replaceState(null, null, window.location.href.replace(/([?&])wp-post-new-reload=true&?/, '$1').replace(/[?&]$/, ''));
    }

    if(jQuery.trim($startDate.val() || '') === '')
    {
        e.preventDefault();

        // Jump to the Date & Time tab so the field is visible.
        jQuery('#mec_metabox_details .mec-add-event-tabs-link[data-href="mec_meta_box_date_form"]').trigger('click');

        // Inline error on the field.
        $startDate.addClass('mec-field-invalid').attr('aria-invalid', 'true');
        var $err = $startDate.next('.mec-field-error');
        if(!$err.length) $err = jQuery('<span class="mec-field-error" role="alert"></span>').insertAfter($startDate);
        var msg = (typeof mec_admin_localize !== 'undefined' && mec_admin_localize.start_date_required) ? mec_admin_localize.start_date_required : 'Please enter a start date for the event.';
        $err.text(msg);

        // Notice card — self-contained so it renders even if the main module died.
        jQuery('.mec-notice-temp').remove();
        var $n = jQuery('<div/>', {
            'class': 'mec-notice mec-notice--error mec-notice-temp',
            html: '<div class="mec-notice-accent"></div><div class="mec-notice-body"><div class="mec-notice-title">Start date required</div><p class="mec-notice-text">' + msg + '</p><div class="mec-notice-actions"><button type="button" class="mec-notice-btn mec-notice-btn--ghost mec-notice-dismiss">Got it</button></div></div><div class="mec-notice-close" aria-label="Close" role="button" tabindex="0">&times;</div>'
        });
        jQuery('#poststuff').prepend($n);
        jQuery('html, body').animate({scrollTop: $n.offset().top - 40}, 200);
        $n.find('.mec-notice-dismiss, .mec-notice-close').on('click', function(){ $n.remove(); });

        $startDate.trigger('focus');
    }
});

// ===== Native-required feedback (e.g. required Event Data fields) =====
// When a field carries the HTML5 "required" attribute (custom Event Data
// fields can), the browser blocks submission BEFORE the submit event fires:
// none of the submit handlers above run, so no MEC notice appears and WP
// core's publish-click URL rewrite ("wp-post-new-reload=true") lingers in
// the address bar. The only signal is the non-bubbling "invalid" event —
// captured here on the document so the user still gets MEC-style feedback.
document.addEventListener('invalid', function (ev)
{
    var field = ev.target;
    if(!field || !field.form || field.form.id !== 'post') return;

    // Undo WP core's address-bar rewrite (same cleanup as the fallback).
    if(window.history && window.history.replaceState && window.location.href.indexOf('wp-post-new-reload') > -1)
    {
        window.history.replaceState(null, null, window.location.href.replace(/([?&])wp-post-new-reload=true&?/, '$1').replace(/[?&]$/, ''));
    }

    // Friendly field name: its label, aria-label, placeholder or title.
    var name = '';
    if(field.id)
    {
        var $l = jQuery('label[for="' + field.id + '"]').first();
        if($l.length) name = jQuery.trim($l.text());
    }
    if(!name) name = field.getAttribute('aria-label') || field.placeholder || field.title || '';

    jQuery('.mec-notice-temp').remove();
    var msg = name
        ? '"' + jQuery('<div/>').text(name).html() + '" ' + (field.validationMessage || 'is required.')
        : (field.validationMessage || 'Please fill in the required fields.');

    var $n = jQuery('<div/>', {
        'class': 'mec-notice mec-notice--error mec-notice-temp',
        html: '<div class="mec-notice-accent"></div><div class="mec-notice-body"><div class="mec-notice-title">Required field missing</div><p class="mec-notice-text">' + msg + '</p><div class="mec-notice-actions"><button type="button" class="mec-notice-btn mec-notice-btn--ghost mec-notice-dismiss">Got it</button></div></div><div class="mec-notice-close" aria-label="Close" role="button" tabindex="0">&times;</div>'
    });
    jQuery('#poststuff').prepend($n);
    jQuery('html, body').animate({scrollTop: $n.offset().top - 40}, 200);
    $n.find('.mec-notice-dismiss, .mec-notice-close').on('click', function(){ $n.remove(); });
}, true);
