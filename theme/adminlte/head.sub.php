<?php
// 이 파일은 새로운 파일 생성시 반드시 포함되어야 함
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가



if (!isset($g5['title'])) {
    $g5['title'] = $config['cf_title'];
    $g5_head_title = $g5['title'];
}
else {
    $g5_head_title = $g5['title']; // 상태바에 표시될 제목
    $g5_head_title .= " | ".$config['cf_title'];
}

// 현재 접속자
// 게시판 제목에 ' 포함되면 오류 발생
$g5['lo_location'] = addslashes($g5['title']);
if (!$g5['lo_location'])
    $g5['lo_location'] = addslashes(clean_xss_tags($_SERVER['REQUEST_URI']));
$g5['lo_url'] = addslashes(clean_xss_tags($_SERVER['REQUEST_URI']));
if (strstr($g5['lo_url'], '/'.G5_ADMIN_DIR.'/') || $is_admin == 'super') $g5['lo_url'] = '';


?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<?php
if (G5_IS_MOBILE) {
    echo '<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">'.PHP_EOL;
    echo '<meta name="HandheldFriendly" content="true">'.PHP_EOL;
    echo '<meta name="format-detection" content="telephone=no">'.PHP_EOL;
} else {

    echo '<meta http-equiv="Expires" content="-1">'.PHP_EOL;
    echo '<meta http-equiv="Pragma" content="no-cache"">'.PHP_EOL;
    echo '<meta http-equiv="Cache-Control" content="no-cache"">'.PHP_EOL;

	echo '<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">'.PHP_EOL;
    echo '<meta http-equiv="imagetoolbar" content="no">'.PHP_EOL;
    echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">'.PHP_EOL;
}

if($config['cf_add_meta'])
    echo $config['cf_add_meta'].PHP_EOL;
?>
<title><?php echo $g5_head_title; ?></title>
<!-- <link rel="stylesheet" href="<?php echo G5_THEME_CSS_URL; ?>/<?php echo G5_IS_MOBILE ? 'mobile' : 'default'; ?>.css"> -->
<link rel="shortcut icon" type="image/x-icon" href="<?php echo G5_THEME_URL; ?>/dist/img/withUs1.png">


<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/plugins/fontawesome-free/css/all.min.css">

<!-- <link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css"> -->
<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/plugins/bootstrap-select/css/bootstrap-select.css">

<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/dist/css/AdminLTE.min.css">

<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/css/custom.css">

<script src="<?php echo G5_THEME_URL; ?>/plugins/jquery/jquery.min.js"></script>
<script src="<?php echo G5_THEME_URL; ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>


<!-- <script src="http://dmaps.daum.net/map_js_init/postcode.v2.js"></script> -->

<script>
// 자바스크립트에서 사용하는 전역변수 선언
var g5_url       = "<?php echo G5_URL; ?>";
var g5_bbs_url   = "<?php echo G5_BBS_URL; ?>";
var g5_is_member = "<?php echo isset($is_member)?$is_member:''; ?>";
var g5_is_admin  = "<?php echo isset($is_admin)?$is_admin:''; ?>";
var g5_is_mobile = "<?php echo G5_IS_MOBILE; ?>";
var g5_bo_table  = "<?php echo isset($bo_table)?$bo_table:''; ?>";
var g5_sca       = "<?php echo isset($sca)?$sca:''; ?>";
var g5_editor    = "<?php echo ($config['cf_editor'] && $board['bo_use_dhtml_editor'])?$config['cf_editor']:''; ?>";
var g5_cookie_domain = "<?php echo G5_COOKIE_DOMAIN; ?>";
</script>

<!-- 
<script src="<?php echo G5_JS_URL ?>/jquery-1.8.3.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery.menu.js?ver=<?php echo G5_JS_VER; ?>"></script>
<script src="<?php echo G5_JS_URL; ?>/common.js"></script>
<script src="<?php echo G5_JS_URL; ?>/wrest.js"></script>
<script src="<?php echo G5_JS_URL; ?>/placeholders.min.js"></script>
 -->

 <script src="<?php echo G5_JS_URL; ?>/common.js"></script>


<?php
if(G5_IS_MOBILE) {
    echo '<script src="'.G5_JS_URL.'/modernizr.custom.70111.js"></script>'.PHP_EOL; // overflow scroll 감지
}
if(!defined('G5_IS_ADMIN'))
    echo $config['cf_add_script'];
?>
</head>
<?php
//explode($_SERVER['SCRIPT_NAME']);
if (basename($_SERVER['SCRIPT_NAME']) == "login.php" || basename($_SERVER['SCRIPT_NAME']) == "password_lost.php") { ?>
<body<?php echo isset($g5['body_script']) ? $g5['body_script'] : ''; ?> class="hold-transition login-page">
<?php } else if (basename($_SERVER['SCRIPT_NAME']) == "register.php" || basename($_SERVER['SCRIPT_NAME']) == "register_form.php") { ?>
<body<?php echo isset($g5['body_script']) ? $g5['body_script'] : ''; ?> class="register-page">
<?php } else if (basename($_SERVER['SCRIPT_NAME']) == "member_confirm.php") { ?>
<body<?php echo isset($g5['body_script']) ? $g5['body_script'] : ''; ?> class="hold-transition lockscreen">
<?php } else { ?>
<body<?php echo isset($g5['body_script']) ? $g5['body_script'] : ''; ?> class="hold-transition skin-blue sidebar-mini">
<?php } ?>

<?php
if ($is_member) { // 회원이라면 로그인 중이라는 메세지를 출력해준다.
    $sr_admin_msg = '';
    if ($is_admin == 'super') $sr_admin_msg = "최고관리자 ";
    else if ($is_admin == 'group') $sr_admin_msg = "그룹관리자 ";
    else if ($is_admin == 'board') $sr_admin_msg = "게시판관리자 ";

    //echo '<div id="hd_login_msg">'.$sr_admin_msg.get_text($member['mb_nick']).'님 로그인 중 ';
    //echo '<a href="'.G5_BBS_URL.'/logout.php">로그아웃</a></div>';
}
?>