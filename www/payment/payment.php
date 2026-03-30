<?
	include_once  "../../inc/lib/base.class.php";
	//include_once  "../../inc/lib/login.check.php";


?>

<!DOCTYPE HTML>

<html lang="ko">
<head>

	<?
		include_once "../../inc/inc_titlemeta.php";
		include_once "../../inc/inc_css.php";
		include_once "../../inc/inc_script.php";

		$depthnum = 1;
		$pagenum = 4;
	?>
	
</head>

<body>

	<div class="no-wrap">
		<!-- Header -->
		<?
			include_once "../../inc/header.php";
		?>

		<!-- Container -->
		<main id="no-main">
			<!-- Section:Navigation -->
			<? 
				include_once "../../inc/lnb_matching.php"; 
			?>
			<!-- Section:Contents -->
			<section class="no-subContents__wrap">
				<div class="no-inner">
					<!-- 멤버쉽 비용 -->
					<div class="no-payPrice">
						<div class="no-tab-menu">
							<button class="no-tablinks three-month active">1개월</button>
							<button class="no-tablinks six-month" >3개월</button>
							<button class="no-tablinks nine-month">6개월</button>
							<button class="no-tablinks twelve-month" >12개월</button>
						</div>
						<!--컨텐츠--->
						<ul class="no-payPrice-cnt">
							
							<?
								$month3_free = getMembership(1);
								$month3_basic = getMembership(2);
								$month3_standard = getMembership(3);
								$month3_premium = getMembership(4);
								$month3_premium_free = getMembership(5);
							?>
							<!--3개월-->
							<li>
								<div class="no-payPrice__type">
									<ul>
										<li>
											<div class="no-payPrice__type--free no-payPrice__type--box">
												<h3><?=$month3_free[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month3_free[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month3_free[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.free.php?id=<?=$month3_free[0]['no']?>">체험하기</a>
												</div>
											</div>
										</li>
										<li>
											<div class="no-payPrice__type--basic no-payPrice__type--box">
												<h3><?=$month3_basic[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month3_basic[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month3_basic[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.php?id=<?=$month3_basic[0]['no']?>">결제하기</a>
												</div>
											</div>
										</li>
										<li>
											<div class="no-payPrice__type--standard no-payPrice__type--box">
												<h3><?=$month3_standard[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month3_standard[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month3_standard[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.php?id=<?=$month3_standard[0]['no']?>">결제하기</a>
												</div>
											</div>
										</li>
										<li>
											<div class="no-payPrice__type--premium no-payPrice__type--box">
												<h3><?=$month3_premium[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month3_premium[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month3_premium[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.php?id=<?=$month3_premium[0]['no']?>">결제하기</a>
												</div>
											</div>
										</li>
										<li>
											<div class="no-payPrice__type--premium-free no-payPrice__type--box">
												<h3><?=$month3_premium_free[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month3_premium_free[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month3_premium_free[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.php?id=<?=$month3_premium_free[0]['no']?>">결제하기</a>
												</div>
											</div>
										</li>
									</ul>
								</div>
								<div class="no-payPrice-notice">
									<i class="fa-regular fa-circle-exclamation"></i>
									<div>
										<p class="mb10">
											결제 금액은 부가세가 포함된 가격으로 무이자 할부는 제공되지 않습니다.
											1개월 요금제는 매월 자동결제로, 신용카드 결제만 가능합니다.
										</p>
											<p>
											매커멕스 멤버십은 디지털 콘텐츠 특성상 결제취소 되지 않습니다.
											서비스를 이용하지 않은 상태에서 취소/환불은 하단의 [자주 묻는 질문]에서 확인 가능합니다.
										</p>
									</div>
								</div>
								<div id="Tab1" class="no-tabcontent">
									<div class="no-payTable-box">
										<table class="no-payTable">
											<thead>
											</thead>
											<tbody>
												<tr>
													<td>요금제별 제공 내용</td>
													<td class="free"><?=$month3_free[0]['title']?></td>
													<td class="basic"><?=$month3_basic[0]['title']?></td>
													<td class="standard"><?=$month3_standard[0]['title']?></td>
													<td class="premium"><?=$month3_premium[0]['title']?></td>
													<td class="premium-free"><?=$month3_premium_free[0]['title']?></td>
												</tr>
												<tr>
													<td>회원사 정보 열람</td>
													<td><?=$month3_free[0]['open_cnt']?>회</td>
													<td><?=$month3_basic[0]['open_cnt']?>회</td>
													<td><?=$month3_standard[0]['open_cnt']?>회</td>
													<td><?=$month3_premium[0]['open_cnt']?>회</td>
													<td><?=$month3_premium_free[0]['open_cnt']?>회</td>
												</tr>
												<tr>
													<td>매칭요청</td>
													<td><?=$month3_free[0]['matching_cnt']?>회</td>
													<td><?=$month3_basic[0]['matching_cnt']?>회</td>
													<td><?=$month3_standard[0]['matching_cnt']?>회</td>
													<td><?=$month3_premium[0]['matching_cnt']?>회</td>
													<td><?=$month3_premium_free[0]['matching_cnt']?>회</td>
												</tr>
												<tr>
													<td>전문가 컨설팅</td>
													<td>-</td>
													<td><?=$month3_basic[0]['expert_consulting_time']?></td>
													<td><?=$month3_standard[0]['expert_consulting_time']?></td>
													<td><?=$month3_premium[0]['expert_consulting_time']?></td>
													<td><?=$month3_premium_free[0]['expert_consulting_time']?></td>
												</tr>
												<tr class="sale-price">
													<td>프로모션 할인가</td>
													<td>-</td>
													<td>&#8361;<?=number_format($month3_basic[0]['price_discount'],0)?>(<?=$month3_basic[0]['discount_percent']?>%)</td>
													<td>&#8361;<?=number_format($month3_standard[0]['price_discount'],0)?>(<?=$month3_standard[0]['discount_percent']?>%)</td>
													<td>&#8361;<?=number_format($month3_premium[0]['price_discount'],0)?>(<?=$month3_premium[0]['discount_percent']?>%)</td>
													<td>&#8361;<?=number_format($month3_premium_free[0]['price_discount'],0)?>(<?=$month3_premium_free[0]['discount_percent']?>%)</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</li>

							<!--6개월-->
							<?
								$month6_free = getMembership(6);
								$month6_basic = getMembership(7);
								$month6_standard = getMembership(8);
								$month6_premium = getMembership(9);
								$month6_premium_free = getMembership(10);
							?>
							<!--3개월-->
							<li>
								<div class="no-payPrice__type">
									<ul>
										<li>
											<div class="no-payPrice__type--free no-payPrice__type--box">
												<h3><?=$month6_free[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month6_free[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month6_free[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.free.php?id=<?=$month6_free[0]['no']?>">체험하기</a>
												</div>
											</div>
										</li>
										<li>
											<div class="no-payPrice__type--basic no-payPrice__type--box">
												<h3><?=$month6_basic[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month6_basic[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month6_basic[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.php?id=<?=$month6_basic[0]['no']?>">결제하기</a>
												</div>
											</div>
										</li>
										<li>
											<div class="no-payPrice__type--standard no-payPrice__type--box">
												<h3><?=$month6_standard[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month6_standard[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month6_standard[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.php?id=<?=$month6_standard[0]['no']?>">결제하기</a>
												</div>
											</div>
										</li>
										<li>
											<div class="no-payPrice__type--premium no-payPrice__type--box">
												<h3><?=$month6_premium[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month6_premium[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month6_premium[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.php?id=<?=$month6_premium[0]['no']?>">결제하기</a>
												</div>
											</div>
										</li>
										<li>
											<div class="no-payPrice__type--premium-free no-payPrice__type--box">
												<h3><?=$month6_premium_free[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month6_premium_free[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month6_premium_free[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.php?id=<?=$month6_premium_free[0]['no']?>">결제하기</a>
												</div>
											</div>
										</li>
									</ul>
								</div>
								<div class="no-payPrice-notice">
									<i class="fa-regular fa-circle-exclamation"></i>
									<div>
										<p class="mb10">
											결제 금액은 부가세가 포함된 가격으로 무이자 할부는 제공되지 않습니다.
											1개월 요금제는 매월 자동결제로, 신용카드 결제만 가능합니다.
										</p>
											<p>
											매커멕스 멤버십은 디지털 콘텐츠 특성상 결제취소 되지 않습니다.
											서비스를 이용하지 않은 상태에서 취소/환불은 하단의 [자주 묻는 질문]에서 확인 가능합니다.
										</p>
									</div>
								</div>
								<div id="Tab1" class="no-tabcontent">
									<div class="no-payTable-box">
										<table class="no-payTable">
											<thead>
											</thead>
											<tbody>
												<tr>
													<td>요금제별 제공 내용</td>
													<td class="free"><?=$month6_free[0]['title']?></td>
													<td class="basic"><?=$month6_basic[0]['title']?></td>
													<td class="standard"><?=$month6_standard[0]['title']?></td>
													<td class="premium"><?=$month6_premium[0]['title']?></td>
													<td class="premium-free"><?=$month6_premium_free[0]['title']?></td>
												</tr>
												<tr>
													<td>회원사 정보 열람</td>
													<td><?=$month6_free[0]['open_cnt']?>회</td>
													<td><?=$month6_basic[0]['open_cnt']?>회</td>
													<td><?=$month6_standard[0]['open_cnt']?>회</td>
													<td><?=$month6_premium[0]['open_cnt']?>회</td>
													<td><?=$month6_premium_free[0]['open_cnt']?>회</td>
												</tr>
												<tr>
													<td>매칭요청</td>
													<td><?=$month6_free[0]['matching_cnt']?>회</td>
													<td><?=$month6_basic[0]['matching_cnt']?>회</td>
													<td><?=$month6_standard[0]['matching_cnt']?>회</td>
													<td><?=$month6_premium[0]['matching_cnt']?>회</td>
													<td><?=$month6_premium_free[0]['matching_cnt']?>회</td>
												</tr>
												<tr>
													<td>전문가 컨설팅</td>
													<td>-</td>
													<td><?=$month6_basic[0]['expert_consulting_time']?></td>
													<td><?=$month6_standard[0]['expert_consulting_time']?></td>
													<td><?=$month6_premium[0]['expert_consulting_time']?></td>
													<td><?=$month6_premium_free[0]['expert_consulting_time']?></td>
												</tr>
												<tr class="sale-price">
													<td>프로모션 할인가</td>
													<td>-</td>
													<td>&#8361;<?=number_format($month6_basic[0]['price_discount'],0)?>(<?=$month6_basic[0]['discount_percent']?>%)</td>
													<td>&#8361;<?=number_format($month6_standard[0]['price_discount'],0)?>(<?=$month6_standard[0]['discount_percent']?>%)</td>
													<td>&#8361;<?=number_format($month6_premium[0]['price_discount'],0)?>(<?=$month6_premium[0]['discount_percent']?>%)</td>
													<td>&#8361;<?=number_format($month6_premium_free[0]['price_discount'],0)?>(<?=$month6_premium_free[0]['discount_percent']?>%)</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</li>

							<!--9개월-->
							<?
								$month9_free = getMembership(11);
								$month9_basic = getMembership(12);
								$month9_standard = getMembership(13);
								$month9_premium = getMembership(14);
								$month9_premium_free = getMembership(15);
							?>
							<!--3개월-->
							<li>
								<div class="no-payPrice__type">
									<ul>
										<li>
											<div class="no-payPrice__type--free no-payPrice__type--box">
												<h3><?=$month9_free[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month9_free[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month9_free[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.free.php?id=<?=$month9_free[0]['no']?>">체험하기</a>
												</div>
											</div>
										</li>
										<li>
											<div class="no-payPrice__type--basic no-payPrice__type--box">
												<h3><?=$month9_basic[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month9_basic[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month9_basic[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.php?id=<?=$month9_basic[0]['no']?>">결제하기</a>
												</div>
											</div>
										</li>
										<li>
											<div class="no-payPrice__type--standard no-payPrice__type--box">
												<h3><?=$month9_standard[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month9_standard[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month9_standard[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.php?id=<?=$month9_standard[0]['no']?>">결제하기</a>
												</div>
											</div>
										</li>
										<li>
											<div class="no-payPrice__type--premium no-payPrice__type--box">
												<h3><?=$month9_premium[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month9_premium[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month9_premium[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.php?id=<?=$month9_premium[0]['no']?>">결제하기</a>
												</div>
											</div>
										</li>
										<li>
											<div class="no-payPrice__type--premium-free no-payPrice__type--box">
												<h3><?=$month9_premium_free[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month9_premium_free[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month9_premium_free[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.php?id=<?=$month9_premium_free[0]['no']?>">결제하기</a>
												</div>
											</div>
										</li>
									</ul>
								</div>
								<div class="no-payPrice-notice">
									<i class="fa-regular fa-circle-exclamation"></i>
									<div>
										<p class="mb10">
											결제 금액은 부가세가 포함된 가격으로 무이자 할부는 제공되지 않습니다.
											1개월 요금제는 매월 자동결제로, 신용카드 결제만 가능합니다.
										</p>
											<p>
											매커멕스 멤버십은 디지털 콘텐츠 특성상 결제취소 되지 않습니다.
											서비스를 이용하지 않은 상태에서 취소/환불은 하단의 [자주 묻는 질문]에서 확인 가능합니다.
										</p>
									</div>
								</div>
								<div id="Tab1" class="no-tabcontent">
									<div class="no-payTable-box">
										<table class="no-payTable">
											<thead>
											</thead>
											<tbody>
												<tr>
													<td>요금제별 제공 내용</td>
													<td class="free"><?=$month9_free[0]['title']?></td>
													<td class="basic"><?=$month9_basic[0]['title']?></td>
													<td class="standard"><?=$month9_standard[0]['title']?></td>
													<td class="premium"><?=$month9_premium[0]['title']?></td>
													<td class="premium-free"><?=$month9_premium_free[0]['title']?></td>
												</tr>
												<tr>
													<td>회원사 정보 열람</td>
													<td><?=$month9_free[0]['open_cnt']?>회</td>
													<td><?=$month9_basic[0]['open_cnt']?>회</td>
													<td><?=$month9_standard[0]['open_cnt']?>회</td>
													<td><?=$month9_premium[0]['open_cnt']?>회</td>
													<td><?=$month9_premium_free[0]['open_cnt']?>회</td>
												</tr>
												<tr>
													<td>매칭요청</td>
													<td><?=$month9_free[0]['matching_cnt']?>회</td>
													<td><?=$month9_basic[0]['matching_cnt']?>회</td>
													<td><?=$month9_standard[0]['matching_cnt']?>회</td>
													<td><?=$month9_premium[0]['matching_cnt']?>회</td>
													<td><?=$month9_premium_free[0]['matching_cnt']?>회</td>
												</tr>
												<tr>
													<td>전문가 컨설팅</td>
													<td>-</td>
													<td><?=$month9_basic[0]['expert_consulting_time']?></td>
													<td><?=$month9_standard[0]['expert_consulting_time']?></td>
													<td><?=$month9_premium[0]['expert_consulting_time']?></td>
													<td><?=$month9_premium_free[0]['expert_consulting_time']?></td>
												</tr>
												<tr class="sale-price">
													<td>프로모션 할인가</td>
													<td>-</td>
													<td>&#8361;<?=number_format($month9_basic[0]['price_discount'],0)?>(<?=$month9_basic[0]['discount_percent']?>%)</td>
													<td>&#8361;<?=number_format($month9_standard[0]['price_discount'],0)?>(<?=$month9_standard[0]['discount_percent']?>%)</td>
													<td>&#8361;<?=number_format($month9_premium[0]['price_discount'],0)?>(<?=$month9_premium[0]['discount_percent']?>%)</td>
													<td>&#8361;<?=number_format($month9_premium_free[0]['price_discount'],0)?>(<?=$month9_premium_free[0]['discount_percent']?>%)</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</li>

							<!--12개월-->
							<?
								$month12_free = getMembership(16);
								$month12_basic = getMembership(17);
								$month12_standard = getMembership(18);
								$month12_premium = getMembership(19);
								$month12_premium_free = getMembership(20);
							?>
							<!--3개월-->
							<li>
								<div class="no-payPrice__type">
									<ul>
										<li>
											<div class="no-payPrice__type--free no-payPrice__type--box">
												<h3><?=$month12_free[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month12_free[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month12_free[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.free.php?id=<?=$month12_free[0]['no']?>">체험하기</a>
												</div>
											</div>
										</li>
										<li>
											<div class="no-payPrice__type--basic no-payPrice__type--box">
												<h3><?=$month12_basic[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month12_basic[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month12_basic[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.php?id=<?=$month12_basic[0]['no']?>">결제하기</a>
												</div>
											</div>
										</li>
										<li>
											<div class="no-payPrice__type--standard no-payPrice__type--box">
												<h3><?=$month12_standard[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month12_standard[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month12_standard[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.php?id=<?=$month12_standard[0]['no']?>">결제하기</a>
												</div>
											</div>
										</li>
										<li>
											<div class="no-payPrice__type--premium no-payPrice__type--box">
												<h3><?=$month12_premium[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month12_premium[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month12_premium[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.php?id=<?=$month12_premium[0]['no']?>">결제하기</a>
												</div>
											</div>
										</li>
										<li>
											<div class="no-payPrice__type--premium-free no-payPrice__type--box">
												<h3><?=$month12_premium_free[0]['title']?></h3>
												<div class="no-payPrice__type--price">
													<div class="no-payPrice__type--sale">
														&#8361;<b><?=number_format($month12_premium_free[0]['price_origin'],0)?></b>
													</div>
													<div class="no-payPrice__type--actual">
														&#8361;<b><?=number_format($month12_premium_free[0]['price_discount'],0)?></b>
													</div>
												</div>
												<div class="no-payPrice__type--pay">
													<a href="./payment.detail.php?id=<?=$month12_premium_free[0]['no']?>">결제하기</a>
												</div>
											</div>
										</li>
									</ul>
								</div>
								<div class="no-payPrice-notice">
									<i class="fa-regular fa-circle-exclamation"></i>
									<div>
										<p class="mb10">
											결제 금액은 부가세가 포함된 가격으로 무이자 할부는 제공되지 않습니다.
											1개월 요금제는 매월 자동결제로, 신용카드 결제만 가능합니다.
										</p>
											<p>
											매커멕스 멤버십은 디지털 콘텐츠 특성상 결제취소 되지 않습니다.
											서비스를 이용하지 않은 상태에서 취소/환불은 하단의 [자주 묻는 질문]에서 확인 가능합니다.
										</p>
									</div>
								</div>
								<div id="Tab1" class="no-tabcontent">
									<div class="no-payTable-box">
										<table class="no-payTable">
											<thead>
											</thead>
											<tbody>
												<tr>
													<td>요금제별 제공 내용</td>
													<td class="free"><?=$month12_free[0]['title']?></td>
													<td class="basic"><?=$month12_basic[0]['title']?></td>
													<td class="standard"><?=$month12_standard[0]['title']?></td>
													<td class="premium"><?=$month12_premium[0]['title']?></td>
													<td class="premium-free"><?=$month12_premium_free[0]['title']?></td>
												</tr>
												<tr>
													<td>회원사 정보 열람</td>
													<td><?=$month12_free[0]['open_cnt']?>회</td>
													<td><?=$month12_basic[0]['open_cnt']?>회</td>
													<td><?=$month12_standard[0]['open_cnt']?>회</td>
													<td><?=$month12_premium[0]['open_cnt']?>회</td>
													<td><?=$month12_premium_free[0]['open_cnt']?>회</td>
												</tr>
												<tr>
													<td>매칭요청</td>
													<td><?=$month12_free[0]['matching_cnt']?>회</td>
													<td><?=$month12_basic[0]['matching_cnt']?>회</td>
													<td><?=$month12_standard[0]['matching_cnt']?>회</td>
													<td><?=$month12_premium[0]['matching_cnt']?>회</td>
													<td><?=$month12_premium_free[0]['matching_cnt']?>회</td>
												</tr>
												<tr>
													<td>전문가 컨설팅</td>
													<td>-</td>
													<td><?=$month12_basic[0]['expert_consulting_time']?></td>
													<td><?=$month12_standard[0]['expert_consulting_time']?></td>
													<td><?=$month12_premium[0]['expert_consulting_time']?></td>
													<td><?=$month12_premium_free[0]['expert_consulting_time']?></td>
												</tr>
												<tr class="sale-price">
													<td>프로모션 할인가</td>
													<td>-</td>
													<td>&#8361;<?=number_format($month12_basic[0]['price_discount'],0)?>(<?=$month12_basic[0]['discount_percent']?>%)</td>
													<td>&#8361;<?=number_format($month12_standard[0]['price_discount'],0)?>(<?=$month12_standard[0]['discount_percent']?>%)</td>
													<td>&#8361;<?=number_format($month12_premium[0]['price_discount'],0)?>(<?=$month12_premium[0]['discount_percent']?>%)</td>
													<td>&#8361;<?=number_format($month12_premium_free[0]['price_discount'],0)?>(<?=$month12_premium_free[0]['discount_percent']?>%)</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</li>

						</ul>
						




					</div>

					<!-- 전문가 컨설팅 서비스 -->
					<div class="no-special">
						<div>
							<i class="fa-solid fa-circle-exclamation"></i>
							<h3>특별 서비스</h3>
						</div>
						<p>
							멤버십 프리미엄 / 프리미엄 결제 후 <b>[마이페이지] - [멤버십]</b> 에서 원하시는 분야 전문가를 선택하시면 전문가가 지정되어 기업 운영에 필요한 컨설팅 서비스를 무료로 받을 수 있습니다.
						</p>
					</div>
					<div class="no-payService">
						<div class="no-members_wrap">
							<?php
								$board_info = getBoardInfoByName("전문가 무료 컨설팅 서비스");
								$board_no = $board_info[0]['no'];
								$arrLenders = getBoardLimit($board_no, 20, "");
							?>
							<ul class="no-members_con">
								<?php
									foreach ($arrLenders as $k => $v) {
										$title = iconv_substr($v['title'], 0, 80, "utf-8");
										$imgSrc = "";
										if ($v['thumb_image']) {
											$imgSrc = $UPLOAD_WDIR_BOARD . "/" . $v['thumb_image'];
										} else {
											$imgSrc = getImageTag($v['contents'], "src");
											$imgSrc = $imgSrc[0];
										}
										$position = $v['extra1'];
										$name = $v['extra2'];
										$major = $v['extra3'];
									?>
									<li>
										<h2 class="no-payPart"><?=$title?></h2>
										<div class="no-payMebers">
											<div class="no-members_img">
												<img src="<?=$imgSrc?>" alt="<?=$title?>">
											</div>
											<div class="no-members_info">
												<span><?=$position?></span>
												<h3><?=$name?></h3>
											</div>
											<div class="no-members_career_box">
												<ul class="no-members_career_cnt">
													<li>
														<h3>전공</h3>
														<ul class="no-members_career_info">
															<li><?=$major?></li>
														</ul>
													</li>
													<li>
														<h3>주요경력</h3>
														<ul class="no-members_career_info">
															<?php for ($i = 4; $i <= 9; $i++): ?>
																<?php if (!empty($v["extra$i"])): ?>
																	<li><?=$v["extra$i"]?></li>
																<?php endif; ?>
															<?php endfor; ?>
														</ul>
													</li>
													<li>
														<h3>주요업적</h3>
														<ul class="no-members_career_info">
															<?php for ($i = 10; $i <= 15; $i++): ?>
																<?php if (!empty($v["extra$i"])): ?>
																	<li><?=$v["extra$i"]?></li>
																<?php endif; ?>
															<?php endfor; ?>
														</ul>
													</li>
												</ul>
											</div>
											<a href="#none" class="no-members_career_btn">펼치기</a>
										</div>
									</li>
								<?php } ?>
							</ul>
						</div>
					</div>


				</div>
			</section>
		</main>
		<script src='/resource/js/payment.js' defer></script>
		<!-- Footer -->
		<?
			include_once "../../inc/footer.php";
		?>
	<!-- 24.02.06 삭제 요청
		<?
			include_once "./payment.modal.php";
		?>-->
	</div>

	<? include_once  dirname(__FILE__)."/inc/lib/popup.inc.php"; ?>
	<? include_once  dirname(__FILE__)."/inc/lib/popup.m.inc.php"; ?>

</body>

<script>


	function onTabHandler(tabHead, tabBody) {
	  const tabMenu = $(tabHead);
	  const tabCnt = $(tabBody);
	  tabCnt.eq(0).show();

	  tabMenu.click(function (e) {
		e.preventDefault();
		const idx = $(this).index();
		console.log(idx, tabCnt.eq(idx));
		$(this).siblings().find("button").removeClass("active");
		$(this).find("button").addClass("active");

		tabCnt.hide();
		tabCnt.eq(idx).show();
	  });
	}

	onTabHandler(".no-tab-menu> button", ".no-payPrice-cnt > li");




</script>
</html>