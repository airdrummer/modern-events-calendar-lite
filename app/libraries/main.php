<?php

/** no direct access **/
defined('MECEXEC') or die();

use ICal\ICal;

/**
 * Webnus MEC main class.
 * @author Webnus <info@webnus.net>
 */
class MEC_main extends MEC_base
{
    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
    }

    /**
     * Returns the archive URL of events for provided skin
     * @param string $skin
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function archive_URL($skin)
    {
        return $this->URL('site') . $this->get_main_slug() . '/' . $skin . '/';
    }

    public function get_waiting_fields()
    {
        $options = get_option('mec_options');
        return isset($options['waiting_fields']) ? $options['waiting_fields'] : [];
    }

    /**
     * Returns full current URL of WordPress
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_full_url()
    {
        // Check protocol
        $page_url = 'http';
        if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') $page_url .= 's';

        // Get domain
        $site_domain = (isset($_SERVER['HTTP_HOST']) and trim($_SERVER['HTTP_HOST']) != '') ? sanitize_text_field($_SERVER['HTTP_HOST']) : sanitize_text_field($_SERVER['SERVER_NAME']);

        $page_url .= '://';
        $page_url .= $site_domain . $_SERVER['REQUEST_URI'];

        // Return full URL
        return $page_url;
    }

    /**
     * Get domain of a certain URL
     * @param string $url
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_domain($url = null)
    {
        // Get current URL
        if (is_null($url)) $url = $this->get_full_url();

        $url = str_replace('http://', '', $url);
        $url = str_replace('https://', '', $url);
        $url = str_replace('ftp://', '', $url);
        $url = str_replace('svn://', '', $url);
        $url = str_replace('www.', '', $url);

        $ex = explode('/', $url);
        $ex2 = explode('?', $ex[0]);

        return $ex2[0];
    }

    /**
     * Remove query string from the URL
     * @param string $key
     * @param string $url
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function remove_qs_var($key, $url = '')
    {
        if (trim($url) == '') $url = $this->get_full_url();

        $url = preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
        return substr($url, 0, -1);
    }

    /**
     * Add query string to the URL
     * @param string $key
     * @param string $value
     * @param string $url
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function add_qs_var($key, $value, $url = '')
    {
        if (trim($url) == '') $url = $this->get_full_url();

        $url = preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
        $url = substr($url, 0, -1);

        if (strpos($url, '?') === false)
            return $url . '?' . $key . '=' . $value;
        else
            return $url . '&' . $key . '=' . $value;
    }

    /**
     * Add multiple query strings to the URL
     * @param array $vars
     * @param string $url
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function add_qs_vars($vars, $url = '')
    {
        if (trim($url) == '') $url = $this->get_full_url();

        foreach ($vars as $key => $value) $url = $this->add_qs_var($key, $value, $url);
        return $url;
    }

    /**
     * Returns WordPress authors
     * @param array $args
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_authors($args = [])
    {
        return get_users($args);
    }

    /**
     * Returns full URL of an asset
     * @param string $asset
     * @param boolean $override
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function asset($asset, $override = true)
    {
        $url = $this->URL('MEC') . 'assets/' . $asset;

        if ($override)
        {
            // Search the file in the main theme
            $theme_path = get_template_directory() . DS . 'webnus' . DS . MEC_DIRNAME . DS . 'assets' . DS . $asset;

            /**
             * If overridden file exists on the main theme, then use it instead of normal file
             * For example you can override /path/to/plugin/assets/js/frontend.js file in your theme by adding a file into the /path/to/theme/webnus/modern-events-calendar/assets/js/frontend.js
             */
            if (file_exists($theme_path)) $url = get_template_directory_uri() . '/webnus/' . MEC_DIRNAME . '/assets/' . $asset;

            // If the theme is a child theme then search the file in child theme
            if (get_template_directory() != get_stylesheet_directory())
            {
                // Child theme overridden file
                $child_theme_path = get_stylesheet_directory() . DS . 'webnus' . DS . MEC_DIRNAME . DS . 'assets' . DS . $asset;

                /**
                 * If overridden file exists on the child theme, then use it instead of normal or main theme file
                 * For example you can override /path/to/plugin/assets/js/frontend.js file in your theme by adding a file into the /path/to/child/theme/webnus/modern-events-calendar/assets/js/frontend.js
                 */
                if (file_exists($child_theme_path)) $url = get_stylesheet_directory_uri() . '/webnus/' . MEC_DIRNAME . '/assets/' . $asset;
            }
        }

        return $url;
    }

    public function svg($icon, $override = true)
    {
        $title = sprintf(__('%s icon', 'modern-events-calendar-lite'), ucfirst(str_replace('-', ' ', $icon)));

        return '<img class="mec-svg-icon" src="' . esc_url($this->asset('img/svg/' . $icon . '.svg', $override)) . '" alt="' . esc_attr($title) . '" title="' . esc_attr($title) . '">';
    }

    /**
     * Returns URL of WordPress items such as site, admin, plugins, MEC plugin etc.
     * @param string $type
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function URL($type = 'site')
    {
        // Make it lowercase
        $type = strtolower($type);

        // Frontend
        if (in_array($type, ['frontend', 'site'])) $url = home_url() . '/';
        // Backend
        else if (in_array($type, ['backend', 'admin'])) $url = admin_url();
        // WordPress' Content directory URL
        else if ($type == 'content') $url = content_url() . '/';
        // WordPress' plugins directory URL
        else if ($type == 'plugin') $url = plugins_url() . '/';
        // WordPress include directory URL
        else if ($type == 'include') $url = includes_url();
        // Webnus MEC plugin URL
        else
        {
            // If plugin installed regularly on plugins directory
            if (!defined('MEC_IN_THEME')) $url = plugins_url() . '/' . MEC_DIRNAME . '/';
            // If plugin embedded into one theme
            else $url = get_template_directory_uri() . '/plugins/' . MEC_DIRNAME . '/';
        }

        return $url;
    }

    /**
     * Returns plugin absolute path
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_plugin_path()
    {
        return MEC_ABSPATH;
    }

    /**
     * Returns a WordPress option
     * @param string $option
     * @param mixed $default
     * @return mixed
     * @author Webnus <info@webnus.net>
     */
    public function get_option($option, $default = null)
    {
        return get_option($option, $default);
    }

    /**
     * Returns WordPress categories based on arguments
     * @param array $args
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_categories($args = [])
    {
        return get_categories($args);
    }

    /**
     * Returns WordPress tags based on arguments
     * @param array $args
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_tags($args = [])
    {
        return get_tags($args);
    }

    /**
     * Convert location string to latitude and longitude
     * @param string $address
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_lat_lng($address)
    {
        $address = urlencode($address);
        if (!trim($address)) return [0, 0];

        // MEC Settings
        $settings = $this->get_settings();

        $url1 = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $address . ((isset($settings['google_maps_api_key']) and trim($settings['google_maps_api_key']) != '') ? '&key=' . $settings['google_maps_api_key'] : '');
        $url2 = 'http://www.datasciencetoolkit.org/maps/api/geocode/json?sensor=false&address=' . $address;

        // Get Latitide and Longitude by First URL
        $JSON = wp_remote_retrieve_body(wp_remote_get($url1, [
            'body' => null,
            'timeout' => '10',
            'redirection' => '10',
        ]));

        $data = json_decode($JSON, true);

        $location_point = isset($data['results'][0]) ? $data['results'][0]['geometry']['location'] : [];
        if ((isset($location_point['lat']) and $location_point['lat']) and (isset($location_point['lng']) and $location_point['lng']))
        {
            return [$location_point['lat'], $location_point['lng']];
        }

        // Get Latitide and Longitude by Second URL
        $JSON = wp_remote_retrieve_body(wp_remote_get($url2, [
            'body' => null,
            'timeout' => '10',
            'redirection' => '10',
        ]));

        $data = json_decode($JSON, true);

        $location_point = isset($data['results'][0]) ? $data['results'][0]['geometry']['location'] : [];
        if ((isset($location_point['lat']) and $location_point['lat']) and (isset($location_point['lng']) and $location_point['lng']))
        {
            return [$location_point['lat'], $location_point['lng']];
        }

        return [0, 0];
    }

    /**
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_default_label_color()
    {
        return apply_filters('mec_default_label_color', '#fefefe');
    }

    /**
     * @param mixed $event
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_post_content($event)
    {
        $event_id = is_object($event) ? $event->data->ID : $event;
        $post = get_post($event_id);

        if (!$post || is_wp_error($post) || !isset($post->post_content) || !is_string($post->post_content))
        {
            return '';
        }

        $content = str_replace('[MEC ', '', $post->post_content);
        $content = apply_filters('the_content', $content ?: '');

        return str_replace(']]>', ']]&gt;', do_shortcode($content));
    }

    /**
     * @param int $post_id
     * @param boolean $skip
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_post_meta($post_id, $skip = false)
    {
        // Cache
        $cache = $this->getCache();

        // Return From Cache
        return $cache->rememberOnce('meta-' . $post_id . '-' . ($skip ? 1 : 0), function () use ($post_id, $skip)
        {
            $raw_data = get_post_meta($post_id, '', true);
            $data = [];

            // Invalid Raw Data
            if (!is_array($raw_data)) return $data;

            foreach ($raw_data as $key => $val)
            {
                if ($skip and strpos($key, 'mec') === false and strpos($key, 'event') === false) continue;
                $data[$key] = isset($val[0]) ? (!is_serialized($val[0]) ? $val[0] : unserialize($val[0])) : null;
            }

            return $data;
        });
    }

    /**
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_skins()
    {
        $skins = [
            'list' => __('List View', 'modern-events-calendar-lite'),
            'grid' => __('Grid View', 'modern-events-calendar-lite'),
            'agenda' => __('Agenda View', 'modern-events-calendar-lite'),
            'full_calendar' => __('Full Calendar', 'modern-events-calendar-lite'),
            'yearly_view' => __('Yearly View', 'modern-events-calendar-lite'),
            'monthly_view' => __('Calendar/Monthly View', 'modern-events-calendar-lite'),
            'daily_view' => __('Daily View', 'modern-events-calendar-lite'),
            'weekly_view' => __('Weekly View', 'modern-events-calendar-lite'),
            'timetable' => __('Timetable View', 'modern-events-calendar-lite'),
            'masonry' => __('Masonry View', 'modern-events-calendar-lite'),
            'map' => __('Map View', 'modern-events-calendar-lite'),
            'cover' => __('Cover View', 'modern-events-calendar-lite'),
            'countdown' => __('Countdown View', 'modern-events-calendar-lite'),
            'available_spot' => __('Available Spot', 'modern-events-calendar-lite'),
            'carousel' => __('Carousel View', 'modern-events-calendar-lite'),
            'slider' => __('Slider View', 'modern-events-calendar-lite'),
            'timeline' => __('Timeline View', 'modern-events-calendar-lite'),
            'tile' => __('Tile View', 'modern-events-calendar-lite'),
            'general_calendar' => __('General Calendar', 'modern-events-calendar-lite'),
        ];

        return apply_filters('mec_calendar_skins', $skins);
    }

    public function get_months_labels()
    {
        $labels = [
            1 => date_i18n('F', strtotime(date('Y') . '-01-01')),
            2 => date_i18n('F', strtotime(date('Y') . '-02-01')),
            3 => date_i18n('F', strtotime(date('Y') . '-03-01')),
            4 => date_i18n('F', strtotime(date('Y') . '-04-01')),
            5 => date_i18n('F', strtotime(date('Y') . '-05-01')),
            6 => date_i18n('F', strtotime(date('Y') . '-06-01')),
            7 => date_i18n('F', strtotime(date('Y') . '-07-01')),
            8 => date_i18n('F', strtotime(date('Y') . '-08-01')),
            9 => date_i18n('F', strtotime(date('Y') . '-09-01')),
            10 => date_i18n('F', strtotime(date('Y') . '-10-01')),
            11 => date_i18n('F', strtotime(date('Y') . '-11-01')),
            12 => date_i18n('F', strtotime(date('Y') . '-12-01')),
        ];


        return apply_filters('mec_months_labels', $labels);
    }

    /**
     * Returns weekday labels
     * @param integer $week_start
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_weekday_labels($week_start = null)
    {
        if (is_null($week_start)) $week_start = $this->get_first_day_of_week();

        /**
         * Please don't change it to translate-able strings
         */
        $raw = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $labels = array_slice($raw, $week_start);
        $rest = array_slice($raw, 0, $week_start);

        foreach ($rest as $label) $labels[] = $label;

        return apply_filters('mec_weekday_labels', $labels);
    }

    /**
     * Returns abbr weekday labels
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_weekday_abbr_labels()
    {
        $week_start = $this->get_first_day_of_week();
        $raw = [
            $this->m('weekdays_su', esc_html__('SU', 'modern-events-calendar-lite')),
            $this->m('weekdays_mo', esc_html__('MO', 'modern-events-calendar-lite')),
            $this->m('weekdays_tu', esc_html__('TU', 'modern-events-calendar-lite')),
            $this->m('weekdays_we', esc_html__('WE', 'modern-events-calendar-lite')),
            $this->m('weekdays_th', esc_html__('TH', 'modern-events-calendar-lite')),
            $this->m('weekdays_fr', esc_html__('FR', 'modern-events-calendar-lite')),
            $this->m('weekdays_sa', esc_html__('SA', 'modern-events-calendar-lite')),
        ];

        $labels = array_slice($raw, $week_start);
        $rest = array_slice($raw, 0, $week_start);

        foreach ($rest as $label) $labels[] = $label;

        return apply_filters('mec_weekday_abbr_labels', $labels);
    }

    /**
     * Returns translatable weekday labels
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_weekday_i18n_labels()
    {
        $week_start = $this->get_first_day_of_week();
        $raw = [[7, esc_html__('Sunday', 'modern-events-calendar-lite')], [1, esc_html__('Monday', 'modern-events-calendar-lite')], [2, esc_html__('Tuesday', 'modern-events-calendar-lite')], [3, esc_html__('Wednesday', 'modern-events-calendar-lite')], [4, esc_html__('Thursday', 'modern-events-calendar-lite')], [5, esc_html__('Friday', 'modern-events-calendar-lite')], [6, esc_html__('Saturday', 'modern-events-calendar-lite')]];

        $labels = array_slice($raw, $week_start);
        $rest = array_slice($raw, 0, $week_start);

        foreach ($rest as $label) $labels[] = $label;

        return apply_filters('mec_weekday_i18n_labels', $labels);
    }

    /**
     * Flush WordPress rewrite rules
     * @author Webnus <info@webnus.net>
     */
    public function flush_rewrite_rules()
    {
        // Register Events Post Type
        $MEC_events = MEC::getInstance('app.features.events', 'MEC_feature_events');
        $MEC_events->register_post_type();

        flush_rewrite_rules();
    }

    /**
     * Get single slug of MEC
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_single_slug()
    {
        $settings = $this->get_settings();
        $slug = (isset($settings['single_slug']) and trim($settings['single_slug']) != '') ? $settings['single_slug'] : 'event';

        return strtolower($slug);
    }

    /**
     * Returns main slug of MEC
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_main_slug()
    {
        $settings = $this->get_settings();
        $slug = (isset($settings['slug']) and trim($settings['slug']) != '') ? $settings['slug'] : 'events';

        return strtolower($slug);
    }

    /**
     * Returns category slug of MEC
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_category_slug()
    {
        $settings = $this->get_settings();
        $slug = (isset($settings['category_slug']) and trim($settings['category_slug']) != '') ? $settings['category_slug'] : 'mec-category';

        return strtolower($slug);
    }

    /**
     * Get archive page title
     * @param bool $meta
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_archive_title($meta = true)
    {
        $settings = $this->get_settings();
        $archive_title = (isset($settings['archive_title']) and trim($settings['archive_title']) != '') ? $settings['archive_title'] : 'Events';

        // Add Blog Name
        if ($meta and apply_filters('mec_archive_title_add_blog_name', true)) $archive_title .= ' - ' . get_bloginfo('name');

        return apply_filters('mec_archive_title', $archive_title);
    }

    public function get_archive_url()
    {
        $archive_link = get_post_type_archive_link($this->get_main_post_type());

        // Archive is disabled
        if ($archive_link === false)
        {
            $archive_page = get_page_by_path('events2');
            if ($archive_page) $archive_link = get_permalink($archive_page);
        }

        return $archive_link;
    }

    /**
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_archive_thumbnail()
    {
        return apply_filters('mec_archive_thumbnail', '');
    }

    /**
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_single_thumbnail()
    {
        return apply_filters('mec_single_thumbnail', '');
    }

    /**
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_main_post_type()
    {
        return apply_filters('mec_post_type_name', 'mec-events');
    }

    /**
     * Returns main options of MEC
     * @param string $locale
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_options($locale = null)
    {
        if ($locale)
        {
            $options = get_option('mec_options_' . strtolower($locale), []);
            if (!is_array($options) || !count($options)) $options = get_option('mec_options', []);

            return $options;
        }
        else return get_option('mec_options', []);
    }

    /**
     * Returns Multilingual options of MEC
     * @param string $key
     * @param string $locale
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_ml_settings($key = null, $locale = null)
    {
        if (!$locale) $locale = $this->get_current_locale();

        $options = get_option('mec_options_ml_' . strtolower($locale), []);
        if (!$this->is_multilingual() or !is_array($options) or (is_array($options) and !count($options)))
        {
            $all = get_option('mec_options', []);
            if (!is_array($all)) $all = [];

            $options = (isset($all['settings']) ? $all['settings'] : []);
        }

        return ($key ? (isset($options[$key]) ? $options[$key] : null) : $options);
    }

    /**
     * Returns MEC settings menus
     * @param string $active_menu
     * @return void
     * @author Webnus <info@webnus.net>
     */
    public function get_sidebar_menu($active_menu = 'settings')
    {
        $options = $this->get_settings();
        $settings = apply_filters('mec-settings-items-settings', [
            esc_html__('General', 'modern-events-calendar-lite') => 'general_option',
            esc_html__('Archive Pages', 'modern-events-calendar-lite') => 'archive_options',
            esc_html__('Slugs/Permalinks', 'modern-events-calendar-lite') => 'slug_option',
            esc_html__('Currency', 'modern-events-calendar-lite') => 'currency_option',
            esc_html__('Security Captcha', 'modern-events-calendar-lite') => 'captcha_option',
            esc_html__('Search', 'modern-events-calendar-lite') => 'search_options',
        ], $active_menu);

        $integrations = apply_filters('mec-settings-items-integrations', [
            esc_html__('Mailchimp', 'modern-events-calendar-lite') => 'mailchimp_option',
            esc_html__('Campaign Monitor', 'modern-events-calendar-lite') => 'campaign_monitor_option',
            esc_html__('MailerLite', 'modern-events-calendar-lite') => 'mailerlite_option',
            esc_html__('Constant Contact', 'modern-events-calendar-lite') => 'constantcontact_option',
            esc_html__('Active Campaign', 'modern-events-calendar-lite') => 'active_campaign_option',
            esc_html__('AWeber', 'modern-events-calendar-lite') => 'aweber_option',
            esc_html__('MailPoet', 'modern-events-calendar-lite') => 'mailpoet_option',
            esc_html__('Sendfox', 'modern-events-calendar-lite') => 'sendfox_option',
            esc_html__('BuddyPress', 'modern-events-calendar-lite') => 'buddy_option',
            esc_html__('LearnDash', 'modern-events-calendar-lite') => 'learndash_options',
            esc_html__('Paid Membership Pro', 'modern-events-calendar-lite') => 'pmp_options',
        ], $active_menu);

        $single_event = apply_filters('mec-settings-item-single_event', [
            esc_html__('Single Event Page', 'modern-events-calendar-lite') => 'event_options',
            esc_html__('Custom Fields', 'modern-events-calendar-lite') => 'event_form_option',
            esc_html__('Sidebar', 'modern-events-calendar-lite') => 'single_sidebar_options',
            esc_html__('Icons', 'modern-events-calendar-lite') => 'single_icons_options',
        ], $active_menu);

        $booking = apply_filters('mec-settings-item-booking', [
            $this->m('booking', esc_html__('Booking', 'modern-events-calendar-lite')) => 'booking_option',
            sprintf(esc_html__('%s Elements', 'modern-events-calendar-lite'), $this->m('booking', esc_html__('Booking', 'modern-events-calendar-lite'))) => 'booking_elements',
            esc_html__('Appointments', 'modern-events-calendar-lite') => 'booking_appointments_options',
            esc_html__('Global Tickets', 'modern-events-calendar-lite') => 'booking_tickets_option',
            sprintf(esc_html__('%s Form', 'modern-events-calendar-lite'), $this->m('booking', esc_html__('Booking', 'modern-events-calendar-lite'))) => 'booking_form_option',
            esc_html__('Payment Gateways', 'modern-events-calendar-lite') => 'payment_gateways_option',
            esc_html__('MEC Cart', 'modern-events-calendar-lite') => 'cart_option',
            esc_html__('Ticket Variations & Options', 'modern-events-calendar-lite') => 'ticket_variations_option',
            esc_html__('Taxes / Fees', 'modern-events-calendar-lite') => 'taxes_option',
            esc_html__('Coupons', 'modern-events-calendar-lite') => 'coupon_option',
        ], $active_menu);

        $modules = apply_filters('mec-settings-item-modules', [
            esc_html__('Speakers', 'modern-events-calendar-lite') => 'speakers_option',
            esc_html__('Organizers', 'modern-events-calendar-lite') => 'organizers_option',
            esc_html__('Locations', 'modern-events-calendar-lite') => 'locations_option',
            esc_html__('Countdown', 'modern-events-calendar-lite') => 'countdown_option',
            esc_html__('Map', 'modern-events-calendar-lite') => 'googlemap_option',
            esc_html__('Exceptional Days', 'modern-events-calendar-lite') => 'exceptional_option',
            esc_html__('Local Time', 'modern-events-calendar-lite') => 'time_module_option',
            esc_html__('Progress Bar', 'modern-events-calendar-lite') => 'progress_bar_option',
            esc_html__('Event Gallery', 'modern-events-calendar-lite') => 'event_gallery_option',
            esc_html__('QR Code', 'modern-events-calendar-lite') => 'qrcode_module_option',
            esc_html__('Weather', 'modern-events-calendar-lite') => 'weather_module_option',
            esc_html__('Related Events', 'modern-events-calendar-lite') => 'related_events',
            esc_html__('Social Networks', 'modern-events-calendar-lite') => 'social_options',
            esc_html__('Event Export', 'modern-events-calendar-lite') => 'export_module_option',
            esc_html__('Next Event', 'modern-events-calendar-lite') => 'next_event_option',
            esc_html__('Next / Previous Events', 'modern-events-calendar-lite') => 'next_previous_events',
        ], $active_menu);

        $FES = apply_filters('mec-settings-items-fes', [
            esc_html__('General', 'modern-events-calendar-lite') => 'fes_general_options',
            esc_html__('Access Level', 'modern-events-calendar-lite') => 'fes_acl_options',
            esc_html__('FES Sections', 'modern-events-calendar-lite') => 'fes_section_options',
            esc_html__('Required Fields', 'modern-events-calendar-lite') => 'fes_req_fields_options',
        ], $active_menu);

        $notifications_items = [
            esc_html__('Options', 'modern-events-calendar-lite') => 'notification_options',
            esc_html__('New Event', 'modern-events-calendar-lite') => 'new_event',
            esc_html__('User Event Publishing', 'modern-events-calendar-lite') => 'user_event_publishing',
        ];

        if ($this->getPRO())
        {
            $settings[esc_html__('RESTful API', 'modern-events-calendar-lite')] = 'restful_api_options';

            $notifications_items = [
                esc_html__('Options', 'modern-events-calendar-lite') => 'notification_options',
                esc_html__('Booking', 'modern-events-calendar-lite') => 'booking_notification_section',
                esc_html__('Booking Confirmation', 'modern-events-calendar-lite') => 'booking_confirmation',
                esc_html__('Booking Rejection', 'modern-events-calendar-lite') => 'booking_rejection',
                esc_html__('Booking Verification', 'modern-events-calendar-lite') => 'booking_verification',
                esc_html__('Booking Cancellation', 'modern-events-calendar-lite') => 'cancellation_notification',
                esc_html__('Booking Reminder', 'modern-events-calendar-lite') => 'booking_reminder',
                esc_html__('Booking Reschedule', 'modern-events-calendar-lite') => 'booking_moved',
                esc_html__('Event Soldout', 'modern-events-calendar-lite') => 'event_soldout',
                esc_html__('Admin', 'modern-events-calendar-lite') => 'admin_notification',
                esc_html__('Event Finished', 'modern-events-calendar-lite') => 'event_finished',
                esc_html__('New Event', 'modern-events-calendar-lite') => 'new_event',
                esc_html__('User Event Publishing', 'modern-events-calendar-lite') => 'user_event_publishing',
                esc_html__('Auto Emails', 'modern-events-calendar-lite') => 'auto_emails_option',
                esc_html__('Suggest Event', 'modern-events-calendar-lite') => 'suggest_event',
            ];

            // Certificate
            if (isset($options['certificate_status']) && $options['certificate_status'])
            {
                $notifications_items[esc_html__('Certification', 'modern-events-calendar-lite')] = 'certificate_send';
            }

            $modules = apply_filters('mec-settings-item-modules', [
                esc_html__('Speakers', 'modern-events-calendar-lite') => 'speakers_option',
                esc_html__('Organizers', 'modern-events-calendar-lite') => 'organizers_option',
                esc_html__('Sponsors', 'modern-events-calendar-lite') => 'sponsors_option',
                esc_html__('Locations', 'modern-events-calendar-lite') => 'locations_option',
                esc_html__('Countdown', 'modern-events-calendar-lite') => 'countdown_option',
                esc_html__('Map', 'modern-events-calendar-lite') => 'googlemap_option',
                esc_html__('Exceptional Days', 'modern-events-calendar-lite') => 'exceptional_option',
                esc_html__('Local Time', 'modern-events-calendar-lite') => 'time_module_option',
                esc_html__('Progress Bar', 'modern-events-calendar-lite') => 'progress_bar_option',
                esc_html__('Event Gallery', 'modern-events-calendar-lite') => 'event_gallery_option',
                esc_html__('QR Code', 'modern-events-calendar-lite') => 'qrcode_module_option',
                esc_html__('Weather', 'modern-events-calendar-lite') => 'weather_module_option',
                esc_html__('Related Events', 'modern-events-calendar-lite') => 'related_events',
                esc_html__('Social Networks', 'modern-events-calendar-lite') => 'social_options',
                esc_html__('Export', 'modern-events-calendar-lite') => 'export_module_option',
                esc_html__('Next Event', 'modern-events-calendar-lite') => 'next_event_option',
                esc_html__('Next / Previous Events', 'modern-events-calendar-lite') => 'next_previous_events',
                esc_html__('Certificates', 'modern-events-calendar-lite') => 'certificate_options',
                esc_html__('SMS', 'modern-events-calendar-lite') => 'sms_options',
            ], $active_menu);
        }

        $notifications = apply_filters('mec-settings-item-notifications', $notifications_items, $active_menu);
        ?>
        <ul class="wns-be-group-menu">

            <!-- Settings -->
            <li class="wns-be-group-menu-li mec-settings-menu <?php echo($active_menu == 'settings' ? 'active' : ''); ?>">
                <a href="<?php echo esc_url($this->remove_qs_var('tab')); ?>" id="" class="wns-be-group-tab-link-a">
                    <i class="mec-sl-settings"></i>
                    <span class="wns-be-group-menu-title"><?php esc_html_e('Settings', 'modern-events-calendar-lite'); ?></span>
                </a>
                <ul class="<?php echo($active_menu == 'settings' ? 'subsection' : 'mec-settings-submenu'); ?>">
                    <?php foreach ($settings as $settings_name => $settings_link): ?>
                        <?php
                        if ($settings_link == 'mailchimp_option' || $settings_link == 'active_campaign_option' || $settings_link == 'mailpoet_option' || $settings_link == 'sendfox_option' || $settings_link == 'aweber_option' || $settings_link == 'campaign_monitor_option' || $settings_link == 'mailerlite_option' || $settings_link == 'constantcontact_option' || $settings_link == 'buddy_option' || $settings_link == 'learndash_options' || $settings_link == 'pmp_options'):
                            if ($this->getPRO()): ?>
                                <li>
                                    <a
                                        <?php if ($active_menu == 'settings'): ?>
                                            data-id="<?php echo esc_attr($settings_link); ?>" class="wns-be-group-tab-link-a WnTabLinks"
                                        <?php else: ?>
                                            href="<?php echo esc_url($this->remove_qs_var('tab') . '#' . $settings_link); ?>"
                                        <?php endif; ?>>
                                        <span
                                            class="pr-be-group-menu-title"><?php echo esc_html($settings_name); ?></span>
                                    </a>
                                </li>
                            <?php
                            endif;
                        else: ?>
                            <li>
                                <a
                                    <?php if ($active_menu == 'settings'): ?>
                                        data-id="<?php echo esc_attr($settings_link); ?>" class="wns-be-group-tab-link-a WnTabLinks"
                                    <?php else: ?>
                                        href="<?php echo esc_url($this->remove_qs_var('tab') . '#' . $settings_link); ?>"
                                    <?php endif; ?>>
                                    <span class="pr-be-group-menu-title"><?php echo esc_html($settings_name); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </li>

            <!-- Single Event -->
            <li class="wns-be-group-menu-li mec-settings-menu <?php echo($active_menu == 'single_event' ? 'active' : ''); ?>">
                <a href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-single')); ?>" id=""
                   class="wns-be-group-tab-link-a">
                    <i class="mec-sl-event"></i>
                    <span class="wns-be-group-menu-title"><?php esc_html_e('Single Event', 'modern-events-calendar-lite'); ?></span>
                </a>
                <ul class="<?php echo($active_menu == 'single_event' ? 'subsection' : 'mec-settings-submenu'); ?>">
                    <?php foreach ($single_event as $single_event_name => $single_event_link) : ?>
                        <li>
                            <a
                                <?php if ($active_menu == 'single_event'): ?>
                                    data-id="<?php echo esc_attr($single_event_link); ?>" class="wns-be-group-tab-link-a WnTabLinks"
                                <?php else: ?>
                                    href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-single') . '#' . $single_event_link); ?>"
                                <?php endif; ?>>
                                <span class="pr-be-group-menu-title"><?php echo esc_html($single_event_name); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>

            <!-- Modules -->
            <li class="wns-be-group-menu-li mec-settings-menu <?php echo($active_menu == 'modules' ? 'active' : ''); ?>">
                <a href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-modules')); ?>" id=""
                   class="wns-be-group-tab-link-a">
                    <i class="mec-sl-grid"></i>
                    <span class="wns-be-group-menu-title"><?php esc_html_e('Event Modules', 'modern-events-calendar-lite'); ?></span>
                </a>
                <ul class="<?php echo($active_menu == 'modules' ? 'subsection' : 'mec-settings-submenu'); ?>">

                    <?php foreach ($modules as $modules_name => $modules_link): ?>
                        <?php if ($modules_link == 'googlemap_option' || $modules_link == 'qrcode_module_option' || $modules_link == 'weather_module_option'): ?>
                            <?php if ($this->getPRO()): ?>
                                <li>
                                    <a
                                        <?php if ($active_menu == 'modules'): ?>
                                            data-id="<?php echo esc_attr($modules_link); ?>" class="wns-be-group-tab-link-a WnTabLinks"
                                        <?php else: ?>
                                            href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-modules') . '#' . $modules_link); ?>"
                                        <?php endif; ?>>
                                        <span
                                            class="pr-be-group-menu-title"><?php echo esc_html($modules_name); ?></span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li>
                                <a
                                    <?php if ($active_menu == 'modules'): ?>
                                        data-id="<?php echo esc_attr($modules_link); ?>" class="wns-be-group-tab-link-a WnTabLinks"
                                    <?php else: ?>
                                        href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-modules') . '#' . $modules_link); ?>"
                                    <?php endif; ?>>
                                    <span class="pr-be-group-menu-title"><?php echo esc_html($modules_name); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>

                    <?php endforeach; ?>
                </ul>
            </li>

            <!-- Booking -->
            <?php if ($this->getPRO()): ?>
                <li class="wns-be-group-menu-li mec-settings-menu <?php echo($active_menu == 'booking' ? 'active' : ''); ?>">
                    <a href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-booking')); ?>" id=""
                       class="wns-be-group-tab-link-a">
                        <i class="mec-sl-wallet"></i>
                        <span
                            class="wns-be-group-menu-title"><?php echo esc_html($this->m('booking', esc_html__('Booking', 'modern-events-calendar-lite'))); ?></span>
                    </a>
                    <ul class="<?php echo($active_menu == 'booking' ? 'subsection' : 'mec-settings-submenu'); ?>">

                        <?php foreach ($booking as $booking_name => $booking_link): ?>
                            <?php if ($booking_link == 'booking_appointments_options' || $booking_link == 'cart_option' || $booking_link == 'coupon_option' || $booking_link == 'taxes_option' || $booking_link == 'ticket_variations_option' || $booking_link == 'booking_form_option' || $booking_link == 'uploadfield_option' || $booking_link == 'payment_gateways_option' || $booking_link == 'booking_shortcode' || $booking_link == 'webhooks_option' || $booking_link == 'booking_tickets_option' || $booking_link == 'booking_elements'): ?>
                                <?php if (isset($options['booking_status']) and $options['booking_status']): ?>
                                    <li>
                                        <a
                                            <?php if ($active_menu == 'booking'): ?>
                                                data-id="<?php echo esc_attr($booking_link); ?>" class="wns-be-group-tab-link-a WnTabLinks"
                                            <?php else: ?>
                                                href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-booking') . '#' . $booking_link); ?>"
                                            <?php endif; ?>>
                                            <span
                                                class="pr-be-group-menu-title"><?php echo esc_html($booking_name); ?></span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php else: ?>
                                <li>
                                    <a
                                        <?php if ($active_menu == 'booking'): ?>
                                            data-id="<?php echo esc_attr($booking_link); ?>" class="wns-be-group-tab-link-a WnTabLinks"
                                        <?php else: ?>
                                            href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-booking') . '#' . $booking_link); ?>"
                                        <?php endif; ?>>
                                        <span
                                            class="pr-be-group-menu-title"><?php echo esc_html($booking_name); ?></span>
                                    </a>
                                </li>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- Frontend Event Submission -->
            <li class="wns-be-group-menu-li mec-settings-menu <?php echo($active_menu == 'fes' ? 'active' : ''); ?>">
                <a href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-fes')); ?>" id=""
                   class="wns-be-group-tab-link-a">
                    <i class="mec-sl-cloud-upload"></i>
                    <span class="wns-be-group-menu-title"><?php esc_html_e('Front-end Submission', 'modern-events-calendar-lite'); ?></span>
                </a>
                <ul class="<?php echo($active_menu == 'fes' ? 'subsection' : 'mec-settings-submenu'); ?>">
                    <?php foreach ($FES as $fes_name => $fes_link) : ?>
                        <li>
                            <a
                                <?php if ($active_menu == 'fes'): ?>
                                    data-id="<?php echo esc_attr($fes_link); ?>" class="wns-be-group-tab-link-a WnTabLinks"
                                <?php else: ?>
                                    href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-fes') . '#' . $fes_link); ?>"
                                <?php endif; ?>>
                                <span class="pr-be-group-menu-title"><?php echo esc_html($fes_name); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>

            <!-- Integrations -->
            <?php if ($this->getPRO()): ?>
                <li class="wns-be-group-menu-li mec-settings-menu <?php echo($active_menu == 'integrations' ? 'active' : ''); ?>">
                    <a href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-integrations')); ?>" id=""
                       class="wns-be-group-tab-link-a">
                        <i class="mec-sl-wrench"></i>
                        <span class="wns-be-group-menu-title"><?php esc_html_e('Integrations', 'modern-events-calendar-lite'); ?></span>
                    </a>
                    <ul class="<?php echo($active_menu == 'integrations' ? 'subsection' : 'mec-settings-submenu'); ?>">
                        <?php foreach ($integrations as $integrations_name => $integrations_link) : ?>

                            <li>
                                <a
                                    <?php if ($active_menu == 'integrations'): ?>
                                        data-id="<?php echo esc_attr($integrations_link); ?>" class="wns-be-group-tab-link-a WnTabLinks"
                                    <?php else: ?>
                                        href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-integrations') . '#' . $integrations_link); ?>"
                                    <?php endif; ?>>
                                    <span
                                        class="pr-be-group-menu-title"><?php echo esc_html($integrations_name); ?></span>
                                </a>
                            </li>

                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- Notifications -->
            <li class="wns-be-group-menu-li mec-settings-menu <?php echo($active_menu == 'notifications' ? 'active' : ''); ?>">
                <a href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-notifications') . (!$this->getPRO() ? '#new_event' : '')); ?>"
                   id="" class="wns-be-group-tab-link-a">
                    <i class="mec-sl-envelope-open"></i>
                    <span class="wns-be-group-menu-title"><?php esc_html_e('Notifications', 'modern-events-calendar-lite'); ?></span>
                </a>
                <ul class="<?php echo($active_menu == 'notifications' ? 'subsection' : 'mec-settings-submenu'); ?>">

                    <?php foreach ($notifications as $notifications_name => $notifications_link): ?>
                        <?php if ($notifications_link != 'new_event' and $notifications_link != 'user_event_publishing'): ?>
                            <?php if ((isset($options['booking_status']) and $options['booking_status']) || false !== strpos($notifications_link, 'rsvp')): ?>
                                <li>
                                    <a
                                        <?php if ($active_menu == 'notifications'): ?>
                                            data-id="<?php echo esc_attr($notifications_link); ?>" class="wns-be-group-tab-link-a WnTabLinks"
                                        <?php else: ?>
                                            href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-notifications') . '#' . $notifications_link); ?>"
                                        <?php endif; ?>>
                                        <span
                                            class="pr-be-group-menu-title"><?php echo esc_html($notifications_name); ?></span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li>
                                <a
                                    <?php if ($active_menu == 'notifications'): ?>
                                        data-id="<?php echo esc_attr($notifications_link); ?>" class="wns-be-group-tab-link-a WnTabLinks"
                                    <?php else: ?>
                                        href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-notifications') . '#' . $notifications_link); ?>"
                                    <?php endif; ?>>
                                    <span
                                        class="pr-be-group-menu-title"><?php echo esc_html($notifications_name); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </li>

            <!-- Custom Menus -->
            <?php do_action('mec_settings_sidebar', $active_menu); ?>

            <li class="wns-be-group-menu-li mec-settings-menu <?php echo($active_menu == 'styling' ? 'active' : ''); ?>">
                <a href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-styling')); ?>" id=""
                   class="wns-be-group-tab-link-a">
                    <i class="mec-sl-equalizer"></i>
                    <span class="wns-be-group-menu-title"><?php esc_html_e('Appearance', 'modern-events-calendar-lite'); ?></span>
                </a>
            </li>

            <li class="wns-be-group-menu-li mec-settings-menu <?php echo($active_menu == 'customcss' ? 'active' : ''); ?>">
                <a href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-customcss')); ?>" id=""
                   class="wns-be-group-tab-link-a">
                    <i class="mec-sl-pencil"></i>
                    <span class="wns-be-group-menu-title"><?php esc_html_e('Custom CSS', 'modern-events-calendar-lite'); ?></span>
                </a>
            </li>

            <li class="wns-be-group-menu-li mec-settings-menu <?php echo($active_menu == 'messages' ? 'active' : ''); ?>">
                <a href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-messages')); ?>" id=""
                   class="wns-be-group-tab-link-a">
                    <i class="mec-sl-speech"></i>
                    <span class="wns-be-group-menu-title"><?php esc_html_e('Translate Options', 'modern-events-calendar-lite'); ?></span>
                </a>
            </li>

            <li class="wns-be-group-menu-li mec-settings-menu <?php echo($active_menu == 'ie' ? 'active' : ''); ?>">
                <a href="<?php echo esc_url($this->add_qs_var('tab', 'MEC-ie')); ?>" id=""
                   class="wns-be-group-tab-link-a">
                    <i class="mec-sl-refresh"></i>
                    <span class="wns-be-group-menu-title"><?php esc_html_e('Import / Export', 'modern-events-calendar-lite'); ?></span>
                </a>
            </li>
        </ul> <!-- close wns-be-group-menu -->
        <script>
            jQuery(document).ready(function () {
                if (jQuery('.mec-settings-menu').hasClass('active')) {
                    jQuery('.mec-settings-menu.active').find('ul li:first-of-type').addClass('active');
                }

                jQuery('.WnTabLinks').each(function () {
                    var ContentId = jQuery(this).attr('data-id');
                    jQuery(this).click(function () {
                        jQuery('.wns-be-sidebar li ul li').removeClass('active');
                        jQuery(this).parent().addClass('active');
                        jQuery(".mec-options-fields").hide();
                        jQuery(".mec-options-fields").removeClass('active');
                        jQuery("#" + ContentId + "").show();
                        jQuery("#" + ContentId + "").addClass('active');
                        if (jQuery("#wns-be-infobar").hasClass("sticky")) {
                            jQuery('html, body').animate({
                                scrollTop: jQuery("#" + ContentId + "").offset().top - 140
                            }, 300);
                        }
                    });

                    var hash = window.location.hash.replace('#', '');
                    jQuery('[data-id="' + hash + '"]').trigger('click');
                });

                jQuery(".wns-be-sidebar li ul li").on('click', function (event) {
                    jQuery(".wns-be-sidebar li ul li").removeClass('active');
                    jQuery(this).addClass('active');
                });
            });
        </script>
        <?php
    }

    /**
     * Returns MEC settings
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_settings()
    {
        $options = $this->get_options();
        return $options['settings'] ?? [];
    }

    /**
     * Returns MEC addons message
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function addons_msg()
    {
        $get_n_option = get_option('mec_addons_notification_option');
        if ($get_n_option == 'open') return '';

        return '
        <div class="w-row mec-addons-notification-wrap">
            <div class="w-col-sm-12">
                <div class="w-clearfix w-box mec-addons-notification-box-wrap">
                    <div class="w-box-head">' . esc_html__('New Addons For MEC! Now Customize MEC in Elementor', 'modern-events-calendar-lite') . '<span><i class="mec-sl-close"></i></span></div>
                    <div class="w-box-content">
                        <div class="mec-addons-notification-box-image">
                            <img src="' . plugin_dir_url(__FILE__) . '../../assets/img/mec-addons-teaser1.png" />
                        </div>
                        <div class="mec-addons-notification-box-content">
                            <div class="w-box-content">
                                <p>' . esc_html__('The time has come at last, and the new practical add-ons for MEC have been released. This is a revolution in the world of Event Calendars. We have provided you with a wide range of features only by having the 4 add-ons below:', 'modern-events-calendar-lite') . '</p>
                                <ol>
                                    <li>' . esc_html__('<strong>WooCommerce Integration:</strong> You can now purchase ticket (as products) and Woo products at the same time.', 'modern-events-calendar-lite') . '</li>
                                    <li>' . esc_html__('<strong>Event API:</strong> display your events (shortcodes/single event) on other websites without MEC.  Use JSON output features to make your Apps compatible with MEC.', 'modern-events-calendar-lite') . '</li>
                                    <li>' . esc_html__('<strong>Multisite Event Sync:</strong> Sync events between your subsites and main websites. Changes in the main one will be inherited by the subsites. you can set these up in the admin panel.', 'modern-events-calendar-lite') . '</li>
                                    <li>' . esc_html__('<strong>User Dashboard:</strong> Create exclusive pages for users. These pages can contain ticket purchase information, information about registered events. Users can now log in to purchase tickets.', 'modern-events-calendar-lite') . '</li>
                                </ol>
                                <a href="https://webnus.net/modern-events-calendar/addons/?ref=17" target="_blank">' . esc_html__('find out more', 'modern-events-calendar-lite') . '</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ';
    }

    /**
     * Returns MEC custom message 2
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function mec_custom_msg_2($display_option = '', $message = '')
    {
        $get_cmsg_display_option = get_option('mec_custom_msg_2_display_option');
        $get_mec_saved_message_time = get_option('mec_saved_message_2_time');
        $data_url = 'https://files.webnus.site/addons-api/mec-extra-content-2.json';

        if (!isset($get_mec_saved_message_time)):
            if (ini_get('allow_url_fopen'))
            {
                $body = @file_get_contents(plugin_dir_path(__FILE__) . '../api/addons-api/mec-extra-content-2.json');
            }
            $obj = json_decode($body);
            update_option('mec_saved_message_2_time', date("Y-m-d"));
        else:
            if (strtotime(date("Y-m-d")) > strtotime($get_mec_saved_message_time))
            {
                if (ini_get('allow_url_fopen'))
                {
                    $body = @file_get_contents(plugin_dir_path(__FILE__) . '../api/addons-api/mec-extra-content-2.json');
                }
                $obj = json_decode($body);
                update_option('mec_saved_message_2_time', date("Y-m-d"));
            }
            else
            {
                $mec_custom_msg_html = get_option('mec_custom_msg_2_html');
                $mec_custom_msg_display = get_option('mec_custom_msg_2_display');
                if ($get_cmsg_display_option != $mec_custom_msg_display) :
                    update_option('mec_custom_msg_2_display_option', $mec_custom_msg_display);
                    update_option('mec_custom_msg_2_close_option', 'close');
                    update_option('mec_saved_message_2_time', date("Y-m-d"));
                    return $mec_custom_msg_html;
                else:
                    $get_cmsg_close_option = get_option('mec_custom_msg_2_close_option');
                    update_option('mec_saved_message_2_time', date("Y-m-d"));
                    if ($get_cmsg_close_option == 'open') return '';
                    return $mec_custom_msg_html;
                endif;
            }
        endif;

        if (!empty($obj))
        {
            $display = '';
            $html = '';

            foreach ($obj as $value)
            {
                $html = '<div class="mec-custom-msg-2-notification-set-box extra"><div style="margin: 0" class="w-row mec-custom-msg-notification-wrap"><div class="w-col-sm-12"><div class="w-clearfix w-box mec-cmsg-2-notification-box-wrap mec-new-addons-wrap" style="margin-top:0;"><div class="w-box-head">Announcement<span><i class="mec-sl-close"></i></span></div><div class="w-box-content"><div class="mec-addons-notification-box-image" style="width: 240px; margin-right: 10px;"><img src="' . plugin_dir_url(__FILE__) . '../api/addons-api/square-integration-addon.svg" /></div><div class="mec-addons-notification-box-content mec-new-addons" style="width: calc(100% - 270px);"><div class="w-box-content"><div class="csm-message-notice" style="text-align: center; background: #BAF0FC57; border-radius: 6px;letter-spacing: 4.4px; color: #00CAE6; text-transform: uppercase; padding: 10px 5px; font-weight: bold; margin-bottom: 40px;">Square Payment</div><p>As promised, another one of the most-requested addons by you, Square Payment, is released this week. The first six addons are out already. Webex Integration and Social Auto Poster and Elementor FES Builder and Seat and Gutenberg Single Builder and Square Payment are now available on our website for purchase.<br/></p><div style="clear:both"></div><a href="https://webnus.net/modern-events-calendar/addons/square-payment/?ref=17" target="_blank">Read More</a></div></div></div></div></div></div></div>';
                update_option('mec_custom_msg_2_html', $html);
                $display = $value->display;
                update_option('mec_custom_msg_2_display', $display);
            }

            if ($get_cmsg_display_option != $display)
            {
                update_option('mec_custom_msg_2_display_option', $display);
                update_option('mec_custom_msg_2_close_option', 'close');
            }
            else
            {
                $get_cmsg_close_option = get_option('mec_custom_msg_2_close_option');
                if ($get_cmsg_close_option === 'open') return '';
            }

            return $html;
        }

        return '';
    }

    /**
     * Returns MEC custom message
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function mec_custom_msg($display_option = '', $message = '')
    {
        $get_cmsg_display_option = get_option('mec_custom_msg_display_option');
        $get_mec_saved_message_time = get_option('mec_saved_message_time');
        $data_url = 'https://files.webnus.site/addons-api/mec-extra-content.json';

        if (!isset($get_mec_saved_message_time)):
            if (ini_get('allow_url_fopen'))
            {
                $body = @file_get_contents(plugin_dir_path(__FILE__) . '../api/addons-api/mec-extra-content.json');
            }
            $obj = json_decode($body);
            update_option('mec_saved_message_time', date("Y-m-d"));
        else:
            if (strtotime(date("Y-m-d")) > strtotime($get_mec_saved_message_time))
            {
                if (ini_get('allow_url_fopen'))
                {
                    $body = @file_get_contents(plugin_dir_path(__FILE__) . '../api/addons-api/mec-extra-content.json');
                }
                $obj = json_decode($body);
                update_option('mec_saved_message_time', date("Y-m-d"));
            }
            else
            {
                $mec_custom_msg_html = get_option('mec_custom_msg_html');
                $mec_custom_msg_display = get_option('mec_custom_msg_display');
                if ($get_cmsg_display_option != $mec_custom_msg_display) :
                    update_option('mec_custom_msg_display_option', $mec_custom_msg_display);
                    update_option('mec_custom_msg_close_option', 'close');
                    update_option('mec_saved_message_time', date("Y-m-d"));
                    return $mec_custom_msg_html;
                elseif ($get_cmsg_display_option == $mec_custom_msg_display) :
                    $get_cmsg_close_option = get_option('mec_custom_msg_close_option');
                    update_option('mec_saved_message_time', date("Y-m-d"));
                    if ($get_cmsg_close_option == 'open') return;
                    return $mec_custom_msg_html;
                endif;
            }
        endif;

        if (!empty($obj)) :
            foreach ($obj as $key => $value)
            {
                $html = '<div class="mec-custom-msg-notification-set-box extra"><div style="margin: 0" class="w-row mec-custom-msg-notification-wrap"><div class="w-col-sm-12"><div class="w-clearfix w-box mec-cmsg-notification-box-wrap mec-new-addons-wrap" style="margin-top:0;"><div class="w-box-head">Announcement<span><i class="mec-sl-close"></i></span></div><div class="w-box-content"><div class="mec-addons-notification-box-image" style="width: 240px; margin-right: 10px;"><img src="' . plugin_dir_url(__FILE__) . '../api/addons-api/liquid-view-layouts.svg" /></div><div class="mec-addons-notification-box-content mec-new-addons" style="width: calc(100% - 270px);"><div class="w-box-content"><div class="csm-message-notice" style="text-align: center; background: #BAF0FC57; border-radius: 6px;letter-spacing: 4.4px; color: #00CAE6; text-transform: uppercase; padding: 10px 5px; font-weight: bold; margin-bottom: 40px;">Liquid View Layouts</div><p>As promised, another one of the most-requested addons by you, Liquid View Layouts, is released this week. The all addons are out already. Webex Integration and Social Auto Poster and Elementor FES Builder and Seat and Gutenberg Single Builder and Square Payment and Liquid View Layouts are now available on our website for purchase.<br/></p><div style="clear:both"></div><a href="https://webnus.net/modern-events-calendar/addons/liquid-view-layout/?ref=17" target="_blank">Read More</a></div></div></div></div></div></div></div>';
                update_option('mec_custom_msg_html', $html);
                $display = $value->display;
                update_option('mec_custom_msg_display', $display);
            }

            if ($get_cmsg_display_option != $display) :
                update_option('mec_custom_msg_display_option', $display);
                update_option('mec_custom_msg_close_option', 'close');
                return $html;
            elseif ($get_cmsg_display_option == $display) :
                $get_cmsg_close_option = get_option('mec_custom_msg_close_option');
                if ($get_cmsg_close_option == 'open') return;
                return $html;
            endif;
        else:
            return '';
        endif;
    }

    /**
     * Returns MEC settings
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_default_form()
    {
        $options = $this->get_options();
        return $options['default_form'] ?? [];
    }

    /**
     * Returns registration form fields
     * @param integer $event_id
     * @param integer $translated_event_id
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_reg_fields($event_id = null, $translated_event_id = null)
    {
        $options = $this->get_options();
        $reg_fields = $options['reg_fields'] ?? [];

        // Event Booking Fields
        if ($event_id)
        {
            $global_inheritance = get_post_meta($event_id, 'mec_reg_fields_global_inheritance', true);
            if (trim($global_inheritance) == '') $global_inheritance = 1;

            if (!$global_inheritance)
            {
                $event_reg_fields = get_post_meta($event_id, 'mec_reg_fields', true);
                if (is_array($event_reg_fields)) $reg_fields = $event_reg_fields;

                // We're getting fields for a translated event
                if ($translated_event_id and $event_id != $translated_event_id)
                {
                    $translated_reg_fields = get_post_meta($translated_event_id, 'mec_reg_fields', true);
                    if (!is_array($translated_reg_fields)) $translated_reg_fields = [];

                    foreach ($translated_reg_fields as $field_id => $translated_reg_field)
                    {
                        if (!isset($reg_fields[$field_id])) continue;
                        if (isset($translated_reg_field['label']) and trim($translated_reg_field['label'])) $reg_fields[$field_id]['label'] = $translated_reg_field['label'];
                        if (isset($translated_reg_field['options']) and is_array($translated_reg_field['options'])) $reg_fields[$field_id]['options'] = $translated_reg_field['options'];
                    }
                }
            }
        }

        return apply_filters('mec_get_reg_fields', $reg_fields, $event_id);
    }

    /**
     * Returns booking fixed fields
     * @param integer $event_id
     * @param integer $translated_event_id
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_bfixed_fields($event_id = null, $translated_event_id = null)
    {
        $options = $this->get_options();
        $bfixed_fields = $options['bfixed_fields'] ?? [];

        // Event Fields
        if ($event_id)
        {
            $global_inheritance = get_post_meta($event_id, 'mec_reg_fields_global_inheritance', true);
            if (trim($global_inheritance) == '') $global_inheritance = 1;

            if (!$global_inheritance)
            {
                $event_bfixed_fields = get_post_meta($event_id, 'mec_bfixed_fields', true);
                if (is_array($event_bfixed_fields)) $bfixed_fields = $event_bfixed_fields;

                // We're getting fields for a translated event
                if ($translated_event_id and $event_id != $translated_event_id)
                {
                    $translated_bfixed_fields = get_post_meta($translated_event_id, 'mec_bfixed_fields', true);
                    if (!is_array($translated_bfixed_fields)) $translated_bfixed_fields = [];

                    foreach ($translated_bfixed_fields as $field_id => $translated_bfixed_field)
                    {
                        if (!isset($bfixed_fields[$field_id])) continue;
                        if (isset($translated_bfixed_field['label']) and trim($translated_bfixed_field['label'])) $bfixed_fields[$field_id]['label'] = $translated_bfixed_field['label'];
                        if (isset($translated_bfixed_field['options']) and is_array($translated_bfixed_field['options'])) $bfixed_fields[$field_id]['options'] = $translated_bfixed_field['options'];
                    }
                }
            }
        }

        return apply_filters('mec_get_bfixed_fields', $bfixed_fields, $event_id);
    }

    /**
     * Returns event form fields
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_event_fields()
    {
        $options = $this->get_options();
        $event_fields = $options['event_fields'] ?? [];

        if (isset($event_fields[':i:'])) unset($event_fields[':i:']);
        if (isset($event_fields[':fi:'])) unset($event_fields[':fi:']);

        return apply_filters('mec_get_event_fields', $event_fields);
    }

    /**
     * Returns Ticket Variations
     * @param integer $event_id
     * @param integer $ticket_id
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function ticket_variations($event_id = null, $ticket_id = null)
    {
        $settings = $this->get_settings();
        $ticket_variations = (isset($settings['ticket_variations']) and is_array($settings['ticket_variations'])) ? $settings['ticket_variations'] : [];

        // Event Ticket Variations
        if ($event_id)
        {
            $global_inheritance = get_post_meta($event_id, 'mec_ticket_variations_global_inheritance', true);
            if (trim($global_inheritance) == '') $global_inheritance = 1;

            if (!$global_inheritance)
            {
                $event_ticket_variations = get_post_meta($event_id, 'mec_ticket_variations', true);
                if (is_array($event_ticket_variations)) $ticket_variations = $event_ticket_variations;
            }

            // Variations Per Ticket
            if ($ticket_id)
            {
                $tickets = get_post_meta($event_id, 'mec_tickets', true);
                $ticket = ((isset($tickets[$ticket_id]) and is_array($tickets[$ticket_id])) ? $tickets[$ticket_id] : []);

                $event_inheritance = $ticket['variations_event_inheritance'] ?? 1;
                if (!$event_inheritance and isset($ticket['variations']) and is_array($ticket['variations'])) $ticket_variations = $ticket['variations'];
            }
        }

        // Clean
        if (isset($ticket_variations[':i:'])) unset($ticket_variations[':i:']);
        if (isset($ticket_variations[':v:'])) unset($ticket_variations[':v:']);

        return $ticket_variations;
    }

    public function has_variations_per_ticket($event_id, $ticket_id)
    {
        $has = false;

        $tickets = get_post_meta($event_id, 'mec_tickets', true);
        $ticket = ((isset($tickets[$ticket_id]) and is_array($tickets[$ticket_id])) ? $tickets[$ticket_id] : []);

        $event_inheritance = $ticket['variations_event_inheritance'] ?? 1;
        if (!$event_inheritance and isset($ticket['variations']) and is_array($ticket['variations'])) $has = true;

        return $has;
    }

    public function get_full_tickets($event_id): array
    {
        // Tickets
        $tickets = get_post_meta($event_id, 'mec_tickets', true);

        if (!is_array($tickets)) $tickets = [];
        if (isset($tickets[':i:'])) unset($tickets[':i:']);

        $full_tickets = [];
        foreach ($tickets as $ticket_id => $ticket)
        {
            $full_tickets[$ticket_id] = $ticket;

            $full_tickets[$ticket_id]['variations'] = $this->ticket_variations($event_id, $ticket_id);
        }

        return $full_tickets;
    }

    /**
     * Returns Messages Options
     * @param string $locale
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_messages_options($locale = null)
    {
        if ($this->is_multilingual() and !$locale) $locale = $this->get_current_language();

        $options = $this->get_options($locale);
        return $options['messages'] ?? [];
    }

    /**
     * Returns gateways options
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_gateways_options()
    {
        $options = $this->get_options();
        return $options['gateways'] ?? [];
    }

    /**
     * Returns notifications settings of MEC
     * @param string $locale
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_notifications($locale = null)
    {
        if ($this->is_multilingual() and !$locale) $locale = $this->get_current_language();

        $options = $this->get_options($locale);
        return $options['notifications'] ?? [];
    }

    /**
     * Returns Import/Export options of MEC
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_ix_options()
    {
        $options = $this->get_options();
        return $options['ix'] ?? [];
    }

    /**
     * Returns style settings of MEC
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_styles()
    {
        $options = $this->get_options();
        return $options['styles'] ?? [];
    }

    /**
     * Returns styling option of MEC
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_styling()
    {
        $options = $this->get_options();
        return $options['styling'] ?? [];
    }

    /**
     * Saves MEC settings
     * @return void
     * @author Webnus <info@webnus.net>
     */
    public function save_options()
    {
        $wpnonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : null;

        // Check if our nonce is set.
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'mec_options_form')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Current User is not Permitted
        if (!current_user_can('mec_settings') and !current_user_can('administrator')) $this->response(['success' => 0, 'code' => 'ADMIN_ONLY']);

        // Get mec options
        $mec = isset($_REQUEST['mec']) ? $this->sanitize_deep_array($_REQUEST['mec']) : [];

        if (isset($mec['reg_fields']) and !is_array($mec['reg_fields'])) $mec['reg_fields'] = [];
        if (isset($mec['bfixed_fields']) and !is_array($mec['bfixed_fields'])) $mec['bfixed_fields'] = [];
        if (isset($mec['event_fields']) and !is_array($mec['event_fields'])) $mec['event_fields'] = [];

        $filtered = [];
        foreach ($mec as $key => $value) $filtered[$key] = (is_array($value) ? $value : []);

        // Get current MEC options
        $current = get_option('mec_options', []);
        if (is_string($current) and trim($current) == '') $current = [];

        // Validations
        if (isset($filtered['settings']) and isset($filtered['settings']['slug'])) $filtered['settings']['slug'] = strtolower(str_replace(' ', '-', $filtered['settings']['slug']));
        if (isset($filtered['settings']) and isset($filtered['settings']['category_slug'])) $filtered['settings']['category_slug'] = strtolower(str_replace(' ', '-', $filtered['settings']['category_slug']));
        if (isset($filtered['settings']) and isset($filtered['settings']['custom_archive'])) $filtered['settings']['custom_archive'] = isset($filtered['settings']['custom_archive']) ? str_replace('\"', '"', $filtered['settings']['custom_archive']) : '';

        // Bellow conditional block codes is used for sortable booking form items.
        if (isset($filtered['reg_fields']))
        {
            if (!is_array($filtered['reg_fields'])) $filtered['reg_fields'] = [];
        }

        if (isset($current['reg_fields']) and isset($filtered['reg_fields']))
        {
            $current['reg_fields'] = $filtered['reg_fields'];
        }

        // Bellow conditional block codes is used for sortable booking fixed form items.
        if (isset($filtered['bfixed_fields']))
        {
            if (!is_array($filtered['bfixed_fields'])) $filtered['bfixed_fields'] = [];
        }

        if (isset($current['bfixed_fields']) and isset($filtered['bfixed_fields']))
        {
            $current['bfixed_fields'] = $filtered['bfixed_fields'];
        }

        // Bellow conditional block codes is used for sortable event form items.
        if (isset($filtered['event_fields']))
        {
            if (!is_array($filtered['event_fields'])) $filtered['event_fields'] = [];
        }

        if (isset($current['event_fields']) and isset($filtered['event_fields']))
        {
            $current['event_fields'] = $filtered['event_fields'];
        }

        // Tag Method Changed
        $old_tag_method = ((isset($current['settings']) and isset($current['settings']['tag_method'])) ? $current['settings']['tag_method'] : 'post_tag');
        if (isset($filtered['settings']) and isset($filtered['settings']['tag_method']) and $filtered['settings']['tag_method'] != $old_tag_method)
        {
            do_action('mec_tag_method_changed', $filtered['settings']['tag_method'], $old_tag_method);
        }

        // Third Party Validation
        $filtered = apply_filters('mec_validate_general_settings_options', $filtered, $current);

        // Generate New Options
        $final = $current;

        // Merge new options with previous options
        foreach ($filtered as $key => $value)
        {
            if (is_array($value))
            {
                foreach ($value as $k => $v)
                {
                    // Define New Array
                    if (!isset($final[$key])) $final[$key] = [];

                    // Overwrite Old Value
                    $final[$key][$k] = $v;
                }
            }
            // Overwrite Old Value
            else $final[$key] = $value;
        }

        // Disable some options when MEC Cart is enabled
        if (isset($final['settings']) and isset($final['settings']['mec_cart_status']) and $final['settings']['mec_cart_status'])
        {
            $final['settings']['wc_status'] = 0;
            $final['settings']['currency_per_event'] = 0;
        }

        $final = apply_filters('mec_save_options_final', $final);

        // MEC Save Options
        do_action('mec_save_options', $final);

        // Multilingual Options
        if ($this->is_multilingual())
        {
            // Locale
            $locale = isset($_REQUEST['mec_locale']) ? sanitize_text_field($_REQUEST['mec_locale']) : null;
            if ($locale)
            {
                $ml_current = get_option('mec_options_ml_' . $locale, []);
                if (is_string($ml_current) and trim($ml_current) == '') $ml_current = [];

                $ml_options = $ml_current;
                foreach (['single_date_format1' => 'settings', 'booking_date_format1' => 'settings'] as $k2 => $k1)
                {
                    if (isset($filtered[$k1]) and isset($filtered[$k1][$k2])) $ml_options[$k2] = $filtered[$k1][$k2];
                    else if (!isset($ml_options[$k2]) and isset($final[$k1], $final[$k1][$k2])) $ml_options[$k2] = $final[$k1][$k2];
                }

                update_option('mec_options_ml_' . $locale, $ml_options);
            }
        }

        // Save final options
        update_option('mec_options', $final);

        // MEC Saved Options
        do_action('mec_saved_options', $final);

        // Refresh WordPress rewrite rules
        $this->flush_rewrite_rules();

        // Print the response
        $this->response(['success' => 1]);
    }

    /**
     * Saves MEC Notifications
     * @author Webnus <info@webnus.net>
     */
    public function save_notifications()
    {
        $wpnonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : null;

        // Check if our nonce is set.
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'mec_options_form')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Current User is not Permitted
        if (!current_user_can('mec_settings') and !current_user_can('administrator')) $this->response(['success' => 0, 'code' => 'ADMIN_ONLY']);

        // Locale
        $locale = isset($_REQUEST['mec_locale']) ? sanitize_text_field($_REQUEST['mec_locale']) : null;

        // Get mec options
        $mec = isset($_REQUEST['mec']) ? $this->sanitize_deep_array($_REQUEST['mec']) : [];
        $notifications = $mec['notifications'] ?? [];
        $settings = $mec['settings'] ?? [];

        $rendered = [];
        foreach ($notifications as $notif_key => $notification)
        {
            if (isset($notification['receiver_users']) and is_string($notification['receiver_users']) and trim($notification['receiver_users']))
            {
                $notification['receiver_users'] = array_map('trim', explode(',', $notification['receiver_users']));
            }

            $rendered[$notif_key] = $notification;
        }

        // Get current MEC notifications
        $current = $this->get_notifications($locale);
        if (is_string($current) and trim($current) == '') $current = [];

        // Merge new options with previous options
        $final_notifications = [];
        $final_notifications['notifications'] = array_merge($current, $rendered);

        $core_options = get_option('mec_options', []);
        if (isset($core_options['settings']) and is_array($core_options['settings'])) $final_notifications['settings'] = array_merge($core_options['settings'], $settings);

        // Get current MEC options
        $options = get_option('mec_options', []);

        if ($this->is_multilingual() and $locale and !is_array($options)) $options = get_option('mec_options_' . strtolower($locale), []);
        if (is_string($options) and trim($options) == '') $options = [];

        // Merge new options with previous options
        $final = array_merge($options, $final_notifications);

        if ($this->is_multilingual() and $locale)
        {
            // Save final options
            update_option('mec_options_' . strtolower($locale), $final);

            $default_locale = $this->get_current_language();
            if ($default_locale === $locale) update_option('mec_options', $final);
        }

        // Save final options
        update_option('mec_options', $final);

        // Print the response
        $this->response(['success' => 1]);
    }

    /**
     * Saves MEC settings
     * @return void
     * @author Webnus <info@webnus.net>
     */
    public function save_messages()
    {
        // Security Nonce
        $wpnonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : null;

        // Check if our nonce is set.
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'mec_options_form')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Current User is not Permitted
        if (!current_user_can('mec_settings') and !current_user_can('administrator')) $this->response(['success' => 0, 'code' => 'ADMIN_ONLY']);

        // Locale
        $locale = isset($_REQUEST['mec_locale']) ? sanitize_text_field($_REQUEST['mec_locale']) : null;

        // Get mec options
        $mec = isset($_REQUEST['mec']) ? $this->sanitize_deep_array($_REQUEST['mec']) : [];
        $messages = isset($mec['messages']) ? $mec['messages'] : [];

        // Get current MEC options
        $current = $this->get_messages_options($locale);
        if (is_string($current) and trim($current) == '') $current = [];

        // Merge new options with previous options
        $final_messages = [];
        $final_messages['messages'] = array_merge($current, $messages);

        // Get current MEC options
        $options = [];

        if ($this->is_multilingual() and $locale) $options = get_option('mec_options_' . strtolower($locale), []);
        if (!is_array($options) or (is_array($options) and !count($options))) $options = get_option('mec_options', []);
        if (is_string($options) and trim($options) == '') $options = [];

        // Merge new options with previous options
        $final = array_merge($options, $final_messages);

        // Multilingual
        if ($this->is_multilingual() and $locale)
        {
            // Save final options
            update_option('mec_options_' . strtolower($locale), $final);

            $default_locale = $this->get_current_language();
            if ($default_locale === $locale) update_option('mec_options', $final);
        }
        else
        {
            // Save final options
            update_option('mec_options', $final);
        }

        // Print the response
        $this->response(['success' => 1]);
    }

    /**
     * Saves MEC Import/Export options
     * @param array $ix_options
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function save_ix_options($ix_options = [])
    {
        // Current User is not Permitted
        $capability = (current_user_can('administrator') ? 'manage_options' : 'mec_import_export');
        if (!current_user_can($capability)) $this->response(['success' => 0, 'code' => 'ADMIN_ONLY']);

        // Get current MEC ix options
        $current = $this->get_ix_options();
        if (is_string($current) and trim($current) == '') $current = [];

        // Merge new options with previous options
        $final_ix = [];
        $final_ix['ix'] = array_merge($current, $ix_options);

        // Get current MEC options
        $options = get_option('mec_options', []);
        if (is_string($options) and trim($options) == '') $options = [];

        // Merge new options with previous options
        $final = array_merge($options, $final_ix);

        // Save final options
        update_option('mec_options', $final);

        return true;
    }

    /**
     * Get first day of week from WordPress
     * @return int
     * @author Webnus <info@webnus.net>
     */
    public function get_first_day_of_week()
    {
        return get_option('start_of_week', 1);
    }

    /**
     * @param array $response
     * @return void
     * @author Webnus <info@webnus.net>
     */
    public function response($response)
    {
        wp_send_json($response);
    }

    /**
     * Check if a date passed or not
     * @param mixed $end
     * @param mixed $now
     * @return int
     * @author Webnus <info@webnus.net>
     */
    public function is_past($end, $now)
    {
        return (int) $this->is_date_after($end, $now);
    }

    /**
     * Check if a date is after a certain point or not
     *
     * @param string|int $point
     * @param string|int $date
     * @param boolean $equal
     *
     * @return boolean
     */
    public function is_date_after($point, $date, $equal = false)
    {
        if (!is_numeric($point)) $point = strtotime($point);
        if (!is_numeric($date)) $date = strtotime($date);

        // Never End
        if ($point <= 0) return false;

        return $equal ? $date >= $point : $date > $point;
    }

    /**
     * @param int $id
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_weekday_name_by_day_id($id)
    {
        // These names will be used in PHP functions, so they mustn't translate
        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
        return $days[$id];
    }

    /**
     * Spilts 2 dates to weeks
     * @param DateTime|String $start
     * @param DateTime|String $end
     * @param int $first_day_of_week
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function split_to_weeks($start, $end, $first_day_of_week = null)
    {
        if (is_null($first_day_of_week)) $first_day_of_week = $this->get_first_day_of_week();

        $end_day_of_week = ($first_day_of_week - 1 >= 0) ? $first_day_of_week - 1 : 6;

        $start_time = strtotime($start);
        $end_time = strtotime($end);

        $start = new DateTime(date('Y-m-d', $start_time));
        $end = new DateTime(date('Y-m-d 23:59', $end_time));

        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($start, $interval, $end);

        $weekday = 0;
        $weekNumber = 1;
        $weeks = [];
        foreach ($dateRange as $date)
        {
            // Fix the PHP notice
            if (!isset($weeks[$weekNumber])) $weeks[$weekNumber] = [];

            // It's first week and the week is not started from first weekday
            if ($weekNumber == 1 and $weekday == 0 and $date->format('w') != $first_day_of_week)
            {
                $remained_days = $date->format('w');

                if ($first_day_of_week == 0) $remained_days = $date->format('w'); // Sunday
                else if ($first_day_of_week == 1) // Monday
                {
                    if ($remained_days != 0) $remained_days = $remained_days - 1;
                    else $remained_days = 6;
                }
                else if ($first_day_of_week == 6) // Saturday
                {
                    if ($remained_days != 6) $remained_days = $remained_days + 1;
                    else $remained_days = 0;
                }
                else if ($first_day_of_week == 5) // Friday
                {
                    if ($remained_days < 4) $remained_days = $remained_days + 2;
                    else if ($remained_days == 5) $remained_days = 0;
                    else if ($remained_days == 6) $remained_days = 1;
                }

                $interval = new DateInterval('P' . $remained_days . 'D');
                $interval->invert = 1;
                $date->add($interval);

                for ($i = $remained_days; $i > 0; $i--)
                {
                    $weeks[$weekNumber][] = $date->format('Y-m-d');
                    $date->add(new DateInterval('P1D'));
                }
            }

            $weeks[$weekNumber][] = $date->format('Y-m-d');
            $weekday++;

            if ($date->format('w') == $end_day_of_week)
            {
                $weekNumber++;
                $weekday = 0;
            }
        }

        // Month is finished but week is not finished
        if ($weekday > 0 and $weekday < 7)
        {
            $remained_days = (6 - $weekday);
            for ($i = 0; $i <= $remained_days; $i++)
            {
                $date->add(new DateInterval('P1D'));
                $weeks[$weekNumber][] = $date->format('Y-m-d');

                if ($date->format('w') == $end_day_of_week) $weekNumber++;
            }
        }

        return $weeks;
    }

    /**
     * Returns MEC Container Width
     * @author Webnus <info@webnus.net>
     */
    public function get_container_width()
    {
        $settings = $this->get_settings();
        $container_width = (isset($settings['container_width']) and trim($settings['container_width']) != '') ? $settings['container_width'] : '';
        update_option('mec_container_width', $container_width);
    }

    /**
     * Returns MEC colors
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_available_colors()
    {
        $colors = get_option('mec_colors', $this->get_default_colors());
        return apply_filters('mec_available_colors', $colors);
    }

    /**
     * Returns MEC default colors
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_default_colors()
    {
        return apply_filters('mec_default_colors', ['fdd700', '00a0d2', 'e14d43', 'dd823b', 'a3b745']);
    }

    /**
     * Add a new color to MEC available colors
     * @param string $color
     * @author Webnus <info@webnus.net>
     */
    public function add_to_available_colors($color)
    {
        $colors = $this->get_available_colors();
        $colors[] = $color;

        $colors = array_unique($colors);
        update_option('mec_colors', $colors);
    }

    /**
     * Returns available googlemap styles
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_googlemap_styles()
    {
        $styles = [
            ['key' => 'light-dream.json', 'name' => 'Light Dream'],
            ['key' => 'intown-map.json', 'name' => 'inTown Map'],
            ['key' => 'midnight.json', 'name' => 'Midnight'],
            ['key' => 'pale-down.json', 'name' => 'Pale Down'],
            ['key' => 'blue-essence.json', 'name' => 'Blue Essence'],
            ['key' => 'blue-water.json', 'name' => 'Blue Water'],
            ['key' => 'apple-maps-esque.json', 'name' => 'Apple Maps Esque'],
            ['key' => 'CDO.json', 'name' => 'CDO'],
            ['key' => 'shades-of-grey.json', 'name' => 'Shades of Grey'],
            ['key' => 'subtle-grayscale.json', 'name' => 'Subtle Grayscale'],
            ['key' => 'ultra-light.json', 'name' => 'Ultra Light'],
            ['key' => 'facebook.json', 'name' => 'Facebook'],
        ];

        return apply_filters('mec_googlemap_styles', $styles);
    }

    /**
     * Filters provided google map styles
     * @param string $style
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_googlemap_style($style)
    {
        return apply_filters('mec_get_googlemap_style', $style);
    }

    /**
     * Fetchs googlemap styles from file
     * @param string $style
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function fetch_googlemap_style($style)
    {
        $path = $this->get_plugin_path() . 'app' . DS . 'modules' . DS . 'googlemap' . DS . 'styles' . DS . $style;

        // MEC file library
        $file = $this->getFile();

        if ($file->exists($path)) return trim($file->read($path));
        else return '';
    }

    /**
     * Get marker infowindow for showing on the map
     * @param array $marker
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_marker_infowindow($marker)
    {
        $count = count($marker['event_ids']);

        $content = '
        <div class="mec-marker-infowindow-wp">
            <div class="mec-marker-infowindow-count">' . esc_html($count) . '</div>
            <div class="mec-marker-infowindow-content">
                <span>' . ($count > 1 ? esc_html__('Events at this location', 'modern-events-calendar-lite') : esc_html__('Event at this location', 'modern-events-calendar-lite')) . '</span>
                <span>' . (trim($marker['address']) ? $marker['address'] : $marker['name']) . '</span>
            </div>
        </div>';

        return apply_filters('mec_get_marker_infowindow', $content);
    }

    /**
     * Get marker Lightbox for showing on the map
     * @param object $event
     * @param string $date_format
     * @param string $skin_style
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_marker_lightbox($event, $date_format = 'M d Y', $skin_style = 'classic')
    {
        $ex_format = explode(' ', $date_format);
        $format_1 = $ex_format[0] ?? 'M';
        $format_2 = $ex_format[1] ?? 'd';
        $format_3 = $ex_format[2] ?? 'Y';

        $link = $this->get_event_date_permalink($event, (isset($event->date['start']) ? $event->date['start']['date'] : null));
        $infowindow_thumb = trim($event->data->featured_image['thumbnail']) ? '<div class="mec-event-image"><a data-event-id="' . esc_attr($event->data->ID) . '" href="' . esc_url($link) . '"><img src="' . esc_url($event->data->featured_image['thumbnail']) . '" alt="' . esc_attr($event->data->title) . '" /></a></div>' : '';
        $event_start_date_day = !empty($event->date['start']['date']) ? $this->date_i18n($format_1, strtotime($event->date['start']['date'])) : '';
        $event_start_date_month = !empty($event->date['start']['date']) ? $this->date_i18n($format_2, strtotime($event->date['start']['date'])) : '';
        $event_start_date_year = !empty($event->date['start']['date']) ? $this->date_i18n($format_3, strtotime($event->date['start']['date'])) : '';

        $content = '
		<div class="mec-wrap">
			<div class="mec-map-lightbox-wp mec-event-list-classic">
				<article class="' . ((isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : '') . 'mec-event-article mec-clear">
					' . MEC_kses::element($infowindow_thumb) . '
                    <a data-event-id="' . esc_attr($event->data->ID) . '" href="' . esc_url($link) . '"><div class="mec-event-date mec-color"><i class="mec-sl-calendar"></i> <span class="mec-map-lightbox-month">' . esc_html($event_start_date_month) . '</span><span class="mec-map-lightbox-day"> ' . esc_html($event_start_date_day) . '</span><span class="mec-map-lightbox-year"> ' . esc_html($event_start_date_year) . '</span></div></a>
                    <h4 class="mec-event-title">
                    <a data-event-id="' . esc_attr($event->data->ID) . '" class="mec-color-hover" href="' . esc_url($link) . '">' . esc_html($event->data->title) . '</a>
                    ' . MEC_kses::element($this->get_flags($event)) . '
                    </h4>
				</article>
			</div>
		</div>';

        return apply_filters('mec_get_marker_lightbox', $content, $event, $date_format, $skin_style);
    }

    /**
     * Returns available social networks
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_social_networks()
    {
        $social_networks = [
            'facebook' => ['id' => 'facebook', 'name' => __('Facebook', 'modern-events-calendar-lite'), 'function' => [$this, 'sn_facebook']],
            'twitter' => ['id' => 'twitter', 'name' => __('Twitter', 'modern-events-calendar-lite'), 'function' => [$this, 'sn_twitter']],
            'linkedin' => ['id' => 'linkedin', 'name' => __('Linkedin', 'modern-events-calendar-lite'), 'function' => [$this, 'sn_linkedin']],
            'vk' => ['id' => 'vk', 'name' => __('VK', 'modern-events-calendar-lite'), 'function' => [$this, 'sn_vk']],
            'tumblr' => ['id' => 'tumblr', 'name' => __('Tumblr', 'modern-events-calendar-lite'), 'function' => [$this, 'sn_tumblr']],
            'pinterest' => ['id' => 'pinterest', 'name' => __('Pinterest', 'modern-events-calendar-lite'), 'function' => [$this, 'sn_pinterest']],
            'flipboard' => ['id' => 'flipboard', 'name' => __('Flipboard', 'modern-events-calendar-lite'), 'function' => [$this, 'sn_flipboard']],
            'pocket' => ['id' => 'pocket', 'name' => __('GetPocket', 'modern-events-calendar-lite'), 'function' => [$this, 'sn_pocket']],
            'reddit' => ['id' => 'reddit', 'name' => __('Reddit', 'modern-events-calendar-lite'), 'function' => [$this, 'sn_reddit']],
            'whatsapp' => ['id' => 'whatsapp', 'name' => __('WhatsApp', 'modern-events-calendar-lite'), 'function' => [$this, 'sn_whatsapp']],
            'telegram' => ['id' => 'telegram', 'name' => __('Telegram', 'modern-events-calendar-lite'), 'function' => [$this, 'sn_telegram']],
            'email' => ['id' => 'email', 'name' => __('Email', 'modern-events-calendar-lite'), 'function' => [$this, 'sn_email']],
        ];

        return apply_filters('mec_social_networks', $social_networks);
    }

    /**
     * Do facebook link for social networks
     * @param string $url
     * @param object $event
     * @param array $social
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function sn_facebook($url, $event, $social = [])
    {
        $occurrence = (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '');
        if (trim($occurrence) != '') $url = $this->add_qs_var('occurrence', $occurrence, $url);

        return '<li class="mec-event-social-icon"><a class="facebook" href="https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($url) . '" onclick="javascript:window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=500,width=600\'); return false;" target="_blank" title="' . esc_attr__('Share on Facebook', 'modern-events-calendar-lite') . '"><i class="mec-fa-facebook"></i><span class="mec-social-title">' . ($social['name'] ?? '') . '</span></a></li>';
    }

    /**
     * Do twitter link for social networks
     * @param string $url
     * @param object $event
     * @param array $social
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function sn_twitter($url, $event, $social = [])
    {
        $occurrence = (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '');
        if (trim($occurrence) != '') $url = $this->add_qs_var('occurrence', $occurrence, $url);

        return '<li class="mec-event-social-icon"><a class="twitter" href="https://twitter.com/share?url=' . rawurlencode($url) . '" onclick="javascript:window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=500\'); return false;" target="_blank" title="' . esc_attr__('X Social Network', 'modern-events-calendar-lite') . '"><svg xmlns="http://www.w3.org/2000/svg" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd" viewBox="0 0 512 462.799"><path fill-rule="nonzero" d="M403.229 0h78.506L310.219 196.04 512 462.799H354.002L230.261 301.007 88.669 462.799h-78.56l183.455-209.683L0 0h161.999l111.856 147.88L403.229 0zm-27.556 415.805h43.505L138.363 44.527h-46.68l283.99 371.278z"/></svg><span class="mec-social-title">' . ($social['name'] ?? '') . '</span></a></li>';
    }

    /**
     * Do linkedin link for social networks
     * @param string $url
     * @param object $event
     * @param array $social
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function sn_linkedin($url, $event, $social = [])
    {
        $occurrence = (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '');
        if (trim($occurrence) != '') $url = $this->add_qs_var('occurrence', $occurrence, $url);

        return '<li class="mec-event-social-icon"><a class="linkedin" href="https://www.linkedin.com/shareArticle?mini=true&url=' . rawurlencode($url) . '" onclick="javascript:window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=500\'); return false;" target="_blank" title="' . esc_attr__('Linkedin', 'modern-events-calendar-lite') . '"><i class="mec-fa-linkedin"></i><span class="mec-social-title">' . ($social['name'] ?? '') . '</span></a></li>';
    }

    /**
     * Do email link for social networks
     * @param string $url
     * @param object $event
     * @param array $social
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function sn_email($url, $event, $social = [])
    {
        $occurrence = (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '');
        if (trim($occurrence) != '') $url = $this->add_qs_var('occurrence', $occurrence, $url);

        $event->data->title = str_replace('&#8211;', '-', $event->data->title);
        $event->data->title = str_replace('&#8221;', '’’', $event->data->title);
        $event->data->title = str_replace('&#8217;', "’", $event->data->title);
        $event->data->title = str_replace('&', '%26', $event->data->title);
        $event->data->title = str_replace('#038;', '', $event->data->title);

        return '<li class="mec-event-social-icon"><a class="email" href="mailto:?subject=' . rawurlencode($event->data->title) . '&body=' . rawurlencode($url) . '" title="' . esc_attr__('Email', 'modern-events-calendar-lite') . '"><i class="mec-fa-envelope"></i><span class="mec-social-title">' . ($social['name'] ?? '') . '</span></a></li>';
    }

    /**
     * Do VK link for social networks
     * @param string $url
     * @param object $event
     * @param array $social
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function sn_vk($url, $event, $social = [])
    {
        $occurrence = (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '');
        if (trim($occurrence) != '') $url = $this->add_qs_var('occurrence', $occurrence, $url);

        return '<li class="mec-event-social-icon"><a class="vk" href=" http://vk.com/share.php?url=' . rawurlencode($url) . '" title="' . esc_attr__('VK', 'modern-events-calendar-lite') . '" target="_blank"><i class="mec-fa-vk"></i><span class="mec-social-title">' . ($social['name'] ?? '') . '</span></a></li>';
    }


    /**
     * Do tumblr link for social networks
     * @param string $url
     * @param object $event
     * @param array $social
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function sn_tumblr($url, $event, $social = [])
    {
        $occurrence = (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '');
        if (trim($occurrence) != '') $url = $this->add_qs_var('occurrence', $occurrence, $url);
        return '<li class="mec-event-social-icon"><a class="tumblr" href="https://www.tumblr.com/widgets/share/tool?canonicalUrl=' . rawurlencode($url) . '&title' . rawurlencode($event->data->title) . '&caption=' . rawurlencode($event->data->title) . '" target="_blank" title="' . esc_attr__('Share on Tumblr', 'modern-events-calendar-lite') . '"><i class="mec-fa-tumblr"></i><span class="mec-social-title">' . ($social['name'] ?? '') . '</span></a></li>';
    }

    /**
     * Do pinterest link for social networks
     * @param string $url
     * @param object $event
     * @param array $social
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function sn_pinterest($url, $event, $social = [])
    {
        $occurrence = (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '');
        if (trim($occurrence) != '') $url = $this->add_qs_var('occurrence', $occurrence, $url);

        return '<li class="mec-event-social-icon"><a class="pinterest" href="http://pinterest.com/pin/create/button/?url=' . rawurlencode($url) . '" target="_blank" title="' . esc_attr__('Share on Pinterest', 'modern-events-calendar-lite') . '"><i class="mec-fa-pinterest"></i><span class="mec-social-title">' . ($social['name'] ?? '') . '</span></a></li>';
    }

    /**
     * Do flipboard link for social networks
     * @param string $url
     * @param object $event
     * @param array $social
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function sn_flipboard($url, $event, $social = [])
    {
        $occurrence = (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '');
        if (trim($occurrence) != '') $url = $this->add_qs_var('occurrence', $occurrence, $url);

        return '<li class="mec-event-social-icon"><a class="flipboard" href="https://share.flipboard.com/bookmarklet/popout?v=2&title=' . esc_attr($event->data->title) . '&url=' . rawurlencode($url) . '" target="_blank" title="' . esc_attr__('Share on Flipboard', 'modern-events-calendar-lite') . '">
            <i><svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="flipboard" class="svg-inline--fa fa-flipboard fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M0 32v448h448V32H0zm358.4 179.2h-89.6v89.6h-89.6v89.6H89.6V121.6h268.8v89.6z"></path></svg></i>
            <span class="mec-social-title">' . ($social['name'] ?? '') . '</span>
        </a></li>';
    }

    /**
     * Do pocket link for social networks
     * @param string $url
     * @param object $event
     * @param array $social
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function sn_pocket($url, $event, $social = [])
    {
        $occurrence = (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '');
        if (trim($occurrence) != '') $url = $this->add_qs_var('occurrence', $occurrence, $url);

        return '<li class="mec-event-social-icon"><a class="pocket" href="https://getpocket.com/edit?url=' . rawurlencode($url) . '" target="_blank" title="' . esc_attr__('Share on GetPocket', 'modern-events-calendar-lite') . '"><i class="mec-fa-get-pocket"></i><span class="mec-social-title">' . ($social['name'] ?? '') . '</span></a></li>';
    }

    /**
     * Do reddit link for social networks
     * @param string $url
     * @param object $event
     * @param array $social
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function sn_reddit($url, $event, $social = [])
    {
        $occurrence = (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '');
        if (trim($occurrence) != '') $url = $this->add_qs_var('occurrence', $occurrence, $url);

        return '<li class="mec-event-social-icon"><a class="reddit" href="https://reddit.com/submit?url=' . rawurlencode($url) . '&title=' . esc_attr($event->data->title) . '" target="_blank" title="' . esc_attr__('Share on Reddit', 'modern-events-calendar-lite') . '"><i class="mec-fa-reddit"></i><span class="mec-social-title">' . ($social['name'] ?? '') . '</span></a></li>';
    }

    /**
     * Do telegram link for social networks
     * @param string $url
     * @param object $event
     * @param array $social
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function sn_telegram($url, $event, $social = [])
    {
        $occurrence = (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '');
        if (trim($occurrence) != '') $url = $this->add_qs_var('occurrence', $occurrence, $url);

        return '<li class="mec-event-social-icon"><a class="telegram" href="https://telegram.me/share/url?url=' . rawurlencode($url) . '&text=' . esc_attr($event->data->title) . '" target="_blank" title="' . esc_attr__('Share on Telegram', 'modern-events-calendar-lite') . '">
            <i><svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="telegram" class="svg-inline--fa fa-telegram fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512"><path fill="currentColor" d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm121.8 169.9l-40.7 191.8c-3 13.6-11.1 16.9-22.4 10.5l-62-45.7-29.9 28.8c-3.3 3.3-6.1 6.1-12.5 6.1l4.4-63.1 114.9-103.8c5-4.4-1.1-6.9-7.7-2.5l-142 89.4-61.2-19.1c-13.3-4.2-13.6-13.3 2.8-19.7l239.1-92.2c11.1-4 20.8 2.7 17.2 19.5z"></path></svg></i>
            <span class="mec-social-title">' . ($social['name'] ?? '') . '</span>
        </a></li>';
    }

    /**
     * Do whatsapp link for social networks
     * @param string $url
     * @param object $event
     * @param array $social
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function sn_whatsapp($url, $event, $social = [])
    {
        $occurrence = (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '');
        if (trim($occurrence) != '') $url = $this->add_qs_var('occurrence', $occurrence, $url);

        return '<li class="mec-event-social-icon"><a class="whatsapp" href="https://api.whatsapp.com/send?text=' . rawurlencode($url) . '" target="_blank" title="' . esc_attr__('Share on WhatsApp', 'modern-events-calendar-lite') . '"><i class="mec-fa-whatsapp"></i><span class="mec-social-title">' . ($social['name'] ?? '') . '</span></a></li>';
    }

    /**
     * Get available skins for archive page
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_archive_skins()
    {
        if (!$this->getPRO())
        {
            $archive_skins = [
                ['skin' => 'full_calendar', 'name' => __('Full Calendar', 'modern-events-calendar-lite')],
                ['skin' => 'monthly_view', 'name' => __('Calendar/Monthly View', 'modern-events-calendar-lite')],
                ['skin' => 'weekly_view', 'name' => __('Weekly View', 'modern-events-calendar-lite')],
                ['skin' => 'daily_view', 'name' => __('Daily View', 'modern-events-calendar-lite')],
                ['skin' => 'list', 'name' => __('List View', 'modern-events-calendar-lite')],
                ['skin' => 'grid', 'name' => __('Grid View', 'modern-events-calendar-lite')],
                ['skin' => 'general_calendar', 'name' => __('General Calendar', 'modern-events-calendar-lite')],
                ['skin' => 'custom', 'name' => __('Custom Shortcode', 'modern-events-calendar-lite')],
            ];
        }
        else
        {
            $archive_skins = [
                ['skin' => 'full_calendar', 'name' => __('Full Calendar', 'modern-events-calendar-lite')],
                ['skin' => 'yearly_view', 'name' => __('Yearly View', 'modern-events-calendar-lite')],
                ['skin' => 'monthly_view', 'name' => __('Calendar/Monthly View', 'modern-events-calendar-lite')],
                ['skin' => 'weekly_view', 'name' => __('Weekly View', 'modern-events-calendar-lite')],
                ['skin' => 'daily_view', 'name' => __('Daily View', 'modern-events-calendar-lite')],
                ['skin' => 'timetable', 'name' => __('Timetable View', 'modern-events-calendar-lite')],
                ['skin' => 'masonry', 'name' => __('Masonry View', 'modern-events-calendar-lite')],
                ['skin' => 'list', 'name' => __('List View', 'modern-events-calendar-lite')],
                ['skin' => 'grid', 'name' => __('Grid View', 'modern-events-calendar-lite')],
                ['skin' => 'agenda', 'name' => __('Agenda View', 'modern-events-calendar-lite')],
                ['skin' => 'map', 'name' => __('Map View', 'modern-events-calendar-lite')],
                ['skin' => 'general_calendar', 'name' => __('General Calendar', 'modern-events-calendar-lite')],
                ['skin' => 'custom', 'name' => __('Custom Shortcode', 'modern-events-calendar-lite')],
            ];
        }

        return apply_filters('mec_archive_skins', $archive_skins);
    }

    /**
     * Get available skins for archive page
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_category_skins()
    {
        if (!$this->getPRO())
        {
            $category_skins = [
                ['skin' => 'full_calendar', 'name' => __('Full Calendar', 'modern-events-calendar-lite')],
                ['skin' => 'monthly_view', 'name' => __('Calendar/Monthly View', 'modern-events-calendar-lite')],
                ['skin' => 'weekly_view', 'name' => __('Weekly View', 'modern-events-calendar-lite')],
                ['skin' => 'daily_view', 'name' => __('Daily View', 'modern-events-calendar-lite')],
                ['skin' => 'list', 'name' => __('List View', 'modern-events-calendar-lite')],
                ['skin' => 'grid', 'name' => __('Grid View', 'modern-events-calendar-lite')],
                ['skin' => 'general_calendar', 'name' => __('General Calendar', 'modern-events-calendar-lite')],
                ['skin' => 'custom', 'name' => __('Custom Shortcode', 'modern-events-calendar-lite')],
            ];
        }
        else
        {
            $category_skins = [
                ['skin' => 'full_calendar', 'name' => __('Full Calendar', 'modern-events-calendar-lite')],
                ['skin' => 'yearly_view', 'name' => __('Yearly View', 'modern-events-calendar-lite')],
                ['skin' => 'monthly_view', 'name' => __('Calendar/Monthly View', 'modern-events-calendar-lite')],
                ['skin' => 'weekly_view', 'name' => __('Weekly View', 'modern-events-calendar-lite')],
                ['skin' => 'daily_view', 'name' => __('Daily View', 'modern-events-calendar-lite')],
                ['skin' => 'timetable', 'name' => __('Timetable View', 'modern-events-calendar-lite')],
                ['skin' => 'masonry', 'name' => __('Masonry View', 'modern-events-calendar-lite')],
                ['skin' => 'list', 'name' => __('List View', 'modern-events-calendar-lite')],
                ['skin' => 'grid', 'name' => __('Grid View', 'modern-events-calendar-lite')],
                ['skin' => 'agenda', 'name' => __('Agenda View', 'modern-events-calendar-lite')],
                ['skin' => 'map', 'name' => __('Map View', 'modern-events-calendar-lite')],
                ['skin' => 'general_calendar', 'name' => __('General Calendar', 'modern-events-calendar-lite')],
                ['skin' => 'custom', 'name' => __('Custom Shortcode', 'modern-events-calendar-lite')],
            ];
        }

        return apply_filters('mec_category_skins', $category_skins);
    }

    public function get_events($limit = -1, $status = ['publish'], $page = 1, $cache_results = true)
    {
        $cache_key = null;

        // Use transient caching for commonly requested queries
        if($cache_results && $limit != -1 && $status == ['publish']) {
            $cache_key = 'mec_events_' . md5(serialize($status) . $limit . $page);
            $cached_result = get_transient($cache_key);

            if($cached_result !== false) {
                return $cached_result;
            }
        }

        // Use more efficient WP_Query instead of get_posts for better hook support
        $args = [
            'post_type' => $this->get_main_post_type(),
            'posts_per_page' => $limit,
            'post_status' => $status,
            'no_found_rows' => true,
            'orderby' => 'ID',
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ];

        // Add pagination if needed
        if($limit != -1 && $page > 1) {
            $args['paged'] = $page;
        }

        $query = new WP_Query($args);
        $result = $query->posts;

        // Cache the result
        if($cache_results && $limit != -1 && $cache_key) {
            set_transient($cache_key, $result, 5 * MINUTE_IN_SECONDS);
        }

        return $result;
    }

    /**
     * Get id of ongoing events
     * @param int $now
     * @param null $status
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_ongoing_event_ids($now = null, $status = null)
    {
        // Database Object
        $db = $this->getDB();

        $status_query = '';
        if ($status) $status_query .= " AND `status`='" . esc_sql($status) . "'";

        $ids = $db->select("SELECT `post_id` FROM `#__mec_dates` WHERE `tstart` <= " . $now . " AND `tend` > " . $now . $status_query, 'loadColumn');
        return array_unique($ids);
    }

    /**
     * Get id of upcoming events
     * @param int $now
     * @param null $status
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_upcoming_event_ids($now = null, $status = null)
    {
        // Database Object
        $db = $this->getDB();

        // Current Timestamp
        $start = (($now and is_numeric($now)) ? $now : current_time('timestamp'));

        $status_query = '';
        if ($status) $status_query .= " AND `status`='" . esc_sql($status) . "'";

        $ids = $db->select("SELECT `post_id` FROM `#__mec_dates` WHERE `tstart` >= " . $start . $status_query, 'loadColumn');
        return array_unique($ids);
    }

    /**
     * Get id of expired events
     * @param int $now
     * @param null $status
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_expired_event_ids($now = null, $status = null)
    {
        // Database Object
        $db = $this->getDB();

        // Current Timestamp
        $end = (($now and is_numeric($now)) ? $now : current_time('timestamp', 0));

        $status_query = '';
        if ($status) $status_query .= " AND `status`='" . esc_sql($status) . "'";

        $ids = $db->select("SELECT `post_id` FROM `#__mec_dates` WHERE `tend` <= " . $end . $status_query, 'loadColumn');
        return array_unique($ids);
    }

    /**
     * Get id of all events
     * @param null $status
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_all_event_ids($status = null)
    {
        // Database Object
        $db = $this->getDB();

        $status_query = '';
        if ($status) $status_query .= " AND `status`='" . esc_sql($status) . "'";

        $ids = $db->select("SELECT `post_id` FROM `#__mec_dates` WHERE 1" . $status_query, 'loadColumn');
        return array_unique($ids);
    }

    /**
     * Get id of events by period
     * @param string|int $start
     * @param string|int $end
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_event_ids_by_period($start, $end)
    {
        if (!is_numeric($start)) $start = strtotime($start);
        if (!is_numeric($end)) $end = strtotime($end);

        // Database Object
        $db = $this->getDB();

        return $db->select("SELECT `post_id` FROM `#__mec_dates` WHERE (`tstart` <= " . $start . " AND `tend` >= " . $end . ") OR (`tstart` > " . $start . " AND `tend` < " . $end . ") OR (`tstart` > " . $start . " AND `tstart` < " . $end . " AND `tend` >= " . $end . ") OR (`tstart` <= " . $start . " AND `tend` > " . $start . " AND `tend` < " . $end . ")", 'loadColumn');
    }

    public function get_filtered_events($locations = [], $categories = [], $organizers = [])
    {
        // Taxonomy Query
        $tax_query = [];

        // Filter by Location
        if (count($locations))
        {
            $tax_query[] = [
                'taxonomy' => 'mec_location',
                'field' => 'term_id',
                'terms' => $locations,
                'operator' => 'IN',
            ];
        }

        // Filter by Categories
        if (count($categories))
        {
            $tax_query[] = [
                'taxonomy' => 'mec_category',
                'field' => 'term_id',
                'terms' => $categories,
                'operator' => 'IN',
            ];
        }

        // Filter by Organizers
        if (count($organizers))
        {
            $tax_query[] = [
                'taxonomy' => 'mec_organizer',
                'field' => 'term_id',
                'terms' => $organizers,
                'operator' => 'IN',
            ];
        }

        // Filter Events
        return get_posts([
            'post_type' => $this->get_main_post_type(),
            'numberposts' => -1,
            'post_status' => ['publish'],
            'tax_query' => $tax_query,
        ]);
    }

    /**
     * Get method of showing for multiple days events
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_multiple_days_method()
    {
        $settings = $this->get_settings();

        $method = $settings['multiple_day_show_method'] ?? 'first_day_listgrid';
        return apply_filters('mec_multiple_days_method', $method);
    }

    /**
     * Get method of showing/hiding events based on event time
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_hide_time_method()
    {
        $settings = $this->get_settings();

        $method = $settings['hide_time_method'] ?? 'start';
        return apply_filters('mec_hide_time_method', $method);
    }

    /**
     * Get custom hour for hide time
     * @return int
     * @author Webnus <info@webnus.net>
     */
    public function get_hide_time_n()
    {
        $settings = $this->get_settings();

        $n = $settings['hide_time_n'] ?? '2';
        return (int) apply_filters('mec_hide_time_n', $n);
    }

    /**
     * Get hour format of MEC
     * @return int|string
     * @author Webnus <info@webnus.net>
     */
    public function get_hour_format()
    {
        $settings = $this->get_settings();

        $format = isset($settings['time_format']) ? $settings['time_format'] : 12;
        return apply_filters('mec_hour_format', $format);
    }

    /**
     * Get formatted hour based on configurations
     * @param int $hour
     * @param int $minutes
     * @param string $ampm
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_formatted_hour($hour, $minutes, $ampm)
    {
        // Hour Format of MEC (12/24)
        $hour_format = $this->get_hour_format();

        $formatted = '';
        if ($hour_format == '12')
        {
            $formatted = sprintf("%02d", $hour) . ':' . sprintf("%02d", $minutes) . ' ' . esc_html__($ampm, 'modern-events-calendar-lite');
        }
        else if ($hour_format == '24')
        {
            if (strtoupper($ampm) == 'PM' and $hour != 12) $hour += 12;
            if (strtoupper($ampm) == 'AM' and $hour == 12) $hour += 12;

            $formatted = sprintf("%02d", $hour) . ':' . sprintf("%02d", $minutes);
        }

        return $formatted;
    }

    /**
     * Get formatted time based on WordPress Time Format
     * @param int $seconds
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_time($seconds)
    {
        $format = get_option('time_format');
        if (trim($format) === '') $format = 'H:i';

        return gmdate($format, $seconds);
    }

    /**
     * Renders a module such as links or googlemap
     * @param string $module
     * @param array $params
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function module($module, $params = [])
    {
        // Get module path
        $path = MEC::import('app.modules.' . $module, true, true);

        // MEC libraries
        $render = $this->getRender();
        $factory = $this->getFactory();

        // Extract Module Params
        extract($params);

        ob_start();
        include $path;
        return $output = ob_get_clean();
    }

    /**
     * Returns MEC currencies
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_currencies()
    {
        $currencies = [
            '$' => 'USD',
            '€' => 'EUR',
            '£' => 'GBP',
            'CHF' => 'CHF',
            'CAD' => 'CAD',
            'AUD' => 'AUD',
            'JPY' => 'JPY',
            'SEK' => 'SEK',
            'GEL' => 'GEL',
            'AFN' => 'AFN',
            'ALL' => 'ALL',
            'DZD' => 'DZD',
            'AOA' => 'AOA',
            'ARS' => 'ARS',
            'AMD' => 'AMD',
            'AWG' => 'AWG',
            'AZN' => 'AZN',
            'BSD' => 'BSD',
            'BHD' => 'BHD',
            'BBD' => 'BBD',
            'BYR' => 'BYR',
            'BZD' => 'BZD',
            'BMD' => 'BMD',
            'BTN' => 'BTN',
            'BOB' => 'BOB',
            'BAM' => 'BAM',
            'BWP' => 'BWP',
            'BRL' => 'BRL',
            'BND' => 'BND',
            'BGN' => 'BGN',
            'BIF' => 'BIF',
            'KHR' => 'KHR',
            'CVE' => 'CVE',
            'KYD' => 'KYD',
            'XAF' => 'XAF',
            'CLP' => 'CLP',
            'COP' => 'COP',
            'KMF' => 'KMF',
            'CDF' => 'CDF',
            'NZD' => 'NZD',
            'CRC' => 'CRC',
            'HRK' => 'HRK',
            'CUC' => 'CUC',
            'CUP' => 'CUP',
            'CZK' => 'CZK',
            'DKK' => 'DKK',
            'DJF' => 'DJF',
            'DOP' => 'DOP',
            'XCD' => 'XCD',
            'EGP' => 'EGP',
            'ERN' => 'ERN',
            'EEK' => 'EEK',
            'ETB' => 'ETB',
            'FKP' => 'FKP',
            'FJD' => 'FJD',
            'GMD' => 'GMD',
            'GHS' => 'GHS',
            'GIP' => 'GIP',
            'GTQ' => 'GTQ',
            'GNF' => 'GNF',
            'GYD' => 'GYD',
            'HTG' => 'HTG',
            'HNL' => 'HNL',
            'HKD' => 'HKD',
            'HUF' => 'HUF',
            'ISK' => 'ISK',
            'INR' => 'INR',
            'IDR' => 'IDR',
            'IRR' => 'IRR',
            'IQD' => 'IQD',
            'ILS' => 'ILS',
            'NIS' => 'NIS',
            'JMD' => 'JMD',
            'JOD' => 'JOD',
            'KZT' => 'KZT',
            'KES' => 'KES',
            'KWD' => 'KWD',
            'KGS' => 'KGS',
            'LAK' => 'LAK',
            'LVL' => 'LVL',
            'LBP' => 'LBP',
            'LSL' => 'LSL',
            'LRD' => 'LRD',
            'LYD' => 'LYD',
            'LTL' => 'LTL',
            'MOP' => 'MOP',
            'MKD' => 'MKD',
            'MGA' => 'MGA',
            'MWK' => 'MWK',
            'MYR' => 'MYR',
            'MVR' => 'MVR',
            'MRO' => 'MRO',
            'MUR' => 'MUR',
            'MXN' => 'MXN',
            'MDL' => 'MDL',
            'MNT' => 'MNT',
            'MAD' => 'MAD',
            'MZN' => 'MZN',
            'MMK' => 'MMK',
            'NAD' => 'NAD',
            'NPR' => 'NPR',
            'ANG' => 'ANG',
            'TWD' => 'TWD',
            'NIO' => 'NIO',
            'NGN' => 'NGN',
            'KPW' => 'KPW',
            'NOK' => 'NOK',
            'OMR' => 'OMR',
            'PKR' => 'PKR',
            'PAB' => 'PAB',
            'PGK' => 'PGK',
            'PYG' => 'PYG',
            'PEN' => 'PEN',
            'PHP' => 'PHP',
            'PLN' => 'PLN',
            'QAR' => 'QAR',
            'CNY' => 'CNY',
            'RON' => 'RON',
            'RUB' => 'RUB',
            'RWF' => 'RWF',
            'SHP' => 'SHP',
            'SVC' => 'SVC',
            'WST' => 'WST',
            'SAR' => 'SAR',
            'RSD' => 'RSD',
            'SCR' => 'SCR',
            'SLL' => 'SLL',
            'SGD' => 'SGD',
            'SBD' => 'SBD',
            'SOS' => 'SOS',
            'ZAR' => 'ZAR',
            'KRW' => 'KRW',
            'LKR' => 'LKR',
            'SDG' => 'SDG',
            'SRD' => 'SRD',
            'SZL' => 'SZL',
            'SYP' => 'SYP',
            'STD' => 'STD',
            'TJS' => 'TJS',
            'TZS' => 'TZS',
            'THB' => 'THB',
            'TOP' => 'TOP',
            'PRB' => 'PRB',
            'TTD' => 'TTD',
            'TND' => 'TND',
            'TRY' => 'TRY',
            'TMT' => 'TMT',
            'TVD' => 'TVD',
            'UGX' => 'UGX',
            'UAH' => 'UAH',
            'AED' => 'AED',
            'UYU' => 'UYU',
            'UZS' => 'UZS',
            'VUV' => 'VUV',
            'VEF' => 'VEF',
            'VND' => 'VND',
            'XOF' => 'XOF',
            'YER' => 'YER',
            'ZMK' => 'ZMK',
            'ZWL' => 'ZWL',
            'BDT' => 'BDT',
        ];

        return apply_filters('mec_currencies', $currencies);
    }

    /**
     * Returns MEC version
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_version()
    {
        $version = MEC_VERSION;

        if (defined('WP_DEBUG') and WP_DEBUG) $version .= '.' . time();
        return $version;
    }

    /**
     * Set endpoint vars to true
     * @param array $vars
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function filter_request($vars)
    {
        if (isset($vars['gateway-cancel'])) $vars['gateway-cancel'] = true;
        if (isset($vars['gateway-return'])) $vars['gateway-return'] = true;
        if (isset($vars['gateway-notify'])) $vars['gateway-notify'] = true;

        return $vars;
    }

    /**
     * Do the jobs after endpoints and show related output
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function do_endpoints()
    {
        if (get_query_var('verify'))
        {
            $key = sanitize_text_field(get_query_var('verify'));

            $db = $this->getDB();
            $book_id = $db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_key`='mec_verification_key' AND `meta_value`='$key'", 'loadResult');

            if (!$book_id) return false;

            $status = get_post_meta($book_id, 'mec_verified', true);
            if ($status == '1')
            {
                $status_user = get_post_meta($book_id, 'mec_verified_user', true);
                if (trim($status_user) == '') $status_user = 0;

                if (!$status_user)
                {
                    // User Status
                    update_post_meta($book_id, 'mec_verified_user', 1);

                    echo '<p class="mec-success">' . esc_html__('Your booking has been verified successfully!', 'modern-events-calendar-lite') . '</p>';
                    return false;
                }
                else
                {
                    echo '<p class="mec-success">' . esc_html__('Your booking already verified!', 'modern-events-calendar-lite') . '</p>';
                    return false;
                }
            }

            $book = $this->getBook();
            if ($book->verify($book_id))
            {
                // Confirm Booking After Verification
                $confirmation_status = get_post_meta($book_id, 'mec_confirmed', true);
                if (!$confirmation_status)
                {
                    $event_id = get_post_meta($book_id, 'mec_event_id', true);
                    $price = get_post_meta($book_id, 'mec_price', true);

                    [$auto_confirm_free, $auto_confirm_paid] = $book->get_auto_confirmation_status($event_id, $book_id);

                    // Auto confirmation for free bookings is enabled
                    if ($price <= 0 and $auto_confirm_free)
                    {
                        $book->confirm($book_id, 'auto');
                    }

                    // Auto confirmation for paid bookings is enabled
                    if ($price > 0 and $auto_confirm_paid)
                    {
                        $book->confirm($book_id, 'auto');
                    }
                }

                echo '<p class="mec-success">' . esc_html__('Your booking has been verified successfully!', 'modern-events-calendar-lite') . '</p>';
            }
            else echo '<p class="mec-error">' . esc_html__('Your booking cannot verify!', 'modern-events-calendar-lite') . '</p>';
        }
        else if (get_query_var('cancel'))
        {
            $key = sanitize_text_field(get_query_var('cancel'));
            $sure = isset($_GET['mec-sure']) ? (int) $_GET['mec-sure'] : 0;

            $db = $this->getDB();
            $book_id = $db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_key`='mec_cancellation_key' AND `meta_value`='$key'", 'loadResult');

            if (!$book_id) return false;

            $status = get_post_meta($book_id, 'mec_verified', true);
            if ($status == '-1')
            {
                $status_user = get_post_meta($book_id, 'mec_canceled_user', true);
                if (trim($status_user) == '') $status_user = 0;

                if (!$status_user)
                {
                    // User Status
                    update_post_meta($book_id, 'mec_canceled_user', 1);

                    echo '<p class="mec-success">' . esc_html__('Your booking successfully canceled.', 'modern-events-calendar-lite') . '</p>';
                }
                else
                {
                    echo '<p class="mec-success">' . esc_html__('Your booking already canceled!', 'modern-events-calendar-lite') . '</p>';
                }

                return false;
            }

            $timestamps = explode(':', get_post_meta($book_id, 'mec_date', true));
            $start = $timestamps[0];
            $end = $timestamps[1];

            $right_now = current_time('timestamp', 0);
            if ($right_now >= $end)
            {
                echo '<p class="mec-error">' . esc_html__('The event is already finished!', 'modern-events-calendar-lite') . '</p>';
                return false;
            }

            // MEC Settings
            $settings = $this->get_settings();

            $cancellation_period_from = $settings['cancellation_period_from'] ?? 0;
            $cancellation_period_to = $settings['cancellation_period_time'] ?? 0;
            $cancellation_period_p = $settings['cancellation_period_p'] ?? 'hour';
            $cancellation_period_type = $settings['cancellation_period_type'] ?? 'before';

            if ($cancellation_period_from or $cancellation_period_to)
            {
                if ($cancellation_period_from)
                {
                    if ($cancellation_period_type == 'before') $min_time = ($start - ($cancellation_period_from * ($cancellation_period_p == 'hour' ? 3600 : 86400)));
                    else $min_time = ($start + ($cancellation_period_from * ($cancellation_period_p == 'hour' ? 3600 : 86400)));

                    if ($right_now < $min_time)
                    {
                        echo '<p class="mec-error">' . esc_html__("The cancelation window is not started yet.", 'modern-events-calendar-lite') . '</p>';
                        return false;
                    }
                }

                if ($cancellation_period_to)
                {
                    if ($cancellation_period_type == 'before') $max_time = ($start - ($cancellation_period_to * ($cancellation_period_p == 'hour' ? 3600 : 86400)));
                    else $max_time = ($start + ($cancellation_period_to * ($cancellation_period_p == 'hour' ? 3600 : 86400)));

                    if ($right_now > $max_time)
                    {
                        echo '<p class="mec-error">' . esc_html__("The cancelation window is passed.", 'modern-events-calendar-lite') . '</p>';
                        return false;
                    }
                }
            }

            if (!$sure)
            {
                echo '<p class="warning-msg">' . esc_html__("Are you sure that you want to cancel the booking?", 'modern-events-calendar-lite') . ' <strong><a href="' . $this->add_qs_var('mec-sure', 1) . '">' . esc_html__('Yes, please cancel it.', 'modern-events-calendar-lite') . '</a></strong></p>';
                return false;
            }

            $book = $this->getBook();
            if ($book->cancel($book_id))
            {
                echo '<p class="mec-success">' . esc_html__('Your booking successfully canceled.', 'modern-events-calendar-lite') . '</p>';

                $cancel_page = (isset($settings['booking_cancel_page']) and trim($settings['booking_cancel_page'])) ? $settings['booking_cancel_page'] : null;
                $cancel_page_url = get_permalink($cancel_page);
                $cancel_page_time = (isset($settings['booking_cancel_page_time']) and trim($settings['booking_cancel_page_time']) != '') ? $settings['booking_cancel_page_time'] : 2500;

                if ($cancel_page and $cancel_page_url) echo '<script>setTimeout(function(){window.location.replace("' . esc_js($cancel_page_url) . '");}, ' . esc_js($cancel_page_time) . ');</script>';
            }
            else echo '<p class="mec-error">' . esc_html__('Your booking cannot be canceled.', 'modern-events-calendar-lite') . '</p>';
        }
        else if (get_query_var('gateway-cancel'))
        {
            echo '<p class="mec-success">' . esc_html__('You canceled the payment successfully.', 'modern-events-calendar-lite') . '</p>';
        }
        else if (get_query_var('gateway-return'))
        {
            echo '<p class="info-msg">' . esc_html__('You returned from payment gateway successfully.', 'modern-events-calendar-lite') . '</p>';
        }

        // Trigget Actions
        do_action('mec_gateway_do_endpoints', $this);
    }

    public function booking_invoice()
    {
        // Booking Invoice
        if (isset($_GET['method']) and sanitize_text_field($_GET['method']) == 'mec-invoice')
        {
            $settings = $this->get_settings();
            if (isset($settings['booking_invoice']) and !$settings['booking_invoice']) wp_die(__('Cannot find the invoice!', 'modern-events-calendar-lite'), esc_html__('Invoice is invalid.', 'modern-events-calendar-lite'));

            $ml_settings = $this->get_ml_settings();

            $transaction_id = sanitize_text_field($_GET['id']);

            // Libraries
            $book = $this->getBook();
            $render = $this->getRender();
            $db = $this->getDB();

            $transaction = $book->get_transaction($transaction_id);
            $event_id = $transaction['event_id'] ?? 0;
            $requested_event_id = $transaction['translated_event_id'] ?? $event_id;

            // Don't Show PDF If Booking is Pending
            $book_id = $db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_value`='" . $transaction_id . "' AND `meta_key`='mec_transaction_id'", 'loadResult');
            $mec_confirmed = get_post_meta($book_id, 'mec_confirmed', true);

            if (!$mec_confirmed and (!current_user_can('administrator') and !current_user_can('editor'))) wp_die(__('Your booking still is not confirmed. You can download it after confirmation!', 'modern-events-calendar-lite'), esc_html__('Booking Not Confirmed.', 'modern-events-calendar-lite'));
            if (!$event_id) wp_die(__('Cannot find the booking!', 'modern-events-calendar-lite'), esc_html__('Booking is invalid.', 'modern-events-calendar-lite'));

            // Invoice Key
            $invoice_key = $transaction['invoice_key'] ?? null;
            if ($invoice_key and (!isset($_GET['mec-key']) or sanitize_text_field($_GET['mec-key']) != $invoice_key)) wp_die(__("You don't have access to view this invoice!", 'modern-events-calendar-lite'), esc_html__('Key is invalid.', 'modern-events-calendar-lite'));

            $event = $render->data($event_id);

            $bfixed_fields = $this->get_bfixed_fields($event_id);
            $reg_fields = $this->get_reg_fields($event_id);

            $location_id = $this->get_master_location_id($event);
            $location = isset($event->locations[$location_id]) ? (trim($event->locations[$location_id]['address']) ? $event->locations[$location_id]['address'] : $event->locations[$location_id]['name']) : '';

            $dates = isset($transaction['date']) ? explode(':', $transaction['date']) : [time(), time()];

            // Multiple Dates
            $all_dates = ((isset($transaction['all_dates']) and is_array($transaction['all_dates'])) ? $transaction['all_dates'] : []);

            // Get Booking Post
            $booking = $book->get_bookings_by_transaction_id($transaction_id);

            $booking_time = isset($booking[0]) ? get_post_meta($booking[0]->ID, 'mec_booking_time', true) : null;
            if (!$booking_time and is_numeric($dates[0])) $booking_time = date('Y-m-d', $dates[0]);

            $booking_time = date('Y-m-d', strtotime($booking_time));

            // Coupon Code
            $coupon_code = isset($booking[0]) ? get_post_meta($booking[0]->ID, 'mec_coupon_code', true) : '';

            // Include the tFPDF Class
            if (!class_exists('tFPDF')) require_once MEC_ABSPATH . 'app' . DS . 'api' . DS . 'TFPDF' . DS . 'tfpdf.php';

            $pdf = new tFPDF();
            $pdf->AddPage();

            // Add a Unicode font (uses UTF-8)
            $pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
            $pdf->AddFont('DejaVuBold', '', 'DejaVuSansCondensed-Bold.ttf', true);

            $pdf->SetTitle(sprintf(esc_html__('%s Invoice', 'modern-events-calendar-lite'), $transaction_id));
            $pdf->SetAuthor(get_bloginfo('name'), true);

            // Event Information
            $pdf->SetFont('DejaVuBold', '', 18);
            $pdf->Write(25, html_entity_decode(get_the_title($event->ID)));
            $pdf->Ln();

            if (trim($location))
            {
                $pdf->SetFont('DejaVuBold', '', 12);
                $pdf->Write(6, esc_html__('Location', 'modern-events-calendar-lite') . ': ');
                $pdf->SetFont('DejaVu', '', 12);
                $pdf->Write(6, $location);
                $pdf->Ln();
            }

            $date_format = (isset($ml_settings['booking_date_format1']) and trim($ml_settings['booking_date_format1'])) ? $ml_settings['booking_date_format1'] : 'Y-m-d';
            $time_format = get_option('time_format');

            if (is_numeric($dates[0]) and is_numeric($dates[1]))
            {
                $start_datetime = date($date_format . ' ' . $time_format, $dates[0]);
                $end_datetime = date($date_format . ' ' . $time_format, $dates[1]);
            }
            else
            {
                $start_datetime = $dates[0] . ' ' . $event->data->time['start'];
                $end_datetime = $dates[1] . ' ' . $event->data->time['end'];
            }

            $booking_options = $event->meta['mec_booking'] ?? [];
            $bookings_all_occurrences = $booking_options['bookings_all_occurrences'] ?? 0;

            if (count($all_dates))
            {
                $pdf->SetFont('DejaVuBold', '', 12);
                $pdf->Write(6, esc_html__('Date and Times', 'modern-events-calendar-lite'));
                $pdf->Ln();
                $pdf->SetFont('DejaVu', '', 12);

                foreach ($all_dates as $one_date)
                {
                    $other_timestamps = explode(':', $one_date);
                    if (isset($other_timestamps[0]) and isset($other_timestamps[1]))
                    {
                        $pdf->Write(6, sprintf(esc_html__('%s to %s', 'modern-events-calendar-lite'), $this->date_i18n($date_format . ' ' . $time_format, $other_timestamps[0]), $this->date_i18n($date_format . ' ' . $time_format, $other_timestamps[1])));
                        $pdf->Ln();
                    }
                }

                $pdf->Ln();
            }
            else if (!$bookings_all_occurrences)
            {
                $pdf->SetFont('DejaVuBold', '', 12);
                $pdf->Write(6, esc_html__('Date and Time', 'modern-events-calendar-lite') . ': ');
                $pdf->SetFont('DejaVu', '', 12);
                $pdf->Write(6, trim($start_datetime) . ' - ' . (($start_datetime != $end_datetime) ? $end_datetime . ' ' : ''), '- ');
                $pdf->Ln();
            }

            $pdf->SetFont('DejaVuBold', '', 12);
            $pdf->Write(6, esc_html__('Transaction ID', 'modern-events-calendar-lite') . ': ');
            $pdf->SetFont('DejaVu', '', 12);
            $pdf->Write(6, $transaction_id);
            $pdf->Ln();

            if (is_array($bfixed_fields) and count($bfixed_fields) and isset($transaction['fields']) and is_array($transaction['fields']) and count($transaction['fields']))
            {
                $pdf->SetFont('DejaVuBold', '', 16);
                $pdf->Write(20, sprintf(esc_html__('%s Fields', 'modern-events-calendar-lite'), $this->m('booking', esc_html__('Booking', 'modern-events-calendar-lite'))));
                $pdf->Ln();

                foreach ($bfixed_fields as $bfixed_field_id => $bfixed_field)
                {
                    if (!is_numeric($bfixed_field_id)) continue;

                    $bfixed_value = $transaction['fields'][$bfixed_field_id] ?? null;
                    if (!$bfixed_value) continue;

                    $bfixed_type = $bfixed_field['type'] ?? null;
                    $bfixed_label = $bfixed_field['label'] ?? '';

                    if ($bfixed_type != 'agreement')
                    {
                        $pdf->SetFont('DejaVu', '', 12);
                        $pdf->Write(6, $bfixed_label . ": " . (is_array($bfixed_value) ? stripslashes(implode(',', $bfixed_value)) : stripslashes($bfixed_value)));
                        $pdf->Ln();
                    }
                }
            }

            // Attendees
            if (isset($transaction['tickets']) and is_array($transaction['tickets']) and count($transaction['tickets']))
            {
                $pdf->SetFont('DejaVuBold', '', 16);
                $pdf->Write(20, esc_html__('Attendees', 'modern-events-calendar-lite'));
                $pdf->Ln();

                $i = 1;
                foreach ($transaction['tickets'] as $attendee)
                {
                    if (!isset($attendee['id'])) continue;

                    $pdf->SetFont('DejaVuBold', '', 12);
                    $pdf->Write(6, stripslashes($attendee['name']));
                    $pdf->Ln();

                    $pdf->SetFont('DejaVu', '', 10);
                    $pdf->Write(6, $attendee['email']);
                    $pdf->Ln();

                    $pdf->Write(6, ((isset($event->tickets[$attendee['id']]) ? esc_html__($this->m('ticket', esc_html__('Ticket', 'modern-events-calendar-lite'))) . ': ' . esc_html($event->tickets[$attendee['id']]['name']) : '') . ' ' . (isset($event->tickets[$attendee['id']]) ? $book->get_ticket_price_label($event->tickets[$attendee['id']], $booking_time, $event_id, $dates[0]) : '')));

                    // Registration Fields
                    $reg_form = (isset($attendee['reg']) and is_array($attendee['reg'])) ? $attendee['reg'] : [];
                    $reg_fields = apply_filters('mec_bookign_reg_form', $reg_fields, $event_id, get_post($event_id));

                    if (isset($reg_form) and count($reg_form))
                    {
                        foreach ($reg_form as $field_id => $value)
                        {
                            $label = isset($reg_fields[$field_id]) ? $reg_fields[$field_id]['label'] : '';
                            $type = isset($reg_fields[$field_id]) ? $reg_fields[$field_id]['type'] : '';

                            $pdf->Ln();

                            if ($type == 'agreement')
                            {
                                $pdf->Write(5, sprintf(esc_html__($label, 'modern-events-calendar-lite'), get_the_title($reg_fields[$field_id]['page'])) . ": " . ($value == '1' ? esc_html__('Yes', 'modern-events-calendar-lite') : esc_html__('No', 'modern-events-calendar-lite')));
                            }
                            else
                            {
                                $pdf->Write(5, $label . ": " . (is_string($value) ? stripslashes($value) : (is_array($value) ? stripslashes(implode(', ', $value)) : '---')));
                            }
                        }
                    }

                    // Ticket Variations
                    if (isset($attendee['variations']) and is_array($attendee['variations']) and count($attendee['variations']))
                    {
                        $ticket_variations = $this->ticket_variations($event_id, $attendee['id']);

                        foreach ($attendee['variations'] as $variation_id => $variation_count)
                        {
                            if (!$variation_count || $variation_count < 0) continue;

                            $variation_title = (isset($ticket_variations[$variation_id]) and isset($ticket_variations[$variation_id]['title'])) ? $ticket_variations[$variation_id]['title'] : '';
                            if (!trim($variation_title)) continue;

                            $pdf->Ln();
                            $pdf->Write(6, '+ ' . $variation_title . ' (' . $variation_count . ')');
                        }
                    }

                    if ($i != count($transaction['tickets'])) $pdf->Ln(12);
                    else $pdf->Ln();

                    $i++;
                }
            }

            // Billing Information
            if (isset($transaction['price_details']) and isset($transaction['price_details']['details']) and is_array($transaction['price_details']['details']) and count($transaction['price_details']['details']))
            {
                $pdf->SetFont('DejaVuBold', '', 16);
                $pdf->Write(20, esc_html__('Billing', 'modern-events-calendar-lite'));
                $pdf->Ln();

                $pdf->SetFont('DejaVu', '', 12);
                foreach ($transaction['price_details']['details'] as $price_row)
                {
                    $pdf->Write(6, $price_row['description'] . ": " . $this->render_price($price_row['amount'], $requested_event_id));
                    $pdf->Ln();
                }

                if ($coupon_code)
                {
                    $pdf->Write(6, esc_html__('Coupon Code', 'modern-events-calendar-lite') . ": " . $coupon_code);
                    $pdf->Ln();
                }

                $pdf->SetFont('DejaVuBold', '', 12);
                $pdf->Write(10, esc_html__('Total', 'modern-events-calendar-lite') . ': ');
                $pdf->Write(10, $this->render_price($transaction['price'], $requested_event_id));
                $pdf->Ln();
            }

            // Geteway
            $pdf->SetFont('DejaVuBold', '', 16);
            $pdf->Write(20, esc_html__('Payment', 'modern-events-calendar-lite'));
            $pdf->Ln();

            if (isset($transaction['payable']))
            {
                $pdf->SetFont('DejaVu', '', 12);
                $pdf->Write(6, esc_html__('Paid Amount', 'modern-events-calendar-lite') . ': ');
                $pdf->Write(6, $this->render_price($transaction['payable'], $requested_event_id));
                $pdf->Ln();
            }

            $pdf->SetFont('DejaVu', '', 12);
            $pdf->Write(6, esc_html__('Gateway', 'modern-events-calendar-lite') . ': ');
            $pdf->Write(6, get_post_meta($book_id, 'mec_gateway_label', true));
            $pdf->Ln();

            $pdf->SetFont('DejaVu', '', 12);
            $pdf->Write(6, esc_html__('Transaction ID', 'modern-events-calendar-lite') . ': ');
            $pdf->Write(6, ((isset($transaction['gateway_transaction_id']) and trim($transaction['gateway_transaction_id'])) ? $transaction['gateway_transaction_id'] : $transaction_id));
            $pdf->Ln();

            $date_format = get_option('date_format');
            $time_format = get_option('time_format');

            $pdf->SetFont('DejaVu', '', 12);
            $pdf->Write(6, esc_html__('Payment Time', 'modern-events-calendar-lite') . ': ');
            $pdf->Write(6, date($date_format . ' ' . $time_format, strtotime(get_post_meta($book_id, 'mec_booking_time', true))));
            $pdf->Ln();

            do_action('mec_book_invoice_pdf_before_qr_code', $pdf, $book_id, $transaction);

            $image = $this->module('qrcode.invoice', ['event' => $event]);
            if (trim($image))
            {
                // QR Code
                $pdf->SetX(-50);
                $pdf->Image($image);
                $pdf->Ln();
            }

            $pdf->Output();
            exit;
        }
    }

    public function cart_invoice()
    {
        // Cart Invoice
        if (!isset($_GET['method']) or sanitize_text_field($_GET['method']) !== 'mec-cart-invoice') return;

        $settings = $this->get_settings();
        if (!isset($settings['mec_cart_invoice']) or !$settings['mec_cart_invoice']) wp_die(__('Cannot find the invoice!', 'modern-events-calendar-lite'), esc_html__('Invoice is invalid.', 'modern-events-calendar-lite'));

        $ml_settings = $this->get_ml_settings();

        $cart_id = sanitize_text_field($_GET['mec-key']);

        $c = $this->getCart();
        $cart = $c->get_archived_cart($cart_id);

        if (!count($cart)) wp_die(__('Cannot find the invoice!', 'modern-events-calendar-lite'), esc_html__('Invoice is invalid.', 'modern-events-calendar-lite'));

        // Libraries
        $book = $this->getBook();
        $render = $this->getRender();
        $db = $this->getDB();

        // Include the tFPDF Class
        if (!class_exists('tFPDF')) require_once MEC_ABSPATH . 'app' . DS . 'api' . DS . 'TFPDF' . DS . 'tfpdf.php';

        $pdf = new tFPDF();
        $pdf->AddPage();

        // Add a Unicode font (uses UTF-8)
        $pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
        $pdf->AddFont('DejaVuBold', '', 'DejaVuSansCondensed-Bold.ttf', true);

        $pdf->SetTitle(sprintf(esc_html__('%s Invoice', 'modern-events-calendar-lite'), $cart_id));
        $pdf->SetAuthor(get_bloginfo('name'), true);

        foreach ($cart as $transaction_id)
        {
            $transaction = $book->get_transaction($transaction_id);
            $event_id = $transaction['event_id'] ?? 0;
            $requested_event_id = $transaction['translated_event_id'] ?? $event_id;

            // Don't Show PDF If Booking is Pending
            $book_id = $db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_value`='" . $transaction_id . "' AND `meta_key`='mec_transaction_id'", 'loadResult');
            $mec_confirmed = get_post_meta($book_id, 'mec_confirmed', true);

            $event = $render->data($event_id);

            $reg_fields = $this->get_reg_fields($event_id);
            $bfixed_fields = $this->get_bfixed_fields($event_id);

            $location_id = $this->get_master_location_id($event);
            $location = isset($event->locations[$location_id]) ? (trim($event->locations[$location_id]['address']) ? $event->locations[$location_id]['address'] : $event->locations[$location_id]['name']) : '';

            $dates = isset($transaction['date']) ? explode(':', $transaction['date']) : [time(), time()];

            // Multiple Dates
            $all_dates = ((isset($transaction['all_dates']) and is_array($transaction['all_dates'])) ? $transaction['all_dates'] : []);

            // Get Booking Post
            $booking = $book->get_bookings_by_transaction_id($transaction_id);

            $booking_time = isset($booking[0]) ? get_post_meta($booking[0]->ID, 'mec_booking_time', true) : null;
            if (!$booking_time and is_numeric($dates[0])) $booking_time = date('Y-m-d', $dates[0]);

            $booking_time = date('Y-m-d', strtotime($booking_time));

            // Coupon Code
            $coupon_code = isset($booking[0]) ? get_post_meta($booking[0]->ID, 'mec_coupon_code', true) : '';

            // Event Information
            $pdf->SetFont('DejaVuBold', '', 18);
            $pdf->Write(18, html_entity_decode(get_the_title($event->ID)));
            $pdf->Ln();

            $pdf->SetFont('DejaVuBold', '', 9);
            $pdf->Write(5, esc_html__('Transaction ID', 'modern-events-calendar-lite') . ': ');
            $pdf->SetFont('DejaVu', '', 9);
            $pdf->Write(5, $transaction_id . ' (' . ($mec_confirmed ? esc_html__('Confirmed', 'modern-events-calendar-lite') : esc_html__('Not Confirmed', 'modern-events-calendar-lite')) . ')');
            $pdf->Ln();

            if (trim($location))
            {
                $pdf->SetFont('DejaVuBold', '', 9);
                $pdf->Write(5, esc_html__('Location', 'modern-events-calendar-lite') . ': ');
                $pdf->SetFont('DejaVu', '', 9);
                $pdf->Write(5, $location);
                $pdf->Ln();
            }

            $date_format = (isset($ml_settings['booking_date_format1']) and trim($ml_settings['booking_date_format1'])) ? $ml_settings['booking_date_format1'] : 'Y-m-d';
            $time_format = get_option('time_format');

            if (is_numeric($dates[0]) and is_numeric($dates[1]))
            {
                $start_datetime = date($date_format . ' ' . $time_format, $dates[0]);
                $end_datetime = date($date_format . ' ' . $time_format, $dates[1]);
            }
            else
            {
                $start_datetime = $dates[0] . ' ' . $event->data->time['start'];
                $end_datetime = $dates[1] . ' ' . $event->data->time['end'];
            }

            $booking_options = $event->meta['mec_booking'] ?? [];
            $bookings_all_occurrences = $booking_options['bookings_all_occurrences'] ?? 0;

            if (count($all_dates))
            {
                $pdf->SetFont('DejaVuBold', '', 9);
                $pdf->Write(5, __('Date & Times', 'modern-events-calendar-lite'));
                $pdf->Ln();
                $pdf->SetFont('DejaVu', '', 9);

                foreach ($all_dates as $one_date)
                {
                    $other_timestamps = explode(':', $one_date);
                    if (isset($other_timestamps[0]) and isset($other_timestamps[1]))
                    {
                        $pdf->Write(5, sprintf(esc_html__('%s to %s', 'modern-events-calendar-lite'), $this->date_i18n($date_format . ' ' . $time_format, $other_timestamps[0]), $this->date_i18n($date_format . ' ' . $time_format, $other_timestamps[1])));
                        $pdf->Ln();
                    }
                }

                $pdf->Ln();
            }
            else if (!$bookings_all_occurrences)
            {
                $pdf->SetFont('DejaVuBold', '', 9);
                $pdf->Write(5, __('Date & Time', 'modern-events-calendar-lite') . ': ');
                $pdf->SetFont('DejaVu', '', 9);
                $pdf->Write(5, trim($start_datetime) . ' - ' . (($start_datetime != $end_datetime) ? $end_datetime . ' ' : ''), '- ');
                $pdf->Ln();
            }

            // Booking Fixed Fields
            if (is_array($bfixed_fields) and count($bfixed_fields))
            {
                $pdf->Ln();
                $pdf->SetFont('DejaVuBold', '', 12);
                $pdf->Write(5, sprintf(esc_html__('%s Fields', 'modern-events-calendar-lite'), $this->m('booking', esc_html__('Booking', 'modern-events-calendar-lite'))));
                $pdf->Ln();

                $pdf->SetFont('DejaVu', '', 9);
                foreach ($bfixed_fields as $bfixed_field_id => $bfixed_field)
                {
                    if (!is_numeric($bfixed_field_id)) continue;

                    $bfixed_value = $transaction['fields'][$bfixed_field_id] ?? null;
                    if (!$bfixed_value) continue;

                    $bfixed_type = $bfixed_field['type'] ?? null;
                    $bfixed_label = $bfixed_field['label'] ?? '';

                    if ($bfixed_type == 'agreement')
                    {
                        $pdf->Write(5, sprintf(esc_html__($bfixed_label, 'modern-events-calendar-lite'), get_the_title($bfixed_field['page'])) . ": " . ($bfixed_value == '1' ? esc_html__('Yes', 'modern-events-calendar-lite') : esc_html__('No', 'modern-events-calendar-lite')));
                    }
                    else
                    {
                        $pdf->Write(5, $bfixed_label . ": " . (is_array($bfixed_value) ? stripslashes(implode(',', $bfixed_value)) : stripslashes($bfixed_value)));
                    }

                    $pdf->Ln();
                }
            }

            // Attendees
            if (isset($transaction['tickets']) and is_array($transaction['tickets']) and count($transaction['tickets']))
            {
                $pdf->Ln();
                $pdf->SetFont('DejaVuBold', '', 12);
                $pdf->Write(5, esc_html__('Attendees', 'modern-events-calendar-lite'));
                $pdf->Ln();
                $pdf->Ln();

                $i = 1;
                foreach ($transaction['tickets'] as $attendee)
                {
                    if (!isset($attendee['id'])) continue;

                    $pdf->SetFont('DejaVuBold', '', 9);
                    $pdf->Write(5, stripslashes($attendee['name']));
                    $pdf->Ln();

                    $pdf->SetFont('DejaVu', '', 9);
                    $pdf->Write(5, $attendee['email']);
                    $pdf->Ln();

                    $pdf->Write(5, ((isset($event->tickets[$attendee['id']]) ? esc_html__($this->m('ticket', esc_html__('Ticket', 'modern-events-calendar-lite'))) . ': ' . esc_html($event->tickets[$attendee['id']]['name']) : '') . ' ' . (isset($event->tickets[$attendee['id']]) ? esc_html($book->get_ticket_price_label($event->tickets[$attendee['id']], $booking_time, $event_id, $dates[0])) : '')));

                    // Registration Fields
                    $reg_form = $attendee['reg'] ?? [];
                    $reg_fields = apply_filters('mec_bookign_reg_form', $reg_fields, $event_id, get_post($event_id));

                    if (isset($reg_form) and count($reg_form))
                    {
                        foreach ($reg_form as $field_id => $value)
                        {
                            $label = isset($reg_fields[$field_id]) ? $reg_fields[$field_id]['label'] : '';
                            $type = isset($reg_fields[$field_id]) ? $reg_fields[$field_id]['type'] : '';

                            $pdf->Ln();

                            if ($type == 'agreement')
                            {
                                $pdf->Write(5, sprintf(esc_html__($label, 'modern-events-calendar-lite'), get_the_title($reg_fields[$field_id]['page'])) . ": " . ($value == '1' ? esc_html__('Yes', 'modern-events-calendar-lite') : esc_html__('No', 'modern-events-calendar-lite')));
                            }
                            else
                            {
                                $pdf->Write(5, $label . ": " . (is_string($value) ? $value : (is_array($value) ? implode(', ', $value) : '---')));
                            }
                        }
                    }

                    // Ticket Variations
                    if (isset($attendee['variations']) and is_array($attendee['variations']) and count($attendee['variations']))
                    {
                        $ticket_variations = $this->ticket_variations($event_id, $attendee['id']);

                        foreach ($attendee['variations'] as $variation_id => $variation_count)
                        {
                            if (!$variation_count or ($variation_count and $variation_count < 0)) continue;

                            $variation_title = (isset($ticket_variations[$variation_id]) and isset($ticket_variations[$variation_id]['title'])) ? $ticket_variations[$variation_id]['title'] : '';
                            if (!trim($variation_title)) continue;

                            $pdf->Ln();
                            $pdf->Write(5, '+ ' . $variation_title . ' (' . $variation_count . ')');
                        }
                    }

                    if ($i != count($transaction['tickets'])) $pdf->Ln(9);
                    else $pdf->Ln();

                    $i++;
                }
            }

            // Billing Information
            if (isset($transaction['price_details']) and isset($transaction['price_details']['details']) and is_array($transaction['price_details']['details']) and count($transaction['price_details']['details']))
            {
                $pdf->SetFont('DejaVuBold', '', 12);
                $pdf->Write(5, esc_html__('Billing', 'modern-events-calendar-lite'));
                $pdf->Ln();

                $pdf->SetFont('DejaVu', '', 9);
                foreach ($transaction['price_details']['details'] as $price_row)
                {
                    $pdf->Write(5, $price_row['description'] . ": " . $this->render_price($price_row['amount'], $requested_event_id));
                    $pdf->Ln();
                }

                if ($coupon_code)
                {
                    $pdf->Write(5, esc_html__('Coupon Code', 'modern-events-calendar-lite') . ": " . $coupon_code);
                    $pdf->Ln();
                }

                $pdf->SetFont('DejaVuBold', '', 9);
                $pdf->Write(5, esc_html__('Total', 'modern-events-calendar-lite') . ': ');
                $pdf->Write(5, $this->render_price($transaction['price'], $requested_event_id));
                $pdf->Ln();
            }

            // Gqteway
            $pdf->Ln();
            $pdf->SetFont('DejaVuBold', '', 12);
            $pdf->Write(5, esc_html__('Payment', 'modern-events-calendar-lite'));
            $pdf->Ln();

            if (isset($transaction['payable']))
            {
                $pdf->SetFont('DejaVu', '', 12);
                $pdf->Write(6, esc_html__('Paid Amount', 'modern-events-calendar-lite') . ': ');
                $pdf->Write(6, $this->render_price($transaction['payable'], $requested_event_id));
                $pdf->Ln();
            }

            $pdf->SetFont('DejaVu', '', 9);
            $pdf->Write(5, esc_html__('Gateway', 'modern-events-calendar-lite') . ': ');
            $pdf->Write(5, get_post_meta($book_id, 'mec_gateway_label', true));
            $pdf->Ln();

            $pdf->SetFont('DejaVu', '', 9);
            $pdf->Write(5, esc_html__('Transaction ID', 'modern-events-calendar-lite') . ': ');
            $pdf->Write(5, ((isset($transaction['gateway_transaction_id']) and trim($transaction['gateway_transaction_id'])) ? $transaction['gateway_transaction_id'] : $transaction_id));
            $pdf->Ln();

            $date_format = get_option('date_format');
            $time_format = get_option('time_format');

            $pdf->SetFont('DejaVu', '', 9);
            $pdf->Write(5, esc_html__('Payment Time', 'modern-events-calendar-lite') . ': ');
            $pdf->Write(5, date($date_format . ' ' . $time_format, strtotime(get_post_meta($book_id, 'mec_booking_time', true))));
            $pdf->Ln();
        }

        $pdf->Output();
        exit;
    }

    public function print_calendar()
    {
        // Print Calendar
        if (isset($_GET['method']) and sanitize_text_field($_GET['method']) == 'mec-print' and $this->getPRO())
        {
            $year = isset($_GET['mec-year']) ? sanitize_text_field($_GET['mec-year']) : null;
            $month = isset($_GET['mec-month']) ? sanitize_text_field($_GET['mec-month']) : null;

            // Month and Year are required!
            if (!trim($year) or !trim($month)) return;

            $start = $year . '-' . $month . '-01';
            $end = date('Y-m-t', strtotime($start));

            $atts = [];
            $atts['sk-options']['agenda']['start_date_type'] = 'date';
            $atts['sk-options']['agenda']['start_date'] = $start;
            $atts['sk-options']['agenda']['maximum_date_range'] = $end;
            $atts['sk-options']['agenda']['style'] = 'clean';
            $atts['sk-options']['agenda']['limit'] = 1000;
            $atts['sf_status'] = false;
            $atts['sf_display_label'] = false;

            // Create Skin Object Class
            $SKO = new MEC_skin_agenda();

            // Initialize the skin
            $SKO->initialize($atts);

            // Fetch the events
            $SKO->fetch();

            ob_start();
            ?>
            <html>

            <head>
                <?php wp_head(); ?>
            </head>

            <body class="<?php body_class('mec-print'); ?>">
            <?php echo MEC_kses::full($SKO->output()); ?>
            </body>

            </html>
            <?php
            $html = ob_get_clean();

            echo MEC_kses::full($html);
            exit;
        }
    }

    public function booking_modal()
    {
        // Print Calendar
        if (isset($_GET['method']) and sanitize_text_field($_GET['method']) == 'mec-booking-modal' and $this->getPRO())
        {
            global $post;

            // Current Post is not Event
            if (!isset($post->post_type) || $post->post_type != $this->get_main_post_type()) return;

            ob_start();
            ?>
            <html>

            <head>
                <?php wp_head(); ?>
            </head>

            <body <?php body_class('mec-booking-modal'); ?>>
            <?php echo do_shortcode('[mec-booking event-id="' . $post->ID . '"]'); ?>
            <?php wp_footer(); ?>
            </body>

            </html>
            <?php
            echo ob_get_clean();
            exit;
        }
    }

    /**
     * Generates ical output
     * @throws Exception
     * @author Webnus <info@webnus.net>
     */
    public function ical()
    {
        // ical export
        if (isset($_GET['method']) and sanitize_text_field($_GET['method']) == 'ical')
        {
            $id = sanitize_text_field($_GET['id']);
            $post = get_post($id);

            if (isset($post->post_type) && $post->post_type == $this->get_main_post_type() && $post->post_status == 'publish')
            {
                $occurrence = isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '';

                $events = $this->ical_single($id, $occurrence, '', true);
                $ical_calendar = $this->ical_calendar($events);

                header('Content-type: application/force-download; charset=utf-8');
                header('Content-Disposition: attachment; filename="mec-event-' . $id . '.ics"');

                echo MEC_kses::full($ical_calendar);
                exit;
            }
        }
    }

    /**
     * Generates ical output in email
     * @author Webnus <info@webnus.net>
     */
    public function ical_email()
    {
        // ical export
        if (isset($_GET['method']) and sanitize_text_field($_GET['method']) == 'ical-email')
        {
            $id = sanitize_text_field($_GET['id']);
            $book_id = sanitize_text_field($_GET['book_id']);
            $key = sanitize_text_field($_GET['key']);

            if ($key != md5($book_id)) wp_die(__('Request is not valid.', 'modern-events-calendar-lite'), esc_html__('iCal export stopped!', 'modern-events-calendar-lite'), ['back_link' => true]);

            $occurrence = isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '';

            $events = $this->ical_single_email($id, $book_id, $occurrence);
            $ical_calendar = $this->ical_calendar($events);

            header('Content-type: application/force-download; charset=utf-8');
            header('Content-Disposition: attachment; filename="mec-booking-' . $book_id . '.ics"');

            echo MEC_kses::full($ical_calendar);
            exit;
        }
    }

    /**
     * Returns the iCal URL of event
     * @param $event_id
     * @param string $occurrence
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function ical_URL($event_id, $occurrence = '')
    {
        $url = $this->URL('site');
        $url = $this->add_qs_var('method', 'ical', $url);
        $url = $this->add_qs_var('id', $event_id, $url);

        // Add Occurrence Date if passed
        if (trim($occurrence)) $url = $this->add_qs_var('occurrence', $occurrence, $url);

        return $url;
    }

    public function ical_URL_email($event_id, $book_id, $occurrence = '')
    {
        $url = $this->URL('site');
        $url = $this->add_qs_var('method', 'ical-email', $url);
        $url = $this->add_qs_var('id', $event_id, $url);
        $url = $this->add_qs_var('book_id', $book_id, $url);
        $url = $this->add_qs_var('key', md5($book_id), $url);

        // Add Occurrence Date if passed
        if (trim($occurrence)) $url = $this->add_qs_var('occurrence', $occurrence, $url);

        return $url;
    }

    /**
     * Returns iCal export for one event
     * @param int $event_id
     * @param string $occurrence
     * @param string $occurrence_time
     * @param bool $start_from_beginning
     * @return string
     * @throws Exception
     * @author Webnus <info@webnus.net>
     */
    public function ical_single($event_id, $occurrence = '', $occurrence_time = '', $start_from_beginning = false)
    {
        // Valid Line Separator
        $crlf = "\r\n";

        // MEC Render Library
        $render = $this->getRender();

        // Event Data
        $event = $render->data($event_id);

        // Not an Event
        if ($event->post->post_type != $this->get_main_post_type()) return '';

        if ($start_from_beginning and isset($event->meta, $event->meta['mec_start_datetime']))
        {
            $start_time = strtotime($event->meta['mec_start_datetime']);
            $end_time = strtotime($event->meta['mec_end_datetime']);
        }
        else
        {
            $occurrence_end_date = (trim($occurrence) ? $this->get_end_date_by_occurrence($event_id, $occurrence) : '');

            // Event Dates
            $dates = $this->get_event_next_occurrences($event, $occurrence, 2, $occurrence_time);

            $date = $dates[0] ?? [];

            if ($date['start']['hour'] == '0' && $date['start']['ampm'] == 'AM') $date['start']['hour'] = 12;
            if ($date['end']['hour'] == '0' && $date['end']['ampm'] == 'AM') $date['end']['hour'] = 12;

            $start_time_string = isset($date['start']) ? sprintf("%02d", $date['start']['hour']) . ':' . sprintf("%02d", $date['start']['minutes']) . ' ' . $date['start']['ampm'] : '';
            $end_time_string = isset($date['end']) ? sprintf("%02d", $date['end']['hour']) . ':' . sprintf("%02d", $date['end']['minutes']) . ' ' . $date['end']['ampm'] : '';

            if (isset($date['start'], $date['start']['timestamp'])) $start_time = $date['start']['timestamp'];
            else $start_time = strtotime(((isset($date['start']) and trim($date['start']['date'])) ? $date['start']['date'] : $occurrence) . ' ' . $start_time_string);

            if (isset($date['end'], $date['end']['timestamp'])) $end_time = $date['end']['timestamp'];
            else $end_time = strtotime((trim($occurrence_end_date) ? $occurrence_end_date : $date['end']['date']) . ' ' . $end_time_string);
        }

        $gmt_offset_seconds = $this->get_gmt_offset_seconds($start_time, $event);
        $stamp = strtotime($event->post->post_date);
        $modified = strtotime($event->post->post_modified);

        $rrules = $this->get_ical_rrules($event);
        $time_format = 'Ymd\\THi00\\Z';

        $sequence = (isset($event->meta['mec_sequence']) ? (int) $event->meta['mec_sequence'] : 0);

        // All Day Event
        if (isset($event->meta['mec_date']) and isset($event->meta['mec_date']['allday']) and $event->meta['mec_date']['allday'])
        {
            $start_time = strtotime("Today", $start_time);
            $end_time = strtotime('Tomorrow', $end_time);
        }

        $ical = "BEGIN:VEVENT" . $crlf;
        $ical .= "CLASS:PUBLIC" . $crlf;
        $ical .= "UID:MEC-" . md5($event_id) . "@" . $this->get_domain() . $crlf;
        $ical .= "DTSTART:" . gmdate($time_format, ($start_time - $gmt_offset_seconds)) . $crlf;
        $ical .= "DTEND:" . gmdate($time_format, ($end_time - $gmt_offset_seconds)) . $crlf;
        $ical .= "DTSTAMP:" . gmdate($time_format, ($stamp - $gmt_offset_seconds)) . $crlf;

        if (is_array($rrules) and count($rrules))
        {
            foreach ($rrules as $rrule) $ical .= $rrule . $crlf;
        }

        $event_content = preg_replace('#<a[^>]*href="((?!/)[^"]+)">[^<]+</a>#', '$0 ( $1 )', $event->content);
        $event_content = strip_shortcodes(strip_tags($event_content));
        $event_content = str_replace("\r\n", "\\n", $event_content);
        $event_content = str_replace("\n", "\\n", $event_content);
        $event_content = preg_replace('/(<script[^>]*>.+?<\/script>|<style[^>]*>.+?<\/style>)/s', '', $event_content);

        $ical .= "CREATED:" . date('Ymd', $stamp) . $crlf;
        $ical .= "LAST-MODIFIED:" . date('Ymd', $modified) . $crlf;
        $ical .= "PRIORITY:5" . $crlf;
        $ical .= "SEQUENCE:" . $sequence . $crlf;
        $ical .= "TRANSP:OPAQUE" . $crlf;
        $ical .= "SUMMARY:" . html_entity_decode(apply_filters('mec_ical_single_summary', $event->title, $event_id), ENT_NOQUOTES, 'UTF-8') . $crlf;
        $ical .= "DESCRIPTION:" . html_entity_decode(apply_filters('mec_ical_single_description', $event_content, $event_id, $event), ENT_NOQUOTES, 'UTF-8') . $crlf;
        $ical .= "URL:" . apply_filters('mec_ical_single_url', $event->permalink, $event_id) . $crlf;

        // Organizer
        $organizer_id = $this->get_master_organizer_id($event->ID, $start_time);
        $organizer = $event->organizers[$organizer_id] ?? [];
        $organizer_name = (isset($organizer['name']) and trim($organizer['name'])) ? $organizer['name'] : null;
        $organizer_email = (isset($organizer['email']) and trim($organizer['email'])) ? $organizer['email'] : null;

        if ($organizer_name or $organizer_email) $ical .= "ORGANIZER;CN=" . $organizer_name . ":MAILTO:" . $organizer_email . $crlf;

        // Categories
        $categories = '';
        if (isset($event->categories) and is_array($event->categories) and count($event->categories))
        {
            foreach ($event->categories as $category) $categories .= $category['name'] . ',';
        }

        if (trim($categories) != '') $ical .= "CATEGORIES:" . trim($categories, ', ') . $crlf;

        // Location
        $location_id = $this->get_master_location_id($event->ID, $start_time);
        $location = $event->locations[$location_id] ?? [];
        $address = ((isset($location['address']) and trim($location['address'])) ? $location['address'] : (isset($location['name']) ? $location['name'] : ''));

        if (trim($address) != '') $ical .= "LOCATION:" . $address . $crlf;

        // Featured Image
        if (trim($event->featured_image['full']) != '')
        {
            $ex = explode('/', $event->featured_image['full']);
            $filename = end($ex);
            $ical .= "ATTACH;FMTTYPE=" . $this->get_mime_content_type($filename) . ":" . $event->featured_image['full'] . $crlf;
        }

        $ical .= "END:VEVENT" . $crlf;

        return apply_filters('mec_ical_single', $ical);
    }

    public function ical_single_occurrence($start_time, $end_time, $event, $rrules = [], $extra_lines = [])
    {
        // Valid Line Separator
        $crlf = "\r\n";

        $stamp_gmt = strtotime($event->post->post_date_gmt);
        $stamp = strtotime($event->post->post_date);
        $modified = strtotime($event->post->post_modified);

        $timezone = $this->get_timezone($event->ID);
        $time_format = 'Ymd\\THi00';

        $sequence = (isset($event->meta['mec_sequence']) ? (int) $event->meta['mec_sequence'] : 0);

        // All Day Event
        if (isset($event->meta['mec_date']) and isset($event->meta['mec_date']['allday']) and $event->meta['mec_date']['allday'])
        {
            $time_format = 'Ymd\\T000000';
            $end_time = strtotime('+1 Day', $end_time);
        }

        $ical = "BEGIN:VEVENT" . $crlf;
        $ical .= "CLASS:PUBLIC" . $crlf;
        $ical .= "DTSTART;TZID=" . $timezone . ":" . date($time_format, $start_time) . $crlf;
        $ical .= "DTEND;TZID=" . $timezone . ":" . date($time_format, $end_time) . $crlf;
        $ical .= "DTSTAMP:" . gmdate($time_format, $stamp_gmt) . $crlf;
        $ical .= "UID:MEC-" . md5($event->ID) . "@" . $this->get_domain() . $crlf;

        if (is_array($rrules) and count($rrules))
        {
            foreach ($rrules as $rrule) $ical .= $rrule . $crlf;
        }

        if (is_array($extra_lines) and count($extra_lines))
        {
            foreach ($extra_lines as $extra_line) $ical .= $extra_line . $crlf;
        }

        $event_content = preg_replace('#<a[^>]*href="((?!/)[^"]+)">[^<]+</a>#', '$0 ( $1 )', $event->content);
        $event_content = strip_shortcodes(strip_tags($event_content));
        $event_content = str_replace("\r\n", "\\n", $event_content);
        $event_content = str_replace("\n", "\\n", $event_content);
        $event_content = preg_replace('/(<script[^>]*>.+?<\/script>|<style[^>]*>.+?<\/style>)/s', '', $event_content);

        $ical .= "CREATED:" . date('Ymd', $stamp) . $crlf;
        $ical .= "LAST-MODIFIED:" . date('Ymd', $modified) . $crlf;
        $ical .= "PRIORITY:5" . $crlf;
        $ical .= "SEQUENCE:" . $sequence . $crlf;
        $ical .= "TRANSP:OPAQUE" . $crlf;
        $ical .= "SUMMARY:" . html_entity_decode(apply_filters('mec_ical_single_summary', $event->title, $event->ID), ENT_NOQUOTES, 'UTF-8') . $crlf;
        $ical .= "DESCRIPTION:" . html_entity_decode(apply_filters('mec_ical_single_description', $event_content, $event->ID, $event), ENT_NOQUOTES, 'UTF-8') . $crlf;
        $ical .= "URL:" . apply_filters('mec_ical_single_url', $event->permalink, $event->ID) . $crlf;

        // Organizer
        $organizer_id = $this->get_master_organizer_id($event->ID, $start_time);
        $organizer = $event->organizers[$organizer_id] ?? [];
        $organizer_name = (isset($organizer['name']) and trim($organizer['name'])) ? $organizer['name'] : null;
        $organizer_email = (isset($organizer['email']) and trim($organizer['email'])) ? $organizer['email'] : null;

        if ($organizer_name or $organizer_email) $ical .= "ORGANIZER;CN=" . $organizer_name . ":MAILTO:" . $organizer_email . $crlf;

        // Categories
        $categories = '';
        if (isset($event->categories) and is_array($event->categories) and count($event->categories))
        {
            foreach ($event->categories as $category) $categories .= $category['name'] . ',';
        }

        if (trim($categories) != '') $ical .= "CATEGORIES:" . trim($categories, ', ') . $crlf;

        // Location
        $location_id = $this->get_master_location_id($event->ID, $start_time);
        $location = $event->locations[$location_id] ?? [];
        $address = ((isset($location['address']) and trim($location['address'])) ? $location['address'] : ($location['name'] ?? ''));

        if (trim($address) != '') $ical .= "LOCATION:" . $address . $crlf;

        // Featured Image
        if (trim($event->featured_image['full']) != '')
        {
            $ex = explode('/', $event->featured_image['full']);
            $filename = end($ex);
            $ical .= "ATTACH;FMTTYPE=" . $this->get_mime_content_type($filename) . ":" . $event->featured_image['full'] . $crlf;
        }

        $ical .= "END:VEVENT" . $crlf;

        return apply_filters('mec_ical_single', $ical);
    }

    /**
     * Returns iCal export for email
     * @param int $event_id
     * @param int $book_id
     * @param string $occurrence
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function ical_single_email($event_id, $book_id, $occurrence = '')
    {
        // Valid Line Separator
        $crlf = "\r\n";

        $date = get_post_meta($book_id, 'mec_date', true);
        $timestamps = explode(':', $date);

        // MEC Render Library
        $render = $this->getRender();
        $event = $render->data($event_id);

        $start_time = ($timestamps[0] ?? strtotime(get_the_date($book_id)));
        $end_time = ($timestamps[1] ?? strtotime(get_the_date($book_id)));

        $location_id = $this->get_master_location_id($event->ID, $start_time);
        $location = $event->locations[$location_id] ?? [];
        $address = (isset($location['address']) and trim($location['address'])) ? $location['address'] : ($location['name'] ?? '');

        $gmt_offset_seconds = $this->get_gmt_offset_seconds($start_time, $event);

        $stamp = strtotime($event->post->post_date);
        $modified = strtotime($event->post->post_modified);
        $time_format = (isset($event->meta['mec_date']) and isset($event->meta['mec_date']['allday']) and $event->meta['mec_date']['allday']) ? 'Ymd' : 'Ymd\\THi00\\Z';

        $sequence = (isset($event->meta['mec_sequence']) ? (int) $event->meta['mec_sequence'] : 0);

        $event_content = preg_replace('#<a[^>]*href="((?!/)[^"]+)">[^<]+</a>#', '$0 ( $1 )', $event->content);
        $event_content = strip_shortcodes(strip_tags($event_content));
        $event_content = str_replace("\r\n", "\\n", $event_content);
        $event_content = str_replace("\n", "\\n", $event_content);
        $event_content = preg_replace('/(<script[^>]*>.+?<\/script>|<style[^>]*>.+?<\/style>)/s', '', $event_content);

        $ical = "BEGIN:VEVENT" . $crlf;
        $ical .= "CLASS:PUBLIC" . $crlf;
        $ical .= "UID:MEC-" . md5($event_id) . "@" . $this->get_domain() . $crlf;
        $ical .= "DTSTART:" . gmdate($time_format, ($start_time - $gmt_offset_seconds)) . $crlf;
        $ical .= "DTEND:" . gmdate($time_format, ($end_time - $gmt_offset_seconds)) . $crlf;
        $ical .= "DTSTAMP:" . gmdate($time_format, ($stamp - $gmt_offset_seconds)) . $crlf;
        $ical .= "CREATED:" . date('Ymd', $stamp) . $crlf;
        $ical .= "LAST-MODIFIED:" . date('Ymd', $modified) . $crlf;
        $ical .= "PRIORITY:5" . $crlf;
        $ical .= "SEQUENCE:" . $sequence . $crlf;
        $ical .= "TRANSP:OPAQUE" . $crlf;
        $ical .= "SUMMARY:" . html_entity_decode(apply_filters('mec_ical_single_summary', $event->title, $event_id), ENT_NOQUOTES, 'UTF-8') . $crlf;
        $ical .= "DESCRIPTION:" . html_entity_decode(apply_filters('mec_ical_single_description', $event_content, $event_id, $event), ENT_NOQUOTES, 'UTF-8') . $crlf;
        $ical .= "X-ALT-DESC;FMTTYPE=text/html:" . html_entity_decode(apply_filters('mec_ical_single_description', $event_content, $event_id, $event), ENT_NOQUOTES, 'UTF-8') . $crlf;
        $ical .= "URL:" . apply_filters('mec_ical_single_url', $event->permalink, $event_id) . $crlf;

        // Location
        if (trim($address) != '') $ical .= "LOCATION:" . $address . $crlf;

        // Featured Image
        if (trim($event->featured_image['full']) != '')
        {
            $ex = explode('/', $event->featured_image['full']);
            $filename = end($ex);
            $ical .= "ATTACH;FMTTYPE=" . $this->get_mime_content_type($filename) . ":" . $event->featured_image['full'] . $crlf;
        }

        $ical .= "END:VEVENT" . $crlf;

        return apply_filters('mec_ical_single', $ical);
    }

    /**
     * Returns iCal export for some events
     * @param string $events
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function ical_calendar($events)
    {
        // Valid Line Separator
        $crlf = "\r\n";

        $ical = "BEGIN:VCALENDAR" . $crlf;
        $ical .= "VERSION:2.0" . $crlf;
        $ical .= "METHOD:PUBLISH" . $crlf;
        $ical .= "CALSCALE:GREGORIAN" . $crlf;
        $ical .= "PRODID:-//WordPress - MECv" . $this->get_version() . "//EN" . $crlf;
        $ical .= "X-ORIGINAL-URL:" . $this->URL('site') . $crlf;
        $ical .= "X-WR-CALNAME:" . get_bloginfo('name') . $crlf;
        $ical .= "X-WR-CALDESC:" . get_bloginfo('description') . $crlf;
        $ical .= "REFRESH-INTERVAL;VALUE=DURATION:PT1H" . $crlf;
        $ical .= "X-PUBLISHED-TTL:PT1H" . $crlf;
        $ical .= "X-MS-OLK-FORCEINSPECTOROPEN:TRUE" . $crlf;
        $ical .= $events;
        $ical .= "END:VCALENDAR" . $crlf;

        return $ical;
    }

    /**
     * Get mime-type of a file
     * @param string $filename
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_mime_content_type($filename)
    {
        // Remove query string from the image name
        if (strpos($filename, '?') !== false)
        {
            $ex = explode('?', $filename);
            $filename = $ex[0];
        }

        $mime_types = [
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // MS Office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        ];

        $ex = explode('.', $filename);
        $ext = strtolower(array_pop($ex));
        if (array_key_exists($ext, $mime_types))
        {
            return $mime_types[$ext];
        }
        else if (function_exists('finfo_open'))
        {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);

            return $mimetype;
        }
        else
        {
            return 'application/octet-stream';
        }
    }

    /**
     * Returns book post type slug
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_book_post_type()
    {
        return apply_filters('mec_book_post_type_name', 'mec-books');
    }

    /**
     * Returns shortcode post type slug
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_shortcode_post_type()
    {
        return apply_filters('mec_shortcode_post_type_name', 'mec_calendars');
    }

    /**
     * Returns email post type
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_email_post_type()
    {
        return apply_filters('mec_email_post_type_name', 'mec-emails');
    }

    /**
     * Returns certificate type
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_certificate_post_type()
    {
        return apply_filters('mec_certificate_post_type_name', 'mec-certificate');
    }

    /**
     * Returns webhooks post type
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_webhook_post_type()
    {
        return apply_filters('mec_email_post_type_name', 'mec-webhooks');
    }

    public function field_icon_feature($key, $values = [], $prefix = 'reg')
    {
        if ($prefix !== 'event') return '';

        // MEC Settings
        $settings = $this->get_settings();

        // Feature is disabled
        if (!isset($settings['event_fields_icon']) or (isset($settings['event_fields_icon']) and !$settings['event_fields_icon'])) return '';

        $preview_html_id = 'mec_thumbnail_img_' . esc_attr($prefix) . '_' . esc_attr($key);
        $input_html_id = 'mec_thumbnail_' . esc_attr($prefix) . '_' . esc_attr($key);
        $icon = isset($values['icon']) ? $values['icon'] : '';

        return '<span class="mec_' . esc_attr($prefix) . '_icon">
            <div class="mec-field-preview-icon" id="' . esc_attr($preview_html_id) . '">' . (trim($icon) != '' ? '<img src="' . esc_url($icon) . '" />' : '') . '</div>
            <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][icon]" id="' . esc_attr($input_html_id) . '" value="' . esc_attr($icon) . '" />
            <button type="button" class="mec_upload_image_button button" data-input-id="' . esc_attr($input_html_id) . '" data-preview-id="' . esc_attr($preview_html_id) . '" id="mec_thumbnail_button_' . esc_attr($prefix) . '_' . esc_attr($key) . '">' . esc_html__('Upload/Add Icon', 'modern-events-calendar-lite') . '</button>
            <button type="button" class="mec_remove_image_button button ' . (!trim($icon) ? 'mec-util-hidden' : '') . '" data-input-id="' . esc_attr($input_html_id) . '" data-preview-id="' . esc_attr($preview_html_id) . '">' . esc_html__('Remove Icon', 'modern-events-calendar-lite') . '</button>
        </span>';
    }

    /**
     * Show text field options in booking form
     * @param string $key
     * @param array $values
     * @param string $prefix
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function field_text($key, $values = [], $prefix = 'reg')
    {
        return '<li id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '">
            <span class="mec_' . esc_attr($prefix) . '_field_sort">' . esc_html__('Sort', 'modern-events-calendar-lite') . '</span>
            <span class="mec_' . esc_attr($prefix) . '_field_type">' . esc_html__('Text', 'modern-events-calendar-lite') . '</span>
            ' . ($prefix == 'event' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%event_field_' . esc_html($key) . '%%</span>' : ($prefix == 'bfixed' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%booking_field_' . esc_html($key) . '%%</span>' : '')) . '
            ' . $this->field_icon_feature($key, $values, $prefix) . '
            ' . apply_filters('mec_form_field_description', '', $key, $values, $prefix) . '
            <p class="mec_' . esc_attr($prefix) . '_field_options">
                <label class="label-checkbox">
                    <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="0" />
                    <input type="checkbox" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="1" ' . ((isset($values['mandatory']) and $values['mandatory']) ? 'checked="checked"' : '') . ' />
                    ' . esc_html__('Required Field', 'modern-events-calendar-lite') . '
                </label>
            </p>
            <span onclick="mec_' . esc_attr($prefix) . '_fields_remove(' . esc_attr($key) . ');" class="mec_' . esc_attr($prefix) . '_field_remove">' . esc_html__('Remove', 'modern-events-calendar-lite') . '</span>
            <div>
                <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][type]" value="text" />
                <input type="text" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][label]" placeholder="' . esc_attr__('Insert a label for this field', 'modern-events-calendar-lite') . '" value="' . (isset($values['label']) ? stripslashes($values['label']) : '') . '" />
                ' . ($prefix == 'reg' ? $this->get_wp_user_fields_dropdown('mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mapping]', ($values['mapping'] ?? '')) : '') . '
            </div>
        </li>';
    }

    /**
     * Show text field options in booking form
     * @param string $key
     * @param array $values
     * @param string $prefix
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function field_name($key, $values = [], $prefix = 'reg')
    {
        return '<li id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '">
             <span class="mec_' . esc_attr($prefix) . '_field_sort">' . esc_html__('Sort', 'modern-events-calendar-lite') . '</span>
             <span class="mec_' . esc_attr($prefix) . '_field_type">' . esc_html__('MEC Name', 'modern-events-calendar-lite') . '</span>
             ' . ($prefix == 'event' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%event_field_' . esc_html($key) . '%%</span>' : ($prefix == 'bfixed' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%booking_field_' . esc_html($key) . '%%</span>' : '')) . '
             ' . $this->field_icon_feature($key, $values, $prefix) . '
             ' . apply_filters('mec_form_field_description', '', $key, $values, $prefix) . '
             <p class="mec_' . esc_attr($prefix) . '_field_options" style="display:none">
                 <label class="label-checkbox">
                     <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="1" />
                     <input type="checkbox" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="1" checked="checked" disabled />
                     ' . esc_html__('Required Field', 'modern-events-calendar-lite') . '
                 </label>
             </p>
             <div>
                 <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][type]" value="name" />
                 <input type="text" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][label]" placeholder="' . esc_attr__('Insert a label for this field', 'modern-events-calendar-lite') . '" value="' . (isset($values['label']) ? stripslashes($values['label']) : '') . '" />
             </div>
         </li>';
    }

    /**
     * Show text field options in booking form
     * @param string $key
     * @param array $values
     * @param string $prefix
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function field_mec_email($key, $values = [], $prefix = 'reg')
    {
        return '<li id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '">
             <span class="mec_' . esc_attr($prefix) . '_field_sort">' . esc_html__('Sort', 'modern-events-calendar-lite') . '</span>
             <span class="mec_' . esc_attr($prefix) . '_field_type">' . esc_html__('MEC Email', 'modern-events-calendar-lite') . '</span>
             ' . ($prefix == 'event' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%event_field_' . esc_html($key) . '%%</span>' : ($prefix == 'bfixed' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%booking_field_' . esc_html($key) . '%%</span>' : '')) . '
             ' . $this->field_icon_feature($key, $values, $prefix) . '
             ' . apply_filters('mec_form_field_description', '', $key, $values, $prefix) . '
             <p class="mec_' . esc_attr($prefix) . '_field_options" style="display:none">
                 <label class="label-checkbox">
                     <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="1" />
                     <input type="checkbox" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="1" checked="checked" disabled />
                     ' . esc_html__('Required Field', 'modern-events-calendar-lite') . '
                 </label>
             </p>
             <div>
                 <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][type]" value="mec_email" />
                 <input type="text" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][label]" placeholder="' . esc_attr__('Insert a label for this field', 'modern-events-calendar-lite') . '" value="' . (isset($values['label']) ? stripslashes($values['label']) : '') . '" />
             </div>
         </li>';
    }

    /**
     * Show email field options in booking form
     * @param string $key
     * @param array $values
     * @param string $prefix
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function field_email($key, $values = [], $prefix = 'reg')
    {
        return '<li id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '">
            <span class="mec_' . esc_attr($prefix) . '_field_sort">' . esc_html__('Sort', 'modern-events-calendar-lite') . '</span>
            <span class="mec_' . esc_attr($prefix) . '_field_type">' . esc_html__('Email', 'modern-events-calendar-lite') . '</span>
            ' . ($prefix == 'event' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%event_field_' . esc_html($key) . '%%</span>' : ($prefix == 'bfixed' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%booking_field_' . esc_html($key) . '%%</span>' : '')) . '
            ' . $this->field_icon_feature($key, $values, $prefix) . '
            ' . apply_filters('mec_form_field_description', '', $key, $values, $prefix) . '
            <p class="mec_' . esc_attr($prefix) . '_field_options">
                <label class="label-checkbox">
                    <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="0" />
                    <input type="checkbox" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="1" ' . ((isset($values['mandatory']) and $values['mandatory']) ? 'checked="checked"' : '') . ' />
                    ' . esc_html__('Required Field', 'modern-events-calendar-lite') . '
                </label>
            </p>
            <span onclick="mec_' . esc_attr($prefix) . '_fields_remove(' . esc_attr($key) . ');" class="mec_' . esc_attr($prefix) . '_field_remove">' . esc_html__('Remove', 'modern-events-calendar-lite') . '</span>
            <div>
                <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][type]" value="email" />
                <input type="text" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][label]" placeholder="' . esc_attr__('Insert a label for this field', 'modern-events-calendar-lite') . '" value="' . (isset($values['label']) ? stripslashes($values['label']) : '') . '" />
                ' . ($prefix == 'reg' ? $this->get_wp_user_fields_dropdown('mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mapping]', ($values['mapping'] ?? '')) : '') . '
            </div>
        </li>';
    }

    /**
     * Show URL field options in forms
     * @param string $key
     * @param array $values
     * @param string $prefix
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function field_url($key, $values = [], $prefix = 'reg')
    {
        return '<li id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '">
            <span class="mec_' . esc_attr($prefix) . '_field_sort">' . esc_html__('Sort', 'modern-events-calendar-lite') . '</span>
            <span class="mec_' . esc_attr($prefix) . '_field_type">' . esc_html__('URL', 'modern-events-calendar-lite') . '</span>
            ' . ($prefix == 'event' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%event_field_' . esc_html($key) . '%%</span>' : ($prefix == 'bfixed' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%booking_field_' . esc_html($key) . '%%</span>' : '')) . '
            ' . $this->field_icon_feature($key, $values, $prefix) . '
            ' . apply_filters('mec_form_field_description', '', $key, $values, $prefix) . '
            <p class="mec_' . esc_attr($prefix) . '_field_options">
                <label class="label-checkbox">
                    <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="0" />
                    <input type="checkbox" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="1" ' . ((isset($values['mandatory']) and $values['mandatory']) ? 'checked="checked"' : '') . ' />
                    ' . esc_html__('Required Field', 'modern-events-calendar-lite') . '
                </label>
            </p>
            <span onclick="mec_' . esc_attr($prefix) . '_fields_remove(' . esc_attr($key) . ');" class="mec_' . esc_attr($prefix) . '_field_remove">' . esc_html__('Remove', 'modern-events-calendar-lite') . '</span>
            <div>
                <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][type]" value="url" />
                <input type="text" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][label]" placeholder="' . esc_attr__('Insert a label for this field', 'modern-events-calendar-lite') . '" value="' . (isset($values['label']) ? stripslashes($values['label']) : '') . '" />
                ' . ($prefix == 'reg' ? $this->get_wp_user_fields_dropdown('mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mapping]', ($values['mapping'] ?? '')) : '') . '
            </div>
        </li>';
    }

    /**
     * Show file field options in booking form
     * @param string $key
     * @param array $values
     * @param string $prefix
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function field_file($key, $values = [], $prefix = 'reg')
    {
        return '<li id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '">
            <span class="mec_' . esc_attr($prefix) . '_field_sort">' . esc_html__('Sort', 'modern-events-calendar-lite') . '</span>
            <span class="mec_' . esc_attr($prefix) . '_field_type">' . esc_html__('File', 'modern-events-calendar-lite') . '</span>
            ' . ($prefix == 'event' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%event_field_' . esc_html($key) . '%%</span>' : ($prefix == 'bfixed' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%booking_field_' . esc_html($key) . '%%</span>' : '')) . '
            ' . $this->field_icon_feature($key, $values, $prefix) . '
            ' . apply_filters('mec_form_field_description', '', $key, $values, $prefix) . '
            <p class="mec_' . esc_attr($prefix) . '_field_options">
                <label class="label-checkbox">
                    <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="0" />
                    <input type="checkbox" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="1" ' . ((isset($values['mandatory']) and $values['mandatory']) ? 'checked="checked"' : '') . ' />
                    ' . esc_html__('Required Field', 'modern-events-calendar-lite') . '
                </label>
            </p>
            <span onclick="mec_' . esc_attr($prefix) . '_fields_remove(' . esc_attr($key) . ');" class="mec_' . esc_attr($prefix) . '_field_remove">' . esc_html__('Remove', 'modern-events-calendar-lite') . '</span>
            <div>
                <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][type]" value="file" />
                <input type="text" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][label]" placeholder="' . esc_attr__('Insert a label for this field', 'modern-events-calendar-lite') . '" value="' . (isset($values['label']) ? stripslashes($values['label']) : '') . '" />
            </div>
        </li>';
    }

    /**
     * Show date field options in booking form
     * @param string $key
     * @param array $values
     * @param string $prefix
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function field_date($key, $values = [], $prefix = 'reg')
    {
        return '<li id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '">
            <span class="mec_' . esc_attr($prefix) . '_field_sort">' . esc_html__('Sort', 'modern-events-calendar-lite') . '</span>
            <span class="mec_' . esc_attr($prefix) . '_field_type">' . esc_html__('Date', 'modern-events-calendar-lite') . '</span>
            ' . ($prefix == 'event' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%event_field_' . esc_html($key) . '%%</span>' : ($prefix == 'bfixed' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%booking_field_' . esc_html($key) . '%%</span>' : '')) . '
            ' . $this->field_icon_feature($key, $values, $prefix) . '
            ' . apply_filters('mec_form_field_description', '', $key, $values, $prefix) . '
            <p class="mec_' . esc_attr($prefix) . '_field_options">
                <label class="label-checkbox">
                    <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="0" />
                    <input type="checkbox" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="1" ' . ((isset($values['mandatory']) and $values['mandatory']) ? 'checked="checked"' : '') . ' />
                    ' . esc_html__('Required Field', 'modern-events-calendar-lite') . '
                </label>
            </p>
            <span onclick="mec_' . esc_attr($prefix) . '_fields_remove(' . esc_attr($key) . ');" class="mec_' . esc_attr($prefix) . '_field_remove">' . esc_html__('Remove', 'modern-events-calendar-lite') . '</span>
            <div>
                <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][type]" value="date" />
                <input type="text" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][label]" placeholder="' . esc_attr__('Insert a label for this field', 'modern-events-calendar-lite') . '" value="' . (isset($values['label']) ? stripslashes($values['label']) : '') . '" />
                ' . ($prefix == 'reg' ? $this->get_wp_user_fields_dropdown('mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mapping]', ($values['mapping'] ?? '')) : '') . '
            </div>
        </li>';
    }

    /**
     * Show tel field options in booking form
     * @param string $key
     * @param array $values
     * @param string $prefix
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function field_tel($key, $values = [], $prefix = 'reg')
    {
        return '<li id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '">
            <span class="mec_' . esc_attr($prefix) . '_field_sort">' . esc_html__('Sort', 'modern-events-calendar-lite') . '</span>
            <span class="mec_' . esc_attr($prefix) . '_field_type">' . esc_html__('Tel', 'modern-events-calendar-lite') . '</span>
            ' . ($prefix == 'event' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%event_field_' . esc_html($key) . '%%</span>' : ($prefix == 'bfixed' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%booking_field_' . esc_html($key) . '%%</span>' : '')) . '
            ' . $this->field_icon_feature($key, $values, $prefix) . '
            ' . apply_filters('mec_form_field_description', '', $key, $values, $prefix) . '
            <p class="mec_' . esc_attr($prefix) . '_field_options">
                <label class="label-checkbox">
                    <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="0" />
                    <input type="checkbox" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="1" ' . ((isset($values['mandatory']) and $values['mandatory']) ? 'checked="checked"' : '') . ' />
                    ' . esc_html__('Required Field', 'modern-events-calendar-lite') . '
                </label>
            </p>
            <span onclick="mec_' . esc_attr($prefix) . '_fields_remove(' . esc_attr($key) . ');" class="mec_' . esc_attr($prefix) . '_field_remove">' . esc_html__('Remove', 'modern-events-calendar-lite') . '</span>
            <div>
                <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][type]" value="tel" />
                <input type="text" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][label]" placeholder="' . esc_attr__('Insert a label for this field', 'modern-events-calendar-lite') . '" value="' . (isset($values['label']) ? stripslashes($values['label']) : '') . '" />
                ' . ($prefix == 'reg' ? $this->get_wp_user_fields_dropdown('mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mapping]', ($values['mapping'] ?? '')) : '') . '
            </div>
        </li>';
    }

    /**
     * Show textarea field options in booking form
     * @param string $key
     * @param array $values
     * @param string $prefix
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function field_textarea($key, $values = [], $prefix = 'reg')
    {
        return '<li id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '">
            <span class="mec_' . esc_attr($prefix) . '_field_sort">' . esc_html__('Sort', 'modern-events-calendar-lite') . '</span>
            <span class="mec_' . esc_attr($prefix) . '_field_type">' . esc_html__('Textarea', 'modern-events-calendar-lite') . '</span>
            ' . ($prefix == 'event' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%event_field_' . esc_html($key) . '%%</span>' : ($prefix == 'bfixed' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%booking_field_' . esc_html($key) . '%%</span>' : '')) . '
            ' . $this->field_icon_feature($key, $values, $prefix) . '
            ' . apply_filters('mec_form_field_description', '', $key, $values, $prefix) . '
            <p class="mec_' . esc_attr($prefix) . '_field_options">
                <div id="mec_' . esc_attr($prefix) . '_field_options_' . esc_attr($key) . '_mandatory_wrapper" class="' . ((isset($values['editor']) and $values['editor']) ? 'mec-util-hidden' : '') . '">
                    <label class="label-checkbox">
                        <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="0" />
                        <input type="checkbox" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="1" ' . ((isset($values['mandatory']) and $values['mandatory']) ? 'checked="checked"' : '') . ' />
                        ' . esc_html__('Required Field', 'modern-events-calendar-lite') . '
                    </label>
                </div>
                ' . ($prefix == 'event' ? '<br><label class="label-checkbox">
                    <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][editor]" value="0" />
                    <input type="checkbox" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][editor]" value="1" onchange="jQuery(\'#mec_' . esc_attr($prefix) . '_field_options_' . esc_attr($key) . '_mandatory_wrapper\').toggleClass(\'mec-util-hidden\');" ' . ((isset($values['editor']) and $values['editor']) ? 'checked="checked"' : '') . ' />
                    ' . esc_html__('HTML Editor', 'modern-events-calendar-lite') . '
                </label>' : '') . '
            </p>
            <span onclick="mec_' . esc_attr($prefix) . '_fields_remove(' . esc_attr($key) . ');" class="mec_' . esc_attr($prefix) . '_field_remove">' . esc_html__('Remove', 'modern-events-calendar-lite') . '</span>
            <div>
                <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][type]" value="textarea" />
                <input type="text" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][label]" placeholder="' . esc_attr__('Insert a label for this field', 'modern-events-calendar-lite') . '" value="' . (isset($values['label']) ? stripslashes($values['label']) : '') . '" />
                ' . ($prefix == 'reg' ? $this->get_wp_user_fields_dropdown('mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mapping]', ($values['mapping'] ?? '')) : '') . '
            </div>
        </li>';
    }

    /**
     * Show paragraph field options in booking form
     * @param string $key
     * @param array $values
     * @param string $prefix
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function field_p($key, $values = [], $prefix = 'reg')
    {
        return '<li id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '">
            <span class="mec_' . esc_attr($prefix) . '_field_sort">' . esc_html__('Sort', 'modern-events-calendar-lite') . '</span>
            <span class="mec_' . esc_attr($prefix) . '_field_type">' . esc_html__('Paragraph', 'modern-events-calendar-lite') . '</span>
            <span onclick="mec_' . esc_attr($prefix) . '_fields_remove(' . esc_attr($key) . ');" class="mec_' . esc_attr($prefix) . '_field_remove">' . esc_html__('Remove', 'modern-events-calendar-lite') . '</span>
            ' . apply_filters('mec_form_field_description', '', $key, $values, $prefix) . '
            <div>
                <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][type]" value="p" />
                <textarea name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][content]">' . (isset($values['content']) ? htmlentities(stripslashes($values['content'])) : '') . '</textarea>
                <p class="description">' . esc_html__('HTML and shortcode are allowed.') . '</p>
            </div>
        </li>';
    }

    /**
     * Show checkbox field options in booking form
     * @param string $key
     * @param array $values
     * @param string $prefix
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function field_checkbox($key, $values = [], $prefix = 'reg')
    {
        $i = 0;
        $field = '<li id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '">
            <span class="mec_' . esc_attr($prefix) . '_field_sort">' . esc_html__('Sort', 'modern-events-calendar-lite') . '</span>
            <span class="mec_' . esc_attr($prefix) . '_field_type">' . esc_html__('Checkboxes', 'modern-events-calendar-lite') . '</span>
            ' . ($prefix == 'event' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%event_field_' . esc_html($key) . '%%</span>' : ($prefix == 'bfixed' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%booking_field_' . esc_html($key) . '%%</span>' : '')) . '
            ' . $this->field_icon_feature($key, $values, $prefix) . '
            ' . apply_filters('mec_form_field_description', '', $key, $values, $prefix) . '
            <p class="mec_' . esc_attr($prefix) . '_field_options">
                <label class="label-checkbox">
                    <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="0" />
                    <input type="checkbox" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="1" ' . ((isset($values['mandatory']) and $values['mandatory']) ? 'checked="checked"' : '') . ' />
                    ' . esc_html__('Required Field', 'modern-events-calendar-lite') . '
                </label>
            </p>
            <span onclick="mec_' . esc_attr($prefix) . '_fields_remove(' . esc_attr($key) . ');" class="mec_' . esc_attr($prefix) . '_field_remove">' . esc_html__('Remove', 'modern-events-calendar-lite') . '</span>
            <div>
                <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][type]" value="checkbox" />
                <input type="text" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][label]" placeholder="' . esc_attr__('Insert a label for this field', 'modern-events-calendar-lite') . '" value="' . (isset($values['label']) ? stripslashes($values['label']) : '') . '" />
                ' . ($prefix == 'reg' ? $this->get_wp_user_fields_dropdown('mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mapping]', ($values['mapping'] ?? '')) : '') . '
                <ul id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '_options_container" class="mec_' . esc_attr($prefix) . '_fields_options_container">';

        if (isset($values['options']) and is_array($values['options']) and count($values['options']))
        {
            foreach ($values['options'] as $option_key => $option)
            {
                $i = max($i, $option_key);
                $field .= $this->field_option($key, $option_key, $values, $prefix);
            }
        }

        $field .= '</ul>
                <button type="button" class="mec-' . esc_attr($prefix) . '-field-add-option" data-field-id="' . esc_attr($key) . '">' . esc_html__('Option', 'modern-events-calendar-lite') . '</button>
                <input type="hidden" id="mec_new_' . esc_attr($prefix) . '_field_option_key_' . esc_attr($key) . '" value="' . ($i + 1) . '" />
            </div>
        </li>';

        return $field;
    }

    /**
     * Show radio field options in booking form
     * @param string $key
     * @param array $values
     * @param string $prefix
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function field_radio($key, $values = [], $prefix = 'reg')
    {
        $i = 0;
        $field = '<li id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '">
            <span class="mec_' . esc_attr($prefix) . '_field_sort">' . esc_html__('Sort', 'modern-events-calendar-lite') . '</span>
            <span class="mec_' . esc_attr($prefix) . '_field_type">' . esc_html__('Radio Buttons', 'modern-events-calendar-lite') . '</span>
            ' . ($prefix == 'event' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%event_field_' . esc_html($key) . '%%</span>' : ($prefix == 'bfixed' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%booking_field_' . esc_html($key) . '%%</span>' : '')) . '
            ' . $this->field_icon_feature($key, $values, $prefix) . '
            ' . apply_filters('mec_form_field_description', '', $key, $values, $prefix) . '
            <p class="mec_' . esc_attr($prefix) . '_field_options">
                <label class="label-checkbox">
                    <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="0" />
                    <input type="checkbox" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="1" ' . ((isset($values['mandatory']) and $values['mandatory']) ? 'checked="checked"' : '') . ' />
                    ' . esc_html__('Required Field', 'modern-events-calendar-lite') . '
                </label>
            </p>
            <span onclick="mec_' . esc_attr($prefix) . '_fields_remove(' . esc_attr($key) . ');" class="mec_' . esc_attr($prefix) . '_field_remove">' . esc_html__('Remove', 'modern-events-calendar-lite') . '</span>
            <div>
                <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][type]" value="radio" />
                <input type="text" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][label]" placeholder="' . esc_attr__('Insert a label for this field', 'modern-events-calendar-lite') . '" value="' . (isset($values['label']) ? stripslashes($values['label']) : '') . '" />
                ' . ($prefix == 'reg' ? $this->get_wp_user_fields_dropdown('mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mapping]', ($values['mapping'] ?? '')) : '') . '
                <ul id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '_options_container" class="mec_' . esc_attr($prefix) . '_fields_options_container">';

        if (isset($values['options']) and is_array($values['options']) and count($values['options']))
        {
            foreach ($values['options'] as $option_key => $option)
            {
                $i = max($i, $option_key);
                $field .= $this->field_option($key, $option_key, $values, $prefix);
            }
        }

        $field .= '</ul>
                <button type="button" class="mec-' . esc_attr($prefix) . '-field-add-option" data-field-id="' . esc_attr($key) . '">' . esc_html__('Option', 'modern-events-calendar-lite') . '</button>
                <input type="hidden" id="mec_new_' . esc_attr($prefix) . '_field_option_key_' . esc_attr($key) . '" value="' . ($i + 1) . '" />
            </div>
        </li>';

        return $field;
    }

    /**
     * Show select field options in booking form
     * @param string $key
     * @param array $values
     * @param string $prefix
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function field_select($key, $values = [], $prefix = 'reg')
    {
        $i = 0;
        $field = '<li id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '">
            <span class="mec_' . esc_attr($prefix) . '_field_sort">' . esc_html__('Sort', 'modern-events-calendar-lite') . '</span>
            <span class="mec_' . esc_attr($prefix) . '_field_type">' . esc_html__('Dropdown', 'modern-events-calendar-lite') . '</span>
            ' . ($prefix == 'event' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%event_field_' . esc_html($key) . '%%</span>' : ($prefix == 'bfixed' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%booking_field_' . esc_html($key) . '%%</span>' : '')) . '
            ' . $this->field_icon_feature($key, $values, $prefix) . '
            ' . apply_filters('mec_form_field_description', '', $key, $values, $prefix) . '
            <p class="mec_' . esc_attr($prefix) . '_field_options">
                <label class="label-checkbox">
                    <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="0" />
                    <input type="checkbox" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="1" ' . ((isset($values['mandatory']) and $values['mandatory']) ? 'checked="checked"' : '') . ' />
                    ' . esc_html__('Required Field', 'modern-events-calendar-lite') . '
                </label>
            </p>
            <p class="mec_' . esc_attr($prefix) . '_field_options">
                <label class="label-checkbox">
                    <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][ignore]" value="0" />
                    <input type="checkbox" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][ignore]" value="1" ' . ((isset($values['ignore']) and $values['ignore']) ? 'checked="checked"' : '') . ' />
                    ' . esc_html__('Consider the first item as a placeholder', 'modern-events-calendar-lite') . '
                </label>
            </p>
            <span onclick="mec_' . esc_attr($prefix) . '_fields_remove(' . esc_attr($key) . ');" class="mec_' . esc_attr($prefix) . '_field_remove">' . esc_html__('Remove', 'modern-events-calendar-lite') . '</span>
            <div>
                <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][type]" value="select" />
                <input type="text" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][label]" placeholder="' . esc_attr__('Insert a label for this field', 'modern-events-calendar-lite') . '" value="' . (isset($values['label']) ? stripslashes($values['label']) : '') . '" />
                ' . ($prefix == 'reg' ? $this->get_wp_user_fields_dropdown('mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mapping]', ($values['mapping'] ?? '')) : '') . '
                <ul id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '_options_container" class="mec_' . esc_attr($prefix) . '_fields_options_container">';

        if (isset($values['options']) and is_array($values['options']) and count($values['options']))
        {
            foreach ($values['options'] as $option_key => $option)
            {
                $i = max($i, $option_key);
                $field .= $this->field_option($key, $option_key, $values, $prefix);
            }
        }

        $field .= '</ul>
                <button type="button" class="mec-' . esc_attr($prefix) . '-field-add-option" data-field-id="' . esc_attr($key) . '">' . esc_html__('Option', 'modern-events-calendar-lite') . '</button>
                <input type="hidden" id="mec_new_' . esc_attr($prefix) . '_field_option_key_' . esc_attr($key) . '" value="' . ($i + 1) . '" />
            </div>
        </li>';

        return $field;
    }

    /**
     * Show agreement field options in booking form
     * @param string $key
     * @param array $values
     * @param string $prefix
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function field_agreement($key, $values = [], $prefix = 'reg')
    {
        // WordPress Pages
        $pages = get_pages();

        $i = 0;
        $field = '<li id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '">
            <span class="mec_' . esc_attr($prefix) . '_field_sort">' . esc_html__('Sort', 'modern-events-calendar-lite') . '</span>
            <span class="mec_' . esc_attr($prefix) . '_field_type">' . esc_html__('Agreement', 'modern-events-calendar-lite') . '</span>
            ' . ($prefix == 'event' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%event_field_' . esc_html($key) . '%%</span>' : ($prefix == 'bfixed' ? '<span class="mec_' . esc_attr($prefix) . '_notification_placeholder">%%booking_field_' . esc_html($key) . '%%</span>' : '')) . '
            ' . $this->field_icon_feature($key, $values, $prefix) . '
            ' . apply_filters('mec_form_field_description', '', $key, $values, $prefix) . '
            <p class="mec_' . esc_attr($prefix) . '_field_options">
                <label class="label-checkbox">
                    <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="0" />
                    <input type="checkbox" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][mandatory]" value="1" ' . ((!isset($values['mandatory']) or (isset($values['mandatory']) and $values['mandatory'])) ? 'checked="checked"' : '') . ' />
                    ' . esc_html__('Required Field', 'modern-events-calendar-lite') . '
                </label>
            </p>
            <span onclick="mec_' . esc_attr($prefix) . '_fields_remove(' . esc_attr($key) . ');" class="mec_' . esc_attr($prefix) . '_field_remove">' . esc_html__('Remove', 'modern-events-calendar-lite') . '</span>
            <div>
                <input type="hidden" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][type]" value="agreement" />
                <input type="text" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][label]" placeholder="' . esc_attr__('Insert a label for this field', 'modern-events-calendar-lite') . '" value="' . (isset($values['label']) ? stripslashes($values['label']) : esc_attr__('I agree with %s', 'modern-events-calendar-lite')) . '" /><p class="description">' . esc_html__('Instead of %s, the page title with a link will be show.', 'modern-events-calendar-lite') . '</p>
                <div>
                    <label for="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '_page">' . esc_html__('Agreement Page', 'modern-events-calendar-lite') . '</label>
                    <select id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '_page" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][page]">';

        $page_options = '';
        foreach ($pages as $page) $page_options .= '<option ' . ((isset($values['page']) and $values['page'] == $page->ID) ? 'selected="selected"' : '') . ' value="' . esc_attr($page->ID) . '">' . esc_html($page->post_title) . '</option>';

        $field .= $page_options . '</select>
                </div>
                <div>
                    <label for="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '_status">' . esc_html__('Status', 'modern-events-calendar-lite') . '</label>
                    <select id="mec_' . esc_attr($prefix) . '_fields_' . esc_attr($key) . '_status" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($key) . '][status]">
                        <option value="checked" ' . ((isset($values['status']) and $values['status'] == 'checked') ? 'selected="selected"' : '') . '>' . esc_html__('Checked by default', 'modern-events-calendar-lite') . '</option>
                        <option value="unchecked" ' . ((isset($values['status']) and $values['status'] == 'unchecked') ? 'selected="selected"' : '') . '>' . esc_html__('Unchecked by default', 'modern-events-calendar-lite') . '</option>
                    </select>
                </div>
                <input type="hidden" id="mec_new_' . esc_attr($prefix) . '_field_option_key_' . esc_attr($key) . '" value="' . ($i + 1) . '" />
            </div>
        </li>';

        return $field;
    }

    /**
     * Show option tag parameters in booking form for select, checkbox and radio tags
     * @param string $field_key
     * @param string $key
     * @param array $values
     * @param string $prefix
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function field_option($field_key, $key, $values = [], $prefix = 'reg')
    {
        return '<li id="mec_' . esc_attr($prefix) . '_fields_option_' . esc_attr($field_key) . '_' . esc_attr($key) . '">
            <span class="mec_' . esc_attr($prefix) . '_field_option_sort">' . esc_html__('Sort', 'modern-events-calendar-lite') . '</span>
            <span onclick="mec_' . esc_attr($prefix) . '_fields_option_remove(' . esc_attr($field_key) . ',' . esc_attr($key) . ');" class="mec_' . esc_attr($prefix) . '_field_remove">' . esc_html__('Remove', 'modern-events-calendar-lite') . '</span>
            <input type="text" name="mec[' . esc_attr($prefix) . '_fields][' . esc_attr($field_key) . '][options][' . esc_attr($key) . '][label]" placeholder="' . esc_attr__('Insert a label for this option', 'modern-events-calendar-lite') . '" value="' . ((isset($values['options']) and isset($values['options'][$key])) ? esc_attr(stripslashes($values['options'][$key]['label'])) : '') . '" />
        </li>';
    }

    /**
     * Render raw price and return its output
     * @param int|object $event
     * @param int $price
     * @param boolean $apply_free
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function render_price($price, $event = null, $apply_free = true)
    {
        // return Free if price is 0
        if ($price == '0' and $apply_free) return esc_html__('Free', 'modern-events-calendar-lite');

        $thousand_separator = $this->get_thousand_separator($event);
        $decimal_separator = $this->get_decimal_separator($event);

        $currency = $this->get_currency_sign($event);
        $currency_sign_position = $this->get_currency_sign_position($event);
        $decimals = $this->get_decimals($event);

        // Force to double
        if (is_string($price)) $price = (float) $price;

        $rendered = number_format($price, ($decimal_separator === false ? 0 : $decimals), ($decimal_separator === false ? '' : $decimal_separator), $thousand_separator);

        if ($currency_sign_position == 'after') $rendered = $rendered . $currency;
        else if ($currency_sign_position == 'after_space') $rendered = $rendered . ' ' . $currency;
        else if ($currency_sign_position == 'before_space') $rendered = $currency . ' ' . $rendered;
        else $rendered = $currency . $rendered;

        return $rendered;
    }

    /**
     * Returns thousands separator
     * @param int|object $event
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_thousand_separator($event = null)
    {
        $settings = $this->get_settings();

        // Separator
        $separator = (isset($settings['thousand_separator']) ? $settings['thousand_separator'] : ',');

        // Currency Per Event
        if ($event and isset($settings['currency_per_event']) and $settings['currency_per_event'])
        {
            $options = $this->get_event_currency_options($event);
            if (isset($options['thousand_separator']) and trim($options['thousand_separator'])) $separator = $options['thousand_separator'];
        }

        return apply_filters('mec_thousand_separator', $separator);
    }

    /**
     * Returns decimal separator
     * @param int|object $event
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_decimal_separator($event = null)
    {
        $settings = $this->get_settings();

        // Separator
        $separator = (isset($settings['decimal_separator']) ? $settings['decimal_separator'] : '.');

        // Status
        $disabled = (isset($settings['decimal_separator_status']) and $settings['decimal_separator_status'] == 0);

        // Currency Per Event
        if ($event and isset($settings['currency_per_event']) and $settings['currency_per_event'])
        {
            $options = $this->get_event_currency_options($event);
            if (isset($options['decimal_separator']) and trim($options['decimal_separator'])) $separator = $options['decimal_separator'];
            if (isset($options['decimal_separator_status'])) $disabled = (bool) !$options['decimal_separator_status'];
        }

        return apply_filters('mec_decimal_separator', ($disabled ? false : $separator));
    }

    /**
     * @param int|object $event
     * @return array
     */
    public function get_event_currency_options($event)
    {
        $event_id = (is_object($event) ? $event->ID : $event);

        $options = get_post_meta($event_id, 'mec_currency', true);
        if (!is_array($options)) $options = [];

        return $options;
    }

    /**
     * Returns currency of MEC
     * @param int|object $event
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_currency($event = null)
    {
        $settings = $this->get_settings();
        $currency = ($settings['currency'] ?? '');

        // Currency Per Event
        if ($event and isset($settings['currency_per_event']) and $settings['currency_per_event'])
        {
            $options = $this->get_event_currency_options($event);
            if (isset($options['currency']) and trim($options['currency'])) $currency = $options['currency'];
        }

        return apply_filters('mec_currency', $currency);
    }

    /**
     * Returns currency sign of MEC
     * @param int|object $event
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_currency_sign($event = null)
    {
        $settings = $this->get_settings();

        // Get Currency Symptom
        $currency = $this->get_currency($event);
        if (isset($settings['currency_symptom']) and trim($settings['currency_symptom'])) $currency = $settings['currency_symptom'];

        // Currency Per Event
        if ($event and isset($settings['currency_per_event']) and $settings['currency_per_event'])
        {
            $options = $this->get_event_currency_options($event);
            if (isset($options['currency_symptom']) and trim($options['currency_symptom'])) $currency = $options['currency_symptom'];
        }

        return apply_filters('mec_currency_sign', $currency);
    }

    /**
     * Returns currency code of MEC
     * @param int|object $event
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_currency_code($event = null)
    {
        $currency = $this->get_currency($event);
        $currencies = $this->get_currencies();

        return $currencies[$currency] ?? 'USD';
    }

    /**
     * Returns currency sign position of MEC
     * @param int|object $event
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_currency_sign_position($event = null)
    {
        $settings = $this->get_settings();

        // Currency Position
        $position = $settings['currency_sign'] ?? '';

        // Currency Per Event
        if ($event and isset($settings['currency_per_event']) and $settings['currency_per_event'])
        {
            $options = $this->get_event_currency_options($event);
            if (isset($options['currency_sign']) and trim($options['currency_sign'])) $position = $options['currency_sign'];
        }

        return apply_filters('mec_currency_sign_position', $position);
    }

    public function get_decimals($event = null)
    {

        $settings = $this->get_settings();

        // Currency Decimals
        $decimals = (isset($settings['currency_decimals']) ? (int) $settings['currency_decimals'] : 2);

        // Currency Per Event
        if ($event and isset($settings['currency_per_event']) and $settings['currency_per_event'])
        {
            $options = $this->get_event_currency_options($event);
            if (isset($options['currency_decimals']) and trim($options['currency_decimals'])) $decimals = (int) $options['currency_decimals'];
        }

        return apply_filters('mec_currency_decimals', $decimals);
    }

    /**
     * Returns MEC Payment Gateways
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_gateways()
    {
        return apply_filters('mec_gateways', []);
    }

    /**
     * Check to see if user exists by its username
     * @param string $username
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function username_exists($username)
    {
        /** first validation **/
        if (!trim($username)) return true;

        return username_exists($username);
    }

    /**
     * Check to see if user exists by its email
     * @param string $email
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function email_exists($email)
    {
        /** first validation **/
        if (!trim($email)) return true;

        return email_exists($email);
    }

    /**
     * Register a user in WordPress
     * @param string $username
     * @param string $email
     * @param string $password
     * @param boolean $auto
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function register_user($username, $email, $password = null, $auto = true)
    {
        /** first validation **/
        if (!trim($username) or !trim($email)) return false;

        if ($auto) return register_new_user($username, $email);
        return wp_create_user($username, $password, $email);
    }

    /**
     * Convert a formatted date into standard format
     * @param string $date
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function to_standard_date($date)
    {
        $date = str_replace('-', '/', $date);
        $date = str_replace('.', '/', $date);

        return date('Y-m-d', strtotime($date));
    }

    /**
     * Render the date
     * @param string $date
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function render_date($date)
    {
        return $date;
    }

    /**
     * Generate output of MEC Dashboard
     * @author Webnus <info@webnus.net>
     */
    public function dashboard()
    {
        // Import dashboard page of MEC
        $path = $this->import('app.features.mec.dashboard', true, true);

        // Create mec_events table if it's removed for any reason
        $this->create_mec_tables();

        ob_start();
        include $path;
        echo MEC_kses::full(ob_get_clean());
    }

    /**
     * Redirect on plugin activation
     * @author Webnus <info@webnus.net>
     */
    public function mec_redirect_after_activate()
    {
        $do_redirection = apply_filters('mec_do_redirection_after_activation', true);
        if (!$do_redirection) return false;

        // No need to redirect
        if (!get_option('mec_activation_redirect', false)) return true;

        // Delete the option to don't do it always
        delete_option('mec_activation_redirect');

        // Redirect to MEC Dashboard
        wp_redirect(admin_url('/admin.php?page=MEC-wizard'));
        exit;
    }

    /**
     * Check if we can show booking module or not
     * @param object $event
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function can_show_booking_module($event, $check_stop_selling_window = false)
    {
        // PRO Version is required
        if (!$this->getPRO()) return false;

        // MEC Settings
        $settings = $this->get_settings();

        // Booking on single page is disabled
        if (!isset($settings['booking_status']) || !$settings['booking_status']) return false;

        // Who Can Book
        $wcb_all = (isset($settings['booking_wcb_all']) and is_numeric($settings['booking_wcb_all'])) ? $settings['booking_wcb_all'] : 1;
        if (!$wcb_all)
        {
            $user_id = get_current_user_id();

            // Guest User is not Permitted
            if (!$user_id) return false;

            // User
            $user = get_user_by('id', $user_id);

            // Invalid User ID
            if (!$user || !isset($user->roles)) return false;

            $roles = $user->roles;

            $can = false;
            foreach ($roles as $role) if (isset($settings['booking_wcb_' . $role]) and $settings['booking_wcb_' . $role]) $can = true;

            if (!$can) return false;
        }

        $tickets = $event->data->tickets ?? [];
        $dates = $event->dates ?? ($event->date ?? []);
        $next_date = $dates[0] ?? ($event->date ?? []);

        // No Dates or no Tickets
        if (!count($dates) || !count($tickets)) return false;

        $start_timestamp = $next_date['start']['timestamp'] ?? null;

        // Occurrence Canceled
        if ($this->is_occurrence_canceled($event, $start_timestamp)) return false;

        // Only one Ticket and that ticket is also unavailable
        if (count($dates) === 1 && $check_stop_selling_window)
        {
            $ticket_available = false;
            foreach ($tickets as $ticket)
            {
                $stop_selling_value = $ticket['stop_selling_value'] ?? '';
                if (!$stop_selling_value)
                {
                    $ticket_available = true;
                    break;
                }

                $stop_selling_type = $ticket['stop_selling_type'] ?? 'day';
                if ($stop_selling_type === 'hour') $close_starting = strtotime('-' . $stop_selling_value . ' hours', $start_timestamp);
                else $close_starting = strtotime('-' . $stop_selling_value . ' days', $start_timestamp);

                if (current_time('timestamp') < $close_starting)
                {
                    $ticket_available = true;
                    break;
                }
            }

            if (!$ticket_available) return false;
        }

        // Booking Options
        $booking_options = (isset($event->data->meta['mec_booking']) and is_array($event->data->meta['mec_booking'])) ? $event->data->meta['mec_booking'] : [];

        $booking_unlimited = (!isset($booking_options['bookings_limit_unlimited']) || $booking_options['bookings_limit_unlimited']);
        $booking_limit = (isset($booking_options['bookings_limit']) and trim($booking_options['bookings_limit']) !== '') ? (int) $booking_options['bookings_limit'] : -1;

        // Limit is 0
        if (!$booking_unlimited and $booking_limit === 0) return false;

        $bookings_stop_selling_after_first_occurrence = $booking_options['stop_selling_after_first_occurrence'] ?? 0;
        if ($bookings_stop_selling_after_first_occurrence and $this->is_first_occurrence_passed($event)) return false;

        $book_all_occurrences = 0;
        if (isset($event->data) and isset($event->data->meta) and isset($booking_options['bookings_all_occurrences'])) $book_all_occurrences = (int) $booking_options['bookings_all_occurrences'];

        $show_booking_form_interval = (isset($settings['show_booking_form_interval'])) ? $settings['show_booking_form_interval'] : 0;
        if (isset($booking_options['show_booking_form_interval']) and trim($booking_options['show_booking_form_interval']) != '') $show_booking_form_interval = $booking_options['show_booking_form_interval'];

        // Check Show Booking Form Time
        if ($show_booking_form_interval)
        {
            if ($book_all_occurrences)
            {
                $db = $this->getDB();
                $first_timestamp = $db->select("SELECT `tstart` FROM `#__mec_dates` WHERE `post_id`=" . $event->data->ID . " ORDER BY `tstart` ASC LIMIT 1", 'loadResult');
                $render_date = date('Y-m-d h:i a', $first_timestamp);
            }
            else
            {
                $render_date = (isset($next_date['start']['date']) ? trim($next_date['start']['date']) : date('Y-m-d')) . ' ' . (isset($next_date['start']['hour']) ? trim(sprintf('%02d', $next_date['start']['hour'])) : date('h', current_time('timestamp', 0))) . ':'
                    . (isset($next_date['start']['minutes']) ? trim(sprintf('%02d', $next_date['start']['minutes'])) : date('i', current_time('timestamp', 0))) . ' ' . (isset($next_date['start']['ampm']) ? trim($next_date['start']['ampm']) : date('a', current_time('timestamp', 0)));
            }

            if ($this->check_date_time_validation('Y-m-d h:i a', strtolower($render_date)))
            {
                $date_diff = $this->date_diff(date('Y-m-d h:i a', current_time('timestamp')), $render_date);
                if (isset($date_diff->days) and !$date_diff->invert)
                {
                    $minute = $date_diff->days * 24 * 60;
                    $minute += $date_diff->h * 60;
                    $minute += $date_diff->i;

                    if ($minute > $show_booking_form_interval) return false;
                }
            }
        }

        // Booking OnGoing Event Option
        $ongoing_event_book = (isset($settings['booking_ongoing']) and $settings['booking_ongoing'] == '1');

        // The event is Expired/Passed
        if ($ongoing_event_book)
        {
            if (!isset($next_date['end']) || $this->is_past($next_date['end']['date'], current_time('Y-m-d'))) return false;
            if (isset($next_date['end']['timestamp']) && $next_date['end']['timestamp'] < current_time('timestamp')) return false;
        }
        else
        {
            $time_format = 'Y-m-d';
            $render_date = isset($next_date['start']) ? trim($next_date['start']['date']) : false;

            if (!trim($event->data->meta['mec_repeat_status']))
            {
                if (isset($next_date['start']['hour'])) $render_date .= ' ' . sprintf('%02d', $next_date['start']['hour']) . ':' . sprintf('%02d', $next_date['start']['minutes']) . trim($next_date['start']['ampm']);
                else $render_date .= ' ' . date('h:ia', $event->data->time['start_timestamp']);

                $time_format .= ' h:ia';
            }

            if (!$render_date || $this->is_past($render_date, current_time($time_format))) return false;
        }

        // MEC payment gateways
        $gateway_options = $this->get_gateways_options();

        $is_gateway_enabled = false;
        foreach ($gateway_options as $gateway_option)
        {
            if (is_array($gateway_option) and isset($gateway_option['status']) and $gateway_option['status'] != 0)
            {
                $is_gateway_enabled = true;
                break;
            }
        }

        $wc_status = isset($settings['wc_status']) && class_exists('WooCommerce') && $settings['wc_status'];

        // No Payment gateway is enabled
        if (!$is_gateway_enabled && !$wc_status) return false;

        return apply_filters('mec_can_show_booking_module', true, $event);
    }

    /**
     * Check if we can show countdown module or not
     * @param object $event
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function can_show_countdown_module($event)
    {
        // MEC Settings
        $settings = $this->get_settings();

        // Countdown on single page is disabled
        if (!isset($settings['countdown_status']) || !$settings['countdown_status']) return false;

        $date = $event->date;
        $start_date = (isset($date['start']) and isset($date['start']['date'])) ? $date['start']['date'] : date('Y-m-d');

        $countdown_method = get_post_meta($event->ID, 'mec_countdown_method', true);
        if (trim($countdown_method) == '') $countdown_method = 'global';

        if ($countdown_method == 'global') $ongoing = isset($settings['hide_time_method']) && trim($settings['hide_time_method']) == 'end';
        else $ongoing = $countdown_method == 'end';

        // The event is Expired/Passed
        if ($this->is_past($start_date, date('Y-m-d')) and !$ongoing) return false;

        return true;
    }

    /**
     * @param null $event
     * @return DateTimeZone
     */
    public function get_TZO($event = null)
    {
        $timezone = $this->get_timezone($event);
        return new DateTimeZone($timezone);
    }

    /**
     * Get default timezone of WordPress
     * @param mixed $event
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_timezone($event = null)
    {
        if (!is_null($event))
        {
            $event_id = ((is_object($event) and isset($event->ID)) ? $event->ID : $event);
            $timezone = get_post_meta($event_id, 'mec_timezone', true);

            if (trim($timezone) != '' and $timezone != 'global') $timezone_string = $timezone;
            else $timezone_string = get_option('timezone_string');
        }
        else $timezone_string = get_option('timezone_string');

        $gmt_offset = get_option('gmt_offset');

        if (trim($timezone_string) == '' and trim($gmt_offset)) $timezone_string = $this->get_timezone_by_offset($gmt_offset);
        else if (trim($timezone_string) == '' and trim($gmt_offset) == '0')
        {
            $timezone_string = 'UTC';
        }

        return $timezone_string;
    }

    /**
     * Get GMT offset based on hours:minutes
     * @param mixed $event
     * @param $date
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_gmt_offset($event = null, $date = null)
    {
        // Timezone
        $timezone = $this->get_timezone($event);

        // Convert to Date
        if ($date and is_numeric($date)) $date = date('Y-m-d', $date);
        else if (!$date) $date = current_time('Y-m-d');

        $UTC = new DateTimeZone('UTC');
        $TZ = new DateTimeZone($timezone);

        $gmt_offset_seconds = $TZ->getOffset((new DateTime($date, $UTC)));
        $gmt_offset = ($gmt_offset_seconds / HOUR_IN_SECONDS);

        $minutes = $gmt_offset * 60;
        $hour_minutes = sprintf("%02d", $minutes % 60);

        // Convert the hour into two digits format
        $h = ($minutes - $hour_minutes) / 60;
        $hours = sprintf("%02d", abs($h));

        // Add - sign to the first of hour if it's negative
        if ($h < 0) $hours = '-' . $hours;

        return (substr($hours, 0, 1) == '-' ? '' : '+') . $hours . ':' . (((int) $hour_minutes < 0) ? abs($hour_minutes) : $hour_minutes);
    }

    /**
     * Get GMT offset based on seconds
     * @param $date
     * @param mixed $event
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_gmt_offset_seconds($date = null, $event = null)
    {
        if ($date)
        {
            $timezone = new DateTimeZone($this->get_timezone($event));

            // Convert to Date
            if (is_numeric($date)) $date = date('Y-m-d H:i:s', $date);

            $target = new DateTime($date, $timezone);
            return $timezone->getOffset($target);
        }
        else
        {
            $gmt_offset = get_option('gmt_offset');
            $seconds = $gmt_offset * HOUR_IN_SECONDS;

            return (substr($gmt_offset, 0, 1) == '-' ? '' : '+') . $seconds;
        }
    }

    public function get_timezone_by_offset($offset)
    {
        $seconds = $offset * 3600;

        $timezone = timezone_name_from_abbr('', $seconds, 0);
        if ($timezone === false)
        {
            $timezones = [
                '-12' => 'Pacific/Auckland',
                '-11.5' => 'Pacific/Auckland', // Approx
                '-11' => 'Pacific/Apia',
                '-10.5' => 'Pacific/Apia', // Approx
                '-10' => 'Pacific/Honolulu',
                '-9.5' => 'Pacific/Honolulu', // Approx
                '-9' => 'America/Anchorage',
                '-8.5' => 'America/Anchorage', // Approx
                '-8' => 'America/Los_Angeles',
                '-7.5' => 'America/Los_Angeles', // Approx
                '-7' => 'America/Denver',
                '-6.5' => 'America/Denver', // Approx
                '-6' => 'America/Chicago',
                '-5.5' => 'America/Chicago', // Approx
                '-5' => 'America/New_York',
                '-4.5' => 'America/New_York', // Approx
                '-4' => 'America/Halifax',
                '-3.5' => 'America/Halifax', // Approx
                '-3' => 'America/Sao_Paulo',
                '-2.5' => 'America/Sao_Paulo', // Approx
                '-2' => 'America/Sao_Paulo',
                '-1.5' => 'Atlantic/Azores', // Approx
                '-1' => 'Atlantic/Azores',
                '-0.5' => 'UTC', // Approx
                '0' => 'UTC',
                '0.5' => 'UTC', // Approx
                '1' => 'Europe/Paris',
                '1.5' => 'Europe/Paris', // Approx
                '2' => 'Europe/Helsinki',
                '2.5' => 'Europe/Helsinki', // Approx
                '3' => 'Europe/Moscow',
                '3.5' => 'Europe/Moscow', // Approx
                '4' => 'Asia/Dubai',
                '4.5' => 'Asia/Tehran',
                '5' => 'Asia/Karachi',
                '5.5' => 'Asia/Kolkata',
                '5.75' => 'Asia/Katmandu',
                '6' => 'Asia/Yekaterinburg',
                '6.5' => 'Asia/Yekaterinburg', // Approx
                '7' => 'Asia/Krasnoyarsk',
                '7.5' => 'Asia/Krasnoyarsk', // Approx
                '8' => 'Asia/Shanghai',
                '8.5' => 'Asia/Shanghai', // Approx
                '8.75' => 'Asia/Tokyo', // Approx
                '9' => 'Asia/Tokyo',
                '9.5' => 'Asia/Tokyo', // Approx
                '10' => 'Australia/Melbourne',
                '10.5' => 'Australia/Adelaide',
                '11' => 'Australia/Melbourne', // Approx
                '11.5' => 'Pacific/Auckland', // Approx
                '12' => 'Pacific/Auckland',
                '12.75' => 'Pacific/Apia', // Approx
                '13' => 'Pacific/Apia',
                '13.75' => 'Pacific/Honolulu', // Approx
                '14' => 'Pacific/Honolulu',
            ];

            $timezone = isset($timezones[$offset]) ? $timezones[$offset] : null;
        }

        return $timezone;
    }

    /**
     * Get status of Google recaptcha
     * @param string $section
     * @return boolean
     * @author Webnus <info@webnus.net>
     * @deprecated
     */
    public function get_recaptcha_status($section = '')
    {
        return $this->getCaptcha()->status($section);
    }

    /**
     * Get re-captcha verification from Google servers
     * @param string $remote_ip
     * @param string $response
     * @return boolean
     * @deprecated
     * @author Webnus <info@webnus.net>
     */
    public function get_recaptcha_response($response, $remote_ip = null)
    {
        return $this->getCaptcha()->is_valid();
    }

    /**
     * Get current language of WordPress
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_current_language()
    {
        return apply_filters('plugin_locale', get_locale(), 'modern-events-calendar-lite');
    }

    /**
     * Write to a log file
     * @param string|array $log_msg
     * @param string $path
     * @author Webnus <info@webnus.net>
     */
    public function debug_log($log_msg, $path = '')
    {
        if (trim($path) == '') $path = MEC_ABSPATH . 'log.txt';
        if (is_array($log_msg) || is_object($log_msg)) $log_msg = print_r($log_msg, true);

        $log_msg .= "\n" . '========' . "\n";

        $fh = fopen($path, 'a');
        fwrite($fh, $log_msg);
    }

    /**
     * Filter Skin parameters to add taxonomy, etc. filters that come from WordPress Query
     * This used for taxonomy archive pages etc. that are handled by WordPress itself
     * @param array $atts
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function add_search_filters($atts = [])
    {
        // Taxonomy Archive Page
        if (is_tax())
        {
            $query = get_queried_object();
            $term_id = $query->term_id;

            if (!isset($atts['category'])) $atts['category'] = '';

            $atts['category'] = trim(trim($atts['category'], ', ') . ',' . $term_id, ', ');
        }

        return $atts;
    }

    /**
     * Filter TinyMce Buttons
     * @param array $buttons
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function add_mce_buttons($buttons)
    {
        array_push($buttons, 'mec_mce_buttons');
        return $buttons;
    }

    /**
     * Filter TinyMce plugins
     * @param array $plugins
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function add_mce_external_plugins($plugins)
    {
        $plugins['mec_mce_buttons'] = $this->asset('js/mec-external.js');
        return $plugins;
    }

    /**
     * Return JSON output id and the name of a post type
     * @param string $post_type
     * @return string JSON
     * @author Webnus <info@webnus.net>
     */
    public function mce_get_shortcode_list($post_type = 'mec_calendars')
    {
        $shortcodes = [];
        $shortcodes['mce_title'] = esc_html__('M.E. Calendar', 'modern-events-calendar-lite');
        $shortcodes['shortcodes'] = [];

        if (post_type_exists($post_type))
        {
            $shortcodes_list = get_posts([
                'post_type' => $post_type,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'order' => 'DESC',
            ]);

            if (count($shortcodes_list))
            {
                foreach ($shortcodes_list as $shortcode)
                {
                    $shortcode_item = [];
                    $shortcode_item['ID'] = $shortcode->ID;

                    // PostName
                    $shortcode_item['PN'] = $shortcode->post_name;
                    array_push($shortcodes['shortcodes'], $shortcode_item);
                }
            }
        }

        return json_encode($shortcodes);
    }

    /**
     * Return date_diff
     * @param string $start_date
     * @param string $end_date
     * @return object
     * @author Webnus <info@webnus.net>
     */
    public function date_diff($start_date, $end_date)
    {
        if (version_compare(PHP_VERSION, '5.3.0', '>='))
        {
            if (!empty($start_date) && !empty($end_date))
            {
                return date_diff(date_create($start_date), date_create($end_date));
            }
            else
            {
                return null;
            }
        }
        else
        {
            $start = new DateTime($start_date);
            $end = new DateTime($end_date);
            $days = round(($end->format('U') - $start->format('U')) / (60 * 60 * 24));

            $interval = new stdClass();
            $interval->days = abs($days);
            $interval->invert = ($days >= 0 ? 0 : 1);

            return $interval;
        }
    }

    /**
     * Convert a certain time into seconds (Hours should be in 24 hours format)
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     * @return int
     * @author Webnus <info@webnus.net>
     */
    public function time_to_seconds($hours, $minutes = 0, $seconds = 0)
    {
        return (((int) $hours * 3600) + ((int) $minutes * 60) + (int) $seconds);
    }

    /**
     * Convert a 12-hour format hour to a 24-hour format hour
     * @param int $hour
     * @param string $ampm
     * @param string $type
     * @return int
     * @author Webnus <info@webnus.net>
     */
    public function to_24hours($hour, $ampm = 'PM', $type = 'end')
    {
        // Time is already in 24-hour format
        if (is_null($ampm)) return $hour;

        $ampm = strtoupper($ampm);

        if ($ampm == 'AM' and $hour < 12) return $hour;
        else if ($ampm == 'AM' and $hour == 12 and $type === 'end') return 24;
        else if ($ampm == 'AM' and $hour == 12 and $type === 'start') return 0;
        else if ($ampm == 'PM' and $hour < 12) return ((int) $hour) + 12;
        else if ($ampm == 'PM' and $hour == 12) return 12;
        else if ($hour > 12) return $hour;
    }

    /**
     * Get rendered events based on a certain criteria
     * @param array $args
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_rendered_events($args = [])
    {
        $events = [];
        $sorted = [];

        // Parse the args
        $args = wp_parse_args($args, [
            'post_type' => $this->get_main_post_type(),
            'posts_per_page' => '-1',
            'post_status' => 'publish',
        ]);

        // The Query
        $query = new WP_Query($args);

        if ($query->have_posts())
        {
            // MEC Render Library
            $render = $this->getRender();

            // The Loop
            while ($query->have_posts())
            {
                $query->the_post();

                $event_id = get_the_ID();
                $rendered = $render->data($event_id);

                $data = new stdClass();
                $data->ID = $event_id;
                $data->data = $rendered;
                $data->dates = $render->dates($event_id, $rendered, 6);
                $data->date = isset($data->dates[0]) ? $data->dates[0] : [];

                // Caclculate event start time
                $event_start_time = strtotime($data->date['start']['date']) + $rendered->meta['mec_start_day_seconds'];

                // Add the event into the to be sorted array
                if (!isset($sorted[$event_start_time])) $sorted[$event_start_time] = [];
                $sorted[$event_start_time][] = $data;
            }

            ksort($sorted, SORT_NUMERIC);
        }

        // Add sorted events to the results
        foreach ($sorted as $sorted_events)
        {
            if (!is_array($sorted_events)) continue;
            foreach ($sorted_events as $sorted_event) $events[$sorted_event->ID] = $sorted_event;
        }

        // Restore original Post Data
        wp_reset_postdata();

        return $events;
    }

    /**
     * Duplicate an event
     * @param int $post_id
     * @return boolean|int
     * @author Webnus <info@webnus.net>
     */
    public function duplicate($post_id)
    {
        $post = get_post($post_id);

        // Post is not exists
        if (!$post) return false;

        // Duplicate Post
        $new_post_id = $this->duplicate_post($post_id);

        // MEC DB Library
        $db = $this->getDB();

        // Duplicate MEC record
        $mec_data = $db->select("SELECT * FROM `#__mec_events` WHERE `post_id`='$post_id'", 'loadAssoc');

        $q1 = "";
        $q2 = "";
        foreach ($mec_data as $key => $value)
        {
            if (in_array($key, ['id', 'post_id'])) continue;

            $q1 .= "`$key`,";
            $q2 .= "'$value',";
        }

        $db->q("INSERT INTO `#__mec_events` (`post_id`," . trim($q1, ', ') . ") VALUES ('$new_post_id'," . trim($q2, ', ') . ")");

        // Update Schedule
        $schedule = $this->getSchedule();
        $schedule->reschedule($new_post_id);

        return $new_post_id;
    }

    /**
     * Duplicate a post
     * @param int $post_id
     * @return boolean|int
     * @author Webnus <info@webnus.net>
     */
    public function duplicate_post($post_id)
    {
        $post = get_post($post_id);

        // Post is not exists
        if (!$post) return false;

        // MEC DB Library
        $db = $this->getDB();

        // New post data array
        $args = [
            'comment_status' => $post->comment_status,
            'ping_status' => $post->ping_status,
            'post_author' => $post->post_author,
            'post_content' => $post->post_content,
            'post_excerpt' => $post->post_excerpt,
            'post_name' => sanitize_title($post->post_name . '-' . mt_rand(100, 999)),
            'post_parent' => $post->post_parent,
            'post_password' => $post->post_password,
            'post_status' => 'draft',
            'post_title' => sprintf(esc_html__('Copy of %s', 'modern-events-calendar-lite'), $post->post_title),
            'post_type' => $post->post_type,
            'to_ping' => $post->to_ping,
            'menu_order' => $post->menu_order,
        ];

        // insert the new post
        $new_post_id = wp_insert_post($args);

        // get all current post terms ad set them to the new post draft
        $taxonomies = get_object_taxonomies($post->post_type);
        foreach ($taxonomies as $taxonomy)
        {
            $post_terms = wp_get_object_terms($post_id, $taxonomy, ['fields' => 'slugs']);
            wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
        }

        // duplicate all post meta
        $post_metas = $db->select("SELECT `meta_key`, `meta_value` FROM `#__postmeta` WHERE `post_id`='$post_id'", 'loadObjectList');
        if (count($post_metas))
        {
            $wpdb = $db->get_DBO();
            $sql_query = "INSERT INTO `#__postmeta` (post_id, meta_key, meta_value) ";

            $sql_query_sel = [];
            foreach ($post_metas as $meta_info)
            {
                $meta_key = esc_sql($meta_info->meta_key);
                $meta_value = $meta_info->meta_value;

                $sql_query_sel[] = $wpdb->prepare("SELECT $new_post_id, %s, %s", $meta_key, $meta_value);
            }

            $sql_query .= implode(" UNION ALL ", $sql_query_sel);
            $db->q($sql_query);
        }

        return $new_post_id;
    }

    /**
     * Returns start/end date label
     * @param array $start
     * @param array $end
     * @param string $format
     * @param string $separator
     * @param boolean $minify
     * @param integer $allday
     * @param object $event
     * @param boolean $omit_end_date
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function date_label($start, $end, $format, $separator = ' - ', $minify = true, $allday = 0, $event = null, $omit_end_date = false)
    {
        $start_datetime = $start['date'];
        $end_datetime = $end['date'];

        $start_datetime = preg_replace("/[^0-9\-\s:apmAPM]/", "", $start_datetime);
        $end_datetime = preg_replace("/[^0-9\-\s:apmAPM]/", "", $end_datetime);

        $start_parsed = date_parse($start_datetime);
        $end_parsed = date_parse($end_datetime);

        if (isset($start['hour']) and ($start_parsed === false or (is_array($start_parsed) and isset($start_parsed['hour']) and $start_parsed['hour'] === false)))
        {
            $s_hour = $start['hour'];
            if (strtoupper($start['ampm']) == 'AM' and $s_hour == '0') $s_hour = 12;

            $start_datetime .= ' ' . sprintf("%02d", $s_hour) . ':' . sprintf("%02d", $start['minutes']) . ' ' . $start['ampm'];
        }

        if (isset($end['hour']) and ($end_parsed === false or (is_array($end_parsed) and isset($end_parsed['hour']) and $end_parsed['hour'] === false)))
        {
            $e_hour = $end['hour'];
            if (strtoupper($end['ampm']) == 'AM' and $e_hour == '0') $e_hour = 12;

            $end_datetime .= ' ' . sprintf("%02d", $e_hour) . ':' . sprintf("%02d", $end['minutes']) . ' ' . $end['ampm'];
        }

        $start_timestamp = strtotime($start_datetime);
        $end_timestamp = strtotime($end_datetime);

        $timezone_GMT = new DateTimeZone("GMT");
        $timezone_event = new DateTimeZone($this->get_timezone($event));

        $dt_now = new DateTime("now", $timezone_GMT);
        
        // Validate datetime string before creating DateTime object to prevent fatal errors
        try {
            $dt_start = new DateTime($start_datetime, $timezone_GMT);
        } catch (Exception $e) {
            // If datetime string is invalid, use timestamp or fallback to current time
            if ($start_timestamp && $start_timestamp !== false) {
                $dt_start = new DateTime("@" . $start_timestamp);
                $dt_start->setTimezone($timezone_GMT);
            } else {
                // Fallback to current time if everything fails
                $dt_start = new DateTime("now", $timezone_GMT);
            }
        }
        
        try {
            $dt_end = new DateTime($end_datetime, $timezone_GMT);
        } catch (Exception $e) {
            // If datetime string is invalid, use timestamp or fallback to current time
            if ($end_timestamp && $end_timestamp !== false) {
                $dt_end = new DateTime("@" . $end_timestamp);
                $dt_end->setTimezone($timezone_GMT);
            } else {
                // Fallback to current time if everything fails
                $dt_end = new DateTime("now", $timezone_GMT);
            }
        }

        $offset_now = $timezone_event->getOffset($dt_now);
        $offset_start = $timezone_event->getOffset($dt_start);
        $offset_end = $timezone_event->getOffset($dt_end);

        if ($offset_now != $offset_start and !function_exists('wp_date'))
        {
            $diff = $offset_start - $offset_now;
            if ($diff > 0) $start_timestamp += $diff;
        }

        if ($offset_now != $offset_end and !function_exists('wp_date'))
        {
            $diff = $offset_end - $offset_now;
            if ($diff > 0) $end_timestamp += $diff;
        }

        // Event is All Day so remove the time formats
        if ($allday)
        {
            foreach (['a', 'A', 'B', 'g', 'G', 'h', 'H', 'i', 's', 'u', 'v'] as $f) $format = str_replace($f, '', $format);
            $format = trim($format, ': ');
        }

        $start_timestamp = apply_filters('mec_date_label_start_timestamp', $start_timestamp, $event);
        $end_timestamp = apply_filters('mec_date_label_end_timestamp', $end_timestamp, $event);

        if ($start_timestamp >= $end_timestamp)
        {
            $format = stripslashes($format);

            return '<span class="mec-start-date-label">' . esc_html($this->date_i18n($format, $start_timestamp, $event)) . '</span>';
        }
        else
        {
            $start_date = $this->date_i18n($format, $start_timestamp, $event);
            $end_date = $this->date_i18n($format, $end_timestamp, $event);

            if ($start_date == $end_date) return '<span class="mec-start-date-label">' . esc_html($start_date) . '</span>';
            else
            {
                $start_m = date('m', $start_timestamp);
                $end_m = date('m', $end_timestamp);

                $start_y = date('Y', $start_timestamp);
                $end_y = date('Y', $end_timestamp);

                // Same Month but Different Days
                if ($minify and $start_m == $end_m and $start_y == $end_y and date('d', $start_timestamp) != date('d', $end_timestamp))
                {
                    $month_format = 'F';
                    if (strpos($format, 'm') !== false) $month_format = 'm';
                    else if (strpos($format, 'M') !== false) $month_format = 'M';
                    else if (strpos($format, 'n') !== false) $month_format = 'n';

                    $year_format = '';
                    if (strpos($format, 'Y') !== false) $year_format = 'Y';
                    else if (strpos($format, 'y') !== false) $year_format = 'y';
                    else if (strpos($format, 'o') !== false) $year_format = 'o';

                    $start_m = $this->date_i18n($month_format, $start_timestamp, $event);
                    $start_y = trim($year_format) ? $this->date_i18n($year_format, $start_timestamp, $event) : '';
                    $end_y = trim($year_format) ? $this->date_i18n($year_format, $end_timestamp, $event) : '';

                    $f1 = '';
                    if (strpos($format, 'l d') !== false)
                    {
                        $f1 = $this->date_i18n('l d', $start_timestamp, $event) . ' - ' . $this->date_i18n('l d', $end_timestamp, $event);
                        $format = str_replace('l d', 'f1', $format);
                    }

                    $f2 = '';
                    if (strpos($format, 'dS') !== false)
                    {
                        $f2 = $this->date_i18n('dS', $start_timestamp, $event) . ' - ' . $this->date_i18n('dS', $end_timestamp, $event);
                        $format = str_replace('dS', 'f2', $format);
                    }

                    $f3 = '';
                    if (strpos($format, 'jS') !== false)
                    {
                        $f3 = $this->date_i18n('jS', $start_timestamp, $event) . ' - ' . $this->date_i18n('jS', $end_timestamp, $event);
                        $format = str_replace('jS', 'f3', $format);
                    }

                    $chars = str_split($format);

                    $date_label = '';
                    foreach ($chars as $char)
                    {
                        if (in_array($char, ['d', 'D', 'j', 'l', 'N', 'S', 'w', 'z']))
                        {
                            $dot = (strpos($format, $char . '.') !== false);
                            $date_label .= $this->date_i18n($char, $start_timestamp, $event) . ($dot ? '.' : '') . ' - ' . $this->date_i18n($char, $end_timestamp, $event);
                        }
                        else if (in_array($char, ['F', 'm', 'M', 'n']))
                        {
                            $date_label .= $start_m;
                        }
                        else if (in_array($char, ['Y', 'y', 'o']))
                        {
                            $date_label .= ($start_y === $end_y ? $start_y : $start_y . ' - ' . $end_y);
                        }
                        else if (in_array($char, ['e', 'I', 'O', 'P', 'p', 'T', 'Z']))
                        {
                            $date_label .= $this->date_i18n($char, $start_timestamp, $event);
                        }
                        else $date_label .= $char;
                    }

                    // Custom Date Formats
                    $date_label = str_replace('f1', $f1, $date_label);
                    $date_label = str_replace('f2', $f2, $date_label);
                    $date_label = str_replace('f3', $f3, $date_label);

                    return '<span class="mec-start-date-label">' . esc_html($date_label) . '</span>';
                }
                else
                {
                    $end_format = $format;
                    if ($omit_end_date && date('Ymd', $start_timestamp) === date('Ymd', $end_timestamp))
                    {
                        $end_format = get_option('time_format');
                    }

                    return apply_filters(
                        'mec_date_label_start_end_html',
                        '<span class="mec-start-date-label">' . esc_html($this->date_i18n($format, $start_timestamp, $event)) . '</span><span class="mec-end-date-label" itemprop="endDate">' . esc_html($separator . $this->date_i18n($end_format, $end_timestamp, $event)) . '</span>',
                        $start_timestamp,
                        $end_timestamp,
                        $event
                    );
                }
            }
        }
    }

    public function dateify($event, $format, $separator = ' - ')
    {
        // Settings
        $settings = $this->get_settings();

        $time = sprintf("%02d", $event->data->meta['mec_end_time_hour']) . ':';
        $time .= sprintf("%02d", $event->data->meta['mec_end_time_minutes']) . ' ';
        $time .= $event->data->meta['mec_end_time_ampm'];

        $start_date = $event->date['start']['date'];
        $end_date = $event->date['end']['date'];

        $start_timestamp = strtotime($event->date['start']['date']);
        $end_timestamp = strtotime($event->date['end']['date']);

        // Midnight Hour
        $midnight_hour = (isset($settings['midnight_hour']) and $settings['midnight_hour']) ? $settings['midnight_hour'] : 0;
        $midnight = $end_timestamp + (3600 * $midnight_hour);

        // End Date is before Midnight
        if ($start_timestamp < $end_timestamp and $midnight >= strtotime($end_date . ' ' . $time)) $end_date = date('Y-m-d', ($end_timestamp - 86400));

        return $this->date_label(['date' => $start_date], ['date' => $end_date], $format, $separator, true, 0, $event);
    }

    public function date_i18n($format, $time = null, $event = null)
    {
        // Force to numeric
        if (!is_numeric($time) && $time) $time = strtotime($time);

        if ($event and function_exists('wp_date'))
        {
            $TZO = ((isset($event->TZO) and $event->TZO and ($event->TZO instanceof DateTimeZone)) ? $event->TZO : $this->get_TZO($event));

            // Force to UTC
            $time = $time - $TZO->getOffset(new DateTime(date('Y-m-d H:i:s', $time)));

            return wp_date($format, $time, $TZO);
        }
        else
        {
            $timezone_GMT = new DateTimeZone("GMT");
            $timezone_site = new DateTimeZone($this->get_timezone());

            $dt_now = new DateTime("now", $timezone_GMT);
            $dt_time = new DateTime(date('Y-m-d', $time), $timezone_GMT);

            $offset_now = $timezone_site->getOffset($dt_now);
            $offset_time = $timezone_site->getOffset($dt_time);

            if ($offset_now != $offset_time and !function_exists('wp_date'))
            {
                $diff = $offset_time - $offset_now;
                if ($diff > 0) $time += $diff;
            }

            return date_i18n($format, $time);
        }
    }

    /**
     * Returns start/end time labels
     * @param string $start
     * @param string $end
     * @param array $args
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function display_time($start = '', $end = '', $args = [])
    {
        if (!trim($start)) return '';

        $class = isset($args['class']) ? esc_attr($args['class']) : 'mec-time-details';
        $separator = isset($args['separator']) ? esc_attr($args['separator']) : '-';
        $display_svg = isset($args['display_svg']) && $args['display_svg'];
        $icon = isset($args['icon']) && trim($args['icon']) ? $args['icon'] : '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path id="clock" d="M8,16a8,8,0,1,1,8-8A8.009,8.009,0,0,1,8,16ZM8,1.333A6.667,6.667,0,1,0,14.667,8,6.674,6.674,0,0,0,8,1.333Zm2.667,10a.665.665,0,0,1-.471-.2L7.529,8.471a.666.666,0,0,1-.144-.217A.673.673,0,0,1,7.333,8V3.333a.667.667,0,1,1,1.333,0V7.724L11.138,10.2a.667.667,0,0,1-.471,1.138Z" fill="#60daf2" fill-rule="evenodd"/></svg>';

        $return = '<div class="' . esc_attr($class) . '">' . ($display_svg ? $icon : '');
        $return .= '<span class="mec-start-time">' . esc_html($start) . '</span>';
        if (trim($end)) $return .= ' ' . $separator . ' <span class="mec-end-time">' . esc_html($end) . '</span>';
        $return .= '</div>';

        return $return;
    }

    /**
     * Returns end date of an event based on start date
     * @param string $date
     * @param object $event
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_end_date($date, $event)
    {
        $start_date = $event->meta['mec_date']['start'] ?? [];
        $end_date = $event->meta['mec_date']['end'] ?? [];

        // Event Past
        $past = $this->is_past($event->mec->end, $date);

        // Normal event
        if (isset($event->mec->repeat) and $event->mec->repeat == '0')
        {
            return $end_date['date'] ?? $date;
        }
        // Custom Days
        else if ($custom_date_db = $this->get_end_date_from_db($date, $event))
        {
            return $custom_date_db;
        }
        // Past Event
        else if ($past)
        {
            return $end_date['date'] ?? $date;
        }
        else
        {
            $event_period = ((isset($start_date['date']) and isset($end_date['date'])) ? $this->date_diff($start_date['date'], $end_date['date']) : false);
            $event_period_days = $event_period ? $event_period->days : 0;

            /**
             * Multiple Day Event
             * Check to see if today is between start day and end day.
             * For example start day is 5 and end day is 15, but we're in 9th so only 6 days remained till ending the event not 10 days.
             */
            if ($event_period_days)
            {
                $start_day = date('j', strtotime($start_date['date']));
                $day = date('j', strtotime($date));

                if ($day >= $start_day) $passed_days = $day - $start_day;
                else $passed_days = ($day + date('t', strtotime($start_date['date']))) - $start_day;

                if ($passed_days <= $event_period_days) $event_period_days = $event_period_days - $passed_days;
            }

            return date('Y-m-d', strtotime('+' . $event_period_days . ' Days', strtotime($date)));
        }
    }

    public function get_end_date_from_db($date, $event)
    {
        // Cache
        $cache = $this->getCache();

        return $cache->rememberOnce($event->ID . ':' . $date, function () use ($event, $date)
        {
            // DB
            $db = $this->getDB();

            return $db->select("SELECT `dend` FROM `#__mec_dates` WHERE `post_id`='" . $event->ID . "' AND `dstart`<='" . $date . "' AND `dend`>='" . $date . "' ORDER BY `id` DESC LIMIT 1", 'loadResult');
        });
    }

    /**
     * Get Archive Status of MEC
     * @return int
     * @author Webnus <info@webnus.net>
     */
    public function get_archive_status()
    {
        $settings = $this->get_settings();

        $status = isset($settings['archive_status']) ? $settings['archive_status'] : '1';
        return apply_filters('mec_archive_status', $status);
    }

    /**
     * Check to see if a table exists or not
     * @param string $table
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function table_exists($table = 'mec_events')
    {
        // MEC DB library
        $db = $this->getDB();

        return $db->q("SHOW TABLES LIKE '#__$table'");
    }

    /**
     * Create MEC Tables
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function create_mec_tables()
    {
        // MEC Events table already exists
        if ($this->table_exists('mec_events') and $this->table_exists('mec_dates') and $this->table_exists('mec_occurrences') and $this->table_exists('mec_users') and $this->table_exists('mec_bookings') and $this->table_exists('mec_booking_attendees')) return true;

        // MEC File library
        $file = $this->getFile();

        // MEC DB library
        $db = $this->getDB();

        // Run Queries
        $query_file = MEC_ABSPATH . 'assets' . DS . 'sql' . DS . 'tables.sql';
        if ($file->exists($query_file))
        {
            $queries = $file->read($query_file);
            $sqls = explode(';', $queries);

            foreach ($sqls as $sql)
            {
                $sql = trim($sql, '; ');
                if (trim($sql) == '') continue;

                $sql .= ';';

                try
                {
                    $db->q($sql);
                }
                catch (Exception $e)
                {
                }
            }
        }

        return true;
    }

    /**
     * Return HTML email type
     * @param string $content_type
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function html_email_type($content_type)
    {
        return 'text/html';
    }

    public function get_next_upcoming_event()
    {
        MEC::import('app.skins.list');

        // Get list skin
        $list = new MEC_skin_list();

        // Attributes
        $atts = [
            'show_only_past_events' => 0,
            'show_past_events' => 0,
            'start_date_type' => 'today',
            'sk-options' => [
                'list' => ['limit' => 1],
            ],
        ];

        // Initialize the skin
        $list->initialize($atts);

        // General Settings
        $settings = $this->get_settings();

        // Disable Ongoing Events
        $disable_for_ongoing = isset($settings['countdown_disable_for_ongoing_events']) && $settings['countdown_disable_for_ongoing_events'];
        if ($disable_for_ongoing) $list->hide_time_method = 'start';

        // Fetch the events
        $list->fetch();

        $events = $list->events;
        $key = key($events);

        return $events[$key][0] ?? [];
    }

    /**
     * Return a web page
     * @param string $url
     * @param int $timeout
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_web_page($url, $timeout = 20)
    {
        $result = false;

        // Doing WordPress Remote
        if (function_exists('wp_remote_get'))
        {
            $result = wp_remote_retrieve_body(wp_remote_get($url, [
                'body' => null,
                'timeout' => $timeout,
                'redirection' => 5,
            ]));
        }

        // Doing FGC
        if ($result === false)
        {
            $http = [];
            $result = @file_get_contents($url, false, stream_context_create(['http' => $http]));
        }

        return $result;
    }

    public function save_events($events = [])
    {
        $ids = [];

        foreach ($events as $event) $ids[] = $this->save_event($event, ($event['ID'] ?? null));
        return $ids;
    }

    public function save_event($event = [], $post_id = null)
    {
        $post = [
            'post_title' => $event['title'],
            'post_content' => $event['content'] ?? '',
            'post_type' => $this->get_main_post_type(),
            'post_status' => $event['status'] ?? 'publish',
        ];

        // Update previously inserted post
        if (!is_null($post_id)) $post['ID'] = $post_id;

        // Post Author
        if (isset($event['author']) and $event['author'] and is_numeric($event['author'])) $post['post_author'] = $event['author'];

        $post_id = wp_insert_post($post);

        update_post_meta($post_id, 'mec_location_id', ($event['location_id'] ?? 1));
        update_post_meta($post_id, 'mec_dont_show_map', 0);
        update_post_meta($post_id, 'mec_organizer_id', ($event['organizer_id'] ?? 1));

        $start_time_hour = ($event['start_time_hour'] ?? 8);
        $start_time_minutes = ($event['start_time_minutes'] ?? 0);
        $start_time_ampm = ($event['start_time_ampm'] ?? 'AM');

        $end_time_hour = ($event['end_time_hour'] ?? 6);
        $end_time_minutes = ($event['end_time_minutes'] ?? 0);
        $end_time_ampm = ($event['end_time_ampm'] ?? 'PM');

        $allday = ($event['allday'] ?? 0);
        $time_comment = ($event['time_comment'] ?? '');
        $hide_time = ((isset($event['date']) and isset($event['date']['hide_time'])) ? $event['date']['hide_time'] : 0);
        $hide_end_time = ((isset($event['date']) and isset($event['date']['hide_end_time'])) ? $event['date']['hide_end_time'] : 0);

        $day_start_seconds = $this->time_to_seconds($this->to_24hours($start_time_hour, $start_time_ampm), $start_time_minutes);
        $day_end_seconds = $this->time_to_seconds($this->to_24hours($end_time_hour, $end_time_ampm), $end_time_minutes);

        update_post_meta($post_id, 'mec_allday', $allday);
        update_post_meta($post_id, 'mec_hide_time', $hide_time);
        update_post_meta($post_id, 'mec_hide_end_time', $hide_end_time);

        update_post_meta($post_id, 'mec_start_date', $event['start']);
        update_post_meta($post_id, 'mec_start_time_hour', $start_time_hour);
        update_post_meta($post_id, 'mec_start_time_minutes', $start_time_minutes);
        update_post_meta($post_id, 'mec_start_time_ampm', $start_time_ampm);
        update_post_meta($post_id, 'mec_start_day_seconds', $day_start_seconds);

        update_post_meta($post_id, 'mec_end_date', $event['end']);
        update_post_meta($post_id, 'mec_end_time_hour', $end_time_hour);
        update_post_meta($post_id, 'mec_end_time_minutes', $end_time_minutes);
        update_post_meta($post_id, 'mec_end_time_ampm', $end_time_ampm);
        update_post_meta($post_id, 'mec_end_day_seconds', $day_end_seconds);

        update_post_meta($post_id, 'mec_repeat_status', $event['repeat_status']);
        update_post_meta($post_id, 'mec_repeat_type', $event['repeat_type']);
        update_post_meta($post_id, 'mec_repeat_interval', $event['interval']);

        update_post_meta($post_id, 'mec_certain_weekdays', explode(',', trim(($event['weekdays'] ?? ''), ', ')));

        $date = [
            'start' => ['date' => $event['start'], 'hour' => $start_time_hour, 'minutes' => $start_time_minutes, 'ampm' => $start_time_ampm],
            'end' => ['date' => $event['end'], 'hour' => $end_time_hour, 'minutes' => $end_time_minutes, 'ampm' => $end_time_ampm],
            'repeat' => ((isset($event['date']) and isset($event['date']['repeat']) and is_array($event['date']['repeat'])) ? $event['date']['repeat'] : []),
            'allday' => $allday,
            'hide_time' => ((isset($event['date']) and isset($event['date']['hide_time'])) ? $event['date']['hide_time'] : 0),
            'hide_end_time' => ((isset($event['date']) and isset($event['date']['hide_end_time'])) ? $event['date']['hide_end_time'] : 0),
            'comment' => $time_comment,
        ];

        // Finish Date
        $finish_date = ($event['finish'] ?? '');

        // End after count
        $repeat_count = ($event['repeat_count'] ?? null);
        if ($repeat_count and is_numeric($repeat_count))
        {
            $repeat_count = ($repeat_count - 1);
            update_post_meta($post_id, 'mec_repeat_end_at_occurrences', $repeat_count);
            update_post_meta($post_id, 'mec_repeat_end', 'occurrences');

            $date['repeat']['end'] = 'occurrences';
            $date['repeat']['end_at_occurrences'] = $repeat_count;

            $plus_date = '';
            if ($event['repeat_type'] == 'daily')
            {
                $plus_date = '+' . $repeat_count * $event['interval'] . ' Days';
            }
            else if ($event['repeat_type'] == 'weekly')
            {
                $plus_date = '+' . $repeat_count * ($event['interval']) . ' Days';
            }
            else if ($event['repeat_type'] == 'monthly')
            {
                $plus_date = '+' . $repeat_count * $event['interval'] . ' Months';
            }
            else if ($event['repeat_type'] == 'yearly')
            {
                $plus_date = '+' . $repeat_count * $event['interval'] . ' Years';
            }

            if ($plus_date) $finish_date = date('Y-m-d', strtotime($plus_date, strtotime($event['end'])));
        }

        if ($finish_date)
        {
            update_post_meta($post_id, 'mec_repeat_end_at_date', $finish_date);
            update_post_meta($post_id, 'mec_repeat_end', ($repeat_count ? 'occurrences' : 'date'));

            $date['repeat']['end'] = ($repeat_count ? 'occurrences' : 'date');
            $date['repeat']['end_at_date'] = $finish_date;
        }

        update_post_meta($post_id, 'mec_date', $date);

        // Not In Days
        $not_in_days = ($event['not_in_days'] ?? '');
        if ($not_in_days) update_post_meta($post_id, 'mec_not_in_days', $not_in_days);

        // Creating $mec array for inserting in mec_events table
        $mec = ['post_id' => $post_id, 'start' => $event['start'], 'repeat' => $event['repeat_status'], 'rinterval' => $event['interval'], 'time_start' => $day_start_seconds, 'time_end' => $day_end_seconds];

        // Add parameters to the $mec
        $mec['end'] = (trim($finish_date) ? $finish_date : '0000-00-00');
        $mec['year'] = $event['year'] ?? null;
        $mec['month'] = $event['month'] ?? null;
        $mec['day'] = $event['day'] ?? null;
        $mec['week'] = $event['week'] ?? null;
        $mec['weekday'] = $event['weekday'] ?? null;
        $mec['weekdays'] = $event['weekdays'] ?? null;
        $mec['days'] = $event['days'] ?? '';
        $mec['not_in_days'] = $not_in_days;

        // MEC DB Library
        $db = $this->getDB();

        // Update MEC Events Table
        $mec_event_id = $db->select("SELECT `id` FROM `#__mec_events` WHERE `post_id`='$post_id'", 'loadResult');

        if (!$mec_event_id)
        {
            $q1 = "";
            $q2 = "";

            foreach ($mec as $key => $value)
            {
                $q1 .= "`$key`,";

                if (is_null($value)) $q2 .= "NULL,";
                else $q2 .= "'$value',";
            }

            $db->q("INSERT INTO `#__mec_events` (" . trim($q1, ', ') . ") VALUES (" . trim($q2, ', ') . ")", 'INSERT');
        }
        else
        {
            $q = "";

            foreach ($mec as $key => $value)
            {
                if (is_null($value)) $q .= "`$key`=NULL,";
                else $q .= "`$key`='$value',";
            }

            $db->q("UPDATE `#__mec_events` SET " . trim($q, ', ') . " WHERE `id`='$mec_event_id'");
        }

        if (isset($event['meta']) and is_array($event['meta'])) foreach ($event['meta'] as $key => $value) update_post_meta($post_id, $key, $value);

        // Update Schedule
        $schedule = $this->getSchedule();
        $schedule->reschedule($post_id, $schedule->get_reschedule_maximum($event['repeat_type']));

        return $post_id;
    }

    public function save_category($category = [])
    {
        $name = $category['name'] ?? '';
        if (!trim($name)) return false;

        $term = get_term_by('name', $name, 'mec_category');

        // Term already exists
        if (is_object($term) and isset($term->term_id)) return $term->term_id;

        $term = wp_insert_term($name, 'mec_category');

        // An error occurred
        if (is_wp_error($term)) return false;

        $category_id = $term['term_id'];
        if (!$category_id) return false;

        return $category_id;
    }

    public function save_tag($tag = [])
    {
        $name = $tag['name'] ?? '';
        if (!trim($name)) return false;

        $term = get_term_by('name', $name, apply_filters('mec_taxonomy_tag', ''));

        // Term already exists
        if (is_object($term) and isset($term->term_id)) return $term->term_id;

        $term = wp_insert_term($name, apply_filters('mec_taxonomy_tag', ''));

        // An error occurred
        if (is_wp_error($term)) return false;

        $tag_id = $term['term_id'];
        if (!$tag_id) return false;

        return $tag_id;
    }

    public function save_label($label = [])
    {
        $name = $label['name'] ?? '';
        if (!trim($name)) return false;

        $term = get_term_by('name', $name, 'mec_label');

        // Term already exists
        if (is_object($term) and isset($term->term_id)) return $term->term_id;

        $term = wp_insert_term($name, 'mec_label');

        // An error occurred
        if (is_wp_error($term)) return false;

        $label_id = $term['term_id'];
        if (!$label_id) return false;

        $color = $label['color'] ?? '';
        update_term_meta($label_id, 'color', $color);

        return $label_id;
    }

    public function save_organizer($organizer = [])
    {
        $name = $organizer['name'] ?? '';
        if (!trim($name)) return false;

        $term = get_term_by('name', $name, 'mec_organizer');

        // Term already exists
        if (is_object($term) and isset($term->term_id)) return $term->term_id;

        $term = wp_insert_term($name, 'mec_organizer');

        // An error occurred
        if (is_wp_error($term)) return false;

        $organizer_id = $term['term_id'];
        if (!$organizer_id) return false;

        if (isset($organizer['tel']) && strpos($organizer['tel'], '@') !== false)
        {
            // Just for EventON
            $tel = '';
            $email = trim($organizer['tel']) ? $organizer['tel'] : '';
        }
        else
        {
            $tel = (isset($organizer['tel']) and trim($organizer['tel'])) ? $organizer['tel'] : '';
            $email = (isset($organizer['email']) and trim($organizer['email'])) ? $organizer['email'] : '';
        }

        $url = (isset($organizer['url']) and trim($organizer['url'])) ? $organizer['url'] : '';
        $thumbnail = $organizer['thumbnail'] ?? '';

        update_term_meta($organizer_id, 'tel', $tel);
        update_term_meta($organizer_id, 'email', $email);
        update_term_meta($organizer_id, 'url', $url);
        if (trim($thumbnail)) update_term_meta($organizer_id, 'thumbnail', $thumbnail);

        return $organizer_id;
    }

    public function save_location($location = [])
    {
        $name = $location['name'] ?? '';
        if (!trim($name)) return false;

        $term = get_term_by('name', $name, 'mec_location');

        // Term already exists
        if (is_object($term) and isset($term->term_id)) return $term->term_id;

        $term = wp_insert_term($name, 'mec_location');

        // An error occurred
        if (is_wp_error($term)) return false;

        $location_id = $term['term_id'];
        if (!$location_id) return false;

        $latitude = (isset($location['latitude']) and trim($location['latitude'])) ? $location['latitude'] : 0;
        $longitude = (isset($location['longitude']) and trim($location['longitude'])) ? $location['longitude'] : 0;
        $address = $location['address'] ?? '';
        $thumbnail = $location['thumbnail'] ?? '';
        $url = $location['url'] ?? '';

        if (!trim($latitude) or !trim($longitude))
        {
            $geo_point = $this->get_lat_lng($address);

            $latitude = $geo_point[0];
            $longitude = $geo_point[1];
        }

        update_term_meta($location_id, 'address', $address);
        update_term_meta($location_id, 'latitude', $latitude);
        update_term_meta($location_id, 'longitude', $longitude);
        update_term_meta($location_id, 'url', $url);

        if (trim($thumbnail)) update_term_meta($location_id, 'thumbnail', $thumbnail);
        return $location_id;
    }

    public function save_speaker($speaker = [])
    {
        $name = $speaker['name'] ?? '';
        if (!trim($name)) return false;

        $term = get_term_by('name', $name, 'mec_speaker');

        // Term already exists
        if (is_object($term) and isset($term->term_id)) return $term->term_id;

        $term = wp_insert_term($name, 'mec_speaker');

        // An error occurred
        if (is_wp_error($term)) return false;

        $speaker_id = $term['term_id'];
        if (!$speaker_id) return false;

        $job_title = (isset($speaker['job_title']) and trim($speaker['job_title'])) ? $speaker['job_title'] : '';
        $tel = (isset($speaker['tel']) and trim($speaker['tel'])) ? $speaker['tel'] : '';
        $email = (isset($speaker['email']) and trim($speaker['email'])) ? $speaker['email'] : '';
        $facebook = (isset($speaker['facebook']) and trim($speaker['facebook'])) ? esc_url($speaker['facebook']) : '';
        $twitter = (isset($speaker['twitter']) and trim($speaker['twitter'])) ? esc_url($speaker['twitter']) : '';
        $instagram = (isset($speaker['instagram']) and trim($speaker['instagram'])) ? esc_url($speaker['instagram']) : '';
        $linkedin = (isset($speaker['linkedin']) and trim($speaker['linkedin'])) ? esc_url($speaker['linkedin']) : '';
        $website = (isset($speaker['website']) and trim($speaker['website'])) ? esc_url($speaker['website']) : '';
        $thumbnail = $speaker['thumbnail'] ?? '';

        update_term_meta($speaker_id, 'job_title', $job_title);
        update_term_meta($speaker_id, 'tel', $tel);
        update_term_meta($speaker_id, 'email', $email);
        update_term_meta($speaker_id, 'facebook', $facebook);
        update_term_meta($speaker_id, 'twitter', $twitter);
        update_term_meta($speaker_id, 'instagram', $instagram);
        update_term_meta($speaker_id, 'linkedin', $linkedin);
        update_term_meta($speaker_id, 'website', $website);
        if (trim($thumbnail)) update_term_meta($speaker_id, 'thumbnail', $thumbnail);

        return $speaker_id;
    }

    public function save_sponsor($sponsor = [])
    {
        $name = $sponsor['name'] ?? '';
        if (!trim($name)) return false;

        $term = get_term_by('name', $name, 'mec_sponsor');

        // Term already exists
        if (is_object($term) and isset($term->term_id)) return $term->term_id;

        $term = wp_insert_term($name, 'mec_sponsor');

        // An error occurred
        if (is_wp_error($term)) return false;

        $sponsor_id = $term['term_id'];
        if (!$sponsor_id) return false;

        $link = (isset($sponsor['link']) and trim($sponsor['link'])) ? esc_url($sponsor['link']) : '';
        $logo = (isset($sponsor['logo']) and trim($sponsor['logo'])) ? esc_url($sponsor['logo']) : '';

        update_term_meta($sponsor_id, 'link', $link);
        if (trim($logo)) update_term_meta($sponsor_id, 'logo', $logo);

        return $sponsor_id;
    }

    /**
     * Returns data export array for one event
     * @param int $event_id
     * @return stdClass
     * @author Webnus <info@webnus.net>
     */
    public function export_single($event_id)
    {
        // MEC Render Library
        $render = $this->getRender();

        return $render->data($event_id);
    }

    /**
     * Converts array to XML string
     * @param array $data
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function xml_convert($data)
    {
        $main_node = array_keys($data);

        // Creating SimpleXMLElement object
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $main_node[0] . '></' . $main_node[0] . '>');

        // Convert array to xml
        $this->array_to_xml($data[$main_node[0]], $xml);

        // Return XML String
        return $xml->asXML();
    }

    public function array_to_xml($data, &$xml)
    {
        foreach ($data as $key => $value)
        {
            if (is_numeric($key)) $key = 'item';
            if ($key === 'edited_occurrences') continue;

            if (is_array($value))
            {
                $sub_node = $xml->addChild($key);
                $this->array_to_xml($value, $sub_node);
            }
            else if (is_object($value))
            {
                $sub_node = $xml->addChild($key);
                $this->array_to_xml($value, $sub_node);
            }
            else
            {
                $xml->addChild($key, ($value ? htmlspecialchars($value, ENT_XML1, 'UTF-8') : $value));
            }
        }
    }

    /**
     * Returns Weekdays Day Numbers
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_weekdays()
    {
        $weekdays = [1, 2, 3, 4, 5];

        // Get weekdays from options
        $settings = $this->get_settings();
        if (isset($settings['weekdays']) and is_array($settings['weekdays']) and count($settings['weekdays'])) $weekdays = $settings['weekdays'];

        return apply_filters('mec_weekday_numbers', $weekdays);
    }

    /**
     * Returns Weekends Day Numbers
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_weekends()
    {
        $weekends = [6, 7];

        // Get weekdays from options
        $settings = $this->get_settings();
        if (isset($settings['weekends']) and is_array($settings['weekends']) and count($settings['weekends'])) $weekends = $settings['weekends'];

        return apply_filters('mec_weekend_numbers', $weekends);
    }

    /**
     * Returns Event link with Occurrence Date
     * @param string|object $event
     * @param string $date
     * @param boolean $force
     * @param array $time
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_event_date_permalink($event, $date = null, $force = false, $time = null)
    {
        // Get MEC Options
        $settings = $this->get_settings();

        if (is_object($event))
        {
            // Event Permalink
            $url = $event->data->permalink;
            $referer = $_SERVER['HTTP_REFERER'] ?? '';

            if (str_contains($referer, 'external=1'))
            {
                if (str_contains($url, '?'))
                {
                    $url .= '&external=1';
                }
                else
                {
                    $url .= '?external=1';
                }
            }

            // Return same URL if date is not provided
            if (is_null($date)) return apply_filters('mec_event_permalink', $url);

            // Single Page Date method is set to next date
            if (!$force && (!isset($settings['single_date_method']) || $settings['single_date_method'] === 'next')) return apply_filters('mec_event_permalink', $url);

            if (is_array($time) and isset($time['start_timestamp'])) $time_str = date('H:i:s', $time['start_timestamp']);
            else if (is_array($time) and isset($time['start_raw'])) $time_str = $time['start_raw'];
            else if (isset($event->data->time) and is_array($event->data->time) and isset($event->data->time['start_timestamp'])) $time_str = date('H:i:s', $event->data->time['start_timestamp']);
            else if (isset($event->data->time) and is_array($event->data->time) and isset($event->data->time['start_raw'])) $time_str = date('H:i:s', strtotime($event->data->time['start_raw']));
            else $time_str = $event->data->time['start_raw'];

            // Timestamp
            $timestamp = strtotime($date . ' ' . $time_str);

            // Do not add occurrence when custom link is set
            $read_more = (isset($event->data->meta) and isset($event->data->meta['mec_read_more']) and filter_var($event->data->meta['mec_read_more'], FILTER_VALIDATE_URL)) ? $event->data->meta['mec_read_more'] : null;
            $read_more_occ_url = MEC_feature_occurrences::param($event->ID, $timestamp, 'read_more', $read_more);

            if ($read_more_occ_url && filter_var($read_more_occ_url, FILTER_VALIDATE_URL)) $url = $read_more_occ_url;

            // Add Date to the URL
            $url = $this->add_qs_var('occurrence', $date, $url);

            $repeat_type = $event->data->meta['mec_repeat_type'] ?? '';
            if ($repeat_type === 'custom_days' && isset($event->data->time['start_raw']))
            {
                // Add Time
                $url = $this->add_qs_var('time', $timestamp, $url);
            }

            return apply_filters('mec_event_permalink', $url);
        }
        else
        {
            // Event Permalink
            $url = $event;

            // Return same URL if data is not provided
            if (is_null($date)) return apply_filters('mec_event_permalink', $url);

            // Single Page Date method is set to next date
            if (!$force && (!isset($settings['single_date_method']) || $settings['single_date_method'] === 'next')) return apply_filters('mec_event_permalink', $url);

            return apply_filters('mec_event_permalink', $this->add_qs_var('occurrence', $date, $url));
        }
    }

    /**
     * Register MEC Activity Action Type in BuddeyPress
     * @return void
     */
    public function bp_register_activity_actions()
    {
        bp_activity_set_action(
            'mec',
            'booked_event',
            esc_html__('Booked an event.', 'modern-events-calendar-lite')
        );
    }

    /**
     * Add a new activity to BuddyPress when a user book an event
     * @param int $book_id
     * @return boolean|int
     */
    public function bp_add_activity($book_id)
    {
        // Get MEC Options
        $settings = $this->get_settings();

        // BuddyPress' integration is disabled
        if (!isset($settings['bp_status']) || !$settings['bp_status']) return false;

        // BuddyPress add activity is disabled
        if (!isset($settings['bp_add_activity']) || !$settings['bp_add_activity']) return false;

        // BuddyPress is not installed or activated
        if (!function_exists('bp_activity_add')) return false;

        $verification = get_post_meta($book_id, 'mec_verified', true);
        $confirmation = get_post_meta($book_id, 'mec_confirmed', true);

        // Booking is not verified or confirmed
        if ($verification != 1 or $confirmation != 1) return false;

        $event_id = get_post_meta($book_id, 'mec_event_id', true);
        $booker_id = get_post_field('post_author', $book_id);

        $event_title = get_the_title($event_id);
        $event_link = get_the_permalink($event_id);

        $profile_link = bp_core_get_userlink($booker_id);
        $bp_activity_id = get_post_meta($book_id, 'mec_bp_activity_id', true);

        $activity_id = bp_activity_add([
            'id' => $bp_activity_id,
            'action' => sprintf(esc_html__('%s booked %s event.', 'modern-events-calendar-lite'), $profile_link, '<a href="' . esc_url($event_link) . '">' . esc_html($event_title) . '</a>'),
            'component' => 'mec',
            'type' => 'booked_event',
            'primary_link' => $event_link,
            'user_id' => $booker_id,
            'item_id' => $book_id,
            'secondary_item_id' => $event_id,
        ]);

        // Set Activity ID
        update_post_meta($book_id, 'mec_bp_activity_id', $activity_id);

        return $activity_id;
    }

    public function bp_add_profile_menu()
    {
        // Get MEC Options
        $settings = $this->get_settings();

        // BuddyPress' integration is disabled
        if (!isset($settings['bp_status']) or !$settings['bp_status']) return false;

        // BuddyPress' events menus is disabled
        if (!isset($settings['bp_profile_menu']) or !$settings['bp_profile_menu']) return false;

        // User is not logged in
        if (!is_user_logged_in()) return false;

        global $bp;

        // Loggedin User is not Displayed User
        if (!isset($bp->displayed_user) or (isset($bp->displayed_user->id) and get_current_user_id() != $bp->displayed_user->id)) return false;

        bp_core_new_nav_item([
            'name' => esc_html__('Events', 'modern-events-calendar-lite'),
            'slug' => 'mec-events',
            'screen_function' => [$this, 'bp_profile_menu_screen'],
            'position' => 30,
            'parent_url' => bp_loggedin_user_domain() . '/mec-events/',
            'parent_slug' => $bp->profile->slug,
            'default_subnav_slug' => 'events',
        ]);

        return true;
    }

    public function bp_profile_menu_screen()
    {
        add_action('bp_template_title', [$this, 'bp_profile_menu_title']);
        add_action('bp_template_content', [$this, 'bp_profile_menu_content']);

        bp_core_load_template(['buddypress/members/single/plugins']);
    }

    public function bp_profile_menu_title()
    {
        echo esc_html__('Events', 'modern-events-calendar-lite');
    }

    public function bp_profile_menu_content()
    {
        echo do_shortcode('[MEC_fes_list relative-link="1"]');
    }

    /**
     * Add booker information to mailchimp list
     * @param int $book_id
     * @return boolean
     */
    public function mailchimp_add_subscriber($book_id)
    {
        // Get MEC Options
        $settings = $this->get_settings();
        $ml_settings = $this->get_ml_settings();

        // Mailchim integration is disabled
        if (!isset($settings['mchimp_status']) or (isset($settings['mchimp_status']) and !$settings['mchimp_status'])) return false;

        $api_key = $settings['mchimp_api_key'] ?? '';
        $list_id = $settings['mchimp_list_id'] ?? '';

        // Mailchim credentials are required
        if (!trim($api_key) or !trim($list_id)) return false;

        // Options
        $date_format = (isset($ml_settings['booking_date_format1']) and trim($ml_settings['booking_date_format1'])) ? $ml_settings['booking_date_format1'] : 'Y-m-d';
        $segment_status = (isset($settings['mchimp_segment_status']) and $settings['mchimp_segment_status']);

        // Booking Date
        $mec_date = get_post_meta($book_id, 'mec_date', true);
        $dates = (trim($mec_date) ? explode(':', $mec_date) : []);
        $booking_date = date($date_format, $dates[0]);

        // Event Title
        $event_id = get_post_meta($book_id, 'mec_event_id', true);
        $event = get_post($event_id);

        $book = $this->getBook();
        $attendees = $book->get_attendees($book_id);

        $data_center = substr($api_key, strpos($api_key, '-') + 1);
        $subscription_status = isset($settings['mchimp_subscription_status']) ? $settings['mchimp_subscription_status'] : 'subscribed';

        $member_response = null;
        $did = [];

        foreach ($attendees as $attendee)
        {
            // Name
            $name = ((isset($attendee['name']) and trim($attendee['name'])) ? $attendee['name'] : '');

            // Email
            $email = ((isset($attendee['email']) and trim($attendee['email'])) ? $attendee['email'] : '');
            if (!is_email($email)) continue;

            // No Duplicate
            if (in_array($email, $did)) continue;
            $did[] = $email;

            $names = explode(' ', $name);

            $first_name = $names[0];
            unset($names[0]);

            $last_name = implode(' ', $names);

            // UPSERT
            $member_response = wp_remote_request('https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5(strtolower($email)), [
                'method' => 'PUT',
                'body' => json_encode([
                    'email_address' => $email,
                    'status' => $subscription_status,
                    'merge_fields' => [
                        'FNAME' => $first_name,
                        'LNAME' => $last_name,
                    ],
                    'tags' => [$booking_date, $event->post_title],
                ]),
                'timeout' => '10',
                'redirection' => '10',
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Basic ' . base64_encode('user:' . $api_key)],
            ]);

            // TAGS
            wp_remote_post('https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5(strtolower($email)) . '/tags', [
                'body' => json_encode([
                    'tags' => [
                        ['name' => $booking_date, 'status' => 'active'],
                        ['name' => $event->post_title, 'status' => 'active'],
                    ],
                ]),
                'timeout' => '10',
                'redirection' => '10',
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Basic ' . base64_encode('user:' . $api_key)],
            ]);
        }

        // Handle Segment
        if ($segment_status)
        {
            wp_remote_post('https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $list_id . '/segments/', [
                'body' => json_encode([
                    'name' => sprintf('%s at %s', $event->post_title, $booking_date),
                    'options' => [
                        'match' => 'any',
                        'conditions' => [],
                    ],
                ]),
                'timeout' => '10',
                'redirection' => '10',
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Basic ' . base64_encode('user:' . $api_key)],
            ]);
        }

        return ($member_response ? wp_remote_retrieve_response_code($member_response) : false);
    }

    /**
     * Add booker information to campaign monitor list
     * @param int $book_id
     * @return boolean
     */
    public function campaign_monitor_add_subscriber($book_id)
    {
        // Skip on Lite
        if (!$this->getPRO()) return false;

        require_once MEC_ABSPATH . '/app/api/Campaign_Monitor/csrest_subscribers.php';

        // Get MEC Options
        $settings = $this->get_settings();

        // Campaign Monitor integration is disabled
        if (!isset($settings['campm_status']) or (isset($settings['campm_status']) and !$settings['campm_status'])) return false;

        $api_key = $settings['campm_api_key'] ?? '';
        $list_id = $settings['campm_list_id'] ?? '';

        // Campaign Monitor credentials are required
        if (!trim($api_key) or !trim($list_id)) return false;

        // MEC User
        $u = $this->getUser();
        $booker = $u->booking($book_id);

        $wrap = new CS_REST_Subscribers($list_id, $api_key);
        $wrap->add([
            'EmailAddress' => $booker->user_email,
            'Name' => $booker->first_name . ' ' . $booker->last_name,
            'ConsentToTrack' => 'yes',
            'Resubscribe' => true,
        ]);
    }

    /**
     * Add booker information to mailerlite list
     * @param int $book_id
     * @return boolean}int
     */
    public function mailerlite_add_subscriber($book_id)
    {
        // Get MEC Options
        $settings = $this->get_settings();

        // mailerlite integration is disabled
        if (!isset($settings['mailerlite_status']) or (isset($settings['mailerlite_status']) and !$settings['mailerlite_status'])) return false;

        $api_key = $settings['mailerlite_api_key'] ?? '';
        $list_id = $settings['mailerlite_list_id'] ?? '';

        // mailerlite credentials are required
        if (!trim($api_key) or !trim($list_id)) return false;

        // MEC User
        $u = $this->getUser();
        $booker = $u->booking($book_id);

        $url = 'https://api.mailerlite.com/api/v2/groups/' . $list_id . '/subscribers';

        $json = json_encode([
            'email' => $booker->user_email,
            'name' => $booker->first_name . ' ' . $booker->last_name,
        ]);

        // Execute the Request and Return the Response Code
        return wp_remote_retrieve_response_code(wp_remote_post($url, [
            'body' => $json,
            'timeout' => '10',
            'redirection' => '10',
            'headers' => ['Content-Type' => 'application/json', 'X-MailerLite-ApiKey' => $api_key],
        ]));
    }

    /**
     * Add booker information to Active Campaign list
     * @param int $book_id
     * @return boolean
     */
    public function active_campaign_add_subscriber($book_id)
    {
        // Get MEC Options
        $settings = $this->get_settings();

        // Mailchim integration is disabled
        if (!isset($settings['active_campaign_status']) or (isset($settings['active_campaign_status']) and !$settings['active_campaign_status'])) return false;

        $api_url = $settings['active_campaign_api_url'] ?? '';
        $api_key = $settings['active_campaign_api_key'] ?? '';
        $list_id = $settings['active_campaign_list_id'] ?? '';

        // Mailchim credentials are required
        if (!trim($api_url) or !trim($api_key)) return false;

        // MEC User
        $u = $this->getUser();
        $booker = $u->booking($book_id);

        $url = $api_url . '/api/3/contact/sync';

        $array_parameters = [
            'email' => $booker->user_email,
            'firstName' => $booker->first_name,
            'lastName' => $booker->last_name,
        ];
        $array_parameters = apply_filters('mec_active_campaign_parameters', $array_parameters, $booker, $book_id);
        $json = json_encode([
            'contact' => $array_parameters,
        ]);

        // Execute the Request and Return the Response Code
        $request = wp_remote_post($url, [
            'body' => $json,
            'timeout' => '10',
            'redirection' => '10',
            'headers' => ['Content-Type' => 'application/json', 'Api-Token' => $api_key],
        ]);

        if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200)
        {
            error_log(print_r($request, true));
        }
        $response = wp_remote_retrieve_body($request);

        // Subscribe to list
        if (trim($list_id))
        {
            $person = json_decode($response);
            $new_url = $api_url . '/api/3/contactLists';
            $new_json = json_encode([
                'contactList' => [
                    'list' => (int) $list_id,
                    'contact' => (int) $person->contact->id,
                    'status' => 1,
                ],
            ]);
            $new_request = wp_remote_post($new_url, [
                'body' => $new_json,
                'timeout' => '10',
                'redirection' => '10',
                'headers' => ['Content-Type' => 'application/json', 'Api-Token' => $api_key],
            ]);

            if (is_wp_error($new_request) || wp_remote_retrieve_response_code($new_request) != 200)
            {
                error_log(print_r($new_request, true));
            }

            $new_response = wp_remote_retrieve_body($new_request);
        }
    }

    /**
     * Add booker information to Aweber list
     * @param int $book_id
     * @return boolean
     */
    public function aweber_add_subscriber($book_id)
    {
        // Aweber Plugin is not installed or it's not activated
        if (!class_exists('AWeberWebFormPluginNamespace\AWeberWebformPlugin')) return false;

        // Get MEC Options
        $settings = $this->get_settings();

        // AWeber's integration is disabled
        if (!isset($settings['aweber_status']) || !$settings['aweber_status']) return false;

        $list_id = isset($settings['aweber_list_id']) ? preg_replace("/[^0-9]/", "", $settings['aweber_list_id']) : '';

        // AWeber's credentials are required
        if (!trim($list_id)) return false;

        $aweber = new \AWeberWebFormPluginNamespace\AWeberWebformPlugin();

        // MEC User
        $u = $this->getUser();
        $booker = $u->booking($book_id);
        $name = trim($booker->first_name . ' ' . $booker->last_name);

        return $aweber->create_subscriber($booker->user_email, null, $list_id, $name, 'a,b');
    }

    /**
     * Add booker information to Mailpoet list
     * @param int $book_id
     * @return boolean|array
     */
    public function mailpoet_add_subscriber($book_id)
    {
        // Mailpoet Plugin is not installed or it's not activated
        if (!class_exists(\MailPoet\API\API::class)) return false;

        // Get MEC Options
        $settings = $this->get_settings();

        // MailPoet integration is disabled
        if (!isset($settings['mailpoet_status']) or (isset($settings['mailpoet_status']) and !$settings['mailpoet_status'])) return false;

        // MailPoet API
        $mailpoet_api = \MailPoet\API\API::MP('v1');

        // List ID
        $list_ids = (isset($settings['mailpoet_list_id']) and trim($settings['mailpoet_list_id'])) ? [$settings['mailpoet_list_id']] : null;

        // MEC User
        $u = $this->getUser();
        $booker = $u->booking($book_id);

        try
        {
            return $mailpoet_api->addSubscriber([
                'email' => $booker->user_email,
                'first_name' => $booker->first_name,
                'last_name' => $booker->last_name,
            ], $list_ids);
        }
        catch (Exception $e)
        {
            if ($e->getCode() == 12 and $list_ids)
            {
                try
                {
                    $subscriber = $mailpoet_api->getSubscriber($booker->user_email);
                    return $mailpoet_api->subscribeToLists($subscriber['id'], $list_ids);
                }
                catch (Exception $e)
                {
                    return false;
                }
            }

            return false;
        }
    }

    /**
     * Add booker information to Sendfox list
     * @param int $book_id
     * @return boolean|array
     */
    public function sendfox_add_subscriber($book_id)
    {
        // Sendfox Plugin is not installed or it's not activated
        if (!function_exists('gb_sf4wp_add_contact')) return false;

        // Get MEC Options
        $settings = $this->get_settings();

        // Sendfox integration is disabled
        if (!isset($settings['sendfox_status']) || !$settings['sendfox_status']) return false;

        // List ID
        $list_id = ((isset($settings['sendfox_list_id']) and trim($settings['sendfox_list_id'])) ? (int) $settings['sendfox_list_id'] : null);

        // MEC User
        $u = $this->getUser();
        $booker = $u->booking($book_id);

        return gb_sf4wp_add_contact([
            'email' => $booker->user_email,
            'first_name' => $booker->first_name,
            'last_name' => $booker->last_name,
            'lists' => [$list_id],
        ]);
    }

    /**
     * Add booker information to constantcontact list
     * @param int $book_id
     * @return boolean|int
     */
    public function constantcontact_add_subscriber($book_id)
    {
        // Get MEC Options
        $settings = $this->get_settings();

        // constantcontact integration is disabled
        if (!isset($settings['constantcontact_status']) || !$settings['constantcontact_status']) return false;

        $api_key = $settings['constantcontact_api_key'] ?? '';
        $client_secret = $settings['constantcontact_client_secret'] ?? '';
        $list_id = $settings['constantcontact_list_id'] ?? '';

        // constantcontact credentials are required
        if (!trim($api_key) || !trim($client_secret) || !trim($list_id)) return false;

        // Constant Contact Refresh Token
        $constantcontact_refresh_token = get_option('mec_constantcontact_refresh_token', '');

        // Access Token
        $access_token = $this->get_constantcontact_access_token($constantcontact_refresh_token);

        // No Access Token
        if (!$constantcontact_refresh_token || !$access_token) return false;

        // MEC User
        $u = $this->getUser();
        $booker = $u->booking($book_id);

        $json = json_encode([
            'list_memberships' => [$list_id],
            'email_address' => $booker->user_email,
            'first_name' => $booker->first_name,
            'last_name' => $booker->last_name,
        ]);

        $response = wp_remote_post("https://api.cc.email/v3/contacts/sign_up_form", [
            'body' => $json,
            'timeout' => '10',
            'redirection' => '10',
            'headers' => [
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
            ],
        ]);

        return wp_remote_retrieve_response_code($response);
    }

    public function get_constantcontact_lists($refresh_token)
    {
        // Access Token
        $access_token = $this->get_constantcontact_access_token($refresh_token);

        $response = wp_remote_get("https://api.cc.email/v3/contact_lists?include_count=true&status=active&include_membership_count=all", [
            'timeout' => '10',
            'redirection' => '10',
            'headers' => [
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
            ],
        ]);

        $status = wp_remote_retrieve_response_code($response);
        if ($status !== 200) return [];

        $JSON = wp_remote_retrieve_body($response);
        $results = json_decode($JSON);

        return isset($results->lists) && is_array($results->lists) ? $results->lists : [];
    }

    public function get_constantcontact_access_token($refresh_token)
    {
        $settings = $this->get_settings();

        $client_id = isset($settings['constantcontact_api_key']) && trim($settings['constantcontact_api_key'])
            ? $settings['constantcontact_api_key']
            : '';

        $client_secret = isset($settings['constantcontact_client_secret']) && trim($settings['constantcontact_client_secret'])
            ? $settings['constantcontact_client_secret']
            : '';

        $auth = $client_id . ':' . $client_secret;
        $credentials = base64_encode($auth);

        $response = wp_remote_post("https://authz.constantcontact.com/oauth2/default/v1/token", [
            'body' => [
                'refresh_token' => $refresh_token,
                'grant_type' => 'refresh_token',
            ],
            'timeout' => '10',
            'redirection' => '10',
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . $credentials,
            ],
        ]);

        $status = wp_remote_retrieve_response_code($response);
        if ($status !== 200) return '';

        $JSON = wp_remote_retrieve_body($response);
        $results = json_decode($JSON);

        return $results->access_token ?? '';
    }

    /**
     * Returns Booking of a certain event at certain date
     * @param int $event_id
     * @param integer $timestamp
     * @param integer|string $limit
     * @param integer $user_id
     * @param boolean $verified
     * @return array
     */
    public function get_bookings($event_id, $timestamp = null, $limit = '-1', $user_id = null, $verified = true)
    {
        if ($timestamp)
        {
            $booking_options = get_post_meta($event_id, 'mec_booking', true);
            if (!is_array($booking_options)) $booking_options = [];

            $book_all_occurrences = isset($booking_options['bookings_all_occurrences']) ? (int) $booking_options['bookings_all_occurrences'] : 0;

            if (!$book_all_occurrences) $date_query = " AND `timestamp`=" . $timestamp;
            else $date_query = " AND `timestamp`<=" . $timestamp;
        }
        else $date_query = "";

        if ($user_id) $user_query = " AND `user_id`=" . $user_id;
        else $user_query = "";

        if (is_numeric($limit) and $limit > 0) $limit_query = " LIMIT " . $limit;
        else $limit_query = "";

        if ($verified) $status_query = " AND `status` IN ('publish', 'future') AND `confirmed`='1' AND `verified`='1'";
        else $status_query = "";

        // Database
        $db = $this->getDB();

        $records = $db->select("SELECT `id`,`booking_id`,`timestamp` FROM `#__mec_bookings` WHERE `event_id`=" . $event_id . $status_query . $date_query . $user_query . $limit_query);

        $results = [];
        foreach ($records as $record)
        {
            $post = get_post($record->booking_id);
            $post->mec_timestamp = $record->timestamp;
            $post->mec_booking_record_id = $record->id;

            $results[] = $post;
        }

        return $results;
    }

    public function get_bookings_for_occurrence($timestamps, $args = [])
    {
        $limit = (isset($args['limit']) and is_numeric($args['limit'])) ? $args['limit'] : -1;
        $status = (isset($args['status']) and is_array($args['status'])) ? $args['status'] : [];
        $confirmed = (isset($args['confirmed']) and is_numeric($args['confirmed'])) ? $args['confirmed'] : null;
        $verified = (isset($args['verified']) and is_numeric($args['verified'])) ? $args['verified'] : null;
        $event_id = (isset($args['event_id']) and is_numeric($args['event_id'])) ? $args['event_id'] : null;

        $start = $timestamps[0];
        $end = $timestamps[1] ?? null;

        // Database
        $db = $this->getDB();

        // Query
        $query = "SELECT `id`,`booking_id`,`timestamp` FROM `#__mec_bookings` WHERE 1";

        // Confirmation
        if (!is_null($confirmed))
        {
            $query .= " AND `confirmed`='" . esc_sql($confirmed) . "'";
        }

        // Verification
        if (!is_null($verified))
        {
            $query .= " AND `verified`='" . esc_sql($verified) . "'";
        }

        // Status
        if (count($status))
        {
            $status_str = '';
            foreach ($status as $s) $status_str .= "'" . $s . "', ";

            $query .= " AND `status` IN (" . trim($status_str, ', ') . ")";
        }

        // Event ID
        if ($event_id)
        {
            $query .= " AND `event_id`=" . esc_sql($event_id);
        }

        // Times
        if ($start and $end)
        {
            $query .= " AND `timestamp`>='" . esc_sql($start) . "' AND `timestamp`<'" . esc_sql($end) . "'";
        }
        else $query .= " AND `timestamp`='" . esc_sql($start) . "'";

        // Order
        $query .= " ORDER BY `id` ASC";

        // Limit
        if ($limit > 0) $query .= " LIMIT " . $limit;

        $records = $db->select($query);

        $results = [];
        foreach ($records as $record)
        {
            $post = get_post($record->booking_id);
            $post->mec_timestamp = $record->timestamp;

            $results[] = $post;
        }

        return $results;
    }

    public function get_bookings_by_event_occurrence($event_id, $occurrence)
    {
        return $this->get_bookings_for_occurrence([
            $occurrence,
        ], [
            'event_id' => $event_id,
            'limit' => -1,
        ]);
    }

    public function get_total_attendees_by_event_occurrence($event_id, $occurrence)
    {
        $bookings = $this->get_bookings_by_event_occurrence($event_id, $occurrence);

        $total = 0;
        if (count($bookings))
        {
            // Booking Library
            $book = $this->getBook();

            // Determine Total Attendees
            foreach ($bookings as $booking) $total += $book->get_total_attendees($booking->ID);
        }

        return $total;
    }

    /**
     * Check whether to show event note or not
     * @param string $status
     * @return boolean
     */
    public function is_note_visible($status)
    {
        // MEC Settings
        $settings = $this->get_settings();

        // FES Note is not enabled
        if (!isset($settings['fes_note']) || !$settings['fes_note']) return false;

        // Return visibility status by post status and visibility method
        return (isset($settings['fes_note_visibility']) ? ($settings['fes_note_visibility'] == 'always' ? true : $status != 'publish') : true);
    }

    /**
     * Get Next event based on datetime of current event
     * @param array $atts
     * @return object
     */
    public function get_next_event($atts = [])
    {
        MEC::import('app.skins.list');

        // Get list skin
        $list = new MEC_skin_list();

        // Initialize the skin
        $list->initialize($atts);

        // Fetch the events
        $list->fetch();

        $events = $list->events;
        $key = key($events);

        return $events[$key][0] ?? (new stdClass());
    }

    /**
     * For getting event end date based on occurrence date
     * @param int $event_id
     * @param string $occurrence
     * @return string
     */
    public function get_end_date_by_occurrence($event_id, $occurrence)
    {
        $event_date = get_post_meta($event_id, 'mec_date', true);

        $start_date = $event_date['start'] ?? [];
        $end_date = $event_date['end'] ?? [];

        $event_period = $this->date_diff($start_date['date'], $end_date['date']);
        $event_period_days = $event_period ? $event_period->days : 0;

        // Single Day Event
        if (!$event_period_days) return $occurrence;

        return date('Y-m-d', strtotime('+' . $event_period_days . ' days', strtotime($occurrence)));
    }

    /**
     * Add MEC Event CPT to Tags Archive Page
     * @param object $query
     */
    public function add_events_to_tags_archive($query)
    {
        if ($query->is_tag() and $query->is_main_query() and !is_admin())
        {
            $pt = $this->get_main_post_type();
            $query->set('post_type', ['post', $pt]);
        }
    }

    /**
     * Get Post ID by meta value and meta key
     * @param string $meta_key
     * @param string $meta_value
     * @return string
     */
    public function get_post_id_by_meta($meta_key, $meta_value)
    {
        $db = $this->getDB();
        return $db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_value`='$meta_value' AND `meta_key`='$meta_key'", 'loadResult');
    }

    /**
     * Set Featured Image for a Post
     * @param string $image_url
     * @param int $post_id
     * @param array $allowed_extensions
     * @return bool|int
     */
    public function set_featured_image($image_url, $post_id, $allowed_extensions = [])
    {
        $attach_id = $this->get_attach_id($image_url);
        if (!$attach_id)
        {
            $upload_dir = wp_upload_dir();
            $filename = basename($image_url);

            $ex = explode('.', $filename);
            $extension = end($ex);

            // Invalid Extension
            if (count($allowed_extensions) && !in_array($extension, $allowed_extensions)) return false;

            $validate = wp_check_filetype($filename);
            if ($validate['type'] === false) return false;

            if (wp_mkdir_p($upload_dir['path'])) $file = $upload_dir['path'] . '/' . $filename;
            else $file = $upload_dir['basedir'] . '/' . $filename;

            if (!file_exists($file))
            {
                $image_data = $this->get_web_page($image_url);
                file_put_contents($file, $image_data);
            }

            $wp_filetype = wp_check_filetype($filename, null);
            $attachment = [
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit',
            ];

            $attach_id = wp_insert_attachment($attachment, $file, $post_id);
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $attach_data = wp_generate_attachment_metadata($attach_id, $file);
            wp_update_attachment_metadata($attach_id, $attach_data);
        }

        return set_post_thumbnail($post_id, $attach_id);
    }

    /**
     * Get Attachment ID by Image URL
     * @param string $image_url
     * @return int
     */
    public function get_attach_id($image_url)
    {
        $db = $this->getDB();
        return $db->select("SELECT `ID` FROM `#__posts` WHERE `guid`='$image_url'", 'loadResult');
    }

    /**
     * Get Image Type by Buffer. Used in Facebook Importer
     * @param string $buffer
     * @return string
     */
    public function get_image_type_by_buffer($buffer)
    {
        $types = ['jpeg' => "\xFF\xD8\xFF", 'gif' => 'GIF', 'png' => "\x89\x50\x4e\x47\x0d\x0a", 'bmp' => 'BM', 'psd' => '8BPS', 'swf' => 'FWS'];
        $found = 'other';

        foreach ($types as $type => $header)
        {
            if (strpos($buffer, $header) === 0)
            {
                $found = $type;
                break;
            }
        }

        return $found;
    }

    /**
     * Load Google Maps assets
     * @return bool
     * @var $define_settings
     * @var boolean $force
     */
    public function load_map_assets($force = false, $define_settings = null)
    {
        if (!$this->getPRO()) return false;

        // MEC Settings
        $settings = $this->get_settings();

        $assets = ['js' => [], 'css' => []];

        $local = $this->get_current_language();
        $ex = explode('_', $local);

        $language = ((isset($ex[0]) and trim($ex[0])) ? $ex[0] : 'en');
        $region = ((isset($ex[1]) and trim($ex[1])) ? $ex[1] : 'US');

        $gm_include = apply_filters('mec_gm_include', true);
        if ($gm_include or $force) $assets['js']['googlemap'] = '//maps.googleapis.com/maps/api/js?libraries=places' . ((isset($settings['google_maps_api_key']) and trim($settings['google_maps_api_key']) != '') ? '&key=' . $settings['google_maps_api_key'] : '') . '&language=' . $language . '&region=' . $region;

        $assets['js']['mec-richmarker-script'] = $this->asset('packages/richmarker/richmarker.min.js'); // Google Maps Rich Marker
        $assets['js']['mec-clustering-script'] = $this->asset('packages/clusterer/markerclusterer.min.js'); // Google Maps Clustering
        $assets['js']['mec-googlemap-script'] = $this->asset('js/googlemap.js'); // Google Maps Javascript API

        // Apply Filters
        $assets = apply_filters('mec_map_assets_include', $assets, $this, $define_settings);

        // Apply Filters Customize
        $assets = apply_filters('mec_map_customize_assets_include', $assets, $this, $define_settings);

        if (isset($assets['js']) && is_array($assets['js']) && count($assets['js']) > 0) foreach ($assets['js'] as $key => $link) wp_enqueue_script($key, $link, ['jquery'], $this->get_version());
        if (isset($assets['css']) && is_array($assets['css']) && count($assets['css']) > 0) foreach ($assets['css'] as $key => $link) wp_enqueue_style($key, $link, [], $this->get_version());
    }

    /**
     * Load Owl Carousel assets
     */
    public function load_owl_assets()
    {
        // Include MEC frontend CSS files
        wp_enqueue_style('mec-owl-carousel-style');
        wp_enqueue_style('mec-owl-carousel-theme-style', $this->asset('packages/owl-carousel/owl.theme.min.css'));
    }

    /**
     * Load Isotope assets
     */
    public function load_isotope_assets()
    {
        // Isotope JS file
        wp_enqueue_script('mec-isotope-script', $this->asset('js/isotope.pkgd.min.js'), [], $this->get_version(), true);
        wp_enqueue_script('mec-imagesload-script', $this->asset('js/imagesload.js'), [], $this->get_version(), true);
    }

    /**
     * Load Time Picker assets
     */
    public function load_time_picker_assets()
    {
        // Include CSS
        wp_enqueue_style('mec-time-picker', $this->asset('packages/timepicker/jquery.timepicker.min.css'));

        // Include JS
        wp_enqueue_script('mec-time-picker', $this->asset('packages/timepicker/jquery.timepicker.min.js'));
    }

    /**
     * Load Month Picker assets
     */
    public function load_month_picker_assets()
    {
        // IncludeS files
        wp_enqueue_style('mec-month-picker-style', $this->asset('packages/month-picker/MonthPicker.css'));

        $dates = [];
        $d = [
            'days' => ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
            'months' => ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
        ];

        foreach ($d as $type => $values)
        {
            foreach ($values as $k => $value)
            {

                switch ($type)
                {
                    case 'days':
                        $day_min = date_i18n('D', strtotime($value));

                        $dates['days'][$k] = date_i18n('l', strtotime($value));
                        $dates['daysShort'][$k] = $day_min;
                        $dates['daysMin'][$k] = $day_min;
                        break;
                    case 'months':
                        $dates['months'][$k] = date_i18n('F', strtotime($value));
                        $dates['monthsShort'][$k] = date_i18n('M', strtotime($value));
                        break;
                }
            }
        }

        $data = [
            'dates' => $dates,
        ];
        echo '<script>var MEC_Month_Picker_Data = ' . json_encode($data, JSON_UNESCAPED_UNICODE) . ' </script>';
        wp_enqueue_script('mec-month-picker-js', $this->asset('packages/month-picker/MonthPicker.js'));
    }

    function get_client_ip()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) $ipaddress = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $ipaddress = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        else if (isset($_SERVER['HTTP_X_FORWARDED'])) $ipaddress = sanitize_text_field($_SERVER['HTTP_X_FORWARDED']);
        else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) $ipaddress = sanitize_text_field($_SERVER['HTTP_FORWARDED_FOR']);
        else if (isset($_SERVER['HTTP_FORWARDED'])) $ipaddress = sanitize_text_field($_SERVER['HTTP_FORWARDED']);
        else if (isset($_SERVER['REMOTE_ADDR'])) $ipaddress = sanitize_text_field($_SERVER['REMOTE_ADDR']);
        else $ipaddress = 'UNKNOWN';

        $ips = explode(',', $ipaddress);
        if (count($ips) > 1) $ipaddress = $ips[0];

        return $ipaddress;
    }

    public function get_timezone_by_ip()
    {
        // Client IP
        $ip = $this->get_client_ip();

        $cache_key = 'mec_visitor_timezone_' . $ip;
        $cache = $this->getCache();

        // Get From Cache
        if ($cache->has($cache_key)) return $cache->get($cache_key);

        // First Provider
        $JSON = $this->get_web_page('http://ip-api.com/json/' . $ip, 3);
        $data = json_decode($JSON, true);

        // Second Provider
        if (!trim($JSON) or (is_array($data) and !isset($data['timezone'])))
        {
            $JSON = $this->get_web_page('https://ipapi.co/' . $ip . '/json/', 3);
            $data = json_decode($JSON, true);
        }

        // Second provider returns X instead of false in case of error!
        $timezone = (isset($data['timezone']) and strtolower($data['timezone']) != 'x') ? $data['timezone'] : false;

        // Add to Cache
        $cache->set($cache_key, $timezone);

        return $timezone;
    }

    public function is_ajax()
    {
        return (defined('DOING_AJAX') && DOING_AJAX);
    }

    public function load_sed_assets($settings = [])
    {
        if (!is_array($settings) || !count($settings)) $settings = $this->get_settings();

        // Load Map assets
        if (isset($settings['google_maps_status']) and $settings['google_maps_status']) $this->load_map_assets();

        // Include FlipCount library
        wp_enqueue_script('mec-flipcount-script');
    }

    public function is_sold($event, $date = '')
    {
        if (is_object($event))
        {
            $event_id = $event->data->ID;
            $tickets = (isset($event->data->tickets) and is_array($event->data->tickets)) ? $event->data->tickets : [];

            $timestamp = (trim($date) ? $date : ((isset($event->date['start']) and isset($event->date['start']['timestamp'])) ? $event->date['start']['timestamp'] : 0));
        }
        else
        {
            $event_id = $event;
            $tickets = get_post_meta($event_id, 'mec_tickets', true);
            if (!is_array($tickets)) $tickets = [];

            $timestamp = is_numeric($date) ? $date : (int) strtotime($date);
        }

        // No Tickets
        if (!count($tickets) or !$timestamp) return false;

        // MEC Cache
        $cache = $this->getCache();

        return $cache->rememberOnce($event_id . ':' . $timestamp, function () use ($event_id, $timestamp)
        {
            $book = $this->getBook();
            $availability = $book->get_tickets_availability($event_id, $timestamp);

            $sold = false;
            if (is_array($availability) and count($availability))
            {
                $remained_tickets = 0;
                foreach ($availability as $ticket_id => $remained)
                {
                    if (is_numeric($ticket_id) and $remained >= 0) $remained_tickets += $remained;
                    if (is_numeric($ticket_id) and $remained == -1)
                    {
                        $remained_tickets = -1;
                        break;
                    }
                }

                // Soldout
                if ($remained_tickets === 0) $sold = true;
            }

            return $sold;
        });
    }

    public function get_date_periods($date_start, $date_end, $type = 'daily')
    {
        $periods = [];

        $time_start = strtotime($date_start);
        $time_end = strtotime($date_end);

        if ($type == 'daily')
        {
            while ($time_start < $time_end)
            {
                $periods[] = ['start' => date("Y-m-d H:i:s", $time_start), 'end' => date("Y-m-d H:i:s", ($time_start + 86399)), 'label' => date("Y-m-d", $time_start)];
                $time_start += 86400;
            }
        }
        else if ($type == 'weekly')
        {
        }
        else if ($type == 'monthly')
        {
            $start_year = date('Y', $time_start);
            $start_month = date('m', $time_start);
            $start_id = (int) $start_year . $start_month;

            $end_year = date('Y', $time_end);
            $end_month = date('m', $time_end);
            $end_id = (int) $end_year . $end_month;

            while ($start_id <= $end_id)
            {
                $periods[] = ['start' => $start_year . "-" . $start_month . "-01 00:00:00", 'end' => $start_year . "-" . $start_month . "-" . date('t', strtotime($start_year . "-" . $start_month . "-01 00:00:00")) . " 23:59:59", 'label' => date('Y F', strtotime($start_year . "-" . $start_month . "-01 00:00:00"))];

                if ($start_month == '12')
                {
                    $start_month = '01';
                    $start_year++;
                }
                else
                {
                    $start_month = (int) $start_month + 1;
                    if (strlen($start_month) == 1) $start_month = '0' . $start_month;
                }

                $start_id = (int) $start_year . $start_month;
            }
        }
        else if ($type == 'yearly')
        {
            $start_year = date('Y', $time_start);
            $end_year = date('Y', $time_end);

            while ($start_year <= $end_year)
            {
                $periods[] = ['start' => $start_year . "-01-01 00:00:00", 'end' => $start_year . "-12-31 23:59:59", 'label' => $start_year];
                $start_year++;
            }
        }

        return $periods;
    }

    public function get_messages()
    {
        if ($this->getPRO())
        {
            $messages = [
                'taxonomies' => [
                    'category' => ['name' => __('Taxonomies', 'modern-events-calendar-lite')],
                    'messages' => [
                        'taxonomy_categories' => ['label' => __('Category Plural Label', 'modern-events-calendar-lite'), 'default' => __('Categories', 'modern-events-calendar-lite')],
                        'taxonomy_category' => ['label' => __('Category Singular Label', 'modern-events-calendar-lite'), 'default' => __('Category', 'modern-events-calendar-lite')],
                        'taxonomy_labels' => ['label' => __('Label Plural Label', 'modern-events-calendar-lite'), 'default' => __('Labels', 'modern-events-calendar-lite')],
                        'taxonomy_label' => ['label' => __('Label Singular Label', 'modern-events-calendar-lite'), 'default' => __('label', 'modern-events-calendar-lite')],
                        'taxonomy_locations' => ['label' => __('Location Plural Label', 'modern-events-calendar-lite'), 'default' => __('Locations', 'modern-events-calendar-lite')],
                        'taxonomy_location' => ['label' => __('Location Singular Label', 'modern-events-calendar-lite'), 'default' => __('Location', 'modern-events-calendar-lite')],
                        'taxonomy_organizers' => ['label' => __('Organizer Plural Label', 'modern-events-calendar-lite'), 'default' => __('Organizers', 'modern-events-calendar-lite')],
                        'taxonomy_organizer' => ['label' => __('Organizer Singular Label', 'modern-events-calendar-lite'), 'default' => __('Organizer', 'modern-events-calendar-lite')],
                        'taxonomy_speakers' => ['label' => __('Speaker Plural Label', 'modern-events-calendar-lite'), 'default' => __('Speakers', 'modern-events-calendar-lite')],
                        'taxonomy_speaker' => ['label' => __('Speaker Singular Label', 'modern-events-calendar-lite'), 'default' => __('Speaker', 'modern-events-calendar-lite')],
                        'taxonomy_sponsors' => ['label' => __('Sponsor Plural Label', 'modern-events-calendar-lite'), 'default' => __('Sponsors', 'modern-events-calendar-lite')],
                        'taxonomy_sponsor' => ['label' => __('Sponsor Singular Label', 'modern-events-calendar-lite'), 'default' => __('Sponsor', 'modern-events-calendar-lite')],
                    ],
                ],
                'weekdays' => [
                    'category' => ['name' => __('Weekdays', 'modern-events-calendar-lite')],
                    'messages' => [
                        'weekdays_su' => ['label' => __('Sunday abbreviation', 'modern-events-calendar-lite'), 'default' => __('SU', 'modern-events-calendar-lite')],
                        'weekdays_mo' => ['label' => __('Monday abbreviation', 'modern-events-calendar-lite'), 'default' => __('MO', 'modern-events-calendar-lite')],
                        'weekdays_tu' => ['label' => __('Tuesday abbreviation', 'modern-events-calendar-lite'), 'default' => __('TU', 'modern-events-calendar-lite')],
                        'weekdays_we' => ['label' => __('Wednesday abbreviation', 'modern-events-calendar-lite'), 'default' => __('WE', 'modern-events-calendar-lite')],
                        'weekdays_th' => ['label' => __('Thursday abbreviation', 'modern-events-calendar-lite'), 'default' => __('TH', 'modern-events-calendar-lite')],
                        'weekdays_fr' => ['label' => __('Friday abbreviation', 'modern-events-calendar-lite'), 'default' => __('FR', 'modern-events-calendar-lite')],
                        'weekdays_sa' => ['label' => __('Saturday abbreviation', 'modern-events-calendar-lite'), 'default' => __('SA', 'modern-events-calendar-lite')],
                    ],
                ],
                'booking' => [
                    'category' => ['name' => __('Booking', 'modern-events-calendar-lite')],
                    'messages' => [
                        'booking' => ['label' => __('Booking (Singular)', 'modern-events-calendar-lite'), 'default' => __('Booking', 'modern-events-calendar-lite')],
                        'bookings' => ['label' => __('Bookings (Plural)', 'modern-events-calendar-lite'), 'default' => __('Bookings', 'modern-events-calendar-lite')],
                        'book_success_message' => ['label' => __('Booking Success Message', 'modern-events-calendar-lite'), 'default' => __('Thank you for booking. Your tickets are booked, booking verification might be needed, please check your email.', 'modern-events-calendar-lite')],
                        'booking_restriction_message1' => ['label' => __('Booking Restriction Message 1', 'modern-events-calendar-lite'), 'default' => __('You selected %s tickets to book but maximum number of tikets per user is %s tickets.', 'modern-events-calendar-lite')],
                        'booking_restriction_message2' => ['label' => __('Booking Restriction Message 2', 'modern-events-calendar-lite'), 'default' => __('You have already booked %s tickets and the maximum number of tickets per user is %s.', 'modern-events-calendar-lite')],
                        'booking_restriction_message3' => ['label' => __('Booking IP Restriction Message', 'modern-events-calendar-lite'), 'default' => __('Maximum allowed number of tickets that you can book is %s.', 'modern-events-calendar-lite')],
                        'booking_button' => ['label' => __('Booking Button', 'modern-events-calendar-lite'), 'default' => __('Book Now', 'modern-events-calendar-lite')],
                        'ticket' => ['label' => __('Ticket (Singular)', 'modern-events-calendar-lite'), 'default' => __('Ticket', 'modern-events-calendar-lite')],
                        'tickets' => ['label' => __('Tickets (Plural)', 'modern-events-calendar-lite'), 'default' => __('Tickets', 'modern-events-calendar-lite')],
                    ],
                ],
                'others' => [
                    'category' => ['name' => __('Others', 'modern-events-calendar-lite')],
                    'messages' => [
                        'register_button' => ['label' => __('Register Button', 'modern-events-calendar-lite'), 'default' => __('REGISTER', 'modern-events-calendar-lite')],
                        'view_detail' => ['label' => __('View Detail Button', 'modern-events-calendar-lite'), 'default' => __('View Detail', 'modern-events-calendar-lite')],
                        'event_detail' => ['label' => __('Event Detail Button', 'modern-events-calendar-lite'), 'default' => __('Event Detail', 'modern-events-calendar-lite')],
                        'read_more_link' => ['label' => __('Event Link', 'modern-events-calendar-lite'), 'default' => __('Event Link', 'modern-events-calendar-lite')],
                        'more_info_link' => ['label' => __('More Info Link', 'modern-events-calendar-lite'), 'default' => __('More Info', 'modern-events-calendar-lite')],
                        'event_cost' => ['label' => __('Event Cost', 'modern-events-calendar-lite'), 'default' => __('Event Cost', 'modern-events-calendar-lite')],
                        'cost' => ['label' => __('Cost', 'modern-events-calendar-lite'), 'default' => __('Cost', 'modern-events-calendar-lite')],
                        'other_organizers' => ['label' => __('Other Organizers', 'modern-events-calendar-lite'), 'default' => __('Other Organizers', 'modern-events-calendar-lite')],
                        'other_locations' => ['label' => __('Other Locations', 'modern-events-calendar-lite'), 'default' => __('Other Locations', 'modern-events-calendar-lite')],
                        'all_day' => ['label' => __('All Day', 'modern-events-calendar-lite'), 'default' => __('All Day', 'modern-events-calendar-lite')],
                        'expired' => ['label' => __('Expired', 'modern-events-calendar-lite'), 'default' => __('Expired', 'modern-events-calendar-lite')],
                        'ongoing' => ['label' => __('Ongoing', 'modern-events-calendar-lite'), 'default' => __('Ongoing', 'modern-events-calendar-lite')],
                    ],
                ],
            ];
        }
        else
        {
            $messages = [
                'taxonomies' => [
                    'category' => ['name' => __('Taxonomies', 'modern-events-calendar-lite')],
                    'messages' => [
                        'taxonomy_categories' => ['label' => __('Category Plural Label', 'modern-events-calendar-lite'), 'default' => __('Categories', 'modern-events-calendar-lite')],
                        'taxonomy_category' => ['label' => __('Category Singular Label', 'modern-events-calendar-lite'), 'default' => __('Category', 'modern-events-calendar-lite')],
                        'taxonomy_labels' => ['label' => __('Label Plural Label', 'modern-events-calendar-lite'), 'default' => __('Labels', 'modern-events-calendar-lite')],
                        'taxonomy_label' => ['label' => __('Label Singular Label', 'modern-events-calendar-lite'), 'default' => __('label', 'modern-events-calendar-lite')],
                        'taxonomy_locations' => ['label' => __('Location Plural Label', 'modern-events-calendar-lite'), 'default' => __('Locations', 'modern-events-calendar-lite')],
                        'taxonomy_location' => ['label' => __('Location Singular Label', 'modern-events-calendar-lite'), 'default' => __('Location', 'modern-events-calendar-lite')],
                        'taxonomy_organizers' => ['label' => __('Organizer Plural Label', 'modern-events-calendar-lite'), 'default' => __('Organizers', 'modern-events-calendar-lite')],
                        'taxonomy_organizer' => ['label' => __('Organizer Singular Label', 'modern-events-calendar-lite'), 'default' => __('Organizer', 'modern-events-calendar-lite')],
                        'taxonomy_speakers' => ['label' => __('Speaker Plural Label', 'modern-events-calendar-lite'), 'default' => __('Speakers', 'modern-events-calendar-lite')],
                        'taxonomy_speaker' => ['label' => __('Speaker Singular Label', 'modern-events-calendar-lite'), 'default' => __('Speaker', 'modern-events-calendar-lite')],
                    ],
                ],
                'weekdays' => [
                    'category' => ['name' => __('Weekdays', 'modern-events-calendar-lite')],
                    'messages' => [
                        'weekdays_su' => ['label' => __('Sunday abbreviation', 'modern-events-calendar-lite'), 'default' => __('SU', 'modern-events-calendar-lite')],
                        'weekdays_mo' => ['label' => __('Monday abbreviation', 'modern-events-calendar-lite'), 'default' => __('MO', 'modern-events-calendar-lite')],
                        'weekdays_tu' => ['label' => __('Tuesday abbreviation', 'modern-events-calendar-lite'), 'default' => __('TU', 'modern-events-calendar-lite')],
                        'weekdays_we' => ['label' => __('Wednesday abbreviation', 'modern-events-calendar-lite'), 'default' => __('WE', 'modern-events-calendar-lite')],
                        'weekdays_th' => ['label' => __('Thursday abbreviation', 'modern-events-calendar-lite'), 'default' => __('TH', 'modern-events-calendar-lite')],
                        'weekdays_fr' => ['label' => __('Friday abbreviation', 'modern-events-calendar-lite'), 'default' => __('FR', 'modern-events-calendar-lite')],
                        'weekdays_sa' => ['label' => __('Saturday abbreviation', 'modern-events-calendar-lite'), 'default' => __('SA', 'modern-events-calendar-lite')],
                    ],
                ],
                'others' => [
                    'category' => ['name' => __('Others', 'modern-events-calendar-lite')],
                    'messages' => [
                        'register_button' => ['label' => __('Register Button', 'modern-events-calendar-lite'), 'default' => __('REGISTER', 'modern-events-calendar-lite')],
                        'view_detail' => ['label' => __('View Detail Button', 'modern-events-calendar-lite'), 'default' => __('View Detail', 'modern-events-calendar-lite')],
                        'event_detail' => ['label' => __('Event Detail Button', 'modern-events-calendar-lite'), 'default' => __('Event Detail', 'modern-events-calendar-lite')],
                        'read_more_link' => ['label' => __('Event Link', 'modern-events-calendar-lite'), 'default' => __('Event Link', 'modern-events-calendar-lite')],
                        'more_info_link' => ['label' => __('More Info Link', 'modern-events-calendar-lite'), 'default' => __('More Info', 'modern-events-calendar-lite')],
                        'event_cost' => ['label' => __('Event Cost', 'modern-events-calendar-lite'), 'default' => __('Event Cost', 'modern-events-calendar-lite')],
                        'cost' => ['label' => __('Cost', 'modern-events-calendar-lite'), 'default' => __('Cost', 'modern-events-calendar-lite')],
                        'other_organizers' => ['label' => __('Other Organizers', 'modern-events-calendar-lite'), 'default' => __('Other Organizers', 'modern-events-calendar-lite')],
                        'other_locations' => ['label' => __('Other Locations', 'modern-events-calendar-lite'), 'default' => __('Other Locations', 'modern-events-calendar-lite')],
                        'all_day' => ['label' => __('All Day', 'modern-events-calendar-lite'), 'default' => __('All Day', 'modern-events-calendar-lite')],
                        'expired' => ['label' => __('Expired', 'modern-events-calendar-lite'), 'default' => __('Expired', 'modern-events-calendar-lite')],
                        'ongoing' => ['label' => __('Ongoing', 'modern-events-calendar-lite'), 'default' => __('Ongoing', 'modern-events-calendar-lite')],
                    ],
                ],
            ];
        }

        return apply_filters('mec_messages', $messages);
    }

    /**
     * For showing dynamic messages based on their default value and the inserted value in backend (if any)
     * @param $message_key string
     * @param $default string
     * @return string
     */
    public function m($message_key, $default)
    {
        $message_values = $this->get_messages_options();

        // Message is not set from backend
        if (!isset($message_values[$message_key]) or (!trim($message_values[$message_key]))) return $default;

        // Return the dynamic message inserted in backend
        return stripslashes($message_values[$message_key]);
    }

    /**
     * Get Weather from the data provider
     * @param $apikey
     * @param $lat
     * @param $lng
     * @param $datetime
     * @return bool|array
     */
    public function get_weather_darksky($apikey, $lat, $lng, $datetime)
    {
        $locale = substr(get_locale(), 0, 2);

        // Set the language to English if it's not included in available languages
        if (!in_array($locale, [
            'ar',
            'az',
            'be',
            'bg',
            'bs',
            'ca',
            'cs',
            'da',
            'de',
            'el',
            'en',
            'es',
            'et',
            'fi',
            'fr',
            'hr',
            'hu',
            'id',
            'is',
            'it',
            'ja',
            'ka',
            'ko',
            'kw',
            'nb',
            'nl',
            'no',
            'pl',
            'pt',
            'ro',
            'ru',
            'sk',
            'sl',
            'sr',
            'sv',
            'tet',
            'tr',
            'uk',
            'x-pig-latin',
            'zh',
            'zh-tw',
        ])) $locale = 'en';

        // Dark Sky Provider
        $JSON = $this->get_web_page('https://api.darksky.net/forecast/' . $apikey . '/' . $lat . ',' . $lng . ',' . strtotime($datetime) . '?exclude=minutely,hourly,daily,alerts&units=ca&lang=' . $locale);
        $data = json_decode($JSON, true);

        return $data['currently'] ?? false;
    }

    /**
     * Get Weather from the data provider
     * @param $apikey
     * @param $lat
     * @param $lng
     * @param $datetime
     * @return bool|array
     */
    public function get_weather_visualcrossing($apikey, $lat, $lng, $datetime)
    {
        $locale = substr(get_locale(), 0, 2);

        // Set the language to English if it's not included in available languages
        if (!in_array($locale, [
            'de',
            'en',
            'es',
            'fi',
            'fr',
            'it',
            'ja',
            'ko',
            'pt',
            'ru',
            'zn',
        ])) $locale = 'en';

        // Visual Crossing Provider
        $JSON = $this->get_web_page('https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/' . $lat . ',' . $lng . '/' . date('Y-m-d\TH:i:s', strtotime($datetime)) . '?key=' . $apikey . '&include=current&unitGroup=metric&lang=' . $locale);
        $data = json_decode($JSON, true);

        return $data['currentConditions'] ?? false;
    }

    /**
     * Get Weather from the data provider
     * @param $apikey
     * @param $lat
     * @param $lng
     * @param $datetime
     * @return bool|array
     */
    public function get_weather_wa($apikey, $lat, $lng, $datetime)
    {
        $locale = substr(get_locale(), 0, 2);

        // Set the language to English if it's not included in available languages
        if (!in_array($locale, [
            'ar',
            'bn',
            'bg',
            'zh',
            'zh_tw',
            'cs',
            'da',
            'nl',
            'fi',
            'fr',
            'de',
            'el',
            'hi',
            'hu',
            'it',
            'ja',
            'jv',
            'ko',
            'zh_cmn',
            'mr',
            'pl',
            'pt',
            'pa',
            'ro',
            'ru',
            'si',
            'si',
            'sk',
            'es',
            'sv',
            'ta',
            'te',
            'tr',
            'uk',
            'ur',
            'vi',
            'zh_wuu',
            'zh_hsn',
            'zh_yue',
            'zu',
        ])) $locale = 'en';

        // Dark Sky Provider
        $JSON = $this->get_web_page('https://api.weatherapi.com/v1/current.json?key=' . $apikey . '&q=' . $lat . ',' . $lng . '&lang=' . $locale);
        $data = json_decode($JSON, true);

        return $data['current'] ?? false;
    }

    /**
     * Convert weather unit
     * @param $value
     * @param $mode
     * @return false|float
     * @author Webnus <info@webnus.net>
     */
    function weather_unit_convert($value, $mode)
    {
        if (func_num_args() < 2) return false;
        $mode = strtoupper($mode);

        if ($mode == 'F_TO_C') return round(((floatval($value) - 32) * 5 / 9));
        else if ($mode == 'C_TO_F') return round(((1.8 * floatval($value)) + 32));
        else if ($mode == 'M_TO_KM') return round(1.609344 * floatval($value));
        else if ($mode == 'KM_TO_M') return round(0.6214 * floatval($value));
        return false;
    }

    /**
     * Get Integrated plugins to import events
     * @return array
     */
    public function get_integrated_plugins_for_import()
    {
        return [
            'eventon' => esc_html__('EventON', 'modern-events-calendar-lite'),
            'the-events-calendar' => esc_html__('The Events Calendar', 'modern-events-calendar-lite'),
            'weekly-class' => esc_html__('Events Schedule WP Plugin', 'modern-events-calendar-lite'),
            'calendarize-it' => esc_html__('Calendarize It', 'modern-events-calendar-lite'),
            'event-espresso' => esc_html__('Event Espresso', 'modern-events-calendar-lite'),
            'events-manager-recurring' => esc_html__('Events Manager (Recurring)', 'modern-events-calendar-lite'),
            'events-manager-single' => esc_html__('Events Manager (Single)', 'modern-events-calendar-lite'),
            'wp-event-manager' => esc_html__('WP Event Manager', 'modern-events-calendar-lite'),
        ];
    }

    public function get_original_event($event_id)
    {
        // If WPML Plugin is installed and activated
        if (class_exists('SitePress'))
        {
            $trid = apply_filters('wpml_element_trid', null, $event_id, 'post_mec-events');
            $translations = apply_filters('wpml_get_element_translations', null, $trid, 'post_mec-events');

            if (!is_array($translations) or !count($translations)) return $event_id;

            $original_id = $event_id;
            foreach ($translations as $translation)
            {
                if (isset($translation->original) and $translation->original)
                {
                    $original_id = $translation->element_id;
                    break;
                }
            }

            return $original_id;
        }
        // Poly Lang is installed and activated
        else if (function_exists('pll_default_language'))
        {
            $def = pll_default_language();

            $translations = pll_get_post_translations($event_id);
            if (!is_array($translations) or !count($translations)) return $event_id;

            if (isset($translations[$def]) and is_numeric($translations[$def])) return $translations[$def];
        }

        return $event_id;
    }

    public function is_multilingual()
    {
        $multilingual = false;

        // WPML
        if (class_exists('SitePress')) $multilingual = true;

        // Polylang
        if (function_exists('pll_default_language')) $multilingual = true;

        return $multilingual;
    }

    public function get_current_locale()
    {
        return get_locale();
    }

    public function get_current_lang_code()
    {
        // WPML
        if (class_exists('SitePress')) return $this->get_current_locale();
        // Polylang, etc.
        else
        {
            $ex = explode('_', $this->get_current_locale());
            return $ex[0];
        }
    }

    public function get_backend_active_locale()
    {
        // WPML
        if (class_exists('SitePress'))
        {
            $languages = apply_filters('wpml_active_languages', []);
            if (is_array($languages) and count($languages))
            {
                foreach ($languages as $language)
                {
                    if (isset($language['active']) and $language['active']) return $language['default_locale'];
                }
            }
        }

        // Polylang
        if (function_exists('pll_default_language'))
        {
            global $polylang;
            return $polylang->pref_lang->locale;
        }

        return $this->get_current_locale();
    }

    public function get_post_locale($post_id)
    {
        // WPML
        if (class_exists('SitePress'))
        {
            $lang = apply_filters('wpml_post_language_details', null, $post_id);
            return $lang['locale'] ?? '';
        }

        // Polylang
        if (function_exists('pll_get_post_language'))
        {
            return pll_get_post_language($post_id, 'locale');
        }

        return '';
    }

    /**
     * To check is a date is valid or not
     * @param string $date
     * @param string $format
     * @return bool
     */
    public function validate_date($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public function parse_ics($feed)
    {
        try
        {
            return new ICal($feed, [
                'defaultSpan' => 2,     // Default value
                'defaultTimeZone' => 'UTC',
                'defaultWeekStart' => 'MO',  // Default value
                'disableCharacterReplacement' => false, // Default value
                'skipRecurrence' => true, // Default value
                'useTimeZoneWithRRules' => false, // Default value
            ]);
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }

    public function get_pro_link()
    {
        $link = 'https://webnus.net/mec-purchase/?ref=17/';
        return apply_filters('MEC_upgrade_link', $link);
    }

    /**
     * Get Label for booking confirmation
     * @param int $confirmed
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_confirmation_label($confirmed = 1)
    {
        if ($confirmed == '1') $label = esc_html__('Confirmed', 'modern-events-calendar-lite');
        else if ($confirmed == '-1') $label = esc_html__('Rejected', 'modern-events-calendar-lite');
        else $label = esc_html__('Pending', 'modern-events-calendar-lite');

        return $label;
    }

    /**
     * Get Label for events status
     * @param string $label
     * @param boolean $return_class
     * @return string|array
     * @author Webnus <info@webnus.net>
     */
    public function get_event_label_status($label = 'empty', $return_class = true)
    {
        if (!trim($label)) $label = 'empty';
        switch ($label)
        {
            case 'publish':
                $label = esc_html__('Confirmed', 'modern-events-calendar-lite');
                $status_class = 'mec-book-confirmed';
                break;
            case 'pending':
                $label = esc_html__('Pending', 'modern-events-calendar-lite');
                $status_class = 'mec-book-pending';
                break;
            case 'trash':
                $label = esc_html__('Rejected', 'modern-events-calendar-lite');
                $status_class = 'mec-book-pending';
                break;
            default:
                $label = esc_html__(ucwords($label), 'modern-events-calendar-lite');
                $status_class = 'mec-book-other';
                break;
        }

        return !$return_class ? $label : ['label' => $label, 'status_class' => $status_class];
    }

    /**
     * Get Label for booking verification
     * @param int $verified
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_verification_label($verified = 1)
    {
        if ($verified == '1') $label = esc_html__('Verified', 'modern-events-calendar-lite');
        else if ($verified == '-1') $label = esc_html__('Canceled', 'modern-events-calendar-lite');
        else $label = esc_html__('Waiting', 'modern-events-calendar-lite');

        return $label;
    }

    /**
     * Added Block Editor Custome Category
     * @param array $categories
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function add_custom_block_cateogry($categories)
    {
        $categories = array_merge([['slug' => 'mec.block.category', 'title' => esc_html__('M.E. Calendar', 'modern-events-calendar-lite'), 'icon' => 'calendar-alt']], $categories);
        return $categories;
    }

    /**
     * Advanced Repeating MEC Active
     * @param array $days
     * @param string $item
     * @author Webnus <info@webnus.net>
     */
    public function mec_active($days = [], $item = '')
    {
        if (is_array($days) and in_array($item, $days)) echo 'mec-active';
    }

    /**
     * Advanced repeat sorting by start of week day number
     * @param int $start_of_week
     * @param $day
     * @return string|boolean
     * @author Webnus <info@webnus.net>
     */
    public function advanced_repeating_sort_day($start_of_week = 1, $day = 1)
    {
        if (func_num_args() < 2) return false;

        $start_of_week = intval($start_of_week);
        $day = intval($day) == 0 ? intval($day) : intval($day) - 1;

        // KEEP IT FOR TRANSLATORS
        [__('Sun', 'modern-events-calendar-lite'), esc_html__('Mon', 'modern-events-calendar-lite'), esc_html__('Tue', 'modern-events-calendar-lite'), esc_html__('Wed', 'modern-events-calendar-lite'), esc_html__('Thu', 'modern-events-calendar-lite'), esc_html__('Fri', 'modern-events-calendar-lite'), esc_html__('Sat', 'modern-events-calendar-lite')];

        // DO NOT MAKE THEM TRANSLATE-ABLE
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        $s1 = array_splice($days, $start_of_week, count($days));
        $s2 = array_splice($days, 0, $start_of_week);
        $merge = array_merge($s1, $s2);

        return $merge[$day];
    }

    public function get_ical_rrules($event, $only_rrule = false)
    {
        if (is_numeric($event))
        {
            $render = $this->getRender();
            $event = $render->data($event);
        }

        $recurrence = [];
        if (isset($event->mec->repeat) and $event->mec->repeat)
        {
            $repeat_options = (isset($event->meta) and isset($event->meta['mec_repeat']) and is_array($event->meta['mec_repeat'])) ? $event->meta['mec_repeat'] : [];

            $finish_time = $event->time['end'];
            $finish_time = str_replace(['h:', 'H:', 'H'], 'h', $finish_time);
            $finish_time = str_replace(['h ', 'h'], ':', $finish_time);

            $finish = '';
            if ($event->mec->end != '0000-00-00')
            {
                $time_format = 'Ymd\\THi00\\Z';
                $finish_datetime = strtotime($event->mec->end . ' ' . $finish_time);

                $gmt_offset_seconds = $this->get_gmt_offset_seconds($finish_datetime, $event);
                $finish = gmdate($time_format, ($finish_datetime - $gmt_offset_seconds));
            }

            $freq = '';
            $interval = '1';
            $bysetpos = '';
            $byday = '';
            $wkst = '';
            $count = '';

            $repeat_type = $event->meta['mec_repeat_type'];
            $week_day_mapping = ['1' => 'MO', '2' => 'TU', '3' => 'WE', '4' => 'TH', '5' => 'FR', '6' => 'SA', '7' => 'SU'];

            if ($repeat_type == 'daily')
            {
                $freq = 'DAILY';
                $interval = $event->mec->rinterval;
            }
            else if ($repeat_type == 'weekly')
            {
                $freq = 'WEEKLY';
                $interval = ($event->mec->rinterval / 7);
            }
            else if ($repeat_type == 'monthly')
            {
                $freq = 'MONTHLY';
                $interval = $event->mec->rinterval;
            }
            else if ($repeat_type == 'yearly') $freq = 'YEARLY';
            else if ($repeat_type == 'weekday')
            {
                $mec_weekdays = explode(',', trim($event->mec->weekdays, ','));
                foreach ($mec_weekdays as $mec_weekday) $byday .= $week_day_mapping[$mec_weekday] . ',';

                $byday = trim($byday, ', ');
                $freq = 'WEEKLY';
            }
            else if ($repeat_type == 'weekend')
            {
                $mec_weekdays = explode(',', trim($event->mec->weekdays, ','));
                foreach ($mec_weekdays as $mec_weekday) $byday .= $week_day_mapping[$mec_weekday] . ',';

                $byday = trim($byday, ', ');
                $freq = 'WEEKLY';
            }
            else if ($repeat_type == 'certain_weekdays')
            {
                $mec_weekdays = explode(',', trim($event->mec->weekdays, ','));
                foreach ($mec_weekdays as $mec_weekday) $byday .= $week_day_mapping[$mec_weekday] . ',';

                $byday = trim($byday, ', ');
                $freq = 'WEEKLY';
            }
            else if ($repeat_type == 'advanced')
            {
                $advanced_days = is_array($event->meta['mec_advanced_days']) ? $event->meta['mec_advanced_days'] : [];

                $first_rule = $advanced_days[0] ?? null;
                $ex = explode('.', $first_rule);

                $w = $ex[1] ?? null;
                if ($w === 'l') $w = -1;

                $byday_mapping = ['MON' => 'MO', 'TUE' => 'TU', 'WED' => 'WE', 'THU' => 'TH', 'FRI' => 'FR', 'SAT' => 'SA', 'SUN' => 'SU'];
                $byday = $w . $byday_mapping[strtoupper($ex[0])];

                $wkst_mapping = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];
                $wkst = $wkst_mapping[$this->get_first_day_of_week()];

                $freq = 'MONTHLY';
            }
            else if ($repeat_type == 'custom_days')
            {
                $freq = '';
                $mec_periods = explode(',', trim($event->mec->days, ','));

                // Add main occurrence
                if (isset($event->meta, $event->meta['mec_start_datetime'], $event->meta['mec_end_datetime']))
                {
                    $main_occ_start = strtotime($event->meta['mec_start_datetime']);
                    $main_occ_end = strtotime($event->meta['mec_end_datetime']);

                    array_unshift($mec_periods, date('Y-m-d', $main_occ_start) . ':' . date('Y-m-d', $main_occ_end) . ':' . date('h-i-A', $main_occ_start) . ':' . date('h-i-A', $main_occ_end));
                }

                $days = '';
                foreach ($mec_periods as $mec_period)
                {
                    $mec_days = explode(':', trim($mec_period, ': '));
                    if (!isset($mec_days[1])) continue;

                    $time_start = $event->time['start'];
                    if (isset($mec_days[2])) $time_start = str_replace('-', ':', str_replace('-AM', ' AM', str_replace('-PM', ' PM', $mec_days[2])));

                    $time_end = $event->time['end'];
                    if (isset($mec_days[3])) $time_end = str_replace('-', ':', str_replace('-AM', ' AM', str_replace('-PM', ' PM', $mec_days[3])));

                    $start_time = strtotime($mec_days[0] . ' ' . $time_start);
                    $end_time = strtotime($mec_days[1] . ' ' . $time_end);

                    // All Day Event
                    if (isset($event->meta['mec_date']) and isset($event->meta['mec_date']['allday']) and $event->meta['mec_date']['allday'])
                    {
                        $start_time = strtotime("Today", $start_time);
                        $end_time = strtotime('Tomorrow', $end_time);
                    }

                    $gmt_offset_seconds = $this->get_gmt_offset_seconds($start_time, $event);

                    $days .= gmdate('Ymd\\THi00\\Z', ($start_time - $gmt_offset_seconds)) . '/' . gmdate('Ymd\\THi00\\Z', ($end_time - $gmt_offset_seconds)) . ',';
                }

                // Add RDATE
                $recurrence[] = trim('RDATE;VALUE=PERIOD:' . trim($days, ', '), '; ');
            }

            // Add RRULE
            if (trim($freq))
            {
                $rrule = 'RRULE:FREQ=' . $freq . ';'
                    . ($interval > 1 ? 'INTERVAL=' . $interval . ';' : '')
                    . ($count != '' ? 'COUNT=' . $count : (($finish != '0000-00-00' and $finish != '') ? 'UNTIL=' . $finish . ';' : ''))
                    . ($wkst != '' ? 'WKST=' . $wkst . ';' : '')
                    . ($bysetpos != '' ? 'BYSETPOS=' . $bysetpos . ';' : '')
                    . ($byday != '' ? 'BYDAY=' . $byday . ';' : '');

                $recurrence[] = trim($rrule, '; ');
            }

            if (trim($event->mec->not_in_days))
            {
                $mec_not_in_days = explode(',', trim($event->mec->not_in_days, ','));
                $seconds_start = $event->mec->time_start;

                $not_in_days = '';
                foreach ($mec_not_in_days as $mec_not_in_day)
                {
                    $timestamp = strtotime($mec_not_in_day) + $seconds_start;

                    $gmt_offset_seconds = $this->get_gmt_offset_seconds($timestamp, $event);
                    $not_in_days .= gmdate('Ymd\THis\Z', $timestamp - $gmt_offset_seconds) . ',';
                }

                // Add EXDATE
                $recurrence[] = trim('EXDATE:' . trim($not_in_days, ', '), '; ');
            }
        }

        if ($only_rrule)
        {
            $rrule = '';
            if (is_array($recurrence) and count($recurrence))
            {
                foreach ($recurrence as $recur)
                {
                    if (strpos($recur, 'RRULE') !== false) $rrule = $recur;
                }
            }

            return $rrule;
        }
        else return $recurrence;
    }

    public static function get_upcoming_events($limit = 12)
    {
        MEC::import('app.skins.list');

        // Get list skin
        $list = new MEC_skin_list();

        // Attributes
        $atts = [
            'show_past_events' => 1,
            'start_date_type' => 'today',
            'sk-options' => [
                'list' => ['limit' => 20],
            ],
        ];

        // Initialize the skin
        $list->initialize($atts);

        // Fetch the events
        $list->fetch();

        return $list->events;
    }

    /**
     * Do the shortcode and return its output
     * @param integer $shortcode_id
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public static function get_shortcode_events($shortcode_id)
    {
        // Get Render
        $render = new MEC_render();
        $atts = apply_filters('mec_calendar_atts', $render->parse($shortcode_id, []));

        $skin = isset($atts['skin']) ? $atts['skin'] : $render->get_default_layout();

        $path = MEC::import('app.skins.' . $skin, true, true);
        $skin_path = apply_filters('mec_skin_path', $skin);

        if ($skin_path != $skin and $render->file->exists($skin_path)) $path = $skin_path;
        if (!$render->file->exists($path))
        {
            return esc_html__('Skin controller does not exist.', 'modern-events-calendar-lite');
        }

        include_once $path;

        $skin_class_name = 'MEC_skin_' . $skin;

        // Create Skin Object Class
        $SKO = new $skin_class_name();

        // Initialize the skin
        $SKO->initialize($atts);

        // Fetch the events
        $SKO->fetch();

        // Return the Events
        return $SKO->events;
    }

    /**
     * User limited for booking an event
     * @param string $user_email
     * @param array $ticket_info
     * @param integer $limit
     * @return array|boolean
     * @author Webnus <info@webnus.net>
     */
    public function booking_permitted($user_email, $ticket_info, $limit)
    {
        if (!is_array($ticket_info) or is_array($ticket_info) and count($ticket_info) < 2) return false;

        $user_email = sanitize_email($user_email);
        $user = $this->getUser()->by_email($user_email);
        $user_id = isset($user->ID) ? $user->ID : 0;

        // It's the first booking of this email
        if (!$user_id) return true;

        $event_id = isset($ticket_info['event_id']) ? intval($ticket_info['event_id']) : 0;
        $count = isset($ticket_info['count']) ? intval($ticket_info['count']) : 0;

        $timestamp = isset($ticket_info['date']) ? $ticket_info['date'] : '';
        if (!is_numeric($timestamp)) $timestamp = strtotime($timestamp);

        $year = date('Y', $timestamp);
        $month = date('m', $timestamp);
        $day = date('d', $timestamp);
        $hour = date('H', $timestamp);
        $minutes = date('i', $timestamp);

        $permission = true;
        $query = new WP_Query([
            'post_type' => $this->get_book_post_type(),
            'author' => $user_id,
            'posts_per_page' => -1,
            'post_status' => ['publish', 'pending', 'draft', 'future', 'private'],
            'year' => $year,
            'monthnum' => $month,
            'day' => $day,
            'hour' => $hour,
            'minute' => $minutes,
            'meta_query' => [
                ['key' => 'mec_event_id', 'value' => $event_id, 'compare' => '='],
                ['key' => 'mec_verified', 'value' => '-1', 'compare' => '!='], // Don't include canceled bookings
                ['key' => 'mec_confirmed', 'value' => '-1', 'compare' => '!='], // Don't include rejected bookings
            ],
        ]);

        $bookings = 0;
        if ($query->have_posts())
        {
            while ($query->have_posts())
            {
                $query->the_post();

                $ticket_ids_string = trim(get_post_meta(get_the_ID(), 'mec_ticket_id', true), ', ');
                $ticket_ids_count = count(explode(',', $ticket_ids_string));

                $bookings += $ticket_ids_count;
            }
        }

        if (($bookings + $count) > $limit) $permission = false;

        return ['booking_count' => $bookings, 'permission' => $permission];
    }

    public function booking_permitted_by_ip($event_id, $limit, $ticket_info = [])
    {
        if (!is_array($ticket_info) or count($ticket_info) < 2) return false;

        $count = isset($ticket_info['count']) ? intval($ticket_info['count']) : 0;

        $timestamp = $ticket_info['date'] ?? '';
        if (!is_numeric($timestamp) && $timestamp) $timestamp = strtotime($timestamp);

        $year = date('Y', $timestamp);
        $month = date('m', $timestamp);
        $day = date('d', $timestamp);
        $hour = date('H', $timestamp);
        $minutes = date('i', $timestamp);

        $attendee_ip = $this->get_client_ip();

        $args = [
            'post_type' => $this->get_book_post_type(),
            'posts_per_page' => -1,
            'post_status' => ['publish', 'pending', 'draft', 'future', 'private'],
            'year' => $year,
            'monthnum' => $month,
            'day' => $day,
            'hour' => $hour,
            'minute' => $minutes,
            'meta_query' => [
                [
                    'key' => 'mec_event_id',
                    'value' => $event_id,
                    'compare' => '=',
                ],
                [
                    'key' => 'mec_verified',
                    'value' => '-1',
                    'compare' => '!=',
                ],
                [
                    'key' => 'mec_confirmed',
                    'value' => '-1',
                    'compare' => '!=',
                ],
                [
                    'key' => 'mec_attendees',
                    'value' => $attendee_ip,
                    'compare' => 'LIKE',
                ],
            ],
        ];

        $bookings = 0;
        $permission = true;
        $mec_books = get_posts($args);

        foreach ($mec_books as $mec_book)
        {
            $get_attendees = get_post_meta($mec_book->ID, 'mec_attendees', true);
            if (is_array($get_attendees))
            {
                foreach ($get_attendees as $attendee)
                {
                    if (isset($attendee['buyerip']) and trim($attendee['buyerip'], '') == $attendee_ip)
                    {
                        $bookings += isset($attendee['count']) ? intval($attendee['count']) : 0;
                    }
                }
            }
        }

        if (($bookings + $count) > $limit) $permission = false;

        return ['booking_count' => $bookings, 'permission' => $permission];
    }

    /**
     * Return SoldOut Or A Few Tickets Label
     * @param string|object $event
     * @param string $date
     * @return string|boolean
     * @author Webnus <info@webnus.net>
     */
    public function get_flags($event, $date = null)
    {
        if (is_object($event))
        {
            $event_id = $event->data->ID;

            if (is_array($date) and isset($date['start']) and isset($date['start']['timestamp'])) $timestamp = $date['start']['timestamp'];
            else if (is_array($event->date) and isset($event->date['start']) and isset($event->date['start']['timestamp'])) $timestamp = $event->date['start']['timestamp'];
            else $timestamp = $event->data->time['start_timestamp'];
        }
        else
        {
            $event_id = $event;
            $timestamp = $date ? strtotime($date) : 0;
        }

        if ((!isset($event_id) or !trim($event_id)) or !trim($timestamp)) return false;

        // MEC Settings
        $settings = $this->get_settings();

        // Booking on single page is disabled
        if (!isset($settings['booking_status']) || !$settings['booking_status']) return false;

        // Original Event ID for Multilingual Websites
        $event_id = $this->get_original_event($event_id);

        // No Tickets
        $tickets = get_post_meta($event_id, 'mec_tickets', true);
        if (!is_array($tickets) || !count($tickets)) return false;

        // MEC Cache
        $cache = $this->getCache();

        // Return from Cache
        if ($cache->has('flag-' . $event_id . ':' . $timestamp)) return $cache->get('flag-' . $event_id . ':' . $timestamp);

        $total_event_seats = 0;
        foreach ($tickets as $ticket_id => $ticket)
        {
            if (!is_numeric($ticket_id)) continue;

            $bookings_limit_unlimited = $ticket['unlimited'] ?? 0;
            if (!$bookings_limit_unlimited and $total_event_seats >= 0 and isset($ticket['limit']) and is_numeric($ticket['limit']) and $ticket['limit'] >= 0) $total_event_seats += $ticket['limit'];
            else $total_event_seats = -1;
        }

        // Convert Timestamp
        $timestamp = $this->get_start_time_of_multiple_days($event_id, $timestamp);

        $book = $this->getBook();
        $availability = $book->get_tickets_availability($event_id, $timestamp);

        if (!is_array($availability) or (is_array($availability) and !count($availability))) return false;

        $sale_stopped = false;
        $remained_tickets = 0;

        foreach ($availability as $ticket_id => $remained)
        {
            if (is_numeric($ticket_id) and $remained >= 0) $remained_tickets += $remained;
            if (is_numeric($ticket_id) and isset($availability['stop_selling_' . $ticket_id]) and $availability['stop_selling_' . $ticket_id]) $sale_stopped = true;

            // Unlimited Tickets
            if (is_numeric($ticket_id) and $remained == -1)
            {
                $remained_tickets = -1;
                break;
            }
        }

        if (isset($availability['total']) and $availability['total'] >= 0 and $remained_tickets >= 0) $remained_tickets = (int) min($availability['total'], $remained_tickets);

        $add_css_class = $remained_tickets ? 'mec-few-tickets' : '';
        $output_tag = ' <span class="mec-event-title-soldout ' . esc_attr($add_css_class) . '"><span class=soldout>%%title%%</span></span> ';

        // Return Sale Has ended
        if ($sale_stopped)
        {
            $flag = str_replace('%%title%%', esc_html__('Sale has ended', 'modern-events-calendar-lite'), $output_tag) . '<input type="hidden" value="%%soldout%%"/>';
            $cache->set('flag-' . $event_id . ':' . $timestamp, $flag);

            return $flag;
        }
        // Return Sold Out Label
        else if ($remained_tickets === 0)
        {
            $flag = str_replace('%%title%%', esc_html__('Sold Out', 'modern-events-calendar-lite'), $output_tag) . '<input type="hidden" value="%%soldout%%"/>';
            $cache->set('flag-' . $event_id . ':' . $timestamp, $flag);

            return $flag;
        }

        // Booking Options
        $booking_options = get_post_meta($event_id, 'mec_booking', true);

        $bookings_last_few_tickets_percentage_inherite = $booking_options['last_few_tickets_percentage_inherit'] ?? 1;
        $bookings_last_few_tickets_percentage = ((isset($booking_options['last_few_tickets_percentage']) and trim($booking_options['last_few_tickets_percentage']) != '') ? $booking_options['last_few_tickets_percentage'] : null);

        $total_bookings_limit = (isset($booking_options['bookings_limit']) and trim($booking_options['bookings_limit'])) ? $booking_options['bookings_limit'] : 100;
        $bookings_limit_unlimited = $booking_options['bookings_limit_unlimited'] ?? 0;
        if ($bookings_limit_unlimited == '1') $total_bookings_limit = -1;

        // Get Per Occurrence
        $total_bookings_limit = MEC_feature_occurrences::param($event_id, $timestamp, 'bookings_limit', $total_bookings_limit);

        if (count($tickets) === 1)
        {
            $ticket = reset($tickets);
            if (isset($ticket['limit']) and trim($ticket['limit'])) $total_bookings_limit = $ticket['limit'];

            $bookings_limit_unlimited = $ticket['unlimited'] ?? 0;
            if ($bookings_limit_unlimited == '1') $total_bookings_limit = -1;
        }

        if ($total_event_seats >= 0 and $total_bookings_limit >= 0 and $total_event_seats < $total_bookings_limit) $total_bookings_limit = $total_event_seats;

        // Percentage
        $percentage = ((isset($settings['booking_last_few_tickets_percentage']) and trim($settings['booking_last_few_tickets_percentage']) != '') ? $settings['booking_last_few_tickets_percentage'] : 15);
        if (!$bookings_last_few_tickets_percentage_inherite and $bookings_last_few_tickets_percentage) $percentage = (int) $bookings_last_few_tickets_percentage;

        // Return A Few Ticket Label
        if (($total_bookings_limit > 0) and ($remained_tickets > 0 and $remained_tickets <= (($percentage * $total_bookings_limit) / 100)))
        {
            $flag = str_replace('%%title%%', esc_html__('Last Few Tickets', 'modern-events-calendar-lite'), $output_tag);
            $cache->set('flag-' . $event_id . ':' . $timestamp, $flag);

            return $flag;
        }

        $cache->set('flag-' . $event_id . ':' . $timestamp, false);
        return false;
    }

    public function is_soldout($event, $date)
    {
        return (bool) $this->get_flags($event, $date);
    }

    /**
     * Add Query String To URL
     * @param string $url
     * @param string $key
     * @param string $value
     * @resourse wp-mix.com
     * @return string
     */
    public function add_query_string($url, $key, $value)
    {
        $url = preg_replace('/([?&])' . $key . '=.*?(&|$)/i', '$1$2$4', $url);

        if (substr($url, strlen($url) - 1) == "?" or substr($url, strlen($url) - 1) == "&")
            $url = substr($url, 0, -1);

        if (strpos($url, '?') === false)
        {
            return ($url . '?' . $key . '=' . $value);
        }
        else
        {
            return ($url . '&' . $key . '=' . $value);
        }
    }

    /**
     * Check Is DateTime Format Validation
     * @param string $format
     * @param string $date
     * @return boolean
     */
    public function check_date_time_validation($format, $date)
    {
        if (func_num_args() < 2) return false;

        $check = DateTime::createFromFormat($format, $date);

        return $check && $check->format($format) === $date;
    }

    public function get_start_of_multiple_days($event_id, $date)
    {
        if (is_null($date) || trim($date) == '') return null;

        if (is_numeric($date))
        {

            $date = date('Y-m-d', $date);
        }

        $cache = $this->getCache();
        return $cache->rememberOnce('start-multiple-days-' . $event_id . '-' . $date, function () use ($event_id, $date)
        {
            $db = $this->getDB();
            return $db->select("SELECT `dstart` FROM `#__mec_dates` WHERE `post_id`='" . $event_id . "' AND ((`dstart`='" . esc_sql($date) . "') OR (`dstart`<'" . esc_sql($date) . "' AND `dend`>='" . esc_sql($date) . "')) ORDER BY `dstart` DESC LIMIT 1", 'loadResult');
        });
    }

    public function get_start_time_of_multiple_days($event_id, $time)
    {
        if (is_null($time) || !trim($time)) return null;

        // Cache
        $cache = $this->getCache();

        // Get Start Time
        $new_time = $cache->rememberOnce('start-multiple-days-start-time' . $event_id . ':' . $time, function () use ($event_id, $time)
        {
            // Database
            $db = $this->getDB();

            return $db->select("SELECT `tstart` FROM `#__mec_dates` WHERE `post_id`=" . esc_sql($event_id) . " AND ((`tstart`=" . esc_sql($time) . ") OR (`tstart`<" . esc_sql($time) . " AND `tend`>" . esc_sql($time) . ")) ORDER BY `tstart` DESC LIMIT 1", 'loadResult');
        });

        return ($new_time ?: $time);
    }

    public function is_midnight_event($event)
    {
        // Settings
        $settings = $this->get_settings();

        $start_timestamp = strtotime($event->date['start']['date']);
        $end_timestamp = strtotime($event->date['end']['date']);

        $diff = $this->date_diff($event->date['start']['date'], $event->date['end']['date']);
        $days = (isset($diff->days) and !$diff->invert) ? $diff->days : 0;

        $time = $event->data->time['end_raw'];

        // Midnight Hour
        $midnight_hour = (isset($settings['midnight_hour']) and $settings['midnight_hour']) ? $settings['midnight_hour'] : 0;
        $midnight = $end_timestamp + (3600 * $midnight_hour);

        // End Date is before Midnight
        if ($days == 1 and $start_timestamp < $end_timestamp and $midnight >= strtotime($event->date['end']['date'] . ' ' . $time)) return true;

        return false;
    }

    public function mec_content_html($text, $max_length)
    {
        $tags = [];
        $result = "";
        $is_open = false;
        $grab_open = false;
        $is_close = false;
        $in_double_quotes = false;
        $in_single_quotes = false;
        $tag = "";
        $i = 0;
        $stripped = 0;
        $stripped_text = strip_tags($text);

        while ($i < strlen($text) && $stripped < strlen($stripped_text) && $stripped < $max_length)
        {
            $symbol = $text[$i];
            $result .= $symbol;
            switch ($symbol)
            {
                case '<':
                    $is_open = true;
                    $grab_open = true;
                    break;

                case '"':
                    if ($in_double_quotes) $in_double_quotes = false;
                    else $in_double_quotes = true;
                    break;

                case "'":
                    if ($in_single_quotes) $in_single_quotes = false;
                    else $in_single_quotes = true;
                    break;

                case '/':
                    if ($is_open && !$in_double_quotes && !$in_single_quotes)
                    {
                        $is_close = true;
                        $is_open = false;
                        $grab_open = false;
                    }

                    break;

                case ' ':
                    if ($is_open) $grab_open = false;
                    else $stripped++;

                    break;

                case '>':
                    if ($is_open)
                    {
                        $is_open = false;
                        $grab_open = false;
                        array_push($tags, $tag);
                        $tag = "";
                    }
                    else if ($is_close)
                    {
                        $is_close = false;
                        array_pop($tags);
                        $tag = "";
                    }

                    break;

                default:
                    if ($grab_open || $is_close) $tag .= $symbol;
                    if (!$is_open && !$is_close) $stripped++;
            }

            $i++;
        }

        while ($tags) $result .= "</" . array_pop($tags) . ">";

        return $result;
    }

    public function get_users_dropdown($current = [], $notifications = 'booking_notification')
    {
        ob_start();
        $users_stat = count_users();

        if (is_array($users_stat) and isset($users_stat['total_users']) and $users_stat['total_users'] > 500):
            ?>
            <input type="text" id="mec_notifications_<?php echo esc_attr($notifications); ?>_receiver_users"
                   name="mec[notifications][<?php echo esc_attr($notifications); ?>][receiver_users]"
                   value="<?php echo(is_array($current) ? implode(',', $current) : ''); ?>">
        <?php else: ?>
            <select id="mec_notifications_<?php echo esc_attr($notifications); ?>_receiver_users"
                    class="mec-notification-dropdown-select2"
                    name="mec[notifications][<?php echo esc_attr($notifications); ?>][receiver_users][]"
                    multiple="multiple">
                <?php
                $users = get_users([
                    'number' => 500,
                ]);

                foreach ($users as $user)
                {
                    ?>
                    <option
                        value="<?php echo isset($user->data->ID) ? esc_attr($user->data->ID) : 0; ?>" <?php echo (is_array($current) and in_array(intval($user->data->ID), $current)) ? 'selected="selected"' : ''; ?>><?php echo (isset($user->data->display_name) and trim($user->data->display_name)) ? esc_html(trim($user->data->display_name)) : '(' . esc_html(trim($user->data->user_login)) . ')'; ?></option>
                    <?php
                }
                ?>
            </select>
        <?php
        endif;

        $output = ob_get_contents();
        ob_clean();

        return $output;
    }

    public function get_emails_by_users($users)
    {
        $users_list = [];
        if (is_array($users) and count($users))
        {
            $query = 'SELECT `user_email` FROM `#__users` WHERE';
            foreach ($users as $user_id)
            {
                $query .= ' ID=' . $user_id . ' OR';
            }

            $db = $this->getDB();
            $users_list = $db->select(substr(trim($query), 0, -2), 'loadObjectList');
        }

        return array_keys($users_list);
    }

    public function get_roles_dropdown($current = [], $notifications = 'booking_notification')
    {
        global $wp_roles;
        $roles = $wp_roles->get_names();
        ob_start();
        ?>
        <select id="mec_notifications_<?php echo esc_attr($notifications); ?>_receiver_roles"
                class="mec-notification-dropdown-select2"
                name="mec[notifications][<?php echo esc_attr($notifications); ?>][receiver_roles][]"
                multiple="multiple">
            <?php
            foreach ($roles as $role_key => $role_name)
            {
                ?>
                <option
                    value="<?php echo esc_attr($role_key); ?>" <?php echo (is_array($current) and in_array(trim($role_key), $current)) ? 'selected="selected"' : ''; ?>><?php echo esc_html($role_name); ?></option>
                <?php
            }
            ?>
        </select>
        <?php
        $output = ob_get_contents();
        ob_clean();

        return $output;
    }

    public function get_emails_by_roles($roles)
    {
        $user_list = [];
        foreach ($roles as $role)
        {
            $curren_get_users = get_users([
                'role' => $role,
            ]);

            if (count($curren_get_users))
            {
                foreach ($curren_get_users as $user)
                {
                    if (isset($user->data->user_email) and !in_array($user->data->user_email, $user_list)) $user_list[] = $user->data->user_email;
                }
            }
        }

        return $user_list;
    }

    public function get_normal_labels($event, $display_label = false)
    {
        $output = '';

        if ($display_label != false and is_object($event) and isset($event->data->labels) and !empty($event->data->labels))
        {
            foreach ($event->data->labels as $label)
            {
                if (isset($label['style']) and !trim($label['style']) and isset($label['name']) and trim($label['name'])) $output .= '<span data-style="Normal" class="mec-label-normal" style="background-color:' . esc_attr($label['color']) . ';">' . trim($label['name']) . '</span>';
            }
        }

        // Ongoing Event
        if ($display_label and $this->is_ongoing($event)) $output .= '<span data-style="Normal" class="mec-label-normal mec-ongoing-normal-label">' . $this->m('ongoing', esc_html__('Ongoing', 'modern-events-calendar-lite')) . '</span>';
        // Expired Event
        else if ($display_label and $this->is_expired($event)) $output .= '<span data-style="Normal" class="mec-label-normal mec-expired-normal-label">' . $this->m('expired', esc_html__('Expired', 'modern-events-calendar-lite')) . '</span>';
        // Upcoming Event
        else if ($display_label and is_object($event) and isset($event->date, $event->date['start'], $event->date['start']['timestamp']))
        {
            // Settings
            $settings = $this->get_settings();

            // Remaining Time
            $remaining = $event->date['start']['timestamp'] - current_time('timestamp');

            // Remaining Time
            if ($remaining > 0 and isset($settings['remaining_time_label']) and $settings['remaining_time_label'])
            {
                // Months
                if ($remaining >= 7776000) $remaining_str = sprintf(esc_html__('%s months', 'modern-events-calendar-lite'), number_format_i18n(round($remaining / 2592000), 0));
                // Days
                else if ($remaining >= 172800) $remaining_str = sprintf(esc_html__('%s days', 'modern-events-calendar-lite'), number_format_i18n(round($remaining / 86400), 0));
                // Hours
                else if ($remaining >= 7200) $remaining_str = sprintf(esc_html__('%s hours', 'modern-events-calendar-lite'), number_format_i18n(round($remaining / 3600), 0));
                // Minutes
                else $remaining_str = sprintf(esc_html__('%s minutes', 'modern-events-calendar-lite'), number_format_i18n(round($remaining / 60), 0));

                $output .= '<span data-style="Normal" class="mec-label-normal mec-remaining-time-normal-label">' . $remaining_str . '</span>';
            }
        }

        return $output ? '<span class="mec-labels-normal">' . MEC_kses::element($output) . '</span>' : $output;
    }

    public function display_cancellation_reason($event, $display_reason = false)
    {
        if (!is_object($event)) return '';

        $event_id = $event->ID;
        if (isset($event->requested_id)) $event_id = $event->requested_id; // Requested Event in Multilingual Websites

        $start_timestamp = (isset($event->data->time['start_timestamp']) ? $event->data->time['start_timestamp'] : (isset($event->date['start']['timestamp']) ? $event->date['start']['timestamp'] : strtotime($event->date['start']['date'])));

        // All Params
        $params = MEC_feature_occurrences::param($event_id, $start_timestamp, '*');

        $event_status = (isset($event->data->meta['mec_event_status']) and trim($event->data->meta['mec_event_status'])) ? $event->data->meta['mec_event_status'] : 'EventScheduled';
        $event_status = (isset($params['event_status']) and trim($params['event_status']) != '') ? $params['event_status'] : $event_status;

        $reason = get_post_meta($event_id, 'mec_cancelled_reason', true);
        $reason = (isset($params['cancelled_reason']) and trim($params['cancelled_reason']) != '') ? $params['cancelled_reason'] : $reason;

        $output = '';
        if (isset($event_status) and $event_status == 'EventCancelled' && $display_reason != false and isset($reason) and !empty($reason))
        {
            $output = '<div class="mec-cancellation-reason"><span>' . MEC_kses::element($reason) . '</span></div>';
        }

        return $output;
    }

    public function standardize_format($date = '', $format = 'Y-m-d')
    {
        if (!trim($date)) return '';

        $date = str_replace('.', '-', $date);
        $f = explode('&', trim($format));

        if (isset($f[1])) $return = date($f[1], strtotime($date));
        else $return = date($format, strtotime($date));

        return $return;
    }

    public function timepicker($args)
    {
        $method = $args['method'] ?? 24;
        $time_hour = $args['time_hour'] ?? null;
        $time_minutes = $args['time_minutes'] ?? null;
        $time_ampm = $args['time_ampm'] ?? null;
        $name = $args['name'] ?? 'mec[date]';
        $id_key = $args['id_key'] ?? '';

        $hour_key = $args['hour_key'] ?? 'hour';
        $minutes_key = $args['minutes_key'] ?? 'minutes';
        $ampm_key = $args['ampm_key'] ?? 'ampm';

        if ($method == 24)
        {
            if ($time_ampm == 'PM' and $time_hour != 12) $time_hour += 12;
            if ($time_ampm == 'AM' and $time_hour == 12) $time_hour += 12;
            ?>
            <select name="<?php echo esc_attr($name); ?>[<?php echo esc_attr($hour_key); ?>]"
                    <?php if (trim($id_key)): ?>id="mec_<?php echo esc_attr($id_key); ?>hour" <?php endif; ?>
                    title="<?php esc_attr_e('Hours', 'modern-events-calendar-lite'); ?>">
                <?php for ($i = 0; $i <= 23; $i++) : ?>
                    <option <?php echo ($time_hour == $i) ? 'selected="selected"' : ''; ?>
                        value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                <?php endfor; ?>
            </select>
            <span class="time-dv">:</span>
            <select name="<?php echo esc_attr($name); ?>[<?php echo esc_attr($minutes_key); ?>]"
                    <?php if (trim($id_key)): ?>id="mec_<?php echo esc_attr($id_key); ?>minutes" <?php endif; ?>
                    title="<?php esc_attr_e('Minutes', 'modern-events-calendar-lite'); ?>">
                <?php for ($i = 0; $i <= 11; $i++) : ?>
                    <option <?php echo ($time_minutes == ($i * 5)) ? 'selected="selected"' : ''; ?>
                        value="<?php echo($i * 5); ?>"><?php echo sprintf('%02d', ($i * 5)); ?></option>
                <?php endfor; ?>
            </select>
            <?php
        }
        else
        {
            $include_h0 = isset($args['include_h0']) ? (bool) $args['include_h0'] : false;

            $h = ($include_h0 ? 0 : 1);
            if (!$include_h0 and $time_ampm == 'AM' and $time_hour == '0') $time_hour = 12;
            ?>
            <select name="<?php echo esc_attr($name); ?>[<?php echo esc_attr($hour_key); ?>]"
                    <?php if (trim($id_key)): ?>id="mec_<?php echo esc_attr($id_key); ?>hour" <?php endif; ?>
                    title="<?php esc_attr_e('Hours', 'modern-events-calendar-lite'); ?>">
                <?php for ($i = $h; $i <= 12; $i++) : ?>
                    <option <?php echo ($time_hour == $i) ? 'selected="selected"' : ''; ?>
                        value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                <?php endfor; ?>
            </select>
            <span class="time-dv">:</span>
            <select name="<?php echo esc_attr($name); ?>[<?php echo esc_attr($minutes_key); ?>]"
                    <?php if (trim($id_key)): ?>id="mec_<?php echo esc_attr($id_key); ?>minutes" <?php endif; ?>
                    title="<?php esc_attr_e('Minutes', 'modern-events-calendar-lite'); ?>">
                <?php for ($i = 0; $i <= 11; $i++) : ?>
                    <option <?php echo ($time_minutes == ($i * 5)) ? 'selected="selected"' : ''; ?>
                        value="<?php echo($i * 5); ?>"><?php echo sprintf('%02d', ($i * 5)); ?></option>
                <?php endfor; ?>
            </select>
            <select name="<?php echo esc_attr($name); ?>[<?php echo esc_attr($ampm_key); ?>]"
                    <?php if (trim($id_key)): ?>id="mec_<?php echo esc_attr($id_key); ?>ampm" <?php endif; ?>
                    title="<?php esc_attr_e('AM / PM', 'modern-events-calendar-lite'); ?>">
                <option <?php echo ($time_ampm == 'AM') ? 'selected="selected"' : ''; ?>
                    value="AM"><?php esc_html_e('AM', 'modern-events-calendar-lite'); ?></option>
                <option <?php echo ($time_ampm == 'PM') ? 'selected="selected"' : ''; ?>
                    value="PM"><?php esc_html_e('PM', 'modern-events-calendar-lite'); ?></option>
            </select>
            <?php
        }
    }

    public function holding_status($event)
    {
        if ($this->is_ongoing($event)) return '<dl><dd><span class="mec-holding-status mec-holding-status-ongoing">' . $this->m('ongoing', esc_html__('Ongoing...', 'modern-events-calendar-lite')) . '</span></dd></dl>';
        else if ($this->is_expired($event)) return '<dl><dd><span class="mec-holding-status mec-holding-status-expired">' . $this->m('expired', esc_html__('Expired!', 'modern-events-calendar-lite')) . '</span></dd></dl>';

        return '';
    }

    public function is_ongoing($event)
    {
        $date = (($event and isset($event->date)) ? $event->date : []);

        $start_date = (isset($date['start']) and isset($date['start']['date'])) ? $date['start']['date'] : null;
        $end_date = (isset($date['end']) and isset($date['end']['date'])) ? $date['end']['date'] : null;

        if (!$start_date or !$end_date) return false;

        $start_time = null;
        if (isset($date['start']['hour']))
        {
            $s_hour = $date['start']['hour'];
            if (isset($date['start']['ampm']) and strtoupper($date['start']['ampm']) == 'AM' and $s_hour == '0') $s_hour = 12;

            $start_time = sprintf("%02d", $s_hour) . ':';
            $start_time .= sprintf("%02d", $date['start']['minutes']);
            if (isset($date['start']['ampm'])) $start_time .= ' ' . trim($date['start']['ampm']);
        }
        else if (isset($event->data->time) and is_array($event->data->time) and isset($event->data->time['start_timestamp'])) $start_time = date('H:i', $event->data->time['start_timestamp']);

        $end_time = null;
        if (isset($date['end']['hour']))
        {
            $e_hour = $date['end']['hour'];
            if (isset($date['end']['ampm']) and strtoupper($date['end']['ampm']) == 'AM' and $e_hour == '0') $e_hour = 12;

            $end_time = sprintf("%02d", $e_hour) . ':';
            $end_time .= sprintf("%02d", $date['end']['minutes']);
            if ($date['end']['ampm']) $end_time .= ' ' . trim($date['end']['ampm']);
        }
        else if (isset($event->data->time) and is_array($event->data->time) and isset($event->data->time['end_timestamp'])) $end_time = date('H:i', $event->data->time['end_timestamp']);

        if (!$start_time or !$end_time) return false;

        $allday = get_post_meta($event->ID, 'mec_allday', true);
        if ($allday)
        {
            $start_time = '12:01 AM';
            $end_time = '11:59 PM';
        }

        // Timezone
        $TZO = $this->get_TZO($event);

        $d1 = new DateTime($start_date . ' ' . $start_time, $TZO);
        $d2 = new DateTime('now', $TZO);
        $d3 = new DateTime($end_date . ' ' . $end_time, $TZO);

        // The event is ongoing
        if ($d1 <= $d2 and $d3 > $d2) return true;
        return false;
    }

    public function is_expired($event)
    {
        $date = (($event and isset($event->date)) ? $event->date : []);

        $end_date = (isset($date['end']) and isset($date['end']['date'])) ? $date['end']['date'] : null;
        if (!$end_date) return false;

        $e_hour = ($date['end']['hour'] ?? 11);
        if (isset($date['end']['ampm']) and strtoupper($date['end']['ampm']) == 'AM' and $e_hour == '0') $e_hour = 12;

        $end_time = sprintf("%02d", $e_hour) . ':';
        $end_time .= sprintf("%02d", ($date['end']['minutes'] ?? 59));
        $end_time .= ' ' . (isset($date['end']['ampm']) ? trim($date['end']['ampm']) : 'PM');

        $allday = $date['allday'] ?? 0;
        if ($allday) $end_time = '11:59 PM';

        // Timezone
        $TZO = $this->get_TZO($event);

        $d1 = new DateTime('now', $TZO);
        $d2 = new DateTime($end_date . ' ' . $end_time, $TZO);

        // The event is expired
        if ($d2 < $d1) return true;
        return false;
    }

    public function is_started($event)
    {
        $date = (($event and isset($event->date)) ? $event->date : []);

        $start_date = (isset($date['start']) and isset($date['start']['date'])) ? $date['start']['date'] : null;
        if (!$start_date) return false;

        $s_hour = ($date['start']['hour'] ?? null);
        if (isset($date['start']['ampm']) and strtoupper($date['start']['ampm']) == 'AM' and $s_hour == '0') $s_hour = 12;

        $start_time = sprintf("%02d", $s_hour) . ':';
        $start_time .= sprintf("%02d", ($date['start']['minutes'] ?? null));
        $start_time .= ' ' . (isset($date['start']['ampm']) ? trim($date['start']['ampm']) : null);

        $allday = ($date['allday'] ?? 0);
        if ($allday) $start_time = '12:01 AM';

        // Timezone
        $TZO = $this->get_TZO($event);

        $d1 = new DateTime($start_date . ' ' . $start_time, $TZO);
        $d2 = new DateTime('now', $TZO);

        // The event is started
        if ($d1 <= $d2) return true;
        return false;
    }

    public function array_key_first($arr)
    {
        if (!function_exists('array_key_first'))
        {
            reset($arr);
            return key($arr);
        }
        else return array_key_first($arr);
    }

    public function array_key_last($arr)
    {
        if (!function_exists('array_key_last'))
        {
            end($arr);
            return key($arr);
        }
        else return array_key_last($arr);
    }

    public function is_day_first($format = '')
    {
        if (!trim($format)) $format = get_option('date_format');
        $chars = str_split($format);

        $status = true;
        foreach ($chars as $char)
        {
            if (in_array($char, ['d', 'D', 'j', 'l', 'N', 'S', 'w', 'z']))
            {
                $status = true;
                break;
            }
            else if (in_array($char, ['F', 'm', 'M', 'n']))
            {
                $status = false;
                break;
            }
        }

        return $status;
    }

    public function is_year_first($format = '')
    {
        if (!trim($format)) $format = get_option('date_format');
        $chars = str_split($format);

        $status = true;
        foreach ($chars as $char)
        {
            if (in_array($char, ['Y', 'y', 'o']))
            {
                $status = true;
                break;
            }
            else if (in_array($char, ['F', 'm', 'M', 'n', 'd', 'D', 'j', 'l', 'N', 'S', 'w', 'z']))
            {
                $status = false;
                break;
            }
        }

        return $status;
    }

    public function timezones($selected)
    {
        $output = wp_timezone_choice($selected);

        $ex = explode('<optgroup', $output);
        unset($ex[count($ex) - 1]);

        return implode('<optgroup', $ex);
    }

    public function get_event_next_occurrences($event, $occurrence, $maximum = 2, $occurrence_time = '')
    {
        $event_id = $event->ID;

        // Event Repeat Type
        $repeat_type = (!empty($event->meta['mec_repeat_type']) ? $event->meta['mec_repeat_type'] : get_post_meta($event_id, 'mec_repeat_type', true));

        $md_start = $this->get_start_of_multiple_days($event_id, $occurrence);
        if ($md_start) $occurrence = $md_start;

        $md_start_time = $this->get_start_time_of_multiple_days($event_id, $occurrence_time);
        if ($md_start_time) $occurrence_time = $md_start_time;

        if (strtotime($occurrence) and in_array($repeat_type, ['certain_weekdays', 'custom_days', 'weekday', 'weekend', 'advanced'])) $occurrence = date('Y-m-d', strtotime($occurrence));
        else if (strtotime($occurrence))
        {
            $new_occurrence = date('Y-m-d', strtotime('-1 day', strtotime($occurrence)));
            if ($repeat_type === 'monthly' and date('m', strtotime($new_occurrence)) != date('m', strtotime($occurrence))) $new_occurrence = date('Y-m-d', strtotime($occurrence));

            $occurrence = $new_occurrence;
        }
        else $occurrence = null;

        $render = $this->getRender();
        return $render->dates($event_id, ($event->data ?? null), $maximum, (trim($occurrence_time) ? date('Y-m-d H:i:s', $occurrence_time) : $occurrence));
    }

    public function get_post_thumbnail_url($post = null, $size = 'post-thumbnail')
    {
        if (function_exists('get_the_post_thumbnail_url')) return get_the_post_thumbnail_url($post, $size);
        else
        {
            $post_thumbnail_id = get_post_thumbnail_id($post);
            if (!$post_thumbnail_id) return false;

            $image = wp_get_attachment_image_src($post_thumbnail_id, $size);
            return $image['0'] ?? false;
        }
    }

    public function is_multipleday_occurrence($event, $check_same_month = false)
    {
        // Multiple Day Flag
        if (isset($event->data) and isset($event->data->multipleday)) return (bool) $event->data->multipleday;

        $start_date = ((isset($event->date) and isset($event->date['start']) and isset($event->date['start']['date'])) ? $event->date['start']['date'] : null);
        $end_date = ((isset($event->date) and isset($event->date['end']) and isset($event->date['end']['date'])) ? $event->date['end']['date'] : null);

        if ($check_same_month)
        {
            $multipleday = (!is_null($start_date) and $start_date !== $end_date);
            return ($multipleday and (date('m', strtotime($start_date)) == date('m', strtotime($end_date))));
        }

        return (!is_null($start_date) and $start_date !== $end_date);
    }

    public function get_wp_user_fields()
    {
        $meta_keys = get_transient('mec-user-meta-keys');
        if (!empty($meta_keys))
        {

            return $meta_keys;
        }

        $db = $this->getDB();
        $raw_fields = $db->select("SELECT DISTINCT `meta_key` FROM `#__usermeta` WHERE `meta_value` NOT LIKE '%{%'");

        $forbidden = [
            'nickname',
            'syntax_highlighting',
            'comment_shortcuts',
            'admin_color',
            'use_ssl',
            'show_admin_bar_front',
            'wp_user_level',
            'user_last_view_date',
            'user_last_view_date_events',
            'wc_last_active',
            'last_update',
            'last_activity',
            'locale',
            'show_welcome_panel',
            'rich_editing',
            'nav_menu_recently_edited',
        ];

        $fields = [];
        foreach ($raw_fields as $raw_field)
        {
            $key = $raw_field->meta_key;

            if (substr($key, 0, 1) === '_') continue;
            if (substr($key, 0, 4) === 'icl_') continue;
            if (substr($key, 0, 4) === 'mec_') continue;
            if (substr($key, 0, 3) === 'wp_') continue;
            if (substr($key, 0, 10) === 'dismissed_') continue;
            if (in_array($key, $forbidden)) continue;

            $fields[$key] = trim(ucwords(str_replace('_', ' ', str_replace('-', ' ', $key))));
        }

        set_transient('mec-user-meta-keys', $fields, 36000);

        return $fields;
    }

    public function get_wp_user_fields_dropdown($name, $value)
    {
        $fields = $this->get_wp_user_fields();

        $dropdown = '<select name="' . esc_attr($name) . '" title="' . esc_html__('Mapping with Profile Fields', 'modern-events-calendar-lite') . '">';
        $dropdown .= '<option value="">-----</option>';
        foreach ($fields as $key => $label) $dropdown .= '<option value="' . esc_attr($key) . '" ' . ($value == $key ? 'selected="selected"' : '') . '>' . esc_html($label) . '</option>';
        $dropdown .= '</select>';

        return $dropdown;
    }

    public function wizard_import_dummy_events()
    {
        if (apply_filters('mec_activation_import_events', true))
        {
            // Create Default Events
            $events = [
                ['title' => 'One Time Multiple Day Event', 'start' => date('Y-m-d', strtotime('+5 days')), 'end' => date('Y-m-d', strtotime('+7 days')), 'finish' => date('Y-m-d', strtotime('+7 days')), 'repeat_type' => '', 'repeat_status' => 0, 'interval' => null, 'meta' => ['mec_color' => 'dd823b']],
                ['title' => 'Daily each 3 days', 'start' => date('Y-m-d'), 'end' => date('Y-m-d'), 'repeat_type' => 'daily', 'repeat_status' => 1, 'interval' => 3, 'meta' => ['mec_color' => 'a3b745']],
                ['title' => 'Weekly on Mondays', 'start' => date('Y-m-d', strtotime('Next Monday')), 'end' => date('Y-m-d', strtotime('Next Monday')), 'repeat_type' => 'weekly', 'repeat_status' => 1, 'interval' => 7, 'meta' => ['mec_color' => 'e14d43']],
                ['title' => 'Monthly on 27th', 'start' => date('Y-m-27'), 'end' => date('Y-m-27'), 'repeat_type' => 'monthly', 'repeat_status' => 1, 'interval' => null, 'year' => '*', 'month' => '*', 'day' => ',27,', 'week' => '*', 'weekday' => '*', 'meta' => ['mec_color' => '00a0d2']],
                ['title' => 'Yearly on August 20th and 21st', 'start' => date('Y-08-20'), 'end' => date('Y-08-21'), 'repeat_type' => 'yearly', 'repeat_status' => 1, 'interval' => null, 'year' => '*', 'month' => ',08,', 'day' => ',20,21,', 'week' => '*', 'weekday' => '*', 'meta' => ['mec_color' => 'fdd700']],
            ];

            // Import Events
            $this->save_events($events);
        }
    }

    public function wizard_import_dummy_shortcodes()
    {
        if (apply_filters('mec_activation_import_shortcodes', true))
        {
            // Search Form Options
            $sf_options = ['category' => ['type' => 'dropdown'], 'text_search' => ['type' => 'text_input']];

            // Create Default Calendars
            $calendars = [
                ['title' => 'Full Calendar', 'meta' => ['skin' => 'full_calendar', 'show_past_events' => 1, 'sk-options' => ['full_calendar' => ['start_date_type' => 'today', 'default_view' => 'list', 'monthly' => 1, 'weekly' => 1, 'daily' => 1, 'list' => 1]], 'sf-options' => ['full_calendar' => ['month_filter' => ['type' => 'dropdown'], 'text_search' => ['type' => 'text_input']]], 'sf_status' => 1]],
                ['title' => 'Monthly View', 'meta' => ['skin' => 'monthly_view', 'show_past_events' => 1, 'sk-options' => ['monthly_view' => ['start_date_type' => 'start_current_month', 'next_previous_button' => 1]], 'sf-options' => ['monthly_view' => $sf_options], 'sf_status' => 1]],
                ['title' => 'Weekly View', 'meta' => ['skin' => 'weekly_view', 'show_past_events' => 1, 'sk-options' => ['weekly_view' => ['start_date_type' => 'start_current_month', 'next_previous_button' => 1]], 'sf-options' => ['weekly_view' => $sf_options], 'sf_status' => 1]],
                ['title' => 'Daily View', 'meta' => ['skin' => 'daily_view', 'show_past_events' => 1, 'sk-options' => ['daily_view' => ['start_date_type' => 'start_current_month', 'next_previous_button' => 1]], 'sf-options' => ['daily_view' => $sf_options], 'sf_status' => 1]],
                ['title' => 'Map View', 'meta' => ['skin' => 'map', 'show_past_events' => 1, 'sk-options' => ['map' => ['limit' => 200]], 'sf-options' => ['map' => $sf_options], 'sf_status' => 1]],
                ['title' => 'Upcoming events (List)', 'meta' => ['skin' => 'list', 'show_past_events' => 0, 'sk-options' => ['list' => ['load_more_button' => 1]], 'sf-options' => ['list' => $sf_options], 'sf_status' => 1]],
                ['title' => 'Upcoming events (Grid)', 'meta' => ['skin' => 'grid', 'show_past_events' => 0, 'sk-options' => ['grid' => ['load_more_button' => 1]], 'sf-options' => ['grid' => $sf_options], 'sf_status' => 1]],
                ['title' => 'Carousel View', 'meta' => ['skin' => 'carousel', 'show_past_events' => 0, 'sk-options' => ['carousel' => ['count' => 3, 'limit' => 12]], 'sf-options' => ['carousel' => $sf_options], 'sf_status' => 0]],
                ['title' => 'Countdown View', 'meta' => ['skin' => 'countdown', 'show_past_events' => 0, 'sk-options' => ['countdown' => ['style' => 'style3', 'event_id' => '-1']], 'sf-options' => ['countdown' => $sf_options], 'sf_status' => 0]],
                ['title' => 'Slider View', 'meta' => ['skin' => 'slider', 'show_past_events' => 0, 'sk-options' => ['slider' => ['style' => 't1', 'limit' => 6, 'autoplay' => 3000]], 'sf-options' => ['slider' => $sf_options], 'sf_status' => 0]],
                ['title' => 'Masonry View', 'meta' => ['skin' => 'masonry', 'show_past_events' => 0, 'sk-options' => ['masonry' => ['limit' => 24, 'filter_by' => 'category']], 'sf-options' => ['masonry' => $sf_options], 'sf_status' => 0]],
                ['title' => 'Agenda View', 'meta' => ['skin' => 'agenda', 'show_past_events' => 0, 'sk-options' => ['agenda' => ['load_more_button' => 1]], 'sf-options' => ['agenda' => $sf_options], 'sf_status' => 1]],
                ['title' => 'Timetable View', 'meta' => ['skin' => 'timetable', 'show_past_events' => 0, 'sk-options' => ['timetable' => ['next_previous_button' => 1]], 'sf-options' => ['timetable' => $sf_options], 'sf_status' => 1]],
                ['title' => 'Tile View', 'meta' => ['skin' => 'tile', 'show_past_events' => 0, 'sk-options' => ['tile' => ['next_previous_button' => 1]], 'sf-options' => ['tile' => $sf_options], 'sf_status' => 1]],
                ['title' => 'Timeline View', 'meta' => ['skin' => 'timeline', 'show_past_events' => 0, 'sk-options' => ['timeline' => ['load_more_button' => 1]], 'sf-options' => ['timeline' => $sf_options], 'sf_status' => 0]],
            ];

            foreach ($calendars as $calendar)
            {
                // Calendar exists
                if (post_exists($calendar['title'], 'MEC')) continue;

                $post = ['post_title' => $calendar['title'], 'post_content' => 'MEC', 'post_type' => 'mec_calendars', 'post_status' => 'publish'];
                $post_id = wp_insert_post($post);

                update_post_meta($post_id, 'label', '');
                update_post_meta($post_id, 'category', '');
                update_post_meta($post_id, 'location', '');
                update_post_meta($post_id, 'organizer', '');
                update_post_meta($post_id, 'tag', '');
                update_post_meta($post_id, 'author', '');

                foreach ($calendar['meta'] as $key => $value) update_post_meta($post_id, $key, $value);
            }
        }
    }

    public function save_wizard_options()
    {
        $wpnonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : null;

        // Check if our nonce is set.
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'mec_options_wizard')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Current User is not Permitted
        if (!current_user_can('mec_settings') and !current_user_can('administrator')) $this->response(['success' => 0, 'code' => 'ADMIN_ONLY']);

        $mec = isset($_REQUEST['mec']) ? $this->sanitize_deep_array($_REQUEST['mec']) : [];

        $filtered = [];
        foreach ($mec as $key => $value) $filtered[$key] = (is_array($value) ? $value : []);

        $current = get_option('mec_options', []);
        $final = $current;

        // Merge new options with previous options
        foreach ($filtered as $key => $value)
        {
            if (is_array($value))
            {
                foreach ($value as $k => $v)
                {
                    // Define New Array
                    if (!isset($final[$key])) $final[$key] = [];

                    // Overwrite Old Value
                    $final[$key][$k] = $v;
                }
            }
            // Overwrite Old Value
            else $final[$key] = $value;
        }

        update_option('mec_options', $final);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function is_user_booked($user_id, $event_id, $timestamp)
    {
        $bookings = $this->get_bookings($event_id, $timestamp, 1, $user_id);
        return (bool) count($bookings);
    }

    public function get_event_attendees($id, $occurrence = null, $verified = true)
    {
        $bookings = $this->get_bookings($id, $occurrence, '-1', null, $verified);

        // Attendees
        $attendees = [];
        foreach ($bookings as $booking)
        {
            $atts = get_post_meta($booking->ID, 'mec_attendees', true);
            $atts = apply_filters('mec_filter_event_bookings', $atts, $booking->ID, $occurrence);

            if (isset($atts['attachments'])) unset($atts['attachments']);

            foreach ($atts as $key => $value)
            {
                if (!is_numeric($key)) continue;

                $atts[$key]['book_id'] = $booking->ID;
                $atts[$key]['key'] = ($key + 1);
            }

            $attendees = array_merge($attendees, $atts);
        }

        // $attendees = apply_filters('mec_attendees_list_data', $attendees, $id, $occurrence);
        add_filter('mec_attendees_list_data', function ($attendees, $id, $occurrence)
        {
            return $attendees;
        }, 10, 3);

        usort($attendees, function ($a, $b)
        {
            return strcmp($a['name'], $b['name']);
        });

        return $attendees;
    }

    public function mysql2date($format, $date, $timezone)
    {
        if (empty($date)) return false;

        $datetime = date_create($date, $timezone);
        if (false === $datetime) return false;

        // Returns a sum of timestamp with timezone offset. Ideally should never be used.
        if ('G' === $format || 'U' === $format) return $datetime->getTimestamp() + $datetime->getOffset();

        return $datetime->format($format);
    }

    public function is_second_booking($event_id, $email)
    {
        $attendees = $this->get_event_attendees($event_id, null, false);
        if (!is_array($attendees)) $attendees = [];

        $found = false;
        foreach ($attendees as $attendee)
        {
            if ($email and isset($attendee['email']) and trim(strtolower($email)) == trim(strtolower($attendee['email'])))
            {
                $found = true;
                break;
            }
        }

        return $found;
    }

    public function get_from_mapped_field($reg_field, $default_value = '')
    {
        $current_user_id = get_current_user_id();
        if (!$current_user_id) return $default_value;

        $mapped_field = (isset($reg_field['mapping']) and trim($reg_field['mapping']) != '') ? $reg_field['mapping'] : '';
        if (!$mapped_field) return $default_value;

        $value = get_user_meta($current_user_id, $mapped_field, true);
        return ($value ? $value : $default_value);
    }

    public function get_master_location_id($event, $occurrence = null)
    {
        // Event ID
        if (is_numeric($event))
        {
            $location_id = get_post_meta($event, 'mec_location_id', true);

            // Get From Occurrence
            if ($occurrence) $location_id = MEC_feature_occurrences::param($event, $occurrence, 'location_id', $location_id);
        }
        // Event Object
        else
        {
            $meta = (isset($event->data, $event->data->meta) ? $event->data->meta : ($event->meta ?? []));
            $location_id = (isset($meta['mec_location_id'])) ? $meta['mec_location_id'] : '';

            // Get From Occurrence
            if (isset($event->date) and isset($event->date['start']) and isset($event->date['start']['timestamp'])) $location_id = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'location_id', $location_id);
        }

        if (trim($location_id) === '' or $location_id == 1) $location_id = 0;

        return apply_filters('wpml_object_id', $location_id, 'mec_location', true);
    }

    public function get_master_organizer_id($event, $occurrence = null)
    {
        // Event ID
        if (is_numeric($event))
        {
            $organizer_id = get_post_meta($event, 'mec_organizer_id', true);

            // Get From Occurrence
            if ($occurrence) $organizer_id = MEC_feature_occurrences::param($event, $occurrence, 'organizer_id', $organizer_id);
        }
        // Event Object
        else
        {
            $organizer_id = (isset($event->data) and isset($event->data->meta) and isset($event->data->meta['mec_organizer_id'])) ? $event->data->meta['mec_organizer_id'] : '';

            // Get From Occurrence
            if (isset($event->date) and isset($event->date['start']) and isset($event->date['start']['timestamp'])) $organizer_id = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'organizer_id', $organizer_id);
        }

        if (trim($organizer_id) === '' or $organizer_id == 1) $organizer_id = 0;

        $organizer_id = apply_filters('wpml_object_id', $organizer_id, 'mec_organizer', true);
        return $organizer_id;
    }

    public function get_location_data($location_id): array
    {
        $term = get_term($location_id);
        if (!isset($term->term_id) || $location_id == 1) return [];

        return [
            'id' => $term->term_id,
            'name' => $term->name,
            'address' => get_metadata('term', $term->term_id, 'address', true),
            'opening_hour' => get_metadata('term', $term->term_id, 'opening_hour', true),
            'latitude' => get_metadata('term', $term->term_id, 'latitude', true),
            'longitude' => get_metadata('term', $term->term_id, 'longitude', true),
            'url' => get_metadata('term', $term->term_id, 'url', true),
            'tel' => get_metadata('term', $term->term_id, 'tel', true),
            'thumbnail' => get_metadata('term', $term->term_id, 'thumbnail', true),
        ];
    }

    public function get_organizer_data($organizer_id)
    {
        $term = get_term($organizer_id);
        if (!isset($term->term_id) or $organizer_id == 1) return [];

        return [
            'id' => $term->term_id,
            'name' => $term->name,
            'tel' => get_metadata('term', $term->term_id, 'tel', true),
            'email' => get_metadata('term', $term->term_id, 'email', true),
            'url' => get_metadata('term', $term->term_id, 'url', true),
            'page_label' => get_metadata('term', $term->term_id, 'page_label', true),
            'thumbnail' => get_metadata('term', $term->term_id, 'thumbnail', true),
        ];
    }

    public function is_uncategorized($term_id)
    {
        $term = get_term($term_id);
        $name = strtolower($term->name);

        return ($name === 'uncategorized' or $name === esc_html__('Uncategorized'));
    }

    public function get_thankyou_page_id($event_id = null)
    {
        // Global Settings
        $settings = $this->get_settings();

        // Global Thank-You Page
        $thankyou_page_id = (isset($settings['booking_thankyou_page']) and is_numeric($settings['booking_thankyou_page']) and trim($settings['booking_thankyou_page'])) ? $settings['booking_thankyou_page'] : 0;

        // Get by Event
        if ($event_id)
        {
            $booking_options = get_post_meta($event_id, 'mec_booking', true);
            if (!is_array($booking_options)) $booking_options = [];

            $bookings_thankyou_page_inherit = $booking_options['thankyou_page_inherit'] ?? 1;
            if (!$bookings_thankyou_page_inherit)
            {
                if (isset($booking_options['booking_thankyou_page']) and $booking_options['booking_thankyou_page']) $thankyou_page_id = $booking_options['booking_thankyou_page'];
                else $thankyou_page_id = 0;
            }
        }

        return $thankyou_page_id;
    }

    public function get_thankyou_page_time($transaction_id = null)
    {
        // Global Settings
        $settings = $this->get_settings();

        // Global Time
        $thankyou_page_time = (isset($settings['booking_thankyou_page_time']) and is_numeric($settings['booking_thankyou_page_time'])) ? (int) $settings['booking_thankyou_page_time'] : 2000;

        // Get by Event
        if ($transaction_id)
        {
            // Booking
            $book = $this->getBook();
            $transaction = $book->get_transaction($transaction_id);

            $event_id = $transaction['event_id'] ?? 0;
            if ($event_id)
            {
                $booking_options = get_post_meta($event_id, 'mec_booking', true);
                if (!is_array($booking_options)) $booking_options = [];

                $bookings_thankyou_page_inherit = $booking_options['thankyou_page_inherit'] ?? 1;
                if (!$bookings_thankyou_page_inherit)
                {
                    if (isset($booking_options['booking_thankyou_page_time']) and $booking_options['booking_thankyou_page_time']) $thankyou_page_time = (int) $booking_options['booking_thankyou_page_time'];
                }
            }
        }

        return max($thankyou_page_time, 0);
    }

    public function is_first_occurrence_passed($event)
    {
        // Event ID
        if (is_numeric($event)) $event_id = $event;
        // Event Object
        else $event_id = $event->ID;

        $now = current_time('timestamp', 0);

        $db = $this->getDB();
        $first = $db->select("SELECT `tstart` FROM `#__mec_dates` WHERE `post_id`='" . $event_id . "' ORDER BY `tstart` ASC LIMIT 1", 'loadResult');

        return ($first and $first < $now);
    }

    public function preview()
    {
        // Elementor
        if (isset($_GET['action']) and sanitize_text_field($_GET['action']) === 'elementor') return true;

        // Default
        return false;
    }

    public function display_featured_image_caption($event)
    {
        if (is_numeric($event)) $event_id = $event;
        else $event_id = $event->ID;

        $caption = apply_filters('the_post_thumbnail_caption', get_the_post_thumbnail_caption($event_id));
        return (trim($caption) ? '<span class="mec-featured-image-caption">' . esc_html($caption) . '</span>' : '');
    }

    public function get_event_cost($event, $render = true)
    {
        $cost_auto_calculate = (isset($event->data->meta) and isset($event->data->meta['mec_cost_auto_calculate']) and trim($event->data->meta['mec_cost_auto_calculate'])) ? $event->data->meta['mec_cost_auto_calculate'] : 0;
        if ($cost_auto_calculate) $cost = $this->get_cheapest_ticket_price($event);
        else
        {
            $cost = (isset($event->data->meta) and isset($event->data->meta['mec_cost']) and trim($event->data->meta['mec_cost'])) ? $event->data->meta['mec_cost'] : '';
            if (isset($event->date) and isset($event->date['start']) and isset($event->date['start']['timestamp'])) $cost = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'cost', $cost);
        }

        $event_id = $event->ID;
        if (isset($event->requested_id)) $event_id = $event->requested_id;

        if (!$render) return $cost;
        return (is_numeric($cost) ? $this->render_price($cost, $event_id) : $cost);
    }

    public function get_cheapest_ticket_price($event)
    {
        $tickets = (isset($event->data->tickets) and is_array($event->data->tickets)) ? $event->data->tickets : [];

        // Booking Library
        $book = $this->getBook();

        $timestamp = isset($event->date, $event->date['start'], $event->date['start']['timestamp']) ? $event->date['start']['timestamp'] : null;

        $min = null;
        foreach ($tickets as $ticket)
        {
            $price = $book->get_ticket_price($ticket, current_time('Y-m-d'), $event->ID, $timestamp);

            if (is_null($min)) $min = $price;
            else $min = min($min, $price);
        }

        return $min;
    }

    public function get_time_components($event, $type = 'start')
    {
        $date = $event->date[$type]['date'];
        $hour = $event->date[$type]['hour'];
        $minutes = $event->date[$type]['minutes'];
        $ampm = $event->date[$type]['ampm'];

        if ($hour == '0' and $type === 'start')
        {
            $hour = 12;
            $ampm = 'AM';
        }
        else if ($hour == '0' and $type === 'end')
        {
            $hour = 12;
        }

        return [
            'date' => $date,
            'hour' => $hour,
            'minutes' => $minutes,
            'ampm' => $ampm,
        ];
    }

    public function event_date_updated($event_id, $prev_start_datetime, $prev_end_datetime)
    {
        $prev_start_timestamp = strtotime($prev_start_datetime);

        $new_start_datetime = get_post_meta($event_id, 'mec_start_datetime', true);
        $new_start_timestamp = strtotime($new_start_datetime);

        $new_end_datetime = get_post_meta($event_id, 'mec_end_datetime', true);
        $new_end_timestamp = strtotime($new_end_datetime);

        // Libraries
        $db = $this->getDB();
        $book = $this->getBook();

        $bookings = $this->get_bookings($event_id, $prev_start_timestamp, -1, null, false);
        foreach ($bookings as $booking)
        {
            $db->q("UPDATE `#__mec_bookings` SET `timestamp`='" . esc_sql($new_start_timestamp) . "', `date`='" . date('Y-m-d H:i:s', $new_start_timestamp) . "' WHERE `id`='" . esc_sql($booking->mec_booking_record_id) . "' AND `event_id`='" . esc_sql($event_id) . "'");
            $db->q("UPDATE `#__posts` SET `post_date`='" . esc_sql(date('Y-m-d H:i:s', $new_start_timestamp)) . "', `post_date_gmt`='" . esc_sql(get_gmt_from_date(date('Y-m-d H:i:s', $new_start_timestamp))) . "' WHERE `ID`='" . esc_sql($booking->ID) . "'");

            update_post_meta($booking->ID, 'mec_date', $new_start_timestamp . ':' . $new_end_timestamp);
            update_post_meta($booking->ID, 'mec_attention_time', $new_start_timestamp . ':' . $new_end_timestamp);
            update_post_meta($booking->ID, 'mec_attention_time_start', $new_start_timestamp);
            update_post_meta($booking->ID, 'mec_attention_time_end', $new_end_timestamp);

            $transaction_id = get_post_meta($booking->ID, 'mec_transaction_id', true);

            $transaction = $book->get_transaction($transaction_id);
            $transaction['date'] = $new_start_timestamp . ':' . $new_end_timestamp;

            $book->update_transaction($transaction_id, $transaction);
        }
    }

    public function get_mec_events_data($post_id)
    {
        // Cache
        $cache = $this->getCache();

        // Return From Cache
        return $cache->rememberOnce('mec-events-data-' . $post_id, function () use ($post_id)
        {
            $db = $this->getDB();
            return $db->select("SELECT * FROM `#__mec_events` WHERE `post_id`='$post_id'", "loadObject");
        });
    }

    public function sanitize_deep_array($inputs, $type = 'text', $excludes = [], $path = '')
    {
        if (!is_array($inputs)) return $inputs;

        $sanitized = [];
        foreach ($inputs as $key => $val)
        {
            $p = $path . $key . '.';
            if ((is_array($excludes) and in_array(trim($p, '. '), $excludes)) or (is_array($excludes) and !count($excludes)))
            {
                $sanitized[$key] = $val;
                continue;
            }

            if (is_array($val)) $sanitized[$key] = $this->sanitize_deep_array($val, $type, $excludes, $p);
            else if ($type == 'int') $sanitized[$key] = (int) $val;
            else if ($type == 'url') $sanitized[$key] = esc_url($val);
            else if ($type == 'email') $sanitized[$key] = sanitize_email($val);
            else if ($type == 'page') $sanitized[$key] = MEC_kses::page($val);
            else
            {
                $sanitized[$key] = sanitize_text_field($val);
            }
        }

        return $sanitized;
    }

    public function can_display_booking_progress_bar($settings)
    {
        $display_progress_bar = true;
        if (!isset($settings['booking_display_progress_bar']) || !$settings['booking_display_progress_bar']) $display_progress_bar = false;

        return $display_progress_bar;
    }

    public function display_progress_bar($event)
    {
        return $this->module('progress-bar.single', ['event' => $event]);
    }

    /**
     * @param stdClass $event
     * @param array $dates
     * @return array
     */
    public function remove_canceled_dates($event, $dates = [])
    {
        $filtered_dates = [];

        $i = 0;
        foreach ($dates as $date)
        {
            $i++;

            // Do not remove current date
            if ($i === 1)
            {
                $filtered_dates[] = $date;
                continue;
            }

            if (!isset($date['start']) or !isset($date['start']['timestamp'])) continue;

            $start_timestamp = $date['start']['timestamp'];
            if ($this->is_occurrence_canceled($event, $start_timestamp)) continue;

            $filtered_dates[] = $date;
        }

        return $filtered_dates;
    }

    public function is_occurrence_canceled($event, $start_timestamp = '')
    {
        if (is_null($start_timestamp) or !trim($start_timestamp)) return false;

        // All Params
        $params = MEC_feature_occurrences::param($event->ID, $start_timestamp, '*');

        $event_status = (isset($event->data->meta['mec_event_status']) and trim($event->data->meta['mec_event_status'])) ? $event->data->meta['mec_event_status'] : 'EventScheduled';
        $event_status = (isset($params['event_status']) and trim($params['event_status']) != '') ? $params['event_status'] : $event_status;

        if ($event_status === 'EventCancelled') return true;

        return false;
    }

    /**
     * @param $info
     * @return bool|mixed|void
     */
    public function debug_email($info)
    {
        // Convert to String
        if (is_array($info) || is_object($info)) $info = print_r($info, true);

        // Global Settings
        $settings = $this->get_settings();

        // Receiver
        $to = $settings['gateways_debug_email'] ?? '';

        // Receiver is not valid
        if (!trim($to) || !is_email($to)) return;

        $subject = 'MEC - PayPal Standard Debug: ' . current_time('Y-m-d H:i:s');
        $message = 'Timestamp: ' . current_time('Y-m-d H:i:s') . "\n\n\n" . $info;

        // Add Request to Message
        $message .= "\n\n\n" . '[Request]' . "\n\n" . print_r($_REQUEST, true);

        return wp_mail($to, $subject, $message);
    }

    public function get_event_color_dot($event, $only_color_code = false)
    {
        $category_color = '';
        if (is_object($event) and isset($event->data->categories) and is_array($event->data->categories) and count($event->data->categories))
        {
            foreach ($event->data->categories as $category)
            {
                $category_color = (isset($category['color']) && trim($category['color'])) ? $category['color'] : '';
                if ($category_color) break;
            }
        }

        $event_color = (isset($event->data, $event->data->meta, $event->data->meta['mec_color']) and trim($event->data->meta['mec_color'])) ? '#' . $event->data->meta['mec_color'] : '';

        // Event Color has more priority
        $color = trim($event_color) ? $event_color : $category_color;

        // Only color code
        if ($only_color_code) return $color;

        // No Color
        if (trim($color) === '') return '';
        return '<span class="event-color" style="background: ' . esc_attr($color) . '"></span>';
    }

    /**
     * @param $attendee_id
     * @param $template
     * @return string
     */
    public function get_certificate_link($attendee_id, $template)
    {
        $link = get_permalink($template);
        $record = $this->get_mec_attendee_record($attendee_id);

        $key = $attendee_id . '-' . $record->mec_booking_id . '-' . $record->transaction_id . '-' . $record->ticket_id;

        return $this->add_qs_var('key', $key, $link);
    }

    /**
     * @param $attendee_id
     * @return mixed
     */
    public function get_mec_attendee_record($attendee_id)
    {
        return $this->getDB()
            ->select("SELECT a.*, b.booking_id, b.transaction_id, b.event_id, b.ticket_ids, b.status, b.confirmed, b.verified, b.date, b.timestamp FROM `#__mec_booking_attendees` AS a LEFT JOIN `#__mec_bookings` AS b ON a.`mec_booking_id` = b.`id` WHERE a.`id`='" . esc_sql($attendee_id) . "'", 'loadObject');
    }

    /**
     * @param $attendees
     * @param $event_id
     * @param $occurrence
     * @param bool $checkbox
     * @return string
     */
    public function get_attendees_table($attendees, $event_id, $occurrence, $checkbox = true)
    {
        // Database
        $db = $this->getDB();

        // Settings
        $settings = $this->get_settings();

        $tickets = get_post_meta($event_id, 'mec_tickets', true);

        $html = '<div class="w-clearfix mec-attendees-head">
            ' . ($checkbox ? '<div class="w-col-xs-1">
                <span>
                    <input type="checkbox" id="mec-send-email-check-all" onchange="mec_send_email_check_all(this);">
                </span>
            </div>' : '')
            . '
            <div class="w-col-xs-3 name">
                <span>' . esc_html__('Name', 'modern-events-calendar-lite') . '</span>
            </div>
            <div class="w-col-xs-2 email">
                <span>' . esc_html__('Email', 'modern-events-calendar-lite') . '</span>
            </div>
            <div class="w-col-xs-3 ticket">
                <span>' . esc_html($this->m('ticket', esc_html__('Ticket', 'modern-events-calendar-lite'))) . '</span>
            </div>
            <div class="w-col-xs-' . ($checkbox ? 2 : 3) . '">
                <span>' . esc_html__('Variations', 'modern-events-calendar-lite') . '</span>
            </div>';

        $html = apply_filters('mec_attendees_list_header_html', $html, $event_id, $occurrence);
        $html .= '</div>';

        // User
        $u = $this->getUser();

        foreach ($attendees as $attendee)
        {
            $mec_attendee_id = '';

            // Attendee ID for Certificate
            if ($occurrence && isset($settings['certificate_status']) && $settings['certificate_status'] && isset($attendee['id']) && isset($attendee['book_id']) && isset($attendee['email']))
            {
                $attendee_user_id = $u->by_email($attendee['email']);
                if ($attendee_user_id && isset($attendee_user_id->ID))
                {
                    $mec_booking_id = $db->select("SELECT `id` FROM `#__mec_bookings` WHERE `event_id`='" . esc_sql($event_id) . "' AND `booking_id`='" . esc_sql($attendee['book_id']) . "' AND `timestamp`='" . esc_sql($occurrence) . "' ORDER BY `id` ASC LIMIT 1", 'loadResult');
                    $mec_attendee_id = $db->select("SELECT `id` FROM `#__mec_booking_attendees` WHERE `mec_booking_id`='" . esc_sql($mec_booking_id) . "' AND `user_id`='" . $attendee_user_id->ID . "' AND `ticket_id`='" . esc_sql($attendee['id']) . "'", "loadResult");
                }
            }

            $html .= '<div class="w-clearfix mec-attendees-content">';
            if ($checkbox) $html .= '<div class="w-col-xs-1"><input type="checkbox" data-attendee-id="' . esc_attr($mec_attendee_id) . '" data-book_attendee_key="' . $attendee['book_id'] . '-' . $attendee['key'] . '" onchange="mec_send_email_check(this);" /><span class="mec-util-hidden mec-send-email-attendee-info">' . esc_html($attendee['name'] . ':.:' . $attendee['email']) . ',</span></div>';
            $html .= '<div class="w-col-xs-3 name">' . get_avatar($attendee['email']) . $attendee['name'] . '</div>';
            $html .= '<div class="w-col-xs-2 email">' . esc_html($attendee['email']) . '</div>';
            $html .= '<div class="w-col-xs-3 ticket">' . ((isset($attendee['id']) and isset($tickets[$attendee['id']]['name'])) ? $tickets[$attendee['id']]['name'] : esc_html__('Unknown', 'modern-events-calendar-lite')) . '</div>';

            $variations = '<div class="w-col-xs-' . ($checkbox ? 2 : 3) . '">';
            if (isset($attendee['variations']) and is_array($attendee['variations']) and count($attendee['variations']))
            {
                $ticket_variations = $this->ticket_variations($event_id, $attendee['id']);

                foreach ($attendee['variations'] as $variation_id => $variation_count)
                {
                    if (!$variation_count || $variation_count < 0) continue;

                    $variation_title = (isset($ticket_variations[$variation_id]) and isset($ticket_variations[$variation_id]['title'])) ? $ticket_variations[$variation_id]['title'] : '';
                    if (!trim($variation_title)) continue;

                    $variations .= '<span>+ ' . esc_html($variation_title) . '</span>
                        <span>(' . esc_html($variation_count) . ')</span>';
                }
            }

            $variations .= '</div>';

            $html .= $variations;
            $html = apply_filters('mec_attendees_list_html', $html, $attendee, $attendee['key'], $attendee['book_id'], $occurrence);
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * @param array $dates
     * @param stdClass $event
     * @return array
     */
    public function maybe_use_last_date($dates, $event)
    {
        // Only when one date is found
        if (count($dates) === 1 && isset($dates[0]['end']['timestamp']) && $dates[0]['end']['timestamp'])
        {
            // Event repeat status
            $repeat_status = $event->data->meta['mec_repeat_status'] ?? 0;
            $current = current_time('timestamp');

            // First and only date is expired
            if ($repeat_status && $current > $dates[0]['end']['timestamp'])
            {
                // DB
                $db = $this->getDB();

                // First Upcoming
                $mec_date = $db->select("SELECT dstart, dend FROM `#__mec_dates` WHERE `post_id`='" . esc_sql($event->ID) . "' AND `tstart` >= '".esc_sql($current)."' ORDER BY `id` ASC LIMIT 1", 'loadObject');

                // Last Date
                if (!isset($mec_date->dstart)) $mec_date = $db->select("SELECT dstart, dend FROM `#__mec_dates` WHERE `post_id`='" . esc_sql($event->ID) . "' ORDER BY `id` DESC LIMIT 1", 'loadObject');

                if (isset($mec_date->dstart, $mec_date->dend))
                {
                    // Render Library
                    $render = $this->getRender();

                    $dates = [];
                    $dates[] = $render->add_timestamps([
                        'start' => [
                            'date' => $mec_date->dstart,
                            'hour' => $event->data->meta['mec_date']['start']['hour'],
                            'minutes' => $event->data->meta['mec_date']['start']['minutes'],
                            'ampm' => $event->data->meta['mec_date']['start']['ampm'],
                        ],
                        'end' => [
                            'date' => $mec_date->dend,
                            'hour' => $event->data->meta['mec_date']['end']['hour'],
                            'minutes' => $event->data->meta['mec_date']['end']['minutes'],
                            'ampm' => $event->data->meta['mec_date']['end']['ampm'],
                        ],
                        'allday' => $event->data->meta['mec_allday'] ?? 0,
                        'hide_time' => $event->data->meta['mec_hide_time'] ?? 0,
                        'past' => 1,
                    ]);
                }
            }
        }

        return $dates;
    }

    public function generate_download_csv($rows, $filename)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $delimiter = "\t";
        $output = fopen('php://output', 'w');

        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        foreach ($rows as $row)
        {
            fputcsv($output, $row, $delimiter);
        }
    }

    public function generate_download_excel($rows, $filename)
    {
        include_once MEC_ABSPATH . 'app' . DS . 'api' . DS . 'XLSX' . DS . 'xlsxwriter.class.php';

        $writer = new MEC_XLSXWriter();
        $writer->writeSheet($rows);
        $writer->writeToFile($filename);

        // Download
        if (file_exists($filename))
        {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filename));

            readfile($filename);
            unlink($filename);
            exit;
        }
    }

    /**
     * @param array $icons
     * @return MEC_icons
     */
    public function icons(array $icons = []): MEC_icons
    {
        // Import Library
        MEC::import('app.libraries.icons');

        return (new MEC_icons($icons));
    }

    public function add_global_exceptional_days($days = [])
    {
        // Force Array
        if (!is_array($days)) $days = [];

        // MEC Settings
        $settings = $this->get_settings();

        // Global Exceptional Days
        $global = isset($settings['global_exceptional_days']) && is_array($settings['global_exceptional_days']) ? $settings['global_exceptional_days'] : [];

        foreach ($global as $key => $day)
        {
            if (!is_numeric($key)) continue;
            $days[] = $this->standardize_format($day);
        }

        return array_unique($days);
    }

    public function get_organizer_id_by_email($email)
    {
        $db = $this->getDB();
        $term_ids = $db->select("SELECT term_id FROM `#__termmeta` WHERE `meta_key`='email' AND `meta_value`='" . esc_sql($email) . "' LIMIT 20", 'loadColumn');

        $organizer_id = '';
        foreach ($term_ids as $term_id)
        {
            $term = get_term((int) $term_id);
            if ($term->taxonomy === 'mec_organizer')
            {
                $organizer_id = $term->term_id;
                break;
            }
        }

        return $organizer_id;
    }

    /**
     * @param $event_id
     * @return bool
     */
    public function hide_end_time_status($event_id): bool
    {
        // Settings
        $settings = $this->get_settings();

        // Global Status
        $hide_end_time_global = isset($settings['hide_event_end_time']) && $settings['hide_event_end_time'];
        if ($hide_end_time_global) return true;

        $event_status = get_post_meta($event_id, 'mec_hide_end_time', true);
        if (is_null($event_status) || (is_string($event_status) && trim($event_status) === '')) $event_status = 0;

        return (bool) $event_status;
    }

    /**
     * @param $start_date
     * @param $end_date
     * @return int
     */
    public function get_days_diff($start_date, $end_date)
    {
        $event_period = $this->date_diff($start_date, $end_date);
        return $event_period ? $event_period->days : 0;
    }

    /**
     * @param $event_id
     * @param $occurrence
     * @param $occurrence_time
     * @return array
     */
    public function get_start_date_to_get_event_dates($event_id, $occurrence, $occurrence_time = null)
    {
        $repeat_type = get_post_meta($event_id, 'mec_repeat_type', true);

        $md_start = $this->get_start_of_multiple_days($event_id, $occurrence);
        if ($md_start) $occurrence = $md_start;

        $md_start_time = $this->get_start_time_of_multiple_days($event_id, $occurrence_time);
        if ($md_start_time) $occurrence_time = $md_start_time;

        if (strtotime($occurrence) and in_array($repeat_type, ['certain_weekdays', 'custom_days', 'weekday', 'weekend'])) $occurrence = date('Y-m-d', strtotime($occurrence));
        else if (strtotime($occurrence))
        {
            $new_occurrence = date('Y-m-d', strtotime('-1 day', strtotime($occurrence)));
            if ($repeat_type == 'monthly' and date('m', strtotime($new_occurrence)) != date('m', strtotime($occurrence))) $new_occurrence = date('Y-m-d', strtotime($occurrence));

            $occurrence = $new_occurrence;
        }
        else $occurrence = null;

        if ($occurrence && $repeat_type === 'custom_days') $occurrence = date('Y-m-d 00:00:00', strtotime($occurrence));

        return [$occurrence, $occurrence_time];
    }

    public function adjust_event_dates_for_booking($event, $dates, $occurrence = null)
    {
        // Remove First Date if it is already started!
        if (count($dates) > 1 && !$occurrence)
        {
            $all_dates = $dates;

            // Global Settings
            $settings = $this->get_settings();

            foreach ($dates as $d => $date)
            {
                $start_date = (isset($date['start']) and isset($date['start']['date'])) ? $date['start']['date'] : current_time('Y-m-d H:i:s');
                $end_date = (isset($date['end']) and isset($date['end']['date'])) ? $date['end']['date'] : current_time('Y-m-d H:i:s');

                $s_time = sprintf("%02d", $date['start']['hour']) . ':';
                $s_time .= sprintf("%02d", $date['start']['minutes']);
                $s_time .= trim($date['start']['ampm']);

                $start_time = date('D M j Y G:i:s', strtotime($start_date . ' ' . $s_time));

                $e_time = sprintf("%02d", $date['end']['hour']) . ':';
                $e_time .= sprintf("%02d", $date['end']['minutes']);
                $e_time .= trim($date['end']['ampm']);

                $end_time = date('D M j Y G:i:s', strtotime($end_date . ' ' . $e_time));

                $d1 = new DateTime($start_time);
                $d2 = new DateTime(current_time("D M j Y G:i:s"));
                $d3 = new DateTime($end_time);

                // Booking OnGoing Event Option
                $ongoing_event_book = isset($settings['booking_ongoing']) && $settings['booking_ongoing'] == '1';
                if ($ongoing_event_book)
                {
                    if ($d3 < $d2)
                    {
                        unset($dates[$d]);
                    }
                }
                else
                {
                    if ($d1 < $d2)
                    {
                        unset($dates[$d]);
                    }
                }
            }

            if (count($dates) === 0) $dates = [end($all_dates)];
        }

        $dates = $this->remove_canceled_dates($event, array_values($dates));
        return $this->maybe_use_last_date($dates, $event);
    }

    public function display_not_found_message($echo = true)
    {
        // Default Message
        $message = esc_html__('No event found!', 'modern-events-calendar-lite');

        // Global Settings
        $settings = $this->get_settings();

        // Get Message from Settings
        if (isset($settings['not_found_message']) && trim($settings['not_found_message'])) $message = stripslashes($settings['not_found_message']);

        // Display the Message
        if ($echo) echo $message;
        else return $message;
    }

    public function random_string_generator($length = 12)
    {
        return wp_generate_password($length, false);
    }

    public function get_days_in_previous_month($month, $year)
    {
        return date('t', strtotime('-1 month', strtotime($year . '-' . $month . '-10')));
    }

    public function is_new_version_available()
    {
        $url = add_query_arg(
            ['category' => 'mec'],
            MEC_API_UPDATE . '/updates/?action=get_metadata&slug=modern-events-calendar-lite'
        );

        $response = wp_remote_get($url);
        $status = wp_remote_retrieve_response_code($response);

        if ($status !== 200) return false;

        $body = wp_remote_retrieve_body($response);
        if ($body === '') return false;

        $JSON = json_decode($body);
        if (!is_object($JSON) || !isset($JSON->version)) return false;

        if ($JSON->version > MEC_VERSION) return $JSON->version;

        return false;
    }

    public function get_book_datetime_string($timestamps, $event_id, $book_id)
    {
        [$start_timestamp, $end_timestamp] = explode(':', $timestamps);

        // Date & Time Format
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');

        $allday = get_post_meta($event_id, 'mec_allday', true);
        $hide_time = get_post_meta($event_id, 'mec_hide_time', true);
        $hide_end_time = $this->hide_end_time_status($event_id);

        if (trim($timestamps) && strpos($timestamps, ':') !== false)
        {
            if (trim($start_timestamp) != trim($end_timestamp))
            {
                return sprintf(esc_html__('%s to %s', 'modern-events-calendar-lite'), $this->date_i18n($date_format . ((!$allday and !$hide_time) ? ' ' . $time_format : ''), $start_timestamp), $this->date_i18n($date_format . ((!$allday and !$hide_time and !$hide_end_time) ? ' ' . $time_format : ''), $end_timestamp));
            }
            else return get_the_date($date_format . ((!$allday and !$hide_time) ? ' ' . $time_format : ''), $book_id);
        }

        return get_the_date($date_format . ((!$allday and !$hide_time) ? ' ' . $time_format : ''), $book_id);
    }

    public function convert_term_name_to_id($names, $taxonomy)
    {
        if (!is_array($names))
        {
            $term = get_term_by('name', $names, $taxonomy);
            return $term ? $term->term_id : null;
        }

        $ids = [];
        foreach ($names as $name)
        {
            $id = $this->convert_term_name_to_id($name, $taxonomy);
            if (!$id) continue;

            $ids[] = $id;
        }

        return $ids;
    }

    public function is_mobile()
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4));
    }

    public function get_raw_post_description($post_id)
    {
        if (function_exists('kc_add_map'))
        {
            $post = get_post($post_id);
            return strip_tags(strip_shortcodes(apply_filters('the_content', $post->post_content)));
        }

        return strip_tags(strip_shortcodes(get_post_field('post_content', $post_id)));
    }

    public static function str_random($length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $count = strlen($characters);

        $string = '';
        for ($i = 0; $i < $length; $i++) $string .= $characters[rand(0, $count - 1)];

        return $string;
    }
}
