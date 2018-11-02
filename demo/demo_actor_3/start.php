<?php 




$server = new swoole_http_server("127.0.0.1",9003);

$server->on('request',function($request, $response){

	$postdata = $request->post;
	$getdata = $request->get;
	$response->write("post:".json_encode($postdata) .";get:".json_encode($getdata));
	$response->end();

});