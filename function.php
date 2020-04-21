<?php

//global $appEnv;
$appEnv = getenv('APP_ENV');
//================================
// ログ
//================================
//ログを取るか
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log','php.log');
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('Asia/Tokyo');

//================================
// デバッグ
//================================
//デバッグフラグ
$debug_flg = true;
//デバッグログ関数
function debug($str){
  global $debug_flg;
  if(!empty($debug_flg)){
    error_log('デバッグ：'.$str);
  }
}
debug(print_r($appEnv, true));
//================================
// セッション準備・セッション有効期限を延ばす
//================================
//セッションファイルの置き場を変更する（/var/tmp/以下に置くと30日は削除されない）
session_save_path("/var/tmp/");
//ガーベージコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ１００分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime ', 60*60*24*30);
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

//================================
// 画面表示処理開始ログ吐き出し関数
//================================
function debugLogStart(){
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('セッションID：'.session_id());
  debug('セッション変数の中身：'.print_r($_SESSION,true));
  debug('現在日時タイムスタンプ：'.time());
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
    debug( 'ログイン期限日時タイムスタンプ：'.( $_SESSION['login_date'] + $_SESSION['login_limit'] ) );
  }
}

//================================
// 定数
//================================
//エラーメッセージを定数に設定
define('MSG01','入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03','パスワード（再入力）が合っていません');
define('MSG04','半角英数字のみご利用いただけます');
define('MSG05','6文字以上で入力してください');
define('MSG06','256文字以内で入力してください');
define('MSG07','エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG08', 'そのEmailは既に登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '電話番号の形式が違います');
define('MSG11', 'パスワードが違います');
define('MSG12', '古いパスワードが違います');
define('MSG13', '古いパスワードと同じです');
define('MSG14', '文字で入力してください');
define('MSG15', '正しくありません');
define('MSG16', '登録されていないメールアドレスです');
define('MSG17', '11文字以下で入力してください');
define('MSG18', '200文字以上で入力してください');
define('MSG19', 'そのコース名は既に登録されています');
define('SUC01', 'メールを送信しました');
define('SUC02', 'プロフィールを変更しました');

//================================
// グローバル変数
//================================
//エラーメッセージ格納用の配列
$err_msg = array();

//================================
// バリデーション関数
//================================

//バリデーション関数（未入力チェック）
function validRequired($str, $key){
  if(!isset($str) || $str === ''){ //金額フォームなどを考えると数値の０はOKにし、空文字はダメにする
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}
//バリデーション関数（Email形式チェック）
function validEmail($str, $key){
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}
//バリデーション関数（企業のEmail重複チェック）
function validEmailDup($dbh,$email){
  global $err_msg;
  //例外処理
  try {
    // SQL文作成
    $sql = 'SELECT count(*) FROM c_profile WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    //array_shift関数は配列の先頭を取り出す関数です。クエリ結果は配列形式で入っているので、array_shiftで1つ目だけ取り出して判定します
    if(!empty(array_shift($result))){
      $err_msg['email'] = MSG08;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' .$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
//バリデーション関数（ユーザーのEmail重複チェック）
function validEmailDupUser($dbh,$email){
  global $err_msg;
  //例外処理
  try {
    // SQL文作成
    $sql = 'SELECT count(*) FROM u_profile WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    //array_shift関数は配列の先頭を取り出す関数です。クエリ結果は配列形式で入っているので、array_shiftで1つ目だけ取り出して判定します
    if(!empty(array_shift($result))){
      $err_msg['email'] = MSG08;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' .$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
//バリデーション関数（t_id重複チェック）
function validTidDup($dbh,$t_id){
	global $err_msg;
	//例外処理
	try {
	  // SQL文作成
	  $sql = 'SELECT * FROM u_profile WHERE t_id = :t_id AND delete_flg = 0';
	  $data = array(':t_id' => $t_id);
	  // クエリ実行
	  $stmt = queryPost($dbh, $sql, $data);
	  // クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
	  //array_shift関数は配列の先頭を取り出す関数です。クエリ結果は配列形式で入っているので、array_shiftで1つ目だけ取り出して判定します
    //$result = array_shift($result);
    debug('function $result：'.print_r($result,true));
    return $result;
	} catch (Exception $e) {
	  error_log('エラー発生:' . $e->getMessage());
	  $err_msg['common'] = MSG07;
	}
}
//バリデーション関数（コース名重複チェック）
function validCourseDup($dbh, $val, $s_id){
	global $err_msg;
	//例外処理
	try {
	  // SQL文作成
	  $sql = 'SELECT * FROM courses WHERE s_id = :s_id AND course_name = :val AND delete_flg = 0';
	  $data = array(':s_id' => $s_id, ':val' => $val);
	  // クエリ実行
	  $stmt = queryPost($dbh, $sql, $data);
	  // クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
	  //array_shift関数は配列の先頭を取り出す関数です。クエリ結果は配列形式で入っているので、array_shiftで1つ目だけ取り出して判定します
      if(!empty($result) && !empty(array_shift($result))){
      $err_msg['newCourse'] = MSG19;
    }
	} catch (Exception $e) {
	  error_log('エラー発生:' . $e->getMessage());
	  $err_msg['common'] = MSG07;
	}
}
//バリデーション関数（同値チェック）
function validMatch($str1, $str2, $key){
  if($str1 !== $str2){
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}
//バリデーション関数（最小文字数チェック）
function validMinLen($str, $key, $min = 6){
  if(mb_strlen($str) < $min){
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}
//バリデーション関数（最小文字数チェック）
function validMinLen200($str, $key, $min = 200){
  if(mb_strlen($str) < $min){
    global $err_msg;
    $err_msg[$key] = MSG18;
  }
}
//バリデーション関数（最大文字数チェック）
function validMaxLen($str, $key, $max = 256){
  if(mb_strlen($str) > $max){
    global $err_msg;
    $err_msg[$key] = MSG06;
  }
}
//バリデーション関数（半角チェック）
function validHalf($str, $key){
  if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}
//パスワードチェック
function validPass($str, $key){
  //半角英数字チェック
  validHalf($str, $key);
  //最大文字数チェック
  validMaxLen($str, $key);
  //最小文字数チェック
  validMinLen($str, $key);
}
//selectboxチェック
function validSelect($str, $key){
  if(!preg_match("/^[0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG15;
  }
}
//エラーメッセージ表示
function getErrMsg($key){
  global $err_msg;
  if(!empty($err_msg[$key])){
    return $err_msg[$key];
  }
}
//固定長チェック
function validLength($str, $key, $len = 8){
  if( mb_strlen($str) !== $len ){
    global $err_msg;
    $err_msg[$key] = $len . MSG14;
  }
}
//電話番号形式チェック
function validTel($str, $key){
  if(!preg_match("/0\d{1,4}\d{1,4}\d{4}/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG10;
  }
}
//電話番号固定長チェック
function validTelMaxLen($str, $key, $max = 11){
  if(mb_strlen($str) > $max){
    global $err_msg;
    $err_msg[$key] = MSG17;
  }
}

//================================
// データベース
//================================
//DB接続関数
function dbConnect(){
  //DBへの接続準備
    global $appEnv;
    if($appEnv == 'production'){
    require('DB-config.php');
    $dsn = "mysql:dbname=$DBname;host=$DBhost;charset=utf8mb4";
    $user = $DBuser;
    $password = $DBpassword;
    }else{
  $dsn = 'mysql:dbname=realgachi;host=localhost;charset=utf8mb4';
  $user = 'root';
  $password = 'root';
    }
  $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // PDOオブジェクト生成（DBへ接続）
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}
function queryPost($dbh, $sql, $data){
  //クエリー作成
  $stmt = $dbh->prepare($sql);
	debug('$stmt：'.print_r($stmt,true));
  //プレースホルダに値をセットし、SQL文を実行
  if(!$stmt->execute($data)){
    debug('function:クエリに失敗しました。');
    //debug('function:失敗したSQL：'.print_r($stmt,true));
    $err_msg['common'] = MSG07;
    return 0;
  }
  debug('クエリ成功。');
  return $stmt;
}
////レビュー取得
function getReview($dbh, $r_id){
  $sql = 'SELECT * FROM reviews AS r LEFT JOIN schools AS s ON r.school_id = s.id LEFT JOIN courses AS c ON r.course_id = c.id LEFT JOIN registers AS reg ON r.register_id = reg.id WHERE r.id = :r_id';
	$data = array(':r_id' => $r_id);
	$stmt = queryPost($dbh, $sql, $data);
	if($stmt){
		$rst = $stmt->fetch();
		return $rst;
  }
}

function getReviews($dbh, $u_id){
   debug('レビューを取得します');
debug('ユーザーID：'.$u_id);
try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM reviews WHERE user_id = :u_id';
    $data = array(':u_id'=>$u_id);
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
        return $stmt->fetchAll();
    }else{
        return false;
    }
}catch (Exception $e){
    error_log('エラー発生：'. $e->getMessage());
}
}
////レビュー取得12月２８作成
function getAllReviews($dbh,$u_id){
debug('レビューを取得します2');
debug('ユーザーID：'.$u_id);
try{
    $dbh = dbConnect();
    $sql = 'SELECT r.*, s.school_name, s.method_id, c.course_name FROM reviews AS r LEFT JOIN schools AS s ON r.school_id = s.id LEFT JOIN courses AS c ON r.course_id = c.id WHERE r.user_id = :u_id';
    $data = array('u_id'=>$u_id);
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
        return $stmt->fetchAll();
    }else{
        return false;
    }
}catch (Exception $e){
    error_log('エラー発生：'. $e->getMessage());
}
}



function getReviewListPage($dbh, $s_id, $minNum = 1, $span = 20){
  $sql = 'SELECT count(*) FROM reviews WHERE school_id = :s_id';
  $data = array(':s_id' => $s_id);
  $stmt = queryPost($dbh, $sql, $data);
  $rst['total'] = $stmt -> fetchColumn();
  debug('total_page:'.print_r($rst['total'], true));
  $rst['total_page'] = ceil(intval($rst['total'])/ intval($span));
  if(!$stmt){
    return false;
  }
  $sql = 'SELECT r.id, r.user_id, r.good_comment, r.bad_comment, u.u_name, u.pic, r.dm_flg FROM reviews AS r LEFT JOIN u_profile AS u ON r.user_id = u.id WHERE r.school_id = :s_id LIMIT '.$span.' OFFSET '.$minNum;
	$data = array(':s_id' => $s_id);
	$stmt = queryPost($dbh, $sql, $data);
	if($stmt){
		$rst['data'] = $stmt->fetchAll();
		return $rst;
  }
}


//在籍状況取得
function getRegister($dbh){
  $sql = 'SELECT * FROM registers';
	$data = array();
	$stmt = queryPost($dbh, $sql, $data);
	if($stmt){
		$rst = $stmt->fetchAll();
		return $rst;
	}
}

//評価項目取得
function getAssessmentList($dbh){
	$sql = 'SELECT * FROM assessments';
	$data = array();
	$stmt = queryPost($dbh, $sql, $data);
	if($stmt){
		$rst = $stmt->fetchAll();
		return $rst;
	}
}

//スコア情報取得
function getAssessment($dbh, $r_id){
  $sql = 'SELECT * FROM scores AS s LEFT JOIN assessments AS a ON s.assessments_id = a.id WHERE review_id = :r_id';
	$data = array(':r_id' => $r_id);
	$stmt = queryPost($dbh, $sql, $data);
	if($stmt){
		$rst = $stmt->fetchAll();
		return $rst;
  }
}

//会社情報取得
function getCompany($dbh, $c_id){
  debug('企業情報を取得します。');
  //例外処理
  try {
    // SQL文作成
    $sql = 'SELECT * FROM c_profile WHERE id = :id AND delete_flg = 0';
    $data = array(':id' => $c_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを１レコード返却
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//会社情報取得
function getCompanies($dbh, $c_id){
  debug('企業情報を取得します。');
  //例外処理
  try {
    // SQL文作成
    $sql = 'SELECT * FROM c_profile WHERE id = :id AND delete_flg = 0';
    $data = array(':id' => $c_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを１レコード返却
    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

function getSchoolList($minNum,$price,$language,$courseType,$style,$access,$time,$method,$span=5){
  debug('スクール情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM schools';
		if(!empty($language||$courseType||$style||$access||$time||$method)) $sql.=' WHERE';
		if(!empty($language)) $sql.= " language_id LIKE '%".$language."%' AND";
		if(!empty($courseType)) $sql.= " course_type_id LIKE '%".$courseType."%' AND";
		if(!empty($style)) $sql.= " style_id LIKE '%".$style."%' AND";
		if(!empty($access)) $sql.= " access_id LIKE '%".$access."%' AND";
		if(!empty($time)) $sql.= " time_id LIKE '%".$time."%' AND";
		if(!empty($method)) $sql.= " method_id LIKE '%".$method."%' AND";
		$sql = rtrim($sql," AND");
    if(!empty($price)){
      switch($price){
        case 1:
          $sql.= ' ORDER BY price_id ASC';
          break;
        case 2:
          $sql.= ' ORDER BY price_id DESC';
          break;
      }
    }


    $data = array();
    // クエリ実行
		$stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt -> rowCount(); //総レコード数
    $rst['total_page'] = ceil($rst['total']/$span); //総ページ数
    if(!$stmt){
      return false;
    }
    $sql.= ' LIMIT '.$span.' OFFSET '.$minNum;
    $data = array();
    debug('function:SQL：'.$sql);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      // クエリ結果のデータを全レコードを格納
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//言語
function getLanguage(){
  debug('言語を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM languages';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//コースタイプ
function getCourseType(){
  debug('コースタイプ情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM course_types';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//コース
function getCourse(){
  debug('コース情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM courses';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//受講スタイル
function getStyle(){
  debug('受講スタイル情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM styles';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//立地
function getAccess(){
  debug('アクセス情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM accesses';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//期間
function getTime(){
  debug('期間情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM times';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//教わり方
function getMethod(){
  debug('教わり方情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM methods';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//ユーザー情報取得
function getUser($dbh,$u_id){
  debug('ユーザー情報を取得します。');
  //例外処理
  try {
    // SQL文作成
    $sql = 'SELECT * FROM u_profile WHERE id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを１レコード返却
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
function getHistory($dbh,$u_id){
	debug('職歴を取得します。');
  //例外処理
  try {
    // SQL文作成
    $sql = 'SELECT * FROM history WHERE u_id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを１レコード返却
    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

function getUserToPortfolio($dbh,$u_id){
  $sql = 'SELECT * FROM u_profile AS u LEFT JOIN portfolios AS p ON u.id = p.user_id WHERE u.id = :u_id';
	$data = array(':u_id' => $u_id);
	$stmt = queryPost($dbh, $sql, $data);
	if($stmt){
		$rst = $stmt->fetch();
		return $rst;
  }
}

//スクール情報取得
function getSchoolAll($dbh){
	$sql = 'SELECT * FROM schools';
	$data = array();
	$stmt = queryPost($dbh, $sql, $data);
	if($stmt){
		$rst = $stmt->fetchAll();
		return $rst;
	}
}

function getSchool($dbh, $s_id){
	$sql = 'SELECT * FROM schools WHERE id = :s_id';
	$data = array(':s_id' => $s_id);
	$stmt = queryPost($dbh, $sql, $data);
	if($stmt){
		$rst = $stmt->fetch();
		return $rst;
	}
}

function getSchoolDetail($dbh, $s_id){
	$sql = 'SELECT * FROM schools AS s LEFT JOIN prices AS p ON s.price_id = p.id LEFT JOIN languages AS l ON s.language_id = l.id LEFT JOIN courses AS c ON s.cource_id = c.id LEFT JOIN course_types AS ct ON s.course_type_id = ct.id LEFT JOIN accesses AS a ON s.access_id = a.id LEFT JOIN times AS t ON s.time_id = t.id WHERE s.id = :s_id';
	$data = array(':s_id' => $s_id);
	$stmt = queryPost($dbh, $sql, $data);
	if($stmt){
		$rst = $stmt->fetch();
		return $rst;
	}
}


//コース情報取得
function getCourseList($dbh, $s_id){
	$sql = 'SELECT * FROM courses WHERE s_id = :s_id';
	$data = array(':s_id' => $s_id);
	$stmt = queryPost($dbh, $sql, $data);
	if($stmt){
		$rst = $stmt->fetchAll();
		return $rst;
	}
}

//ポートフォリオ取得
function getPortfolio($dbh, $p_id){
  $sql = 'SELECT p.id, p.user_id, p.title, p.period, p.language, p.school_id, p.update_date, p.pic, p.url, p.s_comment, p.m_comment, p.r_comment, s.school_name FROM portfolios AS p LEFT JOIN schools AS s ON p.school_id = s.id WHERE p.id = :p_id';
  $data = array(':p_id' => $p_id);
  $stmt = queryPost($dbh, $sql, $data);
	if($stmt){
		$rst = $stmt->fetch();
		return $rst;
	}
}
////自分のポートフォリオ取得
function getUserPortfolio($dbh,$u_id){
//関数の中で使う変数を引数にとる、今回は$u_id(そのログインしてるユーザーの情報だけ欲しいから)
debug('ポートフォリオを取得します');
debug('ユーザーID:'.$u_id);
try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM portfolios WHERE user_id = :u_id AND delete_flg=0';
    $data = array(':u_id'=>$u_id);
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
        return $stmt->fetchAll();
        //複数取り出すのでfetchALL使う
    }else{
        return false;
    }
}catch (Exception $e){
    error_log('エラー発生:'. $e->getMessage());
	}
}

function getPortfolioListPage($dbh, $minNum = 0, $span = 20, $search = ''){
  $sql = 'SELECT count(*) FROM portfolios AS p';
  if(!empty($search)){
    $sql .= ' WHERE '.$search;
  }
  $data = array();
  $stmt = queryPost($dbh, $sql, $data);
  $rst['total'] = $stmt -> fetchColumn();
  debug('total_page:'.print_r($rst['total'], true));
  $rst['total_page'] = ceil(intval($rst['total'])/ intval($span));
  if(!$stmt){
    return false;
  }
  $sql = 'SELECT p.id, p.title, p.period, p.language, p.school_id, p.update_date, p.pic, p.url, s.school_name  FROM portfolios AS p LEFT JOIN schools AS s ON p.school_id = s.id';
  $limit = ' LIMIT '.$span.' OFFSET '.$minNum;
  if(!empty($search)){
    $sql .= ' WHERE '.$search;
  }
  $sql .= $limit;
	$data = array();
	$stmt = queryPost($dbh, $sql, $data);
	if($stmt){
		$rst['data'] = $stmt->fetchAll();
		return $rst;
  }
}

//コース追加
function AddCourse($dbh, $s_id, $val){
	$sql = 'INSERT INTO courses (course_name, s_id) VALUES (:course_name, :s_id)';
	$data = array(':course_name' => $val, ':s_id' => $s_id);
	$stmt = queryPost($dbh, $sql, $data);
	exec('nohup php mail.php ' .$val .' > /dev/null &');
	return $stmt;
}

//職務経歴取得
function getJobHistory($u_profile){
    debug('経歴を取得します');
    debug('ユーザーID：'.$u_id);
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM u_profile WHERE :u_id AND :description';
        array(':u_id'=> $u_id,':description'=>$description);
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
return $stmt->fetchAll();
        }else{
            return false;
        }
    }catch (Exception $e){
        error_log('エラー発生'. $e->getMessage());
    }
}
//掲示板取得
function getBord($dbh, $id){
    debug('掲示板情報を取得します');
    debug('掲示板ID：'.$id);
    //例外処理
    try{
        //DB接続
        $sql = 'SELECT b.id AS b_id FROM boards as b WHERE b.id = :u_id';
        $data = array(':b_id'=>$b_id);
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}

function getBordFromUser($dbh, $u_id, $c_id){
  $sql = 'SELECT id FROM boards WHERE u_profile_id = :u_id AND c_profile_id = :c_id';
  $data = array(':u_id' => $u_id, ':c_id' => $c_id);
  $stmt = queryPost($dbh, $sql, $data);
  if($stmt){
    $rst = $stmt -> fetch(PDO::FETCH_ASSOC);
    return $rst;
  }
}



function getBoardChack($dbh, $b_id){
  $sql = 'SELECT * FROM boards WHERE id = :b_id';
  $data = array(':b_id' => $b_id);
  $stmt = queryPost($dbh, $sql, $data);
  if($stmt){
		$rst = $stmt->fetch(PDO::FETCH_ASSOC);
		return $rst;
  }
}

function getMsg($dbh, $b_id){
  $sql = 'SELECT * FROM msgs WHERE b_id = :b_id';
  $data = array(':b_id' => $b_id);
  $stmt = queryPost($dbh, $sql, $data);
  if($stmt){
		$rst = $stmt->fetchAll();
		return $rst;
  }
}

//フォームデータ入力
function getFormdata($dbFormdata,$key, $flg = false){
    $dbh = dbConnect();
    if($flg){
        $method = $_GET;
    }else{
        $method = $_POST;
    }
    if(!empty($dbFormdata[$key])){
        if(isset($method[$key])){
            return sanitize($method[$key]);
        }else{
            return sanitize($dbFormdata[$key]);
        }
    }else{
        if(isset($method[$key])){
            return sanitize($method[$key]);
        }
    }
}

//フォームデータ入力
function getFormdataArray($dbFormdata,$value_name, $key, $flg = false){
    $dbh = dbConnect();
    if($flg){
        $method = $_GET;
    }else{
        $method = $_POST;
    }

    if( !is_array($dbFormdata[$value_name]) ){
    	return $dbFormdata[$value_name];
    }

    if(!empty($dbFormdata[$value_name])){
    	if( is_array($dbFormdata[$value_name]) ){
    		if(isset($method[$key])){
	            return sanitize($method[$value_name][$key]);
	        }else{
	            return sanitize($dbFormdata[$value_name][$key]);
	        }
    	}

    }else{
        if(isset($method[$value_name][$key])){
            return sanitize($method[$value_name][$key]);
        }
    }
}

//ページネーション
function pagenate($link = '', $activePage, $span, $totalPage, $pageRange = 5){
  $pages = [];
  $activePage = (int)$activePage;
  $span = (int)$span;
  $totalPage = (int)$totalPage;
  $pageRange = (int)$pageRange;
  debug('$activePage'.$activePage);
  debug('$totalPage'.$totalPage);
  if($pageRange >= $totalPage){
			/*総ページ数による分岐*/
			$first_page = 1;
			$last_page = $totalPage;
		}else{
			/*現在ページ数による分岐*/
			if($activePage < ($pageRange / 2) ){
				$first_page = 1;
				$last_page = $pageRange;
			}else if($activePage + ($pageRange /2) > $totalPage){
				$first_page = $totalPage - $pageRange + 1;
				$last_page = $totalPage;
			}else{
				$first_page = $activePage - floor($pageRange /2);
				$last_page = $activePage + floor($pageRange /2);
			}
		}
    debug('$last_page: '.print_r($last_page, true));

		/*ページ数の挿入*/
		for($i = $first_page; $i <= $last_page; $i++){
			if($activePage == $i){
				array_push($pages,
				'<li class="pagenation__item active"><a class="pagenation__link" href="?page='.$i.$link.'">'.$i.'</a></li>'
				);
			}else{
				array_push($pages,
				'<li class="pagenation__item"><a class="pagenation__link" href="?page='.$i.$link.'">'.$i.'</a></li>'
				);
			}
		}
    /*配列の手前に挿入
    $activePage !== 1 && array_unshift($pages,
        '<li class="pagenation__item"><a href="?page='.($activePage -1).$link.'">前へ</a></li>'
		);*/

		$activePage !== 1 && array_unshift($pages,
			'<li class="pagenation__arrow"><a class="pagenation__arrow--left" href="?page='.($activePage -1).$link.'"><i class="fas fa-chevron-left"></i></a></li>'
		);

		/*配列の後方に挿入
		$activePage !== $totalPage && array_push($pages,
			'<li class="pagenation__item"><a href="?page='.($activePage +1).$link.'">次へ</a></li>'
		);*/

		$activePage !== $totalPage && array_push($pages,
			'<li class="pagenation__arrow"><a class="pagenation__arrow--right" href="?page='.($activePage +1).$link.'"><i class="fas fa-chevron-right"></i></a></li>'
		);

		return $pages;
}

//GETパラメータ付与
function rewriteGet($arr = array()){
    if(!empty($_GET)){
        $url = parse_url($_SERVER['REQUEST_URI']);
        parse_str($url['query'], $keys);
        foreach ($arr as $key => $val) {
          if(isset($keys[$val])){
            unset($keys[$val]);
          }
          debug('parse_str()'.print_r($keys, true));
        }
        $str = '&'.http_build_query($keys);
        return $str;
    }
}

//================================
// 画像処理
//================================
function uploadImg($file, $key){
  debug('画像アップロード処理開始');
  debug('FILE情報：'.print_r($file,true));

  if (isset($file['error']) && is_int($file['error'])) {
    try {
      // バリデーション
      // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
      //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
      switch ($file['error']) {
          case UPLOAD_ERR_OK: // OK
              break;
          case UPLOAD_ERR_NO_FILE:   // ファイル未選択の場合
              throw new RuntimeException('ファイルが選択されていません');
          case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズが超過した場合
          case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過した場合
              throw new RuntimeException('ファイルサイズが大きすぎます');
          default: // その他の場合
              throw new RuntimeException('その他のエラーが発生しました');
      }

      // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
      // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { // 第三引数にはtrueを設定すると厳密にチェックしてくれるので必ずつける
          throw new RuntimeException('画像形式が未対応です');
      }

      // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
      // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      // image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = 'img/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
      if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
          throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // 保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：'.$path);
      return $path;

    } catch (RuntimeException $e) {

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();

    }
  }
}
//================================
// メール送信
//================================
function sendMail($from, $to, $subject, $comment){
    if(!empty($to) && !empty($subject) && !empty($comment)){
        //文字化けしないように設定（お決まりパターン）
        mb_language("Japanese"); //現在使っている言語を設定する
        mb_internal_encoding("UTF-8"); //内部の日本語をどうエンコーディング（機械が分かる言葉へ変換）するかを設定

        //メールを送信（送信結果はtrueかfalseで返ってくる）
        $result = mb_send_mail($to, $subject, $comment, "From: ".$from);
        //送信結果を判定
        if ($result) {
          debug('メールを送信しました。');
					return $result;
        } else {
          debug('【エラー発生】メールの送信に失敗しました。');
        }
    }
}
//認証キー生成
function makeRandKey($length = 8) {
    static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i = 0; $i < $length; ++$i) {
        $str .= $chars[mt_rand(0, 61)];
    }
    return $str;
}
//sessionを１回だけ取得できる
function getSessionFlash($key){
  if(!empty($_SESSION[$key])){
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}
//================================
// サニタイズ
//================================
function sanitize($str){
	$new_array = Array();
	if( is_array($str) ){
		foreach( $str as $key => $val ){
			$new_array[$key] = htmlspecialchars($val,ENT_QUOTES,'utf-8');
		}
		return $new_array;
	}
  return (htmlspecialchars($str,ENT_QUOTES,'utf-8'));
}

//MSG取得
function getMsgsAndBord($id){
    debug('msg情報を取得します');
    debug('メッセージID'.$id);
    //例外処理
    try{
        //DBへ接続
        $dbh = dbConnect();
        //SQL分作成
        $sql = 'SELECT m.id AS m_id, u_id, comment, b_id,b.c_profile_id, c.c_name FROM msgs AS m RIGHT JOIN boards AS b ON b.id = m.b_id RIGHT JOIN c_profile AS c ON b.c_profile_id = c.id WHERE b.id = :id AND m.delete_flg = 0';
        $data = array(':id'=>$id);
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
//クエリ結果の全データを返却
        return $stmt->fetchAll();
        }else{
return false;
        }
    }catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
//MSG取得 ユーザー
function getMsgsAndBords($dbh,$id){
    debug('msg情報を取得します');
    debug('メッセージID'.print_r($id, true));
    //例外処理
    try{
        //DBへ接続
        $dbh = dbConnect();
        //SQL分作成
        $sql = 'SELECT m.id AS m_id, u_id, m.comment, m.update_date, b_id,b.c_profile_id, u.id, u.u_name FROM msgs AS m RIGHT JOIN boards AS b ON b.id = m.b_id RIGHT JOIN u_profile AS u ON m.u_id = u.id WHERE b.id = :id AND m.delete_flg = 0';
        $data = array(':id'=>$id);
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
//クエリ結果の全データを返却
         return $stmt->fetchAll();
        }else{
return false;
        }
    }catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}


//function getBordMsgs($dbh,$b_id){
//    debug('ボードのMSG情報を取得します');
//    try{
//        $dbh = dbConnect();
//         $sql = 'SELECT m.id AS m_id, u_id, m.comment, m.update_date, b_id,b.c_profile_id, u.id, u.u_name FROM msgs AS m RIGHT JOIN boards AS b ON b.id = m.b_id RIGHT JOIN u_profile AS u ON m.u_id = u.id WHERE b.id = :id AND m.delete_flg = 0';
//        $data = array(':id' => $b_id);
//        $stmt = queryPost($dbh, $sql, $data);
//        $rst = $stmt->fetchAll();
//        if(!empty($rst)){
//            foreach ($rst as $key => $val){
//                $sql = 'SELECT * FROM msgs WHERE b_id = :b_id AND delete_flg=0 ORDER BY update_date DESC';
//                $data = array(':id'=>$val['id']);
//                $stmt = queryPost($dbh, $sql, $data);
//                $rst[$key]['comment']=$stmt->fetchAll();
//            }
//        }
//        if($stmt){
//            return $rst;
//        }else{
//            return false;
//        }
//    } catch (Exception $e){
//        error_log('エラー発生'.$e->getMessage());
//    }
//}


//////MSG取得
//function getMsgsAndBordsFromBid($dbh, $id){
//    debug('msg情報を取得します');
//    debug('メッセージID'.print_r($b_id));
//    //例外処理
//    try{
//        //DBへ接続
//        $dbh = dbConnect();
//        //SQL分作成
//        $sql = 'SELECT m.id AS m_id, m.comment, m.b_id, m.update_date, b.id FROM msgs AS m RIGHT JOIN boards AS b ON b.id = m.b_id WHERE b.id = :id AND m.delete_flg = 0';
//        $data = array(':b_id'=>$b_id);
//        $stmt = queryPost($dbh, $sql, $data);
//        if($stmt){
////クエリ結果の全データを返却
//         return $stmt->fetchAll();
//        }else{
//return false;
//        }
//    }catch (Exception $e){
//        error_log('エラー発生:'.$e->getMessage());
//    }
//}
////MSG取得
//function getMsgsAndBordsFromBid($dbh, $b_id){
//    debug('msg情報を取得します');
//    debug('メッセージID'.print_r($b_id));
//    //例外処理
//    try{
//        //DBへ接続
//        $dbh = dbConnect();
//        //SQL分作成
//        $sql = 'SELECT * FROM msgs WHERE b_id = :b_id ORDER BY update_date DESC';
//        $data = array(':b_id'=>$b_id);
//        $stmt = queryPost($dbh, $sql, $data);
//        if($stmt){
////クエリ結果の全データを返却
//         return $stmt->fetchAll();
//        }else{
//return false;
//        }
//    }catch (Exception $e){
//        error_log('エラー発生:'.$e->getMessage());
//    }
//}
function getMsgsAndBordsFromBid($dbh, $b_id){
   debug('msg情報を取得します');
   debug('メッセージID'.print_r($b_id, true));
    //例外処理
    try{
        //DBへ接続
        $dbh = dbConnect();
        //SQL分作成
//           $sql = 'SELECT * FROM msgs AS m LEFT JOIN boards AS b ON m.b_id = b.id LEFT JOIN c_profile AS c ON m.c_id = c.id WHERE b_id = :b_id';
         $sql = 'SELECT b.id, b.c_profile_id, b.update_date, m.b_id,m.comment,c.c_name, c.url FROM boards AS b LEFT JOIN msgs AS m ON b.id = m.b_id LEFT JOIN c_profile AS c ON m.c_id = c.id WHERE b.id = :b_id AND b.delete_flg = 0';
//        $sql = 'SELECT m.id, m.b_id, m.comment, b.c_profile_id, b.update_date, b.u_profile_id, c.c_name FROM msgs AS m LEFT JOIN boards AS b ON m.b_id = b.id LEFT JOIN c_profile AS c ON b.c_profile_id = c.id WHERE m.b_id = :b_id ORDER BY update_date';
//        $sql = 'SELECT * FROM msgs AS m RIGHT JOIN boards AS b ON m.b_id = b.id RIGHT JOIN c_profile AS c ON m.c_id = c.id WHERE m.b_id = :b_id';
//          $sql = 'SELECT b.id, b.c_profile_id, b.update_date, b.u_profile_id, m.b_id,m.comment,c.c_name FROM msgs AS m LEFT JOIN boards AS b ON m.b_id = b.id LEFT JOIN c_profile AS c ON b.c_prodile_id = c.id WHERE m.b_id = :b_id';
        $data = array(':b_id'=>$b_id);
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
//クエリ結果の全データを返却
         return $stmt->fetchAll();
        }else{
return false;
        }
    }catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}

////boards取得（ユーザー、企業）
//function getIdOfUserAndCompany($dbh,$b_id){
//    try{
//        $dbh = dbConnect();
//        $sql = 'SELECT b.id, u_profile_id, c_profile_id FROM boards AS b LEFT JOIN u_profile AS u ON b.u_profile_id = u.id LEFT JOIN c_profile AS c ON b.c_profile_id = c.id WHERE b.id = :b_id AND b.delete_flg = 0';
//        $data = array(':b_id'=>$b_id);
//        $stmt = queryPost($dbh,$sql,$data);
//        if($stmt){
//            return $stmt->fetchAll();
//        }else{
//            return false;
//        }
//    }catch (Exception $e){
//        error_log ('エラー発生'.$e->getMessage());
//    }
//}
//MSG取得 企業
function getMsgsAndCBords($dbh,$b_id){
//    debug('msg情報を取得します');
//    debug('メッセージID'.print_r($b_id));
    //例外処理
    try{
        //DBへ接続
        $dbh = dbConnect();
        //SQL分作成
//        $sql = 'SELECT * FROM msgs AS m RIGHT JOIN boards AS b ON m.b_id = b.id RIGHT JOIN u_profile AS u ON m.u_id = u.id WHERE m.b_id = :b_id ';
        //後で治す

        $sql = 'SELECT b.id, b.u_profile_id, b.update_date, m.b_id, m.comment, u.u_name FROM boards AS b LEFT JOIN msgs AS m ON b.id = m.b_id LEFT JOIN u_profile AS u ON m.u_id = u.id WHERE b.id = :b_id AND b.delete_flg = 0';
        $data = array(':b_id'=>$b_id);
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
//クエリ結果の全データを返却
         return $stmt->fetchAll();
        }else{
return false;
        }
    }catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}



//考え方
//get bord from user　別でファンクション作る
function getBordsFromUser($dbh, $u_id){
  $sql = 'SELECT id FROM boards WHERE u_profile_id = :u_id AND delete_flg = 0';
  $data = array(':u_id' => $u_id);
  $stmt = queryPost($dbh, $sql, $data);
  if($stmt){
      return $stmt->fetchAll();
//    $rst = $stmt -> fetchAll();
//    return $rst;
  }
}

function getBordsFromCompany($dbh, $c_id){
$sql = 'SELECT id FROM boards WHERE c_profile_id = :c_id AND delete_flg = 0';
$data = array(':c_id'=>$c_id);
$stmt = queryPost($dbh, $sql, $data);
if($stmt){
     return $stmt->fetchAll();
//    $rst = $stmt -> fetchAll();
//    return $rst;
}
}






function startBord($dbh, $u_id, $c_id){
  $sql = 'INSERT INTO boards (u_profile_id, c_profile_id, create_date) VALUES (:u_id, :c_id, :date)';
  $data = array(':u_id' => $u_id, ':c_id' => $c_id, ':date' => date('Y-m-d H-i-s'));
  $stmt = queryPost($dbh, $sql, $data);
  if($stmt){
    $rst = $dbh->lastInsertId();
    return $rst;
  }
}
//
////削除ボタン作成中
//function deleteButton($dbh,$m_id){
//    $sql = 'UPDATE msgs SET delete_flg = 1 WHERE id = :m_id';
//    $data = array(':m_id'=>$m_id);
//    $stmt = queryPost($dbh, $sql, $data);
//}



//get bord from company
//
//    userからみたIDが何個あるか取ってくる
//    ボードのIDに対してメッセージを新しい１個取ってくる
//
