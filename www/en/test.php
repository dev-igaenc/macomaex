<?
include_once("./_common.php");
$depth_1="1";
$depth_2="1";
$depth_3="";
$depth_4="";
$gr_subject = "펀블";
$bo_subject = "펀블";
$bo_text = "";
include_once(G5_THEME_PATH.'/head.php');
?>
<style>
.ProgressWrap{padding:3rem;}
.ProgressWrap *{font-family:'Malgun Gothic';}
.ProgressWrap h1{font-size:3.4rem; line-height:1; color: #3275bc;}
.ProgressWrap h2{padding:3rem 0 1rem; font-size:2.2rem; line-height:1; color: #3275bc; letter-spacing:-1px; position: relative;}

.ProgressWrap .end{background-color: #9e2929 !important; color: #fff !important;}
.ProgressWrap .ing{background-color: #6599d1 !important; color: #fff !important;}

.ProgressWrap .ps{padding-top:2rem;}
.ProgressWrap .ps>span{margin-right:0.5rem; padding:0.5rem 1rem 0.7rem; font-size:1.4rem; line-height:1; text-align: center; display:inline-block; vertical-align: top; border-radius:5px; box-sizing: border-box;}

.ProgressWrap .menuLink{width:100%; white-space: nowrap;}
.ProgressWrap .menuLink li{padding-top:1rem;}
.ProgressWrap .menuLink>li{padding-top:3rem;}
.ProgressWrap .menuLink>li:first-child{padding-top:0px;}
.ProgressWrap .menuLink li>a{background-color:#eee; color: #000; -webkit-transition: .3s; transition: .3s;}
.ProgressWrap .menuLink li>ul{margin-top:-10px; margin-left:10px; display:inline-block;}
.ProgressWrap .menuLink ul{}

.ProgressWrap .depth{width:100%; padding-bottom:10px; white-space: nowrap;}
.ProgressWrap .depth li{margin-left:10px; display:inline-block; vertical-align: top;}
.ProgressWrap .depth li:first-child{margin-left:0;}
.ProgressWrap .depth li>p{background-color:#aaa; color: #fff;}

.ProgressWrap .dTit{width:150px; height:30px; padding:0 15px; font-size:12px; line-height:27px; text-align: center; display:inline-block; vertical-align: top; border-radius:3px; overflow:hidden; box-sizing:border-box; position: relative;}
.ProgressWrap .dTit>span{display:inline-block; position: relative;}

.ProgressWrap .testText{padding:5px; font-size:18px; line-height:20px; color:#000; position: fixed; left:0; top:0; z-index:10;}

.ProgressWrap .ingBar{width:100%; height:16px; background-color: #ccc; border-radius:8px; position: relative;}
.ProgressWrap .ingBar>.bar{width:0%; height:16px; background-color: #9e2929; border-radius:8px; position: relative; overflow:hidden;}
.ProgressWrap .ingBar>.bar>.txt{font-size:12px; line-height:13px; color:#fff; text-align: center; position: absolute; right:10px; top:0;}
</style>

<div class="ProgressWrap">
	<h1>퍼블 작업 진행도</h1>
	<p class="ps"><span class="ing">진행</span> <span class="end">완료</span></p>

	<h2>진행률</h2>
	<div class="ingBar">
		<div class="bar"><p class="txt"></p></div>
	</div>

	<h2>메뉴</h2>
	<ul class="depth">
		<li><p class="dTit"><span>1 Depth</span></p></li>
		<li><p class="dTit"><span>2 Depth</span></p></li>
		<li><p class="dTit"><span>3 Depth</span></p></li>
		<li><p class="dTit"><span>4 Depth</span></p></li>
	</ul>
	<ul class="menuLink">
		<li>
			<a href="javascript:void(0);" class="dTit" data-num="0" data-link="javascript:link0000();" data-state=""><span>Main</span></a>
		</li>
		<script type="text/javascript">globalLinkManager()</script>
	</ul>
</div>
<script type="text/javascript">
	var c_total = $(".menuLink a").length,
		 c_end = $(".menuLink a[data-state=end]").length,
		 c_this = parseInt(c_end/c_total*100),
		 c_speed = 0;
	$(window).load(function(){
		ProgressBarAni(c_this);
		$(".menuLink a").each(function(index){
			var num = $(this).attr("data-num");
			if($(this).attr("data-state") != "end"){
				$(this).addClass("ing");
				if(num.length != 2) $(this).attr("href","").attr("href","javascript:alert('작업 중입니다. ^^;');");
			}else{
				$(this).addClass("end");
				$(this).attr("href", $(this).attr("data-link"))
			}
		});

		$("body").on("click", ".menuLink a", function(){
			var idx = $(".menuLink a").index($(this));
			$(".menuLink a").each(function(index){
				if(idx == index){
					var num = $(this).attr("data-num");
					var att = $(this).attr("href");
					if(att != "javascript:alert('작업 중입니다. ^^;');"){
						if(num.length != 2){
							eval("link"+num+"('h')");
						}
					}
				}
			});
		});

		$(".dTit").on('mouseenter mouseleave', function(e) {
			if( e.type == 'mouseenter'){
				var idx = $(".dTit").index($(this));
				$(".dTit").each(function(index){
					if(idx == index){
						var ins = $(this).find(">span");
						if(ins.width() > "150"){
							TweenMax.to(ins, 2, {left:"-"+(ins.width()-130), ease:Linear.easeNone});
						}
					}
				});
			} else if( e.type == 'mouseleave'){
				TweenMax.to(".dTit>span", 2, {left:0, ease:Linear.easeNone});
			}
			return false;
		});
	});

	function ProgressBarAni(num){
		if(num < 33){
			c_speed = 2000;
		}else if(num > 33 && num < 66){
			c_speed = 2000;
		}else{
			c_speed = 2000;
		}

		$(".ingBar>.bar").animate({
				width: num+"%"
			}, {
				"easing" : "easeOutBounce", //이징
				"queue": false, // 연계
				"duration": c_speed, // 속도
			step: function( now, fx ){
				$(".ingBar>.bar>.txt").text(parseInt(now)+"%");
			},
			complete: function() {
			}
		});
	}
</script>
<?
include_once(G5_THEME_PATH.'/tail.php');
?>