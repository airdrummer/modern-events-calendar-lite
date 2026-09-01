<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_mec $this */

wp_enqueue_style('mec-lity-style');
wp_enqueue_script('mec-lity-script');

$styling = $this->main->get_styling();
$darkadmin_mode = $styling['dark_mode'] ?? '';
$logo = plugin_dir_url(__FILE__) . '../../../assets/img/' . ($darkadmin_mode == 1 ? 'mec-logo-w2.png' : 'mec-logo-w.png');

$addons = [];
$addons_json_path = plugin_dir_path(__FILE__) . '../../api/addons-api/addons-api.json';
if (file_exists($addons_json_path)) $addons = json_decode((string) @file_get_contents($addons_json_path));
$addons = is_object($addons) ? get_object_vars($addons) : [];
?>
<div id="webnus-dashboard" class="wrap about-wrap mec-addons mec-support-page">
    <div class="welcome-head w-clearfix">
        <div class="w-row">
            <div class="w-col-sm-9">
                <h1> <?php echo esc_html__('Addons', 'modern-events-calendar-lite'); ?> </h1>
                <div class="w-welcome">
                    <?php echo sprintf(esc_html__('%s: extend MEC with powerful addons for page builders, payments, reports and more.', 'modern-events-calendar-lite'), '<strong>' . esc_html__('Modern Events Calendar', 'modern-events-calendar-lite') . '</strong>'); ?>
                </div>
            </div>
            <div class="w-col-sm-3">
                <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr__('Modern Events Calendar', 'modern-events-calendar-lite'); ?>" />
                <span class="w-theme-version"><?php echo esc_html__('Version', 'modern-events-calendar-lite'); ?> <?php echo MEC_VERSION; ?></span>
            </div>
        </div>
    </div>
    <div class="welcome-content w-clearfix extra">
        <?php if (current_user_can('read')): ?>
            <?php if (count($addons)): ?>
                <div class="mec-support-search mec-addons-filter">
                    <form id="mec-addons-filter-form" action="#" method="get">
                        <i class="mec-sl-magnifier" aria-hidden="true"></i>
                        <input id="mec-addons-filter-input" type="search" placeholder="<?php esc_attr_e('Filter addons. Try "Elementor", "Zoom", "payment"…', 'modern-events-calendar-lite'); ?>" />
                        <span class="mec-addons-filter__count"><?php echo esc_html(sprintf(_n('%d addon', '%d addons', count($addons), 'modern-events-calendar-lite'), count($addons))); ?></span>
                    </form>
                </div>

                <div class="mec-support-grid mec-support-grid--4" id="mec-addons-grid">
                    <?php foreach ($addons as $addon): ?>
                        <?php
                        $coming_soon = ($addon->comingsoon ?? 'false') === 'true';
                        $requires_pro = ($addon->pro ?? 'false') === 'true';
                        ?>
                        <div class="mec-support-card mec-addons-card" data-search="<?php echo esc_attr(function_exists('mb_strtolower') ? mb_strtolower($addon->name . ' ' . $addon->desc, 'UTF-8') : strtolower($addon->name . ' ' . $addon->desc)); ?>">
                            <div class="mec-support-card__head">
                                <span class="mec-support-card__icon mec-addons-card__icon">
                                    <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../../api/addons-api/' . $addon->img); ?>" alt="<?php echo esc_attr($addon->name); ?>" />
                                </span>
                                <span class="mec-support-card__title"><?php echo esc_html($addon->name); ?></span>
                            </div>
                            <div class="mec-addons-card__badges">
                                <?php if ($coming_soon): ?>
                                    <span class="mec-addons-badge mec-addons-badge--soon"><?php esc_html_e('Coming Soon', 'modern-events-calendar-lite'); ?></span>
                                <?php else: ?>
                                    <span class="mec-addons-badge <?php echo $requires_pro ? 'mec-addons-badge--pro' : 'mec-addons-badge--lite'; ?>">
                                        <?php echo $requires_pro ? esc_html__('Pro Required', 'modern-events-calendar-lite') : esc_html__('Lite & Pro', 'modern-events-calendar-lite'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="mec-support-card__desc"><?php echo esc_html($addon->desc); ?></p>
                            <?php if (!$coming_soon): ?>
                                <div class="mec-addons-card__actions">
                                    <a class="mec-addons-action" href="<?php echo esc_url($addon->video); ?>" data-lity title="<?php echo esc_attr__('Watch introduction video', 'modern-events-calendar-lite'); ?>" aria-label="<?php echo esc_attr__('Watch introduction video', 'modern-events-calendar-lite'); ?>">
                                        <i class="mec-sl-control-play"></i>
                                    </a>
                                    <a class="mec-addons-action" href="<?php echo esc_url($addon->page); ?>" target="_blank" rel="noopener" title="<?php echo esc_attr__('Details', 'modern-events-calendar-lite'); ?>" aria-label="<?php echo esc_attr__('View addon details', 'modern-events-calendar-lite'); ?>">
                                        <i class="mec-sl-link"></i>
                                    </a>
                                    <a class="mec-support-btn mec-addons-card__buy" href="<?php echo esc_url($addon->purchase); ?>" target="_blank" rel="noopener">
                                        <i class="mec-sl-basket"></i> <?php esc_html_e('Get Addon', 'modern-events-calendar-lite'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mec-addons-empty" id="mec-addons-empty" style="display:none;">
                    <i class="mec-sl-magnifier"></i>
                    <p><?php esc_html_e('No addons matched your search. Try a different keyword.', 'modern-events-calendar-lite'); ?></p>
                </div>
            <?php else: ?>
                <div class="w-row">
                    <div class="w-col-sm-12">
                        <div class="mec-addons-error">
                            <p><?php esc_html_e('The addons list could not be loaded. Please make sure your MEC installation is complete (the app/api/addons-api folder must exist), then reload this page.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php $this->factory->params('footer', function () {
?>
    <script>
        (function() {
            var form = document.getElementById('mec-addons-filter-form');
            var input = document.getElementById('mec-addons-filter-input');
            var grid = document.getElementById('mec-addons-grid');
            var empty = document.getElementById('mec-addons-empty');
            if (!form || !input || !grid) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();
            });

            input.addEventListener('input', function() {
                var q = this.value.trim().toLowerCase();
                var visible = 0;

                grid.querySelectorAll('.mec-addons-card').forEach(function(card) {
                    var match = !q || (card.getAttribute('data-search') || '').indexOf(q) !== -1;
                    card.style.display = match ? '' : 'none';
                    if (match) visible++;
                });

                if (empty) empty.style.display = visible !== 0 ? 'none' : 'flex';
            });
        })();
    </script>
<?php }); ?>
