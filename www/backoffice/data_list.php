<?php
$sub_menu = '800200';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

$g5['title'] = '한국평가데이터';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$sql_common = " from company_update ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$list_num = $total_count - ($page - 1) * $rows ;

$sql = "select * $sql_common order by ACCT_DT DESC limit $from_record, {$config['cf_page_rows']}";
$result = sql_query($sql);
//echo $sql ;
?>
<style>
h2 {color:#333;}
input[type='file'] {width:50%; border:1px solid #eee; background-color:#fff; padding:6px 0px; margin-right:10px;}
</style>
<h2>한국평가데이터 일자별 업데이트 및 DB파일 다운로드</h2>
<div class="local_ov01 local_ov">
    <?php if ($page > 1) {?><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">처음으로</a><?php } ?>
    <span class="btn_ov01"><span class="ov_txt">전체 내용</span><span class="ov_num"> <?php echo $total_count; ?>건</span></span>
	<div style="display:inline-block; margin-left:2rem; padding: 0 2rem; background-color:#fff;">
		DB 다운로드 파일 비밀번호 설정 &ensp;
		<input type="password" name="zippass" class="frm_input" id="zippass" placeholder="ZIP파일 비밀번호">
		<button type="button" class="btn btn-info btn-sm" id="zippsubmit" style="margin:0px;">적용</button>
		<button type="button" class="btn btn-primary btn-sm" id="dbdown" style="margin:0px;">DB 다운로드</button>
	</div>
</div>

<div class="">
			
		<form id="FileUp" action="data_list_update.php" method="post" enctype="MULTIPART/FORM-DATA" OnSubmit="return up_chek(this);">
			<input type="hidden" name="w" value="">
			<input type="file" name="file"><button class="btn btn-info">업로드</button>
		</form>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
	<tr>
        <th scope="col">NO</th>
        <th scope="col">파일명</th>
		<th scope="col">파일삭제</th>
		<th scope="col">일자</th>
		<th scope="col">상태</th>
		<th scope="col">적용일</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i=0; $row=sql_fetch_array($result); $i++) {
        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_id"><?=$list_num-$i?></td>
        <td class="td_left">
		<?php echo htmlspecialchars2($row['FILE']); ?>
		</td>
		<td class="td_center">
		<?
			$filename = G5_DATA_PATH."/kodata/".$row['FILE'];
			$file_chk=file_exists($filename);
			if ($file_chk && $row['FILE']) {
			?>
				<a href="./data_list_update.php?w=df&amp;c_id=<?php echo $row['c_id']; ?>" class="btn btn-sm  btn-danger">파일삭제</a>
			<?} else {?>
				<span class="btn btn-sm  btn-secondary">파일 삭제 됨</span>
			<?}?>
		</td>
		<td class="td_left"><?php echo htmlspecialchars2($row['ACCT_DT']); ?></td>
		<td class="td_center"><?if($row['STATUS']=="S"){?><span class="btn btn-sm  btn-info">적용완료</span><?}else{?><span class="btn btn-sm btn-secondary">미적용</span><?}?></td>
		<td class="td_center"><?php echo htmlspecialchars2($row['S_DT']); ?></td>
        <td class="td_mng td_mng_l">
			<?if($file_chk && $row['FILE']){?>
            <a href="./data_list_update.php?w=s&amp;c_id=<?=$row['c_id']?>" class="btn btn_03 apply">적용</a>
			<?}?>
            <a href="./data_list_update.php?w=d&amp;c_id=<?php echo $row['c_id']; ?>" onclick="return delete_confirm(this);" class="btn btn_02">삭제</a>
        </td>
    </tr>
    <?php
    }
    if ($i == 0) {
        echo '<tr><td colspan="7" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>
<script>
function up_chek(){
	if(!$("#FileUp input[name='file']").val()) {
		alert("파일을 첨부해 주세요.");
		return false;
	}
}
$("#dbdown").click(function(){
	var result = confirm('DB 다운로드는 생성 및 압축에 5분 이상 걸릴 수 있습니다.\n클릭 후 기다리시면 다운로드 가능합니다.');
	if(result) {
		document.location="db_download.php";
	}
})

$("a.apply").click(function(){
	event.preventDefault();
	var loc=$(this).attr("href");
	var result = confirm('DB 적용은 10분 이상 소요됩니다.\n화면이 멈추더라도 기다리면 적용 완료됩니다.');
	if(result) {
		document.location=loc;
	}
})

$("#zippsubmit").click(function(){
	if($("#zippass").val().length==0)  var result = confirm('비밀번호 설정을 해제 하시겠습니까?');
	else var result = confirm('입력하신 비밀번호로  설정하시겠습니까?');
	var pass=$("#zippass").val();
	if(result) {
			$.post( "./admin_ajax.php",{
				type:'zip_pass',
				pass:pass
			})
			.done(function(jqXHR) {
				if(jqXHR==500){
					alert("요청에 문제가 있습니다.");
				}else{
					alert("변경되었습니다.");
					$("#zippass").val("");
				}
			});
	}
})
</script>
<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
