<?php


namespace MEC\SingleBuilder\Widgets\EventCategories;

use MEC\Base;
use MEC\SingleBuilder\Widgets\WidgetBase;

class EventCategories extends WidgetBase {

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
		$categories    = isset($events_detail->data->categories) ? $events_detail->data->categories : [];

		$html = '';
		if ( true === $this->is_editor_mode && empty( $categories ) ) {

			$html = '<div class="mec-content-notification"><p>'
						.'<span>'. esc_html__('To show this widget, you need to set "Category" for your latest event.', 'modern-events-calendar-lite').'</span>'
						. '<a href="https://webnus.net/dox/modern-events-calendar/categories/" target="_blank">' . esc_html__('Read More', 'modern-events-calendar-lite') . ' </a>'
					.'</p></div>';
		} elseif ( !empty($categories) ) {

			ob_start();
				echo '<div class="mec-single-event-category mec-event-meta">';
				if( isset( $atts['mec_category_show_icon'] ) && $atts['mec_category_show_icon'] ){
                    echo $this->icons->display('folder');
                }
				if( isset( $atts['mec_category_show_title'] ) && $atts['mec_category_show_title'] ){
					echo '<dt>' . Base::get_main()->m('taxonomy_categories', esc_html__('Category', 'modern-events-calendar-lite')) . '</dt>';
				}
				echo '<dl>';
				foreach ($categories as $category) {
					$icon = get_metadata('term', $category['id'], 'mec_cat_icon', true);
					if( isset( $atts['mec_category_show_link_icon'] ) && $atts['mec_category_show_link_icon'] ){
						$icon = isset($icon) && $icon != '' ? '<i class="' . esc_attr( $icon ) . ' mec-color"></i>' : '<i class="mec-fa-angle-right"></i>';
					}
					echo '<dd class="mec-events-event-categories">
						<a href="' . esc_attr( get_term_link($category['id'], 'mec_category') ) . '" class="mec-color-hover" rel="tag">' . $icon . esc_html($category['name']) . '</a></dd>';
				}
				echo '</dl></div>';
			$html = ob_get_clean();
		}

		return $html;
	}
}
