<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/API_Controller.php';

class Restful extends API_Controller
{

	function __construct()
	{
		parent::__construct();
	}

	/* /dev/examples/Restful/example */
	function example()
	{
		header("Access-Control-Allow-Origin: *");
		$this->_apiConfig([
			'methods' => ['POST'] // 'GET', 'OPTIONS'
		]);
		$stream_clean = $this->security->xss_clean($this->input->raw_input_stream);
		$reqJSONData = json_decode($stream_clean, true);

		/* SEND DATA RETURN */
		header("Content-type: application/json; charset=utf-8");
		echo json_encode($reqJSONData);
	}

	/* /dev/examples/Restful/ping */
	function ping()
	{
		header("Access-Control-Allow-Origin: *");
		$this->_apiConfig(['methods' => ['GET']]);
		$stream_clean = $this->security->xss_clean($this->input->raw_input_stream);
		$reqJSONData = json_decode($stream_clean, true);

		/* SEND DATA RETURN */
		header("Content-type: application/json; charset=utf-8");
		echo json_encode($reqJSONData);
	}

}
