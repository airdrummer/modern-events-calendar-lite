<?php
/** no direct access **/
defined('MECEXEC') or die();

if ($status['ai_plugin_available'] && !$status['ai_plugin_features_enabled'])
{
    $setup_title = esc_html__('Enable AI in WordPress first', 'modern-events-calendar-lite');
    $setup_text = esc_html__('The WordPress AI plugin is installed, but its site-wide Enable AI setting is off. Enable it before using MEC AI tasks.', 'modern-events-calendar-lite');
    $setup_url = $status['ai_plugin_url'];
    $setup_action = esc_html__('Open WordPress AI settings', 'modern-events-calendar-lite');
}
elseif ($status['approval_required'])
{
    $setup_title = esc_html__('Approve MEC to use your AI provider', 'modern-events-calendar-lite');
    $setup_text = esc_html__('A configured provider is available, but WordPress Connector Approval is blocking MEC from using it. Grant Modern Events Calendar access to the connector before continuing.', 'modern-events-calendar-lite');
    $setup_url = $status['approval_url'];
    $setup_action = esc_html__('Review Connector Approvals', 'modern-events-calendar-lite');
}
else
{
    $setup_title = esc_html__('Set up a text AI provider first', 'modern-events-calendar-lite');
    $setup_text = esc_html__('WordPress includes the AI Client API, but it does not include an AI connection or credentials. MEC needs a configured text-generation provider before it can generate suggestions.', 'modern-events-calendar-lite');
    $setup_url = $status['connectors_url'];
    $setup_action = esc_html__('Open WordPress Connectors', 'modern-events-calendar-lite');
}
?>
<section class="mec-ai-setup-compact" role="status">
    <span class="mec-ai-setup-mark" aria-hidden="true"><i class="mec-sl-info"></i></span>
    <div class="mec-ai-setup-text">
        <strong><?php echo esc_html($setup_title); ?></strong>
        <p><?php echo esc_html($setup_text); ?></p>
        <small><i class="mec-sl-shield" aria-hidden="true"></i><?php esc_html_e('MEC uses the AI configuration managed by WordPress. It never asks for an API key.', 'modern-events-calendar-lite'); ?></small>
    </div>
    <a class="button button-primary mec-ai-submit" href="<?php echo esc_url($setup_url); ?>"><?php echo esc_html($setup_action); ?> <i class="mec-sl-arrow-right" aria-hidden="true"></i></a>
</section>
