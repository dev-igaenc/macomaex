<?php
//error_reporting( E_ALL );
//ini_set( "display_errors", 1 );
$sub_menu = '100000';
require_once './_common.php';

@require_once './safe_check.php';

if (function_exists('social_log_file_delete')) {
    //소셜로그인 디버그 파일 24시간 지난것은 삭제
    social_log_file_delete(86400);
}

$g5['title'] = '관리자메인';
require_once './admin.head.php';

$new_member_rows = 5;
$new_point_rows = 5;
$new_write_rows = 5;

$addtional_content_before = run_replace('adm_index_addtional_content_before', '', $is_admin, $auth, $member);
if ($addtional_content_before) {
    echo $addtional_content_before;
}

if ($is_admin == 'super') {
    $sql_common = " from {$g5['member_table']} ";

    $sql_search = " where (1) ";

    if ($is_admin != 'super') {
        $sql_search .= " and mb_level <= '{$member['mb_level']}' ";
    }

    if (!$sst) {
        $sst = "mb_datetime";
        $sod = "desc";
    }

    $sql_order = " order by {$sst} {$sod} ";

    $sql = " SELECT count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];

    // 탈퇴회원수
    $sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_leave_date <> '' {$sql_order} ";
    $row = sql_fetch($sql);
    $leave_count = $row['cnt'];

    // 차단회원수
    $sql = " SELECT count(*) as cnt {$sql_common} {$sql_search} and mb_intercept_date <> '' {$sql_order} ";
    $row = sql_fetch($sql);
    $intercept_count = $row['cnt'];

    $sql = " SELECT * {$sql_common} {$sql_search} {$sql_order} limit {$new_member_rows} ";
    $result = sql_query($sql);

    $colspan = 12;
    ?>

          <!-- page content -->
<?

//print_r($menu);
		foreach($menu as $key => $value) {
			//print_r($value);
			//echo count($value)."<br>";
			foreach($value as $key2 => $value2) {
				//$sql="Insert into g5_admin_menu SET me_gr='$key', me_code='$value2[0]', me_name='$value2[1]', me_link='$value2[2]', me_diff='$value2[3]', me_css='$value2[4]'";
				//$result = sql_query($sql);
				//echo $sql."<br>";
			}
		}
?>
<style>
.btm_info{
    background-color: #00b0bf;
    color: #fff;
    padding: 30px;
    margin-top: 30px;
    border-radius: 20px;
    position: relative;
}
.btm_info dl dt {
	font-size: 17px;
	margin-bottom:20px;
}
.btm_info dl dd {
   margin:0px;
}
.btm_info .tel dd a {
    color: #fff;
    font-size: 30px;
    line-height: 1;
}
.btm_info .time dd {
    font-size: 15px;
    line-height: 1.5;
	margin:0px;
}
.btm_info .counsel a {
    width: 200px;
    height: 100px;
    background-color: #0a828d;
    font-weight: bold;
    font-size: 16px;
    color: #fff !important;
    text-align: center;
    border-radius: 10px;
    display: flex;
    flex-flow: column wrap;
    justify-content: center;
    text-shadow: 3px 3px 3px rgba(255,255,255,0.3);
    box-shadow: 3px 3px 0 rgba(255,255,255,0.3);
    transition: all .3s ease-out;
}
</style>


          <div class="row">

<?
//일주일 접속통계구하기
$day_7ago = date("Y-m-d", strtotime("-6 Day"));
$day_1mago = date("Y-m-d", strtotime("-30 Day"));
$now_date = date("Y-m-d");

$sql="select * from g5_visit_sum where vs_date >=  '".$day_7ago."' and vs_date <= '".$now_date."' order by vs_date desc";
$result = sql_query($sql);
$chart_date = array();
$chart_value = array();
for ($i=0; $row=sql_fetch_array($result); $i++) {
		$chart_date[]=$row['vs_date'];
		$chart_value[]=$row['vs_count'];
}
$c_date="'".str_replace(",","','",implode(",",$chart_date))."'";
$c_value=implode(",",$chart_value);

?>
            <div class="col-md-6 col-sm-6 ">
              <div class="x_panel tile fixed_height_500">
                <div class="x_title">
                  <h2>최근 일주일 방문 건수</h2>
                  <ul class="nav navbar-right panel_toolbox">
                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </li>
                    <li><a class="close-link"><i class="fa fa-close"></i></a>
                    </li>
                  </ul>
                  <div class="clearfix"></div>
                </div>
				<div class="x_content">
                  <h4>( <?=$day_7ago?> ~ <?=$now_date?>)</h4>
					<canvas id="7Chart" style="height:374px; width:100%;"></canvas>
				</div>
              </div>
            </div>

<script>
$(function(){
	var ctx = document.getElementById("7Chart");
	var mybarChart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: [<?=$c_date?>],
			datasets: [{
				label: '방문수',
				backgroundColor: "#26B99A",
				data: [<?=$c_value?>]
			}]
		},
		options: {
			responsive: false,
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero: true
					}
				}]
			}
		}
	});
});
</script>

<?
//일주일 접속통계구하기
$sql="select sum(vs_count)  as total, avg(vs_count) as avg from g5_visit_sum where vs_date >= '".$now_date."'";
$today = sql_fetch($sql);

$sql="select sum(vs_count)  as total, avg(vs_count) as avg from g5_visit_sum where vs_date >=  '".$day_7ago."' and vs_date <= '".$now_date."'";
$week = sql_fetch($sql);

$sql="select sum(vs_count)  as total, avg(vs_count) as avg from g5_visit_sum where vs_date >=  '".$day_1mago."' and vs_date <= '".$now_date."'";
$mon_ago = sql_fetch($sql);

$sql="select sum(vs_count)  as total, avg(vs_count) as avg from g5_visit_sum";
$total = sql_fetch($sql);



//전체 게시물수 뽑기
 $sql = "SELECT * FROM {$g5['board_table']}";
 $result1 = sql_query($sql);
 $all_com = 0;
 $all_art= 0;
 $week_art=0;
	 $t_cnt =  mysqli_num_rows($result1);
     for ($i=0; $row2=sql_fetch_array($result1); $i++) {
		//전체 게시물
		$gul_su="select count(wr_id) as ctt, avg(wr_id) as avg from g5_write_{$row2['bo_table']} where wr_is_comment='0' ";
		$article = sql_fetch($gul_su);
		$article_su=$article[ctt];
		$all_art= $all_art +$article_su;
		$allavg_art= $all_art / $t_cnt;

		//오늘
		$gul_su="select count(wr_id) as ctt, avg(wr_id) as avg from g5_write_{$row2['bo_table']} where wr_is_comment='0' and DATE(wr_datetime) = '".$now_date."'";
		$article2 = sql_fetch($gul_su);
		$article_su=$article2[ctt];
		$today_art= $today_art +$article_su;

		//최근 7일
		$gul_su="select count(wr_id) as ctt, avg(wr_id) as avg from g5_write_{$row2['bo_table']} where wr_is_comment='0' and DATE(wr_datetime) >=  '".$day_7ago."' and DATE(wr_datetime) <= '".$now_date."'";
		$article3 = sql_fetch($gul_su);
		$article_su =$article3[ctt];
		$week_art= $week_art +$article_su;
		$weekavg_art= $week_art/7;

		//최근 30일
		$gul_su="select count(wr_id) as ctt, avg(wr_id) as avg from g5_write_{$row2['bo_table']} where wr_is_comment='0' and DATE(wr_datetime) >=  '".$day_1mago."' and DATE(wr_datetime) <= '".$now_date."'";
		$article4 = sql_fetch($gul_su);
		$article_su =$article4[ctt];
		$month_art= $month_art +$article_su;
		$monthavg_art= $month_art/30;
 	}



?>
  <div class="col-md-6 col-sm-6 ">
                <div class="x_panel fixed_height_500" >
                  <div class="x_title">
                    <h2>기간별 현황</h2>
                    <ul class="nav navbar-right panel_toolbox">
                      <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                      </li>
                      <li><a class="close-link"><i class="fa fa-close"></i></a>
                      </li>
                    </ul>
                    <div class="clearfix"></div>
                  </div>

                  <div class="x_content">
					<h4></h4>
                    <div class="table-responsive">
                      <table class="table table-striped jambo_table bulk_action">
                        <thead>
                          <tr class="headings">
                            <th class="column-title">날짜 </th>
                            <th class="column-title">방문수 </th>
                            <th class="column-title">게시물 등록수 </th>
                            </th>
                          </tr>
                        </thead>

                        <tbody>
                          <tr class="even pointer">
                            <td class=" "><?=str_replace("-",".",substr($now_date,5))?> (오늘)</td>
                            <td class=" "><?=number_format($today['total'])?> 건</td>
                            <td class=" "><?=number_format($today_art)?> 건</td>
                            </td>
                          </tr>
                          <tr class="odd pointer">
                            <td class=" ">최근 7일 합계</td>
                            <td class=" "><?=number_format($week['total'])?> 건</td>
                            <td class=" "><?=number_format($week_art)?> 건</td>
                            </td>
                          </tr>
                          <tr class="even pointer">
                            <td class=" ">최근 7일 평균</td>
                            <td class=" "><?=number_format($week['avg'])?> 건</td>
                            <td class=" "><?=number_format($weekavg_art)?>  건</td>
                            </td>
                          </tr>
                          <tr class="odd pointer">
                            <td class=" ">최근 30일 합계</td>
                            <td class=" "><?=number_format($mon_ago['total'])?> 건</td>
                            <td class=" "><?=number_format($month_art)?> 건</td>
                            </td>
                          </tr>
                          <tr class="even pointer">
                            <td class=" ">최근30일 평균</td>
                            <td class=" "><?=number_format($mon_ago['avg'])?> 건</td>
                            <td class=" "><?=number_format($monthavg_art)?> 건</td>
                            </td>
                          </tr>
                          <tr class="odd pointer">
                            <td class=" ">전체</td>
                            <td class=" "><?=number_format($total['total'])?> 건</td>
                            <td class=" "><?=number_format($all_art)?> 건</td>
                            </td>
                          </tr>
                          <tr class="even pointer">
                            <td class=" ">전체 평균</td>
                            <td class=" "><?=number_format($total['avg'])?> 건</td>
                            <td class=" "><?=number_format($allavg_art)?> 건</td>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
							
						
                  </div>
                </div>
              </div>
          </div>
<?
	if (!auth_check_menu($auth, '300100', 'r', true)) {

    $sql_common = " from {$g5['board_new_table']} a, {$g5['board_table']} b, {$g5['group_table']} c where a.bo_table = b.bo_table and b.gr_id = c.gr_id ";

    if ($gr_id) {
        $sql_common .= " and b.gr_id = '{$gr_id}' ";
    }
    if (isset($view) && $view) {
        if ($view == 'w') {
            $sql_common .= " and a.wr_id = a.wr_parent ";
        } elseif ($view == 'c') {
            $sql_common .= " and a.wr_id <> a.wr_parent ";
        }
    }
    $sql_order = " order by a.bn_id desc ";

    $sql = " SELECT count(*) as cnt {$sql_common} ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];

    $colspan = 5;

 ?>


          <div class="row">
            <div class="col-md-12 col-sm-12 ">
	  
			  <div class="x_panel tile">
                <div class="x_title">
					<div class="col-md-6">
						<h2>최근게시물</h2>
					</div>
                  <div class="col-md-6">
					 <a href="./bbs/board.php" class="pull-right buttonPrevious btn btn-primary">게시물 관리</a>
                  </div>
					<div class="tbl_head01 tbl_wrap">
						<table>
							<caption>최근게시물</caption>
							<thead>
								<tr>
									<th scope="col">그룹</th>
									<th scope="col">게시판</th>
									<th scope="col">제목</th>
									<th scope="col">이름</th>
									<th scope="col">일시</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$sql = " SELECT a.*, b.bo_subject, c.gr_subject, c.gr_id {$sql_common} {$sql_order} limit {$new_write_rows} ";
								$result = sql_query($sql);
								for ($i = 0; $row = sql_fetch_array($result); $i++) {
									$tmp_write_table = $g5['write_prefix'] . $row['bo_table'];

									if ($row['wr_id'] == $row['wr_parent']) {
										// 원글
										$comment = "";
										$comment_link = "";
										$row2 = sql_fetch(" SELECT * from {$tmp_write_table} where wr_id = '{$row['wr_id']}' ");

										$name = get_sideview($row2['mb_id'], get_text(cut_str($row2['wr_name'], $config['cf_cut_name'])), $row2['wr_email'], $row2['wr_homepage']);
										// 당일인 경우 시간으로 표시함
										$datetime = substr($row2['wr_datetime'], 0, 10);
										$datetime2 = $row2['wr_datetime'];
										if ($datetime == G5_TIME_YMD) {
											$datetime2 = substr($datetime2, 11, 5);
										} else {
											$datetime2 = substr($datetime2, 5, 5);
										}
									} else {
										// 코멘트
										$comment = '댓글. ';
										$comment_link = '#c_' . $row['wr_id'];
										$row2 = sql_fetch(" SELECT * from {$tmp_write_table} where wr_id = '{$row['wr_parent']}' ");
										$row3 = sql_fetch(" SELECT mb_id, wr_name, wr_email, wr_homepage, wr_datetime from {$tmp_write_table} where wr_id = '{$row['wr_id']}' ");

										$name = get_sideview($row3['mb_id'], get_text(cut_str($row3['wr_name'], $config['cf_cut_name'])), $row3['wr_email'], $row3['wr_homepage']);
										// 당일인 경우 시간으로 표시함
										$datetime = substr($row3['wr_datetime'], 0, 10);
										$datetime2 = $row3['wr_datetime'];
										if ($datetime == G5_TIME_YMD) {
											$datetime2 = substr($datetime2, 11, 5);
										} else {
											$datetime2 = substr($datetime2, 5, 5);
										}
									}
									?>

									<tr>
										<td class="td_category"><?php echo cut_str($row['gr_subject'], 10) ?></td>
										<td class="td_category"><a href="./bbs/<?php echo get_pretty_url($row['bo_table']) ?>"><?php echo cut_str($row['bo_subject'], 20) ?></a></td>
										<td><a href="./bbs/<?php echo get_pretty_url($row['bo_table'], $row2['wr_id']); ?><?php echo $comment_link ?>"><?php echo $comment ?><?php echo conv_subject($row2['wr_subject'], 100) ?></a></td>
										<td class="td_mbname">
											<div><?php echo $name ?></div>
										</td>
										<td class="td_datetime"><?php echo $datetime ?></td>
									</tr>

									<?php
								}
								if ($i == 0) {
									echo '<tr><td colspan="' . $colspan . '" class="empty_table">자료가 없습니다.</td></tr>';
								}
								?>
							</tbody>
						</table>
					 </div>
                </div>
              </div>
            </div>
          </div>
	<?}?>

          <div class="row">
            <div class="col-md-12 col-sm-12 ">
              <div class="btm_info">

                <div class="row x_title">
                  <div class="col-md-12">
                    <h2>PRIX 고객센터 안내 <small>CUSTOMER CENTER</small></h2>
                  </div>
                </div>
				
				<div class="col-md-4 col-sm-4">
						<dl class="tel">
							<dt>문의전화</dt>
							<dd><a href="tel:02-3478-0711"><strong>02-3478-0711</strong></a></dd>
						</dl>
				</div>
				<div class="col-md-4 col-sm-4">
						<dl class="time">
							<dt>상담가능시간</dt>
							<dd>평일 10:00 ~ 18:00 (점심 12:00 ~ 13:00)</dd>
							<dd>주말 및 공휴일 휴무</dd>
						</dl>
				</div>
				<div class="col-md-4 col-sm-4">
						<div class="counsel pull-right">
							<a href="mailto:info@prix.co.kr">유지보수 신청</a>
						</div>
                </div>

                <div class="clearfix"></div>
              </div>
            </div>

          </div>
		</div>
        <!-- /page content -->

    <?php
} //endif

$addtional_content_after = run_replace('adm_index_addtional_content_after', '', $is_admin, $auth, $member);
if ($addtional_content_after) {
    echo $addtional_content_after;
}
require_once './admin.tail.php';