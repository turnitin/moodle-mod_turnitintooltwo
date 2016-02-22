<?php

namespace mod_turnitintooltwo\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Library function for turnitintooltwo task function.
 */

class turnitintooltwo_task extends \core\task\scheduled_task {

    public function get_name() {
        // Shown in admin screens
        return get_string('task_name', 'mod_turnitintooltwo');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG, $TURNITINTOOLTWO_TASKCALL;

        // Run turnitintooltwo cron.
        require_once("{$CFG->dirroot}/mod/turnitintooltwo/lib.php");
        $TURNITINTOOLTWO_TASKCALL = true;
        turnitintooltwo_cron();
    }

}