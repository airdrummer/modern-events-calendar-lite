<?php

namespace MEC\Tracking;

/**
 * Content lifecycle events (marketing plan, events 3 and 4).
 *
 *   mec_event_published           — a MEC event was published (created) or
 *                                   updated, from the WordPress admin.
 *   mec_frontend_event_submitted  — an event arrived through the Frontend
 *                                   Event Submission form (the existing
 *                                   'mec_fes_added' action).
 *
 * Registered unconditionally (NOT inside the admin-only init_tracking()),
 * because FES submissions arrive on front-end requests.
 */
class ContentEvents
{
    /**
     * Post IDs that transitioned to publish during this request, captured on
     * transition_post_status and consumed on save_post, so we can tell
     * "created" (first publish) from "updated" (subsequent saves).
     *
     * @var array
     */
    protected static $created = array();

    /**
     * Idempotent registration (init_hooks runs once, but be safe).
     *
     * @return void
     */
    public static function register()
    {
        static $done = false;
        if ($done) return;
        $done = true;

        // Mark first-time publishes. Fires before MEC writes its event meta.
        add_action('transition_post_status', array(__CLASS__, 'transition'), 10, 3);

        // Late save_post (priority 1000, after MEC saved the event meta at 10),
        // so properties read the final values. Fires for BOTH create and update.
        add_action('save_post', array(__CLASS__, 'saved'), 1000, 3);

        // FES: fired by MEC_feature_fes right after a successful submission.
        add_action('mec_fes_added', array(__CLASS__, 'fes_added'), 10, 2);
    }

    /**
     * transition_post_status: remember first-time publishes only.
     *
     * @param string   $new
     * @param string   $old
     * @param \WP_Post $post
     * @return void
     */
    public static function transition($new, $old, $post)
    {
        if (!is_object($post) || !self::is_event($post)) return;
        if ($new === 'publish' && $old !== 'publish') self::$created[$post->ID] = true;
    }

    /**
     * save_post: publish created / updated.
     *
     * @param int      $post_id
     * @param \WP_Post $post
     * @param bool     $update
     * @return void
     */
    public static function saved($post_id, $post, $update)
    {
        // Cheapest checks first — save_post fires for EVERY post on the site.
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
        if (!is_object($post) || !self::is_event($post)) return;
        if ($post->post_status !== 'publish') return;

        // FES submissions get their own dedicated event, not this one.
        if (get_post_meta($post_id, 'mec_created_by_fes', true)) return;

        $is_created = isset(self::$created[$post_id]);
        if ($is_created) unset(self::$created[$post_id]);
        elseif (!$update) return; // untracked edge (import etc.) — do nothing

        self::event_published($post_id, $is_created ? 'created' : 'updated');
    }

    /**
     * FES submission (event 4).
     *
     * @param int    $post_id
     * @param string $placeholder (unused, kept for the hook signature)
     * @return void
     */
    public static function fes_added($post_id, $placeholder = '')
    {
        if (!$post_id) return;

        $properties = array(
            'submission_status' => get_post_status($post_id),
            'submitter_type'    => is_user_logged_in() ? 'user' : 'guest',
            'event_type'        => self::event_type($post_id),
            'is_recurring'      => self::is_recurring($post_id) ? 1 : 0,
        );

        (new PostHog())->capture('mec_frontend_event_submitted', $properties);
    }

    /**
     * Build and send mec_event_published (event 3).
     *
     * @param int    $post_id
     * @param string $action 'created'|'updated'
     * @return void
     */
    protected static function event_published($post_id, $action)
    {
        $more_info = (string) get_post_meta($post_id, 'mec_more_info', true);

        $properties = array(
            'publish_action'             => $action,
            'is_recurring'               => self::is_recurring($post_id) ? 1 : 0,
            'recurrence_type'            => self::recurrence_type($post_id),
            'event_type'                 => self::event_type($post_id),
            'is_paid'                    => self::is_paid($post_id) ? 1 : 0,
            'more_info_link_type'        => self::more_info_type($more_info),
            'more_info_destination_domain' => self::more_info_domain($more_info),
            'speaker_count_bucket'       => Util::bucket(self::taxonomy_count($post_id, 'mec_speaker')),
            'sponsor_count_bucket'       => Util::bucket(self::taxonomy_count($post_id, 'mec_sponsor')),
        );

        (new PostHog())->capture('mec_event_published', $properties);
    }

    /**
     * Is this post a MEC main event post type?
     *
     * @param \WP_Post $post
     * @return bool
     */
    protected static function is_event($post)
    {
        // Same resolution the Collector uses; falls back to the conventional
        // MEC post type when the MEC object graph is not loaded yet.
        if (class_exists('\MEC\Base') && method_exists('\MEC\Base', 'get_main'))
        {
            $main = \MEC\Base::get_main();
            if ($main && method_exists($main, 'get_main_post_type'))
            {
                return $post->post_type === $main->get_main_post_type();
            }
        }

        return $post->post_type === 'mec-events';
    }

    /**
     * @param int $post_id
     * @return bool
     */
    protected static function is_recurring($post_id)
    {
        return (bool) get_post_meta($post_id, 'mec_repeat_status', true);
    }

    /**
     * @param int $post_id
     * @return string|null
     */
    protected static function recurrence_type($post_id)
    {
        $type = get_post_meta($post_id, 'mec_repeat_type', true);

        return ($type !== '' && $type !== null) ? (string) $type : null;
    }

    /**
     * @param int $post_id
     * @return string
     */
    protected static function event_type($post_id)
    {
        $type = get_post_meta($post_id, 'mec_event_type', true);

        return ($type !== '' && $type !== null) ? (string) $type : 'standard';
    }

    /**
     * @param int $post_id
     * @return bool
     */
    protected static function is_paid($post_id)
    {
        $cost = get_post_meta($post_id, 'mec_cost', true);

        return (is_numeric($cost) && (float) $cost > 0);
    }

    /**
     * Classify the more-info link. Only the SHAPE is reported, never the URL.
     *
     * @param string $url
     * @return string|null
     */
    protected static function more_info_type($url)
    {
        $url = trim((string) $url);
        if ($url === '' || $url === 'http://') return null;

        $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
        if ($host === '') return 'other';

        $home = strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST));
        $home = preg_replace('/^www\./', '', $home);
        $host = preg_replace('/^www\./', '', $host);

        if ($host === $home) return 'internal';

        return 'external_ticket';
    }

    /**
     * External destination domain only (for the External Ticket Seller
     * segment). Null for internal/absent links.
     *
     * @param string $url
     * @return string|null
     */
    protected static function more_info_domain($url)
    {
        if (self::more_info_type($url) !== 'external_ticket') return null;

        $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
        return $host !== '' ? $host : null;
    }

    /**
     * Count terms of a taxonomy attached to the post.
     *
     * @param int    $post_id
     * @param string $taxonomy
     * @return int
     */
    protected static function taxonomy_count($post_id, $taxonomy)
    {
        if (!taxonomy_exists($taxonomy)) return 0;

        $terms = wp_get_post_terms($post_id, $taxonomy, array('fields' => 'ids'));

        return is_wp_error($terms) ? 0 : count($terms);
    }
}
