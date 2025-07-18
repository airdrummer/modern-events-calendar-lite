<?php
/** no direct access **/

use MEC\Transactions\Transaction;

defined('MECEXEC') or die();

/**
 * Webnus MEC book class.
 * @author Webnus <info@webnus.net>
 */
class MEC_book extends MEC_base
{
    /**
     * @var array
     */
    public $settings;

    /**
     * @var MEC_main
     */
    public $main;

    /**
     * Post Type Slug
     * @var string
     */
    public $PT;

    /**
     * @var MEC_partial
     */
    public $partial_payment;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // Import MEC Main
        $this->main = $this->getMain();

        // MEC Book Post Type Name
        $this->PT = $this->main->get_book_post_type();

        // MEC Settings
        $this->settings = $this->main->get_settings();

        // MEC Partial Payment
        $this->partial_payment = $this->getPartialPayment();
    }

    /**
     * Get invoice (Ticket price + Fees) based on tickets
     * @param array $tickets
     * @param int $event_id
     * @param array $event_tickets
     * @param array $variations
     * @param array $timestamps
     * @param boolean $apply_fees
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_price_details($tickets, $event_id, $event_tickets, $variations = [], $timestamps = [], $apply_fees = true)
    {
        $total_tickets_amount = 0;
        $total_tickets_count = 0;
        $total_variations_amount = 0;
        $total_fee_amount = 0;

        $variation_details = [];
        $fee_details = [];

        $details = [];
        foreach ($timestamps as $timestamp)
        {
            $date_tickets_amount = 0;
            $date_tickets_count = 0;
            $date_variations_amount = 0;
            $date_fee_amount = 0;

            $timestamp_ex = explode(':', $timestamp);
            $timestamp = $timestamp_ex[0];

            foreach ($tickets as $ticket_id => $count)
            {
                if (!$count) continue;
                if (!isset($event_tickets[$ticket_id])) continue;

                $date_tickets_count += $count;

                $t_price = isset($event_tickets[$ticket_id]['price']) ? $this->get_ticket_price($event_tickets[$ticket_id], current_time('Y-m-d'), $event_id, $timestamp) : 0;
                if (!is_numeric($t_price)) $t_price = 0;

                $date_tickets_amount = $date_tickets_amount + ($t_price * $count);

                // Variations module is enabled and some variations bought
                if (isset($this->settings['ticket_variations_status']) and $this->settings['ticket_variations_status'] and is_array($variations) and count($variations))
                {
                    $ticket_variations = $this->main->ticket_variations($event_id, $ticket_id);

                    foreach ($ticket_variations as $key => $ticket_variation)
                    {
                        if (!is_numeric($key)) continue;
                        if (!isset($ticket_variation['title']) or !trim($ticket_variation['title'])) continue;

                        $booked_variations = isset($variations[$ticket_id]) && is_array($variations[$ticket_id]) ? $variations[$ticket_id] : [];

                        $variation_count = $booked_variations[$key] ?? 0;
                        if (!$variation_count or $variation_count < 0) continue;

                        $v_price = (isset($ticket_variation['price']) and trim($ticket_variation['price']) != '') ? $ticket_variation['price'] : 0;

                        $variation_amount = $v_price * $variation_count;
                        $variation_title = $ticket_variation['title'] . ' (' . esc_html($variation_count) . ')';

                        // Add To Total
                        $date_variations_amount += $variation_amount;

                        // Price Details
                        if (!isset($variation_details[$key])) $variation_details[$key] = ['amount' => $variation_amount, 'description' => __($variation_title, 'modern-events-calendar-lite'), 'type' => 'variation', 'count' => $variation_count];
                        else
                        {
                            $variation_details[$key]['amount'] += $variation_amount;

                            $new_count = ((int) $variation_details[$key]['count'] + $variation_count);
                            $variation_details[$key]['count'] = $new_count;
                            $variation_details[$key]['description'] = esc_html__($ticket_variation['title'] . ' (' . $new_count . ')', 'modern-events-calendar-lite');
                        }
                    }
                }
            }

            $total_tickets_amount += $date_tickets_amount;
            $total_variations_amount += $date_variations_amount;
            $total_tickets_count += $date_tickets_count;

            // Fees module is enabled
            if ($apply_fees and isset($this->settings['taxes_fees_status']) and $this->settings['taxes_fees_status'])
            {
                $fees = $this->get_fees($event_id);

                foreach ($fees as $key => $fee)
                {
                    $fee_amount = 0;
                    if (!is_numeric($key)) continue;

                    if ($fee['type'] == 'amount_per_date') $fee_amount += $fee['amount'];
                    else continue;

                    // Add to Total
                    $date_fee_amount += $fee_amount;

                    // Price Details
                    if (!isset($fee_details[$key])) $fee_details[$key] = ['amount' => $fee_amount, 'description' => __($fee['title'], 'modern-events-calendar-lite'), 'type' => 'fee', 'fee_type' => $fee['type'], 'fee_amount' => $fee['amount']];
                    else $fee_details[$key]['amount'] += $fee_amount;
                }
            }

            $total_fee_amount += $date_fee_amount;
        }

        $tax_inclusion_type = isset($this->settings['tax_inclusion']) && trim($this->settings['tax_inclusion']) ? $this->settings['tax_inclusion'] : 'excluded';
        $total_ticket_percent_fee_amount = 0;

        // Fees module is enabled
        if ($apply_fees && isset($this->settings['taxes_fees_status']) && $this->settings['taxes_fees_status'])
        {
            $fees = $this->get_fees($event_id);
            $rest_fee_amount = 0;

            // Fee Per Booking
            foreach ($fees as $key => $fee)
            {
                if (!is_numeric($key)) continue;

                $fee_amount_config = isset($fee['amount']) ? (float) $fee['amount'] : 0;
                $fee_amount = 0;

                if ($fee['type'] == 'percent')
                {
                    if ($tax_inclusion_type === 'included')
                    {
                        $fee_amount += ($total_tickets_amount + $total_variations_amount) - (($total_tickets_amount + $total_variations_amount) / (1 + ($fee_amount_config / 100)));

                        // Add to Ticket Percent Fee
                        $total_ticket_percent_fee_amount += $total_tickets_amount - ($total_tickets_amount / (1 + ($fee_amount_config / 100)));

                        // Variation Details
                        if (count($variation_details))
                        {
                            $new_variation_details = [];
                            foreach ($variation_details as $variation_detail)
                            {
                                $variation_fee_amount = $variation_detail['amount'] - ($variation_detail['amount'] / (1 + ($fee_amount_config / 100)));

                                $variation_detail['amount'] = $variation_detail['amount'] - $variation_fee_amount;
                                $total_variations_amount = $total_variations_amount - $variation_fee_amount;

                                $new_variation_details[] = $variation_detail;
                            }

                            $variation_details = $new_variation_details;
                        }
                    }
                    else $fee_amount += (($total_tickets_amount + $total_variations_amount) * $fee_amount_config) / 100;
                }
                else if ($fee['type'] == 'amount')
                {
                    $fee_amount += ($total_tickets_count * $fee_amount_config);
                    if ($tax_inclusion_type === 'included')
                    {
                        $total_ticket_percent_fee_amount += $fee_amount;
                    }
                }
                else if ($fee['type'] == 'amount_per_booking')
                {
                    $fee_amount += $fee_amount_config;
                    if ($tax_inclusion_type === 'included')
                    {
                        $total_ticket_percent_fee_amount += $fee_amount;
                    }
                }
                else continue;

                // Add to Total
                $rest_fee_amount += $fee_amount;

                // Price Details
                if (!isset($fee_details[$key])) $fee_details[$key] = ['amount' => $fee_amount, 'description' => __($fee['title'], 'modern-events-calendar-lite'), 'type' => 'fee', 'fee_type' => $fee['type'], 'fee_amount' => $fee_amount_config];
                else $fee_details[$key]['amount'] += $fee_amount;
            }

            $total_fee_amount += $rest_fee_amount;
        }

        if ($tax_inclusion_type === 'included')
        {
            $total_tickets_amount = $total_tickets_amount - $total_ticket_percent_fee_amount;
        }

        // Ticket Details
        $details[] = ['amount' => $total_tickets_amount, 'description' => esc_html__('Subtotal', 'modern-events-calendar-lite'), 'type' => 'tickets'];

        // Variation Details
        foreach ($variation_details as $variation_detail) $details[] = $variation_detail;

        // Fee Details
        foreach ($fee_details as $fee_detail) $details[] = $fee_detail;

        $total = $total_tickets_amount + $total_fee_amount + $total_variations_amount;
        $payable = $total;

        // Calculate Payable
        if ($this->partial_payment->is_enabled() && !$this->partial_payment->is_flexible_enabled()) $payable = $this->partial_payment->calculate($total, $event_id);

        return [
            'total' => $total,
            'payable' => $payable,
            'details' => $details,
        ];
    }

    /**
     * Get fees of a certain event
     * @param int $event_id
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_fees($event_id)
    {
        $fees_global_inheritance = get_post_meta($event_id, 'mec_fees_global_inheritance', true);
        if (is_string($fees_global_inheritance) and trim($fees_global_inheritance) == '') $fees_global_inheritance = 1;

        // Event fees
        $fees = get_post_meta($event_id, 'mec_fees', true);

        // Get fees from global options
        if ($fees_global_inheritance) $fees = $this->settings['fees'] ?? [];

        // Clean
        if (isset($fees[':i:'])) unset($fees[':i:']);

        return $fees;
    }

    /**
     * Save a temporary booking
     * @param array $data
     * @return int
     * @author Webnus <info@webnus.net>
     */
    public function temporary($data = [])
    {
        $transaction = new Transaction(0, $data);
        return $transaction->update_data();
    }

    /**
     * Generate a transaction id for bookings
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_transaction_id()//TODO: remove
    {
        return Transaction::generate_transaction_id();
    }

    /**
     * Get transaction data
     * @param string $transaction_id
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_transaction($transaction_id)
    {
        return get_option($transaction_id, []);
    }

    /**
     * @param $transaction_id
     * @return MEC_transaction
     */
    public function get_TO($transaction_id)
    {
        MEC::import('app.libraries.transaction');
        return (new MEC_transaction($transaction_id));
    }

    /**
     * Update a transaction
     * @param string $transaction_id
     * @param array $data
     * @author Webnus <info@webnus.net>
     */
    public function update_transaction($transaction_id, $data)
    {
        update_option($transaction_id, $data, false);

        if ($transaction_id)
        {
            $transactionObject = new Transaction($transaction_id);
            $transactionObject->reset_cache_tickets_details();
        }
    }

    /**
     * Add a booking
     * @param array $values
     * @param string $transaction_id
     * @param int $ticket_ids
     * @return int|boolean
     * @author Webnus <info@webnus.net>
     */
    public function add($values, $transaction_id, $ticket_ids)
    {
        // Check Transaction State
        $db = $this->main->getDB();
        $db_transaction_ids = $db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_key` = 'mec_transaction_id' AND `meta_value` = '$transaction_id'");

        foreach ($db_transaction_ids as $db_transaction_id)
        {
            $book_status = get_post_status($db_transaction_id->post_id);
            if (trim($book_status) == 'trash') unset($db_transaction_ids[$db_transaction_id->post_id]);
        }

        if (count($db_transaction_ids)) return false;

        // Transaction Data
        $transaction = $this->get_transaction($transaction_id);
        $event_id = $transaction['event_id'];

        $attention_date = $transaction['date'] ?? '';
        $attention_times = explode(':', $attention_date);

        // Default values
        if (!isset($values['post_date'])) $values['post_date'] = date('Y-m-d H:i:s', trim($attention_times[0]));
        if (!isset($values['post_status'])) $values['post_status'] = 'publish';

        $book_id = wp_insert_post($values);

        // Delete Cache
        delete_transient('all_sold_tickets_' . $event_id);

        // Update transaction id after insert book for prevent repeat reservation books.
        update_post_meta($book_id, 'mec_transaction_id', $transaction_id);

        // Payment Gateway
        if (isset($values['mec_gateway']) and isset($values['mec_gateway_label']))
        {
            update_post_meta($book_id, 'mec_gateway', $values['mec_gateway']);
            update_post_meta($book_id, 'mec_gateway_label', $values['mec_gateway_label']);
        }

        $transaction['booking_id'] = $book_id;
        $transaction['invoice_key'] = md5(time() . mt_rand(10000, 99999));
        if (isset($values['mec_gateway']))
        {
            $transaction['gateway'] = $values['mec_gateway'];
        }

        $this->update_transaction($transaction_id, $transaction);

        // Publish it
        wp_publish_post($book_id);

        // Assign User
        if (isset($values['post_author']) and $values['post_author'])
        {
            $u = $this->getUser();
            $u->assign($book_id, $values['post_author']);
        }

        update_post_meta($book_id, 'mec_verified', 0);
        update_post_meta($book_id, 'mec_verification_key', md5(time() . mt_rand(10000, 99999)));
        update_post_meta($book_id, 'mec_cancellation_key', md5(time() . mt_rand(10000, 99999)));

        update_post_meta($book_id, 'mec_confirmed', 0);

        update_post_meta($book_id, 'mec_event_id', $event_id);
        update_post_meta($book_id, 'mec_date', $transaction['date']);
        update_post_meta($book_id, 'mec_ticket_id', $ticket_ids);
        update_post_meta($book_id, 'mec_booking_time', current_time('Y-m-d H:i:s'));
        update_post_meta($book_id, 'mec_locale', $transaction['locale'] ?? '');

        // Multiple Dates
        if (isset($transaction['all_dates']) and is_array($transaction['all_dates'])) update_post_meta($book_id, 'mec_all_dates', $transaction['all_dates']);
        if (isset($transaction['other_dates']) and is_array($transaction['other_dates'])) update_post_meta($book_id, 'mec_other_dates', $transaction['other_dates']);

        update_post_meta($book_id, 'mec_attention_time', $attention_date);
        update_post_meta($book_id, 'mec_attention_time_start', $attention_times[0]);
        update_post_meta($book_id, 'mec_attention_time_end', $attention_times[1]);

        // For Badge Bubble Notification Alert Count From It.
        update_post_meta($book_id, 'mec_book_date_submit', date('YmdHis', current_time('timestamp')));

        $location_id = $this->main->get_master_location_id($event_id, $attention_times[0]);
        if (!empty($location_id)) update_post_meta($book_id, 'mec_booking_location', $location_id);

        // Event Tickets
        $tickets = get_post_meta($event_id, 'mec_tickets', true);

        $attendees = [];
        if (isset($values['mec_attendees']))
        {
            foreach ($values['mec_attendees'] as $k => $mec_attendee)
            {
                if (!is_numeric($k)) continue;
                $values['mec_attendees'][$k]['buyerip'] = $this->main->get_client_ip();

                $ticket_id = $mec_attendee['id'] ?? 0;
                $ticket_price = (isset($tickets[$ticket_id]) ? $tickets[$ticket_id]['price'] : 0);

                update_post_meta($book_id, 'mec_ticket_price_' . $ticket_id, $ticket_price);
            }

            $attendees = $values['mec_attendees'];
            update_post_meta($book_id, 'mec_attendees', $attendees);
        }

        $price = $transaction['price'] ?? ($transaction['total'] ?? 0);
        update_post_meta($book_id, 'mec_price', $price);

        $payable = $transaction['payable'] ?? $price;
        update_post_meta($book_id, 'mec_payable', $payable);

        // A coupon applied
        if (isset($transaction['coupon']))
        {
            $coupon_id = $this->coupon_get_id($transaction['coupon']);
            if ($coupon_id)
            {
                wp_set_object_terms($book_id, $coupon_id, 'mec_coupon');
                update_post_meta($book_id, 'mec_coupon_code', $transaction['coupon']);
            }
        }

        // Register All Attendees
        if (isset($this->settings['booking_registration']) && $this->settings['booking_registration'] == '2' && count($attendees) > 1)
        {
            $u = $this->getUser();

            $a = 0;
            foreach ($attendees as $attendee)
            {
                $a++;

                // Skip Main Attendee
                if ($a === 1) continue;

                $u->register($attendee, [
                    'event_id' => $event_id,
                ]);
            }
        }

        // Local Data
        update_post_meta($book_id, 'mec_local_timezone', $this->main->get_timezone_by_ip());

        // Booking Record
        $this->getBookingRecord()->insert($book_id);

        // Fires after adding a new booking to send notifications etc
        do_action('mec_booking_added', $book_id);

        [$auto_verify_free, $auto_verify_paid] = $this->get_auto_verification_status($event_id, $book_id);
        [$auto_confirm_free, $auto_confirm_paid] = $this->get_auto_confirmation_status($event_id, $book_id);

        $verified = false;

        // Auto verification for free bookings is enabled
        if ($price <= 0 and $auto_verify_free)
        {
            $this->verify($book_id);
            $verified = true;
        }

        // Auto verification for paid bookings is enabled
        if ($price > 0 and $auto_verify_paid)
        {
            $this->verify($book_id);
            $verified = true;
        }

        // Auto confirmation for free bookings is enabled
        if ($price <= 0 and $auto_confirm_free and $verified)
        {
            $this->confirm($book_id, 'auto');
        }

        // Auto confirmation for paid bookings is enabled
        if ($price > 0 and $auto_confirm_paid and $verified)
        {
            // Work or don't work auto confirmation when pay through pay locally payment.
            $gateways_settings = get_option('mec_options', []);
            $gateway_key = null;
            $can_auto_confirm = true;
            $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : false;

            switch ($action)
            {
                case 'mec_do_transaction_pay_locally':
                case 'mec_cart_do_transaction_pay_locally':

                    $gateway_key = 1;
                    break;
                case 'mec_do_transaction_bank_transfer':
                case 'mec_cart_do_transaction_bank_transfer':

                    $gateway_key = 8;
                    break;
            }

            if (!is_null($gateway_key) && isset($gateways_settings['gateways'][$gateway_key]['disable_auto_confirmation']) && trim($gateways_settings['gateways'][$gateway_key]['disable_auto_confirmation']))
            {
                $can_auto_confirm = false;
            }

            if ($can_auto_confirm) $this->confirm($book_id, 'auto');
        }

        // Latest Booking Date & Time
        update_option('mec_latest_booking_datetime', current_time('YmdHis'), false);

        return $book_id;
    }

    /**
     * Confirm a booking
     * @param int $book_id
     * @param string $mode
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function confirm($book_id, $mode = 'manually')
    {
        update_post_meta($book_id, 'mec_confirmed', 1);

        // Fires after confirming a booking to send notifications etc.
        do_action('mec_booking_confirmed', $book_id, $mode);

        $event_id = get_post_meta($book_id, 'mec_event_id', true);
        $date = get_post_meta($book_id, 'mec_date', true);
        $timestamps = explode(':', $date);

        // Booking Records
        $this->getBookingRecord()->confirm($book_id);

        // Disable Cache
        $cache = $this->getCache();
        $cache->disable();

        // Event is sold-out so fire the event
        $soldout = $this->main->is_sold($event_id, $timestamps[0]);
        if ($soldout) do_action('mec_event_soldout', $event_id, $book_id);

        // Enable Cache
        $cache->enable();

        return true;
    }

    /**
     * Reject a booking
     * @param int $book_id
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function reject($book_id)
    {
        update_post_meta($book_id, 'mec_confirmed', -1);

        // Booking Records
        $this->getBookingRecord()->reject($book_id);

        // Fires after rejecting a booking to send notifications etc.
        do_action('mec_booking_rejected', $book_id);

        return true;
    }

    /**
     * Make a booking pending
     * @param int $book_id
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function pending($book_id)
    {
        update_post_meta($book_id, 'mec_confirmed', 0);

        // Booking Records
        $this->getBookingRecord()->pending($book_id);

        // Fires after pending a booking to send notifications etc.
        do_action('mec_booking_pended', $book_id);

        return true;
    }

    /**
     * Verify a booking
     * @param int $book_id
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function verify($book_id)
    {
        update_post_meta($book_id, 'mec_verified', 1);

        // Booking Records
        $this->getBookingRecord()->verify($book_id);

        // Fires after verifying a booking to send notifications etc.
        do_action('mec_booking_verified', $book_id);

        return true;
    }

    /**
     * Cancel a booking
     * @param int $book_id
     * @param ?bool $refund
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function cancel($book_id, $refund = null)
    {
        $verified = -1;
        $verified = apply_filters('mec_verified_value', $verified, $book_id);

        if ($verified != -1)
        {
            return true;
        }

        update_post_meta($book_id, 'mec_verified', -1);
        update_post_meta($book_id, 'mec_cancelled_date', date('Y-m-d H:i:s', current_time('timestamp')));

        if (is_null($refund)) $refund = (isset($this->settings['booking_auto_refund']) and $this->settings['booking_auto_refund']);
        $gateway = get_post_meta($book_id, 'mec_gateway', true);

        if ($refund and $gateway == 'MEC_gateway_stripe')
        {
            $stripe = new MEC_gateway_stripe();
            $stripe->refund($book_id);

            do_action('mec_booking_refunded', $book_id);

        }
        // New Auto Refund for Square
        if ($gateway == 'MEC_gateway_square_payment' || $gateway == 'MEC_gateway_square')
        {
            $auto_refund_square = isset($this->settings['booking_auto_refund_square']) ? $this->settings['booking_auto_refund_square'] : 0;

            if ($auto_refund_square)
            {
                $square = \MEC_Square\Core\Gateway\Init::instance();
                $square->refundsquare($book_id);

                do_action('mec_booking_refunded', $book_id);
            }
        }

        $this->getBookingRecord()->cancel($book_id);

        do_action('mec_booking_canceled', $book_id);

        return true;
    }

    public function move($booking_id, $from_start, $to_start)
    {
        // Database
        $db = $this->getDB();

        // Event
        $event_id = get_post_meta($booking_id, 'mec_event_id', true);

        $offset = (int) $this->main->get_gmt_offset_seconds($event_id, $to_start);
        $to_start_datetime = wp_date('Y-m-d H:i:s', $to_start - $offset);
        $to_end = (int) $db->select("SELECT `tend` FROM `#__mec_dates` WHERE `post_id`='" . esc_sql($event_id) . "' AND `tstart`='" . esc_sql($to_start) . "'", 'loadResult');
        $from_end = (int) $db->select("SELECT `tend` FROM `#__mec_dates` WHERE `post_id`='" . esc_sql($event_id) . "' AND `tstart`='" . esc_sql($from_start) . "'", 'loadResult');

        // Update Booking Post
        $db->q("UPDATE `#__posts` SET `post_date`='" . esc_sql($to_start_datetime) . "', `post_date_gmt`='" . get_gmt_from_date($to_start_datetime) . "' WHERE `ID`='" . esc_sql($booking_id) . "'");

        // Update Booking Meta
        foreach ([
                     'mec_date' => $to_start . ':' . $to_end,
                     'mec_attention_time' => $to_start . ':' . $to_end,
                     'mec_attention_time_start' => $to_start,
                     'mec_attention_time_end' => $to_end,
                 ] as $key => $value)
        {
            // Keep Previous Data
            $prev = get_post_meta($booking_id, $key, true);
            update_post_meta($booking_id, $key . '_prev', $prev);

            update_post_meta($booking_id, $key, $value);
        }

        // All Dates
        $all_dates = get_post_meta($booking_id, 'mec_all_dates', true);
        if (!is_array($all_dates)) $all_dates = [];

        $k = array_search($from_start . ':' . $from_end, $all_dates);
        if ($k !== false)
        {
            $all_dates[$k] = $to_start . ':' . $to_end;
        }

        update_post_meta($booking_id, 'mec_all_dates', $all_dates);

        // Update Transaction
        $transaction_id = get_post_meta($booking_id, 'mec_transaction_id', true);
        $transaction = $this->get_transaction($transaction_id);

        if (isset($transaction['timestamps']) and is_array($transaction['timestamps']))
        {
            $k = array_search($transaction['date'], $transaction['timestamps']);
            if ($k !== false) $transaction['timestamps'][$k] = $to_start . ':' . $to_end;
        }

        $transaction['date'] = $to_start . ':' . $to_end;
        $this->update_transaction($transaction_id, $transaction);

        // Update booking Record
        $db->q("UPDATE `#__mec_bookings` SET `timestamp`='" . esc_sql($to_start) . "', `date`='" . esc_sql($to_start_datetime) . "' WHERE `booking_id`='" . esc_sql($booking_id) . "' AND `event_id`='" . esc_sql($event_id) . "' AND `timestamp`='" . esc_sql($from_start) . "'");

        // Delete Cache
        clean_post_cache($booking_id);

        // Fires after moving a booking to send notifications etc.
        do_action('mec_booking_moved', $booking_id, $to_start);
    }

    public function move_notify($booking_id, $moved_to)
    {
        // Fires after moving a booking to send notifications etc.
        do_action('mec_booking_moved_notify', $booking_id, $moved_to);
    }

    /**
     * Waiting a booking
     * @param int $book_id
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function waiting($book_id)
    {
        update_post_meta($book_id, 'mec_verified', 0);

        // Booking Records
        $this->getBookingRecord()->waiting($book_id);

        // Fires after waiting a booking to send notifications etc.
        do_action('mec_booking_waiting', $book_id);

        return true;
    }

    /**
     * Returns ticket availabilities of an event for a certain date
     * @param int $event_id
     * @param int $timestamp
     * @param string $mode
     * @return array|integer
     * @author Webnus <info@webnus.net>
     */
    public function get_tickets_availability($event_id, $timestamp, $mode = 'availability')
    {
        $ex = explode(':', $timestamp);
        $timestamp = $ex[0];

        if (!is_numeric($timestamp)) $timestamp = strtotime($timestamp);

        $availability = [];
        $tickets = get_post_meta($event_id, 'mec_tickets', true);

        // No Ticket Found!
        if (!is_array($tickets) or !count($tickets))
        {
            if ($mode == 'reservation') return 0;
            else return $availability;
        }

        $booking_options = get_post_meta($event_id, 'mec_booking', true);
        if (!is_array($booking_options)) $booking_options = [];

        $total_bookings_limit = (isset($booking_options['bookings_limit']) and trim($booking_options['bookings_limit']) !== '') ? $booking_options['bookings_limit'] : 100;
        $bookings_limit_unlimited = $booking_options['bookings_limit_unlimited'] ?? 0;
        $book_all_occurrences = isset($booking_options['bookings_all_occurrences']) ? (int) $booking_options['bookings_all_occurrences'] : 0;

        if ($bookings_limit_unlimited == '1') $total_bookings_limit = '-1';

        // Get Per Occurrence
        $total_bookings_limit = MEC_feature_occurrences::param($event_id, $timestamp, 'bookings_limit', $total_bookings_limit);

        // Total Booking Limit
        $total_bookings_limit_original = $total_bookings_limit;

        // Ticket Selling Stop
        $event_date = date('Y-m-d h:i a', $timestamp);

        if (!$book_all_occurrences && trim($timestamp)) $date_query = " AND `timestamp`=" . $timestamp;
        else $date_query = "";

        // Database
        $db = $this->getDB();

        // Cache
        $cache = $this->getCache();

        $booked = 0;
        foreach ($tickets as $ticket_id => $ticket)
        {
            $limit = isset($ticket['limit']) && trim($ticket['limit']) != '' ? $ticket['limit'] : -1;

            $ticket_seats = (isset($ticket['seats']) and is_numeric($ticket['seats'])) ? (int) $ticket['seats'] : 1;
            $ticket_seats = max(1, $ticket_seats);

            $records = $cache->rememberOnce($event_id . ':' . $ticket_id . ':' . $timestamp, function () use ($db, $event_id, $ticket_id, $date_query)
            {
                return $db->select("SELECT `id`,`ticket_ids` FROM `#__mec_bookings` WHERE `event_id`=" . $event_id . " AND `ticket_ids` LIKE '%," . $ticket_id . ",%' AND `status` IN ('publish', 'pending', 'draft', 'future', 'private') AND `confirmed`!='-1' AND `verified`!='-1'" . $date_query);
            });

            $bookings = 0;
            $booked_seats = 0;
            foreach ($records as $record)
            {
                $ticket_ids = explode(',', trim($record->ticket_ids, ', '));
                $ticket_ids_count = array_count_values($ticket_ids);

                if (isset($ticket_ids_count[$ticket_id]) and is_numeric($ticket_ids_count[$ticket_id]))
                {
                    $bookings += $ticket_ids_count[$ticket_id];
                    $booked_seats += ($ticket_ids_count[$ticket_id] * $ticket_seats);
                }
            }

            if ($total_bookings_limit > 0) $total_bookings_limit = max(($total_bookings_limit - $booked_seats), 0);
            $booked += $bookings;

            $availability_date_start = $ticket['availability_start'] ?? '';
            $availability_date_end = $ticket['availability_end'] ?? '';

            if (trim($availability_date_start) && trim($availability_date_end))
            {
                if (!(strtotime($availability_date_start) <= $timestamp && strtotime($availability_date_end) >= $timestamp))
                {
                    $availability[$ticket_id] = 0;
                    $availability['not_available_' . $ticket_id] = true;
                    $availability['seats_' . $ticket_id] = $ticket_seats;
                    continue;
                }
            }

            // Ticket Selling Stop
            $stop_selling_value = isset($ticket['stop_selling_value']) ? trim($ticket['stop_selling_value']) : 0;
            $stop_selling_type = isset($ticket['stop_selling_type']) ? trim($ticket['stop_selling_type']) : 'day';

            if ($stop_selling_value > 0 and $this->main->check_date_time_validation('Y-m-d h:i a', strtolower($event_date)))
            {
                if (strtotime("-" . $stop_selling_value . $stop_selling_type, strtotime($event_date)) <= current_time('timestamp'))
                {
                    $availability[$ticket_id] = 0;
                    $availability['stop_selling_' . $ticket_id] = true;
                    $availability['seats_' . $ticket_id] = $ticket_seats;
                    continue;
                }
            }

            // Few Seats
            if ($ticket_seats > 1 and $total_bookings_limit > 0 and $total_bookings_limit < $ticket_seats)
            {
                $availability[$ticket_id] = 0;
                $availability['seats_' . $ticket_id] = $ticket_seats;
                continue;
            }

            // Ticket is Unlimited
            if ((isset($ticket['unlimited']) and $ticket['unlimited'] == 1) or $limit == -1)
            {
                $availability[$ticket_id] = $total_bookings_limit > 0 ? floor($total_bookings_limit / $ticket_seats) : -1;
                $availability['seats_' . $ticket_id] = $ticket_seats;
                continue;
            }

            if ($limit == '') $limit = 0;

            $limit = (int) $limit;
            $booked_seats = (int) $booked_seats;

            // Unlimited Total
            if ($total_bookings_limit == '-1') $ticket_availability = $limit - $booked_seats;
            else $ticket_availability = min(($limit - $booked_seats), max($total_bookings_limit, 0));

            $availability[$ticket_id] = $ticket_availability >= 0 ? floor($ticket_availability / $ticket_seats) : 0;
            $availability['seats_' . $ticket_id] = $ticket_seats;
        }

        // For the time being set reservation parameter
        if ($mode == 'reservation') return $booked;

        // Set Total Booking Limit
        $availability['total'] = $total_bookings_limit;

        // Do not send higher limit for tickets compared to total limit
        if ($total_bookings_limit != '-1' and $total_bookings_limit > 0)
        {
            $new_availability = [];
            foreach ($availability as $ticket_id => $limit)
            {
                if (is_numeric($ticket_id))
                {
                    $ticket_seats = $availability['seats_' . $ticket_id];
                    $ticket_seats = max(1, $ticket_seats);

                    $seats = $limit * $ticket_seats;
                    $new_availability[$ticket_id] = floor(min($seats, $total_bookings_limit) / $ticket_seats);
                }
                else $new_availability[$ticket_id] = $limit;
            }

            return apply_filters('mec_get_tickets_availability', $new_availability, $event_id, $timestamp, $mode, $tickets);
        }

        // Total Booking Limit Reached
        if ($total_bookings_limit_original != -1 and $booked >= $total_bookings_limit_original)
        {
            $new_availability = [];
            foreach ($availability as $ticket_id => $limit)
            {
                if (is_numeric($ticket_id)) $new_availability[$ticket_id] = 0;
                else $new_availability[$ticket_id] = $limit;
            }

            return apply_filters('mec_get_tickets_availability', $new_availability, $event_id, $timestamp, $mode, $tickets);
        }

        return apply_filters('mec_get_tickets_availability', $availability, $event_id, $timestamp, $mode, $tickets);
    }

    /**
     * Returns ticket availabilities of an event for a certain date
     * @param int $event_id
     * @param array $dates
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_tickets_availability_multiple($event_id, $dates)
    {
        $availability = [];
        foreach ($dates as $date)
        {
            $ex = explode(':', sanitize_text_field($date));
            $date = $ex[0];

            $a = $this->get_tickets_availability($event_id, $date);
            if (!is_array($a)) continue;

            // Fill Compatibility
            if (!count($availability)) $availability = $a;

            // Minimum Availability
            foreach ($availability as $k => $v)
            {
                if (isset($a[$k])) $availability[$k] = min($a[$k], $v);
            }
        }

        return $availability;
    }

    /**
     * Check validity of a coupon
     * @param string $coupon
     * @param int $event_id
     * @param array $transaction
     * @return int
     * @author Webnus <info@webnus.net>
     */
    public function coupon_check_validity($coupon, $event_id, $transaction)
    {
        $term = get_term_by('name', $coupon, 'mec_coupon');
        $coupon_id = $term->term_id ?? 0;

        // Coupon is not exists
        if (!$coupon_id) return 0;

        // Usage Limit
        $usage_limit = get_term_meta($coupon_id, 'usage_limit', true);
        $status = ($usage_limit == '-1' or (int) $usage_limit > $term->count) ? 1 : -1;

        // Expiration Date
        if ($status === 1)
        {
            $expiration_date = get_term_meta($coupon_id, 'expiration_date', true);
            if (trim($expiration_date) and strtotime($expiration_date) < strtotime(date('Y-m-d')))
            {
                $status = -2;
            }
        }

        // Event Specification
        if ($status === 1)
        {
            $all_events = get_term_meta($coupon_id, 'target_event', true);
            if (trim($all_events) == '') $all_events = 1;

            $target_events = get_term_meta($coupon_id, 'target_events', true);
            if (!is_array($target_events))
            {
                $target_events = [];
                if ($all_events and $all_events != 1) $target_events[] = $all_events;
            }

            if (!$all_events and is_array($target_events) and count($target_events) and !in_array($event_id, $target_events))
            {
                $status = -3;
            }
        }

        // Category Specification
        if ($status === 1)
        {
            $all_target_categories = get_term_meta($coupon_id, 'target_category', true);
            if (trim($all_target_categories) == '') $all_target_categories = 1;

            $target_categories = get_term_meta($coupon_id, 'target_categories', true);
            if (!$all_target_categories and is_array($target_categories) and count($target_categories))
            {
                $event_categories = wp_get_post_terms($event_id, 'mec_category', ['fields' => 'ids']);

                $found = false;
                foreach ($target_categories as $target_category)
                {
                    if (in_array($target_category, $event_categories))
                    {
                        $found = true;
                        break;
                    }
                }

                if (!$found) $status = -6;
            }
        }

        // Minimum Tickets
        if ($status === 1)
        {
            $ticket_minimum = get_term_meta($coupon_id, 'ticket_minimum', true);
            if (!trim($ticket_minimum)) $ticket_minimum = 1;

            $tickets = $transaction['tickets'] ?? [];
            if (isset($tickets['attachments'])) unset($tickets['attachments']);

            if (count($tickets) < $ticket_minimum)
            {
                $status = -4;
            }
        }

        // Maximum Tickets
        if ($status === 1)
        {
            $ticket_maximum = get_term_meta($coupon_id, 'ticket_maximum', true);
            if (trim($ticket_maximum))
            {
                $tickets = $transaction['tickets'] ?? [];
                if (isset($tickets['attachments'])) unset($tickets['attachments']);

                if (count($tickets) > $ticket_maximum)
                {
                    $status = -5;
                }
            }
        }

        $all_dates = (isset($transaction['all_dates']) and is_array($transaction['all_dates'])) ? $transaction['all_dates'] : [];

        // Minimum Dates
        if ($status === 1 and count($all_dates) >= 1)
        {
            $date_minimum = get_term_meta($coupon_id, 'date_minimum', true);
            if (!trim($date_minimum)) $date_minimum = 1;

            if (count($all_dates) < $date_minimum)
            {
                $status = -7;
            }
        }

        // Maximum Dates
        if ($status === 1 and count($all_dates) >= 1)
        {
            $date_maximum = get_term_meta($coupon_id, 'date_maximum', true);
            if (trim($date_maximum))
            {
                if (count($all_dates) > $date_maximum)
                {
                    $status = -8;
                }
            }
        }

        return $status;
    }

    /**
     * Apply a coupon to a transaction
     * @param string $coupon
     * @param int $transaction_id
     * @param string $payment_method
     * @return int
     * @author Webnus <info@webnus.net>
     */
    public function coupon_apply($coupon, $transaction_id, $payment_method = 'full')
    {
        $transaction = $this->get_transaction($transaction_id);
        $event_id = $transaction['event_id'] ?? null;

        // Verify validity of coupon
        if ($this->coupon_check_validity($coupon, $event_id, $transaction) != 1) return 0;

        $total = $transaction['total'];
        $discount = $this->coupon_get_discount($coupon, $total);
        $after_discount = $total - $discount;

        $transaction['price_details']['total'] = $after_discount;

        $price_details = $transaction['price_details']['details'];
        foreach ($price_details as $i => $price_detail)
        {
            if (isset($price_detail['type']) and $price_detail['type'] == 'discount' and isset($price_detail['coupon'])) unset($price_details[$i]);
        }

        $price_details[] = ['amount' => $discount, 'description' => __('Discount', 'modern-events-calendar-lite'), 'type' => 'discount', 'coupon' => $coupon];

        $transaction['price_details']['details'] = $price_details;
        $transaction['discount'] = $discount;
        $transaction['price'] = $after_discount;
        $transaction['coupon'] = $coupon;

        // Re-calculate
        $transaction = $this->recalculate($transaction, $payment_method === 'partial');

        // Update Transaction
        $this->update_transaction($transaction_id, $transaction);

        return $transaction['discount'] ?? $discount;
    }

    /**
     * Get discount of a coupon
     * @param string $coupon
     * @param int $total
     * @return int
     * @author Webnus <info@webnus.net>
     */
    public function coupon_get_discount($coupon, $total)
    {
        $coupon_id = $this->coupon_get_id($coupon);

        // Coupon is not exists
        if (!$coupon_id) return 0;

        $discount_type = get_term_meta($coupon_id, 'discount_type', true);
        $discount = get_term_meta($coupon_id, 'discount', true);

        if ($discount_type == 'percent')
        {
            $discount_amount = ($total * $discount) / 100;

            $discount_max = get_term_meta($coupon_id, 'maximum_discount', true);
            if (trim($discount_max) and is_numeric($discount_max)) $discount_amount = min($discount_amount, $discount_max);
        }
        else $discount_amount = min($discount, $total);

        return $discount_amount;
    }

    /**
     * Get id of a coupon by coupon number
     * @param string $coupon
     * @return int
     * @author Webnus <info@webnus.net>
     */
    public function coupon_get_id($coupon)
    {
        $term = get_term_by('name', $coupon, 'mec_coupon');
        return $term->term_id ?? 0;
    }

    /**
     * Is coupon 100 percent?
     * @param string $coupon
     * @return bool
     * @author Webnus <info@webnus.net>
     */
    public function coupon_is_100percent($coupon)
    {
        $coupon_id = $this->coupon_get_id($coupon);

        // Coupon is not exists
        if (!$coupon_id) return false;

        $discount_type = get_term_meta($coupon_id, 'discount_type', true);
        $discount = (int) get_term_meta($coupon_id, 'discount', true);

        return ($discount_type === 'percent' and $discount === 100);
    }

    public function recalculate($transaction, $partial = true)
    {
        $price_details = $transaction['price_details']['details'];

        $other_dates = (isset($transaction['other_dates']) and is_array($transaction['other_dates'])) ? $transaction['other_dates'] : [];
        $dates_count = count($other_dates) + 1;

        $booked_tickets = $transaction['tickets'];
        if (isset($booked_tickets['attachments'])) unset($booked_tickets['attachments']);

        $total_tickets_count = (count($booked_tickets) * $dates_count);

        $total_fee_amount = 0;
        $taxable = 0;
        $total_discount = 0;
        $fees_to_apply = [];
        $discounts_to_apply = [];

        foreach ($price_details as $i => $item)
        {
            $type = $item['type'] ?? '';
            $amount = $item['amount'] ?? 0;

            if ($type == 'fee') $fees_to_apply[] = $item;
            else if ($type == 'discount') $discounts_to_apply[] = $item;
            else $taxable += $amount;

            // Remove Fee and Discount Items
            if (in_array($type, ['fee', 'discount'])) unset($price_details[$i]);
        }

        $total = $taxable;

        $fee_discount = 0;
        $fee_discount_type = 'percent';

        // Apply Discounts
        foreach ($discounts_to_apply as $discount_item)
        {
            $coupon_id = $this->coupon_get_id($discount_item['coupon']);
            if ($coupon_id && get_term_meta($coupon_id, 'apply_on_fees', true))
            {
                $fee_discount_type = get_term_meta($coupon_id, 'discount_type', true);
                $fee_discount = get_term_meta($coupon_id, 'discount', true);
            }

            $discount = $this->coupon_get_discount($discount_item['coupon'], $taxable);
            $taxable = max(0, ($taxable - $discount));
            $total_discount += $discount;

            $price_details[] = ['amount' => $discount, 'description' => __('Discount', 'modern-events-calendar-lite'), 'type' => 'discount', 'coupon' => $discount_item['coupon']];
        }

        // Apply Fees
        foreach ($fees_to_apply as $fee_item)
        {
            $fee_amount = 0;

            if ($fee_item['fee_type'] == 'percent') $fee_amount += ($taxable * $fee_item['fee_amount']) / 100;
            else if ($fee_item['fee_type'] == 'amount') $fee_amount += ($total_tickets_count * $fee_item['fee_amount']);
            else if ($fee_item['fee_type'] == 'amount_per_date') $fee_amount += ($dates_count * $fee_item['fee_amount']);
            else if ($fee_item['fee_type'] == 'amount_per_booking') $fee_amount += $fee_item['fee_amount'];

            // Apply Discount on Fee
            if ($fee_discount && in_array($fee_item['fee_type'], ['amount', 'amount_per_date', 'amount_per_booking']))
            {
                if ($fee_discount_type === 'percent') $fee_discount_amount = (float) ($fee_amount * min($fee_discount, 100)) / 100;
                else $fee_discount_amount = (float) max($fee_discount, 0);

                $fee_discount_amount = min($fee_amount, $fee_discount_amount);

                $total_discount += $fee_discount_amount;
                $fee_amount = max($fee_amount - $fee_discount_amount, 0);
            }

            $total_fee_amount += $fee_amount;
            $price_details[] = ['amount' => $fee_amount, 'description' => __($fee_item['description'], 'modern-events-calendar-lite'), 'type' => 'fee', 'fee_type' => $fee_item['fee_type'], 'fee_amount' => $fee_item['fee_amount']];
        }

        $total += $total_fee_amount;
        $price = ($taxable + $total_fee_amount);
        $payable = $price;

        // Calculate Payable
        if ($this->partial_payment->is_enabled() && $partial) $payable = $this->partial_payment->calculate($price, $transaction['event_id']);

        $transaction['price_details']['total'] = $price;
        $transaction['price_details']['payable'] = $payable;
        $transaction['price_details']['details'] = $price_details;
        $transaction['discount'] = $total_discount;
        $transaction['total'] = $total;
        $transaction['price'] = $price;
        $transaction['payable'] = $payable;

        return $transaction;
    }

    /**
     * Get invoice link for certain transaction
     * @param $transaction_id
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_invoice_link($transaction_id)
    {
        if (isset($this->settings['booking_invoice']) and !$this->settings['booking_invoice']) return '';

        $main = $this->getMain();

        $url = $main->URL();
        $url = $main->add_qs_var('method', 'mec-invoice', $url);

        // Invoice Key
        $transaction = $this->get_transaction($transaction_id);
        if (isset($transaction['invoice_key'])) $url = $main->add_qs_var('mec-key', $transaction['invoice_key'], $url);

        return apply_filters('mec_booking_invoice_url', $main->add_qs_var('id', $transaction_id, $url), $transaction_id);
    }

    /**
     * Get Downloadable file link for certain transaction
     * @param $book_id
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function get_dl_file_link($book_id)
    {
        if (!isset($this->settings['downloadable_file_status']) or (isset($this->settings['downloadable_file_status']) and !$this->settings['downloadable_file_status'])) return '';

        $event_id = get_post_meta($book_id, 'mec_event_id', true);
        $dl_file_id = get_post_meta($event_id, 'mec_dl_file', true);

        return apply_filters('mec_booking_dl_file_url', ($dl_file_id ? wp_get_attachment_url($dl_file_id) : ''), $book_id);
    }

    public function get_bookings_by_transaction_id($transaction_id)
    {
        $main = $this->getMain();

        return get_posts([
            'posts_per_page' => -1,
            'post_type' => $main->get_book_post_type(),
            'meta_key' => 'mec_transaction_id',
            'meta_value' => $transaction_id,
        ]);
    }

    public function get_thankyou_page($page_id, $transaction_id = null, $cart_id = null)
    {
        $main = $this->getMain();
        $page = get_permalink($page_id);

        if ($transaction_id) $page = $main->add_qs_var('transaction', $transaction_id, $page);
        if ($cart_id) $page = $main->add_qs_var('cart', $cart_id, $page);

        return $page;
    }

    public function invoice_link_shortcode()
    {
        $transaction = isset($_GET['transaction']) ? sanitize_text_field($_GET['transaction']) : null;
        if (!$transaction) return null;

        $book = $this->getBook();
        return '<a href="' . esc_url($book->get_invoice_link($transaction)) . '" target="_blank">' . esc_html__('Download Invoice', 'modern-events-calendar-lite') . '</a>';
    }

    public function get_total_attendees($book_id)
    {
        $attendees = get_post_meta($book_id, 'mec_attendees', true);
        $event_id = get_post_meta($book_id, 'mec_event_id', true);
        $tickets = get_post_meta($event_id, 'mec_tickets', true);

        $count = 0;

        if (is_array($attendees))
        {
            foreach ($attendees as $key => $attendee)
            {
                if ($key === 'attachments') continue;

                $seats = 1;

                $ticket_id = $attendee['id'] ?? 0;
                if ($ticket_id && isset($tickets[$ticket_id]['seats']) && $tickets[$ticket_id]['seats'])
                {
                    $seats = $tickets[$ticket_id]['seats'];
                }

                if (!isset($attendee[0]['MEC_TYPE_OF_DATA'])) $count += $seats;
                else if ($attendee[0]['MEC_TYPE_OF_DATA'] != 'attachment') $count += $seats;
            }
        }

        return $count;
    }

    public function get_attendees($book_id)
    {
        $attendees = get_post_meta($book_id, 'mec_attendees', true);
        $clean = [];

        if (is_array($attendees))
        {
            foreach ($attendees as $key => $attendee)
            {
                if ($key === 'attachments') continue;

                $clean[$key] = $attendee;
            }
        }

        return $clean;
    }

    public function get_transaction_id_book_id($book_id)
    {
        return get_post_meta($book_id, 'mec_transaction_id', true);
    }

    public function get_book_id_transaction_id($transaction_id)
    {
        $db = $this->getDB();
        return $db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_key`='mec_transaction_id' AND `meta_value`='" . $db->escape($transaction_id) . "'", 'loadResult');
    }

    public function get_ticket_price_label($ticket, $current_date, $event_id, $timestamp = null)
    {
        return $this->get_ticket_price_key($ticket, $current_date, $event_id, 'price_label', $timestamp);
    }

    public function get_ticket_price($ticket, $current_date, $event_id, $timestamp = null)
    {
        $price = $this->get_ticket_price_key($ticket, $current_date, $event_id, 'price', $timestamp);
        return trim($price) ? $price : 0;
    }

    public function get_ticket_regular_price_key($ticket, $current_date, $event_id, $key, $timestamp = null)
    {
        $data = $ticket[$key] ?? null;

        if ($timestamp and isset($ticket['id']) and trim($ticket['id']) !== '')
        {
            $occ_tickets = MEC_feature_occurrences::param($event_id, $timestamp, 'tickets', []);
            if (is_array($occ_tickets) and isset($occ_tickets[$ticket['id']]) and is_array($occ_tickets[$ticket['id']]) and isset($occ_tickets[$ticket['id']][$key]) and trim($occ_tickets[$ticket['id']][$key]) !== '')
            {
                $data = $occ_tickets[$ticket['id']][$key];
            }
        }

        $price_dates = (isset($ticket['dates']) and is_array($ticket['dates'])) ? $ticket['dates'] : [];
        if (count($price_dates))
        {
            $current_time = strtotime($current_date);
            foreach ($price_dates as $k => $price_date)
            {
                if (!is_numeric($k)) continue;

                $start = $price_date['start'];
                $end = $price_date['end'];

                if ($current_time >= strtotime($start) and $current_time <= strtotime($end))
                {
                    if ($key == 'price_label') $data = $price_date['label'];
                    else $data = $price_date[$key];
                }
            }
        }

        return $data;
    }

    public function get_ticket_price_key($ticket, $current_date, $event_id, $key, $timestamp = null)
    {
        $data = $this->get_ticket_regular_price_key($ticket, $current_date, $event_id, $key, $timestamp);

        return $this->get_price_for_loggedin_users($event_id, $data, $key);
    }

    /**
     * Returns tickets prices of an event for a certain date
     * @param int $event_id
     * @param string $date
     * @param string $key
     * @param int $timestamp
     * @return array
     * @author Webnus <info@webnus.net>
     */
    public function get_tickets_prices($event_id, $date, $key = 'price', $timestamp = null)
    {
        $prices = [];
        $tickets = get_post_meta($event_id, 'mec_tickets', true);

        // No Ticket Found!
        if (!is_array($tickets) || !count($tickets)) return $prices;

        foreach ($tickets as $ticket_id => $ticket)
        {
            $price = $this->get_ticket_price_key($ticket, $date, $event_id, $key, $timestamp);
            $prices[$ticket_id] = apply_filters('mec_filter_ticket_price', $price, $ticket, $event_id, $this);
        }

        return $prices;
    }

    public function get_price_for_loggedin_users($event_id, $price, $type = 'price')
    {
        if (!is_user_logged_in()) return $price;

        $user_id = get_current_user_id();
        return $this->get_price_for_users($event_id, $price, $user_id, $type);
    }

    public function get_price_for_users($event_id, $price, $user_id, $type = 'price')
    {
        // Guest User
        if (!$user_id) return $price;

        $booking_options = get_post_meta($event_id, 'mec_booking', true);
        if (!is_array($booking_options)) $booking_options = [];

        // User
        $user = get_user_by('id', $user_id);

        // Invalid User ID
        if (!$user || !isset($user->roles)) return $price;

        $roles = $user->roles;

        $loggedin_discount = $booking_options['loggedin_discount'] ?? 0;
        $role_discount = $loggedin_discount;

        // Step through all roles in Array that comes from WordPress Core
        foreach ($roles as $role)
        {
            // If role discount is higher than the preset role OR a previous roles discount, set it to the new higher discount
            if (isset($booking_options['roles_discount_' . $role]) and is_numeric($booking_options['roles_discount_' . $role]) and $booking_options['roles_discount_' . $role] > $role_discount) $role_discount = $booking_options['roles_discount_' . $role];
        }

        if (trim($role_discount) and is_numeric($role_discount))
        {
            if ($type === 'price_label' and !is_numeric($price))
            {
                $numeric = preg_replace("/[^0-9.]/", '', $price);
                if (is_numeric($numeric)) $price = $this->main->render_price(($numeric - (($numeric * $role_discount) / 100)), $event_id);
            }
            else
            {
                $price = $price - (($price * $role_discount) / 100);
            }
        }

        // Render Numeric Price
        if ($type === 'price_label' and is_numeric($price)) $price = $this->main->render_price($price, $event_id);

        return $price;
    }

    public function get_user_booking_limit($event_id)
    {
        $unlimited = false;
        $limit = 12;
        $mec_settings = $this->main->get_settings();
        $booking_options = get_post_meta($event_id, 'mec_booking', true);

        // Total user booking limited
        if (isset($booking_options['bookings_user_limit_unlimited']) and !trim($booking_options['bookings_user_limit_unlimited']))
        {
            $limit = (isset($booking_options['bookings_user_limit']) and trim($booking_options['bookings_user_limit'])) ? trim($booking_options['bookings_user_limit']) : $limit;
        }
        else
        {
            // If Inherit from global options activate
            if (!isset($mec_settings['booking_limit']) or !trim($mec_settings['booking_limit'])) $unlimited = true;
            else $limit = trim($mec_settings['booking_limit']);
        }

        return [$limit, $unlimited];
    }

    public function get_minimum_tickets_per_booking($event_id)
    {
        $booking_options = get_post_meta($event_id, 'mec_booking', true);

        $bookings_minimum_per_booking = (isset($booking_options['bookings_minimum_per_booking']) and trim($booking_options['bookings_minimum_per_booking'])) ? (int) $booking_options['bookings_minimum_per_booking'] : 1;
        return max($bookings_minimum_per_booking, 1);
    }

    public function timestamp($start, $end)
    {
        // Timestamp is already available
        if (isset($start['timestamp']) && isset($end['timestamp']))
        {
            return $start['timestamp'] . ':' . $end['timestamp'];
        }

        $s_hour = $start['hour'];
        if (strtoupper($start['ampm']) == 'AM' and $s_hour == '0') $s_hour = 12;

        $e_hour = $end['hour'];
        if (strtoupper($end['ampm']) == 'AM' and $e_hour == '0') $e_hour = 12;

        $start_time = $start['date'] . ' ' . sprintf("%02d", $s_hour) . ':' . sprintf("%02d", $start['minutes']) . ' ' . $start['ampm'];
        $end_time = $end['date'] . ' ' . sprintf("%02d", $e_hour) . ':' . sprintf("%02d", $end['minutes']) . ' ' . $end['ampm'];

        return strtotime($start_time) . ':' . strtotime($end_time);
    }

    public function get_event_id_by_transaction_id($transaction_id)
    {
        $transaction = $this->get_transaction($transaction_id);
        return $transaction['event_id'] ?? 0;
    }

    public function get_attendee_price($transaction, $email)
    {
        if (!is_array($transaction)) $transaction = $this->get_transaction($transaction);

        // No Attendees found!
        if (!isset($transaction['tickets']) || !is_array($transaction['tickets'])) return false;

        $attendee = [];
        foreach ($transaction['tickets'] as $key => $ticket)
        {
            if (!is_numeric($key)) continue;

            if ($ticket['email'] == $email)
            {
                $attendee = $ticket;
                break;
            }
        }

        // Attendee not found
        if (!count($attendee)) return false;

        $event_id = $transaction['event_id'] ?? 0;
        if (!$event_id) return false;

        $tickets = get_post_meta($event_id, 'mec_tickets', true);

        $dates = explode(':', $transaction['date']);

        $ticket_price = isset($tickets[$attendee['id']]) ? $this->get_ticket_price($tickets[$attendee['id']], date('Y-m-d', $dates[0]), $event_id, $dates[0]) : 0;
        if (!$ticket_price) return false;

        $variation_price = 0;

        // Ticket Variations
        if (isset($attendee['variations']) and is_array($attendee['variations']) and count($attendee['variations']))
        {
            $ticket_variations = $this->main->ticket_variations($event_id, $attendee['id']);
            foreach ($attendee['variations'] as $variation_id => $variation_count)
            {
                if (!$variation_count || $variation_count < 0) continue;

                $variation_price += ((isset($ticket_variations[$variation_id]['price']) and is_numeric($ticket_variations[$variation_id]['price'])) ? $ticket_variations[$variation_id]['price'] : 0);
            }
        }

        return ($ticket_price + $variation_price);
    }

    public function get_auto_verification_status($event_id, $book_id)
    {
        // Booking Options
        $BO = get_post_meta($event_id, 'mec_booking', true);
        if (!is_array($BO)) $BO = [];

        $event_auto_verify = (isset($BO['auto_verify']) and trim($BO['auto_verify']) != '') ? $BO['auto_verify'] : 'global';
        if (is_numeric($event_auto_verify)) $event_auto_verify = (int) $event_auto_verify;

        if ($event_auto_verify === 'global')
        {
            $auto_verify_free = $this->settings['booking_auto_verify_free'] ?? 0;
            $auto_verify_paid = $this->settings['booking_auto_verify_paid'] ?? 0;
        }
        else
        {
            $auto_verify_free = $event_auto_verify;
            $auto_verify_paid = $event_auto_verify;
        }

        return apply_filters('mec_get_auto_verification_status', [$auto_verify_free, $auto_verify_paid], $event_id, $book_id);
    }

    public function get_auto_confirmation_status($event_id, $book_id)
    {
        // Booking Options
        $BO = get_post_meta($event_id, 'mec_booking', true);
        if (!is_array($BO)) $BO = [];

        $event_auto_confirm = (isset($BO['auto_confirm']) and trim($BO['auto_confirm']) != '') ? $BO['auto_confirm'] : 'global';
        if (is_numeric($event_auto_confirm)) $event_auto_confirm = (int) $event_auto_confirm;

        if ($event_auto_confirm === 'global')
        {
            $auto_confirm_free = $this->settings['booking_auto_confirm_free'] ?? 0;
            $auto_confirm_paid = $this->settings['booking_auto_confirm_paid'] ?? 0;
        }
        else
        {
            $auto_confirm_free = $event_auto_confirm;
            $auto_confirm_paid = $event_auto_confirm;
        }

        return apply_filters('mec_get_auto_confirmation_status', [$auto_confirm_free, $auto_confirm_paid], $event_id, $book_id);
    }

    public function get_total_tickets($event_id)
    {
        $booking_options = get_post_meta($event_id, 'mec_booking', true);
        if (!is_array($booking_options)) $booking_options = [];

        $tickets = get_post_meta($event_id, 'mec_tickets', true);

        $total_bookings_limit = (isset($booking_options['bookings_limit']) and trim($booking_options['bookings_limit']) !== '') ? $booking_options['bookings_limit'] : 100;
        $bookings_limit_unlimited = $booking_options['bookings_limit_unlimited'] ?? 0;

        if ($bookings_limit_unlimited == '1') $total_bookings_limit = '-1';

        $all_ticket_availability = -1;
        foreach ($tickets as $ticket_id => $ticket)
        {
            $limit = (isset($ticket['limit']) and trim($ticket['limit']) != '') ? $ticket['limit'] : -1;

            $ticket_seats = (isset($ticket['seats']) and is_numeric($ticket['seats'])) ? (int) $ticket['seats'] : 1;
            $ticket_seats = max(1, $ticket_seats);

            // Ticket is Unlimited
            if ((isset($ticket['unlimited']) && $ticket['unlimited'] == 1) || $limit == -1) continue;

            // Unlimited Total
            if ($total_bookings_limit == '-1') $ticket_availability = $limit;
            else $ticket_availability = min($limit, max($total_bookings_limit, 0));

            $all_ticket_availability = max($all_ticket_availability, 0);
            $all_ticket_availability += $ticket_availability >= 0 ? floor($ticket_availability / $ticket_seats) : 0;
        }

        $final = $all_ticket_availability != '-1' ? $all_ticket_availability : $total_bookings_limit;

        return $final == '-1' ? __('Unlimited', 'modern-events-calendar-lite') : $final;
    }

    public function get_all_sold_tickets($event_id)
    {
        // Transient Key
        $transient_key = 'all_sold_tickets_' . $event_id;

        // Cached Data
        $cached = get_transient($transient_key);

        // Return Cached Data
        if ($cached !== false) return $cached;

        $query = new WP_Query([
            'post_type' => $this->PT,
            'posts_per_page' => -1,
            'post_status' => ['publish', 'pending', 'draft', 'future', 'private'],
            'meta_query' => [
                ['key' => 'mec_event_id', 'value' => $event_id, 'compare' => '='],
                ['key' => 'mec_confirmed', 'value' => 1, 'compare' => '='],
                ['key' => 'mec_verified', 'value' => 1, 'compare' => '='],
            ],
        ]);

        $sold = 0;
        if ($query->have_posts())
        {
            // The Loop
            while ($query->have_posts())
            {
                $query->the_post();
                $sold += $this->get_total_attendees(get_the_ID());
            }

            // Restore original Post Data
            wp_reset_postdata();
        }

        // Set Transient
        set_transient($transient_key, $sold, 24 * HOUR_IN_SECONDS);

        return $sold;
    }

    public function get_ticket_total_price($transaction, $attendee, $booking_id)
    {
        $event_id = $transaction['event_id'];

        $all_attendees = get_post_meta($booking_id, 'mec_attendees', true);
        if (!is_array($all_attendees) || !count($all_attendees)) $all_attendees = [get_post_meta($booking_id, 'mec_attendee', true)];

        if (isset($all_attendees['attachments'])) unset($all_attendees['attachments']);

        $total_price = get_post_meta($booking_id, 'mec_price', true);
        if (count($all_attendees) == 1) return $total_price;

        $tickets = get_post_meta($event_id, 'mec_tickets', true);
        $ticket_id = $attendee['id'];

        $ticket_variations = $this->main->ticket_variations($event_id, $ticket_id);

        $ticket_price_booking_saved = get_post_meta($booking_id, 'mec_ticket_price_' . $ticket_id, true);
        if (trim($ticket_price_booking_saved) === '') $ticket_price_booking_saved = 0;

        $ticket_price = (isset($tickets[$ticket_id]) ? $tickets[$ticket_id]['price'] : $ticket_price_booking_saved);

        $user = $this->getUser();
        $booking_user = $user->booking($booking_id);

        $ticket_price = $this->get_price_for_users($event_id, $ticket_price, $booking_user ? $booking_user->ID : 0);

        // Price Per Date
        if (isset($tickets[$ticket_id]['dates']) and is_array($tickets[$ticket_id]['dates']) and count($tickets[$ticket_id]['dates']))
        {
            $book_time = strtotime(get_post_meta($booking_id, 'mec_booking_time', true));
            if ($book_time)
            {
                $pdates = $tickets[$ticket_id]['dates'];
                foreach ($pdates as $pdate)
                {
                    if (!isset($pdate['start']) or !isset($pdate['end'])) continue;

                    $t_start = strtotime($pdate['start']);
                    $t_end = strtotime($pdate['end']);

                    if ($book_time >= $t_start and $book_time <= $t_end and isset($pdate['price']))
                    {
                        $ticket_price = $pdate['price'];
                        break;
                    }
                }
            }
        }

        $variation_price = 0;
        if (isset($attendee['variations']) and is_array($attendee['variations']) and count($attendee['variations']))
        {
            foreach ($attendee['variations'] as $variation_id => $count)
            {
                if (!trim($count)) continue;
                if (!isset($ticket_variations[$variation_id])) continue;

                $p = $ticket_variations[$variation_id]['price'];
                if (is_numeric($p) and is_numeric($count)) $variation_price += ($p * $count);
            }
        }

        // Fees
        $total_fees = 0;

        // Discounts
        $discounts = 0;

        if (isset($transaction['price_details']) and isset($transaction['price_details']['details']) and is_array($transaction['price_details']['details']) and count($transaction['price_details']['details']))
        {
            foreach ($transaction['price_details']['details'] as $detail)
            {
                if (!isset($detail['type'])) continue;

                if ($detail['type'] == 'fee' and isset($detail['amount']) and is_numeric($detail['amount'])) $total_fees += $detail['amount'];
                if ($detail['type'] == 'discount' and isset($detail['amount']) and is_numeric($detail['amount'])) $discounts += $detail['amount'];
            }
        }

        $ticket_total_price = null;
        if (is_numeric($ticket_price) and is_numeric($variation_price) and is_numeric($total_fees) and is_numeric($discounts))
        {

            $ticket_total_price = ($ticket_price + $variation_price + ($total_fees / count($all_attendees))) - ($discounts / count($all_attendees));
        }

        return (!is_null($ticket_total_price) ? $ticket_total_price : $total_price);
    }

    /**
     * Remove Fees From a Transaction
     * @param int $transaction_id
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function remove_fees($transaction_id)
    {
        $transaction = $this->get_transaction($transaction_id);
        $price_details = $transaction['price_details']['details'];

        $removed_fees = [];
        foreach ($price_details as $i => $price_detail)
        {
            if (isset($price_detail['type']) and $price_detail['type'] == 'fee')
            {
                $removed_fees[] = $price_detail;
                unset($price_details[$i]);
            }
        }

        $transaction['price_details']['details'] = $price_details;
        $transaction['removed_fees_status'] = 1;
        $transaction['removed_fees'] = (count($removed_fees) ? $removed_fees : ($transaction['removed_fees'] ?? []));

        // Re-calculate
        $transaction = $this->recalculate($transaction);

        // Update Transaction
        $this->update_transaction($transaction_id, $transaction);

        return true;
    }

    /**
     * Re-Add Fees To a Transaction
     * @param int $transaction_id
     * @return boolean
     * @author Webnus <info@webnus.net>
     */
    public function readd_fees($transaction_id)
    {
        $transaction = $this->get_transaction($transaction_id);

        $is_removed = $transaction['removed_fees_status'] ?? 0;
        if (!$is_removed) return false;

        $price_details = $transaction['price_details']['details'];

        $removed_fees = (isset($transaction['removed_fees']) and is_array($transaction['removed_fees'])) ? $transaction['removed_fees'] : [];
        foreach ($removed_fees as $removed_fee) $price_details[] = $removed_fee;

        $transaction['price_details']['details'] = $price_details;

        unset($transaction['removed_fees_status']);
        unset($transaction['removed_fees']);

        // Re-calculate
        $transaction = $this->recalculate($transaction);

        // Update Transaction
        $this->update_transaction($transaction_id, $transaction);

        return true;
    }

    public function get_cancel_url($event, $booking_id)
    {
        $event_url = trim(get_permalink($event), '/ ');
        $cancelation_key = get_post_meta($booking_id, 'mec_cancellation_key', true);

        if (strpos($event_url, '?') === false) return $event_url . '/cancel/' . $cancelation_key . '/';
        else return $this->main->add_qs_var('cancel', $cancelation_key, $event_url);
    }
}
