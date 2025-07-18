<?php
/** no direct access **/
defined('MECEXEC') or die();

use MEC\FES\FormBuilder;

/**
 * Webnus MEC events class.
 *
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_events extends MEC_base
{
    public $factory;
    public $main;
    public $db;
    public $PT;
    public $settings;
    public $render;

    /**
     * Constructor method
     *
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // Import MEC Factory
        $this->factory = $this->getFactory();

        // Import MEC Main
        $this->main = $this->getMain();

        // Import MEC DB
        $this->db = $this->getDB();

        // MEC Post Type Name
        $this->PT = $this->main->get_main_post_type();

        // MEC Settings
        $this->settings = $this->main->get_settings();
    }

    /**
     * Initialize events feature
     *
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        $this->factory->action('init', [$this, 'register_post_type']);
        $this->factory->action('mec_category_add_form_fields', [$this, 'add_category_fields'], 10, 2);
        $this->factory->action('mec_category_edit_form_fields', [$this, 'edit_category_fields'], 10, 2);
        $this->factory->action('edited_mec_category', [$this, 'save_metadata']);
        $this->factory->action('created_mec_category', [$this, 'save_metadata']);

        $this->factory->action('init', [$this, 'register_endpoints']);
        $this->factory->action('add_meta_boxes_' . $this->PT, [$this, 'remove_taxonomies_metaboxes']);
        $this->factory->action('save_post', [$this, 'save_event'], 10);
        $this->factory->action('edit_post', [$this, 'quick_edit'], 10);
        $this->factory->action('delete_post', [$this, 'delete_event'], 10);
        $this->factory->action('transition_post_status', [$this, 'event_published'], 10, 3);

        $this->factory->filter('post_row_actions', [$this, 'action_links'], 10, 2);
        $this->factory->action('admin_init', [$this, 'duplicate_event']);

        $this->factory->action('add_meta_boxes', [$this, 'register_meta_boxes'], 1);
        $this->factory->action('restrict_manage_posts', [$this, 'add_filters']);
        $this->factory->action('manage_posts_extra_tablenav', [$this, 'add_buttons']);
        if (is_admin()) $this->factory->action('pre_get_posts', [$this, 'filter']);

        $this->factory->action('mec_metabox_details', [$this, 'meta_box_nonce'], 10);
        $this->factory->action('mec_metabox_details', [$this, 'meta_box_dates'], 20);
        $this->factory->action('mec_metabox_details', [$this, 'meta_box_hourly_schedule'], 30);
        $this->factory->action('mec_metabox_details', [$this, 'meta_box_links'], 40);
        $this->factory->action('mec_metabox_details', [$this, 'meta_box_cost'], 50);
        $this->factory->action('mec_metabox_details', [$this, 'meta_box_fields'], 60);

        // Hourly Schedule for FES
        if (!isset($this->settings['fes_section_hourly_schedule']) or (isset($this->settings['fes_section_hourly_schedule']) and $this->settings['fes_section_hourly_schedule']))
        {
            $this->factory->action('mec_fes_metabox_details', [$this, 'meta_box_hourly_schedule'], 30);
        }

        // Data Fields for FES
        if (!isset($this->settings['fes_section_data_fields']) or (isset($this->settings['fes_section_data_fields']) and $this->settings['fes_section_data_fields']))
        {
            $this->factory->action('mec_fes_metabox_details', [$this, 'meta_box_fields'], 20);
        }

        // Show exceptional days if enabled
        if (isset($this->settings['exceptional_days']) and $this->settings['exceptional_days'])
        {
            $this->factory->action('mec_metabox_details', [$this, 'meta_box_exceptional_days'], 25);
            $this->factory->action('mec_fes_metabox_details', [$this, 'meta_box_exceptional_days'], 25);
        }

        // Show Booking meta box only if booking module is enabled
        $booking_status = (isset($this->settings['booking_status']) and $this->settings['booking_status']);
        if ($booking_status)
        {
            $this->factory->action('mec_metabox_booking', [$this, 'meta_box_booking_options'], 5);
            $this->factory->action('mec_metabox_booking', [$this, 'meta_box_tickets']);
            $this->factory->action('mec_metabox_booking', [$this, 'meta_box_regform'], 20);
            $this->factory->action('mec_metabox_booking', [$this, 'meta_box_attendees'], 22);
            $this->factory->action('wp_ajax_mec_event_bookings', [$this, 'mec_event_bookings'], 23);
            $this->factory->action('wp_ajax_mec_move_bookings', [$this, 'mec_move_bookings'], 24);
            $this->factory->action('wp_ajax_mec_manage_bookings', [$this, 'mec_manage_bookings'], 25);

            if (!isset($this->settings['fes_section_booking']) or (isset($this->settings['fes_section_booking']) and $this->settings['fes_section_booking']))
            {
                // Booking Options for FES
                if (!isset($this->settings['fes_section_booking']) or (isset($this->settings['fes_section_booking']) and $this->settings['fes_section_booking'])) $this->factory->action('mec_fes_metabox_details', [$this, 'meta_box_booking_options'], 35);

                // Ticket Options for FES
                if (!isset($this->settings['fes_section_tickets']) or (isset($this->settings['fes_section_tickets']) and $this->settings['fes_section_tickets'])) $this->factory->action('mec_fes_metabox_details', [$this, 'meta_box_tickets'], 40);

                // Registration Form for FES
                if (!isset($this->settings['fes_section_reg_form']) or (isset($this->settings['fes_section_reg_form']) and $this->settings['fes_section_reg_form'])) $this->factory->action('mec_fes_metabox_details', [$this, 'meta_box_regform'], 45);

                // Attendees for FES
                if (!isset($this->settings['fes_section_booking_att']) or (isset($this->settings['fes_section_booking_att']) and $this->settings['fes_section_booking_att'])) $this->factory->action('mec_fes_metabox_details', [$this, 'meta_box_attendees'], 48);
            }
        }

        // Show fees meta box only if fees module is enabled
        if (isset($this->settings['taxes_fees_status']) and $this->settings['taxes_fees_status'])
        {
            $this->factory->action('mec_metabox_booking', [$this, 'meta_box_fees'], 15);

            // Fees for FES
            if (!isset($this->settings['fes_section_booking']) or (isset($this->settings['fes_section_booking']) and $this->settings['fes_section_booking']))
            {
                if ($booking_status and (!isset($this->settings['fes_section_fees']) or (isset($this->settings['fes_section_fees']) and $this->settings['fes_section_fees'])))
                {
                    $this->factory->action('mec_fes_metabox_details', [$this, 'meta_box_fees'], 45);
                }
            }
        }

        // Show ticket variations meta box only if the module is enabled
        if ($booking_status and isset($this->settings['ticket_variations_status']) and $this->settings['ticket_variations_status'])
        {
            $this->factory->action('mec_metabox_booking', [$this, 'meta_box_ticket_variations'], 16);

            // Ticket Variations for FES
            if (!isset($this->settings['fes_section_booking']) or (isset($this->settings['fes_section_booking']) and $this->settings['fes_section_booking']))
            {
                if (!isset($this->settings['fes_section_ticket_variations']) or (isset($this->settings['fes_section_ticket_variations']) and $this->settings['fes_section_ticket_variations']))
                {
                    $this->factory->action('mec_fes_metabox_details', [$this, 'meta_box_ticket_variations'], 46);
                }
            }
        }

        $this->factory->filter('manage_' . $this->PT . '_posts_columns', [$this, 'filter_columns']);
        $this->factory->filter('manage_edit-' . $this->PT . '_sortable_columns', [$this, 'filter_sortable_columns']);
        $this->factory->action('manage_' . $this->PT . '_posts_custom_column', [$this, 'filter_columns_content'], 10, 2);

        $this->factory->action('admin_footer-edit.php', [$this, 'add_bulk_actions']);
        $this->factory->action('load-edit.php', [$this, 'do_bulk_actions']);
        $this->factory->action('pre_post_update', [$this, 'bulk_edit'], 10);

        // WPML Duplicate
        $this->factory->action('icl_make_duplicate', [$this, 'icl_duplicate'], 10, 4);
        $this->factory->action('icl_pro_translation_saved', [$this, 'wpml_pro_translation_saved'], 10, 3);

        // Image Fallback
        if (isset($this->settings['fallback_featured_image_status']) and $this->settings['fallback_featured_image_status'])
        {
            $this->factory->filter('get_post_metadata', [$this, 'set_fallback_image_id'], 10, 4);
            $this->factory->filter('post_thumbnail_html', [$this, 'show_fallback_image'], 20, 5);
        }

        // Event Gallery
        if (isset($this->settings['event_gallery_status']) and $this->settings['event_gallery_status'])
        {
            // AJAX
            $this->factory->action('wp_ajax_mec_event_gallery_image_upload', [$this, 'gallery_image_upload']);
            $this->factory->action('wp_ajax_nopriv_mec_event_gallery_image_upload', [$this, 'gallery_image_upload']);
        }

        // Related Events Per Event
        if (isset($this->settings['related_events_per_event']) and $this->settings['related_events_per_event'])
        {
            $this->factory->action('mec_metabox_details', [$this, 'meta_box_related_events'], 17);

            // Related Events Per Event for FES
            if (!isset($this->settings['fes_section_related_events']) or (isset($this->settings['fes_section_related_events']) and $this->settings['fes_section_related_events']))
            {
                $this->factory->action('mec_fes_metabox_details', [$this, 'meta_box_related_events'], 33);
            }
        }

        // Event Banner
        if (isset($this->settings['banner_status']) && $this->settings['banner_status'] && (!isset($this->settings['banner_force_featured_image']) || !$this->settings['banner_force_featured_image']))
        {
            $this->factory->action('mec_metabox_details', [$this, 'meta_box_banner'], 18);

            // Event Banner for FES
            if (!isset($this->settings['fes_section_banner']) or (isset($this->settings['fes_section_banner']) and $this->settings['fes_section_banner']))
            {
                $this->factory->action('mec_fes_metabox_details', [$this, 'meta_box_banner'], 34);
            }
        }

        // Timezone Notice
        $timezone_string = get_option('timezone_string');
        if (trim($timezone_string) === '')
        {
            add_action('admin_notices', function ()
            {
                echo '<div class="notice notice-warning is-dismissible">
                    <p>' . esc_html__('It is advisable to utilize a geographic timezone, such as "America/Los_Angeles" instead of a UTC timezone offset, like "UTC+0," while using The Modern Events Calendar. The latter may cause issues when importing events or with Daylight Saving Time.', 'modern-events-calendar-lite') . '</p>
                </div>';
            });
        }
    }

    /**
     * Registers events post type and assign it to some taxonomies
     *
     * @author Webnus <info@webnus.net>
     */
    public function register_post_type()
    {
        // Get supported features for event post type
        $supports = apply_filters('mec_event_supports', ['editor', 'title', 'excerpt', 'author', 'thumbnail', 'comments']);

        $args = apply_filters(
            'mec_event_register_post_type_args',
            [
                'labels' => [
                    'name' => esc_html__('Events', 'modern-events-calendar-lite'),
                    'singular_name' => esc_html__('Event', 'modern-events-calendar-lite'),
                    'add_new' => esc_html__('Add Event', 'modern-events-calendar-lite'),
                    'add_new_item' => esc_html__('Add New Event', 'modern-events-calendar-lite'),
                    'not_found' => esc_html__('No events found!', 'modern-events-calendar-lite'),
                    'all_items' => esc_html__('All Events', 'modern-events-calendar-lite'),
                    'edit_item' => esc_html__('Edit Event', 'modern-events-calendar-lite'),
                    'view_item' => esc_html__('View Event', 'modern-events-calendar-lite'),
                    'not_found_in_trash' => esc_html__('No events found in Trash!', 'modern-events-calendar-lite'),
                ],
                'public' => true,
                'has_archive' => ($this->main->get_archive_status() ? true : false),
                'menu_icon' => plugin_dir_url(__FILE__) . '../../assets/img/mec.svg',
                'menu_position' => 26,
                'show_in_menu' => 'mec-intro',
                'rewrite' => [
                    'slug' => $this->main->get_main_slug(),
                    'ep_mask' => EP_MEC_EVENTS,
                    'with_front' => false,
                ],
                'supports' => $supports,
                'show_in_rest' => true,

            ]
        );
        register_post_type($this->PT, $args);

        $singular_label = $this->main->m('taxonomy_category', esc_html__('Category', 'modern-events-calendar-lite'));
        $plural_label = $this->main->m('taxonomy_categories', esc_html__('Categories', 'modern-events-calendar-lite'));

        $category_args = apply_filters(
            'mec_register_taxonomy_args',
            [
                'label' => $plural_label,
                'labels' => [
                    'name' => $plural_label,
                    'singular_name' => $singular_label,
                    'all_items' => sprintf(esc_html__('All %s', 'modern-events-calendar-lite'), $plural_label),
                    'edit_item' => sprintf(esc_html__('Edit %s', 'modern-events-calendar-lite'), $singular_label),
                    'view_item' => sprintf(esc_html__('View %s', 'modern-events-calendar-lite'), $singular_label),
                    'update_item' => sprintf(esc_html__('Update %s', 'modern-events-calendar-lite'), $singular_label),
                    'add_new_item' => sprintf(esc_html__('Add New %s', 'modern-events-calendar-lite'), $singular_label),
                    'new_item_name' => sprintf(esc_html__('New %s Name', 'modern-events-calendar-lite'), $singular_label),
                    'popular_items' => sprintf(esc_html__('Popular %s', 'modern-events-calendar-lite'), $plural_label),
                    'search_items' => sprintf(esc_html__('Search %s', 'modern-events-calendar-lite'), $plural_label),
                ],
                'public' => true,
                'show_ui' => true,
                'show_in_rest' => true,
                'hierarchical' => true,
                'has_archive' => true,
                'rewrite' => ['slug' => $this->main->get_category_slug()],
            ],
            'mec_category'
        );

        register_taxonomy(
            'mec_category',
            $this->PT,
            $category_args
        );

        register_taxonomy_for_object_type('mec_category', $this->PT);
    }

    /**
     * Register meta field to taxonomies
     *
     * @author Webnus <info@webnus.net>
     */
    public function add_category_fields()
    {
        add_thickbox();

        // Fallback Status
        $fallback = (isset($this->settings['fallback_featured_image_status']) and $this->settings['fallback_featured_image_status']);
        ?>
        <div class="form-field">
            <label for="mec_cat_icon"><?php esc_html_e('Category Icon', 'modern-events-calendar-lite'); ?></label>
            <input type="hidden" name="mec_cat_icon" id="mec_cat_icon" value=""/>
            <a href="<?php echo esc_url($this->main->asset('icon.html')); ?>?&width=680&height=450&inlineId=my-content-id"
               class="thickbox mec_category_icon button"><?php echo esc_html__('Select icon', 'modern-events-calendar-lite'); ?></a>
        </div>
        <div class="form-field">
            <label for="mec_cat_color"><?php esc_html_e('Color', 'modern-events-calendar-lite'); ?></label>
            <input type="text" name="mec_cat_color" id="mec_cat_color" class="mec-color-picker"/>
            <p class="description"><?php esc_html_e('Optional category color', 'modern-events-calendar-lite'); ?></p>
        </div>
        <?php if ($fallback): ?>
        <div class="form-field">
            <label for="mec_thumbnail_button"><?php esc_html_e('Fallback Image', 'modern-events-calendar-lite'); ?></label>
            <div id="mec_thumbnail_img"></div>
            <input type="hidden" name="fallback" id="mec_thumbnail" value=""/>
            <button type="button" class="mec_upload_image_button button"
                    id="mec_thumbnail_button"><?php echo esc_html__('Upload/Add image', 'modern-events-calendar-lite'); ?></button>
            <button type="button"
                    class="mec_remove_image_button button mec-util-hidden"><?php echo esc_html__('Remove', 'modern-events-calendar-lite'); ?></button>
        </div>
    <?php endif; ?>
        <?php
    }

    /**
     * Edit icon meta for categories
     *
     * @author Webnus <info@webnus.net>
     */
    public function edit_category_fields($term)
    {
        add_thickbox();

        // Fallback Status
        $fallback = (isset($this->settings['fallback_featured_image_status']) and $this->settings['fallback_featured_image_status']);

        // Fallback Image
        $fallback_image = get_metadata('term', $term->term_id, 'mec_cat_fallback_image', true);

        // Icon
        $icon = get_metadata('term', $term->term_id, 'mec_cat_icon', true);

        // Color
        $color = get_metadata('term', $term->term_id, 'mec_cat_color', true);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_cat_icon"><?php esc_html_e('Category Icon', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="hidden" name="mec_cat_icon" id="mec_cat_icon" value="<?php echo esc_attr($icon); ?>"/>
                <a href="<?php echo esc_url($this->main->asset('icon.html')); ?>?&width=680&height=450&inlineId=my-content-id"
                   class="thickbox mec_category_icon button"><?php echo esc_html__('Select icon', 'modern-events-calendar-lite'); ?></a>
                <?php if (isset($icon)) : ?>
                    <div class="mec-webnus-icon"><i class="<?php echo esc_attr($icon); ?> mec-color"></i></div>
                <?php endif; ?>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_cat_color"><?php esc_html_e('Color', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="text" name="mec_cat_color" id="mec_cat_color" value="<?php echo esc_attr($color); ?>"
                       data-default-color="<?php echo esc_attr($color); ?>" class="mec-color-picker"/>
                <p class="description"><?php esc_html_e('Optional category color', 'modern-events-calendar-lite'); ?></p>
            </td>
        </tr>
        <?php if ($fallback): ?>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_thumbnail_button"><?php esc_html_e('Fallback Image', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <div
                    id="mec_thumbnail_img"><?php if (trim($fallback_image) != '') echo '<img src="' . esc_url($fallback_image) . '" />'; ?></div>
                <input type="hidden" name="fallback" id="mec_thumbnail"
                       value="<?php echo esc_attr($fallback_image); ?>"/>
                <button type="button" class="mec_upload_image_button button"
                        id="mec_thumbnail_button"><?php echo esc_html__('Upload/Add image', 'modern-events-calendar-lite'); ?></button>
                <button type="button"
                        class="mec_remove_image_button button <?php echo(!trim($fallback_image) ? 'mec-util-hidden' : ''); ?>"><?php echo esc_html__('Remove', 'modern-events-calendar-lite'); ?></button>
            </td>
        </tr>
    <?php endif; ?>
        <?php
    }

    /**
     * Save meta data for mec categories
     *
     * @param int $term_id
     * @author Webnus <info@webnus.net>
     */
    public function save_metadata($term_id)
    {
        // Quick Edit
        if (!isset($_POST['mec_cat_icon'])) return;

        $icon = sanitize_text_field($_POST['mec_cat_icon']);
        update_term_meta($term_id, 'mec_cat_icon', $icon);

        $color = isset($_POST['mec_cat_color']) ? sanitize_text_field($_POST['mec_cat_color']) : '';
        update_term_meta($term_id, 'mec_cat_color', $color);

        $fallback = isset($_POST['fallback']) ? sanitize_text_field($_POST['fallback']) : '';
        update_term_meta($term_id, 'mec_cat_fallback_image', $fallback);
    }

    public function register_endpoints()
    {
        add_rewrite_endpoint('verify', EP_MEC_EVENTS);
        add_rewrite_endpoint('cancel', EP_MEC_EVENTS);
        add_rewrite_endpoint('gateway-cancel', EP_MEC_EVENTS);
        add_rewrite_endpoint('gateway-return', EP_MEC_EVENTS);
    }

    /**
     * Remove normal meta boxes for some taxonomies
     *
     * @author Webnus <info@webnus.net>
     */
    public function remove_taxonomies_metaboxes()
    {
        remove_meta_box('tagsdiv-mec_location', $this->PT, 'side');
        remove_meta_box('tagsdiv-mec_organizer', $this->PT, 'side');
        remove_meta_box('tagsdiv-mec_label', $this->PT, 'side');
    }

    /**
     * Registers 2 meta boxes for event data
     *
     * @author Webnus <info@webnus.net>
     */
    public function register_meta_boxes()
    {
        // Event Details
        add_meta_box('mec_metabox_details', esc_html__('Event Details', 'modern-events-calendar-lite'), [$this, 'meta_box_details'], $this->main->get_main_post_type(), 'normal', 'high');

        // Visibility
        $visibility_status = !isset($this->settings['event_visibility_status']) || $this->settings['event_visibility_status'];
        $style_per_event = isset($this->settings['style_per_event']) && $this->settings['style_per_event'];

        if ($visibility_status || $style_per_event)
        {
            add_meta_box('mec_metabox_visibility', esc_html__('Visibility', 'modern-events-calendar-lite'), [$this, 'meta_box_visibility'], $this->main->get_main_post_type(), 'side');
        }

        // Gallery
        if (isset($this->settings['event_gallery_status']) && $this->settings['event_gallery_status'])
        {
            add_meta_box('mec_metabox_gallery', esc_html__('Gallery', 'modern-events-calendar-lite'), [$this, 'meta_box_event_gallery'], $this->main->get_main_post_type(), 'side', 'low');
        }

        // Show Booking meta box only if booking module is enabled
        if ($this->getPRO() and isset($this->settings['booking_status']) and $this->settings['booking_status'])
        {
            add_meta_box('mec_metabox_booking', esc_html__('Booking', 'modern-events-calendar-lite'), [$this, 'meta_box_booking'], $this->main->get_main_post_type(), 'normal', 'high');
        }
    }

    /**
     * Show content of details meta box
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_details($post)
    {
        global $post;
        $note = get_post_meta($post->ID, 'mec_note', true);
        $note_visibility = $this->main->is_note_visible($post->post_status);

        $fes_guest_email = get_post_meta($post->ID, 'fes_guest_email', true);
        $fes_guest_name = get_post_meta($post->ID, 'fes_guest_name', true);

        $event_fields = $this->main->get_event_fields();
        ?>
        <div class="mec-add-event-tabs-wrap">
            <div class="mec-add-event-tabs-left">
                <?php
                $activated = '';
                $tabs = [
                    esc_html__('FES Details', 'modern-events-calendar-lite') => 'mec_meta_box_fes_form',
                    esc_html__('Date And Time', 'modern-events-calendar-lite') => 'mec_meta_box_date_form',
                    esc_html__('Event Repeating', 'modern-events-calendar-lite') => 'mec_meta_box_repeat_form',
                    esc_html__('Event Data', 'modern-events-calendar-lite') => 'mec-event-data',
                    esc_html__('Exceptional Days', 'modern-events-calendar-lite') => 'mec-exceptional-days',
                    esc_html__('Hourly Schedule', 'modern-events-calendar-lite') => 'mec-hourly-schedule',
                    esc_html__('Location/Venue', 'modern-events-calendar-lite') => 'mec-location',
                    esc_html__('Links', 'modern-events-calendar-lite') => 'mec-read-more',
                    esc_html__('Organizer', 'modern-events-calendar-lite') => 'mec-organizer',
                    esc_html__('Cost', 'modern-events-calendar-lite') => 'mec-cost',
                    esc_html__('SEO Schema / Event Status', 'modern-events-calendar-lite') => 'mec-schema',
                    esc_html__('Notifications', 'modern-events-calendar-lite') => 'mec-notifications',
                    esc_html__('Public Download', 'modern-events-calendar-lite') => 'mec-public-download-module-file',
                    esc_html__('Related Events', 'modern-events-calendar-lite') => 'mec-event-related-events',
                    esc_html__('Event Banner', 'modern-events-calendar-lite') => 'mec-event-banner',
                ];

                $single_event_meta_title = apply_filters('mec-single-event-meta-title', $tabs, $activated, $post);
                foreach ($single_event_meta_title as $link_name => $link_address)
                {
                    if ($link_address == 'mec_meta_box_fes_form')
                    {
                        if (($note_visibility and trim($note)) || (trim($fes_guest_email) and trim($fes_guest_name))) echo '<a class="mec-add-event-tabs-link" data-href="' . esc_attr($link_address) . '" href="#">' . esc_html($link_name) . '</a>';
                    }
                    else if ($link_address == 'mec-exceptional-days')
                    {
                        if (isset($this->settings['exceptional_days']) and $this->settings['exceptional_days']) echo '<a class="mec-add-event-tabs-link" data-href="' . esc_attr($link_address) . '" href="#">' . esc_html($link_name) . '</a>';
                    }
                    else if ($link_address == 'mec-event-data')
                    {
                        if (count($event_fields) and isset($this->settings['display_event_fields_backend']) and $this->settings['display_event_fields_backend'] == 1) echo '<a class="mec-add-event-tabs-link" data-href="' . esc_attr($link_address) . '" href="#">' . esc_html($link_name) . '</a>';
                    }
                    else if ($link_address == 'mec-notifications')
                    {
                        if (isset($this->settings['notif_per_event']) and $this->settings['notif_per_event']) echo '<a class="mec-add-event-tabs-link" data-href="' . esc_attr($link_address) . '" href="#">' . esc_html($link_name) . '</a>';
                    }
                    else if ($link_address == 'mec-public-download-module-file')
                    {
                        if (isset($this->settings['public_download_module']) and $this->settings['public_download_module']) echo '<a class="mec-add-event-tabs-link" data-href="' . esc_attr($link_address) . '" href="#">' . esc_html($link_name) . '</a>';
                    }
                    else if ($link_address == 'mec-event-related-events')
                    {
                        if (isset($this->settings['related_events_per_event']) and $this->settings['related_events_per_event']) echo '<a class="mec-add-event-tabs-link" data-href="' . esc_attr($link_address) . '" href="#">' . esc_html($link_name) . '</a>';
                    }
                    else if ($link_address == 'mec-event-banner')
                    {
                        if (isset($this->settings['banner_status']) && $this->settings['banner_status'] && (!isset($this->settings['banner_force_featured_image']) || !$this->settings['banner_force_featured_image'])) echo '<a class="mec-add-event-tabs-link" data-href="' . esc_attr($link_address) . '" href="#">' . esc_html($link_name) . '</a>';
                    }
                    else if ($link_address === 'mec-organizer')
                    {
                        if ((!isset($this->settings['organizers_status']) || $this->settings['organizers_status'])) echo '<a class="mec-add-event-tabs-link" data-href="' . esc_attr($link_address) . '" href="#">' . esc_html($link_name) . '</a>';
                    }
                    else
                    {
                        echo '<a class="mec-add-event-tabs-link" data-href="' . esc_attr($link_address) . '" href="#">' . esc_html($link_name) . '</a>';
                    }
                }
                ?>
            </div>
            <div class="mec-add-event-tabs-right">
                <?php do_action('mec_metabox_details', $post); ?>
            </div>
        </div>
        <script>
            jQuery(".mec-meta-box-fields .mec-event-tab-content:first-of-type,.mec-add-event-tabs-left .mec-add-event-tabs-link:first-of-type").addClass("mec-tab-active");
            jQuery(".mec-add-event-tabs-link").on("click", function (e) {
                e.preventDefault();
                var href = jQuery(this).attr("data-href");
                jQuery(".mec-event-tab-content,.mec-add-event-tabs-link").removeClass("mec-tab-active");
                jQuery(this).addClass("mec-tab-active");
                jQuery("#" + href).addClass("mec-tab-active");
            });
        </script>

        <?php if (isset($this->settings['display_event_fields_backend']) and $this->settings['display_event_fields_backend'] == 1): ?>
        <script>
            jQuery("#publish").on("click", function () {
                var xdf = jQuery("#mec_metabox_details .mec-add-event-tabs-left .mec-add-event-tabs-link[data-href='mec-event-data']");
                jQuery("#mec_metabox_details .mec-add-event-tabs-left .mec-add-event-tabs-link").removeClass("mec-tab-active");
                jQuery("#mec_metabox_details .mec-add-event-tabs-right .mec-event-tab-content").removeClass("mec-tab-active");
                jQuery(xdf).addClass("mec-tab-active");
                jQuery(".mec-add-event-tabs-right #mec-event-data").addClass("mec-tab-active");
            });
        </script>
    <?php
    endif;
    }

    /**
     * Show content of visibility meta box
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_visibility($post)
    {
        // Public Event
        $public = get_post_meta($post->ID, 'mec_public', true);
        if (trim($public) === '') $public = 1;

        $style_per_event = get_post_meta($post->ID, 'mec_style_per_event', true);
        if (trim($style_per_event) == '') $style_per_event = 'global';
        ?>
        <div class="mec-metabox-visibility">
            <?php if (!isset($this->settings['event_visibility_status']) or (isset($this->settings['event_visibility_status']) and $this->settings['event_visibility_status'])): ?>
                <label
                    class="post-attributes-label"><?php esc_html_e('Visibility', 'modern-events-calendar-lite'); ?></label class="post-attributes-label">
                <div class="mec-form-row">
                    <div class="mec-col-12">
                        <select name="mec[public]" id="mec_public"
                                title="<?php esc_attr_e('Event Visibility', 'modern-events-calendar-lite'); ?>">
                            <option
                                value="1" <?php if ('1' == $public) echo 'selected="selected"'; ?>><?php esc_html_e('Show on Shortcodes', 'modern-events-calendar-lite'); ?></option>
                            <option
                                value="0" <?php if ('0' == $public) echo 'selected="selected"'; ?>><?php esc_html_e('Hide on Shortcodes', 'modern-events-calendar-lite'); ?></option>
                        </select>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (isset($this->settings['style_per_event']) and $this->settings['style_per_event']): ?>
                <label class="post-attributes-label"><?php esc_html_e('Details Page Style', 'modern-events-calendar-lite'); ?></label>
                <div class="mec-form-row">
                    <div class="mec-col-12">
                        <select name="mec[style_per_event]" id="mec_style_per_event"
                                title="<?php esc_attr_e('Event Style', 'modern-events-calendar-lite'); ?>">
                            <option value="global"><?php esc_html_e('Inherit from global options', 'modern-events-calendar-lite'); ?></option>
                            <option
                                value="default" <?php echo $style_per_event === 'default' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Default Style', 'modern-events-calendar-lite'); ?></option>
                            <option
                                value="modern" <?php echo $style_per_event === 'modern' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Modern Style', 'modern-events-calendar-lite'); ?></option>
                            <?php do_action('mec_single_style', ['style_per_event' => $style_per_event], 'style_per_event'); ?>
                            <?php if (is_plugin_active('mec-single-builder/mec-single-builder.php')): ?>
                                <option
                                    value="builder" <?php echo $style_per_event === 'builder' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Elementor Single Builder', 'modern-events-calendar-lite'); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Show content of gallery meta box
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_gallery($post)
    {
    }

    /**
     * Add a security nonce to the Add/Edit events page
     *
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_nonce()
    {
        // Add a nonce field, so we can check for it later.
        wp_nonce_field('mec_event_data', 'mec_event_nonce');
    }

    /**
     * Show date options of event into the Add/Edit event page
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_dates($post)
    {
        global $post;

        $allday = get_post_meta($post->ID, 'mec_allday', true);
        $one_occurrence = get_post_meta($post->ID, 'one_occurrence', true);
        $comment = get_post_meta($post->ID, 'mec_comment', true);
        $hide_time = get_post_meta($post->ID, 'mec_hide_time', true);
        $start_date = get_post_meta($post->ID, 'mec_start_date', true);

        $hide_end_time_global = isset($this->settings['hide_event_end_time']) && $this->settings['hide_event_end_time'];
        $hide_end_time = get_post_meta($post->ID, 'mec_hide_end_time', true);

        if ($hide_end_time_global) $hide_end_time = 1;

        // This date format used for datepicker
        $datepicker_format = (isset($this->settings['datepicker_format']) and trim($this->settings['datepicker_format'])) ? $this->settings['datepicker_format'] : 'Y-m-d';

        // Advanced Repeating Day
        $advanced_days = get_post_meta($post->ID, 'mec_advanced_days', true);
        $advanced_days = is_array($advanced_days) ? $advanced_days : [];
        $advanced_str = count($advanced_days) ? implode('-', $advanced_days) : '';

        $start_time_hour = get_post_meta($post->ID, 'mec_start_time_hour', true);
        if (trim($start_time_hour) == '') $start_time_hour = 8;

        $start_time_minutes = get_post_meta($post->ID, 'mec_start_time_minutes', true);
        if (trim($start_time_minutes) == '') $start_time_minutes = 0;

        $start_time_ampm = get_post_meta($post->ID, 'mec_start_time_ampm', true);
        if (trim($start_time_ampm) == '') $start_time_ampm = 'AM';

        $end_date = get_post_meta($post->ID, 'mec_end_date', true);

        $end_time_hour = get_post_meta($post->ID, 'mec_end_time_hour', true);
        if (trim($end_time_hour) == '') $end_time_hour = 6;

        $end_time_minutes = get_post_meta($post->ID, 'mec_end_time_minutes', true);
        if (trim($end_time_minutes) == '') $end_time_minutes = 0;

        $end_time_ampm = get_post_meta($post->ID, 'mec_end_time_ampm', true);
        if (trim($end_time_ampm) == '') $end_time_ampm = 'PM';

        $repeat_status = get_post_meta($post->ID, 'mec_repeat_status', true);
        $repeat_type = get_post_meta($post->ID, 'mec_repeat_type', true);
        if (trim($repeat_type) == '') $repeat_type = 'daily';

        $repeat_interval = get_post_meta($post->ID, 'mec_repeat_interval', true);
        if (trim($repeat_interval) == '' and in_array($repeat_type, ['daily', 'weekly'])) $repeat_interval = 1;

        $certain_weekdays = get_post_meta($post->ID, 'mec_certain_weekdays', true);
        if ($repeat_type != 'certain_weekdays') $certain_weekdays = [];

        $in_days_str = get_post_meta($post->ID, 'mec_in_days', true);
        $in_days = trim($in_days_str) ? explode(',', $in_days_str) : [];

        $entity_type = $this->getAppointments()->get_entity_type($post->ID);
        if ($entity_type === 'appointment') $in_days = [];

        $mec_repeat_end = get_post_meta($post->ID, 'mec_repeat_end', true);
        if (trim($mec_repeat_end) == '') $mec_repeat_end = 'never';

        $repeat_end_at_occurrences = get_post_meta($post->ID, 'mec_repeat_end_at_occurrences', true);
        if (trim($repeat_end_at_occurrences) == '') $repeat_end_at_occurrences = 9;

        $repeat_end_at_date = get_post_meta($post->ID, 'mec_repeat_end_at_date', true);

        $note = get_post_meta($post->ID, 'mec_note', true);
        $note_visibility = $this->main->is_note_visible($post->post_status);

        $fes_guest_email = get_post_meta($post->ID, 'fes_guest_email', true);
        $fes_guest_name = get_post_meta($post->ID, 'fes_guest_name', true);
        $imported_from_google = get_post_meta($post->ID, 'mec_imported_from_google', true);

        $event_timezone = get_post_meta($post->ID, 'mec_timezone', true);
        if (trim($event_timezone) == '') $event_timezone = 'global';

        $countdown_method = get_post_meta($post->ID, 'mec_countdown_method', true);
        if (trim($countdown_method) == '') $countdown_method = 'global';
        ?>
        <div class="mec-meta-box-fields" id="mec-date-time">
            <?php if (($note_visibility and trim($note)) || (trim($fes_guest_email) and trim($fes_guest_name))): ?>
            <div id="mec_meta_box_fes_form" class="mec-event-tab-content">
                <?php endif; ?>
                <?php if ($note_visibility and trim($note)): ?>
                    <div class="mec-event-note">
                        <h4><?php esc_html_e('Note for reviewer', 'modern-events-calendar-lite'); ?></h4>
                        <p><?php echo esc_html($note); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (trim($fes_guest_email) and trim($fes_guest_name)): ?>
                    <div class="mec-guest-data">
                        <h4><?php esc_html_e('Guest Data', 'modern-events-calendar-lite'); ?></h4>
                        <p><strong><?php esc_html_e('Name', 'modern-events-calendar-lite'); ?>
                                :</strong> <?php echo esc_html($fes_guest_name); ?></p>
                        <p><strong><?php esc_html_e('Email', 'modern-events-calendar-lite'); ?>
                                :</strong> <?php echo esc_html($fes_guest_email); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (($note_visibility and trim($note)) || (trim($fes_guest_email) and trim($fes_guest_name))): ?>
            </div>
        <?php endif; ?>
            <?php do_action('start_mec_custom_fields', $post); ?>

            <?php if ($imported_from_google): ?>
                <p class="info-msg"><?php esc_html_e("This event is imported from Google calendar so if you modify it, it would overwrite in the next import from Google.", 'modern-events-calendar-lite'); ?></p>
            <?php endif; ?>

            <div id="mec_meta_box_date_form" class="mec-event-tab-content">

                <?php do_action('mec_editor_before_date_time', $post->ID); ?>

                <div class="mec-date-time-inner-options">
                    <div class="mec-backend-tab-wrap mec-basvanced-toggle" data-for="#mec_meta_box_date_form">
                        <div class="mec-backend-tab">
                            <div class="mec-backend-tab-item mec-b-active-tab"><?php esc_html_e('Basic', 'modern-events-calendar-lite'); ?></div>
                            <div class="mec-backend-tab-item"><?php esc_html_e('Advanced', 'modern-events-calendar-lite'); ?></div>
                        </div>
                    </div>
                    <h4><?php esc_html_e('Date and Time', 'modern-events-calendar-lite'); ?></h4>
                    <div class="mec-basvanced-basic">
                        <div class="mec-title">
                            <span class="mec-dashicons dashicons dashicons-calendar-alt"></span>
                            <label for="mec_start_date"><?php esc_html_e('Start Date', 'modern-events-calendar-lite'); ?></label>
                        </div>
                        <div class="mec-form-row">
                            <div class="mec-col-4">
                                <input type="text" name="mec[date][start][date]" id="mec_start_date"
                                       data-end="#mec_end_date"
                                       value="<?php echo esc_attr($this->main->standardize_format($start_date, $datepicker_format)); ?>"
                                       placeholder="<?php esc_html_e('Start Date', 'modern-events-calendar-lite'); ?>" autocomplete="off"/>
                            </div>
                            <div class="mec-col-6 mec-time-picker <?php echo ($allday == 1) ? 'mec-util-hidden' : ''; ?>">
                                <?php $this->main->timepicker([
                                    'method' => $this->settings['time_format'] ?? 12,
                                    'time_hour' => $start_time_hour,
                                    'time_minutes' => $start_time_minutes,
                                    'time_ampm' => $start_time_ampm,
                                    'name' => 'mec[date][start]',
                                    'id_key' => 'start_',
                                ]); ?>
                            </div>
                        </div>
                        <div class="mec-title">
                            <span class="mec-dashicons dashicons dashicons-calendar-alt"></span>
                            <label for="mec_end_date"><?php esc_html_e('End Date', 'modern-events-calendar-lite'); ?></label>
                        </div>
                        <div class="mec-form-row">
                            <div class="mec-col-4">
                                <input type="text" name="mec[date][end][date]" id="mec_end_date"
                                       data-start="#mec_start_date"
                                       value="<?php echo esc_attr($this->main->standardize_format($end_date, $datepicker_format)); ?>"
                                       placeholder="<?php esc_html_e('End Date', 'modern-events-calendar-lite'); ?>" autocomplete="off"/>
                            </div>
                            <div class="mec-col-6 mec-time-picker <?php echo ($allday == 1) ? 'mec-util-hidden' : ''; ?>">
                                <?php $this->main->timepicker([
                                    'method' => $this->settings['time_format'] ?? 12,
                                    'time_hour' => $end_time_hour,
                                    'time_minutes' => $end_time_minutes,
                                    'time_ampm' => $end_time_ampm,
                                    'name' => 'mec[date][end]',
                                    'id_key' => 'end_',
                                ]); ?>
                            </div>
                        </div>
                        <?php do_action('add_event_after_time_and_date', $post->ID); ?>
                        <div class="mec-form-row mec-all-day-event">
                            <label for="mec_allday">
                                <input
                                    <?php
                                    if ($allday == '1')
                                    {
                                        echo 'checked="checked"';
                                    }
                                    ?>
                                    type="checkbox" name="mec[date][allday]" id="mec_allday" value="1"
                                    onchange="jQuery('.mec-time-picker, .mec-time-picker-label').toggle(); jQuery('#mec_add_in_days').data('allday', (jQuery(this).is(':checked') ? 1 : 0));"/>
                                <?php esc_html_e('All-day Event', 'modern-events-calendar-lite'); ?>
                            </label>
                        </div>
                    </div>
                    <div class="mec-basvanced-advanced w-hidden">
                        <div class="mec-form-row">
                            <label for="mec_hide_time">
                                <input
                                    <?php
                                    if ($hide_time == '1')
                                    {
                                        echo 'checked="checked"';
                                    }
                                    ?>
                                    type="checkbox" name="mec[date][hide_time]" id="mec_hide_time" value="1"/>
                                <?php esc_html_e('Hide Event Time', 'modern-events-calendar-lite'); ?>
                            </label>
                        </div>
                        <div class="mec-form-row <?php echo $hide_end_time_global ? 'w-hidden' : ''; ?>">
                            <label for="mec_hide_end_time">
                                <input
                                    <?php
                                    if ($hide_end_time == '1')
                                    {
                                        echo 'checked="checked"';
                                    }
                                    ?>
                                    type="checkbox" name="mec[date][hide_end_time]" id="mec_hide_end_time" value="1"/>
                                <?php esc_html_e('Hide Event End Time', 'modern-events-calendar-lite'); ?>
                            </label>
                        </div>
                        <div class="mec-form-row">
                            <div class="mec-col-4">
                                <input type="text" class="" name="mec[date][comment]" id="mec_comment"
                                       placeholder="<?php esc_html_e('Notes on the time', 'modern-events-calendar-lite'); ?>"
                                       value="<?php echo esc_attr($comment); ?>"/>
                                <span class="mec-tooltip">
                                    <div class="box top">
                                        <h5 class="title"><?php esc_html_e('Notes on the time', 'modern-events-calendar-lite'); ?></h5>
                                        <div class="content">
                                            <p><?php esc_attr_e('It appears next to the event time on the Single Event Page. You can enter notes such as the timezone name in this field.', 'modern-events-calendar-lite'); ?>
                                                <a href="https://webnus.net/dox/modern-events-calendar/add-event/#Date_and_Time"
                                                   target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite'); ?></a>
                                            </p>
                                        </div>
                                    </div>
                                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                                </span>
                            </div>
                        </div>

                        <?php if (isset($this->settings['countdown_status']) and $this->settings['countdown_status'] and isset($this->settings['countdown_method_per_event']) and $this->settings['countdown_method_per_event']): ?>
                            <h4><?php esc_html_e('Countdown Method', 'modern-events-calendar-lite'); ?></h4>
                            <div class="mec-form-row">
                                <div class="mec-col-4">
                                    <select name="mec[countdown_method]" id="mec_countdown_method"
                                            title="<?php esc_attr_e('Countdown Method', 'modern-events-calendar-lite'); ?>">
                                        <option
                                            value="global" <?php if ('global' == $countdown_method) echo 'selected="selected"'; ?>><?php esc_html_e('Inherit from global options', 'modern-events-calendar-lite'); ?></option>
                                        <option
                                            value="start" <?php if ('start' == $countdown_method) echo 'selected="selected"'; ?>><?php esc_html_e('Count to Event Start', 'modern-events-calendar-lite'); ?></option>
                                        <option
                                            value="end" <?php if ('end' == $countdown_method) echo 'selected="selected"'; ?>><?php esc_html_e('Count to Event End', 'modern-events-calendar-lite'); ?></option>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($this->settings['tz_per_event']) and $this->settings['tz_per_event']): ?>
                            <div class="mec-form-row mec-timezone-event">
                                <h4><label for="mec_event_timezone"><?php esc_html_e('Timezone', 'modern-events-calendar-lite'); ?></label></h4>
                                <div class="mec-form-row">
                                    <div class="mec-col-4">
                                        <select name="mec[timezone]" id="mec_event_timezone">
                                            <option
                                                value="global"><?php esc_html_e('Inherit from global options'); ?></option>
                                            <?php echo MEC_kses::form($this->main->timezones($event_timezone)); ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
            <div id="mec_meta_box_repeat_form" class="mec-event-tab-content">
                <h4><?php esc_html_e('Repeating', 'modern-events-calendar-lite'); ?></h4>
                <div class="mec-form-row">
                    <label for="mec_repeat">
                        <input
                            <?php
                            if ($repeat_status == '1')
                            {
                                echo 'checked="checked"';
                            }
                            ?>
                            type="checkbox" name="mec[date][repeat][status]" id="mec_repeat" value="1"/>
                        <?php esc_html_e('Event Repeating (Recurring events)', 'modern-events-calendar-lite'); ?>
                    </label>
                </div>
                <div class="mec-form-repeating-event-row">
                    <div class="mec-form-row">
                        <label class="mec-col-3" for="mec_repeat_type"><?php esc_html_e('Repeats', 'modern-events-calendar-lite'); ?></label>
                        <select class="mec-col-4" name="mec[date][repeat][type]" id="mec_repeat_type">
                            <option
                                <?php
                                if ($repeat_type == 'daily')
                                {
                                    echo 'selected="selected"';
                                }
                                ?>
                                value="daily"><?php esc_html_e('Daily', 'modern-events-calendar-lite'); ?></option>
                            <option
                                <?php
                                if ($repeat_type == 'weekday')
                                {
                                    echo 'selected="selected"';
                                }
                                ?>
                                value="weekday"><?php esc_html_e('Every Weekday', 'modern-events-calendar-lite'); ?></option>
                            <option
                                <?php
                                if ($repeat_type == 'weekend')
                                {
                                    echo 'selected="selected"';
                                }
                                ?>
                                value="weekend"><?php esc_html_e('Every Weekend', 'modern-events-calendar-lite'); ?></option>
                            <option
                                <?php
                                if ($repeat_type == 'certain_weekdays')
                                {
                                    echo 'selected="selected"';
                                }
                                ?>
                                value="certain_weekdays"><?php esc_html_e('Certain Weekdays', 'modern-events-calendar-lite'); ?></option>
                            <option
                                <?php
                                if ($repeat_type == 'weekly')
                                {
                                    echo 'selected="selected"';
                                }
                                ?>
                                value="weekly"><?php esc_html_e('Weekly', 'modern-events-calendar-lite'); ?></option>
                            <option
                                <?php
                                if ($repeat_type == 'monthly')
                                {
                                    echo 'selected="selected"';
                                }
                                ?>
                                value="monthly"><?php esc_html_e('Monthly', 'modern-events-calendar-lite'); ?></option>
                            <option
                                <?php
                                if ($repeat_type == 'yearly')
                                {
                                    echo 'selected="selected"';
                                }
                                ?>
                                value="yearly"><?php esc_html_e('Yearly', 'modern-events-calendar-lite'); ?></option>
                            <option
                                <?php
                                if ($repeat_type == 'custom_days')
                                {
                                    echo 'selected="selected"';
                                }
                                ?>
                                value="custom_days"><?php esc_html_e('Custom Days', 'modern-events-calendar-lite'); ?></option>
                            <option
                                <?php
                                if ($repeat_type == 'advanced')
                                {
                                    echo 'selected="selected"';
                                }
                                ?>
                                value="advanced"><?php esc_html_e('Advanced', 'modern-events-calendar-lite'); ?></option>
                        </select>
                    </div>
                    <div class="mec-form-row" id="mec_repeat_interval_container">
                        <label class="mec-col-3"
                               for="mec_repeat_interval"><?php esc_html_e('Repeat Interval', 'modern-events-calendar-lite'); ?></label>
                        <input class="mec-col-2" type="text" name="mec[date][repeat][interval]" id="mec_repeat_interval"
                               placeholder="<?php esc_html_e('Repeat interval', 'modern-events-calendar-lite'); ?>"
                               value="<?php echo($repeat_type == 'weekly' ? ($repeat_interval / 7) : $repeat_interval); ?>"/>
                    </div>
                    <div class="mec-form-row" id="mec_repeat_certain_weekdays_container">
                        <label class="mec-col-3"><?php esc_html_e('Week Days', 'modern-events-calendar-lite'); ?></label>
                        <?php
                        $weekdays = $this->main->get_weekday_i18n_labels();
                        foreach ($weekdays as $weekday):
                            ?>
                            <label>
                                <input type="checkbox" name="mec[date][repeat][certain_weekdays][]"
                                       value="<?php echo intval($weekday[0]); ?>" <?php echo(in_array($weekday[0], $certain_weekdays) ? 'checked="checked"' : ''); ?> /><?php echo esc_html($weekday[1]); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="mec-form-row" id="mec_exceptions_in_days_container">
                        <div class="mec-form-row">
                            <div class="mec-col-12">
                                <div class="mec-form-row mec-in-days-add-mode" id="mec-in-days-form">
                                    <div class="mec-col-4">
                                        <input type="text" id="mec_exceptions_in_days_start_date" value=""
                                               placeholder="<?php esc_html_e('Start', 'modern-events-calendar-lite'); ?>"
                                               title="<?php esc_html_e('Start', 'modern-events-calendar-lite'); ?>"
                                               class="mec_date_picker_dynamic_format widefat" autocomplete="off"/>
                                    </div>
                                    <div
                                        class="mec-col-5 mec-time-picker <?php echo $allday == 1 ? 'mec-util-hidden' : ''; ?>">
                                        <?php $this->main->timepicker([
                                            'method' => $this->settings['time_format'] ?? 12,
                                            'time_hour' => $start_time_hour,
                                            'time_minutes' => $start_time_minutes,
                                            'time_ampm' => $start_time_ampm,
                                            'name' => 'mec[exceptionsdays][start]',
                                            'id_key' => 'exceptions_in_days_start_',
                                        ]); ?>
                                    </div>
                                    <div class="mec-col-3">
                                        <button class="button" type="button" id="mec_add_in_days"
                                                data-allday="<?php echo esc_attr($allday); ?>"><?php esc_html_e('Add', 'modern-events-calendar-lite'); ?></button>
                                        <button class="button" type="button" id="mec_edit_in_days"
                                                data-allday="<?php echo esc_attr($allday); ?>"><?php esc_html_e('Update', 'modern-events-calendar-lite'); ?></button>
                                        <button class="button" type="button"
                                                id="mec_cancel_in_days"><?php esc_html_e('Cancel', 'modern-events-calendar-lite'); ?></button>
                                        <span class="mec-tooltip">
                                            <div class="box top">
                                                <h5 class="title"><?php esc_html_e('Custom Days Repeating', 'modern-events-calendar-lite'); ?></h5>
                                                <div class="content">
                                                    <p>
                                                        <?php esc_attr_e('Add certain days to event occurrences. If you have a single day event, start and end dates should be the same, If you have a multi-day event, the interval between the start and end dates must match the initial date.', 'modern-events-calendar-lite'); ?>
                                                        <a href="https://webnus.net/dox/modern-events-calendar/add-event/#Repeat_Methods"
                                                           target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite'); ?></a>
                                                    </p>
                                                </div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <div class="mec-col-4">
                                        <input type="text" id="mec_exceptions_in_days_end_date" value=""
                                               placeholder="<?php esc_html_e('End', 'modern-events-calendar-lite'); ?>"
                                               title="<?php esc_html_e('End', 'modern-events-calendar-lite'); ?>"
                                               class="mec_date_picker_dynamic_format" autocomplete="off"/>
                                    </div>
                                    <div
                                        class="mec-col-8 mec-time-picker <?php echo ($allday == 1) ? 'mec-util-hidden' : ''; ?>">
                                        <?php $this->main->timepicker([
                                            'method' => ($this->settings['time_format'] ?? 12),
                                            'time_hour' => $end_time_hour,
                                            'time_minutes' => $end_time_minutes,
                                            'time_ampm' => $end_time_ampm,
                                            'name' => 'mec[exceptionsdays][end]',
                                            'id_key' => 'exceptions_in_days_end_',
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mec-form-row mec-certain-day" id="mec_in_days">
                            <?php $i = 1;
                            foreach ($in_days as $in_day): ?>
                                <?php
                                $in_day = explode(':', $in_day);
                                $first_date = $this->main->standardize_format($in_day[0], $datepicker_format);
                                $second_date = $this->main->standardize_format($in_day[1], $datepicker_format);

                                $in_day_start_time = '';
                                $in_day_start_time_label = '';
                                $in_day_end_time = '';
                                $in_day_end_time_label = '';

                                if (isset($in_day[2]) and isset($in_day[3]))
                                {
                                    $in_day_start_time = $in_day[2];
                                    $in_day_end_time = $in_day[3];

                                    // If 24 hours format is enabled then convert it back to 12 hours
                                    if (isset($this->settings['time_format']) and $this->settings['time_format'] == 24)
                                    {
                                        $in_day_ex_start = explode('-', $in_day_start_time);
                                        $in_day_ex_end = explode('-', $in_day_end_time);

                                        $in_day_start_time_label = $this->main->to_24hours($in_day_ex_start[0], $in_day_ex_start[2], 'start') . ':' . $in_day_ex_start[1];
                                        $in_day_end_time_label = $this->main->to_24hours($in_day_ex_end[0], $in_day_ex_end[2], 'start') . ':' . $in_day_ex_end[1];
                                    }
                                    else
                                    {
                                        $pos = strpos($in_day_start_time, '-');
                                        if ($pos !== false) $in_day_start_time_label = substr_replace($in_day_start_time, ':', $pos, 1);

                                        $pos = strpos($in_day_end_time, '-');
                                        if ($pos !== false) $in_day_end_time_label = substr_replace($in_day_end_time, ':', $pos, 1);

                                        $in_day_start_time_label = str_replace('-', ' ', $in_day_start_time_label);
                                        $in_day_end_time_label = str_replace('-', ' ', $in_day_end_time_label);
                                    }
                                }

                                $in_day = $first_date . ':' . $second_date . (trim($in_day_start_time) ? ':' . $in_day_start_time : '') . (trim($in_day_end_time) ? ':' . $in_day_end_time : '');
                                $in_day_label = $first_date . (trim($in_day_start_time_label) ? ' <span class="mec-time-picker-label ' . ($allday ? 'mec-util-hidden' : '') . '">' . esc_html($in_day_start_time_label) . '</span>' : '') . ' - ' . $second_date . (trim($in_day_end_time_label) ? ' <span class="mec-time-picker-label ' . ($allday ? 'mec-util-hidden' : '') . '">' . esc_html($in_day_end_time_label) . '</span>' : '');
                                ?>
                                <div class="mec-form-row" id="mec_in_days_row<?php echo esc_attr($i); ?>">
                                    <input type="hidden" name="mec[in_days][<?php echo esc_attr($i); ?>]"
                                           value="<?php echo esc_attr($in_day); ?>"/>
                                    <span class="mec-in-days-day"
                                          onclick="mec_in_days_edit(<?php echo esc_attr($i); ?>);"
                                          title="<?php echo esc_attr__('Click to edit', 'modern-events-calendar-lite'); ?>"><?php echo MEC_kses::element($in_day_label); ?></span>
                                    <span class="mec-not-in-days-remove"
                                          onclick="mec_in_days_remove(<?php echo esc_attr($i); ?>);">x</span>
                                </div>
                                <?php $i++; endforeach; ?>
                        </div>
                        <input type="hidden" id="mec_new_in_days_key" value="<?php echo($i + 1); ?>"/>
                        <div class="mec-util-hidden" id="mec_new_in_days_raw">
                            <div class="mec-form-row" id="mec_in_days_row:i:">
                                <input type="hidden" name="mec[in_days][:i:]" value=":val:"/>
                                <span class="mec-in-days-day" onclick="mec_in_days_edit(:i:);"
                                      title="<?php echo esc_attr__('Click to edit', 'modern-events-calendar-lite'); ?>">:label:</span>
                                <span class="mec-not-in-days-remove" onclick="mec_in_days_remove(:i:);">x</span>
                            </div>
                        </div>
                    </div>
                    <div id="mec-advanced-wraper">
                        <div class="mec-form-row">
                            <ul>
                                <li>
                                    <?php esc_html_e('First', 'modern-events-calendar-lite'); ?>
                                </li>
                                <ul>
                                    <?php $day_1th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 1); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_1th}.1"); ?>">
                                        <?php esc_html_e($day_1th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_1th); ?>.1-</span>
                                    </li>
                                    <?php $day_2th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 2); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_2th}.1"); ?>">
                                        <?php esc_html_e($day_2th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_2th); ?>.1-</span>
                                    </li>
                                    <?php $day_3th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 3); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_3th}.1"); ?>">
                                        <?php esc_html_e($day_3th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_3th); ?>.1-</span>
                                    </li>
                                    <?php $day_4th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 4); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_4th}.1"); ?>">
                                        <?php esc_html_e($day_4th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_4th); ?>.1-</span>
                                    </li>
                                    <?php $day_5th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 5); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_5th}.1"); ?>">
                                        <?php esc_html_e($day_5th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_5th); ?>.1-</span>
                                    </li>
                                    <?php $day_6th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 6); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_6th}.1"); ?>">
                                        <?php esc_html_e($day_6th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_6th); ?>.1-</span>
                                    </li>
                                    <?php $day_7th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 7); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_7th}.1"); ?>">
                                        <?php esc_html_e($day_7th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_7th); ?>.1-</span>
                                    </li>
                                </ul>
                            </ul>
                            <ul>
                                <li>
                                    <?php esc_html_e('Second', 'modern-events-calendar-lite'); ?>
                                </li>
                                <ul>
                                    <?php $day_1th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 1); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_1th}.2"); ?>">
                                        <?php esc_html_e($day_1th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_1th); ?>.2-</span>
                                    </li>
                                    <?php $day_2th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 2); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_2th}.2"); ?>">
                                        <?php esc_html_e($day_2th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_2th); ?>.2-</span>
                                    </li>
                                    <?php $day_3th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 3); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_3th}.2"); ?>">
                                        <?php esc_html_e($day_3th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_3th); ?>.2-</span>
                                    </li>
                                    <?php $day_4th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 4); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_4th}.2"); ?>">
                                        <?php esc_html_e($day_4th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_4th); ?>.2-</span>
                                    </li>
                                    <?php $day_5th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 5); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_5th}.2"); ?>">
                                        <?php esc_html_e($day_5th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_5th); ?>.2-</span>
                                    </li>
                                    <?php $day_6th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 6); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_6th}.2"); ?>">
                                        <?php esc_html_e($day_6th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_6th); ?>.2-</span>
                                    </li>
                                    <?php $day_7th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 7); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_7th}.2"); ?>">
                                        <?php esc_html_e($day_7th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_7th); ?>.2-</span>
                                    </li>
                                </ul>
                            </ul>
                            <ul>
                                <li>
                                    <?php esc_html_e('Third', 'modern-events-calendar-lite'); ?>
                                </li>
                                <ul>
                                    <?php $day_1th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 1); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_1th}.3"); ?>">
                                        <?php esc_html_e($day_1th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_1th); ?>.3-</span>
                                    </li>
                                    <?php $day_2th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 2); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_2th}.3"); ?>">
                                        <?php esc_html_e($day_2th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_2th); ?>.3-</span>
                                    </li>
                                    <?php $day_3th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 3); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_3th}.3"); ?>">
                                        <?php esc_html_e($day_3th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_3th); ?>.3-</span>
                                    </li>
                                    <?php $day_4th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 4); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_4th}.3"); ?>">
                                        <?php esc_html_e($day_4th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_4th); ?>.3-</span>
                                    </li>
                                    <?php $day_5th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 5); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_5th}.3"); ?>">
                                        <?php esc_html_e($day_5th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_5th); ?>.3-</span>
                                    </li>
                                    <?php $day_6th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 6); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_6th}.3"); ?>">
                                        <?php esc_html_e($day_6th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_6th); ?>.3-</span>
                                    </li>
                                    <?php $day_7th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 7); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_7th}.3"); ?>">
                                        <?php esc_html_e($day_7th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_7th); ?>.3-</span>
                                    </li>
                                </ul>
                            </ul>
                            <ul>
                                <li>
                                    <?php esc_html_e('Fourth', 'modern-events-calendar-lite'); ?>
                                </li>
                                <ul>
                                    <?php $day_1th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 1); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_1th}.4"); ?>">
                                        <?php esc_html_e($day_1th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_1th); ?>.4-</span>
                                    </li>
                                    <?php $day_2th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 2); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_2th}.4"); ?>">
                                        <?php esc_html_e($day_2th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_2th); ?>.4-</span>
                                    </li>
                                    <?php $day_3th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 3); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_3th}.4"); ?>">
                                        <?php esc_html_e($day_3th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_3th); ?>.4-</span>
                                    </li>
                                    <?php $day_4th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 4); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_4th}.4"); ?>">
                                        <?php esc_html_e($day_4th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_4th); ?>.4-</span>
                                    </li>
                                    <?php $day_5th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 5); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_5th}.4"); ?>">
                                        <?php esc_html_e($day_5th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_5th); ?>.4-</span>
                                    </li>
                                    <?php $day_6th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 6); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_6th}.4"); ?>">
                                        <?php esc_html_e($day_6th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_6th); ?>.4-</span>
                                    </li>
                                    <?php $day_7th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 7); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_7th}.4"); ?>">
                                        <?php esc_html_e($day_7th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_7th); ?>.4-</span>
                                    </li>
                                </ul>
                            </ul>
                            <ul>
                                <li>
                                    <?php esc_html_e('Last', 'modern-events-calendar-lite'); ?>
                                </li>
                                <ul>
                                    <?php $day_1th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 1); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_1th}.l"); ?>">
                                        <?php esc_html_e($day_1th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_1th); ?>.l-</span>
                                    </li>
                                    <?php $day_2th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 2); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_2th}.l"); ?>">
                                        <?php esc_html_e($day_2th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_2th); ?>.l-</span>
                                    </li>
                                    <?php $day_3th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 3); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_3th}.l"); ?>">
                                        <?php esc_html_e($day_3th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_3th); ?>.l-</span>
                                    </li>
                                    <?php $day_4th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 4); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_4th}.l"); ?>">
                                        <?php esc_html_e($day_4th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_4th); ?>.l-</span>
                                    </li>
                                    <?php $day_5th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 5); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_5th}.l"); ?>">
                                        <?php esc_html_e($day_5th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_5th); ?>.l-</span>
                                    </li>
                                    <?php $day_6th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 6); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_6th}.l"); ?>">
                                        <?php esc_html_e($day_6th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_6th); ?>.l-</span>
                                    </li>
                                    <?php $day_7th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 7); ?>
                                    <li class="<?php $this->main->mec_active($advanced_days, "{$day_7th}.l"); ?>">
                                        <?php esc_html_e($day_7th, 'modern-events-calendar-lite'); ?>
                                        <span class="key"><?php echo esc_html($day_7th); ?>.l-</span>
                                    </li>
                                </ul>
                            </ul>
                            <input class="mec-col-2" type="hidden" name="mec[date][repeat][advanced]"
                                   id="mec_date_repeat_advanced" value="<?php echo esc_attr($advanced_str); ?>"/>
                        </div>
                    </div>
                    <div id="mec_end_wrapper">
                        <div class="mec-form-row">
                            <h4 class="mec-title"><?php esc_html_e('End Repeat', 'modern-events-calendar-lite'); ?></h4>
                        </div>
                        <div class="mec-form-row">
                            <label for="mec_repeat_ends_never">
                                <input
                                    <?php
                                    if ($mec_repeat_end == 'never')
                                    {
                                        echo 'checked="checked"';
                                    }
                                    ?>
                                    type="radio" value="never" name="mec[date][repeat][end]"
                                    id="mec_repeat_ends_never"/>
                                <?php esc_html_e('Never', 'modern-events-calendar-lite'); ?>
                            </label>
                        </div>
                        <div class="mec-form-row">
                            <div class="mec-col-3">
                                <label for="mec_repeat_ends_date">
                                    <input
                                        <?php
                                        if ($mec_repeat_end == 'date')
                                        {
                                            echo 'checked="checked"';
                                        }
                                        ?>
                                        type="radio" value="date" name="mec[date][repeat][end]"
                                        id="mec_repeat_ends_date"/>
                                    <?php esc_html_e('On', 'modern-events-calendar-lite'); ?>
                                </label>
                            </div>
                            <input class="mec-col-2" type="text" name="mec[date][repeat][end_at_date]"
                                   id="mec_date_repeat_end_at_date" autocomplete="off"
                                   value="<?php echo esc_attr($this->main->standardize_format($repeat_end_at_date, $datepicker_format)); ?>"/>
                        </div>
                        <div class="mec-form-row">
                            <div class="mec-col-3">
                                <label for="mec_repeat_ends_occurrences">
                                    <input
                                        <?php
                                        if ($mec_repeat_end == 'occurrences')
                                        {
                                            echo 'checked="checked"';
                                        }
                                        ?>
                                        type="radio" value="occurrences" name="mec[date][repeat][end]"
                                        id="mec_repeat_ends_occurrences"/>
                                    <?php esc_html_e('After', 'modern-events-calendar-lite'); ?>
                                </label>
                            </div>
                            <input class="mec-col-2" type="text" name="mec[date][repeat][end_at_occurrences]"
                                   id="mec_date_repeat_end_at_occurrences" autocomplete="off"
                                   placeholder="<?php esc_html_e('Occurrences times', 'modern-events-calendar-lite'); ?>"
                                   value="<?php echo esc_attr(($repeat_end_at_occurrences + 1)); ?>"/>
                            <span class="mec-tooltip">
								<div class="box top">
									<h5 class="title"><?php esc_html_e('Occurrences times', 'modern-events-calendar-lite'); ?></h5>
									<div class="content">
                                        <p><?php esc_attr_e('The event repeats will stop after certain number of occurences. For example if you set this option 10, the event will have 10 occurrences.', 'modern-events-calendar-lite'); ?>
                                            <a href="https://webnus.net/dox/modern-events-calendar/add-event/#End_Repeat"
                                               target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite'); ?></a>
                                        </p>
                                    </div>
								</div>
								<i title="" class="dashicons-before dashicons-editor-help"></i>
							</span>
                        </div>
                    </div>
                    <div class="mec-form-row">
                        <label for="mec-one-occurrence">
                            <input
                                <?php
                                if ($one_occurrence == '1')
                                {
                                    echo 'checked="checked"';
                                }
                                ?>
                                type="checkbox" name="mec[date][one_occurrence]" id="mec-one-occurrence" value="1"/>
                            <?php esc_html_e('Show only one occurrence of this event', 'modern-events-calendar-lite'); ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Show cost option of event into the Add/Edit event page
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_cost($post)
    {
        $cost = get_post_meta($post->ID, 'mec_cost', true);
        $cost_auto_calculate = get_post_meta($post->ID, 'mec_cost_auto_calculate', true);

        $currency = get_post_meta($post->ID, 'mec_currency', true);
        if (!is_array($currency)) $currency = [];

        $type = ((isset($this->settings['single_cost_type']) and trim($this->settings['single_cost_type'])) ? $this->settings['single_cost_type'] : 'numeric');
        $currency_per_event = ((isset($this->settings['currency_per_event']) and trim($this->settings['currency_per_event'])) ? $this->settings['currency_per_event'] : 0);

        $currencies = $this->main->get_currencies();
        $current_currency = ($currency['currency'] ?? ($this->settings['currency'] ?? 'USD'));
        ?>
        <div class="mec-meta-box-fields mec-event-tab-content" id="mec-cost">
            <?php if ($currency_per_event): ?>
                <div class="mec-backend-tab-wrap mec-basvanced-toggle" data-for="#mec-cost">
                    <div class="mec-backend-tab">
                        <div class="mec-backend-tab-item mec-b-active-tab"><?php esc_html_e('Basic', 'modern-events-calendar-lite'); ?></div>
                        <div class="mec-backend-tab-item"><?php esc_html_e('Advanced', 'modern-events-calendar-lite'); ?></div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="mec-basvanced-basic">
                <h4><?php echo esc_html($this->main->m('event_cost', esc_html__('Event Cost', 'modern-events-calendar-lite'))); ?></h4>
                <div id="mec_meta_box_cost_form" class="<?php echo($cost_auto_calculate ? 'mec-util-hidden' : ''); ?>">
                    <div class="mec-form-row">
                        <?php if (apply_filters('mec_event_cost_custom_field_status', false)): ?>
                            <?php do_action('mec_event_cost_custom_field', $cost, $type, 'mec[cost]'); ?>
                        <?php else: ?>
                            <input
                                type="<?php echo($type === 'alphabetic' ? 'text' : 'number'); ?>" <?php echo($type === 'numeric' ? 'min="0" step="any"' : ''); ?>
                                class="mec-col-3" name="mec[cost]" id="mec_cost" value="<?php echo esc_attr($cost); ?>"
                                title="<?php esc_html_e('Cost', 'modern-events-calendar-lite'); ?>"
                                placeholder="<?php esc_html_e('Cost', 'modern-events-calendar-lite'); ?>"/>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($this->getPRO()): ?>
                    <div class="mec-form-row">
                        <div class="mec-col-12">
                            <label for="mec_cost_auto_calculate">
                                <input type="hidden" name="mec[cost_auto_calculate]" value="0"/>
                                <input type="checkbox" name="mec[cost_auto_calculate]"
                                       id="mec_cost_auto_calculate" <?php echo ($cost_auto_calculate == 1) ? 'checked="checked"' : ''; ?>
                                       value="1"
                                       onchange="jQuery('#mec_meta_box_cost_form').toggleClass('mec-util-hidden');">
                                <?php esc_html_e('Show the minimum price based on tickets', 'modern-events-calendar-lite'); ?>
                            </label>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mec-basvanced-advanced w-hidden">
                <?php if ($currency_per_event): ?>
                    <h4><?php echo esc_html__('Currency Options', 'modern-events-calendar-lite'); ?></h4>
                    <div class="mec-form-row">
                        <label class="mec-col-3"
                               for="mec_currency_currency"><?php esc_html_e('Currency', 'modern-events-calendar-lite'); ?></label>
                        <div class="mec-col-4">
                            <select name="mec[currency][currency]" id="mec_currency_currency">
                                <?php foreach ($currencies as $c => $currency_name): ?>
                                    <option
                                        value="<?php echo esc_attr($c); ?>" <?php echo(($current_currency == $c) ? 'selected="selected"' : ''); ?>><?php echo esc_html($currency_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mec-form-row">
                        <label class="mec-col-3"
                               for="mec_currency_currency_symptom"><?php esc_html_e('Currency Sign', 'modern-events-calendar-lite'); ?></label>
                        <div class="mec-col-4">
                            <input type="text" name="mec[currency][currency_symptom]" id="mec_currency_currency_symptom"
                                   value="<?php echo(isset($currency['currency_symptom']) ? esc_attr($currency['currency_symptom']) : ''); ?>"/>
                            <span class="mec-tooltip">
                            <div class="box left">
                                <h5 class="title"><?php esc_html_e('Currency Sign', 'modern-events-calendar-lite'); ?></h5>
                                <div class="content">
                                    <p><?php esc_attr_e("Default value will be \"currency\" if you leave it empty.", 'modern-events-calendar-lite'); ?>
                                        <a href="https://webnus.net/dox/modern-events-calendar/add-event/#Cost"
                                           target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite'); ?></a>
                                    </p>
                                </div>
                            </div>
                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                        </span>
                        </div>
                    </div>
                    <div class="mec-form-row">
                        <label class="mec-col-3"
                               for="mec_currency_currency_sign"><?php esc_html_e('Currency Position', 'modern-events-calendar-lite'); ?></label>
                        <div class="mec-col-4">
                            <select name="mec[currency][currency_sign]" id="mec_currency_currency_sign">
                                <option
                                    value="before" <?php echo((isset($currency['currency_sign']) and $currency['currency_sign'] == 'before') ? 'selected="selected"' : ''); ?>><?php esc_html_e('$10 (Before)', 'modern-events-calendar-lite'); ?></option>
                                <option
                                    value="before_space" <?php echo((isset($currency['currency_sign']) and $currency['currency_sign'] == 'before_space') ? 'selected="selected"' : ''); ?>><?php esc_html_e('$ 10 (Before with Space)', 'modern-events-calendar-lite'); ?></option>
                                <option
                                    value="after" <?php echo((isset($currency['currency_sign']) and $currency['currency_sign'] == 'after') ? 'selected="selected"' : ''); ?>><?php esc_html_e('10$ (After)', 'modern-events-calendar-lite'); ?></option>
                                <option
                                    value="after_space" <?php echo((isset($currency['currency_sign']) and $currency['currency_sign'] == 'after_space') ? 'selected="selected"' : ''); ?>><?php esc_html_e('10 $ (After with Space)', 'modern-events-calendar-lite'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="mec-form-row">
                        <label class="mec-col-3"
                               for="mec_currency_thousand_separator"><?php esc_html_e('Thousand Separator', 'modern-events-calendar-lite'); ?></label>
                        <div class="mec-col-4">
                            <input type="text" name="mec[currency][thousand_separator]"
                                   id="mec_currency_thousand_separator"
                                   value="<?php echo(isset($currency['thousand_separator']) ? esc_attr($currency['thousand_separator']) : ','); ?>"/>
                        </div>
                    </div>
                    <div class="mec-form-row">
                        <label class="mec-col-3"
                               for="mec_currency_decimal_separator"><?php esc_html_e('Decimal Separator', 'modern-events-calendar-lite'); ?></label>
                        <div class="mec-col-4">
                            <input type="text" name="mec[currency][decimal_separator]"
                                   id="mec_currency_decimal_separator"
                                   value="<?php echo(isset($currency['decimal_separator']) ? esc_attr($currency['decimal_separator']) : '.'); ?>"/>
                        </div>
                    </div>
                    <div class="mec-form-row">
                        <label class="mec-col-3"
                               for="mec_currency_decimals"><?php esc_html_e('Decimals', 'modern-events-calendar-lite'); ?></label>
                        <div class="mec-col-4">
                            <input type="number" name="mec[currency][currency_decimals]" id="mec_currency_decimals"
                                   value="<?php echo(isset($currency['currency_decimals']) ? esc_attr((int) $currency['currency_decimals']) : '2'); ?>"/>
                        </div>
                    </div>
                    <div class="mec-form-row">
                        <div class="mec-col-12">
                            <label for="mec_currency_decimal_separator_status">
                                <input type="hidden" name="mec[currency][decimal_separator_status]" value="1"/>
                                <input type="checkbox" name="mec[currency][decimal_separator_status]"
                                       id="mec_currency_decimal_separator_status" <?php echo((isset($currency['decimal_separator_status']) and $currency['decimal_separator_status'] == '0') ? 'checked="checked"' : ''); ?>
                                       value="0"/>
                                <?php esc_html_e('No decimal', 'modern-events-calendar-lite'); ?>
                            </label>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function meta_box_fields($post)
    {

        FormBuilder::event_data($post);
    }

    /**
     * Show exceptions options of event into the Add/Edit event page
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_exceptional_days($post)
    {
        $not_in_days_str = get_post_meta($post->ID, 'mec_not_in_days', true);
        $not_in_days = trim($not_in_days_str) ? explode(',', $not_in_days_str) : [];
        ?>
        <div class="mec-meta-box-fields mec-event-tab-content mec-fes-exceptional-days" id="mec-exceptional-days">
            <h4><?php esc_html_e('Exceptional Days (Exclude Dates)', 'modern-events-calendar-lite'); ?></h4>
            <div id="mec_meta_box_exceptions_form">
                <div id="mec_exceptions_not_in_days_container">
                    <div class="mec-title">
                        <span class="mec-dashicons dashicons dashicons-calendar-alt"></span>
                        <label
                            for="mec_exceptions_not_in_days_date"><?php esc_html_e('Exclude certain days', 'modern-events-calendar-lite'); ?></label>
                    </div>
                    <?php
                    $builder = $this->getFormBuilder();
                    $builder->exceptionalDays([
                        'values' => $not_in_days,
                    ]);
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Show hourly schedule options of event into the Add/Edit event page
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_hourly_schedule($post)
    {
        FormBuilder::hourly_schedule($post);
    }


    /**
     * Display Related Events in the Add/Edit event page
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_related_events($post)
    {
        FormBuilder::related_events($post);
    }

    /**
     * Display Event Banner in the Add/Edit event page
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_banner($post)
    {
        FormBuilder::banner($post);
    }

    /**
     * Display Event Gallery form in the Add/Edit event page
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_event_gallery($post)
    {
        $gallery = get_post_meta($post->ID, 'mec_event_gallery', true);
        if (!is_array($gallery)) $gallery = [];
        ?>
        <script>
            jQuery(document).ready(function () {
                <?php if(current_user_can('upload_files')): ?>
                jQuery('#mec_event_gallery_image_uploader').on('click', function (event) {
                    event.preventDefault();

                    var frame;
                    if (frame) {
                        frame.open();
                        return;
                    }

                    frame = wp.media({
                        multiple: true
                    });
                    frame.on('select', function () {
                        frame.state().get('selection').map(function (attachment) {
                            var image = attachment.toJSON();
                            var image_id = image.id;

                            jQuery('#mec_meta_box_event_gallery').append(`<li class="mec-event-gallery-wrapper-${image_id}" data-id="${image_id}">
                            <input type="hidden" name="mec[event_gallery][]" value="${image_id}" />
                            <img style="width: 200px;" src="${image.url}" alt="${image.url}" />
                            <span class="mec-event-gallery-delete" data-id="${image_id}">x</span>
                        </li>`);
                        });

                        frame.close();
                        mec_event_gallery_delete_listeners();
                    });

                    frame.open();
                });
                <?php else: ?>
                jQuery("#mec_event_gallery_image_uploader").on('change', function () {
                    var fd = new FormData();
                    fd.append("action", "mec_event_gallery_image_upload");
                    fd.append("_wpnonce", "<?php echo wp_create_nonce('mec_event_gallery_image_upload'); ?>");

                    // Append Images
                    jQuery.each(jQuery("#mec_event_gallery_image_uploader")[0].files, function (i, file) {
                        fd.append('images[]', file);
                    });

                    jQuery("#mec_event_gallery_error").html("").addClass("mec-util-hidden");
                    jQuery.ajax(
                        {
                            url: "<?php echo admin_url('admin-ajax.php', null); ?>",
                            type: "POST",
                            data: fd,
                            dataType: "json",
                            processData: false,
                            contentType: false
                        })
                    .done(function (response) {
                        if (response.success) {
                            var images = response.data;
                            for (var i = 0; i < response.data.length; i++) {
                                var image = images[i];
                                var image_id = image.id;

                                jQuery('#mec_meta_box_event_gallery').append(`<li class="mec-event-gallery-wrapper-${image_id}" data-id="${image_id}">
                                <input type="hidden" name="mec[event_gallery][]" value="${image_id}" />
                                <img style="width: 200px;" src="${image.url}" alt="${image.url}" />
                                <span class="mec-event-gallery-delete" data-id="${image_id}">x</span>
                            </li>`);
                            }

                            mec_event_gallery_delete_listeners();
                        } else {
                            jQuery("#mec_event_gallery_error").html(response.message).removeClass("mec-util-hidden");
                        }

                        // Reset File Input
                        jQuery("#mec_event_gallery_image_uploader").val('');
                    });

                    return false;
                });
                <?php endif; ?>
                function mec_event_gallery_delete_listeners() {
                    jQuery('.mec-event-gallery-delete').off('click').on('click', function () {
                        var id = jQuery(this).data('id');
                        jQuery('.mec-event-gallery-wrapper-' + id).remove();
                    });
                }

                mec_event_gallery_delete_listeners();
            });
        </script>
        <div id="mec-event-gallery">
            <div id="mec_meta_box_event_gallery_options">
                <?php if (current_user_can('upload_files')): ?>
                    <a href="#" type="button" id="mec_event_gallery_image_uploader"
                       data-post-id="<?php echo esc_attr($post->ID); ?>"><?php esc_html_e('Add event gallery images', 'modern-events-calendar-lite'); ?></a>
                <?php else: ?>
                    <a href="#" type="file" id="mec_event_gallery_image_uploader"
                       multiple><?php esc_html_e('Add event gallery images', 'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
                <p class="description"><?php esc_html_e('png, jpg, gif, and webp files are allowed.', 'modern-events-calendar-lite'); ?></p>
                <div class="mec-error mec-util-hidden" id="mec_event_gallery_error"></div>
            </div>
            <ul id="mec_meta_box_event_gallery">
                <?php foreach ($gallery as $image_id): $image_url = wp_get_attachment_url($image_id); ?>
                    <li class="mec-event-gallery-wrapper-<?php echo esc_attr($image_id); ?>"
                        data-id="<?php echo esc_attr($image_id); ?>">
                        <input type="hidden" name="mec[event_gallery][]" value="<?php echo esc_attr($image_id); ?>"/>
                        <img style="width: 200px;" src="<?php echo esc_url($image_url); ?>"
                             alt="<?php echo esc_url($image_url); ?>"/>
                        <span class="mec-event-gallery-delete" data-id="<?php echo esc_attr($image_id); ?>">x</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    /**
     * Show read more option of event into the Add/Edit event page
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_links($post)
    {
        $read_more = get_post_meta($post->ID, 'mec_read_more', true);
        $more_info = get_post_meta($post->ID, 'mec_more_info', true);
        $more_info_title = get_post_meta($post->ID, 'mec_more_info_title', true);
        $more_info_target = get_post_meta($post->ID, 'mec_more_info_target', true);

        $trailer_url_status = isset($this->settings['trailer_url_status']) && $this->settings['trailer_url_status'];

        $trailer_url = get_post_meta($post->ID, 'mec_trailer_url', true);
        $trailer_title = get_post_meta($post->ID, 'mec_trailer_title', true);
        ?>
        <div class="mec-meta-box-fields mec-event-tab-content mec-fes-event-links" id="mec-read-more">
            <h4><?php esc_html_e('Event Links', 'modern-events-calendar-lite'); ?></h4>
            <div class="mec-form-row">
                <label class="mec-col-2"
                       for="mec_read_more_link"><?php echo esc_html($this->main->m('read_more_link', esc_html__('Event Link', 'modern-events-calendar-lite'))); ?></label>
                <input class="mec-col-8" type="text" name="mec[read_more]" id="mec_read_more_link"
                       value="<?php echo esc_attr($read_more); ?>"
                       placeholder="<?php esc_html_e('eg. http://yoursite.com/your-event', 'modern-events-calendar-lite'); ?>"/>
                <?php do_action('extra_event_link', $post->ID); ?>

                <span class="mec-tooltip">
					<div class="box top">
						<h5 class="title"><?php esc_html_e('Event Link', 'modern-events-calendar-lite'); ?></h5>
						<div class="content">
                            <p><?php esc_attr_e('The value of this option will be replaced by the single event page link on shortcodes. Insert full link including http(s):// - Also, if you use an advertising URL, you can use the URL Shortener.', 'modern-events-calendar-lite'); ?>
                                <a href="https://bit.ly/" target="_blank"
                                   rel="noopener noreferrer"><?php esc_html_e('URL Shortener', 'modern-events-calendar-lite'); ?></a>
                            </p>
                        </div>
					</div>
					<i title="" class="dashicons-before dashicons-editor-help"></i>
				</span>
            </div>
            <div class="mec-form-row">
                <label class="mec-col-2"
                       for="mec_more_info_link"><?php echo esc_html($this->main->m('more_info_link', esc_html__('More Info', 'modern-events-calendar-lite'))); ?></label>
                <input class="mec-col-3" type="text" name="mec[more_info]" id="mec_more_info_link"
                       value="<?php echo esc_attr($more_info); ?>"
                       placeholder="<?php esc_html_e('eg. http://yoursite.com/your-event', 'modern-events-calendar-lite'); ?>"/>
                <input class="mec-col-2" type="text" name="mec[more_info_title]" id="mec_more_info_title"
                       value="<?php echo esc_attr($more_info_title); ?>"
                       placeholder="<?php esc_html_e('More Information', 'modern-events-calendar-lite'); ?>"/>
                <select class="mec-col-2" name="mec[more_info_target]" id="mec_more_info_target">
                    <option
                        value="_self" <?php echo($more_info_target == '_self' ? 'selected="selected"' : ''); ?>><?php esc_html_e('Current Window', 'modern-events-calendar-lite'); ?></option>
                    <option
                        value="_blank" <?php echo($more_info_target == '_blank' ? 'selected="selected"' : ''); ?>><?php esc_html_e('New Window', 'modern-events-calendar-lite'); ?></option>
                </select>
                <span class="mec-tooltip">
					<div class="box top">
						<h5 class="title"><?php esc_html_e('More Info', 'modern-events-calendar-lite'); ?></h5>
						<div class="content">
                            <p><?php esc_attr_e('This link will appear on the single event page. Insert full link including http(s)://', 'modern-events-calendar-lite'); ?>
                                <a href="https://webnus.net/dox/modern-events-calendar/add-event/#Links"
                                   target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite'); ?></a>
                            </p>
                        </div>
					</div>
					<i title="" class="dashicons-before dashicons-editor-help"></i>
				</span>
            </div>
            <?php if ($trailer_url_status): ?>
                <h4><?php esc_html_e('Trailer URL', 'modern-events-calendar-lite'); ?></h4>
                <div class="mec-form-row">
                    <div class="mec-col-6" style="padding-right: 10px;">
                        <input name="mec[trailer_url]" id="mec_trailer_url"
                               title="<?php esc_attr_e('Trailer URL', 'modern-events-calendar-lite'); ?>" type="url"
                               value="<?php echo trim($trailer_url) ? esc_url($trailer_url) : ''; ?>" class="widefat"
                               placeholder="http://">
                    </div>
                    <div class="mec-col-5">
                        <input name="mec[trailer_title]" id="mec_trailer_title"
                               title="<?php esc_attr_e('Trailer Title', 'modern-events-calendar-lite'); ?>" type="text"
                               value="<?php echo esc_attr($trailer_title); ?>" class="widefat"
                               placeholder="<?php esc_attr_e('Trailer Title', 'modern-events-calendar-lite'); ?>">
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Show booking meta box contents
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_booking($post)
    {
        $gateway_settings = $this->main->get_gateways_options();
        ?>
        <div class="mec-add-booking-tabs-wrap">
            <div class="mec-add-booking-tabs-left">
                <a class="mec-add-booking-tabs-link mec-tab-active" data-href="mec_meta_box_booking_options"
                   href="#"><?php echo esc_html__('Booking Options', 'modern-events-calendar-lite'); ?></a>
                <a class="mec-add-booking-tabs-link" data-href="mec-tickets"
                   href="#"><?php echo esc_html__('Tickets', 'modern-events-calendar-lite'); ?></a>
                <?php if (isset($this->settings['taxes_fees_status']) and $this->settings['taxes_fees_status']): ?>
                    <a class="mec-add-booking-tabs-link" data-href="mec-fees"
                       href="#"><?php echo esc_html__('Fees', 'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
                <?php if (isset($this->settings['ticket_variations_status']) and $this->settings['ticket_variations_status']): ?>
                    <a class="mec-add-booking-tabs-link" data-href="mec-ticket-variations"
                       href="#"><?php echo esc_html__('Ticket Variations / Options', 'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
                <a class="mec-add-booking-tabs-link" data-href="mec-reg-fields"
                   href="#"><?php echo esc_html__('Booking Form', 'modern-events-calendar-lite'); ?></a>
                <?php if (isset($gateway_settings['op_status']) && $gateway_settings['op_status'] == 1): ?>
                    <a class="mec-add-booking-tabs-link" data-href="mec_meta_box_op_form"
                       href="#"><?php echo esc_html__('Organizer Payment', 'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
                <?php if (isset($this->settings['downloadable_file_status']) and $this->settings['downloadable_file_status']): ?>
                    <a class="mec-add-booking-tabs-link" data-href="mec-downloadable-file"
                       href="#"><?php echo esc_html__('Downloadable File', 'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
                <?php if (isset($gateway_settings['gateways_per_event']) and $gateway_settings['gateways_per_event']): ?>
                    <a class="mec-add-booking-tabs-link"
                       data-href="mec_meta_box_booking_options_form_gateways_per_event"
                       href="#"><?php echo esc_html__('Payment Gateways', 'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
                <a class="mec-add-booking-tabs-link" data-href="mec_meta_box_booking_options_form_attendees"
                   href="#"><?php echo esc_html__('Bookings', 'modern-events-calendar-lite'); ?></a>
                <?php do_action('add_event_booking_sections_left_menu'); ?>
            </div>
            <div class="mec-add-booking-tabs-right">
                <?php do_action('mec_metabox_booking', $post); ?>
            </div>
        </div>
        <script>
            jQuery(".mec-add-booking-tabs-link").on("click", function (e) {
                e.preventDefault();
                var href = jQuery(this).attr("data-href");
                jQuery(".mec-booking-tab-content,.mec-add-booking-tabs-link").removeClass("mec-tab-active");
                jQuery(this).addClass("mec-tab-active");
                jQuery("#" + href).addClass("mec-tab-active");
            });
        </script>
        <?php
    }

    /**
     * Show booking options of event into the Add/Edit event page
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_booking_options($post)
    {
        $FES = !is_admin();

        $booking_options = get_post_meta($post->ID, 'mec_booking', true);
        if (!is_array($booking_options)) $booking_options = [];

        $fes_booking_tbl = (!isset($this->settings['fes_section_booking_tbl']) or (isset($this->settings['fes_section_booking_tbl']) and $this->settings['fes_section_booking_tbl']));
        $fes_booking_dspe = (!isset($this->settings['fes_section_booking_dspe']) or (isset($this->settings['fes_section_booking_dspe']) and $this->settings['fes_section_booking_dspe']));
        $fes_booking_mtpb = (!isset($this->settings['fes_section_booking_mtpb']) or (isset($this->settings['fes_section_booking_mtpb']) and $this->settings['fes_section_booking_mtpb']));
        $fes_booking_dpur = (!isset($this->settings['fes_section_booking_dpur']) or (isset($this->settings['fes_section_booking_dpur']) and $this->settings['fes_section_booking_dpur']));
        $fes_booking_bao = (!isset($this->settings['fes_section_booking_bao']) or (isset($this->settings['fes_section_booking_bao']) and $this->settings['fes_section_booking_bao']));
        $fes_booking_io = (!isset($this->settings['fes_section_booking_io']) or (isset($this->settings['fes_section_booking_io']) and $this->settings['fes_section_booking_io']));
        $fes_booking_aa = (!isset($this->settings['fes_section_booking_aa']) or (isset($this->settings['fes_section_booking_aa']) and $this->settings['fes_section_booking_aa']));
        $fes_booking_lftp = (!isset($this->settings['fes_section_booking_lftp']) or (isset($this->settings['fes_section_booking_lftp']) and $this->settings['fes_section_booking_lftp']));
        $fes_booking_typ = (!isset($this->settings['fes_section_booking_typ']) or (isset($this->settings['fes_section_booking_typ']) and $this->settings['fes_section_booking_typ']));
        $fes_booking_bbl = (!isset($this->settings['fes_section_booking_bbl']) or (isset($this->settings['fes_section_booking_bbl']) and $this->settings['fes_section_booking_bbl']));
        $fes_booking_tubl = (!isset($this->settings['fes_section_booking_tubl']) or (isset($this->settings['fes_section_booking_tubl']) and $this->settings['fes_section_booking_tubl']));

        $dpur_status = (!isset($this->settings['discount_per_user_role_status']) or (isset($this->settings['discount_per_user_role_status']) and $this->settings['discount_per_user_role_status']));

        $partial_payment = $this->getPartialPayment();
        $fes_booking_pp = $partial_payment->is_fes_pp_section_enabled();
        ?>
        <div id="mec-booking">
            <?php if (!$FES or ($FES and ($fes_booking_tbl or $fes_booking_mtpb or $fes_booking_dpur or $fes_booking_bao or $fes_booking_io or $fes_booking_aa or $fes_booking_lftp or $fes_booking_typ or $fes_booking_bbl or $fes_booking_pp))): ?>
                <div class="mec-booking-tab-content mec-tab-active mec-fes-booking-options"
                     id="mec_meta_box_booking_options">

                    <?php if (!$FES): ?>
                        <div class="mec-backend-tab-wrap mec-basvanced-toggle" data-for="#mec-booking"
                             style="margin-top: 40px;">
                            <div class="mec-backend-tab">
                                <div
                                    class="mec-backend-tab-item mec-b-active-tab"><?php esc_html_e('Basic', 'modern-events-calendar-lite'); ?></div>
                                <div class="mec-backend-tab-item"><?php esc_html_e('Advanced', 'modern-events-calendar-lite'); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mec-basvanced-basic">
                        <?php
                        if (!$FES or ($FES and $fes_booking_tbl))
                        {

                            FormBuilder::total_booking_limit($post);
                        }

                        if (!$FES or ($FES and $fes_booking_mtpb))
                        {

                            FormBuilder::minimum_ticket_per_booking($post);
                        }
                        ?>
                    </div>
                    <div class="mec-basvanced-advanced w-hidden">
                        <?php
                        if (isset($this->settings['booking_date_selection_per_event']) and $this->settings['booking_date_selection_per_event'] and (!$FES or ($FES and $fes_booking_dspe)))
                        {

                            FormBuilder::booking_date_selection($post);
                        }

                        if ($dpur_status and (!$FES or ($FES and $fes_booking_dpur)))
                        {

                            FormBuilder::discount_per_user_roles($post);
                        }

                        if (!$FES or ($FES and $fes_booking_bao))
                        {

                            FormBuilder::book_all_occurrences($post);
                        }

                        if (!$FES or ($FES and $fes_booking_io))
                        {

                            FormBuilder::interval_options($post);
                        }

                        if (!$FES or ($FES and $fes_booking_aa))
                        {

                            FormBuilder::automatic_approval($post);
                        }

                        if ((isset($this->settings['last_few_tickets_percentage_per_event']) && $this->settings['last_few_tickets_percentage_per_event']) or ($FES and $fes_booking_lftp))
                        {

                            FormBuilder::last_few_tickets_percentage($post);
                        }

                        if ((isset($this->settings['thankyou_page_per_event']) && $this->settings['thankyou_page_per_event']) && (!$FES || $fes_booking_typ))
                        {

                            FormBuilder::thankyou_page($post);
                        }

                        if (!$FES or ($FES and $fes_booking_bbl))
                        {

                            FormBuilder::booking_button_label($post);
                        }

                        if (!$FES or ($FES and $fes_booking_pp))
                        {

                            FormBuilder::booking_partial_payment($post);
                        }

                        if (!$FES or ($FES and $fes_booking_tubl))
                        {
                            FormBuilder::total_user_booking_limits($post);
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            FormBuilder::gateways($post);
            ?>
        </div>
        <?php
    }

    /**
     * Show tickets options of event into the Add/Edit event page
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_tickets($post)
    {

        FormBuilder::tickets($post);
    }

    /**
     * Show fees of event into the Add/Edit event page
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_fees($post)
    {

        FormBuilder::fees($post);
    }

    /**
     * Show ticket variations into the Add/Edit event page
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_ticket_variations($post)
    {

        FormBuilder::ticket_variations($post);
    }

    /**
     * Show registration form of event into the Add/Edit event page
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_regform($post)
    {

        FormBuilder::booking_form($post);
    }

    /**
     * Show attendees of event into the Add/Edit event page
     *
     * @param WP_Post $post
     * @author Webnus <info@webnus.net>
     */
    public function meta_box_attendees($post)
    {

        FormBuilder::attendees($post);
    }

    /**
     * Save event data
     *
     * @param int $post_id
     * @return void
     * @author Webnus <info@webnus.net>
     */
    public function save_event($post_id)
    {
        // Check if our nonce is set.
        if (!isset($_POST['mec_event_nonce'])) return;

        // It's from FES
        if (isset($_POST['action']) and sanitize_text_field($_POST['action']) === 'mec_fes_form') return;

        // Verify that the nonce is valid.
        if (!wp_verify_nonce(sanitize_text_field($_POST['mec_event_nonce']), 'mec_event_data')) return;

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        // Get Modern Events Calendar Data
        $_mec = isset($_POST['mec']) ? $this->main->sanitize_deep_array($_POST['mec']) : [];

        // Entity Type
        $entity_type = isset($_mec['entity_type']) && in_array($_mec['entity_type'], ['event', 'appointment']) ? $_mec['entity_type'] : 'event';

        $start_date = (isset($_mec['date']['start']['date']) and trim($_mec['date']['start']['date'])) ? $this->main->standardize_format(sanitize_text_field($_mec['date']['start']['date'])) : date('Y-m-d');
        $end_date = (isset($_mec['date']['end']['date']) and trim($_mec['date']['end']['date'])) ? $this->main->standardize_format(sanitize_text_field($_mec['date']['end']['date'])) : date('Y-m-d');

        // Remove Cached Data
        wp_cache_delete($post_id, 'mec-events-data');

        $location_id = isset($_mec['location_id']) ? sanitize_text_field($_mec['location_id']) : 0;
        $dont_show_map = isset($_mec['dont_show_map']) ? sanitize_text_field($_mec['dont_show_map']) : 0;
        $organizer_id = isset($_mec['organizer_id']) ? sanitize_text_field($_mec['organizer_id']) : 0;
        $read_more = isset($_mec['read_more']) ? sanitize_url($_mec['read_more']) : '';
        $more_info = (isset($_mec['more_info']) and trim($_mec['more_info'])) ? sanitize_url($_mec['more_info']) : '';
        $more_info_title = isset($_mec['more_info_title']) ? sanitize_text_field($_mec['more_info_title']) : '';
        $more_info_target = isset($_mec['more_info_target']) ? sanitize_text_field($_mec['more_info_target']) : '';

        $cost = isset($_mec['cost']) ? sanitize_text_field($_mec['cost']) : '';
        $cost = apply_filters(
            'mec_event_cost_sanitize',
            sanitize_text_field($cost),
            $cost
        );

        $cost_auto_calculate = (isset($_mec['cost_auto_calculate']) ? sanitize_text_field($_mec['cost_auto_calculate']) : 0);
        $currency_options = ((isset($_mec['currency']) and is_array($_mec['currency'])) ? $_mec['currency'] : []);

        update_post_meta($post_id, 'mec_entity_type', $entity_type);
        update_post_meta($post_id, 'mec_location_id', $location_id);
        update_post_meta($post_id, 'mec_dont_show_map', $dont_show_map);
        update_post_meta($post_id, 'mec_organizer_id', $organizer_id);
        update_post_meta($post_id, 'mec_read_more', $read_more);
        update_post_meta($post_id, 'mec_more_info', $more_info);
        update_post_meta($post_id, 'mec_more_info_title', $more_info_title);
        update_post_meta($post_id, 'mec_more_info_target', $more_info_target);
        update_post_meta($post_id, 'mec_cost', $cost);
        update_post_meta($post_id, 'mec_cost_auto_calculate', $cost_auto_calculate);
        update_post_meta($post_id, 'mec_currency', $currency_options);

        do_action('update_custom_dev_post_meta', $_mec, $post_id);

        // Additional Organizers
        $additional_organizer_ids = $_mec['additional_organizer_ids'] ?? [];

        foreach ($additional_organizer_ids as $additional_organizer_id) wp_set_object_terms($post_id, (int) $additional_organizer_id, 'mec_organizer', true);
        update_post_meta($post_id, 'mec_additional_organizer_ids', $additional_organizer_ids);

        // Additional locations
        $additional_location_ids = $_mec['additional_location_ids'] ?? [];

        foreach ($additional_location_ids as $additional_location_id) wp_set_object_terms($post_id, (int) $additional_location_id, 'mec_location', true);
        update_post_meta($post_id, 'mec_additional_location_ids', $additional_location_ids);

        // Date Options
        $date = $_mec['date'] ?? [];

        $start_date = date('Y-m-d', strtotime($start_date));

        // Fix Date End & Event Finish
        if (isset($date['repeat']['status']) && $date['repeat']['status'])
        {
            $days_diff = $this->main->get_days_diff($start_date, $end_date);

            // Daily
            if (isset($date['repeat']['type']) && $date['repeat']['type'] === 'daily' && $days_diff >= 5)
            {
                $date['repeat']['end'] = 'date';
                $date['repeat']['end_at_date'] = $end_date;

                $end_date = $start_date;
            }
            // Weekly
            else if (isset($date['repeat']['type']) && $date['repeat']['type'] === 'weekly' && $days_diff >= 15)
            {
                $date['repeat']['end'] = 'date';
                $date['repeat']['end_at_date'] = $end_date;

                $end_date = $start_date;
            }
        }

        // Set the start date
        $date['start']['date'] = $start_date;

        $start_time_hour = isset($date['start']) ? sanitize_text_field($date['start']['hour']) : '8';
        $start_time_minutes = isset($date['start']) ? sanitize_text_field($date['start']['minutes']) : '00';
        $start_time_ampm = (isset($date['start']) and isset($date['start']['ampm'])) ? sanitize_text_field($date['start']['ampm']) : 'AM';

        $end_date = date('Y-m-d', strtotime($end_date));

        // Fix end_date if it's smaller than start_date
        if (strtotime($end_date) < strtotime($start_date)) $end_date = $start_date;

        // Set the end date
        $date['end']['date'] = $end_date;

        $end_time_hour = isset($date['end']) ? sanitize_text_field($date['end']['hour']) : '6';
        $end_time_minutes = isset($date['end']) ? sanitize_text_field($date['end']['minutes']) : '00';
        $end_time_ampm = (isset($date['end']) and isset($date['end']['ampm'])) ? sanitize_text_field($date['end']['ampm']) : 'PM';

        if (isset($this->settings['time_format']) and $this->settings['time_format'] == 24)
        {
            $day_start_seconds = $this->main->time_to_seconds($this->main->to_24hours($start_time_hour, null, 'start'), $start_time_minutes);
            $day_end_seconds = $this->main->time_to_seconds($this->main->to_24hours($end_time_hour, null), $end_time_minutes);
        }
        else
        {
            $day_start_seconds = $this->main->time_to_seconds($this->main->to_24hours($start_time_hour, $start_time_ampm, 'start'), $start_time_minutes);
            $day_end_seconds = $this->main->time_to_seconds($this->main->to_24hours($end_time_hour, $end_time_ampm), $end_time_minutes);
        }

        if ($end_date === $start_date and $day_end_seconds < $day_start_seconds)
        {
            $day_end_seconds = $day_start_seconds;

            $end_time_hour = $start_time_hour;
            $end_time_minutes = $start_time_minutes;
            $end_time_ampm = $start_time_ampm;

            $date['end']['hour'] = $start_time_hour;
            $date['end']['minutes'] = $start_time_minutes;
            $date['end']['ampm'] = $start_time_ampm;
        }

        // If 24 hours format is enabled then convert it back to 12 hours
        if (isset($this->settings['time_format']) and $this->settings['time_format'] == 24)
        {
            if ($start_time_hour < 12) $start_time_ampm = 'AM';
            else if ($start_time_hour == 12) $start_time_ampm = 'PM';
            else if ($start_time_hour > 12)
            {
                $start_time_hour -= 12;
                $start_time_ampm = 'PM';
            }

            if ($start_time_hour == 0)
            {
                $start_time_hour = 12;
                $start_time_ampm = 'AM';
            }

            if ($end_time_hour < 12) $end_time_ampm = 'AM';
            else if ($end_time_hour == 12) $end_time_ampm = 'PM';
            else if ($end_time_hour > 12)
            {
                $end_time_hour -= 12;
                $end_time_ampm = 'PM';
            }

            if ($end_time_hour == 0)
            {
                $end_time_hour = 12;
                $end_time_ampm = 'AM';
            }

            // Set converted values to date array
            $date['start']['hour'] = $start_time_hour;
            $date['start']['ampm'] = $start_time_ampm;

            $date['end']['hour'] = $end_time_hour;
            $date['end']['ampm'] = $end_time_ampm;
        }

        $allday = isset($date['allday']) ? 1 : 0;
        $one_occurrence = isset($date['one_occurrence']) ? 1 : 0;
        $hide_time = isset($date['hide_time']) ? 1 : 0;
        $hide_end_time = isset($date['hide_end_time']) ? 1 : 0;
        $comment = isset($date['comment']) ? sanitize_text_field($date['comment']) : '';
        $timezone = (isset($_mec['timezone']) and trim($_mec['timezone']) != '') ? sanitize_text_field($_mec['timezone']) : 'global';
        $countdown_method = (isset($_mec['countdown_method']) and trim($_mec['countdown_method']) != '') ? sanitize_text_field($_mec['countdown_method']) : 'global';
        $style_per_event = (isset($_mec['style_per_event']) and trim($_mec['style_per_event']) != '') ? sanitize_text_field($_mec['style_per_event']) : 'global';
        $trailer_url = (isset($_mec['trailer_url']) and trim($_mec['trailer_url']) != '') ? sanitize_url($_mec['trailer_url']) : '';
        $trailer_title = isset($_mec['trailer_title']) ? sanitize_text_field($_mec['trailer_title']) : '';
        $public = (isset($_mec['public']) and trim($_mec['public']) != '') ? sanitize_text_field($_mec['public']) : 1;

        // Set start time and end time if event is all day
        if ($allday == 1)
        {
            $start_time_hour = '8';
            $start_time_minutes = '00';
            $start_time_ampm = 'AM';

            $end_time_hour = '6';
            $end_time_minutes = '00';
            $end_time_ampm = 'PM';
        }

        // Repeat Options
        $repeat = $date['repeat'] ?? [];
        $certain_weekdays = $repeat['certain_weekdays'] ?? [];

        $repeat_status = isset($repeat['status']) ? 1 : 0;
        $repeat_type = ($repeat_status and isset($repeat['type'])) ? $repeat['type'] : '';

        // Unset Repeat if no days are selected
        if ($repeat_type == 'certain_weekdays' && (!is_array($certain_weekdays) || !count($certain_weekdays)))
        {
            $repeat_status = 0;
            $repeat['status'] = 0;
            $repeat['type'] = '';
        }

        $repeat_interval = ($repeat_status and isset($repeat['interval']) and trim($repeat['interval'])) ? $repeat['interval'] : 1;

        // Advanced Repeat
        $advanced = isset($repeat['advanced']) ? sanitize_text_field($repeat['advanced']) : '';

        if (!is_numeric($repeat_interval)) $repeat_interval = null;

        if ($repeat_type == 'weekly') $interval_multiply = 7;
        else $interval_multiply = 1;

        // Reset certain weekdays if repeat type is not set to certain weekdays
        if ($repeat_type != 'certain_weekdays') $certain_weekdays = [];

        if (!is_null($repeat_interval)) $repeat_interval = $repeat_interval * $interval_multiply;

        // String To Array
        if ($repeat_type == 'advanced' and trim($advanced)) $advanced = explode('-', $advanced);
        else $advanced = [];

        $repeat_end = ($repeat_status and isset($repeat['end'])) ? $repeat['end'] : '';
        $repeat_end_at_occurrences = ($repeat_status && isset($repeat['end_at_occurrences']) && is_numeric($repeat['end_at_occurrences'])) ? $repeat['end_at_occurrences'] - 1 : 9;
        $repeat_end_at_date = ($repeat_status and isset($repeat['end_at_date'])) ? $this->main->standardize_format($repeat['end_at_date']) : '';

        // Previous Date Times
        $prev_start_datetime = get_post_meta($post_id, 'mec_start_datetime', true);
        $prev_end_datetime = get_post_meta($post_id, 'mec_end_datetime', true);

        $start_datetime = $start_date . ' ' . sprintf('%02d', $start_time_hour) . ':' . sprintf('%02d', $start_time_minutes) . ' ' . $start_time_ampm;
        $end_datetime = $end_date . ' ' . sprintf('%02d', $end_time_hour) . ':' . sprintf('%02d', $end_time_minutes) . ' ' . $end_time_ampm;

        update_post_meta($post_id, 'mec_date', $date);
        update_post_meta($post_id, 'mec_repeat', $repeat);
        update_post_meta($post_id, 'mec_certain_weekdays', $certain_weekdays);
        update_post_meta($post_id, 'mec_allday', $allday);
        update_post_meta($post_id, 'one_occurrence', $one_occurrence);
        update_post_meta($post_id, 'mec_hide_time', $hide_time);
        update_post_meta($post_id, 'mec_hide_end_time', $hide_end_time);
        update_post_meta($post_id, 'mec_comment', $comment);
        update_post_meta($post_id, 'mec_timezone', $timezone);
        update_post_meta($post_id, 'mec_countdown_method', $countdown_method);
        update_post_meta($post_id, 'mec_style_per_event', $style_per_event);
        update_post_meta($post_id, 'mec_trailer_url', $trailer_url);
        update_post_meta($post_id, 'mec_trailer_title', $trailer_title);
        update_post_meta($post_id, 'mec_public', $public);

        do_action('update_custom_post_meta', $date, $post_id);

        update_post_meta($post_id, 'mec_start_date', $start_date);
        update_post_meta($post_id, 'mec_start_time_hour', $start_time_hour);
        update_post_meta($post_id, 'mec_start_time_minutes', $start_time_minutes);
        update_post_meta($post_id, 'mec_start_time_ampm', $start_time_ampm);
        update_post_meta($post_id, 'mec_start_day_seconds', $day_start_seconds);
        update_post_meta($post_id, 'mec_start_datetime', $start_datetime);

        update_post_meta($post_id, 'mec_end_date', $end_date);
        update_post_meta($post_id, 'mec_end_time_hour', $end_time_hour);
        update_post_meta($post_id, 'mec_end_time_minutes', $end_time_minutes);
        update_post_meta($post_id, 'mec_end_time_ampm', $end_time_ampm);
        update_post_meta($post_id, 'mec_end_day_seconds', $day_end_seconds);
        update_post_meta($post_id, 'mec_end_datetime', $end_datetime);

        update_post_meta($post_id, 'mec_repeat_status', $repeat_status);
        update_post_meta($post_id, 'mec_repeat_type', $repeat_type);
        update_post_meta($post_id, 'mec_repeat_interval', $repeat_interval);
        update_post_meta($post_id, 'mec_repeat_end', $repeat_end);
        update_post_meta($post_id, 'mec_repeat_end_at_occurrences', $repeat_end_at_occurrences);
        update_post_meta($post_id, 'mec_repeat_end_at_date', $repeat_end_at_date);
        update_post_meta($post_id, 'mec_advanced_days', $advanced);

        // Event Sequence (Used in iCal feed)
        $sequence = (int) get_post_meta($post_id, 'mec_sequence', true);
        update_post_meta($post_id, 'mec_sequence', ($sequence + 1));

        // For Event Notification Badge.
        if (!current_user_can('administrator')) update_post_meta($post_id, 'mec_event_date_submit', date('YmdHis', current_time('timestamp', 0)));

        // Creating $event array for inserting in mec_events table
        $event = [
            'post_id' => $post_id,
            'start' => $start_date,
            'repeat' => $repeat_status,
            'rinterval' => (!in_array($repeat_type, ['daily', 'weekly', 'monthly']) ? null : $repeat_interval),
            'time_start' => $day_start_seconds,
            'time_end' => $day_end_seconds,
        ];

        $year = null;
        $month = null;
        $day = null;
        $week = null;
        $weekday = null;
        $weekdays = null;

        // MEC weekdays
        $mec_weekdays = $this->main->get_weekdays();

        // MEC weekends
        $mec_weekends = $this->main->get_weekends();

        $plus_date = '';
        if ($repeat_type == 'daily')
        {
            $plus_date = '+' . $repeat_end_at_occurrences * $repeat_interval . ' Days';
        }
        else if ($repeat_type == 'weekly')
        {
            $plus_date = '+' . $repeat_end_at_occurrences * ($repeat_interval) . ' Days';
        }
        else if ($repeat_type == 'weekday')
        {
            $repeat_interval = 1;
            $plus_date = '+' . $repeat_end_at_occurrences * $repeat_interval . ' Weekdays';

            $weekdays = ',' . implode(',', $mec_weekdays) . ',';
        }
        else if ($repeat_type == 'weekend')
        {
            $repeat_interval = 1;
            $plus_date = '+' . round($repeat_end_at_occurrences / 2) * ($repeat_interval * 7) . ' Days';

            $weekdays = ',' . implode(',', $mec_weekends) . ',';
        }
        else if ($repeat_type == 'certain_weekdays')
        {
            $repeat_interval = 1;
            $plus_date = '+' . ceil(($repeat_end_at_occurrences * $repeat_interval) * (7 / count($certain_weekdays))) . ' days';

            $weekdays = ',' . implode(',', $certain_weekdays) . ',';
        }
        else if ($repeat_type == 'monthly')
        {
            $plus_date = '+' . $repeat_end_at_occurrences * $repeat_interval . ' Months';

            $year = '*';
            $month = '*';

            $s = $start_date;
            $e = $end_date;

            $_days = [];
            while (strtotime($s) <= strtotime($e))
            {
                $_days[] = date('d', strtotime($s));
                $s = date('Y-m-d', strtotime('+1 Day', strtotime($s)));
            }

            $day = ',' . implode(',', array_unique($_days)) . ',';

            $week = '*';
            $weekday = '*';
        }
        else if ($repeat_type == 'yearly')
        {
            $plus_date = '+' . $repeat_end_at_occurrences * $repeat_interval . ' Years';

            $year = '*';

            $s = $start_date;
            $e = $end_date;

            $_months = [];
            $_days = [];
            while (strtotime($s) <= strtotime($e))
            {
                $_months[] = date('m', strtotime($s));
                $_days[] = date('d', strtotime($s));

                $s = date('Y-m-d', strtotime('+1 Day', strtotime($s)));
            }

            $_months = array_unique($_months);

            $month = ',' . implode(',', [$_months[0]]) . ',';
            $day = ',' . implode(',', array_unique($_days)) . ',';

            $week = '*';
            $weekday = '*';
        }
        else if ($repeat_type == "advanced")
        {
            // Render class object
            $this->render = $this->getRender();

            // Get finish date
            $event_info = ['start' => $date['start'], 'end' => $date['end']];
            $dates = $this->render->generate_advanced_days($advanced, $event_info, $repeat_end_at_occurrences, $start_date, 'events');

            $period_date = $this->main->date_diff($start_date, end($dates)['end']['date']);
            $plus_date = '+' . $period_date->days . ' Days';
        }

        // In Days
        $in_days_arr = isset($_mec['in_days']) && is_array($_mec['in_days']) && count($_mec['in_days']) ? array_unique($_mec['in_days']) : [];
        $not_in_days_arr = isset($_mec['not_in_days']) && is_array($_mec['not_in_days']) && count($_mec['not_in_days']) ? array_unique($_mec['not_in_days']) : [];

        $in_days = '';
        if (count($in_days_arr))
        {
            if (isset($in_days_arr[':i:'])) unset($in_days_arr[':i:']);

            $in_days_arr = array_map(function ($value)
            {
                $ex = explode(':', $value);

                $in_days_times = '';
                if (isset($ex[2]) and isset($ex[3]))
                {
                    $in_days_start_time = $ex[2];
                    $in_days_end_time = $ex[3];

                    // If 24 hours format is enabled then convert it back to 12 hours
                    if (isset($this->settings['time_format']) && $this->settings['time_format'] == 24)
                    {
                        $ex_start_time = explode('-', $in_days_start_time);
                        $ex_end_time = explode('-', $in_days_end_time);

                        $in_days_start_hour = $ex_start_time[0];
                        $in_days_start_minutes = $ex_start_time[1];
                        $in_days_start_ampm = $ex_start_time[2];

                        $in_days_end_hour = $ex_end_time[0];
                        $in_days_end_minutes = $ex_end_time[1];
                        $in_days_end_ampm = $ex_end_time[2];

                        if (trim($in_days_start_ampm) === '')
                        {
                            if ($in_days_start_hour < 12)
                            {
                                $in_days_start_ampm = 'AM';

                                if ($in_days_start_hour == 0 || $in_days_start_hour == '00')
                                {
                                    $in_days_start_hour = 12;
                                }
                            }
                            else if ($in_days_start_hour == 12) $in_days_start_ampm = 'PM';
                            else if ($in_days_start_hour > 12)
                            {
                                $in_days_start_hour -= 12;
                                $in_days_start_ampm = 'PM';
                            }
                        }

                        if (trim($in_days_end_ampm) === '')
                        {
                            if ($in_days_end_hour < 12)
                            {
                                $in_days_end_ampm = 'AM';

                                if ($in_days_end_hour == 0 || $in_days_end_hour == '00')
                                {
                                    $in_days_end_hour = 12;
                                }
                            }
                            else if ($in_days_end_hour == 12) $in_days_end_ampm = 'PM';
                            else if ($in_days_end_hour > 12)
                            {
                                $in_days_end_hour -= 12;
                                $in_days_end_ampm = 'PM';
                            }
                        }

                        if (strlen($in_days_start_hour) == 1) $in_days_start_hour = '0' . $in_days_start_hour;
                        if (strlen($in_days_start_minutes) == 1) $in_days_start_minutes = '0' . $in_days_start_minutes;

                        if (strlen($in_days_end_hour) == 1) $in_days_end_hour = '0' . $in_days_end_hour;
                        if (strlen($in_days_end_minutes) == 1) $in_days_end_minutes = '0' . $in_days_end_minutes;

                        $in_days_start_time = $in_days_start_hour . '-' . $in_days_start_minutes . '-' . $in_days_start_ampm;
                        $in_days_end_time = $in_days_end_hour . '-' . $in_days_end_minutes . '-' . $in_days_end_ampm;
                    }

                    $in_days_times = ':' . $in_days_start_time . ':' . $in_days_end_time;
                }

                return $this->main->standardize_format($ex[0]) . ':' . $this->main->standardize_format($ex[1]) . $in_days_times;
            }, $in_days_arr);

            usort($in_days_arr, function ($a, $b)
            {
                $ex_a = explode(':', $a);
                $ex_b = explode(':', $b);

                $date_a = $ex_a[0];
                $date_b = $ex_b[0];

                $in_day_a_time_label = '';
                if (isset($ex_a[2]))
                {
                    $in_day_a_time = $ex_a[2];
                    $pos = strpos($in_day_a_time, '-');
                    if ($pos !== false) $in_day_a_time_label = substr_replace($in_day_a_time, ':', $pos, 1);

                    $in_day_a_time_label = str_replace('-', ' ', $in_day_a_time_label);
                }

                $in_day_b_time_label = '';
                if (isset($ex_b[2]))
                {
                    $in_day_b_time = $ex_b[2];
                    $pos = strpos($in_day_b_time, '-');
                    if ($pos !== false) $in_day_b_time_label = substr_replace($in_day_b_time, ':', $pos, 1);

                    $in_day_b_time_label = str_replace('-', ' ', $in_day_b_time_label);
                }

                return strtotime(trim($date_a . ' ' . $in_day_a_time_label)) - strtotime(trim($date_b . ' ' . $in_day_b_time_label));
            });

            if (!isset($in_days_arr[':i:'])) $in_days_arr[':i:'] = ':val:';
            foreach ($in_days_arr as $key => $in_day_arr)
            {
                if (is_numeric($key)) $in_days .= $in_day_arr . ',';
            }
        }

        $not_in_days = '';
        if (count($not_in_days_arr))
        {
            foreach ($not_in_days_arr as $key => $not_in_day_arr)
            {
                if (is_numeric($key)) $not_in_days .= $this->main->standardize_format($not_in_day_arr) . ',';
            }
        }

        $in_days = trim($in_days, ', ');
        $not_in_days = trim($not_in_days, ', ');

        update_post_meta($post_id, 'mec_in_days', $in_days);
        update_post_meta($post_id, 'mec_not_in_days', $not_in_days);

        // Repeat End Date
        if ($repeat_end == 'date') $repeat_end_date = $repeat_end_at_date;
        else if ($repeat_end == 'occurrences')
        {
            if ($plus_date) $repeat_end_date = date('Y-m-d', strtotime($plus_date, strtotime($end_date)));
            else $repeat_end_date = '0000-00-00';
        }
        else $repeat_end_date = '0000-00-00';

        // If event is not repeating then set the end date of event correctly
        if (!$repeat_status || $repeat_type == 'custom_days') $repeat_end_date = $end_date;

        // Add parameters to the $event
        $event['end'] = $repeat_end_date;
        $event['year'] = $year;
        $event['month'] = $month;
        $event['day'] = $day;
        $event['week'] = $week;
        $event['weekday'] = $weekday;
        $event['weekdays'] = $weekdays;
        $event['days'] = $in_days;
        $event['not_in_days'] = $not_in_days;

        // Update MEC Events Table
        $mec_event_id = $this->db->select("SELECT `id` FROM `#__mec_events` WHERE `post_id`='$post_id'", 'loadResult');

        if (!$mec_event_id)
        {
            $q1 = '';
            $q2 = '';

            foreach ($event as $key => $value)
            {
                $q1 .= "`$key`,";

                if (is_null($value)) $q2 .= 'NULL,';
                else $q2 .= "'$value',";
            }

            $this->db->q('INSERT INTO `#__mec_events` (' . trim($q1, ', ') . ') VALUES (' . trim($q2, ', ') . ')', 'INSERT');
        }
        else
        {
            $q = '';

            foreach ($event as $key => $value)
            {
                if (is_null($value)) $q .= "`$key`=NULL,";
                else $q .= "`$key`='$value',";
            }

            $this->db->q('UPDATE `#__mec_events` SET ' . trim($q, ', ') . " WHERE `id`='$mec_event_id'");
        }

        // Update Schedule
        $schedule = $this->getSchedule();
        $schedule->reschedule($post_id, $schedule->get_reschedule_maximum($repeat_type));

        // Hourly Schedule Options
        $raw_hourly_schedules = $_mec['hourly_schedules'] ?? [];
        unset($raw_hourly_schedules[':d:']);

        $hourly_schedules = [];
        foreach ($raw_hourly_schedules as $raw_hourly_schedule)
        {
            if (isset($raw_hourly_schedule['schedules'][':i:'])) unset($raw_hourly_schedule['schedules'][':i:']);
            $hourly_schedules[] = $raw_hourly_schedule;
        }

        update_post_meta($post_id, 'mec_hourly_schedules', $hourly_schedules);

        // Booking and Ticket Options
        $booking = $_mec['booking'] ?? [];
        update_post_meta($post_id, 'mec_booking', $booking);

        $tickets = $_mec['tickets'] ?? [];
        if (isset($tickets[':i:'])) unset($tickets[':i:']);

        // Unset Ticket Dats
        if (count($tickets))
        {
            $new_tickets = [];
            foreach ($tickets as $key => $ticket)
            {
                unset($ticket['dates'][':j:']);
                $ticket_start_time_ampm = ((isset($ticket['ticket_start_time_hour']) and (intval($ticket['ticket_start_time_hour']) > 0 and intval($ticket['ticket_start_time_hour']) < 13) and isset($ticket['ticket_start_time_ampm'])) ? $ticket['ticket_start_time_ampm'] : '');
                $ticket_render_start_time = ((isset($ticket['ticket_start_time_hour']) and $ticket['ticket_start_time_hour']) ? date('h:ia', strtotime(sprintf('%02d', $ticket['ticket_start_time_hour']) . ':' . sprintf('%02d', $ticket['ticket_start_time_minute']) . $ticket_start_time_ampm)) : '');
                $ticket_end_time_ampm = ((isset($ticket['ticket_end_time_hour']) and (intval($ticket['ticket_end_time_hour']) > 0 and intval($ticket['ticket_end_time_hour']) < 13) and isset($ticket['ticket_end_time_ampm'])) ? $ticket['ticket_end_time_ampm'] : '');
                $ticket_render_end_time = ((isset($ticket['ticket_end_time_hour']) and $ticket['ticket_end_time_hour']) ? date('h:ia', strtotime(sprintf('%02d', $ticket['ticket_end_time_hour']) . ':' . sprintf('%02d', $ticket['ticket_end_time_minute']) . $ticket_end_time_ampm)) : '');

                $ticket['ticket_start_time_hour'] = substr($ticket_render_start_time, 0, 2);
                $ticket['ticket_start_time_ampm'] = strtoupper(substr($ticket_render_start_time, 5, 6));
                $ticket['ticket_end_time_hour'] = substr($ticket_render_end_time, 0, 2);
                $ticket['ticket_end_time_ampm'] = strtoupper(substr($ticket_render_end_time, 5, 6));
                $ticket['price'] = trim($ticket['price']);
                $ticket['limit'] = trim($ticket['limit']);
                $ticket['minimum_ticket'] = trim($ticket['minimum_ticket']);
                $ticket['stop_selling_value'] = trim($ticket['stop_selling_value']);
                $ticket['category_ids'] = !empty($ticket['category_ids']) ? (array) $ticket['category_ids'] : [];

                // Bellow conditional block code is used to change ticket dates format to compatible ticket past dates structure for store in db.
                foreach ($ticket['dates'] as $dates_ticket_key => $dates_ticket_values)
                {
                    if (isset($dates_ticket_values['start']) and trim($dates_ticket_values['start']))
                    {
                        $ticket['dates'][$dates_ticket_key]['start'] = $this->main->standardize_format($dates_ticket_values['start']);
                    }

                    if (isset($dates_ticket_values['end']) and trim($dates_ticket_values['end']))
                    {
                        $ticket['dates'][$dates_ticket_key]['end'] = $this->main->standardize_format($dates_ticket_values['end']);
                    }
                }

                $ticket['id'] = $key;
                $new_tickets[$key] = $ticket;
            }

            $tickets = $new_tickets;
        }

        update_post_meta($post_id, 'mec_tickets', $tickets);
        update_post_meta($post_id, 'mec_global_tickets_applied', 1);

        // Fee options
        $fees_global_inheritance = isset($_mec['fees_global_inheritance']) ? sanitize_text_field($_mec['fees_global_inheritance']) : 1;
        update_post_meta($post_id, 'mec_fees_global_inheritance', $fees_global_inheritance);

        $fees = $_mec['fees'] ?? [];
        if (isset($fees[':i:'])) unset($fees[':i:']);

        update_post_meta($post_id, 'mec_fees', $fees);

        // Ticket Variations options
        $ticket_variations_global_inheritance = isset($_mec['ticket_variations_global_inheritance']) ? sanitize_text_field($_mec['ticket_variations_global_inheritance']) : 1;
        update_post_meta($post_id, 'mec_ticket_variations_global_inheritance', $ticket_variations_global_inheritance);

        $ticket_variations = $_mec['ticket_variations'] ?? [];
        if (isset($ticket_variations[':i:'])) unset($ticket_variations[':i:']);

        update_post_meta($post_id, 'mec_ticket_variations', $ticket_variations);

        // Registration Fields options
        $reg_fields_global_inheritance = isset($_mec['reg_fields_global_inheritance']) ? sanitize_text_field($_mec['reg_fields_global_inheritance']) : 1;
        update_post_meta($post_id, 'mec_reg_fields_global_inheritance', $reg_fields_global_inheritance);

        $reg_fields = $_mec['reg_fields'] ?? [];
        if ($reg_fields_global_inheritance) $reg_fields = [];

        do_action('mec_save_reg_fields', $post_id, $reg_fields);
        update_post_meta($post_id, 'mec_reg_fields', $reg_fields);

        $bfixed_fields = $_mec['bfixed_fields'] ?? [];
        if ($reg_fields_global_inheritance) $bfixed_fields = [];

        do_action('mec_save_bfixed_fields', $post_id, $bfixed_fields);
        update_post_meta($post_id, 'mec_bfixed_fields', $bfixed_fields);

        // Organizer Payment Options
        $op = $_mec['op'] ?? [];
        update_post_meta($post_id, 'mec_op', $op);
        update_user_meta(get_post_field('post_author', $post_id), 'mec_op', $op);

        // MEC Fields
        $fields = (isset($_mec['fields']) and is_array($_mec['fields'])) ? $_mec['fields'] : [];
        update_post_meta($post_id, 'mec_fields', $fields);

        // Save fields one by one
        foreach ($fields as $field_id => $values)
        {
            if (is_array($values))
            {
                $values = array_unique($values);
                $values = implode(',', $values);
            }

            update_post_meta($post_id, 'mec_fields_' . $field_id, sanitize_text_field($values));
        }

        // Downloadable File
        if (isset($_mec['downloadable_file']))
        {
            $dl_file = sanitize_text_field($_mec['downloadable_file']);
            update_post_meta($post_id, 'mec_dl_file', $dl_file);
        }

        // Public Download Module File
        if (isset($_mec['public_download_module_file']))
        {
            $public_dl_file = sanitize_text_field($_mec['public_download_module_file']);
            update_post_meta($post_id, 'mec_public_dl_file', $public_dl_file);

            $public_dl_title = isset($_mec['public_download_module_title']) ? sanitize_text_field($_mec['public_download_module_title']) : '';
            update_post_meta($post_id, 'mec_public_dl_title', $public_dl_title);

            $public_dl_description = isset($_mec['public_download_module_description']) ? sanitize_text_field($_mec['public_download_module_description']) : '';
            update_post_meta($post_id, 'mec_public_dl_description', $public_dl_description);
        }

        // Event Gallery
        $gallery = (isset($_mec['event_gallery']) and is_array($_mec['event_gallery'])) ? $_mec['event_gallery'] : [];
        update_post_meta($post_id, 'mec_event_gallery', $gallery);

        // Related Events
        $related_events = (isset($_mec['related_events']) and is_array($_mec['related_events'])) ? $_mec['related_events'] : [];
        update_post_meta($post_id, 'mec_related_events', $related_events);

        // Event Banner
        $event_banner = (isset($_mec['banner']) and is_array($_mec['banner'])) ? $_mec['banner'] : [];
        update_post_meta($post_id, 'mec_banner', $event_banner);

        // Notifications
        if (isset($_mec['notifications']))
        {
            $notifications = is_array($_mec['notifications']) ? $_mec['notifications'] : [];
            update_post_meta($post_id, 'mec_notifications', $notifications);
        }

        // Event Dates Changed?
        if ($prev_start_datetime and $prev_end_datetime and !$repeat_status and $prev_start_datetime != $start_datetime and $prev_end_datetime != $end_datetime)
        {
            $this->main->event_date_updated($post_id, $prev_start_datetime, $prev_end_datetime);
        }

        // Appointments
        $this->getAppointments()->save($post_id, $_mec);

        $mec_update = !isset($_REQUEST['original_publish']) || strtolower(trim(sanitize_text_field($_REQUEST['original_publish']))) !== 'publish';
        do_action('mec_after_publish_admin_event', $post_id, $mec_update);

        // Save Event Data
        do_action('mec_save_event_data', $post_id, $_mec);
    }

    public function quick_edit($post_id)
    {
        // Validating And Verifying
        if ((!isset($_POST['screen']) || trim($_POST['screen']) != 'edit-mec-events') and !check_ajax_referer('inlineeditnonce', '_inline_edit', false)) return;

        $mec_locations = (isset($_POST['tax_input']['mec_location']) and trim($_POST['tax_input']['mec_location'])) ? array_filter(explode(',', sanitize_text_field($_POST['tax_input']['mec_location']))) : null;
        $mec_organizers = (isset($_POST['tax_input']['mec_organizer']) and trim($_POST['tax_input']['mec_organizer'])) ? array_filter(explode(',', sanitize_text_field($_POST['tax_input']['mec_organizer']))) : null;

        // MEC Locations Quick Edit
        $this->mec_locations_edit($post_id, $mec_locations, 'quick_edit');

        // MEC Organizers Quick Edit
        $this->mec_organizers_edit($post_id, $mec_organizers, 'quick_edit');
    }

    /**
     * Publish an event
     * @param string $new
     * @param string $old
     * @param WP_Post $post
     * @return void
     * @author Webnus <info@webnus.net>
     */
    public function event_published($new, $old, $post)
    {
        if ($post->post_type !== $this->PT) return;

        // Fires after publish an event to send notifications etc.
        do_action('mec_event_published', $new, $old, $post);

        // Update Status
        $this->db->q("UPDATE `#__mec_dates` SET `status`='" . esc_sql($new) . "' WHERE `post_id`='" . esc_sql($post->ID) . "'");
    }

    /**
     * Remove MEC event data after deleting a post permanently
     *
     * @param int $post_id
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function delete_event($post_id)
    {
        $post = get_post($post_id);
        if ($post->post_type !== $this->PT) return false;

        $this->db->q("DELETE FROM `#__mec_events` WHERE `post_id`='$post_id'");
        $this->db->q("DELETE FROM `#__mec_dates` WHERE `post_id`='$post_id'");
        $this->db->q("DELETE FROM `#__mec_occurrences` WHERE `post_id`='$post_id'");

        return true;
    }

    public function add_buttons($which)
    {
        $screen = get_current_screen();
        if ($which === 'top' and $screen->post_type === $this->PT)
        {
            echo '<a href="' . esc_url(admin_url('edit.php?post_type=' . $this->PT . '&mec-expired=1')) . '" class="button">' . esc_html__('Expired Events', 'modern-events-calendar-lite') . '</a>';
            echo '&nbsp;<a href="' . esc_url(admin_url('edit.php?post_type=' . $this->PT . '&mec-upcoming=1')) . '" class="button">' . esc_html__('Upcoming Events', 'modern-events-calendar-lite') . '</a>';
        }
    }

    /**
     * Add filter options in manage events page
     *
     * @param string $post_type
     * @return void
     * @author Webnus <info@webnus.net>
     */
    public function add_filters($post_type)
    {
        if ($post_type != $this->PT) return;

        $datepicker_format = 'Y-m-d';
        $start_date = isset($_GET['mec_start_date']) ? $_GET['mec_start_date'] : '';
        echo '<input type="text" name="mec_start_date" id="mec_start_date" value="' . esc_attr($this->main->standardize_format($start_date, $datepicker_format)) . '" placeholder="' . esc_attr__('Start Date', 'modern-events-calendar-lite') . '" autocomplete="off"/>';

        $taxonomy = 'mec_label';
        if (wp_count_terms($taxonomy))
        {
            wp_dropdown_categories(
                [
                    'show_option_all' => sprintf(esc_html__('Show all %s', 'modern-events-calendar-lite'), $this->main->m('taxonomy_labels', esc_html__('labels', 'modern-events-calendar-lite'))),
                    'taxonomy' => $taxonomy,
                    'name' => $taxonomy,
                    'value_field' => 'slug',
                    'orderby' => 'name',
                    'order' => 'ASC',
                    'selected' => (isset($_GET[$taxonomy]) ? sanitize_text_field($_GET[$taxonomy]) : ''),
                    'show_count' => false,
                    'hide_empty' => false,
                ]
            );
        }

        $taxonomy = 'mec_location';
        if (wp_count_terms($taxonomy))
        {
            wp_dropdown_categories(
                [
                    'show_option_all' => sprintf(esc_html__('Show all %s', 'modern-events-calendar-lite'), $this->main->m('taxonomy_locations', esc_html__('locations', 'modern-events-calendar-lite'))),
                    'taxonomy' => $taxonomy,
                    'name' => $taxonomy,
                    'value_field' => 'slug',
                    'orderby' => 'name',
                    'order' => 'ASC',
                    'selected' => (isset($_GET[$taxonomy]) ? sanitize_text_field($_GET[$taxonomy]) : ''),
                    'show_count' => false,
                    'hide_empty' => false,
                ]
            );
        }

        $taxonomy = 'mec_organizer';
        if (wp_count_terms($taxonomy))
        {
            wp_dropdown_categories(
                [
                    'show_option_all' => sprintf(esc_html__('Show all %s', 'modern-events-calendar-lite'), $this->main->m('taxonomy_organizers', esc_html__('organizers', 'modern-events-calendar-lite'))),
                    'taxonomy' => $taxonomy,
                    'name' => $taxonomy,
                    'value_field' => 'slug',
                    'orderby' => 'name',
                    'order' => 'ASC',
                    'selected' => (isset($_GET[$taxonomy]) ? sanitize_text_field($_GET[$taxonomy]) : ''),
                    'show_count' => false,
                    'hide_empty' => false,
                ]
            );
        }

        $taxonomy = 'mec_category';
        if (wp_count_terms($taxonomy))
        {
            wp_dropdown_categories(
                [
                    'show_option_all' => sprintf(esc_html__('Show all %s', 'modern-events-calendar-lite'), $this->main->m('taxonomy_categorys', esc_html__('Categories', 'modern-events-calendar-lite'))),
                    'taxonomy' => $taxonomy,
                    'name' => $taxonomy,
                    'value_field' => 'slug',
                    'orderby' => 'name',
                    'order' => 'ASC',
                    'selected' => (isset($_GET[$taxonomy]) ? sanitize_text_field($_GET[$taxonomy]) : ''),
                    'show_count' => false,
                    'hide_empty' => false,
                ]
            );
        }

        // Lightbox
        echo '
            <div id="mec_manage_events_lightbox" class="lity-hide">
                <div class="mec-attendees-list-head">' . esc_html__('Attendees List', 'modern-events-calendar-lite') . '</div>
                <div class="mec-attendees-list-wrap">
                    <div class="mec-attendees-list-left">
                        <div class="mec-attendees-list-left-menu mec-owl-carousel mec-owl-theme">

                        </div>
                    </div>
                    <div class="mec-attendees-list-right">

                    </div>
                </div>
            </div>';
    }

    /**
     * Filters columns of events feature
     *
     * @param array $columns
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function filter_columns($columns)
    {
        unset($columns['comments']);
        unset($columns['date']);
        unset($columns['tags']);

        $columns['title'] = esc_html__('Title', 'modern-events-calendar-lite');
        $columns['category'] = esc_html__('Category', 'modern-events-calendar-lite');
        $columns['location'] = $this->main->m('taxonomy_location', esc_html__('Location', 'modern-events-calendar-lite'));
        $columns['organizer'] = $this->main->m('taxonomy_organizer', esc_html__('Organizer', 'modern-events-calendar-lite'));
        $columns['start_date'] = esc_html__('Start Date', 'modern-events-calendar-lite');
        $columns['end_date'] = esc_html__('End Date', 'modern-events-calendar-lite');

        // Sold Tickets
        if ($this->getPRO() && isset($this->settings['booking_status']) && $this->settings['booking_status']) $columns['tickets'] = esc_html__('Tickets', 'modern-events-calendar-lite');

        $columns['repeat'] = esc_html__('Repeat', 'modern-events-calendar-lite');
        return $columns;
    }

    /**
     * Filters sortable columns of events feature
     *
     * @param array $columns
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function filter_sortable_columns($columns)
    {
        $columns['start_date'] = 'start_date';
        $columns['end_date'] = 'end_date';

        return $columns;
    }

    /**
     * Filters columns content of events feature
     *
     * @param string $column_name
     * @param int $post_id
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function filter_columns_content($column_name, $post_id)
    {
        if ($column_name == 'location')
        {
            $location = get_term(get_post_meta($post_id, 'mec_location_id', true));
            echo(isset($location->name) && 'uncategorized' !== $location->slug ? esc_html($location->name) : '----');
        }
        else if ($column_name == 'organizer')
        {
            $organizer = get_term(get_post_meta($post_id, 'mec_organizer_id', true));
            echo(isset($organizer->name) && 'uncategorized' !== $organizer->slug ? esc_html($organizer->name) : '----');
        }
        else if ($column_name == 'start_date')
        {
            $datetime_format = get_option('date_format', 'Y-n-d') . ' ' . get_option('time_format', 'H:i');
            $date = get_post_meta($post_id, 'mec_start_date', true);

            echo esc_html($this->main->date_i18n($datetime_format, (strtotime($date) + ((int) get_post_meta($post_id, 'mec_start_day_seconds', true))), $post_id));
        }
        else if ($column_name == 'end_date')
        {
            $datetime_format = get_option('date_format', 'Y-n-d') . ' ' . get_option('time_format', 'H:i');
            $date = get_post_meta($post_id, 'mec_end_date', true);

            echo esc_html($this->main->date_i18n($datetime_format, (strtotime($date) + ((int) get_post_meta($post_id, 'mec_end_day_seconds', true))), $post_id));
        }
        else if ($column_name == 'tickets')
        {
            $book = $this->getBook();

            echo esc_html($book->get_all_sold_tickets($this->main->get_original_event($post_id))) . ' / ' . $book->get_total_tickets($post_id);
        }
        else if ($column_name == 'repeat')
        {
            $repeat_type = get_post_meta($post_id, 'mec_repeat_type', true);
            echo esc_html(ucwords(str_replace('_', ' ', $repeat_type)));
        }
        else if ($column_name == 'category')
        {
            $post_categories = get_the_terms($post_id, 'mec_category');
            if ($post_categories) foreach ($post_categories as $post_category) $categories[] = $post_category->name;
            if (!empty($categories))
            {
                $category_name = implode(",", $categories);
                echo esc_html($category_name);
            }
        }
    }

    /**
     * Sort events if sorted by custom columns
     *
     * @param object $query
     * @return void
     * @author Webnus <info@webnus.net>
     */
    public function filter($query)
    {
        if (!is_admin() or $query->get('post_type') != $this->PT) return;

        $meta_query = [];
        $order_query = [];

        $orderby = $query->get('orderby');
        $order = $query->get('order');

        $expired = (isset($_REQUEST['mec-expired']) ? sanitize_text_field($_REQUEST['mec-expired']) : 0);
        if ($expired)
        {
            $today = current_time('Y-m-d');
            $today_seconds = $this->main->time_to_seconds(current_time('H'), current_time('i'), current_time('s'));

            $expired_ids = $this->db->select("SELECT post_id FROM `#__mec_events` WHERE `end` != '0000-00-00' AND `end` < '" . $today . "' OR (`end` = '" . $today . "' AND `time_end` <= '" . $today_seconds . "')", 'loadColumn');

            $filtered_ids = [];
            foreach ($expired_ids as $expired_id)
            {
                $custom_days = $this->db->select("SELECT days FROM `#__mec_events` WHERE `post_id` = '" . esc_sql($expired_id) . "'", 'loadResult');
                if (!$custom_days) $filtered_ids[] = $expired_id;
                else
                {
                    $ex = explode(',', $custom_days);
                    $last = end($ex);

                    $parts = explode(':', $last);

                    $last_date = $parts[1] ?? '';
                    $last_time = $parts[3] ?? '';
                    $last_time = str_replace('-AM', ' AM', $last_time);
                    $last_time = str_replace('-PM', ' PM', $last_time);
                    $last_time = str_replace('-', ':', $last_time);

                    $last_datetime = $last_date . ' ' . $last_time;
                    if (trim($last_datetime) === '' || strtotime($last_datetime) < current_time('timestamp')) $filtered_ids[] = $expired_id;
                }
            }

            if (!count($filtered_ids)) $filtered_ids = [0];

            $query->set('post__in', $filtered_ids);

            if (!trim($orderby)) $orderby = 'end_date';
            if (!trim($order)) $order = 'asc';
        }

        $upcoming = (isset($_REQUEST['mec-upcoming']) ? sanitize_text_field($_REQUEST['mec-upcoming']) : 0);
        if ($upcoming)
        {
            $now = current_time('Y-m-d H:i:s');

            $post_id_rows = $this->db->select("SELECT `post_id` FROM `#__mec_dates` WHERE `tstart` >= '" . strtotime($now) . "' GROUP BY `post_id`", 'loadObjectList');

            $post_ids = [];
            foreach ($post_id_rows as $post_id_row) $post_ids[] = $post_id_row->post_id;

            $post_ids = array_unique($post_ids);
            $query->set('post__in', $post_ids);

            if (!trim($orderby)) $orderby = 'start_date';
        }

        if ($orderby == 'start_date')
        {
            $meta_query['mec_start_date'] = [
                'key' => 'mec_start_date',
            ];

            $meta_query['mec_start_day_seconds'] = [
                'key' => 'mec_start_day_seconds',
            ];

            $order_query = [
                'mec_start_date' => $query->get('order'),
                'mec_start_day_seconds' => $query->get('order'),
            ];
        }
        else if ($orderby == 'end_date')
        {
            $meta_query['mec_end_date'] = [
                'key' => 'mec_end_date',
            ];

            $meta_query['mec_end_day_seconds'] = [
                'key' => 'mec_end_day_seconds',
            ];

            $order_query = [
                'mec_end_date' => $order,
                'mec_end_day_seconds' => $order,
            ];
        }

        $start_date = isset($_GET['mec_start_date']) ? $_GET['mec_start_date'] : '';
        if (!empty($start_date))
        {

            $meta_query['mec_start_date'] = [
                'key' => 'mec_start_date',
                'value' => date('Y-m-d', strtotime($start_date)),
                'compare' => '=',
                'type' => 'DATE',
            ];
        }

        if (count($meta_query)) $query->set('meta_query', $meta_query);
        if (count($order_query)) $query->set('orderby', $order_query);
    }

    public function add_bulk_actions()
    {
        global $post_type;

        if ($post_type == $this->PT)
        {
            ?>
            <script>
                jQuery(document).ready(function () {
                    jQuery('<option>').val('ical-export').text('<?php echo esc_html__('iCal / Outlook Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action']");
                    jQuery('<option>').val('ical-export').text('<?php echo esc_html__('iCal / Outlook Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action2']");

                    jQuery('<option>').val('csv-export').text('<?php echo esc_html__('CSV Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action']");
                    jQuery('<option>').val('csv-export').text('<?php echo esc_html__('CSV Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action2']");

                    jQuery('<option>').val('g-cal-csv-export').text('<?php echo esc_html__('Google Cal. CSV Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action']");
                    jQuery('<option>').val('g-cal-csv-export').text('<?php echo esc_html__('Google Cal. CSV Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action2']");

                    jQuery('<option>').val('ms-excel-export').text('<?php echo esc_html__('MS Excel Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action']");
                    jQuery('<option>').val('ms-excel-export').text('<?php echo esc_html__('MS Excel Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action2']");

                    jQuery('<option>').val('xml-export').text('<?php echo esc_html__('XML Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action']");
                    jQuery('<option>').val('xml-export').text('<?php echo esc_html__('XML Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action2']");

                    jQuery('<option>').val('json-export').text('<?php echo esc_html__('JSON Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action']");
                    jQuery('<option>').val('json-export').text('<?php echo esc_html__('JSON Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action2']");

                    jQuery('<option>').val('duplicate').text('<?php echo esc_html__('Duplicate', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action']");
                    jQuery('<option>').val('duplicate').text('<?php echo esc_html__('Duplicate', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action2']");
                });
            </script>
            <?php
        }
    }

    public function do_bulk_actions()
    {
        $wp_list_table = _get_list_table('WP_Posts_List_Table');

        $action = $wp_list_table->current_action();
        if (!$action) return false;

        $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : 'post';
        if ($post_type != $this->PT) return false;

        check_admin_referer('bulk-posts');

        switch ($action)
        {
            case 'ical-export':

                $post_ids = (isset($_GET['post']) and is_array($_GET['post']) and count($_GET['post'])) ? array_map('sanitize_text_field', wp_unslash($_GET['post'])) : [];
                $events = '';

                foreach ($post_ids as $post_id) $events .= $this->main->ical_single((int) $post_id);
                $ical_calendar = $this->main->ical_calendar($events);

                header('Content-type: application/force-download; charset=utf-8');
                header('Content-Disposition: attachment; filename="mec-events-' . date('YmdTHi') . '.ics"');

                echo MEC_kses::full($ical_calendar);

                exit;

            case 'ms-excel-export':

                $filename = 'mec-events-' . md5(time() . mt_rand(100, 999)) . '.xlsx';

                $rows = $this->csvexcel();
                $this->main->generate_download_excel($rows, $filename);

                exit;

            case 'csv-export':

                $filename = 'mec-events-' . md5(time() . mt_rand(100, 999)) . '.csv';

                $rows = $this->csvexcel();
                $this->main->generate_download_csv($rows, $filename);

                exit;

            case 'g-cal-csv-export':

                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=mec-events-' . md5(time() . mt_rand(100, 999)) . '.csv');

                $this->gcalcsv();

                exit;

            case 'xml-export':

                $post_ids = (isset($_GET['post']) and is_array($_GET['post']) and count($_GET['post'])) ? array_map('sanitize_text_field', wp_unslash($_GET['post'])) : [];

                $events = [];
                foreach ($post_ids as $post_id) $events[] = $this->main->export_single((int) $post_id);

                $xml_feed = $this->main->xml_convert(['events' => $events]);

                header('Content-type: application/force-download; charset=utf-8');
                header('Content-Disposition: attachment; filename="mec-events-' . date('YmdTHi') . '.xml"');

                echo $xml_feed;

                exit;

            case 'json-export':

                $post_ids = (isset($_GET['post']) and is_array($_GET['post']) and count($_GET['post'])) ? array_map('sanitize_text_field', wp_unslash($_GET['post'])) : [];

                $events = [];
                foreach ($post_ids as $post_id) $events[] = $this->main->export_single((int) $post_id);

                header('Content-type: application/force-download; charset=utf-8');
                header('Content-Disposition: attachment; filename="mec-events-' . date('YmdTHi') . '.json"');

                echo json_encode($events);

                exit;

            case 'duplicate':

                $post_ids = (isset($_GET['post']) and is_array($_GET['post']) and count($_GET['post'])) ? array_map('sanitize_text_field', wp_unslash($_GET['post'])) : [];
                foreach ($post_ids as $post_id) $this->main->duplicate((int) $post_id);

                break;

            default:
                return false;
        }

        wp_redirect('edit.php?post_type=' . $this->main->get_main_post_type());
        exit;
    }

    public function csvexcel($export_all = false)
    {
        // MEC Render Library
        $render = $this->getRender();

        if ($export_all) $post_ids = get_posts('post_type=mec-events&fields=ids&posts_per_page=-1');
        else $post_ids = (isset($_GET['post']) and is_array($_GET['post']) and count($_GET['post'])) ? array_map('sanitize_text_field', wp_unslash($_GET['post'])) : [];

        $columns = [
            esc_html__('ID', 'modern-events-calendar-lite'),
            esc_html__('Title', 'modern-events-calendar-lite'),
            esc_html__('Description', 'modern-events-calendar-lite'),
            esc_html__('Start Date', 'modern-events-calendar-lite'),
            esc_html__('Start Time', 'modern-events-calendar-lite'),
            esc_html__('End Date', 'modern-events-calendar-lite'),
            esc_html__('End Time', 'modern-events-calendar-lite'),
            esc_html__('Link', 'modern-events-calendar-lite'),
            $this->main->m('taxonomy_location', esc_html__('Location', 'modern-events-calendar-lite')),
            esc_html__('Address', 'modern-events-calendar-lite'),
            $this->main->m('taxonomy_organizer', esc_html__('Organizer', 'modern-events-calendar-lite')),
            sprintf(esc_html__('%s Tel', 'modern-events-calendar-lite'), $this->main->m('taxonomy_organizer', esc_html__('Organizer', 'modern-events-calendar-lite'))),
            sprintf(esc_html__('%s Email', 'modern-events-calendar-lite'), $this->main->m('taxonomy_organizer', esc_html__('Organizer', 'modern-events-calendar-lite'))),
            $this->main->m('event_cost', esc_html__('Event Cost', 'modern-events-calendar-lite')),
            esc_html__('Featured Image', 'modern-events-calendar-lite'),
            esc_html__('Labels', 'modern-events-calendar-lite'),
            esc_html__('Categories', 'modern-events-calendar-lite'),
            esc_html__('Tags', 'modern-events-calendar-lite'),
        ];

        // Speakers
        if (isset($this->settings['speakers_status']) and $this->settings['speakers_status']) $columns[] = esc_html__('Speakers', 'modern-events-calendar-lite');

        // Event Fields
        $fields = $this->main->get_event_fields();
        if (!is_array($fields)) $fields = [];

        foreach ($fields as $f => $field)
        {
            if (!is_numeric($f)) continue;
            if (!isset($field['label']) or trim($field['label']) == '') continue;

            $columns[] = stripslashes($field['label']);
        }

        $rows = [];
        $rows[] = $columns;

        foreach ($post_ids as $post_id)
        {
            $post_id = (int) $post_id;

            $data = $render->data($post_id);
            $dates = $render->dates($post_id, $data);
            $date = $dates[0] ?? [];

            // No Date
            if (!count($date)) continue;

            $location = $data->locations[$data->meta['mec_location_id']] ?? [];
            $organizer = $data->organizers[$data->meta['mec_organizer_id']] ?? [];
            $cost = $data->meta['mec_cost'] ?? null;

            $taxonomies = ['mec_label', 'mec_category', apply_filters('mec_taxonomy_tag', '')];
            if (isset($this->settings['speakers_status']) and $this->settings['speakers_status']) $taxonomies[] = 'mec_speaker';

            $labels = [];
            $categories = [];
            $tags = [];
            $speakers = [];

            $terms = wp_get_post_terms($post_id, $taxonomies, ['fields' => 'all']);
            foreach ($terms as $term)
            {
                // First Validation
                if (!isset($term->taxonomy)) continue;

                if ($term->taxonomy == 'mec_label') $labels[] = $term->name;
                else if ($term->taxonomy == 'mec_category') $categories[] = $term->name;
                else if ($term->taxonomy == apply_filters('mec_taxonomy_tag', '')) $tags[] = $term->name;
                else if ($term->taxonomy == 'mec_speaker') $speakers[] = $term->name;
            }

            $event = [
                $post_id,
                html_entity_decode($data->title, ENT_QUOTES | ENT_HTML5),
                html_entity_decode(strip_tags($data->content), ENT_QUOTES | ENT_HTML5),
                $date['start']['date'],
                $data->time['start'],
                $date['end']['date'],
                $data->time['end'],
                $data->permalink,
                ($location['name'] ?? ''),
                ($location['address'] ?? ''),
                ($organizer['name'] ?? ''),
                ($organizer['tel'] ?? ''),
                ($organizer['email'] ?? ''),
                (is_numeric($cost) ? $this->main->render_price($cost, $post_id) : $cost),
                $this->main->get_post_thumbnail_url($post_id),
                implode(', ', $labels),
                implode(', ', $categories),
                implode(', ', $tags),
            ];

            // Speakers
            if (isset($this->settings['speakers_status']) and $this->settings['speakers_status']) $event[] = implode(', ', $speakers);

            // Event Fields
            if (isset($data->fields) and is_array($data->fields) and count($data->fields))
            {
                foreach ($data->fields as $field) $event[] = $field['value'];
            }

            $rows[] = $event;
        }

        return $rows;
    }

    public function gcalcsv($export_all = false)
    {
        // MEC Render Library
        $render = $this->getRender();

        if ($export_all) $post_ids = get_posts('post_type=mec-events&fields=ids&posts_per_page=-1');
        else $post_ids = (isset($_GET['post']) and is_array($_GET['post']) and count($_GET['post'])) ? array_map('sanitize_text_field', wp_unslash($_GET['post'])) : [];

        // Do not translate these column names
        $columns = [
            'Subject',
            'Start Date',
            'Start Time',
            'End Date',
            'End Time',
            'All Day Event',
            'Description',
            'Location',
            'Private',
        ];

        $delimiter = ',';

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, $columns, $delimiter);

        foreach ($post_ids as $post_id)
        {
            $post_id = (int) $post_id;

            $data = $render->data($post_id);
            $dates = $render->dates($post_id, $data);
            $date = $dates[0];

            $start_date = $date['start']['date'];
            $start_time = $data->time['start'];
            $end_date = $date['end']['date'];
            $end_time = $data->time['end'];

            $start_timestamp = isset($date['start'], $date['start']['timestamp']) ? $date['start']['timestamp'] : null;
            $end_timestamp = isset($date['end'], $date['end']['timestamp']) ? $date['end']['timestamp'] : null;

            if ($start_timestamp)
            {
                $start_date = date('m/d/Y', $start_timestamp);
                $start_time = date('h:i A', $start_timestamp);
            }

            if ($end_timestamp)
            {
                $end_date = date('m/d/Y', $end_timestamp);
                $end_time = date('h:i A', $end_timestamp);
            }

            $location = $data->locations[$data->meta['mec_location_id']] ?? [];
            $allday = (boolean) get_post_meta($post_id, 'mec_allday', true);

            $public = get_post_meta($post_id, 'mec_public', true);
            if (trim($public) === '') $public = 1;

            $event = [
                html_entity_decode($data->title, ENT_QUOTES | ENT_HTML5),
                $start_date,
                $start_time,
                $end_date,
                $end_time,
                ($allday ? 'True' : 'False'),
                html_entity_decode(strip_tags($data->content), ENT_QUOTES | ENT_HTML5),
                $location['address'] ?? '',
                ($public ? 'True' : 'False'),
            ];

            fputcsv($output, $event, $delimiter);
        }
    }

    public function action_links($actions, $post)
    {
        if ($post->post_type != $this->PT) return $actions;

        // Editor and Higher
        if (current_user_can('edit_post', $post->ID))
        {
            // Duplicate Button
            $actions['mec-duplicate'] = '<a href="' . esc_url($this->main->add_qs_vars(['mec-action' => 'duplicate-event', 'id' => $post->ID])) . '">' . esc_html__('Duplicate', 'modern-events-calendar-lite') . '</a>';
        }

        // Booking Button
        if ($this->getPRO() && current_user_can('edit_others_posts') && isset($this->settings['booking_status']) && $this->settings['booking_status']) $actions['mec-bookings'] = '<a href="' . esc_url($this->main->add_qs_vars(['post_type' => $this->main->get_book_post_type(), 'mec_event_id' => $post->ID], trim($this->main->URL('admin'), '/ ') . '/edit.php')) . '">' . esc_html__('Bookings', 'modern-events-calendar-lite') . '</a>';

        // Certificate Button
        if ($this->getPRO() && isset($this->settings['certificate_status']) && $this->settings['certificate_status']) $actions['mec-send-certificate'] = '<a href="' . esc_url($this->main->add_qs_vars(['page' => 'MEC-report', 'event_id' => $post->ID], trim($this->main->URL('admin'), '/ ') . '/admin.php')) . '">' . esc_html__('Send Certificates', 'modern-events-calendar-lite') . '</a>';

        return $actions;
    }

    public function duplicate_event()
    {
        // It's not a duplicate request
        if (!isset($_GET['mec-action']) or sanitize_text_field($_GET['mec-action']) != 'duplicate-event') return false;

        // Event ID to duplicate
        $id = isset($_GET['id']) ? (int) sanitize_text_field($_GET['id']) : 0;
        if (!$id) return false;

        // Only editor and higher
        if (!current_user_can('edit_post', $id)) return false;

        // Duplicate
        $new_post_id = $this->main->duplicate($id);

        wp_redirect('post.php?post=' . $new_post_id . '&action=edit');
        exit;
    }

    /**
     * Do bulk edit Action
     *
     * @return void
     * @author Webnus <info@webnus.net>
     */
    public function bulk_edit()
    {
        $post_ids = (isset($_GET['post']) and is_array($_GET['post']) and count($_GET['post'])) ? array_map('sanitize_text_field', wp_unslash($_GET['post'])) : [];
        if (!count($post_ids)) return;

        $mec_locations = (isset($_GET['tax_input']['mec_location']) and trim($_GET['tax_input']['mec_location'])) ? array_filter(explode(',', sanitize_text_field($_GET['tax_input']['mec_location']))) : null;
        $mec_organizers = (isset($_GET['tax_input']['mec_organizer']) and trim($_GET['tax_input']['mec_organizer'])) ? array_filter(explode(',', sanitize_text_field($_GET['tax_input']['mec_organizer']))) : null;

        if (!$mec_locations and !$mec_organizers) return;

        $taxonomies = [];
        if (is_array($mec_locations)) $taxonomies[] = 'mec_location';
        if (is_array($mec_organizers)) $taxonomies[] = 'mec_organizer';

        $terms = get_terms([
            'taxonomy' => $taxonomies,
        ]);

        foreach ($post_ids as $post_id)
        {
            foreach ($terms as $term)
            {
                $term_objects = get_objects_in_term($term->term_id, $term->taxonomy);
                if (in_array($post_id, $term_objects)) wp_remove_object_terms($post_id, $term->term_id, $term->taxonomy);
            }

            // MEC Locations Bulk Edit
            $this->mec_locations_edit($post_id, $mec_locations);

            // MEC Organizers Bulk Edit
            $this->mec_organizers_edit($post_id, $mec_organizers);
        }
    }

    // MEC Locations Edit.
    public function mec_locations_edit($post_id, $mec_locations, $action = 'bulk_edit')
    {
        if (!is_null($mec_locations))
        {
            $term_location = current($mec_locations);
            if (!term_exists($term_location, 'mec_location')) wp_insert_term($term_location, 'mec_location', []);

            $location_id = get_term_by('name', $term_location, 'mec_location')->term_id;
            wp_set_object_terms($post_id, (int) $location_id, 'mec_location');
            update_post_meta($post_id, 'mec_location_id', $location_id);

            if (count($mec_locations) > 1)
            {
                // Additional locations
                $additional_location_ids = [];

                for ($i = 1; $i < count($mec_locations); $i++)
                {
                    if (!term_exists($mec_locations[$i], 'mec_location')) wp_insert_term($mec_locations[$i], 'mec_location', []);

                    $additional_location_id = get_term_by('name', $mec_locations[$i], 'mec_location')->term_id;
                    wp_set_object_terms($post_id, (int) $additional_location_id, 'mec_location', true);
                    $additional_location_ids[] = (int) $additional_location_id;
                }

                update_post_meta($post_id, 'mec_additional_location_ids', $additional_location_ids);
            }
        }
        else if ($action == 'quick_edit')
        {
            update_post_meta($post_id, 'mec_location_id', 0);
            update_post_meta($post_id, 'mec_additional_location_ids', []);
        }
    }

    // MEC Organizers Edit.
    public function mec_organizers_edit($post_id, $mec_organizers, $action = 'bulk_edit')
    {
        if (!is_null($mec_organizers))
        {
            $term_organizer = current($mec_organizers);
            if (!term_exists($term_organizer, 'mec_organizer')) wp_insert_term($term_organizer, 'mec_organizer', []);

            $organizer_id = get_term_by('name', current($mec_organizers), 'mec_organizer')->term_id;
            wp_set_object_terms($post_id, (int) $organizer_id, 'mec_organizer');
            update_post_meta($post_id, 'mec_organizer_id', $organizer_id);

            if (count($mec_organizers) > 1)
            {
                // Additional organizers
                $additional_organizer_ids = [];

                for ($i = 1; $i < count($mec_organizers); $i++)
                {
                    if (!term_exists($mec_organizers[$i], 'mec_organizer')) wp_insert_term($mec_organizers[$i], 'mec_organizer', []);

                    $additional_organizer_id = get_term_by('name', $mec_organizers[$i], 'mec_organizer')->term_id;
                    wp_set_object_terms($post_id, (int) $additional_organizer_id, 'mec_organizer', true);
                    $additional_organizer_ids[] = (int) $additional_organizer_id;
                }

                update_post_meta($post_id, 'mec_additional_organizer_ids', $additional_organizer_ids);
            }
        }
        else if ($action == 'quick_edit')
        {
            update_post_meta($post_id, 'mec_organizer_id', 0);
            update_post_meta($post_id, 'mec_additional_organizer_ids', []);
        }
    }

    public function icl_duplicate($master_post_id, $lang, $post, $id)
    {
        $master = get_post($master_post_id);
        $target = get_post($id);

        if ($master->post_type != $this->PT) return;
        if ($target->post_type != $this->PT) return;

        $already_duplicated = get_post_meta($id, 'mec_icl_duplicated', true);
        if ($already_duplicated) return;

        $master_location_id = get_post_meta($master_post_id, 'mec_location_id', true);
        $target_location_id = apply_filters('wpml_object_id', $master_location_id, 'mec_location', true, $lang);

        update_post_meta($id, 'mec_location_id', $target_location_id);

        $master_additional_location_ids = get_post_meta($master_post_id, 'mec_additional_location_ids', true);
        if (!is_array($master_additional_location_ids)) $master_additional_location_ids = [];

        $target_additional_location_ids = [];
        foreach ($master_additional_location_ids as $master_additional_location_id)
        {
            $target_additional_location_ids[] = apply_filters('wpml_object_id', $master_additional_location_id, 'mec_location', true, $lang);
        }

        update_post_meta($id, 'mec_additional_location_ids', $target_additional_location_ids);

        $master_organizer_id = get_post_meta($master_post_id, 'mec_organizer_id', true);
        $target_organizer_id = apply_filters('wpml_object_id', $master_organizer_id, 'mec_organizer', true, $lang);

        update_post_meta($id, 'mec_organizer_id', $target_organizer_id);

        $master_additional_organizer_ids = get_post_meta($master_post_id, 'mec_additional_organizer_ids', true);
        if (!is_array($master_additional_organizer_ids)) $master_additional_organizer_ids = [];

        $target_additional_organizer_ids = [];
        foreach ($master_additional_organizer_ids as $master_additional_organizer_id)
        {
            $target_additional_organizer_ids[] = apply_filters('wpml_object_id', $master_additional_organizer_id, 'mec_location', true, $lang);
        }

        update_post_meta($id, 'mec_additional_organizer_ids', $target_additional_organizer_ids);

        // MEC Tables
        $this->db->q("INSERT INTO `#__mec_events` (`post_id`, `start`, `end`, `repeat`, `rinterval`, `year`, `month`, `day`, `week`, `weekday`, `weekdays`, `days`, `not_in_days`, `time_start`, `time_end`) SELECT '" . $id . "', `start`, `end`, `repeat`, `rinterval`, `year`, `month`, `day`, `week`, `weekday`, `weekdays`, `days`, `not_in_days`, `time_start`, `time_end` FROM `#__mec_events` WHERE `post_id`='" . $master_post_id . "'");

        update_post_meta($id, 'mec_icl_duplicated', 1);

        // Update Schedule
        $schedule = $this->getSchedule();
        $schedule->reschedule($id);

        // Occurrences
        MEC_feature_occurrences::copy($master_post_id, $id);
    }

    public function wpml_pro_translation_saved($new_post_id, $fields, $job)
    {
        global $iclTranslationManagement;

        $master_post_id = null;
        if (is_object($job) and $iclTranslationManagement)
        {
            $element_type_prefix = $iclTranslationManagement->get_element_type_prefix_from_job($job);
            $original_post = $iclTranslationManagement->get_post($job->original_doc_id, $element_type_prefix);

            if ($original_post) $master_post_id = $original_post->ID;
        }

        // Target Language
        $lang_options = apply_filters('wpml_post_language_details', null, $new_post_id);
        $lang = (is_array($lang_options) and isset($lang_options['language_code'])) ? $lang_options['language_code'] : '';

        // Duplicate Content
        if ($master_post_id) $this->icl_duplicate($master_post_id, $lang, (new stdClass()), $new_post_id);
    }

    public function set_fallback_image_id($value, $post_id, $meta_key, $single)
    {
        // Only on frontend
        if (is_admin() and (!defined('DOING_AJAX') or (defined('DOING_AJAX') and !DOING_AJAX))) return $value;

        // Only for empty _thumbnail_id keys
        if (!empty($meta_key) && '_thumbnail_id' !== $meta_key) return $value;

        // Only For Events
        if (get_post_type($post_id) != $this->PT) return $value;

        // Get current Cache
        $meta_cache = wp_cache_get($post_id, 'post_meta');
        if (!$meta_cache)
        {
            $meta_cache = update_meta_cache('post', [$post_id]);
            $meta_cache = $meta_cache[$post_id] ?? [];
        }

        // Is the _thumbnail_id present in cache?
        if (!empty($meta_cache['_thumbnail_id'][0])) return $value;

        $fallback_image_id = $this->get_fallback_image_id($post_id);
        if (!$fallback_image_id) return $value;

        // Set the Fallback Image in cache
        $meta_cache['_thumbnail_id'][0] = $fallback_image_id;
        wp_cache_set($post_id, $meta_cache, 'post_meta');

        return $value;
    }

    public function show_fallback_image($html, $post_id, $post_thumbnail_id, $size, $attr)
    {
        // Only on frontend
        if ((is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX))) return $html;

        // Only For Events
        if (get_post_type($post_id) != $this->PT) return $html;

        $fallback_image_id = $this->get_fallback_image_id($post_id);

        // if an image is set return that image.
        if ((int) $fallback_image_id !== (int) $post_thumbnail_id) return $html;

        if (isset($attr['class'])) $attr['class'] .= ' mec-fallback-img';
        else
        {
            $size_class = $size;
            if (is_array($size_class)) $size_class = 'size-' . implode('x', $size_class);

            $attr = ['class' => 'attachment-' . $size_class . ' default-featured-img'];
        }

        return wp_get_attachment_image($fallback_image_id, $size, false, $attr);
    }

    public function get_fallback_image_id($event_id)
    {
        // Categories
        $categories = get_the_terms($event_id, 'mec_category');
        if (!is_array($categories) or !count($categories)) return null;

        // Fallback Image ID
        $fallback_image_id = null;
        foreach ($categories as $category)
        {
            $fallback_image = get_term_meta($category->term_id, 'mec_cat_fallback_image', true);
            if (trim($fallback_image))
            {
                $fallback_image_id = attachment_url_to_postid($fallback_image);
                if ($fallback_image_id) break;
            }
        }

        return $fallback_image_id;
    }

    public function mec_event_bookings()
    {
        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : 0;
        $backend = isset($_POST['backend']) ? sanitize_text_field($_POST['backend']) : 0;

        $p_occurrence = isset($_POST['occurrence']) ? sanitize_text_field($_POST['occurrence']) : null;
        $occurrence = explode(':', $p_occurrence)[0];
        if ($occurrence == 'all') $occurrence = strtotime('+100 years');

        $bookings = $this->main->get_bookings($id, $occurrence);
        $book = $this->getBook();

        $html = '';
        $total_attendees = 0;

        if (count($bookings))
        {
            $html .= '<div class="w-clearfix">
                <div class="w-col-xs-4 name">
                    <span>' . esc_html__('Title', 'modern-events-calendar-lite') . '</span>
                </div>
                <div class="w-col-xs-3 email">
                    <span>' . esc_html__('Attendees', 'modern-events-calendar-lite') . '</span>
                </div>
                <div class="w-col-xs-2 ticket">
                    <span>' . esc_html__('Transaction ID', 'modern-events-calendar-lite') . '</span>
                </div>
                <div class="w-col-xs-3">
                    <span>' . esc_html__('Price', 'modern-events-calendar-lite') . '</span>
                </div>
            </div>';

            /** @var WP_Post $booking */
            foreach ($bookings as $booking)
            {
                $attendees = $book->get_attendees($booking->ID);

                $attendees = apply_filters('mec_filter_event_bookings', $attendees, $booking->ID, $p_occurrence);
                $total_attendees += count($attendees);

                $unique_attendees = [];
                foreach ($attendees as $attendee)
                {
                    if (!isset($unique_attendees[$attendee['email']])) $unique_attendees[$attendee['email']] = $attendee;
                    else $unique_attendees[$attendee['email']]['count'] += 1;
                }

                $attendees_html = '<strong>' . count($attendees) . '</strong>';
                $attendees_html .= '<div class="mec-booking-attendees-tooltip">';
                $attendees_html .= '<ul>';

                foreach ($unique_attendees as $unique_attendee)
                {
                    $attendees_html .= '<li>';
                    $attendees_html .= '<div class="mec-booking-attendees-tooltip-name">' . esc_html($unique_attendee['name']) . ((isset($unique_attendee['count']) and $unique_attendee['count'] > 1) ? ' (' . esc_html($unique_attendee['count']) . ')' : '') . '</div>';
                    $attendees_html .= '<div class="mec-booking-attendees-tooltip-email"><a href="mailto:' . esc_attr($unique_attendee['email']) . '">' . esc_html($unique_attendee['email']) . '</a></div>';
                    $attendees_html .= '</li>';
                }

                $attendees_html .= '</ul>';
                $attendees_html .= '</div>';

                $transaction_id = get_post_meta($booking->ID, 'mec_transaction_id', true);

                $price = get_post_meta($booking->ID, 'mec_price', true);
                $event_id = get_post_meta($booking->ID, 'mec_event_id', true);

                $price_html = $this->main->render_price(($price ?: 0), $event_id);
                $price_html .= ' (' . get_post_meta($booking->ID, 'mec_gateway_label', true) . ')';

                $all_dates = get_post_meta($booking->ID, 'mec_all_dates', true);
                if (is_array($all_dates) and count($all_dates) > 1) $price_html .= ' ' . sprintf(esc_html__('for %s dates', 'modern-events-calendar-lite'), count($all_dates));

                $html .= '<div class="w-clearfix">';
                $html .= '<div class="w-col-xs-4">' . ($backend ? '<a href="' . get_edit_post_link($booking->ID) . '" target="_blank">' . esc_html($booking->post_title) . '</a>' : esc_html($booking->post_title)) . '</div>';
                $html .= '<div class="w-col-xs-3">' . MEC_kses::form($attendees_html) . '</div>';
                $html .= '<div class="w-col-xs-2">' . esc_html($transaction_id) . '</div>';
                $html .= '<div class="w-col-xs-3">' . MEC_kses::element($price_html) . '</div>';
                $html .= '</div>';
            }
        }
        else
        {
            $html .= '<p class="mec-not-found">' . esc_html__("No Bookings Found!", 'modern-events-calendar-lite') . '</p>';
        }

        $html = apply_filters('mec_event_bookings_report', $html, $bookings, $id, $backend, $occurrence, $total_attendees);

        echo json_encode(['html' => $html, 'found' => (bool) count($bookings)]);
        exit;
    }

    public function gallery_image_upload()
    {
        // Check if our nonce is set.
        if (!isset($_POST['_wpnonce'])) $this->main->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'mec_event_gallery_image_upload')) $this->main->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        $images = isset($_FILES['images']) && is_array($_FILES['images']) ? $_FILES['images'] : [];

        // No file
        if (!count($images)) $this->main->response(['success' => 0, 'code' => 'NO_FILE', 'message' => esc_html__('Please upload an image.', 'modern-events-calendar-lite')]);

        // Include the functions
        if (!function_exists('wp_handle_upload'))
        {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }

        // Upload Restrictions
        $max_file_size = isset($this->settings['fes_max_file_size']) ? (int) ($this->settings['fes_max_file_size'] * 1000) : (5000 * 1000);
        $allowed = ['jpeg', 'jpg', 'png', 'gif', 'webp'];

        $success = 0;
        $data = [];

        $count = count($images['name']);
        for ($i = 0; $i < $count; $i++)
        {
            $image = [
                'name' => $images['name'][$i],
                'type' => $images['type'][$i],
                'tmp_name' => $images['tmp_name'][$i],
                'error' => $images['error'][$i],
                'size' => $images['size'][$i],
            ];

            $ex = explode('.', $image['name']);
            $extension = end($ex);

            // Invalid Extension
            if (!in_array(strtolower($extension), $allowed)) continue;

            // Invalid Size
            if ($image['size'] > $max_file_size) continue;

            $uploaded = wp_handle_upload($image, ['test_form' => false]);

            if ($uploaded and !isset($uploaded['error']))
            {
                $success = 1;
                $attachment = [
                    'post_mime_type' => $uploaded['type'],
                    'post_title' => '',
                    'post_content' => '',
                    'post_status' => 'inherit',
                ];

                // Add as Attachment
                $attachment_id = wp_insert_attachment($attachment, $uploaded['file']);

                // Update Metadata
                wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $uploaded['file']));

                $data[] = [
                    'id' => $attachment_id,
                    'url' => $uploaded['url'],
                ];
            }
        }

        $message = $success ? esc_html__('The images are uploaded!', 'modern-events-calendar-lite') : esc_html__('An error occurred!', 'modern-events-calendar-lite');

        $this->main->response(['success' => $success, 'message' => $message, 'data' => $data]);
    }

    public function mec_move_bookings()
    {
        // Check if our nonce is set.
        if (!isset($_POST['_wpnonce'])) $this->main->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'mec_move_bookings')) $this->main->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        $event_id = isset($_POST['id']) ? (int) sanitize_text_field($_POST['id']) : 0;
        $from = isset($_POST['from']) ? (int) sanitize_text_field($_POST['from']) : 0;
        $to_start = isset($_POST['to']) ? (int) sanitize_text_field($_POST['to']) : 0;

        if (!$event_id || !$from || !$to_start) $this->main->response(['success' => 0, 'code' => 'MISS_INFORMATION']);

        // Booking Library
        $book = $this->getBook();

        $bookings = $this->main->get_bookings($event_id, $from);
        foreach ($bookings as $booking)
        {
            $book->move($booking->ID, $from, $to_start);
            $book->move_notify($booking->ID, $to_start);
        }

        $message = '<p class="mec-success">' . sprintf(esc_html__('%s bookings moved to new date', 'modern-events-calendar-lite'), count($bookings)) . '</p>';
        $this->main->response(['success' => 1, 'message' => $message]);
    }

    public function mec_manage_bookings()
    {
        // Check if our nonce is set.
        if (!isset($_POST['_wpnonce'])) $this->main->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'mec_manage_bookings')) $this->main->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        $event_id = isset($_POST['id']) ? (int) sanitize_text_field($_POST['id']) : 0;
        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'cancel';
        $date = isset($_POST['date']) ? (int) sanitize_text_field($_POST['date']) : 0;

        if (!$event_id or !$mode or !$date or !in_array($mode, ['cancel', 'refund'])) $this->main->response(['success' => 0, 'code' => 'MISS_INFORMATION']);

        // Booking Library
        $book = $this->getBook();

        $bookings = $this->main->get_bookings($event_id, $date);
        foreach ($bookings as $booking)
        {
            if ($mode === 'refund') $book->cancel($booking->ID, true);
            else $book->cancel($booking->ID, false);
        }

        if ($mode === 'refund') $message = '<p class="mec-success">' . sprintf(esc_html__('%s bookings canceled (and refunded).', 'modern-events-calendar-lite'), count($bookings)) . '</p>';
        else $message = '<p class="mec-success">' . sprintf(esc_html__('%s bookings canceled.', 'modern-events-calendar-lite'), count($bookings)) . '</p>';

        $this->main->response(['success' => 1, 'message' => $message]);
    }
}
