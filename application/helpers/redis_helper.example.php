<?php

class MYREDIS
{
	static private $redis;

	public static function r()
	{
		/* REF
		 * https://github.com/phpredis/phpredis
		 * */

		$host = '127.0.0.1';
		$post = 6379;
		$password = 'password';

		if (is_null(self::$redis)) {
			$redis = new Redis();
			$redis->connect($host, $post, 1, NULL, 0, 0, ['auth' => [$password]]);
			if (!$redis->ping()) {
				exit("REDIS PING ERROR");
			}
			self::$redis = $redis;
		}
		return self::$redis;
	}

}
