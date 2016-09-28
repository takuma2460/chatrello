<?php
date_default_timezone_set('UTC');
include('/Users/admin/vendor/autoload.php');
ini_set( 'display_errors', 1 );
use Trello\Client;

session_start();
$number_task = $_SESSION['number'];
$chatwork_token = $_SESSION['chatwork_token'];
$userid = $_SESSION['trello_userid'];

for($i=0;$i<$number_task;$i++){
	$task[$i] = $_SESSION['task'][$i]['body'];
	$task_limit[$i] = $_SESSION['task'][$i]['limit_time'];
}


if(!empty($_SESSION['trello_api_key']) && !empty($_SESSION['trello_token'])){

	$trello_api_key = $_SESSION['trello_api_key'];
	$trello_token = $_SESSION['trello_token'];
	$userid = $_SESSION['userid'];

}

$client = new Client();

$client->authenticate($trello_api_key, $trello_token, Client::AUTH_URL_CLIENT_ID);
$boards = $client -> members() -> boards() -> all($userid);

$number_board = count($boards);


if(empty($_SESSION['destination_board']) || empty($_SESSION['destination_list'])){
	header('Location:tasks_select.php');
	echo "<p>入力してください</p>";
}else{
	$destination_board = $_SESSION['destination_board'];
}

for($i=0;$i<$number_board;$i++){
	if($destination_board == $boards[$i]['name']){
		$destination_board_id = $boards[$i]['id'];
	}
}

$Cards = $client->boards()->cards()->all($destination_board_id);

$number_card = count($Cards);

for($i=0;$i<$number_card;$i++){
	$idList = $Cards[$i]['idList'];
	$List_data = $client -> lists() -> show($idList);
	$List_name = $List_data['name'];

	if($List_name == $_SESSION['destination_list']){
		$destination_list_id = $idList;
	}
}

if(empty($destination_list_id)){
	header('Location: tasks_select.php');
	echo "<p>aaaaaaaaaa</p>";
}else{

	$dsn = 'mysql:dbname=chatrello;host=localhost';
	$user = 'root';
	$password = '0000';

	try{
		    $dbh = new PDO($dsn, $user, $password);
		    $query = "select * from user where chatwork_token = :chatwork_token";
		    $stmt = $dbh->prepare($query);
		    $stmt->bindParam(':chatwork_token', $chatwork_token, PDO::PARAM_STR);
		    $stmt->execute();

		    $result = $stmt->fetchAll();
		    var_dump($result);

		    if($result[0]['trello_token'] == $trello_token && $result[0]['trello_api_key'] == $trello_api_key){	
		    	for($i=0;$i<$number_task;$i++){
					$task_name = $task[$i];
					$task_limit_time = date('Y-m-d\TH:m:s.000\Z', $task_limit[$i]);
					for($j=0;$j<$number_card;$j++){
						if($task_name == $Cards[$j]['name'] && $task_limit_time == $Cards[$j]['due']){
							goto fin1;
						}else{
							continue;
						}
					}
					$param = array('idList'=>"$destination_list_id",'name'=>"$task_name",'due' => "$task_limit_time");
					$client->cards()->create($param);
					fin1:
				}
		    }else{
			    $query = "update user set trello_token = :trello_token, trello_api_key = :trello_api_key where chatwork_token = :chatwork_token";
			    $stmt = $dbh->prepare($query);
			    $stmt->bindParam(':chatwork_token', $chatwork_token, PDO::PARAM_STR);
			    $stmt->bindParam(':trello_token', $trello_token, PDO::PARAM_STR);
			    $stmt->bindParam(':trello_api_key', $trello_api_key, PDO::PARAM_STR);
			    $stmt->execute();

		    	for($i=0;$i<$number_task;$i++){//引数が多かったのでコピペで済ませました。
					$task_name = $task[$i];
					$task_limit_time = date('Y-m-d\TH:m:s.000\Z', $task_limit[$i]);
					for($j=0;$j<$number_card;$j++){
						if($task_name == $Cards[$j]['name'] && $task_limit_time == $Cards[$j]['due']){
							goto fin2;
						}else{
							continue;
						}
					}
				}
				$param = array('idList'=>"$destination_list_id",'name'=>"$task_name",'due' => "$task_limit_time");
				$client->cards()->create($param);
				fin2:
			}
		} catch (PDOException $e){
	    	print('Error:'.$e->getMessage());
	    	die();
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Congratulations</title>
	<style type="text/css">
	#message{
		font-size:30px;
		text-align: center;
	}
	</style>
</head>
<body>
 <p id="message">挿入完了！</p>
</body>
</html>