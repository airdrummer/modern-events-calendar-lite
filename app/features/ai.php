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
        if (!apply_filters('mec_ai_menu_enabled', false)) return;

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
                'status' => 'coming_soon',
                'action_label' => esc_html__('Coming soon', 'modern-events-calendar-lite'),
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
                $prompt = wp_ai_client_prompt();
                $text_available = ($prompt->is_supported_for_text_generation() === true);
                $image_available = ($prompt->is_supported_for_image_generation() === true);
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

        return [
            'task_type' => 'create_event',
            'prompt' => $prompt,
            'generate_image' => !empty($raw_task['generate_image']),
        ];
    }

    public function generate_ai_task_preview()
    {
        $access = $this->validate_ai_task_access(true);
        if (is_wp_error($access)) $this->send_ai_task_error($access);

        $raw_task = (isset($_POST['mec_ai_task']) && is_array($_POST['mec_ai_task'])) ? wp_unslash($_POST['mec_ai_task']) : [];
        $task_type = (isset($raw_task['type']) && is_scalar($raw_task['type'])) ? sanitize_key($raw_task['type']) : '';
        $tasks = $this->get_ai_tasks();

        if (!$task_type || !isset($tasks[$task_type])) $this->send_ai_task_error(new WP_Error('TASK_NOT_FOUND', esc_html__('The selected AI task is not available.', 'modern-events-calendar-lite')));
        if (($tasks[$task_type]['status'] ?? '') !== 'generate_preview') $this->send_ai_task_error(new WP_Error('TASK_COMING_SOON', esc_html__('This AI task is coming soon.', 'modern-events-calendar-lite')));

        if ($task_type !== 'create_category') $this->send_ai_task_error(new WP_Error('TASK_GENERATION_NOT_AVAILABLE', esc_html__('This AI task cannot generate a preview yet.', 'modern-events-calendar-lite')));

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

    public function apply_ai_task_preview()
    {
        $access = $this->validate_ai_task_access(true);
        if (is_wp_error($access)) $this->send_ai_task_error($access);

        $task_type = (isset($_POST['task_type']) && is_scalar($_POST['task_type'])) ? sanitize_key(wp_unslash($_POST['task_type'])) : '';
        if ($task_type !== 'create_category') $this->send_ai_task_error(new WP_Error('TASK_APPLY_NOT_AVAILABLE', esc_html__('This AI task cannot create content yet.', 'modern-events-calendar-lite')));

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

    private function send_ai_task_error($error)
    {
        $this->main->response([
            'success' => 0,
            'code' => $error->get_error_code(),
            'message' => $error->get_error_message(),
        ]);
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
