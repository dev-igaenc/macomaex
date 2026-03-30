<?
include_once("./_common.php");

$sql_common="";
if($wr_6) $sql_common.=" and wr_6 like ('%{$wr_6}%')";
if($wr_7) $sql_common.=" and wr_7 like ('%{$wr_7}%')";
if($stx) $sql_common.=" and wr_subject like ('%{$stx}%')";

$total_cnt=sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['write_prefix']}agency where 1 {$sql_common} ");

$store_sql="SELECT * FROM {$g5['write_prefix']}agency where 1 {$sql_common} ORDER BY wr_6,wr_7 ASC ";
$store_res=sql_query($store_sql);

for ($i=0;$row=sql_fetch_array($store_res);$i++){
    $stores[]=$row;
}
include_once(G5_THEME_PATH.'/head.php');
?>

    <script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=3863294a080737bb6d374a24a5d2ff43&libraries=services"></script>

<? include $_SERVER["DOCUMENT_ROOT"]."/_inc/sub_top.php"; ?>
<section class="subpage sub0301">
    <div class="limit1200">
        <div class="subp_titleWrap">
            <h4 class="subp_title">대리점안내</h4>
        </div>
        <div class="search_wrap">
            <form class="search__form clear">
                <div class="search__left">
                    <div class="clear">
                        <em class="search__name">지역</em>
                        <select name="wr_6" id="wr_6" class="search__select">
                        </select>
                        <select name="wr_7" id="wr_7" class="search__select">
                        </select>
                    </div>
                    <div class="clear">
                        <em class="search__name">검색어</em>
                        <input type="text" name="stx" value="<?=$stx?>" placeholder="검색어를 입력 하세요" class="search__input--text" />
                    </div>
                </div>
                <button type="submit" class="search__btn--submit">
                    대리점 찾기
                </button>
            </form>
        </div>
        <div class="top_part map">
            <form action="">
                <div class="clear">
                    <div class="right">
                        <div id="map"></div>
                    </div>
                    <div class="left">
                        <div class="list_wrap">
                            <ul class="clear">
                                <?php
                                foreach ($stores as $value){
                                    $thumb = get_list_thumbnail("agency", $value['wr_id'], 70, 70);
                                    if(empty($thumb['src'])){
                                        $thumb['src']="/images/common/footer_logo.png";
                                    }
                                    ?>
                                    <li class="change_location" data-img="<?=$thumb['src']?>" data-latitude="<?=$value['wr_8']?>" data-longitude="<?=$value['wr_9']?>" data-subject="<?=$value['wr_subject']?>" data-location="<?=$value['wr_4']?>" data-tel="<?=$value['wr_5']?>">
                                        <em class="map__title"><?=$value['wr_subject']?> </em>
                                        <p class="map__adress"><?=$value['wr_4']?></p>
                                        <p  class="map__phoneNum">전화 : <?=$value['wr_5']?></p>
                                        <a href="javascript:void(0)" targetLink="/store/view.php?wr_id=<?=$value['wr_id']?>" class="btn_move">자세히보기</a>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
            <?php if($is_admin){?>
                <div class="util_wrap">
                    <ul class="util_list">
                        <li><a href="/bbs/board.php?bo_table=agency"><span>관리자</span></a></li>
                        <li><a href="./storeexceldownload.php" onclick="return excelform(this.href);" ><span>대리점 데이터 다운</span></a></li>
                        <li><a href="./excel_upload.php" onclick="return excelform(this.href);" ><span>Excel등록</span></a></li>
                        <li><a href="./update_latlng.php" onclick="return excelform(this.href);" ><span>좌표등록</span></a></li>
                    </ul>
                </div>
            <?php }?>
        </div>
    </div>

</section>

<script>
	$(window).load(function() {
		var leftWidth;
		$(window).resize(function() {
			if (window.matchMedia("(min-width: 1025px)").matches) {
				leftWidth = $(".sub0401 .top_part.map .left").innerHeight();
				$(".sub0401 .top_part.map .right").css("height", leftWidth);
			} else {
				$(".sub0401 .top_part.map .right").css("height", "");
			}

		}).trigger("resize");

        var mapContainer = document.getElementById('map'), // 지도를 표시할 div
            mapOption = {
                center: new kakao.maps.LatLng(<?=empty($stores[0]['wr_8'])?37.566400714093284:$stores[0]['wr_8']?>, <?=empty($stores[0]['wr_9'])?126.9785391897507:$stores[0]['wr_9']?>), // 지도의 중심좌표
                level: 3 // 지도의 확대 레벨
            };

        var map = new kakao.maps.Map(mapContainer, mapOption); // 지도를 생성합니다
        <?php
        if($total_cnt['cnt'] > 0){ ?>
        var positions=[];
            <?php
            foreach ($stores as $store){
                if(empty($store['wr_8']) || empty($store['wr_9'])) continue;
                ?>
                positions.push({
                    title : '<?=$store['wr_subject']?>',
                    latlng: new kakao.maps.LatLng(<?=$store['wr_8']?>, <?=$store['wr_9']?>)
                });
                <?php
            }
            ?>
        var imageSrc = "https://t1.daumcdn.net/localimg/localimages/07/mapapidoc/markerStar.png";
        for (var i = 0; i < positions.length; i ++) {
            // 마커 이미지의 이미지 크기 입니다
            var imageSize = new kakao.maps.Size(24, 35);
            // 마커 이미지를 생성합니다
            var markerImage = new kakao.maps.MarkerImage(imageSrc, imageSize);
            // 마커를 생성합니다
            var marker = new kakao.maps.Marker({
                map: map, // 마커를 표시할 지도
                position: positions[i].latlng, // 마커를 표시할 위치
                title : positions[i].title, // 마커의 타이틀, 마커에 마우스를 올리면 타이틀이 표시됩니다
                image : markerImage // 마커 이미지
            });
        }
        <?php }else{ ?>
        var markerPosition = new kakao.maps.LatLng(33.450701, 126.570667);
// 마커를 생성합니다
        var marker = new kakao.maps.Marker({
            position: markerPosition
        });
        marker.setMap(map);
    <?php } ?>


        $(".change_location").click(function () {

            var position = new kakao.maps.LatLng($(this).data("latitude"), $(this).data("longitude"));
            var image=$(this).data("img");
            var content = '<div class="mapwrap">' +
                '    <div class="mapinfo">' +
                '        <div class="maptitle">' +
                $(this).data("subject") +
                '            <div class="mapclose" onclick="closeOverlay()" title="닫기"></div>' +
                '        </div>' +
                '        <div class="mapbody">' +
                '            <div class="mapimg">' +
                '<img src="'+ $(this).data("img")+'">' +
                '           </div>' +
                '            <div class="mapdesc">' +
                '                <div class="mapellipsis" style=margin-top:10px>'+$(this).data("location")+'</div>' +
                '                <div class="mapjibun mapellipsis"><i class="fa fa-phone-square" aria-hidden="true"></i>'+$(this).data('tel')+'</div>' +
                '            </div>' +
                '        </div>' +
                '    </div>' +
                '</div>';
            var customOverlay = new kakao.maps.CustomOverlay({
                position: position,
                content: content,
                xAnchor: 0.3,
                yAnchor: 0.91
            });
            customOverlay.setMap(map);
            map.panTo(position);
        })

	})

    function excelform(url)
    {
        var opt = "width=600,height=450,left=10,top=10";
        window.open(url, "win_excel", opt);
        return false;
    }
</script>
<script>
    $('document').ready(function() {
        var area0 = ["시/도 선택","서울특별시","세종특별자치시","인천광역시","대전광역시","광주광역시","대구광역시","울산광역시","부산광역시","경기도","강원도","충청북도","충청남도","전라북도","전라남도","경상북도","경상남도","제주도"];
        var area1 = ["강남구","강동구","강북구","강서구","관악구","광진구","구로구","금천구","노원구","도봉구","동대문구","동작구","마포구","서대문구","서초구","성동구","성북구","송파구","양천구","영등포구","용산구","은평구","종로구","중구","중랑구"];
        var area2 = ["반곡동","소담동","보람동","대평동","가람동","한솔동","나성동","새롬동","다정동","어진동","종촌동","고운동","아름동","도담동","산울동","해밀동","합강동","집현동","조치원읍","연기면","연동면","부강면","금남면","장군면","연서면","전의면","전동면","소정면"]
        var area3 = ["계양구","남구","남동구","동구","부평구","서구","연수구","중구","강화군","옹진군"];
        var area4 = ["대덕구","동구","서구","유성구","중구"];
        var area5 = ["광산구","남구","동구","북구","서구"];
        var area6 = ["남구","달서구","동구","북구","서구","수성구","중구","달성군"];
        var area7 = ["남구","동구","북구","중구","울주군"];
        var area8 = ["강서구","금정구","남구","동구","동래구","부산진구","북구","사상구","사하구","서구","수영구","연제구","영도구","중구","해운대구","기장군"];
        var area9 = ["고양시","과천시","광명시","광주시","구리시","군포시","김포시","남양주시","동두천시","부천시","성남시","수원시","시흥시","안산시","안성시","안양시","양주시","오산시","용인시","의왕시","의정부시","이천시","파주시","평택시","포천시","하남시","화성시","가평군","양평군","여주군","연천군"];
        var area10 = ["강릉시","동해시","삼척시","속초시","원주시","춘천시","태백시","고성군","양구군","양양군","영월군","인제군","정선군","철원군","평창군","홍천군","화천군","횡성군"];
        var area11 = ["제천시","청주시","충주시","괴산군","단양군","보은군","영동군","옥천군","음성군","증평군","진천군","청원군"];
        var area12 = ["계룡시","공주시","논산시","보령시","서산시","아산시","천안시","금산군","당진군","부여군","서천군","연기군","예산군","청양군","태안군","홍성군"];
        var area13 = ["군산시","김제시","남원시","익산시","전주시","정읍시","고창군","무주군","부안군","순창군","완주군","임실군","장수군","진안군"];
        var area14 = ["광양시","나주시","목포시","순천시","여수시","강진군","고흥군","곡성군","구례군","담양군","무안군","보성군","신안군","영광군","영암군","완도군","장성군","장흥군","진도군","함평군","해남군","화순군"];
        var area15 = ["경산시","경주시","구미시","김천시","문경시","상주시","안동시","영주시","영천시","포항시","고령군","군위군","봉화군","성주군","영덕군","영양군","예천군","울릉군","울진군","의성군","청도군","청송군","칠곡군"];
        var area16 = ["거제시","김해시","마산시","밀양시","사천시","양산시","진주시","진해시","창원시","통영시","거창군","고성군","남해군","산청군","의령군","창녕군","하동군","함안군","함양군","합천군"];
        var area17 = ["서귀포시","제주시","남제주군","북제주군"];


        // 시/도 선택 박스 초기화

        $("select[name^='wr_6']").each(function() {
            $selsido = $(this);
            $.each(eval(area0), function() {
                let value="";
                if(this=="시/도 선택"){
                    value="";
                }else{
                    value=this;
                }
                $selsido.append("<option value='"+value+"'>"+this+"</option>");
            });
            $selsido.next().append("<option value=''>구/군 선택</option>");
        });

        <?php if($wr_6){?>
        $("select[name='wr_6'] option[value='<?=$wr_6?>']").attr("selected",true);

        var area = "area"+$("option",$(this)).index($("option:selected",$(this))); // 선택지역의 구군 Array
        var $gugun = $("select[name^='wr_7']"); // 선택영역 군구 객체
        $("option",$gugun).remove(); // 구군 초기화

            $gugun.append("<option value=''>전체</option>");
            $.each(eval(area), function() {
                $gugun.append("<option value='"+this+"'>"+this+"</option>");
            });
        $("select[name='wr_7'] option[value='<?=$wr_7?>']").attr("selected",true);
        <?php } ?>

        $("select[name^='wr_6']").change(function() {
            var area = "area"+$("option",$(this)).index($("option:selected",$(this))); // 선택지역의 구군 Array
            var $gugun = $("select[name^='wr_7']"); // 선택영역 군구 객체
            $("option",$gugun).remove(); // 구군 초기화
                $gugun.append("<option value=''>전체</option>");
                $.each(eval(area), function() {
                    $gugun.append("<option value='"+this+"'>"+this+"</option>");
                });
        });

        var targetLink;
        $(".top_part.map .left .list_wrap li .btn_move").click(function() {
            targetLink = $(this).attr("targetlink");
            window.open(targetLink,"pop","width=" + screen.width + ",height=" + screen.height + "fullscreen=yes");
        })
    });
</script>


<?
include_once(G5_THEME_PATH.'/tail.php');
?>