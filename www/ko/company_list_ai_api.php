<?php
// company_list_ai_api.php
// 목적:
// - 프론트(company_list_ai.php)가 호출하는 ML 추천 전용 API 엔드포인트.
//
// 이 파일에 들어올 내용(예정):
// - action=recommend 요청 수신 및 파라미터 검증(kedcd 등)
// - helper/company_list_ai_helper.php 호출
// - buy 우선 응답 + sell prefetch 전략을 위한 응답 포맷 구성
// - 오류 코드/메시지 표준화(400/502/500)
// - JSON UTF-8 응답 고정
?>
