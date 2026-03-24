<?php
require_once '../../common.php';


include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');
include_once(G5_LIB_PATH.'/register.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');
include_once(G5_LIB_PATH.'/thumbnail.lib.php');



if (!($w == '' || $w == 'u')) {
  alert('w 값이 제대로 넘어오지 않았습니다.');
}

if($w == 'u')
    $mb_id = isset($_SESSION['ss_mb_id']) ? trim($_SESSION['ss_mb_id']) : '';
else if($w == '')
    $mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
else
    alert('잘못된 접근입니다', G5_URL);

if(!$mb_id)
    alert('회원아이디 값이 없습니다. 올바른 방법으로 이용해 주십시오.');

$mb_id = isset($_SESSION['ss_mb_id']) ? trim($_SESSION['ss_mb_id']) : '';
$mb_password    = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : '';
$mb_name        = isset($_POST['mb_name']) ? trim($_POST['mb_name']) : '';
$mb_nick        = isset($_POST['mb_nick']) ? trim($_POST['mb_nick']) : '';
$mb_hp          = isset($_POST['mb_hp'])   ? trim($_POST['mb_hp'])          : "";
$mb_email       = isset($_POST['mb_email']) ? trim($_POST['mb_email']) : '';
$mb_birth       = isset($_POST['mb_birth']) ? trim($_POST['mb_birth'])       : "";
$mb_deptno      = $_POST['mb_deptno']!="" ? trim($_POST['mb_deptno'])      : 'NULL';

run_event('register_form_update_valid', $w, $mb_id, $mb_nick, $mb_email);
run_event('register_form_update_before', $mb_id, $w);

if ($w == '') {
    $sql = " insert into {$g5['member_table']}
                set mb_id = '{$mb_id}',
                     mb_password = '".get_encrypt_string($mb_password)."',
                     mb_name = '{$mb_name}',
                     mb_nick = '{$mb_nick}',
                     mb_nick_date = '".G5_TIME_YMD."',
                     mb_email = '{$mb_email}',
                     mb_homepage = '{$mb_homepage}',
                     mb_tel = '{$mb_tel}',
                     mb_hp = '{$mb_hp}',
                     mb_birth = '{$mb_birth}',
                     mb_zip1 = '{$mb_zip1}',
                     mb_zip2 = '{$mb_zip2}',
                     mb_addr1 = '{$mb_addr1}',
                     mb_addr2 = '{$mb_addr2}',
                     mb_addr3 = '{$mb_addr3}',
                     mb_addr_jibeon = '{$mb_addr_jibeon}',
                     mb_signature = '{$mb_signature}',
                     mb_profile = '{$mb_profile}',
                     mb_today_login = '".G5_TIME_YMDHIS."',
                     mb_datetime = '".G5_TIME_YMDHIS."',
                     mb_ip = '".getRealClientIp()."',
                     mb_level = '4', 
                     mb_recommend = '{$mb_recommend}',
                     mb_login_ip = '".getRealClientIp()."',
                     mb_mailling = '{$mb_mailling}',
                     mb_sms = '{$mb_sms}',
                     mb_open = '{$mb_open}',
                     mb_open_date = '".G5_TIME_YMD."',
                     mb_1 = '{$mb_1}',
                     mb_2 = '{$mb_2}',
                     mb_3 = '{$mb_3}',
                     mb_4 = '{$mb_4}',
                     mb_5 = '{$mb_5}',
                     mb_6 = '{$mb_6}',
                     mb_7 = '{$mb_7}',
                     mb_8 = '{$mb_8}',
                     mb_9 = '{$mb_9}',
                     mb_10 = '{$mb_10}',
                     mb_gubun = 'E',
                     is_login = '{$is_login}',
                     mb_deptno = {$mb_deptno}
                     ";
    isSqlError(sql_query($sql), $sql);
    
}
else {

    $sql_password = "";
    if ($mb_password)
        $sql_password = " , mb_password = '".get_encrypt_string($mb_password)."' ";

    $sql = " update {$g5['member_table']}
    set mb_nick = '{$mb_nick}',
        mb_name = '{$mb_name}',
        mb_email = '{$mb_email}',
        mb_hp = '{$mb_hp}',
        mb_zip1 = '{$mb_zip1}',
        mb_zip2 = '{$mb_zip2}',
        mb_addr1 = '{$mb_addr1}',
        mb_addr2 = '{$mb_addr2}',
        mb_addr3 = '{$mb_addr3}',
        mb_addr_jibeon = '{$mb_addr_jibeon}',
        mb_birth = '{$mb_birth}',
        mb_deptno = '{$mb_deptno}'
    where mb_id = '$mb_id' ";
    isSqlError(sql_query($sql), $sql);

}


// 회원 아이콘
$mb_dir = G5_DATA_PATH.'/member/'.substr($mb_id,0,2);

// 아이콘 삭제
if (isset($_POST['del_mb_icon'])) {
    @unlink($mb_dir.'/'.get_mb_icon_name($mb_id).'.gif');
}

$msg = "";

// 아이콘 업로드
$mb_icon = '';
$image_regex = "/(\.(gif|jpe?g|png))$/i";
$mb_icon_img = get_mb_icon_name($mb_id).'.gif';

if (isset($_FILES['mb_icon']) && is_uploaded_file($_FILES['mb_icon']['tmp_name'])) {
    if (preg_match($image_regex, $_FILES['mb_icon']['name'])) {
        // 아이콘 용량이 설정값보다 이하만 업로드 가능
        if ($_FILES['mb_icon']['size'] <= $config['cf_member_icon_size']) {
            @mkdir($mb_dir, G5_DIR_PERMISSION);
            @chmod($mb_dir, G5_DIR_PERMISSION);
            $dest_path = $mb_dir.'/'.$mb_icon_img;
            move_uploaded_file($_FILES['mb_icon']['tmp_name'], $dest_path);
            chmod($dest_path, G5_FILE_PERMISSION);
            if (file_exists($dest_path)) {
                //=================================================================\
                // 090714
                // gif 파일에 악성코드를 심어 업로드 하는 경우를 방지
                // 에러메세지는 출력하지 않는다.
                //-----------------------------------------------------------------
                $size = @getimagesize($dest_path);
                if (!($size[2] === 1 || $size[2] === 2 || $size[2] === 3)) { // jpg, gif, png 파일이 아니면 올라간 이미지를 삭제한다.
                    @unlink($dest_path);
                } else if ($size[0] > $config['cf_member_icon_width'] || $size[1] > $config['cf_member_icon_height']) {
                    $thumb = null;
                    if($size[2] === 2 || $size[2] === 3) {
                        //jpg 또는 png 파일 적용
                        $thumb = thumbnail($mb_icon_img, $mb_dir, $mb_dir, $config['cf_member_icon_width'], $config['cf_member_icon_height'], true, true);
                        if($thumb) {
                            @unlink($dest_path);
                            rename($mb_dir.'/'.$thumb, $dest_path);
                        }
                    }
                    if( !$thumb ){
                        // 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
                        @unlink($dest_path);
                    }
                }
                //=================================================================\
            }
        } else {
            $msg .= '회원아이콘을 '.number_format($config['cf_member_icon_size']).'바이트 이하로 업로드 해주십시오.';
        }

    } else {
        $msg .= $_FILES['mb_icon']['name'].'은(는) 이미지 파일이 아닙니다.';
    }
}

// 회원 프로필 이미지
if( $config['cf_member_img_size'] && $config['cf_member_img_width'] && $config['cf_member_img_height'] ){
    $mb_tmp_dir = G5_DATA_PATH.'/member_image/';
    $mb_dir = $mb_tmp_dir.substr($mb_id,0,2);
    if( !is_dir($mb_tmp_dir) ){
        @mkdir($mb_tmp_dir, G5_DIR_PERMISSION);
        @chmod($mb_tmp_dir, G5_DIR_PERMISSION);
    }

    // 아이콘 삭제
    if (isset($_POST['del_mb_img'])) {
        @unlink($mb_dir.'/'.$mb_icon_img);
    }

    // 회원 프로필 이미지 업로드
    $mb_img = '';
    if (isset($_FILES['mb_img']) && is_uploaded_file($_FILES['mb_img']['tmp_name'])) {

        $msg = $msg ? $msg."\\r\\n" : '';

        if (preg_match($image_regex, $_FILES['mb_img']['name'])) {
            // 아이콘 용량이 설정값보다 이하만 업로드 가능
            if ($_FILES['mb_img']['size'] <= $config['cf_member_img_size']) {
                @mkdir($mb_dir, G5_DIR_PERMISSION);
                @chmod($mb_dir, G5_DIR_PERMISSION);
                $dest_path = $mb_dir.'/'.$mb_icon_img;
                move_uploaded_file($_FILES['mb_img']['tmp_name'], $dest_path);
                chmod($dest_path, G5_FILE_PERMISSION);
                if (file_exists($dest_path)) {
                    $size = @getimagesize($dest_path);
                    if (!($size[2] === 1 || $size[2] === 2 || $size[2] === 3)) { // gif jpg png 파일이 아니면 올라간 이미지를 삭제한다.
                        @unlink($dest_path);
                    } else if ($size[0] > $config['cf_member_img_width'] || $size[1] > $config['cf_member_img_height']) {
                        $thumb = null;
                        if($size[2] === 2 || $size[2] === 3) {
                            //jpg 또는 png 파일 적용
                            $thumb = thumbnail($mb_icon_img, $mb_dir, $mb_dir, $config['cf_member_img_width'], $config['cf_member_img_height'], true, true);
                            if($thumb) {
                                @unlink($dest_path);
                                rename($mb_dir.'/'.$thumb, $dest_path);
                            }
                        }
                        if( !$thumb ){
                            // 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
                            @unlink($dest_path);
                        }
                    }
                    //=================================================================\
                }
            } else {
                $msg .= '회원이미지을 '.number_format($config['cf_member_img_size']).'바이트 이하로 업로드 해주십시오.';
            }

        } else {
            $msg .= $_FILES['mb_img']['name'].'은(는) gif/jpg 파일이 아닙니다.';
        }
    }
}



// $mb_password    = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : '';
// $mb_password_re = isset($_POST['mb_password_re']) ? trim($_POST['mb_password_re']) : '';
// $mb_name        = isset($_POST['mb_name']) ? trim($_POST['mb_name']) : '';
// $mb_nick        = isset($_POST['mb_nick']) ? trim($_POST['mb_nick']) : '';
// $mb_email       = isset($_POST['mb_email']) ? trim($_POST['mb_email']) : '';
// $mb_sex         = isset($_POST['mb_sex'])           ? trim($_POST['mb_sex'])         : "";
// $mb_birth       = isset($_POST['mb_birth'])         ? trim($_POST['mb_birth'])       : "";
// $mb_homepage    = isset($_POST['mb_homepage'])      ? trim($_POST['mb_homepage'])    : "";
// $mb_tel         = isset($_POST['mb_tel'])           ? trim($_POST['mb_tel'])         : "";
// $mb_hp          = isset($_POST['mb_hp'])            ? trim($_POST['mb_hp'])          : "";
// $mb_zip1        = isset($_POST['mb_zip'])           ? substr(trim($_POST['mb_zip']), 0, 3) : "";
// $mb_zip2        = isset($_POST['mb_zip'])           ? substr(trim($_POST['mb_zip']), 3)    : "";
// $mb_addr1       = isset($_POST['mb_addr1'])         ? trim($_POST['mb_addr1'])       : "";
// $mb_addr2       = isset($_POST['mb_addr2'])         ? trim($_POST['mb_addr2'])       : "";
// $mb_addr3       = isset($_POST['mb_addr3'])         ? trim($_POST['mb_addr3'])       : "";
// $mb_addr_jibeon = isset($_POST['mb_addr_jibeon'])   ? trim($_POST['mb_addr_jibeon']) : "";
// $mb_signature   = isset($_POST['mb_signature'])     ? trim($_POST['mb_signature'])   : "";
// $mb_profile     = isset($_POST['mb_profile'])       ? trim($_POST['mb_profile'])     : "";
// $mb_recommend   = isset($_POST['mb_recommend'])     ? trim($_POST['mb_recommend'])   : "";
// $mb_mailling    = isset($_POST['mb_mailling'])      ? trim($_POST['mb_mailling'])    : "";
// $mb_sms         = isset($_POST['mb_sms'])           ? trim($_POST['mb_sms'])         : "";
// $mb_open        = isset($_POST['mb_open'])          ? trim($_POST['mb_open'])        : "0";
// $mb_1           = isset($_POST['mb_1'])             ? trim($_POST['mb_1'])           : "";
// $mb_2           = isset($_POST['mb_2'])             ? trim($_POST['mb_2'])           : "";
// $mb_3           = isset($_POST['mb_3'])             ? trim($_POST['mb_3'])           : "";
// $mb_4           = isset($_POST['mb_4'])             ? trim($_POST['mb_4'])           : "";
// $mb_5           = isset($_POST['mb_5'])             ? trim($_POST['mb_5'])           : "";
// $mb_6           = isset($_POST['mb_6'])             ? trim($_POST['mb_6'])           : "";
// $mb_7           = isset($_POST['mb_7'])             ? trim($_POST['mb_7'])           : "";
// $mb_8           = isset($_POST['mb_8'])             ? trim($_POST['mb_8'])           : "";
// $mb_9           = isset($_POST['mb_9'])             ? trim($_POST['mb_9'])           : "";
// $mb_10          = isset($_POST['mb_10'])            ? trim($_POST['mb_10'])          : "";

// $mb_deptno      = $_POST['mb_deptno']!=""           ? trim($_POST['mb_deptno'])      : 'NULL';

// $mb_name        = clean_xss_tags($mb_name);
// $mb_email       = get_email_address($mb_email);
// $mb_homepage    = clean_xss_tags($mb_homepage);
// $mb_tel         = clean_xss_tags($mb_tel);
// $mb_zip1        = preg_replace('/[^0-9]/', '', $mb_zip1);
// $mb_zip2        = preg_replace('/[^0-9]/', '', $mb_zip2);
// $mb_addr1       = clean_xss_tags($mb_addr1);
// $mb_addr2       = clean_xss_tags($mb_addr2);
// $mb_addr3       = clean_xss_tags($mb_addr3);
// $mb_addr_jibeon = preg_match("/^(N|R)$/", $mb_addr_jibeon) ? $mb_addr_jibeon : '';