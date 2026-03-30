<?php
if (!defined('_INDEX_')) define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH . '/index.php');
    return;
}

include_once(G5_THEME_PATH . '/head_en.php');
?>
영문
<style>
	.transformation-Table{width:100%; border-top:2px solid #333; font-size:0; line-height:0; overflow:hidden;}
	.transformation-Table>table{width:calc(100% + 1px); border-spacing:0; table-layout : fixed;}
	.transformation-Table>table th,
	.transformation-Table>table td{height:5rem; padding:2rem 0; background-color: #fff; border-bottom:1px solid #dadada; border-right:1px solid #dadada; font-size:1.7rem; line-height:1.5; color:#666; text-align:center; vertical-align: middle; border-collapse:collapse; empty-cells:show; box-sizing: border-box; word-break:keep-all; word-wrap:break-word;}

	.transformation-Table>table th{font-size:1.8rem; color: #333; text-align: center;}
	.transformation-Table>table>thead>tr>th{background-color: #f8f9fb; font-weight: 500;}
	.transformation-Table>table>tbody>tr>th{}

	.transformation-Table *[data-align="center"]{padding:2rem; text-align: left;}
	.transformation-Table *[data-align="center"]>*{display:inline-block;}

	.transformation-Table *[data-hide]{display:none;}

	@media only screen and (max-width : 1024px){
		:root {
			/* color */
			--w : 1;
			--h : 1;
		}
		.transformation-Table table{table-layout: auto; display: flex;}
		.transformation-Table table thead,
		.transformation-Table table tbody,
		.transformation-Table table th,
		.transformation-Table table td{width: auto; padding:0; display: block;}

		.transformation-Table table thead{width:20rem; float: left; white-space: nowrap;}
		.transformation-Table table tbody{width:100%; overflow-x: auto; white-space: nowrap; display: flex;}
		.transformation-Table table tr{flex:1 0 30vw; display: block;}
		.transformation-Table table th,
		.transformation-Table table td{width:calc(100% * var(--w)); height:calc(7rem * var(--h)); padding:0 2rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: center;}
		.transformation-Table table th{}
		.transformation-Table table td{}

		.transformation-Table table *[data-align="center"]{justify-content: flex-start;}
		.transformation-Table table *[data-align="center"]>*{max-width:calc(30vw - 4rem - 1px); display:block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;}

		.transformation-Table table *[data-hide]{display:flex;}

		.transformation-Table table *[colspan="2"]{--h:2;}
		.transformation-Table table *[colspan="3"]{--h:3;}
		.transformation-Table table *[colspan="4"]{--h:4;}
		.transformation-Table table *[colspan="5"]{--h:5;}
		.transformation-Table table *[colspan="6"]{--h:6;}
		.transformation-Table table *[colspan="7"]{--h:7;}


		.transformation-Table table *[rowspan]{position: relative; z-index:2;}
		.transformation-Table table *[rowspan="2"]{--w:2;}
		.transformation-Table table *[rowspan="3"]{--w:3;}
		.transformation-Table table *[rowspan="4"]{--w:4;}
		.transformation-Table table *[rowspan="5"]{--w:5;}
		.transformation-Table table *[rowspan="6"]{--w:6;}
		.transformation-Table table *[rowspan="7"]{--w:7;}
	}
</style>
<div class="inConts">
	<div class="transformation-Table">
		<table>
			<colgroup>
				<col width="7%">
				<col width="*">
				<col width="15%">
				<col width="20%">
				<col width="13%">
			</colgroup>
			<thead>
			<tr>
				<th scope="col">번호</th>
				<th scope="col">논문 명칭</th>
				<th scope="col">저자</th>
				<th scope="col">저널</th>
				<th scope="col">연도</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>1</td>
				<td data-align="center"><div>논문 명칭이 들어갑니다.</div></td>
				<td>저자가 들어갑니다.</td>
				<td>저널이 들어갑니다.</td>
				<td>2022</td>
			</tr>
			<tr>
				<td rowspan="2">2</td>
				<td data-align="center"><div>논문 명칭이 들어갑니다. 논문 명칭이 들어갑니다.</div></td>
				<td colspan="2">저자가 들어갑니다.</td>
				<td>2022</td>
			</tr>
			<tr>
				<td data-hide><!-- 가려짐 --></td>
				<td data-align="center"><div>논문 명칭이 들어갑니다. 논문 명칭이 들어갑니다.</div></td>
				<td>저자가 들어갑니다.</td>
				<td>저널이 들어갑니다.</td>
				<td>2022</td>
			</tr>
			<tr>
				<td>3</td>
				<td data-align="center"><div>논문 명칭이 들어갑니다. 논문 명칭이 들어갑니다. 논문 명칭이 들어갑니다.</div></td>
				<td colspan="3">저자가 들어갑니다.</td>
			</tr>
			<tr>
				<td>4</td>
				<td data-align="center"><div>논문 명칭이 들어갑니다. 논문 명칭이 들어갑니다.</div></td>
				<td>저자가 들어갑니다.</td>
				<td>저널이 들어갑니다.</td>
				<td>2022</td>
			</tr>
			<tr>
				<td>5</td>
				<td data-align="center"><div>논문 명칭이 들어갑니다.</div></td>
				<td>저자가 들어갑니다.</td>
				<td>저널이 들어갑니다.</td>
				<td>2022</td>
			</tr>
			<tr>
				<td>6</td>
				<td data-align="center"><div>논문 명칭이 들어갑니다. 논문 명칭이 들어갑니다.</div></td>
				<td>저자가 들어갑니다.</td>
				<td>저널이 들어갑니다.</td>
				<td>2022</td>
			</tr>
			<tr>
				<td>7</td>
				<td data-align="center"><div>논문 명칭이 들어갑니다. 논문 명칭이 들어갑니다. 논문 명칭이 들어갑니다.</div></td>
				<td>저자가 들어갑니다.</td>
				<td>저널이 들어갑니다.</td>
				<td>2022</td>
			</tr>
			<tr>
				<td>8</td>
				<td data-align="center"><div>논문 명칭이 들어갑니다. 논문 명칭이 들어갑니다. 논문 명칭이 들어갑니다.</div></td>
				<td>저자가 들어갑니다.</td>
				<td>저널이 들어갑니다.</td>
				<td>2022</td>
			</tr>
			<tr>
				<td>9</td>
				<td data-align="center"><div>논문 명칭이 들어갑니다. 논문 명칭이 들어갑니다. 논문 명칭이 들어갑니다.</div></td>
				<td>저자가 들어갑니다.</td>
				<td>저널이 들어갑니다.</td>
				<td>2022</td>
			</tr>
			<tr>
				<td>10</td>
				<td data-align="center"><div>논문 명칭이 들어갑니다. 논문 명칭이 들어갑니다. 논문 명칭이 들어갑니다.</div></td>
				<td>저자가 들어갑니다.</td>
				<td>저널이 들어갑니다.</td>
				<td>2022</td>
			</tr>
			<!-- -->
			</tbody>
		</table>
	</div>
	<div class="transformation-Table">
		<table>
			<colgroup>
				<col width="10%">
				<col width="*">
				<col width="*">
				<col width="*">
				<col width="*">
				<col width="*">
				<col width="10%">
			</colgroup>
			<thead>
			<tr>
				<th>No</th>
				<th>OUT</th>
				<th>IN</th>
				<th>Class</th>
				<th>운임TL</th>
				<th>총 발권금액</th>
				<th>추천여정</th>
			</tr>
			</thead>
			<tbody>
				<tr>
					<th>1</th>
					<td>7C2903 / 2120</td>
					<td>7C2904 / 0200</td>
					<td>H+L</td>
					<td>20DEC22</td>
					<td>KRW 360200</td>
					<td></td>
				</tr>
				<tr>
					<th>2</th>
					<td>VJ881 / 0615</td>
					<td>VJ878 / 0005</td>
					<td>J+W</td>
					<td>20DEC22</td>
					<td>KRW 408600</td>
					<td>@</td>
				</tr>
				<tr>
					<th>3</th>
					<td>TW125 / 0745</td>
					<td>TW126 / 1155</td>
					<td>I+H</td>
					<td>20DEC22</td>
					<td>KRW 416500</td>
					<td></td>
				</tr>
				<tr>
					<th>4</th>
					<td>7C2903 / 2120</td>
					<td>8C29023 / 1535</td>
					<td>H+X</td>
					<td>20DEC22</td>
					<td>KRW 420200</td>
					<td></td>
				</tr>
				<tr>
					<th>5</th>
					<td>VJ*H14519 / 0615</td>
					<td>VJ*H14181 / 0005</td>
					<td>L+K</td>
					<td>16OCT22</td>
					<td>KRW 493800</td>
					<td>@</td>
				</tr>
				<tr>
					<th>6</th>
					<td>7C2903 / 2120</td>
					<td>7C2904 / 0200</td>
					<td>H+L</td>
					<td>20DEC22</td>
					<td>KRW 360200</td>
					<td></td>
				</tr>
				<tr>
					<th>7</th>
					<td>VJ881 / 0615</td>
					<td>VJ878 / 0005</td>
					<td>J+W</td>
					<td>20DEC22</td>
					<td>KRW 408600</td>
					<td>@</td>
				</tr>
				<tr>
					<th>8</th>
					<td>TW125 / 0745</td>
					<td>TW126 / 1155</td>
					<td>I+H</td>
					<td>20DEC22</td>
					<td>KRW 416500</td>
					<td></td>
				</tr>
				<tr>
					<th>9</th>
					<td>7C2903 / 2120</td>
					<td>8C29023 / 1535</td>
					<td>H+X</td>
					<td>20DEC22</td>
					<td>KRW 420200</td>
					<td></td>
				</tr>
				<tr>
					<th>10</th>
					<td>VJ*H14519 / 0615</td>
					<td>VJ*H14181 / 0005</td>
					<td>L+K</td>
					<td>16OCT22</td>
					<td>KRW 493800</td>
					<td>@</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<?php include_once(G5_THEME_PATH . '/tail_en.php'); ?>