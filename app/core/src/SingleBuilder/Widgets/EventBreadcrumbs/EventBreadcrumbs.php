<?php

namespace MEC\SingleBuilder\Widgets\EventBreadcrumbs;

use MEC\Base;
use MEC\SingleBuilder\Widgets\WidgetBase;

class EventBreadcrumbs extends WidgetBase {

	public function get_breadcrumb_html($event_id = 0){

		if(!$event_id){

			$event_id = $this->get_event_id();
		}

		if(!$event_id){
			return '';
		}

        $single         = new \MEC_skin_single();

		ob_start();
			echo '<div class="mec-breadcrumbs">';
				$single->display_breadcrumb_widget( $event_id );
			echo '</div>';
		return ob_get_clean();
	}

	/**
	 *  Get HTML Output
	 *
	 * @param int $event_id
	 * @param array $atts
	 *
	 * @return string
	 */
	public function output( $event_id = 0, $atts = array() ){

		if( !$event_id ){

			$event_id = $this->get_event_id();
		}

		if(!$event_id){
			return '';
		}

		$settings = $this->settings;
		$events_detail = $this->get_event_detail($event_id);

		if ( true === $this->is_editor_mode && ( !isset($settings['breadcrumbs']) || !$settings['breadcrumbs'] ) ) {

			$html = '<div class="mec-content-notification"><p>'
					.'<span>'. esc_html__('To show this widget, you need to enable "Breadcrumbs" module.', 'modern-events-calendar-lite').'</span>'
					. '<a href="https://webnus.net/dox/modern-events-calendar/single-event-settings/#3_Breadcrumbs" target="_blank">' . esc_html__('Read More', 'modern-events-calendar-lite') . ' </a>'
				.'</p></div>';
		} else {

			$html = $this->get_breadcrumb_html($event_id);
		}

		return $html;
	}
}
