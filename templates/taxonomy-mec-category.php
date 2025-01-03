<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * The Template for displaying mec-category taxonomy events
 * 
 * @author Webnus <info@webnus.net>
 * @package MEC/Templates
 * @version 1.0.0
 */
get_header('mec'); ?>
	
    <section id="<?php echo apply_filters('mec_category_page_html_id', 'main-content'); ?>" class="<?php echo apply_filters('mec_category_page_html_class', 'mec-container'); ?>">

        <?php do_action('mec_before_main_content'); ?>
        <?php do_action('mec_before_events_loop'); ?>

            <h1><?php echo single_term_title(''); ?></h1>
            <?php if(is_active_sidebar('mec-archive')): ?>
                <div class="mec-archive-wrapper mec-wrap">
                    <div class="mec-archive-content col-md-8">
                        <?php $MEC = MEC::instance(); echo MEC_kses::full($MEC->category()); ?>
                    </div>
                    <div class="mec-archive-sidebar col-md-4">
                        <?php dynamic_sidebar('mec-archive'); ?>
                    </div>
                </div>
            <?php else: ?>
                <?php $MEC = MEC::instance(); echo MEC_kses::full($MEC->category()); ?>
            <?php endif; ?>

        <?php do_action('mec_after_events_loop'); ?>

    </section>

    <?php do_action('mec_after_main_content'); ?>

<?php get_footer('mec');