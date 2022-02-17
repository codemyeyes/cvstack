<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {


	function __construct()
	{
		parent::__construct();
	}

	/* /dev/examples/Test */
	function index()
	{
		echo "/dev/examples/Test/";
	}

	/* /dev/examples/Test/mongo */
	function mongo()
	{
		echo "/dev/examples/Test/mongo";

		$data = [];
		$data['test_dt'] = date('Y-m-d H:i:s');
		MYMONGODB::conn('cvstack')->test->insertOne($data);

		$rs = MYMONGODB::conn('cvstack')->test->find();
		$rs = MYMONGODB::to_array($rs);
		alert($rs);
	}

	/* /dev/examples/Test/redis */
	function redis()
	{
		echo "/dev/examples/Test/redis";

		MYREDIS::r()->set("hello", "world");
		$rs = MYREDIS::r()->get("hello");
		alert($rs);
	}



}
