<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC syncSchedule class.
 * @author Webnus <info@webnus.net>
 */
class MEC_syncSchedule extends MEC_base
{
    private $main;

    public function __construct()
    {
        $this->main = $this->getMain();
    }

    public function sync()
    {
        // Automated import/export is part of the Pro presentation tier (phase 2).
        // The cron scripts below carry the same gate, because they are directly
        // web-reachable and can be wired into system cron without coming
        // through here. On Lite this changes nothing: the scripts it would
        // include are not shipped, so the file_exists() checks already fail.
        if (!$this->isProPresentationEnabled()) return;

        $ix = $this->main->get_ix_options();

        // To run crons by force
        $internal_cron_system = true;

        if (isset($ix['sync_g_import_auto']) and $ix['sync_g_import_auto'] == '1')
        {
            $sync_g_import = MEC_ABSPATH . 'app' . DS . 'crons' . DS . 'g-import.php';
            if (file_exists($sync_g_import)) include_once $sync_g_import;
        }

        if (isset($ix['sync_g_export_auto']) and $ix['sync_g_export_auto'] == '1')
        {
            $sync_g_export = MEC_ABSPATH . 'app' . DS . 'crons' . DS . 'g-export.php';
            if (file_exists($sync_g_export)) include_once $sync_g_export;
        }

        if (isset($ix['sync_meetup_import_auto']) and $ix['sync_meetup_import_auto'] == '1')
        {
            $sync_meetup_import = MEC_ABSPATH . 'app' . DS . 'crons' . DS . 'meetup-import.php';
            if (file_exists($sync_meetup_import)) include_once $sync_meetup_import;
        }
    }
}
