<?php

if (!function_exists('alert')) {
	function alert($inp)
	{
		echo "<pre>";
		print_r($inp);
		echo "</pre>";
	}
}

if (!function_exists('dd')) {
	function dd($inp)
	{
		echo "<pre>";
		print_r($inp);
		echo "</pre>";
		die();
	}
}

if (!function_exists('cors')) {
    function cors() {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
            // you want to allow, and if so:
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 1000');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                // may also be using PUT, PATCH, HEAD etc
                header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
            }

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: Accept, Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, Authorization");
            }
            exit(0);
        }
    }
}

if (!function_exists('sac_to_time')) {
	function sac_to_time($time)
	{
		$thistime = $time;
		$hour = floor($thistime / 3600);
		$T_minute = $thistime % 3600;

		$minute = floor($T_minute / 60);
		$second = $T_minute % 60;

		$data_time = array();
		$data_time['hour'] = sprintf("%02s", $hour);
		$data_time['minute'] = sprintf("%02s", $minute);
		$data_time['second'] = sprintf("%02s", $second);

		return $data_time;
	}
}

if (!function_exists('range_date')) {
	function range_date($first, $last)
	{
		$arr = array();
		$now = strtotime($first);
		$last = strtotime($last);
		while ($now <= $last) {
			$arr[] = date('Y-m-d', $now);
			$now = strtotime('+1 day', $now);
		}
		return $arr;
	}

}

if (!function_exists('DateDiff')) {
	function DateDiff($strDate1, $strDate2)
	{
		return (strtotime($strDate2) - strtotime($strDate1)) / (60 * 60 * 24);  // 1 day = 60*60*24
	}
}

if (!function_exists('TimeDiff')) {
	function TimeDiff($strTime1, $strTime2)
	{
		return (strtotime($strTime2) - strtotime($strTime1)) / (60 * 60); // 1 Hour =  60*60
	}
}

if (!function_exists('DateTimeDiff')) {
	function DateTimeDiff($strDateTime1, $strDateTime2)
	{
		return (strtotime($strDateTime2) - strtotime($strDateTime1)) / (60 * 60); // 1 Hour =  60*60
	}
}

if (!function_exists('hour_diff')) {
	function hour_diff($strDateTime1, $strDateTime2)
	{
		return (strtotime($strDateTime2) - strtotime($strDateTime1)) / (60 * 60); // sec
	}
}

if (!function_exists('minute_diff')) {
	function minute_diff($strDateTime1, $strDateTime2)
	{
		return (strtotime($strDateTime2) - strtotime($strDateTime1)) / (60); // sec
	}
}

if (!function_exists('dt_diff')) {
	function dt_diff($strDateTime1, $strDateTime2)
	{
		return (strtotime($strDateTime2) - strtotime($strDateTime1)); // sec
	}
}

function dtISO8601($inp = '', $mode = 'normal')
{
	if (empty($inp)) {
		$inp = date("Y-m-d H:i:s");
	}
	$now_c = date("c", strtotime($inp));
	if ($mode == 'normal') {
		$rnd = rand(100, 999);
		$now_c_with_ms = str_replace("+", '.' . $rnd . '+', $now_c);
	} else if ($mode == 'date_hour') {
		$now_c_with_ms = str_replace(':00:00', '', $now_c);
	}
	return $now_c_with_ms;
}

function GUID()
{
	if (function_exists('com_create_guid') === true) {
		return trim(com_create_guid(), '{}');
	}
	return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

if (!function_exists('objectToArray')) {
	function objectToArray($object)
	{
		if (!is_object($object) && !is_array($object)) {
			return $object;
		}
		if (is_object($object)) {
			$object = get_object_vars($object);
		}
		return array_map('objectToArray', $object);
	}
}

if (!function_exists('object_to_array')) {
	function object_to_array($data)
	{
		if (is_array($data) || is_object($data)) {
			$result = [];
			foreach ($data as $key => $value) {
				$result[$key] = (is_array($data) || is_object($data)) ? object_to_array($value) : $value;
			}
			return $result;
		}
		return $data;
	}
}

if (!function_exists('arrayToXml')) {
	function arrayToXml($array, $rootElement = null, $xml = null)
	{
		$_xml = $xml;

		// If there is no Root Element then insert root
		if ($_xml === null) {
			$_xml = new SimpleXMLElement($rootElement !== null ? $rootElement : '<root/>');
		}

		// Visit all key value pair
		foreach ($array as $k => $v) {

			// If there is nested array then
			if (is_array($v)) {

				// Call function for nested array
				arrayToXml($v, $k, $_xml->addChild($k));
			} else {

				// Simply add child element.
				$_xml->addChild($k, $v);
			}
		}

		return $_xml->asXML();
	}
}