<?php

namespace MEC\SingleBuilder\Widgets\EventCost;

use MEC\Base;
use MEC\SingleBuilder\Widgets\WidgetBase;

class EventCost extends WidgetBase
{

    /**
     *  Get HTML Output
     *
     * @param int $event_id
     * @param array $atts
     *
     * @return string
     */
    public function output($event_id = 0, $atts = array())
    {

        if (!$event_id) {

            $event_id = $this->get_event_id();
        }

        if (!$event_id) {
            return '';
        }

        $settings = $this->settings;
        $events_detail = $this->get_event_detail($event_id);
        $html = '';
        if (true === $this->is_editor_mode && (isset($events_detail->data->meta['mec_cost_auto_calculate']) ? $events_detail->data->meta['mec_cost_auto_calculate'] == '0' : true )) {
            if (!(isset($events_detail->data->meta['mec_cost']) && $events_detail->data->meta['mec_cost'] != '')) {
                $html = '<div class="mec-content-notification"><p>'
                    . '<span>' . esc_html__('To show this widget, you need to set "Cost" for your latest event.', 'modern-events-calendar-lite') . '</span>'
                    . '<a href="https://webnus.net/dox/modern-events-calendar/add-event/#Cost" target="_blank">' . esc_html__('Read More', 'modern-events-calendar-lite') . ' </a>'
                    . '</p></div>';
            }else{
                ob_start();

                $cost = \MEC\Base::get_main()->get_event_cost($events_detail);
                if ($cost) {
                    echo '<div class="mec-event-meta">';
                    ?>
                    <div class="mec-event-cost">
                        <?php if (isset($atts['mec_cost_show_icon']) && $atts['mec_cost_show_icon']) {
                            echo $this->icons->display('wallet');
                        } ?>
                        <?php if (isset($atts['mec_cost_show_title']) && $atts['mec_cost_show_title']) { ?>
                            <h3 class="mec-cost"><?php echo esc_html(\MEC\Base::get_main()->m('cost', esc_html__('Cost', 'modern-events-calendar-lite'))); ?></h3>
                        <?php } ?>
                        <dl>
                            <dd class="mec-events-event-cost">
                                <?php
                                if (is_numeric($cost)) {

                                    $rendered_cost = \MEC\Base::get_main()->render_price($cost, $events_detail->ID);
                                } else {

                                    $rendered_cost = $cost;
                                }

                                echo apply_filters('mec_display_event_cost', $rendered_cost, $cost);
                                ?>
                            </dd>
                        </dl>
                    </div>
                    <?php
                    echo '</div>';
                }
            }
        } else {
            ob_start();

            $cost = \MEC\Base::get_main()->get_event_cost($events_detail);
            if ($cost) {
                echo '<div class="mec-event-meta">';
                ?>
                <div class="mec-event-cost">
                    <?php if (isset($atts['mec_cost_show_icon']) && $atts['mec_cost_show_icon']) {
                        echo $this->icons->display('wallet');
                    } ?>
                    <?php if (isset($atts['mec_cost_show_title']) && $atts['mec_cost_show_title']) { ?>
                        <h3 class="mec-cost"><?php echo esc_html(\MEC\Base::get_main()->m('cost', esc_html__('Cost', 'modern-events-calendar-lite'))); ?></h3>
                    <?php } ?>
                    <dl>
                        <dd class="mec-events-event-cost">
                            <?php
                            if (is_numeric($cost)) {

                                $rendered_cost = \MEC\Base::get_main()->render_price($cost, $events_detail->ID);
                            } else {

                                $rendered_cost = $cost;
                            }

                            echo apply_filters('mec_display_event_cost', $rendered_cost, $cost);
                            ?>
                        </dd>
                    </dl>
                </div>
                <?php
                echo '</div>';
            }

            $html = ob_get_clean();
        }

        return $html;
    }
}
