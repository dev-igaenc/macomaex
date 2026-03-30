<?php
$sub_menu = "100550";
include_once('./_common.php');
include_once('../admin.head.php');

auth_check($auth[$sub_menu], 'w');

if ($is_admin != 'super' && $w == '') alert('최고관리자만 접근 가능합니다.');


$gr_id_attr = '';
$sound_only = '';
if ($w == '') {
    $gr_id_attr = 'required';
    $sound_only = '<strong class="sound_only"> 필수</strong>';
    $gr = array('gr_use_access' => 0);
    $html_title .= ' 등록';
} else if ($w == 'u') {
    $gr_id_attr = 'readonly';
    $html_title .= '참여자 정보수정';
} else
    alert('제대로 된 값이 넘어오지 않았습니다.');

if ($id) {
    $row = sql_fetch("select * from ".G5_TABLE_PREFIX."seo where id={$id}");

}
if ($row['category']) {
    $category = $row['category'];
}
$g5['title'] = $html_title;

$seo_data=json_decode($row['seo_value'],true);
?>
<form name="fboardgroup" id="fboardgroup" action="./seo_form.update.php"
      onsubmit="return frmfaqmasterform_check(this);" method="post" autocomplete="off" enctype="multipart/form-data">
    <div class="tbl_frm01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th scope="row"><label for="file_name">FILE NAME<?php echo $sound_only ?></label></th>
                <td><input type="text" name="file_name" value="<?php echo $row['file_name'] ?>"
                           class="<?php echo $gr_id_attr; ?> frm_input" maxlength="100" style="width: 80%">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="seo_title">TITLE<?php echo $sound_only ?></label></th>
                <td><input type="text" name="seo_title" value="<?php echo $seo_data['seo_title'] ?>"
                           class="<?php echo $gr_id_attr; ?> frm_input" maxlength="100" style="width: 80%">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="seo_keyword">KEYWORD</label></th>
                <td><input type="text" name="seo_keyword" value="<?php echo $seo_data['seo_keyword'] ?>"
                           class="<?php echo $gr_id_attr; ?> frm_input" maxlength="100" style="width: 80%">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="seo_description">DESCRIPTION</label></th>
                <td><input type="text" name="seo_description" value="<?php echo $seo_data['seo_description'] ?>"
                           class="<?php echo $gr_id_attr; ?> frm_input" maxlength="100" style="width: 80%">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="seo_url">URL<?php echo $sound_only ?></label></th>
                <td><input type="text" name="seo_url" value="<?php echo $seo_data['seo_url'] ?>"
                           class="<?php echo $gr_id_attr; ?> frm_input" maxlength="100" style="width: 80%">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="seo_img">IMAGE</label></th>
                <td>
                    <input type="file" name="seo_image" id="seo_image" title="파일첨부 <?php echo $i ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file">
                    <?php if ( $seo_data['seo_image']) { ?>
                        <div class="file_del">
                            <input type="checkbox" id="del_seo_img" name="del_seo_img" value="1">
                            <label for="del_seo_img"><?php echo $seo_data['seo_image_name']; ?> 파일 삭제</label>
                        </div>
                        <div>
                            <img src="/data/file/seo/<?=$seo_data['seo_image']?>" style="max-height :200px">
                        </div>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="seo_img">FAVICON</label></th>
                <td>
                    <input type="file" name="seo_favicon" id="seo_favicon" title="파일첨부 <?php echo $i ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file">
                    <?php if ( $seo_data['seo_favicon']) { ?>
                        <div class="file_del">
                            <input type="checkbox" id="del_seo_favicon" name="del_seo_favicon" value="1">
                            <label for="del_seo_favicon"><?php echo $seo_data['seo_favicon_name']; ?> 파일 삭제</label>
                        </div>
                    <?php } ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="btn_right">
        <?php
        $list_url = "./seo_list.php";
        ?>
        <a href="<?php echo $list_url ?>" class="btn_admin btn_02">목록</a>
        <input type="submit" class="btn_submit btn_admin" accesskey="s" value="확인">
    </div>

</form>


<?php
include_once('../admin.tail.php');
?>
