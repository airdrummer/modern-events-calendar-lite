<?php
/** no direct access **/
defined('MECEXEC') or die();

$ai_context = isset($ai_context) ? sanitize_key($ai_context) : 'page';
$event_only = !empty($event_only);
$event_prompt_id = 'mec_ai_' . $ai_context . '_event_prompt';
$compact = ($ai_context === 'modal');
?>
<section class="mec-ai-step mec-ai-task-panel" data-task="create_event"<?php echo $event_only ? '' : ' hidden'; ?>>
    <?php if (!$compact): ?>
    <div class="mec-ai-step-head">
        <span class="mec-ai-eyebrow"><span class="mec-ai-step-num">2</span><?php esc_html_e('Describe the event', 'modern-events-calendar-lite'); ?></span>
        <h2><?php esc_html_e('Event prompt', 'modern-events-calendar-lite'); ?></h2>
        <p><?php esc_html_e('Describe one event in your own words. MEC will create a structured preview before it saves an editable draft.', 'modern-events-calendar-lite'); ?></p>
    </div>
    <?php endif; ?>
    <div class="mec-ai-prompt-row">
        <label for="<?php echo esc_attr($event_prompt_id); ?>"><?php esc_html_e('Event prompt', 'modern-events-calendar-lite'); ?><span class="mec-ai-required">*</span></label>
        <div class="mec-ai-prompt-content">
            <textarea id="<?php echo esc_attr($event_prompt_id); ?>" class="mec-ai-event-prompt" name="mec_ai_task[prompt]" rows="<?php echo $compact ? 5 : 11; ?>" required placeholder="<?php esc_attr_e('For example: Generate an event for a math class in Coquitlam on August 18, 2026, repeating every two weeks. Include three ticket types and a friendly, professional description.', 'modern-events-calendar-lite'); ?>"></textarea>
            <<?php echo $compact ? 'details' : 'div'; ?> class="mec-ai-prompt-help">
                <<?php echo $compact ? 'summary' : 'strong'; ?>><?php esc_html_e('A useful event brief includes:', 'modern-events-calendar-lite'); ?></<?php echo $compact ? 'summary' : 'strong'; ?>>
                <ul>
                    <li><?php esc_html_e('event title, purpose, audience, description, and preferred writing tone', 'modern-events-calendar-lite'); ?></li>
                    <li><?php esc_html_e('venue, organizer, date, start and end time, and whether it repeats', 'modern-events-calendar-lite'); ?></li>
                    <li><?php esc_html_e('existing or new categories, labels, tags, location, and organizer details', 'modern-events-calendar-lite'); ?></li>
                    <li><?php esc_html_e('hourly agenda items and ticket names, prices, and capacity limits', 'modern-events-calendar-lite'); ?></li>
                    <li><?php esc_html_e('say “do not generate an image” when you do not want a featured image', 'modern-events-calendar-lite'); ?></li>
                </ul>
            </<?php echo $compact ? 'details' : 'div'; ?>>
        </div>
    </div>
</section>
