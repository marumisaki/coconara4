<?php 

//共通変数・関数ファイルを読込み
require('function.php');

debug('==========');
debug('ユーザー側メッセージ一覧');
debug('==========');
debugLogStart();
////ログイン認証
require('userAuth.php');

$dbh = dbConnect();
$u_id = $_SESSION['u_id'];



if(isset($_GET['u_id'])){ 
	$u_data = getBordsFromUser($dbh, $u_id);
		if(empty($u_data)){
		header("Location:userLogin.php");
		exit;
	}elseif($u_id !== $u_data['u_id']){
		header("Location:userLogin.php");
		exit;
	}
}

$b_id = getBordsFromUser($dbh,$u_id);
debug('$b_idの中身'.print_r($b_id, true));
foreach ((array)$b_id as $key => $val){
	$m_id = getMsgsAndBordsFromBid($dbh,$val['id']);
	$viewMsgs[] = $m_id;
	debug('$m_idの中身'.print_r($m_id, true));
	$scout = "";
}
//パラメータに不正な値が入っているかチェック
if(empty($viewMsgs)){
error_log('エラー発生:指定ページに不正な値が入りました');
}else{
    $no_msg = 'メッセージがまだありません';
}

debug('画面表示終了＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞');

?>
<?php
$siteTitle = 'メッセージ一覧';
require ('head.php'); 
?>
<body class="body">
<?php require('header.php'); ?>
<main class="container u-m_auto bg-basecolor">
	<h1 class="title title--main u-flex--default u-align-center u-mb_xl u-mt_3l"><span class="u-flex-grow">スカウトメッセージ一覧</span></h1>
	<section class="scout-list wrapper u-m_auto bg-non-0 u-pt_m u-pb_xl u-mb_4l">
		<?php 
			if(!empty($viewMsgs)):foreach($viewMsgs as $key => $val): 
		?>
		<?php
			if(!empty ($val[0]['comment'])){
				$scout = "";
			}else{
				$scout = "u-hidden";
			}
		?>
		<div class="wrapper scout-msgs panel--lightblue u-pl_l u-pr_l u-pt_l u-mt_xl u-width-80 u-m_auto <?php echo $scout; ?>">
		<div class="scout-msg ">
			<div class="wrap-msg-info u-flex-between">
				<h2 class="sub-title text--l"><?php echo $val[0]['c_name']; ?></h2>
				<p class="u-right"><?php echo $val[0]['update_date']; ?></p>
			</div>
			<p class="text"><?php echo $val[0]['comment']; ?>
			<?php if(empty($m_id)){ echo $no_msg;
			} ?></p>
			</div>
			<div class=" u-m_auto  u-mt_m u-center">
				<form method="post" class="post u-width-100 u-inline button-box3">
					<button type ="button" name="button" class="edit button--blue  text--def u-inline u-pl_m u-pr_m u-ml_l button__25 u-mt_m u-width-50sp">
						<a href="msg.php?b_id= <?php echo $val[0]['b_id']; ?>" class=" u-m_auto u-white ">詳しく見る</a>
					</button>
					<button class="button--blue  text--def u-inline u-pl_m u-pr_m u-ml_l button__25 u-mt_m u-width-50sp">
						<a href="<?php echo sanitize ($val[0]['url']);?>" class="u-white">企業情報</a>
					</button>
				</form>
			</div>
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