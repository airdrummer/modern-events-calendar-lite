<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_full_calendar $this */

// Include OWL Assets
$this->main->load_owl_assets();

$sed_method = $this->sed_method;
if ($sed_method == 'new') $sed_method = '0';

// Generating javascript code tpl
$javascript = '<script>
jQuery(document).ready(function()
{
    var mec_interval = setInterval(function()
    {
        // Not Visible
        if(!jQuery("#mec_skin_' . esc_js($this->id) . '").is(":visible")) return;
        
        jQuery("#mec_skin_' . esc_js($this->id) . '").mecFullCalendar(
        {
            id: "' . esc_js($this->id) . '",
            atts: "' . http_build_query(['atts' => $this->atts], '', '&') . '",
            ajax_url: "' . admin_url('admin-ajax.php', null) . '",
            sed_method: "' . esc_js($sed_method) . '",
            image_popup: "' . esc_js($this->image_popup) . '",
            sf:
            {
                container: "' . ($this->sf_status ? '#mec_search_form_' . esc_js($this->id) : '') . '",
                reset: ' . ($this->sf_reset_button ? 1 : 0) . ',
                refine: ' . ($this->sf_refine ? 1 : 0) . ',
            },
            skin: "' . esc_js($this->default_view) . '",
        });
        
        clearInterval(mec_interval);
    }, 500);
});
</script>';

// Include javascript code into the page
if ($this->main->is_ajax() or $this->main->preview()) echo MEC_kses::full($javascript);
else $this->factory->params('footer', $javascript);

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';

$dark_mode = $styling['dark_mode'] ?? '';
if ($dark_mode == 1) $set_dark = 'mec-dark-mode';
else $set_dark = '';

if ($this->sf_display_label) $label_all_set = 'mec-there-labels';
else $label_all_set = '';

do_action('mec_start_skin', $this->id);
do_action('mec_full_skin_head');
?>
<div id="mec_skin_<?php echo esc_attr($this->id); ?>"
     class="mec-wrap <?php echo esc_attr($event_colorskin . ' ' . $label_all_set . ' ' . $set_dark); ?> mec-full-calendar-wrap">

    <div class="mec-search-form mec-totalcal-box">
        <?php if ($this->sf_status): ?>
            <?php
            $sf_month_filter = ($this->sf_options['month_filter'] ?? []);
            $sf_category = ($this->sf_options['category'] ?? []);
            $sf_location = ($this->sf_options['location'] ?? []);
            $sf_organizer = ($this->sf_options['organizer'] ?? []);
            $sf_speaker = ($this->sf_options['speaker'] ?? []);
            $sf_tag = ($this->sf_options['tag'] ?? []);
            $sf_label = ($this->sf_options['label'] ?? []);
            $sf_text_search = ($this->sf_options['text_search'] ?? []);
            $sf_address_search = ($this->sf_options['address_search'] ?? []);
            $sf_event_cost = ($this->sf_options['event_cost'] ?? []);
            $sf_local_time = ($this->sf_options['time_filter'] ?? []);
            $sf_fields = ((isset($this->sf_options['fields']) and is_array($this->sf_options['fields'])) ? $this->sf_options['fields'] : []);

            $sf_month_filter_status = (isset($sf_month_filter['type']) and trim($sf_month_filter['type']));
            $sf_category_status = (isset($sf_category['type']) and trim($sf_category['type']));
            $sf_location_status = (isset($sf_location['type']) and trim($sf_location['type']));
            $sf_organizer_status = (isset($sf_organizer['type']) and trim($sf_organizer['type']));
            $sf_speaker_status = (isset($sf_speaker['type']) and trim($sf_speaker['type']));
            $sf_tag_status = (isset($sf_tag['type']) and trim($sf_tag['type']));
            $sf_label_status = (isset($sf_label['type']) and trim($sf_label['type']));
            $sf_text_search_status = (isset($sf_text_search['type']) and trim($sf_text_search['type']));
            $sf_address_search_status = (isset($sf_address_search['type']) and trim($sf_address_search['type']));
            $sf_event_cost_status = (isset($sf_event_cost['type']) and trim($sf_event_cost['type']));
            $sf_local_time_status = (isset($sf_local_time['type']) and trim($sf_local_time['type']));

            $sf_fields_status = false;
            if (is_array($sf_fields) and count($sf_fields))
            {
                foreach ($sf_fields as $sf_field) if (isset($sf_field['type']) and $sf_field['type']) $sf_fields_status = true;
            }

            // Status of Speakers Feature
            $speakers_status = isset($this->settings['speakers_status']) && $this->settings['speakers_status'];
            $sf_columns = 12;
            ?>
            <?php
            if ((!empty($sf_category) && $sf_category["type"] == 'dropdown') || (!empty($sf_location) && $sf_location["type"] == 'dropdown') || (!empty($sf_organizer) && $sf_organizer["type"] == 'dropdown') || (!empty($sf_speaker) && $sf_speaker["type"] == 'dropdown') || (!empty($sf_tag) && $sf_tag["type"] == 'dropdown') || (!empty($sf_label) && $sf_label["type"] == 'dropdown')) $wrapper_class = 'mec-dropdown-wrap';
            else $wrapper_class = '';

            if($this->sf_dropdown_method == '2') $wrapper_class .= ' mec-dropdown-enhanced';
            else $wrapper_class .= ' mec-dropdown-classic';
            ?>
            <form id="mec_search_form_<?php echo esc_attr($this->id); ?>" class="<?php echo esc_attr($wrapper_class); ?>" autocomplete="off">
                <div>

                    <?php echo apply_filters('mec_filter_fields_search_form', '', $this); ?>

                    <div class="mec-dropdown-wrap">
                        <?php if ($sf_category_status): ?>
                            <?php echo MEC_kses::form($this->sf_search_field('category', $sf_category, $this->sf_display_label)); ?>
                        <?php endif; ?>
                        <?php if ($sf_location_status): ?>
                            <?php echo MEC_kses::form($this->sf_search_field('location', $sf_location, $this->sf_display_label)); ?>
                        <?php endif; ?>
                        <?php if ($sf_organizer_status): ?>
                            <?php echo MEC_kses::form($this->sf_search_field('organizer', $sf_organizer, $this->sf_display_label)); ?>
                        <?php endif; ?>
                        <?php if ($sf_speaker_status and $speakers_status): ?>
                            <?php echo MEC_kses::form($this->sf_search_field('speaker', $sf_speaker, $this->sf_display_label)); ?>
                        <?php endif; ?>
                        <?php if ($sf_tag_status): ?>
                            <?php echo MEC_kses::form($this->sf_search_field('tag', $sf_tag, $this->sf_display_label)); ?>
                        <?php endif; ?>
                        <?php if ($sf_label_status): ?>
                            <?php echo MEC_kses::form($this->sf_search_field('label', $sf_label, $this->sf_display_label)); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($sf_fields_status): ?>
                    <div class="mec-event-fields-row">
                        <?php echo MEC_kses::form($this->sf_search_field('fields', $sf_fields, $this->sf_display_label)); ?>
                    </div>
                <?php endif; ?>
                <div>
                    <?php if ($sf_address_search_status): ?>
                        <?php echo MEC_kses::form($this->sf_search_field('address_search', $sf_address_search, $this->sf_display_label)); ?>
                    <?php endif; ?>
                    <?php if ($sf_event_cost_status): $sf_columns -= 3; ?>
                        <?php echo MEC_kses::form($this->sf_search_field('event_cost', $sf_event_cost, $this->sf_display_label)); ?>
                    <?php endif; ?>
                </div>
                <div class="mec-full-calendar-search-ends">
                    <?php if ($sf_text_search_status): ?>
                        <?php echo MEC_kses::form($this->sf_search_field('text_search', $sf_text_search, $this->sf_display_label)); ?>
                    <?php endif; ?>
                    <?php if ($sf_month_filter_status): $sf_columns -= 3; ?>
                        <?php echo MEC_kses::form($this->sf_search_field('month_filter', $sf_month_filter, $this->sf_display_label)); ?>
                    <?php endif; ?>
                    <?php if ($sf_local_time_status): $sf_columns -= 3; ?>
                        <?php echo MEC_kses::form($this->sf_search_field('time_filter', $sf_local_time, $this->sf_display_label)); ?>
                    <?php endif; ?>
                    <?php if ($this->sf_reset_button): ?>
                        <div class="mec-search-reset-button">
                            <button class="button mec-button" id="mec_search_form_<?php echo esc_attr($this->id); ?>_reset"
                                    type="button"><?php echo esc_html__('Reset', 'modern-events-calendar-lite'); ?></button>
                        </div>
                    <?php endif; ?>
                    <div class="mec-tab-loader">
                        <div class="mec-totalcal-view">
                            <?php if ($this->yearly): ?><span
                                class="mec-totalcal-yearlyview<?php if ($this->default_view == 'yearly') echo ' mec-totalcalview-selected'; ?>"
                                data-skin="yearly"><?php esc_html_e('Yearly', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                            <?php if ($this->monthly): ?><span
                                class="mec-totalcal-monthlyview<?php if ($this->default_view == 'monthly') echo ' mec-totalcalview-selected'; ?>"
                                data-skin="monthly"><?php esc_html_e('Monthly', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                            <?php if ($this->weekly): ?><span
                                class="mec-totalcal-weeklyview<?php if ($this->default_view == 'weekly') echo ' mec-totalcalview-selected'; ?>"
                                data-skin="weekly"><?php esc_html_e('Weekly', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                            <?php if ($this->daily): ?><span
                                class="mec-totalcal-dailyview<?php if ($this->default_view == 'daily') echo ' mec-totalcalview-selected'; ?>"
                                data-skin="daily"><?php esc_html_e('Daily', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                            <?php if ($this->list): ?><span
                                class="mec-totalcal-listview<?php if ($this->default_view == 'list') echo ' mec-totalcalview-selected'; ?>"
                                data-skin="list"><?php esc_html_e('List', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                            <?php if ($this->grid): ?><span
                                class="mec-totalcal-gridview<?php if ($this->default_view == 'grid') echo ' mec-totalcalview-selected'; ?>"
                                data-skin="grid"><?php esc_html_e('Grid', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                            <?php if ($this->tile): ?><span
                                class="mec-totalcal-tileview<?php if ($this->default_view == 'tile') echo ' mec-totalcalview-selected'; ?>"
                                data-skin="tile"><?php esc_html_e('Tile', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <div class="mec-tab-loader">
                <div class="mec-totalcal-view">
                    <?php if ($this->yearly): ?><span
                        class="mec-totalcal-yearlyview<?php if ($this->default_view == 'yearly') echo ' mec-totalcalview-selected'; ?>"
                        data-skin="yearly"><?php esc_html_e('Yearly', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                    <?php if ($this->monthly): ?><span
                        class="mec-totalcal-monthlyview<?php if ($this->default_view == 'monthly') echo ' mec-totalcalview-selected'; ?>"
                        data-skin="monthly"><?php esc_html_e('Monthly', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                    <?php if ($this->weekly): ?><span
                        class="mec-totalcal-weeklyview<?php if ($this->default_view == 'weekly') echo ' mec-totalcalview-selected'; ?>"
                        data-skin="weekly"><?php esc_html_e('Weekly', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                    <?php if ($this->daily): ?><span
                        class="mec-totalcal-dailyview<?php if ($this->default_view == 'daily') echo ' mec-totalcalview-selected'; ?>"
                        data-skin="daily"><?php esc_html_e('Daily', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                    <?php if ($this->list): ?><span
                        class="mec-totalcal-listview<?php if ($this->default_view == 'list') echo ' mec-totalcalview-selected'; ?>"
                        data-skin="list"><?php esc_html_e('List', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                    <?php if ($this->grid): ?><span
                        class="mec-totalcal-gridview<?php if ($this->default_view == 'grid') echo ' mec-totalcalview-selected'; ?>"
                        data-skin="grid"><?php esc_html_e('Grid', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                    <?php if ($this->tile): ?><span
                        class="mec-totalcal-tileview<?php if ($this->default_view == 'tile') echo ' mec-totalcalview-selected'; ?>"
                        data-skin="tile"><?php esc_html_e('Tile', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                </div>
            </div>
        <?php endif; // this is for if($this->sf_status): ?>
    </div>

    <div id="mec_full_calendar_container_<?php echo esc_attr($this->id); ?>" class="mec-full-calendar-skin-container">
        <?php echo MEC_kses::full($this->load_skin($this->default_view)); ?>
    </div>

    <?php echo $this->subscribe_to_calendar(); ?>
</div>
