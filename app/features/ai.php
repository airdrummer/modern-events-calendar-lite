<?php
/** no direct access **/
defined('MECEXEC') or die();

class MEC_feature_ai extends MEC_base
{
    public $factory;
    public $main;
    public $settings;
    public $PT;

    public function __construct()
    {
        $this->factory = $this->getFactory();
        $this->main = $this->getMain();
        $this->settings = $this->main->get_settings();
        $this->PT = $this->main->get_main_post_type();
    }

    public function init()
    {
        $this->factory->action('mec_after_settings_submenu', [$this, 'menu']);
        $this->factory->action('wp_ajax_mec_ai_capture_task', [$this, 'capture_ai_task']);
        $this->factory->action('wp_ajax_mec_ai_generate_task_preview', [$this, 'generate_ai_task_preview']);
        $this->factory->action('wp_ajax_mec_ai_apply_task_preview', [$this, 'apply_ai_task_preview']);
        $this->factory->action('admin_notices', [$this, 'suppress_ai_page_license_notice'], 1);
    }

    public function menu($capability = '')
    {
        if (!$capability) $capability = current_user_can('administrator') ? 'manage_options' : 'mec_settings';
        add_submenu_page('mec-intro', esc_html__('MEC - AI', 'modern-events-calendar-lite'), esc_html__('AI', 'modern-events-calendar-lite'), apply_filters('mec_menu_cap', $capability, 'ai'), 'MEC-ai', [$this, 'page']);
    }

    public function page()
    {
        $path = MEC::import('app.features.mec.ai', true, true);

        ob_start();
        include $path;
        echo MEC_kses::full(ob_get_clean());
    }

    public function suppress_ai_page_license_notice()
    {
        $page = isset($_REQUEST['page']) ? sanitize_key(wp_unslash($_REQUEST['page'])) : '';
        if ($page !== 'mec-ai') return;

        global $wp_filter;
        if (empty($wp_filter['admin_notices']) || !isset($wp_filter['admin_notices']->callbacks)) return;

        foreach ($wp_filter['admin_notices']->callbacks as $priority => $callbacks)
        {
            foreach ($callbacks as $callback)
            {
                $function = $callback['function'] ?? null;
                if (!is_array($function) || !isset($function[0], $function[1]) || !is_object($function[0])) continue;
                if (is_a($function[0], 'MEC_feature_licensegate') && $function[1] === 'notice') remove_action('admin_notices', $function, $priority);
            }
        }
    }

    public function get_ai_tasks()
    {
        $tasks = [
            'create_category' => [
                'label' => esc_html__('Create categories', 'modern-events-calendar-lite'),
                'description' => esc_html__('Turn a category brief into reviewed MEC categories.', 'modern-events-calendar-lite'),
                'status' => 'generate_preview',
                'action_label' => esc_html__('Generate category preview', 'modern-events-calendar-lite'),
            ],
            'create_event' => [
                'label' => esc_html__('Create an event', 'modern-events-calendar-lite'),
                'description' => esc_html__('Turn an event brief into an MEC event draft.', 'modern-events-calendar-lite'),
                'status' => 'generate_preview',
                'action_label' => esc_html__('Generate event preview', 'modern-events-calendar-lite'),
            ],
        ];

        return apply_filters('mec_ai_task_definitions', $tasks);
    }

    public function get_ai_task_status()
    {
        $core_available = function_exists('wp_ai_client_prompt') && function_exists('wp_supports_ai') && wp_supports_ai();
        $text_available = false;
        $image_available = false;
        $approval = $this->get_ai_connector_approval_status();

        if ($core_available)
        {
            try
            {
                $image_available = $this->has_ai_image_generation_support();
                $prompt = wp_ai_client_prompt();
                $text_available = ($prompt->is_supported_for_text_generation() === true);
            }
            catch (Throwable $e)
            {
                $text_available = false;
                $image_available = false;
            }
        }

        return [
            'core_available' => $core_available,
            'text_available' => $text_available,
            'image_available' => $image_available,
            'requirements_met' => ($text_available && !$approval['required'] && (!$approval['ai_plugin_available'] || $approval['ai_plugin_features_enabled'])),
            'ai_plugin_available' => $approval['ai_plugin_available'],
            'ai_plugin_features_enabled' => $approval['ai_plugin_features_enabled'],
            'ai_plugin_url' => $approval['ai_plugin_url'],
            'approval_enabled' => $approval['enabled'],
            'approval_required' => $approval['required'],
            'approval_url' => $approval['url'],
            'booking_available' => ($this->getPRO() && isset($this->settings['booking_status']) && $this->settings['booking_status']),
            'connectors_url' => admin_url('options-connectors.php'),
        ];
    }

    private function has_ai_image_generation_support()
    {
        if (function_exists('\\WordPress\\AI\\has_image_generation_support')) return \WordPress\AI\has_image_generation_support();
        if (defined('WPAI_VERSION')) return false;
        if (!function_exists('wp_ai_client_prompt')) return false;

        try
        {
            return wp_ai_client_prompt()->is_supported_for_image_generation() === true;
        }
        catch (Throwable $e)
        {
            return false;
        }
    }

    private function get_ai_connector_approval_status()
    {
        $status = [
            'ai_plugin_available' => defined('WPAI_VERSION'),
            'ai_plugin_features_enabled' => false,
            'ai_plugin_url' => admin_url('options-general.php?page=ai-wp-admin'),
            'enabled' => false,
            'required' => false,
            'url' => admin_url('tools.php?page=ai-connector-approval'),
        ];

        if (!$status['ai_plugin_available']) return $status;

        $status['ai_plugin_features_enabled'] = (bool) get_option('wpai_features_enabled', false);
        $status['enabled'] = $status['ai_plugin_features_enabled']
            && (bool) get_option('wpai_feature_connector-approval_enabled', false)
            && class_exists('\\WordPress\\AI\\Connector_Approval\\Approvals_Store')
            && function_exists('wp_get_connectors')
            && class_exists('\\WordPress\\AiClient\\AiClient');

        if (!$status['enabled']) return $status;

        try
        {
            $registry = \WordPress\AiClient\AiClient::defaultRegistry();
            $configured_connector_ids = [];

            foreach (wp_get_connectors() as $connector_id => $connector)
            {
                if (!is_string($connector_id) || !is_array($connector) || ($connector['type'] ?? '') !== 'ai_provider') continue;
                if ($registry->hasProvider($connector_id) && $registry->isProviderConfigured($connector_id)) $configured_connector_ids[] = $connector_id;
            }

            if (!$configured_connector_ids) return $status;

            $store = new \WordPress\AI\Connector_Approval\Approvals_Store();
            $mec_basename = defined('MEC_BASENAME') ? MEC_BASENAME : 'modern-events-calendar/mec.php';
            $approved = false;

            foreach ($configured_connector_ids as $connector_id)
            {
                if ($store->is_approved($mec_basename, $connector_id))
                {
                    $approved = true;
                    break;
                }
            }

            $status['required'] = !$approved;
        }
        catch (Throwable $e)
        {
            $status['required'] = false;
        }

        return $status;
    }

    public function capture_ai_task()
    {
        if (!current_user_can('mec_settings') && !current_user_can('administrator')) $this->main->response(['success' => 0, 'code' => 'ADMIN_ONLY']);

        $nonce = (isset($_POST['nonce']) && is_scalar($_POST['nonce'])) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'mec_ai_task_nonce')) $this->main->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        if (!$this->get_ai_task_status()['requirements_met']) $this->main->response([
            'success' => 0,
            'code' => 'AI_TEXT_PROVIDER_REQUIRED',
            'message' => esc_html__('Configure a text-generation AI provider in WordPress Connectors before using MEC AI.', 'modern-events-calendar-lite'),
        ]);

        $raw_task = (isset($_POST['mec_ai_task']) && is_array($_POST['mec_ai_task'])) ? wp_unslash($_POST['mec_ai_task']) : [];
        $task_type = (isset($raw_task['type']) && is_scalar($raw_task['type'])) ? sanitize_key($raw_task['type']) : '';
        $tasks = $this->get_ai_tasks();

        if (!$task_type || !isset($tasks[$task_type])) $this->main->response(['success' => 0, 'code' => 'TASK_NOT_FOUND']);
        if (($tasks[$task_type]['status'] ?? '') === 'coming_soon') $this->main->response([
            'success' => 0,
            'code' => 'TASK_COMING_SOON',
            'message' => esc_html__('This AI task is coming soon.', 'modern-events-calendar-lite'),
        ]);

        $request = apply_filters('mec_ai_capture_task_request', null, $raw_task, $task_type, $this);
        if (is_wp_error($request)) $this->main->response(['success' => 0, 'code' => $request->get_error_code(), 'message' => $request->get_error_message()]);
        if (!is_array($request) && $task_type === 'create_event') $request = $this->normalize_ai_event_request($raw_task);

        if (is_wp_error($request)) $this->main->response(['success' => 0, 'code' => $request->get_error_code(), 'message' => $request->get_error_message()]);
        if (!is_array($request)) $this->main->response(['success' => 0, 'code' => 'TASK_CAPTURE_NOT_AVAILABLE']);

        $this->main->response([
            'success' => 1,
            'message' => esc_html__('Request captured. AI generation will be available in the next phase.', 'modern-events-calendar-lite'),
            'request' => $request,
        ]);
    }

    private function normalize_ai_event_request($raw_task)
    {
        $prompt = (isset($raw_task['prompt']) && is_scalar($raw_task['prompt'])) ? sanitize_textarea_field($raw_task['prompt']) : '';
        if (!trim($prompt)) return new WP_Error('PROMPT_REQUIRED', esc_html__('Please describe the event you want to create.', 'modern-events-calendar-lite'));
        if (strlen($prompt) > 6000) return new WP_Error('PROMPT_TOO_LONG', esc_html__('Keep the event prompt under 6,000 characters.', 'modern-events-calendar-lite'));

        return [
            'task_type' => 'create_event',
            'prompt' => $prompt,
        ];
    }

    public function generate_ai_task_preview()
    {
        $access = $this->validate_ai_task_access();
        if (is_wp_error($access)) $this->send_ai_task_error($access);

        $raw_task = (isset($_POST['mec_ai_task']) && is_array($_POST['mec_ai_task'])) ? wp_unslash($_POST['mec_ai_task']) : [];
        $task_type = (isset($raw_task['type']) && is_scalar($raw_task['type'])) ? sanitize_key($raw_task['type']) : '';
        $tasks = $this->get_ai_tasks();

        if (!$task_type || !isset($tasks[$task_type])) $this->send_ai_task_error(new WP_Error('TASK_NOT_FOUND', esc_html__('The selected AI task is not available.', 'modern-events-calendar-lite')));
        if (($tasks[$task_type]['status'] ?? '') !== 'generate_preview') $this->send_ai_task_error(new WP_Error('TASK_COMING_SOON', esc_html__('This AI task is coming soon.', 'modern-events-calendar-lite')));

        if ($task_type === 'create_category')
        {
            $category_access = $this->validate_ai_category_capability();
            if (is_wp_error($category_access)) $this->send_ai_task_error($category_access);

            $request = $this->normalize_ai_category_request($raw_task);
            if (is_wp_error($request)) $this->send_ai_task_error($request);

            $preview = $this->generate_ai_category_preview($request);
            if (is_wp_error($preview)) $this->send_ai_task_error($preview);

            $this->main->response([
                'success' => 1,
                'message' => esc_html__('Your category suggestions are ready to review.', 'modern-events-calendar-lite'),
                'payload' => $preview['payload'],
                'duplicate_names' => $preview['duplicate_names'],
            ]);
        }

        if ($task_type !== 'create_event') $this->send_ai_task_error(new WP_Error('TASK_GENERATION_NOT_AVAILABLE', esc_html__('This AI task cannot generate a preview yet.', 'modern-events-calendar-lite')));

        $event_access = $this->validate_ai_event_capability();
        if (is_wp_error($event_access)) $this->send_ai_task_error($event_access);

        $request = $this->normalize_ai_event_request($raw_task);
        if (is_wp_error($request)) $this->send_ai_task_error($request);

        $preview = $this->generate_ai_event_preview($request);
        if (is_wp_error($preview)) $this->send_ai_task_error($preview);

        $this->main->response([
            'success' => 1,
            'message' => esc_html__('Your event preview is ready to review.', 'modern-events-calendar-lite'),
            'payload' => $preview['payload'],
            'preview_token' => $preview['preview_token'],
            'image' => $preview['image'],
            'warnings' => $preview['warnings'],
        ]);
    }

    public function apply_ai_task_preview()
    {
        $access = $this->validate_ai_task_access();
        if (is_wp_error($access)) $this->send_ai_task_error($access);

        $task_type = (isset($_POST['task_type']) && is_scalar($_POST['task_type'])) ? sanitize_key(wp_unslash($_POST['task_type'])) : '';
        if ($task_type === 'create_event')
        {
            $event_access = $this->validate_ai_event_capability();
            if (is_wp_error($event_access)) $this->send_ai_task_error($event_access);

            $preview_token = (isset($_POST['preview_token']) && is_scalar($_POST['preview_token'])) ? sanitize_key(wp_unslash($_POST['preview_token'])) : '';
            $preview = $this->get_ai_event_preview($preview_token);
            if (is_wp_error($preview)) $this->send_ai_task_error($preview);

            $raw_payload = (isset($_POST['payload']) && is_scalar($_POST['payload'])) ? wp_unslash($_POST['payload']) : '';
            if ($raw_payload !== '')
            {
                $decoded = json_decode($raw_payload, true);
                if (!is_array($decoded)) $this->send_ai_task_error(new WP_Error('INVALID_EVENT_JSON', esc_html__('The reviewed event details are invalid. Generate a new preview and try again.', 'modern-events-calendar-lite')));

                $payload = $this->normalize_ai_event_payload($decoded);
                if (is_wp_error($payload)) $this->send_ai_task_error($payload);

                $preview['payload'] = $payload;
            }

            $result = $this->apply_ai_event_preview($preview);
            if (is_wp_error($result)) $this->send_ai_task_error($result);

            delete_transient($this->get_ai_event_preview_key($preview_token));
            $this->main->response(array_merge(['success' => 1], $result));
        }

        if ($task_type !== 'create_category') $this->send_ai_task_error(new WP_Error('TASK_APPLY_NOT_AVAILABLE', esc_html__('This AI task cannot create content yet.', 'modern-events-calendar-lite')));

        $category_access = $this->validate_ai_category_capability();
        if (is_wp_error($category_access)) $this->send_ai_task_error($category_access);

        $raw_payload = (isset($_POST['payload']) && is_scalar($_POST['payload'])) ? wp_unslash($_POST['payload']) : '';
        $decoded = json_decode($raw_payload, true);
        if (!is_array($decoded)) $this->send_ai_task_error(new WP_Error('INVALID_CATEGORY_JSON', esc_html__('The reviewed category JSON is invalid. Generate a new preview and try again.', 'modern-events-calendar-lite')));

        $payload = $this->normalize_ai_category_payload($decoded);
        if (is_wp_error($payload)) $this->send_ai_task_error($payload);

        $existing_names = $this->get_ai_existing_category_names();
        $created = [];
        $skipped = [];
        $failed = [];

        foreach ($payload['categories'] as $category)
        {
            $name_key = $this->ai_category_name_key($category['name']);
            if (isset($existing_names[$name_key]))
            {
                $skipped[] = $category['name'];
                continue;
            }

            $term = wp_insert_term($category['name'], 'mec_category', ['description' => $category['description']]);
            if (is_wp_error($term))
            {
                if ($term->get_error_code() === 'term_exists') $skipped[] = $category['name'];
                else $failed[] = ['name' => $category['name'], 'message' => $term->get_error_message()];
                continue;
            }

            $term_id = (int) ($term['term_id'] ?? 0);
            if (!$term_id)
            {
                $failed[] = ['name' => $category['name'], 'message' => esc_html__('WordPress did not return a category ID.', 'modern-events-calendar-lite')];
                continue;
            }

            update_term_meta($term_id, 'mec_cat_icon', $category['icon']);
            update_term_meta($term_id, 'mec_cat_color', $category['color']);
            $existing_names[$name_key] = $term_id;

            $created[] = [
                'name' => $category['name'],
                'term_id' => $term_id,
                'edit_url' => admin_url('term.php?taxonomy=mec_category&tag_ID=' . $term_id . '&post_type=' . rawurlencode($this->PT)),
            ];
        }

        $this->main->response([
            'success' => 1,
            'message' => esc_html__('Category creation is complete.', 'modern-events-calendar-lite'),
            'created' => $created,
            'skipped' => $skipped,
            'failed' => $failed,
        ]);
    }

    private function validate_ai_task_access($requires_category_capability = false)
    {
        if (!current_user_can('mec_settings') && !current_user_can('administrator')) return new WP_Error('ADMIN_ONLY', esc_html__('You do not have permission to use MEC AI tasks.', 'modern-events-calendar-lite'));

        $nonce = (isset($_POST['nonce']) && is_scalar($_POST['nonce'])) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'mec_ai_task_nonce')) return new WP_Error('NONCE_IS_INVALID', esc_html__('Your request could not be verified. Please refresh the page and try again.', 'modern-events-calendar-lite'));

        if (!$this->get_ai_task_status()['requirements_met']) return new WP_Error('AI_TEXT_PROVIDER_REQUIRED', esc_html__('Configure and approve a text-generation AI provider in WordPress before using MEC AI.', 'modern-events-calendar-lite'));

        if ($requires_category_capability)
        {
            $taxonomy = get_taxonomy('mec_category');
            $capability = ($taxonomy && isset($taxonomy->cap->manage_terms)) ? $taxonomy->cap->manage_terms : 'manage_categories';
            if (!current_user_can($capability)) return new WP_Error('CATEGORY_PERMISSION_REQUIRED', esc_html__('You do not have permission to manage MEC categories.', 'modern-events-calendar-lite'));
        }

        return true;
    }

    private function validate_ai_event_capability()
    {
        $post_type = get_post_type_object($this->PT);
        $capability = ($post_type && isset($post_type->cap->edit_posts)) ? $post_type->cap->edit_posts : 'edit_posts';
        if (!current_user_can($capability)) return new WP_Error('EVENT_PERMISSION_REQUIRED', esc_html__('You do not have permission to create MEC events.', 'modern-events-calendar-lite'));

        return true;
    }

    private function validate_ai_category_capability()
    {
        $taxonomy = get_taxonomy('mec_category');
        $capability = ($taxonomy && isset($taxonomy->cap->manage_terms)) ? $taxonomy->cap->manage_terms : 'manage_categories';
        if (!current_user_can($capability)) return new WP_Error('CATEGORY_PERMISSION_REQUIRED', esc_html__('You do not have permission to manage MEC categories.', 'modern-events-calendar-lite'));

        return true;
    }

    private function send_ai_task_error($error)
    {
        $this->main->response([
            'success' => 0,
            'code' => $error->get_error_code(),
            'message' => $error->get_error_message(),
        ]);
    }

    private function generate_ai_event_preview($request)
    {
        if (!function_exists('wp_ai_client_prompt')) return new WP_Error('AI_CLIENT_UNAVAILABLE', esc_html__('The WordPress AI Client is not available.', 'modern-events-calendar-lite'));

        $context = $this->get_ai_event_context();
        $prompt = sprintf(
            "Create one Modern Events Calendar event from this user brief. Return the requested JSON only. Use YYYY-MM-DD dates and 24-hour HH:MM times. Include only requested terms. Set generate_featured_image to false only when the user explicitly says not to generate an image. Use an empty string, empty array, or false when optional data is unknown. For every requested ticket, use a limit of 100 if the brief does not state a capacity. Only use a limit of 0 when the user explicitly requests zero available tickets.\n\nUser brief:\n%s\n\nExisting MEC terms:\n%s",
            $request['prompt'],
            wp_json_encode($context)
        );

        $schema = $this->get_ai_event_response_schema();

        try
        {
            $builder = wp_ai_client_prompt(apply_filters('mec_ai_event_prompt', $prompt, $request, $context))
                ->using_system_instruction(esc_html__('You are an MEC event assistant. Extract only factual event details from the user brief, use the supplied MEC context when appropriate, and follow the JSON schema exactly.', 'modern-events-calendar-lite'))
                ->as_json_response($schema);

            if ($builder->is_supported_for_text_generation() !== true) return new WP_Error('AI_TEXT_PROVIDER_REQUIRED', esc_html__('No configured AI provider supports text generation.', 'modern-events-calendar-lite'));
            $raw_response = $builder->generate_text();
        }
        catch (Throwable $e)
        {
            return new WP_Error('AI_REQUEST_FAILED', esc_html__('MEC could not create an event preview with WordPress AI. Please try again.', 'modern-events-calendar-lite'));
        }

        if (is_wp_error($raw_response)) return new WP_Error('AI_REQUEST_FAILED', $raw_response->get_error_message());

        $decoded = json_decode($raw_response, true);
        if (!is_array($decoded)) return new WP_Error('INVALID_AI_RESPONSE', esc_html__('WordPress AI did not return valid event JSON. Please try again.', 'modern-events-calendar-lite'));

        $payload = $this->normalize_ai_event_payload($decoded);
        if (is_wp_error($payload)) return $payload;

        $warnings = [];
        if (!$this->get_ai_task_status()['booking_available'] && count($payload['event']['tickets'])) $warnings[] = esc_html__('MEC Booking is not active, so tickets will not be created with this draft.', 'modern-events-calendar-lite');

        $image = [];
        if ($payload['event']['generate_featured_image'])
        {
            if ($this->get_ai_task_status()['image_available'])
            {
                $image = $this->generate_ai_event_image($payload['event']);
                if (is_wp_error($image))
                {
                    $warnings[] = $image->get_error_message();
                    $image = [];
                }
            }
            else $warnings[] = esc_html__('No image-generation provider is configured, so this event preview does not include a featured image.', 'modern-events-calendar-lite');
        }

        $token = sanitize_key(str_replace('-', '', wp_generate_uuid4()));
        set_transient($this->get_ai_event_preview_key($token), [
            'user_id' => get_current_user_id(),
            'payload' => $payload,
            'image' => $image,
        ], 30 * MINUTE_IN_SECONDS);

        $image_preview = [];
        if (!empty($image['data']) && !empty($image['mime_type'])) $image_preview = [
            'url' => 'data:' . $image['mime_type'] . ';base64,' . $image['data'],
            'alt_text' => $image['alt_text'],
        ];

        return [
            'payload' => $payload,
            'preview_token' => $token,
            'image' => $image_preview,
            'warnings' => $warnings,
        ];
    }

    private function get_ai_event_response_schema()
    {
        return apply_filters('mec_ai_event_response_schema', [
            'type' => 'object',
            'properties' => [
                'event' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => ['type' => 'string'],
                        'content' => ['type' => 'string'],
                        'start_date' => ['type' => 'string'],
                        'start_time' => ['type' => 'string'],
                        'end_date' => ['type' => 'string'],
                        'end_time' => ['type' => 'string'],
                        'all_day' => ['type' => 'boolean'],
                        'recurrence' => ['type' => 'object', 'properties' => [
                            'type' => ['type' => 'string'], 'interval' => ['type' => 'integer'], 'end_type' => ['type' => 'string'], 'end_date' => ['type' => 'string'], 'occurrences' => ['type' => 'integer'], 'weekdays' => ['type' => 'array', 'items' => ['type' => 'string']],
                        ], 'required' => ['type', 'interval', 'end_type', 'end_date', 'occurrences', 'weekdays'], 'additionalProperties' => false],
                        'location' => ['type' => 'object', 'properties' => ['name' => ['type' => 'string'], 'address' => ['type' => 'string'], 'latitude' => ['type' => 'string'], 'longitude' => ['type' => 'string']], 'required' => ['name', 'address', 'latitude', 'longitude'], 'additionalProperties' => false],
                        'organizer' => ['type' => 'object', 'properties' => ['name' => ['type' => 'string'], 'email' => ['type' => 'string'], 'tel' => ['type' => 'string'], 'url' => ['type' => 'string']], 'required' => ['name', 'email', 'tel', 'url'], 'additionalProperties' => false],
                        'categories' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'tags' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'labels' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'hourly_schedule' => ['type' => 'array', 'items' => ['type' => 'object', 'properties' => ['title' => ['type' => 'string'], 'start_time' => ['type' => 'string'], 'end_time' => ['type' => 'string'], 'description' => ['type' => 'string']], 'required' => ['title', 'start_time', 'end_time', 'description'], 'additionalProperties' => false]],
                        'tickets' => ['type' => 'array', 'items' => ['type' => 'object', 'properties' => ['name' => ['type' => 'string'], 'price' => ['type' => 'number'], 'limit' => ['type' => 'integer']], 'required' => ['name', 'price', 'limit'], 'additionalProperties' => false]],
                        'generate_featured_image' => ['type' => 'boolean'],
                        'image_prompt' => ['type' => 'string'],
                        'image_alt_text' => ['type' => 'string'],
                    ],
                    'required' => ['title', 'content', 'start_date', 'start_time', 'end_date', 'end_time', 'all_day', 'recurrence', 'location', 'organizer', 'categories', 'tags', 'labels', 'hourly_schedule', 'tickets', 'generate_featured_image', 'image_prompt', 'image_alt_text'],
                    'additionalProperties' => false,
                ],
            ],
            'required' => ['event'],
            'additionalProperties' => false,
        ]);
    }

    private function normalize_ai_event_payload($payload)
    {
        if (!is_array($payload) || !isset($payload['event']) || !is_array($payload['event'])) return new WP_Error('INVALID_EVENT_PAYLOAD', esc_html__('The AI response does not contain a valid event.', 'modern-events-calendar-lite'));

        $raw = $payload['event'];
        $title = isset($raw['title']) && is_scalar($raw['title']) ? sanitize_text_field($raw['title']) : '';
        $content = isset($raw['content']) && is_scalar($raw['content']) ? wp_kses_post($raw['content']) : '';
        if (!trim($title)) return new WP_Error('EVENT_TITLE_REQUIRED', esc_html__('The event preview needs a title. Please improve the prompt and try again.', 'modern-events-calendar-lite'));

        $start_date = $this->normalize_ai_event_date($raw['start_date'] ?? '');
        $end_date = $this->normalize_ai_event_date($raw['end_date'] ?? '');
        $all_day = !empty($raw['all_day']);
        $start_time = $this->normalize_ai_event_time($raw['start_time'] ?? '', $all_day ? '00:00' : '');
        $end_time = $this->normalize_ai_event_time($raw['end_time'] ?? '', $all_day ? '23:59' : '');
        if (!$start_date || !$end_date || !$start_time || !$end_time) return new WP_Error('EVENT_DATE_REQUIRED', esc_html__('The event preview needs valid start and end dates and times.', 'modern-events-calendar-lite'));
        if (strtotime($end_date . ' ' . $end_time) < strtotime($start_date . ' ' . $start_time)) return new WP_Error('EVENT_DATE_ORDER', esc_html__('The event end date and time must be on or after its start date and time.', 'modern-events-calendar-lite'));

        $recurrence = $this->normalize_ai_event_recurrence($raw['recurrence'] ?? [], $start_date);
        if (is_wp_error($recurrence)) return $recurrence;

        $location = $this->normalize_ai_event_location($raw['location'] ?? []);
        $organizer = $this->normalize_ai_event_organizer($raw['organizer'] ?? []);
        $categories = $this->normalize_ai_event_term_names($raw['categories'] ?? [], 'categories');
        $tags = $this->normalize_ai_event_term_names($raw['tags'] ?? [], 'tags');
        $labels = $this->normalize_ai_event_term_names($raw['labels'] ?? [], 'labels');
        $schedule = $this->normalize_ai_event_schedule($raw['hourly_schedule'] ?? []);
        if (is_wp_error($schedule)) return $schedule;
        $tickets = $this->normalize_ai_event_tickets($raw['tickets'] ?? []);
        if (is_wp_error($tickets)) return $tickets;

        return [
            'task_type' => 'create_event',
            'schema_version' => 1,
            'event' => [
                'title' => $title,
                'content' => $content,
                'start_date' => $start_date,
                'start_time' => $start_time,
                'end_date' => $end_date,
                'end_time' => $end_time,
                'all_day' => $all_day,
                'recurrence' => $recurrence,
                'location' => $location,
                'organizer' => $organizer,
                'categories' => $categories,
                'tags' => $tags,
                'labels' => $labels,
                'hourly_schedule' => $schedule,
                'tickets' => $tickets,
                'generate_featured_image' => !empty($raw['generate_featured_image']),
                'image_prompt' => isset($raw['image_prompt']) && is_scalar($raw['image_prompt']) ? sanitize_text_field($raw['image_prompt']) : '',
                'image_alt_text' => isset($raw['image_alt_text']) && is_scalar($raw['image_alt_text']) ? sanitize_text_field($raw['image_alt_text']) : '',
            ],
        ];
    }

    private function normalize_ai_event_date($value)
    {
        if (!is_scalar($value)) return false;
        $value = trim(wp_strip_all_tags((string) $value));
        if (!$value) return false;

        $formats = ['!Y-m-d', '!Y/m/d', '!F j, Y', '!F j Y', '!M j, Y', '!M j Y', '!m/d/Y', '!n/j/Y'];
        foreach ($formats as $format)
        {
            $date = DateTime::createFromFormat($format, $value, wp_timezone());
            $errors = DateTime::getLastErrors();
            if ($date && (!$errors || (!$errors['warning_count'] && !$errors['error_count']))) return $date->format('Y-m-d');
        }

        $timestamp = strtotime($value, current_time('timestamp'));
        if ($timestamp === false) return false;

        return wp_date('Y-m-d', $timestamp, wp_timezone());
    }

    private function normalize_ai_event_time($value, $fallback = '')
    {
        $value = is_scalar($value) ? trim((string) $value) : '';
        if (!$value) $value = $fallback;
        $value = strtolower(str_replace('.', '', $value));
        if ($value === 'noon') return '12:00';
        if ($value === 'midnight') return '00:00';
        if (preg_match('/^(?:[01]\\d|2[0-3]):[0-5]\\d$/', $value)) return $value;
        if (!preg_match('/^(1[0-2]|0?[1-9])(?::([0-5]\\d))?\\s*(am|pm)$/', $value, $matches)) return false;

        $hour = (int) $matches[1];
        $minutes = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : 0;
        if ($matches[3] === 'am') $hour = $hour === 12 ? 0 : $hour;
        else $hour = $hour === 12 ? 12 : $hour + 12;

        return sprintf('%02d:%02d', $hour, $minutes);
    }

    private function normalize_ai_event_recurrence($value, $start_date)
    {
        if (!is_array($value)) return new WP_Error('INVALID_RECURRENCE', esc_html__('The event recurrence is invalid.', 'modern-events-calendar-lite'));
        $type = isset($value['type']) && is_scalar($value['type']) ? sanitize_key($value['type']) : 'none';
        if (in_array($type, ['', 'no', 'once', 'single', 'non_recurring', 'not_recurring', 'non_repeating', 'not_repeating', 'does_not_repeat'], true)) $type = 'none';
        if (!in_array($type, ['none', 'daily', 'weekly', 'monthly', 'yearly'], true)) return new WP_Error('INVALID_RECURRENCE', esc_html__('The event recurrence type is invalid.', 'modern-events-calendar-lite'));
        if ($type === 'none') return ['type' => 'none', 'interval' => 1, 'end_type' => 'never', 'end_date' => '', 'occurrences' => 0, 'weekdays' => []];

        $interval = isset($value['interval']) ? absint($value['interval']) : 1;
        if (!$interval || $interval > 365) return new WP_Error('INVALID_RECURRENCE_INTERVAL', esc_html__('The event recurrence interval must be between 1 and 365.', 'modern-events-calendar-lite'));
        $end_type = isset($value['end_type']) && is_scalar($value['end_type']) ? sanitize_key($value['end_type']) : 'never';
        if (!in_array($end_type, ['never', 'date', 'occurrences'], true)) return new WP_Error('INVALID_RECURRENCE_END', esc_html__('The event recurrence end is invalid.', 'modern-events-calendar-lite'));
        $end_date = $end_type === 'date' ? $this->normalize_ai_event_date($value['end_date'] ?? '') : '';
        if ($end_type === 'date' && (!$end_date || $end_date < $start_date)) return new WP_Error('INVALID_RECURRENCE_END', esc_html__('The event recurrence end date is invalid.', 'modern-events-calendar-lite'));
        $occurrences = $end_type === 'occurrences' ? absint($value['occurrences'] ?? 0) : 0;
        if ($end_type === 'occurrences' && (!$occurrences || $occurrences > 500)) return new WP_Error('INVALID_RECURRENCE_COUNT', esc_html__('The event recurrence occurrences must be between 1 and 500.', 'modern-events-calendar-lite'));
        $weekdays = [];
        foreach ((array) ($value['weekdays'] ?? []) as $weekday)
        {
            $weekday = sanitize_key($weekday);
            if (in_array($weekday, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], true)) $weekdays[] = $weekday;
        }

        return ['type' => $type, 'interval' => $interval, 'end_type' => $end_type, 'end_date' => $end_date, 'occurrences' => $occurrences, 'weekdays' => array_values(array_unique($weekdays))];
    }

    private function normalize_ai_event_location($value)
    {
        if (!is_array($value)) $value = [];
        return [
            'name' => isset($value['name']) && is_scalar($value['name']) ? sanitize_text_field($value['name']) : '',
            'address' => isset($value['address']) && is_scalar($value['address']) ? sanitize_textarea_field($value['address']) : '',
            'latitude' => isset($value['latitude']) && is_scalar($value['latitude']) && is_numeric($value['latitude']) ? (string) $value['latitude'] : '',
            'longitude' => isset($value['longitude']) && is_scalar($value['longitude']) && is_numeric($value['longitude']) ? (string) $value['longitude'] : '',
        ];
    }

    private function normalize_ai_event_organizer($value)
    {
        if (!is_array($value)) $value = [];
        return [
            'name' => isset($value['name']) && is_scalar($value['name']) ? sanitize_text_field($value['name']) : '',
            'email' => isset($value['email']) && is_scalar($value['email']) ? sanitize_email($value['email']) : '',
            'tel' => isset($value['tel']) && is_scalar($value['tel']) ? sanitize_text_field($value['tel']) : '',
            'url' => isset($value['url']) && is_scalar($value['url']) ? esc_url_raw($value['url']) : '',
        ];
    }

    private function normalize_ai_event_term_names($values, $label)
    {
        if (!is_array($values)) return [];
        if (count($values) > 20) return [];
        $names = [];
        foreach ($values as $value)
        {
            if (!is_scalar($value)) continue;
            $name = sanitize_text_field($value);
            if ($name) $names[$this->ai_category_name_key($name)] = $name;
        }
        return array_values($names);
    }

    private function normalize_ai_event_schedule($values)
    {
        if (!is_array($values)) return new WP_Error('INVALID_HOURLY_SCHEDULE', esc_html__('The hourly schedule is invalid.', 'modern-events-calendar-lite'));
        if (count($values) > 20) return new WP_Error('HOURLY_SCHEDULE_LIMIT', esc_html__('An event can have up to 20 hourly schedule items.', 'modern-events-calendar-lite'));
        $items = [];
        foreach ($values as $value)
        {
            if (!is_array($value)) return new WP_Error('INVALID_HOURLY_SCHEDULE', esc_html__('The hourly schedule is invalid.', 'modern-events-calendar-lite'));
            $title = isset($value['title']) && is_scalar($value['title']) ? sanitize_text_field($value['title']) : '';
            $start = $this->normalize_ai_event_time($value['start_time'] ?? '');
            $end = $this->normalize_ai_event_time($value['end_time'] ?? '');
            if (!$title || !$start || !$end || $end < $start) return new WP_Error('INVALID_HOURLY_SCHEDULE', esc_html__('Every hourly schedule item needs a title and valid start and end times.', 'modern-events-calendar-lite'));
            $items[] = ['title' => $title, 'start_time' => $start, 'end_time' => $end, 'description' => isset($value['description']) && is_scalar($value['description']) ? sanitize_textarea_field($value['description']) : ''];
        }
        return $items;
    }

    private function normalize_ai_event_tickets($values)
    {
        if (!is_array($values)) return new WP_Error('INVALID_TICKETS', esc_html__('The event tickets are invalid.', 'modern-events-calendar-lite'));
        if (count($values) > 20) return new WP_Error('TICKET_LIMIT', esc_html__('An event can have up to 20 ticket types.', 'modern-events-calendar-lite'));
        $tickets = [];
        foreach ($values as $value)
        {
            if (!is_array($value)) return new WP_Error('INVALID_TICKETS', esc_html__('The event tickets are invalid.', 'modern-events-calendar-lite'));
            $name = isset($value['name']) && is_scalar($value['name']) ? sanitize_text_field($value['name']) : '';
            $price = isset($value['price']) && is_numeric($value['price']) ? (float) $value['price'] : -1;
            $limit = isset($value['limit']) && is_numeric($value['limit']) ? (int) $value['limit'] : -1;
            if (!$name || $price < 0 || $limit < 0) return new WP_Error('INVALID_TICKETS', esc_html__('Every ticket needs a name, a non-negative price, and a non-negative capacity.', 'modern-events-calendar-lite'));
            $tickets[] = ['name' => $name, 'price' => number_format($price, 2, '.', ''), 'limit' => $limit];
        }
        return $tickets;
    }

    private function generate_ai_event_image($event)
    {
        if (!class_exists('\\WordPress\\AiClient\\Files\\Enums\\FileTypeEnum')) return new WP_Error('AI_IMAGE_UNAVAILABLE', esc_html__('WordPress AI image generation is not available on this site.', 'modern-events-calendar-lite'));
        if (!$this->has_ai_image_generation_support()) return new WP_Error('AI_IMAGE_UNAVAILABLE', esc_html__('No configured AI provider supports image generation.', 'modern-events-calendar-lite'));

        $prompt = trim($event['image_prompt']);
        if (!$prompt) $prompt = sprintf('Create a polished featured image for the event "%s". %s', $event['title'], wp_strip_all_tags($event['content']));

        $image_timeout = max(30.0, (float) apply_filters('mec_ai_image_request_timeout', 90.0, $event));
        $timeout_filter = static function ($default_timeout) use ($image_timeout) {
            return max((float) $default_timeout, $image_timeout);
        };
        add_filter('wp_ai_client_default_request_timeout', $timeout_filter);

        try
        {
            $file_type = \WordPress\AiClient\Files\Enums\FileTypeEnum::inline();
            $builder = wp_ai_client_prompt(apply_filters('mec_ai_event_image_prompt', $prompt, $event))
                ->as_output_file_type($file_type);
            if ($builder->is_supported_for_image_generation() !== true) return new WP_Error('AI_IMAGE_UNAVAILABLE', esc_html__('No configured AI provider supports image generation.', 'modern-events-calendar-lite'));

            $result = $builder->generate_image_result();
            if (is_wp_error($result)) return new WP_Error('AI_IMAGE_FAILED', $result->get_error_message());
            $file = $result->toImageFile();
            $data = (string) $file->getBase64Data();
            $mime_type = (string) $file->getMimeType();
        }
        catch (Throwable $e)
        {
            return new WP_Error('AI_IMAGE_FAILED', esc_html__('MEC could not generate the featured image. You can still create the event draft.', 'modern-events-calendar-lite'));
        }
        finally
        {
            remove_filter('wp_ai_client_default_request_timeout', $timeout_filter);
        }

        if (!$data || strpos($mime_type, 'image/') !== 0) return new WP_Error('AI_IMAGE_FAILED', esc_html__('MEC could not read the generated featured image. You can still create the event draft.', 'modern-events-calendar-lite'));

        return [
            'data' => $data,
            'mime_type' => $mime_type,
            'alt_text' => $event['image_alt_text'] ?: $event['title'],
        ];
    }

    private function get_ai_event_preview_key($token)
    {
        return 'mec_ai_event_preview_' . sanitize_key($token);
    }

    private function get_ai_event_preview($token)
    {
        if (!$token) return new WP_Error('EVENT_PREVIEW_REQUIRED', esc_html__('Generate a new event preview before creating the draft.', 'modern-events-calendar-lite'));
        $preview = get_transient($this->get_ai_event_preview_key($token));
        if (!is_array($preview) || (int) ($preview['user_id'] ?? 0) !== get_current_user_id()) return new WP_Error('EVENT_PREVIEW_EXPIRED', esc_html__('This event preview has expired. Generate a new preview and try again.', 'modern-events-calendar-lite'));
        if (!isset($preview['payload']) || !is_array($preview['payload'])) return new WP_Error('EVENT_PREVIEW_EXPIRED', esc_html__('This event preview is no longer available. Generate a new preview and try again.', 'modern-events-calendar-lite'));

        $payload = $this->normalize_ai_event_payload($preview['payload']);
        if (is_wp_error($payload)) return $payload;
        $preview['payload'] = $payload;

        return $preview;
    }

    private function apply_ai_event_preview($preview)
    {
        $event = $preview['payload']['event'];
        $terms = $this->resolve_ai_event_terms($event);
        if (is_wp_error($terms)) return $terms;

        $start_time = $this->get_ai_event_time_parts($event['start_time']);
        $end_time = $this->get_ai_event_time_parts($event['end_time']);
        $repeat = $event['recurrence'];
        $repeat_interval = $repeat['type'] === 'weekly' ? ($repeat['interval'] * 7) : $repeat['interval'];
        $event_args = [
            'title' => $event['title'],
            'content' => $event['content'],
            'status' => 'draft',
            'author' => get_current_user_id(),
            'location_id' => $terms['location_id'] ?: 1,
            'organizer_id' => $terms['organizer_id'] ?: 1,
            'date' => [
                'start' => ['date' => $event['start_date'], 'hour' => $start_time['hour'], 'minutes' => $start_time['minutes'], 'ampm' => $start_time['ampm']],
                'end' => ['date' => $event['end_date'], 'hour' => $end_time['hour'], 'minutes' => $end_time['minutes'], 'ampm' => $end_time['ampm']],
                'repeat' => [],
                'allday' => $event['all_day'] ? 1 : 0,
            ],
            'start' => $event['start_date'],
            'end' => $event['end_date'],
            'start_time_hour' => $start_time['hour'],
            'start_time_minutes' => $start_time['minutes'],
            'start_time_ampm' => $start_time['ampm'],
            'end_time_hour' => $end_time['hour'],
            'end_time_minutes' => $end_time['minutes'],
            'end_time_ampm' => $end_time['ampm'],
            'allday' => $event['all_day'] ? 1 : 0,
            'repeat_status' => $repeat['type'] === 'none' ? 0 : 1,
            'repeat_type' => $repeat['type'] === 'none' ? '' : $repeat['type'],
            'interval' => $repeat_interval,
            'finish' => $repeat['end_type'] === 'date' ? $repeat['end_date'] : '',
            'repeat_count' => $repeat['end_type'] === 'occurrences' ? $repeat['occurrences'] : null,
            'weekdays' => implode(',', $repeat['weekdays']),
            'meta' => [
                'mec_source' => 'mec-calendar',
                'mec_repeat' => ['status' => $repeat['type'] === 'none' ? 0 : 1, 'type' => $repeat['type'] === 'none' ? '' : $repeat['type'], 'interval' => $repeat_interval],
                'mec_allday' => $event['all_day'] ? 1 : 0,
                'mec_hourly_schedules' => count($event['hourly_schedule']) ? $this->get_ai_event_hourly_schedules($event['hourly_schedule']) : [],
                'mec_booking' => ['bookings_limit_unlimited' => 1, 'bookings_limit' => ''],
                'mec_tickets' => $this->get_ai_task_status()['booking_available'] && count($event['tickets']) ? $this->get_ai_event_tickets($event['tickets']) : [],
            ],
        ];

        $post_id = $this->main->save_event($event_args);
        if (is_wp_error($post_id)) return $post_id;
        if (!$post_id) return new WP_Error('EVENT_CREATE_FAILED', esc_html__('MEC could not create the event draft.', 'modern-events-calendar-lite'));

        foreach ($terms['taxonomies'] as $taxonomy => $term_ids)
        {
            if (!$term_ids) continue;
            $result = wp_set_object_terms($post_id, $term_ids, $taxonomy, false);
            if (is_wp_error($result)) return new WP_Error('EVENT_TERMS_FAILED', $result->get_error_message());
        }

        $warnings = [];
        if (!$this->get_ai_task_status()['booking_available'] && count($event['tickets'])) $warnings[] = esc_html__('Ticket details were skipped because MEC Booking is not active.', 'modern-events-calendar-lite');

        if (!empty($preview['image']['data']))
        {
            $attachment_id = $this->import_ai_event_image($preview['image'], $post_id, $event['title']);
            if (is_wp_error($attachment_id)) $warnings[] = $attachment_id->get_error_message();
            else set_post_thumbnail($post_id, $attachment_id);
        }

        return [
            'message' => esc_html__('Your event draft has been created.', 'modern-events-calendar-lite'),
            'event' => [
                'id' => $post_id,
                'title' => get_the_title($post_id),
                'edit_url' => get_edit_post_link($post_id, 'raw'),
            ],
            'warnings' => $warnings,
        ];
    }

    private function resolve_ai_event_terms($event)
    {
        $taxonomies = [
            'mec_category' => $event['categories'],
            apply_filters('mec_taxonomy_tag', '') => $event['tags'],
            'mec_label' => $event['labels'],
        ];
        $resolved = ['taxonomies' => [], 'location_id' => 0, 'organizer_id' => 0];

        foreach ($taxonomies as $taxonomy => $names)
        {
            if (!$taxonomy) continue;
            $ids = $this->resolve_ai_event_term_names($taxonomy, $names);
            if (is_wp_error($ids)) return $ids;
            $resolved['taxonomies'][$taxonomy] = $ids;
        }

        $location = $this->resolve_ai_event_location($event['location']);
        if (is_wp_error($location)) return $location;
        $resolved['location_id'] = $location;
        if ($location) $resolved['taxonomies']['mec_location'] = [$location];

        $organizer = $this->resolve_ai_event_organizer($event['organizer']);
        if (is_wp_error($organizer)) return $organizer;
        $resolved['organizer_id'] = $organizer;
        if ($organizer) $resolved['taxonomies']['mec_organizer'] = [$organizer];

        return $resolved;
    }

    private function resolve_ai_event_term_names($taxonomy, $names)
    {
        $ids = [];
        foreach ($names as $name)
        {
            $term = $this->get_ai_event_term_by_name($taxonomy, $name);
            if ($term && !is_wp_error($term))
            {
                $ids[] = (int) $term->term_id;
                continue;
            }

            $taxonomy_object = get_taxonomy($taxonomy);
            $capability = ($taxonomy_object && isset($taxonomy_object->cap->manage_terms)) ? $taxonomy_object->cap->manage_terms : 'manage_categories';
            if (!current_user_can($capability)) return new WP_Error('TERM_PERMISSION_REQUIRED', sprintf(esc_html__('You do not have permission to create the %s term %s.', 'modern-events-calendar-lite'), $taxonomy, $name));

            $created = wp_insert_term($name, $taxonomy);
            if (is_wp_error($created))
            {
                if ($created->get_error_code() === 'term_exists') $ids[] = (int) $created->get_error_data();
                else return new WP_Error('TERM_CREATE_FAILED', $created->get_error_message());
            }
            else $ids[] = (int) $created['term_id'];
        }

        return array_values(array_unique(array_filter($ids)));
    }

    private function resolve_ai_event_location($location)
    {
        if (!$location['name']) return 0;
        $term = $this->get_ai_event_term_by_name('mec_location', $location['name']);
        if ($term && !is_wp_error($term)) return (int) $term->term_id;

        $taxonomy = get_taxonomy('mec_location');
        $capability = ($taxonomy && isset($taxonomy->cap->manage_terms)) ? $taxonomy->cap->manage_terms : 'manage_categories';
        if (!current_user_can($capability)) return new WP_Error('LOCATION_PERMISSION_REQUIRED', esc_html__('You do not have permission to create MEC locations.', 'modern-events-calendar-lite'));

        $id = $this->main->save_location($location);
        return $id ? (int) $id : new WP_Error('LOCATION_CREATE_FAILED', esc_html__('MEC could not create the requested location.', 'modern-events-calendar-lite'));
    }

    private function resolve_ai_event_organizer($organizer)
    {
        if (!$organizer['name']) return 0;
        $term = $this->get_ai_event_term_by_name('mec_organizer', $organizer['name']);
        if ($term && !is_wp_error($term)) return (int) $term->term_id;

        $taxonomy = get_taxonomy('mec_organizer');
        $capability = ($taxonomy && isset($taxonomy->cap->manage_terms)) ? $taxonomy->cap->manage_terms : 'manage_categories';
        if (!current_user_can($capability)) return new WP_Error('ORGANIZER_PERMISSION_REQUIRED', esc_html__('You do not have permission to create MEC organizers.', 'modern-events-calendar-lite'));

        $id = $this->main->save_organizer($organizer);
        return $id ? (int) $id : new WP_Error('ORGANIZER_CREATE_FAILED', esc_html__('MEC could not create the requested organizer.', 'modern-events-calendar-lite'));
    }

    private function get_ai_event_term_by_name($taxonomy, $name)
    {
        static $terms = [];
        if (!$taxonomy || !taxonomy_exists($taxonomy)) return false;
        if (!isset($terms[$taxonomy]))
        {
            $items = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false, 'number' => 0]);
            $terms[$taxonomy] = [];
            if (!is_wp_error($items) && is_array($items)) foreach ($items as $term) $terms[$taxonomy][$this->ai_category_name_key($term->name)] = $term;
        }

        return $terms[$taxonomy][$this->ai_category_name_key($name)] ?? false;
    }

    private function get_ai_event_time_parts($time)
    {
        $parts = explode(':', $time);
        $hour = (int) $parts[0];
        $minutes = (int) $parts[1];
        $ampm = $hour >= 12 ? 'PM' : 'AM';
        $hour = $hour % 12;
        if (!$hour) $hour = 12;

        return ['hour' => $hour, 'minutes' => $minutes, 'ampm' => $ampm];
    }

    private function get_ai_event_hourly_schedules($items)
    {
        $schedules = [];
        foreach ($items as $index => $item)
        {
            $schedules[$index] = [
                'from' => $this->get_ai_event_hourly_schedule_time($item['start_time']),
                'to' => $this->get_ai_event_hourly_schedule_time($item['end_time']),
                'title' => $item['title'],
                'description' => $item['description'],
            ];
        }

        return [[
            'title' => esc_html__('Day 1', 'modern-events-calendar-lite'),
            'schedules' => $schedules,
        ]];
    }

    private function get_ai_event_hourly_schedule_time($time)
    {
        if ((int) ($this->settings['time_format'] ?? 24) === 24) return $time;

        $parts = $this->get_ai_event_time_parts($time);
        return $parts['hour'] . ':' . str_pad($parts['minutes'], 2, '0', STR_PAD_LEFT) . ' ' . $parts['ampm'];
    }

    private function get_ai_event_tickets($tickets)
    {
        $items = [];
        foreach ($tickets as $index => $ticket)
        {
            $items[$index] = [
                'name' => $ticket['name'],
                'price' => $ticket['price'],
                'limit' => $ticket['limit'],
                'unlimited' => 0,
                'seats' => 1,
                'minimum_ticket' => 0,
                'maximum_ticket' => '',
                'price_label' => '',
                'description' => '',
                'dates' => [],
            ];
        }

        return $items;
    }

    private function import_ai_event_image($image, $post_id, $title)
    {
        if (empty($image['data']) || empty($image['mime_type'])) return new WP_Error('IMAGE_IMPORT_FAILED', esc_html__('The generated featured image is unavailable.', 'modern-events-calendar-lite'));
        $binary = base64_decode($image['data'], true);
        if ($binary === false) return new WP_Error('IMAGE_IMPORT_FAILED', esc_html__('MEC could not import the generated featured image.', 'modern-events-calendar-lite'));

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $temporary = wp_tempnam('mec-ai-event');
        if (!$temporary || file_put_contents($temporary, $binary) === false) return new WP_Error('IMAGE_IMPORT_FAILED', esc_html__('MEC could not prepare the generated featured image.', 'modern-events-calendar-lite'));

        $extension = wp_get_default_extension_for_mime_type($image['mime_type']);
        $file = [
            'name' => sanitize_file_name($title ?: 'mec-ai-event') . '.' . ($extension ?: 'png'),
            'type' => $image['mime_type'],
            'tmp_name' => $temporary,
        ];
        $attachment_id = media_handle_sideload($file, $post_id, $title, [
            'post_title' => $title,
            'meta_input' => ['_wp_attachment_image_alt' => sanitize_text_field($image['alt_text'] ?? $title), 'wpai_generated' => 1],
        ]);
        if (file_exists($temporary)) wp_delete_file($temporary);

        return $attachment_id;
    }

    private function get_ai_event_context()
    {
        return [
            'categories' => $this->get_ai_event_terms_context('mec_category'),
            'tags' => $this->get_ai_event_terms_context(apply_filters('mec_taxonomy_tag', '')),
            'labels' => $this->get_ai_event_terms_context('mec_label'),
            'locations' => $this->get_ai_event_terms_context('mec_location', ['address']),
            'organizers' => $this->get_ai_event_terms_context('mec_organizer'),
        ];
    }

    private function get_ai_event_terms_context($taxonomy, $meta_fields = [])
    {
        if (!$taxonomy || !taxonomy_exists($taxonomy)) return [];
        $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false, 'number' => 50, 'orderby' => 'name', 'order' => 'ASC']);
        if (is_wp_error($terms) || !is_array($terms)) return [];

        $context = [];
        foreach ($terms as $term)
        {
            $item = ['name' => $term->name, 'description' => wp_trim_words(wp_strip_all_tags($term->description), 24, '')];
            foreach ($meta_fields as $field) $item[$field] = sanitize_text_field((string) get_term_meta($term->term_id, $field, true));
            $context[] = $item;
        }

        return $context;
    }

    private function normalize_ai_category_request($raw_task)
    {
        $prompt = (isset($raw_task['prompt']) && is_scalar($raw_task['prompt'])) ? sanitize_textarea_field($raw_task['prompt']) : '';
        if (!trim($prompt)) return new WP_Error('PROMPT_REQUIRED', esc_html__('Please describe the categories you want to create.', 'modern-events-calendar-lite'));
        if (strlen($prompt) > 6000) return new WP_Error('PROMPT_TOO_LONG', esc_html__('Keep the category prompt under 6,000 characters.', 'modern-events-calendar-lite'));

        return [
            'task_type' => 'create_category',
            'prompt' => $prompt,
        ];
    }

    private function generate_ai_category_preview($request)
    {
        if (!function_exists('wp_ai_client_prompt')) return new WP_Error('AI_CLIENT_UNAVAILABLE', esc_html__('The WordPress AI Client is not available.', 'modern-events-calendar-lite'));

        $context = $this->get_ai_category_context();
        $icons = $this->get_ai_category_icons();
        $prompt = sprintf(
            "Create between 1 and 25 flat Modern Events Calendar categories from this user brief. Each category needs a concise name, a useful description, one valid MEC icon CSS class, and a six-digit hex color. The color value must be exactly in the #RRGGBB format, for example #2563EB; do not use a color name. Do not repeat existing category names. Return JSON only.\n\nUser brief:\n%s\n\nExisting MEC categories (use as context; do not duplicate):\n%s\n\nAllowed MEC icon classes:\n%s",
            $request['prompt'],
            wp_json_encode($context),
            implode(', ', $icons)
        );

        $schema = apply_filters('mec_ai_category_response_schema', [
            'type' => 'object',
            'properties' => [
                'categories' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 25,
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'icon' => ['type' => 'string'],
                            'color' => ['type' => 'string'],
                        ],
                        'required' => ['name', 'description', 'icon', 'color'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'required' => ['categories'],
            'additionalProperties' => false,
        ], $request, $context);

        try
        {
            $builder = wp_ai_client_prompt(apply_filters('mec_ai_category_prompt', $prompt, $request, $context))
                ->using_system_instruction(esc_html__('You are an MEC taxonomy assistant. Produce practical, distinct event categories and follow the requested JSON schema exactly.', 'modern-events-calendar-lite'))
                ->as_json_response($schema);

            if ($builder->is_supported_for_text_generation() !== true) return new WP_Error('AI_TEXT_PROVIDER_REQUIRED', esc_html__('No configured AI provider supports text generation.', 'modern-events-calendar-lite'));

            $raw_response = $builder->generate_text();
        }
        catch (Throwable $e)
        {
            return new WP_Error('AI_REQUEST_FAILED', esc_html__('MEC could not request category suggestions from WordPress AI. Please try again.', 'modern-events-calendar-lite'));
        }

        if (is_wp_error($raw_response)) return new WP_Error('AI_REQUEST_FAILED', $raw_response->get_error_message());

        $decoded = json_decode($raw_response, true);
        if (!is_array($decoded)) return new WP_Error('INVALID_AI_RESPONSE', esc_html__('WordPress AI did not return valid category JSON. Please try again.', 'modern-events-calendar-lite'));

        $payload = $this->normalize_ai_category_payload($decoded);
        if (is_wp_error($payload)) return $payload;

        $existing_names = $this->get_ai_existing_category_names();
        $duplicates = [];
        foreach ($payload['categories'] as $category)
        {
            if (isset($existing_names[$this->ai_category_name_key($category['name'])])) $duplicates[] = $category['name'];
        }

        return [
            'payload' => apply_filters('mec_ai_category_preview_payload', $payload, $request),
            'duplicate_names' => $duplicates,
        ];
    }

    private function normalize_ai_category_payload($payload)
    {
        if (!is_array($payload) || !isset($payload['categories']) || !is_array($payload['categories'])) return new WP_Error('INVALID_CATEGORY_PAYLOAD', esc_html__('The category response does not contain a valid categories list.', 'modern-events-calendar-lite'));
        if (!count($payload['categories']) || count($payload['categories']) > 25) return new WP_Error('CATEGORY_LIMIT', esc_html__('Choose between 1 and 25 categories per batch.', 'modern-events-calendar-lite'));

        $icons = array_flip($this->get_ai_category_icons());
        $categories = [];
        $names = [];

        foreach ($payload['categories'] as $index => $category)
        {
            if (!is_array($category)) return new WP_Error('INVALID_CATEGORY', sprintf(esc_html__('Category %d is invalid.', 'modern-events-calendar-lite'), $index + 1));

            $name = (isset($category['name']) && is_scalar($category['name'])) ? sanitize_text_field($category['name']) : '';
            $description = (isset($category['description']) && is_scalar($category['description'])) ? sanitize_textarea_field($category['description']) : '';
            $icon = (isset($category['icon']) && is_scalar($category['icon'])) ? sanitize_text_field($category['icon']) : '';
            $color = (isset($category['color']) && is_scalar($category['color'])) ? $this->normalize_ai_category_color($category['color']) : false;

            if (!trim($name)) return new WP_Error('CATEGORY_NAME_REQUIRED', sprintf(esc_html__('Category %d needs a name.', 'modern-events-calendar-lite'), $index + 1));
            if (!trim($description)) return new WP_Error('CATEGORY_DESCRIPTION_REQUIRED', sprintf(esc_html__('Category %s needs a description.', 'modern-events-calendar-lite'), $name));
            if (!isset($icons[$icon])) return new WP_Error('INVALID_CATEGORY_ICON', sprintf(esc_html__('Category %s has an invalid MEC icon.', 'modern-events-calendar-lite'), $name));
            if (!$color) return new WP_Error('INVALID_CATEGORY_COLOR', sprintf(esc_html__('Category %s needs a valid six-digit hex color.', 'modern-events-calendar-lite'), $name));

            $name_key = $this->ai_category_name_key($name);
            if (isset($names[$name_key])) return new WP_Error('DUPLICATE_CATEGORY_NAME', sprintf(esc_html__('The preview contains the category name %s more than once.', 'modern-events-calendar-lite'), $name));

            $names[$name_key] = true;
            $categories[] = [
                'name' => $name,
                'description' => $description,
                'icon' => $icon,
                'color' => $color,
            ];
        }

        return [
            'task_type' => 'create_category',
            'schema_version' => 1,
            'categories' => $categories,
        ];
    }

    private function get_ai_category_context()
    {
        $terms = get_terms([
            'taxonomy' => 'mec_category',
            'hide_empty' => false,
            'number' => 100,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);
        if (is_wp_error($terms) || !is_array($terms)) return [];

        $context = [];
        foreach ($terms as $term)
        {
            $context[] = [
                'name' => $term->name,
                'description' => wp_trim_words(wp_strip_all_tags($term->description), 40, ''),
                'icon' => (string) get_term_meta($term->term_id, 'mec_cat_icon', true),
                'color' => (string) get_term_meta($term->term_id, 'mec_cat_color', true),
            ];
        }

        return $context;
    }

    private function get_ai_existing_category_names()
    {
        $terms = get_terms([
            'taxonomy' => 'mec_category',
            'hide_empty' => false,
            'number' => 0,
            'fields' => 'id=>name',
        ]);
        if (is_wp_error($terms) || !is_array($terms)) return [];

        $names = [];
        foreach ($terms as $term_id => $name) $names[$this->ai_category_name_key($name)] = (int) $term_id;
        return $names;
    }

    public function get_ai_category_icons()
    {
        static $icons = null;
        if (is_array($icons)) return $icons;

        $icons = [];
        $file = MEC_ABSPATH . 'assets' . DS . 'icon.html';
        $contents = is_readable($file) ? file_get_contents($file) : '';
        if ($contents && preg_match_all('/value="(none|mec-(?:sl|fa)-[a-z0-9-]+)"/', $contents, $matches)) $icons = array_values(array_unique($matches[1]));

        return $icons;
    }

    private function normalize_ai_category_color($color)
    {
        $color = trim(wp_strip_all_tags((string) $color));
        if (!$color) return false;

        if (preg_match('/(?:^|[^a-f0-9])#?([a-f0-9]{6}|[a-f0-9]{3})(?![a-f0-9])/i', $color, $matches))
        {
            $hex = '#' . $matches[1];
            if (strlen($hex) === 4) $hex = '#' . $hex[1] . $hex[1] . $hex[2] . $hex[2] . $hex[3] . $hex[3];
            return strtoupper($hex);
        }

        if (preg_match('/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(?:\s*,\s*(?:0|0?\.\d+|1))?\s*\)$/i', $color, $matches))
        {
            $red = (int) $matches[1];
            $green = (int) $matches[2];
            $blue = (int) $matches[3];

            if ($red <= 255 && $green <= 255 && $blue <= 255) return sprintf('#%02X%02X%02X', $red, $green, $blue);
        }

        $names = [
            'blue' => '#0000FF', 'lightblue' => '#ADD8E6', 'skyblue' => '#87CEEB', 'navy' => '#000080',
            'green' => '#008000', 'lightgreen' => '#90EE90', 'teal' => '#008080', 'cyan' => '#00FFFF',
            'red' => '#FF0000', 'orange' => '#FFA500', 'yellow' => '#FFFF00', 'gold' => '#FFD700',
            'purple' => '#800080', 'violet' => '#EE82EE', 'indigo' => '#4B0082', 'pink' => '#FFC0CB',
            'magenta' => '#FF00FF', 'brown' => '#A52A2A', 'gray' => '#808080', 'grey' => '#808080',
            'black' => '#111827', 'white' => '#FFFFFF',
        ];
        $name = strtolower(preg_replace('/[\s_-]+/', '', $color));

        return $names[$name] ?? false;
    }

    private function ai_category_name_key($name)
    {
        $name = trim(wp_strip_all_tags((string) $name));
        return function_exists('mb_strtolower') ? mb_strtolower($name, 'UTF-8') : strtolower($name);
    }

}
