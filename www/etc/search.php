<?
include_once("./_common.php");
$board['bo_10'] = "99";
$board['bo_9'] = "2";
$board['bo_5'] = "전체검색";
$board['bo_subject'] = "전체검색 결과";

$contact_sql="select * from {$g5['write_prefix']}contact where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$contact_res=sql_query($contact_sql);
$contact_sql="select count(*) as cnt from {$g5['write_prefix']}contact where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$contact_cnt=sql_fetch($contact_sql);

$disclosure_sql="select * from {$g5['write_prefix']}disclosure where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$disclosure_res=sql_query($disclosure_sql);
$disclosure_sql="select count(*) as cnt from {$g5['write_prefix']}disclosure where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$disclosure_cnt=sql_fetch($disclosure_sql);

$news_sql="select * from {$g5['write_prefix']}news where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$news_res=sql_query($news_sql);
$news_sql="select count(*) as cnt from {$g5['write_prefix']}news where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$news_cnt=sql_fetch($news_sql);

$recruits_sql="select * from {$g5['write_prefix']}recruits where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$recruits_res=sql_query($recruits_sql);
$recruits_sql="select count(*) as cnt from {$g5['write_prefix']}recruits where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$recruits_cnt=sql_fetch($recruits_sql);

$relative_sql="select * from {$g5['write_prefix']}relative where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$relative_res=sql_query($relative_sql);
$relative_sql="select count(*) as cnt from {$g5['write_prefix']}relative where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$relative_cnt=sql_fetch($relative_sql);

$contract_sql="select * from {$g5['write_prefix']}contract where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$contract_res=sql_query($contract_sql);
$contract_sql="select count(*) as cnt from {$g5['write_prefix']}contract where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$contract_cnt=sql_fetch($contract_sql);

$notice_sql="select * from {$g5['write_prefix']}notice where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$notice_res=sql_query($notice_sql);
$notice_sql="select count(*) as cnt from {$g5['write_prefix']}notice where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$notice_cnt=sql_fetch($notice_sql);


$bid_sql="select * from {$g5['write_prefix']}bid where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$bid_res=sql_query($bid_sql);
$bid_sql="select count(*) as cnt from {$g5['write_prefix']}bid where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$bid_cnt=sql_fetch($bid_sql);

$allim_sql="select * from {$g5['write_prefix']}allim where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$allim_res=sql_query($allim_sql);
$allim_sql="select count(*) as cnt from {$g5['write_prefix']}allim where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$allim_cnt=sql_fetch($allim_sql);

$ethics_sql="select * from {$g5['write_prefix']}ethics where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$ethics_res=sql_query($ethics_sql);
$ethics_sql="select count(*) as cnt from {$g5['write_prefix']}ethics where 1 and (wr_subject like ('%{$s}%') or wr_content like ('%{$s}%') ) and wr_id = wr_parent and wr_reply = ''  order by wr_datetime desc limit 3";
$ethics_cnt=sql_fetch($ethics_sql);

$total_count=$notice_cnt['cnt']+$allim_cnt['cnt']+$bid_cnt['cnt']+$contact_cnt['cnt']+$contract_cnt['cnt']+$disclosure_cnt['cnt']+$ethics_cnt['cnt']+$news_cnt['cnt']+$recruits_cnt['cnt']+$relative_cnt['cnt'];
include_once(G5_THEME_PATH.'/head.php');
?>
<style>
	.board_btn{display:block; text-align: center;}
	.board_btn>.total{padding:0 0 3rem; font-weight: 500; font-size:2.4rem; text-align:center;}
	.board_btn>.total>strong{font-family: '0425', '0425_i', sans-serif !important; font-weight: bold; font-size:3rem;}
	.board_btn+h5.bul{margin-top:0;}
</style>
<div class="board_btn">
	<div class="total"><strong>"<?=$s?>"</strong>, 검색결과 <strong><?=$total_count?></strong>건</div>
</div>

<h5 class="bul">공지사항
<a href="/bbs/board.php?bo_table=notice" class="more">+ 더보기</a>
<!-- <?php if($notice_cnt['cnt']>3){ ?>
<a href="/bbs/board.php?bo_table=notice&sca=&sop=and&sfl=wr_subject%7C%7Cwr_content&stx=<?=$s?>" class="more">+ 더보기</a>
<?php } ?> -->
</h5>
<div class="board_list">
	<table>
		<colgroup>
			<col width="7%">
			<col width="*">
			<col width="15%">
			<col width="10%">
		</colgroup>
		<thead>
		<tr>
			<th scope="col">No</th>
			<th scope="col">제목</th>
			<th scope="col">등록일</th>
			<th scope="col">조회수</th>
		</tr>
		</thead>
		<tbody>
			<?php
            for ($i=3;$row=sql_fetch_array($notice_res);$i--){
                ?>
                <tr class=" ">
                    <td class="td_num2"><?=$i?></td>
                    <td class="td_subject"><!-- style="padding-left:0px" -->
                        <div class="bo_tit"><a href="/bbs/view.php?bo_table=notice&amp;wr_id=<?=$row['wr_id']?>"><?=$row['wr_subject']?></a></div>
                    </td>
                    <td class="btm"><?php echo date("Y.m.d", strtotime($row['wr_datetime'])) ?></td>
                    <td class="btm"><?=$row['wr_hit'];?></td>
                </tr>
                    <?php
            }
            ?>
		</tbody>
	</table>
</div>

<!--
<h5 class="bul">연락처 안내
<a href="/bbs/board.php?bo_table=contact" class="more">+ 더보기</a>
<?php if($notice_cnt['cnt']>3){ ?>
<a href="/bbs/board.php?bo_table=contact&sca=&sop=and&sfl=wr_subject%7C%7Cwr_content&stx=<?=$s?>" class="more">+ 더보기</a>
<?php } ?>
</h5>
<div class="board_list">
	<table>
		<colgroup>
			<col width="7%">
			<col width="*">
			<col width="15%">
			<col width="10%">
		</colgroup>
		<thead>
		<tr>
			<th scope="col">No</th>
			<th scope="col">제목</th>
			<th scope="col">등록일</th>
			<th scope="col">조회수</th>
		</tr>
		</thead>
		<tbody>
		<?php
		for ($i=3;$row=sql_fetch_array($contact_res);$i--){
			?>
			<tr class=" ">
				<td class="td_num2"><?=$i?></td>
				<td class="td_subject">
					<div class="bo_tit"><a href="/bbs/view.php?bo_table=contact&amp;wr_id=<?=$row['wr_id']?>"><?=$row['wr_subject']?></a></div>
				</td>
				<td class="btm"><?php echo date("Y.m.d", strtotime($row['wr_datetime'])) ?></td>
				<td class="btm"><?=$row['wr_hit'];?></td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
</div>
-->

<h5 class="bul">경영공시
<a href="/bbs/board.php?bo_table=disclosure" class="more">+ 더보기</a>
<!-- <?php if($notice_cnt['cnt']>3){ ?>
<a href="/bbs/board.php?bo_table=disclosure&sca=&sop=and&sfl=wr_subject%7C%7Cwr_content&stx=<?=$s?>" class="more">+ 더보기</a>
<?php } ?> -->
</h5>
<div class="board_list">
	<table>
		<colgroup>
			<col width="7%">
			<col width="*">
			<col width="15%">
			<col width="10%">
		</colgroup>
		<thead>
		<tr>
			<th scope="col">No</th>
			<th scope="col">제목</th>
			<th scope="col">등록일</th>
			<th scope="col">조회수</th>
		</tr>
		</thead>
		<tbody>
		<?php
		for ($i=3;$row=sql_fetch_array($disclosure_res);$i--){
			?>
			<tr class=" ">
				<td class="td_num2"><?=$i?></td>
				<td class="td_subject"><!-- style="padding-left:0px" -->
					<div class="bo_tit"><a href="/bbs/view.php?bo_table=disclosure&amp;wr_id=<?=$row['wr_id']?>"><?=$row['wr_subject']?></a></div>
				</td>
				<td class="btm"><?php echo date("Y.m.d", strtotime($row['wr_datetime'])) ?></td>
				<td class="btm"><?=$row['wr_hit'];?></td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
</div>

<h5 class="bul">채용공고
<a href="/bbs/board.php?bo_table=recruits" class="more">+ 더보기</a>
<!-- <?php if($notice_cnt['cnt']>3){ ?>
<a href="/bbs/board.php?bo_table=recruits&sca=&sop=and&sfl=wr_subject%7C%7Cwr_content&stx=<?=$s?>" class="more">+ 더보기</a>
<?php } ?> -->
</h5>
<div class="board_list">
	<table>
		<colgroup>
			<col width="7%">
			<col width="*">
			<col width="15%">
			<col width="10%">
		</colgroup>
		<thead>
		<tr>
			<th scope="col">No</th>
			<th scope="col">제목</th>
			<th scope="col">등록일</th>
			<th scope="col">조회수</th>
		</tr>
		</thead>
		<tbody>
		<?php
		for ($i=3;$row=sql_fetch_array($recruits_res);$i--){
			?>
			<tr class=" ">
				<td class="td_num2"><?=$i?></td>
				<td class="td_subject"><!-- style="padding-left:0px" -->
					<div class="bo_tit"><a href="/bbs/view.php?bo_table=recruits&amp;wr_id=<?=$row['wr_id']?>"><?=$row['wr_subject']?></a></div>
				</td>
				<td class="btm"><?php echo date("Y.m.d", strtotime($row['wr_datetime'])) ?></td>
				<td class="btm"><?=$row['wr_hit'];?></td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
</div>

<h5 class="bul">친인척 채용공고
<a href="/bbs/board.php?bo_table=relative" class="more">+ 더보기</a>
<!-- <?php if($notice_cnt['cnt']>3){ ?>
<a href="/bbs/board.php?bo_table=relative&sca=&sop=and&sfl=wr_subject%7C%7Cwr_content&stx=<?=$s?>" class="more">+ 더보기</a>
<?php } ?> -->
</h5>
<div class="board_list">
	<table>
		<colgroup>
			<col width="7%">
			<col width="*">
			<col width="15%">
			<col width="10%">
		</colgroup>
		<thead>
		<tr>
			<th scope="col">No</th>
			<th scope="col">제목</th>
			<th scope="col">등록일</th>
			<th scope="col">조회수</th>
		</tr>
		</thead>
		<tbody>
		<?php
		for ($i=3;$row=sql_fetch_array($relative_res);$i--){
			?>
			<tr class=" ">
				<td class="td_num2"><?=$i?></td>
				<td class="td_subject"><!-- style="padding-left:0px" -->
					<div class="bo_tit"><a href="/bbs/view.php?bo_table=relative&amp;wr_id=<?=$row['wr_id']?>"><?=$row['wr_subject']?></a></div>
				</td>
				<td class="btm"><?php echo date("Y.m.d", strtotime($row['wr_datetime'])) ?></td>
				<td class="btm"><?=$row['wr_hit'];?></td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
</div>

<h5 class="bul">수의계약정보
<a href="/bbs/board.php?bo_table=contract" class="more">+ 더보기</a>
<!-- <?php if($notice_cnt['cnt']>3){ ?>
<a href="/bbs/board.php?bo_table=contract&sca=&sop=and&sfl=wr_subject%7C%7Cwr_content&stx=<?=$s?>" class="more">+ 더보기</a>
<?php } ?> -->
</h5>
<div class="board_list">
	<table>
		<colgroup>
			<col width="7%">
			<col width="*">
			<col width="15%">
			<col width="10%">
		</colgroup>
		<thead>
		<tr>
			<th scope="col">No</th>
			<th scope="col">제목</th>
			<th scope="col">등록일</th>
			<th scope="col">조회수</th>
		</tr>
		</thead>
		<tbody>
		<?php
		for ($i=3;$row=sql_fetch_array($contract_res);$i--){
			?>
			<tr class=" ">
				<td class="td_num2"><?=$i?></td>
				<td class="td_subject"><!-- style="padding-left:0px" -->
					<div class="bo_tit"><a href="/bbs/view.php?bo_table=contract&amp;wr_id=<?=$row['wr_id']?>"><?=$row['wr_subject']?></a></div>
				</td>
				<td class="btm"><?php echo date("Y.m.d", strtotime($row['wr_datetime'])) ?></td>
				<td class="btm"><?=$row['wr_hit'];?></td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
</div>

<h5 class="bul">입찰공고
<a href="/bbs/board.php?bo_table=bid" class="more">+ 더보기</a>
<!-- <?php if($notice_cnt['cnt']>3){ ?>
<a href="/bbs/board.php?bo_table=bid&sca=&sop=and&sfl=wr_subject%7C%7Cwr_content&stx=<?=$s?>" class="more">+ 더보기</a>
<?php } ?> -->
</h5>
<div class="board_list">
	<table>
		<colgroup>
			<col width="7%">
			<col width="*">
			<col width="15%">
			<col width="10%">
		</colgroup>
		<thead>
		<tr>
			<th scope="col">No</th>
			<th scope="col">제목</th>
			<th scope="col">등록일</th>
			<th scope="col">조회수</th>
		</tr>
		</thead>
		<tbody>
		<?php
		for ($i=3;$row=sql_fetch_array($bid_res);$i--){
			?>
			<tr class=" ">
				<td class="td_num2"><?=$i?></td>
				<td class="td_subject"><!-- style="padding-left:0px" -->
					<div class="bo_tit"><a href="/bbs/view.php?bo_table=bid&amp;wr_id=<?=$row['wr_id']?>"><?=$row['wr_subject']?></a></div>
				</td>
				<td class="btm"><?php echo date("Y.m.d", strtotime($row['wr_datetime'])) ?></td>
				<td class="btm"><?=$row['wr_hit'];?></td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
</div>

<h5 class="bul">알림마당
<a href="/bbs/board.php?bo_table=allim" class="more">+ 더보기</a>
<!-- <?php if($notice_cnt['cnt']>3){ ?>
<a href="/bbs/board.php?bo_table=allim&sca=&sop=and&sfl=wr_subject%7C%7Cwr_content&stx=<?=$s?>" class="more">+ 더보기</a>
<?php } ?> -->
</h5>
<div class="board_list">
	<table>
		<colgroup>
			<col width="7%">
			<col width="*">
			<col width="15%">
			<col width="10%">
		</colgroup>
		<thead>
		<tr>
			<th scope="col">No</th>
			<th scope="col">제목</th>
			<th scope="col">등록일</th>
			<th scope="col">조회수</th>
		</tr>
		</thead>
		<tbody>
		<?php
		for ($i=3;$row=sql_fetch_array($allim_res);$i--){
			?>
			<tr class=" ">
				<td class="td_num2"><?=$i?></td>
				<td class="td_subject"><!-- style="padding-left:0px" -->
					<div class="bo_tit"><a href="/bbs/view.php?bo_table=allim&amp;wr_id=<?=$row['wr_id']?>"><?=$row['wr_subject']?></a></div>
				</td>
				<td class="btm"><?php echo date("Y.m.d", strtotime($row['wr_datetime'])) ?></td>
				<td class="btm"><?=$row['wr_hit'];?></td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
</div>

<h5 class="bul">청렴자료실
<a href="/bbs/board.php?bo_table=ethics" class="more">+ 더보기</a>
<!-- <?php if($notice_cnt['cnt']>3){ ?>
<a href="/bbs/board.php?bo_table=ethics&sca=&sop=and&sfl=wr_subject%7C%7Cwr_content&stx=<?=$s?>" class="more">+ 더보기</a>
<?php } ?> -->
</h5>
<div class="photo_list ethics">
	<ul class="list">
		<?php
		for ($i=3;$row=sql_fetch_array($ethics_res);$i--){
			$thumb = get_list_thumbnail('ethics', $row['wr_id'], 290, 225, false, true);

			if($thumb['src']) {
				$img_content = '<img src="'.$thumb['ori'].'" class="thb" alt="'.$thumb['alt'].'" />';
			} else {
				//$img_content = '<span class="no_image" style="line-height:'.$board['bo_gallery_height'].'px;">no image</span>';
				$img_content = '<img src="/img/noimage.jpg" class="thb" alt="'.$thumb['alt'].'" />';
			}

			?>
			<li>
				<a href="http://ecophp.iceserver.co.kr/bbs/board.php?bo_table=ethics&amp;wr_id=<?=$row['wr_id']?>" class="gall_cont link">
					<div class="thumb"><img src="/theme/prix/img/thumb/thumb_290x225.png" alt="" />
						<?=$img_content?>
					</div>
					<div class="area"><div class="in_area">
							<p class="title"><?=$row['wr_subject']?></p>
							<p class="day"><?php echo date("Y.m.d", strtotime($row['wr_datetime'])) ?></p>
						</div>
					</div>
				</a>
			</li>
			<?php
		}
		?>
	</ul>
</div>

<h5 class="bul">사내소식
<a href="/bbs/board.php?bo_table=news" class="more">+ 더보기</a>
<!-- <?php if($notice_cnt['cnt']>3){ ?>
<a href="/bbs/board.php?bo_table=news&sca=&sop=and&sfl=wr_subject%7C%7Cwr_content&stx=<?=$s?>" class="more">+ 더보기</a>
<?php } ?> -->
</h5>
<div class="photo_list ethics">
	<ul class="list">
		<?php
		for ($i=3;$row=sql_fetch_array($news_res);$i--){
			$thumb = get_list_thumbnail('ethics', $row['wr_id'], 290, 225, false, true);

			if($thumb['src']) {
				$img_content = '<img src="'.$thumb['ori'].'" class="thb" alt="'.$thumb['alt'].'" />';
			} else {
				//$img_content = '<span class="no_image" style="line-height:'.$board['bo_gallery_height'].'px;">no image</span>';
				$img_content = '<img src="/img/noimage.jpg" class="thb" alt="'.$thumb['alt'].'" />';
			}

			?>
			<li>
				<a href="http://ecophp.iceserver.co.kr/bbs/board.php?bo_table=news&amp;wr_id=<?=$row['wr_id']?>" class="gall_cont link">
					<div class="thumb"><img src="/theme/prix/img/thumb/thumb_290x225.png" alt="" />
						<?=$img_content?>
					</div>
					<div class="area"><div class="in_area">
							<p class="title"><?=$row['wr_subject']?></p>
							<p class="day"><?php echo date("Y.m.d", strtotime($row['wr_datetime'])) ?></p>
						</div>
					</div>
				</a>
			</li>
			<?php
		}
		?>
	</ul>
</div>

<?php include_once(G5_THEME_PATH.'/tail.php'); ?>