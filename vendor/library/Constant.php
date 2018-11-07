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

	public static $tx_status_precommit_fail = 6;

	//事务执行应答
	public static $tx_complete_fail = "fail";
	public static $tx_complete_ok = "ok";
	public static $tx_complete_timeout = "timeout";
	
	//事务补偿类型
	public static $comp_type_save = 0;
	public static $comp_type_del = 1;
	public static $comp_type_update = 2;
	public static $comp_type_compensation = 3;

	//补偿操作类型
	public static $comp_operate_type_update = 1;
	public static $comp_operate_type_comp = 2;

	//事务角色
	public static $txgroup_role_starter = 1;
	public static $txgroup_role_actor = 2;

	//事务传播属性
	public static $propagation_naver = 0;
	public static $propagation_have = 1;

	public static $err_code_timeout = 10001;

	//请求事务协调器action类型
	public static $socket_action_starttrans = 1;
	public static $socket_action_regtxactor = 2;
	public static $socket_action_actor_ack = 3;
	public static $socket_action_precommit = 4;
	public static $socket_action_commit = 5;
	public static $socket_action_rollback = 6;		

	public static $socket_result_precommit = 7;

}
