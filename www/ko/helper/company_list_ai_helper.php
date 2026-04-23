<?php
// helper/company_list_ai_helper.php
// 목적:
// - AI 목록(검색 + 추천목록) 화면에서 사용하는 서버 헬퍼.
//
// 이 파일에 들어올 내용(예정):
// - 목록 recommend 요청 파라미터 검증(kedcd, bzc_cd, trade_type 등)
// - env 로드/조회 유틸(ML_API_URL, ML_API_KEY)
// - EC2 /recommend 호출 함수(cURL, timeout, retry)
// - buy 우선 + sell prefetch 응답 포맷 정규화
// - company_ml_log 저장/조회(당일 캐시 정책 포함)
// - 목록 카드 렌더용 데이터 매핑(top3, list, total, radar, one_line)
?>

