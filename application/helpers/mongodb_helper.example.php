<?php

/*
 * https://docs.mongodb.com/php-library/v1.2/tutorial/crud/
 * */

 /*
$rs__cache = MYMONGODB::conn('dbname')->tb_name->find(
[
	'_id' => 1
]
);
$rs__cache = MYMONGODB::to_array($rs__cache);

$data = [];
$data['test'] = 'test111';

MYMONGODB::conn('dbname')->tb_name->insertOne($data);
  */

class MYMONGODB
{
	static private $db;

	public static function use_token__mongo() {
		return false;
	}

	public static function object_to_array($data)
	{
		if (is_array($data) || is_object($data)) {
			$result = [];
			foreach ($data as $key => $value) {
				$result[$key] = (is_array($data) || is_object($data)) ? self::object_to_array($value) : $value;
			}
			return $result;
		}
		return $data;
	}

	public static function to_array($cursor)
	{
		$data_list = [];
		foreach ($cursor as $document) {
			$data_list[] = self::object_to_array($document);
		}
		return $data_list;
	}

	public static function conn($db_name = null)
	{
		if (is_null(self::$db)) {
			self::$db = [];
//			self::$db['db_name'] = (new MongoDB\Client('mongodb://{username}:{password}@{hostOrIP}:{port}'))->db_name;
//			self::$db['db_name'] = new MongoDB\Client('mongodb://{username}:{password}@{hostOrIP}:{port}/{db_name}');
		}
		if (!is_null($db_name)) {
			return self::$db[$db_name];
		}
		return self::$db;
	}

}
