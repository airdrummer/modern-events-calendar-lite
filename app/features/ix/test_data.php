<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_ix $this */

$categories = get_terms(array(
    'taxonomy' => 'mec_category',
    'hide_empty' => 0,
));
$tags = get_terms(array(
    'taxonomy' => apply_filters('mec_taxonomy_tag', ''),
    'hide_empty' => 0,
));
$locations = get_terms(array(
    'taxonomy' => 'mec_location',
    'hide_empty' => 0,
));
$organizers = get_terms(array(
    'taxonomy' => 'mec_organizer',
    'hide_empty' => 0,
));

$active_tab = 'MEC-test-data';
include MEC::import('app.features.ix.header', true, true);
?>
<div class="wns-be-container mec-full">
    <div class="wns-be-main">
        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <div class="mec-container">
                    <div class="import-content">
                        <h2 class="mec-form-subtitle mec-page-section"><?php esc_html_e('Test Data Generator', 'modern-events-calendar-lite'); ?></h2>
                        <form id="mec_test_data_generator_form" action="<?php echo esc_url($this->main->get_full_url()); ?>" method="POST" enctype="multipart/form-data">
                            <p class="description"><?php echo esc_html__("You can use following form to generate some test events.", 'modern-events-calendar-lite'); ?></p>
                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label">
                                    <label for="mec_test_data_number"><?php esc_html_e("Number of Events", 'modern-events-calendar-lite'); ?></label>
                                </div>
                                <div class="mec-field__control">
                                    <input type="number" id="mec_test_data_number" name="number" value="3" min="1" max="20" step="1">
                                </div>
                            </div>
                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label">
                                    <label for="mec_test_data_category"><?php echo $this->main->m('taxonomy_categories', esc_html__("Categories", 'modern-events-calendar-lite')); ?></label>
                                </div>
                                <div class="mec-field__control">
                                    <select name="category" id="mec_test_data_category">
                                        <option value="">-----</option>
                                        <option value="random"><?php esc_html_e('Random from Existing Items', 'modern-events-calendar-lite'); ?></option>
                                        <option value="generate"><?php esc_html_e('Generate and Assign', 'modern-events-calendar-lite'); ?></option>
                                        <?php foreach($categories as $category): ?>
                                        <option value="<?php echo esc_attr($category->term_id); ?>"><?php echo $category->name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label">
                                    <label for="mec_test_data_tag"><?php echo $this->main->m('taxonomy_tags', esc_html__("Tags", 'modern-events-calendar-lite')); ?></label>
                                </div>
                                <div class="mec-field__control">
                                    <select name="tag" id="mec_test_data_tag">
                                        <option value="">-----</option>
                                        <option value="random"><?php esc_html_e('Random from Existing Items', 'modern-events-calendar-lite'); ?></option>
                                        <option value="generate"><?php esc_html_e('Generate and Assign', 'modern-events-calendar-lite'); ?></option>
                                        <?php foreach($tags as $tag): ?>
                                        <option value="<?php echo esc_attr($tag->term_id); ?>"><?php echo $tag->name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label">
                                    <label for="mec_test_data_location"><?php echo $this->main->m('taxonomy_locations', esc_html__("Locations", 'modern-events-calendar-lite')); ?></label>
                                </div>
                                <div class="mec-field__control">
                                    <select name="location" id="mec_test_data_location">
                                        <option value="">-----</option>
                                        <option value="random"><?php esc_html_e('Random from Existing Items', 'modern-events-calendar-lite'); ?></option>
                                        <option value="generate"><?php esc_html_e('Generate and Assign', 'modern-events-calendar-lite'); ?></option>
                                        <?php foreach($locations as $location): ?>
                                        <option value="<?php echo esc_attr($location->term_id); ?>"><?php echo $location->name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label">
                                    <label for="mec_test_data_organizer"><?php echo $this->main->m('taxonomy_organizers', esc_html__("Organizers", 'modern-events-calendar-lite')); ?></label>
                                </div>
                                <div class="mec-field__control">
                                    <select name="organizer" id="mec_test_data_organizer">
                                        <option value="">-----</option>
                                        <option value="random"><?php esc_html_e('Random from Existing Items', 'modern-events-calendar-lite'); ?></option>
                                        <option value="generate"><?php esc_html_e('Generate and Assign', 'modern-events-calendar-lite'); ?></option>
                                        <?php foreach($organizers as $organizer): ?>
                                        <option value="<?php echo esc_attr($organizer->term_id); ?>"><?php echo $organizer->name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mec-form-row mec-field mec-ix-actions">
                                <div class="mec-field__control">
                                    <input type="hidden" name="mec-ix-action" value="test-data-generation-start">
                                    <?php wp_nonce_field('mec_test_event'); ?>
                                    <button class="button button-primary"><?php esc_html_e('Generate Events', 'modern-events-calendar-lite'); ?></button>
                                </div>
                            </div>
                        </form>

                        <?php if($this->action == 'test-data-generation-start'): ?>
                        <div class="mec-ix-test-data-started">
                            <?php if($this->response['success'] == 0): ?>
                            <div class="mec-error"><?php echo MEC_kses::element($this->response['message']); ?></div>
                            <?php else: ?>
                            <div class="mec-success"><?php echo MEC_kses::element($this->response['message']); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php
                        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
                        do_action('mec_import_export_page', $tab);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
