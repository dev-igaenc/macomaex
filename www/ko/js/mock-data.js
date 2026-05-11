/**
 * mock-data.js — 매커멕스 공용 목업 데이터
 *
 * 단일 진실 공급원(Single Source of Truth): 모든 기업 데이터를 이 파일에서 관리합니다.
 * 자동 생성: node js/build-mock-data.js
 *
 * 노출 전역 변수
 *   window.MOCK_COMPANIES  — 기업 ID 키 객체 (company_detail / company_detail_ai 용)
 *   window.MOCK_CARDS      — 카드 배열 (company_list 용, 순서 보존)
 *
 * badge_venture 정규화
 *   'Y' | 'N'  (이전 코드의 '' / null / undefined 는 모두 'N' 으로 통일)
 */
(function(w) {
'use strict';

w.MOCK_COMPANIES = {
	"anjung-chem": {
		"id": "anjung-chem",
		"title": "(주)안정케미칼",
		"sub": "기타 기초 무기화학물질 제조업",
		"tags": [
			"화학원료",
			"정밀화학",
			"첨가제"
		],
		"score": 4.8,
		"grow": 3.6,
		"profit": 4,
		"stable": 4.9,
		"badge_growth": 15,
		"badge_stability": null,
		"badge_venture": "N",
		"rep": "김안정",
		"founded": "2008년",
		"employees": "48명",
		"biz_type": "제조업",
		"credit": "A-",
		"corp_type": "법인기업",
		"address": "경기 화성시 동탄산단8길 27, 3층 (반송동)",
		"product_line": "산업용 케미컬·정밀첨가제·맞춤 배합·납기·친환경 소재 컨설팅",
		"enp_sze": "중소기업",
		"bzno": "1138100234",
		"clients": [
			{
				"name": "삼성SDI",
				"cat": "전자소재"
			},
			{
				"name": "LG화학",
				"cat": "화학원료"
			},
			{
				"name": "한화솔루션",
				"cat": "정밀화학"
			}
		],
		"ai_analysis_html": "<h4>관계 요약</h4><ul><li>이가이엔씨는 실리콘오일·에멀젼·발수제 등 특수 화학 소재를 제조하고, 안정케미칼은 정밀화학·산업용 첨가제를 제조하는 동종 화학 업종 기업입니다.</li><li>양사 모두 소량 맞춤 배합과 B2B 납품 구조를 갖추고 있어 원료 공유, 공동 R&D, 보완적 제품 라인 확장이 가능한 핵심 파트너 관계입니다.</li></ul><h4>시너지 분석</h4><ul><li><strong>원료 공동 구매</strong> — 이가이엔씨의 실리콘 원료와 안정케미칼의 산업용 첨가제 원료는 공급처가 겹치므로 공동 구매 시 단가 절감 효과를 기대할 수 있습니다.</li><li><strong>보완적 제품 라인</strong> — 이가이엔씨의 발수제·접착 응용 제품에 안정케미칼의 정밀 첨가제를 배합하면 고부가 복합 제품 개발이 가능합니다.</li><li><strong>공동 R&D 및 인증</strong> — 양사 모두 REACH·화학물질 규제 대응 경험을 보유하고 있어, 친환경 신소재 공동 개발 시 인증 비용과 기간을 분담할 수 있습니다.</li></ul><h4>거래처로서의 가치</h4><ul><li>이가이엔씨가 자체 생산하지 않는 산업용 정밀 첨가제를 안정케미칼로부터 안정적으로 조달하여 제품 포트폴리오를 확대할 수 있습니다.</li><li>안정케미칼의 Lot 추적·성적서 자동 발급 등 디지털 품질 관리 체계를 활용하면 이가이엔씨의 납품 신뢰도 향상에 기여합니다.</li><li>경기 화성 소재 안정케미칼과 전북 전주 소재 이가이엔씨 간 물류 비용은 발생하나, 화학 원료 단가 절감분으로 상쇄가 가능합니다.</li></ul><h4>유의 사항</h4><ul><li>원자재 단가 변동에 따른 연동 단가 조항을 장기 계약 시 반드시 포함해야 합니다.</li><li>양사 배합 Know-how 및 레시피 보호를 위한 NDA 범위를 사전에 명확히 설정해야 합니다.</li><li>공동 개발 제품의 IP 귀속 및 샘플 제공 한도를 계약서에 구체적으로 명시해야 합니다.</li></ul><p class=\"ai-analysis-note\">※ 본 분석은 AI 추천 점수 및 양사 데이터를 기반으로 생성된 내용입니다. (시연용)</p>",
		"icon": "cat-iso-5",
		"region_insensitive": false
	},
	"sinroe-logistics": {
		"id": "sinroe-logistics",
		"title": "(주)신뢰물류",
		"sub": "기타 화물 운송 주선업",
		"tags": [
			"유통",
			"물류",
			"배송"
		],
		"score": 4.7,
		"grow": 3.4,
		"profit": 3.8,
		"stable": 4.8,
		"badge_growth": 30,
		"badge_stability": 5,
		"badge_venture": "N",
		"rep": "박물류",
		"founded": "2012년",
		"employees": "120명",
		"biz_type": "유통업",
		"credit": "BBB+",
		"corp_type": "법인기업",
		"address": "경기 이천시 마장면 서이천로 388, B동 (마장산업단지)",
		"product_line": "전국 창고·배송 대행·온도·위험물 보관·WMS 재고 가시화",
		"enp_sze": "중기업",
		"bzno": "2208500178",
		"clients": [
			{
				"name": "쿠팡로지스틱스",
				"cat": "물류대행"
			},
			{
				"name": "CJ대한통운",
				"cat": "배송"
			},
			{
				"name": "한진택배",
				"cat": "운송"
			}
		],
		"ai_analysis_html": "<h4>관계 요약</h4><ul><li>이가이엔씨는 실리콘오일·에멀젼·발수제 등 화학 제품을 제조하며, 신뢰물류는 B2B 물류·풀필먼트 전문 기업입니다.</li><li>화학 제품은 위험물·온도 관리가 필수적인 품목이 많아, 신뢰물류의 특수 보관·배송 인프라와 높은 거래도를 보입니다.</li></ul><h4>시너지 분석</h4><ul><li><strong>위험물 전문 물류</strong> — 이가이엔씨의 실리콘오일·에멀젼 제품은 위험물 분류에 해당할 수 있으며, 신뢰물류의 인증 구역 보관·전용 차량 배송으로 법규 준수와 안전성을 확보할 수 있습니다.</li><li><strong>전국 배송망 활용</strong> — 전북 전주에 위치한 이가이엔씨가 전국 고객사에 납품할 때, 신뢰물류의 창고 허브망을 활용하면 리드타임을 단축하고 물류비를 절감할 수 있습니다.</li><li><strong>재고 가시화 연동</strong> — 신뢰물류의 WMS 시스템과 이가이엔씨의 생산 관리를 연동하면 완제품 재고·출하 현황을 실시간 파악하여 납기 정확도를 높일 수 있습니다.</li></ul><h4>거래처로서의 가치</h4><ul><li>이가이엔씨가 자체 물류 인프라 없이도 전국 단위 B2B 납품 체계를 구축할 수 있어 고정비 부담을 줄일 수 있습니다.</li><li>소량 다품종 시험 생산 제품의 샘플 배송부터 양산 물량 정기 배송까지 단일 물류 파트너로 일원화가 가능합니다.</li><li>화학 제품 수출 시 신뢰물류의 포장·라벨 대응 서비스를 활용하면 해외 바이어 요구사항 충족이 용이합니다.</li></ul><h4>유의 사항</h4><ul><li>화학 제품 특성상 파손·누출 사고 시 책임 한도와 보상 범위를 계약서에 반드시 명시해야 합니다.</li><li>위험물 보관·운송 관련 인허가 유효 기간과 갱신 상태를 정기적으로 확인해야 합니다.</li><li>성수기 물량 급증 시 전용 보관 구역 확보 및 배차 우선순위를 사전에 합의해야 합니다.</li></ul><p class=\"ai-analysis-note\">※ 본 분석은 AI 추천 점수 및 양사 데이터를 기반으로 생성된 내용입니다. (시연용)</p>",
		"icon": "cat-iso-12",
		"region_insensitive": false
	},
	"solid-elec": {
		"id": "solid-elec",
		"title": "(주)솔리드전자",
		"sub": "기타 전자부품 제조업",
		"tags": [
			"전자부품",
			"반도체",
			"소재"
		],
		"score": 4.8,
		"grow": 3.5,
		"profit": 4.1,
		"stable": 4.85,
		"badge_growth": null,
		"badge_stability": null,
		"badge_venture": "N",
		"rep": "이전자",
		"founded": "2010년",
		"employees": "86명",
		"biz_type": "제조업",
		"credit": "A-",
		"corp_type": "법인기업",
		"address": "경기 안산시 단원구 산단로 85, 5층 (원시동)",
		"product_line": "PCB·전장 모듈 조립·소량 다품종 생산·신뢰성 시험",
		"enp_sze": "중소기업",
		"bzno": "1348200456",
		"clients": [
			{
				"name": "현대모비스",
				"cat": "자동차부품"
			},
			{
				"name": "삼성전기",
				"cat": "전자부품"
			},
			{
				"name": "LG이노텍",
				"cat": "모듈"
			}
		],
		"ai_analysis_html": "<h4>관계 요약</h4><ul><li>이가이엔씨는 실리콘오일·에멀젼·발수제를 제조하고, 솔리드전자는 PCB·전장 모듈 조립 및 소량 다품종 생산을 전문으로 합니다.</li><li>실리콘 소재는 PCB 방습 코팅, 전자부품 보호재, 열전도 충전재 등 전자 산업에서 핵심 원료로 사용되어 양사 간 직접적인 공급-수요 관계가 성립합니다.</li></ul><h4>시너지 분석</h4><ul><li><strong>PCB 방습·보호 코팅</strong> — 이가이엔씨의 실리콘 에멀젼·발수제를 솔리드전자의 PCB 제조 공정에 적용하면 방습·방진·절연 성능을 강화할 수 있습니다.</li><li><strong>소량 맞춤 생산 궁합</strong> — 양사 모두 소량 다품종 시험 생산에 강점이 있어, 신규 전자 제품 개발 단계에서 코팅재 레시피와 기판 설계를 동시에 최적화할 수 있습니다.</li><li><strong>신뢰성 공동 검증</strong> — 솔리드전자의 신뢰성 시험 설비와 이가이엔씨의 소재 배합 기술을 결합하면 내열·내습·내화학성 시험 데이터를 공동으로 축적할 수 있습니다.</li></ul><h4>거래처로서의 가치</h4><ul><li>이가이엔씨 입장에서 솔리드전자는 실리콘 코팅재·충전재의 안정적 수요처로, 전장 시장 성장에 따라 지속적 매출 확대가 기대됩니다.</li><li>솔리드전자의 완성차·전장 Tier 고객 네트워크를 통해 이가이엔씨 제품의 레퍼런스를 확보할 수 있습니다.</li><li>전자부품 방수·EMC 인증 요구에 이가이엔씨의 소재 성적서를 패키지로 제공하면 솔리드전자의 고객 승인 기간을 단축할 수 있습니다.</li></ul><h4>유의 사항</h4><ul><li>전자부품용 실리콘 소재는 순도·이온 불순물 기준이 엄격하므로 규격 적합성을 사전에 검증해야 합니다.</li><li>코팅 공정 조건(온도·습도·경화 시간)에 따른 품질 편차를 양사 간 표준 프로토콜로 관리해야 합니다.</li><li>부품 추적성 요구에 따라 소재 Lot 번호와 제조 이력 연동 체계를 구축해야 합니다.</li></ul><p class=\"ai-analysis-note\">※ 본 분석은 AI 추천 점수 및 양사 데이터를 기반으로 생성된 내용입니다. (시연용)</p>",
		"icon": "cat-iso-13",
		"region_insensitive": false
	},
	"anjung-mach": {
		"id": "anjung-mach",
		"title": "(주)안정기계",
		"sub": "기타 금속 가공 기계 제조업",
		"tags": [
			"기계부품",
			"정밀가공",
			"자동화"
		],
		"cat_slugs": ["auto_chassis"],
		"score": 4.7,
		"grow": 3.25,
		"profit": 3.9,
		"stable": 4.75,
		"badge_growth": 25,
		"badge_stability": null,
		"badge_venture": "N",
		"rep": "최기계",
		"founded": "2005년",
		"employees": "62명",
		"biz_type": "제조업",
		"credit": "BBB+",
		"corp_type": "법인기업",
		"address": "인천 남동구 남동서로 115, 202호 (남촌동, 남동공단)",
		"product_line": "CNC 가공·조립·설비 부품 긴급 대응·수출 포장·검수",
		"enp_sze": "중소기업",
		"bzno": "1318700392",
		"clients": [
			{
				"name": "두산공작기계",
				"cat": "설비부품"
			},
			{
				"name": "현대로보틱스",
				"cat": "자동화"
			},
			{
				"name": "LS산전",
				"cat": "전기부품"
			}
		],
		"ai_analysis_html": "<h4>관계 요약</h4><ul><li>이가이엔씨는 실리콘오일·에멀젼·발수제 전문 제조업체이고, 안정기계는 CNC 가공·산업용 부품·정밀가공 전문 기업입니다.</li><li>실리콘 오일은 산업 기계의 윤활유·절삭유·냉각제 원료로 활용되며, 안정기계의 가공 설비 운영과 직접적인 수요-공급 관계가 형성됩니다.</li></ul><h4>시너지 분석</h4><ul><li><strong>산업용 윤활·냉각제 공급</strong> — 이가이엔씨의 실리콘 오일 기반 윤활제·냉각제를 안정기계의 CNC 가공 라인에 적용하면 공구 수명 연장과 가공 정밀도 향상을 기대할 수 있습니다.</li><li><strong>이형제·방청 코팅 협력</strong> — 안정기계가 생산하는 금속 부품의 방청·이형 처리에 이가이엔씨의 실리콘 에멀젼을 활용하면 후처리 공정을 효율화할 수 있습니다.</li><li><strong>설비 부품 상호 연계</strong> — 이가이엔씨의 생산 설비 유지보수에 필요한 정밀 가공 부품을 안정기계에서 긴급 조달할 수 있어 생산 중단 리스크를 줄일 수 있습니다.</li></ul><h4>거래처로서의 가치</h4><ul><li>이가이엔씨 입장에서 안정기계는 자사 실리콘 윤활제·이형제의 산업 현장 적용 레퍼런스를 확보할 수 있는 실증 파트너입니다.</li><li>안정기계의 전국 중소 제조사 납품 네트워크를 통해 이가이엔씨 산업용 실리콘 제품의 판로를 간접적으로 확대할 수 있습니다.</li><li>안정기계의 야간·주말 긴급 대응 체계는 이가이엔씨 설비 부품 긴급 수급 시 큰 이점이 됩니다.</li></ul><h4>유의 사항</h4><ul><li>실리콘 윤활제의 CNC 장비 호환성(씰·패킹 소재와의 상성)을 사전에 반드시 검증해야 합니다.</li><li>가공유 교체 주기·폐유 처리 비용 등 운영 비용을 견적에 반영하여 총비용 기준으로 비교해야 합니다.</li><li>시험 적용 후 가공 품질 데이터를 일정 기간 모니터링하여 효과를 정량적으로 검증해야 합니다.</li></ul><p class=\"ai-analysis-note\">※ 본 분석은 AI 추천 점수 및 양사 데이터를 기반으로 생성된 내용입니다. (시연용)</p>",
		"icon": "cat-iso-6",
		"region_insensitive": false
	},
	"igain": {
		"id": "igain",
		"title": "(주)이가이엔씨",
		"sub": "기타 기초 유기화학물질 제조업",
		"tags": [
			"실리콘오일",
			"에멀젼",
			"첨가제"
		],
		"score": 4.6,
		"grow": 4.25,
		"profit": 3.9,
		"stable": 4.6,
		"badge_growth": null,
		"badge_stability": 15,
		"badge_venture": "N",
		"rep": "정소재",
		"founded": "2003년",
		"employees": "35명",
		"biz_type": "제조업",
		"credit": "BBB",
		"corp_type": "법인기업",
		"address": "전북 전주시 완산구 홍산중앙로 17, 602호 (효자동2가, 에이치하늘향빌딩)",
		"product_line": "실리콘 오일·에멀젼·발수·접착 응용·소량 시험 생산",
		"enp_sze": "소기업",
		"bzno": "1868101689",
		"clients": [
			{
				"name": "아모레퍼시픽",
				"cat": "화장품원료"
			},
			{
				"name": "KCC",
				"cat": "실리콘소재"
			},
			{
				"name": "동우화인켐",
				"cat": "정밀화학"
			}
		],
		"ai_analysis_html": "<h4>관계 요약</h4><ul><li>이가이엔씨는 실리콘오일·에멀젼·발수제·접착 응용 소재를 전문 제조하는 전북 전주 소재 기업으로, 본 페이지는 자사 프로필입니다.</li><li>건자재·코팅·세제·개인케어·식품·전자 등 폭넓은 응용처에 소량 시험 생산부터 양산까지 대응하는 B2B 맞춤형 소재 파트너입니다.</li></ul><h4>시너지 분석</h4><ul><li><strong>응용 다양성</strong> — 내후성·저VOC·식품 접촉 등 특정 응용별 실증 데이터를 축적하고 있어 다양한 산업군 고객과 기술 협력이 가능합니다.</li><li><strong>소량 시험 생산</strong> — 35명 규모의 민첩한 조직으로 레시피 튜닝·규격 적합성 검증을 빠르게 수행하여 대기업 납품 전 단계 지원에 강점을 보유하고 있습니다.</li><li><strong>R&D 협업 체계</strong> — 배합 Know-how 기반 NDA 공동 개발 체계를 구축하고 있어 장기 파트너십을 통한 맞춤 소재 개발이 가능합니다.</li></ul><h4>거래처로서의 가치</h4><ul><li>매커멕스 플랫폼 내 추천 기업들과의 협업을 통해 실리콘 소재의 응용 범위를 지속적으로 확장할 수 있습니다.</li><li>현장 적용 조건(온도·습도·피막 두께) 표준 프로토콜을 통해 고객 승인 기간을 단축하는 구조적 이점을 보유하고 있습니다.</li><li>SDS·규제 서류·친환경 인증 요구를 초기 단계에서 정리하여 거래 협상 속도를 높입니다.</li></ul><h4>유의 사항</h4><ul><li>원료 변동·환율에 따른 단가 연동 조항을 장기 계약 시 반드시 논의해야 합니다.</li><li>배합 비밀·Know-how 범위와 샘플 제공 한도를 NDA에 명확히 명시해야 합니다.</li><li>호환 실패 시 원인 분석 범위와 비용 분담을 사전에 합의해야 합니다.</li></ul><p class=\"ai-analysis-note\">※ 본 분석은 AI 추천 점수 및 양사 데이터를 기반으로 생성된 내용입니다. (시연용)</p>",
		"icon": "cat-iso-5",
		"region_insensitive": false
	},
	"hankook-precision": {
		"id": "hankook-precision",
		"title": "(주)한국정밀기계",
		"sub": "금형 및 주형 제조업",
		"tags": [
			"금형부품",
			"자동차부품",
			"정밀가공"
		],
		"cat_slugs": ["auto_chassis"],
		"score": 4.6,
		"grow": 4.25,
		"profit": 3.9,
		"stable": 4.6,
		"badge_growth": null,
		"badge_stability": 5,
		"badge_venture": "N",
		"rep": "한정밀",
		"founded": "1999년",
		"employees": "210명",
		"biz_type": "제조업",
		"credit": "A-",
		"corp_type": "법인기업",
		"address": "경남 창원시 성산구 창원대로 812, 4층 (상남동)",
		"product_line": "금형 설계·가공·자동차·전자 부품 납품·품질 트레이싱",
		"enp_sze": "중기업",
		"bzno": "6098200715",
		"clients": [
			{
				"name": "현대자동차",
				"cat": "자동차부품"
			},
			{
				"name": "기아",
				"cat": "전장부품"
			},
			{
				"name": "삼성전자",
				"cat": "정밀부품"
			}
		],
		"ai_analysis_html": "<h4>관계 요약</h4><ul><li>이가이엔씨는 실리콘오일·에멀젼·발수제 전문 제조업체이고, 한국정밀기계는 금형 설계·가공 및 자동차·전자 부품 납품 전문 기업입니다.</li><li>금형 이형제·정밀 부품 코팅·방청 처리에 실리콘 소재가 핵심적으로 사용되어 양사 간 원료 공급-소비 관계가 직접적으로 성립합니다.</li></ul><h4>시너지 분석</h4><ul><li><strong>금형 이형제 공급</strong> — 이가이엔씨의 실리콘 오일 기반 이형제를 한국정밀기계의 금형 가공 공정에 적용하면 금형 수명 연장과 제품 표면 품질 향상을 동시에 달성할 수 있습니다.</li><li><strong>정밀 부품 보호 코팅</strong> — 한국정밀기계가 생산하는 자동차·전자 부품에 이가이엔씨의 발수·방습 코팅재를 적용하여 내환경성을 강화할 수 있습니다.</li><li><strong>대량 안정 수요</strong> — 한국정밀기계는 210명 규모의 대형 제조사로 이가이엔씨에 실리콘 이형제·코팅재의 안정적이고 반복적인 대량 수요를 제공할 수 있습니다.</li></ul><h4>거래처로서의 가치</h4><ul><li>이가이엔씨 입장에서 한국정밀기계는 자동차·전자 부품 분야 대형 레퍼런스를 확보할 수 있는 전략적 거래처입니다.</li><li>한국정밀기계의 OEM·Tier1 고객 네트워크를 통해 이가이엔씨 이형제·코팅재의 간접 홍보 효과를 얻을 수 있습니다.</li><li>한국정밀기계의 품질 트레이싱 체계와 연동하면 이가이엔씨 소재의 현장 성능 데이터를 축적하여 제품 개선에 활용할 수 있습니다.</li></ul><h4>유의 사항</h4><ul><li>자동차 부품용 이형제는 IATF 등 고객사 특수 규격 적합성을 사전에 확인해야 합니다.</li><li>금형 소재별(SKD, SKH 등) 이형제 호환성이 다를 수 있으므로 적용 시험을 거쳐야 합니다.</li><li>대량 납품 시 단가 연동 조항과 물량 커밋 조건을 장기 계약에 명시해야 합니다.</li></ul><p class=\"ai-analysis-note\">※ 본 분석은 AI 추천 점수 및 양사 데이터를 기반으로 생성된 내용입니다. (시연용)</p>",
		"icon": "cat-iso-6",
		"region_insensitive": false
	},
	"green-packaging": {
		"id": "green-packaging",
		"title": "(주)그린패키징",
		"sub": "골판지 및 골판지 상자 제조업",
		"tags": [
			"친환경 포장재",
			"완충재",
			"필름"
		],
		"score": 4.6,
		"grow": 4.25,
		"profit": 3.9,
		"stable": 4.6,
		"badge_growth": 25,
		"badge_stability": 25,
		"badge_venture": "N",
		"rep": "서포장",
		"founded": "2016년",
		"employees": "42명",
		"biz_type": "제조업",
		"credit": "BBB+",
		"corp_type": "법인기업",
		"address": "충남 천안시 서북구 직산읍 삼은리 산업로 42",
		"product_line": "완충·필름 맞춤·재생 펄프 박스·브랜드 패키징 시안",
		"enp_sze": "소기업",
		"bzno": "3128400562",
		"clients": [
			{
				"name": "올리브영",
				"cat": "리테일"
			},
			{
				"name": "아모레퍼시픽",
				"cat": "화장품"
			},
			{
				"name": "CJ제일제당",
				"cat": "식품"
			}
		],
		"ai_analysis_html": "<h4>관계 요약</h4><ul><li>이가이엔씨는 실리콘오일·에멀젼·발수제 전문 제조업체이고, 그린패키징은 친환경 포장재(완충·필름·재생 펄프 박스) 전문 기업입니다.</li><li>포장재의 방습·발수 코팅에 실리콘 에멀젼이 활용되어 양사 간 소재 공급-가공 협력 관계가 성립하며, 친환경 트렌드에서 공동 성장이 가능합니다.</li></ul><h4>시너지 분석</h4><ul><li><strong>포장재 방습·발수 코팅</strong> — 이가이엔씨의 실리콘 에멀젼·발수제를 그린패키징의 펄프 박스·필름에 코팅 적용하면 습기 차단 성능을 높여 식품·화학 제품 포장 품질을 강화할 수 있습니다.</li><li><strong>친환경 소재 공동 개발</strong> — 양사 모두 친환경 제품에 주력하고 있어, 생분해성 실리콘 코팅재와 재생 포장재를 결합한 차세대 친환경 패키징 솔루션을 공동 개발할 수 있습니다.</li><li><strong>이가이엔씨 제품 포장 개선</strong> — 그린패키징이 이가이엔씨 화학 제품의 출하 포장재를 맞춤 설계하여 운송 중 파손·누출 리스크를 줄이고 ESG 공시에 부합하는 포장 체계를 구축할 수 있습니다.</li></ul><h4>거래처로서의 가치</h4><ul><li>이가이엔씨 입장에서 그린패키징은 실리콘 코팅재의 포장 산업 내 응용처를 확대할 수 있는 신규 시장 개척 파트너입니다.</li><li>그린패키징의 FSC·LCA 인증 역량과 연계하면 이가이엔씨 코팅재의 친환경 인증 데이터를 공동으로 확보할 수 있습니다.</li><li>그린패키징을 통해 이가이엔씨 자체 제품의 출하 포장을 개선하면 고객사 인상 관리와 물류 비용 절감을 동시에 달성할 수 있습니다.</li></ul><h4>유의 사항</h4><ul><li>식품 접촉 포장재에 적용할 경우 실리콘 코팅재의 식품 안전 기준(식약처·FDA) 적합성을 반드시 확인해야 합니다.</li><li>코팅 공정 추가에 따른 포장재 원가 상승분과 성능 개선 효과를 정량적으로 비교 검증해야 합니다.</li><li>재생 소재와 실리콘 코팅의 재활용 호환성(분리배출 가능 여부)을 사전에 검토해야 합니다.</li></ul><p class=\"ai-analysis-note\">※ 본 분석은 AI 추천 점수 및 양사 데이터를 기반으로 생성된 내용입니다. (시연용)</p>",
		"icon": "cat-iso-5",
		"region_insensitive": false
	},
	"global-logistics": {
		"id": "global-logistics",
		"title": "(주)글로벌로지스틱스",
		"sub": "관세사 및 통관 대행업",
		"tags": [
			"물류대행",
			"창고보관",
			"통관"
		],
		"score": 4.6,
		"grow": 4.25,
		"profit": 3.9,
		"stable": 4.6,
		"badge_growth": null,
		"badge_stability": 10,
		"badge_venture": "Y",
		"rep": "강통관",
		"founded": "2007년",
		"employees": "95명",
		"biz_type": "유통업",
		"credit": "BBB+",
		"corp_type": "법인기업",
		"address": "서울 강서구 마곡중앙로 161-8, 7층 (마곡동, 보타닉파크타워)",
		"product_line": "수출입 통관·보세·일반 창고·해상·항공 복합 배송",
		"enp_sze": "중소기업",
		"bzno": "1078600843",
		"clients": [
			{
				"name": "한국무역협회",
				"cat": "수출입"
			},
			{
				"name": "현대글로비스",
				"cat": "물류"
			},
			{
				"name": "삼성물산",
				"cat": "통관"
			}
		],
		"ai_analysis_html": "<h4>관계 요약</h4><ul><li>이가이엔씨는 실리콘오일·에멀젼·발수제 전문 제조업체이고, 글로벌로지스틱스는 수출입 통관·보세·해상·항공 복합 물류 전문 기업입니다.</li><li>실리콘 화학 제품의 해외 수출 시 화학물질 통관·위험물 운송·목적국 규제 대응이 필수적이므로, 글로벌로지스틱스의 전문 역량과 높은 거래도를 보입니다.</li></ul><h4>시너지 분석</h4><ul><li><strong>화학품 수출 통관 전문성</strong> — 글로벌로지스틱스의 화학품 특수 통관 창구를 활용하면 이가이엔씨의 실리콘 제품 수출 시 HS 코드 분류·SDS 서류·목적국 화학물질 등록 절차를 효율적으로 처리할 수 있습니다.</li><li><strong>위험물 국제 운송</strong> — 실리콘오일·에멀젼 일부 품목은 위험물 분류에 해당할 수 있으며, 글로벌로지스틱스의 위험물 해상·항공 운송 네트워크를 통해 안전하고 합법적인 국제 배송이 가능합니다.</li><li><strong>FTA 원산지 활용</strong> — 글로벌로지스틱스의 FTA 원산지 증빙 서비스를 활용하면 이가이엔씨 제품의 수출 관세를 절감하고 가격 경쟁력을 높일 수 있습니다.</li></ul><h4>거래처로서의 가치</h4><ul><li>이가이엔씨가 해외 시장 진출을 확대할 때 통관·물류·보세 창고를 원스톱으로 위탁하여 자체 수출 인프라 구축 부담을 줄일 수 있습니다.</li><li>글로벌로지스틱스의 동남아·북미 현지 포워딩 네트워크를 통해 이가이엔씨의 해외 바이어 납기 신뢰도를 높일 수 있습니다.</li><li>벤처 인증 기업인 글로벌로지스틱스와의 장기 계약 시 공공 조달·정책 자금 활용에 가점을 받을 수 있습니다.</li></ul><h4>유의 사항</h4><ul><li>화학 제품 국제 운송 시 Incoterms·보험 범위·검사·검역 대행 범위를 계약서에 명확히 반영해야 합니다.</li><li>목적국별 화학물질 규제(REACH, TSCA 등) 변경 시 통관 지연 리스크에 대한 책임 소재를 사전에 합의해야 합니다.</li><li>환율·유가 변동에 따른 운임 조정 주기와 기준을 분기별로 점검해야 합니다.</li></ul><p class=\"ai-analysis-note\">※ 본 분석은 AI 추천 점수 및 양사 데이터를 기반으로 생성된 내용입니다. (시연용)</p>",
		"icon": "cat-iso-12",
		"region_insensitive": false
	},
	"cloudbridge": {
		"id": "cloudbridge",
		"title": "(주)클라우드브릿지",
		"sub": "컴퓨터 시스템 통합 자문 및 구축 서비스업",
		"tags": [
			"클라우드",
			"SaaS",
			"IT컨설팅",
			"데이터분석"
		],
		"score": 4.7,
		"grow": 4.4,
		"profit": 4.1,
		"stable": 4.5,
		"badge_growth": 8,
		"badge_stability": null,
		"badge_venture": "Y",
		"rep": "윤클라우드",
		"founded": "2018년",
		"employees": "72명",
		"biz_type": "IT서비스업",
		"credit": "A-",
		"corp_type": "법인기업",
		"address": "서울 강남구 테헤란로 427, 12층 (삼성동, 위워크타워)",
		"product_line": "클라우드 인프라·SaaS 구축·데이터 분석·IT 컨설팅·보안 솔루션",
		"enp_sze": "중소기업",
		"bzno": "2208100967",
		"clients": [
			{
				"name": "네이버클라우드",
				"cat": "클라우드"
			},
			{
				"name": "카카오엔터프라이즈",
				"cat": "SaaS"
			},
			{
				"name": "SK C&C",
				"cat": "IT서비스"
			}
		],
		"ai_analysis_html": "<h4>관계 요약</h4><ul><li>이가이엔씨는 실리콘오일·에멀젼·발수제 전문 제조업체이고, 클라우드브릿지는 IT서비스·클라우드·SaaS 솔루션 전문 기업입니다.</li><li>이가이엔씨의 생산 관리·배합 레시피·재고·품질 데이터를 디지털화하는 데 클라우드브릿지의 SaaS·데이터 분석 역량이 직접적으로 기여할 수 있는 관계입니다.</li></ul><h4>시너지 분석</h4><ul><li><strong>생산 관리 SaaS 도입</strong> — 클라우드브릿지의 클라우드 기반 생산 관리 시스템을 이가이엔씨에 도입하면 배합 레시피 관리·Lot 추적·재고 현황을 실시간으로 파악할 수 있습니다.</li><li><strong>데이터 기반 품질 개선</strong> — 이가이엔씨의 시험 생산 데이터·품질 검사 결과를 클라우드브릿지의 데이터 분석 플랫폼에서 분석하면 불량률 감소와 배합 최적화에 활용할 수 있습니다.</li><li><strong>디지털 전환 파트너십</strong> — 35명 규모의 이가이엔씨에 맞는 경량화된 SaaS 구독 모델로 초기 IT 투자 부담 없이 단계적 디지털 전환을 추진할 수 있습니다.</li></ul><h4>거래처로서의 가치</h4><ul><li>이가이엔씨가 클라우드브릿지를 통해 생산·품질 관리를 디지털화하면 대기업 고객사의 공급망 연동 요구에 체계적으로 대응할 수 있습니다.</li><li>클라우드브릿지의 보안 솔루션을 활용하면 이가이엔씨의 배합 Know-how와 고객사 NDA 데이터를 안전하게 관리할 수 있습니다.</li><li>원격 서비스 제공이 가능하므로 전북 전주에 위치한 이가이엔씨도 서울 소재 클라우드브릿지의 서비스를 지역 제약 없이 이용할 수 있습니다.</li></ul><h4>유의 사항</h4><ul><li>화학 배합 데이터·레시피 등 핵심 영업비밀의 클라우드 저장 시 데이터 주권·암호화·접근 권한 정책을 철저히 수립해야 합니다.</li><li>SaaS 구독 해지 시 데이터 반환 절차와 형식을 계약 단계에서 명확히 합의해야 합니다.</li><li>기존 생산 관리 방식에서 SaaS 전환 시 현장 직원 교육·적응 기간을 충분히 확보해야 합니다.</li></ul><p class=\"ai-analysis-note\">※ 본 분석은 AI 추천 점수 및 양사 데이터를 기반으로 생성된 내용입니다. (시연용)</p>",
		"icon": "cat-iso-14",
		"region_insensitive": true
	},
	"daewon-steel": {
		"id": "daewon-steel",
		"title": "(주)대원스틸",
		"sub": "기타 1차 철강 제조업",
		"tags": [
			"철강",
			"봉강",
			"판재",
			"형강"
		],
		"cat_slugs": ["auto_chassis"],
		"score": 4.5,
		"grow": 3.8,
		"profit": 3.7,
		"stable": 4.7,
		"badge_growth": 20,
		"badge_stability": null,
		"badge_venture": "N",
		"rep": "김대원",
		"founded": "2001년",
		"employees": "95명",
		"biz_type": "제조업",
		"credit": "A-",
		"corp_type": "법인기업",
		"address": "경남 창원시 의창구 창원대로 18번길 52 (팔용동)",
		"product_line": "철강 봉강·판재·형강 가공·납품·절단·규격 맞춤",
		"enp_sze": "",
		"bzno": "",
		"clients": null,
		"ai_analysis_html": "<h4>관계 요약</h4><ul><li>이가이엔씨는 실리콘오일·에멀젼·발수제 전문 제조업체이고, 대원스틸은 철강·봉강·판재·형강 가공 및 납품 전문 기업입니다.</li><li>철강 가공 공정에서 방청유·절삭유·이형제 등 실리콘 기반 소재가 필수적으로 사용되어 양사 간 원료 공급-소비 관계가 성립합니다.</li></ul><h4>시너지 분석</h4><ul><li><strong>방청·윤활유 공급</strong> — 이가이엔씨의 실리콘 오일 기반 방청유를 대원스틸의 철강 제품 보관·운송 과정에 적용하면 녹 발생을 줄이고 제품 품질을 유지할 수 있습니다.</li><li><strong>절삭·가공유 협력</strong> — 대원스틸의 절단·가공 라인에 이가이엔씨의 실리콘 에멀젼 기반 절삭유를 적용하여 공구 수명 연장과 가공면 품질 향상을 기대할 수 있습니다.</li><li><strong>설비 부품 상호 연계</strong> — 이가이엔씨의 생산 설비에 필요한 철강 구조물·부품을 대원스틸에서 맞춤 가공하여 조달할 수 있습니다.</li></ul><h4>거래처로서의 가치</h4><ul><li>대원스틸은 95명 규모의 안정적 제조사로, 실리콘 방청유·가공유의 지속적 수요처가 될 수 있습니다.</li><li>대원스틸의 다양한 산업군 납품 네트워크를 통해 이가이엔씨 제품의 간접 레퍼런스를 확보할 수 있습니다.</li></ul><h4>유의 사항</h4><ul><li>철강 소재별 방청유 호환성을 사전에 검증해야 합니다.</li><li>대량 납품 시 단가 연동 조항과 납기 조건을 장기 계약에 명시해야 합니다.</li></ul><p class=\"ai-analysis-note\">※ 본 분석은 AI 추천 점수 및 양사 데이터를 기반으로 생성된 내용입니다. (시연용)</p>",
		"icon": "cat-iso-7",
		"region_insensitive": false
	},
	"beauty-factory": {
		"id": "beauty-factory",
		"title": "(주)뷰티팩토리",
		"sub": "화장품 제조업",
		"tags": [
			"ODM",
			"OEM",
			"화장품"
		],
		"score": 4.7,
		"grow": 3.9,
		"profit": 4.1,
		"stable": 4.5,
		"badge_growth": null,
		"badge_stability": 10,
		"badge_venture": "N",
		"rep": "이진우",
		"founded": "2015년",
		"employees": "58명",
		"biz_type": "제조업",
		"credit": "BBB+",
		"corp_type": "개인사업자",
		"address": "경기 성남시 중원구 사기막골로 124, 8층 (상대원동)",
		"product_line": "플라스틱·글라스 화장품 용기 제조",
		"enp_sze": "소기업",
		"bzno": "1298500321",
		"clients": [
			{
				"name": "아모레퍼시픽",
				"cat": "화장품"
			},
			{
				"name": "LG생활건강",
				"cat": "뷰티"
			},
			{
				"name": "에이블씨엔씨",
				"cat": "ODM"
			}
		],
		"ai_analysis_html": "<h4>관계 요약</h4><ul><li>이가이엔씨는 실리콘오일·에멀젼·발수제 전문 제조업체이고, 뷰티팩토리는 화장품 ODM·OEM 및 용기 제조 전문 기업입니다.</li><li>실리콘 에멀젼은 화장품 포뮬레이션의 핵심 원료(피부 보호막 형성·사용감 개선·유화 안정)로 양사 간 직접적인 원료 공급-제품 제조 관계가 성립합니다.</li></ul><h4>시너지 분석</h4><ul><li><strong>화장품용 실리콘 에멀젼 공급</strong> — 이가이엔씨의 실리콘 에멀젼을 뷰티팩토리의 스킨케어·메이크업 포뮬레이션에 적용하면 제품 사용감·발림성·피부 보호 성능을 높일 수 있습니다.</li><li><strong>맞춤 원료 공동 개발</strong> — 양사 모두 소량 시험 생산에 강점이 있어, 신규 화장품 라인에 최적화된 실리콘 에멀젼 레시피를 공동 개발하고 빠르게 시제품을 제작할 수 있습니다.</li><li><strong>수출 규제 공동 대응</strong> — 뷰티팩토리의 EU CPNP·미국 FDA 규제 대응 경험과 이가이엔씨의 화학물질 등록·SDS 작성 역량을 결합하면 해외 시장 진출 시 인허가 비용과 기간을 절감할 수 있습니다.</li></ul><h4>거래처로서의 가치</h4><ul><li>이가이엔씨 입장에서 뷰티팩토리는 화장품 원료 시장 진입의 핵심 레퍼런스 거래처로, K-뷰티 성장세에 따라 안정적 매출 확대가 기대됩니다.</li><li>뷰티팩토리의 다수 브랜드 고객(ODM·OEM)을 통해 이가이엔씨 실리콘 에멀젼이 다양한 화장품에 적용되어 간접 마케팅 효과를 얻을 수 있습니다.</li><li>용기 내면 코팅에 이가이엔씨의 실리콘 소재를 활용하면 내용물 안정성 향상이라는 추가 협력 기회가 생깁니다.</li></ul><h4>유의 사항</h4><ul><li>화장품용 실리콘 원료는 화장품 안전 기준(식약처 고시·INCI 성분 등록)에 적합해야 하므로 사전 인증을 반드시 완료해야 합니다.</li><li>내용물과 실리콘 에멀젼의 호환성(안정성·변색·변취) 테스트를 초기 샘플 단계에서 철저히 수행해야 합니다.</li><li>원료 가격 변동·MOQ 조건·리오더 리드타임을 장기 계약에 명확히 명시해야 합니다.</li></ul><p class=\"ai-analysis-note\">※ 본 분석은 AI 추천 점수 및 양사 데이터를 기반으로 생성된 내용입니다. (시연용)</p>",
		"icon": "cat-iso-3",
		"region_insensitive": false
	},
	"samhwa-chem": {
		"id": "samhwa-chem",
		"title": "(주)삼화케미컬",
		"sub": "",
		"tags": [
			"수처리",
			"산업용가스",
			"첨가제",
			"원료"
		],
		"score": 4.5,
		"grow": 3.9,
		"profit": 3.6,
		"stable": 4.5,
		"badge_growth": null,
		"badge_stability": 20,
		"badge_venture": "N",
		"rep": "",
		"founded": "",
		"employees": "",
		"biz_type": "",
		"credit": "",
		"corp_type": "",
		"address": "",
		"product_line": "",
		"enp_sze": "",
		"bzno": "",
		"clients": null,
		"ai_analysis_html": "",
		"icon": "cat-iso-5",
		"region_insensitive": false
	},
	"nexgen-semi": {
		"id": "nexgen-semi",
		"title": "(주)넥스젠반도체",
		"sub": "",
		"tags": [
			"반도체",
			"PCB",
			"전자소재",
			"센서"
		],
		"score": 4.7,
		"grow": 4.3,
		"profit": 4,
		"stable": 4.4,
		"badge_growth": 10,
		"badge_stability": null,
		"badge_venture": "Y",
		"rep": "",
		"founded": "",
		"employees": "",
		"biz_type": "",
		"credit": "",
		"corp_type": "",
		"address": "",
		"product_line": "",
		"enp_sze": "",
		"bzno": "",
		"clients": null,
		"ai_analysis_html": "",
		"icon": "cat-iso-10",
		"region_insensitive": false
	},
	"hyundai-auto": {
		"id": "hyundai-auto",
		"title": "(주)현대오토파츠",
		"sub": "",
		"tags": [
			"자동차부품",
			"전장",
			"섀시",
			"모빌리티"
		],
		"cat_slugs": ["auto_chassis"],
		"score": 4.6,
		"grow": 3.7,
		"profit": 4.2,
		"stable": 4.6,
		"badge_growth": null,
		"badge_stability": 15,
		"badge_venture": "N",
		"rep": "",
		"founded": "",
		"employees": "",
		"biz_type": "",
		"credit": "",
		"corp_type": "",
		"address": "",
		"product_line": "",
		"enp_sze": "",
		"bzno": "",
		"clients": null,
		"ai_analysis_html": "",
		"icon": "cat-iso-11",
		"region_insensitive": false
	},
	"fresh-food": {
		"id": "fresh-food",
		"title": "(주)프레시푸드",
		"sub": "",
		"tags": [
			"식자재",
			"가공식품",
			"OEM",
			"납품"
		],
		"score": 4.4,
		"grow": 4,
		"profit": 3.5,
		"stable": 4.3,
		"badge_growth": null,
		"badge_stability": null,
		"badge_venture": "N",
		"rep": "",
		"founded": "",
		"employees": "",
		"biz_type": "",
		"credit": "",
		"corp_type": "",
		"address": "",
		"product_line": "",
		"enp_sze": "",
		"bzno": "",
		"clients": null,
		"ai_analysis_html": "",
		"icon": "cat-iso-4",
		"region_insensitive": false
	},
	"medplus": {
		"id": "medplus",
		"title": "(주)메드플러스",
		"sub": "",
		"tags": [
			"의료기기",
			"소모품",
			"위생용품",
			"방역"
		],
		"score": 4.5,
		"grow": 3.6,
		"profit": 3.8,
		"stable": 4.8,
		"badge_growth": null,
		"badge_stability": 8,
		"badge_venture": "N",
		"rep": "",
		"founded": "",
		"employees": "",
		"biz_type": "",
		"credit": "",
		"corp_type": "",
		"address": "",
		"product_line": "",
		"enp_sze": "",
		"bzno": "",
		"clients": null,
		"ai_analysis_html": "",
		"icon": "cat-iso-17",
		"region_insensitive": false
	},
	"woori-pack": {
		"id": "woori-pack",
		"title": "(주)우리패킹",
		"sub": "",
		"tags": [
			"포장재",
			"라벨",
			"인쇄",
			"박스"
		],
		"score": 4.3,
		"grow": 3.5,
		"profit": 3.7,
		"stable": 4.5,
		"badge_growth": 35,
		"badge_stability": null,
		"badge_venture": "N",
		"rep": "",
		"founded": "",
		"employees": "",
		"biz_type": "",
		"credit": "",
		"corp_type": "",
		"address": "",
		"product_line": "",
		"enp_sze": "",
		"bzno": "",
		"clients": null,
		"ai_analysis_html": "",
		"icon": "cat-iso-12",
		"region_insensitive": false
	},
	"dongwon-metal": {
		"id": "dongwon-metal",
		"title": "(주)동원금속",
		"sub": "",
		"tags": [
			"합금",
			"특수금속",
			"표면처리",
			"열처리"
		],
		"cat_slugs": ["auto_chassis"],
		"score": 4.6,
		"grow": 3.4,
		"profit": 4,
		"stable": 4.7,
		"badge_growth": 12,
		"badge_stability": 10,
		"badge_venture": "N",
		"rep": "",
		"founded": "",
		"employees": "",
		"biz_type": "",
		"credit": "",
		"corp_type": "",
		"address": "",
		"product_line": "",
		"enp_sze": "",
		"bzno": "",
		"clients": null,
		"ai_analysis_html": "",
		"icon": "cat-iso-7",
		"region_insensitive": false
	},
	"smartfactory": {
		"id": "smartfactory",
		"title": "(주)스마트팩토리",
		"sub": "",
		"tags": [
			"자동화",
			"로봇설비",
			"생산라인",
			"IoT"
		],
		"score": 4.8,
		"grow": 4.5,
		"profit": 4.2,
		"stable": 4.3,
		"badge_growth": 5,
		"badge_stability": null,
		"badge_venture": "Y",
		"rep": "",
		"founded": "",
		"employees": "",
		"biz_type": "",
		"credit": "",
		"corp_type": "",
		"address": "",
		"product_line": "",
		"enp_sze": "",
		"bzno": "",
		"clients": null,
		"ai_analysis_html": "",
		"icon": "cat-iso-6",
		"region_insensitive": false
	}
};

w.MOCK_CARDS = [
	{
		"id": "anjung-chem",
		"title": "(주)안정케미칼",
		"company": "(주)안정케미칼",
		"tags": [
			"화학원료",
			"정밀화학",
			"첨가제"
		],
		"score": "4.8",
		"grow": 3.6,
		"profit": 4,
		"stable": 4.9,
		"badge_growth": 15,
		"badge_stability": null,
		"badge_venture": "N",
		"icon": "cat-iso-5",
		"region_insensitive": false
	},
	{
		"id": "sinroe-logistics",
		"title": "(주)신뢰물류",
		"company": "(주)신뢰물류",
		"tags": [
			"유통",
			"물류",
			"배송"
		],
		"score": "4.7",
		"grow": 3.4,
		"profit": 3.8,
		"stable": 4.8,
		"badge_growth": 30,
		"badge_stability": 5,
		"badge_venture": "N",
		"icon": "cat-iso-12",
		"region_insensitive": false
	},
	{
		"id": "solid-elec",
		"title": "(주)솔리드전자",
		"company": "(주)솔리드전자",
		"tags": [
			"전자부품",
			"반도체",
			"소재"
		],
		"score": "4.8",
		"grow": 3.5,
		"profit": 4.1,
		"stable": 4.85,
		"badge_growth": null,
		"badge_stability": null,
		"badge_venture": "N",
		"icon": "cat-iso-13",
		"region_insensitive": false
	},
	{
		"id": "anjung-mach",
		"title": "(주)안정기계",
		"company": "(주)안정기계",
		"tags": [
			"기계부품",
			"정밀가공",
			"자동화"
		],
		"score": "4.7",
		"grow": 3.25,
		"profit": 3.9,
		"stable": 4.75,
		"badge_growth": 25,
		"badge_stability": null,
		"badge_venture": "N",
		"icon": "cat-iso-6",
		"region_insensitive": false
	},
	{
		"id": "igain",
		"title": "(주)이가이엔씨",
		"company": "(주)이가이엔씨",
		"tags": [
			"실리콘오일",
			"에멀젼",
			"첨가제"
		],
		"score": "4.6",
		"grow": 4.25,
		"profit": 3.9,
		"stable": 4.6,
		"badge_growth": null,
		"badge_stability": 15,
		"badge_venture": "N",
		"icon": "cat-iso-5",
		"region_insensitive": false
	},
	{
		"id": "hankook-precision",
		"title": "(주)한국정밀기계",
		"company": "(주)한국정밀기계",
		"tags": [
			"금형부품",
			"자동차부품",
			"정밀가공"
		],
		"score": "4.6",
		"grow": 4.25,
		"profit": 3.9,
		"stable": 4.6,
		"badge_growth": null,
		"badge_stability": 5,
		"badge_venture": "N",
		"icon": "cat-iso-6",
		"region_insensitive": false
	},
	{
		"id": "green-packaging",
		"title": "(주)그린패키징",
		"company": "(주)그린패키징",
		"tags": [
			"친환경 포장재",
			"완충재",
			"필름"
		],
		"score": "4.6",
		"grow": 4.25,
		"profit": 3.9,
		"stable": 4.6,
		"badge_growth": 25,
		"badge_stability": 25,
		"badge_venture": "N",
		"icon": "cat-iso-5",
		"region_insensitive": false
	},
	{
		"id": "global-logistics",
		"title": "(주)글로벌로지스틱스",
		"company": "(주)글로벌로지스틱스",
		"tags": [
			"물류대행",
			"창고보관",
			"통관"
		],
		"score": "4.6",
		"grow": 4.25,
		"profit": 3.9,
		"stable": 4.6,
		"badge_growth": null,
		"badge_stability": 10,
		"badge_venture": "Y",
		"icon": "cat-iso-12",
		"region_insensitive": false
	},
	{
		"id": "beauty-factory",
		"title": "(주)뷰티팩토리",
		"company": "(주)뷰티팩토리",
		"tags": [
			"ODM",
			"OEM",
			"화장품"
		],
		"score": "4.7",
		"grow": 3.9,
		"profit": 4.1,
		"stable": 4.5,
		"badge_growth": null,
		"badge_stability": 10,
		"badge_venture": "N",
		"icon": "cat-iso-3",
		"region_insensitive": false
	},
	{
		"id": "daewon-steel",
		"title": "(주)대원스틸",
		"company": "(주)대원스틸",
		"tags": [
			"철강",
			"봉강",
			"판재",
			"형강"
		],
		"score": "4.5",
		"grow": 3.8,
		"profit": 3.7,
		"stable": 4.7,
		"badge_growth": 20,
		"badge_stability": null,
		"badge_venture": "N",
		"icon": "cat-iso-7",
		"region_insensitive": false
	},
	{
		"id": "samhwa-chem",
		"title": "(주)삼화케미컬",
		"company": "(주)삼화케미컬",
		"tags": [
			"수처리",
			"산업용가스",
			"첨가제",
			"원료"
		],
		"score": "4.5",
		"grow": 3.9,
		"profit": 3.6,
		"stable": 4.5,
		"badge_growth": null,
		"badge_stability": 20,
		"badge_venture": "N",
		"icon": "cat-iso-5",
		"region_insensitive": false
	},
	{
		"id": "nexgen-semi",
		"title": "(주)넥스젠반도체",
		"company": "(주)넥스젠반도체",
		"tags": [
			"반도체",
			"PCB",
			"전자소재",
			"센서"
		],
		"score": "4.7",
		"grow": 4.3,
		"profit": 4,
		"stable": 4.4,
		"badge_growth": 10,
		"badge_stability": null,
		"badge_venture": "Y",
		"icon": "cat-iso-10",
		"region_insensitive": false
	},
	{
		"id": "hyundai-auto",
		"title": "(주)현대오토파츠",
		"company": "(주)현대오토파츠",
		"tags": [
			"자동차부품",
			"전장",
			"섀시",
			"모빌리티"
		],
		"score": "4.6",
		"grow": 3.7,
		"profit": 4.2,
		"stable": 4.6,
		"badge_growth": null,
		"badge_stability": 15,
		"badge_venture": "N",
		"icon": "cat-iso-11",
		"region_insensitive": false
	},
	{
		"id": "fresh-food",
		"title": "(주)프레시푸드",
		"company": "(주)프레시푸드",
		"tags": [
			"식자재",
			"가공식품",
			"OEM",
			"납품"
		],
		"score": "4.4",
		"grow": 4,
		"profit": 3.5,
		"stable": 4.3,
		"badge_growth": null,
		"badge_stability": null,
		"badge_venture": "N",
		"icon": "cat-iso-4",
		"region_insensitive": false
	},
	{
		"id": "medplus",
		"title": "(주)메드플러스",
		"company": "(주)메드플러스",
		"tags": [
			"의료기기",
			"소모품",
			"위생용품",
			"방역"
		],
		"score": "4.5",
		"grow": 3.6,
		"profit": 3.8,
		"stable": 4.8,
		"badge_growth": null,
		"badge_stability": 8,
		"badge_venture": "N",
		"icon": "cat-iso-17",
		"region_insensitive": false
	},
	{
		"id": "woori-pack",
		"title": "(주)우리패킹",
		"company": "(주)우리패킹",
		"tags": [
			"포장재",
			"라벨",
			"인쇄",
			"박스"
		],
		"score": "4.3",
		"grow": 3.5,
		"profit": 3.7,
		"stable": 4.5,
		"badge_growth": 35,
		"badge_stability": null,
		"badge_venture": "N",
		"icon": "cat-iso-12",
		"region_insensitive": false
	},
	{
		"id": "dongwon-metal",
		"title": "(주)동원금속",
		"company": "(주)동원금속",
		"tags": [
			"합금",
			"특수금속",
			"표면처리",
			"열처리"
		],
		"score": "4.6",
		"grow": 3.4,
		"profit": 4,
		"stable": 4.7,
		"badge_growth": 12,
		"badge_stability": 10,
		"badge_venture": "N",
		"icon": "cat-iso-7",
		"region_insensitive": false
	},
	{
		"id": "smartfactory",
		"title": "(주)스마트팩토리",
		"company": "(주)스마트팩토리",
		"tags": [
			"자동화",
			"로봇설비",
			"생산라인",
			"IoT"
		],
		"score": "4.8",
		"grow": 4.5,
		"profit": 4.2,
		"stable": 4.3,
		"badge_growth": 5,
		"badge_stability": null,
		"badge_venture": "Y",
		"icon": "cat-iso-6",
		"region_insensitive": false
	},
	{
		"id": "cloudbridge",
		"title": "(주)클라우드브릿지",
		"company": "(주)클라우드브릿지",
		"tags": [
			"클라우드",
			"SaaS",
			"IT컨설팅",
			"데이터분석"
		],
		"score": "4.7",
		"grow": 4.4,
		"profit": 4.1,
		"stable": 4.5,
		"badge_growth": 8,
		"badge_stability": null,
		"badge_venture": "Y",
		"icon": "cat-iso-14",
		"region_insensitive": true
	}
];

/**
 * RICH_ANALYSIS — 전략적 거래 분석 리포트 (회사별 HTML)
 * company_detail_ai.html의 window.RICH_ANALYSIS와 동일
 */
w.RICH_ANALYSIS = {
	"anjung-chem": "<h2>(주)이가이엔씨 × (주)안정케미칼 협력 방안</h2><h3>1. 거래 밸류에이션 요약 (ML Score 기반)</h3><p>양사 결합은 <strong>동종 정밀화학 업계 내 원료-첨가제 수직통합형 기술 파트너십</strong>으로 정의됩니다.</p><table><thead><tr><th>핵심 지표</th><th>점수</th><th>SHAP 분석 기반 기회 요인</th></tr></thead><tbody><tr><td><strong>업종 연관성</strong></td><td><strong>92.0</strong></td><td>동일 기초화학 업종으로 레시피·배합 노하우 기술 협력 즉시 가능</td></tr><tr><td><strong>규모 적합도</strong></td><td><strong>85.0</strong></td><td>중소기업 간 수평적 협업 구조, 의사결정 속도 및 계약 유연성 우수</td></tr><tr><td><strong>거래 네트워크</strong></td><td><strong>78.0</strong></td><td>코팅·세제·건자재 B2B 타겟 산업군 중복도 높아 영업 시너지 기대</td></tr></tbody></table><hr><h3>2. 상세 분석 및 시너지 도출</h3><h4>A. 사업적 시너지: \"원료 공동 구매로 원가 경쟁력 확보\"</h4><ul><li><strong>공동 원료 구매:</strong> 실리콘 계열·첨가제 공통 원료 묶음 발주 시 연간 원자재 비용 10~15% 절감 기대.</li><li><strong>보완적 제품 라인:</strong> 이가이엔씨의 실리콘 에멀젼에 안정케미칼의 정밀 첨가제를 복합 배합한 고부가 신소재 공동 출시 가능.</li></ul><h4>B. 거래 적합도 분석</h4><ul><li><strong>강점(Strengths):</strong> 동종 업종으로 품질 기준 상호 이해도가 높아 협력 초기 마찰 최소화, 시험 생산 연계 용이.</li><li><strong>보완점(Weaknesses):</strong> 일부 제품군 경쟁 관계 존재 가능 — 협력/독자 영업 범위를 계약 초기에 명확히 구분 필요.</li></ul><hr><h3>3. 실무형 협력 전략 (Action Plan)</h3><h4>전략 1: 공동 원료 구매 컨소시엄 구성 (원가 절감)</h4><ul><li><strong>내용:</strong> 공통 원료 공동 발주 체계 구축으로 수량 기반 가격 협상력 확보.</li><li><strong>효과:</strong> 원자재 비용 절감 + 재고 공유로 공급 불안정 리스크 감소.</li></ul><h4>전략 2: 고부가 복합 소재 공동 개발 (기술 협력)</h4><ul><li><strong>내용:</strong> 실리콘 에멀젼+정밀 첨가제 결합 기능성 코팅재 공동 개발, 개발비·IP 50:50 분담.</li><li><strong>효과:</strong> 단품 대비 단가 30% 이상 높은 복합 제품 라인업 구축, 수익성 개선.</li></ul><h4>전략 3: 교차 고객사 추천 영업 (수익 모델)</h4><ul><li><strong>내용:</strong> 양사 기존 고객사 기반 서로의 제품을 Cross-Referral하는 공동 영업 프로그램 운영.</li><li><strong>효과:</strong> 신규 고객 획득 비용(CAC) 절감 및 기존 고객 내 점유율 확대.</li></ul><hr><h3>4. 종합 제언 및 기대 효과</h3><blockquote>\"본 거래은 동종 업계 내 기술 시너지 최상위 조합입니다. 단순 거래를 넘어 R&amp;D·구매·영업 3개 분야의 복합 협력이 가능한 전략적 파트너십입니다.\"</blockquote><ul><li><strong>재무적 기대:</strong> 공동 구매 절감+복합 소재 신제품 매출 합산 시 양사 각각 연간 5~8% 수익 개선 기대.</li><li><strong>전략적 가치:</strong> 화학 소재 시장 내 차별화된 기술 포트폴리오를 보유한 B2B 화학 솔루션 파트너로 포지셔닝 강화.</li></ul><hr><p class=\"ai-analysis-note\">※ 담당자 참고사항: 본 분석은 ML 모델링 점수 기반입니다. 협력 협상 전 양사 현행 고객 리스트 경쟁 여부 확인 및 NDA 체결을 선행 조치로 권장합니다.</p>",
	"sinroe-logistics": "<h2>(주)이가이엔씨 × (주)신뢰물류 협력 방안</h2><h3>1. 거래 밸류에이션 요약 (ML Score 기반)</h3><p>양사 결합은 <strong>화학 제품 특수 물류 일원화를 통한 공급망 안정화 파트너십</strong>으로 정의됩니다.</p><table><thead><tr><th>핵심 지표</th><th>점수</th><th>SHAP 분석 기반 기회 요인</th></tr></thead><tbody><tr><td><strong>거래 네트워크</strong></td><td><strong>82.0</strong></td><td>120명 규모 전국망 보유, B2B 화학 제품 배송 수요처-공급처 연결 구조 확립</td></tr><tr><td><strong>규모 적합도</strong></td><td><strong>76.0</strong></td><td>중소 제조사 맞춤 물류 서비스 체계로 유연한 소량~대량 배송 대응 가능</td></tr><tr><td><strong>업종 연관성</strong></td><td><strong>58.0</strong></td><td>위험물·온도 관리 특수 물류 역량이 화학 소재 납품 요구사항에 직접 부합</td></tr></tbody></table><hr><h3>2. 상세 분석 및 시너지 도출</h3><h4>A. 사업적 시너지: \"화학 제품 물류 인프라 외주화로 고정비 절감\"</h4><ul><li><strong>위험물 전문 배송:</strong> 신뢰물류의 위험물 전용 차량·보관 구역을 활용해 이가이엔씨 실리콘 제품의 법규 준수 배송 체계 확립.</li><li><strong>전국 배송망 즉시 활용:</strong> 이가이엔씨가 전국 고객사 납품 시 신뢰물류 창고 허브망을 활용해 리드타임 단축 및 물류비 절감.</li></ul><h4>B. 거래 적합도 분석</h4><ul><li><strong>강점(Strengths):</strong> WMS 재고 가시화 시스템 연동으로 이가이엔씨 완제품 재고·출하 현황 실시간 파악, 납기 정확도 향상.</li><li><strong>보완점(Weaknesses):</strong> 성수기 배송량 급증 시 전용 보관 구역 확보 우선순위 확약이 필요하며, 파손·누출 사고 시 보상 범위를 계약서에 선제 명시 권장.</li></ul><hr><h3>3. 실무형 협력 전략 (Action Plan)</h3><h4>전략 1: 화학 제품 전용 물류 계약 체결 (공급망 안정화)</h4><ul><li><strong>내용:</strong> 이가이엔씨 전용 보관 구역(위험물 인증) 및 정기 배차 계획을 연간 단위 계약으로 선확보.</li><li><strong>효과:</strong> 자체 물류 투자 없이 전국 B2B 납품 체계 구축, 물류 고정비 40% 절감 기대.</li></ul><h4>전략 2: WMS-ERP 시스템 연동 (운영 효율화)</h4><ul><li><strong>내용:</strong> 신뢰물류 WMS와 이가이엔씨 생산 관리 데이터 연동으로 완제품 재고·출하 일원화.</li><li><strong>효과:</strong> 납기 오류율 감소, 고객사 클레임 대응 시간 단축, 재고 회전율 개선.</li></ul><h4>전략 3: 해외 바이어 수출 포장·통관 지원 (글로벌 확장)</h4><ul><li><strong>내용:</strong> 신뢰물류의 수출 포장·라벨링 서비스를 활용하여 해외 바이어 요구사항 대응 체계 구축.</li><li><strong>효과:</strong> 이가이엔씨 수출 역량 강화 및 신규 해외 시장 진출 속도 가속화.</li></ul><hr><h3>4. 종합 제언 및 기대 효과</h3><blockquote>\"본 거래은 이가이엔씨의 &#39;소재 기술력&#39;에 신뢰물류의 &#39;전국 물류 인프라&#39;를 더해 중소 제조사의 최대 고정비 중 하나인 물류 비용을 구조적으로 절감하는 전략입니다.\"</blockquote><ul><li><strong>재무적 기대:</strong> 물류 외주화로 이가이엔씨 물류 관련 고정비 연간 20~30% 절감 및 납기 준수율 향상에 따른 고객 재구매율 증가 기대.</li><li><strong>전략적 가치:</strong> 물류 역량 내재화 없이 전국 단위 영업망 확보, 핵심 역량(소재 개발)에 집중 투자 가능한 구조 확립.</li></ul><hr><p class=\"ai-analysis-note\">※ 담당자 참고사항: 본 분석은 ML 모델링 점수 기반입니다. 계약 전 위험물 운송 허가 유효기간 및 갱신 상태를 반드시 확인하고, 파손·누출 사고 시 책임 범위를 계약서에 선제 명시하시기 바랍니다.</p>",
	"solid-elec": "<h2>(주)이가이엔씨 × (주)솔리드전자 협력 방안</h2><h3>1. 거래 밸류에이션 요약 (ML Score 기반)</h3><p>양사 결합은 <strong>실리콘 소재-전자부품 코팅 공정 연계를 통한 B2B 기술 공급 파트너십</strong>으로 정의됩니다.</p><table><thead><tr><th>핵심 지표</th><th>점수</th><th>SHAP 분석 기반 기회 요인</th></tr></thead><tbody><tr><td><strong>업종 연관성</strong></td><td><strong>74.0</strong></td><td>PCB·전장 모듈 방습 코팅 공정에 실리콘 에멀젼 직접 적용 가능한 원료-소비 관계</td></tr><tr><td><strong>거래 네트워크</strong></td><td><strong>71.0</strong></td><td>소량 다품종 생산 역량 양사 공통 보유로 시험 생산 단계부터 협력 즉시 착수 가능</td></tr><tr><td><strong>규모 적합도</strong></td><td><strong>68.0</strong></td><td>86명 vs 35명 규모 차이로 이가이엔씨가 안정적 매출처 확보, 협상력 균형 유지</td></tr></tbody></table><hr><h3>2. 상세 분석 및 시너지 도출</h3><h4>A. 사업적 시너지: \"전자부품 보호 코팅 소재의 전문 공급사 포지셔닝\"</h4><ul><li><strong>PCB 방습·보호 코팅 공급:</strong> 이가이엔씨 실리콘 에멀젼·발수제를 솔리드전자 PCB 제조 공정에 적용하여 방습·절연 성능 강화, 제품 불량률 감소.</li><li><strong>신뢰성 공동 검증:</strong> 솔리드전자의 신뢰성 시험 설비와 이가이엔씨 배합 기술 결합으로 내열·내습 데이터 공동 축적, 고객사 승인 기간 단축.</li></ul><h4>B. 거래 적합도 분석</h4><ul><li><strong>강점(Strengths):</strong> 소량 다품종 시험 생산 강점 보유 양사 — 신규 전자 제품 개발 단계에서 코팅재 레시피와 기판 설계를 동시에 최적화 가능.</li><li><strong>보완점(Weaknesses):</strong> 전자부품용 실리콘 소재는 이온 불순물 기준이 엄격하므로 이가이엔씨 소재의 규격 적합성 사전 검증이 선행 조건.</li></ul><hr><h3>3. 실무형 협력 전략 (Action Plan)</h3><h4>전략 1: 코팅 소재 파일럿 공급 계약 (초기 진입)</h4><ul><li><strong>내용:</strong> 솔리드전자 특정 PCB 라인 대상 실리콘 코팅재 시험 적용, 3개월 성능 모니터링 후 본계약 전환.</li><li><strong>효과:</strong> 이가이엔씨 전자 분야 레퍼런스 확보, 솔리드전자 코팅 공정 비용 절감 효과 정량 검증.</li></ul><h4>전략 2: 전장 부품 코팅 공동 인증 획득 (기술 신뢰성)</h4><ul><li><strong>내용:</strong> 완성차·전장 Tier 고객의 소재 승인 요건에 맞춰 IATF·AEC-Q 등 공동 인증 추진.</li><li><strong>효과:</strong> 이가이엔씨 실리콘 소재의 전장 시장 진입장벽 해소, 솔리드전자 고객사 확장에 기여.</li></ul><h4>전략 3: Lot 추적 연동 체계 구축 (품질 관리)</h4><ul><li><strong>내용:</strong> 소재 Lot 번호와 제품 제조 이력을 연동하는 추적 시스템 공동 구축.</li><li><strong>효과:</strong> 불량 발생 시 원인 분석 시간 단축, 양사 클레임 처리 비용 절감.</li></ul><hr><h3>4. 종합 제언 및 기대 효과</h3><blockquote>\"본 거래은 이가이엔씨의 &#39;실리콘 소재 기술&#39;과 솔리드전자의 &#39;전자부품 검증 인프라&#39;를 결합하여 전장 시장 성장의 수혜를 양사가 공유하는 전략적 공급 파트너십입니다.\"</blockquote><ul><li><strong>재무적 기대:</strong> 전장 시장 성장(연 8~12%)에 따라 코팅 소재 공급량 지속 증가, 이가이엔씨 전자 분야 매출 연간 15% 이상 성장 기대.</li><li><strong>전략적 가치:</strong> 전장 부품 시장 레퍼런스 확보로 다른 전자 제조사 영업 시 신뢰도 제고, 이가이엔씨 제품 다각화 기반 마련.</li></ul><hr><p class=\"ai-analysis-note\">※ 담당자 참고사항: 본 분석은 ML 모델링 점수 기반입니다. 전자부품용 소재는 순도·이온 불순물 기준이 일반 산업용과 다르므로, 시험 적용 전 규격 검토를 반드시 완료하시기 바랍니다.</p>",
	"anjung-mach": "<h2>(주)이가이엔씨 × (주)안정기계 협력 방안</h2><h3>1. 거래 밸류에이션 요약 (ML Score 기반)</h3><p>양사 결합은 <strong>CNC 가공 현장 윤활·이형 소재 안정 공급을 통한 제조 공정 효율화 파트너십</strong>으로 정의됩니다.</p><table><thead><tr><th>핵심 지표</th><th>점수</th><th>SHAP 분석 기반 기회 요인</th></tr></thead><tbody><tr><td><strong>거래 네트워크</strong></td><td><strong>74.0</strong></td><td>안정기계의 전국 중소 제조사 납품망이 이가이엔씨 산업용 실리콘 제품의 간접 판로로 작동 가능</td></tr><tr><td><strong>업종 연관성</strong></td><td><strong>68.0</strong></td><td>실리콘 오일 기반 윤활·냉각제가 CNC 가공 공정 핵심 소모품으로 직접적 수요-공급 관계 성립</td></tr><tr><td><strong>규모 적합도</strong></td><td><strong>72.0</strong></td><td>62명 규모 중소기업으로 이가이엔씨와 대등한 협업 체급, 장기 파트너십 구축 적합</td></tr></tbody></table><hr><h3>2. 상세 분석 및 시너지 도출</h3><h4>A. 사업적 시너지: \"가공 현장 소모품 안정 공급으로 생산성 향상\"</h4><ul><li><strong>윤활·냉각제 공급:</strong> 이가이엔씨 실리콘 오일 기반 윤활제·냉각제를 안정기계 CNC 라인에 적용하여 공구 수명 연장 및 가공 정밀도 향상.</li><li><strong>설비 부품 상호 연계:</strong> 이가이엔씨 생산 설비 유지보수에 필요한 정밀 가공 부품을 안정기계에서 긴급 조달, 생산 중단 리스크 최소화.</li></ul><h4>B. 거래 적합도 분석</h4><ul><li><strong>강점(Strengths):</strong> 안정기계의 야간·주말 긴급 대응 체계는 이가이엔씨 소재 긴급 현장 시험 시 빠른 가공 부품 지원 가능.</li><li><strong>보완점(Weaknesses):</strong> 실리콘 윤활제와 CNC 장비 씰·패킹 소재 간 호환성을 사전 검증해야 하며, 가공유 교체 주기·폐유 처리 비용을 총비용 기준으로 산정 필요.</li></ul><hr><h3>3. 실무형 협력 전략 (Action Plan)</h3><h4>전략 1: CNC 현장 윤활소재 시험 도입 (파일럿)</h4><ul><li><strong>내용:</strong> 안정기계 특정 CNC 라인에 이가이엔씨 실리콘 윤활제 시험 적용, 공구 수명 및 가공면 품질 데이터 3개월 수집 후 본계약 전환.</li><li><strong>효과:</strong> 이가이엔씨 산업용 윤활제 실증 레퍼런스 확보, 안정기계 소모품 비용 절감 효과 정량화.</li></ul><h4>전략 2: 설비 부품-소재 상호 구매 협약 (비용 절감)</h4><ul><li><strong>내용:</strong> 이가이엔씨는 정밀 가공 부품을 안정기계에서 우선 조달, 안정기계는 실리콘 윤활·이형제를 이가이엔씨에서 우선 구매하는 상호 구매 협약 체결.</li><li><strong>효과:</strong> 양사 외부 조달 의존도 감소 및 거래 관계 고착화로 장기 매출 안정성 확보.</li></ul><h4>전략 3: 제조 현장 소재 레퍼런스 공동 마케팅 (영업 확장)</h4><ul><li><strong>내용:</strong> 안정기계 납품처(중소 제조사) 대상 이가이엔씨 실리콘 윤활제 공동 제안 및 현장 데모 운영.</li><li><strong>효과:</strong> 이가이엔씨 산업용 소재 판로를 안정기계 고객망을 통해 간접 확장.</li></ul><hr><h3>4. 종합 제언 및 기대 효과</h3><blockquote>\"본 거래은 이가이엔씨의 &#39;실리콘 소재 기술&#39;과 안정기계의 &#39;제조 현장 네트워크&#39;를 연결하여 중소 제조 현장에서 발생하는 소모품 비용을 줄이는 실용적 파트너십입니다.\"</blockquote><ul><li><strong>재무적 기대:</strong> 상호 구매 협약을 통한 조달 비용 절감 및 안정기계 고객망을 통한 이가이엔씨 연간 신규 매출 5~10% 증가 기대.</li><li><strong>전략적 가치:</strong> 산업용 윤활·이형 소재 분야의 현장 실증 데이터 확보로 이가이엔씨의 제조업 시장 신뢰도 제고.</li></ul><hr><p class=\"ai-analysis-note\">※ 담당자 참고사항: 본 분석은 ML 모델링 점수 기반입니다. 시험 도입 전 실리콘 윤활제의 장비 씰·패킹 호환성을 제조사에 확인하고, 가공유 교체에 따른 폐유 처리 절차를 사전에 합의하시기 바랍니다.</p>",
	"igain": "<h2>[Self Analysis Report] (주)이가이엔씨 — 기업 역량 및 협력 포지셔닝 분석</h2><h3>1. 기업 역량 밸류에이션 요약 (ML Score 기반)</h3><p>이가이엔씨는 <strong>기초 유기화학 소재 분야의 B2B 맞춤형 파트너</strong>로, 소량 시험 생산부터 양산까지 대응하는 민첩한 기술 중소기업입니다.</p><table><thead><tr><th>핵심 역량 지표</th><th>평가</th><th>SHAP 분석 기반 강점 요인</th></tr></thead><tbody><tr><td><strong>기술 차별성</strong></td><td><strong>High</strong></td><td>실리콘 오일·에멀젼·발수제 배합 Know-how — 레시피 튜닝 기반 맞춤 소재 개발 역량</td></tr><tr><td><strong>시장 적합성</strong></td><td><strong>High</strong></td><td>코팅·세제·건자재·개인케어·전자 등 다양한 산업군 응용처 확보로 시장 다각화</td></tr><tr><td><strong>운영 민첩성</strong></td><td><strong>Mid-High</strong></td><td>35명 규모의 신속한 의사결정 구조, 소량 시험 생산 대응으로 대기업 납품 전 단계 지원에 강점</td></tr></tbody></table><hr><h3>2. 협력 포지셔닝 분석</h3><h4>A. 이가이엔씨의 핵심 협력 가치</h4><ul><li><strong>배합 Know-how 기반 맞춤 개발:</strong> NDA 기반 공동 개발 체계로 고객사 전용 소재 레시피 최적화 지원.</li><li><strong>신속한 시험 생산 대응:</strong> 소량 다품종 생산 역량으로 신제품 개발 단계의 빠른 소재 검증 지원.</li><li><strong>규제 서류 패키지 제공:</strong> SDS·REACH·친환경 인증 서류를 초기 단계에 정리하여 고객사 승인 기간 단축.</li></ul><h4>B. 협력 파트너 선택 시 고려 사항</h4><ul><li><strong>우선 협력 대상:</strong> 실리콘 에멀젼·발수제의 직접 수요처(화장품·전자·코팅·포장재 제조사) 및 물류·IT 인프라 파트너.</li><li><strong>협력 시 유의점:</strong> 핵심 배합 비밀 보호를 위한 NDA 범위 명확화 및 공동 개발 IP 귀속 기준 사전 설정 필수.</li></ul><hr><h3>3. 성장 전략 방향 (Action Plan)</h3><h4>전략 1: 핵심 응용처별 레퍼런스 확보 (시장 확장)</h4><ul><li><strong>내용:</strong> 화장품·전자·건자재 각 분야 핵심 고객사 1~2개와 공동 개발 레퍼런스 확보.</li><li><strong>효과:</strong> 분야별 레퍼런스가 신규 고객 영업의 신뢰 자산으로 작동, 수주 전환율 향상.</li></ul><h4>전략 2: 디지털 전환으로 생산·품질 데이터 체계화 (운영 혁신)</h4><ul><li><strong>내용:</strong> 클라우드 기반 생산 관리·Lot 추적 시스템 도입으로 배합 레시피·품질 이력 디지털화.</li><li><strong>효과:</strong> 대기업 고객사의 공급망 연동 요구 대응 역량 강화, 클레임 처리 시간 단축.</li></ul><h4>전략 3: 물류 파트너십으로 전국 납품 체계 구축 (확장성)</h4><ul><li><strong>내용:</strong> 특수 화학 물류 전문 파트너와 협약 체결로 전국 단위 안정적 납품 체계 구축.</li><li><strong>효과:</strong> 지리적 제약 없는 전국 영업망 확보, 신규 지역 고객사 공략 가능.</li></ul><hr><h3>4. 종합 제언 및 기대 효과</h3><blockquote>\"이가이엔씨는 &#39;기술력&#39;과 &#39;민첩성&#39;을 핵심 자산으로 보유한 B2B 소재 파트너입니다. 매커멕스를 통해 발굴된 최적 파트너와의 협력을 통해 응용 범위 확장 및 안정적 성장을 동시에 달성하세요.\"</blockquote><ul><li><strong>재무적 기대:</strong> 분야별 레퍼런스 확보 및 디지털 전환 완료 시 향후 2년 내 매출 25~35% 성장 기대.</li><li><strong>전략적 가치:</strong> 특화 소재 기업에서 &#39;B2B 화학 솔루션 파트너&#39;로 포지셔닝 전환, 브랜드 가치 및 협상력 제고.</li></ul><hr><p class=\"ai-analysis-note\">※ 담당자 참고사항: 본 분석은 이가이엔씨 자사 데이터를 기반으로 한 역량 분석 보고서입니다. 협력 파트너 선정 시 추천 기업별 AI 적합도 점수 및 상세 분석 보고서를 함께 참고하시기 바랍니다.</p>",
	"hankook-precision": "<h2>(주)이가이엔씨 × (주)한국정밀기계 협력 방안</h2><h3>1. 거래 밸류에이션 요약 (ML Score 기반)</h3><p>양사 결합은 <strong>금형·정밀부품 이형제·코팅재 전문 공급을 통한 제조 품질 향상 파트너십</strong>으로 정의됩니다.</p><table><thead><tr><th>핵심 지표</th><th>점수</th><th>SHAP 분석 기반 기회 요인</th></tr></thead><tbody><tr><td><strong>업종 연관성</strong></td><td><strong>78.0</strong></td><td>금형 이형제·정밀 부품 코팅·방청 처리에 실리콘 소재 필수 사용, 직접적 원료-소비 관계</td></tr><tr><td><strong>거래 네트워크</strong></td><td><strong>75.0</strong></td><td>한국정밀기계의 자동차·전자 부품 OEM·Tier 고객망이 이가이엔씨 제품 간접 레퍼런스로 연결</td></tr><tr><td><strong>규모 적합도</strong></td><td><strong>69.0</strong></td><td>210명 대형 제조사로 이가이엔씨에 이형제·코팅재의 안정적 대량 수요 제공 가능</td></tr></tbody></table><hr><h3>2. 상세 분석 및 시너지 도출</h3><h4>A. 사업적 시너지: \"금형 공정 소재 전문 공급으로 제품 품질 향상\"</h4><ul><li><strong>금형 이형제 공급:</strong> 이가이엔씨 실리콘 오일 기반 이형제를 한국정밀기계 금형 가공 공정에 적용, 금형 수명 연장 및 제품 표면 품질 향상 동시 달성.</li><li><strong>정밀 부품 보호 코팅:</strong> 한국정밀기계 생산 자동차·전자 부품에 이가이엔씨 발수·방습 코팅재 적용으로 내환경성 강화 및 부품 불량률 감소.</li></ul><h4>B. 거래 적합도 분석</h4><ul><li><strong>강점(Strengths):</strong> 한국정밀기계의 품질 트레이싱 체계 연동 시 이가이엔씨 소재 현장 성능 데이터 축적 가능, 소재 개선에 직접 활용.</li><li><strong>보완점(Weaknesses):</strong> 자동차 부품용 이형제는 IATF 등 특수 규격 적합성 사전 확인 필수, 금형 소재별(SKD·SKH) 호환성 시험 후 납품 권장.</li></ul><hr><h3>3. 실무형 협력 전략 (Action Plan)</h3><h4>전략 1: 금형 이형제 파일럿 적용 (기술 진입)</h4><ul><li><strong>내용:</strong> 한국정밀기계 특정 금형 라인 대상 이가이엔씨 이형제 3개월 시험 적용, 금형 수명·표면 품질 데이터 수집 후 전사 확대.</li><li><strong>효과:</strong> 이가이엔씨 자동차 분야 레퍼런스 확보, 한국정밀기계 금형 교체 주기 연장으로 비용 절감.</li></ul><h4>전략 2: IATF 공동 소재 승인 추진 (시장 진입장벽 해소)</h4><ul><li><strong>내용:</strong> 한국정밀기계 고객사(완성차 Tier) 요구 규격에 맞춰 이가이엔씨 이형제·코팅재 공동 승인 추진.</li><li><strong>효과:</strong> 이가이엔씨 자동차 부품 시장 진입장벽 해소 및 한국정밀기계 고객사 내 소재 공급 독점적 지위 확보.</li></ul><h4>전략 3: 품질 트레이싱 데이터 연동 (운영 혁신)</h4><ul><li><strong>내용:</strong> 한국정밀기계 품질 트레이싱 시스템에 이가이엔씨 소재 Lot 정보 연동, 불량 원인 분석 자동화.</li><li><strong>효과:</strong> 클레임 처리 시간 단축 및 소재 기인 불량 책임 명확화로 양사 분쟁 리스크 감소.</li></ul><hr><h3>4. 종합 제언 및 기대 효과</h3><blockquote>\"본 거래은 이가이엔씨의 &#39;실리콘 소재 기술&#39;이 한국정밀기계의 &#39;자동차·전자 부품 제조 역량&#39;과 결합하여 고부가 정밀 제조 시장에 진입하는 전략적 교두보입니다.\"</blockquote><ul><li><strong>재무적 기대:</strong> 이형제·코팅재 연간 공급 계약 체결 시 이가이엔씨 자동차 부품 분야 매출 연간 20% 이상 성장 기대.</li><li><strong>전략적 가치:</strong> 자동차·전자 분야 대형 제조사 레퍼런스 확보로 동종 업계 영업 신뢰도 제고 및 추가 수주 가속화.</li></ul><hr><p class=\"ai-analysis-note\">※ 담당자 참고사항: 본 분석은 ML 모델링 점수 기반입니다. 자동차 부품 납품 소재는 고객사 특수 승인(PPAP 등)이 필요하므로 파일럿 단계에서 승인 프로세스 일정을 한국정밀기계와 사전 합의하시기 바랍니다.</p>",
	"green-packaging": "<h2>(주)이가이엔씨 × (주)그린패키징 협력 방안</h2><h3>1. 거래 밸류에이션 요약 (ML Score 기반)</h3><p>양사 결합은 <strong>친환경 포장재-발수 코팅 소재 연계를 통한 ESG 기반 패키징 혁신 파트너십</strong>으로 정의됩니다.</p><table><thead><tr><th>핵심 지표</th><th>점수</th><th>SHAP 분석 기반 기회 요인</th></tr></thead><tbody><tr><td><strong>업종 연관성</strong></td><td><strong>69.0</strong></td><td>포장재 방습·발수 코팅 공정에 실리콘 에멀젼 직접 적용 가능, 원료-가공 협력 관계 성립</td></tr><tr><td><strong>거래 네트워크</strong></td><td><strong>65.0</strong></td><td>양사 모두 친환경 제품 포트폴리오 보유로 ESG 트렌드 공동 수혜 가능</td></tr><tr><td><strong>규모 적합도</strong></td><td><strong>76.0</strong></td><td>42명 vs 35명 유사 규모로 대등한 협업 체급, 유연한 계약 구조 설계 가능</td></tr></tbody></table><hr><h3>2. 상세 분석 및 시너지 도출</h3><h4>A. 사업적 시너지: \"친환경 발수 코팅 포장재로 프리미엄 시장 공략\"</h4><ul><li><strong>포장재 방습·발수 코팅 공급:</strong> 이가이엔씨 실리콘 에멀젼·발수제를 그린패키징 펄프 박스·필름에 적용하여 식품·화학 제품 포장의 습기 차단 성능 강화.</li><li><strong>이가이엔씨 자사 포장 개선:</strong> 그린패키징이 이가이엔씨 화학 제품의 출하 포장재를 맞춤 설계하여 운송 중 파손·누출 리스크 절감 및 ESG 공시 대응.</li></ul><h4>B. 거래 적합도 분석</h4><ul><li><strong>강점(Strengths):</strong> 양사 모두 소량 맞춤 생산에 강점 보유 — 신규 친환경 코팅 포장재 시제품을 빠르게 개발·검증하여 시장 출시 속도 확보 가능.</li><li><strong>보완점(Weaknesses):</strong> 식품 접촉 포장재 적용 시 실리콘 코팅재의 식약처·FDA 안전 기준 적합성 확인 필수, 재생 소재와 실리콘 코팅의 재활용 호환성 사전 검토 필요.</li></ul><hr><h3>3. 실무형 협력 전략 (Action Plan)</h3><h4>전략 1: 친환경 발수 코팅 포장재 공동 개발 (기술 협력)</h4><ul><li><strong>내용:</strong> 재생 펄프 기반 포장재에 이가이엔씨 저VOC 실리콘 발수제를 코팅하는 신규 친환경 제품 라인 공동 개발.</li><li><strong>효과:</strong> 기존 포장재 대비 방습 성능 40% 향상, 프리미엄 시장 진출 및 단가 상승 기회 확보.</li></ul><h4>전략 2: 이가이엔씨 출하 포장 전환 (즉시 적용)</h4><ul><li><strong>내용:</strong> 이가이엔씨 화학 제품 출하 포장을 그린패키징의 친환경 맞춤 포장으로 전환, 누출 방지·ESG 요건 동시 충족.</li><li><strong>효과:</strong> 이가이엔씨 ESG 공시 포장재 항목 개선, 고객사 인상 관리 및 그린패키징 고정 매출처 확보.</li></ul><h4>전략 3: 친환경 인증 공동 취득 (시장 신뢰성)</h4><ul><li><strong>내용:</strong> 그린패키징의 FSC·LCA 인증 역량과 이가이엔씨 코팅재 친환경 데이터를 결합하여 공동 친환경 제품 인증 취득.</li><li><strong>효과:</strong> ESG 인증 포장재 시장에서 차별화된 경쟁력 확보, 친환경 포장재를 요구하는 대기업 납품 기회 창출.</li></ul><hr><h3>4. 종합 제언 및 기대 효과</h3><blockquote>\"본 거래은 이가이엔씨의 &#39;실리콘 코팅 기술&#39;과 그린패키징의 &#39;친환경 포장 제조 역량&#39;을 결합하여 ESG 트렌드를 비즈니스 기회로 전환하는 전략적 파트너십입니다.\"</blockquote><ul><li><strong>재무적 기대:</strong> 친환경 발수 코팅 포장재 신규 시장 진출 시 양사 공동 연간 매출 10~15% 추가 성장 기대.</li><li><strong>전략적 가치:</strong> 친환경 포장재 시장의 ESG 선도 기업으로 포지셔닝, 대기업 공급망 진입 기회 확대.</li></ul><hr><p class=\"ai-analysis-note\">※ 담당자 참고사항: 본 분석은 ML 모델링 점수 기반입니다. 식품 접촉 포장재에 실리콘 코팅재 적용 시 식약처 안전 기준 확인 및 재생 소재와의 재활용 호환성 검토를 공동 개발 착수 전에 완료하시기 바랍니다.</p>",
	"global-logistics": "<h2>(주)이가이엔씨 × (주)글로벌로지스틱스 협력 방안</h2><h3>1. 거래 밸류에이션 요약 (ML Score 기반)</h3><p>양사 결합은 <strong>화학 제품 수출입 통관·위험물 국제 운송 원스톱 체계 구축을 통한 글로벌 시장 진출 파트너십</strong>으로 정의됩니다.</p><table><thead><tr><th>핵심 지표</th><th>점수</th><th>SHAP 분석 기반 기회 요인</th></tr></thead><tbody><tr><td><strong>거래 네트워크</strong></td><td><strong>80.0</strong></td><td>글로벌로지스틱스의 동남아·북미 현지 포워딩망이 이가이엔씨 해외 바이어 납기 신뢰도 직접 강화</td></tr><tr><td><strong>규모 적합도</strong></td><td><strong>72.0</strong></td><td>95명 규모 벤처 인증 기업으로 이가이엔씨 수출 물량 단계적 확장에 유연하게 대응 가능</td></tr><tr><td><strong>업종 연관성</strong></td><td><strong>55.0</strong></td><td>화학물질 수출 통관 전문 창구 보유 — HS 코드 분류·SDS 서류·목적국 화학물질 등록 통합 처리</td></tr></tbody></table><hr><h3>2. 상세 분석 및 시너지 도출</h3><h4>A. 사업적 시너지: \"화학 제품 글로벌 수출 인프라 원스톱 위탁\"</h4><ul><li><strong>화학품 수출 통관 전문화:</strong> 글로벌로지스틱스의 화학품 특수 통관 창구를 통해 이가이엔씨 실리콘 제품 수출 시 HS 코드·SDS·목적국 화학물질 등록을 통합 처리.</li><li><strong>FTA 원산지 활용으로 관세 절감:</strong> 글로벌로지스틱스의 FTA 원산지 증빙 서비스 활용 시 이가이엔씨 제품 수출 관세 절감 및 가격 경쟁력 향상.</li></ul><h4>B. 거래 적합도 분석</h4><ul><li><strong>강점(Strengths):</strong> 이가이엔씨가 자체 수출 인프라 구축 없이 통관·물류·보세 창고를 원스톱 위탁하여 글로벌 시장 진출 초기 비용 최소화.</li><li><strong>보완점(Weaknesses):</strong> 목적국별 화학물질 규제(REACH·TSCA) 변경 시 통관 지연 리스크에 대한 책임 소재를 계약서에 사전 합의 필수.</li></ul><hr><h3>3. 실무형 협력 전략 (Action Plan)</h3><h4>전략 1: 동남아 수출 물류 전담 계약 (글로벌 진출)</h4><ul><li><strong>내용:</strong> 이가이엔씨 동남아 수출 물량 전체를 글로벌로지스틱스에 위탁, 통관·운송·현지 통관 원스톱 처리 계약 체결.</li><li><strong>효과:</strong> 이가이엔씨 자체 수출 담당 인력 없이 동남아 수출 체계 완성, 수출 초기 고정비 80% 절감.</li></ul><h4>전략 2: 화학물질 규제 대응 공동 모니터링 (리스크 관리)</h4><ul><li><strong>내용:</strong> 목적국별 화학물질 규제(EU REACH·미국 TSCA·중국 CSCL) 변경 사항을 글로벌로지스틱스가 정기 모니터링하여 이가이엔씨에 사전 보고.</li><li><strong>효과:</strong> 규제 변경으로 인한 통관 지연·반송 리스크를 사전에 차단하여 해외 바이어 납기 신뢰도 유지.</li></ul><h4>전략 3: 벤처기업 공동 조달·정책자금 활용 (비용 최적화)</h4><ul><li><strong>내용:</strong> 벤처 인증 글로벌로지스틱스와의 장기 계약을 통해 중소기업 수출 지원 정책 자금 및 물류 보조금 공동 활용.</li><li><strong>효과:</strong> 양사 수출 관련 정책 자금 수혜 확대, 물류 비용 추가 절감.</li></ul><hr><h3>4. 종합 제언 및 기대 효과</h3><blockquote>\"본 거래은 이가이엔씨의 &#39;실리콘 소재 기술&#39;에 글로벌로지스틱스의 &#39;화학품 수출 통관 전문성&#39;을 더해, 중소 화학 제조사의 글로벌 진출 최대 장벽인 수출 인프라 문제를 구조적으로 해결하는 파트너십입니다.\"</blockquote><ul><li><strong>재무적 기대:</strong> 동남아 수출 정착 시 이가이엔씨 해외 매출 비중 현 수준에서 2년 내 15~25%로 확대 기대.</li><li><strong>전략적 가치:</strong> 글로벌 공급망 진입으로 이가이엔씨의 기업 가치 및 대외 신뢰도 제고, 국내 대기업 납품 협상에서도 글로벌 역량 어필 가능.</li></ul><hr><p class=\"ai-analysis-note\">※ 담당자 참고사항: 본 분석은 ML 모델링 점수 기반입니다. 화학 제품 국제 운송 계약 시 Incoterms·보험 범위·화학물질 검사·검역 대행 범위를 계약서에 명확히 반영하고, 운임 조정 기준을 분기별로 점검하시기 바랍니다.</p>",
	"cloudbridge": "<h2>(주)이가이엔씨 × (주)클라우드브릿지 협력 방안</h2><h3>1. 거래 밸류에이션 요약 (ML Score 기반)</h3><p>양사 결합은 <strong>강남 권역 IT 역량과 전북 화학 기술력을 연결하는 비대면 DX(Digital Transformation) 파트너십</strong>으로 정의됩니다.</p><table><thead><tr><th>핵심 지표</th><th>점수</th><th>SHAP 분석 기반 기회 요인</th></tr></thead><tbody><tr><td><strong>거래 네트워크</strong></td><td><strong>72.0</strong></td><td>클라우드브릿지의 B2B SaaS 고객군과 이가이엔씨 협력 제조사 고객군의 간접 연결 가능성</td></tr><tr><td><strong>규모 적합도</strong></td><td><strong>76.0</strong></td><td>72명 규모 벤처기업으로 이가이엔씨 규모에 맞는 경량화 SaaS 솔루션 설계·제공 가능</td></tr><tr><td><strong>업종 연관성</strong></td><td><strong>48.0</strong></td><td>이가이엔씨 생산·배합·재고 데이터를 디지털화하는 데 클라우드브릿지 SaaS·데이터 분석 역량 직접 기여</td></tr></tbody></table><hr><h3>2. 상세 분석 및 시너지 도출</h3><h4>A. 사업적 시너지: \"화학 소재 제조사 전용 DX 솔루션 공동 개발\"</h4><ul><li><strong>생산 관리 SaaS 도입:</strong> 클라우드브릿지 클라우드 기반 생산 관리 시스템으로 이가이엔씨 배합 레시피·Lot 추적·재고 현황 실시간 파악 가능.</li><li><strong>데이터 기반 품질 개선:</strong> 이가이엔씨 시험 생산 데이터·품질 검사 결과를 클라우드브릿지 분석 플랫폼에서 분석하여 배합 최적화 및 불량률 감소에 활용.</li></ul><h4>B. 거래 적합도 분석</h4><ul><li><strong>강점(Strengths):</strong> 원격 서비스 제공 가능으로 지역 제약 없이 서비스 이용 가능, 35명 규모에 최적화된 경량 SaaS 구독 모델로 초기 IT 투자 부담 최소화.</li><li><strong>보완점(Weaknesses):</strong> 화학 배합 데이터·레시피 등 영업비밀의 클라우드 저장 시 데이터 주권·암호화 정책 수립 필수, SaaS 해지 시 데이터 반환 절차 사전 합의 필요.</li></ul><hr><h3>3. 실무형 협력 전략 (Action Plan)</h3><h4>전략 1: 화학 제조사 특화 SaaS 공동 개발 (기술 협력)</h4><ul><li><strong>내용:</strong> 이가이엔씨 실제 운영 데이터를 기반으로 클라우드브릿지가 화학 소재 제조사 전용 SaaS 모듈 개발, 이가이엔씨는 파일럿 고객 및 레퍼런스 제공.</li><li><strong>효과:</strong> 클라우드브릿지의 화학 제조업 버티컬 SaaS 시장 진입, 이가이엔씨의 DX 전환 비용 절감.</li></ul><h4>전략 2: 이가이엔씨 생산 데이터 디지털화 (즉시 적용)</h4><ul><li><strong>내용:</strong> 배합 레시피 관리·Lot 추적·품질 검사 결과를 클라우드 시스템으로 이관, 현장 직원 교육 포함.</li><li><strong>효과:</strong> 대기업 고객사 공급망 연동 요구 대응 역량 강화, 클레임 처리 시간 단축.</li></ul><h4>전략 3: AI 기반 배합 최적화 파일럿 (혁신)</h4><ul><li><strong>내용:</strong> 클라우드브릿지의 데이터 분석 역량으로 이가이엔씨 축적 배합 데이터를 분석하여 원가·품질 최적 배합 파라미터 도출.</li><li><strong>효과:</strong> 원자재 낭비 감소 및 시험 생산 횟수 절감으로 R&amp;D 효율화.</li></ul><hr><h3>4. 종합 제언 및 기대 효과</h3><blockquote>\"본 거래은 이가이엔씨의 &#39;아날로그 배합 Know-how&#39;를 클라우드브릿지의 &#39;디지털 분석 플랫폼&#39;으로 체계화하여 기업 가치를 높이는 DX 혁신 파트너십입니다.\"</blockquote><ul><li><strong>재무적 기대:</strong> DX 전환 완료 시 이가이엔씨 R&amp;D 효율 20~30% 향상, 클라우드브릿지 화학 제조업 버티컬 SaaS 매출 신규 창출.</li><li><strong>전략적 가치:</strong> 이가이엔씨의 디지털 역량 강화로 대기업 공급망 진입 요건 충족 및 기업 가치 제고, 투자 유치 시 DX 완료 기업으로 프리미엄 밸류에이션 가능.</li></ul><hr><p class=\"ai-analysis-note\">※ 담당자 참고사항: 본 분석은 ML 모델링 점수 기반입니다. 클라우드 전환 전 핵심 배합 Know-how의 데이터 보안 정책(접근 권한·암호화·주권)을 먼저 확립하고, 기존 운영 방식 병행 기간을 충분히 확보하시기 바랍니다.</p>",
	"daewon-steel": "<h2>(주)이가이엔씨 × (주)대원스틸 협력 방안</h2><h3>1. 거래 밸류에이션 요약 (ML Score 기반)</h3><p>양사 결합은 <strong>철강 가공 공정 내 실리콘 기반 방청·윤활 소재 안정 공급을 통한 제조 품질 향상 파트너십</strong>으로 정의됩니다.</p><table><thead><tr><th>핵심 지표</th><th>점수</th><th>SHAP 분석 기반 기회 요인</th></tr></thead><tbody><tr><td><strong>업종 연관성</strong></td><td><strong>72.0</strong></td><td>철강 가공·보관에 방청유·절삭유·이형제 등 실리콘 소재 필수 사용, 직접적 원료-소비 관계</td></tr><tr><td><strong>규모 적합도</strong></td><td><strong>80.0</strong></td><td>95명 중기업 규모로 이가이엔씨에 실리콘 소재의 안정적 대량 반복 수요 제공 가능</td></tr><tr><td><strong>거래 네트워크</strong></td><td><strong>65.0</strong></td><td>대원스틸의 다양한 산업군 납품망을 통해 이가이엔씨 소재의 간접 레퍼런스 확보 가능</td></tr></tbody></table><hr><h3>2. 상세 분석 및 시너지 도출</h3><h4>A. 사업적 시너지: \"철강 공정 소재 전문 공급으로 제품 가치 향상\"</h4><ul><li><strong>방청유·윤활제 안정 공급:</strong> 이가이엔씨 실리콘 오일 기반 방청유를 대원스틸 철강 제품 보관·운송 공정에 적용하여 녹 발생 감소 및 제품 품질 향상.</li><li><strong>절삭·가공유 협력:</strong> 대원스틸의 절단·가공 라인에 이가이엔씨 실리콘 에멀젼 기반 절삭유 적용으로 공구 수명 연장 및 가공면 품질 향상 기대.</li></ul><h4>B. 거래 적합도 분석</h4><ul><li><strong>강점(Strengths):</strong> 대원스틸 95명 규모의 안정적 제조사로 이가이엔씨에 방청유·가공유의 지속적·반복적 수요처 역할 — 장기 계약 안정성 우수.</li><li><strong>보완점(Weaknesses):</strong> 지역적 거리(전북-경남) 물류 비용 발생, 철강 소재별 방청유 호환성 사전 검증 및 대량 납품 시 단가 연동 조항 협의 필요.</li></ul><hr><h3>3. 실무형 협력 전략 (Action Plan)</h3><h4>전략 1: 방청유 현장 시험 도입 (파일럿)</h4><ul><li><strong>내용:</strong> 대원스틸 보관 창고 일부 제품 대상 이가이엔씨 실리콘 방청유 3개월 시험 적용, 녹 발생률·제품 품질 데이터 수집 후 전사 확대.</li><li><strong>효과:</strong> 이가이엔씨 철강 업종 방청유 레퍼런스 확보, 대원스틸 제품 품질 향상으로 고객사 클레임 감소.</li></ul><h4>전략 2: 절삭유 공급 장기 계약 체결 (수익 안정화)</h4><ul><li><strong>내용:</strong> 대원스틸 절단·가공 라인 연간 소요량 기준 절삭유 장기 공급 계약 체결, 수량 기반 단가 연동 조항 포함.</li><li><strong>효과:</strong> 이가이엔씨 안정적 반복 매출 확보, 대원스틸 소모품 비용 최적화 및 공급 리스크 감소.</li></ul><h4>전략 3: 납품처 연계 소재 공동 마케팅 (영업 확장)</h4><ul><li><strong>내용:</strong> 대원스틸이 납품하는 철강 가공 고객사를 대상으로 이가이엔씨 방청유·이형제를 패키지 추천하는 공동 영업 프로그램 운영.</li><li><strong>효과:</strong> 대원스틸 고객망을 통한 이가이엔씨 철강 업종 신규 고객 획득, 영업비 절감.</li></ul><hr><h3>4. 종합 제언 및 기대 효과</h3><blockquote>\"본 거래은 이가이엔씨의 &#39;실리콘 소재 기술&#39;이 대원스틸의 &#39;철강 가공 수요&#39;와 만나 산업 소재 시장에서 안정적 매출 기반을 구축하는 실용적 파트너십입니다.\"</blockquote><ul><li><strong>재무적 기대:</strong> 방청유·절삭유 연간 공급 계약 체결 시 이가이엔씨 철강 분야 반복 매출 연간 10~15% 증가 기대.</li><li><strong>전략적 가치:</strong> 철강 가공 업종 레퍼런스 확보로 동종 제조사 영업 신뢰도 제고 및 추가 수주 가속화.</li></ul><hr><p class=\"ai-analysis-note\">※ 담당자 참고사항: 본 분석은 ML 모델링 점수 기반입니다. 철강 소재별(SS400·STS·알루미늄) 방청유 호환성을 사전에 검증하고, 대량 납품 계약 시 단가 연동 기준과 물량 커밋 조건을 계약서에 명시하시기 바랍니다.</p>",
	"beauty-factory": "<h2>(주)이가이엔씨 × (주)뷰티팩토리 협력 방안</h2><h3>1. 거래 밸류에이션 요약 (ML Score 기반)</h3><p>양사 결합은 <strong>경기 성남 권역 내 실리콘 에멀젼-화장품 제조 원료 직접 연계를 통한 K-뷰티 시장 공동 성장 파트너십</strong>으로 정의됩니다.</p><table><thead><tr><th>핵심 지표</th><th>점수</th><th>SHAP 분석 기반 기회 요인</th></tr></thead><tbody><tr><td><strong>규모 적합도</strong></td><td><strong>92.0</strong></td><td>58명 vs 35명 최적 협업 체급으로 의사결정 속도·계약 유연성 우수, 대등한 파트너십 구조</td></tr><tr><td><strong>거래 네트워크</strong></td><td><strong>85.0</strong></td><td>뷰티팩토리의 다수 ODM·OEM 브랜드 고객망이 이가이엔씨 실리콘 에멀젼의 다중 적용처로 연결</td></tr><tr><td><strong>업종 연관성</strong></td><td><strong>78.0</strong></td><td>실리콘 에멀젼이 화장품 포뮬레이션 핵심 원료(피부 보호막·사용감·유화 안정)로 직접 원료-제품 관계 성립</td></tr></tbody></table><hr><h3>2. 상세 분석 및 시너지 도출</h3><h4>A. 사업적 시너지: \"K-뷰티 성장 수혜를 원료-제조사 파트너십으로 공유\"</h4><ul><li><strong>화장품 전용 실리콘 에멀젼 공급:</strong> 이가이엔씨 실리콘 에멀젼을 뷰티팩토리 스킨케어·메이크업 포뮬레이션에 적용하여 사용감·발림성·피부 보호 성능 향상.</li><li><strong>맞춤 원료 공동 개발:</strong> 양사 모두 소량 시험 생산에 강점 보유 — 신규 화장품 라인 최적화 실리콘 에멀젼 레시피 공동 개발 및 빠른 시제품 제작 가능.</li></ul><h4>B. 거래 적합도 분석</h4><ul><li><strong>강점(Strengths):</strong> 뷰티팩토리의 다수 ODM 브랜드 고객을 통해 이가이엔씨 실리콘 에멀젼이 다양한 화장품에 적용, 간접 마케팅 효과 및 K-뷰티 시장 성장 공동 수혜.</li><li><strong>보완점(Weaknesses):</strong> 화장품용 실리콘 원료는 식약처 고시·INCI 성분 등록 등 인증이 선행 조건, 내용물과의 호환성 테스트(안정성·변색·변취)를 샘플 단계에서 철저히 수행해야 함.</li></ul><hr><h3>3. 실무형 협력 전략 (Action Plan)</h3><h4>전략 1: 화장품용 실리콘 에멀젼 인증 취득 (시장 진입)</h4><ul><li><strong>내용:</strong> 이가이엔씨 실리콘 에멀젼의 화장품 안전 기준(식약처·EU CPNP·미국 FDA) 적합 인증 취득, 뷰티팩토리가 인증 과정에서 수요처 역할 및 데이터 지원.</li><li><strong>효과:</strong> 이가이엔씨의 화장품 원료 시장 진입장벽 해소, 뷰티팩토리의 규제 대응 소재 조달 체계 강화.</li></ul><h4>전략 2: 전용 화장품 실리콘 에멀젼 라인 공동 개발 (수익 모델)</h4><ul><li><strong>내용:</strong> 뷰티팩토리 주력 제품군(스킨케어·메이크업)에 최적화된 전용 실리콘 에멀젼 레시피 공동 개발, NDA 기반 독점 공급 계약 체결.</li><li><strong>효과:</strong> 이가이엔씨 K-뷰티 시장 안정 매출처 확보, 뷰티팩토리 차별화된 원료 포트폴리오 구축.</li></ul><h4>전략 3: 수출 규제 공동 대응 체계 구축 (글로벌 확장)</h4><ul><li><strong>내용:</strong> 뷰티팩토리의 EU·미국 규제 대응 경험과 이가이엔씨의 화학물질 등록·SDS 역량을 결합하여 공동 해외 인허가 대응 체계 구축.</li><li><strong>효과:</strong> 양사 해외 진출 시 인허가 비용·기간 절감 및 K-뷰티 글로벌 시장 공동 공략.</li></ul><hr><h3>4. 종합 제언 및 기대 효과</h3><blockquote>\"본 거래은 이가이엔씨의 &#39;실리콘 소재 기술력&#39;과 뷰티팩토리의 &#39;K-뷰티 제조 네트워크&#39;를 결합하여 국내 화장품 원료 시장의 신규 성장 기회를 양사가 공유하는 전략적 파트너십입니다.\"</blockquote><ul><li><strong>재무적 기대:</strong> 화장품용 실리콘 에멀젼 인증 완료 후 뷰티팩토리 ODM 고객군 전체 적용 시 이가이엔씨 화장품 분야 매출 연간 25~35% 성장 기대.</li><li><strong>전략적 가치:</strong> K-뷰티 성장 트렌드 탑승으로 이가이엔씨의 화장품 원료 시장 포지셔닝 확립 및 글로벌 바이어 접점 확대.</li></ul><hr><p class=\"ai-analysis-note\">※ 담당자 참고사항: 본 분석은 ML 모델링 점수 기반입니다. 화장품용 실리콘 원료 인증(식약처 고시·INCI 등록)이 선행 조건이므로 인증 일정을 최우선으로 수립하고, 내용물 호환성 테스트(안정성·변색·변취)를 파일럿 공급 전에 반드시 완료하시기 바랍니다.</p>"
};

})(window);