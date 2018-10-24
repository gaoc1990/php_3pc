<?php 
/**
 * 系统常量
 */
class Constant{
	//事务状态
	public static $tx_status_begin = 1;
	public static $tx_status_cancommit = 2;
	public static $tx_status_precommit = 3;
	public static $tx_status_commit = 4;
	public static $tx_status_rollback = 5;

	//事务执行应答
	public static $tx_complete_fail = 0;
	public static $tx_complete_ok = 1;
	
	//事务补偿类型
	public static $comp_type_save = 0;
	public static $comp_type_del = 1;
	public static $comp_type_update = 2;
	public static $comp_type_compensation = 3;

	//补偿操作类型
	public static $comp_operate_type_update = 1;
	public static $comp_operate_type_comp = 2;

	//socket获取结果状态
	public static $socket_result_success = 1;
	public static $socket_result_fail = 0;
	public static $socket_result_timeout = -1;

	//事务角色
	public static $txgroup_role_starter = 1;
	public static $txgroup_role_actor = 2;

	//事务传播属性
	public static $propagation_naver = 0;

	public static $err_code_timeout = 10001;

	//请求事务协调器action类型
	public static $socket_action_regtxactor = 1;
	public static $socket_action_actor_ack = 2;
	public static $socket_action_precommit = 3;
	public static $socket_action_commit = 4;

}
