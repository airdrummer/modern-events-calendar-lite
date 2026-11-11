<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_mec $this */

wp_enqueue_style('mec-lity-style');
wp_enqueue_script('mec-lity-script');

$addons = [];
$addons_json_path = plugin_dir_path(__FILE__) . '../../api/addons-api/addons-api.json';
if (file_exists($addons_json_path)) $addons = json_decode((string) @file_get_contents($addons_json_path));
$addons = is_object($addons) ? get_object_vars($addons) : [];
$addons_count = count($addons);

$is_pro_build = $this->isProBuild();
$browse_url = 'https://webnus.net/modern-events-calendar/addons/';
$pricing_url = $this->main->get_pro_link();

$mec_addons_js = [
    'total' => $addons_count,
    'i18n' => [
        'countFmt' => esc_html__('%1$s of %2$s addons', 'modern-events-calendar-lite'),
    ],
];
?>
<div class="wrap" id="mec-wrap">
<div id="webnus-dashboard" class="mec-addons">
    <div class="mec-ds mec-addons-v2" id="mecAddons">

        <!-- Header -->
        <div class="mec-page-head mec-page-head--split">
            <div class="mec-page-head__main">
                <span class="mec-page-eyebrow"><?php esc_html_e('Modern Events Calendar', 'modern-events-calendar-lite'); ?></span>
                <h1><span class="mec-page-mark" aria-hidden="true"><i class="mec-sl-layers"></i></span><?php esc_html_e('Addons', 'modern-events-calendar-lite'); ?></h1>
                <p class="mec-page-lead"><?php esc_html_e('Extend MEC with powerful addons for page builders, payments, reports and more.', 'modern-events-calendar-lite'); ?></p>
                <div class="mec-settings-meta">
                    <span class="mec-tag">MEC <?php echo esc_html(MEC_VERSION); ?></span>
                    <span class="mec-tag"><?php echo sprintf(esc_html(_n('%s addon', '%s addons', $addons_count, 'modern-events-calendar-lite')), number_format_i18n($addons_count)); ?></span>
                    <span class="mec-tag<?php echo $is_pro_build ? ' pro' : ''; ?>"><?php echo $is_pro_build ? esc_html__('Pro', 'modern-events-calendar-lite') : esc_html__('Lite (free)', 'modern-events-calendar-lite'); ?></span>
                </div>
            </div>
            <div class="mec-page-head__actions ms-actions">
                <a class="btn btn-sec" href="<?php echo esc_url($browse_url); ?>" target="_blank" rel="noopener"><span class="ico" data-i="ext"></span><?php esc_html_e('Browse all addons', 'modern-events-calendar-lite'); ?></a>
                <?php if (!$is_pro_build): ?>
                <a class="btn btn-pri" href="<?php echo esc_url($pricing_url); ?>" target="_blank" rel="noopener"><span class="ico" data-i="rocket"></span><?php esc_html_e('Upgrade to Pro', 'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <hr class="wp-header-end" />

        <?php if (current_user_can('read')): ?>

        <?php if ($addons_count): ?>
        <!-- Filter -->
        <section class="ms-search">
            <h2><?php esc_html_e('Find an addon', 'modern-events-calendar-lite'); ?></h2>
            <form id="mec-addons-filter-form" action="#" method="get">
                <span class="ico" data-i="search"></span>
                <label for="mec-addons-filter-input" class="sr"><?php esc_html_e('Filter addons', 'modern-events-calendar-lite'); ?></label>
                <input id="mec-addons-filter-input" type="search" autocomplete="off" placeholder="<?php esc_attr_e('Filter addons. Try "Elementor", "Zoom", "payment"&hellip;', 'modern-events-calendar-lite'); ?>" />
                <span class="adn-count" id="mec-addons-filter-count"><?php echo sprintf(esc_html(_n('%s addon', '%s addons', $addons_count, 'modern-events-calendar-lite')), number_format_i18n($addons_count)); ?></span>
            </form>
            <div class="chips" id="mec-addons-chips"><span><?php esc_html_e('Popular:', 'modern-events-calendar-lite'); ?></span>
                <button type="button"><?php esc_html_e('Elementor', 'modern-events-calendar-lite'); ?></button><button type="button"><?php esc_html_e('Zoom', 'modern-events-calendar-lite'); ?></button><button type="button"><?php esc_html_e('Layouts', 'modern-events-calendar-lite'); ?></button><button type="button"><?php esc_html_e('Import', 'modern-events-calendar-lite'); ?></button><button type="button"><?php esc_html_e('Payment', 'modern-events-calendar-lite'); ?></button>
            </div>
        </section>

        <!-- Grid -->
        <section class="sec">
            <div class="g4" id="mec-addons-grid">
                <?php foreach ($addons as $addon): ?>
                    <?php
                    $coming_soon = ($addon->comingsoon ?? 'false') === 'true';
                    $requires_pro = ($addon->pro ?? 'false') === 'true';
                    $search_index = $addon->slug . ' ' . $addon->name . ' ' . $addon->desc;
                    ?>
                    <div class="ds-card adn<?php echo $coming_soon ? ' soon' : ''; ?>" data-search="<?php echo esc_attr(function_exists('mb_strtolower') ? mb_strtolower($search_index, 'UTF-8') : strtolower($search_index)); ?>">
                        <div class="adn-head">
                            <span class="adn-img"><img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../../api/addons-api/' . $addon->img); ?>" alt="<?php echo esc_attr($addon->name); ?>" /></span>
                            <div>
                                <h3><?php echo esc_html($addon->name); ?></h3>
                                <?php if (!empty($addon->version)): ?><span class="adn-ver">v<?php echo esc_html($addon->version); ?></span><?php endif; ?>
                            </div>
                        </div>
                        <div class="adn-badges">
                            <?php if ($coming_soon): ?>
                                <span class="tag"><?php esc_html_e('Coming Soon', 'modern-events-calendar-lite'); ?></span>
                            <?php else: ?>
                                <span class="tag <?php echo $requires_pro ? 'pro' : 'ok'; ?>"><?php echo $requires_pro ? esc_html__('Pro Required', 'modern-events-calendar-lite') : esc_html__('Lite & Pro', 'modern-events-calendar-lite'); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="adn-desc"><?php echo esc_html($addon->desc); ?></div>
                        <?php if (!$coming_soon): ?>
                        <div class="adn-actions">
                            <a class="adn-ico" href="<?php echo esc_url($addon->video); ?>" data-lity title="<?php echo esc_attr__('Watch introduction video', 'modern-events-calendar-lite'); ?>" aria-label="<?php echo esc_attr__('Watch introduction video', 'modern-events-calendar-lite'); ?>"><span class="ico" data-i="play"></span></a>
                            <a class="adn-ico" href="<?php echo esc_url($addon->page); ?>" target="_blank" rel="noopener" title="<?php echo esc_attr__('Details', 'modern-events-calendar-lite'); ?>" aria-label="<?php echo esc_attr__('View addon details', 'modern-events-calendar-lite'); ?>"><span class="ico" data-i="ext"></span></a>
                            <a class="btn btn-pri btn-sm adn-buy" href="<?php echo esc_url($addon->purchase); ?>" target="_blank" rel="noopener"><span class="ico" data-i="basket"></span><?php esc_html_e('Get Addon', 'modern-events-calendar-lite'); ?></a>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="adn-empty" id="mec-addons-empty" style="display:none;">
                <span class="ico" data-i="search"></span>
                <p><?php esc_html_e('No addons matched your search. Try a different keyword.', 'modern-events-calendar-lite'); ?></p>
            </div>
        </section>
        <?php else: ?>
        <section class="sec">
            <div class="adn-empty err">
                <span class="ico" data-i="alert"></span>
                <p><?php esc_html_e('The addons list could not be loaded. Please make sure your MEC installation is complete (the app/api/addons-api folder must exist), then reload this page.', 'modern-events-calendar-lite'); ?></p>
            </div>
        </section>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>
</div>

<?php
$this->factory->params('footer', function () use ($mec_addons_js) {
?>
<script>
window.MECAddons = <?php echo wp_json_encode($mec_addons_js); ?>;
</script>
<script>
(function () {
    var CFG = window.MECAddons || {};

    /* ---- Icons (subset of the .mec-ds set) ---- */
    var P = {
        search: '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        ext: '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        play: '<rect x="3" y="5" width="18" height="14" rx="3"/><path d="m10 9 5 3-5 3z"/>',
        rocket: '<path d="M5 15c-1.5 1.5-2 5-2 5s3.5-.5 5-2M9 12l3 3M14.5 4.5c2.5-1 5-1 5.5-.5.5.5.5 3-.5 5.5L13 16l-5-5z"/><circle cx="15" cy="9" r="1.5"/>',
        alert: '<path d="M12 9v4M12 17h.01M10.3 3.9 2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/>',
        basket: '<path d="M6 7h12l1.2 11.1a1.5 1.5 0 0 1-1.5 1.9H6.3a1.5 1.5 0 0 1-1.5-1.9z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/>',
        layers: '<path d="m12 2 10 5-10 5L2 7z"/><path d="m2 12 10 5 10-5"/><path d="m2 17 10 5 10-5"/>'
    };

    function icons(root) {
        var els = (root || document).querySelectorAll('span[data-i]:not([data-done])');
        for (var i = 0; i < els.length; i++) {
            var name = els[i].getAttribute('data-i');
            if (!P[name]) continue;
            els[i].innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + P[name] + '</svg>';
            els[i].setAttribute('data-done', '1');
        }
    }
    icons();

    /* ---- Live filter ---- */
    var input = document.getElementById('mec-addons-filter-input');
    var form = document.getElementById('mec-addons-filter-form');
    var grid = document.getElementById('mec-addons-grid');
    var count = document.getElementById('mec-addons-filter-count');
    var empty = document.getElementById('mec-addons-empty');
    if (!input || !grid) return;

    var total = CFG.total || 0;
    var countFmt = (CFG.i18n || {}).countFmt || '%1$s of %2$s addons';

    function apply() {
        var q = input.value.trim().toLowerCase();
        var visible = 0;

        grid.querySelectorAll('.adn').forEach(function (card) {
            var match = !q || (card.getAttribute('data-search') || '').indexOf(q) !== -1;
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        if (count) count.textContent = q ? countFmt.replace('%1$s', visible).replace('%2$s', total) : total;
        if (empty) empty.style.display = visible === 0 ? 'flex' : 'none';
    }

    if (form) form.addEventListener('submit', function (e) { e.preventDefault(); });
    input.addEventListener('input', apply);

    var chips = document.getElementById('mec-addons-chips');
    if (chips) chips.addEventListener('click', function (e) {
        var b = e.target.closest ? e.target.closest('button') : null;
        if (!b) return;
        input.value = (input.value.trim() === b.textContent.trim()) ? '' : b.textContent.trim();
        input.focus();
        apply();
    });
})();
</script>
<?php });
