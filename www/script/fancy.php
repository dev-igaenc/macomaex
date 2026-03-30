<?
include_once("./_common.php");

$board['bo_10']="7";
$board['bo_9']="6";
$board['bo_5'] = "스크립트";
$board['bo_subject'] = "FancyBox(팝업)";
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";

include_once(G5_THEME_PATH.'/head.php');
?>

<style type="text/css">
</style>

<nav id="bo_cate" class="category_top">
	<h2>전체</h2>
	<ul id="bo_cate_ul" style="">
		<li><a href="javascript:void(0);" onclick="javascript:popFancy('#layer-juso');"><span>오시는길</span></a></li>
		<li><a href="javascript:void(0);" onclick="javascript:popFancy('#layer-time');"><span>진료시간</span></a></li>
	</ul>
</nav>


<?
include_once(G5_THEME_PATH.'/tail.php');
?>

<div id="layer-time" class="layer-base layer-main"><div class="layer-in">
	<div class="title">진료시간</div>
	<div class="lay-time-area">
		<div class="lay-map"><img src="<?php echo G5_THEME_IMG_URL ?>/thumb/thumb_700x350.png" alt="">
			<div id="daumRoughmapContainer1670986132765" class="root_daum_roughmap root_daum_roughmap_landing"></div>
		</div>
		<div class="lay-table">
			<table>
				<colgroup>
					<col width="20.3125%"/>
					<col width="*"/>
				</colgroup>
				<tbody>
					<tr>
						<th>평일</th>
						<td>오전 10:00 ~ 오후 07:00</td>
					</tr>
					<tr>
						<th>수요일</th>
						<td>오전 10:00 ~ 오후 08:00</td>
					</tr>
					<tr>
						<th>점심시간</th>
						<td>오후 01:00 ~ 오후 02:00</td>
					</tr>
					<tr>
						<th>토요일</th>
						<td>오전 09:00 ~ 오후 03:00 [토요일 점심시간 없이 진료]</td>
					</tr>
					<tr>
						<th colspan="2">일요일 및 공휴일 휴진 <span class="tel">전화상담/예약  <strong><?=$config['cf_4']?></strong></span></th>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div></div>
<div id="layer-juso" class="layer-base layer-main"><div class="layer-in">
	<div class="title">오시는 길</div>
	<div class="lay-time-area">
		<div class="lay-map"><img src="<?php echo G5_THEME_IMG_URL ?>/thumb/thumb_700x350.png" alt="">
			<div id="daumRoughmapContainer1670991194005" class="root_daum_roughmap root_daum_roughmap_landing"></div>
		</div>
		<div class="lay-table">
			<table>
				<colgroup>
					<col width="20.3125%"/>
					<col width="*"/>
				</colgroup>
				<tbody>
					<tr>
						<th>주소</th>
						<td><?=$config['cf_2']?><?=$config['cf_3']?></td>
					</tr>
					<tr>
						<th>전화번호</th>
						<td><?=$config['cf_4']?></td>
					</tr>
					<tr>
						<th>지하철</th>
						<td>지하철 8호선 다산역 1번출구 (예정지) 도보 1분</td>
					</tr>
					<tr>
						<th>자가용</th>
						<td>지하주차장 이용 가능</td>
					</tr>
					<tr>
						<th colspan="2">
							<a href="https://map.naver.com/v5/entry/place/1541332810?c=14154354.0133335,4526541.6759344,15,0,0,0,dh" target="_blank" class="btns">네이버 지도보기<span class="f_arr">→</span></a>
							<a href="https://map.kakao.com/?from=total&q=%EC%9D%80%ED%95%98%EC%88%98%EC%95%88%EA%B3%BC%EC%9D%98%EC%9B%90" target="_blank" class="btns kakao">KAKAO 지도보기<span class="f_arr">→</span></a>
						</th>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div></div>

<script charset="UTF-8" class="daum_roughmap_loader_script" src="https://ssl.daumcdn.net/dmaps/map_js_init/roughmapLoader.js"></script>