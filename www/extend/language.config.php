<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가;

if (preg_match("/_en|\/en\//i", $_SERVER['REQUEST_URI'])){
	//echo "영";

	// 검색
	$lang_sch_long = "Please enter a search term";
	$lang_sch_btn = "SEARCH";

	// 리스트
	$lang_list_notice = "NOTICE";
	$lang_list_subject = "Subject";
	$lang_list_name = "Writer";
	$lang_list_date = "Date";
	$lang_list_hit = "Views";
	$lang_list_file = "File";
	$lang_list_state = "State";

	$lang_answer = "Answer";
	$lang_answer_c = "Completed";
	$lang_answer_w = "Waiting";

	$lang_event_date = "Event Duration";
	$lang_event_state = "Standby";
	$lang_event_state2 = "Progress";
	$lang_event_state3 = "Shutdown";

	$lang_list_no = "There is no registered article.";

	// 상세
	$lang_view_name = "Writer";
	$lang_view_date = "Date";

	$lang_view_prev = "Prev";
	$lang_view_prev_long = "There is no previous post.";
	$lang_view_next = "Next";
	$lang_view_next_long = "There is no next post.";

	// 코멘트
	$lang_comment_title = "Comments";
	$lang_comment_name = "Name";
	$lang_comment_pass = "Password";
	$lang_comment_secret = "Secret";
	$lang_comment_contents = "Please enter a comment.";
	$lang_comment_ok = "Registering";
	$lang_comment_del = "Delete";
	$lang_comment_modify = "Modify";
	$lang_comment_no = "There are no registered comments.";

	// 쓰기
	$lang_write_name = "Name";
	$lang_write_pass = "Password";
	$lang_write_subject = "Subject";
	$lang_write_secret = "Secret";
	$lang_write_content = "Content";
	$lang_write_file = "Attached file";
	$lang_write_auto = "Prevention of automatic registration";

	$lang_write_direct = "Direct input";

	// 버튼
	$lang_btn_list = "List";
	$lang_btn_modify = "Modify";
	$lang_btn_write = "Write";
	$lang_btn_cancel = "Cancel";
	$lang_btn_delete = "Delete";
	$lang_btn_ok = "Confirm";
	$lang_btn_register = "Register";
	$lang_btn_more = "MORE";

	// 비밀번호 확인 페이지
	$lang_password_title2 = "Delete a text";
	$lang_password_title3 = "Modifying the text";
	$lang_password_text = "<strong>This is a post protected by the secret feature.</strong>Only author and administrator accessible.<br> If it's you, please enter your password.";
	$lang_password_text2 = "<strong>Only the author can delete the text.</strong>If you are the author, you can delete the text by <br />entering the password you entered when you wrote the text.";
	$lang_password_text3 = "<strong>Only the author can modify the text.</strong>If you are the author, you can modify the text by <br />entering the password you entered when you wrote the text.";
	$lang_password_input = "password";
	// 비밀번호 확인 페이지
}else if (preg_match('/_cn|\/cn\//i', $_SERVER['REQUEST_URI'])){
	//echo "중";
}else if (preg_match('/_jp|\/jp\//i', $_SERVER['REQUEST_URI'])){
	//echo "일";
}else{
	//echo "국";
	// 검색
	$lang_sch_long = "검색어를 입력해주세요";
	$lang_sch_btn = "검색";

	// 리스트
	$lang_list_notice = "공지";
	$lang_list_subject = "제목";
	$lang_list_name = "작성자";
	$lang_list_date = "작성일";
	$lang_list_hit = "조회수";
	$lang_list_file = "파일";
	$lang_list_state = "상태";

	$lang_answer = "답변";
	$lang_answer_c = "답변완료";
	$lang_answer_w = "답변대기";

	$lang_event_date = "이벤트 기간";
	$lang_event_state = "대기";
	$lang_event_state2 = "진행중";
	$lang_event_state3 = "종료";

	$lang_list_no = "등록된 글이 없습니다.";

	// 상세
	$lang_view_name = "작성자";
	$lang_view_date = "작성일";

	$lang_view_prev = "이전글";
	$lang_view_prev_long = "이전글이 없습니다.";
	$lang_view_next = "다음글";
	$lang_view_next_long = "다음글이 없습니다.";

	// 코멘트
	$lang_comment_title = "댓글";
	$lang_comment_name = "이름";
	$lang_comment_pass = "비밀번호";
	$lang_comment_secret = "비밀글";
	$lang_comment_contents = "댓글을 입력해주세요.";
	$lang_comment_ok = "댓글등록";
	$lang_comment_del = "삭제";
	$lang_comment_modify = "수정";
	$lang_comment_no = "등록된 댓글이 없습니다.";

	// 쓰기
	$lang_write_name = "이름";
	$lang_write_pass = "비밀번호";
	$lang_write_subject = "제목";
	$lang_write_secret = "비밀글";
	$lang_write_content = "내용";
	$lang_write_file = "첨부파일";
	$lang_write_auto = "자동등록방지";

	$lang_write_direct = "직접입력";

	// 버튼
	$lang_btn_list = "목록";
	$lang_btn_modify = "수정";
	$lang_btn_write = "글쓰기";
	$lang_btn_cancel = "취소";
	$lang_btn_delete = "삭제";
	$lang_btn_ok = "확인";
	$lang_btn_register = "등록";
	$lang_btn_more = "더보기";

	// 비밀번호 확인 페이지
	$lang_password_title2 = "글 삭제";
	$lang_password_title3 = "글 수정";
	$lang_password_text = "<strong>비밀글 기능으로 보호된 글입니다.</strong>작성자와 관리자만 열람하실 수 있습니다.<br /> 본인이라면 비밀번호를 입력하세요.";
	$lang_password_text2 = "<strong>작성자만 글을 삭제할 수 있습니다.</strong>작성자 본인이라면, 글 작성시 입력한 비밀번호를 <br />입력하여 글을 수정할 수 있습니다.";
	$lang_password_text3 = "<strong>작성자만 글을 수정할 수 있습니다.</strong>작성자 본인이라면, 글 작성시 입력한 비밀번호를 <br />입력하여 글을 삭제할 수 있습니다.";
	$lang_password_input = "비밀번호";
}