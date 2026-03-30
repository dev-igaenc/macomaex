		<div id="page-btm" class="fullBox" data-aos="fade-up"><div class="inConts">
			<p class="tit">비뇨기 질환의, 궁금한 점</p>
			<p class="title">그 무엇이든 칸비뇨의학과에<br />문의하세요!</p>
			<p class="btns"><a href="/bbs/write.php?bo_table=contact">온라인 상담신청 <img src="/theme/prix/img/arr.png" alt="" /></a></p>
		</div></div>

<script type="text/javascript">
	var videos = document.getElementById("play-video");
	var frame = $(".logo-video iframe").get(0);

	$(".logo-video").waypoint(function (direction) {
		var frame = $(".logo-video iframe").get(0);
		if (direction == 'down') {
			console.log("a")

			//alert(frame.getDuration())
			if(videos.paused){
				videos.play();
			} else{
				videos.pause();
			}
			/*
			postMessageToPlayer(frame, {
				"event": "command",
				"func": "mute"
			});
			postMessageToPlayer(frame, {
				"event": "command",
				"func": "playVideo"
			});
			*/
		}else{
			if(videos.play){
				videos.pause();
			}
			/*
			postMessageToPlayer(frame, {
				"event": "command",
				"func": "pauseVideo"
			});
			*/
		}
	}, {offset: '50%'});

	if(window.innerWidth < 841){
		$(".logo-video").waypoint(function (direction) {
			console.log("b")
			if (direction == 'down') {
				if(videos.play){
					videos.pause();
				}
				/*
				postMessageToPlayer(frame, {
					"event": "command",
					"func": "pauseVideo"
				});
				*/
			}
		}, {offset: '0%'});
	}else{
		$(".principles-methods-step").waypoint(function (direction) {
			console.log("b")
			if (direction == 'down') {
				if(videos.play){
					videos.pause();
				}
				/*
				postMessageToPlayer(frame, {
					"event": "command",
					"func": "pauseVideo"
				});
				*/
			}
		}, {offset: '90%'});
	}

	function postMessageToPlayer(player, command){
		if (player == null || command == null) return;
		player.contentWindow.postMessage(JSON.stringify(command), "*");
	}
</script>