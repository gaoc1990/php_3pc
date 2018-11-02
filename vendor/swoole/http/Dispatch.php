<?php 

/**
 * 
 */
class Dispatch
{
	
	private $path;
	private $controller;
	private $action;
	private $uri;

	function __construct($uri)
	{
		$this->parseUri($uri);
	}

	public function parseUri($uri){
		$uri = trim($uri,'/');
		$uri_arr = explode('/', $uri);
		if(empty($uri)){
			throw new Exception("error uri", 1);
		}

		if(count($uri_arr) < 2){
			throw new Exception("error uri", 1);
		}

		$this->uri = $uri;
		$this->action =  lcfirst(array_pop($uri_arr));
		$this->controller = ucfirst(array_pop($uri_arr));

		$this->path = implode(DIRECTORY_SEPARATOR, $uri_arr);
	}

	public function doRequest(){
		$file = BASE_DIR . $this->path . "/controller/{$this->controller}.php";
		
		if(!file_exists($file)){
			throw new Exception("request 404", 1);
		}
		require_once($file);
		if(!class_exists($this->controller)){
			throw new Exception("request 404", 1);
		}

		$class = new $this->controller();
		$class->{$this->action}();
		
	}

}