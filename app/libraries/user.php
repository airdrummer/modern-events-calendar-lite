<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC User class.
 * @author Webnus <info@webnus.net>
 */
class MEC_user extends MEC_base
{
    /**
     * @var MEC_main
     */
    public $main;

    /**
     * @var MEC_db
     */
    public $db;

    /**
     * @var array
     */
    public $settings;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // MEC Main library
        $this->main = $this->getMain();

        // MEC DB Library
        $this->db = $this->getDB();

        // MEC settings
        $this->settings = $this->main->get_settings();
    }

    public function register($attendee, $args)
    {
        $name = $attendee['name'] ?? '';
        $raw = (isset($attendee['reg']) and is_array($attendee['reg'])) ? $attendee['reg'] : [];

        $email = $attendee['email'] ?? '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        $reg = [];
        foreach ($raw as $k => $v) $reg[$k] = (is_array($v) ? $v : stripslashes($v));

        $existed_user_id = $this->main->email_exists($email);

        // User already exist
        if ($existed_user_id !== false) {
            // Map Data
            $event_id = $args['event_id'] ?? 0;
            if ($event_id) $this->save_mapped_data($event_id, $existed_user_id, $reg);

            return $existed_user_id;
        }

        // Update WordPress user first name and last name
        if (strpos($name, ',') !== false) $ex = explode(',', $name);
        else $ex = explode(' ', $name);

        $first_name = $ex[0] ?? '';
        $last_name = '';

        if (isset($ex[1])) {
            unset($ex[0]);
            $last_name = implode(' ', $ex);
        }

        // Get username from filter
        $username = apply_filters('mec_user_register_username', sanitize_user($email), $email);

        // Register User
        $user_id = wp_create_user($username, wp_generate_password(12), $email);
        if(is_wp_error($user_id)) return false;

        // Update First Name and Last Name
        if(trim($first_name) or trim($last_name))
        {
            wp_update_user(array(
                'ID' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name
            ));
        }

        // Map Data
        $event_id = $args['event_id'] ?? 0;
        if($event_id) $this->save_mapped_data($event_id, $user_id, $reg);

        return $user_id;
    }

    public function save_mapped_data($event_id, $user_id, $reg)
    {
        $reg_fields = $this->main->get_reg_fields($event_id);

        foreach($reg as $reg_id => $reg_value)
        {
            $reg_field = $reg_fields[$reg_id] ?? [];
            if(isset($reg_field['mapping']) and trim($reg_field['mapping']))
            {
                $reg_value = maybe_unserialize($reg_value);
                $meta_value = is_array($reg_value) ? implode(',', $reg_value) : $reg_value;

                update_user_meta($user_id, $reg_field['mapping'], $meta_value);
            }
        }
    }

    public function assign($booking_id, $user_id)
    {
        // Registration is disabled
        if(isset($this->settings['booking_registration']) and !$this->settings['booking_registration'] and !get_user_by('ID', $user_id)) update_post_meta($booking_id, 'mec_user_id', $user_id);
        else update_post_meta($booking_id, 'mec_user_id', 'wp');
    }

    public function get($id)
    {
        // Registration is disabled
        if(isset($this->settings['booking_registration']) and !$this->settings['booking_registration'])
        {
            $user = $this->mec($id);
            if(!$user) $user = $this->wp($id);
        }
        else
        {
            $user = $this->wp($id);
            if(!$user) $user = $this->mec($id);
        }

        return $user;
    }

    public function mec($id)
    {
        $data = $this->db->select("SELECT * FROM `#__mec_users` WHERE `id`=".((int) $id), 'loadObject');
        if(!$data) return NULL;

        $user = new stdClass();
        $user->ID = $data->id;
        $user->first_name = stripslashes($data->first_name);
        $user->last_name = stripslashes($data->last_name);
        $user->display_name = trim(stripslashes($data->first_name).' '.stripslashes($data->last_name));
        $user->email = $data->email;
        $user->user_email = $data->email;
        $user->user_registered = $data->created_at;
        $user->data = $user;

        return $user;
    }

    public function wp($id)
    {
        return get_userdata($id);
    }

    public function booking($id)
    {
        $mec_user_id = get_post_meta($id, 'mec_user_id', true);
        if(trim($mec_user_id) and is_numeric($mec_user_id)) return $this->mec($mec_user_id);

        return $this->wp(get_post($id)->post_author);
    }

    public function by_email($email)
    {
        return $this->get($this->id('email', $email));
    }

    public function id($field, $value)
    {
        $id = NULL;

        // Registration is disabled
        if(isset($this->settings['booking_registration']) and !$this->settings['booking_registration'])
        {
            $id = $this->db->select("SELECT `id` FROM `#__mec_users` WHERE `".$field."`='".$this->db->escape($value)."'", 'loadResult');
            if(!$id)
            {
                $user = get_user_by($field, $value);
                if(isset($user->ID)) $id = $user->ID;
            }
        }
        else
        {
            $user = get_user_by($field, $value);
            if(isset($user->ID)) $id = $user->ID;

            if(!$id) $id = $this->db->select("SELECT `id` FROM `#__mec_users` WHERE `".$field."`='".$this->db->escape($value)."'", 'loadResult');
        }

        return $id;
    }
}