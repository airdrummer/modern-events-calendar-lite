<?php

/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC factory class.
 * @author Webnus <info@webnus.net>
 */
class MEC_factory extends MEC_base
{
    public $main;
    public $file;
    public $folder;
    public $db;
    public $parser;

    /**
     * @static
     * @var array
     */
    public static $params = [];

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // Load Vendors
        require_once MEC_ABSPATH . 'app/vendor/autoload.php';

        // MEC Main library
        $this->main = $this->getMain();

        // MEC File library
        $this->file = $this->getFile();

        // MEC Folder library
        $this->folder = $this->getFolder();

        // MEC DB library
        $this->db = $this->getDB();

        // MEC Parser library
        $this->parser = $this->getParser();

        // Import MEC Controller Class
        $this->import('app.controller');
    }

    /**
     * Register Webnus MEC actions
     * @author Webnus <info@webnus.net>
     */
    public function load_actions()
    {
        // Set CronJobs
        $this->action('admin_init', [$this, 'mec_add_cron_jobs'], 9999);

        // Register MEC function to be called in WordPress footer hook
        $this->action('wp_footer', [$this, 'load_footer'], 9999);
        $this->action('admin_footer', [$this, 'load_footer'], 9999);

        // Parse WordPress query
        $this->action('parse_query', [$this->parser, 'WPQ_parse'], 99);

        // Add custom styles to header
        $this->action('wp_head', [$this, 'include_styles'], 9999999999);

        if (!is_admin())
        {
            // MEC iCal export
            $this->action('init', [$this->main, 'ical'], 9999);

            // MEC iCal export in email
            $this->action('init', [$this->main, 'ical_email'], 999);

            // MEC Booking Invoice
            $this->action('init', [$this->main, 'booking_invoice'], 9999);

            // MEC Cart Invoice
            $this->action('init', [$this->main, 'cart_invoice'], 9999);

            // MEC Print Feature
            $this->action('init', [$this->main, 'print_calendar'], 9999);

            // MEC Print Feature
            $this->action('wp', [$this->main, 'booking_modal'], 9999);

            // Add Events to Tag Archive Page
            $this->action('pre_get_posts', [$this->main, 'add_events_to_tags_archive']);
        }

        // Redirect to MEC Dashboard
        $this->action('admin_init', [$this->main, 'mec_redirect_after_activate']);

        // MEC booking verification and cancellation
        $this->action('mec_before_main_content', [$this->main, 'do_endpoints'], 9999);

        // Add AJAX actions
        $this->action('wp_ajax_mec_save_styles', [$this->main, 'save_options']);
        $this->action('wp_ajax_mec_save_settings', [$this->main, 'save_options']);
        $this->action('wp_ajax_mec_save_reg_form', [$this->main, 'save_options']);
        $this->action('wp_ajax_mec_save_gateways', [$this->main, 'save_options']);
        $this->action('wp_ajax_mec_save_styling', [$this->main, 'save_options']);
        $this->action('wp_ajax_mec_save_notifications', [$this->main, 'save_notifications']);
        $this->action('wp_ajax_mec_save_messages', [$this->main, 'save_messages']);
        $this->action('wp_ajax_wizard_import_dummy_events', [$this->main, 'wizard_import_dummy_events']);
        $this->action('wp_ajax_wizard_import_dummy_shortcodes', [$this->main, 'wizard_import_dummy_shortcodes']);
        $this->action('wp_ajax_wizard_save_weekdays', [$this->main, 'save_wizard_options']);
        $this->action('wp_ajax_wizard_save_slug', [$this->main, 'save_wizard_options']);
        $this->action('wp_ajax_wizard_save_module', [$this->main, 'save_wizard_options']);
        $this->action('wp_ajax_wizard_save_single', [$this->main, 'save_wizard_options']);
        $this->action('wp_ajax_wizard_save_booking', [$this->main, 'save_wizard_options']);
        $this->action('wp_ajax_wizard_save_styling', [$this->main, 'save_wizard_options']);
    }

    /**
     * Register Webnus MEC hooks such as activate, deactivate and uninstall hooks
     * @author Webnus <info@webnus.net>
     */
    public function load_hooks()
    {
        register_activation_hook(MEC_ABSPATH . MEC_FILENAME, [$this, 'activate']);
        register_deactivation_hook(MEC_ABSPATH . MEC_FILENAME, [$this, 'deactivate']);
    }

    /**
     * load MEC filters
     * @author Webnus <info@webnus.net>
     */
    public function load_filters()
    {
        // Load MEC Plugin links
        $this->filter('plugin_row_meta', [$this, 'load_plugin_links'], 10, 2);
        $this->filter('plugin_action_links_' . plugin_basename(MEC_DIRNAME . DS . MEC_FILENAME), [$this, 'load_plugin_action_links'], 10, 1);

        // Add MEC rewrite rules
        $this->filter('generate_rewrite_rules', [$this->parser, 'load_rewrites']);
        $this->filter('query_vars', [$this->parser, 'add_query_vars']);

        // Manage MEC templates
        $this->filter('template_include', [$this->parser, 'template'], 99);

        // Fetch Googlemap style JSON
        $this->filter('mec_get_googlemap_style', [$this->main, 'fetch_googlemap_style']);

        // Filter Request
        $this->filter('request', [$this->main, 'filter_request']);

        // Block Editor Category
        if (function_exists('register_block_type')) $this->filter('block_categories_all', [$this->main, 'add_custom_block_cateogry'], 9999);

        // Add Taxonomy etc to filters
        $this->filter('mec_vyear_atts', [$this->main, 'add_search_filters']);
        $this->filter('mec_vmonth_atts', [$this->main, 'add_search_filters']);
        $this->filter('mec_vweek_atts', [$this->main, 'add_search_filters']);
        $this->filter('mec_vday_atts', [$this->main, 'add_search_filters']);
        $this->filter('mec_vfull_atts', [$this->main, 'add_search_filters']);
        $this->filter('mec_vmap_atts', [$this->main, 'add_search_filters']);
        $this->filter('mec_vlist_atts', [$this->main, 'add_search_filters']);
        $this->filter('mec_vgrid_atts', [$this->main, 'add_search_filters']);
        $this->filter('mec_vtimetable_atts', [$this->main, 'add_search_filters']);
        $this->filter('mec_vmasonry_atts', [$this->main, 'add_search_filters']);
        $this->filter('mec_vagenda_atts', [$this->main, 'add_search_filters']);
        $this->filter('mce_buttons', [$this->main, 'add_mce_buttons']);
        $this->filter('mce_external_plugins', [$this->main, 'add_mce_external_plugins']);

        $this->filter('pre_get_document_title', [$this->parser, 'archive_document_title']);
    }

    /**
     * load MEC menus
     * @author Webnus <info@webnus.net>
     */
    public function load_menus()
    {
        add_menu_page(
            __('M.E. Calendar', 'modern-events-calendar-lite'),
            esc_html__('M.E. Calendar', 'modern-events-calendar-lite'),
            apply_filters('mec_menu_cap', 'edit_posts', 'mec-intro'),
            'mec-intro',
            [$this->main, 'dashboard'],
            plugin_dir_url(__FILE__) . '../../assets/img/mec.svg',
            26
        );
    }

    /**
     * load MEC Features
     * @author Webnus <info@webnus.net>
     */
    public function load_features()
    {
        $path = MEC_ABSPATH . 'app' . DS . 'features' . DS;
        $files = $this->folder->files($path, '.php$');

        foreach ($files as $file)
        {
            $name = str_replace('.php', '', $file);

            $class = 'MEC_feature_' . $name;
            MEC::getInstance('app.features.' . $name, $class);

            if (!class_exists($class)) continue;

            $object = new $class();
            $object->init();
        }
    }

    /**
     * Inserting MEC plugin links
     * @param array $links
     * @param string $file
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function load_plugin_links($links, $file)
    {
        if (strpos($file, MEC_DIRNAME) !== false)
        {
            if (!$this->getPRO())
            {
                $upgrade = '<a href="' . esc_url($this->main->get_pro_link()) . '" target="_blank"><b>' . _x('Upgrade to Pro Version', 'plugin link', 'modern-events-calendar-lite') . '</b></a>';
                $links[] = $upgrade;
            }
        }

        return $links;
    }

    /**
     * Load MEC plugin action links
     * @param array $links
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function load_plugin_action_links($links)
    {
        $settings = '<a href="' . esc_url($this->main->add_qs_vars(['page' => 'MEC-settings'], $this->main->URL('admin') . 'admin.php')) . '">' . _x('Settings', 'plugin link', 'modern-events-calendar-lite') . '</a>';
        array_unshift($links, $settings);

        if (!$this->getPRO())
        {
            $upgrade = '<a href="' . esc_url($this->main->get_pro_link()) . '" target="_blank"><b>' . _x('Upgrade', 'plugin link', 'modern-events-calendar-lite') . '</b></a>';
            array_unshift($links, $upgrade);
        }

        return $links;
    }

    public function register_styles_and_scripts()
    {
        // Get Current Screen
        global $current_screen;
        if (!isset($current_screen) && function_exists('get_current_screen')) $current_screen = get_current_screen();

        $backend_js_dependencies = [
            'jquery',
            'wp-color-picker',
            'jquery-ui-datepicker',
        ];

        if (is_a($current_screen, '\WP_Screen') && method_exists($current_screen, 'is_block_editor') and $current_screen->is_block_editor())
        {
            $backend_js_dependencies[] = 'wp-blocks';
        }

        $js_dependencies = [
            'jquery',
        ];

        $scripts = [
            'mec-typekit-script' => $this->main->asset('js/jquery.typewatch.js'),
            'mec-niceselect-script' => $this->main->asset('js/jquery.nice-select.min.js'),
            'mec-select2-script' => $this->main->asset('packages/select2/select2.full.min.js'),
            'mec-lity-script' => $this->main->asset('packages/lity/lity.min.js'),
            'mec-nice-scroll' => $this->main->asset('js/jquery.nicescroll.min.js'),
            'featherlight' => $this->main->asset('packages/featherlight/featherlight.js'),
            'mec-owl-carousel-script' => $this->main->asset('packages/owl-carousel/owl.carousel.min.js'),
            'mec-backend-script' => [
                'src' => $this->main->asset('js/backend.js'),
                'deps' => $backend_js_dependencies,
                'in_footer' => false,
            ],
            'mec-events-script' => $this->main->asset('js/events.js'), /// dep in front 'mec-frontend-script'
            'mec-general-calendar-script' => $this->main->asset('js/mec-general-calendar.js'),
            'mec-tooltip-script' => $this->main->asset('packages/tooltip/tooltip.js'),
            'mec-shuffle-script' => $this->main->asset('js/shuffle.min.js'),
            'mec-frontend-script' => [
                'src' => $this->main->asset('js/frontend.js'),
                'deps' => [
                    'jquery',
                    'mec-tooltip-script',
                ],
            ],
            'mec-colorbrightness-script' => $this->main->asset('packages/colorbrightness/colorbrightness.min.js'),
            'mec-chartjs-script' => $this->main->asset('js/chartjs.min.js'),
            'mec-date-format-script' => $this->main->asset('js/date.format.min.js'),
        ];

//        if (is_plugin_active('mec-single-builder/mec-single-builder.php')) {
//            $scripts['mec-flipcount-script'] = $this->main->asset('js/flipcount.js');
//        } elseif (is_plugin_active('divi-single-builder/divi-single-builder.php') || is_plugin_active('mec-divi-single-builder/divi-single-builder.php')) {
//            $scripts['mec-flipcount-script'] = $this->main->asset('js/flipcount.js');
//        } else {
//            $scripts['mec-flipcount-script'] = $this->main->asset('js/flipcount.js');
//        }

        $scripts['mec-flipcount-script'] = $this->main->asset('js/flipcount.js');

        foreach ($scripts as $script_id => $script)
        {
            $src = is_array($script) ? $script['src'] : $script;
            $deps = is_array($script) && isset($script['deps']) ? $script['deps'] : $js_dependencies;
            $version = $this->main->get_version();
            $in_footer = is_array($script) && isset($script['in_footer']) ? $script['in_footer'] : true;

            wp_register_script($script_id, $src, $deps, $version, $in_footer);
        }

        $backend_css_dependencies = [
            'wp-color-picker',
        ];
        $css_dependencies = [];
        $styles = [
            'mec-select2-style' => $this->main->asset('packages/select2/select2.min.css'),
            'featherlight' => $this->main->asset('packages/featherlight/featherlight.css'),
            'mec-font-icons' => $this->main->asset('css/iconfonts.css'),
            'mec-backend-rtl-style' => $this->main->asset('css/mecrtl.min.css'),
            'mec-backend-style' => [
                'src' => $this->main->asset('css/backend.min.css'),
                'deps' => $backend_css_dependencies,
            ],
            'mec-lity-style' => $this->main->asset('packages/lity/lity.min.css'),
            'mec-owl-carousel-style' => $this->main->asset('packages/owl-carousel/owl.carousel.min.css'),
            'mec-niceselect-style' => $this->main->asset('css/nice-select.min.css'),
            'mec-frontend-style' => $this->main->asset('css/frontend.min.css'),
            'mec-frontend-rtl-style' => $this->main->asset('css/frontend-rtl.min.css'),
            'accessibility' => $this->main->asset('css/a11y.min.css'),
            'mec-tooltip-style' => $this->main->asset('packages/tooltip/tooltip.css'),
            'mec-tooltip-shadow-style' => $this->main->asset('packages/tooltip/tooltipster-sideTip-shadow.min.css'),
            'mec-general-calendar-style' => $this->main->asset('css/mec-general-calendar.css'),
            'mec-google-fonts' => '//fonts.googleapis.com/css?family=Montserrat:400,700|Roboto:100,300,400,700',
            'mec-custom-google-font' => get_option('mec_gfont'),
        ];

        foreach ($styles as $style_id => $style)
        {
            $src = is_array($style) ? $style['src'] : $style;
            $deps = is_array($style) && isset($style['deps']) ? $style['deps'] : $css_dependencies;
            $version = $this->main->get_version();

            wp_register_style($style_id, $src, $deps, $version);
        }

        // Settings
        $settings = $this->main->get_settings();
        wp_localize_script('mec-backend-script', 'mec_admin_localize', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'ajax_nonce' => wp_create_nonce('mec_settings_nonce'),
            'mce_items' => $this->main->mce_get_shortcode_list(),
            'datepicker_format' => (isset($settings['datepicker_format']) and trim($settings['datepicker_format'])) ? trim($settings['datepicker_format']) : 'yy-mm-dd',
        ]);

        if (did_action('elementor/loaded')) $elementor_edit_mode = !\Elementor\Plugin::$instance->editor->is_edit_mode() ? 'no' : 'yes';
        else $elementor_edit_mode = 'no';

        // Settings
        $grecaptcha_key = isset($settings['google_recaptcha_sitekey']) ? trim($settings['google_recaptcha_sitekey']) : '';
        $fes_thankyou_page_time = (isset($settings['fes_thankyou_page_time']) and trim($settings['fes_thankyou_page_time']) != '') ? (int) $settings['fes_thankyou_page_time'] : 2000;

        // Localize Some Strings
        $mecdata = apply_filters('mec_locolize_data', [
            'day' => __('day', 'modern-events-calendar-lite'),
            'days' => __('days', 'modern-events-calendar-lite'),
            'hour' => __('hour', 'modern-events-calendar-lite'),
            'hours' => __('hours', 'modern-events-calendar-lite'),
            'minute' => __('minute', 'modern-events-calendar-lite'),
            'minutes' => __('minutes', 'modern-events-calendar-lite'),
            'second' => __('second', 'modern-events-calendar-lite'),
            'seconds' => __('seconds', 'modern-events-calendar-lite'),
            'next' => __('Next', 'modern-events-calendar-lite'),
            'prev' => __('Prev', 'modern-events-calendar-lite'),
            'elementor_edit_mode' => $elementor_edit_mode,
            'recapcha_key' => $grecaptcha_key,
            'ajax_url' => admin_url('admin-ajax.php'),
            'fes_nonce' => wp_create_nonce('mec_fes_nonce'),
            'fes_thankyou_page_time' => $fes_thankyou_page_time,
            'fes_upload_nonce' => wp_create_nonce('mec_fes_upload_featured_image'),
            'current_year' => date('Y', current_time('timestamp', 0)),
            'current_month' => date('m', current_time('timestamp', 0)),
            'datepicker_format' => (isset($settings['datepicker_format']) and trim($settings['datepicker_format'])) ? trim($settings['datepicker_format']) : 'yy-mm-dd',
        ]);

        // Localize Some Strings
        wp_localize_script('mec-frontend-script', 'mecdata', $mecdata);
    }

    /**
     * Load MEC Backend assets such as CSS or JavaScript files
     * @author Webnus <info@webnus.net>
     */
    public function load_backend_assets()
    {
        if ($this->should_include_assets('backend'))
        {
            // Get Current Screen
            global $current_screen;
            if (!isset($current_screen)) $current_screen = get_current_screen();

            // Styling
            $styling = $this->main->get_styling();

            // Include MEC typekit script file
            wp_enqueue_script('mec-typekit-script');

            //Include the nice-select
            wp_enqueue_script('mec-niceselect-script');

            //Include Select2
            wp_enqueue_script('mec-select2-script');
            wp_enqueue_style('mec-select2-style');

            // Include Lity Lightbox
            wp_enqueue_script('mec-lity-script');

            // Include Nicescroll
            wp_enqueue_script('mec-nice-scroll');

            wp_enqueue_style('featherlight');
            wp_enqueue_script('featherlight');

            // Include MEC Carousel JS libraries
            wp_enqueue_script('mec-owl-carousel-script');

            // Backend Dependencies
            $dependencies = ['jquery', 'wp-color-picker', 'jquery-ui-datepicker'];

            // Add WP Blocks to the dependencies only when needed!
            if (method_exists($current_screen, 'is_block_editor') and $current_screen->is_block_editor()) $dependencies[] = 'wp-blocks';

            // Register New Block Editor
            if (function_exists('register_block_type')) register_block_type('mec/blockeditor', ['editor_script' => 'block.editor']);

            // Include MEC backend script file
            wp_enqueue_script('mec-backend-script');

            // Settings
            $settings = $this->main->get_settings();

            wp_localize_script('mec-backend-script', 'mec_admin_localize', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'ajax_nonce' => wp_create_nonce('mec_settings_nonce'),
                'mce_items' => $this->main->mce_get_shortcode_list(),
                'datepicker_format' => (isset($settings['datepicker_format']) and trim($settings['datepicker_format'])) ? trim($settings['datepicker_format']) : 'yy-mm-dd',
            ]);

            wp_enqueue_script('mec-events-script');

            // Thickbox
            wp_enqueue_media();

            // WP Editor
            wp_enqueue_editor();

            // MEC Icons
            wp_enqueue_style('mec-font-icons');

            // Include "Right to Left" CSS file
            if (is_rtl()) wp_enqueue_style('mec-backend-rtl-style');

            // Include Lity CSS file
            wp_enqueue_style('mec-lity-style');
        }

        // Include MEC backend CSS
        wp_enqueue_style('mec-backend-style');

        if (isset($styling) and isset($styling['accessibility']) && $styling['accessibility']) wp_enqueue_style('mec-backend-accessibility', $this->main->asset('css/a11y-backend.min.css'), $this->main->get_version());
    }

    /**
     * Load MEC frontend assets such as CSS or JavaScript files
     * @author Webnus <info@webnus.net>
     */
    public function load_frontend_assets()
    {
        if ($this->should_include_assets())
        {
            // Styling
            $styling = $this->main->get_styling();

            // Google Fonts Status
            $gfonts_status = !(isset($styling['disable_gfonts']) and $styling['disable_gfonts']);

            // Include WordPress jQuery
            wp_enqueue_script('jquery');

            // Include jQuery date picker
            if (!defined("SHOW_CT_BUILDER")) wp_enqueue_script('jquery-ui-datepicker');

            // Load Isotope
            if (class_exists('ET_Builder_Element')) $this->main->load_isotope_assets();

            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
            if (is_plugin_active('elementor/elementor.php') && class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->preview->is_preview_mode()) $this->main->load_isotope_assets();

            wp_enqueue_script('mec-typekit-script');
            wp_enqueue_script('featherlight');

            // Include Select2
            wp_enqueue_script('mec-select2-script');
            wp_enqueue_style('mec-select2-style');

            // General Calendar
            wp_enqueue_script('mec-general-calendar-script');

            // Include MEC frontend script files
            wp_enqueue_script('mec-tooltip-script');
            wp_enqueue_script('mec-frontend-script');

            wp_enqueue_script('mec-events-script');

            // Include Lity Lightbox
            wp_enqueue_script('mec-lity-script');

            // Include color brightness
            wp_enqueue_script('mec-colorbrightness-script');

            // Include MEC frontend JS libraries
            wp_enqueue_script('mec-owl-carousel-script');

            if (did_action('elementor/loaded')) $elementor_edit_mode = !\Elementor\Plugin::$instance->editor->is_edit_mode() ? 'no' : 'yes';
            else $elementor_edit_mode = 'no';

            // Settings
            $settings = $this->main->get_settings();
            $grecaptcha_key = isset($settings['google_recaptcha_sitekey']) ? trim($settings['google_recaptcha_sitekey']) : '';
            $fes_thankyou_page_time = (isset($settings['fes_thankyou_page_time']) and trim($settings['fes_thankyou_page_time']) != '') ? (int) $settings['fes_thankyou_page_time'] : 2000;

            // Localize Some Strings
            $mecdata = apply_filters('mec_locolize_data', [
                'day' => __('day', 'modern-events-calendar-lite'),
                'days' => __('days', 'modern-events-calendar-lite'),
                'hour' => __('hour', 'modern-events-calendar-lite'),
                'hours' => __('hours', 'modern-events-calendar-lite'),
                'minute' => __('minute', 'modern-events-calendar-lite'),
                'minutes' => __('minutes', 'modern-events-calendar-lite'),
                'second' => __('second', 'modern-events-calendar-lite'),
                'seconds' => __('seconds', 'modern-events-calendar-lite'),
                'next' => __('Next', 'modern-events-calendar-lite'),
                'prev' => __('Prev', 'modern-events-calendar-lite'),
                'elementor_edit_mode' => $elementor_edit_mode,
                'recapcha_key' => $grecaptcha_key,
                'ajax_url' => admin_url('admin-ajax.php'),
                'fes_nonce' => wp_create_nonce('mec_fes_nonce'),
                'fes_thankyou_page_time' => $fes_thankyou_page_time,
                'fes_upload_nonce' => wp_create_nonce('mec_fes_upload_featured_image'),
                'current_year' => date('Y', current_time('timestamp', 0)),
                'current_month' => date('m', current_time('timestamp', 0)),
                'datepicker_format' => (isset($settings['datepicker_format']) and trim($settings['datepicker_format'])) ? trim($settings['datepicker_format']) : 'yy-mm-dd',
            ]);

            // Localize Some Strings
            wp_localize_script('mec-frontend-script', 'mecdata', $mecdata);

            // Include Security Captcha Assets
            $this->getCaptcha()->assets();

            // Include MEC frontend CSS files
            wp_enqueue_style('mec-font-icons');
            if (!is_rtl()) wp_enqueue_style('mec-frontend-style');
            if (isset($styling['accessibility']) && $styling['accessibility']) wp_enqueue_style('accessibility');

            wp_enqueue_style('mec-tooltip-style');
            wp_enqueue_style('mec-tooltip-shadow-style');
            wp_enqueue_style('featherlight', $this->main->asset('packages/featherlight/featherlight.css'));

            // Include "Right to Left" CSS file
            if (is_rtl()) wp_enqueue_style('mec-frontend-rtl-style');

            // Include Google Fonts
            if ($gfonts_status and get_option('mec_dyncss') == 0) wp_enqueue_style('mec-google-fonts');

            // Include Google Font
            if ($gfonts_status and get_option('mec_gfont')) wp_enqueue_style('mec-custom-google-font');

            // Include Lity CSS file
            wp_enqueue_style('mec-lity-style');

            // General Calendar
            wp_enqueue_style('mec-general-calendar-style');
        }
    }

    /**
     * Prints custom styles in the page header
     * @return void
     * @author Webnus <info@webnus.net>
     */
    public function include_styles()
    {
        if ($this->should_include_assets('frontend'))
        {
            // Include Dynamic CSS
            if (get_option('mec_dyncss'))
            {
                echo '<style>' . stripslashes(get_option('mec_dyncss')) . '</style>';
            }

            $styles = $this->main->get_styles();

            // Print custom styles
            if (isset($styles['CSS']) and trim($styles['CSS']) != '')
            {
                $CSS = strip_tags($styles['CSS']);
                echo '<style>' . stripslashes($CSS) . '</style>';
            }
        }
    }

    /**
     * Load MEC widget
     * @author Webnus <info@webnus.net>
     */
    public function load_widgets()
    {
        // register mec side bar
        register_sidebar([
            'id' => 'mec-single-sidebar',
            'name' => esc_html__('MEC Single Sidebar', 'modern-events-calendar-lite'),
            'description' => esc_html__('Custom sidebar for single and modal page of MEC.', 'modern-events-calendar-lite'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>',
        ]);

        // Import MEC Widget Class
        $this->import('app.widgets.MEC');
        $this->import('app.widgets.single');

        register_widget('MEC_MEC_widget');
        register_widget('MEC_single_widget');
    }

    /**
     * Register MEC shortcode in WordPress
     * @author Webnus <info@webnus.net>
     */
    public function load_shortcodes()
    {
        // MEC Render library
        $render = $this->getRender();

        // Events Archive Page
        $this->shortcode('MEC', [$render, 'shortcode']);

        // Event Single Page
        $this->shortcode('MEC_single', [$render, 'vsingle']);

        // MEC Render library
        $book = $this->getBook();

        // Booking Invoice
        $this->shortcode('MEC_invoice_link', [$book, 'invoice_link_shortcode']);
    }

    /**
     * Load dynamic css
     * @author Webnus <info@webnus.net>
     */
    public function mec_dyncss()
    {
        // Import Dynamic CSS codes
        $path = $this->import('app.features.mec.dyncss', true, true);

        ob_start();
        include $path;
        echo ob_get_clean();
    }

    /**
     * Load MEC skins in WordPress
     * @author Webnus <info@webnus.net>
     */
    public function load_skins()
    {
        // Import MEC skins Class
        $this->import('app.libraries.skins');

        $MEC_skins = new MEC_skins();
        $MEC_skins->load();
    }

    /**
     * Register MEC addons in WordPress
     * @author Webnus <info@webnus.net>
     */
    public function load_addons()
    {
        // Import MEC VC addon
        $this->import('app.addons.VC');

        $MEC_addon_VC = new MEC_addon_VC();
        $MEC_addon_VC->init();

        // Import MEC KC addon
        $this->import('app.addons.KC');

        $MEC_addon_KC = new MEC_addon_KC();
        $MEC_addon_KC->init();

        // Import MEC Elementor addon
        $this->import('app.addons.elementor');
        $MEC_addon_elementor = new MEC_addon_elementor();
        $MEC_addon_elementor->init();

        // Import MEC Elementor addon
        $this->import('app.addons.avada');
        $MEC_addon_avada = new MEC_addon_avada();
        $MEC_addon_avada->init();

        // Import MEC Divi addon
        $this->import('app.addons.divi');
        $MEC_addon_divi = new MEC_addon_divi();
        $MEC_addon_divi->init();

        // Import MEC Beaver Builder addon
        $this->import('app.addons.beaver');
        $MEC_addon_beaver = new MEC_addon_beaver();
        $MEC_addon_beaver->init();

        // Import MEC LearnDash addon
        $this->import('app.addons.learndash');
        $MEC_addon_LD = new MEC_addon_learndash();
        $MEC_addon_LD->init();

        // Import MEC PaidMembership Pro addon
        $this->import('app.addons.PMP');
        $MEC_addon_PMP = new MEC_addon_PMP();
        $MEC_addon_PMP->init();

        // Import The Newsletter Plugin addon
        $this->import('app.addons.TNP');
        $MEC_addon_TNP = new MEC_addon_TNP();
        $MEC_addon_TNP->init();

        // Import ACF addon
        $this->import('app.addons.ACF');
        $MEC_addon_ACF = new MEC_addon_ACF();
        $MEC_addon_ACF->init();
    }

    /**
     * Initialize MEC Auto Update Feature
     * @author Webnus <info@webnus.net>
     */
    public function load_auto_update()
    {
        $options = get_option('mec_options');
        $product_name = !empty($options['product_name']) ? esc_html__($options['product_name']) : '';
        $product_id = !empty($options['product_id']) ? esc_html__($options['product_id']) : '';
        $purchase_code = !empty($options['purchase_code']) ? esc_html__($options['purchase_code']) : '';
        $url = urlencode(get_home_url());

        require_once MEC_ABSPATH . 'app/core/puc/plugin-update-checker.php';
        if (!$this->getPRO())
        {
            $MyUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
                add_query_arg(['purchase_code' => '', 'url' => '', 'id' => '', 'category' => 'mec'], MEC_API_UPDATE . '/updates/?action=get_metadata&slug=modern-events-calendar-lite'), //Metadata URL.
                MEC_ABSPATH . 'modern-events-calendar-lite.php', //Full path to the main plugin file.
                'modern-events-calendar-lite', //Plugin slug. Usually it's the same as the name of the directory.
                24
            );
        }
        else
        {
            $MyUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
                add_query_arg(['purchase_code' => $purchase_code, 'url' => $url, 'id' => $product_id, 'category' => 'mec'], MEC_API_UPDATE . '/updates/?action=get_metadata&slug=modern-events-calendar'), //Metadata URL.
                MEC_ABSPATH . 'mec.php', //Full path to the main plugin file.
                'mec', //Plugin slug. Usually it's the same as the name of the directory.
                24
            );
        }

        $name = $this->getPRO() ? 'mec' : 'modern-events-calendar-lite';
        add_filter('puc_request_info_result-' . $name, function ($info)
        {
            if (!$info) return;

            unset($info->sections['installation']);
            unset($info->sections['faq']);
            unset($info->sections['screenshots']);
            unset($info->sections['wordpress_event_calendar']);
            unset($info->sections['best_wordpress_event_management_plugin']);
            unset($info->sections['new_designed_beautiful_event_view_layouts:']);
            unset($info->sections['covid-19_(coronavirus)']);
            unset($info->sections['10_best_event_calendar_plugins_and_themes_for_wordpress_2020']);
            unset($info->sections['experts_opinions']);
            unset($info->sections['some_new_features']);
            unset($info->sections['user_reviews']);
            unset($info->sections['convert_your_events_in_a_few_seconds']);
            unset($info->sections['virtual_events_addon']);
            unset($info->sections['main_features']);
            unset($info->sections['integration']);
            unset($info->sections['key_features']);
            unset($info->sections['addons']);
            unset($info->sections['screenshots']);
            unset($info->sections['helpful_documentation']);
            unset($info->sections['developers']);
            unset($info->sections['frequently_asked_questions']);

            return $info;
        });
    }

    /**
     * Add strings (CSS, JavaScript, etc.) to website sections such as footer etc.
     * @param string $key
     * @param string|closure $string
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function params($key, $string)
    {
        $key = (string) $key;

        if ($string instanceof Closure)
        {
            ob_start();
            call_user_func($string);
            $string = ob_get_clean();
        }

        $string = (string) $string;

        // No Key or No String
        if (trim($string) == '' or trim($key) == '') return false;

        // Register the key for removing PHP notices
        if (!isset(self::$params[$key])) self::$params[$key] = [];

        // Add it to the MEC params
        array_push(self::$params[$key], $string);
        return true;
    }

    public function printOnAjaxOrFooter($string)
    {
        if ($string instanceof Closure)
        {
            ob_start();
            call_user_func($string);
            $string = ob_get_clean();
        }

        if (defined('DOING_AJAX') && DOING_AJAX) echo $string;
        else $this->params('footer', $string);
    }

    /**
     * Insert MEC assets into the website footer
     * @return void
     * @author Webnus <info@webnus.net>
     */
    public function load_footer()
    {
        if (!isset(self::$params['footer']) or (isset(self::$params['footer']) and !count(self::$params['footer']))) return;

        // Remove duplicate strings
        $strings = array_unique(self::$params['footer']);

        // Print the assets in the footer
        foreach ($strings as $string) echo PHP_EOL . $string . PHP_EOL;
    }

    /**
     * Add MEC actions to WordPress
     * @param string $hook
     * @param string|array|Closure $function
     * @param int $priority
     * @param int $accepted_args
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function action($hook, $function, $priority = 10, $accepted_args = 1)
    {
        // Check Parameters
        if (!trim($hook) or !$function) return false;

        // Add it to WordPress actions
        return add_action($hook, $function, $priority, $accepted_args);
    }

    /**
     * Add MEC filters to WordPress filters
     * @param string $tag
     * @param string|array $function
     * @param int $priority
     * @param int $accepted_args
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function filter($tag, $function, $priority = 10, $accepted_args = 1)
    {
        // Check Parameters
        if (!trim($tag) or !$function) return false;

        // Add it to WordPress filters
        return add_filter($tag, $function, $priority, $accepted_args);
    }

    /**
     * Add MEC shortcodes to WordPress shortcodes
     * @param string $shortcode
     * @param string|array $function
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function shortcode($shortcode, $function)
    {
        // Check Parameters
        if (!trim($shortcode) or !$function) return false;

        // Add it to WordPress shortcodes
        add_shortcode($shortcode, $function);
        return true;
    }

    /**
     * Runs on plugin activation
     * @param boolean $network
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function activate($network = false)
    {
        // Redirect user to MEC Dashboard
        add_option('mec_activation_redirect', true);

        // Uninstall Hook
        register_uninstall_hook(MEC_ABSPATH . MEC_FILENAME, ['MEC_factory', 'uninstall']);

        $current_blog_id = get_current_blog_id();

        // Plugin activated only for one blog
        if (!function_exists('is_multisite') or (function_exists('is_multisite') and !is_multisite())) $network = false;
        if (!$network)
        {
            // Refresh WordPress rewrite rules
            $this->main->flush_rewrite_rules();

            return $this->install($current_blog_id);
        }

        // Plugin activated for all blogs
        $blogs = $this->db->select("SELECT `blog_id` FROM `#__blogs`", 'loadColumn');
        foreach ($blogs as $blog_id)
        {
            switch_to_blog($blog_id);
            $this->install($blog_id);
        }

        switch_to_blog($current_blog_id);

        // Refresh WordPress rewrite rules
        $this->main->flush_rewrite_rules();
        return true;
    }

    /**
     * Runs on plugin deactivation
     * @param boolean $network
     * @return void
     * @author Webnus <info@webnus.net>
     */
    public function deactivate($network = false)
    {
        $this->main->flush_rewrite_rules();

        // Clear Scheduler Cronjob
        wp_clear_scheduled_hook('mec_scheduler');
        wp_clear_scheduled_hook('mec_syncScheduler');
    }

    /**
     * Runs on plugin uninstallation
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public static function uninstall()
    {
        // Main Object
        $main = MEC::getInstance('app.libraries.main');

        // Database Object
        $db = MEC::getInstance('app.libraries.db');

        // Refresh WordPress rewrite rules
        $main->flush_rewrite_rules();

        // Getting current blog
        $current_blog_id = get_current_blog_id();

        if (!function_exists('is_multisite') or (function_exists('is_multisite') and !is_multisite())) return self::purge($current_blog_id);

        // Plugin activated for all blogs
        $blogs = $db->select("SELECT `blog_id` FROM `#__blogs`", 'loadColumn');
        foreach ($blogs as $blog_id)
        {
            switch_to_blog($blog_id);
            self::purge($blog_id);
        }

        // Switch back to current blog
        switch_to_blog($current_blog_id);
        return true;
    }

    /**
     * Install the plugin on s certain blog
     * @param int $blog_id
     * @author Webnus <info@webnus.net>
     */
    public function install($blog_id = 1)
    {
        // Plugin installed before
        if (get_option('mec_installed', 0))
        {
            // Create mec_events table if it's removed for any reason
            $this->main->create_mec_tables();

            return;
        }

        // Run Queries
        $query_file = MEC_ABSPATH . 'assets' . DS . 'sql' . DS . 'install.sql';
        if ($this->file->exists($query_file))
        {
            $queries = $this->file->read($query_file);
            $sqls = explode(';', $queries);

            foreach ($sqls as $sql)
            {
                $sql = trim($sql, '; ');
                if (trim($sql) == '') continue;

                $sql .= ';';

                try
                {
                    $this->db->q($sql);
                }
                catch (Exception $e)
                {
                }
            }
        }

        // Default Options
        $options = [
            'settings' => [
                'multiple_day_show_method' => 'first_day_listgrid',
                'google_maps_status' => 1,
                'export_module_status' => 1,
                'sn' => ['googlecal' => 1, 'ical' => 1, 'facebook' => 1, 'gplus' => 1, 'twitter' => 1, 'linkedin' => 1, 'email' => 1],
                'countdown_status' => 1,
                'social_network_status' => 1,
                'default_skin_archive' => 'full_calendar',
            ],
            'styles' => ['CSS' => ''],
            'gateways' => [1 => ['status' => 1]],
            'notifications' => [
                'booking_notification' => [
                    'subject' => 'Your booking is received.',
                    'recipients' => '',
                    'content' => "Hello %%name%%,

                    Your booking is received. We will check and confirm your booking as soon as possible.
                    Thanks for your patience.

                    Regards,
                    %%blog_name%%",
                ],
                'email_verification' => [
                    'subject' => 'Please verify your booking.',
                    'recipients' => '',
                    'content' => "Hi %%name%%,

                    Please verify your booking by clicking on following link:

                    %%verification_link%%

                    Regards,
                    %%blog_name%%",
                ],
                'booking_confirmation' => [
                    'subject' => 'Your booking is confirmed.',
                    'recipients' => '',
                    'content' => "Hi %%name%%,

                    Your booking is confirmed. You should be available at %%book_date%% in %%event_location_address%%.

                    You can contact to event organizer by calling %%event_organizer_tel%%.

                    Regards,
                    %%blog_name%%",
                ],
                'cancellation_notification' => [
                    'status' => '0',
                    'subject' => 'Your booking is canceled.',
                    'recipients' => '',
                    'send_to_admin' => '1',
                    'send_to_organizer' => '0',
                    'send_to_user' => '0',
                    'content' => "Hi %%name%%,

                    For your information, your booking for %%event_title%% at %%book_date%% is canceled.

                    Regards,
                    %%blog_name%%",
                ],
                'admin_notification' => [
                    'subject' => 'A new booking is received.',
                    'recipients' => '',
                    'content' => "Dear Admin,

                    A new booking is received. Please check and confirm it as soon as possible.

                    %%admin_link%%

                    %%attendees_full_info%%

                    Regards,
                    %%blog_name%%",
                ],
                'new_event' => [
                    'status' => '1',
                    'subject' => 'A new event is added.',
                    'recipients' => '',
                    'content' => "Hello,

                    A new event just added. The event title is %%event_title%% and its status is %%event_status%%.
                    The new event may need to be published. Please use this link for managing your website events: %%admin_link%%

                    Regards,
                    %%blog_name%%",
                ],
                'user_event_publishing' => [
                    'status' => '1',
                    'subject' => 'Your event gets published',
                    'recipients' => '',
                    'content' => "Hello %%name%%,

                    Your event gets published. You can check it below:

                    <a href=\"%%event_link%%\">%%event_title%%</a>

                    Regards,
                    %%blog_name%%",
                ],
                'event_soldout' => [
                    'status' => '0',
                    'subject' => 'Your event is soldout!',
                    'recipients' => '',
                    'send_to_admin' => '1',
                    'send_to_organizer' => '1',
                    'content' => "Hi %%name%%,

                    For your information, your %%event_title%% event at %%book_date%% is soldout.

                    Regards,
                    %%blog_name%%",
                ],
                'booking_rejection' => [
                    'status' => '0',
                    'subject' => 'Your booking got rejected!',
                    'recipients' => '',
                    'send_to_admin' => '0',
                    'send_to_organizer' => '1',
                    'send_to_user' => '1',
                    'content' => "Hi %%name%%,

                    For your information, your booking for %%event_title%% at %%book_date%% is rejected.

                    Regards,
                    %%blog_name%%",
                ],
            ],
        ];

        add_option('mec_options', $options);

        // Mark this blog as installed
        update_option('mec_installed', 1);

        // Set the version into the Database
        update_option('mec_version', $this->main->get_version());

        // MEC Capabilities
        $role = get_role('administrator');
        if ($role)
        {
            $role->add_cap('mec_bookings', true);
            $role->add_cap('mec_add_booking', true);
            $role->add_cap('mec_coupons', true);
            $role->add_cap('mec_report', true);
            $role->add_cap('mec_import_export', true);
            $role->add_cap('mec_settings', true);
            $role->add_cap('mec_shortcodes', true);
        }
    }

    /**
     * Add cron jobs
     * @author Webnus <info@webnus.net>
     */
    public function mec_add_cron_jobs()
    {
        // Scheduler Cron job
        if (!wp_next_scheduled('mec_scheduler')) wp_schedule_event(time(), 'hourly', 'mec_scheduler');
        if (!wp_next_scheduled('mec_syncScheduler')) wp_schedule_event(time(), 'daily', 'mec_syncScheduler');
        if (!wp_next_scheduled('mec_maintenance')) wp_schedule_event(time(), 'daily', 'mec_maintenance');
    }

    /**
     * Remove MEC from a blog
     * @param int $blog_id
     * @author Webnus <info@webnus.net>
     */
    public static function purge($blog_id = 1)
    {
        // Database Object
        $main = MEC::getInstance('app.libraries.main');

        // Settings
        $settings = $main->get_settings();

        // Purge data on uninstall
        if (isset($settings['remove_data_on_uninstall']) and $settings['remove_data_on_uninstall'])
        {
            // Database Object
            $db = MEC::getInstance('app.libraries.db');

            // Drop Tables
            $db->q("DROP TABLE IF EXISTS `#__mec_events`");
            $db->q("DROP TABLE IF EXISTS `#__mec_dates`");
            $db->q("DROP TABLE IF EXISTS `#__mec_users`");
            $db->q("DROP TABLE IF EXISTS `#__mec_occurrences`");

            // Removing MEC posts and postmeta data
            $posts = $db->select("SELECT ID FROM `#__posts` WHERE `post_type`='mec-events' OR `post_type`='mec_calendars' OR `post_type`='mec-books'", 'loadAssocList');
            if (is_array($posts) and count($posts))
            {
                $post_ids = $meta_ids = '';

                $remove_post_sql = "DELETE FROM `#__posts` WHERE";
                $remove_post_meta_sql = "DELETE FROM `#__postmeta` WHERE";

                foreach ($posts as $post)
                {
                    if (isset($post['ID']))
                    {
                        $meta_ids .= ' `post_id`=' . $post['ID'] . ' OR ';
                        $post_ids .= ' `ID`=' . $post['ID'] . ' OR ';
                    }
                }

                $remove_post_sql .= substr($post_ids, 0, -4);
                $remove_post_meta_sql .= substr($meta_ids, 0, -4);

                $db->q($remove_post_sql);
                $db->q($remove_post_meta_sql);
            }

            // Removing all MEC taxonomy terms
            $terms = $db->select("SELECT #__term_taxonomy.`term_id`, #__term_taxonomy.`taxonomy` FROM `#__terms` INNER JOIN `#__term_taxonomy` ON #__terms.`term_id` = #__term_taxonomy.`term_id` WHERE #__term_taxonomy.`taxonomy` = 'mec_category' OR #__term_taxonomy.`taxonomy` = 'mec_label' OR #__term_taxonomy.`taxonomy` = 'mec_location' OR #__term_taxonomy.`taxonomy` = 'mec_organizer' OR #__term_taxonomy.`taxonomy` = 'mec_speaker' OR #__term_taxonomy.`taxonomy` = 'mec_coupon'", 'loadAssocList');
            foreach ($terms as $term)
            {
                if (isset($term['term_id']) and isset($term['taxonomy']))
                {
                    wp_delete_term((int) $term['term_id'], trim($term['taxonomy']));
                }
            }

            // MEC Deleted
            delete_option('mec_installed');
            delete_option('mec_options');
            delete_option('mec_version');
            delete_option('widget_mec_mec_widget');
            delete_option('widget_mec_single_widget');
            delete_option('mec_gfont');
            delete_option('mec_dyncss');
            delete_option('mec_custom_msg_display_option');
            delete_option('mec_custom_msg_2_display_option');
            delete_option('mec_custom_msg_close_option');
            delete_option('mec_custom_msg_2_close_option');
            delete_option('mec_category_children');
        }
    }

    /**
     * Add a body class for active theme
     * @return int $class
     * @author Webnus <info@webnus.net>
     */
    public function mec_active_theme_body_class($classes)
    {
        $class = 'mec-theme-' . get_template();

        if (is_array($classes))
        {
            $classes[] = $class;
        }
        else
        {
            $classes .= ' ' . $class . ' ';
        }
        return $classes;
    }

    /**
     * Remove MEC from a blog
     * @param $dark
     * @return int $dark
     * @author Webnus <info@webnus.net>
     */
    public function mec_body_class($dark)
    {
        $styling = $this->main->get_styling();

        $dark_mode = $styling['dark_mode'] ?? '';
        if (!empty($dark_mode) and $dark_mode == 1) $dark[] = 'mec-dark-mode';

        return $dark;
    }

    /**
     * Remove MEC from a blog
     * @param $darkadmin
     * @return int $darkadmin
     * @author Webnus <info@webnus.net>
     */
    public function mec_admin_body_class($darkadmin)
    {
        $styling = $this->main->get_styling();

        $darkadmin_mode = $styling['dark_mode'] ?? '';
        if ($darkadmin_mode == 1) $darkadmin = 'mec-admin-dark-mode';

        return $darkadmin;
    }

    public function should_include_assets($client = 'frontend')
    {
        if ($client == 'frontend') return apply_filters('mec_include_frontend_assets', true);
        else
        {
            // Current Screen
            $screen = get_current_screen();

            $base = $screen->base;
            $page = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : '';
            $post_type = $screen->post_type;
            $taxonomy = $screen->taxonomy;

            // It's one of MEC taxonomy pages
            if (trim($taxonomy) and in_array($taxonomy, [
                    apply_filters('mec_taxonomy_tag', ''),
                    'mec_category',
                    'mec_label',
                    'mec_location',
                    'mec_organizer',
                    'mec_speaker',
                    'mec_coupon',
                ])) return true;

            // It's one of MEC post type pages
            if (trim($post_type) and in_array($post_type, [
                    $this->main->get_main_post_type(),
                    'mec_calendars',
                    $this->main->get_book_post_type(),
                ])) return true;

            // It's Block Editor
            if (method_exists($screen, 'is_block_editor') and $screen->is_block_editor()) return true;

            // It's one of MEC pages or the pages that MEC should work fine
            if ((trim($base) and in_array($base, [
                        'toplevel_page_mec-intro',
                        'm-e-calendar_page_MEC-settings',
                        'm-e-calendar_page_MEC-addons',
                        'm-e-calendar_page_MEC-report',
                        'm-e-calendar_page_MEC-ix',
                        'm-e-calendar_page_MEC-support',
                        'm-e-calendar_page_MEC-wizard',
                        'm-e-calendar_page_MEC-go-pro',
                        'widgets',
                    ])) or (trim($page) and in_array($page, [
                        'mec-intro',
                        'MEC-settings',
                        'MEC-addons',
                        'MEC-report',
                        'MEC-ix',
                        'MEC-support',
                        'MEC-wizard',
                        'MEC-go-pro',
                        'mec-advanced-report',
                    ]))) return true;

                    // It's the main dashboard screen (index.php)
                    if ($screen->base === 'dashboard') return true;

            return apply_filters('mec_include_backend_assets', false);
        }
    }

    function mecShowUpgradeNotification($currentPluginMetadata, $newPluginMetadata)
    {
        // check "upgrade_notice"

        ?>
        <div class="mec-update-warning" style="margin-bottom: 5px; max-width: 1000px;">
            <strong><?php echo esc_html__('Notice:', 'modern-events-calendar-lite'); ?></strong>
            <?php echo esc_html__('If you are unable to auto update, please check this article:  ', 'modern-events-calendar-lite'); ?>
            <a href="https://webnus.net/dox/modern-events-calendar/manual-update/"
               target="_blank"><?php echo esc_html__('How to manually update', 'modern-events-calendar-lite'); ?></a>
        </div>
        <?php

    }
}
