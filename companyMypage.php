<?php

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　マイページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//私の編集分（保存）

//ログイン認証
/*
require('companyAuth.php');


$dbh = dbConnect();

$_SESSION['c_id']='';
$c_id = $_SESSION['c_id'];


$companyInfo = getCompanies($dbh, $c_id);


if(isset($_GET['c_id'])){
    $companyInfo = getCompanies($dbh,$c_id);
    debug('companyInfo'.print_r($companyInfo, true));
    if(empty($companyInfo['c_id'])){
        header("Location:companyLogin.php");
        exit;
    }elseif($c_id!== $companyInfo['c_id']){
        header("Location:companyLogin.php");
        exit;
    }
}
*/

//ログイン認証
require('companyAuth.php');


$dbh = dbConnect();
$companyInfo = getCompanies($dbh, $_SESSION['c_id']);

//$_SESSION['c_id']='';
//$c_id = $_SESSION['c_id'];

//該当ユーザー以外がURLを入力した場合
if(empty($companyInfo)){
    header("Location:companyLogin.php");
    exit;
}
if(isset($_SESSION['c_id'])){
     $c_id = $_SESSION['c_id'];
}
//}else if($_SESSION['c_id'] !== $companyInfo['id']){
//    header("location: companyLogin.php");
//    exit;
//}else if(isset($_SESSION['u_id'])){
//  header("location: companyLogin.php");
//   exit;
//}else{
//  header("location: top.php");
//  exit;
//}





//if(isset($_GET['c_id'])){
//    $c_id = $_GET['c_id'];
//    $c_data = getBordsFromCompany($dbh, $c_id);
//    if(empty($c_id)){
//        header("Location:companyLogin.php");
//        exit;
//    }elseif($c_id !== $c_data['c_id']){
//            header("Location:companyLogin.php");
//            exit;
//        }
//}

$b_id = getBordsFromCompany($dbh, $c_id);
debug('$b_idの中身'.print_r($b_id, true));


//
//
//if(isset($_SESSION['c_id'])){
//    if($_SESSION['c_id'] === $c_data['c_data']){
//        $c_id = $_SESSION['c_id'];
//    if(empty($c_id)){
//        header("Location:companyLogin.php");
//        exit;
//    }elseif($c_id !== $c_data['c_id']){
//            header("Location:companyLogin.php");
//            exit;
//        }
//}
//}

foreach ((array)$b_id as $key =>$val){
    $m_id = getMsgsAndCBords($dbh, $val['id']);
    $viewMsgs[]= $m_id;
    debug('$m_idの中身'.print_r($m_id, true));
}

//$viewMsg = array();

//$viewMsg = getMsgsAndCBords($dbh, $c_id);
//debug('viewMsg'.print_r($viewMsg, true));
//debug('companyInfo'.print_r($companyInfo, true));
//if(!empty($viewMsg)){
//    $viewBoard = getCBords($dbh,$b_id);
//    debug('掲示板ID：'.print_r($viewBoard, true));
//   var_dump ($viewBoard);
//
//if(!empty($m_id)){
//    $viewMsg = getMsgsAndBord($dbh,$c_id);
//    debug('取得したDBデータ'.print_r($viewMsg, true));
//    var_dump($viewMsg);
//}
if(!empty($m_id)){
    error_log('エラー発生:指定ページに不正な値が入りました');
}else{
    $no_msg = 'メッセージがまだありません';
}



debug('画面表示終了>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
    ?>
<?php
	require('head.php');
	$siteTitle ='企業マイページ';?>
<?php require('header.php'); ?>
	<body class="body">
		<main class="container u-m_auto bg-basecolor">
			<section class="wrapper u-m_auto u-mb_xl u-mt_5l u-width-90 bg-non-0 u-pb_l u-radius__s">
        <h1 class="title title--main u-flex--default u-align-center u-mb_xl u-mt_3l"><span class="u-flex-grow">企業マイページ</span></h1>
                <h2 class="u-sub u-center text--xl u-radius__s u-mb_m u-mt_xl u-pt_m u-pb_m fw-bold panel--sub u-width-90 u-m_auto">スカウトメッセージ</h2>
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
                    <button type="button" name="button" class="button--blue u-width-35 button-mypage u-radius__s text--def u-pt_m u-pb_m u-center u-m_auto u-block fw-bold u-pl_m u-pr_m u-mt_l"><a href="msg.php?b_id= <?php echo $val[0]['b_id']; ?>" class="u-white">掲示板でメッセージを確認する</a></button>
                </div>
                   <?php
                endforeach;
                endif;
                ?>
               
                
            </section>

            <section class="wrapper u-m_auto u-mb_xl u-mt_5l u-width-90 bg-non-0 u-radius__s">
                    <h2 class="u-sub u-center text--xl u-radius__s u-mb_m u-mt_xl u-pt_m u-pb_m fw-bold panel--sub u-width-90 u-m_auto">    企業掲載情報</h2>
                    <div class="u-width-90 u-radius__s u-m_auto u-pb_l">
                     <span style="color: #7EA6F4" class="text--def fw-bold">企業紹介文</span>
                 <div class="wrapper scout-msgs panel--lightblue u-pl_l u-pr_l u-pt_l u-pb_m u-m_auto u-mt_m">
                   
                <?php
                    if(!empty($companyInfo)):foreach($companyInfo as $key=>$val): ?>
                <p class="text">
             <?php echo $val['basic_info']; ?>
                </p>
             
                </div>
                <span style="color: #7EA6F4" class="text--def u-mt_l u-block fw-bold">求める人材</span>
                <div class="wrapper scout-msgs panel--lightblue u-pl_l u-pr_l u-pt_l u-pb_m u-m_auto u-mt_m ">
                    <p class="text">
                   <?php echo $val['recruit']; ?><br />
                    </p>
                       <?php
                    endforeach;
                    endif;
                    ?>
                </div>
                        <a href="companyEdit.php" class="button--blue u-width-30 button-mypage u-radius__s text--def u-pt_m u-pb_m u-center u-m_auto u-block fw-bold u-pl_m u-pr_m u-mt_xxl u-white">企業情報を編集する</a>
               </div>
            </section>
        </main>
<?php
    require('footer.php');
                        ?>