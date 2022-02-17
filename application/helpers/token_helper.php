<?php

class TOKEN
{
	private static $mode = 'jwt'; // 'normal', 'jwt'

	private static $ttl = 60 * 60;
	private static $prefix_token_user_id = 'token_user_id:';

	private static function jwt_code_error($inp_code)
	{
		$jwt_code_prefix = 950000;
		$code = [];

		$code[950100] = [];
		$code[950100]['code'] = 950100;
		$code[950100]['msg'] = 'logout_or_expire_time';

		$code[951100] = [];
		$code[951100]['code'] = 951100;
		$code[951100]['msg'] = 'refresh_token__empty';

		$code[951110] = [];
		$code[951110]['code'] = 951110;
		$code[951110]['msg'] = 'refresh_token__invalid';

		if (empty($code[$inp_code])) {
			$rt = [];
			$rt['code'] = $inp_code;
			$rt['msg'] = $inp_code;
			return $rt;
		}

		$rt = [];
		$rt['code'] = $code[$inp_code]['code'];
		$rt['msg'] = $code[$inp_code]['msg'];
		return $rt;
	}

	public static function gen_token($mode, $payload)
	{
		if ($mode == 'jwt') {
			return self::jwt__gen_token($payload);
		}
	}

	public static function jwt__gen_token($payload)
	{
		$CI =& get_instance();
		$rs__gen_token = $CI->authorization_token->genToken($payload);

//		$access_token = $rs__gen_token['res']['access_token'];
		$refresh_token = $rs__gen_token['res']['refresh_token'];
		$redis__user_id = self::$prefix_token_user_id . $payload['user_id'];
		$redis__user_id__nx_xx = (MYREDIS::r()->exists($redis__user_id) == 0) ? 'nx' : 'xx';

		$token_detail = [];
		if ($redis__user_id__nx_xx == 'xx') {
			$token_detail = json_decode(MYREDIS::r()->get($redis__user_id), true);
		}
		$token_detail['jwt'] = [];
//		$token_detail['jwt']['access_token'] = $access_token;
		$token_detail['jwt']['refresh_token'] = $refresh_token;

		MYREDIS::r()->set($redis__user_id, json_encode($token_detail), [$redis__user_id__nx_xx, 'ex' => self::$ttl]);

		$res = [];
//		$res['data'] = $payload;
		$res['access_token'] = $rs__gen_token['res']['access_token'];
		$res['refresh_token'] = $rs__gen_token['res']['refresh_token'];

		$rt = [];
		$rt['code'] = 0;
		$rt['msg'] = 'ok';
		$rt['res'] = $res;
		return $rt;
	}

	public static function check($mode, $token = '')
	{
		if ($mode == 'jwt') {
			return self::jwt__check();
		}
	}

	public static function jwt__check()
	{
		$CI =& get_instance();
		$rs__verifyAccessToken = $CI->authorization_token->verifyAccessToken();
		if ($rs__verifyAccessToken['code'] != 0) {
			return $rs__verifyAccessToken;
		}
		$user_id = $rs__verifyAccessToken['res']['data']['user_id'];

		$redis__user_id = self::$prefix_token_user_id . $user_id;
		if (MYREDIS::r()->exists($redis__user_id) == 0) {
			return self::jwt_code_error(950100);
		}

		$token_detail__json = MYREDIS::r()->get($redis__user_id);
		MYREDIS::r()->set($redis__user_id, $token_detail__json, ['xx', 'ex' => self::$ttl]);

		$res = [];
		$res['data'] = $rs__verifyAccessToken['res']['data'];

		$rt = [];
		$rt['code'] = 0;
		$rt['is_expire'] = $rs__verifyAccessToken['is_expire'];
		$rt['msg'] = 'ok';
		$rt['res'] = $res;
		return $rt;
	}

	public static function logout($mode)
	{
		if ($mode == 'jwt') {
			return self::jwt__logout();
		}
	}

	public static function jwt__logout()
	{
		$CI =& get_instance();
		$rs__verifyAccessToken = $CI->authorization_token->verifyAccessToken();
		if ($rs__verifyAccessToken['is_expire'] == 1) {

		} else if ($rs__verifyAccessToken['code'] != 0) {
			return $rs__verifyAccessToken;
		}
		$user_id = $rs__verifyAccessToken['res']['data']['user_id'];

		$redis__user_id = self::$prefix_token_user_id . $user_id;
		MYREDIS::r()->unlink($redis__user_id);

		$rt = [];
		$rt['code'] = 0;
		$rt['msg'] = 'logout';
		return $rt;
	}

	public static function refresh_token($mode, $refresh_token = '')
	{
		if ($mode == 'jwt') {
			return self::jwt__refresh_token($refresh_token);
		}
	}

	public static function jwt__refresh_token($refresh_token)
	{
		$CI =& get_instance();
		$rs__verifyAccessToken = $CI->authorization_token->verifyAccessToken();
		if ($rs__verifyAccessToken['is_expire'] == 1) {

		} else if ($rs__verifyAccessToken['code'] != 0) {
			return $rs__verifyAccessToken;
		}
		$user_id = $rs__verifyAccessToken['res']['data']['user_id'];

		$redis__user_id = self::$prefix_token_user_id . $user_id;
		if (MYREDIS::r()->exists($redis__user_id) == 0) {
			return self::jwt_code_error(950100);
		}
		$token_detail = json_decode(MYREDIS::r()->get($redis__user_id), true);

		if (empty($token_detail['jwt'])) {
			return self::jwt_code_error(951100);
		} else if ($token_detail['jwt']['refresh_token'] != $refresh_token) {
			return self::jwt_code_error(951110);
		}
		$rs__verifyRefreshToken = $CI->authorization_token->verifyRefreshToken($refresh_token);
		if ($rs__verifyRefreshToken['code'] != 0) {
			return $rs__verifyRefreshToken;
		}

		$rs__jwt__gen_token = self::jwt__gen_token($rs__verifyRefreshToken['res']['data']);
		$token_detail['jwt']['refresh_token'] = $rs__jwt__gen_token['res']['refresh_token'];
		MYREDIS::r()->set($redis__user_id, json_encode($token_detail), ['xx', 'ex' => self::$ttl]);
		return $rs__jwt__gen_token;
	}

}
