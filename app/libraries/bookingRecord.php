<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Booking Record class.
 * @author Webnus <info@webnus.net>
 */
class MEC_bookingRecord extends MEC_base
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
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // Import MEC Main
        $this->main = $this->getMain();

        // Import MEC DB
        $this->db = $this->getDB();
    }

    /**
     * @param WP_Post|integer $booking
     * @return array
     */
    public function insert($booking)
    {
        // Get Booking by ID
        if (is_numeric($booking)) $booking = get_post($booking);

        if (!$booking || !is_a($booking, '\WP_Post')) return [];

        // User Library
        $u = $this->getUser();

        // Get Main User
        $user = $u->booking($booking->ID);

        $user_id = $user ? $user->ID : 0;
        $verified = get_post_meta($booking->ID, 'mec_verified', true);
        $confirmed = get_post_meta($booking->ID, 'mec_confirmed', true);
        $event_id = get_post_meta($booking->ID, 'mec_event_id', true);
        $ticket_ids = get_post_meta($booking->ID, 'mec_ticket_id', true);
        $transaction_id = get_post_meta($booking->ID, 'mec_transaction_id', true);

        $event_tickets = get_post_meta($event_id, 'mec_tickets', true);
        if (!is_array($event_tickets)) $event_tickets = [];

        $seats = 0;
        $booked_ticket_ids = is_string($ticket_ids) ? explode(',', trim($ticket_ids, ', ')) : [];
        if(is_array($booked_ticket_ids)) {
            foreach ($booked_ticket_ids as $booked_ticket_id)
            {
                $booked_ticket_id = (int) trim($booked_ticket_id);
                $data = (isset($event_tickets[$booked_ticket_id]) and is_array($event_tickets[$booked_ticket_id])) ? $event_tickets[$booked_ticket_id] : [];

                $ticket_seats = (isset($data['seats']) and is_numeric($data['seats'])) ? (int) $data['seats'] : 1;
                $seats += $ticket_seats;
            }
        }

        $booking_options = get_post_meta($event_id, 'mec_booking', true);
        $all_occurrences = $booking_options['bookings_all_occurrences'] ?? 0;

        $attendees = get_post_meta($booking->ID, 'mec_attendees', true);

        $all_dates = get_post_meta($booking->ID, 'mec_all_dates', true);
        $timestamps = [];

        // Multiple Dates
        if ($all_dates and is_array($all_dates) and count($all_dates)) $timestamps = $all_dates;
        // Single Date
        else $timestamps[] = get_post_meta($booking->ID, 'mec_date', true);

        $ids = [];
        foreach ($timestamps as $timestamp)
        {
            $timestamp = is_array($timestamp) ? '' : explode(':', $timestamp)[0];
            if ($timestamp && !is_numeric($timestamp)) $timestamp = strtotime($timestamp);

            if (!trim($timestamp)) continue;

            // Exists?
            $exists = $this->db->select("SELECT `id` FROM `#__mec_bookings` WHERE `transaction_id`='" . esc_sql($transaction_id) . "' AND `timestamp`='" . esc_sql($timestamp) . "'", 'loadResult');
            if ($exists) continue;

            // Insert
            $query = "INSERT INTO `#__mec_bookings` (`booking_id`,`user_id`,`transaction_id`,`event_id`,`ticket_ids`,`seats`,`status`,`confirmed`,`verified`,`all_occurrences`,`date`,`timestamp`) VALUES ('" . esc_sql($booking->ID) . "','" . esc_sql($user_id) . "','" . esc_sql($transaction_id) . "','" . esc_sql($event_id) . "','" . esc_sql($ticket_ids) . "','" . esc_sql($seats) . "','" . $booking->post_status . "','" . esc_sql($confirmed) . "','" . esc_sql($verified) . "','" . esc_sql($all_occurrences) . "','" . date('Y-n-d H:i:s', $timestamp) . "','" . esc_sql($timestamp) . "');";
            $id = $this->db->q($query, 'INSERT');

            foreach ($attendees as $k => $attendee)
            {
                // No Attendee
                if (!is_numeric($k)) continue;
                if (!isset($attendee['id'])) continue;

                // Ticket ID
                $ticket_id = $attendee['id'];

                // Register attendee in MEC
                $attendee_id = $u->register($attendee, [
                    'register_in_mec' => true,
                ]);

                // Insert Booking Attendees
                $query = "INSERT INTO `#__mec_booking_attendees` (`mec_booking_id`,`user_id`,`ticket_id`) VALUES ('" . esc_sql($id) . "','" . esc_sql($attendee_id) . "','" . esc_sql($ticket_id) . "');";
                $this->db->q($query, 'INSERT');
            }

            $ids[] = $id;
        }

        return $ids;
    }

    /**
     * @param WP_Post|integer $booking
     * @return array
     */
    public function update($booking)
    {
        // Delete
        $this->delete($booking);

        return $this->insert($booking);
    }

    /**
     * @param WP_Post|integer $booking
     */
    public function delete($booking)
    {
        // Get Booking by ID
        if (is_numeric($booking)) $booking = get_post($booking);

        $this->db->q("DELETE FROM `#__mec_bookings` WHERE `booking_id`='" . $booking->ID . "'");
    }

    public function confirm($booking)
    {
        return $this->set($booking, ['confirmed' => 1]);
    }

    public function reject($booking)
    {
        return $this->set($booking, ['confirmed' => -1]);
    }

    public function pending($booking)
    {
        return $this->set($booking, ['confirmed' => 0]);
    }

    public function verify($booking)
    {
        return $this->set($booking, ['verified' => 1]);
    }

    public function cancel($booking)
    {
        return $this->set($booking, ['verified' => -1]);
    }

    public function waiting($booking)
    {
        return $this->set($booking, ['verified' => 0]);
    }

    public function set($booking, $values)
    {
        // Get Booking by ID
        if (is_numeric($booking)) $booking = get_post($booking);

        // Invalid Booking
        if (!$booking || !is_a($booking, '\WP_Post')) return [];

        $q = "";
        foreach ($values as $key => $value) $q .= "`" . esc_attr($key) . "`='" . esc_sql($value) . "',";

        // Nothing to Update!
        if (trim($q) == '') return false;

        return $this->db->q("UPDATE `#__mec_bookings` SET " . trim($q, ', ') . " WHERE `booking_id`='" . esc_sql($booking->ID) . "'");
    }
}
