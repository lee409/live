<?php
/**
 * 账号相关
 */
defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_sys_class('model', '', 0);
class account_model extends model {
	public function __construct() {
		$this->db_config = pc_base::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'member';
		parent::__construct();
	}

	/**
	 * 重置模型操作表表
	 * @param string $modelid 模型id
	 */
	public function set_model($modelid = '') {
		if($modelid) {
			$model = getcache('member_model', 'commons');
			if(isset($model[$modelid])) {
				$this->table_name = $this->db_tablepre.$model[$modelid]['tablename'];
			} else {
				$this->table_name = $this->db_tablepre.$model[10]['tablename'];
			}
		} else {
			$this->table_name = $this->db_tablepre.'member';
		}
	}
	
	/**
	 * 查询密码
	 *
	 * @param array $data        	
	 * @return multitype:Ambigous <string, boolean, NULL, unknown> |boolean
	 */
	public function fetchPwd($data) {
		if (empty ( $data ['account'] )) {
			return false;
		} else {
			$tablename = $this->db_tablepre . 'member';
			$member_info = $this->get_one ( array (
					'username' => $data ['account']
			) );
			return $member_info ['password'];
		}
	}
}