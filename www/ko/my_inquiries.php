<?php
include_once('./_common.php');

$menu_context = isset($_GET['menu']) && $_GET['menu'] === 'support' ? 'support' : 'mypage';
if ($menu_context === 'support') {
    $board['bo_10'] = '5';
    $board['bo_9'] = '4';
} else {
    $board['bo_10'] = '6';
    $board['bo_9'] = '7';
}

// 회원 본인 문의만 조회 가능
login_chk();

if (!function_exists('my_inquiries_build_url')) {
    function my_inquiries_build_url($params = array())
    {
        return '/account/inquiries' . ($params ? '?' . http_build_query($params) : '');
    }
}

if (!function_exists('my_inquiries_status_text')) {
    function my_inquiries_status_text($comment_count)
    {
        return ((int) $comment_count > 0) ? '답변완료' : '답변대기';
    }
}

$member_id = trim((string) $member['mb_id']);
$member_id_sql = sql_real_escape_string($member_id);
$contact_table = $g5['write_prefix'] . 'Contact';
$contact_table_exists = false;
$table_check = sql_query(" show tables like '{$contact_table}' ", false);
if ($table_check && sql_num_rows($table_check)) {
    $contact_table_exists = true;
}

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$page_rows = 10;
$from_record = ($page - 1) * $page_rows;
$stx = trim((string) ($_GET['stx'] ?? ''));
$wr_id = isset($_GET['wr_id']) ? (int) $_GET['wr_id'] : 0;
$write_url = '/bbs/write.php?bo_table=Contact';

$list_params = array('menu' => $menu_context);
if ($stx !== '') {
    $list_params['stx'] = $stx;
}
if ($page > 1) {
    $list_params['page'] = $page;
}
$list_url = my_inquiries_build_url($list_params);

$where_sql = " where wr_is_comment = '0' and LOWER(TRIM(mb_id)) = LOWER(TRIM('{$member_id_sql}')) ";
if ($stx !== '') {
    $stx_sql = sql_real_escape_string($stx);
    $where_sql .= " and (
        wr_subject like '%{$stx_sql}%'
        or wr_content like '%{$stx_sql}%'
        or ca_name like '%{$stx_sql}%'
    ) ";
}

$rows = array();
$detail = array();
$detail_files = array('count' => 0);
$reply_rows = array();
$total_count = 0;
$total_page = 1;
$write_pages = '';

if ($contact_table_exists) {
    $count_row = sql_fetch(" select count(*) as cnt from {$contact_table} {$where_sql} ");
    $total_count = isset($count_row['cnt']) ? (int) $count_row['cnt'] : 0;
    $total_page = max(1, (int) ceil($total_count / $page_rows));

    if ($page > $total_page) {
        $page = $total_page;
        $from_record = ($page - 1) * $page_rows;

        $list_params = array('menu' => $menu_context);
        if ($stx !== '') {
            $list_params['stx'] = $stx;
        }
        if ($page > 1) {
            $list_params['page'] = $page;
        }
        $list_url = my_inquiries_build_url($list_params);
    }

    $list_sql = " select
            wr_id,
            ca_name,
            wr_subject,
            wr_datetime,
            wr_comment
        from {$contact_table}
        {$where_sql}
        order by wr_id desc
        limit {$from_record}, {$page_rows} ";
    $list_result = sql_query($list_sql, false);
    if ($list_result) {
        while ($row = sql_fetch_array($list_result)) {
            $rows[] = $row;
        }
    }

    $paging_params = array('menu' => $menu_context);
    if ($stx !== '') {
        $paging_params['stx'] = $stx;
    }
    $write_pages = get_paging(
        G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'],
        $page,
        $total_page,
        my_inquiries_build_url($paging_params)
    );

    if ($wr_id > 0) {
        $detail = sql_fetch(" select *
            from {$contact_table}
            where wr_id = '{$wr_id}'
              and wr_is_comment = '0'
              and LOWER(TRIM(mb_id)) = LOWER(TRIM('{$member_id_sql}'))
            limit 1 ");

        if (!isset($detail['wr_id'])) {
            alert('접근할 수 없는 문의입니다.', my_inquiries_build_url(array('menu' => $menu_context)));
        }

        set_session('ss_view_Contact_' . $wr_id, true);

        $detail_files = get_file('Contact', $wr_id);
        if (!is_array($detail_files)) {
            $detail_files = array('count' => 0);
        }

        $reply_result = sql_query(" select
                wr_id,
                wr_name,
                wr_content,
                wr_datetime
            from {$contact_table}
            where wr_parent = '{$wr_id}'
              and wr_is_comment = '1'
            order by wr_comment, wr_comment_reply ", false);
        if ($reply_result) {
            while ($reply = sql_fetch_array($reply_result)) {
                $reply_rows[] = $reply;
            }
        }
    }
}

$g5['title'] = '내 고객문의';
$notice_style_path = G5_THEME_PATH . '/skin/board/MS_NOTICE_NORMAL_LANG/style.css';
$notice_style_url = G5_THEME_URL . '/skin/board/MS_NOTICE_NORMAL_LANG/style.css';
if (file_exists($notice_style_path)) {
    add_stylesheet('<link rel="stylesheet" href="' . $notice_style_url . '?ver=' . filemtime($notice_style_path) . '">', 200);
}

include_once(G5_THEME_PATH . '/head.php');
?>

<style>
#bo_list .bo_tit {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

#bo_list .bo_tit a {
    flex: 1 1 auto;
    min-width: 0;
}

#bo_list .cnt_cmt {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 20px;
    padding: 0 7px;
    line-height: 1;
    margin-left: 0;
    position: static;
    flex: 0 0 auto;
}

#bo_v .board_write {
    margin-bottom: 24px;
}
</style>

<?php if (isset($detail['wr_id'])) { ?>
<div id="bo_v">
    <div class="board_view">
        <table>
            <thead>
            <tr>
                <th>
                    <?php if (!empty($detail['ca_name'])) { ?>
                    <p class="view_cate"><?php echo get_text($detail['ca_name']); ?></p>
                    <?php } ?>

                    <div class="view_title"><?php echo cut_str(get_text($detail['wr_subject']), 9999); ?></div>
                    <div class="view_info">
                        <div class="view_txt">
                            <p class="txt"><strong>작성자</strong>&nbsp;<?php echo get_text($detail['wr_name']); ?></p>
                            <p class="txt"><strong>등록일</strong>&nbsp;<?php echo date('Y.m.d', strtotime($detail['wr_datetime'])); ?></p>
                            <p class="txt"><strong>상태</strong>&nbsp;<?php echo my_inquiries_status_text($detail['wr_comment']); ?></p>
                        </div>
                    </div>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="view_cont">
                    <div class="board_write">
                        <table>
                            <tbody>
                            <tr>
                                <th scope="row">회사명</th>
                                <td><?php echo $detail['wr_1'] ? get_text($detail['wr_1']) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th scope="row">연락처</th>
                                <td><?php echo $detail['wr_10'] ? get_text($detail['wr_10']) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th scope="row">이메일</th>
                                <td><?php echo $detail['wr_email'] ? get_text($detail['wr_email']) : '-'; ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="bo_v_con"><?php echo conv_content($detail['wr_content'], 0, 'wr_content'); ?></div>

                    <?php if (!empty($detail_files['count'])) { ?>
                    <div class="view_file">
                        <?php for ($i = 0; $i < $detail_files['count']; $i++) { ?>
                        <a href="<?php echo $detail_files[$i]['href']; ?>"><?php echo get_text($detail_files[$i]['source']); ?></a>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="board_btn end">
        <ul class="board_user">
            <li><a href="<?php echo htmlspecialchars($list_url, ENT_QUOTES, 'UTF-8'); ?>" class="click write big">목록</a></li>
            <li><a href="<?php echo $write_url; ?>" class="click write big">문의작성</a></li>
        </ul>
    </div>

    <div id="bo_vc">
        <h2>답변</h2>
        <?php if ($reply_rows) { ?>
            <?php for ($i = 0; $i < count($reply_rows); $i++) { ?>
            <?php $reply = $reply_rows[$i]; ?>
        <article>
            <div class="cm_wrap">
                <header>
                    <div class="bo_vc_hdinfo">
                        <strong class="sv_member"><?php echo get_text($reply['wr_name'] ?: '관리자'); ?></strong>
                        <span><?php echo date('Y.m.d H:i', strtotime($reply['wr_datetime'])); ?></span>
                    </div>
                </header>
                <p class="cmt_contents"><?php echo conv_content($reply['wr_content'], 0, 'wr_content'); ?></p>
            </div>
        </article>
            <?php } ?>
        <?php } else { ?>
        <p id="bo_vc_empty">아직 답변이 등록되지 않았습니다.</p>
        <?php } ?>
    </div>
</div>
<?php } ?>

<div id="bo_list">
    <form method="get" action="/account/inquiries">
        <input type="hidden" name="menu" value="<?php echo htmlspecialchars($menu_context, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="board_search">
            <select name="sfl" id="sfl">
                <option value="subject_content">제목+내용</option>
            </select>
            <input type="text" name="stx" value="<?php echo htmlspecialchars($stx, ENT_QUOTES, 'UTF-8'); ?>" required id="stx" class="sch_input" size="25" maxlength="50" placeholder="검색어를 입력해주세요">
            <input type="submit" value="검색">
        </div>
    </form>

    <div class="board_btn">
        <div class="total">Total <strong><?php echo number_format($total_count); ?></strong>, &nbsp; Page <strong><?php echo $page; ?></strong></div>
        <?php if (!isset($detail['wr_id'])) { ?>
        <ul class="board_user">
            <li><a href="<?php echo $write_url; ?>" class="click write">문의작성</a></li>
        </ul>
        <?php } ?>
    </div>

    <div class="board_list">
        <table>
            <colgroup>
                <col width="7%">
                <col width="*">
                <col width="15%">
            </colgroup>
            <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">제목</th>
                <th scope="col">등록일</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($rows) { ?>
                <?php for ($i = 0; $i < count($rows); $i++) { ?>
                <?php
                $row = $rows[$i];
                $row_number = $total_count - (($page - 1) * $page_rows) - $i;
                $row_url_params = array('menu' => $menu_context, 'wr_id' => (int) $row['wr_id']);
                if ($stx !== '') {
                    $row_url_params['stx'] = $stx;
                }
                if ($page > 1) {
                    $row_url_params['page'] = $page;
                }
                $row_url = my_inquiries_build_url($row_url_params);
                $status_text = my_inquiries_status_text($row['wr_comment']);
                ?>
            <tr class="<?php echo $i % 2 === 0 ? 'even' : ''; ?>">
                <td class="td_num2">
                    <?php if (isset($detail['wr_id']) && (int) $detail['wr_id'] === (int) $row['wr_id']) { ?>
                    <span class="bo_current">열람중</span>
                    <?php } else { ?>
                    <?php echo number_format($row_number); ?>
                    <?php } ?>
                </td>
                <td class="td_subject">
                    <?php if (!empty($row['ca_name'])) { ?>
                    <span class="bo_cate_link"><?php echo get_text($row['ca_name']); ?></span>
                    <?php } ?>
                    <div class="bo_tit">
                        <a href="<?php echo htmlspecialchars($row_url, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo get_text($row['wr_subject']); ?>
                        </a>
                        <span class="cnt_cmt"><?php echo $status_text; ?></span>
                    </div>
                </td>
                <td class="btm"><?php echo date('Y.m.d', strtotime($row['wr_datetime'])); ?></td>
            </tr>
                <?php } ?>
            <?php } else { ?>
            <tr class="noPost">
                <td colspan="3"><?php echo $contact_table_exists ? '게시물이 없습니다.' : '문의 데이터를 불러올 수 없습니다.'; ?></td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <?php echo $write_pages; ?>
</div>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
