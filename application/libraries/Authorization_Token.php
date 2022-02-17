<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Authorization_token
 * ----------------------------------------------------------
 * API Token Generate/Validation
 *
 * @author: Jeevan Lal
 * @version: 0.0.1
 */

require_once APPPATH . 'third_party/php-jwt/JWT.php';
require_once APPPATH . 'third_party/php-jwt/BeforeValidException.php';
require_once APPPATH . 'third_party/php-jwt/ExpiredException.php';
require_once APPPATH . 'third_party/php-jwt/SignatureInvalidException.php';

use \Firebase\JWT\JWT;

class Authorization_Token
{
	/**
	 * Token Key
	 */
	protected $token_key;

	/**
	 * Token algorithm
	 */
	protected $token_algorithm;

	/**
	 * Token Request Header Name
	 */
	protected $token_header;

	/**
	 * Token Expire Time
	 */
	protected $token_expire_time;

	protected $JWT_ACCESS_SECRET;
	protected $JWT_REFRESH_SECRET;


	public function __construct()
	{
		$this->CI =& get_instance();

		/**
		 * jwt config file load
		 */
		$this->CI->load->config('jwt');

		/**
		 * Load Config Items Values
		 */
		$this->token_key = $this->CI->config->item('jwt_key');
		$this->token_algorithm = $this->CI->config->item('jwt_algorithm');
		$this->token_header = $this->CI->config->item('token_header');
		$this->token_expire_time = $this->CI->config->item('token_expire_time');


		$this->JWT_ACCESS_SECRET = $this->CI->config->item('JWT_ACCESS_SECRET');
		$this->JWT_REFRESH_SECRET = $this->CI->config->item('JWT_REFRESH_SECRET');

		$this->JWT_ACCESS_TIME = $this->CI->config->item('JWT_ACCESS_TIME');
		$this->JWT_REFRESH_TIME = $this->CI->config->item('JWT_REFRESH_TIME');

	}


	public function test()
	{
		alert('JWT_SECRET ========');
		alert($this->JWT_ACCESS_SECRET);
		alert($this->JWT_REFRESH_SECRET);

		alert('JWT_TIME ========');
		alert($this->JWT_ACCESS_TIME);
		alert($this->JWT_REFRESH_TIME);

		$data = [];
		$data['username'] = 'nics';

		$accessToken = $this->genAccessToken($data);
		alert("ACCESS_TOKEN ======");
		alert($accessToken);

	}

	public function genToken($data)
	{
		$rs__genAccessToken = $this->__genToken($this->JWT_ACCESS_SECRET, $data);
		if ($rs__genAccessToken['code'] != 0) {
			$rs__genAccessToken['msg'] = 'genAccessToken:' . $rs__genAccessToken['msg'];
			return $rs__genAccessToken;
		}

		$rs__genRefreshToken = $this->__genToken($this->JWT_REFRESH_SECRET, $data);
		if ($rs__genRefreshToken['code'] != 0) {
			$rs__genRefreshToken['msg'] = 'genAccessToken:' . $rs__genRefreshToken['msg'];
			return $rs__genRefreshToken;
		}

		$res = [];
		$res['access_token'] = $rs__genAccessToken['res']['token'];
		$res['refresh_token'] = $rs__genRefreshToken['res']['token'];

		$rt = [];
		$rt['code'] = 0;
		$rt['msg'] = 'ok';
		$rt['res'] = $res;
		return $rt;
	}

	/*
	public function verifyAccessToken()
	{
		$rs__verifyToken = $this->__verifyToken('access_token');

		if ($rs__verifyToken['status'] == false) {
			$rt = [];
			$rt['code'] = 990100;
			$rt['msg'] = "verifyAccessToken:" . $rs__verifyToken['message'];
			return $rt;
		}

		$res = [];
		$res['data'] = $rs__verifyToken['data'];

		$rt = [];
		$rt['code'] = 0;
		$rt['msg'] = 'ok';
		$rt['res'] = $res;
		return $rt;
	}
	*/

	public function verifyAccessToken()
	{
		$chk = [];
		$is_expire = 0;

		$headers = $this->CI->input->request_headers();
		$token_data = $this->tokenIsExist($headers);

		if ($token_data['status'] === TRUE) {
			if (empty($token_data['token'])) {
				$chk = ['status' => FALSE, 'message' => 'Token is not defined.'];
			} else {
				try {
					$token__ex = explode(' ', $token_data['token']);
					if (count($token__ex) > 1) {
						$token_data['token'] = $token__ex[count($token__ex) - 1];
					}

					try {
						$token_decode = JWT::decode($token_data['token'], $this->JWT_ACCESS_SECRET, array($this->token_algorithm));
					} catch (Exception $e) {
						$chk = ['status' => FALSE, 'message' => $e->getMessage()];
					}

					if (!empty($token_decode) and is_object($token_decode)) {
						if (empty($token_decode->API_TIME or !is_numeric($token_decode->API_TIME))) {
							$chk = ['status' => FALSE, 'message' => 'Token Time Not Define!'];
						} else {
							$time_difference = strtotime('now') - $token_decode->API_TIME;
							if ($time_difference >= $this->JWT_ACCESS_TIME) {
								$chk = ['status' => FALSE, 'data' => $token_decode, 'message' => 'Token Time Expire.'];
								$is_expire = 1;
							} else {
								$chk = ['status' => TRUE, 'data' => $token_decode];
							}
						}
					} else {
						$chk = ['status' => FALSE, 'message' => 'Forbidden'];
					}
				} catch (Exception $e) {
					$chk = ['status' => FALSE, 'message' => $e->getMessage()];
				}
			}
		} else {
			$chk = ['status' => FALSE, 'message' => $token_data['message']];
		}


		if ($chk['status'] == false) {
			$res = [];
			$res['data'] = ($is_expire == 0) ? [] : objectToArray($token_decode);

			$rt = [];
			$rt['code'] = 990100;
			$rt['is_expire'] = $is_expire;
			$rt['msg'] = "verifyAccessToken:" . $chk['message'];
			$rt['res'] = $res;
			return $rt;
		}

		$res = [];
		$res['access_token'] = $token_data['token'];
		$res['data'] = objectToArray($token_decode);

		$rt = [];
		$rt['code'] = 0;
		$rt['is_expire'] = $is_expire;
		$rt['msg'] = "verifyAccessToken:ok";
		$rt['res'] = $res;
		return $rt;
	}

	public function verifyRefreshToken($token)
	{
		$chk = [];
		$is_expire = 0;

		if (empty($token)) {
			$chk = ['status' => FALSE, 'message' => 'Token is not defined.'];
		} else {
			try {
				try {
					$token_decode = JWT::decode($token, $this->JWT_REFRESH_SECRET, array($this->token_algorithm));
				} catch (Exception $e) {
					$chk = ['status' => FALSE, 'message' => $e->getMessage()];
				}

				if (!empty($token_decode) and is_object($token_decode)) {
					if (empty($token_decode->API_TIME or !is_numeric($token_decode->API_TIME))) {
						$chk = ['status' => FALSE, 'message' => 'Token Time Not Define!'];
					} else {
						$time_difference = strtotime('now') - $token_decode->API_TIME;
						if ($time_difference >= $this->JWT_REFRESH_TIME) {
							$chk = ['status' => FALSE, 'data' => $token_decode, 'message' => 'Token Time Expire.'];
							$is_expire = 1;
						} else {
							$chk = ['status' => TRUE, 'data' => $token_decode];
						}
					}
				} else {
					$chk = ['status' => FALSE, 'message' => 'Forbidden'];
				}
			} catch (Exception $e) {
				$chk = ['status' => FALSE, 'message' => $e->getMessage()];
			}
		}


		if ($chk['status'] == false) {
			$res = [];
			$res['data'] = ($is_expire == 0) ? [] : objectToArray($token_decode);

			$rt = [];
			$rt['code'] = 990100;
			$rt['is_expire'] = $is_expire;
			$rt['msg'] = "verifyRefreshToken:" . $chk['message'];
			$rt['res'] = $res;
			return $rt;
		}

		$res = [];
		$res['data'] = objectToArray($token_decode);

		$rt = [];
		$rt['code'] = 0;
		$rt['is_expire'] = $is_expire;
		$rt['msg'] = "verifyRefreshToken:ok";
		$rt['res'] = $res;
		return $rt;
	}

	public function __genToken($JWT_SECRET, $data = null)
	{
		if ($data and is_array($data)) {
			// add api time key in user array()
			$data['API_TIME'] = time();
			try {
				$res = [];
				$res['token'] = JWT::encode($data, $JWT_SECRET, $this->token_algorithm);

				$rt = [];
				$rt['code'] = 0;
				$rt['msg'] = 'ok';
				$rt['res'] = $res;
				return $rt;
			} catch (Exception $e) {
				$rt = [];
				$rt['code'] = 990000;
				$rt['msg'] = $e->getMessage();
				return $rt;
			}
		} else {
			$rt = [];
			$rt['code'] = 990001;
			$rt['msg'] = "Token Data Undefined!";
			return $rt;
		}
	}

	public function __verifyToken($JWT_TYPE)
	{
		$headers = $this->CI->input->request_headers();
		$token_data = $this->tokenIsExist($headers);

		$token__ex = explode(' ', $token_data['token']);
		if (count($token__ex) > 1) {
			$token_data['token'] = $token__ex[count($token__ex) - 1];
		}

		$JWT_SECRET = '';
		$JWT_EXPIRE_TIME = 0;
		if ($JWT_TYPE == 'access_token') {
			$JWT_SECRET = $this->JWT_ACCESS_SECRET;
			$JWT_EXPIRE_TIME = $this->JWT_ACCESS_TIME;
		} else if ($JWT_TYPE == 'refresh_token') {
			$JWT_SECRET = $this->JWT_REFRESH_SECRET;
			$JWT_EXPIRE_TIME = $this->JWT_REFRESH_TIME;
		}

		if ($token_data['status'] === TRUE) {
			try {
				try {
					$token_decode = JWT::decode($token_data['token'], $JWT_SECRET, array($this->token_algorithm));
				} catch (Exception $e) {
					return ['status' => FALSE, 'message' => $e->getMessage()];
				}

				if (!empty($token_decode) and is_object($token_decode)) {
					if (empty($token_decode->API_TIME or !is_numeric($token_decode->API_TIME))) {
						return ['status' => FALSE, 'message' => 'Token Time Not Define!'];
					} else {
						$time_difference = strtotime('now') - $token_decode->API_TIME;
						if ($time_difference >= $JWT_EXPIRE_TIME) {
							return ['status' => FALSE, 'data' => $token_decode, 'message' => 'Token Time Expire.'];
						} else {
							return ['status' => TRUE, 'data' => $token_decode];
						}
					}
				} else {
					return ['status' => FALSE, 'message' => 'Forbidden'];
				}
			} catch (Exception $e) {
				return ['status' => FALSE, 'message' => $e->getMessage()];
			}
		} else {
			return ['status' => FALSE, 'message' => $token_data['message']];
		}
	}


	/**
	 * Generate Token
	 * @param: {array} data
	 */
	public function generateToken($data = null)
	{
		if ($data and is_array($data)) {
			// add api time key in user array()
			$data['API_TIME'] = time();

			try {
				return JWT::encode($data, $this->token_key, $this->token_algorithm);
			} catch (Exception $e) {
				return 'Message: ' . $e->getMessage();
			}
		} else {
			return "Token Data Undefined!";
		}
	}

	/**
	 * Validate Token with Header
	 * @return : user information's
	 */
	public function validateToken()
	{
		/**
		 * Request All Headers
		 */
		$headers = $this->CI->input->request_headers();

		/**
		 * Authorization Header Exists
		 */
		$token_data = $this->tokenIsExist($headers);
		if ($token_data['status'] === TRUE) {
			try {
				/**
				 * Token Decode
				 */
				try {
					$token_decode = JWT::decode($token_data['token'], $this->token_key, array($this->token_algorithm));
				} catch (Exception $e) {
					return ['status' => FALSE, 'message' => $e->getMessage()];
				}

				if (!empty($token_decode) and is_object($token_decode)) {
					// Check Token API Time [API_TIME]
					if (empty($token_decode->API_TIME or !is_numeric($token_decode->API_TIME))) {

						return ['status' => FALSE, 'message' => 'Token Time Not Define!'];
					} else {
						/**
						 * Check Token Time Valid
						 */
						$time_difference = strtotime('now') - $token_decode->API_TIME;
						if ($time_difference >= $this->token_expire_time) {
							return ['status' => FALSE, 'message' => 'Token Time Expire.'];

						} else {
							/**
							 * All Validation False Return Data
							 */
							return ['status' => TRUE, 'data' => $token_decode];
						}
					}

				} else {
					return ['status' => FALSE, 'message' => 'Forbidden'];
				}
			} catch (Exception $e) {
				return ['status' => FALSE, 'message' => $e->getMessage()];
			}
		} else {
			// Authorization Header Not Found!
			return ['status' => FALSE, 'message' => $token_data['message']];
		}
	}

	/**
	 * Token Header Check
	 * @param: request headers
	 */
	private function tokenIsExist($headers)
	{
		if (!empty($headers) and is_array($headers)) {
			foreach ($headers as $header_name => $header_value) {
				if (strtolower(trim($header_name)) == strtolower(trim($this->token_header)))
					return ['status' => TRUE, 'token' => $header_value];
			}
		}
		return ['status' => FALSE, 'message' => 'Token is not defined.'];
	}
}
