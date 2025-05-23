<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Qra {

	/*
	*	Class Description: QRA handles manipulation of the Gridsquares used within amateur radio
	*
	*	Units of measurement are the following
	*
	*	Info: Distance Function
	*
	*	M = Miles
	*	K = Kilometers
	*	N = Nautical Miles
	*/

	// Name: qra2latlong
	// Task: convert qra to lat/long
	function qra2latlong($strQRA) {
		return qra2latlong($strQRA);
	}

	// calculate  the bearing between two squares
	function bearing($tx, $rx, $unit = 'M', $ant_path = null) {
		$my = qra2latlong($tx);
		$stn = qra2latlong($rx);

		if ($my !== false && $stn !== false) {
			$bearing = bearing($my[0], $my[1], $stn[0], $stn[1], $unit, $ant_path);
			return $bearing;
		} else {
			return false;
		}
	}

	/*
	* Function: calculate the distance between two gridsqaures
	*
	*	Inputs are QRA's TX and TX and the unit
	*
	*/
	function distance($tx, $rx, $unit = 'M', $ant_path = null) {
		// Calc LatLongs
		$my = qra2latlong($tx);
		$stn = qra2latlong($rx);

		// Check if qra2latlong returned valid values
		if ($my && $stn) {
			// Feed in Lat Longs plus the unit type
			try {
				$total_distance = calc_distance($my[0], $my[1], $stn[0], $stn[1], $unit, $ant_path);
			} catch (Exception $e) {
				$total_distance = 0;
			}

			// Return the distance
			return $total_distance;
		} else {
			// Handle the case where qra2latlong did not return valid values
			return 0;
		}
	}

	/*
	* Function returns just the bearing
	*  Input locator1 and locator2
	*/
	function get_bearing($tx, $rx, $ant_path = null) {
		$my = qra2latlong($tx);
		$stn = qra2latlong($rx);
		return get_bearing($my[0], $my[1], $stn[0], $stn[1], $ant_path);
	}

	/*
	Find the Midpoint between two gridsquares using lat / long

	Needs following passed

	$coords[]=array('lat' => '53.344104','lng'=>'-6.2674937');
	$coords[]=array('lat' => '51.5081289','lng'=>'-0.128005');

*/

	function get_midpoint($coords) {
		$count_coords = count($coords);
		$xcos = 0.0;
		$ycos = 0.0;
		$zsin = 0.0;

		foreach ($coords as $lnglat) {
			$lat = $lnglat['lat'] * pi() / 180;
			$lon = $lnglat['lng'] * pi() / 180;

			$acos = cos($lat) * cos($lon);
			$bcos = cos($lat) * sin($lon);
			$csin = sin($lat);
			$xcos += $acos;
			$ycos += $bcos;
			$zsin += $csin;
		}

		$xcos /= $count_coords;
		$ycos /= $count_coords;
		$zsin /= $count_coords;
		$lon = atan2($ycos, $xcos);
		$sqrt = sqrt($xcos * $xcos + $ycos * $ycos);
		$lat = atan2($zsin, $sqrt);

		return array($lat * 180 / pi(), $lon * 180 / pi());
	}

	function echoQrbCalcLink($mygrid, $grid, $vucc, $isVisitor = false) {
		$echo = "";
		if (!empty($grid)) {
			$echo = $grid;
			$echo .= (!$isVisitor) ? (' <a href="javascript:spawnQrbCalculator(\'' . $mygrid . '\',\'' . $grid . '\')"><i class="fas fa-globe"></i></a>') : '';
		} else if (!empty($vucc)) {
			$echo = $vucc;
			$echo .= (!$isVisitor) ? (' <a href="javascript:spawnQrbCalculator(\'' . $mygrid . '\',\'' . $vucc . '\')"><i class="fas fa-globe"></i></a>') : '';
		}
		return $echo;
	}

	/**
	 * Function to calculate the maximum distance in a bunch of QSOs
	 *
	 * @param string 	$mylocator	Maidenhead locator of the station
	 * @param object 	$qsos		Object of QSOs
	 * @param string 	$unit		Unit of measurement
	 * 
	 * @return object 	$maxdistanceqso		Object of the QSO with the maximum distance
	 * @return float 	$maxdistance		Maximum distance
	 */

	function getMaxDistanceQSO($mylocator, $qsos, $unit = "M") {
		//return nothing for empty QSO set
		if (count($qsos->result()) < 1) {
			return null;
		}

		//collect maximum data
		$maxdistance = 0;
		$maxdistanceqso = null;

		//iterate through all qsos
		foreach ($qsos->result() as $row) {

			if (empty($row->COL_GRIDSQUARE)) {
				continue;
			}

			//get distance in kilometers
			$distance = $this->distance($mylocator, $row->COL_GRIDSQUARE, $unit);

			//store new highscore if present
			if ($distance > $maxdistance) {
				$maxdistance = $distance;
				$maxdistanceqso = $row;
			}
		}

		$result = [];
		$result['qso'] = $maxdistanceqso;
		$result['distance'] = $maxdistance;

		//return findings
		return $result;
	}

	/**
	 * Function to calculate the center of a bunch of coordinates
	 * Source: https://www.phpsnippet.com/snippet/center-point-of-multiple-gps-coordinates-with-php
	 * 
	 * @param array 	$coordinates	Array of coordinates [latitude, longitude]
	 * 					Example:
	 * 					$coordinates = [
	 *						[37.7797, -122.41924],
	 *						[37.77323, -122.41114],
	 *						[37.79203, -122.40864],
	 *						[37.7952, -122.4222]
	 *					];
	 * 					
	 * @return array 	[latitude, longitude]	Center of the coordinates
	 */

	function getCenterLatLng($coordinates) {
		$x = $y = $z = 0;
		$n = count($coordinates);
		foreach ($coordinates as $point)
		{
			$lt = $point[0] * pi() / 180;
			$lg = $point[1] * pi() / 180;
			$x += cos($lt) * cos($lg);
			$y += cos($lt) * sin($lg);
			$z += sin($lt);
		}
		$x /= $n;
		$y /= $n;

		return [atan2(($z / $n), sqrt($x * $x + $y * $y)) * 180 / pi(), atan2($y, $x) * 180 / pi()];
	}
}


function calc_distance($lat1, $lon1, $lat2, $lon2, $unit = 'M', $ant_path = null) {
	$theta = $lon1 - $lon2;
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	$dist = acos($dist);
	$dist = rad2deg($dist);
	$dist = $dist * 60 * 1.1515;

	if ($ant_path == "L") { // we only need to calculate the distance for long paths, all other paths are the same
		$dist = 24880 - $dist;
	}

	if ($unit == "K") {
		$dist *= 1.609344;
	} else if ($unit == "N") {
		$dist *= 0.8684;
	}
	if ((is_nan($dist)) || !(is_numeric($dist))) { 	// Special-Case, catch same grid and/or Errors
		$dist = 0;
	}
	return round($dist, 1);
}

function bearing($lat1, $lon1, $lat2, $lon2, $unit = 'M', $ant_path = null) {
	$dist = calc_distance($lat1, $lon1, $lat2, $lon2, $unit, $ant_path);
	$dist = round($dist, 0);

	$bearing = get_bearing($lat1, $lon1, $lat2, $lon2);
	if ($ant_path == 'L') {
		$bearing = ($bearing + 180) % 360;
	}

	$dirs = array("N", "E", "S", "W");

	$rounded = round($bearing / 22.5) % 16;
	if (($rounded % 4) == 0) {
		$dir = $dirs[$rounded / 4];
	} else {
		$dir = $dirs[2 * floor(((floor($rounded / 4) + 1) % 4) / 2)];
		$dir .= $dirs[1 + 2 * floor($rounded / 8)];
		#if ($rounded % 2 == 1)
		#  $dir = $dirs[round_to_int($rounded/4) % 4] . "-" . $dir;
	}
	$var_dist = "";
	#return $dir;
	if (isset($dist)) {
		$var_dist = $dist;
		switch ($unit) {
			case 'M':
				$var_dist .= " miles";
				break;
			case 'N':
				$var_dist .= " nautic miles";
				break;
			case 'K':
				$var_dist .= " kilometers";
				break;
		}
	}
	return round($bearing, 0) . "&#186; " . $dir . " " . $var_dist;
}

function get_bearing($lat1, $lon1, $lat2, $lon2) {
	return (int)(rad2deg(atan2(sin(deg2rad($lon2) - deg2rad($lon1)) * cos(deg2rad($lat2)), cos(deg2rad($lat1)) * sin(deg2rad($lat2)) - sin(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon2) - deg2rad($lon1)))) + 360) % 360;
}

function qra2latlong($strQRA) {
	$strQRA = preg_replace('/\s+/', '', $strQRA);
	if (substr_count($strQRA, ',') > 0) {
		if (substr_count($strQRA, ',') == 3) {
			// Handle grid corners
			$grids = explode(',', $strQRA);
			$gridlengths = array(strlen($grids[0]), strlen($grids[1]), strlen($grids[2]), strlen($grids[3]));
			$same = array_count_values($gridlengths);
			if (count($same) != 1) {
				return false;
			}
			$coords = array(0, 0);
			for ($i = 0; $i < 4; $i++) {
				$cornercoords[$i] = qra2latlong($grids[$i]);
				$coords[0] += $cornercoords[$i][0];
				$coords[1] += $cornercoords[$i][1];
			}
			return array(round($coords[0] / 4), round($coords[1] / 4));
		} else if (substr_count($strQRA, ',') == 1) {
			// Handle grid lines
			$grids = explode(',', $strQRA);
			if (strlen($grids[0]) != strlen($grids[1])) {
				return false;
			}
			$coords = array(0, 0);
			for ($i = 0; $i < 2; $i++) {
				$linecoords[$i] = qra2latlong($grids[$i]);
			}
			if ($linecoords[0][0] != $linecoords[1][0]) {
				$coords[0] = round((($linecoords[0][0] + $linecoords[1][0]) / 2), 1);
			} else {
				$coords[0] = round($linecoords[0][0], 1);
			}
			if ($linecoords[0][1] != $linecoords[1][1]) {
				$coords[1] = round(($linecoords[0][1] + $linecoords[1][1]) / 2);
			} else {
				$coords[1] = round($linecoords[0][1]);
			}
			return $coords;
		} else {
			return false;
		}
	}

	if ((strlen($strQRA) % 2 == 0) && (strlen($strQRA) <= 10)) {	// Check if QRA is EVEN (the % 2 does that) and smaller/equal 8
		$strQRA = strtoupper($strQRA);
		if (strlen($strQRA) == 2)  $strQRA .= "55";	// Only 2 Chars? Fill with center "55"
		if (strlen($strQRA) == 4)  $strQRA .= "LL";	// Only 4 Chars? Fill with center "LL" as only A-R allowed
		if (strlen($strQRA) == 6)  $strQRA .= "55";	// Only 6 Chars? Fill with center "55"
		if (strlen($strQRA) == 8)  $strQRA .= "LL";	// Only 8 Chars? Fill with center "LL" as only A-R allowed

		if (!preg_match('/^[A-R]{2}[0-9]{2}[A-X]{2}[0-9]{2}[A-X]{2}$/', $strQRA)) {
			return false;
		}

		list($a, $b, $c, $d, $e, $f, $g, $h, $i, $j) = str_split($strQRA, 1);	// Maidenhead is always alternating. e.g. "AA00AA00AA00" - doesn't matter how deep. 2 chars, 2 numbers, etc.
		$a = ord($a) - ord('A');
		$b = ord($b) - ord('A');
		$c = ord($c) - ord('0');
		$d = ord($d) - ord('0');
		$e = ord($e) - ord('A');
		$f = ord($f) - ord('A');
		$g = ord($g) - ord('0');
		$h = ord($h) - ord('0');
		$i = ord($i) - ord('A');
		$j = ord($j) - ord('A');

		$nLong = ($a * 20) + ($c * 2) + ($e / 12) + ($g / 120) + ($i / 2880) - 180;
		$nLat = ($b * 10) + $d + ($f / 24) + ($h / 240) + ($j / 5760) - 90;

		$arLatLong = array($nLat, $nLong);
		return $arLatLong;
	} else {
		return array(0, 0);
	}
}



/* End of file Qra.php */
