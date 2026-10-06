<?php

/** no direct access **/
defined('MECEXEC') or die();

/**
 * IA-01 (revision 12): the getting-started walkthrough. The FIRST content
 * block on the page, above the license box, for every install state.
 *
 * C-05: no wall of text - one line, and the setup wizard does the heavy
 * lifting instead of a paragraph describing it.
 *
 * P-02: no YouTube iframe on load. A lightweight thumbnail facade loads the
 * player only on click (youtube-nocookie, GDPR-friendlier than the embed).
 */
?>
<div class="mec-intro-grid mec-intro-grid--2">
    <div class="mec-intro-card mec-intro-video">
        <button type="button" class="mec-video-facade" data-video-id="P0c2G1qhusk" aria-label="<?php echo esc_attr__('Play the getting-started walkthrough', 'modern-events-calendar-lite'); ?>">
            <img src="https://i.ytimg.com/vi/P0c2G1qhusk/hqdefault.jpg" alt="" loading="lazy" />
            <span class="mec-video-facade__play" aria-hidden="true"></span>
        </button>
    </div>
    <div class="mec-intro-card mec-intro-card--pad">
        <h2><?php esc_html_e('Getting started with Modern Events Calendar', 'modern-events-calendar-lite'); ?></h2>
        <p><?php esc_html_e('New to MEC? Watch the quick walkthrough, then let the setup wizard do the heavy lifting.', 'modern-events-calendar-lite'); ?></p>
        <div class="mec-intro-cta">
            <a class="mec-intro-btn" href="<?php echo esc_url(admin_url('admin.php?page=MEC-wizard')); ?>"><?php esc_html_e('Run Setup Wizard', 'modern-events-calendar-lite'); ?></a>
            <a class="mec-intro-btn mec-intro-btn--ghost" href="<?php echo esc_url(admin_url('post-new.php?post_type=mec-events')); ?>"><?php esc_html_e('Add New Event', 'modern-events-calendar-lite'); ?></a>
            <a class="mec-intro-btn mec-intro-btn--ghost" href="https://webnus.net/dox/modern-events-calendar/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Documentation', 'modern-events-calendar-lite'); ?></a>
        </div>
    </div>
</div>
<script>
    (function () {
        // P-02: swap the facade for the real player, but only when asked.
        document.querySelectorAll('.mec-video-facade').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-video-id') || '';
                if (!id) return;

                var iframe = document.createElement('iframe');
                iframe.width = '560';
                iframe.height = '315';
                iframe.src = 'https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1&rel=0';
                iframe.title = <?php echo wp_json_encode(esc_html__('Getting started with Modern Events Calendar', 'modern-events-calendar-lite')); ?>;
                iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
                iframe.referrerPolicy = 'strict-origin-when-cross-origin';
                iframe.allowFullscreen = true;

                btn.replaceWith(iframe);
            });
        });
    })();
</script>
