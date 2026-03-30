<?php
include_once('./_common.php');

if (!$is_admin) alert("잘못된 접근방식입니다.");

$g5['title'] = '좌표 일괄 등록';
include_once(G5_PATH . '/head.sub.php');
$sql = "select * from {$g5['write_prefix']}{$bo_table} where wr_4 !='' and (wr_8='' or wr_9='')";
$res = sql_query($sql);
?>
<div style="padding: 20px 50px;">
    <table class="table table-bordered border-primary">
        <thead>
        <tr>
            <th scope="col">주소</th>
            <th scope="col">결과</th>
        </tr>
        </thead>
        <tbody>

        <?php
        for ($i = 0; $row = sql_fetch_array($res); $i++) {
            ?>
            <tr>
                <td scope="row">
                    <div class="update_latlng" data-wr_id="<?= $row['wr_id'] ?>" data-wr_4="<?= $row['wr_4'] ?>">
                        <?= $row['wr_subject']; ?>
                    </div>
                </td>
                <td>O</td>
            </tr>

            <?php
        }
        ?>
        </tbody>
    </table>
</div>
    <div id="map"></div>

    <div class="btn_win01 btn_win" style="padding: 0 50px;text-align: right">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>
<style>
    #hd_login_msg{display: none;}
</style>
    <script type="text/javascript"
            src="//dapi.kakao.com/v2/maps/sdk.js?appkey=<?=$config['cf_10']?>&libraries=services"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
            crossorigin="anonymous"></script>

    <script>
        var mapContainer = document.getElementById('map'), // 지도를 표시할 div
            mapOption = {
                center: new daum.maps.LatLng(37.566400714093284, 126.9785391897507), // 지도의 중심좌표
                level: 3 // 지도의 확대 레벨
            };

        // 지도를 생성합니다
        var map = new daum.maps.Map(mapContainer, mapOption);

        var geocoder = new daum.maps.services.Geocoder();
        // 지도를 클릭한 위치에 표출할 마커입니다
        var marker = new daum.maps.Marker({
            map: map,
            // 지도 중심좌표에 마커를 생성합니다
            position: map.getCenter()
        });

        function getLByAddress(address, wr_id) {
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
                    console.log(coords.getLat());
                    console.log(coords.getLng());
                    $.ajax({
                        url: g5_url + "/store/ajax.update_latlng.php",
                        method: "POST",
                        data: {wr_id: wr_id, wr_8: coords.getLat(), wr_9: coords.getLng(),bo_table : '<?=$bo_table?>'},
                        dataType: 'json'
                    });
                }
            });
        }

        $(document).ready(function () {
            $(".update_latlng").each(function () {
                getLByAddress($(this).data("wr_4"), $(this).data("wr_id"));
            })
        });
    </script>
<?php echo html_end(); // HTML 마지막 처리 함수 : 반드시 넣어주시기 바랍니다.