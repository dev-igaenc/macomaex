<?php
require_once './_common.php';
if(!$is_admin) alert("잘못된 접근방식입니다.");

require_once G5_PATH . '/lib/PHPExcel.php';
$obj = new PHPExcel();
$writer = new PHPExcel_Writer_Excel5($obj);
$sheet = new PHPExcel_Worksheet($obj, 'new sheet');
$obj->addSheet($sheet);
$obj->createSheet();
$obj->setActiveSheetIndex(0);
$curSheet = $obj->getActiveSheet();

$bo_table=$_GET['bo_table'];

$sql_common = " from {$g5['write_prefix']}{$bo_table}";

$sql_search = " where 1 ";
$sql = "select * {$sql_common} {$sql_search} order by wr_id asc";
$result = sql_query($sql);


$titles=array(
    //edu_apply
    "A"=>'번호',  //wr_subject
    "B"=>'가맹점명',   //ca_name
    "C"=>"영업시간",
    "D"=>"연락처",
    "E"=>"취급품목",
    "F"=>"전시장 유/무",
    "G"=>"주차장 유/무",
    "H"=>"시/도",
    "I"=>"구/군",
    "J"=>"주소",
    "K"=>"위도",
    "L"=>"경도",
);
foreach ($titles as $key => $val){
    $curSheet->setCellValue($key."1", $val);
}


$i=2;
while ($data = sql_fetch_array($result)) {
    $curSheet->setCellValueExplicit('A'.$i, $data['wr_id'], PHPExcel_Cell_DataType::TYPE_STRING);
    $curSheet->setCellValue('B'.$i, $data['wr_subject']);
    $curSheet->setCellValue('C'.$i, $data['wr_1']);
    $curSheet->setCellValue('D'.$i, $data['wr_5']);
    $curSheet->setCellValue('E'.$i, $data['wr_2']);
    $curSheet->setCellValue('F'.$i, $data['wr_3']);
    $curSheet->setCellValue('G'.$i, $data['wr_10']);
    $curSheet->setCellValue('H'.$i, $data['wr_6']);
    $curSheet->setCellValue('I'.$i, $data['wr_7']);
    $curSheet->setCellValue('J'.$i, $data['wr_4']);
    $curSheet->setCellValue('K'.$i, $data['wr_8']);
    $curSheet->setCellValue('L'.$i, $data['wr_9']);

    $i++; //다음줄에 값을 저장하기 위해 증가

}
ob_end_clean();
$filename = 'store.xls';
header('Content-Type: application/vnd.ms-execl');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');
$writer->save('php://output');


