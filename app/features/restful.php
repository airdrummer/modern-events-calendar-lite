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
}