<?php

$query = " select title , no from nb_board where board_no=".$board_no." and is_notice != 'Y' and no<'{$no}' order by no desc limit 0 , 1";
$result = mysql_query($query);
$r1 = mysql_fetch_array($result);

$prev_title = count($r1)>0 ? cutstr(stripslashes(htmlspecialchars($r1['title'])),35) : "이전글이 없습니다.";
$prev_link = count($r1)>0 ? $_SERVER['PHP_SELF']."?no=".$r1['no']."&board_no=".$board_no  : "#none";


$query = " select title , no from nb_board where board_no=".$board_no." and is_notice != 'Y' and no>'{$no}' order by no asc limit 0 , 1";
$result = mysql_query($query);
$r2 = mysql_fetch_array($result);

$next_title = count($r2)>0 ? cutstr(stripslashes(htmlspecialchars($r2['title'])),35) : "다음글이 없습니다.";
$next_link = count($r2)>0 ? $_SERVER['PHP_SELF']."?no=".$r2['no']."&board_no=".$board_no  : "#none";

?>
<article class="no_prevnext">
	<ul>
		<li>
			<table class="no_prevnext-tbl">
				<colgroup>
					<col style="width: 140px">
					<col>
				</colgroup>
				<tbody>
					<tr>
						<th>
							<strong class="no_prevnext-dic">
								이전
								<i class="ri-arrow-up-s-line"></i>
							</strong>
						</th>
						<td>
							<a href="<?=$prev_link?>" <?=$prev_link=='#none'?"onclick='return false;'":""?>>
								<p class="no_prevnext-tit"><?=$prev_title?></p>
							</a>
						</td>
					</tr>
				</tbody>
			</table>
		</li>
		<li>
			<table class="no_prevnext-tbl">
				<colgroup>
					<col style="width: 140px">
					<col>
				</colgroup>
				<tbody>
					<tr>
						<th>
							<strong class="no_prevnext-dic">
								다음
								<i class="ri-arrow-down-s-line"></i>
							</strong>
						</th>
						<td>
							<a href="<?=$next_link?>" <?=$next_link=='#none'?"onclick='return false;'":""?>>
								<p class="no_prevnext-tit"><?=$next_title?></p>
							</a>
						</td>
					</tr>
				</tbody>
			</table>
		</li>
	</ul>
</article>