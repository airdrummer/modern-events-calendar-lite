<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC update class.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_update extends MEC_base
{
    public $factory;
    public $main;
    public $db;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // Import MEC Main
        $this->main = $this->getMain();
        
        // Import MEC DB
        $this->db = $this->getDB();

        // Import MEC Factory
        $this->factory = $this->getFactory();
    }
    
    /**
     * Initialize update feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
		// Plugin is not installed yet so no need to run these upgrades
        if(!get_option('mec_installed', 0)) return;

        // Run the Update Function
        $this->factory->action('admin_init', array($this, 'update'));
    }

    public function update()
    {
        $version = get_option('mec_version', '1.0.0');

        // It's updated to latest version
        if(version_compare($version, $this->main->get_version(), '>=')) return;
     
        // Run the updates one by one
        if(version_compare($version, '1.0.3', '<')) $this->version103();
        if(version_compare($version, '1.3.0', '<')) $this->version130();
        if(version_compare($version, '1.5.0', '<')) $this->version150();
        if(version_compare($version, '2.2.0', '<')) $this->version220();
        if(version_compare($version, '2.9.0', '<')) $this->version290();
        if(version_compare($version, '3.2.0', '<')) $this->version320();
        if(version_compare($version, '3.5.0', '<')) $this->version350();
        if(version_compare($version, '4.0.0', '<')) $this->version400();
        if(version_compare($version, '4.3.0', '<')) $this->version430();
        if(version_compare($version, '4.4.6', '<')) $this->version446();
        if(version_compare($version, '4.6.1', '<')) $this->version461();
        if(version_compare($version, '4.9.0', '<')) $this->version490();
        if(version_compare($version, '5.0.5', '<')) $this->version505();
        if(version_compare($version, '5.5.1', '<')) $this->version551();
        if(version_compare($version, '5.7.1', '<')) $this->version571();
        if(version_compare($version, '5.10.0', '<')) $this->version5100();
        if(version_compare($version, '5.11.0', '<')) $this->version5110();
        if(version_compare($version, '5.12.6', '<')) $this->version5126();
        if(version_compare($version, '5.13.5', '<')) $this->version5135();
        if(version_compare($version, '5.14.0', '<')) $this->version5140();
        if(version_compare($version, '5.16.0', '<')) $this->version5160();
        if(version_compare($version, '5.16.1', '<')) $this->version5161();
        if(version_compare($version, '5.16.2', '<')) $this->version5162();
        if(version_compare($version, '5.17.0', '<')) $this->version5170();
        if(version_compare($version, '5.17.1', '<')) $this->version5171();
        if(version_compare($version, '5.19.1', '<')) $this->version5191();
        if(version_compare($version, '5.22.0', '<')) $this->version5220();
        if(version_compare($version, '6.0.0', '<')) $this->version600();
        // if(version_compare($version, '6.2.6', '>')) $this->version626();
        if(version_compare($version, '6.4.0', '<')) $this->version640();
        if(version_compare($version, '6.5.3', '<')) $this->version653();
        if(version_compare($version, '6.6.11', '<')) $this->version6611();
        if(version_compare($version, '6.7.5', '<')) $this->version675();
        if(version_compare($version, '6.8.35', '<')) $this->version6835();
        if(version_compare($version, '7.0.0', '<')) $this->version700();
        if(version_compare($version, '7.2.0', '<')) $this->version720();
        if(version_compare($version, '7.4.0', '<')) $this->version740();
        if(version_compare($version, '7.11.0', '<')) $this->version7110();
        if(version_compare($version, '7.13.0', '<')) $this->version7130();

        // Update to latest version to prevent running the code twice
        update_option('mec_version', $this->main->get_version());
    }

    public function update_capabilities($capabilities)
    {
        // Site Admin
        $role = get_role('administrator');
        if($role) foreach($capabilities as $capability) $role->add_cap($capability, true);

        // Multisite
        if(is_multisite())
        {
            // All Super Admins
            $supers = get_super_admins();
            foreach($supers as $admin)
            {
                $user = new WP_User(0, $admin);
                foreach($capabilities as $capability) $user->add_cap($capability, true);
            }
        }
    }

    public function reschedule()
    {
        // Scheduler
        $schedule = $this->getSchedule();

        // Add Schedule for All Events
        $events = $this->main->get_events();
        foreach($events as $event) $schedule->reschedule($event->ID, 50);
    }
    
    /**
     * Update database to version 1.0.3
     * @author Webnus <info@webnus.net>
     */
    public function version103()
    {
        // Get current MEC options
        $current = get_option('mec_options', array());
        if(is_string($current) and trim($current) == '') $current = [];
        
        // Merge new options with previous options
        $current['notifications']['new_event'] = array
        (
            'status'=>'1',
            'subject'=>'A new event is added.',
            'recipients'=>'',
            'content'=>"Hello,

            A new event just added. The event title is %%event_title%% and it's status is %%event_status%%
            The new event may need to be published. Please use this link for managing your website events: %%admin_link%%

            Regards,
            %%blog_name%%"
        );
        
        // Update it only if options already exists.
        if(get_option('mec_options') !== false)
        {
            // Save new options
            update_option('mec_options', $current);
        }
    }
    
    /**
     * Update database to version 1.3.0
     * @author Webnus <info@webnus.net>
     */
    public function version130()
    {
        $this->db->q("ALTER TABLE `#__mec_events` ADD `days` TEXT NULL DEFAULT NULL, ADD `time_start` INT(10) NOT NULL DEFAULT '0', ADD `time_end` INT(10) NOT NULL DEFAULT '0'");
    }
    
    /**
     * Update database to version 1.5.0
     * @author Webnus <info@webnus.net>
     */
    public function version150()
    {
        $this->db->q("ALTER TABLE `#__mec_events` ADD `not_in_days` TEXT NOT NULL DEFAULT '' AFTER `days`");
        $this->db->q("ALTER TABLE `#__mec_events` CHANGE `days` `days` TEXT NOT NULL DEFAULT ''");
    }

    /**
     * Update database to version 2.2.0
     * @author Webnus <info@webnus.net>
     */
    public function version220()
    {
        // Get current MEC options
        $current = get_option('mec_options', array());
        if(is_string($current) and trim($current) == '') $current = [];

        // Merge new options with previous options
        $current['notifications']['booking_reminder'] = array
        (
            'status'=>'0',
            'subject'=>'Booking Reminder',
            'recipients'=>'',
            'days'=>'1,3',
            'content'=>"Hello,

            This email is to remind you that you booked %%event_title%% event on %%book_date%% date.
            We're looking forward to see you at %%event_location_address%%. You can contact %%event_organizer_email%% if you have any questions.

            Regards,
            %%blog_name%%"
        );

        // Update it only if options already exists.
        if(get_option('mec_options') !== false)
        {
            // Save new options
            update_option('mec_options', $current);
        }
    }

    public function version290()
    {
        $this->db->q("UPDATE `#__postmeta` SET `meta_value`=CONCAT(',', `meta_value`) WHERE `meta_key`='mec_ticket_id'");
        $this->db->q("UPDATE `#__postmeta` SET `meta_value`=CONCAT(`meta_value`, ',') WHERE `meta_key`='mec_ticket_id'");
    }

    public function version320()
    {
        $this->db->q("ALTER TABLE `#__mec_events` DROP INDEX `repeat`;");
        $this->db->q("ALTER TABLE `#__mec_events` CHANGE `rinterval` `rinterval` VARCHAR(10);");
        $this->db->q("ALTER TABLE `#__mec_events` CHANGE `year` `year` VARCHAR(80);");
        $this->db->q("ALTER TABLE `#__mec_events` CHANGE `month` `month` VARCHAR(80);");
        $this->db->q("ALTER TABLE `#__mec_events` CHANGE `day` `day` VARCHAR(80);");
        $this->db->q("ALTER TABLE `#__mec_events` CHANGE `week` `week` VARCHAR(80);");
        $this->db->q("ALTER TABLE `#__mec_events` CHANGE `weekday` `weekday` VARCHAR(80);");
        $this->db->q("ALTER TABLE `#__mec_events` CHANGE `weekdays` `weekdays` VARCHAR(80);");
        $this->db->q("ALTER TABLE `#__mec_events` ADD INDEX( `start`, `end`, `repeat`, `rinterval`, `year`, `month`, `day`, `week`, `weekday`, `weekdays`, `time_start`, `time_end`);");
    }

    public function version350()
    {
        $this->db->q("CREATE TABLE IF NOT EXISTS `#__mec_dates` (
          `id` int(10) UNSIGNED NOT NULL,
          `post_id` int(10) NOT NULL,
          `dstart` date NOT NULL,
          `dend` date NOT NULL,
          `type` enum('include','exclude') COLLATE [:COLLATE:] NOT NULL DEFAULT 'include'
        ) DEFAULT CHARSET=[:CHARSET:] COLLATE=[:COLLATE:];");

        $this->db->q("ALTER TABLE `#__mec_dates` ADD PRIMARY KEY (`id`), ADD KEY `post_id` (`post_id`), ADD KEY `type` (`type`);");
        $this->db->q("ALTER TABLE `#__mec_dates` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;");

        $custom_days = $this->db->select("SELECT * FROM `#__mec_events` WHERE `days`!=''", 'loadAssocList');
        foreach($custom_days as $custom_day)
        {
            $days = explode(',', trim($custom_day['days'], ', '));

            $new_days_str = '';
            foreach($days as $day)
            {
                if(!trim($day)) continue;

                $start = $day;
                $end = $day;

                $this->db->q("INSERT INTO `#__mec_dates` (`post_id`,`dstart`,`dend`,`type`) VALUES ('".$custom_day['post_id']."','$start','$end','include')");

                $new_days_str .= $start.':'.$end.',';
            }

            $new_days_str = trim($new_days_str, ', ');

            $this->db->q("UPDATE `#__mec_events` SET `days`='".$new_days_str."' WHERE `post_id`='".$custom_day['post_id']."'");
            update_post_meta($custom_day['post_id'], 'mec_in_days', $new_days_str);
        }
    }

    public function version400()
    {
        // Add Columns
        $this->db->q("ALTER TABLE `#__mec_dates` ADD `tstart` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `dend`;");
        $this->db->q("ALTER TABLE `#__mec_dates` ADD `tend` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `tstart`;");

        // Add Indexes
        $this->db->q("ALTER TABLE `#__mec_dates` ADD INDEX (`tstart`);");
        $this->db->q("ALTER TABLE `#__mec_dates` ADD INDEX (`tend`);");

        // Drop Columns
        $this->db->q("ALTER TABLE `#__mec_dates` DROP COLUMN `type`;");

        // Reschedule
        $this->reschedule();

        // Scheduler Cron job
        if(!wp_next_scheduled('mec_scheduler')) wp_schedule_event(time(), 'hourly', 'mec_scheduler');
    }

    public function version430()
    {
        // Get current MEC options
        $current = get_option('mec_options', array());
        if(is_string($current) and trim($current) == '') $current = [];

        // Merge new options with previous options
        $current['notifications']['cancellation_notification'] = array
        (
            'status'=>'0',
            'subject'=>'Your booking is canceled.',
            'recipients'=>'',
            'send_to_admin'=>'1',
            'send_to_organizer'=>'0',
            'send_to_user'=>'0',
            'content'=>"Hi %%name%%,

            For your information, your booking for %%event_title%% at %%book_date%% is canceled.

            Regards,
            %%blog_name%%"
        );

        // Update it only if options already exists.
        if(get_option('mec_options') !== false)
        {
            // Save new options
            update_option('mec_options', $current);
        }
    }

    public function version446()
    {
        if(!wp_next_scheduled('mec_syncScheduler')) wp_schedule_event(time(), 'daily', 'mec_syncScheduler');
    }

    public function version461()
    {
        // Get current MEC options
        $current = get_option('mec_options', array());
        if(is_string($current) and trim($current) == '') $current = [];

        // Merge new options with previous options
        $current['notifications']['user_event_publishing'] = array
        (
            'status'=>'0',
            'subject'=>'Your event gets published!',
            'recipients'=>'',
            'content'=>"Hello %%name%%,

            Your event gets published. You can check it below:

            <a href=\"%%event_link%%\">%%event_title%%</a>

            Regards,
            %%blog_name%%"
        );

        // Update it only if options already exists.
        if(get_option('mec_options') !== false)
        {
            // Save new options
            update_option('mec_options', $current);
        }
    }

    public function version490()
    {
        // Get Booking Posts
        $bookings = get_posts(array(
            'post_type'  => $this->main->get_book_post_type(),
            'numberposts'  => '-1',
            'post_status'  => 'any',
        ));

        foreach($bookings as $id => $booking)
        {
            $event_id = get_post_meta($booking->ID, 'mec_event_id', true);
            $location_id = get_post_meta($event_id, 'mec_location_id', true);

            if(!empty($location_id)) update_post_meta($booking->ID, 'mec_booking_location', $location_id);
        }
    }

    public function version505()
    {
        if(!wp_next_scheduled('mec_syncScheduler')) wp_schedule_event(time(), 'daily', 'mec_syncScheduler');
    }

    public function version551()
    {
        // Get Booking Posts
        $bookings = get_posts(array(
            'post_type'  => $this->main->get_book_post_type(),
            'numberposts'  => '-1',
            'post_status'  => 'any',
        ));

        foreach($bookings as $id => $booking)
        {
            $event_id = get_post_meta($booking->ID, 'mec_event_id', true);

            $start_time_int = (int) get_post_meta($event_id, 'mec_start_day_seconds', true);
            $end_time_int = (int) get_post_meta($event_id, 'mec_end_day_seconds', true);

            $start_time = gmdate('H:i:s', $start_time_int);
            $end_time = gmdate('H:i:s', $end_time_int);

            $mec_date = get_post_meta($booking->ID, 'mec_date', true);
            if(is_array($mec_date) and isset($mec_date['start']) and isset($mec_date['start']['date'])) $mec_date = $mec_date['start']['date'].':'.$mec_date['end']['date'];

            list($start_date, $end_date) = explode(':', $mec_date);
            if(is_numeric($start_date) or is_numeric($end_date)) continue;

            $start_datetime = $start_date.' '.$start_time;
            $end_datetime = $end_date.' '.$end_time;

            // Update MEC Date
            update_post_meta($booking->ID, 'mec_date', strtotime($start_datetime).':'.strtotime($end_datetime));

            $post_date = date('Y-m-d H:i:s', strtotime($start_datetime));
            $gmt_date = get_gmt_from_date($post_date);

            // Update Booking Date
            wp_update_post(array(
                'ID' => $booking->ID,
                'post_date' => $post_date,
                'post_date_gmt' => $gmt_date,
            ));
        }
    }

    public function version571()
    {
        // Get current MEC options
        $current = get_option('mec_options', array());
        if(is_string($current) and trim($current) == '') $current = [];

        if(!isset($current['notifications']['booking_reminder'])) return;
        if(isset($current['notifications']['booking_reminder']['hours'])) return;

        // Change Days to Hours
        $days = explode(',', trim($current['notifications']['booking_reminder']['days'], ', '));

        $hours = '';
        foreach($days as $day)
        {
            $hours .= ($day * 24).',';
        }

        $current['notifications']['booking_reminder']['hours'] = trim($hours, ', ');
        unset($current['notifications']['booking_reminder']['days']);

        // Update it only if options already exists.
        if(get_option('mec_options') !== false)
        {
            // Save new options
            update_option('mec_options', $current);
        }
    }

    public function version5100()
    {
        $this->db->q("CREATE TABLE IF NOT EXISTS `#__mec_occurrences` (
          `id` int(10) UNSIGNED NOT NULL,
          `post_id` int(10) UNSIGNED NOT NULL,
          `occurrence` int(10) UNSIGNED NOT NULL,
          `params` text COLLATE [:COLLATE:]
        ) DEFAULT CHARSET=[:CHARSET:] COLLATE=[:COLLATE:];");

        $this->db->q("ALTER TABLE `#__mec_occurrences` ADD PRIMARY KEY (`id`), ADD KEY `post_id` (`post_id`), ADD KEY `occurrence` (`occurrence`);");
        $this->db->q("ALTER TABLE `#__mec_occurrences` MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;");
    }

    public function version5110()
    {
        $this->db->q("CREATE TABLE IF NOT EXISTS `#__mec_users` (
          `id` int(10) NOT NULL,
          `first_name` varchar(255) NOT NULL,
          `last_name` varchar(255) NOT NULL,
          `email` varchar(127) NOT NULL,
          `reg` TEXT NULL DEFAULT NULL,
          `created_at` datetime DEFAULT NULL,
          `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) DEFAULT CHARSET=[:CHARSET:] COLLATE=[:COLLATE:];");

        $this->db->q("ALTER TABLE `#__mec_users` ADD PRIMARY KEY (`id`);");
        $this->db->q("ALTER TABLE `#__mec_users` MODIFY `id` int NOT NULL AUTO_INCREMENT;");
        $this->db->q("ALTER TABLE `#__mec_users` AUTO_INCREMENT=1000000;");
        $this->db->q("ALTER TABLE `#__mec_users` ADD UNIQUE KEY `email` (`email`);");
    }

    public function version5126()
    {
        $all = $this->db->select("SELECT * FROM `#__mec_users`", 'loadAssocList');
        $zeros = $this->db->select("SELECT * FROM `#__mec_users` WHERE `id`='0'", 'loadAssocList');

        if(is_array($all) and !count($all))
        {
            $this->db->q("DROP TABLE `#__mec_users`");
            $this->version5110();
        }
        elseif(is_array($zeros) and count($zeros))
        {
            $this->db->q("TRUNCATE `#__mec_users`");
            $this->db->q("ALTER TABLE `#__mec_users` CHANGE `email` `email` VARCHAR(127) NOT NULL;");
            $this->db->q("ALTER TABLE `#__mec_users` ADD PRIMARY KEY (`id`);");
            $this->db->q("ALTER TABLE `#__mec_users` MODIFY `id` int NOT NULL AUTO_INCREMENT;");
            $this->db->q("ALTER TABLE `#__mec_users` AUTO_INCREMENT=1000000;");
            $this->db->q("ALTER TABLE `#__mec_users` ADD UNIQUE KEY `email` (`email`);");
        }
        else
        {
            $this->db->q("ALTER TABLE `#__mec_users` CHANGE `email` `email` VARCHAR(127) NOT NULL;");
        }
    }

    public function version5135()
    {
        // Get current MEC options
        $current = get_option('mec_options', array());
        if(is_string($current) and trim($current) == '') $current = [];

        // Merge new options with previous options
        $current['notifications']['booking_rejection'] = array
        (
            'status'=>'0',
            'subject'=>'Your booking got rejected!',
            'recipients'=>'',
            'send_to_admin'=>'0',
            'send_to_organizer'=>'1',
            'send_to_user'=>'1',
            'content'=>"Hi %%name%%,

            For your information, your booking for %%event_title%% at %%book_datetime%% is rejected.

            Regards,
            %%blog_name%%"
        );

        $current['notifications']['event_soldout'] = array
        (
            'status'=>'0',
            'subject'=>'Your event is soldout!',
            'recipients'=>'',
            'send_to_admin'=>'1',
            'send_to_organizer'=>'1',
            'content'=>"Hi %%name%%,

            For your information, your %%event_title%% event at %%book_datetime%% is soldout.

            Regards,
            %%blog_name%%"
        );

        // Update it only if options already exists.
        if(get_option('mec_options') !== false)
        {
            // Save new options
            update_option('mec_options', $current);
        }
    }

    public function version5140()
    {
        // List of Capabilities
        $capabilities = array('mec_bookings', 'mec_add_booking', 'mec_coupons', 'mec_report', 'mec_import_export', 'mec_settings');

        // Update Capabilities
        $this->update_capabilities($capabilities);
    }

    public function version5160()
    {
        $mec = $this->db->select("SELECT * FROM `#__mec_users`", 'loadAssocList');
        if(is_array($mec) and !count($mec))
        {
            $this->db->q("DROP TABLE `#__mec_users`");
            $this->version5110();
        }

        // Add Public Column
        $this->db->q("ALTER TABLE `#__mec_dates` ADD `public` INT(4) UNSIGNED NOT NULL DEFAULT 1 AFTER `tend`;");
    }

    public function version5161()
    {
        // Add Public Column If Not Exists
        if(!$this->db->columns('mec_dates', 'public'))
        {
            $this->db->q("ALTER TABLE `#__mec_dates` ADD `public` INT(4) UNSIGNED NOT NULL DEFAULT 1 AFTER `tend`;");
        }
    }

    public function version5162()
    {
        $this->version5161();
    }

    public function version5170()
    {
        // List of Capabilities
        $capabilities = array('mec_shortcodes', 'mec_settings');

        // Update Capabilities
        $this->update_capabilities($capabilities);
    }

    public function version5171()
    {
        $this->version5170();
        $this->reschedule();
    }

    public function version5191()
    {
        $this->version5170();
    }

    public function version5220()
    {
        // All Events
        $events = $this->main->get_events();

        foreach($events as $event)
        {
            $start_time_hour = get_post_meta($event->ID, 'mec_start_time_hour', true);
            $start_time_minutes = get_post_meta($event->ID, 'mec_start_time_minutes', true);
            $start_time_ampm = get_post_meta($event->ID, 'mec_start_time_ampm', true);
            $end_time_hour = get_post_meta($event->ID, 'mec_end_time_hour', true);
            $end_time_minutes = get_post_meta($event->ID, 'mec_end_time_minutes', true);
            $end_time_ampm = get_post_meta($event->ID, 'mec_end_time_ampm', true);

            $day_start_seconds = $this->main->time_to_seconds($this->main->to_24hours($start_time_hour, $start_time_ampm), $start_time_minutes);
            $day_end_seconds = $this->main->time_to_seconds($this->main->to_24hours($end_time_hour, $end_time_ampm), $end_time_minutes);

            update_post_meta($event->ID, 'mec_start_day_seconds', $day_start_seconds);
            update_post_meta($event->ID, 'mec_end_day_seconds', $day_end_seconds);
        }
    }

    public function version600()
    {
        $this->db->q("CREATE TABLE IF NOT EXISTS `#__mec_bookings` (
          `id` int UNSIGNED NOT NULL,
          `booking_id` int UNSIGNED NOT NULL,
          `event_id` int UNSIGNED NOT NULL,
          `ticket_ids` varchar(255) NOT NULL,
          `status` varchar(20) NOT NULL DEFAULT 'pending',
          `confirmed` tinyint NOT NULL DEFAULT '0',
          `verified` tinyint NOT NULL DEFAULT '0',
          `all_occurrences` tinyint NOT NULL DEFAULT '0',
          `date` datetime NOT NULL,
          `timestamp` int UNSIGNED NOT NULL
        ) DEFAULT CHARSET=[:CHARSET:] COLLATE=[:COLLATE:];");

        $this->db->q("ALTER TABLE `#__mec_bookings` ADD PRIMARY KEY (`id`);");
        $this->db->q("ALTER TABLE `#__mec_bookings` MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;");
        $this->db->q("ALTER TABLE `#__mec_bookings` ADD KEY `event_id` (`event_id`,`ticket_ids`,`status`,`confirmed`,`verified`,`date`);");
        $this->db->q("ALTER TABLE `#__mec_bookings` ADD KEY `booking_id` (`booking_id`);");
        $this->db->q("ALTER TABLE `#__mec_bookings` ADD KEY `timestamp` (`timestamp`);");
        $this->db->q("ALTER TABLE `#__mec_bookings` ADD `transaction_id` VARCHAR(20) NULL AFTER `booking_id`;");

        $this->db->q("ALTER TABLE `#__mec_bookings` ADD `user_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `booking_id`;");
        $this->db->q("ALTER TABLE `#__mec_bookings` ADD INDEX (`user_id`);");

        // Get Booking Posts
        $bookings = get_posts(array(
            'post_type' => $this->main->get_book_post_type(),
            'numberposts' => '-1',
            'post_status' => 'any',
        ));

        // Booking Record
        $bookingRecord = $this->getBookingRecord();

        // Add Records for Existing Bookings
        foreach($bookings as $booking) $bookingRecord->insert($booking);
    }

    public function version626()
    {
        if(!$this->getPRO()) return;

        // Get Options
        $options = get_option('mec_options');
        $code = $options['purchase_code'] ?? '';
        $item_id = $options['product_id'] ?? '';
        $url = get_home_url();

        $reActivationOption = get_option('reActivationOption');
        if(empty($code) || $reActivationOption) return;
        
        if(!$reActivationOption)
        {
            $verify_url = MEC_API_ACTIVATION . '/activation/verify?category=mec&license=' . $code . '&url=' . $url . '&item_id=' . $item_id;
            $JSON = wp_remote_retrieve_body(wp_remote_get($verify_url, array(
                'body' => null,
                'timeout' => '120',
                'redirection' => '10',
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36',
            )));

            if($JSON != '')
            {
                $data = json_decode($JSON);
                if($data and isset($data->item_link))
                {
                    $options['product_id'] = $data->item_id;

                    update_option('mec_license_status', 'active');
                    update_option('mec_options', $options);
                    update_option('reActivationOption', '1');
                }
                else
                {
                    update_option('mec_license_status', 'faild');
                    update_option('reActivationOption', '1');
                }
            }
            else update_option('reActivationOption', '1');
        }
    }

    public function version640()
    {
        $this->db->q("ALTER TABLE `#__mec_dates` ADD `status` VARCHAR(20) NOT NULL DEFAULT 'publish' AFTER `tend`;");
    }

    public function version653()
    {
        $this->db->q("ALTER TABLE `#__mec_bookings` CHANGE `ticket_ids` `ticket_ids` VARCHAR(655) NOT NULL;");
    }

    public function version6611()
    {
        // Add Seats Columns If Not Exists
        if(!$this->db->columns('mec_bookings', 'seats'))
        {
            $this->db->q("ALTER TABLE `#__mec_bookings` ADD `seats` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `ticket_ids`;");

            // Booking Records
            $bookings = $this->db->select("SELECT `id`, `ticket_ids` FROM `#__mec_bookings` ORDER BY `date` DESC LIMIT 2000", 'loadObjectList');
            foreach($bookings as $booking)
            {
                $ticket_ids = trim($booking->ticket_ids, ', ');
                $seats = substr_count($ticket_ids, ',') + 1;

                $this->db->q("UPDATE `#__mec_bookings` SET `seats`='".esc_sql($seats)."' WHERE `id`=".esc_sql($booking->id));
            }
        }
    }

    public function version675()
    {
        $bookings = get_posts([
            'post_type' => $this->main->get_book_post_type(),
            'numberposts' => 300,
            'post_status' => 'publish'
        ]);

        // MEC Booking
        $book = $this->getBook();

        foreach($bookings as $booking)
        {
            $price = get_post_meta($booking->ID, 'mec_price', true);

            // Payable Data Exists
            $existing_payable = get_post_meta($booking->ID, 'mec_payable', true);
            if($existing_payable) continue;

            update_post_meta($booking->ID, 'mec_payable', $price);

            $transaction_id = get_post_meta($booking->ID, 'mec_transaction_id', true);
            $transaction = $book->get_transaction($transaction_id);

            if(isset($transaction['price']))
            {
                $transaction['price_details']['payable'] = $transaction['price'];
                $transaction['payable'] = $transaction['price'];

                $book->update_transaction($transaction_id, $transaction);
            }
        }
    }

    public function version6835()
    {
        if(!wp_next_scheduled('mec_maintenance')) wp_schedule_event(time(), 'daily', 'mec_maintenance');
    }

    public function version700()
    {
        // Table already exists
        if($this->db->exists('mec_booking_attendees')) return;

        $this->db->q("CREATE TABLE IF NOT EXISTS `#__mec_booking_attendees` (
          `id` int UNSIGNED NOT NULL,
          `mec_booking_id` int UNSIGNED NOT NULL,
          `user_id` int UNSIGNED NOT NULL,
          `ticket_id` int UNSIGNED NOT NULL
        ) CHARSET=[:CHARSET:] COLLATE=[:COLLATE:];");

        $this->db->q("ALTER TABLE `#__mec_booking_attendees` ADD PRIMARY KEY (`id`);");
        $this->db->q("ALTER TABLE `#__mec_booking_attendees` ADD KEY `mec_booking_id` (`mec_booking_id`);");
        $this->db->q("ALTER TABLE `#__mec_booking_attendees` MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;");
        $this->db->q("ALTER TABLE `#__mec_booking_attendees` ADD CONSTRAINT `mec_booking_id` FOREIGN KEY (`mec_booking_id`) REFERENCES `#__mec_bookings`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
    }

    public function version720()
    {
        // Get current MEC options
        $current = get_option('mec_options', array());
        if(is_string($current) and trim($current) == '') $current = [];

        // Merge new options with previous options
        $current['notifications']['certificate_send'] = array
        (
            'status'=>'1',
            'subject'=>'Event Attendee Certificates',
            'recipients'=>'',
            'content'=>"Hi %%name%%,

            Congratulations for successfully attending / completing the event %%event_title%%.
            Click the following link to download or print your PDF certificate.
            
            %%certificate_link%%

            Regards,
            %%blog_name%%"
        );

        // Update it only if options already exists.
        if(get_option('mec_options') !== false)
        {
            // Save new options
            update_option('mec_options', $current);
        }
    }

    public function version740()
    {
        $settings = $this->main->get_settings();

        // Speaker Status
        if(isset($settings['speakers_status']) && $settings['speakers_status'])
        {
            $speakers = get_terms('mec_speaker', [
                'orderby' => 'name',
                'order' => 'ASC',
                'hide_empty' => '0',
            ]);

            $i = 1;
            foreach($speakers as $speaker)
            {
                update_term_meta($speaker->term_id, 'mec_index', $i);
            }
        }
    }

    public function version7110()
    {
        // Get current MEC options
        $current = get_option('mec_options', array());
        if(is_string($current) && trim($current) == '') $current = [];

        // Booking Moved Notification
        $current['notifications']['booking_moved'] = array
        (
            'status'=>'0',
            'subject'=>'Your booking has been rescheduled',
            'recipients'=>'',
            'content'=>"Hi %%name%%,

            We are writing to inform you that your booking for %%event_title%%, %%book_datetime_prev%% has been moved to a new date. Please find the updated booking details below:

            New Booking Details:
            
            Event/Service Name: %%event_title%%
            New Date: %%book_date%%
            New Time: %%book_time%%
            
            We apologize for any inconvenience this may cause and appreciate your understanding. If the new date and time do not work for you, you can cancel the booking using following link:
            
            %%cancellation_link%%

            Regards,
            %%blog_name%%"
        );

        // Suggest Event Notification
        $current['notifications']['suggest_event'] = array
        (
            'subject'=>"Discover more events you'll love!",
            'recipients'=>'',
            'content'=>"Hi %%name%%,

            We thought you'd be interested in the following event coming up that we think you'll love.
            
            %%event_title%%
            %%event_description%%
            
            You can find more details and book your tickets on our website: %%event_link%% 

            Regards,
            %%blog_name%%"
        );

        // Update it only if options already exists.
        if(get_option('mec_options') !== false)
        {
            // Save new options
            update_option('mec_options', $current);
        }
    }

    public function version7130()
    {
        // Get current MEC options
        $current = get_option('mec_options', array());
        if(is_string($current) && trim($current) == '') $current = [];

        $settings = $current['settings'] ?? [];

        $trash_interval = isset($settings['events_trash_interval']) ? (int) $settings['events_trash_interval'] : 0;
        $purge_interval = isset($settings['events_purge_interval']) ? (int) $settings['events_purge_interval'] : 0;

        if($trash_interval && $trash_interval <= 36) $trash_interval = $trash_interval * 30;
        if($purge_interval && $purge_interval <= 36) $purge_interval = $purge_interval * 30;

        $current['settings']['events_trash_interval'] = $trash_interval;
        $current['settings']['events_purge_interval'] = $purge_interval;

        // Update it only if options already exists.
        if(get_option('mec_options') !== false)
        {
            // Save new options
            update_option('mec_options', $current);
        }
    }
}
