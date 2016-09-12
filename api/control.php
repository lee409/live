<?php
/**
 * 接口基类
 * Created by Gex.
 * Date: 15-3-27
 */
pc_base::load_sys_class ( 'cache', '', 0 );
define ( 'APPNAME', 'com.lxm.live' );
abstract class Control {
	protected $error;
	protected $requestData;
	protected $debug = false;
	protected $memcache = null;
	public function __construct() {
		// 处理未捕获异常
		$this->setErrHandler ();
		// 初始化数据
		$this->_init ();
		// 安全性验证
		$this->_verification ();
		// 执行
		$this->_run ();
	}
	/**
	 * 初始化
	 */
	protected function _init() {
		$this->requestData = $this->debug ? $_GET : $_POST;
	}
	/**
	 * 执行
	 */
	protected function _run() {
	}
	/**
	 * 取值
	 *
	 * @param unknown $key        	
	 * @param string $defaultValue        	
	 * @return Ambigous <string, unknown>
	 */
	protected function data($key, $defaultValue = 0) {
		if (! isset ( $key )) {
			return $this->requestData;
		}
		$value = $this->requestData [$key];
		return $value ? $value : $defaultValue;
	}
	private function setErrHandler() {
		set_exception_handler ( array (
				$this,
				'exceptionHandlder' 
		) );
		set_error_handler ( array (
				$this,
				'errorHandler' 
		) );
	}
	
	/**
	 * 输出json数据
	 *
	 * @param int $code        	
	 * @param string $message        	
	 * @param array $data        	
	 */
	function output_jsondata($code, $message, $data) {
		$data = array (
				'code' => $code,
				'message' => $message,
				'data' => $data 
		);
		header ( 'Content-Type:application/json; charset=utf-8' );
		exit ( json_encode ( $data, JSON_UNESCAPED_UNICODE ) );
	}
	function output_error_json($message, $code = 1, $data = null) {
		$this->output_jsondata ( $code, $message, $data );
	}
	function output_success_json($message = '操作成功', $code = 0, $data = null) {
		$this->output_jsondata ( $code, $message, $data );
	}
	function output_nologin_json($message = '您还未登录', $data = null) {
		$this->output_jsondata ( 99, $message, $data );
	}
	public function exceptionHandlder($e) {
		$this->output_error_json ( $e->getMessage (), 9999 );
	}
	// 验证请求的合法性
	abstract protected function _verification();
}
class hsshopControl extends Control {
	
	/**
	 * 不用登录也可以访问的方法列表
	 */
	protected $allowCheckPwd = array (
			'regist',
			'login' 
	);
		
	/**
	 * 是否开启调试模式
	 */
	protected $debug = false;
	
	/**
	 * 检查参数vcode
	 */
	protected function _verification() {
		if (! $this->debug) {
			if (in_array ( $_GET ['op'], $this->allowCheckPwd )) {
			} else {
				if (! $this->checkVcode ()) {
					$this->output_error_json ( L ( 'token_error' ), 20204 );
				}
			}
		}
	}
	protected function _init() {
		parent::_init ();
		// 断商城是否维护中
		// if (C('site_status')==-1) {
		// $this->output_error_json('系统正在升级维护中……');
		// }
		
		// 取版本号
		$this->requestData ['app_version'] = 'v1.2';
		$user_agent = $_SERVER ['HTTP_USER_DATA'];
		if ($user_agent) {
			$user_data = json_decode ( $user_agent, true );
			$this->requestData ['app_version'] = $user_data ['app_version'];
			$this->requestData ['mobile_model'] = $user_data ['mobile_model'];
			$this->requestData ['sys_version'] = $user_data ['sys_version'];
		}
		
		// 口调用日志
		// $this->addApiRequestLog();
		
		// 动签到
		// $this->checkSignedData();
	}
	
	/**
	 * 检测逻辑
	 */
	protected function checkVcode() {
		//http://www.lxm.com/live_demo/api.php?
		//act=loginregist&op=homedata&usercode=db98f87a-b1b5-0e88-48db-6dbbe4f2bb7c&
		//tokencode=13acb42fc41b4dfdfdd7041354eff473&
		//vcode=13acb42fc41b4dfdfdd7041354eff473364cac0ee584a41c7d9c380651dfca79&
		//reqtime=1472782904&method=addFeedback
		$result = false;
		$creatorCode = APPNAME;
		$userCode = $this->requestData['userid'];
		$tokenCode = $this->requestData ['tokencode'];
		$vcodeD = $this->requestData ['vcode'];
		$reqtime = $this->requestData ['reqtime'];
		$method = $_GET ['op'];
		
		if (isset ( $tokenCode ) && isset ( $vcodeD ) && isset ( $userCode )) {			
			$vcodeS = $tokenCode . "" . md5 ( $method . "" . APPNAME . "" . md5 ( substr ( $userCode, 0, 6 ) ) );
// 			var_dump(md5 ( substr ( $userCode, 0, 6 ) ));
// 			var_dump($method . "" . APPNAME . "" . md5 ( substr ( $userCode, 0, 6 ) ));
// 			var_dump(md5 ( $method . "" . APPNAME . "" . md5 ( substr ( $userCode, 0, 6 ) ) ));
// 			var_dump($tokenCode . "" . md5 ( $method . "" . APPNAME . "" . md5 ( substr ( $userCode, 0, 6 ) ) ));
// 			2016-09-02 14:59:11.927 BMSQS[98434:765353] userCode 的前六位=db98f8
// 			2016-09-02 14:59:11.927 BMSQS[98434:765353] md5( userCode 的前六位 )=4429af51474539662bcf0310c72aee07
// 			2016-09-02 14:59:11.927 BMSQS[98434:765353] “methodName” + params + md5( userCode 的前六位 )=addFeedbackcom.lxm.live4429af51474539662bcf0310c72aee07
// 			2016-09-02 14:59:11.927 BMSQS[98434:765353] md5( “methodName” + params + md5( userCode 的前六位 )=364cac0ee584a41c7d9c380651dfca79
// 			2016-09-02 14:59:11.927 BMSQS[98434:765353] token + md5( “methodName” + params + md5( userCode 的前六位 ))=13acb42fc41b4dfdfdd7041354eff473364cac0ee584a41c7d9c380651dfca79
					
			if ($vcodeD == $vcodeS)
				$result = true;
		}
		return $result;
	}
	
	/**
	 * api接口调用日志
	 */
	protected function addApiRequestLog() {
		$requestLogs = array ();
		$requestLogs ['mid'] = $_REQUEST ['mid'];
		$requestLogs ['action_operate'] = $_REQUEST ['act'] . '-' . $_REQUEST ['op'];
		$requestLogs ['paras'] = var_export ( $_REQUEST, true );
		$requestLogs ['addtime'] = TIMESTAMP;
		$requestLogs ['user_data'] = addslashes ( stripslashes ( $_SERVER ['HTTP_USER_DATA'] ) );
		if (in_array ( $_REQUEST ['op'], array (
				'register',
				'login' 
		) )) {
			$requestLogs ['paras'] = preg_replace ( "#'password(.*),#i", '', $requestLogs ['paras'] );
		}
		$model = pc_base::load_model ( 'account' );
		$model->insertHshopRequestLog ( $requestLogs ); // 双11暂时下线，避免影响数据库
		
		return true;
	}
	
	/**
	 * API接口被动签到
	 */
	protected function checkSignedData() {
		$member_id = $this->requestData ['mid'];
		if ($member_id <= 0)
			return;
		
		$signed_key = date ( 'Ymd' ) . $member_id;
		if (MemcacheGet ( $signed_key ) == false) {
			$scorecard = Logic ( 'scorecard' )->checkinScorecard ( $member_id, true );
			if ($scorecard ['state'] == true) {
				MemcacheAdd ( $signed_key, 1, 7200 );
			}
		}
		
		return;
	}
	
	/**
	 * 执行接口方法，支持多版本接口，注意：不用的接口版本方法需要删除！
	 */
	protected function _run() {
		$method_default = $_GET ['op'];
		
		$method_default .= 'Op';
		if (method_exists ( $this, $method_default )) {
			$this->$method_default ();
		} else {
			$this->output_error_json ( L ( 'api_call_error' ) );
		}
		exit ();
	}
}