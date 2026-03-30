<?php
@header('Content-Type: text/html; charset=utf-8');
@header('X-Robots-Tag: noindex');
include ('../gw_config.php');
$title = "카카오 알림톡 라이센스 확인 1/3";
include_once ('./install.inc.php');
?>

<?php
// 카카오 알림톡 설정 테이블이 존재한다면 설치할 수 없다.
// 카카오 알림톡 설정 테이블이 있는가?
if (!$alimtalk_table) {
?>
<h1>카카오 알림톡 설치를 위해 아래 내용을 확인해 주십시오.</h1>

<div class="ins_inner">
    <p>
		현재 사용하고있는 <?php echo G5_MYSQL_DB ?> 데이터베이스에 카카오 알림톡 사용을 위한 테이블이 생성됩니다.<br />
		<ul>
			<li><strong><?php echo $g5['nc_alimtalk_config'] ?></strong></li>
			<li><strong><?php echo $g5['nc_alimtalk_history'] ?></strong></li>
			<li><strong><?php echo $g5['nc_friendtalk_history'] ?></strong></li>
			<li><strong><?php echo $g5['nc_friendtalk_image'] ?></strong></li>
		</ul>
    </p>
</div>

<form action="./install_config.php" method="post" onsubmit="return frm_submit(this);">

<div class="ins_inner">
    <p>
        <strong class="st_strong">라이센스(License) 내용을 반드시 확인하십시오.</strong><br>
        라이센스에 동의하시는 경우에만 설치가 진행됩니다.
    </p>

    <div class="ins_ta ins_license">
        <textarea name="textarea" id="ins_license" readonly><?php echo implode('', file('./LICENSE.txt')); ?></textarea>
    </div>

    <div id="ins_agree">
        <label for="agree">동의합니다.</label>
        <input type="checkbox" name="agree" value="동의함" id="agree">
    </div>

    <div class="inner_btn">
        <input type="submit" value="다음">
    </div>
</div>

</form>

<script>
function frm_submit(f)
{
    if (!f.agree.checked) {
        alert("라이센스 내용에 동의하셔야 설치가 가능합니다.");
        return false;
    }
    return true;
}
</script>
<?php
} // if
?>

<?php
include_once ('./install.inc2.php');
