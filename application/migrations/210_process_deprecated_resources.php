<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_process_deprecated_resources extends CI_Migration 
{
	public function up() {

		$files = array('assets/json/dok.txt', 'assets/json/pota.txt', 'assets/json/sota.txt', 'assets/json/wwff.txt', 'updates/clublog_scp.txt');

		if (file_exists('.git')) {
			try {
				// Restore the deprecated files (discard changes)
				foreach ($files as $file) {
					if (file_exists($file)) {
						exec('git reset -- ' . $file);
						exec('git restore ' . $file);
						log_message('debug', 'Updater: Discard changes in deprecated file: ' . $file);
					}
				}
			} catch (\Throwable $th) {
				log_message("Error","Mig 210 failed. Check 'git status'.");
			}
		}
	}

	public function down() {

		// nothing to do here

	}
}
