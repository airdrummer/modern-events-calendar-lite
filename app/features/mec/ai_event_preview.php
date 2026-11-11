<?php
/** no direct access **/
defined('MECEXEC') or die();
?>
<section class="mec-ai-step mec-ai-event-preview" hidden>
    <div class="mec-ai-step-head">
        <span class="mec-ai-eyebrow"><span class="mec-ai-step-num">3</span><?php esc_html_e('Review event preview', 'modern-events-calendar-lite'); ?></span>
        <h2><?php esc_html_e('Review event preview', 'modern-events-calendar-lite'); ?></h2>
        <p><?php esc_html_e('Fine-tune key details below, then confirm to create an editable draft. Nothing is published yet.', 'modern-events-calendar-lite'); ?></p>
    </div>
    <div class="mec-ai-event-preview-content"></div>
    <div class="mec-ai-submit-row mec-ai-apply-row">
        <div><strong><?php esc_html_e('Create event draft', 'modern-events-calendar-lite'); ?></strong><span><?php esc_html_e('MEC will save this event as a draft and attach the previewed image when one is available.', 'modern-events-calendar-lite'); ?></span></div>
        <button type="button" class="button button-primary mec-ai-submit mec-ai-create-event"><?php esc_html_e('Create event draft', 'modern-events-calendar-lite'); ?> <i class="mec-sl-arrow-right" aria-hidden="true"></i></button>
    </div>
</section>
