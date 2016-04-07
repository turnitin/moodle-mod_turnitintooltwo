<?php

require_once(__DIR__.'/../../../../config.php');
require_once($CFG->libdir.'/csvlib.class.php');

$etd = optional_param('etd', '', PARAM_INT);
abstract class export_courses {

    /**
     * Download a CSV with given data.
     *
     * @return void
     */
    public static function getCSV($etd) {
        global $USER, $CFG;

		$filename = clean_filename('Migration Status');

		$csvexport = new csv_export_writer('comma');
		$csvexport->set_filename($filename);

		// Print names of all the fields
		if ($etd == 1) {
		$headerData = array('Moodle Course ID', 'Turnitin Course ID', 'Course Name', 'TII Assignment ID');
		} else {
			$headerData = array('Moodle Course ID', 'Turnitin Course ID', 'Course Name', 'Status');
		}

		// add the header line to the data
		$csvexport->add_data($headerData);

		// Add the CSV data if we have session data (the first time the download button is clicked).
		if (isset($_SESSION["migrationtool"]["csvdata"])) {
			foreach ($_SESSION["migrationtool"]["csvdata"] as $line) {
			    $csvexport->add_data($line);
			}

			// Clean up the session data we saved earlier.
			unset($_SESSION["migrationtool"]["csvdata"]);

			// Copy the CSV in case we need to use it again (download button is clicked multiple times).
			copy($csvexport->path, '../../logs/migrationtool/'.date('Y-m-d_His').' - Migration Status.csv');
		}
		else {
			// If the download button is clicked more than once we need to copy the data from the latest log.
			$exports = scandir('../../logs/migrationtool/', 1);
			copy('../../logs/migrationtool/'.$exports[0], $csvexport->path);
		}

		// Download the CSV file.
		$csvexport->download_file();
	}
}

export_courses::getCSV($etd);