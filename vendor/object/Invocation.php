<?php 

/**
 * 调用上下文类
 */
class Invocation 
{
	//方法名称
	private $method_name;
	//参数列表
	private $arguments;
	//实例的对象
	private $invoker;

	function __construct()
	{
		
	}

	/**
	 * 执行定义的上下文方法
	 * @return [type] [description]
	 */
	public function invoke(){
		if(empty($this->invoker) || empty($this->method_name))
		{
			throw new Exception("invalid invocation", 10001);
		}
		
		try
		{
			if(!empty($this->arguments))
			{
				$res = call_user_func(array($this->invoker, $this->method_name), $this->arguments);
			}
			else
			{
				$res = call_user_func(array($this->invoker, $this->method_name));
			}
			
			return $res;
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * [getMethodName description]
	 * @return string 方法名
	 */
	public function getMethodName(){
		return $this->method_name;
	}

	/**
	 * [getArguments description]
	 * @return array 参数列表
	 */
	public function getArguments(){
		return $this->arguments;
	}
	/**
	 * [getInvoker description]
	 * @return DtrInterface 事务资源管理器
	 */
	public function getInvoker(){
		return $this->invoker;
	}

}
