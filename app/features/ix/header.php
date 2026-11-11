<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Shared page header + tab bar for the MEC-ix page.
 * Included by every app/features/ix/*.php tab template so the chrome is
 * declared once. Renders on the settings design system (body.mec-ds-settings).
 *
 * Expected variables (set by the including template):
 * @var MEC_feature_ix $this
 * @var string $active_tab Slug of the active tab ('' = Google Cal. Import)
 */

$active_tab = $active_tab ?? '';

// License status for the unified page header (render only, same as settings).
$mec_license_token = ($this->isProBuild() and class_exists('MEC_license')) ? MEC_license::instance()->licensed() : false;
$mec_partner = ($this->isProBuild() and class_exists('MEC_license')) ? MEC_license::instance()->partner_licence() : null;
$mec_license_active = ($mec_license_token or is_array($mec_partner));

$mec_ix_tabs = [
    '' => ['label' => esc_html__('Google Cal. Import', 'modern-events-calendar-lite'), 'icon' => 'mec-sl-cloud-download'],
    'MEC-g-calendar-export' => ['label' => esc_html__('Google Cal. Export', 'modern-events-calendar-lite'), 'icon' => 'mec-sl-cloud-upload'],
    'MEC-f-calendar-import' => ['label' => esc_html__('Facebook Cal. Import', 'modern-events-calendar-lite'), 'icon' => 'mec-sl-social-facebook'],
    'MEC-meetup-import' => ['label' => esc_html__('Meetup Import', 'modern-events-calendar-lite'), 'icon' => 'mec-sl-share-alt'],
    'MEC-sync' => ['label' => esc_html__('Synchronization', 'modern-events-calendar-lite'), 'icon' => 'mec-sl-loop'],
    'MEC-export' => ['label' => esc_html__('Export', 'modern-events-calendar-lite'), 'icon' => 'mec-sl-logout'],
    'MEC-import' => ['label' => esc_html__('Import', 'modern-events-calendar-lite'), 'icon' => 'mec-sl-login'],
    'MEC-thirdparty' => ['label' => esc_html__('Third Party Plugins', 'modern-events-calendar-lite'), 'icon' => 'mec-sl-puzzle'],
    'MEC-test-data' => ['label' => esc_html__('Test Data', 'modern-events-calendar-lite'), 'icon' => 'mec-sl-magic-wand'],
];
?>
<div class="wrap" id="mec-wrap">
    <div class="mec-page-head">
        <span class="mec-page-eyebrow"><?php esc_html_e('Modern Events Calendar', 'modern-events-calendar-lite'); ?></span>
        <h1><span class="mec-page-mark" aria-hidden="true"><i class="mec-sl-refresh"></i></span><?php esc_html_e('Import / Export', 'modern-events-calendar-lite'); ?></h1>
        <p class="mec-page-lead"><?php esc_html_e('Move events and bookings in and out of MEC, and keep them in sync with Google, Facebook, and Meetup calendars.', 'modern-events-calendar-lite'); ?></p>
        <div class="mec-settings-meta">
            <span class="mec-tag">MEC <?php echo esc_html(MEC_VERSION); ?></span>
            <?php if($this->isProBuild()): ?>
                <?php if($mec_license_active): ?>
                    <span class="mec-tag ok"><span class="d"></span><?php esc_html_e('License active', 'modern-events-calendar-lite'); ?></span>
                <?php else: ?>
                    <span class="mec-tag warn"><span class="d"></span><?php esc_html_e('License not activated', 'modern-events-calendar-lite'); ?></span>
                <?php endif; ?>
            <?php else: ?>
                <span class="mec-tag"><?php esc_html_e('Lite (free)', 'modern-events-calendar-lite'); ?></span>
            <?php endif; ?>
        </div>
    </div>
    <hr class="wp-header-end" />
    <nav class="mec-tabbar" aria-label="<?php esc_attr_e('Import / Export tools', 'modern-events-calendar-lite'); ?>">
        <?php foreach($mec_ix_tabs as $slug => $tab): ?>
            <?php
            // The meetup tab keeps the mec-ix-action QS var stripped (legacy behavior of its template)
            $href = ($slug === 'MEC-meetup-import')
                ? $this->main->remove_qs_var('mec-ix-action', $this->main->add_qs_var('tab', $slug))
                : $this->main->add_qs_var('tab', $slug);
            if($slug === '') $href = $this->main->remove_qs_var('tab');
            ?>
            <a href="<?php echo esc_url($href); ?>" class="mec-tabbar__tab<?php echo $active_tab === $slug ? ' mec-tabbar__tab--active' : ''; ?>"<?php echo $active_tab === $slug ? ' aria-current="page"' : ''; ?>>
                <i class="<?php echo esc_attr($tab['icon']); ?>" aria-hidden="true"></i>
                <span><?php echo esc_html($tab['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
