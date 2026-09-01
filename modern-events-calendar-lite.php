<?php

/**
 *   Plugin Name: Modern Events Calendar Lite
 *   Plugin URI: http://webnus.net/modern-events-calendar/
 *   Description: An awesome plugin for events calendar
 *   Author: Webnus
 *   Author URI: https://webnus.net
 *   Developer: Webnus
 *   Developer URI: https://webnus.net
 *   Version: 7.36.1
 *   Text Domain: modern-events-calendar-lite
 *   Domain Path: /languages
 **/

if (!defined('MECEXEC')) {
    /** MEC Execution **/
    define('MECEXEC', 1);

    /** Directory Separator **/
    if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

    /** MEC Absolute Path **/
    define('MEC_ABSPATH', dirname(__FILE__) . DS);

    /** Plugin Directory Name **/
    define('MEC_DIRNAME', basename(MEC_ABSPATH));

    /** Plugin File Name **/
    define('MEC_FILENAME', basename(__FILE__));

    /** Plugin Base Name **/
    define('MEC_BASENAME', plugin_basename(__FILE__)); // modern-events-calendar/mec.php

    /** Plugin Version **/
    define('MEC_VERSION', '7.36.1');

    /** Plugin Edition ('pro' here; the Lite package defines 'lite') — used by product analytics. */
    define('MEC_EDITION', 'pro');

    /** API URL **/
    define('MEC_API_ACTIVATION', 'https://my.webnus.net/api/v3');
    define('MEC_API_UPDATE', 'https://api.webnus.site/v3');
    // Token issuance endpoint: lives in the activation2 folder on the server (same Slim app).
    // The client POSTs to /license/claim under this base URL.
    // Points to activation2 for now (not the main activation endpoint) until
    // testing is complete, so existing users are not affected.
    define('MEC_API_LICENSE', 'https://my.webnus.net/api/v3/activation2/license');

    /**
     * Licence build constants.
     *
     * REGENERATED PER RELEASE by bin/build-constants.php — do not hand-edit.
     * The payload carries the release timestamp and the phase table, and is
     * covered by its own Ed25519 signature. Editing any of the three makes the
     * plugin fail closed to Lite parity.
     *
     * NOTE: these are PRODUCTION values. The matching private key lives only
     * in mec-license-server/private/ on the infrastructure side, never in this
     * repository. Anyone who edits these constants invalidates the build
     * signature and the plugin refuses to run as Pro.
     **/
    define('MEC_LICENSE_KEY_ENV', 'production');
    define('MEC_LICENSE_PUBKEY', 'Hrq-nkclj9-ELifFhlgjk8Yq3n3KHm9424P3Vs2b9RE');
    define('MEC_LICENSE_BUILD', 'eyJyZWxlYXNlX2lhdCI6MTc4NjI3MTg0OSwicGhhc2VzIjpbWzAsMF0sWzgsMV0sWzE1LDJdLFsyOSwzXSxbNDMsNF1dfQ');
    define('MEC_LICENSE_BUILD_SIG', '3Mv9_ubayaPw59Dam3O9jSeruLcECda0JNy05ADqkeDcdDrsgcBHpXqQKhmfKLFYDZ6XqorvmSIcMLKw1L81Dw');

    /**
     * Licence core.
     *
     * Hard-required here, before the MEC object graph exists, precisely so that
     * it cannot be swapped out through the theme-override path in
     * MEC::import(). See mec-init.php:169-218.
     **/
    require_once MEC_ABSPATH . 'app' . DS . 'libraries' . DS . 'license.php';

    /** Include Webnus MEC class if not included before **/
    if (!class_exists('MEC')) require_once MEC_ABSPATH . 'mec-init.php';

    add_action('before_woocommerce_init', function ()
    {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil'))
        {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    });

    /** Initialize Webnus MEC Plugin **/
    $MEC = MEC::instance();
    $MEC->init();

    require_once MEC_ABSPATH . 'app/core/mec.php';
    do_action('mec_init');
}
