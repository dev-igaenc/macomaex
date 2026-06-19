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



<!-- 하단 시작 { -->

<section id="partner-showcase" aria-label="협력기업 로고 영역">
    <div class="inConts">
        <div class="partner-showcase_rows">
            <div class="partner-showcase_item partner-showcase_item--kodata">
                <div class="partner-showcase_logo is-kodata">
                    <img src="/img/KODATA.png" alt="KODATA 로고">
                </div>
                <p class="partner-showcase_text">KODATA 기업데이터 기반 AI 추천</p>
            </div>
            <div class="partner-showcase_item partner-showcase_item--innobiz">
                <div class="partner-showcase_logo is-innobiz">
                    <img src="/img/partner-innobiz-logo-200.png" alt="이노비즈 로고">
                </div>
                <p class="partner-showcase_text">이노비즈 협회 회원서비스 운영 기관</p>
            </div>
        </div>
    </div>
</section>

<div id="footer">
    <div class="inConts">
        <div class="textArea">
            <ul class="links">
                <li><a href="<?=$link_0101?>">매커멕스란</a></li>
                <li><a href="<?=$link_1201?>">이용약관</a></li>
				<li><a href="<?=$link_1202?>">개인정보 처리방침</a></li>
				<li><a href="<?=$link_1204?>">환불규정</a></li>
                <li><a href="<?=$link_0501?>">공지사항</a></li>
                <li><a href="<?=$link_0502?>">FAQ</a></li>
            </ul>

            <ul class="desc">
                <li>(주)이가이엔씨</li>
				<li>대표자 : 이민형</li>
				<li>사업자등록 번호 : 186-81-01689</li>
				<li>통신판매신고 번호 : 제2021-서울서초-2339호</li>

                <li>주소 : 서울 강남구 테헤란로 138 성홍타워 4층 413호, 415호</li>
                <li>E-MAIL : admin@macomaex.com</li>
                <li>TEL : 02-573-8677</li>
				<!--
                <li>주소 서울시 마포구 성산동 234 성산 푸르지오</li>
                <li>E-MAIL  sallbull@gmail.com</li>
                <li>TEL  010-9922-3157</li>
				-->
                <li class="copyright">COPYRIGHT©MACOMAEX. ALL RIGHT RESERVED.</li>
            </ul>
        </div>
        <div class="logoArea">
            <img src="/img/logo.svg" alt="">
        </div>
    </div>
</div>

</div>




<a href="javascript:void(0);" class="goTop">go top</a>

<?php
if(G5_DEVICE_BUTTON_DISPLAY && !G5_IS_MOBILE) { ?>
<?php
}

if (!empty($analytics_enabled) && $config['cf_analytics']) {
    echo $config['cf_analytics'];
}
?>

<!-- } 하단 끝 -->

<script>
$(function() {
    // 폰트 리사이즈 쿠키있으면 실행
    //font_resize("container", get_cookie("ck_font_resize_rmv_class"), get_cookie("ck_font_resize_add_class"));
});
</script>


<?php include_once(G5_THEME_PATH."/tail.sub.php"); ?>


<script type="text/javascript">
	AOS.init({
		offset: 200,
		once: true,
		//delay: 0,
		duration: 700,
		easing: "ease-in-out-sine"
		//easing: "cubic-bezier(.25,.46,.45,.94)"
		//easing: 'ease-out'
	});
</script>
<?php if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] == '222.107.227.177') { ?>
<script>


  (function(){var w=window;if(w.ChannelIO){return w.console.error("ChannelIO script included twice.");}var ch=function(){ch.c(arguments);};ch.q=[];ch.c=function(args){ch.q.push(args);};w.ChannelIO=ch;function l(){if(w.ChannelIOInitialized){return;}w.ChannelIOInitialized=true;var s=document.createElement("script");s.type="text/javascript";s.async=true;s.src="https://cdn.channel.io/plugin/ch-plugin-web.js";var x=document.getElementsByTagName("script")[0];if(x.parentNode){x.parentNode.insertBefore(s,x);}}if(document.readyState==="complete"){l();}else{w.addEventListener("DOMContentLoaded",l);w.addEventListener("load",l);}})();

  ChannelIO('boot', {
    "pluginKey": "296accc8-9450-4f33-a7e5-8a61d905164d"
  });


</script>
<?php } ?>
