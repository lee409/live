<?php
class member_group{
	private $db;
	
	function __construct() {
		parent::__construct();
		$this->db = pc_base::load_model('api_member_group_model');
	}
	
	/**
	 * 会员组首页
	 */
	function init() {
	
		include $this->admin_tpl('member_init');
	}
	
	/**
	 * 会员组列表
	 */
	function manage() {
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$member_group_list = $this->db->listinfo('', 'sort ASC', $page, 15);
		$this->member_db = pc_base::load_model('member_model');
		//TODO 此处循环中执行sql，会严重影响效率，稍后考虑在memebr_group表中加入会员数字段和统计会员总数功能解决。
		foreach ($member_group_list as $k=>$v) {
			$membernum = $this->member_db->count(array('groupid'=>$v['groupid']));
			$member_group_list[$k]['membernum'] = $membernum;
		}
		$pages = $this->db->pages;
		json_encode($pages);
	}
}