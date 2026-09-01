<?php
/** no direct access **/
defined('MECEXEC') or die();
?>
<div class="mec-shortcode-box">
    <div class="mec-shortcode-box__row">
        <button type="button" class="mec-shortcode mec-shortcode-token" id="MECCopyCode" onclick="mec_copy_code()" title="<?php echo esc_attr__('Click to copy shortcode', 'modern-events-calendar-lite'); ?>" aria-label="<?php echo esc_attr(sprintf(__('Copy the shortcode %s to the clipboard', 'modern-events-calendar-lite'), '[MEC id="' . $post->ID . '"]')); ?>">[MEC id="<?php echo esc_html($post->ID); ?>"]</button>
        <span class="mec-copied" aria-hidden="true"><?php esc_html_e('Copied!', 'modern-events-calendar-lite'); ?></span>
    </div>
    <p class="mec-shortcode-howto">
        <?php echo '<strong>' . esc_html__('How to use:', 'modern-events-calendar-lite') . '</strong> ' . esc_html__('Paste this shortcode into any page, post or text widget, or add the MEC block/widget of your page builder. It renders the calendar you configure here.', 'modern-events-calendar-lite'); ?>
    </p>
</div>
