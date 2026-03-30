<div class="no-bg"></div>
<div class="no-payment-complete-bill">
	<div class="no-payment-complete-bill__close">
		<i class="fa-regular fa-xmark" aria-hidden="true"></i>
	</div>
	<div class="no-payment-complete-bill__title">
		<p>매커멕스</p>
		<h2>제품 이름</h2>
	</div>

	<div class="no-payment-complete-bill__method">
		<span>신한 체크카드</span>
	</div>
	
	<div class="no-payment-complete-bill__info">
		<ul>
			<li>
				<span>승인 일시</span>
				<p>2015.15.20 14:00:00</p>
			</li>
			<li>
				<span>승인 번호</span>
				<p>56564156156</p>
			</li>
			<li>
				<span>거래 유형</span>
				<p>승인</p>
			</li>
			<li>
				<span>할부</span>
				<p>일시불</p>
			</li>
		</ul>
		<ul>
			<li>
				<span>공급 가액</span>
				<p>5,003원</p>
			</li>
			<li>
				<span>부가세</span>
				<p>553원</p>
			</li>
		</ul>
	</div>
</div>

<footer id="no-footer">
	<div class="no-footer__con">
		<div class="no-footer__top">
			<div class="no-footer__guide">
				<div class="no-footer__contact">
					<h2 class="no-footer__title">고객센터</h2>
					<a href="tel:02-573-8677" class="no-footer__phone">02-573-8677</a>
					<ul class="no-footer__time">
						<li>평일 09:00 ~ 17:00</li>
						<li>점심시간 12:00 ~ 13:00</li>
						<li>주말 및 공휴일 휴무</li>
					</ul>
					<a href="<?=$contact?>" class="no-footer__contact__btn">고객문의</a>
				</div>
				<div class="no-footer__service">
					<h2 class="no-footer__title">서비스</h2>
					<ul class="no-footer__service__depth">
						<li>
							<a href="<?=$service_matching?>">Matching Station</a>
						</li>
						<li>
							<a href="<?=$service_consulting?>">Consulting Station</a>
						</li>
						<li>
							<a href="http://www.mnastation.com/" target="_blanks">M&A Station</a>
						</li>
						<li>
							<a href="<?=$service_exit?>">Exit Station</a>
						</li>
					</ul>
				</div>
			</div>
			<nav class="no-footer__menu">
				<ul class="no-footer__gnb">
					<li>
						<a href="<?=$e2eservice?>" class="no-footer__title">매커멕스소개</a>
						<ul class="no-footer__lnb">
							<li>
								<a href="<?=$e2eservice?>">E2E서비스</a>
							</li>
							<li>
								<a href="<?=$e2eprocess?>">E2E프로세스</a>
							</li>
						</ul>
					</li>
					<li>
						<a href="<?=$manufacture?>" class="no-footer__title">제조사안내</a>
						<ul class="no-footer__lnb">
							<li>
								<a href="<?=$manufacture?>">이용안내</a>
							</li>
							<li>
								<a href="<?=$MFR_form?>">등록하기</a>
							</li>
						</ul>
					</li>
					<li>
						<a href="<?=$distribution?>" class="no-footer__title">유통사안내</a>
						<ul class="no-footer__lnb">
							<li>
								<a href="<?=$distribution?>">이용안내</a>
							</li>
							<li>
								<a href="<?=$openmarket?>">폐쇄몰안내</a>
							</li>
							<li>
								<a href="<?=$DISTI_form?>">등록하기</a>
							</li>
						</ul>
					</li>
					<li>
						<a href="<?=$cm_notive?>" class="no-footer__title">고객센터</a>
						<ul class="no-footer__lnb">
							<li>
								<a href="<?=$cm_notice?>">공지사항</a>
							</li>
							<li>
								<a href="<?=$cm_faq?>">자주묻는질문</a>
							</li>
							<li>
								<a href="<?=$cm_policy?>">개인정보처리방침</a>
							</li>
							<li>
								<a href="<?=$cm_terms?>">이용약관</a>
							</li>
							<li>
								<a href="<?=$contact?>">고객문의</a>
							</li>
						</ul>
					</li>
				</ul>
			</nav>
		</div>
		<div class="no-footer__bottom">
			<ul class="no-footer__info">
				<li><?=$SITEINFO_FOOTER_NAME?></li>
				<li>주소 : <?=$SITEINFO_FOOTER_ADDRESS?></li>
				<li>대표 : <?=$SITEINFO_FOOTER_OWNER?></li>
				<li>개인정보관리책임자 : <?=$SITEINFO_FOOTER_CHARGER?></li>
				<li>사업자등록번호 : <?=$SITEINFO_FOOTER_SSN?></li>
				<li>통신판매신고번호 : <?=$SITEINFO_FOOTER_REG?></li>
			</ul>
			<div class="no-footer__etc">
				<p class="no-footer__copyright">Copyright &copy; MACOMAEX inc, All right reserved.</p>
				<ul class="no-footer__link">
				  <?php if (!empty($SITEINFO_FOOTER_FACEBOOK)): ?>
					<li>
					  <a href="<?=$SITEINFO_FOOTER_FACEBOOK?>" target="_blank">
						<i class="ri-facebook-circle-fill"></i>
					  </a>
					</li>
				  <?php endif; ?>
				  <?php if (!empty($SITEINFO_FOOTER_INSTA)): ?>
					<li>
					  <a href="<?=$SITEINFO_FOOTER_INSTA?>" target="_blank">
						<i class="ri-instagram-fill"></i>
					  </a>
					</li>
				  <?php endif; ?>
				  <?php if (!empty($SITEINFO_FOOTER_YOUTUBE)): ?>
					<li>
					  <a href="<?=$SITEINFO_FOOTER_YOUTUBE?>" target="_blank">
						<i class="ri-youtube-fill"></i>
					  </a>
					</li>
				  <?php endif; ?>
				  <?php if (!empty($SITEINFO_FOOTER_KAKAO)): ?>
					<li>
					  <a href="<?=$SITEINFO_FOOTER_KAKAO?>" target="_blank">
						<i class="ri-kakao-talk-fill"></i>
					  </a>
					</li>
				  <?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
</footer>

<iframe name="common_frame" width=0 height=0 frameborder=0 style="display:none;"></iframe>