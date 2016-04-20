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
        $csvexport = new csv_export_writer('comma');

        // Get our file name from the latest CSV that was created.
        $exports = glob('../../logs/migrationtool/*.csv');
        arsort($exports);
        $filename = explode("migrationtool/",current($exports));
    	$csvexport->filename = $filename[1];

		// Empty row to force file path creation.       
		$csvexport->add_data(array());

		// Copy the latest CSV.
        copy('../../logs/migrationtool/'.$filename[1], $csvexport->path);

        // Download the CSV file.
        $csvexport->download_file();
    }
}

export_courses::getCSV();
