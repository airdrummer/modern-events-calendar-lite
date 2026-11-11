jQuery(document).ready(function ($) {
  // Check validation of grid skin event count
  $("#mec_skin_grid_count").keyup(function () {
    var valid = false;
    if (
      $(this).val() == "1" ||
      $(this).val() == "2" ||
      $(this).val() == "3" ||
      $(this).val() == "4" ||
      $(this).val() == "6" ||
      $(this).val() == "12"
    ) {
      valid = true;
    }

    if (valid === false) {
      $(this).addClass("bootstrap_unvalid");
      $(".mec-tooltiptext").css("visibility", "visible");
    } else {
      $(this).removeClass("bootstrap_unvalid");
      $(".mec-tooltiptext").css("visibility", "hidden");
    }
  });

  // MEC Accordion
  $(".mec-accordion .mec-acc-label .mec-acc-cat-name").on("click", function () {
    var key = $(this).parent().attr("data-key");
    var status = $(this).parent().attr("data-status");

    // Open the accordion
    if (status === "close") {
      $(".mec-accordion .mec-acc-label ul").hide();
      $(".mec-accordion .mec-acc-label").attr("data-status", "close");
      $(this).parent().attr("data-status", "open");
      $("#mec-acc-" + key).show();
    } else {
      $(".mec-accordion .mec-acc-label ul").hide();
      $(".mec-accordion .mec-acc-label").attr("data-status", "close");
      $("#mec-acc-" + key).hide();
    }
  });

  // MEC Select, Deselect, Toggle
  $(".mec-select-deselect-actions li").on("click", function () {
    var target = $(this).parent().data("for");
    var action = $(this).data("action");

    if (action === "select-all") {
      $(target + " input[type=checkbox]").each(function () {
        this.checked = true;
      });
    } else if (action === "deselect-all") {
      $(target + " input[type=checkbox]").each(function () {
        this.checked = false;
      });
    } else if (action === "toggle") {
      $(target + " input[type=checkbox]").each(function () {
        this.checked = !this.checked;
      });
    }
  });

  // MEC image popup switcher
  if ($(".mec-sed-method-wrap").length > 0) {
    $(".mec-sed-method-wrap").each(function () {
      var sed_value = $(this).find('[id*="_sed_method_field"]').val();
      if (sed_value == "m1") {
        $(this).siblings(".mec-image-popup-wrap").show();
      }
    });
  }

  // MEC Single Event Display Method Switcher
  $(".mec-sed-methods li").on("click", function () {
    var target = $(this).parent().data("for");
    var method = $(this).data("method");

    // Set the Method
    $(target).val(method);

    // Set the active method
    $(this).parent().find("li").removeClass("active");
    $(this).addClass("active");

    // Display Image popup section
    if (method == "m1") {
      $(".mec-image-popup-wrap").show();
    } else {
      $(".mec-image-popup-wrap").hide();
    }
  });

  // Initialize WP Color Picker
  if ($.fn.wpColorPicker) jQuery(".mec-color-picker").wpColorPicker();

  // Initialize MEC Skin Switcher
  $("#mec_skin").on("change", function () {
    mec_skin_toggle();
  });

  mec_skin_toggle();

  // Initialize MEC Skin Gallery (visual skin picker; the classic dropdown stays as fallback)
  if ($("#mec_skin_gallery").length) {
    var syncSkinGallery = function () {
      var skin = $("#mec_skin").val();

      $(".mec-skin-card")
        .removeClass("mec-skin-card--selected")
        .attr("aria-pressed", "false");
      $('.mec-skin-card[data-skin="' + skin + '"]')
        .addClass("mec-skin-card--selected")
        .attr("aria-pressed", "true");
    };

    // Pick a skin from the gallery -> drive the original select (triggers mec_skin_toggle)
    $(document).on("click", "#mec_skin_gallery .mec-skin-card", function () {
      var skin = $(this).data("skin");
      if (!skin || $("#mec_skin").val() === skin) return;

      $("#mec_skin").val(skin);
      if ($("#mec_skin").hasClass("wn-mec-select")) $("#mec_skin").niceSelect("update");
      $("#mec_skin").trigger("change");
    });

    // Keep the gallery in sync when the skin changes from anywhere (classic dropdown, etc.)
    $("#mec_skin").on("change", syncSkinGallery);

    syncSkinGallery();
  }

  // Initialize MEC Style Galleries (per-skin "Style" select -> image card picker)
  // Only selects with 2+ options are converted; the sub-select
  // #mec_skin_full_calendar_monthly_style stays a dropdown (dependent secondary field).
  if (typeof mec_admin_localize !== "undefined" && mec_admin_localize.skins_assets_url) {
    var mecStyleFolders = {
      list: "list",
      grid: "grid",
      agenda: "agenda",
      full_calendar: "full-calendar",
      yearly_view: "yearly",
      monthly_view: "monthly",
      daily_view: "daily",
      map: "map",
      weekly_view: "weekly",
      timetable: "time-table",
      cover: "cover",
      countdown: "countdown",
      available_spot: "available-spot",
      carousel: "carousel",
      slider: "slider",
      masonry: "masonry",
      timeline: "timeline",
    };

    // Option value -> image file name (naming differs per skin in assets/img/skins/)
    var mecStyleImage = function (skin, style) {
      var folder = mecStyleFolders[skin] || skin;
      if (skin === "countdown") return folder + "-type-" + style.replace("style", "") + ".png";
      if (skin === "carousel") return folder + "-type-" + style.replace("type", "") + ".png";
      if (skin === "slider") return folder + "-type-" + style.replace("t", "") + ".png";
      if (skin === "map") return folder + "-" + style + ".jpg";
      // full_calendar has no per-style image — use the monthly view preview
      // (the default tab look) from its own folder
      if (skin === "full_calendar") return "full-calendar-monthly.png";
      return folder + "-" + style + ".png";
    };

    $('select.wn-mec-select[id^="mec_skin_"][id$="_style"]')
      .not("#mec_skin_full_calendar_monthly_style")
      .each(function () {
        var $select = $(this);

        var selectId = $select.attr("id");
        var skin = selectId
          .replace(/^mec_skin_/, "")
          .replace(/_style$/, "");
        var current = $select.val();

        var cards = "";
        $select.find("option").each(function () {
          var value = $(this).attr("value");
          var label = $(this).text();
          var img =
            mec_admin_localize.skins_assets_url +
            mecStyleFolders[skin] +
            "/" +
            mecStyleImage(skin, value);
          cards +=
            '<button type="button" class="mec-skin-card mec-style-card" data-style="' +
            value +
            '" aria-pressed="false">' +
            '<span class="mec-skin-card__thumb"><img src="' +
            img +
            '" alt="' + label + '" loading="lazy" onerror="this.parentNode.style.display=\'none\'" /></span>' +
            '<span class="mec-skin-card__body"><span class="mec-skin-card__name">' +
            label +
            "</span></span></button>";
        });

        var $gallery = $(
          '<div class="mec-style-gallery' +
            ($select.find("option").length < 2 ? " single" : "") +
            '" role="group" aria-label="Style">' +
            cards +
            "</div>"
        );
        $gallery.insertAfter($select);

        // Hide the original select + any nice-select dropdown built for it
        // (niceSelect initializes later, so hide via a class on the control + CSS)
        $select.addClass("mec-util-hidden");
        $select.closest(".mec-field__control").addClass("mec-has-style-gallery");

        var syncStyleGallery = function () {
          var value = $select.val();
          $gallery
            .find(".mec-style-card")
            .removeClass("mec-skin-card--selected")
            .attr("aria-pressed", "false");
          $gallery
            .find('.mec-style-card[data-style="' + value + '"]')
            .addClass("mec-skin-card--selected")
            .attr("aria-pressed", "true");
        };

        // Pick a style -> drive the original select (fires its inline onchange)
        $gallery.on("click", ".mec-style-card", function () {
          var value = $(this).data("style");
          if ($select.val() === value) return;

          $select.val(value);
          if ($select.hasClass("wn-mec-select")) $select.niceSelect("update");
          $select.trigger("change");
        });

        // Keep in sync when the select changes from anywhere (mec_skin_toggle, etc.)
        $select.on("change", syncStyleGallery);

        syncStyleGallery();
      });
  }

  // Inline validation warnings (UX audit — Issue 4): non-blocking hints for
  // invalid dates and reversed date ranges in the active skin's options.
  // Soft checks only — values that don't look like ISO dates are skipped, so
  // sites using a custom datepicker format never get false warnings.
  function mecValidateSkinDateFields() {
    var $container = $(".mec-skin-options-container").filter(":visible").first();
    if (!$container.length) return;

    var $start = $container.find('input[name$="[start_date]"]');
    var $end = $container.find('input[name$="[maximum_date_range]"]');
    if (!$start.length && !$end.length) return;

    // Set or remove a warning message under the field's control (role=alert for screen readers)
    function warn($input, message) {
      if (!$input.length) return;

      var $row = $input.closest(".mec-field");
      var $existing = $row.find(".mec-field-warning");

      if (!message) {
        $existing.remove();
        return;
      }

      if (!$existing.length) {
        $existing = $('<p class="mec-field-warning" role="alert"></p>');
        $row.find(".mec-field__control").first().append($existing);
      }
      $existing.text(message);
    }

    var dateRe = /^\d{4}-\d{1,2}-\d{1,2}$/;
    var sRaw = $.trim($start.val() || "");
    var eRaw = $.trim($end.val() || "");
    var sDate = sRaw && dateRe.test(sRaw) ? new Date(sRaw) : null;
    var eDate = eRaw && dateRe.test(eRaw) ? new Date(eRaw) : null;

    var sWarn = "";
    var eWarn = "";

    if (sDate && isNaN(sDate.getTime())) sWarn = "This is not a valid date (expected format: YYYY-MM-DD).";
    if (eDate && isNaN(eDate.getTime())) eWarn = "This is not a valid date (expected format: YYYY-MM-DD).";

    if (!sWarn && !eWarn && sDate && eDate && sDate.getTime() > eDate.getTime()) {
      eWarn = "The start date is after the maximum date. Events will be listed in reverse order.";
    }

    warn($start, sWarn);
    warn($end, eWarn);
  }

  $(document).on(
    "change blur",
    '.mec-skin-options-container input[name$="[start_date]"], .mec-skin-options-container input[name$="[maximum_date_range]"]',
    mecValidateSkinDateFields
  );

  // Re-validate when the active skin changes (mec_skin_toggle shows/hides containers)
  $("#mec_skin").on("change", function () {
    setTimeout(mecValidateSkinDateFields, 50);
    setTimeout(mecValidateSingleEventSkins, 50);
  });

  mecValidateSkinDateFields();

  // Required-event check for single-event skins (round-2 proposal):
  // cover / countdown / available_spot render ONE event — if none is
  // selectable, the front end stays empty. Same inline-warning pattern as above.
  function mecValidateSingleEventSkins() {
    var skin = $("#mec_skin").val();
    var singleEventSkins = ["cover", "countdown", "available_spot"];

    $(".mec-single-event-warning").remove();

    if (!skin || singleEventSkins.indexOf(skin) === -1) return;

    var $select = $("#mec_skin_" + skin + "_event_id");
    if (!$select.length) return;

    var value = $select.val();
    var optionCount = $select.find("option").length;
    var message = "";

    if (optionCount === 0 || value === null || value === "") {
      message = "This skin shows a single event, but no event is selected.. Create an event first, or the front end stays empty.";
    } else if (value === "-1" && optionCount < 2) {
      // Auto mode ("Next Upcoming Event") but no upcoming events exist
      message = '"Next Upcoming Event" is selected but no upcoming events were found. The front end stays empty until an event is scheduled.';
    }

    if (!message) return;

    $select
      .closest(".mec-field")
      .find(".mec-field__control")
      .first()
      .append(
        $('<p class="mec-field-warning mec-single-event-warning" role="alert"></p>').text(message)
      );
  }

  $(document).on(
    "change",
    "#mec_skin_cover_event_id, #mec_skin_countdown_event_id, #mec_skin_available_spot_event_id",
    mecValidateSingleEventSkins
  );

  mecValidateSingleEventSkins();

  // Live date-format samples (UX audit — Issue 7): render PHP date tokens
  // (e.g. "M d Y") as a real example next to each field, replacing the need
  // to open external documentation to understand the tokens.
  function mecRenderPhpDateFormat(format, date) {
    var daysShort = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    var daysLong = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    var monthsShort = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var monthsLong = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

    var day = date.getDate();
    var month = date.getMonth();
    var year = date.getFullYear();
    var hours24 = date.getHours();
    var hours12 = hours24 % 12 ? hours24 % 12 : 12;
    var suffix = day % 10 === 1 && day !== 11 ? "st" : day % 10 === 2 && day !== 12 ? "nd" : day % 10 === 3 && day !== 13 ? "rd" : "th";

    var pad = function (n) {
      return (n < 10 ? "0" : "") + n;
    };

    var tokens = {
      d: pad(day),
      D: daysShort[date.getDay()],
      j: day,
      l: daysLong[date.getDay()],
      N: date.getDay() === 0 ? 7 : date.getDay(),
      S: suffix,
      w: date.getDay(),
      F: monthsLong[month],
      M: monthsShort[month],
      m: pad(month + 1),
      n: month + 1,
      Y: year,
      y: pad(year % 100),
      a: hours24 < 12 ? "am" : "pm",
      A: hours24 < 12 ? "AM" : "PM",
      g: hours12,
      G: hours24,
      h: pad(hours12),
      H: pad(hours24),
      i: pad(date.getMinutes()),
      s: pad(date.getSeconds()),
    };

    var result = "";
    for (var i = 0; i < format.length; i++) {
      var char = format.charAt(i);

      // Backslash escapes the next character (PHP date() behavior)
      if (char === "\\" && i + 1 < format.length) {
        result += format.charAt(++i);
        continue;
      }

      result += Object.prototype.hasOwnProperty.call(tokens, char) ? tokens[char] : char;
    }
    return result;
  }

  function mecUpdateDateFormatSample($input) {
    var value = $.trim($input.val() || "");
    var $sample = $input.next(".mec-date-format-sample");

    if (!value) {
      $sample.remove();
      return;
    }

    if (!$sample.length) {
      $sample = $('<span class="mec-date-format-sample"></span>').insertAfter($input);
    }

    $sample.text(mecRenderPhpDateFormat(value, new Date()));
  }

  $(document).on(
    "input change",
    '.mec-calendar-metabox input[name*="date_format"]',
    function () {
      mecUpdateDateFormatSample($(this));
    }
  );

  $('.mec-calendar-metabox input[name*="date_format"]').each(function () {
    mecUpdateDateFormatSample($(this));
  });

  // MEC Checkbox Toggle (Used in Date Filter Options)
  $(".mec-checkbox-toggle").on("change", function () {
    var id = $(this).attr("id");
    $(".mec-checkbox-toggle:not(#" + id + ")").prop("checked", false);
  });

  // MEC Setting Sticky
  if ($(".wns-be-container-sticky").length > 0) {
    var stickyNav = function () {
      var stickyNavTop = $(".wns-be-container-sticky").offset().top;
      var scrollTop = $(window).scrollTop();
      var width = $(".wns-be-container-sticky").width();
      if (scrollTop > stickyNavTop) {
        $("#wns-be-infobar").addClass("sticky");
        $("#wns-be-infobar").css({
          width: width,
        });
      } else {
        $("#wns-be-infobar").removeClass("sticky");
      }
    };
    stickyNav();
    $(window).scroll(function () {
      stickyNav();
    });

    // Q-03 - keyword highlighting: wrap the first verbatim occurrence of the
    // (normalised, lower-cased) query inside the matched rows' label text
    // nodes with .mec-search-mark — the exact "where it matched" signal,
    // working in dark mode too (background cue, not text colour). Original
    // HTML is cached per label in jQuery's data store and restored before
    // every re-mark / clear, so the markup never accumulates. Only text
    // nodes are touched: child elements (flags, icons) survive untouched.
    var $mecSearchMarked = jQuery();
    var mecSearchUnmark = function () {
      $mecSearchMarked.each(function () {
        var $el = jQuery(this);
        var html = $el.data("mec-search-html");
        if (html !== undefined) $el.html(html).removeData("mec-search-html");
      });
      $mecSearchMarked = jQuery();
    };
    var mecSearchMark = function (query) {
      if (!query) return;
      var LABELS =
        ".mec-field__label label, .mec-check > span, label[class*='mec-col-']";
      var $rows = jQuery("#wns-be-content .mec-form-row.results");
      $rows.find(LABELS).add($rows.children("label")).each(function () {
        var el = this;
        var $el = jQuery(el);
        if ($el.data("mec-search-html") === undefined) {
          $el.data("mec-search-html", $el.html());
          $mecSearchMarked = $mecSearchMarked.add($el);
        }
        // Collect first, then mutate: replacing the walker's current node
        // mid-traversal stops the walk.
        var nodes = [];
        var walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, null);
        var n;
        while ((n = walker.nextNode())) nodes.push(n);
        for (var i = 0; i < nodes.length; i++) {
          var text = nodes[i].nodeValue;
          var idx = text.toLowerCase().indexOf(query);
          if (idx === -1) continue;
          var frag = document.createDocumentFragment();
          if (idx > 0)
            frag.appendChild(document.createTextNode(text.slice(0, idx)));
          var mark = document.createElement("span");
          mark.className = "mec-search-mark";
          mark.textContent = text.slice(idx, idx + query.length);
          frag.appendChild(mark);
          frag.appendChild(document.createTextNode(text.slice(idx + query.length)));
          nodes[i].parentNode.replaceChild(frag, nodes[i]);
          break; // one highlight per label is enough
        }
      });
    };

    // U2 - Reachability of in-page matches. Users searching for an option
    // that lives far down the current page had no signal that the hit was
    // ON the tab they were already on: the page never moved and the
    // "N settings found" counter could have meant anywhere. Three pieces:
    // 1) mecSearchJump - center the match in the viewport (centering keeps
    //    it clear of the sticky infobar) and pulse it once with
    //    .mec-search-flash; targets already in view only pulse.
    // 2) the counter card doubles as a jump control (click / Enter / arrow
    //    keys) cycling through the matches - see the DS feedback block.
    // 3) mecSearchRevealBasvanced - a match inside a collapsed Basic /
    //    Advanced side is unreachable, so that side is opened for the
    //    duration of the search and re-collapsed when it is cleared.
    var mecSearchMatches = jQuery();
    var mecSearchMatchIdx = 0;
    var mecSearchBasvancedUndo = [];

    var mecSearchJump = function (idx) {
      var $t = mecSearchMatches.eq(idx);
      if (!$t.length) return;
      mecSearchMatchIdx = idx;
      var winH = jQuery(window).height();
      var rect = $t[0].getBoundingClientRect();
      var inView = rect.top >= 70 && rect.bottom <= winH - 24;
      if (!inView) {
        jQuery("html, body").stop().animate(
          {
            scrollTop: Math.max(
              0,
              $t.offset().top - (winH - $t.outerHeight()) / 2
            ),
          },
          300
        );
      }
      $t.removeClass("mec-search-flash");
      void $t[0].offsetWidth; /* restart the pulse on re-jumps */
      $t.addClass("mec-search-flash");
      window.setTimeout(function () {
        $t.removeClass("mec-search-flash");
      }, 1700);
    };

    // Mirrors the events.js basvanced toggle (class flip + tab states)
    // WITHOUT triggering its click handler - a programmatic click would
    // also overwrite the user's persisted Basic/Advanced preference in
    // localStorage.
    var mecSearchBasvancedSide = function ($scope, side) {
      var $rows = $scope.find(side);
      if (!$rows.length) return null;
      return {
        hidden: $rows.filter(".w-hidden").length === $rows.length,
        hit:
          $rows.filter(".results").length > 0 ||
          $rows.find(".results").length > 0,
      };
    };

    var mecSearchRestoreBasvanced = function () {
      // Only revert what the search itself changed, and only while the user
      // has not manually re-toggled it since (manual clicks flip the same
      // classes, so an unchanged DOM still matches our last state).
      while (mecSearchBasvancedUndo.length) {
        var u = mecSearchBasvancedUndo.pop();
        var $scope = jQuery("#" + u.id);
        if (!$scope.length) continue;
        if ($scope.find(u.opened).filter(".w-hidden").length) continue;
        $scope.find(u.opened).addClass("w-hidden");
        $scope.find(u.closed).removeClass("w-hidden");
        var restoreIdx = u.opened === ".mec-basvanced-basic" ? 1 : 0;
        var $items = jQuery(
          '.mec-basvanced-toggle[data-for="#' + u.id + '"] .mec-backend-tab-item'
        );
        $items
          .removeClass("mec-b-active-tab")
          .eq(restoreIdx)
          .addClass("mec-b-active-tab");
        $items
          .attr("aria-pressed", "false")
          .eq(restoreIdx)
          .attr("aria-pressed", "true");
      }
    };

    var mecSearchRevealBasvanced = function () {
      mecSearchRestoreBasvanced();
      var SIDES = [".mec-basvanced-basic", ".mec-basvanced-advanced"];
      jQuery("#wns-be-content .mec-options-fields:visible").each(function () {
        var sec = this;
        if (!sec.id) return;
        var $toggle = jQuery(
          '.mec-basvanced-toggle[data-for="#' + sec.id + '"]'
        );
        if (!$toggle.length) return;
        var $scope = jQuery(sec);
        var basic = mecSearchBasvancedSide($scope, SIDES[0]);
        var adv = mecSearchBasvancedSide($scope, SIDES[1]);
        var openIdx = -1;
        if (
          basic &&
          basic.hidden &&
          basic.hit &&
          !(adv && !adv.hidden && adv.hit)
        ) {
          openIdx = 0;
        } else if (
          adv &&
          adv.hidden &&
          adv.hit &&
          !(basic && !basic.hidden && basic.hit)
        ) {
          openIdx = 1;
        }
        if (openIdx === -1) return;
        var closeIdx = 1 - openIdx;
        $scope.find(SIDES[openIdx]).removeClass("w-hidden");
        $scope.find(SIDES[closeIdx]).addClass("w-hidden");
        var $items = $toggle.find(".mec-backend-tab-item");
        $items
          .removeClass("mec-b-active-tab")
          .eq(openIdx)
          .addClass("mec-b-active-tab");
        $items
          .attr("aria-pressed", "false")
          .eq(openIdx)
          .attr("aria-pressed", "true");
        mecSearchBasvancedUndo.push({
          id: sec.id,
          opened: SIDES[openIdx],
          closed: SIDES[closeIdx],
        });
      });
    };

    $("#mec-search-settings").typeWatch({
      wait: 400,
      // QA - captureLength must be 1 (TypeWatch defaults to 2): with the
      // default, backspacing from a 2+ char query down to a single char
      // never fired the callback, so stale results, the hit counter and
      // the keyword marks stayed on screen while the input showed one
      // character. Single-character queries filtered nothing at all.
      captureLength: 1,
      // QA - TypeWatch defaults to highlight:true, which selects the whole
      // value on focus: clicking inside the box to refine the query wiped
      // it on the first keystroke.
      highlight: false,
      callback: function (value) {
        var elements = [];
        // Whitespace-only input counts as a cleared search (it used to match
        // every section because the normalised query was "").
        value = String(value || "").replace(/\s+/g, " ").trim();
        if (!value || value == "") {
          // QA - drop the last "N settings found" from the aria-live region:
          // the restore path below returns early and the fall-through path
          // ends before the DS feedback block, so screen readers kept reading
          // the stale announcement after the search was cleared. No-op until
          // the region exists (it is created lazily on the first real query).
          $("#mec-search-live").text("");
          // U2 - re-collapse the Basic/Advanced sides the search had to open
          // and forget the jump targets.
          mecSearchRestoreBasvanced();
          mecSearchMatches = jQuery();
          mecSearchMatchIdx = 0;
          // Restore the section that was active before the search started
          // (fall back to the first section). Used to always jump to General.
          if (window.mecSearchRestoreId) {
            // One-shot: forget the remembered section after restoring, so the
            // NEXT search captures whatever tab is current at that time
            // (otherwise a manual tab switch between searches would be
            // overridden by the stale memory).
            var mecRestoreId = window.mecSearchRestoreId;
            window.mecSearchRestoreId = "";
            var $restore = $(
              '.wns-be-sidebar .subsection a.WnTabLinks[data-id="' +
                mecRestoreId +
                '"]'
            );
            if ($restore.length) {
              $restore.trigger("click");
              $("#mec-search-empty").remove();
              $("#mec-search-count").remove();
              mecSearchUnmark();
              // P2-09 - also drop the row/list marks on this early return.
              jQuery("#wns-be-content .results").removeClass("results");
              jQuery("#wns-be-content .noresults").removeClass("noresults");
              jQuery("#wns-be-content ul li.enable").removeClass("enable");
              jQuery("#wns-be-content ul li.disable").removeClass("disable");
              return;
            }
          }
          $(".mec-options-fields").hide();
          $(".mec-options-fields").removeClass("active");
          $(
            ".wns-be-group-tab form .mec-options-fields:first-of-type"
          ).addClass("active");
          $(".subsection li").removeClass("active");
          $(
            ".wns-be-sidebar .wns-be-group-menu .subsection li:first-of-type"
          ).addClass("active");
          $("#mec-search-empty").remove();
          $("#mec-search-count").remove();
          mecSearchUnmark();
        } else {
          // Remember where the user was before the first filtered keystroke.
          if (!window.mecSearchRestoreId) {
            var $activeSection = $(".mec-options-fields.active");
            if ($activeSection.length) window.mecSearchRestoreId = $activeSection.attr("id");
          }
          // P2-09 - Case-insensitive, whitespace-normalised plain-text match
          // (TypeWatch passes the raw input value; the haystacks below were
          // already lower-cased, so "Hide" used to find nothing).
          var mecSearchNorm = function (str) {
            return String(str || "").replace(/\s+/g, " ").trim().toLowerCase();
          };
          var mecSearchQuery = mecSearchNorm(value);
          // Collect each matched element's text separately (joined with a
          // space) so words from adjacent elements are not glued together.
          var mecSearchText = function ($scope, selector) {
            return mecSearchNorm(
              $scope
                .find(selector)
                .map(function () {
                  return $(this).text();
                })
                .get()
                .join(" ")
            );
          };
          // Clear the previous query's row/list marks before marking again.
          jQuery("#wns-be-content .results").removeClass("results");
          jQuery("#wns-be-content .noresults").removeClass("noresults");
          jQuery("#wns-be-content ul li.enable").removeClass("enable");
          jQuery("#wns-be-content ul li.disable").removeClass("disable");
          $(".mec-options-fields").filter(function () {
            // Labels: migrated .mec-field rows, .mec-check captions and the
            // legacy-bridge rows (mec-col-N labels, bare row labels) that
            // add-on custom pages still output.
            var search_label = mecSearchText(
              $(this),
              '.mec-field__label, .mec-check > span, label[class*="mec-col-"], .mec-form-row > label'
            );
            // Titles: page sections (h2), legacy add-on subtitles (h4, kept
            // for backward compatibility) and panel/subsection titles (h5).
            var search_title = mecSearchText(
              $(this),
              "h2.mec-form-subtitle, h4.mec-form-subtitle, h5"
            );
            // Help text and tooltip titles, also when they sit outside a row.
            var search_help = mecSearchText(
              $(this),
              ".mec-field__help, .mec-tooltip .title"
            );
            var search_rows = mecSearchText($(this), ".mec-form-row");
            if (!search_label && !search_title && !search_help && !search_rows) {
              return false;
            }
            if (
              search_label.indexOf(mecSearchQuery) > -1 ||
              search_title.indexOf(mecSearchQuery) > -1 ||
              search_help.indexOf(mecSearchQuery) > -1 ||
              search_rows.indexOf(mecSearchQuery) > -1
            ) {
              $(".mec-options-fields").hide();
              $(".mec-options-fields").removeClass("active");
              $(
                ".wns-be-group-menu .subsection .mec-settings-menu li"
              ).removeClass("active");
              elements.push($(this));
            }
          });

          $(".mec-settings-menu li").filter(function () {
            var search_label = mecSearchNorm($(this).find("a").text());
            var search_title = mecSearchNorm($(this).find("a span").text());
            if (
              (!search_label || search_label == "") &&
              (!search_title || search_title == "")
            ) {
              return false;
            }
            if (
              search_label.indexOf(mecSearchQuery) > -1 ||
              search_title.indexOf(mecSearchQuery) > -1
            ) {
              $(".mec-settings-menu li").removeClass("active");
              $(
                ".wns-be-group-menu .subsection .mec-settings-menu li"
              ).removeClass("active");
              elements.push($(this));
            }
          });

          $.each(elements, function (i, searchStr) {
            searchStr.show();
            searchStr.addClass("active");
          });

          // Plain substring test - the query used to be compiled as a RegExp,
          // so "(" threw and "." matched every row. Runs before the DS
          // feedback block below so the visible-hit count can read the row
          // marks.
          jQuery("#wns-be-content .mec-form-row").each(function () {
            if (mecSearchQuery === "") return;
            if (mecSearchNorm($(this).text()).indexOf(mecSearchQuery) !== -1) {
              jQuery(this).addClass("results");
            } else {
              jQuery(this).addClass("noresults");
            }
          });

          jQuery("#wns-be-content ul li").each(function () {
            if (mecSearchQuery === "") return;
            if (mecSearchNorm($(this).text()).indexOf(mecSearchQuery) !== -1) {
              jQuery(this).addClass("enable");
            } else {
              jQuery(this).addClass("disable");
            }
          });

          // U2 - open a collapsed Basic/Advanced side when all of a
          // section's matches sit inside it (hidden matches are unreachable
          // and, being :hidden, would not even be counted), then collect
          // the jump targets: matched rows plus matched list items (the
          // list-built sections), merged in document order.
          mecSearchRevealBasvanced();
          mecSearchMatches = jQuery("#wns-be-content .mec-form-row.results").filter(":visible");
          var mecSearchListMatches = jQuery("#wns-be-content ul li.enable").filter(":visible");
          if (mecSearchListMatches.length) mecSearchMatches = mecSearchMatches.add(mecSearchListMatches);
          mecSearchMatchIdx = 0;

          if ($("body").hasClass("mec-ds-settings")) {
          $("#mec-search-empty").remove();
          $("#mec-search-count").remove();
          $("#mec-search-live").text("");

            var L10N = (window.mec_admin_localize && window.mec_admin_localize.l10n) || {};
            // The counter counts what the user can see and act on: matched
            // setting rows inside the shown panels ("5 settings found").
            // Section counting is only the fallback for title-only matches
            // (the query hit a section name but no row inside it); elements
            // mixes panels and sidebar links, so it never read well as the
            // primary count — a single row match also reported as
            // "1 section found", which confused users.
            var rowHits = mecSearchMatches.length;
            var foundMsg;
            if (rowHits > 0) {
              // U2 - "...on this page" removes the ambiguity the bare count
              // had: users could not tell whether a hit meant "scroll down
              // on the tab you are on" or "it lives on another tab".
              foundMsg = (
                rowHits === 1
                  ? (L10N.search_setting_found_here_single || "%s setting found on this page")
                  : (L10N.search_setting_found_here || "%s settings found on this page")
              ).replace("%s", rowHits);
            } else if (elements.length === 0) {
              foundMsg = L10N.search_no_match || "No matching settings";
            } else {
              foundMsg = (
                elements.length === 1
                  ? (L10N.search_section_found_single || "%s section found")
                  : (L10N.search_section_found || "%s sections found")
              ).replace("%s", elements.length);
            }

            var $live = $("#mec-search-live");
            if (!$live.length) {
              $live = $(
                '<div id="mec-search-live" class="mec-sr-only" aria-live="polite"></div>'
              ).appendTo("body");
            }
            $live.text(foundMsg);

            // Q-03 - visible results counter: the same foundMsg the aria-live
            // region announces, rendered as the order-0 line of the search
            // dropdown so "did my search do anything?" is answered right
            // under the box being typed in. Only for real hits; the empty
            // card below covers the zero case.
            if (elements.length > 0) {
              var $count = $("#mec-search-count");
              if (!$count.length) {
                $count = $('<div id="mec-search-count" class="mec-search-count"></div>');
                (window.mecSearchPanel ? window.mecSearchPanel() : $("#wns-be-content")).append($count);
              }
              // U2 - with jump targets the counter doubles as the jump
              // control: click / Enter / ArrowDown cycles forward,
              // ArrowUp steps back. Bound once; the handler reads the
              // closure's match list, which is refreshed per query.
              var jumpable = mecSearchMatches.length > 0;
              $count
                .attr("role", jumpable ? "button" : null)
                .attr("tabindex", jumpable ? "0" : null)
                .attr(
                  "aria-label",
                  jumpable
                    ? String(L10N.search_jump_hint || "Jump to match")
                    : null
                )
                .toggleClass("mec-search-count--jump", jumpable)
                .empty()
                .append($("<span>").text(foundMsg));
              if (jumpable) {
                $count.append(
                  $('<span class="mec-search-count__jump" aria-hidden="true"></span>')
                );
                if (!$count.data("mecSearchJumpBound")) {
                  $count
                    .on("keydown", function (e) {
                      if (
                        e.key !== "Enter" &&
                        e.key !== " " &&
                        e.key !== "ArrowDown" &&
                        e.key !== "ArrowUp"
                      )
                        return;
                      e.preventDefault();
                      var dir = e.key === "ArrowUp" ? -1 : 1;
                      var n = mecSearchMatches.length;
                      mecSearchJump((mecSearchMatchIdx + dir + n) % n);
                    })
                    .on("click", function () {
                      var n = mecSearchMatches.length;
                      if (n) mecSearchJump((mecSearchMatchIdx + 1) % n);
                    })
                    .data("mecSearchJumpBound", true);
                }
              }
            }

            if (elements.length === 0) {
              (window.mecSearchPanel ? window.mecSearchPanel() : $("#wns-be-content")).append(
                '<div class="mec-search-empty" id="mec-search-empty">' +
                  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path><path d="M8.5 11h5"></path></svg>' +
                  "<strong>" + $("<div>").text(L10N.search_no_match || "No matching settings").html() + ".</strong>" +
                  "<span>" + $("<div>").text(L10N.search_no_match_hint || "Try a different keyword, or check the other tabs in the sidebar.").html() + "</span>" +
                  "</div>"
              );
            }
          }

          // Q-03 - refresh the keyword marks with the current query.
          mecSearchUnmark();
          mecSearchMark(mecSearchQuery);

          // U2 - bring the first match into view; without this the page
          // stayed wherever the user happened to be and they had to hunt
          // for the highlight through a wall of unrelated settings.
          if (mecSearchMatches.length) mecSearchJump(0);
        }
        if (!value || value == "") {
          jQuery("#wns-be-content .results").removeClass("results");
          jQuery("#wns-be-content .noresults").removeClass("noresults");
          jQuery("#wns-be-content ul li.enable").removeClass("enable");
          jQuery("#wns-be-content ul li.disable").removeClass("disable");
          mecSearchUnmark();
        }
      },
    });
  }

  // Import Settings
  function CheckJSON(text) {
    if (typeof text != "string") text = JSON.stringify(text);
    try {
      JSON.parse(text);
      return true;
    } catch (e) {
      return false;
    }
  }

  // Location select2
  jQuery(".mec-additional-locations select").select2();
  jQuery("#mec_location_id").select2();

  // Organizer Select2
  jQuery(".mec-additional-organizers select").select2();
  jQuery("#mec_organizer_id").select2();

  // Related Events Select2
  jQuery(".mec-related_events-dropdown-select2").select2({
    closeOnSelect: false,
  });

  // Add shortcode select2
  jQuery(".mec-create-shortcode-tab-content select").select2();

  // General Calendar
  jQuery("#mec_skin_general_calendar_skins").select2();

  // Add Notification DropDown Select2
  jQuery(".mec-notification-dropdown-select2").each(function () {
    jQuery(this).select2({
      closeOnSelect: false,
      width: "100%",
      placeholder: jQuery(this).data("placeholder") || "",
    });
  });

  jQuery("#mec_h_fontfamily").select2();

  jQuery("#mec_p_fontfamily").select2();

  $(".mec-import-settings").on("click", function (e) {
    e.preventDefault();
    var value = $(".mec-import-settings-content").val();
    if (CheckJSON(value) || value == "") {
      value = jQuery.parseJSON($(".mec-import-settings-content").val());
    } else {
      value = "No-JSON";
    }
    $.ajax({
      url: mec_admin_localize.ajax_url,
      type: "POST",
      data: {
        action: "import_settings",
        nonce: mec_admin_localize.ajax_nonce,
        content: value,
      },
      beforeSend: function () {
        $(".mec-import-settings-wrap").append(
          '<div class="mec-loarder-wrap"><div class="mec-loarder"><div></div><div></div><div></div></div></div>'
        );
        $(".mec-import-options-notification")
          .find(".mec-message-import-error")
          .remove();
        $(".mec-import-options-notification")
          .find(".mec-message-import-success")
          .remove();
      },
      success: function (response) {
        $(".mec-import-options-notification").append(response);
        $(".mec-loarder-wrap").remove();
        $(".mec-import-settings-content").val("");
      },
    });
  });

  /* MEC activation */
  if ($("#MECActivation").length > 0) {
    $("#MECActivation input[type=submit]").on("click", function (e) {
      e.preventDefault();

      // Define DOM
      var Spinner = $(".wna-spinner-wrap");
      var LicenseField = $("#MECActivation .LicenseField");
      var PurchaseStatus = $("#MECActivation").find(".MECPurchaseStatus");
      var LicenseMessage = $("#MECActivation").find(".MECLicenseMessage");

      // Global Actions
      Spinner.remove();
      LicenseMessage.addClass("mec-message-hidden");
      PurchaseStatus.removeClass("PurchaseError");
      PurchaseStatus.removeClass("PurchaseSuccess");

      // Basic Information
      var PurchaseCode = $(
              "#MECActivation input[name=MECPurchaseCode]"
      ).val();
      var information = { LicenseTypeJson: "", PurchaseCodeJson: PurchaseCode };
      var ajaxAction = "activate_license";

      if ($(this).hasClass("mec_revoke")) {
        ajaxAction = "revoke_license";
        information = "";
      }

      $.ajax({
        url: mec_admin_localize.ajax_url,
        type: "POST",
        data: {
          action: ajaxAction,
          nonce: mec_admin_localize.ajax_nonce,
          content: information,
        },
        beforeSend: function () {
          LicenseField.append(
            '<div class="wna-spinner-wrap"><div class="wna-spinner"><div class="double-bounce1"></div><div class="double-bounce2"></div></div></div>'
          );
        },
        success: function (response) {
          const res = JSON.parse(response);
          $(".wna-spinner-wrap").remove();

          if (res.status === true) {
            if (res.message == "success") {
              PurchaseStatus.addClass("PurchaseSuccess");
              $("#MECActivation input[type=submit]")
                .removeClass("mec_activate")
                .addClass("mec_revoke")
                .val(res.button_text);
              // Reload so the whole dashboard reflects the new licensed state
              // (offline box hidden, expiry banner, notice cleared, etc.).
              setTimeout(function () { window.location.reload(); }, 800);
            }

            if (res.message == "revoked") {
              PurchaseStatus.removeClass("PurchaseError").removeClass(
                "PurchaseSuccess"
              );
              $("#MECActivation input[type=submit]")
                .removeClass("mec_revoke")
                .addClass("mec_activate")
                .val(res.button_text);
              $(
      // I-05: the field is type=text now - match by name, not by type.
      "#MECActivation input[name=MECPurchaseCode]"
              ).val("");

              // The licence was dropped here, but the store did not confirm
              // that the activation was released. Show that instead of
              // reloading, or the only notice of it scrolls away unread.
              if (res.warning) {
                LicenseMessage.text(res.warning).removeClass(
                  "mec-message-hidden"
                );
              } else {
                // Reload so the dashboard reflects the unlicensed state.
                setTimeout(function () { window.location.reload(); }, 800);
              }
            }
          } else {
            // .val(), not .text() — this is an <input>, whose label is its
            // value. .text() left the button reading whatever it read before.
            $("#MECActivation input[type=submit]").val(res.button_text);
            PurchaseStatus.removeClass("PurchaseSuccess").addClass(
              "PurchaseError"
            );

            // The license server could not be reached — this is not a
            // rejection of the code. Show the reason instead of the static
            // "check your purchase code" default, which describes a problem
            // this code does not have.
            // "rejected": the server answered and refused the code (activation
            // limit reached, expired...) — same treatment, its own reason.
            if (res.message == "network_error" || res.message == "rejected") {
              if (res.reason) LicenseMessage.text(res.reason);
            }

            // The purchase code was accepted and stored, but this site could
            // not be licensed. Keep the revoke button (there IS a stored code
            // to revoke), show the specific reason, and do NOT reload — the
            // reloaded page shows the same red cross with no explanation.
            if (res.message == "claim_failed") {
              $("#MECActivation input[type=submit]")
                .removeClass("mec_activate")
                .addClass("mec_revoke");
              if (res.reason) LicenseMessage.text(res.reason);
            }

            LicenseMessage.removeClass("mec-message-hidden");
          }
        },
      });
    });
  }

  /* Addons Activation */
  if ($(".box-addon-activation-toggle-head").length > 0) {
    $(".box-addon-activation-toggle-head").on("click", function () {
      // Scope to this toggle's own box so multiple instances (dashboard
      // tour/debug) do not open and close together.
      var content = $(this)
        .closest(".box-addons-activation")
        .find(".box-addon-activation-toggle-content");
      if (content.length) content.stop(true, true).slideToggle("slow");

      // I-07: expose the open/closed state to assistive tech.
      var expanded = $(this).attr("aria-expanded") === "true";
      $(this).attr("aria-expanded", expanded ? "false" : "true");

      if ($(this).find("i").hasClass("mec-sl-plus")) {
        $(this).find("i").removeClass("mec-sl-plus").addClass("mec-sl-minus");
      } else if ($(this).find("i").hasClass("mec-sl-minus")) {
        $(this).find("i").removeClass("mec-sl-minus").addClass("mec-sl-plus");
      }
    });
  }

  /* Addons Notification */
  $(".mec-addons-notification-box-wrap span, .mec-addons-notification-box-wrap .mec-notice-close").on("click", function (e) {
    e.preventDefault();
    $.ajax({
      url: mec_admin_localize.ajax_url,
      type: "POST",
      data: {
        action: "close_notification",
        nonce: mec_admin_localize.ajax_nonce,
      },
      success: function (response) {
        $(".mec-addons-notification-set-box").fadeOut(100, function () {
          $(this).remove();
        });
        $(".mec-addons-notification-wrap").fadeOut(100, function () {
          $(this).remove();
        });
      },
    });
  });

  /* Custom msg Notification */
  $(".mec-cmsg-notification-box-wrap span, .mec-cmsg-notification-box-wrap .mec-notice-close").on("click", function (e) {
    e.preventDefault();
    $.ajax({
      url: mec_admin_localize.ajax_url,
      type: "POST",
      data: {
        action: "close_cmsg_notification",
        nonce: mec_admin_localize.ajax_nonce,
      },
      success: function (response) {
        $(".mec-custom-msg-notification-set-box").fadeOut(100, function () {
          $(this).remove();
        });
        $(".mec-custom-msg-notification-wrap").fadeOut(100, function () {
          $(this).remove();
        });
      },
    });
  });

  $(".mec-cmsg-2-notification-box-wrap span, .mec-cmsg-2-notification-box-wrap .mec-notice-close").on("click", function (e) {
    e.preventDefault();
    $.ajax({
      url: mec_admin_localize.ajax_url,
      type: "POST",
      data: {
        action: "close_cmsg_2_notification",
        nonce: mec_admin_localize.ajax_nonce,
      },
      success: function (response) {
        $(".mec-custom-msg-2-notification-set-box").fadeOut(100, function () {
          $(this).remove();
        });
        $(".mec-custom-msg-2-notification-wrap").fadeOut(100, function () {
          $(this).remove();
        });
      },
    });
  });

  let $event_selectbox = $(".mec-reports-selectbox-event");

  /* Load event dates in Report page */
  if ($event_selectbox.length > 0) {
    $event_selectbox.select2();
    $event_selectbox.on("change", function (e) {
      e.preventDefault();
      var id = $event_selectbox.val();
      $.ajax({
        url: mec_admin_localize.ajax_url,
        type: "POST",
        data: {
          action: "report_event_dates",
          nonce: mec_admin_localize.ajax_nonce,
          event_id: id,
        },
        success: function (response) {
          $(".mec-report-selected-event-attendees-wrap").hide();
          $(".mec-reports-selectbox-dates").remove();
          $(
            ".mec-report-selected-event-attendees-wrap .w-row .w-col-sm-12"
          ).html("");
          $(".mec-report-select-event-wrap .w-row .w-col-sm-12").append(
            response
          );
          $(".mec-report-sendmail-wrap").hide();
          $(".mec-report-backtoselect-wrap").hide();

          $(window).trigger("mec_booking_report_change_event", response);
        },
      });
    });

    $event_selectbox.trigger("change");
  }

  $(".mec-report-backtoselect-wrap button").on("click", function (e) {
    e.preventDefault();
    $(".mec-report-backtoselect-wrap").hide();
    $(".mec-report-sendmail-wrap").show();
    $(".mec-report-selected-event-attendees-wrap").show();
    $(".mec-report-sendmail-form-wrap").hide();
  });

  // MEC Mass Action
  $('#mec_report_mass_action_form').on('submit', function(e)
  {
    e.preventDefault();

    const $form = $(this);
    const $message = $('#mec_report_mass_message');

    // Clear Message
    $message.html('');

    $.ajax({
      url: mec_admin_localize.ajax_url,
      type: "POST",
      data: $form.serialize(),
      success: function (response) {
        if(response.success) {
          $message.html(`<p class="mec-success">${response.message}</p>`);
        }
      },
      error: function () {},
    });
  });

  // MEC fast copy by one click
  $("#MECCopyCode").on("click", function () {
    var $copied = $(this).parent().find(".mec-copied");
    $copied.addClass("mec-copied-done");

    // Keep the "Copied!" feedback visible for a fixed 2s,
    // regardless of where the pointer goes afterwards.
    clearTimeout($(this).data("copiedTimer"));
    $(this).data("copiedTimer", setTimeout(function () {
      $copied.removeClass("mec-copied-done");
    }, 2000));
  });

  // Basic / Advanced Toggle: moved to events.js as a delegated handler so the
  // same code drives the toggle on the FES front-end form (this file is not
  // loaded there).

  // General Image picker
  $(".mec-image-picker-wrapper").each(function () {
    const $wrapper = $(this);
    const $button = $wrapper.find(".mec-image-picker-upload");
    const $remove = $wrapper.find(".mec-image-picker-remove");
    const $previewWrapper = $wrapper.find(".mec-image-picker-preview-wrapper");
    const $preview = $wrapper.find(".mec-image-picker-preview");
    const $input = $wrapper.find(".mec-image-picker-input");

    // Keep a single media frame per picker instead of a new one per click.
    let frame = null;

    const setSelected = function (url) {
      $preview.html(url ? '<img alt="" src="' + url + '">' : "");
      $input.val(url);

      // has icon -> hide Upload, show Remove + preview (and vice versa)
      const has = !!url;
      $button.toggleClass("w-hidden", has);
      $remove.toggleClass("w-hidden", !has);
      $previewWrapper.toggleClass("w-hidden", !has);
    };

    $button.on("click", function () {
      if (!frame) {
        frame = wp.media();
        frame.on("select", function () {
          // Grab the selected attachment.
          const attachment = frame.state().get("selection").first();

          setSelected(attachment.attributes.url);
          frame.close();
        });
      }

      frame.open();
    });

    // General Image Remover
    $remove.on("click", function (event) {
      event.preventDefault();

      setSelected("");
    });
  });

  // API Key
  $('#mec_add_new_api_key').on('click', function()
  {
    const $wrapper = $('.mec-api-key-wrapper');
    const $new_id = $('#mec_next_api_key_id');
    const id = $new_id.val();

    let key = '';
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let counter = 0;
    while (counter < 40) {
      key += characters.charAt(Math.floor(Math.random() * characters.length));
      counter += 1;
    }

    const html = $('#mec_api_key_template').html().replace(/:i:/g, id).replace(/:k:/g, key);
    const $row = $(html);

    $row.find(':input').prop('disabled', false);
    $wrapper.append($row);
    $new_id.val(parseInt(id) + 1);
  });

  $(document).on('click', '.mec-remove-api-key', function()
  {
    $(this).closest('.mec-api-key-row').remove();
  });

  // User Registration
  $('#mec_settings_booking_registration').on('change', function()
  {
    const value = $(this).val();
    const $options = $('#mec_settings_booking_registration_wrapper');
    const $userpass = $('#mec_settings_booking_registration_userpass_wrapper');

    if(value === '0') $options.addClass('w-hidden');
    else $options.removeClass('w-hidden');

    if(value === '2') $userpass.addClass('w-hidden');
    else $userpass.removeClass('w-hidden');
  });

  $('.mec-image-size-field').each(function () {
    const $field = $(this);
    const skin = $field.data('skin');

    if (!skin) {
      return;
    }

    if ($field.data('forceHidden')) {
      $field.addClass('mec-util-hidden');
      return;
    }

    const $styleSelect = $('#mec_skin_' + skin + '_style');

    if ($styleSelect.length) {
      mec_toggle_image_size_field(skin, $styleSelect.val());
    }
  });
});

function mec_skin_full_calendar_skin_toggled(Context) {
  var id = jQuery(Context).attr("id");
  var checked = jQuery(Context).is(":checked");
  var default_view = "list";

  if (id === "mec_skin_full_calendar_list") {
    jQuery(Context)
      .parent()
      .parent()
      .parent()
      .find(".mec-date-format")
      .toggle();
  } else if (id === "mec_skin_full_calendar_grid") {
    jQuery(Context)
      .parent()
      .parent()
      .parent()
      .find(".mec-date-format")
      .toggle();
    default_view = "grid";
  } else if (id === "mec_skin_full_calendar_tile") {
    default_view = "tile";
  } else if (id === "mec_skin_full_calendar_yearly") {
    jQuery(Context)
      .parent()
      .parent()
      .parent()
      .find(".mec-date-format")
      .toggle();
    default_view = "yearly";
  } else if (id === "mec_skin_full_calendar_monthly") {
    jQuery("#mec_full_calendar_monthly_style").toggle();
    jQuery("#mec_full_calendar_monthly_view_options").toggle();
    default_view = "monthly";
  } else if (id === "mec_skin_full_calendar_weekly") {
    default_view = "weekly";
  } else if (id === "mec_skin_full_calendar_daily") {
    default_view = "daily";
  }

  var $dropdown = jQuery("#mec_skin_full_calendar_default_view");
  var current_value = $dropdown.find("option:selected").prop("value");
  var $option = $dropdown.find('option[value="' + default_view + '"]');

  if (checked) $option.removeAttr("disabled");
  else $option.attr("disabled", "disabled");

  if (current_value === default_view)
    $dropdown.children("option:enabled").eq(0).prop("selected", true);
  $dropdown.niceSelect("update");
}

function mec_event_attendees(ID, occurrence) {
  // Set Occurrence
  if (typeof occurrence === "undefined") occurrence = "";

  jQuery.ajax({
    url: mec_admin_localize.ajax_url,
    type: "POST",
    dataType: "JSON",
    data: {
      action: "mec_attendees",
      id: ID,
      occurrence: occurrence,
    },
    success: function (response) {
      if (response.email_button != "") {
        jQuery(".mec-report-selected-event-attendees-wrap").show();
        jQuery(
          ".mec-report-selected-event-attendees-wrap .w-row .w-col-sm-12"
        ).html(response.html);
        jQuery(".mec-report-sendmail-wrap").show();
        jQuery(".mec-report-sendmail-wrap .w-row .w-col-sm-12").html(
          response.email_button
        );
      } else {
        jQuery(".mec-report-selected-event-attendees-wrap").show();
        jQuery(".mec-report-sendmail-wrap").hide();
        jQuery(
          ".mec-report-selected-event-attendees-wrap .w-row .w-col-sm-12"
        ).html(response.html);
        jQuery(".mec-report-sendmail-wrap .w-row .w-col-sm-12").html("");
      }

      jQuery(window).trigger("mec_booking_report_change_occurrence");
    },
    error: function () {},
  });
}

function mec_submit_event_email(ID) {
  // Set Occurrence
  if (typeof ID === "undefined") ID = "";

  if (jQuery(".mec-send-email-count > span").text() == 0) {
    alert("Please choose attendees first");
    return;
  }
  jQuery(
    ".mec-report-sendmail-form-wrap .w-row .w-col-sm-12 #mec-send-email-editor-wrap"
  ).attr("id", "mec-send-email-editor" + ID + "-wrap");
  jQuery(".mec-report-selected-event-attendees-wrap").hide();
  jQuery(".mec-report-sendmail-form-wrap").show();
  jQuery("#mec-send-email-editor" + ID + "-wrap").html(
    '<textarea id="editor' + ID + '" class="wp-editor-area"></textarea>'
  );
  jQuery("#mec-send-email-editor" + ID + "-wrap")
    .parent()
    .find(".mec-send-email-button")
    .data("id", ID);
  jQuery(".mec-report-sendmail-wrap").hide();
  jQuery(".mec-report-backtoselect-wrap").show();

  wp.editor.initialize("editor" + ID, {
    tinymce: {
      wpautop: true,
      toolbar1:
        "formatselect bold italic | bullist numlist | blockquote | alignleft aligncenter alignright | link unlink | wp_more | spellchecker",
    },
    quicktags: true,
    mediaButtons: true,
  });
}

function mec_certificate_send() {
  const $checkboxes = jQuery(
    ".mec-attendees-content input[type=checkbox]:checked"
  );
  if ($checkboxes.length === 0) {
    alert("Please choose attendees first.");
    return;
  }

  let template = jQuery("#certificate_select").val();
  if (!template) {
    alert("Please choose a template.");
    return;
  }

  let attendee_ids = "";
  $checkboxes.each(function () {
    attendee_ids += jQuery(this).data("attendee-id") + ",";
  });

  jQuery.ajax({
    url: mec_admin_localize.ajax_url,
    type: "POST",
    dataType: "JSON",
    data: {
      action: "mec_send_certificates",
      template,
      attendee_ids,
    },
    success: function (response) {
      $checkboxes.each(function () {
        jQuery(this).removeAttr("checked");
      });

      jQuery("#mec-certificate-message").html(
        `<div class="mec-success" style="margin-bottom: 0;">${response.message}</div>`
      );
    },
    error: function () {},
  });

  return true;
}

function initSlider() {
  jQuery(".mec-attendees-list-left-menu").owlCarousel({
    autoplay: false,
    autoWidth: true,
    items: 12,
    responsiveClass: true,
    responsive: {
      0: {
        items: 1,
      },
      979: {
        items: 2,
      },
      1199: {
        items: 12,
      },
    },
    dots: false,
    nav: true,
  });
}

function mec_skin_toggle() {
  var skin = jQuery("#mec_skin").val();

  jQuery(".mec-skin-options-container").hide();
  jQuery("#mec_" + skin + "_skin_options_container").show();

  jQuery(".mec-search-form-options-container").hide();
  jQuery("#mec_" + skin + "_search_form_options_container").show();

  // Show/Hide Filter Options
  if (skin === "countdown" || skin === "cover" || skin === "available_spot") {
    jQuery("#mec_meta_box_calendar_filter").hide();
    jQuery("#mec_meta_box_calendar_no_filter").show();
  } else {
    jQuery("#mec_meta_box_calendar_no_filter").hide();
    jQuery("#mec_meta_box_calendar_filter").show();
  }

  // Show/Hide Search Widget Options
  if (
    skin === "countdown" ||
    skin === "cover" ||
    skin === "available_spot" ||
    skin === "masonry" ||
    skin === "carousel" ||
    skin === "slider" ||
    skin === "timeline"
  ) {
    jQuery("#mec_calendar_search_form").hide();
  } else {
    jQuery("#mec_calendar_search_form").show();
  }

  // Show/Hide Ongoing Events
  if (
    skin === "list" ||
    skin === "weekly_view" ||
    skin === "agenda" ||
    skin === "timeline" ||
    skin === "monthly_view" ||
    skin === "timetable" ||
    skin === "slider" ||
    skin === "carousel" ||
    skin === "masonry" ||
    skin === "daily_view" ||
    skin === "tile" ||
    skin === "grid" ||
    skin === "yearly_view" ||
    skin === "custom" ||
    skin === "full_calendar"
  ) {
    jQuery("#mec_date_ongoing_filter").show();
  } else {
    jQuery("#mec_show_only_ongoing_events").prop("checked", false);
    jQuery("#mec_date_ongoing_filter").hide();
  }

  // Show/Hide Expired Events
  if (skin === "map") {
    jQuery("#mec_show_only_past_events").prop("checked", false);
    jQuery("#mec_date_only_past_filter").hide();
  } else jQuery("#mec_date_only_past_filter").show();

  // Trigger change event of skin style in order to show/hide related fields
  jQuery("#mec_skin_" + skin + "_style").trigger("change");

  // Show/Hide Dropdown Method
  if (skin === "general_calendar") {
    jQuery("#mec_shortcode_dropdown_method_wrapper").hide();
  } else jQuery("#mec_shortcode_dropdown_method_wrapper").show();
}

function mec_skin_style_changed(skin, style, context) {
  if (style.includes("fluent")) {
    jQuery(".mec-" + skin + "-fluent").removeClass("mec-fluent-hidden");
    jQuery(".mec-not-" + skin + "-fluent").addClass("mec-fluent-hidden");
  } else {
    jQuery(".mec-" + skin + "-fluent").addClass("mec-fluent-hidden");
    jQuery(".mec-not-" + skin + "-fluent").removeClass("mec-fluent-hidden");
  }

  jQuery(".mec-skin-" + skin + "-date-format-container").hide();
  if(skin === 'masonry') {
    jQuery(".mec-skin-masonry-date-format-container").show();
  } else if(skin === 'cover' && style === 'liquid-type1') {
    jQuery("#mec_skin_cover_date_format_liquid_container").show();
  } else {
    jQuery("#mec_skin_" + skin + "_date_format_" + style + "_container").show();
  }

  // List Standard Progress Bar
  if (skin === "list" && style === "standard")
    jQuery(".mec-progress-bar-display-wrapper").removeClass("mec-util-hidden");
  else jQuery(".mec-progress-bar-display-wrapper").addClass("mec-util-hidden");

  // List Standard Status Bar
  if (skin === "list" && style === "standard")
    jQuery(".mec-status-bar-display-wrapper").removeClass("mec-util-hidden");
  else jQuery(".mec-status-bar-display-wrapper").addClass("mec-util-hidden");

  // Show Or Hide Include Events Time Switcher
  if (style === "classic" || style === "minimal" || style === "modern")
    jQuery(context).parent().parent().find(".mec-include-events-times").show();
  else
    jQuery(context).parent().parent().find(".mec-include-events-times").hide();

  if (style === "accordion") {
    jQuery(context).parent().parent().find("#mec_skin_list_localtime").hide();
    jQuery(context).parent().parent().find(".mec-event-price-container").hide();
  } else {
    jQuery(context).parent().parent().find("#mec_skin_list_localtime").show();
    jQuery(context).parent().parent().find(".mec-event-price-container").show();
  }

  let $mobile_clean_style;
  if (skin === 'monthly_view') {
    const $right_section = jQuery('.mec-display-events-right-section');
    $mobile_clean_style = jQuery('#mec_skin_monthly_view_mobile_clean_style_wrapper');

    if(style === 'clean' || style === 'modern')
    {
      $right_section.show();
      $mobile_clean_style.hide();
    }
    else
    {
      $right_section.hide();
      $mobile_clean_style.show();
    }
  }

  if(skin === 'full_calendar')
  {
    $mobile_clean_style = jQuery("#mec_skin_full_calendar_mobile_clean_style_wrapper");
    const monthly_style = jQuery('#mec_skin_full_calendar_monthly_style').val();

    if(monthly_style === 'clean' || monthly_style === 'modern') $mobile_clean_style.hide();
    else $mobile_clean_style.show();
  }

  mec_toggle_image_size_field(skin, style);

  jQuery(document).trigger("mec_skin_style_changed", [skin, style, context]);
}

function mec_toggle_image_size_field(skin, style) {
  const $field = jQuery('.mec-image-size-field[data-skin="' + skin + '"]');

  if (!$field.length) {
    return;
  }

  if ($field.data('forceHidden')) {
    $field.addClass('mec-util-hidden');
    return;
  }

  const rawHideStyles = $field.data('hideStyles');

  if (!rawHideStyles) {
    $field.removeClass('mec-util-hidden');
    return;
  }

  let hideStyles = rawHideStyles;

  if (!Array.isArray(hideStyles)) {
    hideStyles = rawHideStyles.toString().split(',');
  }

  hideStyles = hideStyles
    .map(function (item) {
      return (item || '').toString().trim();
    })
    .filter(function (item) {
      return item.length > 0;
    });

  if (!style && hideStyles.length) {
    $field.addClass('mec-util-hidden');
    return;
  }

  if (hideStyles.indexOf(style) !== -1) {
    $field.addClass('mec-util-hidden');
  } else {
    $field.removeClass('mec-util-hidden');
  }
}

function mec_skin_map_toggle(context) {
  jQuery(context)
    .parent()
    .parent()
    .parent()
    .find(".mec-set-geolocation")
    .toggle();
}

function mec_skin_geolocation_toggle(context) {
  jQuery(context)
    .parent()
    .parent()
    .parent()
    .parent()
    .find(".mec-set-geolocation-focus")
    .toggle();
}

function mec_show_widget_options(context) {
  var skin = jQuery(context).find(jQuery(":selected")).data("skin");
  if (skin === "monthly_view") {
    jQuery(context)
      .parent()
      .parent()
      .find(jQuery(".mec-current-check-wrap"))
      .show();
    jQuery(context)
      .parent()
      .parent()
      .find(jQuery(".mec-grid-options-wrap"))
      .hide();
  } else if (skin === "grid") {
    jQuery(context)
      .parent()
      .parent()
      .find(jQuery(".mec-current-check-wrap"))
      .hide();
    jQuery(context)
      .parent()
      .parent()
      .find(jQuery(".mec-grid-options-wrap"))
      .show();
  } else {
    jQuery(context)
      .parent()
      .parent()
      .find(jQuery(".mec-current-check-wrap"))
      .hide();
    jQuery(context)
      .parent()
      .parent()
      .find(jQuery(".mec-grid-options-wrap"))
      .hide();
  }
}

// Niceselect
jQuery(document).ready(function () {
  if (jQuery(".wn-mec-select").length > 0)
    jQuery(".wn-mec-select").niceSelect();

  // Send Custom Email To Attendees Button
  jQuery(".mec-send-email-button").click(function () {
    var $this = this;
    var data_send = jQuery(".mec-attendees-content")
      .find('input[type="checkbox"]:checked')
      .parent()
      .find(".mec-send-email-attendee-info")
      .text();
    var mail_subject = jQuery("#mec-send-email-subject").val();
    var mail_content = wp.editor.getContent("editor" + jQuery(this).data("id"));
    var mail_message = jQuery("#mec-send-email-message");
    var mail_copy = jQuery("#mec-send-admin-copy").is(":checked") ? 1 : 0;

    if (data_send.length == 0)
      mail_message
        .attr("class", "mec-util-hidden mec-error")
        .html(jQuery("#mec-send-email-no-user-selected").val())
        .show();
    else if (mail_subject.length == 0)
      mail_message
        .attr("class", "mec-util-hidden mec-error")
        .html(jQuery("#mec-send-email-empty-subject").val())
        .show();
    else if (mail_content.length == 0)
      mail_message
        .attr("class", "mec-util-hidden mec-error")
        .html(jQuery("#mec-send-email-empty-content").val())
        .show();
    else {
      mail_message.hide();
      jQuery($this).html(jQuery("#mec-send-email-label-loading").val());
      jQuery.ajax({
        url: mec_admin_localize.ajax_url,
        type: "POST",
        data: {
          action: "mec_mass_email",
          nonce: mec_admin_localize.ajax_nonce,
          mail_recipients_info: data_send,
          mail_subject: mail_subject,
          mail_content: mail_content,
          mail_copy: mail_copy,
        },
        success: function (response) {
          jQuery($this).html(jQuery("#mec-send-email-label").val());
          if (response == true)
            mail_message
              .attr("class", "mec-util-hidden mec-success")
              .html(jQuery("#mec-send-email-success").val())
              .show();
          else
            mail_message
              .attr("class", "mec-util-hidden mec-error")
              .html(jQuery("#mec-send-email-error").val())
              .show();
        },
        error: function () {
          jQuery($this).html(jQuery("#mec-send-email-label").val());
          mail_message
            .attr("class", "mec-util-hidden mec-error")
            .html(jQuery("#mec-send-email-error").val())
            .show();
        },
      });
    }
  });

  jQuery(".mec-attendees-list-left-menu .owl-item").click(function () {
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".mec-send-email-count > span")
      .html(0);
  });
});

// MEC fast copy by one click
function mec_copy_code() {
  var el = document.getElementById("MECCopyCode");
  if (!el) return;

  var text = (el.textContent || "").trim();

  // Preferred: async Clipboard API — reliable for <button> content (Safari/Firefox included)
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text);
    return;
  }

  // Fallback: legacy Range + execCommand (old browsers)
  var range = document.createRange();
  range.selectNode(el);
  window.getSelection().removeAllRanges(); // clear current selection
  window.getSelection().addRange(range); // to select text
  document.execCommand("copy");
  window.getSelection().removeAllRanges(); // to deselect
}

// Check All Send Custom Email To Attendees
function mec_send_email_check(Context) {
  var all_item = jQuery(Context)
    .parent()
    .parent()
    .parent()
    .find(".mec-attendees-content");
  var item_len = all_item.find('input[type="checkbox"]').length;
  var check_len = all_item.find('input[type="checkbox"]:checked').length;
  var all_check = jQuery(Context)
    .parent()
    .parent()
    .parent()
    .find("#mec-send-email-check-all");

  jQuery(".mec-send-email-count > span").html(check_len);
  if (item_len === check_len) all_check.prop("checked", true);
  else all_check.prop("checked", false);
}

function mec_send_email_check_all(Context) {
  var all_item = jQuery(Context)
    .parent()
    .parent()
    .parent()
    .parent()
    .find(".mec-attendees-content");

  if (jQuery(Context).is(":checked"))
    all_item.find('input[type="checkbox"]').prop("checked", true);
  else all_item.find('input[type="checkbox"]').prop("checked", false);

  var check_len = all_item.find('input[type="checkbox"]:checked').length;
  jQuery(".mec-send-email-count > span").html(check_len);
}

(function (wp, $) {
  var items = "";
  if (typeof mec_admin_localize !== "undefined")
    items = JSON.parse(mec_admin_localize.mce_items);

  // Block Editor
  if (items && wp && wp.blocks) {
    items.shortcodes.forEach(function (e, i) {
      wp.blocks.registerBlockType(`mec/blockeditor-${i}`, {
        title: items.shortcodes[i]["PN"]
          .toLowerCase()
          .replace(/(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g, function (s) {
            return s.toUpperCase().replace(/-/g, " ");
          }),
        icon: "calendar-alt",
        category: "mec.block.category",
        edit: function () {
          return `[MEC id="${items.shortcodes[i]["ID"]}"]`;
        },
        save: function () {
          return `[MEC id="${items.shortcodes[i]["ID"]}"]`;
        },
      });
    });
  }
})(window.wp, jQuery);

jQuery(document).ready(function($){
    // Settings screens + the MEC / addon metaboxes of the event editor
    if(!$('body').hasClass('mec-ds-settings') && !$('body').hasClass('post-type-mec-events')) return;

    var L10N = (window.mec_admin_localize && window.mec_admin_localize.l10n) || {};

    var seq = 0;
    var TIPS = 'body.mec-ds-settings .mec-tooltip, body.post-type-mec-events .mec-metabox .mec-tooltip';

    // Raw "add a row" templates (:i: placeholders) live hidden in the page and are
    // cloned by their "Add" buttons. They must not be numbered: the clone would copy
    // the same id (duplicate IDs, and aria-describedby pointing at the hidden template
    // box instead of the new row's own box).
    var isTemplate = function($tip){
        return $tip.closest('.mec-util-hidden, [id$="_raw"], [id*="_raw_"], [id^="mec_new_"]').length > 0;
    };

    var nextId = function(){
        var id;
        do { id = 'mec-settings-tip-' + (++seq); } while(document.getElementById(id));
        return id;
    };

    var initTip = function($tip){
        var $box = $tip.find('.box').first();
        if(!$box.length || $tip.data('mecTipReady') || isTemplate($tip)) return;
        $tip.data('mecTipReady', true);

        // A cloned row can arrive with an id copied from elsewhere: keep an id only if it is unique.
        var id = $box.attr('id');
        if(!id || $('[id="' + id + '"]').length > 1) id = nextId();
        $box.attr('id', id).attr('role', 'tooltip');
        $tip.attr('tabindex', '0').attr('aria-describedby', id).attr('aria-expanded', 'false');
        if(!$tip.attr('role')) $tip.attr('role', 'button');

        // The wrapper is the single focus target: give it an accessible name
        // (same source the former icon-level copy used) and hide the purely
        // decorative help icon from assistive tech / the Tab order.
        var $icon = $tip.find('i').first();
        if(!$tip.attr('aria-label') && !$tip.attr('aria-labelledby') && !$tip.attr('title')){
            var label = $icon.attr('aria-label') || $icon.attr('title') || $tip.find('.title').first().text();
            $tip.attr('aria-label', $.trim(label || '') || L10N.more_information || 'More information');
        }
        $icon.removeAttr('tabindex role aria-describedby aria-label').attr('aria-hidden', 'true');
    };

    $(TIPS).each(function(){ initTip($(this)); });

    // Rows added later (tickets, fees, variations, ...) bring their own tooltips.
    if(window.MutationObserver){
        var root = document.querySelector('.wns-be-main') || document.getElementById('post-body') || document.body;
        new MutationObserver(function(muts){
            for(var i = 0; i < muts.length; i++){
                for(var j = 0; j < muts[i].addedNodes.length; j++){
                    var node = muts[i].addedNodes[j];
                    if(node.nodeType !== 1) continue;
                    var $node = $(node);
                    ($node.is('.mec-tooltip') ? $node : $node.find('.mec-tooltip')).each(function(){
                        var $t = $(this);
                        // same scope as TIPS: settings pages, and MEC metaboxes of the event editor
                        if($('body').hasClass('mec-ds-settings') || $t.closest('.mec-metabox').length) initTip($t);
                    });
                }
            }
        }).observe(root, {childList: true, subtree: true});
    }
});

jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    var $crumb = $('#mec-infobar-section');
    if(!$crumb.length) return;

    var updateCrumb = function($link){
        var text = $link.contents().filter(function(){ return this.nodeType === 3; }).text().trim();
        text = text || $link.text().trim();
        $crumb.text(text);

        var $wrap = $crumb.closest('.mec-infobar-crumb');
        var $tab = $wrap.find('.c-tab');
        $wrap.toggleClass('mec-crumb-dedup', $tab.length > 0 && $tab.text().trim() === text);
    };

    var $active = $('.wns-be-sidebar .subsection li.active a.WnTabLinks').first();
    if($active.length) updateCrumb($active);

    $(document).on('click', '.wns-be-sidebar .subsection a.WnTabLinks', function(){
        updateCrumb($(this));
    });
});

jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    var $sel = $('#mec_captcha_provider');
    if(!$sel.length) return;

    var panels = {
        v2: '#mec_google_recaptcha_container_toggle',
        v3: '#mec_google_recaptcha_v3_container_toggle',
        mt: '#mec_mtcaptcha_container_toggle'
    };
    var checks = {
        v2: '#mec_google_recaptcha_checkbox',
        v3: '#mec_google_recaptcha_v3_checkbox',
        mt: '#mec_mtcaptcha_checkbox'
    };

    // apply() runs on explicit user choice only — NEVER on page load. The initial
    // state (select + panels + checkboxes) is fully server-rendered, and a
    // load-time sync would silently rewrite saved data: a legacy setup with
    // two enabled providers would be reduced to one behind display:none
    // toggles and the next save would make the loss permanent.
    var apply = function(val){
        $.each(panels, function(key, panel){
            var $chk = $(checks[key]);
            $chk.prop('checked', key === val);

            $(panel).toggleClass('mec-util-hidden', key !== val);
        });
    };

    $sel.on('change', function(){ apply(this.value); });

    // P1-08 - visibility-only sync once on DOM ready and on bfcache restore,
    // so a browser that restores an unsaved select value on reload still
    // shows the matching panel. It never changes a checkbox (see above),
    // and a panel stays visible while its own provider is enabled, so a
    // legacy two-provider setup keeps every enabled panel on screen.
    var syncPanels = function(){
        var val = $sel.val();
        $.each(panels, function(key, panel){
            $(panel).toggleClass('mec-util-hidden', key !== val && !$(checks[key]).prop('checked'));
        });
    };

    syncPanels();
    $(window).off('pageshow.mecCaptchaSwitcher').on('pageshow.mecCaptchaSwitcher', syncPanels);
});

jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    var KEY = 'mec-basvanced-pref';

    var readStore = function(){
        try { return JSON.parse(localStorage.getItem(KEY) || '{}'); }
        catch(e) { return {}; }
    };

    $('.mec-basvanced-toggle').each(function(){
        var $wrap = $(this);
        var target = $wrap.data('for');
        if(!target) return;

        var pref = readStore()[target];
        if(pref === 'advanced' || pref === 'basic'){
            var idx = (pref === 'advanced') ? 1 : 0;
            $wrap.find('.mec-backend-tab-item').eq(idx).trigger('click');
        }

        $wrap.find('.mec-backend-tab-item').on('click', function(){
            var idx = $wrap.find('.mec-backend-tab-item').index(this);
            var store = readStore();
            store[target] = (idx === 1) ? 'advanced' : 'basic';
            try { localStorage.setItem(KEY, JSON.stringify(store)); } catch(e) {}
        });
    });
});

// P1-06 - Form-independent settings guards. One registry for every
// settings form: core tabs mark their <form> with data-mec-settings-form,
// and add-on pages rendered inside .wns-be-main are matched by position.
// Each form keeps its own serialized baseline in jQuery data.
window.mecSettingsGuard = window.mecSettingsGuard || (function($){
    var selector = 'form[data-mec-settings-form], body.mec-ds-settings .wns-be-main form';

    var forms = function(){
        if(!document.body || !document.body.classList.contains('mec-ds-settings')) return $();
        return $(selector);
    };

    var reset = function($forms){
        $forms.each(function(){
            $(this).data('mecSettingsBaseline', $(this).serialize());
        });
    };

    var isDirty = function(){
        var dirty = false;
        forms().each(function(){
            var $f = $(this);
            var base = $f.data('mecSettingsBaseline');
            if(typeof base === 'undefined'){ reset($f); return; }
            if($f.serialize() !== base){ dirty = true; return false; }
        });
        return dirty;
    };

    return {
        selector: selector,
        fieldSelector: 'form[data-mec-settings-form] :input, body.mec-ds-settings .wns-be-main form :input',
        forms: forms,
        reset: reset,
        isDirty: isDirty
    };
})(jQuery);

// Call from a tab's AJAX-save success. Pass the saved form, or nothing to
// reset every settings form on the page.
window.mecSettingsMarkSaved = window.mecSettingsMarkSaved || function($form){
    jQuery(window).trigger('mec-settings-saved', [$form]);
};

// True only for a parsed JSON save reply with success 1/true;
// {success:0,code:...}, plain text, 0 and -1 do not count as saved.
window.mecSettingsSaveOk = window.mecSettingsSaveOk || function(data){
    var res = data;
    if(typeof res === 'string'){
        try { res = JSON.parse(res); } catch(err){ return false; }
    }
    return !!(res && typeof res === 'object' && (res.success === 1 || res.success === true));
};

// Settings-save busy state (replaces the old gray .mec-loarder-wrap overlay):
// while the AJAX save runs the form area is dimmed and made inert, so fields
// can't be edited mid-save. inert blocks click/focus/typing natively; the
// .mec-is-saving class adds the dim look plus a pointer-events fallback for
// browsers without inert support.
window.mecSettingsSaving = window.mecSettingsSaving || function(on){
    var $main = jQuery('.wns-be-main');
    if(!$main.length) return;
    $main.toggleClass('mec-is-saving', !!on).prop('inert', !!on);
};

// Admin toast — the jQuery twin of the mec-user-dashboard React Toast.tsx:
// same markup (.mec-ud-toast classes), timings and look, so backend feedback
// matches the frontend dashboard. Types: success | error | info.
// opts.sticky keeps the toast on screen until dismissed; opts.action +
// opts.actionLabel render an inline action button (e.g. Retry) that runs the
// callback and closes the toast.
window.mecToast = window.mecToast || (function(){
    var AUTO_MS = { success: 4000, info: 4000, error: 6000 };
    var EXIT_MS = 220;
    var ICONS = { success: '\u2713', error: '\u2715', info: 'i' };

    var dismiss = function($toast){
        if($toast.hasClass('is-leaving')) return;
        $toast.addClass('is-leaving');
        setTimeout(function(){ $toast.remove(); }, EXIT_MS);
    };

    return function(message, type, opts){
        if(!message) return;
        opts = opts || {};
        type = AUTO_MS[type] ? type : 'info';

        var L = (window.mec_admin_localize && window.mec_admin_localize.l10n) || {};

        var $viewport = jQuery('#mec-toast-viewport');
        if(!$viewport.length){
            $viewport = jQuery('<div id="mec-toast-viewport" class="mec-ud-toast-viewport" role="status" aria-live="polite"></div>').appendTo('body');
        }

        var $toast = jQuery('<div class="mec-ud-toast"></div>').addClass('is-' + type);
        jQuery('<span class="mec-ud-toast__icon" aria-hidden="true"></span>').text(ICONS[type]).appendTo($toast);
        jQuery('<span class="mec-ud-toast__message"></span>').text(message).appendTo($toast);

        if(typeof opts.action === 'function'){
            jQuery('<button type="button" class="mec-ud-toast__action"></button>')
                .text(opts.actionLabel || L.retry || 'Retry')
                .on('click', function(){ dismiss($toast); opts.action(); })
                .appendTo($toast);
        }

        jQuery('<button type="button" class="mec-ud-toast__close"></button>')
            .attr('aria-label', L.dismiss_notice || 'Dismiss notification')
            .text('\u00d7')
            .on('click', function(){ dismiss($toast); })
            .appendTo($toast);

        $viewport.append($toast);
        if(!opts.sticky) setTimeout(function(){ dismiss($toast); }, AUTO_MS[type]);
        return $toast;
    };
})();

// Failed settings save (shared by General and the other core tabs): end the
// loading state and show an error toast with a Retry action. Retry clicks
// $retry, or the tab's Save button when none is given.
window.mecSettingsSaveFailed = window.mecSettingsSaveFailed || function($retry){
    var L = (window.mec_admin_localize && window.mec_admin_localize.l10n) || {};
    clearTimeout(jQuery(document).data('mecSettingsSaveTimer'));
    if(window.mecSettingsSaving) window.mecSettingsSaving(false);
    // Back-compat: add-ons running their own save flow may still inject the
    // legacy gray overlay; core tabs no longer do.
    jQuery('.mec-loarder-wrap').remove();
    jQuery('.dpr-save-btn').removeClass('loading').text(L.save_changes || 'Save Changes');

    window.mecToast(L.save_failed || "Couldn't save your changes. Your edits are still on this page - nothing was lost.", 'error', {
        actionLabel: L.retry || 'Retry',
        action: function(){
            ($retry && $retry.length ? $retry : jQuery('.dpr-save-btn').first()).trigger('click');
        }
    });
};

// Save feedback for the core tabs other than General, matching General
// (settings.php): ok -> mark $form saved right away, then after 600ms end the
// loading state and show the "Settings Saved!" toast; otherwise
// mecSettingsSaveFailed(). The pending UI timer is kept on document and
// cleared by the next feedback, so an older save can't clobber a newer one.
window.mecSettingsSaveFeedback = window.mecSettingsSaveFeedback || function($form, ok){
    if(!ok){
        window.mecSettingsSaveFailed();
        return;
    }

    var L = (window.mec_admin_localize && window.mec_admin_localize.l10n) || {};
    var $doc = jQuery(document);
    clearTimeout($doc.data('mecSettingsSaveTimer'));
    if(window.mecSettingsMarkSaved) window.mecSettingsMarkSaved($form);
    $doc.data('mecSettingsSaveTimer', setTimeout(function(){
        if(window.mecSettingsSaving) window.mecSettingsSaving(false);
        // Back-compat: add-ons running their own save flow may still inject the
        // legacy gray overlay; core tabs no longer do.
        jQuery('.mec-loarder-wrap').remove();
        jQuery('.dpr-save-btn').removeClass('loading').text(L.save_changes || 'Save Changes');
        window.mecToast(L.settings_saved || 'Settings Saved!', 'success');
    }, 600));
};

// Standard AJAX save for a settings form (used by add-on tabs rendered through
// MEC_main::render_settings_shell()). Replaces the ~80 line per-tab submit
// handlers: double-submit guard, busy state, request to admin-ajax, then
// mecSettingsSaveFeedback() for the toast / loading reset.
//
//   mecSettingsBindForm('#my_form', {
//       action:   'mec_save_settings',              // optional
//       validate: function($form){ return true; },  // optional, false aborts
//       extraData: function($form){ return '&a=b'; }, // optional, appended to the request body
//       done:     function(ok, response){}          // optional
//   });
//
// The Save buttons are <button type="submit" form="my_form" class="dpr-save-btn">,
// so a click submits natively (no click handler / hidden button needed).
window.mecSettingsBindForm = window.mecSettingsBindForm || function(selector, opts){
    var $ = jQuery;
    opts = opts || {};

    var $form = $(selector);
    if(!$form.length) return;

    $form.off('submit.mecSettingsBind').on('submit.mecSettingsBind', function(e){
        e.preventDefault();

        var L = (window.mec_admin_localize && window.mec_admin_localize.l10n) || {};
        var $btn = $('.dpr-save-btn');

        // A save is already running (double click): ignore this one.
        if($btn.first().hasClass('loading')) return;

        if(typeof opts.validate === 'function' && opts.validate($form) === false) return;

        $btn.addClass('loading').text(L.saving || 'Saving ...');
        if(window.mecSettingsSaving) window.mecSettingsSaving(true);

        var finish = function(ok, response){
            window.mecSettingsSaveFeedback($form, ok);
            if(typeof opts.done === 'function') opts.done(ok, response);
        };

        try
        {
            $.ajax({
                type: 'POST',
                url: window.ajaxurl,
                timeout: 30000,
                data: 'action=' + encodeURIComponent(opts.action || 'mec_save_settings') + '&' + $form.serialize() + ((typeof opts.extraData === 'function' && opts.extraData($form)) || ''),
                success: function(data){ finish(window.mecSettingsSaveOk(data), data); },
                error: function(){ finish(false); }
            });
        }
        catch(err)
        {
            // Never leave the button locked if something throws before the request is sent.
            window.mecSettingsSaveFailed();
            throw err;
        }
    });
};

jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    var G = window.mecSettingsGuard;
    if(!G.forms().length) return;

    G.reset(G.forms());

    // Some tabs fill fields from JS after DOM ready (pickers, builders).
    // Re-take the baseline once on window load unless the user has already
    // edited something, so that is not reported as an unsaved change.
    var touched = false;
    $(document).off('input.mecSettingsGuard change.mecSettingsGuard')
        .on('input.mecSettingsGuard change.mecSettingsGuard', G.fieldSelector, function(e){
            if(e.originalEvent) touched = true;
        });
    $(window).off('load.mecSettingsGuard').on('load.mecSettingsGuard', function(){
        if(!touched) G.reset(G.forms());
    });

    var L10N = (window.mec_admin_localize && window.mec_admin_localize.l10n) || {};

    $(window).off('beforeunload.mecSettingsGuard').on('beforeunload.mecSettingsGuard', function(){
        if(G.isDirty()){
            return L10N.unsaved_changes || 'You have unsaved changes on this page. Leaving now will discard them.';
        }
    });

    $(window).off('mec-settings-saved.mecSettingsGuard').on('mec-settings-saved.mecSettingsGuard', function(e, $form){
        var $saved = ($form && $form.jquery) ? $form.filter(G.selector) : $();
        G.reset($saved.length ? $saved : G.forms());
    });

    // Fallback for add-on pages that save through admin-ajax
    // action=mec_save_settings from their own inline scripts without calling
    // mecSettingsSaveFeedback()/mecSettingsMarkSaved() (older add-on
    // versions). The migrated add-ons call those themselves; a second reset is
    // harmless. Only runs on pages without a core settings form.
    if(!$('form[data-mec-settings-form]').length){
        $(document).off('ajaxSuccess.mecSettingsGuard').on('ajaxSuccess.mecSettingsGuard', function(e, xhr, opts, data){
            var sent = (opts && typeof opts.data === 'string') ? opts.data : '';
            if(!/(^|&)action=mec_save_settings(&|$)/.test(sent)) return;
            if(!window.mecSettingsSaveOk(data)) return;
            window.mecSettingsMarkSaved();
        });
    }
});

jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    var L10N = (window.mec_admin_localize && window.mec_admin_localize.l10n) || {};
    var PREVIEW = L10N.preview || 'Preview: %s';

    var $dateSample = $('#mec_datepicker_sample');
    var $dateSelect = $('#mec_settings_datepicker_format');

    if($dateSample.length && $dateSelect.length){
        var updateDateSample = function(){
            var text = $dateSelect.find('option:selected').text() || '';
            var sample = text.split(' (')[0];
            $dateSample.text(sample ? PREVIEW.replace('%s', $.trim(sample)) : '');
        };
        updateDateSample();
        $dateSelect.on('change', updateDateSample);
    }

    var $timeSample = $('#mec_timeformat_sample');
    var $timeSelect = $('#mec_settings_time_format');

    if($timeSample.length && $timeSelect.length){
        var updateTimeSample = function(){
            var d = new Date();
            var h = d.getHours();
            var m = ('0' + d.getMinutes()).slice(-2);
            var sample;
            if($timeSelect.val() === '24'){
                sample = ('0' + h).slice(-2) + ':' + m;
            } else {
                var suffix = (h >= 12) ? 'PM' : 'AM';
                var h12 = h % 12; if(h12 === 0) h12 = 12;
                sample = ('0' + h12).slice(-2) + ':' + m + ' ' + suffix;
            }
            $timeSample.text(PREVIEW.replace('%s', sample));
        };
        updateTimeSample();
        $timeSelect.on('change', updateTimeSample);
    }
});

jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    $('.mec-basvanced-toggle').each(function(){
        var $items = $(this).find('.mec-backend-tab-item');
        var sync = function(){
            $items.each(function(){
                $(this).attr('aria-pressed', $(this).hasClass('mec-b-active-tab') ? 'true' : 'false');
            });
        };
        // Native <button> items (settings.php) already have role/focus/keys.
        var $divs = $items.not('button').attr({ 'role': 'button', 'tabindex': '0' });
        sync();
        // Defer: the class swap happens in the delegated events.js handler, after this one.
        $items.on('click', function(){ setTimeout(sync, 0); });
        $divs.on('keydown', function(e){
            if(e.key === 'Enter' || e.key === ' '){ e.preventDefault(); $(this).trigger('click'); }
        });
    });

    // NOTE: sidebar .WnTabLinks get their role="tab" / tabindex / arrow-key
    // handling from the "Keyboard & ARIA tab pass" ready block further down.
    // An older block here also stamped role="button" + tabindex=0 on the same
    // elements — dead code that the tab block immediately overrode, so it was
    // removed.
});

// Shared dropdown panel under the settings search box (.mec-search-settings-wrap
// is position:relative, so the panel anchors to it whether the infobar is
// static or sticky). Both search feedback cards — the no-match empty state and
// the cross-group links — render here, floating OVER the content instead of
// prepending to #wns-be-content and pushing the filtered settings down on
// every keystroke. The panel carries no chrome of its own, so it is invisible
// while both cards are hidden/removed.
window.mecSearchPanel = window.mecSearchPanel || function(){
    var $panel = jQuery('#mec-search-panel');
    if(!$panel.length){
        $panel = jQuery('<div id="mec-search-panel" class="mec-search-panel"></div>').appendTo('.mec-search-settings-wrap');
    }
    return $panel;
};

jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    var $input = $('#mec-search-settings');
    // The index is localized (factory.php) on every MEC settings tab.
    var LOC = window.mec_admin_localize || {};
    if(!$input.length || !$.isArray(LOC.settings_search_index)) return;

    var L10N = LOC.l10n || {};

    // Copy: add-on group links are pushed below.
    var index = LOC.settings_search_index.slice();

    // P2-09 - Add-on custom settings pages (page=MEC-settings&tab=...) are not
    // part of the server index; pick their group links up from the sidebar so
    // "RSVP", "Seat", "User Dashboard", ... are found from the main tab too.
    var tabOf = function(url){
        var m = /[?&]tab=([^&#]*)/.exec(String(url || ''));
        if(!m) return '';
        try { return decodeURIComponent(m[1]); } catch(e){ return m[1]; } // malformed %
    };
    var knownTabs = {};
    index.forEach(function(g){ knownTabs[tabOf(g.url)] = true; });
    $('.wns-be-group-menu > li.wns-be-group-menu-li > a.wns-be-group-tab-link-a[href]').each(function(){
        var href = $(this).attr('href');
        var tab = tabOf(href);
        var label = $.trim($(this).find('.wns-be-group-menu-title').first().text() || $(this).text()).replace(/\s+/g, ' ');
        if(!tab || !label || knownTabs[tab] || href.indexOf('page=MEC-settings') === -1) return;
        knownTabs[tab] = true;
        index.push({ group: label, url: href, items: [] });
    });

    var $box = $('<div class="mec-search-cross" id="mec-search-cross"></div>').hide().appendTo(window.mecSearchPanel ? window.mecSearchPanel() : '#wns-be-content');

    // U3 - Deep cross-tab search. The server index above (and the sidebar
    // scrape) only carry GROUP and SECTION titles, so an option that lives
    // on ANOTHER tab — "Auto verification for free bookings" on the Booking
    // tab — was unfindable from every other tab. On the first qualifying
    // query the other settings tabs are fetched in the background (max 2 at
    // a time), their per-option labels + section anchors are extracted with
    // the SAME selectors the in-page search matches, and the result is
    // cached in sessionStorage (keyed by plugin version) — once per browser
    // session. Deep results render as "Group › Option label" and deep-link
    // as url#section?q=<query>; the landing pass (bottom of this file)
    // activates the section, prefills the box and lets the U2 jump scroll
    // to the exact option and pulse it.
    var DEEP_SELECTORS = '.mec-field__label label, .mec-check > span, label[class*="mec-col-"], .mec-form-row > label, h5';
    var CACHE_PREFIX = 'mec-search-deep:' + String(LOC.settings_search_index_version || '0') + ':';
    var currentTab = tabOf(window.location.href);

    var ssGet = function(k){ try { return sessionStorage.getItem(k); } catch(e){ return null; } };
    var ssSet = function(k, v){ try { sessionStorage.setItem(k, v); } catch(e){} };

    var deepEntries = [];   // [{t: label, g: group, u: url#section}]
    var queuedTabs = null;  // tabs left to fetch
    var pending = 0;        // fetches in flight
    var started = false;

    var parseTabHtml = function(job, html){
        var entries = [];
        try
        {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            $(doc).find('#wns-be-content .mec-options-fields').each(function(){
                if(!this.id) return;
                var base = job.url.split('#')[0] + '#' + this.id;
                var seen = {};
                $(this).find(DEEP_SELECTORS).each(function(){
                    var t = String($(this).text() || '').replace(/\s+/g, ' ').trim();
                    if(t.length < 3 || t.length > 120 || seen[t]) return;
                    seen[t] = true;
                    entries.push({ t: t, g: job.group, u: base });
                });
            });
        }
        catch(e){ return; }
        ssSet(CACHE_PREFIX + job.tab, JSON.stringify(entries));
        deepEntries = deepEntries.concat(entries);
    };

    var queueNext = function(){
        if(!queuedTabs || !queuedTabs.length || pending >= 2) return;
        var job = queuedTabs.shift();
        pending++;
        fetch(job.url, { credentials: 'same-origin' })
            .then(function(r){ return r.ok ? r.text() : ''; })
            .then(function(html){ pending--; if(html) parseTabHtml(job, html); queueNext(); refresh(); })
            .catch(function(){ pending--; queueNext(); refresh(); });
    };

    var ensureIndexed = function(){
        if(started) return;
        started = true;
        queuedTabs = [];
        index.forEach(function(g){
            var tab = tabOf(g.url);
            if(!tab || tab === currentTab) return; // current tab is searched live
            var cached = ssGet(CACHE_PREFIX + tab);
            if(cached !== null)
            {
                try { deepEntries = deepEntries.concat(JSON.parse(cached)); return; } catch(e){}
            }
            queuedTabs.push({ tab: tab, url: g.url, group: g.group });
        });
        queueNext();
    };

    var render = function(value){
        // Same normalisation as the in-page search: collapse whitespace, trim;
        // an empty or whitespace-only query clears the box.
        var raw = String(value || '').replace(/\s+/g, ' ').trim();
        var q = raw.toLowerCase();
        if(q.length < 2){ $box.hide().empty(); return; }

        var out = [];
        index.forEach(function(g){
            if(out.length < 8 && g.group.toLowerCase().indexOf(q) > -1){
                out.push({ t: g.group, u: g.url });
            }
            (g.items || []).forEach(function(it){
                if(out.length < 8 && it.label.toLowerCase().indexOf(q) > -1){
                    out.push({ t: g.group + ' \u203A ' + it.label, u: it.url });
                }
            });
        });
        deepEntries.forEach(function(e){
            if(out.length < 8 && e.t.toLowerCase().indexOf(q) > -1){
                out.push({ t: e.g + ' \u203A ' + e.t, u: e.u + '?q=' + encodeURIComponent(raw) });
            }
        });

        var busy = pending > 0 || (queuedTabs && queuedTabs.length > 0);
        // While background indexing runs, keep the box up (with the busy
        // line) even before any hit arrives — silence reads as "nothing
        // found" otherwise, and deep hits may land a beat later.
        if(!out.length && !busy){ $box.hide().empty(); return; }

        $box.empty();
        if(out.length){
            // Built with .text()/.attr() so group and label names are never parsed as HTML.
            var $ul = $('<ul></ul>');
            out.forEach(function(r){ $('<li></li>').append($('<a></a>').attr('href', r.u).text(r.t)).appendTo($ul); });
            $box.append($('<strong></strong>').text(L10N.search_other_tabs || 'Found in other tabs'), $ul);
        }
        if(busy){
            $box.append($('<div class="mec-search-indexing"></div>').text(String(L10N.search_indexing || 'Searching other tabs\u2026')));
        }
        $box.show();
    };

    var refresh = function(){ render($input.val()); };

    $input.on('input', function(){
        // Old browsers without fetch keep the shallow group/section search.
        var v = String(this.value || '').trim();
        if(typeof fetch !== 'undefined' && v.length >= 2) ensureIndexed();
        render(this.value);
    });
});

jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    var BASE = {
        monthly_view: [['classic','Classic'],['clean','Clean'],['modern','Modern'],['novel','Novel'],['simple','Simple']],
        list: [['classic','Classic'],['minimal','Minimal'],['modern','Modern'],['standard','Standard'],['accordion','Toggle']],
        grid: [['classic','Classic'],['clean','Clean'],['minimal','Minimal'],['modern','Modern'],['simple','Simple'],['colorful','Colorful'],['novel','Novel']],
        weekly_view: [['classic','Classic']],
        daily_view: [['classic','Classic']],
        full_calendar: [['classic','Classic']],
        yearly_view: [['modern','Modern']],
        timetable: [['modern','Modern'],['clean','Clean']],
        map: [['classic','Classic']]
    };

    var initSkinField = function(cfg){
        var $skin = $(cfg.skin);
        if(!$skin.length) return;

        var $style = $(cfg.style), $wrap = $(cfg.wrap);
        // Wrapper used to be a <span>; it is a <div> now — the input's direct
        // parent is tag-agnostic and always the .mec-*-custom-skins wrapper.
        var $custom = $(cfg.custom).parent();
        var $method = cfg.methodRow ? $(cfg.methodRow) : $();

        var hidden = {};
        $(cfg.hiddenScope).each(function(){
            hidden[$(this).attr('data-mec-skin-style')] = $(this);
        });

        var sync = function(){
            var skin = $skin.val();
            if($custom.length) $custom.toggle(skin === 'custom');
            if($method.length) $method.toggleClass('mec-util-hidden', skin === 'custom');

            var options = BASE[skin];
            if(!options || !$style.length){ if($wrap.length) $wrap.hide(); return; }

            var current = hidden[skin] ? hidden[skin].val() : '';
            if(!options.some(function(o){ return o[0] === current; })) current = options[0][0];

            $style.empty();
            options.forEach(function(o){ $('<option/>').val(o[0]).text(o[1]).appendTo($style); });
            $style.val(current);
            if(hidden[skin]) hidden[skin].val(current);
            $wrap.show();
        };

        $style.on('change', function(){
            var skin = $skin.val();
            if(hidden[skin]) hidden[skin].val($style.val());
        });

        $skin.on('change', sync);
        sync();
    };

    initSkinField({
        skin: '#mec_settings_default_skin_archive',
        style: '#mec_archive_style_select',
        wrap: '#mec_archive_style_wrap',
        custom: '#mec_settings_custom_archive',
        methodRow: '.mec-archive-events-method-row',
        hiddenScope: '.wns-be-main [data-mec-skin-style][name*="archive_skin"], .wns-be-main [data-mec-skin-style][name*="_archive"]'
    });

    initSkinField({
        skin: '#mec_settings_default_skin_category',
        style: '#mec_category_style_select',
        wrap: '#mec_category_style_wrap',
        custom: '#mec_settings_custom_archive_category',
        methodRow: null,
        hiddenScope: '.wns-be-main [data-mec-skin-style][name*="category_skin"]'
    });
});

jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    var syncReveal = function($ctl){
        var target = $ctl.attr('data-mec-reveals');
        if(!target) return;

        var hide;
        if($ctl.is('select')){
            var expected = $ctl.attr('data-mec-reveals-value');
            hide = (expected !== undefined) ? ($ctl.val() !== expected) : !$ctl.val();
        } else {
            hide = !$ctl.prop('checked');
        }

        // Clear any legacy inline "display" (e.g. server-rendered
        // style="display:none" from older templates or addons) so the
        // hidden-state classes above are the single source of truth.
        $(target).toggleClass('mec-is-hidden', hide).toggleClass('mec-util-hidden', hide).css('display', '');
    };

    $(document).on('change', '[data-mec-reveals]', function(){ syncReveal($(this)); });

    $('[data-mec-reveals]').each(function(){ syncReveal($(this)); });
});

/**
 * QA18: keep legacy inline "jQuery('#X').toggle();" status checkboxes in sync
 * with their target. .toggle() only flips the current visibility, so any
 * mismatch between the server-rendered class and the checkbox state (e.g. a
 * section never saved, or browser form-state / bfcache restore) used to make
 * the fields show when the option was OFF. This handler runs after the inline
 * one and sets visibility from the checkbox state. It never changes values.
 */
jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    var SEL = 'form[data-mec-settings-form] input[type="checkbox"][onchange*="\').toggle()"]';
    var NAME = /^mec\[/;

    var syncLegacyToggle = function(el){
        var name = el.getAttribute('name') || '';
        if(!NAME.test(name) || name.indexOf('inherit') !== -1) return;
        var m = (el.getAttribute('onchange') || '').match(/jQuery\('#([\w-]+)'\)\.toggle\(\)/);
        if(!m) return;
        var $t = $(document.getElementById(m[1]));
        if(!$t.length) return;
        var on = !!el.checked;
        $t.toggleClass('mec-util-hidden', !on).css('display', on ? '' : 'none');
    };

    var syncAll = function(){ $(SEL).each(function(){ syncLegacyToggle(this); }); };

    $(document).on('change', SEL, function(){ syncLegacyToggle(this); });
    syncAll();
    $(window).on('load pageshow', syncAll);
});

jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    var G = window.mecSettingsGuard;
    if(!G || !G.forms().length) return;

    var timer = null;

    // Dirty indicator (UX plan item U11): amber dot on the Save buttons while
    // a form differs from its saved baseline. The old #mec_unsaved_hint text
    // span is gone — it took space in the sticky infobar flex row and pushed
    // the settings search box aside every time a field was edited.
    var refresh = function(){
        $('.dpr-save-btn').toggleClass('mec-dirty', G.isDirty());
    };

    $(document).off('change.mecSettingsDirty input.mecSettingsDirty')
        .on('change.mecSettingsDirty input.mecSettingsDirty', G.fieldSelector, function(){
            clearTimeout(timer);
            timer = setTimeout(refresh, 300);
        });

    // Runs after the guard block's own handlers (bound earlier), so the
    // baseline has already been reset when refresh() reads it.
    $(window).off('mec-settings-saved.mecSettingsDirty load.mecSettingsDirty')
        .on('mec-settings-saved.mecSettingsDirty load.mecSettingsDirty', function(){
            setTimeout(refresh, 0);
        });

    refresh();
});

jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    var KEY = 'mec-last-section';
    var $links = $('.wns-be-sidebar .WnTabLinks');
    if(!$links.length) return;

    var hash = window.location.hash.replace('#', '');

    if(!hash){
        var stored = null;
        try { stored = localStorage.getItem(KEY); } catch(e){}
        if(stored){
            var $target = $links.filter('[data-id="' + stored + '"]');
            if($target.length) $target.trigger('click');
        }
    }

    $links.on('click', function(){
        try { localStorage.setItem(KEY, $(this).attr('data-id')); } catch(e){}
    });
});

// U3 - Deep-link landing for the cross-tab search: #sectionId?q=...
// Activates the target section, then prefills the search box with q so the
// normal in-page filter runs and the U2 jump scrolls to the exact option
// and pulses it. Section activation also fixes the pre-existing #anchor
// cross-group links, which used to land on the tab's FIRST section because
// nothing on the client ever consumed the hash. Runs after the search
// bindings above (ready blocks fire in registration order).
jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    var hash = String(window.location.hash || '');
    if(hash.charAt(0) === '#') hash = hash.slice(1);
    if(!hash) return;

    var safeDecode = function(s){
        try { return decodeURIComponent(s); } catch(e){ return s; }
    };

    var parts = hash.split('?');
    var sectionId = safeDecode(parts.shift());
    var q = '';
    if(parts.length){
        try { q = new URLSearchParams(parts.join('?')).get('q') || ''; } catch(e){}
    }

    if(sectionId){
        var $link = $('.wns-be-sidebar .subsection a.WnTabLinks[data-id="' + sectionId + '"]');
        if($link.length) $link.trigger('click');
    }

    if(q){
        var $input = $('#mec-search-settings');
        if($input.length){
            $input.val(q).trigger('input');
            // Focus without scrolling: the U2 jump owns the page movement.
            try { $input[0].focus({ preventScroll: true }); } catch(e){}
        }
    }
});

jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    var L10N = (window.mec_admin_localize && window.mec_admin_localize.l10n) || {};

    var oneChar = function(v){
        if(v === '' || v.length === 1) return '';
        return L10N.separator_one_char || 'Separator must be exactly one character.';
    };

    var slugRule = function(v){
        if(v === '' || /^[a-z0-9-]*$/.test(v)) return '';
        return L10N.slug_invalid || 'Use only lowercase letters (a-z), numbers and hyphens.';
    };

    var keyRule = function(v){
        if(v === '' || v === $.trim(v)) return '';
        return L10N.no_spaces || 'This value should not start or end with a space.';
    };

    var RULES = {
        '#mec_settings_currency_decimals': function(v){
            if(v === '' || (/^\d+$/.test(v) && +v <= 6)) return '';
            return L10N.decimals_range || 'Decimals must be a whole number between 0 and 6.';
        },
        '#mec_settings_thousand_separator': oneChar,
        '#mec_settings_decimal_separator': oneChar,
        '#mec_settings_slug': slugRule,
        '#mec_settings_category_slug': slugRule,
        '#mec_settings_google_recaptcha_sitekey': keyRule,
        '#mec_settings_google_recaptcha_secretkey': keyRule,
        '#mec_settings_google_recaptcha_v3_sitekey': keyRule,
        '#mec_settings_google_recaptcha_v3_secretkey': keyRule,
        '#mec_settings_mtcaptcha_sitekey': keyRule,
        '#mec_settings_mtcaptcha_privatekey': keyRule,
        '#mec_settings_google_geocoding_api_key': keyRule
    };

    // soft: the value is the one loaded from the database (a legacy value
    // saved before the rule existed). It is shown but does not block saving.
    var showMsg = function($el, message, soft){
        var id = $el.attr('id');
        var $control = $el.closest('.mec-field__control');
        var $warn = $control.find('.mec-field-warning[data-mec-for="' + id + '"]');

        if(message){
            if(!$warn.length) $warn = $('<p class="mec-field-warning" data-mec-for="' + id + '"></p>').appendTo($control);
            $warn.text(message).toggleClass('mec-field-warning--legacy', !!soft);
            $el.toggleClass('mec-invalid', !soft);
        } else {
            $warn.remove();
            $el.removeClass('mec-invalid');
        }
    };

    // Only values the user changed block saving; an unchanged saved value
    // that breaks a newer rule gets a non-blocking warning.
    var validate = function($el){
        var rule = RULES['#' + $el.attr('id')];
        var value = $el.val();
        showMsg($el, rule ? (rule(value) || '') : '', value === $el.data('mecValidateInitial'));
    };

    var $validated = $();
    Object.keys(RULES).forEach(function(sel){
        var $el = $(sel);
        if(!$el.length) return;
        $el.data('mecValidateInitial', $el.val());
        $validated = $validated.add($el);
        $el.off('.mecSettingsValidate').on('input.mecSettingsValidate change.mecSettingsValidate', function(){ validate($(this)); });
        validate($el);
    });

    // After a successful save the current values become the saved baseline.
    $(window).off('mec-settings-saved.mecSettingsValidate').on('mec-settings-saved.mecSettingsValidate', function(){
        $validated.each(function(){
            var $el = $(this);
            $el.data('mecValidateInitial', $el.val());
            validate($el);
        });
    });

    var hasErrors = function(){
        return $('.mec-field-warning[data-mec-for]').not('.mec-field-warning--legacy').length > 0;
    };

    var block = function(e){
        if(!hasErrors()) return;
        e.preventDefault();
        e.stopPropagation();
        var $first = $('.mec-invalid').first();
        if($first.length){
            $([document.documentElement, document.body]).animate({ scrollTop: $first.offset().top - 140 }, 200);
            $first.trigger('focus');
        }
    };

    // Capture listeners are not namespaced: bind them once per page.
    if(window.mecSettingsValidateBound) return;
    window.mecSettingsValidateBound = true;

    document.addEventListener('click', function(e){
        if(e.target.closest && e.target.closest('.dpr-save-btn, .mec-ud-toast__action, .mec-save-retry')) block(e);
    }, true);

    document.addEventListener('submit', function(e){
        if(e.target && window.mecSettingsGuard && $(e.target).is(window.mecSettingsGuard.selector)) block(e);
    }, true);
});

// P5-06: legacy add-on Save links (<a class="dpr-save-btn"> without href) are not
// keyboard-focusable. Expose them as buttons: Tab stop + Enter/Space -> click.
jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    $('a.dpr-save-btn:not([href])').attr({ 'role': 'button', 'tabindex': '0' }).on('keydown', function(e){
        if(e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar'){
            e.preventDefault();
            this.click();
        }
    });
});

jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    var $tabs = $('.wns-be-sidebar .subsection a.WnTabLinks');
    if(!$tabs.length) return;

    $tabs.each(function(){
        var id = $(this).attr('data-id');
        var tabId = 'mec-tab-' + id;

        $(this).attr({ 'role': 'tab', 'id': tabId, 'aria-controls': id, 'tabindex': '-1' });

        var $panel = $('#' + id + '.mec-options-fields');
        if($panel.length) $panel.attr({ 'role': 'tabpanel', 'aria-labelledby': tabId, 'tabindex': '0' });
    });

    $tabs.closest('ul').attr('role', 'tablist');
    $tabs.closest('li').attr('role', 'presentation');

    var sync = function(){
        $tabs.each(function(){
            var selected = $(this).closest('li').hasClass('active');
            $(this).attr('aria-selected', selected ? 'true' : 'false');
            $(this).attr('tabindex', selected ? '0' : '-1');
        });
        // P5-06: keep the tablist reachable by Tab when no tab is active yet.
        if(!$tabs.filter('[tabindex="0"]').length) $tabs.first().attr('tabindex', '0');
    };
    sync();
    // P5-06: the sidebar's inline script (main.php) marks the first tab active in a
    // later ready handler than this one (backend.js loads in the head); re-sync after it.
    setTimeout(sync, 0);
    $(document).on('click', '.wns-be-sidebar .subsection a.WnTabLinks', sync);

    var move = function($from, delta){
        var list = $tabs.toArray();
        var i = list.indexOf($from[0]);
        if(i < 0) return;
        var j = (i + delta + list.length) % list.length;
        $(list[j]).trigger('click').trigger('focus');
    };

    $tabs.on('keydown', function(e){
        var rtl = $('html').attr('dir') === 'rtl';
        switch(e.key){
            case 'ArrowDown': e.preventDefault(); move($(this), 1); break;
            case 'ArrowUp': e.preventDefault(); move($(this), -1); break;
            case 'ArrowRight': e.preventDefault(); move($(this), rtl ? -1 : 1); break;
            case 'ArrowLeft': e.preventDefault(); move($(this), rtl ? 1 : -1); break;
            case 'Home': e.preventDefault(); $tabs.first().trigger('click').trigger('focus'); break;
            case 'End': e.preventDefault(); $tabs.last().trigger('click').trigger('focus'); break;
            // These tab links render without href, so Enter/Space must be
            // handled explicitly (was handled by the removed role=button block).
            case 'Enter':
            case ' ':
                e.preventDefault();
                $(this).trigger('click');
                break;
        }
    });
});

jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    var $btn = $('#mec_test_captcha_btn');
    var $result = $('#mec_captcha_test_result');
    if(!$btn.length || !$result.length) return;

    var FIELDS = {
        v2: { site: '#mec_settings_google_recaptcha_sitekey', secret: '#mec_settings_google_recaptcha_secretkey' },
        v3: { site: '#mec_settings_google_recaptcha_v3_sitekey', secret: '#mec_settings_google_recaptcha_v3_secretkey' },
        mt: { site: '#mec_settings_mtcaptcha_sitekey', secret: '#mec_settings_mtcaptcha_privatekey' }
    };

    var L10N = (window.mec_admin_localize && window.mec_admin_localize.l10n) || {};

    $btn.on('click', function(){
        var provider = $('#mec_captcha_provider').val() || '';
        var fields = FIELDS[provider];

        if(!fields){
            $result.attr('data-state', 'err').text(L10N.captcha_select_provider || 'Select a captcha provider first.');
            return;
        }

        var site = $(fields.site).val() || '';
        var secret = $(fields.secret).val() || '';

        $btn.prop('disabled', true);
        $result.attr('data-state', '').text(L10N.captcha_testing || 'Testing ...');

        $.post(window.ajaxurl, {
            action: 'mec_test_captcha_keys',
            provider: provider,
            site: site,
            secret: secret,
            nonce: (window.mec_admin_localize && window.mec_admin_localize.captcha_nonce) || ''
        }).done(function(response){
            var ok = response && response.success;
            var message = response && response.data && response.data.message ? response.data.message : (ok ? (L10N.captcha_keys_valid || 'Keys look valid.') : (L10N.captcha_test_failed || 'Test failed.'));
            // 'unverified' = only the key format could be checked (MTCaptcha).
            var state = ok ? ((response.data && response.data.state === 'unverified') ? 'warn' : 'ok') : 'err';
            $result.attr('data-state', state).text(message);
        }).fail(function(xhr){
            var json = xhr && xhr.responseJSON;
            var message = json && json.data && json.data.message ? json.data.message : (L10N.captcha_request_failed || 'Test request failed.');
            $result.attr('data-state', 'err').text(message);
        }).always(function(){
            $btn.prop('disabled', false);
        });
    });
});

jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings') && !$('body').hasClass('post-type-mec-events')) return;

    var $card = $('.wns-be-container').first();
    var $openTip = null;
    // tooltips handled by this engine (settings page + event editor MEC metaboxes)
    var TIP = '.mec-ds-settings .mec-tooltip, .post-type-mec-events .mec-metabox .mec-tooltip';

    var place = function($tip){
        var $box = $tip.find('.box').first();
        if(!$box.length || !$tip.hasClass('mec-tooltip')) return;

        var icon = $tip[0].getBoundingClientRect();
        var box = $box[0];

        var bw = box.offsetWidth, bh = box.offsetHeight;
        if(!bw || !bh) return;

        var pad = 8, gap = 9;
        var cardRect = $card.length ? $card[0].getBoundingClientRect() : {left: 0, right: window.innerWidth};

        var up = (icon.bottom + gap + bh > window.innerHeight - pad) && (icon.top - gap - bh > pad);

        var minLeft = Math.max(cardRect.left + pad, pad);
        var maxRight = Math.min(cardRect.right - pad, window.innerWidth - pad);

        var left = icon.left - 10;
        if(left + bw > maxRight) left = maxRight - bw;
        if(left < minLeft) left = minLeft;

        var top = up ? (icon.top - gap - bh) : (icon.bottom + gap);

        box.style.left = left + 'px';
        box.style.top = top + 'px';

        var arrowLeft = (icon.left + icon.width / 2) - left - 5;
        arrowLeft = Math.max(6, Math.min(arrowLeft, bw - 16));

        $tip[0].style.setProperty('--mec-tip-arrow', arrowLeft + 'px');
        $tip.toggleClass('mec-tip-up', up);

        $openTip = $tip;
    };

    // aria-expanded mirrors what is shown: open while hovered or focused, unless Esc/Enter closed it.
    var syncTip = function($t){ $t.attr('aria-expanded', String(($t.is(':hover') || $t.is(':focus-within')) && !$t.hasClass('mec-tip-closed'))); };

    $(document).on('mouseenter focusin', TIP, function(){
        place($(this));
        syncTip($(this));
    });

    $(document).on('mouseleave focusout', TIP, function(e){
        var el = this, leave = function(){
            var $t = $(el);
            if(!$t.is(':hover') && !$t.is(':focus-within')){
                if($openTip && $openTip[0] === el) $openTip = null;
                $t.removeClass('mec-tip-closed');
            }
            syncTip($t);
        };
        if(e.type === 'focusout') setTimeout(leave, 0); else leave();
    });

    // Esc dismisses the open tooltip until both pointer and focus have left it.
    $(document).on('keydown', function(e){
        if(e.key === 'Escape') $(TIP).filter(':hover, :focus-within').addClass('mec-tip-closed').attr('aria-expanded', 'false');
    });

    // The focusable wrapper has role=button: Enter/Space toggle its tooltip
    // (it already opens on focus) instead of doing nothing or scrolling.
    $(document).on('keydown', '.mec-ds-settings .mec-tooltip[role="button"], .post-type-mec-events .mec-metabox .mec-tooltip[role="button"]', function(e){
        if(e.target !== this || (e.key !== 'Enter' && e.key !== ' ' && e.key !== 'Spacebar')) return;
        e.preventDefault();
        syncTip($(this).toggleClass('mec-tip-closed'));
    });

    $(window).on('scroll', function(){
        if($openTip) place($openTip);
    });
});

jQuery(document).ready(function($){
    if(!$('body').hasClass('mec-ds-settings')) return;

    var $copyLive = null;
    var mecCopy = function(el){
        // Readonly value inputs (e.g. Zapier API URLs/keys) carry the payload
        // in .value; code chips carry it in .textContent.
        var text = (el.tagName === 'INPUT') ? el.value : el.textContent;
        var l10n = (window.mec_admin_localize && window.mec_admin_localize.l10n) || {};

        // Tip + polite live region are shown only after the copy outcome is known.
        var notify = function(msg){
            var $tip = $('<span class="mec-copy-tip"></span>').text(msg);
            var rect = el.getBoundingClientRect();
            $tip.css({
                'position': 'fixed',
                'top': (rect.top - 30) + 'px',
                'left': (rect.left + (rect.width / 2) - 35) + 'px',
                'z-index': 100001
            });
            $('body').append($tip);
            setTimeout(function(){ $tip.remove(); }, 1200);

            // One polite live region; cleared first so a repeat copy is announced again.
            if(!$copyLive) $copyLive = $('<div class="mec-sr-only" aria-live="polite"></div>').appendTo('body');
            $copyLive.text('');
            setTimeout(function(){ $copyLive.text(msg); }, 50);
        };
        var done = function(){ notify(l10n.copied || 'Copied!'); };

        var fallback = function(){
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            var ok = false;
            try { ok = document.execCommand('copy', false, null); } catch(err) {}
            document.body.removeChild(ta);
            el.focus();
            if(ok) return done();

            // Copy blocked: select the value so the user can copy it manually.
            if(el.tagName === 'INPUT') el.select();
            else { var r = document.createRange(); r.selectNodeContents(el); var s = window.getSelection(); s.removeAllRanges(); s.addRange(r); }
            notify(l10n.press_ctrl_c || 'Press Ctrl+C');
        };
        if(navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(text).then(done, fallback);
        else fallback();
    };

    // QA11: every <code> in Settings copies. Keyboard access is added here instead of in the markup
    // (core + add-ons); placeholder lists stay click-only so they don't flood the Tab order.
    var noCopy = 'a, label, pre';
    $('.wns-be-main code').not('[tabindex], .mec-placeholders-list code').filter(function(){
        return !$(this).closest(noCopy).length;
    }).attr({tabindex: 0, role: 'button'});

    // QA17: name each copy button "Copy: <token>" (markup-provided chips included).
    var copyL10n = (window.mec_admin_localize && window.mec_admin_localize.l10n) || {};
    $('.wns-be-main code[role="button"]').not('[aria-label]').each(function(){
        var token = $.trim($(this).text());
        if(token) $(this).attr('aria-label', (copyL10n.copy_code || 'Copy: %s').split('%s').join(token));
    });

    $(document).on('click', '.wns-be-main code, .mec-info-card code, .mec-field__help code, .mec-api-key-code, input.mec-copy-on-click', function(e){
        if(this.tagName === 'CODE' && $(this).closest(noCopy).length) return;
        e.preventDefault();
        e.stopPropagation();
        mecCopy(this);
    });

    $(document).on('keydown', '.wns-be-main code[role="button"], .mec-api-key-code, input.mec-copy-on-click', function(e){
        // jQuery does not copy .repeat onto its event object; read it from originalEvent.
        if(e.ctrlKey || e.metaKey || e.altKey || (e.originalEvent && e.originalEvent.repeat)) return;
        if(e.key !== 'Enter' && e.key !== ' ' && e.key !== 'Spacebar') return;
        e.preventDefault();
        mecCopy(this);
    });
});

// 30.x - Placeholders card enhancer: upgrades every placeholder list to the
// searchable card UI. Handles both server-rendered cards (SMS tab etc.) and
// legacy <ul> lists (MEC-notifications tab sections, addons) detected by
// their %%token%% <span> children.
jQuery(document).ready(function($){
    if (!document.body.classList.contains('mec-ds-settings')) return;

    function mecBindPlaceholdersCard($card)
    {
        if ($card.data('mec-ph-ready')) return;
        $card.data('mec-ph-ready', true);

        var $list = $card.find('.mec-placeholders-list').first();
        if (!$list.length) return;

        var total = $list.children('li').length;

        var $toolbar = $card.find('.mec-placeholders-toolbar');
        if (!$toolbar.length)
        {
            var L10N = (window.mec_admin_localize && window.mec_admin_localize.l10n) || {};
            $toolbar = $('<div class="mec-placeholders-toolbar"><input type="search" class="mec-placeholders-search" placeholder="' + (L10N.search_placeholders || 'Search placeholders...') + '" aria-label="' + (L10N.search_placeholders || 'Search placeholders...') + '"><span class="mec-placeholders-count" title="' + (L10N.placeholders || 'Placeholders') + '"></span></div>');
            $list.before($toolbar);
        }

        var $input = $toolbar.find('.mec-placeholders-search');
        var $count = $toolbar.find('.mec-placeholders-count');
        $count.text(total);

        $input.on('input', function()
        {
            var q = String($(this).val() || '').toLowerCase();
            var shown = 0;

            $list.children('li').each(function()
            {
                var hit = !q || $(this).text().toLowerCase().indexOf(q) > -1;
                $(this).toggleClass('mec-ph-hidden', !hit);
                if (hit) shown++;
            });

            $count.text(shown + '/' + total);
        });
    }

    // Server-rendered cards.
    $('.mec-placeholders-card').each(function()
    {
        mecBindPlaceholdersCard($(this));
    });

    // Legacy lists: any <ul> with >= 3 <li><span>%%token%%</span> children.
    $('ul').filter(function()
    {
        var $ul = $(this);
        if ($ul.hasClass('mec-placeholders-list')) return false;
        if ($ul.closest('.mec-placeholders-card').length) return false;

        var $tokens = $ul.children('li').children('span').filter(function()
        {
            return $(this).text().indexOf('%%') === 0;
        });

        return $tokens.length >= 3;
    })
    .each(function()
    {
        var $ul = $(this);

        // Optional intro line ("You can use the following placeholders").
        var $help = $ul.prev('p.mec-field__help');

        $ul.addClass('mec-placeholders-list');
        $ul.wrap('<div class="mec-info-card mec-placeholders-card"><div class="ic-body mec-placeholders-wrap"></div></div>');

        var $card = $ul.closest('.mec-placeholders-card');
        $card.prepend('<span class="dashicons dashicons-editor-code" aria-hidden="true"></span>');
        if ($help.length) $help.insertBefore($ul);

        mecBindPlaceholdersCard($card);
    });

    // Click-to-copy for <span> tokens (code chips are handled above).
    $(document).on('click', '.mec-placeholders-list li > span', function(e)
    {
        e.preventDefault();
        var el = this;
        var text = el.textContent;

        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy', false, null); } catch(err) {}
        document.body.removeChild(ta);

        var $tip = $('<span class="mec-copy-tip"></span>').text((window.mec_admin_localize && window.mec_admin_localize.l10n && window.mec_admin_localize.l10n.copied) || 'Copied!');
        var rect = el.getBoundingClientRect();
        $tip.css({
            'position': 'fixed',
            'top': (rect.top - 30) + 'px',
            'left': (rect.left + (rect.width / 2) - 35) + 'px',
            'z-index': 100001
        });
        $('body').append($tip);
        setTimeout(function(){ $tip.remove(); }, 1200);
    });
});

/* Event metaboxes: give every tab panel the same .mec-panel__head wrapper,
   including panels rendered by addons that only output a bare heading. */
jQuery(document).ready(function ($) {
    $('.postbox.mec-metabox [class*="-tab-content"], .postbox.mec-metabox #mec-virtual, .postbox.mec-metabox #mec-webex, .postbox.mec-metabox #mec-zoom, .postbox.mec-metabox .mec-meta-box-fields').each(function () {
        var $panel = $(this);
        if ($panel.children('.mec-panel__head').length) return;
        var $first = $panel.children().filter(function () {
            return this.tagName !== 'INPUT' && this.tagName !== 'SCRIPT' && this.tagName !== 'STYLE';
        }).first();
        if (!$first.length || !$first.is('h2, h3, h4')) return;
        if ($first.closest('.mec-panel__head').length) return;
        $first.addClass('mec-panel__title').wrap('<div class="mec-panel__head"></div>');
    });
});
