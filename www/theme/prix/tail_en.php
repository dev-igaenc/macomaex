<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
	include_once(G5_THEME_MOBILE_PATH.'/tail.php');
	return;
}
?>
	<?php if (!defined("_INDEX_")) { ?>
		</div>
	</div>
	<?php }?>

</div>
<!-- } 콘텐츠 끝 -->

<hr>

<!-- 하단 시작 { -->
<div id="footer"><div class="inConts">
	<div class="link">
		<a href="<?=$link12_01?>">개인정보보호정책 </a>
		<a href="<?=$link12_02?>" >이메일무단수집거부</a>
	</div>
	<div class="text">
		<p><span>(주)씨와이비 E</span></p>
		<p><span>주소 : 서울특별시 서초구 논현로 21 4층 (양재동, 영창빌딩)</span></p>
		<p><span>Tel : 1899-1478</span><span>Fax : 02-3478-3346</span><span>Email : Cyb.aean@gmail.com</span></p>
		<address>Copyright &copy; 2022 <span>씨와이비</span>. All Rights Reserved.</address>
	</div>

	<div class="famArea">
		<div class="famLay">
			<p>FAMILY SITE</p>
			<div class="in_fam">
				<a href="https://www.naver.com/" target="_blank">계열사1</a>
				<a href="https://www.daum.net/" target="_blank">계열사2</a>
			</div>
		</div>
	</div>

	<div class="csArea">
		<p class="tit">대표전화</p>
		<p class="tel">1899.1478</p>
		<p class="txt">평일 10:00 ~ 18:00 (점심 12:00~13:00)<br />토 / 일 / 공휴일 휴무</p>
	</div>
</div></div>
<a href="javascript:void(0);" class="goTop">go top</a>

<?php
if(G5_DEVICE_BUTTON_DISPLAY && !G5_IS_MOBILE) { ?>
<?php
}

if ($config['cf_analytics']) {
    echo $config['cf_analytics'];
}
?>

<!-- } 하단 끝 -->

<script>
$(function() {
    // 폰트 리사이즈 쿠키있으면 실행
    font_resize("container", get_cookie("ck_font_resize_rmv_class"), get_cookie("ck_font_resize_add_class"));
});
</script>


<?php include_once(G5_THEME_PATH."/tail.sub.php"); ?>

<script type="text/javascript">
	AOS.init({
		once: true,
		//delay: 0,
		duration: 700,
		easing: 'ease-out'
	});
</script>