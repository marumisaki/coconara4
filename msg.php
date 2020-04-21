<?php

require('function.php');

require('auth.php');
debug('msg.php');

if($_GET['b_id']){
  $b_id = $_GET['b_id'];
}else{
  header("location: schoolList.php");
  exit;
}

$dbh = dbConnect();

$b_data = getBoardChack($dbh, $b_id);
debug('$b_data:'.print_r($b_data, true));
if(empty($b_data)){
    header("location: schoolList.php");
    exit;
}

$m_data = getMsg($dbh, $b_id);
debug('msg'.print_r($m_data, true));
debug('SESSION'.print_r($_SESSION, true));

if(isset($_SESSION['u_id'])){
  if($_SESSION['u_id'] === $b_data['u_profile_id']){
    $u_id = $_SESSION['u_id'];
    $profile = getCompany($dbh, $b_data['c_profile_id']);
    debug('c-profile'.print_r($profile, true));
  }else{
    header("location: userMypage.php");
    exit;
  }
}else if(isset($_SESSION['c_id'])){
  if($_SESSION['c_id'] === $b_data['c_profile_id']){
    $c_id = $_SESSION['c_id'];
    $profile = getUser($dbh, $b_data['u_profile_id']);
  }else{
    header("location: companyMypage.php");
    exit;
  }
}else{
    header("location: schoolList.php");
    exit;
}



?>
<?php
 $siteTitle = 'メッセージ';
 require('head.php');
?>
   <body class="body">

    <!-- ヘッダー -->
    <?php
      require('header.php');
    ?>

  <div class="container u-pt_4l">

    <?php if(isset($u_id)){ ?>
      <h1 class="title title--main u-flex--default u-align-center u-mb_xl"><span class="u-flex-grow"><?php echo sanitize($profile['c_name']); ?>様とのメッセージ</span></h1>
    
      <section class="panel--white u-pb_xxl u-pt_xxl u-mb_xxl">
          <div class="u-width-80 u-pl_l u-pr_l u-m_auto">
              <div class="u-flex u-mb_l">
          <img src="<?php echo (sanitize($profile['pic'] ?? 'img/silet.png')); ?>" class="u-block img__icon u-mb_l u-m_auto">
        <div class="u-ml_3l__sp u-flex-grow">
          <p class="title--sub u-center__sp"><?php echo sanitize($profile['c_name']); ?></p>
              <p class=""><span class="text--sky">電話番号</span>　<?php echo sanitize($profile['tel']); ?></p>
        </div>
      </div>
              <div class="u-mb_l">
                  <h2 class="text--sky">必要とされるスキル</h2>
                  <p class="u-pl_l text"><?php echo sanitize($profile['skill']); ?></p>
              </div>
      <div class="u-mb_l">
        <h2 class="text--sky">企業の基本情報</h2>
        <p class="u-pl_l text"><?php echo sanitize($profile['basic_info']); ?></p>
      </div>
      <div class="u-mb_l">
        <h2 class="text--sky">求める人材・仕事内容</h2>
        <p class="u-pl_l text"><?php echo sanitize($profile['recruit']); ?></p>
      </div>
      <div class="u-flex-center">
         <a href="<?php echo sanitize($profile['url']); ?>" class="button button--blue button__30 text--white u-center">企業ホームページ</a>
      </div>
          </div>
    </section>

  <?php }else if(isset($c_id)){ ?>

      <h1 class="title title--main u-flex--default u-align-center u-mb_xl"><span class="u-flex-grow"><?php echo sanitize($profile['u_name']); ?>さんとのメッセージ</span></h1>
      <section class="panel--white u-pb_xxl u-pt_xxl u-mb_xxl">
          <div class="u-width-80 u-pl_l u-pr_l u-m_auto">
      <div class="u-flex u-mb_l">
          <img src="<?php echo (sanitize($profile['pic'] ?? 'img/silet.png')); ?>" class="img__icon">
          <div class="u-ml_3l__sp u-mb_m u-flex-grow">
              <p class="title--sub u-center__sp"><?php echo sanitize($profile['u_name']); ?></p>
        </div>
      </div>
      <div class="u-mb_l">
        <h2 class="text--sky">自己紹介</h2>
        <p class="u-pl_l text"><?php echo sanitize($profile['description']); ?></p>
      </div>
      <div class="u-mb_l">
        <h2 class="text--sky">自己PR</h2>
        <p class="u-pl_l text"><?php echo sanitize($profile['goal']); ?></p>
      </div>
      <div class="u-flex-between button-box3">
          <a href="https://twitter.com/<?php echo $profile['screen_name']; ?>" class="button button--blue button__30 text--white">twitter</a>
          <a href="userProfile.php?u_id=<?php echo sanitize($profile['id']); ?>" class="button button--blue button__30 text--white u-center">ポートフォリオ</a>
      </div>
          </div>
    </section>
  <?php } ?>


       <main class="panel--sub u-pb_xxl u-pt_xxl">
           <div class="js-msg u-pl_l u-pr_l u-m_auto">
   </div>
 </main>

      <section class="panel--lightblue">
          <div class="form u-m_auto">
        <div class="u-flex-vertical u-align-center">
        <textarea class="comment textarea u-width-70 u-mb_l u-radius__m" rows="4"></textarea>
        <button class="js-addMsg button button--yellow" <?php isset($u_id)? print 'data-u_id='.$u_id: print 'data-c_id='.$c_id; ?> data-b_id="<?php echo sanitize($b_id);?>" >送信する</button>
      </div>
    </div>
  </section>
       </div>

<?php
  require('footer.php');
?>
