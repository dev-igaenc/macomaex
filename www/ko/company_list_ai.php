<?php
// company_list_ai.php
// 목적:
// - 사업자번호를 입력받아 AI 추천 흐름을 시작하고,
//   같은 페이지에서 AI 추천 목록까지 렌더하는 화면.
//
// 이 파일에 들어올 내용(예정):
// - ai_recommend.html 시안 마크업/스타일 이식 (파일명만 서비스 네이밍으로 변경)
// - 사업자번호 입력 검증(10자리)
// - Kodata 확인(get_set.php: main_comp_chk) 호출
// - 확인 모달/가이드 모달/로딩 UI 처리
// - buy 우선 + sell prefetch 결과를 화면 목록(TOP3/ALL)에 렌더
// - 카드 클릭 시 company_detail_ai.php로 이동
?>
