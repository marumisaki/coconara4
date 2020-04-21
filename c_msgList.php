<?php 

//共通変数・関数ファイルを読込み
require('function.php');

debug('==========');
debug('企業側メッセージ一覧');
debug('==========');
debugLogStart();
////ログイン認証
require('companyAuth.php');
$dbh = dbConnect();
$companyInfo = getCompanies($dbh, $_SESSION['c_id']);

//該当ユーザー以外がURLを入力した場合
if(empty($companyInfo)){
    header("Location:companyLogin.php");
    exit;
}
if(isset($_SESSION['c_id'])){
     $c_id = $_SESSION['c_id'];
}

$b_id = getBordsFromCompany($dbh, $c_id);
debug('$b_idの中身'.print_r($b_id, true));

foreach ((array)$b_id as $key =>$val){
    $m_id = getMsgsAndCBords($dbh, $val['id']);
    $viewMsgs[]= $m_id;
    debug('$m_idの中身'.print_r($m_id, true));
}

if(!empty($m_id)){
    error_log('エラー発生:指定ページに不正な値が入りました');
}else{
    $no_msg = 'メッセージがまだありません';
}

debug('画面表示終了>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
    ?>
<?php
	$siteTitle ='企業マイページ';
	require('head.php');
?>
<body class="body">
<?php require('header.php'); ?>
<main class="container u-m_auto bg-basecolor">
	<h1 class="title title--main u-flex--default u-align-center u-mb_xl u-mt_3l"><span class="u-flex-grow">スカウトメッセージ一覧</span></h1>
	<section class="scout-list wrapper u-m_auto bg-non-0 u-pt_m u-pb_xl u-mb_4l">
		<?php if(!empty($viewMsgs)):foreach($viewMsgs as $key => $val): ?>
		<?php
		if(!empty($val[0]['comment'])){
			$msg = "";
		}else{
			$msg ="u-hidden";
		}
		?>
		<div class="wrapper scout-msgs panel--lightblue u-pl_l u-pr_l u-pt_l u-pb_l u-mt_l u-m_auto u-width-90 <?php echo $msg; ?>">
		<div class="panel--msg">
			<span class="u-left"><?php echo $val[0]['u_name']; ?></span>
			<span class="u-rightpc">受信日時：<?php echo $val[0]['update_date']; ?></span></div>
			<p class="text u-pt_m">
			<?php echo $val[0]['comment']; ?><br />
			<?php if(empty($m_id)){ echo $no_msg;
			} ?>
			</p>
			<button type="button" name="button" class="button--blue u-width-35 button-mypage u-radius__s text--def u-pt_m u-pb_m u-center u-m_auto u-block fw-bold u-pl_m u-pr_m u-mt_l">
			<a href="msg.php?b_id= <?php echo $val[0]['b_id']; ?>" class="u-white">掲示板でメッセージを確認する</a>
			</button>
		</div>
		<?php
			endforeach;
			endif;
		?>
	</section>
</main>
<?php
require('footer.php');
?>