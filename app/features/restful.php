<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC RESTful class.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_restful extends MEC_base
{
    /**
     * @var MEC_factory
     */
    public $factory;

    /**
     * @var MEC_restful
     */
    public $restful;

    private $settings;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // Import MEC Factory
        $this->factory = $this->getFactory();

        // Import MEC RESTful
        $this->restful = $this->getRestful();

        // MEC Settings
        $this->settings = $this->getMain()->get_settings();
    }

    /**
     * Initialize
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // Disabled
        if (!isset($this->settings['restful_api_status']) || !$this->settings['restful_api_status']) return;

        $this->factory->action('rest_api_init', [$this, 'register']);
    }

    public function register()
    {
        // Get Events
        register_rest_route($this->restful->get_namespace(), 'events', [
            'methods' => 'GET',
            'callback' => [$this, 'events'],
            'permission_callback' => [$this->restful, 'guest'],
        ]);

        // Get Event
        register_rest_route($this->restful->get_namespace(), 'events/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_event'],
            'permission_callback' => [$this->restful, 'guest'],
            'args' => [
                'id' => [
                    'validate_callback' => function ($param)
                    {
                        return is_numeric($param);
                    },
                ],
            ],
        ]);

        // Login Controller
        register_rest_route($this->restful->get_namespace(), 'login', [
            'methods' => 'POST',
            'callback' => [$this, 'login'],
            'permission_callback' => [$this->restful, 'guest'],
        ]);

        // Upload Image
        register_rest_route($this->restful->get_namespace(), 'images', [
            'methods' => 'POST',
            'callback' => [$this, 'upload_image'],
            'permission_callback' => [$this->restful, 'permission'],
        ]);

        // Upload File
        register_rest_route($this->restful->get_namespace(), 'files', [
            'methods' => 'POST',
            'callback' => [$this, 'upload_file'],
            'permission_callback' => [$this->restful, 'permission'],
        ]);

        // Create Event
        register_rest_route($this->restful->get_namespace(), 'events', [
            'methods' => 'POST',
            'callback' => [$this, 'create_event'],
            'permission_callback' => [$this->restful, 'permission'],
        ]);

        // My Events
        register_rest_route($this->restful->get_namespace(), 'my-events', [
            'methods' => 'GET',
            'callback' => [$this, 'my'],
            'permission_callback' => [$this->restful, 'permission'],
        ]);

        // Edit Event
        register_rest_route($this->restful->get_namespace(), 'events/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'edit_event'],
            'permission_callback' => [$this->restful, 'permission'],
        ]);

        // Trash Event
        register_rest_route($this->restful->get_namespace(), 'events/(?P<id>\d+)/trash', [
            'methods' => 'DELETE',
            'callback' => [$this, 'trash'],
            'permission_callback' => [$this->restful, 'permission'],
        ]);

        // Delete Event
        register_rest_route($this->restful->get_namespace(), 'events/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete'],
            'permission_callback' => [$this->restful, 'permission'],
        ]);

        // Custom Fields
        register_rest_route($this->restful->get_namespace(), 'config/custom-fields', [
            'methods' => 'GET',
            'callback' => [$this, 'custom_fields'],
            'permission_callback' => [$this->restful, 'guest'],
        ]);
    }

    public function events(WP_REST_Request $request)
    {
        $limit = $request->get_param('limit');
        if (!$limit) $limit = 12;

        if (!is_numeric($limit))
        {
            return $this->restful->response([
                'data' => new WP_Error(400, esc_html__('Limit parameter must be numeric!', 'modern-events-calendar-lite')),
                'status' => 400,
            ]);
        }

        $order = $request->get_param('order');
        if (!$order) $order = 'ASC';

        if (!in_array($order, ['ASC', 'DESC']))
        {
            return $this->restful->response([
                'data' => new WP_Error(400, esc_html__('Order parameter is invalid!', 'modern-events-calendar-lite')),
                'status' => 400,
            ]);
        }

        $start_date_type = $request->get_param('start_date_type');
        if (!$start_date_type) $start_date_type = 'today';

        $start_date = $request->get_param('start_date');

        if ($start_date_type === 'date' && !$start_date)
        {
            return $this->restful->response([
                'data' => new WP_Error(400, esc_html__('When the start_date_type parameter is set to date, then start_date parameter is required.', 'modern-events-calendar-lite')),
                'status' => 400,
            ]);
        }

        $end_date_type = $request->get_param('end_date_type');
        if (!$end_date_type) $end_date_type = 'date';

        $end_date = $request->get_param('end_date');

        $show_only_past_events = (int) $request->get_param('show_only_past_events');
        $include_past_events = (int) $request->get_param('include_past_events');

        $show_only_ongoing_events = (int) $request->get_param('show_only_ongoing_events');
        $include_ongoing_events = (int) $request->get_param('include_ongoing_events');

        $args = [
            'sk-options' => [
                'list' => [
                    'limit' => $limit,
                    'order_method' => $order,
                    'start_date_type' => $start_date_type,
                    'start_date' => $start_date,
                    'end_date_type' => $end_date_type,
                    'maximum_date_range' => $end_date,
                ],
            ],
            'show_only_past_events' => $show_only_past_events,
            'show_past_events' => $include_past_events,
            'show_only_ongoing_events' => $show_only_ongoing_events,
            'show_ongoing_events' => $include_ongoing_events,
            's' => (string) $request->get_param('keyword'),
            'label' => (string) $request->get_param('labels'),
            'ex_label' => (string) $request->get_param('ex_labels'),
            'category' => (string) $request->get_param('categories'),
            'ex_category' => (string) $request->get_param('ex_categories'),
            'location' => (string) $request->get_param('locations'),
            'ex_location' => (string) $request->get_param('ex_locations'),
            'address' => (string) $request->get_param('address'),
            'organizer' => (string) $request->get_param('organizers'),
            'ex_organizer' => (string) $request->get_param('ex_organizers'),
            'sponsor' => (string) $request->get_param('sponsors'),
            'speaker' => (string) $request->get_param('speakers'),
            'ex_speaker' => (string) $request->get_param('ex_speakers'),
            'tag' => (string) $request->get_param('tags'),
            'ex_tag' => (string) $request->get_param('ex_tags'),
        ];

        // Events Object
        $EO = new MEC_skin_list();
        $EO->initialize($args);

        // Set Offset
        $EO->offset = (int) $request->get_param('offset');

        // Search
        $EO->search();

        // Events
        $events = $EO->fetch();

        // Response
        return $this->restful->response([
            'data' => [
                'events' => $events,
                'pagination' => [
                    'next_date' => $EO->end_date,
                    'next_offset' => $EO->next_offset,
                    'has_more_events' => $EO->has_more_events,
                    'found' => $EO->found,
                ],
            ],
        ]);
    }

    public function get_event(WP_REST_Request $request)
    {
        // Event ID
        $id = $request->get_param('id');

        // Invalid Event ID
        if (!is_numeric($id))
        {
            return $this->restful->response([
                'data' => new WP_Error(400, esc_html__('Event id must be numeric!', 'modern-events-calendar-lite')),
                'status' => 400,
            ]);
        }

        // Event Post
        $post = get_post($id);

        // Not Event Post or Not Published Event
        if (
            !$post
            || $post->post_type !== $this->getMain()->get_main_post_type()
            || $post->post_status !== 'publish'
            || $post->post_password !== ''
        )
        {
            return $this->restful->response([
                'data' => new WP_Error(404, esc_html__('Event not found!', 'modern-events-calendar-lite')),
                'status' => 404,
            ]);
        }

        // Render Event Data
        $single = new MEC_skin_single();
        $events = $single->get_event_mec($id);

        // Response
        return $this->restful->response([
            'data' => isset($events[0]) && is_object($events[0]) ? $events[0] : new stdClass(),
        ]);
    }

    public function login(WP_REST_Request $request)
    {
        $vars = $request->get_params();

        $username = $vars['username'] ?? '';
        $password = $vars['password'] ?? '';

        // Login
        $response = wp_signon([
            'user_login' => $username,
            'user_password' => $password,
            'remember' => false,
        ], is_ssl());

        // Invalid Credentials
        if (is_wp_error($response)) return $response;

        // Response
        return $this->restful->response([
            'data' => [
                'success' => 1,
                'id' => $response->ID,
                'token' => $this->restful->get_user_token($response->ID),
            ],
            'status' => 200,
        ]);
    }

    public function upload_image(WP_REST_Request $request)
    {
        if (!current_user_can('upload_files')) return $this->restful->response([
            'data' => new WP_Error(401, esc_html__("You're not authorized to upload images!", 'modern-events-calendar-lite')),
            'status' => 401,
        ]);

        // Media Libraries
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $vars = $request->get_file_params();

        $image = is_array($vars) && isset($vars['image']) ? $vars['image'] : [];
        $tmp = $image['tmp_name'] ?? null;

        // Image Not Found
        if (!$tmp) return $this->restful->response([
            'data' => new WP_Error(400, esc_html__('Image is required!', 'modern-events-calendar-lite')),
            'status' => 400,
        ]);

        $ex = explode('.', $image['name']);
        $extension = end($ex);

        // Invalid Extension
        if (!in_array($extension, ['png', 'jpg', 'jpeg', 'gif'])) return $this->restful->response([
            'data' => new WP_Error(400, esc_html__('Invalid image extension! PNG, JPG and GIF images are allowed.', 'modern-events-calendar-lite')),
            'status' => 400,
        ]);

        // Upload File
        $uploaded = wp_handle_upload($image, ['test_form' => false]);

        // Upload Failed
        if (isset($uploaded['error'])) return $this->restful->response([
            'data' => new WP_Error(400, $uploaded['error']),
            'status' => 400,
        ]);

        $name = $image['name'];
        $file = $uploaded['file'];

        $attachment = [
            'post_mime_type' => $uploaded['type'],
            'post_title' => sanitize_file_name($name),
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        // Add as Attachment
        $id = wp_insert_attachment($attachment, $file);

        // Update Metadata
        wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $file));

        // Response
        return $this->restful->response([
            'data' => [
                'success' => 1,
                'image_id' => $id,
            ],
            'status' => 200,
        ]);
    }

    public function upload_file(WP_REST_Request $request)
    {
        if (!current_user_can('upload_files')) return $this->restful->response([
            'data' => new WP_Error(401, esc_html__("You're not authorized to upload files!", 'modern-events-calendar-lite')),
            'status' => 401,
        ]);

        // Media Libraries
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $vars = $request->get_file_params();

        $file = is_array($vars) && isset($vars['file']) ? $vars['file'] : [];
        $tmp = $file['tmp_name'] ?? null;

        // Image Not Found
        if (!$tmp) return $this->restful->response([
            'data' => new WP_Error(400, esc_html__('File is required!', 'modern-events-calendar-lite')),
            'status' => 400,
        ]);

        $ex = explode('.', $file['name']);
        $extension = end($ex);

        // Invalid Extension
        if (!in_array($extension, ['docx', 'jpeg', 'jpg', 'png', 'pdf', 'zip'])) return $this->restful->response([
            'data' => new WP_Error(400, esc_html__('Invalid file extension! Docx, PNG, JPG, PDF and zip files are allowed.', 'modern-events-calendar-lite')),
            'status' => 400,
        ]);

        // Upload File
        $uploaded = wp_handle_upload($file, ['test_form' => false]);

        // Upload Failed
        if (isset($uploaded['error'])) return $this->restful->response([
            'data' => new WP_Error(400, $uploaded['error']),
            'status' => 400,
        ]);

        $name = $file['name'];
        $wp_upload_dir = wp_upload_dir();

        $attachment = [
            'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path($uploaded['file']),
            'post_mime_type' => $uploaded['type'],
            'post_title' => sanitize_file_name($name),
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        // Add as Attachment
        $id = wp_insert_attachment($attachment, $uploaded['file']);

        // Update Metadata
        wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $uploaded['file']));

        // Response
        return $this->restful->response([
            'data' => [
                'success' => 1,
                'file_id' => $id,
            ],
            'status' => 200,
        ]);
    }

    public function create_event(WP_REST_Request $request, $event_id = null): WP_REST_Response
    {
        $vars = $request->get_params();

        $tax = isset($vars['taxonomies']) && is_array($vars['taxonomies']) ? $vars['taxonomies'] : [];
        $post_title = isset($vars['title']) ? sanitize_text_field($vars['title']) : '';
        $post_content = $vars['content'] ?? '';

        // Event Title is Required
        if (!trim($post_title)) return $this->restful->response([
            'data' => new WP_Error('400', esc_html__("Event title field is required!", 'modern-events-calendar-lite')),
            'status' => 400,
        ]);

        $main = $this->getMain();

        // Post Status
        $status = 'pending';
        if (current_user_can('publish_posts')) $status = 'publish';

        // Event location
        $location = isset($tax['location']) && is_array($tax['location']) ? $tax['location'] : [];
        $location_id = $location && isset($location['name']) ? $main->save_location([
            'name' => trim($location['name']),
            'address' => $location['address'] ?? '',
            'latitude' => $location['latitude'] ?? 0,
            'longitude' => $location['longitude'] ?? 0,
            'thumbnail' => $location['thumbnail'] ?? '',
        ]) : 1;

        // Event Organizer
        $organizer = isset($tax['organizer']) && is_array($tax['organizer']) ? $tax['organizer'] : [];
        $organizer_id = $organizer && isset($organizer['name']) ? $main->save_organizer([
            'name' => trim($organizer['name']),
            'email' => $organizer['email'] ?? '',
            'tel' => $organizer['tel'] ?? '',
            'url' => $organizer['url'] ?? '',
            'thumbnail' => $location['thumbnail'] ?? '',
        ]) : 1;

        // Event Categories
        $category_ids = [];
        if (isset($tax['categories']) && is_array($tax['categories']))
        {
            foreach ($tax['categories'] as $category)
            {
                $category_id = $main->save_category([
                    'name' => trim($category),
                ]);

                if ($category_id) $category_ids[] = $category_id;
            }
        }

        // Event Tags
        $tag_ids = [];
        if (isset($tax['tags']) && is_array($tax['tags']))
        {
            foreach ($tax['tags'] as $tag)
            {
                $tag_id = $main->save_tag([
                    'name' => trim($tag),
                ]);

                if ($tag_id) $tag_ids[] = $tag_id;
            }
        }

        // Event Labels
        $label_ids = [];
        if (isset($tax['labels']) && is_array($tax['labels']))
        {
            foreach ($tax['labels'] as $label)
            {
                $label_id = isset($label['name']) && trim($label['name']) ? $main->save_label([
                    'name' => trim($label['name']),
                    'color' => $label['color'] ?? '',
                ]) : 0;

                if ($label_id) $label_ids[] = $label_id;
            }
        }

        // Event Speakers
        $speaker_ids = [];
        if (isset($tax['speakers']) && is_array($tax['speakers']))
        {
            foreach ($tax['speakers'] as $speaker)
            {
                $speaker_id = isset($speaker['name']) && trim($speaker['name']) ? $main->save_speaker([
                    'name' => trim($speaker['name']),
                    'job_title' => $speaker['job_title'] ?? '',
                    'tel' => $speaker['tel'] ?? '',
                    'email' => $speaker['email'] ?? '',
                    'facebook' => $speaker['facebook'] ?? '',
                    'twitter' => $speaker['twitter'] ?? '',
                    'instagram' => $speaker['instagram'] ?? '',
                    'linkedin' => $speaker['linkedin'] ?? '',
                    'website' => $speaker['website'] ?? '',
                    'thumbnail' => $speaker['thumbnail'] ?? '',
                ]) : 0;

                if ($speaker_id) $speaker_ids[] = $speaker_id;
            }
        }

        // Event Sponsors
        $sponsor_ids = [];
        if (isset($tax['sponsors']) && is_array($tax['sponsors']) && isset($this->settings['sponsors_status']) && $this->settings['sponsors_status'])
        {
            foreach ($tax['sponsors'] as $sponsor)
            {
                $sponsor_id = isset($sponsor['name']) && trim($sponsor['name']) ? $main->save_sponsor([
                    'name' => trim($sponsor['name']),
                    'link' => $sponsor['link'] ?? '',
                ]) : 0;

                if ($sponsor_id) $sponsor_ids[] = $sponsor_id;
            }
        }

        // Start
        $start_date = $vars['start_date'] ?? current_time('Y-m-d');
        $start_hour = $vars['start_hour'] ?? 8;
        $start_minutes = $vars['start_minutes'] ?? 0;
        $start_ampm = $vars['start_ampm'] ?? 'AM';

        // End
        $end_date = $vars['end_date'] ?? current_time('Y-m-d');
        $end_hour = $vars['end_hour'] ?? 6;
        $end_minutes = $vars['end_minutes'] ?? 0;
        $end_ampm = $vars['end_ampm'] ?? 'PM';

        // Time Options
        $allday = $vars['allday'] ?? 0;
        $time_comment = $vars['time_comment'] ?? '';
        $hide_time = $vars['hide_time'] ?? 0;
        $hide_end_time = $vars['hide_end_time'] ?? 0;

        // Repeat Options
        $repeat_status = $vars['repeat_status'] ?? 0;
        $repeat_type = $vars['repeat_type'] ?? '';
        $repeat_interval = $vars['repeat_interval'] ?? 1;
        $finish = $vars['finish'] ?? '';
        $year = $vars['year'] ?? '';
        $month = $vars['month'] ?? '';
        $day = $vars['day'] ?? '';
        $week = $vars['week'] ?? '';
        $weekday = $vars['weekday'] ?? '';
        $weekdays = $vars['weekdays'] ?? '';
        $days = $vars['days'] ?? '';
        $not_in_days = $vars['not_in_days'] ?? '';

        $additional_organizer_ids = [];
        if (isset($vars['additional_organizer_ids']) && is_array($vars['additional_organizer_ids']))
        {
            $additional_organizer_ids[] = $vars['additional_organizer_ids'];
        }

        $hourly_schedules = [];
        if (isset($vars['hourly_schedules']) && is_array($vars['hourly_schedules']))
        {
            $hourly_schedules[] = $vars['hourly_schedules'];
        }

        $tickets = [];
        if (isset($vars['tickets']) && is_array($vars['tickets']))
        {
            $tickets[] = $vars['tickets'];
        }

        $fees = [];
        if (isset($vars['fees']) && is_array($vars['fees']))
        {
            $fees[] = $vars['fees'];
        }

        $advanced_days = [];
        if (isset($vars['advanced_days']) && is_array($vars['advanced_days']))
        {
            $advanced_days[] = $vars['advanced_days'];
        }

        $args = [
            'title' => $post_title,
            'content' => $post_content,
            'status' => $status,
            'location_id' => $location_id,
            'organizer_id' => $organizer_id,
            'date' => [
                'start' => [
                    'date' => $start_date,
                    'hour' => $start_hour,
                    'minutes' => $start_minutes,
                    'ampm' => $start_ampm,
                ],
                'end' => [
                    'date' => $end_date,
                    'hour' => $end_hour,
                    'minutes' => $end_minutes,
                    'ampm' => $end_ampm,
                ],
                'repeat' => [],
                'allday' => $allday,
                'comment' => $time_comment,
                'hide_time' => $hide_time,
                'hide_end_time' => $hide_end_time,
            ],
            'start' => $start_date,
            'start_time_hour' => $start_hour,
            'start_time_minutes' => $start_minutes,
            'start_time_ampm' => $start_ampm,
            'end' => $end_date,
            'end_time_hour' => $end_hour,
            'end_time_minutes' => $end_minutes,
            'end_time_ampm' => $end_ampm,
            'repeat_status' => $repeat_status,
            'repeat_type' => $repeat_type,
            'interval' => $repeat_interval,
            'finish' => $finish,
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'week' => $week,
            'weekday' => $weekday,
            'weekdays' => $weekdays,
            'days' => $days,
            'not_in_days' => $not_in_days,
            'meta' => [
                'mec_source' => 'mec-calendar',
                'mec_dont_show_map' => $vars['dont_show_map'] ?? '',
                'mec_color' => $vars['color'] ?? '',
                'mec_read_more' => $vars['read_more'] ?? '',
                'mec_more_info' => $vars['more_info'] ?? '',
                'mec_more_info_title' => $vars['more_info_title'] ?? '',
                'mec_more_info_target' => $vars['more_info_target'] ?? '',
                'mec_cost' => $vars['cost'] ?? '',
                'mec_additional_organizer_ids' => $additional_organizer_ids,
                'mec_repeat' => [
                    'status' => $repeat_status,
                    'type' => $repeat_type,
                    'interval' => $repeat_interval,
                    'end' => $vars['end'] ?? '',
                    'end_at_date' => $vars['end_at_date'] ?? '',
                    'end_at_occurrences' => $vars['end_at_occurrences'] ?? '',
                ],
                'mec_allday' => $allday,
                'mec_hide_time' => $hide_time,
                'mec_hide_end_time' => $hide_end_time,
                'mec_comment' => $time_comment,
                'mec_repeat_end' => $vars['repeat_end'] ?? '',
                'mec_repeat_end_at_occurrences' => $vars['repeat_end_at_occurrences'] ?? '',
                'mec_repeat_end_at_date' => $vars['repeat_end_at_date'] ?? '',
                'mec_in_days' => $vars['in_days'] ?? '',
                'mec_not_in_days' => $vars['not_in_days'] ?? '',
                'mec_hourly_schedules' => $hourly_schedules,
                'mec_booking' => [
                    'bookings_limit_unlimited' => $vars['bookings_limit_unlimited'] ?? '',
                    'bookings_limit' => $vars['bookings_limit'] ?? '',
                ],
                'mec_tickets' => $tickets,
                'mec_fees_global_inheritance' => $vars['fees_global_inheritance'] ?? 1,
                'mec_fees' => $fees,
                'mec_reg_fields_global_inheritance' => 1,
                'mec_reg_fields' => [],
                'mec_advanced_days' => $advanced_days,
                'mec_fields' => [],
            ],
        ];

        // Insert the event into MEC
        $post_id = $main->save_event($args, $event_id);

        // Set location to the post
        if ($location_id) wp_set_object_terms($post_id, (int) $location_id, 'mec_location');

        // Set organizer to the post
        if ($organizer_id) wp_set_object_terms($post_id, (int) $organizer_id, 'mec_organizer');

        // Set categories to the post
        if (count($category_ids)) foreach ($category_ids as $category_id) wp_set_object_terms($post_id, (int) $category_id, 'mec_category', true);

        // Set tags to the post
        if (count($tag_ids)) foreach ($tag_ids as $tag_id) wp_set_object_terms($post_id, (int) $tag_id, apply_filters('mec_taxonomy_tag', ''), true);

        // Set labels to the post
        if (count($label_ids)) foreach ($label_ids as $label_id) wp_set_object_terms($post_id, (int) $label_id, 'mec_label', true);

        // Set speakers to the post
        if (count($speaker_ids)) foreach ($speaker_ids as $speaker_id) wp_set_object_terms($post_id, (int) $speaker_id, 'mec_speaker', true);

        // Set sponsors to the post
        if (count($sponsor_ids)) foreach ($sponsor_ids as $sponsor_id) wp_set_object_terms($post_id, (int) $sponsor_id, 'mec_sponsor', true);

        // Featured Image
        if (isset($vars['thumbnail']) && $vars['thumbnail']) set_post_thumbnail($post_id, (int) $vars['thumbnail']);

        // Publish Event
        if ($status === 'publish' && get_post_status($post_id) !== 'published') wp_publish_post($post_id);

        if ($status === 'publish') $message = esc_html__('The event is published.', 'modern-events-calendar-lite');
        else $message = esc_html__('The event is submitted. It will publish as soon as possible.', 'modern-events-calendar-lite');

        // Trigger Action
        do_action('mec_api_event_created', $post_id, $request);

        // Response
        return $this->restful->response([
            'data' => [
                'success' => 1,
                'message' => $message,
                'event_id' => $post_id,
            ],
            'status' => 200,
        ]);
    }

    public function edit_event(WP_REST_Request $request): WP_REST_Response
    {
        $id = $request->get_param('id');

        // Current User is not Authorized to Edit this Event
        if (!current_user_can('edit_post', $id)) return $this->restful->response([
            'data' => new WP_Error('401', esc_html__("You're not authorized to edit this event!", 'modern-events-calendar-lite')),
            'status' => 401,
        ]);

        return $this->create_event($request, $id);
    }

    public function my(WP_REST_Request $request)
    {
        $limit = $request->get_param('limit');
        if (!$limit) $limit = 12;

        if (!is_numeric($limit))
        {
            return $this->restful->response([
                'data' => new WP_Error(400, esc_html__('Limit parameter must be numeric!', 'modern-events-calendar-lite')),
                'status' => 400,
            ]);
        }

        // Get Current User
        $user = wp_get_current_user();

        // Invalid User
        if (is_wp_error($user)) return $user;

        // Page
        $paged = $request->get_param('paged');
        if (!$paged) $paged = 1;

        // The Query
        $query = new WP_Query([
            'post_type' => $this->getMain()->get_main_post_type(),
            'posts_per_page' => $limit,
            'paged' => $paged,
            'post_status' => ['pending', 'draft', 'future', 'publish'],
            'author' => get_current_user_id(),
        ]);

        $events = [];
        while ($query->have_posts())
        {
            $query->the_post();

            $events[] = [
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'url' => get_the_permalink(),
                'status' => get_post_status(),
            ];
        }

        wp_reset_postdata();

        // Response
        return $this->restful->response([
            'data' => [
                'events' => $events,
                'pagination' => [
                    'current_page' => $paged,
                    'total_pages' => $query->max_num_pages,
                ],
            ],
            'status' => 200,
        ]);
    }

    public function trash(WP_REST_Request $request): WP_REST_Response
    {
        $id = $request->get_param('id');

        // Current User is not Authorized to Delete this Event
        if (!current_user_can('delete_post', $id)) return $this->restful->response([
            'data' => new WP_Error('401', esc_html__("You're not authorized to trash this event!", 'modern-events-calendar-lite')),
            'status' => 401,
        ]);

        // Event
        $event = get_post($id);

        // Not Found!
        if (!$event || (isset($event->post_type) && $event->post_type !== $this->getMain()->get_main_post_type())) return $this->restful->response([
            'data' => new WP_Error('404', esc_html__('Event not found!', 'modern-events-calendar-lite')),
            'status' => 404,
        ]);

        // Trash
        wp_trash_post($id);

        // Response
        return $this->restful->response([
            'data' => [
                'success' => 1,
            ],
            'status' => 200,
        ]);
    }

    public function delete(WP_REST_Request $request): WP_REST_Response
    {
        $id = $request->get_param('id');

        // Current User is not Authorized to Delete this Event
        if (!current_user_can('delete_post', $id)) return $this->restful->response([
            'data' => new WP_Error('401', esc_html__("You're not authorized to delete this event!", 'modern-events-calendar-lite')),
            'status' => 401,
        ]);

        // Event
        $event = get_post($id);

        // Not Found!
        if (!$event || (isset($event->post_type) && $event->post_type !== $this->getMain()->get_main_post_type())) return $this->restful->response([
            'data' => new WP_Error('404', esc_html__('Event not found!', 'modern-events-calendar-lite')),
            'status' => 404,
        ]);

        // Delete
        wp_delete_post($id, true);

        // Response
        return $this->restful->response([
            'data' => [
                'success' => 1,
            ],
            'status' => 200,
        ]);
    }

    public function custom_fields(): WP_REST_Response
    {
        $fields = $this->getMain()->get_event_fields();

        // Response
        return $this->restful->response([
            'data' => [
                'success' => 1,
                'fields' => $fields,
            ],
            'status' => 200,
        ]);
    }
}
