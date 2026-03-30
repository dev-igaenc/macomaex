var scrollTop,			// 스크롤 높이
	 gnbTop,				// GNB Top
	 lnbTop,				// LNB Top

	 win_w,				// 윈도우 넓이 - 메인
	 win_h,				// 윈도우 높이 - 메인

	 lyNums = 0,			// 레이어에 사용
	 lyOne = 0;			// 레이어에 사용

var pcBgH = 0;
var confirmClicked = false;
var getParam = new Array();
location.search.replace(/([^=&?]+)=([^&]+)/gi, function (a, b, c) {
	getParam[decodeURIComponent(b)] = decodeURIComponent(c);
});
/*
if(window.matchMedia("(min-width: 840px)").matches){
}else{
}
*/
$(function(){
	var $someImages = $('img.ofi');
	objectFitImages($someImages);

	$("body").on("mouseenter", "#header:not('.all') #pcMenu>#list>li", function(){// [PC] GNB OVER
		var idx = $("#pcMenu #list>li:not('.one')").index($(this));
		$("#pcMenu #list>li:not('.one')").each(function(index){
			if(idx == index){
				$(this).addClass("active");
				$(this).find(".gnbSub").stop().slideDown(300);
			}else{
				$(this).removeClass("active");
				$(this).find(".gnbSub").stop().slideUp(300);
			}
		});
	});
	$("body").on("mouseleave", "#header:not('.all') #pcMenu>#list", function(){// [PC] GNB OUT
		$("#pcMenu #list .gnbSub").stop().slideUp(150);
		$("#pcMenu #list>li").removeClass("active");
	});

	$("body").on("click", ".language", function(){ // LANGUAGE 열기
		if(!$(".language").hasClass("open")){
			$(".language").addClass("open");
			$(".language>ul").stop().slideDown(300);
		}else{
			$(".language").removeClass("open");
			$(".language>ul").stop().slideUp(150);
		}
	});
	$("body").on("mouseleave", ".language", function(){ // LANGUAGE 닫기
		$(".language").removeClass("open");
		$(".language>ul").stop().slideUp(150);
	});

	$("body").on("click", "#header .tSchArea>.tSch", function(){// [PC] SEARCH OPEN
		$(".mMenu:not('.mo')").removeClass("open");
		$("#pcMenu .gnbSub").stop().slideUp(150, function(){
			$("#header .gnbBg").height(0)
			$("#header").removeClass("all");
		});

		if(!$(this).parent().hasClass("open")){
			$(this).parent().addClass("open");
			$("#header").addClass("sch");
		}else{
			$(this).parent().removeClass("open");
			$("#header").removeClass("sch");
		}
	});
	$("#header .tSchArea a.tSch").click(function (){ // [PC] SEARCH
		//console.log("aaa");
		if($('input[name="stx"]').val()==""){
			alert("검색어를 입력해주세요");
			return false;
		}
		$('form[name=\'search\']').submit();
	});

	$("body").on("mouseleave", "#wrap[data-device='pc'] #header", function(){// [PC] HEADER OUT
		if(!$(".mMenu").hasClass("mo")){
			$("#header").removeClass("all");
			$("#header .tSchArea").removeClass("open");
			$(".mMenu:not('.mo')").removeClass("open");
			$("#pcMenu .gnbSub").stop().slideUp(150, function(){
				$("#header .gnbBg").height(0)
				$("#header").removeClass("sch");
			});
		}
	});

	$("body").on("click", "#wrap[data-device='pc'] .mMenu", function(){ // [PC] ALL MENU OPEN
		$("#header").removeClass("all");
		$("#header .tSchArea").removeClass("open");

		if(!$(this).hasClass("open")){
			$(this).addClass("open");
			$("#header").addClass("all")
			$("#pcMenu .gnbSub").stop().slideDown(300);
		}else{
			$(this).removeClass("open");
			$("#pcMenu .gnbSub").stop().slideUp(150, function(){
				$("#header .gnbBg").height(0)
				$("#header").removeClass("all");
			});
		}
	});

	$("body").on("click", "#wrap[data-device='mobile'] .mMenu", function(){ // [MO] MENU OPEN
		if(!$(".mMenu").hasClass("open")){
			$("#header").addClass("all");
			$(".mMenu").addClass("open");
			$("html").addClass("hide");
			$("#menuArea").addClass("open");
		}else{
			$("#header").removeClass("all");
			$(".mMenu").removeClass("open");
			$("html").removeClass("hide");
			$("#menuArea").removeClass("open");
		}
	});
	$("body").on("click", "#menu.gnb>li:not('.one')>a", function(){// [MO] GNB OPEN
		var idx = $("#menu.gnb>li:not('.one')>a").index($(this));
		$("#menu.gnb>li:not('.one')>a").each(function(index){
			if(idx == index){
				if(!$(this).parent().hasClass("active")){
					$(this).parent().addClass("active");
					$(this).parent().find(".gnbSub").stop().slideDown(300);
				}else{
					$(this).parent().removeClass("active");
					$(this).parent().find(".gnbSub").stop().slideUp(300);
				}
			}else{
				$(this).parent().removeClass("active");
				$(this).parent().find(".gnbSub").stop().slideUp(300);
			}
		});
	});

	$("body").on("click", ".famLay", function(e){// FAMILY SITE
		if(!confirmClicked) {
			confirmClicked = true;
			$('body').on('scroll touchmove mousewheel', function(event) {
				event.preventDefault();
				event.stopPropagation();
				return false;
			});
		}

		//fullpage_api.setAllowScrolling(false);

		if($(this).hasClass("open")){
			$(this).removeClass("open")
			$(".famLay .in_fam").stop().slideUp(300);
		}else{
			$(this).addClass("open")
			$(".famLay .in_fam").stop().slideDown(300);
		}
	});
	$("body").on("mouseleave", ".famLay", function(){//  FAMILY SITE
		//fullpage_api.setAllowScrolling(true);

		$(".famLay").removeClass("open");
		$(".famLay .in_fam").stop().slideUp(300);

		if(confirmClicked) {
			$('body').off('scroll touchmove mousewheel');
			confirmClicked = false;
		}
	});
});

$(window).load(function(){
	$('img[usemap]').rwdImageMaps();

	// 오픈시 지워야함
	$("body").append('<p class="testText"></p>');

	// 서브모션
	//$("#sVisual, #pageTop, #contents").addClass("open");
	//$("#snb").attr("data-device","mobile");

	$("#bo_cate h2").html($("#bo_cate_ul #bo_cate_on").text());
	$("body").on("click", "#wrap[data-device='mobile'] #bo_cate", function(){// 카테고리 열기/닫기
		if(!$(this).hasClass("open")){
			$(this).addClass("open");
			$("#bo_cate_ul").stop().slideDown(300);
		}else{
			$(this).removeClass("open");
			$("#bo_cate_ul").stop().slideUp(300);
		}
	});

	$(".search-result h2").html($(".search-result a.sch_on").text());
	$("body").on("click", "#wrap[data-device='mobile'] .search-result", function(){// 전체검색결과 열기/닫기
		if(!$(this).hasClass("open")){
			$(this).addClass("open");
			$(".search-result>ul").stop().slideDown(300);
		}else{
			$(this).removeClass("open");
			$(".search-result>ul").stop().slideUp(300);
		}
	});

	$("body").on("mouseenter", "#wrap[data-device='pc'] #snb .sMenu", function(){// 서브 메뉴 열기
		var idx = $(".sMenu").index($(this));
		$(".sMenu").each(function(index){
			if(idx == index){
				if(!$(this).hasClass("active")){
					$(this).addClass("active");
					$(this).find(".lnbSub").stop().slideDown(300);
				}else{
					$(this).removeClass("active");
					$(this).find(".lnbSub").stop().slideUp(300);
				}
			}else{
				$(this).removeClass("active");
				$(this).find(".lnbSub").stop().slideUp(300);
			}
		});
	});
	$("body").on("click", "#wrap[data-device='mobile'] #snb .sMenu", function(){// 서브 메뉴 열기
		var idx = $(".sMenu").index($(this));
		$(".sMenu").each(function(index){
			if(idx == index){
				if(!$(this).hasClass("active")){
					$(this).addClass("active");
					$(this).find(".lnbSub").stop().slideDown(300);
				}else{
					$(this).removeClass("active");
					$(this).find(".lnbSub").stop().slideUp(300);
				}
			}else{
				$(this).removeClass("active");
				$(this).find(".lnbSub").stop().slideUp(300);
			}
		});
	});
	$("body").on("mouseleave", "#snb", function(){// 서브 메뉴 닫기
		$(".sMenu").removeClass("active");
		$(".sMenu .lnbSub").stop().slideUp(300);
	});

	$("body").on("click", ".faqArea .q", function(){// FAQ Script
		var idx = $(".faqArea .q").index($(this));
		$(".faqArea .q").each(function(index){
			if(idx == index){
				if(!$(this).parent().hasClass("active")){
					$(this).parent().addClass("active");
					$(".faqArea .a").eq(index).slideDown(300);
				}else{
					$(this).parent().removeClass("active");
					$(".faqArea .a").eq(index).slideUp(300);
				}
			}else{
				$(this).parent().removeClass("active");
				$(".faqArea .a").eq(index).slideUp(300);
			}
		});
	});

	$("body").on("click", ".goTop", function(){ // Go Top
		$('html, body').stop().animate({scrollTop: $("html").offset().top}, 1000, "easeOutExpo");
	});

	if($(".layerCell")){
		$(".layerCell").wrap('<div class="layerArea"><div class="layerIn">');
		$(".layerIn").append('<div class="layerBgIn"></div>');
	}

	$(window).on("scroll",function(){// Scroll Event
		scrollTop = $(window).scrollTop();
		scrollEvent(scrollTop);
	}).scroll();
	$(window).on('throttledresize', function(){// Resize Event
		win_w = $(window).width();
		win_h = $(window).height();
		resizeEvent(win_w,win_h);
	}).resize();
	setTimeout(function(){
		//thumbnail();
		scrollEvent($(window).scrollTop());
		resizeEvent($(window).width(),$(window).height());
	}, 100);
	setTimeout(function(){
		//thumbnail();
	}, 300);
});
function ScrollporgressBar(){
	var maxY = document.documentElement.scrollHeight - document.documentElement.clientHeight;
	var scrollY = window.scrollY || document.documentElement.scrollTop;
	return (scrollY / maxY) * 100;
}
// Scroll Event
function scrollEvent(st){
	var progress = ScrollporgressBar();
	$('#progressBar').css("width", progress+'%');

	if(st > 0){
		$("a.goTop").addClass("open");
		if($(".mScrolls")){
			$(".mScrolls").stop().fadeOut(50);
		}
	}else{
		$("a.goTop").removeClass("open");
		if($(".mScrolls")){
			$(".mScrolls").stop().fadeIn(300);
		}
	}
}
// Resize Event
var one_mo = true;
function resizeEvent(w,h){
	if($("html").hasClass("hide")){
		$("html").removeClass("hide");
		$("#header").removeClass("all");
		$(".mMenu").removeClass("open");
		$("#menuArea").removeClass("open");
		$("#header").removeClass("sch");
		$(".tSchArea").removeClass("open");
	}

	$("#header").removeClass("sch");
	$(".tSchArea").removeClass("open");

	$("#bo_cate_ul").attr("style", "")
	//alert($(window).width())

	/*
	$(".nTable tr").each(function(index){
		$(this).find(">*:last-child").attr("style","").css("border-right","none");
	});
	*/
	if($("html").hasClass("main")){
	}

	if(w <= "1024"){
		$("#wrap").attr("data-device","mobile");
		setTimeout(function(){
			var h_top = $("#header").height();
			$("#menuBg, #menuArea").css("top", h_top);
		}, 300);

		if($("#mVisual")){
			$("#mVisual .slider").each(function(index){
				$(this).css("background-image", "url("+$(this).attr("data-mo")+")")
			});
		}

		if(one_mo){
			$("#menu>li:not('.one')>a").attr("pc-href", $("#menu>li:not('.one')>a").attr("href"));
			one_mo = false;
		}
		$("#menu>li:not('.one')>a").attr("href","javascript:void(0);");

		$(".btnArea").each(function(index){// 버튼 정렬
			var btnlength = $(this).find(">*").length;
			if(btnlength == 1){
				$(this).addClass("one")
			}else if(btnlength == 2){
				$(this).addClass("two")
			}else if(btnlength > 2){
				$(this).addClass("three")
			}
		});
	}else{
		$("#wrap").attr("data-device","pc");

		if($("#mVisual")){
			$("#mVisual .slider").each(function(index){
				//$(this).css("background-image", "url("+$(this).attr("data-pc")+")")
			});
		}

		if(!one_mo){
			$("#menu>li:not('.one')>a").attr("href", $("#menu>li:not('.one')>a").attr("pc-href"));
		}

		$(".mMenu").removeClass("open");
		$("html").removeClass("hide");
		$("#menuArea").removeClass("open");
	}
	if(w <= "840"){
		setTimeout(function(){
			var h_top = $("#header").height();
			$(".tSchArea .tSch_in").css("top", h_top);
		}, 300);
	}else{
		$(".tSch_in").attr("style", "");
	}
}

/*
function sel_it(url){
	if(url != ""){
		location.href=url;
		//window.open(url);
		$(".famLay option:eq(0)").attr("selected", "selected");
	}
}
*/
/*
// thumbnail 만들기
function thumbnail(){
	var blank = '/images/blank.gif';
	//if(!$("img.bg_thumb").hasClass("one")){
	$("img.bg_thumb").each(function(index){
		var t_size = $(this).size();
		var t_w = $(this).width();
		var t_h = $(this).height();
		var t_src = $(this).attr("src");
		var bg_siz = "cover";
		var bg_c = "background-color: #f2f2f2";
		//alert(t_size+"__"+t_w+"__"+t_h)
		//$(this).addClass("one");
		if(t_src == "" || t_size == 0 || t_w == 0){
			t_src="/images/front/noimage.jpg";
			//nos = "noBg";
		}

		if(t_w < t_h){
			bg_siz="contain";
			bg_c = "background-color:#fff"
		}else{
			bg_siz = "cover";
		}

		//$(this).attr("src", blank).css({"background-size":bg_siz, "width":"100%", "max-width":"9999px", "height":"100%", "":''})
		//alert($(this).parent().attr("class") == "bg_thumb_box")
		if($(this).parent().attr("class") != "bg_thumb_box"){
			//alert("d")
			$(this).wrap('<p class="bg_thumb_box" style="'+bg_c+'; background-image:url(\''+t_src+'\'); background-size:'+bg_siz+'"></p>');
		}else{
			$(this).parent().attr("style", bg_c+'; background-image:url(\''+t_src+'\'); background-size:'+bg_siz);
		}
	});
	//}
}
*/
/*
function lastDepthMenu(num){
	var $this = $(".lastDepthSelect .lastDepthSub>a").eq(num);

	$(".lastDepthSelect .lastDepthSub>a").removeClass("active");
	$(".lastDepthConts").hide();

	$this.addClass("active");
	$(".lastDepthSelect>a>span").text($this.text());
	$(".lastDepthConts").eq(num).show();
}
*/
// 레이어 열기
var layOne = true;
var scratchState = false;
function openLay(name, gNum){
	if(layOne == true){

		lyNums++;
		//console.log(lyNums);
		$(".layerBox").each(function(index){
			if($(this).hasClass(name)){
				layOne = false;
				$(this).layerScript({divs : name});

				if(name == "walletSortLay"){
					$(".layerCell").addClass("btm")
				}

				setTimeout(function(){
					if(name == "full_clauseLay"){
						full_clause(gNum)
						//$("input#numberLay_pass").attr('autofocus', 'autofocus');

						/*
						$("body").on("click", "input#numberLay_pass", function(){
							//alert("c")
							$("input#numberLay_pass").attr('autofocus', 'autofocus').focus();
						});
						$("input#numberLay_pass").click();
						*/
					}
				}, 100);

				setTimeout(function(){
					if(name == "scratchLay"){
						scratchEvent();
					}
				}, 100);
			}
		});
	}
}
// 레이어 닫기
function layerClose(name){
	$(".layerBox").each(function(index){
		if($(this).hasClass(name)){
			var e = $(this);

			e.attr("lyNums", "");
			e.hide().attr("style","");

			lyNums --;
			if(lyNums == 0){
				$("html").css("overflow-y","auto");
				$("body").removeClass("lyOn");
				$(".layerArea").removeClass("one");
				$(".layerBox").attr("lyNums", "").hide();
			}
		}

		if($(this).attr("lyNums") == 1){
			$(this).show();
			$(".layerArea").removeClass("two");
			$(".layerArea").addClass("one");
		}
	});
}
// Plugin Script
jQuery(function($){
	//[s] Layer Script
	$.fn.layerScript = function(o){
		o = $.extend({
			divs : ''
		}, o || {});

		var e = $(this),
			  //bg = $('<div id="layerBg"></div>'),
			  //bgIn = $('<div class="layerBgIn"></div>'),
			  ly_w,
			  ly_h,
			  closeDiv = o.divs;

		e.attr("lyNums", lyNums);
		$("body").attr("lyNums", lyNums);
		// 열기
		if(!$("body").hasClass("lyOn")){
			$("html").css("overflow-y","hidden");
			$("body").addClass("lyOn");
			$(".layerArea").addClass("one");
		}
		if(lyNums == 2){
			$(".layerArea").removeClass("one");
			$(".layerArea").addClass("two");
			$(".layerBox").hide();
			e.css("z-index",13);
		}

		e.show();
		layOne = true;

		setTimeout(function(){
		}, 300);

		// 닫기
		$(this).find(".closeLy").off("click");
		$(this).find(".closeLy").on('click', function(){
			layerClose(closeDiv);
		});
		$(this).find(".btnArea>.closeLy").off("click");
		$(this).find(".btnArea>.closeLy").on('click', function(){
			layerClose(closeDiv);
		});
	}
	//[e] Layer Script

	//[s] Img Over Script
	$.fn.iOverScript = function(o){
		o = $.extend({
			btns : '',
			bg : '',
			speed : 700
		}, o || {});

		var e = $(this);

		// 활성화
		e.on("mouseenter", o.btns, function(event){
			var idx = $(o.btns).index($(this));
			point_ray("on", idx, event);
		});
		// 비활성화
		e.on("mouseleave", o.btns, function(event){
			var idx = $(o.btns).index($(this));
			point_ray("off", idx, event);
		});

		function point_ray(directions, nums, event){
			e.find(o.btns).each(function(index){
				if(nums == index){
					w = $(this).width();
					h = $(this).height();
					x = ( event.pageX - $(this).offset().left - ( w/2 )) * ( w > h ? ( h/w ) : 1 ),
					y = ( event.pageY - $(this).offset().top  - ( h/2 )) * ( h > w ? ( w/h ) : 1 ),
					direction = Math.round( ( ( ( Math.atan2(y, x) * (180 / Math.PI) ) + 180 ) / 90 ) + 3 )  % 4;

					if(directions == "on"){
						$(this).find(o.bg).show();
						if(direction == 0) {
							$(this).find(o.bg).css({"top":-h, "left":0});
						} else if(direction == 1) {
							$(this).find(o.bg).css({"top":0, "left":w});
						} else if(direction == 2) {
							$(this).find(o.bg).css({"top":h, "left":0});
						} else {
							$(this).find(o.bg).css({"top":0, "left":-w});
						}
						$(this).find(o.bg).stop().animate({top:0, left:0}, o.speed, "easeOutExpo");
					}else if(directions == "off"){
						if(direction == 0) {
							$(this).find(o.bg).stop().animate({top:-h, left:0}, o.speed, "easeOutExpo", function(){$(this).parent().find(o.bg).hide();});
						} else if(direction == 1) {
							$(this).find(o.bg).stop().animate({top:0, left:w}, o.speed, "easeOutExpo", function(){$(this).parent().find(o.bg).hide();});
						} else if(direction == 2) {
							$(this).find(o.bg).stop().animate({top:h, left:0}, o.speed, "easeOutExpo", function(){$(this).parent().find(o.bg).hide();});
						} else {
							$(this).find(o.bg).stop().animate({top:0, left:-w}, o.speed, "easeOutExpo", function(){$(this).parent().find(o.bg).hide();});
						}
					}
				}
			});
		}
	}
	//[e] Img Over Script
});

// 따라다니는 베너
var area_TT;
(function($) {
	$.fn.followingBanner = function(options) {
		var defaults = {
			direction : "top",
		};
		var settings = $.extend( {}, defaults, options );

		return this.each(function(index) {
			let ST = 0;
			let WW = 0;
			let WH = 0;
			var total_L = $(".followingBanner.top").length;
			var s = settings;
			var obj = $(this);
			var area_T = obj.offset().top;
			var area_H = obj.outerHeight();
			var area_Ts;

			obj.attr("data-top", area_T);
			obj.wrap('<div id="'+obj.attr("class")+'" class="followingBanner" style="height:'+area_H+'px"></div>');
			$("#"+obj.attr("class")).addClass(s.direction)

			$(window).trigger('resize');
			$(window).on('resize',function(){
				WH = $(window).height();
				area_T = obj.closest(".followingBanner").offset().top;
				area_H = obj.outerHeight();
				obj.closest(".followingBanner").height(area_H)
				if(s.direction == "top" && total_L > 0){
					//alert($(".followingBanner.top").eq(total_L).height())
					area_TT = area_T - $(".followingBanner.top").eq(total_L-1).height();
					area_T = area_TT;
					area_Ts = $(".followingBanner.top").eq(total_L-1).height();
					//alert(area_T)
					console.log(ST, area_T + $(".followingBanner.top").eq(total_L-1).height(), $(".followingBanner.top").eq(total_L-1).height(), total_L)
				}
			});

			$(window).on('wheel',function(){
				ST = window.scrollY || window.pageYOffset;

				if(s.direction == "top"){
					if( ST >= area_T){
						obj.attr("style", "position: fixed; top:"+area_Ts+"px");
					}else{
						obj.attr("style" ,"")
						//obj.unwrap();
					}
				}

				if(s.direction == "bottom"){
					if( (ST+WH-area_H) >= area_T){
						obj.attr("style" ,"position: static;")
					}else{
						obj.attr("style", "");
						//obj.unwrap();
					}
				}

				//console.log(ST+"__"+area_T+"__"+(ST+WH-area_H))
			});

			$(window).on('scroll',function(){
				$(window).trigger('wheel');
			});
			$(window).trigger('resize');
			$(window).trigger('wheel');
			$(window).trigger('scroll');
		});
	};
})(jQuery);

// 멀티 텝버튼
(function($) {
	$.fn.multiTab = function(options) {
		var defaults = {
			//direction : "top",
		};
		var settings = $.extend( {}, defaults, options );

		return this.each(function(index) {
			var tab = $(this);
			var tab_name = tab.find(".name");
			var tab_list = tab.find(".multi-list");
			var tab_trigger = tab.find(".multi-list>li");
			var tab_select = tab.find(".multi-list>li.active");

			tab_name.html(tab_select.text());


			tab_trigger.on("click", function(){// 카테고리 열기/닫기
				tab_trigger.removeClass("active");

				$(this).addClass("active");
				tab_name.html($(this).text());
			});

			tab.on("click", function(){// 카테고리 열기/닫기
				if(window.matchMedia("(max-width: 840px)").matches){
					if(!$(this).hasClass("open")){
						$(".multi-tab .multi-list").stop().slideUp(300);
						$(".multi-tab").removeClass("open");

						$(this).addClass("open");
						tab_list.stop().slideDown(300);
					}else{
						$(this).removeClass("open");
						tab_list.stop().slideUp(300);
					}
				}
			});

			$(window).on('throttledresize', function(){// Resize Event
				$(".multi-list").attr("style", "");
			}).resize();
		});
	};
})(jQuery);