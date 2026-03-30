<?php
$sub_menu = '400300';
include_once('./_common.php');
if(!$is_admin) alert("잘못된 접근방식입니다.");

// 상품이 많을 경우 대비 설정변경
set_time_limit ( 0 );
ini_set('memory_limit', '50M');

$bo_table=$_POST['bo_table'];

function only_number($n)
{
    return preg_replace('/[^0-9]/', '', $n);
}

$is_upload_file = (isset($_FILES['excelfile']['tmp_name']) && $_FILES['excelfile']['tmp_name']) ? 1 : 0;

if( ! $is_upload_file){
    alert("엑셀 파일을 업로드해 주세요.");
}

if($is_upload_file) {
    $file = $_FILES['excelfile']['tmp_name'];

    include_once(G5_LIB_PATH.'/PHPExcel/IOFactory.php');

    $objPHPExcel = PHPExcel_IOFactory::load($file);
    $sheet = $objPHPExcel->getSheet(0);

    $num_rows = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();

    $dup_it_id = array();
    $fail_it_id = array();
    $dup_count = 0;
    $total_count = 0;
    $fail_count = 0;
    $succ_count = 0;

    for ($i = 2; $i <= $num_rows; $i++) {
        $total_count++;

        $j = 0;

        $rowData = $sheet->rangeToArray('A' . $i . ':' . $highestColumn . $i,
            NULL,
            TRUE,
            FALSE);

        $wr_id              = (string) $rowData[0][$j++];
        $wr_subject         = addslashes($rowData[0][$j++]);
        $wr_1         = addslashes($rowData[0][$j++]);
        $wr_5         = addslashes($rowData[0][$j++]);
        $wr_2         = addslashes($rowData[0][$j++]);
        $wr_3         = addslashes($rowData[0][$j++]);
        $wr_10         = addslashes($rowData[0][$j++]);
        $wr_6         = addslashes($rowData[0][$j++]);
        $wr_7         = addslashes($rowData[0][$j++]);
        $wr_4         = addslashes($rowData[0][$j++]);
        $wr_8         = addslashes($rowData[0][$j++]);
        $wr_9         = addslashes($rowData[0][$j++]);

        if(!$wr_subject ||  !$wr_4) {
            $fail_count++;
            continue;
        }


        if($wr_id){
            $sql = "UPDATE {$g5['write_prefix']}{$bo_table} set 
                       wr_subject='{$wr_subject}',
                       wr_1='{$wr_1}',
                       wr_2='{$wr_2}',
                       wr_3='{$wr_3}',
                       wr_4='{$wr_4}',
                       wr_5='{$wr_5}',
                       wr_6='{$wr_6}',
                       wr_7='{$wr_7}',
                       wr_8='{$wr_8}',
                       wr_9='{$wr_9}',
                       wr_10='{$wr_10}'
                    WHERE 
                       wr_id='{$wr_id}'
";
        }else{
            $wr_num = get_next_num($g5['write_prefix']."{$bo_table}"  );
            $sql = "INSERT INTO {$g5['write_prefix']}{$bo_table}  
                    set wr_num = '$wr_num',
                     wr_reply = '',
                     wr_comment = 0,
                     ca_name = '',
                     wr_option = '',
                     wr_subject = '$wr_subject',
                     wr_content = '',
                     wr_seo_title = '$wr_subject',
                     wr_link1 = '',
                     wr_link2 = '',
                     wr_link1_hit = 0,
                     wr_link2_hit = 0,
                     wr_hit = 0,
                     wr_good = 0,
                     wr_nogood = 0,
                     mb_id = 'admin',
                     wr_password = '',
                     wr_name = ' 최고관리자',
                     wr_email = '',
                     wr_homepage = '',
                     wr_datetime = '".G5_TIME_YMDHIS."',
                     wr_last = '".G5_TIME_YMDHIS."',
                     wr_ip = '{$_SERVER['REMOTE_ADDR']}',
                     wr_1 = '$wr_1',
                     wr_2 = '$wr_2',
                     wr_3 = '$wr_3',
                     wr_4 = '$wr_4',
                     wr_5 = '$wr_5',
                     wr_6 = '$wr_6',
                     wr_7 = '$wr_7',
                     wr_8 = '$wr_8',
                     wr_9 = '$wr_9',
                     wr_10 = '$wr_10' ";
        }
        sql_query($sql);
        $insert_id=sql_insert_id();
        if($insert_id) sql_query("UPDATE {$g5['write_prefix']}{$bo_table} SET wr_parent='{$insert_id}' where wr_id='{$insert_id}'");
        unset($wr_subject);
        unset($wr_1);
        unset($wr_2);
        unset($wr_3);
        unset($wr_4);
        unset($wr_5);
        unset($wr_6);
        unset($wr_7);
        unset($wr_8);
        unset($wr_9);
        unset($wr_10);
        unset($sql);
        $succ_count++;
    }
    // 게시판의 글 수
    $sql = " select count(*) as cnt from {$g5['write_prefix']}{$bo_table} where wr_is_comment = 0 ";
    $row = sql_fetch($sql);
    $bo_count_write = $row['cnt'];

    // 게시판의 코멘트 수
    $sql = " select count(*) as cnt from {$g5['write_prefix']}{$bo_table} where wr_is_comment = 1 ";
    $row = sql_fetch($sql);
    $bo_count_comment = $row['cnt'];

    $sql = " update {$g5['board_table']}
                set bo_count_write = '{$bo_count_write}',
                    bo_count_comment = '{$bo_count_comment}'
              where bo_table = '{$bo_table}' ";
    sql_query($sql);
		
}

$g5['title'] = '상품 엑셀일괄등록 결과';
include_once(G5_PATH.'/head.sub.php');
?>

<?php
	$sql3 = " select * from {$g5['write_prefix']}{$bo_table}";
    $row3 = sql_fetch($sql3);

?>

    <div style="padding: 20px 50px;">
    <table class="table table-bordered border-primary">
        <tr>
            <th colspan="2"><?php echo $g5['title']; ?></th>
        </tr>
        <tr>
            <th>완료건수</th>
            <td><?php echo number_format($succ_count); ?></td>
        </tr>
        <tr>
            <th>실패건수</th>
            <td><?php echo number_format($fail_count); ?></td>
        </tr>
    </table>
    </div>
    <div class="new_win" style="padding: 0 50px;text-align: right">
        <div class="btn_win01 btn_win">
            <button type="button" onclick="window.close();">창닫기</button>
        </div>

    </div>
    <style>
        #hd_login_msg{display: none;}
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
            crossorigin="anonymous"></script>
<?php
include_once(G5_PATH.'/tail.sub.php');