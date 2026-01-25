<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Credentials: true');
	header("Content-type: json/application");
	require_once "functions.php";
	require_once "connect.php";

	$method = $_SERVER['REQUEST_METHOD'];

	$get = 0;
	$q = $_GET['q'] ?? '';
		
	$params = explode('/', trim($q, '/'));
		
	$type = $params[0] ?? '';
	$id = $params[1] ?? '';
	if ($type == 'books'){
		switch($method){
			case 'GET':
				if(!empty($id) && is_numeric($id)){
					$get = get_book($connect, $id);
				} else{
					$get = get_all_posts($connect);
				}
				echo $get;
				break;
			case 'POST':
				echo post_book($connect, $_POST);
				break;
			case 'PATCH':
				if(isset($id)){
					$data = file_get_contents('php://input');
					$data = json_decode($data, true);
					echo patch_book($connect, $data, $id);
				}
				break;
			case 'DELETE':
				echo delete_book($connect, $id);
				break;
			default:
				http_response_code(404);
				echo json_encode([
					'status' => false,
					'message' => 'unknown method'
				]);
		}
	}
	/*
	switch ($method)  {
		case 'GET':
			$get = get_all_posts($connect);
			break;
		case 'POST':
				// ...
			break;
		case 'PUT':
				// ...
			break;
		case 'DELETE':
				// ...
			break;
	}*/
?>