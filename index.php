
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
  <h2>必要な持ち物<h2>
  <ul>
    <li class="string2">chatworkトークン</li>
    <li class="string2">TrelloユーザID</li>
    <li class="string2">Trelloトークン</li>
    <li class="string2">TrelloAPIKey</li>
  </ul>
  <hr />
  <h3>chatworkトークン取得方法</h3>
    <ol>
      <li>チャットワークAPIに申し込む</li>
        <p><a href="https://www.chatwork.com/service/packages/chatwork/subpackages/api/apply_beta.php">申し込みページ</a></p>
        <p>※以下の件名のメールが届けば申し込み完了</p>
        <p>subject：【チャットワーク】チャットワークAPI（プレビュー版）ご利用開始のお知らせ</p>
        <p>メールが届くまでに多少時間がかかります。</p>
      <li>下記サイトの「チャットワークAPIのAPIトークンを発行してもらう」項目内の手順でトークンを発行し記録</li>
        <a href="http://hm-solution.jp/web/post3762.html">http://hm-solution.jp/web/post3762.html</a>
    </ol>
  <hr />
  <h3>TrelloユーザID取得方法</h3>
    <ol>
      <li>Trelloにログインする</li>
      <li>画面右上の自分のユーザーネームをクリックしプロフィール画面に行く</li>
      <li>名前の右下に表示された@以下がユーザーIDになります</li>
        <p>※@は含まれない</p>
    </ol>
  <hr />
  <h3>TrelloAPIKey & Trelloトークン取得方法</h3>
    <ol>
      <li>Trelloにログインする</li>
      <li>Developer API Keysの取得</li>
      <ol>  
        <li><a href="https://trello.com/1/appKey/generate">https://trello.com/1/appKey/generate</a>にアクセスする</li>
        <li>上部にあるKeyを記録</li>
      </ol>
      <li>トークンの取得</li>
      <ol>
        <li><a href="https://trello.com/1/appKey/generate">https://trello.com/1/appKey/generate</a>にアクセスする</li>
        <li> ページ上部にある「あなたは手動でTokenを作られます。」の「Token」の部分をクリック</li>
        <li>「許可」をクリック</li>
        <li> 表示されたトークンを記録</li>
      </ol>
    </ol>
  <hr />
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
