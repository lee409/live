<?php
defined ( 'IN_PHPCMS' ) or exit ( 'Access Invalid!' );
include api/control.php;
require(PHPCMS_PATH.'/api/control.php');
class loginregistControl extends hsshopControl {
	
	/**
	 * 允许直接访问
	 */
	protected $allow = array (
			'login',
			'regist'
	);
	const CACHE_PREFIX = 'app_index_';
	
	/**
	 * APP登录-
	 */
	public function loginOp() {
		if (! $this->checkPwd ()) {
			$this->output_error_json ( L ( 'account_error' ), 20205 );
		}
		$this->output_success_json('查询成功', 0);
	}
	
	public function registOp() {
		/*检测账号是否存在1*/
		if (! $this->checkAccount ()) {
			$this->output_error_json ( '账号已存在', 20205 );
		}
		/*检测注册的账号、密码*/
		$this->output_success_json('查询成功', 0);
	}
	
	public function homedataOp() {
		$this->output_success_json('查询成功', 0);
	}
	
	/**
	 * 验证密码
	 */
	private function checkPwd() {
		$model = pc_base::load_apimodel ( 'account_model' );
		$success = false;
		if($model->fetchPwd ( $this->data () )==$this->data()['password']){
			$success = true;
		}
		return $success;
	}
}