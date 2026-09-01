/**
 * MEC product analytics — front-end relay (events 2 and 5).
 *
 * Exposes window.mecRelayBeacon(event, props, dedupKey):
 *   - sendBeacon to the admin-ajax relay endpoint (see Tracking\ClientRelay)
 *   - hard client-side dedup via sessionStorage when a dedupKey is given
 *
 * Also listens for MEC search/filter completions (the "mec_search_init"
 * document event every view triggers once results are shown) and relays
 * mec_calendar_discovery_used. The search phrase itself is NEVER collected —
 * only the action shape, the filter type, and a result-count bucket.
 */
(function () {
    'use strict';

    if (window.mecRelayBeacon) return; // double-enqueue guard

    var ajaxUrl = (typeof mecdata !== 'undefined' && mecdata.ajax_url) ? mecdata.ajax_url : null;

    function bucket(n) {
        n = parseInt(n, 10) || 0;
        if (n <= 0) return '0';
        if (n <= 4) return '1_4';
        if (n <= 20) return '5_20';
        if (n <= 100) return '21_100';
        return '101_plus';
    }

    window.mecRelayBeacon = function (event, props, dedupKey) {
        if (!ajaxUrl || !navigator.sendBeacon) return;

        if (dedupKey) {
            try {
                if (sessionStorage.getItem(dedupKey)) return;
                sessionStorage.setItem(dedupKey, '1');
            } catch (e) { /* private mode: fall through and send */ }
        }

        var body = new FormData();
        body.append('action', 'mec_track_event');
        body.append('event', event);
        body.append('props', JSON.stringify(props || {}));

        navigator.sendBeacon(ajaxUrl, body);
    };

    // ---- Event 5: mec_calendar_discovery_used -------------------------------

    // Remember WHAT the visitor used, so that when the results arrive
    // (mec_search_init) we can attach discovery_action and filter_type.
    var pending = { action: null, filter_type: null };

    // Search phrase field: mec_sf_s_{id}. Everything else in the search form
    // is a filter (month, category, label, location, organizer, …).
    var FILTER_BY_ID = [
        ['mec_sf_s', 'text'],
        ['mec_sf_address_s', 'text_address'],
        ['mec_sf_month', 'month'],
        ['mec_sf_year', 'year'],
        ['mec_sf_category', 'category'],
        ['mec_sf_label', 'label'],
        ['mec_sf_location', 'location'],
        ['mec_sf_organizer', 'organizer'],
        ['mec_sf_speaker', 'speaker'],
        ['mec_sf_sponsor', 'sponsor'],
        ['mec_sf_event_type', 'event_type'],
        ['mec_sf_event_cost', 'cost'],
        ['mec_sf_timepicker', 'time'],
        ['mec_sf_date', 'date']
    ];

    function filterTypeFromId(id) {
        for (var i = 0; i < FILTER_BY_ID.length; i++) {
            if (id && id.indexOf(FILTER_BY_ID[i][0]) === 0) return FILTER_BY_ID[i][1];
        }
        return 'other';
    }

    jQuery(function ($) {
        if (!$ || !$.fn) return;

        // Any change inside a MEC search form records the initiating action.
        $(document).on('change', 'form[id^="mec_search_form_"] select, form[id^="mec_search_form_"] input', function () {
            var id = (this.id || '');
            pending.action = (id.indexOf('mec_sf_s') === 0) ? 'search' : 'filter';
            pending.filter_type = filterTypeFromId(id);
        });

        // Explicit submit = search even if the phrase field id differed.
        $(document).on('submit', 'form[id^="mec_search_form_"]', function () {
            if (!pending.action) {
                pending.action = 'search';
                pending.filter_type = 'text';
            }
        });

        // Results shown — every view triggers this with (event, view, settings).
        $(document).on('mec_search_init', function (event, view, viewSettings) {
            if (!pending.action) return; // initial page render, not a discovery

            var resultCount = 0;
            if (viewSettings && viewSettings.id) {
                resultCount = $('#mec_skin_' + viewSettings.id + ' .mec-event, #mec_skin_' + viewSettings.id + ' article').length
                    || $('.mec-wrap .mec-event, .mec-wrap article.mec-event-article').length;
            } else {
                resultCount = $('.mec-wrap .mec-event, .mec-wrap article.mec-event-article').length;
            }

            var props = {
                discovery_action: pending.action,
                filter_type: pending.filter_type,
                selected_count: 1,
                result_count_bucket: bucket(resultCount),
                view_type: view || null,
                calendar_url: location.origin + location.pathname
            };

            // Consume: one beacon per completed discovery.
            pending.action = null;
            pending.filter_type = null;

            window.mecRelayBeacon('mec_calendar_discovery_used', props);
        });
    });
})();
