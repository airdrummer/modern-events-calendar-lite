<?php
/** no direct access **/
defined('MECEXEC') or die();

$event_task = $tasks['create_event'] ?? [];
?>
<p class="mec-ai-new-event-action"><button type="button" class="button mec-ai-mode-button" data-mec-ai-modal="mec-ai-event-modal"><span class="mec-ai-magic-logo" aria-hidden="true"></span><span><?php esc_html_e('AI Mode', 'modern-events-calendar-lite'); ?></span><svg class="mec-ai-magic-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9.5 3.5 11 8l4.5 1.5L11 11l-1.5 4.5L8 11 3.5 9.5 8 8z"/><path d="M17.5 12l.9 2.6 2.6.9-2.6.9-.9 2.6-.9-2.6-2.6-.9 2.6-.9z"/></svg></button></p>
<div id="mec-ai-event-modal" class="mec-ai-event-modal" title="<?php esc_attr_e('Create Event with AI', 'modern-events-calendar-lite'); ?>">
    <div class="mec-ai-runner mec-ai-event-runner mec-ai-compact" data-redirect-on-create="1">
        <?php if (!$status['requirements_met']): ?>
            <?php include MEC::import('app.features.mec.ai_setup_guide', true, true); ?>
        <?php else: ?>
            <div class="mec-ai-task-form" role="form">
                <input type="hidden" name="mec_ai_task[type]" value="create_event" data-status="<?php echo esc_attr($event_task['status'] ?? 'generate_preview'); ?>" data-action-label="<?php echo esc_attr($event_task['action_label'] ?? __('Generate event preview', 'modern-events-calendar-lite')); ?>" data-description="<?php echo esc_attr($event_task['description'] ?? ''); ?>">
                <?php $ai_context = 'modal'; $event_only = true; include MEC::import('app.features.mec.ai_event_task', true, true); ?>
                <div class="mec-ai-submit-row mec-ai-submit-compact">
                    <div><strong class="mec-ai-submit-title"><?php esc_html_e('Ready to generate an event preview?', 'modern-events-calendar-lite'); ?></strong><span class="mec-ai-submit-copy"><?php esc_html_e('MEC will create a structured event preview from your brief. Nothing is saved until you confirm the draft.', 'modern-events-calendar-lite'); ?></span></div>
                    <button type="button" class="button button-primary mec-ai-submit mec-ai-generate-preview"><?php esc_html_e('Generate event preview', 'modern-events-calendar-lite'); ?> <i class="mec-sl-arrow-right" aria-hidden="true"></i></button>
                </div>
                <div class="mec-ai-request-notice" role="status" aria-live="polite"></div>
            </div>
            <div class="mec-ai-category-result" role="status" aria-live="polite"></div>
            <?php include MEC::import('app.features.mec.ai_event_preview', true, true); ?>
        <?php endif; ?>
    </div>
</div>
