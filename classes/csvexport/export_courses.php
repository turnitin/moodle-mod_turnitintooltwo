<?php

require_once(__DIR__.'/../../../../config.php');
require_once($CFG->libdir.'/csvlib.class.php');

abstract class export_courses {

    /**
     * Download a CSV with given data.
     *
     * @return void
     */
    public static function getCSV() {
        global $USER, $CFG;

        make_temp_directory('csvimport/' . $USER->id);

		$filename = clean_filename('Migration Status');

		$csvexport = new csv_export_writer('comma');
		$csvexport->set_filename($filename);

		// Print names of all the fields
		$headerData = array('Moodle Course ID', 'Turnitin Course ID', 'Course Name', 'Status');

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
			copy($csvexport->path, $CFG->tempdir . '/csvimport/'.$USER->id.'/Migration Status.csv');
		}
		else {
			// If the download button is clicked more than once we need to copy the data from the first CSV.
			copy($CFG->tempdir . '/csvimport/'.$USER->id.'/Migration Status.csv', $csvexport->path);
		}

		// Download the CSV file.
		$csvexport->download_file();
	}
}

export_courses::getCSV();
