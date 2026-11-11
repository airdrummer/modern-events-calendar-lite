<?php
/** no direct access **/
defined('MECEXEC') or die();

$styles = $this->main->get_styles();
?>

<div class="mec-page-head">
    <span class="mec-page-eyebrow"><?php esc_html_e('Modern Events Calendar', 'modern-events-calendar-lite'); ?></span>
    <h1><span class="mec-page-mark" aria-hidden="true"><span class="dashicons dashicons-editor-code"></span></span><?php esc_html_e('MEC Settings', 'modern-events-calendar-lite'); ?><span class="mec-page-title-sep" aria-hidden="true">/</span><?php esc_html_e('Custom CSS', 'modern-events-calendar-lite'); ?></h1>
    <p class="mec-page-lead"><?php esc_html_e('Add custom CSS rules that apply to all MEC shortcodes and pages.', 'modern-events-calendar-lite'); ?></p>
    <?php $this->main->render_settings_page_meta(); ?>
</div>
<hr class="wp-header-end" />
<div class="wns-be-container wns-be-container-sticky">

    <div id="wns-be-infobar">
        <div class="mec-infobar-crumb">
            <span class="c-root"><?php esc_html_e('MEC Settings', 'modern-events-calendar-lite'); ?></span>
            <span class="c-sep">/</span>
            <span class="c-tab"><?php esc_html_e('Custom CSS', 'modern-events-calendar-lite'); ?></span>
        </div>
        <div class="mec-search-settings-wrap">
            <i class="mec-sl-magnifier"></i>
            <input id="mec-search-settings" type="text" placeholder="<?php esc_html_e('Search...' , 'modern-events-calendar-lite'); ?>">
        </div>
        <button type="submit" class="dpr-btn dpr-save-btn" form="mec_styles_form"><?php esc_html_e('Save Changes', 'modern-events-calendar-lite'); ?></button>
    </div>

    <div class="wns-be-sidebar">
        <?php $this->main->get_sidebar_menu('customcss'); ?>
    </div>

    <div class="wns-be-main customcss-settings">

        <div id="wns-be-notification"></div>

        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <div class="mec-container">
                    <form id="mec_styles_form" data-mec-settings-form="1">
                        <div class="mec-options-fields">
                            <h2 class="mec-form-subtitle mec-page-section"><?php esc_html_e('Custom Styles', 'modern-events-calendar-lite'); ?></h2>
                            <div class="mec-form-row">
                                <textarea id="mec_styles_CSS" name="mec[styles][CSS]" aria-label="<?php esc_attr_e('Custom Styles', 'modern-events-calendar-lite'); ?>"><?php echo (isset($styles['CSS']) ? esc_textarea(stripslashes($styles['CSS'])) : ''); ?></textarea>
                                <p class="mec-col-12 description"><?php esc_html_e("If you're a developer or you have knowledge of CSS codes, you can place your style codes here. These codes will be included in your theme frontend after all styles so they will override MEC default (or theme) styles.", 'modern-events-calendar-lite'); ?></p>
                                <?php wp_nonce_field('mec_options_form'); ?>
                                <button style="display: none;" id="mec_styles_form_button" class="button button-primary mec-button-primary" type="submit"><?php esc_html_e('Save Changes', 'modern-events-calendar-lite'); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="wns-be-footer">
        <button type="submit" class="dpr-btn dpr-save-btn" form="mec_styles_form"><?php esc_html_e('Save Changes', 'modern-events-calendar-lite'); ?></button>
    </div>

</div>

<?php
$this->getFactory()->params('footer', function()
{
    ?>
    <script>
    // WordPress core CodeMirror on the styles box (enqueued in factory.php
    // for this tab only). Initialize defensively: if the code-editor bundle
    // is missing or fails, the styled plain textarea keeps working.
    var mecStylesEditor = null;
    jQuery(document).ready(function()
    {
        if(typeof wp !== 'undefined' && wp.codeEditor && typeof wp.codeEditor.initialize === 'function')
        {
            try
            {
                mecStylesEditor = wp.codeEditor.initialize(jQuery('#mec_styles_CSS'));

                // Keep the hidden textarea live: the unsaved-changes guard
                // (mecSettingsGuard) compares the serialized form against its
                // baseline, and fromTextArea only syncs on a native submit.
                // Editor-sized content, so save() on every change is cheap.
                if(mecStylesEditor && mecStylesEditor.codemirror)
                {
                    mecStylesEditor.codemirror.on('change', function(cm){ cm.save(); });
                }
            }
            catch(e) { mecStylesEditor = null; }
        }
    });

    jQuery(document).ready(function()
    {
        jQuery(".dpr-save-btn").on('click', function(event)
        {
            event.preventDefault();
            jQuery("#mec_styles_form_button").trigger('click');
        });
    });

    jQuery("#mec_styles_form").on('submit', function(event)
    {
        event.preventDefault();

        // A save is already running (double click): ignore this one.
        if(jQuery(".dpr-save-btn").hasClass('loading')) return;

        // Add loading Class to the button
        jQuery(".dpr-save-btn").addClass('loading').text("<?php echo esc_js(esc_attr__('Saving ...', 'modern-events-calendar-lite')); ?>");
        try
        {

        // The CodeMirror wrapper only copies its content back into the
        // textarea on a native form submit, which never happens here —
        // force the sync before the value is serialized.
        if(mecStylesEditor && mecStylesEditor.codemirror) mecStylesEditor.codemirror.save();

        var styles = jQuery("#mec_styles_form").serialize();

        // Shared save feedback (backend.js); a stale cached backend.js without it
        // only ends the loading state, as before.
        var saveDone = function(ok)
        {
            if(typeof window.mecSettingsSaveFeedback === 'function') return window.mecSettingsSaveFeedback(jQuery('#mec_styles_form'), ok);
            jQuery('.dpr-save-btn').removeClass('loading').text("<?php echo esc_js(esc_attr__('Save Changes', 'modern-events-calendar-lite')); ?>");
            if(typeof window.mecSettingsSaving === 'function') window.mecSettingsSaving(false);
        };

        jQuery.ajax(
        {
            type: "POST",
            url: ajaxurl,
            timeout: 30000,
            data: "action=mec_save_styles&"+styles,
            beforeSend: function()
            {
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