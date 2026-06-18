<?php
$sub_menu = "100550";
include_once('./_common.php');


if(!sql_query(" DESCRIBE ".G5_TABLE_PREFIX."seo ", false)) {
    $sql="CREATE TABLE `".G5_TABLE_PREFIX."seo` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(50) DEFAULT NULL,
  `seo_value` longtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `file_name` (`file_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
    sql_query($sql);
}

auth_check_menu($auth, $sub_menu, 'w');

// 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
@mkdir(G5_DATA_PATH . '/file/seo/', G5_DIR_PERMISSION);
@chmod(G5_DATA_PATH . '/file/seo/', G5_DIR_PERMISSION);


if ($is_admin != 'super') alert('최고관리자만 접근 가능합니다.');

$file_name=addslashes($_POST['file_name']);
$del_seo=sql_fetch("select * from ".G5_TABLE_PREFIX."seo where file_name='{$file_name}'");
$del_data=json_decode($del_seo['seo_value'],true);

$del_check=$_POST['del_seo_img'];
$del_favicon_check=$_POST['del_seo_favicon'];
$seo_meta=array();

if($del_check){
    @unlink(G5_DATA_PATH . '/file/' . 'seo' . '/' . $del_data['seo_image']);
    $seo_meta['seo_image']="";
    $seo_meta['seo_image_name']="";
}else{
    $seo_meta['seo_image']=$del_data['seo_image'];
    $seo_meta['seo_image_name']=$del_data['seo_image_name'];
}

if(del_favicon_check){
    @unlink(G5_DATA_PATH . '/file/' . 'seo' . '/' . $del_data['del_seo_favicon']);
    $seo_meta['seo_favicon']="";
    $seo_meta['seo_favicon_name']="";
}else{
    $seo_meta['seo_favicon']=$del_data['seo_favicon'];
    $seo_meta['seo_favicon_name']=$del_data['seo_favicon_name'];
}

foreach ($_POST as $key => $value){
    if(strpos($key,'seo_')===false){
        continue;
    }else{
        $seo_meta[$key]=addslashes($value);
    }
}

if(!empty($_FILES['seo_image']['name'])){
    $upload['file'] = '';
    $upload['source'] = '';
    $upload['filesize'] = 0;
    $upload['image'] = array();
    $upload['image'][0] = '';
    $upload['image'][1] = '';
    $upload['image'][2] = '';

    // 삭제에 체크가 되어있다면 파일을 삭제합니다.

    @unlink(G5_DATA_PATH . '/file/' . 'seo' . '/' . $del_data['seo_image']);

    $tmp_file = $_FILES['seo_image']['tmp_name'];
    $filesize = $_FILES['seo_image']['size'];
    $filename = $_FILES['seo_image']['name'];
    $filename = get_safe_filename($filename);

    // 서버에 설정된 값보다 큰파일을 업로드 한다면
    if ($filename) {
        if ($_FILES['seo_image']['error'] == 1) {
            $file_upload_msg .= '\"' . $filename . '\" 파일의 용량이 서버에 설정(' . $upload_max_filesize . ')된 값보다 크므로 업로드 할 수 없습니다.\\n';
        } else if ($_FILES['seo_image']['error'] != 0) {
            $file_upload_msg .= '\"' . $filename . '\" 파일이 정상적으로 업로드 되지 않았습니다.\\n';
        }
    }

    if (is_uploaded_file($tmp_file)) {
        // 관리자가 아니면서 설정한 업로드 사이즈보다 크다면 건너뜀
        //=================================================================\
        // 090714
        // 이미지나 플래시 파일에 악성코드를 심어 업로드 하는 경우를 방지
        // 에러메세지는 출력하지 않는다.
        //-----------------------------------------------------------------
        $timg = @getimagesize($tmp_file);
        // image type

        //=================================================================

        $upload['image'] = $timg;

        // 프로그램 원래 파일명
        $upload['source'] = $filename;
        $upload['filesize'] = $filesize;

        // 아래의 문자열이 들어간 파일은 -x 를 붙여서 웹경로를 알더라도 실행을 하지 못하도록 함
        $filename = preg_replace("/\.(php|pht|phtm|htm|cgi|pl|exe|jsp|asp|inc)/i", "$0-x", $filename);
        $image_name=$file_name.".".end((explode(".", $filename)));
        shuffle($chars_array);
        $shuffle = implode('', $chars_array);

        // 첨부파일 첨부시 첨부파일명에 공백이 포함되어 있으면 일부 PC에서 보이지 않거나 다운로드 되지 않는 현상이 있습니다. (길상여의 님 090925)
        $upload['file'] = abs(ip2long($_SERVER['REMOTE_ADDR'])) . '_' . substr($shuffle, 0, 8) . '_' . replace_filename($filename);

        $dest_file = G5_DATA_PATH . '/file/' . 'seo' . '/' . $image_name;
        $upload_image_name = $image_name;
        // 업로드가 안된다면 에러메세지 출력하고 죽어버립니다.
        $error_code = move_uploaded_file($tmp_file, $dest_file) or die($_FILES['seo_image']['error']);
        $seo_meta['seo_image']=$upload_image_name;
        $seo_meta['seo_image_name']=$filename;
        // 올라간 파일의 퍼미션을 변경합니다.
        chmod($dest_file, G5_FILE_PERMISSION);
    }
}

if(!empty($_FILES['seo_favicon']['name'])){
	unset($filename);
	unset($upload);
    $upload['file'] = '';
    $upload['source'] = '';
    $upload['filesize'] = 0;
    $upload['image'] = array();
    $upload['image'][0] = '';
    $upload['image'][1] = '';
    $upload['image'][2] = '';

    // 삭제에 체크가 되어있다면 파일을 삭제합니다.

    @unlink(G5_DATA_PATH . '/file/' . 'seo' . '/' . $del_data['seo_favicon']);

    $tmp_file = $_FILES['seo_favicon']['tmp_name'];
    $filesize = $_FILES['seo_favicon']['size'];
    $filename = $_FILES['seo_favicon']['name'];
    $filename = get_safe_filename($filename);

    // 서버에 설정된 값보다 큰파일을 업로드 한다면
    if ($filename) {
        if ($_FILES['seo_favicon']['error'] == 1) {
            $file_upload_msg .= '\"' . $filename . '\" 파일의 용량이 서버에 설정(' . $upload_max_filesize . ')된 값보다 크므로 업로드 할 수 없습니다.\\n';
        } else if ($_FILES['seo_favicon']['error'] != 0) {
            $file_upload_msg .= '\"' . $filename . '\" 파일이 정상적으로 업로드 되지 않았습니다.\\n';
        }
    }

    if (is_uploaded_file($tmp_file)) {
        // 관리자가 아니면서 설정한 업로드 사이즈보다 크다면 건너뜀
        //=================================================================\
        // 090714
        // 이미지나 플래시 파일에 악성코드를 심어 업로드 하는 경우를 방지
        // 에러메세지는 출력하지 않는다.
        //-----------------------------------------------------------------
        $timg = @getimagesize($tmp_file);
        // image type

        //=================================================================

        $upload['image'] = $timg;

        // 프로그램 원래 파일명
        $upload['source'] = $filename;
        $upload['filesize'] = $filesize;

        // 아래의 문자열이 들어간 파일은 -x 를 붙여서 웹경로를 알더라도 실행을 하지 못하도록 함
        $filename = preg_replace("/\.(php|pht|phtm|htm|cgi|pl|exe|jsp|asp|inc)/i", "$0-x", $filename);
        $image_name=$file_name.".".end((explode(".", $filename)));
        shuffle($chars_array);
        $shuffle = implode('', $chars_array);

        // 첨부파일 첨부시 첨부파일명에 공백이 포함되어 있으면 일부 PC에서 보이지 않거나 다운로드 되지 않는 현상이 있습니다. (길상여의 님 090925)
        $upload['file'] = abs(ip2long($_SERVER['REMOTE_ADDR'])) . '_' . substr($shuffle, 0, 8) . '_' . replace_filename($filename);

        $dest_file = G5_DATA_PATH . '/file/' . 'seo' . '/' . $image_name;
        $upload_image_name = $image_name;
        // 업로드가 안된다면 에러메세지 출력하고 죽어버립니다.
        $error_code = move_uploaded_file($tmp_file, $dest_file) or die($_FILES['seo_favicon']['error']);
        $seo_meta['seo_favicon']=$upload_image_name;
        $seo_meta['seo_favicon_name']=$filename;
        // 올라간 파일의 퍼미션을 변경합니다.
        chmod($dest_file, G5_FILE_PERMISSION);
    }
}




$seo_data=json_encode($seo_meta,JSON_UNESCAPED_UNICODE);

$sql = "INSERT INTO ".G5_TABLE_PREFIX."seo
            VALUES (NULL,'{$file_name}','{$seo_data}')
                ON DUPLICATE KEY UPDATE seo_value = VALUES(seo_value)";

sql_query($sql);
goto_url("./seo_list.php");
