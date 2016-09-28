

<?php 

session_start();
$number_task = $_SESSION['number'];

if($_POST['trello_token'] && $_POST['trello_api_key'] && $_POST['trello_token']){
	$_SESSION['trello_token']= $_POST['trello_token'];
	$_SESSION['trello_api_key']= $_POST['trello_api_key'];
	$_SESSION['userid']= $_POST['trello_token'];

	setcookie('trello_token', $_SESSION['trello_token'], strtotime( '+30 days' ));
	setcookie('trello_api_key', $_SESSION['trello_api_key'], strtotime( '+30 days' ));
	setcookie('userid', $_SESSION['userid'], strtotime( '+30 days' ));
}
if($_POST['destination_board'] && $_POST['destination_list']){
	$_SESSION['destination_board'] = $_POST['destination_board'];
	$_SESSION['destination_list'] = $_POST['destination_list'];
}

for($i=0;$i<$number_task;$i++){
	$task[$i] = $_SESSION['task'][$i]['body'];
}

if(!$login){

}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title >タスクを選択</title>
	<style type="text/css">
		#task_title{
			text-align: center;
		}
		#select1 {
			font-size:30px;
			margin:0 0 0 650px;
		}
		#form1{
		    
		    margin:0 0 10px 25px;
	    }
	    #form2{
		    
		    margin:0 0 10px 116px;
	    }
		#form3 {
			
    		margin:0 0 10px 5px;
		}
		#form4 {
			
    		margin:0 0 10px 5px;
		}
		#form5 {
			
    		background: #ffaa00;
    		margin:0 0 20px 20px;
		}
		#form6 {
			
    		margin:0 0 10px 125px;
		}
		.textbox{
			border:0;
            padding:10px;
            font-size: 20px;
            font-family: Arial, sans-serif;
            color: #000000;
            border:solid 1px #ccc;
            width:300px;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            border-radius: 3px;

            -moz-box-shadow: inset 0 0 4px rgba(0,0,0,0.2);
            -webkit-box-shadow: inset 0 0 4px rgba(0,0,0,0.2);
    		box-shadow: inner 0 0 4px rgba(0, 0, 0, 0.2);
		}
		.string1{
			font-size: 20px;
      		text-align: center;
		}
		#button{
			margin:30px 0 ;
			text-align: center;
		}
	    
	</style>
</head>
<body>
	<h1 id="task_title">タスク一覧</h1>
	<ul id = "select1">
	<?php for($i=0;$i<$number_task;$i++):?>
	<?php echo "<li>$task[$i]</li>"; ?>
	<?php endfor; ?>
	</ul>
	<form action="tasks_select.php" method="POST" >
		<label><p class="string1">トレロのアクセストークン：<input type="text" name="trello_token" id="form1" class="textbox" value="<?php echo isset($_COOKIE['trello_token']) ? $_COOKIE['trello_token']:'' ?>"/></p></label>
		<label><p class="string1">トレロのAPIkey：<input type="text" name="trello_api_key" id="form2" class="textbox" value="<?php echo isset($_COOKIE['trello_api_key']) ? $_COOKIE['trello_api_key']:'' ?>"/></p></label>
		<label><p class="string1">トレロのユーザID<input type="text" name="trello_userid" id="form6" class="textbox" value="<?php echo isset($_COOKIE['userid']) ? $_COOKIE['userid']:'' ?>"/></p></label>
		<label><p class="string1">トレロの挿入先ボードを指定：<input type="text" name="destination_board" id="form3" class="textbox"></p></label>
		<label><p class="string1">トレロの挿入先リストを指定：<input type="text" name="destination_list" id="form4" class="textbox"></p></label>
		<p id="button"><input type="submit" id="form5" class="textbox"></p>
	</form>
</body>
</html>