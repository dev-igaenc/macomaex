<?php
// helper/company_detail_ai_helper.php
// 목적:
// - AI 상세 화면(company_detail_ai.php)에서 사용하는 서버 헬퍼.
//
// 이 파일에 들어올 내용(예정):
// - 상세 진입 파라미터 검증(kedcd, trade_type, candidate_type 등)
// - 목록에서 전달된 데이터(session/query) 복원/검증 보조
// - 상세 우측 카드용 데이터 정규화(score, radar, reasons, tags)
// - 상세 페이지 오류/빈값 폴백 처리
// - (추후) analyze/LLM 호출 시 요청/응답 가공 로직
?>

