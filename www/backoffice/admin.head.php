<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if(!$is_admin) {
	$msg = '권한이 없습니다.';
	if ($return) {
		return $msg;
	} else {
		alert($msg);
	}
}
if($sub_menu!="300250"){
	auth_check_menu($auth, $sub_menu, 'r');
}

// 읽지 않은 쪽지가 있다면
if ($is_member) {
	if( isset($member['mb_memo_cnt']) ){
		$memo_not_read = $member['mb_memo_cnt'];
	} else {
		$memo_not_read = get_memo_not_read($member['mb_id']);
	}
	
	$mb_scrap_cnt = isset($member['mb_scrap_cnt']) ? (int) $member['mb_scrap_cnt'] : '';
	$sql = " select count(*) as cnt from {$g5['auth_table']} where mb_id = '{$member['mb_id']}' ";
	$row = sql_fetch($sql);
	if ($row['cnt'])
		$is_auth = true;
}

$kind = 'recv';
$unkind = 'send';
$kind_title = '받은';
$recv_img = 'on';
$send_img = 'off';

$sql = " select count(*) as cnt from {$g5['memo_table']} where me_{$kind}_mb_id = '{$member['mb_id']}' and me_type = '$kind' ";
$row = sql_fetch($sql);
$total_count = isset($row['cnt']) ? (int)$row['cnt'] : 0;

$list = array();

$sql = " select a.*, b.mb_id, b.mb_nick, b.mb_name, b.mb_email, b.mb_homepage
            from {$g5['memo_table']} a
            left join {$g5['member_table']} b on (a.me_{$unkind}_mb_id = b.mb_id)
            where a.me_{$kind}_mb_id = '{$member['mb_id']}' and a.me_type = '$kind'
            order by a.me_id desc limit 5";

$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $list[$i] = $row;

    $mb_id = $row["me_{$unkind}_mb_id"];

    if ($row['mb_nick'])
        $mb_nick = $row['mb_nick'];
    else
        $mb_nick = '정보없음';

    //$name = get_sideview($row['mb_id'], $row['mb_nick'], $row['mb_email'], $row['mb_homepage']);
	$name=$row['mb_name'];


    if (substr($row['me_read_datetime'],0,1) == 0)
        $read_datetime = '아직 읽지 않음';
    else
        $read_datetime = substr($row['me_read_datetime'],2,14);

    $send_datetime = substr($row['me_send_datetime'],2,14);

    $list[$i]['mb_id'] = $mb_id;
    $list[$i]['name'] = $name;
    $list[$i]['send_datetime'] = $send_datetime;
    $list[$i]['read_datetime'] = $read_datetime;
    $list[$i]['view_href'] = './memo_view.php?me_id='.$row['me_id'].'&amp;kind='.$kind.'&amp;page='.$page;
    $list[$i]['del_href'] = './memo_delete.php?me_id='.$row['me_id'].'&amp;token='.$token.'&amp;kind='.$kind;
}

$g5_debug['php']['begin_time'] = $begin_time = get_microtime();

$files = glob(G5_ADMIN_PATH . '/css/admin_csrf_token_key_extend_*');
if (is_array($files)) {
    foreach ((array) $files as $k => $css_file) {

        $fileinfo = pathinfo($css_file);
        $ext = $fileinfo['extension'];

        if ($ext !== 'css') {
            continue;
        }

        $css_file = str_replace(G5_ADMIN_PATH, G5_ADMIN_URL, $css_file);
        add_stylesheet('<link rel="stylesheet" href="' . $css_file . '">', $k);
    }
}

include_once(G5_PATH.'/head.sub.php');

function print_menu1($key, $no = '')
{
    global $menu;

    $str = print_menu2($key, $no);

    return $str;
}

function print_menu2($key, $no = '')
{
    global $menu, $auth_menu, $is_admin, $auth, $g5, $sub_menu, $current_menu;

    $str = "";
    if (!isset($menu[$key]) || !is_array($menu[$key])) {
        return $str;
    }
    $hidden_trade_submenus = array('500200', '500300', '500400');
    $hidden_payment_menu_links = array(
        '/payment.list.php',
        '/payment.membership.list.php'
    );
    for ($i = 1; $i < count($menu[$key]); $i++) {
        if (!isset($menu[$key][$i])) {
            continue;
        }

        // 거래(menu500)에서는 거래현황(500100)만 노출하고 제조사/유통사/기타는 숨김
        if ($key === 'menu500' && in_array((string)$menu[$key][$i][0], $hidden_trade_submenus, true)) {
            continue;
        }

        // 결제 메뉴 개편 중 고객 결제 관리/맴버쉽 결제 히스토리만 URL 기준으로 숨김
        $menu_link = isset($menu[$key][$i][2]) ? (string)$menu[$key][$i][2] : '';
        foreach ($hidden_payment_menu_links as $hidden_link) {
            if ($menu_link !== '' && strpos($menu_link, $hidden_link) !== false) {
                continue 2;
            }
        }

        if ($is_admin != 'super' && (!array_key_exists($menu[$key][$i][0], $auth) || !strstr($auth[$menu[$key][$i][0]], 'r'))) {
            continue;
        }

        $gnb_grp_div = $gnb_grp_style = '';

        if (isset($menu[$key][$i][4])) {
            if (($menu[$key][$i][4] == 1 && $gnb_grp_style == false) || ($menu[$key][$i][4] != 1 && $gnb_grp_style == true)) {
                $gnb_grp_div = 'gnb_grp_div';
            }

            if ($menu[$key][$i][4] == 1) {
                $gnb_grp_style = 'gnb_grp_style';
            }
        }

        $current_class = '';

        $active_menu = isset($current_menu) && $current_menu ? $current_menu : $sub_menu;
        if ($menu[$key][$i][0] == $active_menu) {
            $current_class = ' current-page';
        }

        $str .= '<li data-menu="' . $menu[$key][$i][0] . '" class="'.$current_class.'"><a href="' . $menu[$key][$i][2] . '">' . $menu[$key][$i][1] . '</a></li>';

        $auth_menu[$menu[$key][$i][0]] = $menu[$key][$i][1];
    }


    return $str;
}

$adm_menu_cookie = array(
    'container' => '',
    'gnb'       => '',
    'btn_gnb'   => '',
);

if (!empty($_COOKIE['g5_admin_btn_gnb'])) {
    $adm_menu_cookie['container'] = 'container-small';
    $adm_menu_cookie['gnb'] = 'gnb_small';
    $adm_menu_cookie['btn_gnb'] = 'btn_gnb_open';
}
?>

<script>
    var g5_admin_csrf_token_key = "<?php echo (function_exists('admin_csrf_token_key')) ? admin_csrf_token_key() : ''; ?>";
    var tempX = 0;
    var tempY = 0;

    function imageview(id, w, h) {

        menu(id);

        var el_id = document.getElementById(id);

        //submenu = eval(name+".style");
        submenu = el_id.style;
        submenu.left = tempX - (w + 11);
        submenu.top = tempY - (h / 2);

        selectBoxVisible();

        if (el_id.style.display != 'none')
            selectBoxHidden(id);
    }
</script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <!-- Bootstrap -->
    <link href="<?=G5_ADMIN_URL?>/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="<?=G5_ADMIN_URL?>/vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- iCheck -->
    <link href="<?=G5_ADMIN_URL?>/vendors/iCheck/skins/flat/green.css" rel="stylesheet">
    <!-- bootstrap-progressbar -->
    <link href="<?=G5_ADMIN_URL?>/vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
    <!-- Custom Theme Style -->
    <link href="<?=G5_ADMIN_URL?>/build/css/custom.min.css" rel="stylesheet">
	<!--link href="<?=G5_ADMIN_URL?>/css/style.css" rel="stylesheet"-->
	<link href="<?=G5_ADMIN_URL?>/css/login.css" rel="stylesheet">



    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
              <a href="<?php echo correct_goto_url(G5_ADMIN_URL); ?>" class="site_title"><i class="fa fa-cog" aria-hidden="true"></i> <span><img src="<?php echo G5_ADMIN_URL ?>/img/logo.png" alt="<?php echo get_text($config['cf_title']); ?> 관리자"></span></a>
            </div>

            <div class="clearfix"></div>

            <!-- menu profile quick info -->
            <div class="profile clearfix">
              <div class="profile_pic" >
				<?php echo str_replace(">"," class='img-circle profile_img'>",get_member_profile_img($member['mb_id'])); ?>
              </div>
              <div class="profile_info">
                <span>환영합니다,</span>
                <h2><?=$member['mb_name']?></h2>
              </div>
            </div>
            <!-- /menu profile quick info -->

            <br />

            <!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              <div class="menu_section">
                <h3>메뉴</h3>
                <ul class="nav side-menu">
            <?php
			$idx_v["기본관리"]="fa-home";
			$idx_v["회원"]="fa-edit";
			$idx_v["게시판관리"]="fa-desktop";
			$idx_v["접속통계"]="fa-bar-chart-o";
			$idx_v["쇼핑몰현황/기타"]="fa-table";
			$idx_v["SMS 관리"]="fa-edit";
			$idx_v["기업 DB"]="fa-database";
			$idx_v["맴버쉽관리"]="fa-user-o";
			$idx_v["거래"]="fa-building-o";
			$idx_v["결제"]="fa-credit-card";
			$idx_v["배너관리"]="fa-picture-o";
			$idx_v["공지사항"]="fa-exclamation-circle";
			$idx_v["고객문의"]="fa-commenting-o";
			$idx_v["기업홍보관"]="fa-building-o";
			$idx_v["쿠폰관리"]="fa-tag";
			$idx_v["알림톡 설정"]="fa-commenting-o";
			$idx_v["개발/운영도구"]="fa-wrench";
			//print_r($menu);
			//print_r($amenu);
			//print_r($auth_menu);
            $jj = 1;
            foreach ($amenu as $key => $value) {


                $href1 = $href2 = '';

                if (isset($menu['menu' . $key][0][2]) && $menu['menu' . $key][0][2]) {
                    $href1 = '<a href="' . $menu['menu' . $key][0][2] . '" class="gnb_1da">';
                    $href2 = '</a>';
                } else {
                    continue;
                }

                $current_class = "";
                if (isset($sub_menu) && (substr($sub_menu, 0, 3) == substr($menu['menu' . $key][0][0], 0, 3))) {
                    $current_class = " on";
                }

	                $button_title = $menu['menu' . $key][0][1];
					$button_link = $menu['menu' . $key][0][2];
					$button_one = $menu['menu' . $key][0][8];
					$button_icon = isset($idx_v[$button_title]) ? $idx_v[$button_title] : 'fa-circle-o';
					if($button_one==1) $linkz='href="'.$button_link.'"';
					else $linkz='';
				//$mb_auth=explode("|",$member['mb_auth']);
				//$chk = array_search($menu['menu' . $key][0][0], $mb_auth);

            ?>
	                  <li><a <?=$linkz?>><i class="fa <?=$button_icon?>"></i> <?php echo $button_title; ?> <?if($button_one!=1){?><span class="fa fa-chevron-down"></span><?}?></a>
                    <ul class="nav child_menu">
                      <?php echo print_menu1('menu' . $key, 1); ?>
                    </ul>
                  </li>
	            <?php
	                $jj++;
	            }     //end foreach
	            ?>
                </ul>
              </div>

            </div>
            <!-- /sidebar menu -->

            <!-- /menu footer buttons -->
            <div class="sidebar-footer hidden-small">
              <a data-toggle="tooltip" data-placement="top" title="홈페이지 바로가기" href="/" target="_blank">
				<span class="glyphicon glyphicon-home" aria-hidden="true"></span>
              </a>
              <?php if ((int)$member['mb_level'] === 20) { ?>
              <a data-toggle="tooltip" data-placement="top" title="감사로그" href="<?php echo G5_ADMIN_URL ?>/audit_log_list.php">
                <span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>
              </a>
              <?php } else { ?>
              <a data-toggle="tooltip" data-placement="top">
                &ensp;
              </a>
              <?php } ?>
              <a data-toggle="tooltip" data-placement="top" title="Settings" href="<?php echo G5_ADMIN_URL ?>/admin_form.php?w=u&amp;mb_id=<?php echo $member['mb_id'] ?>">
                <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="Logout" href="<?php echo G5_BBS_URL ?>/logout.php">
                <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
              </a>
            </div>
            <!-- /menu footer buttons -->
          </div>
        </div>

        <!-- top navigation -->
        <div class="top_nav">
          <div class="nav_menu">
              <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
              </div>
              <nav class="nav navbar-nav">
              <ul class=" navbar-right">
                <li class="nav-item dropdown open" style="padding-left: 15px;">
                  <a href="javascript:;" class="user-profile dropdown-toggle" aria-haspopup="true" id="navbarDropdown" data-toggle="dropdown" aria-expanded="false">
                   <?=$member['mb_name']?>
                  </a>
                  <div class="dropdown-menu dropdown-usermenu pull-right" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item"  href="<?php echo G5_ADMIN_URL ?>/admin_form.php?w=u&amp;mb_id=<?php echo $member['mb_id'] ?>"> 관리자정보</a>
                    <a class="dropdown-item"  href="<?php echo G5_BBS_URL ?>/logout.php"><i class="fa fa-sign-out pull-right"></i> 로그아웃</a>
                  </div>
                </li>
              </ul>
            </nav>
          </div>
        </div>
        <!-- /top navigation -->
		<div class="right_col" role="main">
