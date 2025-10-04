<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Appointments class.
 * @author Webnus <info@webnus.net>
 */
class MEC_appointments extends MEC_base
{
    /**
     * @var MEC_main
     */
    public $main;

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

        // MEC settings
        $this->settings = $this->main->get_settings();
    }

    public function get_entity_type(int $event_id): string
    {
        $entity_type = get_post_meta($event_id, 'mec_entity_type', true);
        if (!in_array($entity_type, ['event', 'appointment'])) $entity_type = 'event';

        return $entity_type;
    }

    public function save($event_id, $data)
    {
        // Entity Type
        $entity_type = isset($data['entity_type']) && in_array($data['entity_type'], ['event', 'appointment']) ? $data['entity_type'] : 'event';

        if ($entity_type === 'event')
        {
            delete_post_meta($event_id, 'mec_appointments');
            return;
        }

        // Appointment Config
        $config = isset($data['appointments']) && is_array($data['appointments']) ? $data['appointments'] : [];

        // Remove Temp Keys
        if (isset($config['availability']) && is_array($config['availability']))
        {
            foreach ($config['availability'] as &$day)
            {
                if (isset($day[':t:'])) unset($day[':t:']);
            }

            unset($day); // break reference
        }

        $adjusted_map = [];
        if (isset($config['adjusted_availability']) && is_array($config['adjusted_availability']))
        {
            foreach ($config['adjusted_availability'] as $k => &$day)
            {
                if (isset($day[':t:'])) unset($day[':t:']);

                if (empty($day['date']))
                {
                    unset($config['adjusted_availability'][$k]);
                    continue;
                }

                $day['date'] = sanitize_text_field($day['date']);

                $periods = [];
                foreach ($day as $i => $slot)
                {
                    if ($i === 'date' || !is_array($slot)) continue;
                    $periods[] = $slot;
                }

                $adjusted_map[$day['date']] = $periods;
            }

            unset($day);
        }

        $availability = $config['availability'] ?? [];
        $duration = isset($config['duration']) && is_numeric($config['duration']) ? (int) $config['duration'] : 10;
        $buffer = isset($config['buffer']) && is_numeric($config['buffer']) ? (int) $config['buffer'] : 0;

        $config['duration'] = $duration;
        $config['buffer'] = $buffer;

        $start_date = null;
        $start_hour = null;
        $start_minutes = null;
        $start_ampm = null;
        $end_date = null;
        $end_hour = null;
        $end_minutes = null;
        $end_ampm = null;

        $slots = $this->generate_slots($availability, $duration, $buffer);
        $adjusted_slots = [];
        foreach ($adjusted_map as $date => $periods)
        {
            $g = $this->generate_slots([0 => $periods], $duration, $buffer);
            $adjusted_slots[$date] = $g[0] ?? [];
        }

        $today = current_time('Y-m-d');
        $today_ts = strtotime($today);

        $in_days = [];
        for ($i = 0; $i < 180; $i++)
        {
            $date = wp_date('Y-m-d', strtotime("+$i days", $today_ts));

            if (isset($adjusted_slots[$date]))
            {
                $day_slots = $adjusted_slots[$date];
                if (!count($day_slots)) continue;
            }
            else
            {
                // Get PHP weekday index (0 = Monday, 6 = Sunday)
                $weekday = (int) wp_date('N', strtotime($date)) - 1;
                if (empty($slots[$weekday])) continue;

                $day_slots = $slots[$weekday];
            }

            // Day Slots
            foreach ($day_slots as $day_slot)
            {
                $st = strtotime($date .' '.$day_slot['start']);
                $et = strtotime($date .' '.$day_slot['end']);

                if (is_null($start_date)) $start_date = $date;
                if (is_null($start_hour)) $start_hour = date('h', $st);
                if (is_null($start_minutes)) $start_minutes = date('i', $st);
                if (is_null($start_ampm)) $start_ampm = date('A', $st);
                if (is_null($end_date)) $end_date = $date;
                if (is_null($end_hour)) $end_hour = date('h', $et);
                if (is_null($end_minutes)) $end_minutes = date('i', $et);
                if (is_null($end_ampm)) $end_ampm = date('A', $et);

                $start_time = date('h-i-A', $st);
                $end_time = date('h-i-A', $et);

                $in_days[] = "$date:$date:$start_time:$end_time";
            }
        }

        // To avoid first date duplication
        array_shift($in_days);

        $in_days_str = '';
        foreach ($in_days as $key => $in_day)
        {
            if (is_numeric($key)) $in_days_str .= $in_day . ',';
        }

        $in_days_str = trim($in_days_str, ', ');
        $repeat_type = 'custom_days';

        if (isset($this->settings['time_format']) and $this->settings['time_format'] == 24)
        {
            $day_start_seconds = $this->main->time_to_seconds($this->main->to_24hours($start_hour, null, 'start'), $start_minutes);
            $day_end_seconds = $this->main->time_to_seconds($this->main->to_24hours($end_hour, null), $end_minutes);
        }
        else
        {
            $day_start_seconds = $this->main->time_to_seconds($this->main->to_24hours($start_hour, $start_ampm, 'start'), $start_minutes);
            $day_end_seconds = $this->main->time_to_seconds($this->main->to_24hours($end_hour, $end_ampm), $end_minutes);
        }

        if ($end_date === $start_date && $day_end_seconds < $day_start_seconds)
        {
            $day_end_seconds = $day_start_seconds;

            $end_hour = $start_hour;
            $end_minutes = $start_minutes;
            $end_ampm = $start_ampm;
        }

        $start_datetime = $start_date.' '.$start_hour.':'.$start_minutes.' '.$start_ampm;
        $end_datetime = $end_date.' '.$end_hour.':'.$end_minutes.' '.$end_ampm;

        update_post_meta($event_id, 'mec_date', [
            'start' => [
                'date' => $start_date,
                'hour' => date('g', strtotime($start_datetime)),
                'minutes' => $start_minutes,
                'ampm' => $start_ampm,
            ],
            'end' => [
                'date' => $end_date,
                'hour' => date('g', strtotime($end_datetime)),
                'minutes' => $end_minutes,
                'ampm' => $end_ampm,
            ],
            'comment' => '',
            'repeat' => [
                'status' => 1,
                'type' => $repeat_type,
                'interval' => '',
                'advanced' => '',
                'end' => 'never',
                'end_at_date' => '',
                'end_at_occurrences' => 10,
            ],
        ]);

        update_post_meta($event_id, 'mec_start_date', $start_date);
        update_post_meta($event_id, 'mec_start_time_hour', $start_hour);
        update_post_meta($event_id, 'mec_start_time_minutes', $start_minutes);
        update_post_meta($event_id, 'mec_start_time_ampm', $start_ampm);
        update_post_meta($event_id, 'mec_start_day_seconds', $day_start_seconds);
        update_post_meta($event_id, 'mec_start_datetime', $start_datetime);

        update_post_meta($event_id, 'mec_end_date', $end_date);
        update_post_meta($event_id, 'mec_end_time_hour', $end_hour);
        update_post_meta($event_id, 'mec_end_time_minutes', $end_minutes);
        update_post_meta($event_id, 'mec_end_time_ampm', $end_ampm);
        update_post_meta($event_id, 'mec_end_day_seconds', $day_end_seconds);
        update_post_meta($event_id, 'mec_end_datetime', $end_datetime);
        update_post_meta($event_id, 'mec_repeat_status', 1);
        update_post_meta($event_id, 'mec_repeat_type', $repeat_type);
        update_post_meta($event_id, 'mec_repeat_interval', null);
        update_post_meta($event_id, 'mec_public', 0);
        update_post_meta($event_id, 'mec_in_days', $in_days_str);

        $db = $this->getDB();
        $db->q("UPDATE `#__mec_events` SET `repeat`=1, `rinterval`=null, `days`='".esc_sql($in_days_str)."' WHERE `post_id`='".esc_sql($event_id)."'");

        update_post_meta($event_id, 'mec_appointments', $config);

        // Update Schedule
        $schedule = $this->getSchedule();
        $schedule->reschedule($event_id, 500);
    }

    public function generate_slots(array $availability, int $duration = 10, int $buffer = 0): array
    {
        $result = [];

        if (!count($availability)) return $result;
        if ($duration <= 0) return $result;

        foreach ($availability as $d => $slots)
        {
            $result[$d] = [];
            if (!is_array($slots)) continue;

            foreach ($slots as $slot)
            {
                if (
                    !isset($slot['start']['hour'], $slot['start']['minutes']) ||
                    !isset($slot['end']['hour'], $slot['end']['minutes'])
                )
                {
                    continue;
                }

                // Convert start and end time to timestamps
                if (isset($this->settings['time_format']) and $this->settings['time_format'] == 24)
                {
                    $start = mktime($this->main->to_24hours($slot['start']['hour'], null, 'start'), $slot['start']['minutes'], 0, 1, 1, 2000);
                    $end = mktime($this->main->to_24hours($slot['end']['hour'], null), $slot['end']['minutes'], 0, 1, 1, 2000);
                }
                else
                {
                    $start = mktime($this->main->to_24hours($slot['start']['hour'], $slot['start']['ampm'], 'start'), $slot['start']['minutes'], 0, 1, 1, 2000);
                    $end = mktime($this->main->to_24hours($slot['end']['hour'], $slot['end']['ampm']), $slot['end']['minutes'], 0, 1, 1, 2000);
                }

                $appointments = [];
                while ($start + ($duration * 60) <= $end)
                {
                    $appointments[] = [
                        'start' => date('H:i', $start),
                        'end' => date('H:i', $start + ($duration * 60)),
                    ];

                    // Move start time forward by duration + buffer
                    $start += ($duration + $buffer) * 60;
                }

                $result[$d] = array_merge($result[$d], $appointments);
            }
        }

        return $result;
    }
}
