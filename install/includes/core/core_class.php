<?php

class Core
{

	// Function to validate the post data
	function validate_post($data)
	{
		// Counter variable
		$counter = 0;
		$errors = [];

		// Validate the hostname
		if (isset($data['db_hostname']) and !empty($data['db_hostname'])) {
			$counter++;
		} else {
			$errors[] = "DB Hostname is missing.";
		}

		// Validate the username
		if (isset($data['db_username']) and !empty($data['db_username'])) {
			$counter++;
		} else {
			$errors[] = "DB Username is missing.";
		}

		// Validate the database
		if (isset($data['db_name']) and !empty($data['db_name'])) {
			$counter++;
		} else {
			$errors[] = "DB Name is missing.";
		}

		if ($data['directory'] ?? '' != "") {
			$doc_root = realpath($_SERVER['DOCUMENT_ROOT']);
			$target   = realpath($_SERVER['DOCUMENT_ROOT'] . '/' . $data['directory']);
			if ($doc_root !== false && $target !== false && strpos($target . '/', $doc_root . '/') === 0) {
				$counter++;
			} else {
				$errors[] = "Directory does not exist or is outside the web root.";
			}
		} else {
			// directory is not set so nothing to check here
			$counter++;
		}

		// Validate First Name
		if (isset($data['firstname']) && !empty($data['firstname'])) {
			$counter++;
		} else {
			$errors[] = "First Name is missing.";
		}

		// Validate Last Name
		if (isset($data['lastname']) && !empty($data['lastname'])) {
			$counter++;
		} else {
			$errors[] = "Last Name is missing.";
		}

		// Validate Username
		if (isset($data['username']) && !empty($data['username'])) {
			$counter++;
		} else {
			$errors[] = "Username is missing.";
		}

		// Validate Callsign
		if (isset($data['callsign']) && !empty($data['callsign'])) {
			$counter++;
		} else {
			$errors[] = "Callsign is missing.";
		}

		// Validate Password
		if (isset($data['password']) && !empty($data['password'])) {
			$counter++;
		} else {
			$errors[] = "User Password is missing.";
		}

		// Validate Locator
		if (isset($data['userlocator']) && !empty($data['userlocator'])) {
			$locator = $data['userlocator'];
			if (preg_match('/^[A-R]{2}[0-9]{2}[A-X]{2}$/i', $locator)) {
				$counter++;
			} else {
				$errors[] = "Invalid Maidenhead Locator format.";
			}
		} else {
			$errors[] = "Locator is missing.";
		}

		// Validate Confirm Password
		if (isset($data['cnfm_password']) && !empty($data['cnfm_password'])) {
			$counter++;
		} else {
			$errors[] = "Confirm Password is missing.";
		}

		// Validate Email Address
		if (isset($data['user_email']) && filter_var($data['user_email'], FILTER_VALIDATE_EMAIL)) {
			$counter++;
		} else {
			$errors[] = "Invalid Email Address.";
		}

		// Validate Timezone
		if (isset($data['timezone']) && is_numeric($data['timezone'])) {
			$counter++;
		} else {
			$errors[] = "Invalid Timezone.";
		}

		// Validate Website URL
		if (isset($data['websiteurl']) && filter_var($data['websiteurl'], FILTER_VALIDATE_URL) &&
			in_array(parse_url($data['websiteurl'], PHP_URL_SCHEME), ['http', 'https'])) {
			$counter++;
		} else {
			$errors[] = "Invalid or missing Website URL (must start with http:// or https://).";
		}

		// Check if all the required fields have been entered
		if ($counter == 14) {
			log_message('info', 'Data validation passed.');
			return true;
		} else {
			log_message('error', 'Data validation failed.');
			foreach ($errors as $error) {
				log_message('error', $error);
			}
			return false;
		}
	}

	// Function to write the database config file
	function write_config($data) {

		$template_path 	= '../application/config/database.sample.php';
		$output_path 	= $_SERVER['DOCUMENT_ROOT'] . '/' . $data['directory'] . '/application/config/database.php';

		if (isset($_ENV['CI_ENV'])) {
			$output_path 	= $_SERVER['DOCUMENT_ROOT'] . '/' . $data['directory'] . '/application/config/'.$_ENV['CI_ENV'].'/database.php';
			log_message('info', 'CI_ENV is set to ' . $_ENV['CI_ENV'] . '. Using ' . $_ENV['CI_ENV'] . ' database.php config path.');
		} else {
			log_message('info', 'CI_ENV is not set. Using default database.php config path.');
		}

		// if config file already exists, we stop early and fail
		if (file_exists($output_path)) {
			log_message('error', 'database.php config file already exists');
			return false;
		}

		if (!file_exists($template_path)) {
			log_message('error', 'database.php template file not found.');
			return false;
		}

		// Open the file
		$database_file = file_get_contents($template_path);
		if ($database_file === false) {
			log_message('error', 'Failed to read database.php template file.');
			return false;
		}
		log_message('info', 'database.php template file read successfully.');

		$values = array(
			'hostname' => $data['db_hostname'],
			'username' => $data['db_username'],
			'password' => $data['db_password'] ?? '',
			'database' => $data['db_name'],
		);

		$new = $database_file;
		foreach ($values as $key => $value) {
			$new = $this->_set_db_value($new, $key, $value);
			if ($new === null) {
				log_message('error', 'database.php template does not contain the key: ' . $key);
				return false;
			}
		}
		log_message('info', 'Database config file prepared successfully. Writing to file...');

		// Write the new database.php file
		$handle = fopen($output_path, 'w+');
		if ($handle === false) {
			log_message('error', 'Failed to open target path for writing the database.php file.');
			return false;
		}

		// Verify file permissions
		if (is_writable($output_path)) {
			// Write the file
			if (fwrite($handle, $new)) {
				if(file_exists($output_path)) {
					log_message('info', 'database.php file written successfully.');
					return true;
				} else {
					log_message('error', 'database.php file not found after writing.');
					return false;
				}
			} else {
				return false;
			}
		} else {
			log_message('error', 'database.php path is not writable.');
			return false;
		}
	}

	// Function to write the config file
	function write_configfile($data) {

		$template_path 	= '../application/config/config.sample.php';
		$output_path 	= '../application/config/config.php';

		if (isset($_ENV['CI_ENV'])) {
			$output_path = '../application/config/'.$_ENV['CI_ENV'].'/config.php';
			$output_dir = dirname($output_path);
			if (!is_dir($output_dir)) {
				if (!mkdir($output_dir, 0755, true)) {
					log_message('error', 'Failed to create directory: ' . $output_dir);
					return false;
				}
				log_message('info', 'Directory created: ' . $output_dir);
			}
			log_message('info', 'CI_ENV is set to ' . $_ENV['CI_ENV'] . '. Using ' . $_ENV['CI_ENV'] . ' config.php config path.');
		} else {
			log_message('info', 'CI_ENV is not set. Using default config.php config path.');
		}

		// if config file already exists, we stop early and fail
		if (file_exists($output_path)) {
			log_message('error', 'config.php config file already exists');
			return false;
		}

		// Open the file
		$config_file = file_get_contents($template_path);
		if ($config_file === false) {
			log_message('error', 'Failed to read config.php template file.');
			return false;
		}
		log_message('info', 'config.php template file read successfully.');

		// creating a unique encryption key
		$encryptionkey = uniqid(bin2hex(random_bytes(8)), false);

		// Username/password-based providers
		$callbooks_userpass = ['qrz', 'hamqth', 'qrzcq', 'qrzru'];
		// Token-based providers (single "<provider>_token" config key)
		$callbooks_token    = ['qrzcall'];

		$selected = $data['global_call_lookup'] ?? '';

		$values = array(
			'directory'      => $this->_quote($data['directory']),
			'callbook'       => $this->_quote($selected),
			'locator'        => $this->_quote(strtoupper($data['userlocator'])),
			'base_url'       => $this->_quote($data['websiteurl']),
			'log_threshold'  => (int)$data['log_threshold'],
			'encryption_key' => $this->_quote($encryptionkey),
		);

		// Blank out every provider, only the selected one gets credentials.
		foreach ($callbooks_userpass as $cb) {
			$values[$cb . '_username'] = $this->_quote($cb === $selected ? ($data['callbook_username'] ?? '') : '');
			$values[$cb . '_password'] = $this->_quote($cb === $selected ? ($data['callbook_password'] ?? '') : '');
		}
		foreach ($callbooks_token as $cb) {
			$values[$cb . '_token'] = $this->_quote($cb === $selected ? ($data['callbook_token'] ?? '') : '');
		}

		$new = $config_file;
		foreach ($values as $key => $literal) {
			$new = $this->_set_config_value($new, $key, $literal);
			if ($new === null) {
				log_message('error', 'config.php template does not contain the key: ' . $key);
				return false;
			}
		}
		log_message('info', 'Config.php file prepared successfully. Writing to file...');

		// Write the new config.php file
		$handle = fopen($output_path, 'w+');
		if ($handle === false) {
			log_message('error', 'Failed to open target path for writing the config.php file.');
			return false;
		}

		// Verify file permissions
		if (is_writable($output_path)) {
			// Write the file
			if (fwrite($handle, $new)) {
				if(file_exists($output_path)) {
					log_message('info', 'config.php file written successfully.');
					$_SESSION['cron_auth_token'] = hash_hmac('sha256', 'wavelog-cron-v1', $encryptionkey);
					return true;
				} else {
					log_message('error', 'config.php file not found after writing.');
					return false;
				}
			} else {
				return false;
			}
		} else {
			log_message('error', 'config.php path is not writable.');
			return false;
		}
	}

	private function _sanitize($value){
		$value = str_replace('\\', '\\\\', $value ?? '');
		return str_replace("'", "\\'", $value);
	}

	// Turn a value into a single-quoted PHP string literal
	private function _quote($value) {
		return "'" . $this->_sanitize($value) . "'";
	}

	// Replace the value of a $config['<key>'] assignment in config.sample.php.
	// $literal must already be a valid PHP expression. Returns null if the key
	// is not found exactly once.
	private function _set_config_value($content, $key, $literal) {
		$pattern = '/^[ \t]*\$config\[\'' . preg_quote($key, '/') . '\'\][^;]*;/m';
		$new = preg_replace_callback($pattern, function () use ($key, $literal) {
			return "\$config['" . $key . "'] = " . $literal . ";";
		}, $content, 1, $count);

		return ($count === 1) ? $new : null;
	}

	// Replace the value of a '<key>' => '...' entry in database.sample.php.
	// Returns null if the key is not found exactly once.
	private function _set_db_value($content, $key, $value) {
		$pattern = '/^([ \t]*)\'' . preg_quote($key, '/') . '\'\s*=>\s*\'[^\']*\',/m';
		$new = preg_replace_callback($pattern, function ($m) use ($key, $value) {
			return $m[1] . "'" . $key . "' => " . $this->_quote($value) . ",";
		}, $content, 1, $count);

		return ($count === 1) ? $new : null;
	}
}
