<?php
/** no direct access **/
defined('MECEXEC') or die();

$tasks = $this->get_ai_tasks();
$status = $this->get_ai_task_status();
$icons = $this->get_ai_category_icons();
?>
<div class="wrap mec-ai-page">
    <?php if (!$status['requirements_met']): ?>
        <section class="w-box mec-ai-card mec-ai-setup-guide">
            <span class="mec-ai-step"><?php esc_html_e('SETUP REQUIRED', 'modern-events-calendar-lite'); ?></span>
            <?php if ($status['ai_plugin_available'] && !$status['ai_plugin_features_enabled']): ?>
                <h2><?php esc_html_e('Enable AI in WordPress first', 'modern-events-calendar-lite'); ?></h2>
                <p><?php esc_html_e('The WordPress AI plugin is installed, but its site-wide Enable AI setting is off. Enable it before using MEC AI tasks.', 'modern-events-calendar-lite'); ?></p>
            <?php elseif ($status['approval_required']): ?>
                <h2><?php esc_html_e('Approve MEC to use your AI provider', 'modern-events-calendar-lite'); ?></h2>
                <p><?php esc_html_e('A configured provider is available, but WordPress Connector Approval is blocking MEC from using it. Grant Modern Events Calendar access to the connector before continuing.', 'modern-events-calendar-lite'); ?></p>
            <?php else: ?>
                <h2><?php esc_html_e('Set up a text AI provider first', 'modern-events-calendar-lite'); ?></h2>
                <p><?php esc_html_e('WordPress 7 includes the AI Client API, but it does not include an AI connection or credentials. MEC needs a configured text-generation provider before it can generate category suggestions.', 'modern-events-calendar-lite'); ?></p>
            <?php endif; ?>
            <ol class="mec-ai-setup-steps">
                <?php if ($status['ai_plugin_available'] && !$status['ai_plugin_features_enabled']): ?>
                    <li><span>1</span><div><strong><?php esc_html_e('Open WordPress AI settings', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('Go to Settings → AI in your WordPress admin.', 'modern-events-calendar-lite'); ?></small></div></li>
                    <li><span>2</span><div><strong><?php esc_html_e('Turn on Enable AI', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('Use the site-wide switch at the top of the page to allow WordPress AI features.', 'modern-events-calendar-lite'); ?></small></div></li>
                    <li><span>3</span><div><strong><?php esc_html_e('Return to MEC AI', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('The Category Creation task will be available when the other AI requirements are ready.', 'modern-events-calendar-lite'); ?></small></div></li>
                <?php elseif ($status['approval_required']): ?>
                    <li><span>1</span><div><strong><?php esc_html_e('Review Connector Approvals', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('WordPress has recorded MEC’s request to use an AI connector.', 'modern-events-calendar-lite'); ?></small></div></li>
                    <li><span>2</span><div><strong><?php esc_html_e('Grant Modern Events Calendar access', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('Approve MEC for the connector you want it to use. This permission is managed by WordPress.', 'modern-events-calendar-lite'); ?></small></div></li>
                    <li><span>3</span><div><strong><?php esc_html_e('Return to MEC AI', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('After approval, Category Creation will be ready here.', 'modern-events-calendar-lite'); ?></small></div></li>
                <?php else: ?>
                    <li><span>1</span><div><strong><?php esc_html_e('Open WordPress Connectors', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('Install a provider connector that supports text generation, such as OpenAI, Anthropic, or Google.', 'modern-events-calendar-lite'); ?></small></div></li>
                    <li><span>2</span><div><strong><?php esc_html_e('Connect and configure the provider', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('Add its credentials and choose a text-generation model. Credentials are managed by WordPress, not MEC.', 'modern-events-calendar-lite'); ?></small></div></li>
                    <li><span>3</span><div><strong><?php esc_html_e('Return to MEC AI', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('Once WordPress reports text generation as available, Category Creation will appear here.', 'modern-events-calendar-lite'); ?></small></div></li>
                <?php endif; ?>
            </ol>
            <div class="mec-ai-setup-actions">
                <?php if ($status['ai_plugin_available'] && !$status['ai_plugin_features_enabled']): ?>
                    <a class="button button-primary mec-ai-submit" href="<?php echo esc_url($status['ai_plugin_url']); ?>"><?php esc_html_e('Open WordPress AI settings', 'modern-events-calendar-lite'); ?> <span aria-hidden="true">↗</span></a>
                <?php else: ?>
                    <a class="button button-primary mec-ai-submit" href="<?php echo esc_url($status['approval_required'] ? $status['approval_url'] : $status['connectors_url']); ?>"><?php echo esc_html($status['approval_required'] ? __('Review Connector Approvals', 'modern-events-calendar-lite') : __('Open WordPress Connectors', 'modern-events-calendar-lite')); ?> <span aria-hidden="true">↗</span></a>
                <?php endif; ?>
                <span><?php esc_html_e('MEC uses the AI configuration managed by WordPress. It never asks for an API key.', 'modern-events-calendar-lite'); ?></span>
            </div>
        </section>
    <?php else: ?>
        <div class="mec-ai-layout">
            <main class="w-box mec-ai-card mec-ai-task-card">
                <div class="mec-ai-card-heading">
                    <div>
                        <span class="mec-ai-step">01</span>
                        <h2><?php esc_html_e('Choose a task', 'modern-events-calendar-lite'); ?></h2>
                    </div>
                    <span class="mec-ai-phase-badge"><?php esc_html_e('AI-powered', 'modern-events-calendar-lite'); ?></span>
                </div>

                <form id="mec-ai-task-form" novalidate>
                    <div class="mec-form-row mec-ai-form-row">
                        <label for="mec_ai_task_type" class="mec-col-3"><?php esc_html_e('Task type', 'modern-events-calendar-lite'); ?></label>
                        <div class="mec-col-9">
                            <select id="mec_ai_task_type" name="mec_ai_task[type]">
                                <?php foreach ($tasks as $task_id => $task): ?>
                                    <option value="<?php echo esc_attr($task_id); ?>" data-description="<?php echo esc_attr($task['description'] ?? ''); ?>" data-status="<?php echo esc_attr($task['status'] ?? 'coming_soon'); ?>" data-action-label="<?php echo esc_attr($task['action_label'] ?? __('Coming soon', 'modern-events-calendar-lite')); ?>" <?php selected($task_id, 'create_category'); ?>><?php echo esc_html($task['label'] ?? $task_id); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p id="mec-ai-task-description" class="description mec-ai-task-description"><?php echo esc_html($tasks['create_category']['description'] ?? ''); ?></p>
                        </div>
                    </div>

                    <?php if (isset($tasks['create_category'])): ?>
                        <section class="mec-ai-task-panel" data-task="create_category">
                            <div class="mec-ai-section-heading">
                                <span class="mec-ai-step">02</span>
                                <div>
                                    <h2><?php esc_html_e('Describe the categories', 'modern-events-calendar-lite'); ?></h2>
                                    <p><?php esc_html_e('Ask for one or many MEC categories. You will review and edit every suggestion before anything is created.', 'modern-events-calendar-lite'); ?></p>
                                </div>
                            </div>
                            <div class="mec-form-row mec-ai-form-row mec-ai-prompt-row">
                                <label for="mec_ai_category_prompt"><?php esc_html_e('Category prompt', 'modern-events-calendar-lite'); ?><span class="mec-ai-required">*</span></label>
                                <div class="mec-ai-prompt-content">
                                    <textarea id="mec_ai_category_prompt" name="mec_ai_task[prompt]" rows="11" required placeholder="<?php esc_attr_e('For example: Create categories for a community arts centre with classes, workshops, performances, family activities, and seasonal events.', 'modern-events-calendar-lite'); ?>"></textarea>
                                    <div class="mec-ai-prompt-help">
                                        <strong><?php esc_html_e('A useful category brief includes:', 'modern-events-calendar-lite'); ?></strong>
                                        <ul>
                                            <li><?php esc_html_e('the kinds of events or audiences the categories should cover', 'modern-events-calendar-lite'); ?></li>
                                            <li><?php esc_html_e('how many categories you need, or examples you want included', 'modern-events-calendar-lite'); ?></li>
                                            <li><?php esc_html_e('the tone or level of detail for category descriptions', 'modern-events-calendar-lite'); ?></li>
                                            <li><?php esc_html_e('any naming conventions or existing categories MEC should avoid duplicating', 'modern-events-calendar-lite'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php if (isset($tasks['create_event'])): ?>
                        <section class="mec-ai-task-panel mec-ai-coming-soon" data-task="create_event" hidden>
                            <div class="mec-ai-section-heading">
                                <span class="mec-ai-step">02</span>
                                <div>
                                    <h2><?php esc_html_e('Event Creation is coming soon', 'modern-events-calendar-lite'); ?></h2>
                                    <p><?php esc_html_e('Category Creation is establishing the reviewed AI workflow first. Event Creation will return with the same safe preview-and-confirm experience.', 'modern-events-calendar-lite'); ?></p>
                                </div>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php do_action('mec_ai_task_fields', $tasks, $status); ?>

                    <div class="mec-ai-submit-row">
                        <div><strong id="mec-ai-submit-title"><?php esc_html_e('Ready to generate a category preview?', 'modern-events-calendar-lite'); ?></strong><span id="mec-ai-submit-copy"><?php esc_html_e('MEC will send your brief and existing category context to the WordPress AI provider. No categories are created yet.', 'modern-events-calendar-lite'); ?></span></div>
                        <button type="submit" class="button button-primary mec-ai-submit"><?php esc_html_e('Generate category preview', 'modern-events-calendar-lite'); ?> <span aria-hidden="true">→</span></button>
                    </div>
                    <div id="mec-ai-request-notice" class="mec-ai-request-notice" role="status" aria-live="polite"></div>
                </form>
                <div id="mec-ai-category-result" class="mec-ai-category-result" role="status" aria-live="polite"></div>

                <section id="mec-ai-category-preview" class="mec-ai-category-preview" hidden>
                    <div class="mec-ai-section-heading">
                        <span class="mec-ai-step">03</span>
                        <div>
                            <h2><?php esc_html_e('Review category suggestions', 'modern-events-calendar-lite'); ?></h2>
                            <p><?php esc_html_e('Review the generated details, use the MEC icon picker or color picker, deselect anything you do not want, then confirm creation.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                    <div id="mec-ai-category-preview-list" class="mec-ai-category-preview-list"></div>
                    <div class="mec-ai-submit-row mec-ai-apply-row">
                        <div><strong><?php esc_html_e('Create selected categories', 'modern-events-calendar-lite'); ?></strong><span><?php esc_html_e('Existing category names are skipped and never changed.', 'modern-events-calendar-lite'); ?></span></div>
                        <button type="button" id="mec-ai-create-categories" class="button button-primary mec-ai-submit"><?php esc_html_e('Create selected categories', 'modern-events-calendar-lite'); ?> <span aria-hidden="true">→</span></button>
                    </div>
                </section>
            </main>

            <aside class="w-box mec-ai-card mec-ai-status-card">
                <div class="mec-ai-card-heading"><div><span class="mec-ai-step">STATUS</span><h2><?php esc_html_e('AI readiness', 'modern-events-calendar-lite'); ?></h2></div></div>
                <ul class="mec-ai-status-list">
                    <li class="<?php echo ($status['core_available'] ? 'is-ready' : 'is-warning'); ?>"><span></span><div><strong><?php esc_html_e('WordPress AI capability', 'modern-events-calendar-lite'); ?></strong><small><?php echo esc_html($status['core_available'] ? __('Built into WordPress', 'modern-events-calendar-lite') : __('Disabled or unavailable', 'modern-events-calendar-lite')); ?></small></div></li>
                    <li class="<?php echo ($status['text_available'] ? 'is-ready' : 'is-warning'); ?>"><span></span><div><strong><?php esc_html_e('Text generation', 'modern-events-calendar-lite'); ?></strong><small><?php echo esc_html($status['text_available'] ? __('A provider is ready', 'modern-events-calendar-lite') : __('Configure a provider to continue', 'modern-events-calendar-lite')); ?></small></div></li>
                    <li class="<?php echo ($status['image_available'] ? 'is-ready' : 'is-muted'); ?>"><span></span><div><strong><?php esc_html_e('Image generation', 'modern-events-calendar-lite'); ?></strong><small><?php echo esc_html($status['image_available'] ? __('A provider is ready', 'modern-events-calendar-lite') : __('Optional, not configured', 'modern-events-calendar-lite')); ?></small></div></li>
                    <?php if ($status['ai_plugin_available']): ?>
                        <li class="is-ready"><span></span><div><strong><?php esc_html_e('WordPress AI plugin features', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('Enabled for MEC AI', 'modern-events-calendar-lite'); ?></small></div></li>
                    <?php endif; ?>
                </ul>
                <a class="mec-ai-connectors-link" href="<?php echo esc_url($status['connectors_url']); ?>"><?php esc_html_e('Open WordPress Connectors', 'modern-events-calendar-lite'); ?> <span aria-hidden="true">↗</span></a>
            </aside>
        </div>

        <script type="text/html" id="mec-ai-icon-picker-template">
            <div class="mec-ai-icon-picker" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Choose a category icon', 'modern-events-calendar-lite'); ?>">
                <div class="mec-ai-icon-picker-dialog">
                    <div class="mec-ai-icon-picker-heading"><strong><?php esc_html_e('Choose an MEC icon', 'modern-events-calendar-lite'); ?></strong><button type="button" class="button-link mec-ai-close-icon-picker" aria-label="<?php esc_attr_e('Close icon picker', 'modern-events-calendar-lite'); ?>">×</button></div>
                    <input type="search" class="mec-ai-icon-search" placeholder="<?php esc_attr_e('Search icon class', 'modern-events-calendar-lite'); ?>">
                    <div class="mec-ai-icon-grid">
                        <?php foreach ($icons as $icon): ?>
                            <button type="button" class="mec-ai-icon-choice" data-icon="<?php echo esc_attr($icon); ?>" title="<?php echo esc_attr($icon); ?>"><i class="<?php echo esc_attr($icon); ?>"></i><span class="screen-reader-text"><?php echo esc_html($icon); ?></span></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </script>
    <?php endif; ?>
</div>
