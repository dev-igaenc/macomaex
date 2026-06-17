<?php
//error_reporting( E_ALL );
//ini_set( "display_errors", 1 );
$sub_menu = '100000';
require_once './_common.php';

@require_once './safe_check.php';

if (function_exists('social_log_file_delete')) {
    //소셜로그인 디버그 파일 24시간 지난것은 삭제
    social_log_file_delete(86400);
}

$g5['title'] = '관리자메인';
require_once './admin.head.php';

$new_member_rows = 5;
$new_point_rows = 5;
$new_write_rows = 5;

$addtional_content_before = run_replace('adm_index_addtional_content_before', '', $is_admin, $auth, $member);
if ($addtional_content_before) {
    echo $addtional_content_before;
}

if ($is_admin == 'super') {
    $member_sql_common = " from {$g5['member_table']} where (1) ";

    $total_count = (int) sql_fetch(" select count(*) as cnt {$member_sql_common} ")['cnt'];
    $leave_count = (int) sql_fetch(" select count(*) as cnt {$member_sql_common} and mb_leave_date <> '' ")['cnt'];
    $intercept_count = (int) sql_fetch(" select count(*) as cnt {$member_sql_common} and mb_intercept_date <> '' ")['cnt'];
    $member_live_count = (int) sql_fetch(" select count(*) as cnt {$member_sql_common} and mb_leave_date = '' and mb_intercept_date = '' and mb_level < 10 ")['cnt'];
    $member_today_count = (int) sql_fetch(" select count(*) as cnt {$member_sql_common} and DATE(mb_datetime) = '" . G5_TIME_YMD . "' ")['cnt'];

    $day_7ago = date("Y-m-d", strtotime("-6 day"));
    $day_1mago = date("Y-m-d", strtotime("-30 day"));
    $now_date = date("Y-m-d");

    $visit_result = sql_query(" select * from g5_visit_sum where vs_date >= '{$day_7ago}' and vs_date <= '{$now_date}' order by vs_date asc ");
    $chart_date = array();
    $chart_value = array();
    for ($i = 0; $row = sql_fetch_array($visit_result); $i++) {
        $chart_date[] = $row['vs_date'];
        $chart_value[] = (int) $row['vs_count'];
    }
    $chart_label_json = json_encode($chart_date);
    $chart_value_json = json_encode($chart_value);

    $today = sql_fetch(" select sum(vs_count) as total, avg(vs_count) as avg from g5_visit_sum where vs_date >= '{$now_date}' ");
    $week = sql_fetch(" select sum(vs_count) as total, avg(vs_count) as avg from g5_visit_sum where vs_date >= '{$day_7ago}' and vs_date <= '{$now_date}' ");
    $mon_ago = sql_fetch(" select sum(vs_count) as total, avg(vs_count) as avg from g5_visit_sum where vs_date >= '{$day_1mago}' and vs_date <= '{$now_date}' ");
    $total = sql_fetch(" select sum(vs_count) as total, avg(vs_count) as avg from g5_visit_sum ");

    $result1 = sql_query(" SELECT bo_table FROM {$g5['board_table']} ");
    $board_count = sql_num_rows($result1);
    $all_art = 0;
    $today_art = 0;
    $week_art = 0;
    $month_art = 0;
    for ($i = 0; $row2 = sql_fetch_array($result1); $i++) {
        $article = sql_fetch(" select count(wr_id) as ctt from g5_write_{$row2['bo_table']} where wr_is_comment = '0' ");
        $article2 = sql_fetch(" select count(wr_id) as ctt from g5_write_{$row2['bo_table']} where wr_is_comment = '0' and DATE(wr_datetime) = '{$now_date}' ");
        $article3 = sql_fetch(" select count(wr_id) as ctt from g5_write_{$row2['bo_table']} where wr_is_comment = '0' and DATE(wr_datetime) >= '{$day_7ago}' and DATE(wr_datetime) <= '{$now_date}' ");
        $article4 = sql_fetch(" select count(wr_id) as ctt from g5_write_{$row2['bo_table']} where wr_is_comment = '0' and DATE(wr_datetime) >= '{$day_1mago}' and DATE(wr_datetime) <= '{$now_date}' ");

        $all_art += (int) $article['ctt'];
        $today_art += (int) $article2['ctt'];
        $week_art += (int) $article3['ctt'];
        $month_art += (int) $article4['ctt'];
    }
    $allavg_art = $board_count ? ($all_art / $board_count) : 0;
    $weekavg_art = $week_art / 7;
    $monthavg_art = $month_art / 30;

    $contact_table = $g5['write_prefix'] . 'Contact';
    $contact_table_exists = false;
    $contact_check = sql_query(" show tables like '{$contact_table}' ", false);
    if ($contact_check && sql_num_rows($contact_check)) {
        $contact_table_exists = true;
    }

    $contact_total_count = 0;
    $contact_today_count = 0;
    $contact_pending_count = 0;
    $recent_contacts = false;
    if ($contact_table_exists) {
        $contact_total_count = (int) sql_fetch(" select count(*) as cnt from {$contact_table} where wr_is_comment = '0' ")['cnt'];
        $contact_today_count = (int) sql_fetch(" select count(*) as cnt from {$contact_table} where wr_is_comment = '0' and DATE(wr_datetime) = '{$now_date}' ")['cnt'];
        $contact_pending_count = (int) sql_fetch(" select count(*) as cnt
            from {$contact_table} a
            where a.wr_is_comment = '0'
              and not exists (
                  select 1
                  from {$contact_table} c
                  where c.wr_parent = a.wr_id
                    and c.wr_is_comment = '1'
              ) ")['cnt'];
        $recent_contacts = sql_query(" select a.wr_id, a.wr_subject, a.wr_name, a.wr_datetime,
            (
                select count(*)
                from {$contact_table} c
                where c.wr_parent = a.wr_id
                  and c.wr_is_comment = '1'
            ) as comment_cnt
            from {$contact_table} a
            where a.wr_is_comment = '0'
            order by a.wr_id desc
            limit 4 ");
    }

    $payment_table_exists = false;
    $payment_check = sql_query(" show tables like 'g5_membership_payment' ", false);
    if ($payment_check && sql_num_rows($payment_check)) {
        $payment_table_exists = true;
    }

    $payment_complete_count = 0;
    $payment_today_count = 0;
    $payment_today_amount = 0;
    $recent_payments = false;
    if ($payment_table_exists) {
        $payment_summary = sql_fetch(" select
                sum(case when complete_yn = 'Y' then 1 else 0 end) as complete_cnt,
                sum(case when complete_yn = 'Y' and DATE(IFNULL(complete_date, regdate)) = '{$now_date}' then 1 else 0 end) as today_cnt,
                sum(case when complete_yn = 'Y' and DATE(IFNULL(complete_date, regdate)) = '{$now_date}' then cast(replace(ifnull(price, '0'), ',', '') as unsigned) else 0 end) as today_amount
            from g5_membership_payment ");
        $payment_complete_count = (int) $payment_summary['complete_cnt'];
        $payment_today_count = (int) $payment_summary['today_cnt'];
        $payment_today_amount = (int) $payment_summary['today_amount'];
        $recent_payments = sql_query(" select *,
                (select title from g5_membership_manage where no = membership_parent_no) as title
            from g5_membership_payment
            order by no desc
            limit 4 ");
    }

    $error_table = function_exists('system_error_log_table_name') ? system_error_log_table_name() : (G5_TABLE_PREFIX . 'system_error_log');
    $error_table_exists = function_exists('system_error_log_table_exists') ? system_error_log_table_exists() : false;
    if (!$error_table_exists) {
        $error_check = sql_query(" show tables like '{$error_table}' ", false);
        if ($error_check && sql_num_rows($error_check)) {
            $error_table_exists = true;
        }
    }

    $error_total_week_count = 0;
    $error_pending_count = 0;
    if ($error_table_exists) {
        $error_summary = sql_fetch(" select
                count(*) as total_cnt,
                sum(case when ifnull(el_resolved, 0) = 0 then 1 else 0 end) as unresolved_cnt,
                sum(case when el_datetime >= '{$day_7ago} 00:00:00' then 1 else 0 end) as week_cnt
            from {$error_table} ");
        $error_total_week_count = (int) $error_summary['week_cnt'];
        $error_pending_count = (int) $error_summary['unresolved_cnt'];
    }
    ?>

    <style>
    .admin-summary-row {
        margin: 0 -10px 18px;
    }
    .admin-summary-col {
        padding: 0 10px;
    }
    .admin-summary-card {
        border: 1px solid #e3e7ec;
        border-radius: 12px;
        background: #fff;
        padding: 18px 20px;
        min-height: 126px;
    }
    .admin-summary-card h3 {
        margin: 0 0 10px;
        font-size: 13px;
        font-weight: 700;
        color: #667482;
    }
    .admin-summary-card strong {
        display: block;
        font-size: 28px;
        line-height: 1.1;
        color: #2a3f54;
    }
    .admin-summary-card p {
        margin: 10px 0 0;
        font-size: 12px;
        color: #7b8894;
        line-height: 1.6;
    }
    .admin-summary-card.is-accent {
        border-top: 3px solid #26b99a;
    }
    .admin-mini-table th,
    .admin-mini-table td {
        padding-top: 10px !important;
        padding-bottom: 10px !important;
        vertical-align: middle !important;
    }
    .admin-mini-table .td_status,
    .admin-mini-table .td_datetime,
    .admin-mini-table .td_num {
        white-space: nowrap;
    }
    .admin-mini-badge {
        display: inline-block;
        min-width: 44px;
        padding: 4px 8px;
        border-radius: 4px;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.2;
        text-align: center;
        white-space: nowrap;
    }
    .admin-mini-badge.is-wait {
        background: #f0ad4e;
    }
    .admin-mini-badge.is-done {
        background: #26b99a;
    }
    .admin-mini-badge.is-idle {
        background: #9aa7b4;
    }
    @media (max-width: 1199px) {
        .admin-summary-col { margin-bottom: 16px; }
    }
    @media (max-width: 767px) {
        .admin-summary-card { min-height: 0; }
    }
    </style>

    <div class="row admin-summary-row">
        <div class="col-md-3 col-sm-6 col-xs-12 admin-summary-col">
            <div class="admin-summary-card is-accent">
                <h3>회원 현황</h3>
                <strong><?php echo number_format($member_live_count); ?></strong>
                <p>전체 <?php echo number_format($total_count); ?>명 · 오늘 가입 <?php echo number_format($member_today_count); ?>명 · 차단 <?php echo number_format($intercept_count); ?>명</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 admin-summary-col">
            <div class="admin-summary-card">
                <h3>방문 현황</h3>
                <strong><?php echo number_format((int) $today['total']); ?></strong>
                <p>최근 7일 평균 <?php echo number_format((float) $week['avg'], 1); ?>건 · 전체 누적 <?php echo number_format((int) $total['total']); ?>건</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 admin-summary-col">
            <div class="admin-summary-card">
                <h3>고객 문의</h3>
                <strong><?php echo number_format($contact_total_count); ?></strong>
                <p>미답변 <?php echo number_format($contact_pending_count); ?>건 · 오늘 등록 <?php echo number_format($contact_today_count); ?>건</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 admin-summary-col">
            <div class="admin-summary-card">
                <h3>결제 / 오류</h3>
                <strong><?php echo number_format($payment_today_count); ?></strong>
                <p>오늘 결제 <?php echo number_format($payment_today_amount); ?>원 · 미해결 에러 <?php echo number_format($error_pending_count); ?>건</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-sm-6 ">
            <div class="x_panel tile fixed_height_500">
                <div class="x_title">
                    <h2>최근 일주일 방문 건수</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                        <li><a class="close-link"><i class="fa fa-close"></i></a></li>
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <h4>( <?php echo $day_7ago; ?> ~ <?php echo $now_date; ?> )</h4>
                    <canvas id="dashboardVisitChart" style="height:374px; width:100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 ">
            <div class="x_panel fixed_height_500">
                <div class="x_title">
                    <h2>기간별 현황</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                        <li><a class="close-link"><i class="fa fa-close"></i></a></li>
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="table-responsive">
                        <table class="table table-striped jambo_table bulk_action">
                            <thead>
                            <tr class="headings">
                                <th class="column-title">날짜</th>
                                <th class="column-title">방문수</th>
                                <th class="column-title">게시물 등록수</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="even pointer">
                                <td><?php echo str_replace("-", ".", substr($now_date, 5)); ?> (오늘)</td>
                                <td><?php echo number_format((int) $today['total']); ?> 건</td>
                                <td><?php echo number_format($today_art); ?> 건</td>
                            </tr>
                            <tr class="odd pointer">
                                <td>최근 7일 합계</td>
                                <td><?php echo number_format((int) $week['total']); ?> 건</td>
                                <td><?php echo number_format($week_art); ?> 건</td>
                            </tr>
                            <tr class="even pointer">
                                <td>최근 7일 평균</td>
                                <td><?php echo number_format((float) $week['avg'], 1); ?> 건</td>
                                <td><?php echo number_format($weekavg_art, 1); ?> 건</td>
                            </tr>
                            <tr class="odd pointer">
                                <td>최근 30일 합계</td>
                                <td><?php echo number_format((int) $mon_ago['total']); ?> 건</td>
                                <td><?php echo number_format($month_art); ?> 건</td>
                            </tr>
                            <tr class="even pointer">
                                <td>최근 30일 평균</td>
                                <td><?php echo number_format((float) $mon_ago['avg'], 1); ?> 건</td>
                                <td><?php echo number_format($monthavg_art, 1); ?> 건</td>
                            </tr>
                            <tr class="odd pointer">
                                <td>전체</td>
                                <td><?php echo number_format((int) $total['total']); ?> 건</td>
                                <td><?php echo number_format($all_art); ?> 건</td>
                            </tr>
                            <tr class="even pointer">
                                <td>전체 평균</td>
                                <td><?php echo number_format((float) $total['avg'], 1); ?> 건</td>
                                <td><?php echo number_format($allavg_art, 1); ?> 건</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="x_panel tile">
                <div class="x_title">
                    <div class="col-md-6">
                        <h2>최근 고객 문의</h2>
                    </div>
                    <div class="col-md-6">
                        <a href="./bbs/board.php?bo_table=Contact" class="pull-right buttonPrevious btn btn-primary">문의 관리</a>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="tbl_head01 tbl_wrap">
                        <table class="admin-mini-table">
                            <caption>최근 고객 문의</caption>
                            <thead>
                            <tr>
                                <th scope="col">제목</th>
                                <th scope="col">작성자</th>
                                <th scope="col">답변</th>
                                <th scope="col">일시</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($contact_table_exists) { ?>
                                <?php for ($i = 0; $row = sql_fetch_array($recent_contacts); $i++) { ?>
                                    <tr>
                                        <td>
                                            <a href="./bbs/board.php?bo_table=Contact&amp;wr_id=<?php echo (int) $row['wr_id']; ?>">
                                                <?php echo htmlspecialchars2(cut_str($row['wr_subject'], 60)); ?>
                                            </a>
                                        </td>
                                        <td class="td_name"><?php echo htmlspecialchars2($row['wr_name']); ?></td>
                                        <td class="td_status">
                                            <?php if ((int) $row['comment_cnt'] > 0) { ?>
                                                <span class="admin-mini-badge is-done">완료</span>
                                            <?php } else { ?>
                                                <span class="admin-mini-badge is-wait">대기</span>
                                            <?php } ?>
                                        </td>
                                        <td class="td_datetime"><?php echo substr($row['wr_datetime'], 0, 10); ?></td>
                                    </tr>
                                <?php } ?>
                                <?php if ($i == 0) { ?>
                                    <tr><td colspan="4" class="empty_table">문의가 없습니다.</td></tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr><td colspan="4" class="empty_table">문의 게시판을 찾을 수 없습니다.</td></tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-12">
            <div class="x_panel tile">
                <div class="x_title">
                    <div class="col-md-6">
                        <h2>최근 결제</h2>
                    </div>
                    <div class="col-md-6">
                        <a href="./payment.list.php" class="pull-right buttonPrevious btn btn-primary">결제 관리</a>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="tbl_head01 tbl_wrap">
                        <table class="admin-mini-table">
                            <caption>최근 결제</caption>
                            <thead>
                            <tr>
                                <th scope="col">아이디</th>
                                <th scope="col">상품</th>
                                <th scope="col">금액</th>
                                <th scope="col">상태</th>
                                <th scope="col">일시</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($payment_table_exists) { ?>
                                <?php for ($i = 0; $row = sql_fetch_array($recent_payments); $i++) { ?>
                                    <tr>
                                        <td class="td_name">
                                            <a href="./payment.view.php?no=<?php echo (int) $row['no']; ?>">
                                                <?php echo htmlspecialchars2($row['uid'] ? $row['uid'] : $row['buyer_name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars2(cut_str($row['title'], 28)); ?></td>
                                        <td class="td_num"><?php echo number_format((int) str_replace(',', '', (string) $row['price'])); ?>원</td>
                                        <td class="td_status">
                                            <?php if ($row['complete_yn'] === 'Y') { ?>
                                                <span class="admin-mini-badge is-done">승인</span>
                                            <?php } else { ?>
                                                <span class="admin-mini-badge is-idle">대기</span>
                                            <?php } ?>
                                        </td>
                                        <td class="td_datetime"><?php echo substr($row['regdate'], 0, 10); ?></td>
                                    </tr>
                                <?php } ?>
                                <?php if ($i == 0) { ?>
                                    <tr><td colspan="5" class="empty_table">결제 내역이 없습니다.</td></tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr><td colspan="5" class="empty_table">결제 테이블을 찾을 수 없습니다.</td></tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    $(function () {
        var ctx = document.getElementById("dashboardVisitChart");
        if (!ctx) {
            return;
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo $chart_label_json ? $chart_label_json : '[]'; ?>,
                datasets: [{
                    label: '방문수',
                    data: <?php echo $chart_value_json ? $chart_value_json : '[]'; ?>,
                    backgroundColor: '#26B99A'
                }]
            },
            options: {
                responsive: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    });
    </script>
    <?php
} //endif

$addtional_content_after = run_replace('adm_index_addtional_content_after', '', $is_admin, $auth, $member);
if ($addtional_content_after) {
    echo $addtional_content_after;
}
require_once './admin.tail.php';
