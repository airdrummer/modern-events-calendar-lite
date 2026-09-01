<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var WP_Post $post */
/** @var MEC_feature_mec $this */

/**
 * Webnus MEC taxonomy walker class.
 * @author Webnus <info@webnus.net>
 */
class MEC_tax_walker extends Walker_Category_Checklist
{
    public function start_lvl( &$output, $depth = 0, $args = array() ) {
        $output .= "";
    }

    public function end_lvl( &$output, $depth = 0, $args = array() ) {
        $output .= "";
    }

    public function start_el(&$output, $data_object, $depth = 0, $args = array(), $current_object_id = 0)
    {
		$args['popular_cats'] = empty($args['popular_cats']) ? array() : $args['popular_cats'];
		$class = in_array($data_object->term_id, $args['popular_cats']) ? ' class="popular-category"' : '';

		$args['selected_cats'] = empty($args['selected_cats']) ? array() : $args['selected_cats'];

		if(!empty($args['list_only']))
        {
			$aria_cheched = 'false';
			$inner_class = 'category';

			if(in_array($data_object->term_id, $args['selected_cats']))
            {
				$inner_class .= ' selected';
				$aria_cheched = 'true';
			}
            // Show only Terms with Posts
            if($data_object->count)
            {
                $output .= "\n".'<li '.$class.'>'.
                    '<div class="'.esc_attr($inner_class).'" data-term-id='.esc_attr($data_object->term_id).' tabindex="0" role="checkbox" aria-checked="'.esc_attr($aria_cheched).'">'.
                    esc_html(apply_filters('the_category', $data_object->name)).'</div>';
            }
		}
        else
        {
            // Show only Terms with Posts
            if($data_object->count)
            {
                $output .= "\n<option value='".esc_attr($data_object->term_id)."'";
                if(in_array($data_object->term_id, $args['selected_cats'])) $output .= " selected='selected'";
                $output .= ">".esc_html(apply_filters('the_category', $data_object->name));
            }
		}
	}

    public function end_el( &$output, $data_object, $depth = 0, $args = array() ) {
        $output .= "</option>\n";
    }
}

$MEC_tax_walker = new MEC_tax_walker();
?>
<div class="mec-calendar-metabox">
    <?php
        // Add a nonce field, so we can check for it later.
        wp_nonce_field('mec_calendar_data', 'mec_calendar_nonce');
    ?>
    <div id="mec_meta_box_calendar_no_filter" class="mec-util-hidden">
        <p><?php esc_html_e('No filter options applicable for this skin.', 'modern-events-calendar-lite'); ?></p>
    </div>
    <div id="mec_meta_box_calendar_filter">
        <div class="mec-create-shortcode-tabs-wrap">
            <div class="mec-create-shortcode-tabs-left">
                <a class="mec-create-shortcode-tabs-link mec-tab-active" data-href="mec_select_entity_type" href="#"><?php echo esc_html__('Entity Type' , 'modern-events-calendar-lite'); ?></a>
                <a class="mec-create-shortcode-tabs-link" data-href="mec_select_categories" href="#"><?php echo esc_html__('Categories' , 'modern-events-calendar-lite'); ?></a>
                <a class="mec-create-shortcode-tabs-link" data-href="mec_select_locations" href="#"><?php echo esc_html__('Locations' , 'modern-events-calendar-lite'); ?></a>
                <?php if(!isset($this->settings['organizers_status']) || $this->settings['organizers_status']): ?>
                <a class="mec-create-shortcode-tabs-link" data-href="mec_select_organizers" href="#"><?php echo esc_html__('Organizers' , 'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
                <?php if(isset($this->settings['speakers_status']) && $this->settings['speakers_status']): ?>
                    <a class="mec-create-shortcode-tabs-link" data-href="mec_select_speakers" href="#"><?php echo esc_html__('Speakers' , 'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
                <a class="mec-create-shortcode-tabs-link" data-href="mec_select_labels" href="#"><?php echo esc_html__('Labels' , 'modern-events-calendar-lite'); ?></a>
                <?php if($this->getPro() && isset($this->settings['sponsors_status']) and $this->settings['sponsors_status']): ?>
                <a class="mec-create-shortcode-tabs-link" data-href="mec_select_sponsors" href="#"><?php echo esc_html__('Sponsors' , 'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
                <a class="mec-create-shortcode-tabs-link" data-href="mec_select_tags" href="#"><?php echo esc_html__('Tags' , 'modern-events-calendar-lite'); ?></a>
                <a class="mec-create-shortcode-tabs-link" data-href="mec_select_authors" href="#"><?php echo esc_html__('Authors' , 'modern-events-calendar-lite'); ?></a>
                <a class="mec-create-shortcode-tabs-link" data-href="mec_select_occurrences" href="#"><?php echo esc_html__('Occurrences' , 'modern-events-calendar-lite'); ?></a>
                <a class="mec-create-shortcode-tabs-link" data-href="mec_select_holding_statuses" href="#"><?php echo esc_html__('Expired / Ongoing' , 'modern-events-calendar-lite'); ?></a>
                <?php do_action( 'mec_shortcode_filters_tab_links', $post ); ?>
            </div>
            <div class="mec-add-booking-tabs-right">
                <div class="mec-meta-box-fields mec-create-shortcode-tab-content mec-tab-active mec-panel" id="mec_select_entity_type">
                    <?php $entity_type_filter = get_post_meta($post->ID, 'entity_type_filter', true); ?>
                    <?php if (!in_array($entity_type_filter, ['all', 'event', 'appointment'], true)) $entity_type_filter = 'all'; ?>
                    <div class="mec-panel__head">
                        <h4 class="mec-panel__title"><?php esc_html_e('Entity Type', 'modern-events-calendar-lite'); ?></h4>
                    </div>
                    <div class="mec-form-row mec-field mec-field--choices">
                        <div class="mec-field__control">
                            <label class="mec-check" for="mec_entity_type_all">
                                <input type="radio" name="mec[entity_type_filter]" value="all" id="mec_entity_type_all" <?php checked($entity_type_filter, 'all'); ?>>
                                <span><?php esc_html_e('Both Events and Appointments', 'modern-events-calendar-lite'); ?></span>
                            </label>
                            <label class="mec-check" for="mec_entity_type_event">
                                <input type="radio" name="mec[entity_type_filter]" value="event" id="mec_entity_type_event" <?php checked($entity_type_filter, 'event'); ?>>
                                <span><?php esc_html_e('Events', 'modern-events-calendar-lite'); ?></span>
                            </label>
                            <label class="mec-check" for="mec_entity_type_appointment">
                                <input type="radio" name="mec[entity_type_filter]" value="appointment" id="mec_entity_type_appointment" <?php checked($entity_type_filter, 'appointment'); ?>>
                                <span><?php esc_html_e('Appointments', 'modern-events-calendar-lite'); ?></span>
                            </label>
                            <p class="mec-field__help"><?php esc_html_e('Limit the calendar to regular events, appointments, or both.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="mec-meta-box-fields mec-create-shortcode-tab-content mec-panel" id="mec_select_categories">
                    <div class="mec-panel__head">
                        <h4 class="mec-panel__title"><?php echo esc_html($this->main->m('taxonomy_categories', esc_html__('Categories', 'modern-events-calendar-lite'))); ?></h4>
                    </div>
                    <div class="mec-form-row mec-field">
                        <div class="mec-field__label">
                            <span><?php echo esc_html__('Include', 'modern-events-calendar-lite'); ?></span>
                        </div>
                        <div class="mec-field__control">
                            <select name="mec_tax_input[mec_category][]" multiple="multiple" title="<?php esc_attr_e('Include categories', 'modern-events-calendar-lite'); ?>">
                                <?php
                                    $selected_categories = explode(',', get_post_meta($post->ID, 'category', true));
                                    wp_terms_checklist(0, array(
                                        'descendants_and_self'=>0,
                                        'taxonomy'=>'mec_category',
                                        'selected_cats'=>$selected_categories,
                                        'popular_cats'=>false,
                                        'checked_ontop'=>false,
                                        'walker'=>$MEC_tax_walker
                                    ));
                                ?>
                            </select>
                            <p class="mec-field__help"><?php esc_html_e('Limit the calendar to events from the selected categories. Leave empty to include everything.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                    <div class="mec-form-row mec-field">
                        <div class="mec-field__label">
                            <span><?php echo esc_html__('Exclude', 'modern-events-calendar-lite'); ?></span>
                        </div>
                        <div class="mec-field__control">
                            <select name="mec_tax_input[mec_ex_category][]" multiple="multiple" title="<?php esc_attr_e('Exclude categories', 'modern-events-calendar-lite'); ?>">
                                <?php
                                    $ex_selected_categories = explode(',', get_post_meta($post->ID, 'ex_category', true));
                                    wp_terms_checklist(0, array(
                                        'descendants_and_self'=>0,
                                        'taxonomy'=>'mec_category',
                                        'selected_cats'=>$ex_selected_categories,
                                        'popular_cats'=>false,
                                        'checked_ontop'=>false,
                                        'walker'=>$MEC_tax_walker
                                    ));
                                ?>
                            </select>
                            <p class="mec-field__help"><?php esc_html_e('Hide events from the selected categories even if included above.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="mec-meta-box-fields mec-create-shortcode-tab-content mec-panel" id="mec_select_locations">
                    <div class="mec-panel__head">
                        <h4 class="mec-panel__title"><?php echo esc_html($this->main->m('taxonomy_locations', esc_html__('Locations', 'modern-events-calendar-lite'))); ?></h4>
                    </div>
                    <div class="mec-form-row mec-field">
                        <div class="mec-field__label">
                            <span><?php echo esc_html__('Include', 'modern-events-calendar-lite'); ?></span>
                        </div>
                        <div class="mec-field__control">
                            <select name="mec_tax_input[mec_location][]" multiple="multiple" title="<?php esc_attr_e('Include locations', 'modern-events-calendar-lite'); ?>">
                                <?php
                                    $selected_locations = explode(',', get_post_meta($post->ID, 'location', true));
                                    wp_terms_checklist(0, array(
                                        'descendants_and_self'=>0,
                                        'taxonomy'=>'mec_location',
                                        'selected_cats'=>$selected_locations,
                                        'popular_cats'=>false,
                                        'checked_ontop'=>false,
                                        'walker'=>$MEC_tax_walker,
                                    ));
                                ?>
                            </select>
                            <p class="mec-field__help"><?php esc_html_e('Limit the calendar to events from the selected locations. Leave empty to include everything.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                    <div class="mec-form-row mec-field">
                        <div class="mec-field__label">
                            <span><?php echo esc_html__('Exclude', 'modern-events-calendar-lite'); ?></span>
                        </div>
                        <div class="mec-field__control">
                            <select name="mec_tax_input[mec_ex_location][]" multiple="multiple" title="<?php esc_attr_e('Exclude locations', 'modern-events-calendar-lite'); ?>">
                                <?php
                                    $ex_selected_locations = explode(',', get_post_meta($post->ID, 'ex_location', true));
                                    wp_terms_checklist(0, array(
                                        'descendants_and_self'=>0,
                                        'taxonomy'=>'mec_location',
                                        'selected_cats'=>$ex_selected_locations,
                                        'popular_cats'=>false,
                                        'checked_ontop'=>false,
                                        'walker'=>$MEC_tax_walker
                                    ));
                                ?>
                            </select>
                            <p class="mec-field__help"><?php esc_html_e('Hide events from the selected locations even if included above.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                </div>
                <?php if(!isset($this->settings['organizers_status']) || $this->settings['organizers_status']): ?>
                <div class="mec-meta-box-fields mec-create-shortcode-tab-content mec-panel" id="mec_select_organizers">
                    <div class="mec-panel__head">
                        <h4 class="mec-panel__title"><?php echo esc_html($this->main->m('taxonomy_organizers', esc_html__('Organizers', 'modern-events-calendar-lite'))); ?></h4>
                    </div>
                    <div class="mec-form-row mec-field">
                        <div class="mec-field__label">
                            <span><?php echo esc_html__('Include', 'modern-events-calendar-lite'); ?></span>
                        </div>
                        <div class="mec-field__control">
                            <select name="mec_tax_input[mec_organizer][]" multiple="multiple" title="<?php esc_attr_e('Include organizers', 'modern-events-calendar-lite'); ?>">
                                <?php
                                    $selected_organizers = explode(',', get_post_meta($post->ID, 'organizer', true));
                                    wp_terms_checklist(0, array(
                                        'descendants_and_self'=>0,
                                        'taxonomy'=>'mec_organizer',
                                        'selected_cats'=>$selected_organizers,
                                        'popular_cats'=>false,
                                        'checked_ontop'=>false,
                                        'walker'=>$MEC_tax_walker
                                    ));
                                ?>
                            </select>
                            <p class="mec-field__help"><?php esc_html_e('Limit the calendar to events from the selected organizers. Leave empty to include everything.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                    <div class="mec-form-row mec-field">
                        <div class="mec-field__label">
                            <span><?php echo esc_html__('Exclude', 'modern-events-calendar-lite'); ?></span>
                        </div>
                        <div class="mec-field__control">
                            <select name="mec_tax_input[mec_ex_organizer][]" multiple="multiple" title="<?php esc_attr_e('Exclude organizers', 'modern-events-calendar-lite'); ?>">
                                <?php
                                    $ex_selected_organizers = explode(',', get_post_meta($post->ID, 'ex_organizer', true));
                                    wp_terms_checklist(0, array(
                                        'descendants_and_self'=>0,
                                        'taxonomy'=>'mec_organizer',
                                        'selected_cats'=>$ex_selected_organizers,
                                        'popular_cats'=>false,
                                        'checked_ontop'=>false,
                                        'walker'=>$MEC_tax_walker
                                    ));
                                ?>
                            </select>
                            <p class="mec-field__help"><?php esc_html_e('Hide events from the selected organizers even if included above.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if(isset($this->settings['speakers_status']) && $this->settings['speakers_status']): ?>
                    <div class="mec-meta-box-fields mec-create-shortcode-tab-content mec-panel" id="mec_select_speakers">
                        <div class="mec-panel__head">
                            <h4 class="mec-panel__title"><?php echo esc_html($this->main->m('taxonomy_speakers', esc_html__('Speakers', 'modern-events-calendar-lite'))); ?></h4>
                        </div>
                        <div class="mec-form-row mec-field">
                            <div class="mec-field__label">
                                <span><?php echo esc_html__('Include', 'modern-events-calendar-lite'); ?></span>
                            </div>
                            <div class="mec-field__control">
                                <select name="mec_tax_input[mec_speaker][]" multiple="multiple" title="<?php esc_attr_e('Include speakers', 'modern-events-calendar-lite'); ?>">
                                    <?php
                                    $selected_speakers = explode(',', get_post_meta($post->ID, 'speaker', true));
                                    wp_terms_checklist(0, array(
                                        'descendants_and_self'=>0,
                                        'taxonomy'=>'mec_speaker',
                                        'selected_cats'=>$selected_speakers,
                                        'popular_cats'=>false,
                                        'checked_ontop'=>false,
                                        'walker'=>$MEC_tax_walker
                                    ));
                                    ?>
                                </select>
                                <p class="mec-field__help"><?php esc_html_e('Limit the calendar to events from the selected speakers. Leave empty to include everything.', 'modern-events-calendar-lite'); ?></p>
                            </div>
                        </div>
                        <div class="mec-form-row mec-field">
                            <div class="mec-field__label">
                                <span><?php echo esc_html__('Exclude', 'modern-events-calendar-lite'); ?></span>
                            </div>
                            <div class="mec-field__control">
                                <select name="mec_tax_input[mec_ex_speaker][]" multiple="multiple" title="<?php esc_attr_e('Exclude speakers', 'modern-events-calendar-lite'); ?>">
                                    <?php
                                    $ex_selected_speakers = explode(',', get_post_meta($post->ID, 'ex_speaker', true));
                                    wp_terms_checklist(0, array(
                                        'descendants_and_self'=>0,
                                        'taxonomy'=>'mec_speaker',
                                        'selected_cats'=>$ex_selected_speakers,
                                        'popular_cats'=>false,
                                        'checked_ontop'=>false,
                                        'walker'=>$MEC_tax_walker
                                    ));
                                    ?>
                                </select>
                                <p class="mec-field__help"><?php esc_html_e('Hide events from the selected speakers even if included above.', 'modern-events-calendar-lite'); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="mec-meta-box-fields mec-create-shortcode-tab-content mec-panel" id="mec_select_labels">
                    <div class="mec-panel__head">
                        <h4 class="mec-panel__title"><?php echo esc_html($this->main->m('taxonomy_labels', esc_html__('Labels', 'modern-events-calendar-lite'))); ?></h4>
                    </div>
                    <div class="mec-form-row mec-field">
                        <div class="mec-field__label">
                            <span><?php echo esc_html__('Include', 'modern-events-calendar-lite'); ?></span>
                        </div>
                        <div class="mec-field__control">
                            <select name="mec_tax_input[mec_label][]" multiple="multiple" title="<?php echo esc_attr($this->main->m('taxonomy_labels', esc_html__('Labels', 'modern-events-calendar-lite'))); ?>">
                            <?php
                                $selected_labels = explode(',', get_post_meta($post->ID, 'label', true));
                                wp_terms_checklist(0, array(
                                    'descendants_and_self'=>0,
                                    'taxonomy'=>'mec_label',
                                    'selected_cats'=>$selected_labels,
                                    'popular_cats'=>false,
                                    'checked_ontop'=>false,
                                    'walker'=>$MEC_tax_walker
                                ));
                            ?>
                            </select>
                            <p class="mec-field__help"><?php esc_html_e('Limit the calendar to events with the selected labels. Leave empty to include everything.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                    <div class="mec-form-row mec-field">
                        <div class="mec-field__label">
                            <span><?php echo esc_html__('Exclude', 'modern-events-calendar-lite'); ?></span>
                        </div>
                        <div class="mec-field__control">
                            <select name="mec_tax_input[mec_ex_labels][]" multiple="multiple" title="<?php esc_attr_e('Exclude labels', 'modern-events-calendar-lite'); ?>">
                                <?php
                                $ex_selected_labels = explode(',', get_post_meta($post->ID, 'ex_label', true));
                                wp_terms_checklist(0, array(
                                    'descendants_and_self'=>0,
                                    'taxonomy'=>'mec_label',
                                    'selected_cats'=>$ex_selected_labels,
                                    'popular_cats'=>false,
                                    'checked_ontop'=>false,
                                    'walker'=>$MEC_tax_walker
                                ));
                                ?>
                            </select>
                            <p class="mec-field__help"><?php esc_html_e('Hide events with the selected labels even if included above.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                </div>
                <?php if($this->getPro() && isset($this->settings['sponsors_status']) and $this->settings['sponsors_status']): ?>
                <div class="mec-meta-box-fields mec-create-shortcode-tab-content mec-panel" id="mec_select_sponsors">
                    <div class="mec-panel__head">
                        <h4 class="mec-panel__title"><?php echo esc_html($this->main->m('taxonomy_sponsor', esc_html__('Sponsors', 'modern-events-calendar-lite'))); ?></h4>
                    </div>
                    <div class="mec-form-row mec-field">
                        <div class="mec-field__label">
                            <span><?php echo esc_html__('Include', 'modern-events-calendar-lite'); ?></span>
                        </div>
                        <div class="mec-field__control">
                            <select name="mec_tax_input[mec_sponsor][]" multiple="multiple" title="<?php echo esc_html($this->main->m('taxonomy_sponsor', esc_html__('Sponsors', 'modern-events-calendar-lite'))); ?>">
                                <?php
                                $selected_sponsors = explode(',', get_post_meta($post->ID, 'sponsor', true));
                                wp_terms_checklist(0, array(
                                    'descendants_and_self'=>0,
                                    'taxonomy'=>'mec_sponsor',
                                    'selected_cats'=>$selected_sponsors,
                                    'popular_cats'=>false,
                                    'checked_ontop'=>false,
                                    'walker'=>$MEC_tax_walker,
                                ));
                                ?>
                            </select>
                            <p class="mec-field__help"><?php esc_html_e('Limit the calendar to events from the selected sponsors. Leave empty to include everything.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="mec-meta-box-fields mec-create-shortcode-tab-content mec-panel" id="mec_select_tags">
                    <div class="mec-panel__head">
                        <h4 class="mec-panel__title"><?php esc_html_e('Tags', 'modern-events-calendar-lite'); ?></h4>
                    </div>
                    <div class="mec-form-row mec-field">
                        <div class="mec-field__label">
                            <span><?php echo esc_html__('Include', 'modern-events-calendar-lite'); ?></span>
                        </div>
                        <div class="mec-field__control">
                            <select name="mec_tax_input[mec_tag][]" multiple="multiple" title="<?php echo esc_html__('Tags', 'modern-events-calendar-lite'); ?>">
                                <?php
                                    $selected_tags = explode(',', get_post_meta($post->ID, 'tag', true));
                                    $tag_terms = get_terms([
                                        'taxonomy'   => apply_filters('mec_taxonomy_tag', ''),
                                        'hide_empty' => true,
                                    ]);
                                ?>
                                <?php foreach($tag_terms as $tag_term): ?>
                                <option value="<?php echo esc_attr($tag_term->name); ?>" <?php echo in_array($tag_term->name, $selected_tags) ? 'selected' : ''; ?>><?php echo esc_html($tag_term->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="mec-field__help"><?php esc_html_e('Limit the calendar to events with the selected tags. Leave empty to include everything.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                    <div class="mec-form-row mec-field">
                        <div class="mec-field__label">
                            <span><?php echo esc_html__('Exclude', 'modern-events-calendar-lite'); ?></span>
                        </div>
                        <div class="mec-field__control">
                            <select name="mec_tax_input[mec_ex_tags][]" multiple="multiple" title="<?php echo esc_html__('Tags', 'modern-events-calendar-lite'); ?>">
                                <?php
                                    $ex_selected_tags = explode(',', get_post_meta($post->ID, 'ex_tag', true));
                                ?>
                                <?php foreach($tag_terms as $tag_term): ?>
                                <option value="<?php echo esc_attr($tag_term->name); ?>" <?php echo in_array($tag_term->name, $ex_selected_tags) ? 'selected' : ''; ?>><?php echo esc_html($tag_term->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="mec-field__help"><?php esc_html_e('Hide events with the selected tags even if included above.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="mec-meta-box-fields mec-create-shortcode-tab-content mec-panel" id="mec_select_authors">
                    <div class="mec-panel__head">
                        <h4 class="mec-panel__title"><?php esc_html_e('Authors', 'modern-events-calendar-lite'); ?></h4>
                    </div>
                    <div class="mec-form-row mec-field">
                        <div class="mec-field__label">
                            <span><?php echo esc_html__('Include', 'modern-events-calendar-lite'); ?></span>
                        </div>
                        <div class="mec-field__control">
                            <select name="mec_tax_input[mec_author][]" multiple="multiple" title="<?php esc_attr__('Authors', 'modern-events-calendar-lite'); ?>">
                            <?php
                                $selected_authors = explode(',', get_post_meta($post->ID, 'author', true));
                                $authors = get_users(array(
                                    'role__not_in'=>array('subscriber', 'contributor'),
                                    'orderby'=>'post_count',
                                    'order'=>'DESC',
                                    'number'=>'-1',
                                    'fields'=>array('ID', 'display_name')
                                ));

                                foreach($authors as $author)
                                {
                                    ?>
                                    <option <?php if(in_array($author->ID, $selected_authors)) echo 'selected="selected"'; ?> value="<?php echo esc_attr($author->ID); ?>"><?php echo esc_html($author->display_name); ?></option>
                                    <?php
                                }
                            ?>
                            </select>
                            <p class="mec-field__help"><?php esc_html_e('Limit the calendar to events by the selected authors. Leave empty to include all authors.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                    <div class="mec-form-row mec-field">
                        <div class="mec-field__label">
                            <span><?php echo esc_html__('Exclude', 'modern-events-calendar-lite'); ?></span>
                        </div>
                        <div class="mec-field__control">
                            <select name="mec_tax_input[mec_ex_authors][]" multiple="multiple" title="<?php esc_attr_e('Exclude authors', 'modern-events-calendar-lite'); ?>">
                                <?php
                                $ex_selected_authors = explode(',', get_post_meta($post->ID, 'ex_author', true));
                                foreach($authors as $author)
                                {
                                    ?>
                                    <option <?php if(in_array($author->ID, $ex_selected_authors)) echo 'selected="selected"'; ?> value="<?php echo esc_attr($author->ID); ?>"><?php echo esc_html($author->display_name); ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                            <p class="mec-field__help"><?php esc_html_e('Hide events by the selected authors even if included above.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="mec-meta-box-fields mec-create-shortcode-tab-content mec-panel" id="mec_select_occurrences">
                    <div class="mec-panel__head">
                        <h4 class="mec-panel__title"><?php esc_html_e('Occurrences', 'modern-events-calendar-lite'); ?></h4>
                    </div>
                    <?php $show_only_one_occurrence = get_post_meta($post->ID, 'show_only_one_occurrence', true); ?>
                    <div class="mec-form-row mec-field mec-field--check">
                        <label class="mec-check" for="show_only_one_occurrence">
                            <input type="hidden" name="mec[show_only_one_occurrence]" value="0" />
                            <input type="checkbox" name="mec[show_only_one_occurrence]" id="show_only_one_occurrence" value="1" <?php if($show_only_one_occurrence == 1) echo 'checked="checked"'; ?> />
                            <span><?php esc_html_e('Show only one occurrence', 'modern-events-calendar-lite'); ?></span>
                        </label>
                        <p class="mec-field__help"><?php esc_html_e('Display a single occurrence of each repeating event instead of all its dates.', 'modern-events-calendar-lite'); ?></p>
                    </div>
                </div>
                <?php do_action('mec_shortcode_filters', $post->ID, $MEC_tax_walker); ?>
                <div class="mec-meta-box-fields mec-create-shortcode-tab-content mec-panel" id="mec_select_holding_statuses">
                    <div class="mec-panel__head">
                        <h4 class="mec-panel__title"><?php esc_html_e('Expired Events', 'modern-events-calendar-lite'); ?></h4>
                    </div>
                    <?php $show_past_events = get_post_meta($post->ID, 'show_past_events', true); ?>
                    <div class="mec-form-row mec-field mec-field--check">
                        <label class="mec-check" for="mec_show_past_events">
                            <input type="hidden" name="mec[show_past_events]" value="0" />
                            <input type="checkbox" name="mec[show_past_events]" class="mec-checkbox-toggle" id="mec_show_past_events" value="1" <?php if($show_past_events == '' or $show_past_events == 1) echo 'checked="checked"'; ?> />
                            <span><?php esc_html_e('Include Expired Events', 'modern-events-calendar-lite'); ?></span>
                        </label>
                        <p class="mec-field__help"><?php esc_html_e('Show past events alongside upcoming ones, starting from the selected start date.', 'modern-events-calendar-lite'); ?></p>
                    </div>
                    <div id="mec_date_only_past_filter">
                        <?php $show_only_past_events = get_post_meta($post->ID, 'show_only_past_events', true); ?>
                        <div class="mec-form-row mec-field mec-field--check">
                            <label class="mec-check" for="mec_show_only_past_events">
                                <input type="hidden" name="mec[show_only_past_events]" value="0" />
                                <input type="checkbox" name="mec[show_only_past_events]" class="mec-checkbox-toggle" id="mec_show_only_past_events" value="1" <?php if($show_only_past_events == 1) echo 'checked="checked"'; ?> />
                                <span><?php esc_html_e('Show Only Expired Events', 'modern-events-calendar-lite'); ?></span>
                            </label>
                            <p class="mec-field__help mec-field__help--warn"><?php esc_html_e('List only past events, counting backward from the selected start date.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                    <div id="mec_date_ongoing_filter">
                        <div class="mec-panel__head">
                            <h4 class="mec-panel__title"><?php esc_html_e('Ongoing Events', 'modern-events-calendar-lite'); ?></h4>
                        </div>
                        <?php $show_ongoing_events = get_post_meta($post->ID, 'show_ongoing_events', true); ?>
                        <div class="mec-form-row mec-field mec-field--check">
                            <label class="mec-check" for="mec_show_ongoing_events">
                                <input type="hidden" name="mec[show_ongoing_events]" value="0" />
                                <input type="checkbox" name="mec[show_ongoing_events]" class="mec-checkbox-toggle" id="mec_show_ongoing_events" value="1" <?php if($show_ongoing_events == 1) echo 'checked="checked"'; ?> />
                                <span><?php esc_html_e('Include Ongoing Events', 'modern-events-calendar-lite'); ?></span>
                            </label>
                            <p class="mec-field__help"><?php esc_html_e('Include events that are currently running on List, Grid, Agenda and Timeline skins.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                        <?php $show_only_ongoing_events = get_post_meta($post->ID, 'show_only_ongoing_events', true); ?>
                        <div class="mec-form-row mec-field mec-field--check">
                            <label class="mec-check" for="mec_show_only_ongoing_events">
                                <input type="hidden" name="mec[show_only_ongoing_events]" value="0" />
                                <input type="checkbox" name="mec[show_only_ongoing_events]" class="mec-checkbox-toggle" id="mec_show_only_ongoing_events" value="1" <?php if($show_only_ongoing_events == 1) echo 'checked="checked"'; ?> />
                                <span><?php esc_html_e('Show Only Ongoing Events', 'modern-events-calendar-lite'); ?></span>
                            </label>
                            <p class="mec-field__help"><?php esc_html_e('Show only events that are currently running on List, Grid, Agenda and Timeline skins.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                </div>
                <?php do_action( 'mec_shortcode_filters_content', $post ); ?>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(function($)
{
    $(".mec-create-shortcode-tabs-link").on("click", function(e)
    {
        e.preventDefault();
        var href = $(this).attr("data-href");

        $(".mec-create-shortcode-tab-content,.mec-create-shortcode-tabs-link").removeClass("mec-tab-active");
        $(this).addClass("mec-tab-active");
        $("#" + href ).addClass("mec-tab-active");
    });

    // All tabs remain visible regardless of entity type selection
});
</script>
