<?php 
 session_start(); //sessionを使う宣言 一番上に書かないといけない
  if (!isset($_SESSION['postk'], $_SESSION['posty'])){
   exit("<p>不正な処理なので<a href=''>正しいページ</a>から送信し直してください</p>");
  }

 $spk = $_SESSION['postk'];
 $spy = $_SESSION['posty'];
 echo "<pre>";
 var_dump($spk);
 var_dump($spy);

 include "connect_db.php";

 if ($_SESSION['posty']['kain']== "希望する"){
 	$pass=substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 8);
 	$hash=password_hash($pass, PASSWORD_DEFAULT);
    }
 try{
	$dbh->beginTransaction();  //トランザクション開始

	$sql = 'INSERT INTO kokyak(k_name, k_huri, mail, tel, zip, addr, pswd) VALUES(?,?,?,?,?,?,?);'; 
	$sth = $dbh->prepare($sql);
	$sth->bindValue(1,$spk['k_name'], PDO::PARAM_STR);
	$sth->bindValue(2,$spk['k_huri'],PDO::PARAM_STR);
	$sth->bindValue(3,$spk['mail'],PDO::PARAM_STR);
	$sth->bindValue(4,$spk['tel'],PDO::PARAM_STR);
	$sth->bindValue(5,$spk['zip'],PDO::PARAM_STR);
	$sth->bindValue(6,$spk['addr'],PDO::PARAM_STR);
	$sth->bindValue(7,$hash ,PDO::PARAM_STR);
	$sth->execute();  
	  $k_id = $dbh->lastInsertId();

	$sql = 'INSERT INTO yoyak( y_date, y_time, course, ninzu, comment, kain, k_id) VALUES(?,?,?,?,?,?,?);';
    $sth = $dbh->prepare($sql);
	$sth->bindValue(1,$spy['y_date'], PDO::PARAM_STR);
	$sth->bindValue(2,$spy['y_time'],PDO::PARAM_STR);
	$sth->bindValue(3,$spy['course'],PDO::PARAM_STR);
	$sth->bindValue(4,$spy['ninzu'],PDO::PARAM_INT);
	$sth->bindValue(5,$spy['comment'],PDO::PARAM_STR);
	$sth->bindValue(6,$spy['kain'],PDO::PARAM_STR);
	$sth->bindValue(7,$k_id,PDO::PARAM_INT);
	$sth->execute();  

	$dbh->commit();  // 全て実行

    // パスワードはあれば送るし､なければ送らない
	$psw= isset($pass)?"ご登録パスワードは: " . $pass . "です" : "";
	
    $to = $spk['mail']; //お客さんのアドレス
    $subject ="ご予約確認のメール";
    $message = "ご予約ありがとうございます。" . $spy['y_date'] . $spy['y_time'] . "にご予約を承りました。" . $psw;
    $headers = "From:神楽坂ラレアンス<mary@example.co.jp>";
    $result= mail($to, $subject, $message, $headers); 

	echo "<h1>ご予約承りました</h1><p>あなたのパスワードは" . $pass . "です</p>" . "<img src='images/orei.png' alt='ありがとうっぽい画像'>";
}catch(PDOException $err){
	$dbh->rollBack(); // 何もなかったことに
	echo $err . "のエラーにより処理は行われませんでした｡";
 }
 $_SESSION['postk'] = NULL;
 $_SESSION['posty'] = NULL;
 ?>













	