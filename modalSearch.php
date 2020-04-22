
<section id="search-menu" class="modal panel--lightblue panel--lightblue--side u-position u-mb_l">
    <form class="u-m_auto" name="" method="get">
        <h1 class="u-width-100">金額</h1>
        <div class="u-width-100 cp_ipselect cp_sl01">
            <span></span>
            <select name="price_id">
                <option value="0" <?php if(getFormData($schools,'price_id',true) == 0 ){ echo 'selected'; } ?>>選択してください</option>
                <option value="1" <?php if(getFormData($schools,'price_id',true) == 1 ){ echo 'selected'; } ?>>金額が安い順</option>
                <option value="2" <?php if(getFormData($schools,'price_id',true) == 2 ){ echo 'selected'; } ?>>金額が高い順</option>
            </select>
        </div>
        <h1>言語</h1>
        <div class="u-width-100 cp_ipselect cp_sl01">
            <span class="icn_select"></span>
            <select name="language_id">
                <option value="0" <?php if(getFormData($schools,'language_id',true) == 0 ){ echo 'selected'; } ?>>選択してください</option>
                <?php
foreach($dbLanguageData as $key => $val){
?>
                <option value="<?php echo $val['id'] ?>" <?php if(getFormData($schools,'language_id',true) == $val['id'] ){ echo 'selected'; } ?>>
                    <?php echo $val['language_name']; ?>
                </option>
                <?php
}
?>
            </select>
        </div>
        <h1>コース</h1>
        <div class="u-width-100 cp_ipselect cp_sl01">
            <span></span>
            <select name="course_id">
                <option value="0" <?php if(getFormData($schools,'course_id',true) == 0 ){ echo 'selected'; } ?>>選択してください</option>
                <?php
foreach($dbCourseTypeData as $key => $val){
?>
                <option value="<?php echo $val['id'] ?>" <?php if(getFormData($schools,'course_type_id',true) == $val['id'] ){ echo 'selected'; } ?>>
                    <?php echo $val['course_type_name']; ?>
                </option>
                <?php
}
?>
            </select>
        </div>
        <h1>受講スタイル</h1>
        <div class="u-width-100 cp_ipselect cp_sl01">
            <span class="icn_select"></span>
            <select name="style_id">
                <option value="0" <?php if(getFormData($schools,'style_id',true) == 0 ){ echo 'selected'; } ?>>選択してください</option>
                <?php
foreach($dbStyleData as $key => $val){
?>
                <option value="<?php echo $val['id'] ?>" <?php if(getFormData($schools,'style_id',true) == $val['id'] ){ echo 'selected'; } ?>>
                    <?php echo $val['style_name']; ?>
                </option>
                <?php
}
?>
            </select>
        </div>
        <h1>立地</h1>
        <div class="u-width-100 cp_ipselect cp_sl01">
            <span class="icn_select"></span>
            <select name="access_id">
                <option value="0" <?php if(getFormData($schools,'access_id',true) == 0 ){ echo 'selected'; } ?>>選択してください</option>
                <?php
foreach($dbAccessData as $key => $val){
?>
                <option value="<?php echo $val['id'] ?>" <?php if(getFormData($schools,'access_id',true) == $val['id'] ){ echo 'selected'; } ?>>
                    <?php echo $val['access_name']; ?>
                </option>
                <?php
}
?>
            </select>
        </div>
        <h1>期間</h1>
        <div class="u-width-100 cp_ipselect cp_sl01">
            <span class="icn_select"></span>
            <select name="time_id">
                <option value="0" <?php if(getFormData($schools,'time_id',true) == 0 ){ echo 'selected'; } ?>>選択してください</option>
                <?php
foreach($dbTimeData as $key => $val){
?>
                <option value="<?php echo $val['id'] ?>" <?php if(getFormData($schools,'time_id',true) == $val['id'] ){ echo 'selected'; } ?>>
                    <?php echo $val['time_name']; ?>
                </option>
                <?php
}
?>
            </select>
        </div>
        <h1>教わり方</h1>
        <div class="u-width-100 cp_ipselect cp_sl01">
            <span class="icn_select"></span>
            <select name="method_id">
                <option value="0" <?php if(getFormData($schools,'method_id',true) == 0 ){ echo 'selected'; } ?>>選択してください</option>
                <?php
foreach($dbMethodData as $key => $val){
?>
                <option value="<?php echo $val['id'] ?>" <?php if(getFormData($schools,'method_id',true) == $val['id'] ){ echo 'selected'; } ?>>
                    <?php echo $val['method_name']; ?>
                </option>
                <?php
}
?>
            </select>
        </div>
        <input class="u-m_auto button--blue u-width-70 u-mt_xl u-mb_xl u-block" type="submit" value="検索">
    </form>
</section>