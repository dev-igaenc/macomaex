<?
include_once("./_common.php");

$board['bo_10']="7";
$board['bo_9']="7";
$board['bo_5'] = "스크립트";
$board['bo_subject'] = "구글지도";
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";

include_once(G5_THEME_PATH.'/head.php');
?>
<script type="text/javascript" src="https://maps.google.co.kr/maps/api/js?key=AIzaSyBqEy7QNt6VpqC3SNVWnmSQrwWGw_ruTC8"></script>
<script type="text/javascript" src="/build/google-map.js"></script>
<style>
	.multi-tab{width:100%; margin: 0 auto 4rem; font-size:0; line-height:0; text-align: center; display:block; position: relative;}
	.multi-tab .name{width:100%; height:7rem; padding:0 5rem 0 2rem; border:1px solid #ddd; font-weight: 500; font-size:1.7rem; line-height:1.4; color: #333; text-align: left; position: relative; display: none; flex-direction: row; align-items: center; justify-content: flex-start; -ms-flex-align:center;}
	.multi-tab .name:after,
	.multi-tab .name:before{content: ""; width:1.4rem; height:1px; margin:0 0 0 0; background-color: #333; display: block; position:absolute; top:50%;}
	.multi-tab .name:after{right:2rem; transform:rotate(45deg);}
	.multi-tab .name:before{right:1rem; transform:rotate(-45deg);}

	.multi-tab ul.multi-list{margin:-0.5rem; display: flex; flex-wrap: wrap; flex-direction: row; justify-content: center;}
	.multi-tab ul.multi-list>li{margin:0.5rem; flex:0 1 16rem; display:inline-block; vertical-align: top; position: relative;}
	.multi-tab ul.multi-list>li a{width:100%; height:4.6rem; padding:0 2rem; border:1px solid #ddd; font-weight: 400; font-size:1.6rem; line-height:1.4; color: #666; display: flex; flex-direction: row; align-items: center; justify-content: center; -ms-flex-align:center; white-space: nowrap; border-radius:99px;}

	.multi-tab ul.multi-list>li.active a,
	.multi-tab ul.multi-list>li:hover a{background-color:var(--point); border-color:var(--point); color:#fff;}
	.multi-tab ul.multi-list>li.active a{font-weight: 500;}

	@media only screen and (max-width : 840px){
		.multi-tab{}
		.multi-tab .name{font-size:2.2rem; display: flex;}

		.multi-tab ul.multi-list{width:100%; margin:0; background-color: #fff; border:1px solid #ddd; display: none; position: absolute; left:0; top:calc(100% - 1px); z-index:3;}
		.multi-tab ul.multi-list>li{margin:0; display:block;}
		.multi-tab ul.multi-list>li+li{border-top:1px solid #ddd;}
		.multi-tab ul.multi-list>li a{height:6.5rem; border:none; font-size:2rem; text-align: left; white-space: normal; border-radius:0; justify-content: flex-start;}
		.multi-tab ul.multi-list>li a>span{}

		.multi-tab.open .name:after{transform:rotate(-45deg);}
		.multi-tab.open .name:before{transform:rotate(45deg);}
	}
</style>
<script>
	$(window).load(function(){	// $(".testText").text("off2");
		GoogleMap.initialize("서울시 상암동 1649");

		$("body").on("click", ".multi-list a", function(){ // READ MORE on/off
			var idx = $(".multi-list a").index($(this));
			$(".multi-list a").each(function(index){
				if(idx == index){
					console.log($(this).attr("data-juso"))
					GoogleMap.initialize($(this).attr("data-juso"));
				}
			});
		});
	});
</script>
<div id="GoogleMap_map" style="height:30rem;"></div>

<div class="multi-tab">
	<button type="button" class="name"></button>
	<ul class="multi-list">
		<li class="active"><a href="javascript:void(0);" data-juso="서울시 상암동 1649">TAB01-01</a></li>
		<li><a href="javascript:void(0);" data-juso="서울시 서초구 서초대로 132">TAB01-02</a></li>
		<li><a href="javascript:void(0);" data-juso="서울시 서초구 서초대로 127">TAB01-03</a></li>
		<li><a href="javascript:void(0);" data-juso="서울시 구로구 오류동 행복주택">TAB01-04</a></li>
		<li><a href="javascript:void(0);" data-juso="서울시 구로구 경인로20가길 68">TAB01-05</a></li>
	</ul>
</div>

<?php include_once(G5_THEME_PATH . '/tail.php'); ?>

<script>
	$(document).ready(function() {
		$(".multi-tab").multiTab();
	});
</script>