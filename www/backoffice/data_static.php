<?php
$sub_menu = '500100';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

$g5['title'] = '등록 데이터 통계 (한국평가데이터)';
include_once (G5_ADMIN_PATH.'/admin.head.php');


?>

<div class="local_ov01 local_ov">
    <?php if ($page > 1) {?><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">처음으로</a><?php } ?>
    <span class="btn_ov01"><span class="ov_txt"></span></span>
</div>

<h2><?=$g5['title']?></h2>
<div class="tbl_head01 tbl_wrap">
<?
$row = sql_fetch(" select count(*) as cnt from company"); //기업개요
$total_count = $row['cnt'];

$row = sql_fetch(" select count(*) as cnt from company_financial"); //주요재무정보
$total_count2 = $row['cnt'];

$row = sql_fetch(" select count(*) as cnt from company_client"); //주요거래처
$total_count3 = $row['cnt'];

$row = sql_fetch(" select count(*) as cnt from company_stock"); //주식관련사항
$total_count4 = $row['cnt'];
?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<div id="chart_month" style="height:500px;"></div>
</div>
<script>
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);
  
  function drawChart() {
    var data2 = new google.visualization.DataTable();
    data2.addColumn('string', '항목');
    data2.addColumn('number', '수량');

    data2.addRows([
      ['기업개요', <?=$total_count?>],
      ['주요재무정보', <?=$total_count2?>],
	  ['주요거래처', <?=$total_count3?>],
      ['주식관련사항', <?=$total_count4?>]
    ]);

    var view = new google.visualization.DataView(data2);
    view.setColumns([0, 1,
      {
        calc: "stringify",
        sourceColumn: 1,
        type: "string",
        role: "annotation"
      }
    ]);

    var options2 = {
      title: '자료통계',
      hAxis: {
        viewWindow: {
          min: [7, 30, 0],
          max: [17, 30, 0]
        }
      },
      vAxis: {
        title: '등록데이터 (건)'
      },
      legend : { position: "none" }
    };

    var chart2 = new google.visualization.ColumnChart(document.getElementById('chart_month'));
    chart2.draw(view, options2);
  }
</script>
<div class="text-center" >
<br>
<a href="javascript:alert('적용 예정')" class="btn btn-primary">최근 DB다운로드</a> <a href="javascript:alert('적용 예정')" class="btn btn-primary">기업 DB업로드</a>
</div>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');