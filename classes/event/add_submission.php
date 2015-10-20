<?php
namespace mod_turnitintooltwo\event;

/*
 * Log event when paper is submitted either by student or instructor on behalf of student
 */

defined('MOODLE_INTERNAL') || die();

class add_submission extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['level'] = self::LEVEL_PARTICIPATING; // For 2.6, this appears to have been renamed to 'edulevel' in 2.7
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'turnitintooltwo';
    }

    public static function get_name() {
        return get_string('addsubmission', 'mod_turnitintooltwo');
    }

    public function get_description() {
        return $this->other['desc'];
    }

    public function get_url() {
        return new \moodle_url('/mod/turnitintooltwo/view.php', array( 'id' => $this->objectid));
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['desc'])) {
            throw new \coding_exception('The \'desc\' value must be set in other.');
        }

        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }
}