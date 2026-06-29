<?php

include_once('./_common.php');

// 상품이 많을 경우 대비 설정변경
set_time_limit ( 0 );
ini_set('memory_limit', '50M');

if(1) {
    $file = "kssc_11.xls";

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
	$itid_head=time();

    for ($i = 2; $i <= $num_rows; $i++) {


        $rowData = $sheet->rangeToArray('A' . $i . ':' . $highestColumn . $i,
                                            NULL,
                                            TRUE,
                                            FALSE);
		$it_id=$itid_head+$i;
		if(!$rowData[0][1]) continue;
        $total_count++;
        $j = 0;

        $ca_id              = addslashes($rowData[0][$j++]);
        $ca_id2             = addslashes((string)$rowData[0][$j++]);

		if(strlen($ca_id)==1) $ca_id3="대분류";
        if(strlen($ca_id)==2) $ca_id3="중분류";
		if(strlen($ca_id)==3) $ca_id3="소분류";
		if(strlen($ca_id)==4) $ca_id3="세분류";
		if(strlen($ca_id)==5) $ca_id3="세세분류";

		/*
		$ca_id4             = addslashes((string)$rowData[0][$j++]);
        $it_name            = addslashes($rowData[0][$j++]);
        $it_maker           = addslashes((string)$rowData[0][$j++]);
        $it_origin          = addslashes((string)$rowData[0][$j++]);
        $it_brand           = addslashes((string)$rowData[0][$j++]);
        $it_model           = addslashes((string)$rowData[0][$j++]);
        $it_type4           = addslashes((string)$rowData[0][$j++]);
        $it_1           = addslashes((string)$rowData[0][$j++]);
        $it_2           = addslashes((string)$rowData[0][$j++]);
        $it_type2           = addslashes((string)$rowData[0][$j++]);
        $it_type3           = addslashes((string)$rowData[0][$j++]);
		$it_type1           = addslashes((string)$rowData[0][$j++]);
		$it_type5           = addslashes((string)$rowData[0][$j++]);
        $it_9          = addslashes((string)$rowData[0][$j++]);
		$it_10          = addslashes((string)$rowData[0][$j++]);
		$it_basic           = addslashes((string)$rowData[0][$j++]); //이력번호
        $it_explan           = addslashes((string)$rowData[0][$j++]);
        $it_5   = addslashes((string)$rowData[0][$j++]);
        $it_6      = addslashes(only_number($rowData[0][$j++]));
        $it_price           = addslashes(only_number($rowData[0][$j++]));
		$it_3           = addslashes(only_number($rowData[0][$j++]));
		$it_4           = addslashes((string)$rowData[0][$j++]);
        $it_tel_inq         = addslashes((string)$rowData[0][$j++]);
        $it_point           = addslashes(only_number($rowData[0][$j++]));
        $it_point_type      = addslashes(only_number($rowData[0][$j++]));
        $it_sell_email      = addslashes((string)$rowData[0][$j++]);
        $it_use             = addslashes((string)$rowData[0][$j++]);
        $it_stock_qty       = addslashes(only_number($rowData[0][$j++]));
        $it_noti_qty        = addslashes(only_number($rowData[0][$j++]));
        $it_buy_min_qty     = addslashes(only_number($rowData[0][$j++]));
        $it_buy_max_qty     = addslashes(only_number($rowData[0][$j++]));
        $it_notax           = addslashes(only_number($rowData[0][$j++]));
        $it_order           = addslashes(only_number($rowData[0][$j++]));
		$it_info_gubun           = addslashes((string)$rowData[0][$j++]);
		$it_info_value           = addslashes((string)$rowData[0][$j++]);
		$it_info_value_ext1           = addslashes((string)$rowData[0][$j++]);
		$it_info_value_ext2           = addslashes((string)$rowData[0][$j++]);
		$it_info_value_ext3           = addslashes((string)$rowData[0][$j++]);
		$it_info_value_ext4           = addslashes((string)$rowData[0][$j++]);
		$it_info_value_ext5           = addslashes((string)$rowData[0][$j++]);
		$it_info_value_ext6           = addslashes((string)$rowData[0][$j++]);
        $it_img1            = addslashes((string)$rowData[0][$j++]);
        $it_img2            = addslashes((string)$rowData[0][$j++]);
        $it_img3            = addslashes((string)$rowData[0][$j++]);
        $it_img4            = addslashes((string)$rowData[0][$j++]);
        $it_img5            = addslashes((string)$rowData[0][$j++]);
        $it_img6            = addslashes((string)$rowData[0][$j++]);
        $it_img7            = addslashes((string)$rowData[0][$j++]);
        $it_img8            = addslashes((string)$rowData[0][$j++]);
        $it_img9            = addslashes((string)$rowData[0][$j++]);
        $it_img10           = addslashes((string)$rowData[0][$j++]);
        $it_explan2         = strip_tags(trim($it_explan));
		*/

	

		if(strlen($ca_id)==1) {
			$code=explode("(",str_replace(")","",$ca_id2));
			$code2=explode("~",$code[1]);
			if(count($code2)==1) {
				$addcd[$code2[0]]=$ca_id;
			}else{
				for($x=$code2[0];$x<=$code2[1];$x++){
					$adcd=sprintf('%02d',$x);
					$addcd[$adcd]=$ca_id;
				}
			}
		}

		if(strlen($ca_id)>1) {
			$cd_2=substr($ca_id,0,2);
			$add_txt=$addcd[$cd_2];
		}
		
        $sql = " INSERT INTO kssc_11
                     SET k_code = '{$add_txt}$ca_id',
                         k_code_name = '$ca_id2',
                         k_code_type = '$ca_id3'";
		echo $sql."<br>";
        //sql_query($sql);

        $succ_count++;
    }
	//print_r($addcd);
}
