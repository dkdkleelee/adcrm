<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
  include_once(G5_THEME_MOBILE_PATH . '/head.php');
  return;
}



/////////////////////5분 후 자동 로그아웃 ///////////// 
// if($member['mb_id']) 
// { 
//     $checktime = mktime(date("H"),date("i")-30,date("s"),date("m"),date("d"),date("Y")); // 시간지정 
//     if($_SESSION['ss_login_time'] && ($_SESSION['ss_login_time'] < $checktime)) { 
//         // 페이지를 연 시점이 되어있고, 저장된 시간이 특정시간 이전일때 
//         goto_url($g4['bbs_path']."/logout.php",$urlencode); // 강제 로그아웃 
//     } else { 
//         // 로그인 타임(페이지를 연 시간)이 없거나, 특정시간을 넘기지 않은 경우는 시간재저장 
//         $login_time = mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y")); // 현재시간 저장 
//         set_session("ss_login_time", $login_time); 
//     } 
// } 


// if($_SERVER['SCRIPT_NAME'] != "/index.php") {
//   if($_SERVER['HTTP_REFERER'] == '') exit("<script> history.back();</script>");
// } 


//로그인안되어있을시 login
if (strpos($_SERVER['PHP_SELF'], 'register') !== false) {
  $user_chk = '1';
}
if (strpos($_SERVER['PHP_SELF'], 'register_form') !== false) {
  $user_chk = '1';
}
if (strpos($_SERVER['PHP_SELF'], 'password_lost') !== false) {
  $user_chk = '1';
}
if (!$is_member && !$user_chk) {
  header("Location:" . G5_BBS_URL . "/login");
  exit;
}


include_once(G5_THEME_PATH . '/head.sub.php');
// include_once(G5_LIB_PATH . '/latest.lib.php');
// include_once(G5_LIB_PATH . '/outlogin.lib.php');
// include_once(G5_LIB_PATH . '/poll.lib.php');
// include_once(G5_LIB_PATH . '/visit.lib.php');
// include_once(G5_LIB_PATH . '/connect.lib.php');
// include_once(G5_LIB_PATH . '/popular.lib.php');


// $memo_noread_sql = " select * from {$g5['memo_table']} where me_recv_mb_id = '{$member['mb_id']}' and me_read_datetime = '0000-00-00 00:00:00' ";
// $memo_noread_result = sql_query($memo_noread_sql);


// $notification_board_name = "notice";
// $notification_board = G5_TABLE_PREFIX."write_" . $notification_board_name;
// $notice_count_sql = " SELECT COUNT(*) AS cnt FROM {$notification_board} WHERE wr_id = wr_parent AND wr_reply != 'A' ORDER BY wr_id DESC ";
// $notice_count = sql_fetch($notice_count_sql);
// $total_notice_count = $notice_count['cnt'];


//$notice_sql = " SELECT * FROM {$notification_board} WHERE wr_id = wr_parent AND wr_reply != 'A' ORDER BY wr_id DESC LIMIT 0, 10 ";
// $notice_sql = " SELECT * FROM {$notification_board} WHERE wr_id = wr_parent AND wr_reply != 'A' ORDER BY wr_id DESC ";
// $notice_result = sql_query($notice_sql);

//채팅 건수 확인 쿼리
// $chat_noread_sql = "
// select a.me_id
//      , a.me_recv_mb_id
//      , a.me_send_mb_id
//      , a.me_send_datetime
//      , a.me_read_datetime
//      , case when char_length(a.me_memo) > 10 then concat(substr(a.me_memo, 1, 10), '...')
//             else a.me_memo end as me_memo
//      , a.me_send_id
//      , a.me_type
//      , a.me_send_ip  
//      , b.mb_name 
//      , case
//        when timestampdiff(second, a.me_send_datetime, now()) < 60 then concat(timestampdiff(second, a.me_send_datetime, now()), ' 초 전')
//        when timestampdiff(minute, a.me_send_datetime, now()) < 60 then concat(timestampdiff(minute, a.me_send_datetime, now()), ' 분 전')
//        when timestampdiff(hour, a.me_send_datetime, now()) < 24 then concat(timestampdiff(hour, a.me_send_datetime, now()), ' 시간 전')
//        else concat(timestampdiff(day, a.me_send_datetime, now()), ' 일 전')
//      end as time_diff
//     , (select count(*) from {$g5['memo_table']} where me_recv_mb_id = '{$member['mb_id']}' and me_read_datetime is null and me_type = 'recv') as total_unread
// from {$g5['memo_table']} a
// left join {$g5['member_table']} b on a.me_send_mb_id = b.mb_id 
// where me_recv_mb_id = '{$member['mb_id']}' 
// and me_read_datetime is null 
// and me_type = 'recv' 
// and a.me_send_datetime = (select max(me_send_datetime) 
//                             from {$g5['memo_table']} 
//                            where me_recv_mb_id = '{$member['mb_id']}'
//                              and me_read_datetime is null 
//                              and me_type = 'recv')
// order by me_send_datetime desc
// ";

// $chat_noread_result = sql_query($chat_noread_sql);
// $not_read_cnt = sql_num_rows($chat_noread_result);
// $chat_str = "";



// for ($i = 0; $chat = sql_fetch_array($chat_noread_result); $i++) {

//   if($i >= 3) {
//     $chat_str .= "<a href='#' class='dropdown-item dropdown-footer'>See All Messages</a>";
//     break;
//   }
//   if($i == 0) {
//     $chat_str .= "<div class='dropdown-menu dropdown-menu-lg dropdown-menu-right'>";
//   }

//   $sender_img = get_member_profile_image2($chat['me_send_mb_id'], '50', '50', 'User Image', '', 'img-size-50 ml-3 mr-3 img-circle');

  
//   $url = G5_BBS_URL."/memo_form?me_recv_mb_id=".$chat['me_send_mb_id'];
//   $chat_str .= "
//   <a href='{$url}' class='dropdown-item' onclick='win_memo(this.href); return false;'></a>
//     <div class='media'>
//         {$sender_img}
//         <div class='media-body'>
//         <h3 class='dropdown-item-title'>
//             {$chat['mb_name']}
//         </h3>
//         <p class='text-sm'>{$chat['me_memo']}</p>
//         <p class='text-sm text-muted'><i class='far fa-clock mr-1'></i> {$chat['time_diff']}</p>
//         </div>
//     </div>
//   </a>
//   <div class='dropdown-divider'></div>
//   ";
// }

// $chat_str .= "</div>";
?>




<div class="wrapper">

  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="<?php echo G5_THEME_URL; ?>/dist/img/withUs1.png" alt="gonPlanLogo" height="60" width="60">
  </div>

  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <!-- 
      <li class="nav-item d-none d-sm-inline-block">
        <a href="index3.html" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li> 
      -->
      
      <!-- loop 최근 메뉴 3개 까지 보여줌 4개부터는 쿠키 삭제 처리 TODO -->
    </ul>

    <ul class="navbar-nav ml-auto">
      <!--
      검색
      <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li>
      -->


    <!-- 
      채팅
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-comments"></i>
          <span id="no_read_chat" class="badge badge-danger navbar-badge"><?php echo $not_read_cnt; ?></span>
        </a>

        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <a href="#" class="dropdown-item">
            <div class="media">
              <img src="<?php echo G5_THEME_URL; ?>/dist/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Brad Diesel
                </h3>
                <p class="text-sm">Call me whenever you can...</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <div class="media">
              <img src="<?php echo G5_THEME_URL; ?>/dist/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  John Pierce
                </h3>
                <p class="text-sm">I got your message bro</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <div class="media">
              <img src="<?php echo G5_THEME_URL; ?>/dist/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Nora Silvester
                </h3>
                <p class="text-sm">The subject goes here</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
        </div> 
      </li>
      -->


      <!--
      공지사항
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge">15</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header">15 Notifications</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-envelope mr-2"></i> 4 new messages
            <span class="float-right text-muted text-sm">3 mins</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> 8 friend requests
            <span class="float-right text-muted text-sm">12 hours</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-file mr-2"></i> 3 new reports
            <span class="float-right text-muted text-sm">2 days</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
      </li> 
    -->
      
    <!-- <button type="button" class="btn btn-default" data-toggle="tooltip" data-placement="bottom" title="tooltip">Tooltip on right1</button> -->
      <?php if ($is_admin && $member['mb_level'] == 10) {  ?>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo correct_goto_url(G5_ADMIN_URL); ?>" role="button" data-toggle="tooltip" title="관리자">
            <i class="fas fa-wrench"></i>
          </a>
        </li>
      <?php }  ?>
      

      <li class="nav-item">
        <a class="nav-link" href="<?php echo G5_BBS_URL ?>/logout.php" role="button" data-toggle="tooltip" title="로그아웃">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button" data-toggle="tooltip" title="전체화면">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <!-- <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button" data-toggle="tooltip" title="설정">
          <i class="fas fa-th-large"></i>
        </a>
      </li> -->
      
    </ul>
  </nav>


  <?php include_once(G5_THEME_PATH . "/aside.php"); ?>



  <!-- 콘텐츠 시작 { -->
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <?php if (!defined("_INDEX_")) { ?>
      <!-- Content Header (Page header) -->
      <!-- 
      <section class="content-header">
        <h1>
          <?php echo get_head_title($g5['title']); ?>
          <small>&nbsp;</small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="<?php echo G5_URL; ?>"><i class="fa fa-dashboard"></i> Home</a></li>
          <?php
          if ($bo_table) { ?>
            <li class="active"><a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $bo_table; ?><?php echo $qstr; ?>"><?php echo get_head_title($g5['title']); ?></a></li>
          <?php } else { ?>
            <li class="active"><a href="#"><?php echo get_head_title($g5['title']); ?></a></li>
          <?php } ?>
        </ol>
      </section> 
      -->
    <?php } ?>