<?php

namespace MEC\Tracking;

/**
 * Booking lifecycle events (marketing plan, event 8).
 *
 * mec_booking_completed fires exactly once per booking, at the moment the
 * booking becomes confirmed. The existing 'mec_booking_confirmed' action is
 * the single choke point through which every confirmation path passes:
 * free/pay-locally auto-confirmation, gateway payment verification (the
 * "verify" flow in MEC_main), and manual confirmation from the admin.
 *
 * Privacy: only buckets and enums leave the install — never the buyer's
 * name, email, exact amounts, or transaction ids.
 */
class BookingEvents
{
    /**
     * Dedup marker: once set on a booking, mec_booking_completed will never
     * fire for it again (a booking can be confirmed, rejected and
     * re-confirmed through its life).
     */
    const TRACKED_META = 'mec_tracked_completed';

    /**
     * Idempotent registration.
     *
     * @return void
     */
    public static function register()
    {
        static $done = false;
        if ($done) return;
        $done = true;

        add_action('mec_booking_confirmed', array(__CLASS__, 'confirmed'), 10, 2);
    }

    /**
     * A booking became confirmed.
     *
     * @param int    $book_id
     * @param string $mode 'auto'|'manually'|… (unused for properties; kept for signature)
     * @return void
     */
    public static function confirmed($book_id, $mode = 'manually')
    {
        if (!is_numeric($book_id)) return;
        $book_id = (int) $book_id;
        if ($book_id <= 0) return;

        // Exactly once per booking, ever.
        if (get_post_meta($book_id, self::TRACKED_META, true)) return;
        update_post_meta($book_id, self::TRACKED_META, 1);

        $price    = (float) get_post_meta($book_id, 'mec_price', true);
        $payable  = (float) get_post_meta($book_id, 'mec_payable', true);
        $gateway  = str_replace('MEC_gateway_', '', (string) get_post_meta($book_id, 'mec_gateway', true));

        $attendees = get_post_meta($book_id, 'mec_attendees', true);
        $attendees = is_array($attendees) ? $attendees : array();

        // Distinct ticket ids among attendees (a ticket can carry several attendees).
        $ticket_ids = array();
        foreach ($attendees as $attendee)
        {
            if (is_array($attendee) && isset($attendee['id'])) $ticket_ids[] = (int) $attendee['id'];
        }
        $ticket_ids = array_unique($ticket_ids);

        // Payment status by shape, not by amount: a confirmed paid booking
        // passed gateway verification on the way here; pay-locally stays
        // offline money by definition; free is free.
        if ($price <= 0) $payment_status = 'free';
        elseif ($gateway === 'pay_locally') $payment_status = 'pending_offline';
        else $payment_status = 'paid';

        (new PostHog())->capture('mec_booking_completed', array(
            'booking_type'          => 'event',
            'is_paid'               => ($price > 0) ? 1 : 0,
            'gateway'               => ($gateway !== '') ? $gateway : null,
            'payment_status'        => $payment_status,
            'ticket_count_bucket'   => Util::bucket(count($ticket_ids)),
            'attendees_count_bucket' => Util::bucket(count($attendees)),
            'value_bucket'          => Util::price_bucket($payable > 0 ? $payable : $price),
            'currency'              => self::currency(),
        ));
    }

    /**
     * Site currency code (never an amount).
     *
     * @return string|null
     */
    protected static function currency()
    {
        if (class_exists('\MEC\Base') && method_exists('\MEC\Base', 'get_main'))
        {
            $main = \MEC\Base::get_main();
            if ($main && method_exists($main, 'get_currency')) return (string) $main->get_currency();
        }

        return null;
    }
}
