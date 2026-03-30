<?
include_once("./_common.php");



$store_sql="SELECT * FROM {$g5['write_prefix']}agency where 1 and wr_id={$wr_id} ";
$row=sql_fetch($store_sql);

include_once(G5_THEME_PATH.'/view_head.php');
?>

<link rel="stylesheet" href="/theme/basic/css/sub.css" />
<script src="/theme/basic/js/sub.js"></script>
<input type="hidden" name="wr_8" id="wr_8" value="<?=$row['wr_8']?>">
<input type="hidden" name="wr_9" id="wr_9" value="<?=$row['wr_9']?>">

<section>
    <div class="topVis" style="background: url('/images/sub/view_bg.jpg')no-repeat;">
        <strong class="topVis__tit"><?=$row['wr_subject']?></strong>
        <div class="topVis__line"></div>
        <em class="topVis__txt"><?=$row['wr_2']?></em>
        <span class="topVis__phone"><?=$row['wr_5']?></span>
    </div>
    <nav class="vieNav">
        <div class="vieNavInner limit1280">
            <a href="/product/list2.php?ca_id=10" class="vieNav__link" target="_blank">도어</a>
            <a href="/product/list2.php?ca_id=20" class="vieNav__link" target="_blank">글라스도어</a>
            <a href="/product/list2.php?ca_id=30" class="vieNav__link" target="_blank">중문&파티션</a>
            <a href="/product/list2.php?ca_id=40" class="vieNav__link" target="_blank">몰딩</a>
            <a href="/product/list2.php?ca_id=50" class="vieNav__link" target="_blank">월판넬&마감재</a>
            <a href="/product/list2.php?ca_id=60" class="vieNav__link" target="_blank">인테리어필름</a>
            <a href="/product/list2.php?ca_id=70" class="vieNav__link" target="_blank">하드웨어</a>
            <a href="/product/list2.php?ca_id=80" class="vieNav__link" target="_blank">창호</a>
        </div>
    </nav>
    <div class="viewTopInfo">
        <div class="viewTopInfo__inner limit1280">
            <div class="viewTopInfo__left">
                <div class="bigPhoto"></div>
                <ul class="viewPhoto__iist">
                    <?php
                    $file=get_file("agency",$wr_id);
                    for ($i=0;$i<$file['count'];$i++){
                        $img_url=$file[$i]['path']."/".$file[$i]['file'];
                        if(empty($img_url)) continue;
                        ?>
                        <li class="viewPhoto__item" imgLink="<?=$img_url?>"></li>

                    <?php } ?>
                </ul>
            </div>
            <div class="viewTopInfo__right">
                <table class="viewTopInfo__right--table">
                    <tbody>
                        <tr>
                            <th>가맹점명</th>
                            <td><?=$row['wr_subject']?></td>
                        </tr>
                        <tr>
                            <th>지역</th>
                            <td><?=$row['wr_6']?> <?=$row['wr_7']?></td>
                        </tr>
                        <tr>
                            <th>주소</th>
                            <td><?=$row['wr_4']?></td>
                        </tr>
                        <tr>
                            <th>영업시간</th>
                            <td><?=$row['wr_1']?></td>
                        </tr>
                        <tr>
                            <th>연락처</th>
                            <td><?=$row['wr_5']?></td>
                        </tr>
                        <tr>
                            <th>취급품목</th>
                            <td><?=$row['wr_2']?></td>
                        </tr>
                        <tr>
                            <th>전시장 유/무</th>
                            <td><?=$row['wr_3']?></td>
                        </tr>
                        <tr>
                            <th>주차장 유/무</th>
                            <td><?=$row['wr_10']?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script type="text/javascript"
            src="//dapi.kakao.com/v2/maps/sdk.js?appkey=3863294a080737bb6d374a24a5d2ff43&libraries=services"></script>

    <div class="mapPart">
        <div id="map">
            
        </div>
    </div>
    <div class="viewBottomPart" style="background:url('/images/sub/viewBottomPart_bg.jpg')no-repeat">
        <img src="/images/sub/view_logo.png" alt="" />
        <p class="copy">COPYRIGHT © 2022 Copyright JAEHYUN INTEX. All Rights Reserved</p>
        <a href="/" target="_blank" class='viewBottom__btn'>홈페이지 바로가기</a>
    </div>

</section>
<? include_once(G5_THEME_PATH.'/view_tail.php'); ?>
<script>
    var mapContainer = document.getElementById('map'), // 지도를 표시할 div
        mapOption = {
            center: new daum.maps.LatLng(<?=$row[wr_8]?>, <?=$row[wr_9]?>), // 지도의 중심좌표
            level: 3 // 지도의 확대 레벨
        };

    // 지도를 생성합니다
    var map = new daum.maps.Map(mapContainer, mapOption);

    // 주소-좌표 변환 객체를 생성합니다
    var geocoder = new daum.maps.services.Geocoder();

    // 지도를 클릭한 위치에 표출할 마커입니다
    var marker = new daum.maps.Marker({
        map: map,
        // 지도 중심좌표에 마커를 생성합니다
        position: map.getCenter()
    });


    function getLByAddress(address) {
// 주소로 좌표를 검색합니다
        geocoder.addressSearch(address, function (result, status) {

            // 정상적으로 검색이 완료됐으면
            if (status === daum.maps.services.Status.OK) {

                var coords = new daum.maps.LatLng(result[0].y, result[0].x);

                // 결과값으로 받은 위치를 마커로 표시합니다
                //  var marker = new daum.maps.Marker({
                //     map: map,
                //    position: coords
                //});

                // 인포윈도우로 장소에 대한 설명을 표시합니다
                //var infowindow = new daum.maps.InfoWindow({
                //    content: '<div style="width:150px;text-align:center;padding:6px 0;">우리회사</div>'
                // });
                //infowindow.open(map, marker);
                marker.setPosition(coords);
                // 지도에 마커를 표시합니다
                marker.setMap(map);

                // 지도의 중심을 결과값으로 받은 위치로 이동시킵니다
                map.setCenter(coords);
                document.getElementById('wr_8').value = coords.getLat();
                document.getElementById('wr_9').value = coords.getLng();
            }
        });
    }

    // 지도에 클릭 이벤트를 등록합니다
    // 지도를 클릭하면 마지막 파라미터로 넘어온 함수를 호출합니다
    daum.maps.event.addListener(map, 'click', function (mouseEvent) {
        // 지도에 마커를 표시합니다
        marker.setMap(map);

        // 클릭한 위도, 경도 정보를 가져옵니다
        var latlng = mouseEvent.latLng;

        // 마커 위치를 클릭한 위치로 옮깁니다
        marker.setPosition(latlng);

        document.getElementById('wr_8').value = latlng.getLat();
        document.getElementById('wr_9').value = latlng.getLng();

    });


</script>

<script>
$(window).load(function() {
    var imgUrl ;
    $(".viewPhoto__item").each(function() {
        imgUrl = $(this).attr("imgLink");
        if($(this).index() == 0) {
            $(".bigPhoto").css("background","url(" + imgUrl +")no-repeat");
        }
        $(this).css("background","url("+ imgUrl+")no-repeat");
    })
    $(".viewPhoto__item").click(function() {
        imgUrl = $(this).attr("imgLink");
        $(".bigPhoto").css("background","url(" + imgUrl +")no-repeat");
    })

    
})
</script>