<?php

namespace MEC\SingleBuilder\Widgets\EventSpeakers;

use MEC\Base;
use MEC\SingleBuilder\Widgets\WidgetBase;

class EventSpeakers extends WidgetBase {

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
		$event_detail = $this->get_event_detail($event_id);
		$speakers = (isset($event_detail->data->speakers) and is_array($event_detail->data->speakers)) ? $event_detail->data->speakers : [];

		$html = '';
		if ( true === $this->is_editor_mode && ( empty($speakers) || (!isset($settings['speakers_status']) || !$settings['speakers_status']) ) ) {

			$html = '<div class="mec-content-notification"><p>'
					.'<span>'. esc_html__('To show this widget, you need to enable "Speakers" module.', 'modern-events-calendar-lite').'</span>'
					. '<a href="https://webnus.net/dox/modern-events-calendar/event-modules/#Speakers" target="_blank">' . esc_html__('Read More', 'modern-events-calendar-lite') . ' </a>'
				.'</p></div>';
		} elseif ( true === $this->is_editor_mode && isset($settings['speakers_status']) && $settings['speakers_status'] ) {

			$html = Base::get_main()->module('speakers.details', array('event'=>$event_detail));
		} else {

			ob_start();
				// Event Speaker
				echo Base::get_main()->module('speakers.details', array('event'=>$event_detail));
				?>
				<script>
					// Fix modal speaker in some themes
					jQuery( ".mec-speaker-avatar-dialog a, .mec-schedule-speakers a" ).click(function(e) {
						e.preventDefault();
						var id =  jQuery(this).attr('href');
						lity(id);

						return false;
					});
					// Fix modal booking in some themes
					function openBookingModal(){
						jQuery( ".mec-booking-button.mec-booking-data-lity" ).on('click',function(e) {
							e.preventDefault();
							var book_id =  jQuery(this).attr('href');
							Lity.close();
							lity(book_id);

							return false;
						});
					}
				</script>
			<?php
			$html = ob_get_clean();
		}

		return $html;
	}
}
