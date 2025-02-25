<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var $this MEC_feature_books **/
/** @var int $step_skipped */
/** @var int $translated_event_id */
/** @var stdClass $event **/
/** @var string $date **/
/** @var string $uniqueid **/
/** @var bool $display_progress_bar **/
/** @var array $tickets **/
/** @var array $all_dates **/

$event_id = $event->ID;
$requested_event_id = $event->requested_id ?? $event_id;

$reg_fields = \MEC\Base::get_main()->get_reg_fields($event_id, $translated_event_id);
$bfixed_fields = \MEC\Base::get_main()->get_bfixed_fields($event_id, $translated_event_id);

$custom_view_fields = apply_filters('mec_have_custom_view_fields', false, $bfixed_fields, 'booking_fixed_fields', $event_id);

$mainClass      = new \MEC_main();
$set            = $mainClass->get_options();

if(!$custom_view_fields ||
    isset($set['default_form']['form_id']) && is_plugin_active('mec-form-builder/mec-form-builder.php'))
{
    \MEC\BookingForm\Attendees::output(
        $event,
        $date,
        $tickets,
        $reg_fields,
        $bfixed_fields,
        $uniqueid,
        $all_dates
    );
}
else
{
    do_action(
        'mec_booking_attendee_form_custom_view',
        $event,
        $event_id,
        array(
            'date' => $date,
            'uniqueid' => $uniqueid,
            'tickets' => $tickets,
        )
    );
}


