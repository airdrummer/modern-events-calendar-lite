<?php
/** no direct access **/
defined('MECEXEC') or die();

$tasks = $this->get_ai_tasks();
$status = $this->get_ai_task_status();
$icons = $this->get_ai_category_icons();
$default_task = array_key_exists('create_category', $tasks) ? 'create_category' : (string) array_key_first($tasks);

$styling = $this->main->get_styling();
$darkadmin_mode = $styling['dark_mode'] ?? '';
$mec_logo_src = plugin_dir_url(__FILE__) . '../../../assets/img/' . ($darkadmin_mode == 1 ? 'mec-logo-w2.png' : 'mec-logo-w.png');
$mec_plugin_basename = plugin_basename(MEC_ABSPATH . 'mec.php');
$mec_updates_transient = get_site_transient('update_plugins');
$mec_update_available = is_object($mec_updates_transient) && isset($mec_updates_transient->response[$mec_plugin_basename]);

$ai_lead = $status['requirements_met']
    ? esc_html__('Create events and categories from a short brief, review an AI-generated preview, then save an editable draft. Powered by your WordPress AI providers.', 'modern-events-calendar-lite')
    : esc_html__('Connect a WordPress AI provider to create events and categories from a short brief, review AI-generated previews, and save editable drafts.', 'modern-events-calendar-lite');
?>
<div id="webnus-dashboard" class="wrap about-wrap mec-intro-page mec-ai-page">
    <div class="mec-intro-brandhead">
        <div class="mec-page-head">
            <span class="mec-page-eyebrow"><?php esc_html_e('Modern Events Calendar', 'modern-events-calendar-lite'); ?></span>
            <h1><span class="mec-page-mark" aria-hidden="true"><i class="mec-sl-magic-wand"></i></span><?php esc_html_e('MEC AI', 'modern-events-calendar-lite'); ?></h1>
            <p class="mec-page-lead"><?php echo $ai_lead; ?></p>
        </div>
        <div class="mec-intro-brandbox">
            <img class="mec-intro-logo" src="<?php echo esc_url($mec_logo_src); ?>" alt="<?php echo esc_attr__('Modern Events Calendar', 'modern-events-calendar-lite'); ?>" />
            <span class="mec-intro-ver<?php echo $mec_update_available ? ' mec-intro-ver--old' : ''; ?>">
                <i aria-hidden="true"></i>
                <?php echo esc_html($this->isProBuild() ? 'Pro' : 'Lite'); ?> · v<?php echo esc_html(MEC_VERSION); ?>
                <?php if ($mec_update_available): ?>
                    · <a href="<?php echo esc_url(admin_url('update-core.php')); ?>"><?php esc_html_e('Update available', 'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
            </span>
        </div>
    </div>
    <hr class="wp-header-end">
    <div class="welcome-content w-clearfix extra">
    <?php if (!$status['requirements_met']):
        if ($status['ai_plugin_available'] && !$status['ai_plugin_features_enabled']):
            $setup_title = esc_html__('Enable AI in WordPress first', 'modern-events-calendar-lite');
            $setup_text = esc_html__('The WordPress AI plugin is installed, but its site-wide Enable AI setting is off. Enable it before using MEC AI tasks.', 'modern-events-calendar-lite');
        elseif ($status['approval_required']):
            $setup_title = esc_html__('Approve MEC to use your AI provider', 'modern-events-calendar-lite');
            $setup_text = esc_html__('A configured provider is available, but WordPress Connector Approval is blocking MEC from using it. Grant Modern Events Calendar access to the connector before continuing.', 'modern-events-calendar-lite');
        else:
            $setup_title = esc_html__('Set up a text AI provider first', 'modern-events-calendar-lite');
            $setup_text = esc_html__('WordPress includes the AI Client API, but it does not include an AI connection or credentials. MEC needs a configured text-generation provider before it can generate category suggestions.', 'modern-events-calendar-lite');
        endif;
    ?>
        <section class="w-box mec-intro-card mec-ai-card mec-ai-setup-guide">
            <div class="mec-ai-card-head">
                <h2><?php echo $setup_title; ?></h2>
                <span class="mec-ai-eyebrow"><i class="mec-sl-info" aria-hidden="true"></i><?php esc_html_e('Setup required', 'modern-events-calendar-lite'); ?></span>
            </div>
            <div class="mec-ai-card-body">
                <p class="mec-ai-setup-lead"><?php echo $setup_text; ?></p>
                <ol class="mec-ai-setup-steps">
                    <?php if ($status['ai_plugin_available'] && !$status['ai_plugin_features_enabled']): ?>
                        <li><span>1</span><div><strong><?php esc_html_e('Open WordPress AI settings', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('Go to Settings → AI in your WordPress admin.', 'modern-events-calendar-lite'); ?></small></div></li>
                        <li><span>2</span><div><strong><?php esc_html_e('Turn on Enable AI', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('Use the site-wide switch at the top of the page to allow WordPress AI features.', 'modern-events-calendar-lite'); ?></small></div></li>
                        <li><span>3</span><div><strong><?php esc_html_e('Return to MEC AI', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('The AI tasks will be available when the other AI requirements are ready.', 'modern-events-calendar-lite'); ?></small></div></li>
                    <?php elseif ($status['approval_required']): ?>
                        <li><span>1</span><div><strong><?php esc_html_e('Review Connector Approvals', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('WordPress has recorded MEC’s request to use an AI connector.', 'modern-events-calendar-lite'); ?></small></div></li>
                        <li><span>2</span><div><strong><?php esc_html_e('Grant Modern Events Calendar access', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('Approve MEC for the connector you want it to use. This permission is managed by WordPress.', 'modern-events-calendar-lite'); ?></small></div></li>
                        <li><span>3</span><div><strong><?php esc_html_e('Return to MEC AI', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('After approval, the AI tasks will be ready here.', 'modern-events-calendar-lite'); ?></small></div></li>
                    <?php else: ?>
                        <li><span>1</span><div><strong><?php esc_html_e('Open WordPress Connectors', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('Install a provider connector that supports text generation, such as OpenAI, Anthropic, or Google.', 'modern-events-calendar-lite'); ?></small></div></li>
                        <li><span>2</span><div><strong><?php esc_html_e('Connect and configure the provider', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('Add its credentials and choose a text-generation model. Credentials are managed by WordPress, not MEC.', 'modern-events-calendar-lite'); ?></small></div></li>
                        <li><span>3</span><div><strong><?php esc_html_e('Return to MEC AI', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('Once WordPress reports text generation as available, the AI tasks will appear here.', 'modern-events-calendar-lite'); ?></small></div></li>
                    <?php endif; ?>
                </ol>
                <div class="mec-ai-setup-actions">
                    <?php if ($status['ai_plugin_available'] && !$status['ai_plugin_features_enabled']): ?>
                        <a class="button button-primary mec-ai-submit" href="<?php echo esc_url($status['ai_plugin_url']); ?>"><?php esc_html_e('Open WordPress AI settings', 'modern-events-calendar-lite'); ?> <i class="mec-sl-arrow-right" aria-hidden="true"></i></a>
                    <?php else: ?>
                        <a class="button button-primary mec-ai-submit" href="<?php echo esc_url($status['approval_required'] ? $status['approval_url'] : $status['connectors_url']); ?>"><?php echo esc_html($status['approval_required'] ? __('Review Connector Approvals', 'modern-events-calendar-lite') : __('Open WordPress Connectors', 'modern-events-calendar-lite')); ?> <i class="mec-sl-arrow-right" aria-hidden="true"></i></a>
                    <?php endif; ?>
                    <span class="mec-ai-setup-note"><i class="mec-sl-shield" aria-hidden="true"></i><?php esc_html_e('MEC uses the AI configuration managed by WordPress. It never asks for an API key.', 'modern-events-calendar-lite'); ?></span>
                </div>
            </div>
        </section>
    <?php else: ?>
        <div class="mec-ai-layout">
            <main class="w-box mec-intro-card mec-ai-card mec-ai-task-card">
                <div class="mec-ai-card-head">
                    <h2><?php esc_html_e('AI tasks', 'modern-events-calendar-lite'); ?></h2>
                    <span class="mec-ai-badge"><i class="mec-sl-energy" aria-hidden="true"></i><?php esc_html_e('AI-powered', 'modern-events-calendar-lite'); ?></span>
                </div>
                <div class="mec-ai-card-body">
                    <form id="mec-ai-task-form" novalidate>
                    <section class="mec-ai-step">
                        <div class="mec-ai-step-head">
                            <span class="mec-ai-eyebrow"><span class="mec-ai-step-num">1</span><?php esc_html_e('Choose a task', 'modern-events-calendar-lite'); ?></span>
                            <h2><?php esc_html_e('What should MEC AI do?', 'modern-events-calendar-lite'); ?></h2>
                            <p><?php esc_html_e('Pick a task, describe what you need, and review the AI preview before anything is created.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                        <div class="mec-ai-task-cards">
                            <?php foreach ($tasks as $task_id => $task): ?>
                                <?php $task_status = $task['status'] ?? 'coming_soon'; ?>
                                <label class="mec-ai-task-choice<?php echo $task_id === $default_task ? ' is-active' : ''; ?>">
                                    <input type="radio" name="mec_ai_task[type]" value="<?php echo esc_attr($task_id); ?>" data-description="<?php echo esc_attr($task['description'] ?? ''); ?>" data-status="<?php echo esc_attr($task_status); ?>" data-action-label="<?php echo esc_attr($task['action_label'] ?? __('Coming soon', 'modern-events-calendar-lite')); ?>" <?php checked($task_id, $default_task); ?>>
                                    <span class="mec-ai-task-radio" aria-hidden="true"></span>
                                    <span class="mec-ai-task-body">
                                        <strong><?php echo esc_html($task['label'] ?? $task_id); ?></strong>
                                        <small><?php echo esc_html($task['description'] ?? ''); ?></small>
                                    </span>
                                    <?php if ($task_status !== 'generate_preview'): ?>
                                        <span class="mec-ai-task-chip"><?php esc_html_e('Coming soon', 'modern-events-calendar-lite'); ?></span>
                                    <?php endif; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <p id="mec-ai-task-description" class="mec-ai-task-note"><?php echo esc_html($tasks[$default_task]['description'] ?? ''); ?></p>
                    </section>

                    <?php if (isset($tasks['create_category'])): ?>
                            <section class="mec-ai-step mec-ai-task-panel" data-task="create_category">
                                <div class="mec-ai-step-head">
                                    <span class="mec-ai-eyebrow"><span class="mec-ai-step-num">2</span><?php esc_html_e('Describe the categories', 'modern-events-calendar-lite'); ?></span>
                                    <h2><?php esc_html_e('Category prompt', 'modern-events-calendar-lite'); ?></h2>
                                    <p><?php esc_html_e('Ask for one or many MEC categories. You will review and edit every suggestion before anything is created.', 'modern-events-calendar-lite'); ?></p>
                                </div>
                                <div class="mec-ai-prompt-row">
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
                            <section class="mec-ai-step mec-ai-task-panel" data-task="create_event" hidden>
                                <div class="mec-ai-step-head">
                                    <span class="mec-ai-eyebrow"><span class="mec-ai-step-num">2</span><?php esc_html_e('Describe the event', 'modern-events-calendar-lite'); ?></span>
                                    <h2><?php esc_html_e('Event prompt', 'modern-events-calendar-lite'); ?></h2>
                                    <p><?php esc_html_e('Describe one event in your own words. MEC will create a structured preview before it saves an editable draft.', 'modern-events-calendar-lite'); ?></p>
                                </div>
                                <div class="mec-ai-prompt-row">
                                    <label for="mec_ai_event_prompt"><?php esc_html_e('Event prompt', 'modern-events-calendar-lite'); ?><span class="mec-ai-required">*</span></label>
                                    <div class="mec-ai-prompt-content">
                                        <textarea id="mec_ai_event_prompt" name="mec_ai_task[prompt]" rows="11" required placeholder="<?php esc_attr_e('For example: Generate an event for a math class in Coquitlam on August 18, 2026, repeating every two weeks. Include three ticket types and a friendly, professional description.', 'modern-events-calendar-lite'); ?>"></textarea>
                                        <div class="mec-ai-prompt-help">
                                            <strong><?php esc_html_e('A useful event brief includes:', 'modern-events-calendar-lite'); ?></strong>
                                            <ul>
                                                <li><?php esc_html_e('event title, purpose, audience, description, and preferred writing tone', 'modern-events-calendar-lite'); ?></li>
                                                <li><?php esc_html_e('venue, organizer, date, start and end time, and whether it repeats', 'modern-events-calendar-lite'); ?></li>
                                                <li><?php esc_html_e('existing or new categories, labels, tags, location, and organizer details', 'modern-events-calendar-lite'); ?></li>
                                                <li><?php esc_html_e('hourly agenda items and ticket names, prices, and capacity limits', 'modern-events-calendar-lite'); ?></li>
                                                <li><?php esc_html_e('say “do not generate an image” when you do not want a featured image', 'modern-events-calendar-lite'); ?></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        <?php endif; ?>

                        <?php do_action('mec_ai_task_fields', $tasks, $status); ?>

                        <div class="mec-ai-submit-row">
                            <div><strong id="mec-ai-submit-title"><?php esc_html_e('Ready to generate a category preview?', 'modern-events-calendar-lite'); ?></strong><span id="mec-ai-submit-copy"><?php esc_html_e('MEC will send your brief and existing category context to the WordPress AI provider. No categories are created yet.', 'modern-events-calendar-lite'); ?></span></div>
                            <button type="submit" class="button button-primary mec-ai-submit"><?php esc_html_e('Generate category preview', 'modern-events-calendar-lite'); ?> <i class="mec-sl-arrow-right" aria-hidden="true"></i></button>
                        </div>
                        <div id="mec-ai-request-notice" class="mec-ai-request-notice" role="status" aria-live="polite"></div>
                    </form>
                    <div id="mec-ai-category-result" class="mec-ai-category-result" role="status" aria-live="polite"></div>

                    <section id="mec-ai-category-preview" class="mec-ai-step mec-ai-category-preview" hidden>
                        <div class="mec-ai-step-head">
                            <span class="mec-ai-eyebrow"><span class="mec-ai-step-num">3</span><?php esc_html_e('Review category suggestions', 'modern-events-calendar-lite'); ?></span>
                            <h2><?php esc_html_e('Review category suggestions', 'modern-events-calendar-lite'); ?></h2>
                            <p><?php esc_html_e('Review the generated details, use the MEC icon picker or color picker, deselect anything you do not want, then confirm creation.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                        <div id="mec-ai-category-preview-list" class="mec-ai-category-preview-list"></div>
                        <div class="mec-ai-submit-row mec-ai-apply-row">
                            <div><strong><?php esc_html_e('Create selected categories', 'modern-events-calendar-lite'); ?></strong><span><?php esc_html_e('Existing category names are skipped and never changed.', 'modern-events-calendar-lite'); ?></span></div>
                            <button type="button" id="mec-ai-create-categories" class="button button-primary mec-ai-submit"><?php esc_html_e('Create selected categories', 'modern-events-calendar-lite'); ?> <i class="mec-sl-arrow-right" aria-hidden="true"></i></button>
                        </div>
                    </section>

                    <section id="mec-ai-event-preview" class="mec-ai-step mec-ai-event-preview" hidden>
                        <div class="mec-ai-step-head">
                            <span class="mec-ai-eyebrow"><span class="mec-ai-step-num">3</span><?php esc_html_e('Review event preview', 'modern-events-calendar-lite'); ?></span>
                            <h2><?php esc_html_e('Review event preview', 'modern-events-calendar-lite'); ?></h2>
                            <p><?php esc_html_e('Fine-tune key details below, then confirm to create an editable draft. Nothing is published yet.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                        <div id="mec-ai-event-preview-content" class="mec-ai-event-preview-content"></div>
                        <div class="mec-ai-submit-row mec-ai-apply-row">
                            <div><strong><?php esc_html_e('Create event draft', 'modern-events-calendar-lite'); ?></strong><span><?php esc_html_e('MEC will save this event as a draft and attach the previewed image when one is available.', 'modern-events-calendar-lite'); ?></span></div>
                            <button type="button" id="mec-ai-create-event" class="button button-primary mec-ai-submit"><?php esc_html_e('Create event draft', 'modern-events-calendar-lite'); ?> <i class="mec-sl-arrow-right" aria-hidden="true"></i></button>
                        </div>
                    </section>
                </div>
            </main>

            <aside class="w-box mec-intro-card mec-ai-card mec-ai-status-card">
                <div class="mec-ai-card-head">
                    <h2><?php esc_html_e('AI readiness', 'modern-events-calendar-lite'); ?></h2>
                    <span class="mec-ai-eyebrow"><?php esc_html_e('Status', 'modern-events-calendar-lite'); ?></span>
                </div>
                <div class="mec-ai-card-body">
                    <ul class="mec-ai-status-list">
                        <li class="<?php echo ($status['core_available'] ? 'is-ready' : 'is-warning'); ?>"><span></span><div><strong><?php esc_html_e('WordPress AI capability', 'modern-events-calendar-lite'); ?></strong><small><?php echo esc_html($status['core_available'] ? __('Built into WordPress', 'modern-events-calendar-lite') : __('Disabled or unavailable', 'modern-events-calendar-lite')); ?></small></div></li>
                        <li class="<?php echo ($status['text_available'] ? 'is-ready' : 'is-warning'); ?>"><span></span><div><strong><?php esc_html_e('Text generation', 'modern-events-calendar-lite'); ?></strong><small><?php echo esc_html($status['text_available'] ? __('A provider is ready', 'modern-events-calendar-lite') : __('Configure a provider to continue', 'modern-events-calendar-lite')); ?></small></div></li>
                        <li class="<?php echo ($status['image_available'] ? 'is-ready' : ''); ?>"><span></span><div><strong><?php esc_html_e('Image generation', 'modern-events-calendar-lite'); ?></strong><small><?php echo esc_html($status['image_available'] ? __('A compatible provider is configured', 'modern-events-calendar-lite') : __('Optional, not configured', 'modern-events-calendar-lite')); ?></small></div></li>
                        <?php if ($status['ai_plugin_available']): ?>
                            <li class="is-ready"><span></span><div><strong><?php esc_html_e('WordPress AI plugin features', 'modern-events-calendar-lite'); ?></strong><small><?php esc_html_e('Enabled for MEC AI', 'modern-events-calendar-lite'); ?></small></div></li>
                        <?php endif; ?>
                    </ul>
                    <a class="mec-ai-connectors-link" href="<?php echo esc_url($status['connectors_url']); ?>"><?php esc_html_e('Open WordPress Connectors', 'modern-events-calendar-lite'); ?> <i class="mec-sl-arrow-right" aria-hidden="true"></i></a>
                </div>
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
</div>
