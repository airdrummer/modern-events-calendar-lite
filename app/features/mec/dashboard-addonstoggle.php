<?php

/** no direct access **/
defined('MECEXEC') or die();

/**
 * I-07 / Q-04: the "Activate Addons" disclosure. Shared partial so every
 * box that renders it (network subsite box, the license box, the Lite
 * addons box) uses one source. It is a real <button> with aria-expanded;
 * backend.js toggles the content and the plus/minus icon.
 */
?>
<div class="box-addons-activation">
    <button type="button" class="box-addon-activation-toggle-head" aria-expanded="false">
        <i class="mec-sl-plus" aria-hidden="true"></i><span><?php esc_html_e('Activate Addons', 'modern-events-calendar-lite'); ?></span>
    </button>
    <div class="box-addon-activation-toggle-content">
        <?php do_action('addons_activation'); ?>
    </div>
</div>
