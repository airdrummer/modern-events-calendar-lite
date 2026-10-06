<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_timetable $this */

// Get layout path
$render_path = $this->get_render_path();

// Generate Events
ob_start();
include $render_path;
$date_events = ob_get_clean();

$navigator_html = '';
if($this->next_previous_button)
{
    $number_of_days = max(1, (int) $this->number_of_days);
    $previous_week = strtotime('-7 days', strtotime($this->start_date));
    $next_week = strtotime('+7 days', strtotime($this->start_date));
    $previous_week_end = strtotime('+'.($number_of_days - 1).' days', $previous_week);
    $current_week_end = strtotime('+'.($number_of_days - 1).' days', strtotime($this->start_date));

    if(($this->atts['show_past_events'] ?? false) or $previous_week_end >= current_time('timestamp'))
    {
        $navigator_html .= '<div class="mec-previous-month mec-previous-week mec-load-week mec-color" data-mec-start-date="'.esc_attr(date('Y-m-d', $previous_week)).'"><a href="#" class="mec-load-week-link" aria-label="'.esc_attr__('Previous week', 'modern-events-calendar-lite').'"><i class="mec-sl-angle-left"></i></a></div>';
    }

    $navigator_html .= '<h4 class="mec-month-label">'.esc_html(sprintf('%s - %s', $this->main->date_i18n('M j', strtotime($this->start_date)), $this->main->date_i18n('M j, Y', $current_week_end))).'</h4>';

    if(!$this->show_only_expired_events or $next_week <= current_time('timestamp'))
    {
        $navigator_html .= '<div class="mec-next-month mec-next-week mec-load-week mec-color" data-mec-start-date="'.esc_attr(date('Y-m-d', $next_week)).'"><a href="#" class="mec-load-week-link" aria-label="'.esc_attr__('Next week', 'modern-events-calendar-lite').'"><i class="mec-sl-angle-right"></i></a></div>';
    }
}

// Return the data if called by AJAX
if(isset($this->atts['return_items']) and $this->atts['return_items'])
{
    echo json_encode(array(
        'date_events' => $date_events,
        'navigator' => $navigator_html,
    ));
    exit;
}

$sed_method = $this->sed_method;
if($sed_method == 'new') $sed_method = '0';

// Generating javascript code tpl
$javascript = '<script>
jQuery(document).ready(function()
{
    var mec_interval = setInterval(function()
    {
        // Not Visible
        if(!jQuery("#mec_skin_'.esc_js($this->id).'").is(":visible")) return;
        
        jQuery("#mec_skin_'.esc_js($this->id).'").mecWeeklyProgram(
        {
            id: "'.esc_js($this->id).'",
            ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
            atts: "'.http_build_query(array('atts' => $this->atts), '', '&').'",
            start_date: "'.esc_js($this->start_date).'",
            week_navigator: '.($this->next_previous_button ? 1 : 0).',
            sed_method: "'.esc_js($sed_method).'",
            image_popup: "'.esc_js($this->image_popup).'",
            sf:
            {
                container: "'.($this->sf_status ? '#mec_search_form_'.esc_js($this->id) : '').'",
                reset: '.($this->sf_reset_button ? 1 : 0).',
                refine: '.($this->sf_refine ? 1 : 0).',
            },
        });
        
        clearInterval(mec_interval);
    }, 500);
});
</script>';

// Include javascript code into the page
if($this->main->is_ajax() or $this->main->preview()) echo MEC_kses::full($javascript);
else $this->factory->params('footer', $javascript);

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';
?>
<div id="mec_skin_<?php echo esc_attr($this->id); ?>" class="mec-timetable-wrap mec-wrap <?php echo esc_attr($event_colorskin . ' ' . $this->html_class); ?>">

    <?php if($this->sf_status) echo MEC_kses::full($this->sf_search_form()); ?>

    <?php if($this->next_previous_button): ?>
    <div class="mec-timetable-week-navigator mec-calendar-a-month mec-clear">
        <?php echo MEC_kses::page($navigator_html); ?>
    </div>
    <?php endif; ?>

    <div id="mec_skin_events_<?php echo esc_attr($this->id); ?>">
        <?php echo MEC_kses::full($date_events); ?>
    </div>

</div>
