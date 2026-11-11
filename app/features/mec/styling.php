<?php
/** no direct access **/
defined('MECEXEC') or die();

$styling = $this->main->get_styling();
$fonts = include MEC::import('app.features.mec.webfonts.webfonts', true, true);

$google_fonts = [];
$google_fonts['none'] = array(
	'label'=>esc_html__('Inherit from theme', 'modern-events-calendar-lite'),
	'variants'=>array('regular'),
	'subsets'=>array(),
	'category'=>'',
    'value'=>'',
);

if(is_array($fonts))
{
	foreach($fonts['items'] as $font)
    {
        $google_fonts[$font['family']] = array(
            'label'=>$font['family'],
            'variants'=>$font['variants'],
            'subsets'=>$font['subsets'],
            'category'=>$font['category'],
        );
    }
}
?>
<div class="mec-page-head">
    <span class="mec-page-eyebrow"><?php esc_html_e('Modern Events Calendar', 'modern-events-calendar-lite'); ?></span>
    <h1><span class="mec-page-mark" aria-hidden="true"><span class="dashicons dashicons-admin-appearance"></span></span><?php esc_html_e('MEC Settings', 'modern-events-calendar-lite'); ?><span class="mec-page-title-sep" aria-hidden="true">/</span><?php esc_html_e('Appearance', 'modern-events-calendar-lite'); ?></h1>
    <p class="mec-page-lead"><?php esc_html_e('Customize colors, typography and border radius for event calendars.', 'modern-events-calendar-lite'); ?></p>
    <?php $this->main->render_settings_page_meta(); ?>
</div>
<hr class="wp-header-end" />
<div class="wns-be-container wns-be-container-sticky">

    <div id="wns-be-infobar">
        <div class="mec-infobar-crumb">
            <span class="c-root"><?php esc_html_e('MEC Settings', 'modern-events-calendar-lite'); ?></span>
            <span class="c-sep">/</span>
            <span class="c-tab"><?php esc_html_e('Appearance', 'modern-events-calendar-lite'); ?></span>
        </div>
        <div class="mec-search-settings-wrap">
            <i class="mec-sl-magnifier"></i>
            <input id="mec-search-settings" type="text" placeholder="<?php esc_html_e('Search...' , 'modern-events-calendar-lite'); ?>">
        </div>
        <button type="submit" class="dpr-btn dpr-save-btn" form="mec_styling_form"><?php esc_html_e('Save Changes', 'modern-events-calendar-lite'); ?></button>
    </div>

    <div class="wns-be-sidebar">
        <?php $this->main->get_sidebar_menu('styling'); ?>
    </div>

    <div class="wns-be-main styling-settings">

        <div id="wns-be-notification"></div>

        <div id="wns-be-content">
            <div class="wns-be-group-tab">

                <div class="mec-container">
                    <form id="mec_styling_form" data-mec-settings-form="1">
                        <div class="mec-options-fields">
                            <h2 class="mec-form-subtitle mec-page-section"><?php esc_html_e('Styling', 'modern-events-calendar-lite'); ?></h2>
                        <!-- Colorskin -->
                            <h5 class="mec-panel__title"><?php esc_html_e('Color Skin', 'modern-events-calendar-lite'); ?></h5>
                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label"><label><?php esc_html_e('Predefined Color Skin', 'modern-events-calendar-lite'); ?></label></div><div class="mec-field__control">
                                    <ul class="mec-image-select-wrap">
                                        <?php
                                        $colorskins = array(
                                            '#40d9f1'=>'mec-colorskin-1',
                                            '#0093d0'=>'mec-colorskin-2',
                                            '#e53f51'=>'mec-colorskin-3',
                                            '#f1c40f'=>'mec-colorskin-4',
                                            '#e64883'=>'mec-colorskin-5',
                                            '#45ab48'=>'mec-colorskin-6',
                                            '#9661ab'=>'mec-colorskin-7',
                                            '#0aad80'=>'mec-colorskin-8',
                                            '#0ab1f0'=>'mec-colorskin-9',
                                            '#ff5a00'=>'mec-colorskin-10',
                                            '#c3512f'=>'mec-colorskin-11',
                                            '#55606e'=>'mec-colorskin-12',
                                            '#fe8178'=>'mec-colorskin-13',
                                            '#7c6853'=>'mec-colorskin-14',
                                            '#bed431'=>'mec-colorskin-15',
                                            '#2d5c88'=>'mec-colorskin-16',
                                            '#77da55'=>'mec-colorskin-17',
                                            '#2997ab'=>'mec-colorskin-18',
                                            '#734854'=>'mec-colorskin-19',
                                            '#a81010'=>'mec-colorskin-20',
                                            '#4ccfad'=>'mec-colorskin-21',
                                            '#3a609f'=>'mec-colorskin-22',
                                            '#333333'=>'mec-colorskin-23',
                                            '#D2D2D2'=>'mec-colorskin-24',
                                            '#636363'=>'mec-colorskin-25',
                                            );

                                            foreach($colorskins as $colorskin=>$values): ?>
                                            <li class="mec-image-select">
                                                <label for="<?php echo esc_attr($values); ?>">
                                                    <input type="radio" id="<?php echo esc_attr($values); ?>" name="mec[styling][mec_colorskin]" value="<?php echo esc_attr($colorskin); ?>" <?php if(isset($styling['mec_colorskin']) && ($styling['mec_colorskin'] == $colorskin)) echo 'checked="checked"'; ?>>
                                                    <span class="<?php echo esc_attr($values); ?>"></span>
                                                </label>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label"><label for="mec_settings_color"><?php esc_html_e('Custom Color Skin', 'modern-events-calendar-lite'); ?></label></div><div class="mec-field__control">
                                    <input type="text" class="wp-color-picker-field" id="mec_settings_color" name="mec[styling][color]" value="<?php echo (isset($styling['color']) ? esc_attr($styling['color']) : ''); ?>" data-default-color="" />
                                </div>
                                <p class="mec-field__help"><?php esc_attr_e("If you want to select a predefined color skin, you must clear the color of this item", 'modern-events-calendar-lite'); ?></p></div>
                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label"><label for="mec_styling_primary_border_radius"><?php esc_html_e('Primary border-radius', 'modern-events-calendar-lite'); ?></label></div><div class="mec-field__control">
                                    <input type="text" id="mec_styling_primary_border_radius" name="mec[styling][primary_border_radius]" placeholder="12" value="<?php echo ((isset($styling['primary_border_radius']) and trim($styling['primary_border_radius']) != '') ? $styling['primary_border_radius'] : ''); ?>" />
                                    <div class="mec-tooltip">
                                        <div class="box left">
                                            <span class="title"><?php esc_html_e('Primary border-radius', 'modern-events-calendar-lite'); ?></span>
                                            <div class="content"><p><?php esc_attr_e('Customize the border-radius for wrappers, event items and event modules', 'modern-events-calendar-lite'); ?></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label"><label for="mec_styling_secondary_border_radius"><?php esc_html_e('Secondary border-radius', 'modern-events-calendar-lite'); ?></label></div><div class="mec-field__control">
                                    <input type="text" id="mec_styling_secondary_border_radius" name="mec[styling][secondary_border_radius]" placeholder="3" value="<?php echo ((isset($styling['secondary_border_radius']) and trim($styling['secondary_border_radius']) != '') ? $styling['secondary_border_radius'] : ''); ?>" />
                                    <div class="mec-tooltip">
                                        <div class="box left">
                                            <span class="title"><?php esc_html_e('Secondary border-radius', 'modern-events-calendar-lite'); ?></span>
                                            <div class="content"><p><?php esc_attr_e('Customize the border-radius buttons and other smaller elements.', 'modern-events-calendar-lite'); ?></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="mec-form-row mec-field mec-field--check">
                                <div class="mec-field__control">
                                    <label class="mec-check" for="mec_styling_dark_mode"><input type="hidden" name="mec[styling][dark_mode]" value="0" />
                                        <input value="1" type="checkbox" id="mec_styling_dark_mode" name="mec[styling][dark_mode]" <?php if(isset($styling['dark_mode']) and $styling['dark_mode']) echo 'checked="checked"'; ?> /><span><?php esc_html_e('Frontend Dark Mode', 'modern-events-calendar-lite'); ?></span>
                                    </label>
                                    <div class="mec-tooltip">
                                        <div class="box top">
                                            <span class="title"><?php esc_html_e('Frontend Dark Mode', 'modern-events-calendar-lite'); ?></span>
                                            <div class="content"><p><?php esc_attr_e('Enable this option to turn on the dark mode for calendars, single event pages and other public-facing MEC pages.', 'modern-events-calendar-lite'); ?><a href="https://webnus.net/dox/modern-events-calendar/appearance-settings/#3-_Dark_Mode/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </div>
                                </div>
                            </div>

                                                        <div class="mec-form-row mec-field mec-field--check">
                                <div class="mec-field__control">
                                    <label class="mec-check" for="mec_styling_admin_dark_mode"><input type="hidden" name="mec[styling][admin_dark_mode]" value="0" />
                                        <input value="1" type="checkbox" id="mec_styling_admin_dark_mode" name="mec[styling][admin_dark_mode]" <?php if($this->main->is_admin_dark_mode()) echo 'checked="checked"'; ?> /><span><?php esc_html_e('Admin Dark Mode', 'modern-events-calendar-lite'); ?></span>
                                    </label>
                                    <div class="mec-tooltip">
                                        <div class="box top">
                                            <span class="title"><?php esc_html_e('Admin Dark Mode', 'modern-events-calendar-lite'); ?></span>
                                            <div class="content"><p><?php esc_attr_e('Enable this option to use the dark theme on MEC pages inside the WordPress dashboard. It does not affect your website visitors.', 'modern-events-calendar-lite'); ?></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Advanced Options -->
                            <h5 class="mec-panel__title"><?php esc_html_e('Advanced Color Options (shortcodes)', 'modern-events-calendar-lite'); ?></h5>
                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label"><label for="mec_settings_title_color"><?php esc_html_e('Title', 'modern-events-calendar-lite'); ?></label></div><div class="mec-field__control">
                                    <input type="text" class="wp-color-picker-field" id="mec_settings_title_color" name="mec[styling][title_color]" value="<?php echo (isset($styling['title_color']) ? esc_attr($styling['title_color']) : ''); ?>" data-default-color="" />
                                </div>
                            </div>

                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label"><label for="mec_settings_title_color_hover"><?php esc_html_e('Title Hover', 'modern-events-calendar-lite'); ?></label></div><div class="mec-field__control">
                                    <input type="text" class="wp-color-picker-field" id="mec_settings_title_color_hover" name="mec[styling][title_color_hover]" value="<?php echo (isset($styling['title_color_hover']) ? esc_attr($styling['title_color_hover']) : ''); ?>" data-default-color="" />
                                </div>
                            </div>

                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label"><label for="mec_settings_content_color"><?php esc_html_e('Content', 'modern-events-calendar-lite'); ?></label></div><div class="mec-field__control">
                                    <input type="text" class="wp-color-picker-field" id="mec_settings_content_color" name="mec[styling][content_color]" value="<?php echo (isset($styling['content_color']) ? esc_attr($styling['content_color']) : ''); ?>" data-default-color="" />
                                </div>
                            </div>

                            <!-- Typography -->
                            <h5 class="mec-panel__title"><?php esc_html_e('Typography', 'modern-events-calendar-lite'); ?></h5>
                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label"><label for="mec_h_fontfamily"><?php esc_html_e('Heading (Events Title) Font Family', 'modern-events-calendar-lite'); ?></label></div><div class="mec-field__control">

                                    <select class="mec-p-fontfamily" name="mec[styling][mec_h_fontfamily]" id="mec_h_fontfamily">
                                        <?php
                                        foreach($google_fonts as $google_font)
                                        {
                                            $variants = '';
                                            foreach($google_font['variants'] as $key=>$variant)
                                            {
                                                $variants .= $variant;
                                                if(next($google_font['variants']) == true) $variants .= ",";
                                            }

                                            $value = (isset($google_font['value']) ? $google_font['value'] : '['. $google_font['label'] .','. $variants .']');
                                            if($value == '['.esc_html__('Default Font', 'modern-events-calendar-lite').',regular]' or $value == '['.esc_html__('Inherit from theme', 'modern-events-calendar-lite').',regular]') $value = '';
                                            ?>
                                            <option value="<?php echo esc_attr($value); ?>" <?php if(isset($styling['mec_h_fontfamily']) and ($styling['mec_h_fontfamily'] == $value)) echo 'selected="selected"'; ?>><?php echo esc_html($google_font['label']); ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>

                                </div>
                            </div>

                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label"><label for="mec_p_fontfamily"><?php esc_html_e('Paragraph Font Family', 'modern-events-calendar-lite'); ?></label></div><div class="mec-field__control">

                                    <select class="mec-p-fontfamily" name="mec[styling][mec_p_fontfamily]" id="mec_p_fontfamily">
                                        <?php
                                        foreach($google_fonts as $google_font)
                                        {
                                            $variants = '';
                                            foreach($google_font['variants'] as $key=>$variant)
                                            {
                                                $variants .= $variant;
                                                if(next($google_font['variants']) == true) $variants .= ",";
                                            }

                                            $value = (isset($google_font['value']) ? $google_font['value'] : '['. $google_font['label'] .','. $variants .']');
                                            if($value == '['.esc_html__('Default Font', 'modern-events-calendar-lite').',regular]' or $value == '['.esc_html__('Inherit from theme', 'modern-events-calendar-lite').',regular]') $value = '';
                                            ?>
                                            <option value="<?php echo esc_attr($value); ?>" <?php if(isset($styling['mec_p_fontfamily'] ) && ($styling['mec_p_fontfamily'] == $value ) ) echo 'selected'; ?>><?php echo esc_html($google_font['label']); ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>

                                </div>
                            </div>
                            <div class="mec-form-row mec-field mec-field--check">
                                <div class="mec-field__control">
                                    <label class="mec-check" for="mec_styling_disable_gfonts"><input type="hidden" name="mec[styling][disable_gfonts]" value="0" />
                                        <input value="1" type="checkbox" id="mec_styling_disable_gfonts" name="mec[styling][disable_gfonts]" <?php if(isset($styling['disable_gfonts']) and $styling['disable_gfonts']) echo 'checked="checked"'; ?> /><span><?php esc_html_e('Disable Google Fonts', 'modern-events-calendar-lite'); ?></span>
                                    </label>
                                    <div class="mec-tooltip">
                                        <div class="box top">
                                            <span class="title"><?php esc_html_e('Disable Google Fonts', 'modern-events-calendar-lite'); ?></span>
                                            <div class="content"><p><?php esc_attr_e('To be GDPR compliant you may need to disable Google fonts. Set "Default Font" value for font family and enable this option.', 'modern-events-calendar-lite'); ?><a href="https://webnus.net/dox/modern-events-calendar/appearance-settings/#9-_Disable_Google_Fonts/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Container Width -->
                            <h5 class="mec-panel__title"><?php esc_html_e('Accessibility', 'modern-events-calendar-lite'); ?></h5>
                            <div class="mec-form-row mec-field mec-field--check">
                                <div class="mec-field__control">
                                    <label class="mec-check" for="mec_styling_accessibility"><input type="hidden" name="mec[styling][accessibility]" value="0" />
                                        <input value="1" type="checkbox" id="mec_styling_accessibility" name="mec[styling][accessibility]" <?php if(isset($styling['accessibility']) and $styling['accessibility']) echo 'checked="checked"'; ?> /><span><?php esc_html_e('Accessibility', 'modern-events-calendar-lite'); ?></span>
                                    </label>
                                    <div class="mec-tooltip">
                                        <div class="box top">
                                            <span class="title"><?php esc_html_e('Accessibility', 'modern-events-calendar-lite'); ?></span>
                                            <div class="content"><p><?php esc_attr_e('Enable A11Y or accessibility in MEC.', 'modern-events-calendar-lite'); ?><a href="https://webnus.net/dox/modern-events-calendar/improving-accessibility-on-mec/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </div>
                                </div>
                            </div>


                            <!-- Container Width -->
                            <h5 class="mec-panel__title"><?php esc_html_e('Container Width', 'modern-events-calendar-lite'); ?></h5>
                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label"><label for="mec_styling_container_normal_width"><?php esc_html_e('Desktop Normal Screens', 'modern-events-calendar-lite'); ?></label></div><div class="mec-field__control">
                                    <input type="text" id="mec_styling_container_normal_width" name="mec[styling][container_normal_width]" value="<?php echo ((isset($styling['container_normal_width']) and trim($styling['container_normal_width']) != '') ? $styling['container_normal_width'] : ''); ?>" />
                                    <div class="mec-tooltip">
                                        <div class="box left">
                                            <span class="title"><?php esc_html_e('Desktop Normal Screens', 'modern-events-calendar-lite'); ?></span>
                                            <div class="content"><p><?php esc_attr_e('You can enter your theme container size in this field to override it for normal screens.', 'modern-events-calendar-lite'); ?><a href="https://webnus.net/dox/modern-events-calendar/appearance-settings/#11-_Desktop_Normal_Screens/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label"><label for="mec_styling_container_large_width"><?php esc_html_e('Desktop Large Screens', 'modern-events-calendar-lite'); ?></label></div><div class="mec-field__control">
                                    <input type="text" id="mec_styling_container_large_width" name="mec[styling][container_large_width]" value="<?php echo ((isset($styling['container_large_width']) and trim($styling['container_large_width']) != '') ? $styling['container_large_width'] : ''); ?>" />
                                    <div class="mec-tooltip">
                                        <div class="box left">
                                            <span class="title"><?php esc_html_e('Desktop Large Screens', 'modern-events-calendar-lite'); ?></span>
                                            <div class="content"><p><?php esc_attr_e('You can enter your theme container size in this field to override it for large screens.', 'modern-events-calendar-lite'); ?><a href="https://webnus.net/dox/modern-events-calendar/appearance-settings/#12-_Desktop_Large_Screens/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </div>
                                </div>
                            </div>
                            <?php do_action('mec_end_styling_settings', $styling); ?>

                            <!-- Other Styling Option -->
                            <h5 class="mec-panel__title"><?php esc_html_e('Other Styling Option', 'modern-events-calendar-lite'); ?></h5>

                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label"><label for="mec_settings_fes_color"><?php esc_html_e('Frontend Event Submission Color', 'modern-events-calendar-lite'); ?></label></div><div class="mec-field__control">
                                    <input type="text" class="wp-color-picker-field" id="mec_settings_fes_color" name="mec[styling][fes_color]" value="<?php echo (isset($styling['fes_color']) ? esc_attr($styling['fes_color']) : ''); ?>" data-default-color="" />
                                </div>
                            </div>

                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label"><label for="mec_settings_notification_bg"><?php esc_html_e('Notifications Background', 'modern-events-calendar-lite'); ?></label></div><div class="mec-field__control">
                                    <input type="text" class="wp-color-picker-field" id="mec_settings_notification_bg" name="mec[styling][notification_bg]" value="<?php echo (isset($styling['notification_bg']) ? esc_attr($styling['notification_bg']) : ''); ?>" data-default-color="" />
                                </div>
                            </div>

                            <div class="mec-form-row">
                                <?php wp_nonce_field('mec_options_form'); ?>
                                <button  style="display: none;" id="mec_styling_form_button" class="button button-primary mec-button-primary" type="submit"><?php esc_html_e('Save Changes', 'modern-events-calendar-lite'); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="wns-be-footer">
        <button type="submit" class="dpr-btn dpr-save-btn" form="mec_styling_form"><?php esc_html_e('Save Changes', 'modern-events-calendar-lite'); ?></button>
    </div>

</div>

<?php
$this->getFactory()->params('footer', function()
{
    ?>
    <script>
    jQuery(document).ready(function()
    {
        jQuery(".dpr-save-btn").on('click', function(event)
        {
            event.preventDefault();
            jQuery("#mec_styling_form_button").trigger('click');
        });
    });

    (function($)
    {
        'use strict';
        $(document).ready(function()
        {
            //Initiate Color Picker. The Iris palette is turned into a floating
            //popup via CSS (backend-refinements.css ~30.50), so opening it no
            //longer stretches the page.
            $('.wp-color-picker-field').wpColorPicker();

            //Since the palette floats instead of growing the page, nudge the
            //scroll only when it would open below the visible viewport.
            $('.wp-color-picker-field').each(function()
            {
                var $container = $(this).closest('.wp-picker-container');

                $container.on('click', '.wp-color-result', function()
                {
                    setTimeout(function()
                    {
                        //Closed in the meantime: nothing to keep in view.
                        if(!$container.hasClass('wp-picker-active')) return;

                        var holder = $container.find('.wp-picker-holder').get(0);
                        if(!holder) return;

                        var below = holder.getBoundingClientRect().bottom - (window.innerHeight - 24);
                        if(below > 0) window.scrollBy({top: below, behavior: 'smooth'});
                    }, 150);
                });
            });
        });

        $('.wpsa-browse').click(function(e)
        {
            e.preventDefault();
            var image = wp.media({
                title: 'Upload',
                multiple: false
            }).open()
            .on('select', function(e)
            {
                var uploaded_image = image.state().get('selection').first();
                var image_url = uploaded_image.toJSON().url;
                $('#mec_settings_upload').val(image_url);
            });
        });
    })(jQuery);

    jQuery("#mec_styling_form").on('submit', function(event)
    {
        event.preventDefault();

        // A save is already running (double click): ignore this one.
        if(jQuery(".dpr-save-btn").hasClass('loading')) return;

        // Add loading Class to the button
        jQuery(".dpr-save-btn").addClass('loading').text("<?php echo esc_js(esc_attr__('Saving ...', 'modern-events-calendar-lite')); ?>");
        try
        {

        var styling = jQuery("#mec_styling_form").serialize();

        // Shared save feedback (backend.js); a stale cached backend.js without it
        // only ends the loading state, as before.
        var saveDone = function(ok)
        {
            if(typeof window.mecSettingsSaveFeedback === 'function') return window.mecSettingsSaveFeedback(jQuery('#mec_styling_form'), ok);
            jQuery('.dpr-save-btn').removeClass('loading').text("<?php echo esc_js(esc_attr__('Save Changes', 'modern-events-calendar-lite')); ?>");
            if(typeof window.mecSettingsSaving === 'function') window.mecSettingsSaving(false);
        };

        jQuery.ajax(
        {
            type: "POST",
            url: ajaxurl,
            timeout: 30000,
            data: "action=mec_save_styling&"+styling,
            beforeSend: function () {
                if(typeof window.mecSettingsSaving === 'function') window.mecSettingsSaving(true);
            },
            success: function(data)
            {
                saveDone(window.mecSettingsSaveOk && window.mecSettingsSaveOk(data));
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                saveDone(false);
            }
        });
        }
        catch(err)
        {
            // Never leave the button locked if something throws before the request is sent.
            jQuery('.dpr-save-btn').removeClass('loading').text("<?php echo esc_js(esc_attr__('Save Changes', 'modern-events-calendar-lite')); ?>");
            if(typeof window.mecSettingsSaving === 'function') window.mecSettingsSaving(false);
            throw err;
        }
    });
    </script>
    <?php
});