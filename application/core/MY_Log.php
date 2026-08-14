<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Writes NDJSON (one JSON object per line) when $config['log_file_extension']
 * is set to 'json'. Any other extension keeps CodeIgniter's text format.
 *
 * With $config['log_to_stdout'] - or WAVELOG_LOG_STDOUT in the container - the
 * log goes to the process stdout instead of a file, always as NDJSON.
 */
class MY_Log extends CI_Log {

	/**
	 * Write to stdout instead of a log file
	 *
	 * @var bool
	 */
	protected $_stdout = FALSE;

	/**
	 * Emit NDJSON instead of CodeIgniter's text format
	 *
	 * @var bool
	 */
	protected $_json = FALSE;

	public function __construct()
	{
		parent::__construct();

		$config =& get_config();

		// The container sets WAVELOG_LOG_STDOUT, normalised by the entrypoint.
		$env = getenv('WAVELOG_LOG_STDOUT');
		$this->_stdout = ($env === FALSE OR $env === '')
			? ! empty($config['log_to_stdout'])
			: ($env === 'true');

		// Writing to stdout only makes sense in a machine-readable format.
		$this->_json = ($this->_stdout OR $this->_file_ext === 'json');

		// ISO 8601 is what log shippers expect, but never override an
		// explicitly configured format.
		if ($this->_json
			&& (empty($config['log_date_format']) OR $config['log_date_format'] === 'Y-m-d H:i:s'))
		{
			$this->_date_fmt = DateTime::ATOM;
		}
	}

	public function write_log($level, $msg)
	{
		if ($this->_stdout === FALSE)
		{
			return parent::write_log($level, $msg);
		}

		$level = strtoupper($level);

		if (( ! isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))
			&& ! isset($this->_threshold_array[$this->_levels[$level]]))
		{
			return FALSE;
		}

		// One write per line keeps entries atomic on the pipe (up to PIPE_BUF).
		$result = @file_put_contents('php://stdout', $this->_format_line($level, $this->_now(), $msg));

		return is_int($result);
	}

	protected function _format_line($level, $date, $message)
	{
		if ($this->_json === FALSE)
		{
			return parent::_format_line($level, $date, $message);
		}

		$line = json_encode(
			array('time' => $date, 'type' => 'app', 'level' => $level, 'message' => $message),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
		);

		return ($line === FALSE)
			? parent::_format_line($level, $date, $message)
			: $line.PHP_EOL;
	}

	/**
	 * Current timestamp, with microsecond support like CI_Log::write_log() has.
	 */
	protected function _now()
	{
		if (strpos($this->_date_fmt, 'u') === FALSE)
		{
			return date($this->_date_fmt);
		}

		$microtime_full = microtime(TRUE);
		$microtime_short = sprintf('%06d', ($microtime_full - floor($microtime_full)) * 1000000);
		$date = new DateTime(date('Y-m-d H:i:s.'.$microtime_short, $microtime_full));

		return $date->format($this->_date_fmt);
	}
}
