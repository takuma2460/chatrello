
<?php

 ini_set( 'display_errors', 1 );

 $errors = array();

 if (!empty($_COOKIE['chatrello'])) {
    session_id($_COOKIE['chatrello']);
 }

 session_start();

 if($_POST['chatwork_token']){
    $_SESSION['chatwork_token'] = $_POST['chatwork_token'];
    session_regenerate_id();
    setcookie('chatrello', session_id(), strtotime( '+30 days' ));
 }else{
    $login = false;
 }

 $dsn = 'mysql:dbname=chatrello;host=localhost';
 $user = 'root';//rootなのは許してください
 $password = '0000';

 try{
    $dbh = new PDO($dsn, $user, $password);
    
    $query = "select * from user where chatwork_token = :chatwork_token"; // 送信されたトークンが既に存在しているか確認
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(':chatwork_token', $_SESSION['chatwork_token'], PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetchAll();

    $header = array(
      "Content-Type: application/x-www-form-urlencoded",
      "X-ChatWorkToken: " . $_SESSION['chatwork_token'],
              );
    $context = array(
      "http" => array(
      "method"  => "GET",
      "header"  => implode("\r\n", $header),
                )
              );
    $json_me = file_get_contents('https://api.chatwork.com/v1/me', false, stream_context_create($context));
    //chatworkから情報を取得

    if (empty($json_me)) {
          $login = false;
          $errors[] = '本人の情報を取得できません。';

    } else {
          $mydata = json_decode($json_me, true);
  
          if (!empty($mydata) && !empty($mydata['name'])) {
              $login = true;
              //chatworkのタスクについて取得
              $task_status_json = file_get_contents('https://api.chatwork.com/v1/my/status', false, stream_context_create($context));
              $task_status = json_decode($task_status_json, true);
              $number = $task_status['mytask_num'];
              
              $task_json = file_get_contents('https://api.chatwork.com/v1/my/tasks', false, stream_context_create($context));
              $task = json_decode($task_json, true);

              $_SESSION['task'] = $task;
              
              $_SESSION['number'] = $number;

              if(empty($task)){
                  $login = false;
                  $errors[] = 'task の情報を取得できません。';
              }
          }
    }
    //DBにchatwork_tokenが存在するか
    if($result[0]["chatwork_token"] == $_SESSION['chatwork_token']){
        header('Location:tasks_select.php');
    }else{
      if($login){//DBになければ挿入
        $insert = "INSERT INTO user(chatwork_token)";
        $insert .= " VALUES(:chatwork_token)";
        $stmt = $dbh->prepare($insert);
        $stmt->bindParam(':chatwork_token', $_SESSION['chatwork_token'], PDO::PARAM_STR);
        $stmt->execute();
        header('Location:tasks_select.php');
      }
    }  
  }catch (PDOException $e){
    print('Error:'.$e->getMessage());
    die();
 }
if(!$login){

}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>チャットレロ</title>
  <style type="text/css">
    #textbox1{
      margin:0 0 20 20px;
    }
    .string{
      font-size: 20px;
      text-align: center;
    }
    #submit1{
      margin:0 0 0 530px;
      background: #ffaa00;
    }
    .form{
      border:0;
      padding:10px;
      font-size: 20px;
      font-family: Arial, sans-serif;
      color:#000000;
      border:solid 1px #ccc;
      width:300px;
      -webkit-border-radius: 3px;
      -moz-border-radius: 3px;
      border-radius: 3px;

      -moz-box-shadow: inset 0 0 4px rgba(0,0,0,0.2);
      -webkit-box-shadow: inset 0 0 4px rgba(0,0,0,0.2);
      box-shadow: inner 0 0 4px rgba(0, 0, 0, 0.2);
    }
    #text{
      text-align: center;
      padding:10px;
    }
    #submit_button{
      padding:10px;
    }
  </style>
</head>
<body>
  <h1 id="text">チャットワークのタスクをトレロにつっこみます</h1>
  <form action="index.php" method="POST">
  <div>
  <label><p class="string">チャットワークのアクセストークン：<input type="text" name="chatwork_token" id="textbox1" class="form"/></p></label>
  </div>
  <p><input type="submit"　name="submit_token" id="submit1" class="form"/></p>
  </form>
  <?php if (!empty($errors)): foreach ($errors as $error): ?>
  <?php echo $error . '<br>'; ?>
  <?php endforeach; endif; ?>
</body>
</html>
