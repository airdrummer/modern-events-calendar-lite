<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC masonry class.
 * @author Webnus <info@webnus.net>
 */
class MEC_skin_masonry extends MEC_skins
{
    /**
     * @var string
     */
    public $skin = 'masonry';
    public $date_format_1 = 'j';
    public $date_format_2 = 'F';
    public $filter_by = 'category';
    public $masonry_like_grid;
    public $fit_to_row;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Registers skin actions into WordPress
     * @author Webnus <info@webnus.net>
     */
    public function actions()
    {
        $this->factory->action('wp_ajax_mec_masonry_load_more', array($this, 'load_more'));
        $this->factory->action('wp_ajax_nopriv_mec_masonry_load_more', array($this, 'load_more'));
    }
    
    /**
     * Initialize the skin
     * @author Webnus <info@webnus.net>
     * @param array $atts
     */
    public function initialize($atts)
    {
        $this->atts = $atts;
        
        // Skin Options
        $this->skin_options = (isset($this->atts['sk-options']) and isset($this->atts['sk-options'][$this->skin])) ? $this->atts['sk-options'][$this->skin] : [];

        // Icons
        $this->icons = $this->main->icons(
            isset($this->atts['icons']) && is_array($this->atts['icons']) ? $this->atts['icons'] : []
        );
        
        // Date Formats
        $this->date_format_1 = (isset($this->skin_options['date_format1']) and trim($this->skin_options['date_format1'])) ? $this->skin_options['date_format1'] : 'j';
        $this->date_format_2 = (isset($this->skin_options['date_format2']) and trim($this->skin_options['date_format2'])) ? $this->skin_options['date_format2'] : 'F';

        // Filter By
        $this->filter_by = (isset($this->skin_options['filter_by']) and trim($this->skin_options['filter_by'])) ? $this->skin_options['filter_by'] : '';
        
        // Search Form Options
        $this->sf_options = $this->atts['sf-options'][$this->skin] ?? [];
        
        // Search Form Status
        $this->sf_status = $this->atts['sf_status'] ?? true;
        $this->sf_display_label = $this->atts['sf_display_label'] ?? false;
        $this->sf_dropdown_method = $this->atts['sf_dropdown_method'] ?? '1';
        $this->sf_reset_button = $this->atts['sf_reset_button'] ?? false;
        $this->sf_refine = $this->atts['sf_refine'] ?? false;
        
        // Generate an ID for the skin
        $this->id = $this->atts['id'] ?? mt_rand(100, 999);
        
        // Set the ID
        if(!isset($this->atts['id'])) $this->atts['id'] = $this->id;
        
        // Show "Load More" button or not
        $this->load_more_button = $this->skin_options['load_more_button'] ?? true;

        // Pagination
        $this->pagination = $this->skin_options['pagination'] ?? (!$this->load_more_button ? '0' : 'loadmore');

        // Show Masonry like grid
        $this->masonry_like_grid = $this->skin_options['masonry_like_grid'] ?? true;

        // Show "Sort by date" button or not
        $this->fit_to_row = $this->skin_options['fit_to_row'] ?? true;

        // HTML class
        $this->html_class = '';
        if(isset($this->atts['html-class']) and trim($this->atts['html-class']) != '') $this->html_class = $this->atts['html-class'];

        // Booking Button
        $this->booking_button = isset($this->skin_options['booking_button']) ? (int) $this->skin_options['booking_button'] : 0;

        // SED Method
        $this->sed_method = $this->get_sed_method();

        // Order Method
        $this->order_method = (isset($this->skin_options['order_method']) and trim($this->skin_options['order_method'])) ? $this->skin_options['order_method'] : 'ASC';

        // Image popup
        $this->image_popup = $this->skin_options['image_popup'] ?? '0';

        // reason_for_cancellation
        $this->reason_for_cancellation = $this->skin_options['reason_for_cancellation'] ?? false;

        // display_label
        $this->display_label = $this->skin_options['display_label'] ?? false;
        
        // From Widget
        $this->widget = isset($this->atts['widget']) && trim($this->atts['widget']);
        
        // Init MEC
        $this->args['mec-init'] = true;
        $this->args['mec-skin'] = $this->skin;
        
        // Post Type
        $this->args['post_type'] = $this->main->get_main_post_type();

        // Post Status
        $this->args['post_status'] = 'publish';
        
        // Keyword Query
        $this->args['s'] = $this->keyword_query();
        
        // Taxonomy
        $this->args['tax_query'] = $this->tax_query();
        
        // Meta
        $this->args['meta_query'] = $this->meta_query();
        
        // Tag
        if(apply_filters('mec_taxonomy_tag', '') === 'post_tag') $this->args['tag'] = $this->tag_query();
        
        // Author
        $this->args['author'] = $this->author_query();
        $this->args['author__not_in'] = $this->author_query_ex();
        
        // Pagination Options
        $this->paged = get_query_var('paged', 1);
        $this->limit = (isset($this->skin_options['limit']) and trim($this->skin_options['limit'])) ? $this->skin_options['limit'] : 24;
        
        $this->args['posts_per_page'] = $this->limit;
        $this->args['paged'] = $this->paged;
        
        // Sort Options
        $this->args['orderby'] = 'mec_start_day_seconds ID';
        $this->args['order'] = (in_array($this->order_method, array('ASC', 'DESC')) ? $this->order_method : 'ASC');
        $this->args['meta_key'] = 'mec_start_day_seconds';
        
        // Exclude Posts
        if(isset($this->atts['exclude']) and is_array($this->atts['exclude']) and count($this->atts['exclude'])) $this->args['post__not_in'] = $this->atts['exclude'];
        
        // Include Posts
        if(isset($this->atts['include']) and is_array($this->atts['include']) and count($this->atts['include'])) $this->args['post__in'] = $this->atts['include'];

        // Show Only Expired Events
        $this->show_only_expired_events = (isset($this->atts['show_only_past_events']) and trim($this->atts['show_only_past_events'])) ? '1' : '0';

        // Maximum Date Range.
        $this->maximum_date_range = $this->get_end_date();

        if($this->show_only_expired_events)
        {
            $this->order_method = 'DESC';
            $this->atts['show_past_events'] = '1';
            $this->args['order'] = 'DESC';
        }

        // Show Past Events
        $this->args['mec-past-events'] = isset($this->atts['show_past_events']) ? $this->atts['show_past_events'] : '0';

        // Start Date
        $this->start_date = $this->get_start_date();
        
        // We will extend the end date in the loop
        $this->end_date = $this->start_date;

        // Show Ongoing Events
        $this->show_ongoing_events = (isset($this->atts['show_only_ongoing_events']) and trim($this->atts['show_only_ongoing_events'])) ? '1' : '0';
        if($this->show_ongoing_events)
        {
            $this->args['mec-show-ongoing-events'] = $this->show_ongoing_events;
            if((strpos($this->style, 'fluent') === false && strpos($this->style, 'liquid') === false))
            {
                $this->maximum_date = $this->start_date;
            }
        }

        // Include Ongoing Events
        $this->include_ongoing_events = (isset($this->atts['show_ongoing_events']) and trim($this->atts['show_ongoing_events'])) ? '1' : '0';
        if($this->include_ongoing_events) $this->args['mec-include-ongoing-events'] = $this->include_ongoing_events;
        
        // Set start time
        if(isset($this->atts['seconds']))
        {
            $this->args['mec-seconds'] = $this->atts['seconds'];
            $this->args['mec-seconds-date'] = isset($this->atts['seconds_date']) ? $this->atts['seconds_date'] : $this->start_date;
        }
        
        // Apply Maximum Date
        $apply_sf_date = isset($_REQUEST['apply_sf_date']) ? sanitize_text_field($_REQUEST['apply_sf_date']) : 0;
        $month = (isset($this->sf) && isset($this->sf['month']) && trim($this->sf['month'])) ? $this->sf['month'] : (isset($_REQUEST['mec_month']) ? $_REQUEST['mec_month'] : '');
        if($apply_sf_date == 1 and trim($month)) $this->maximum_date = date('Y-m-t', strtotime($this->start_date));
        
        // Found Events
        $this->found = 0;

        do_action('mec-masonry-initialize-end', $this);
    }
    
    /**
     * Returns start day of skin for filtering events
     * @author Webnus <info@webnus.net>
     * @return string
     */
    public function get_start_date()
    {
        // Default date
        $date = current_time('Y-m-d');
        
        if(isset($this->skin_options['start_date_type']) and $this->skin_options['start_date_type'] == 'today') $date = current_time('Y-m-d');
        elseif(isset($this->skin_options['start_date_type']) and $this->skin_options['start_date_type'] == 'tomorrow') $date = date('Y-m-d', strtotime('Tomorrow'));
        elseif(isset($this->skin_options['start_date_type']) and $this->skin_options['start_date_type'] == 'yesterday') $date = date('Y-m-d', strtotime('Yesterday'));
        elseif(isset($this->skin_options['start_date_type']) and $this->skin_options['start_date_type'] == 'start_last_month') $date = date('Y-m-d', strtotime('first day of last month'));
        elseif(isset($this->skin_options['start_date_type']) and $this->skin_options['start_date_type'] == 'start_current_month') $date = date('Y-m-d', strtotime('first day of this month'));
        elseif(isset($this->skin_options['start_date_type']) and $this->skin_options['start_date_type'] == 'start_next_month') $date = date('Y-m-d', strtotime('first day of next month'));
        elseif(isset($this->skin_options['start_date_type']) and $this->skin_options['start_date_type'] == 'date') $date = date('Y-m-d', strtotime($this->skin_options['start_date']));
        
        // Hide past events
        if(isset($this->atts['show_past_events']) and !trim($this->atts['show_past_events']))
        {
            $today = current_time('Y-m-d');
            if(strtotime($date) < strtotime($today)) $date = $today;
        }

        // Show only expired events
        if(isset($this->show_only_expired_events) and $this->show_only_expired_events)
        {
            $now = date('Y-m-d H:i:s', current_time('timestamp'));
            if(strtotime($date) > strtotime($now)) $date = $now;
        }

        // MEC Next Page
        if(isset($_REQUEST['mec_next_page']) and trim($_REQUEST['mec_next_page']))
        {
            $ex = explode(':', $_REQUEST['mec_next_page']);

            if(strtotime($ex[0])) $date = $ex[0];
            if(isset($ex[1])) $this->offset = $ex[1];
        }
        
        return $date;
    }

    /**
     * Load more events for AJAX request
     * @author Webnus <info@webnus.net>
     * @return void
     */
    public function load_more()
    {
        $this->sf = (isset($_REQUEST['sf']) and is_array($_REQUEST['sf'])) ? $this->main->sanitize_deep_array($_REQUEST['sf']) : [];

        $mec_filter_by = isset($_REQUEST['mec_filter_by']) ? sanitize_text_field($_REQUEST['mec_filter_by']) : '';
        $mec_filter_value = isset($_REQUEST['mec_filter_value']) ? sanitize_text_field($_REQUEST['mec_filter_value']) : '';
        if($mec_filter_by and ($mec_filter_value and $mec_filter_value != '*')) $this->sf[$mec_filter_by] = $mec_filter_value;

        $apply_sf_date = isset($_REQUEST['apply_sf_date']) ? sanitize_text_field($_REQUEST['apply_sf_date']) : 1;
        $atts = $this->sf_apply(((isset($_REQUEST['atts']) and is_array($_REQUEST['atts'])) ? $this->main->sanitize_deep_array($_REQUEST['atts']) : array()), $this->sf, $apply_sf_date);
        
        // Initialize the skin
        $this->initialize($atts);
        
        // Override variables
        $this->start_date = isset($_REQUEST['mec_start_date']) ? sanitize_text_field($_REQUEST['mec_start_date']) : date('y-m-d');
        $this->end_date = $this->start_date;
        $this->offset = isset($_REQUEST['mec_offset']) ? sanitize_text_field($_REQUEST['mec_offset']) : 0;
		
        // Apply Maximum Date
        $month = (isset($this->sf) && isset($this->sf['month']) && trim($this->sf['month'])) ? $this->sf['month'] : (isset($_REQUEST['mec_month']) ? $_REQUEST['mec_month'] : '');
        if($apply_sf_date == 1 and trim($month)) $this->maximum_date = date('Y-m-t', strtotime($this->start_date));
        
        // Return the events
        $this->atts['return_items'] = true;
        if (!$apply_sf_date) $this->loading_more = true;
        
        // Fetch the events
        $this->fetch();
        
        // Return the output
        $output = $this->output();
        
        echo json_encode($output);
        exit;
    }

    public function filter_by()
    {
        $output = '<div class="mec-events-masonry-cats"><a href="#" class="mec-masonry-cat-selected" data-filter="*">'.esc_html__('All', 'modern-events-calendar-lite').'</a>';

        $taxonomy = $this->filter_by_get_taxonomy();
        $terms = get_terms($taxonomy, array
        (
            'hide_empty' => true,
            'include' => ((isset($this->atts[$this->filter_by]) and trim($this->atts[$this->filter_by])) ? $this->atts[$this->filter_by] : ''),
        ));

        foreach($terms as $term) $output .= '<a href="#" data-filter=".mec-t'.esc_attr($term->term_id).'">'.esc_html($term->name).'</a>';

        $output .= '</div>';
        return $output;
    }

    public function filter_by_get_taxonomy()
    {
        if($this->filter_by == 'label') $taxonomy = 'mec_label';
        elseif($this->filter_by == 'location') $taxonomy = 'mec_location';
        elseif($this->filter_by == 'organizer') $taxonomy = 'mec_organizer';
        else $taxonomy = 'mec_category';

        return $taxonomy;
    }

    public function filter_by_classes($event_id)
    {
        $output = '';

        $taxonomy = $this->filter_by_get_taxonomy();
        $terms = wp_get_post_terms($event_id, $taxonomy, array
        (
            'hide_empty' => true,
        ));

        foreach($terms as $term) $output .= ' mec-t'.$term->term_id;
        return trim($output);
    }
}
