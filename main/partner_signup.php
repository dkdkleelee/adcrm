<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

$mb_id      = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
$ptn_id     = isset($_POST['ptn_id']) ? trim($_POST['ptn_id']) : '';
$ptn_pw     = isset($_POST['ptn_pw']) ? trim($_POST['ptn_pw']) : '';
$ptn_pw_re  = isset($_POST['ptn_pw_re']) ? trim($_POST['ptn_pw_re']) : '';
$encode_pw  = get_encrypt_string($ptn_pw);
$ptn_nm     = isset($_POST['ptn_nm']) ? trim($_POST['ptn_nm']) : '';
$ptn_reprnm = isset($_POST['ptn_reprnm']) ? trim($_POST['ptn_reprnm']) : '';
$ptn_phone  = isset($_POST['ptn_phone']) ? trim($_POST['ptn_phone']) : '';
$ptn_tel    = isset($_POST['ptn_tel']) ? trim($_POST['ptn_tel']) : '';
$ptn_email  = isset($_POST['ptn_email']) ? trim($_POST['ptn_email']) : '';
$ptn_email  = get_email_address($ptn_email);
$ptn_nick   = isset($_POST['ptn_nm']) ? trim($_POST['ptn_nm']) : '';

if($ptn_pw != $ptn_pw_re)
    alert('비밀번호가 일치하지 않습니다.');
    
if($w == "") {

    $sql = "
    insert IGNORE into {$g5['crm_signup']} (
          ptn_id
        , ptn_pw
        , ptn_nm
        , ptn_reprnm
        , ptn_phone
        , ptn_tel
        , ptn_email
        , ptn_nick
        , sign_status
        , sign_ip
        , insert_date
    ) VALUES (
        '{$ptn_id}'
        ,'{$encode_pw}'
        ,'{$ptn_nm}'
        ,'{$ptn_reprnm}'
        ,'{$ptn_phone}'
        ,'{$ptn_tel}'
        ,'{$ptn_email}'
        ,'{$ptn_nick}'
        ,'N'
        ,'{$_SERVER['REMOTE_ADDR']}'
        , now()
    )";
    $ins_result = sql_query($sql);
    sql_result_alert($ins_result, '가입완료\n관리자승인후\n사용가능합니다.' , G5_BBS_URL . "/login.php");

} else {

    //비밀번호수정
    if($ptn_pw != "") {
        //$sql_password = "mb_password = '".get_encrypt_string($ptn_pw)."' ";
        $sql = "update {$g5['member_table']} set
         mb_password = '".get_encrypt_string($ptn_pw)."'
        ,mb_name = '{$ptn_reprnm}'
        ,mb_hp = '{$ptn_phone}'
        ,mb_email = '{$ptn_email}'
        where mb_id = '{$mb_id}' ;
        ";
        $upd_mem = sql_query($sql);

        $sql1 = "update {$g5['crm_partner']} set
         ptn_tel = '{$ptn_tel}'
        ,ptn_email = '{$ptn_email}'
        ,ptn_reprnm = '{$ptn_reprnm}'
        where ptn_nm = '$ptn_nm' ;
        ";
        $result1 = sql_query($sql1);

    } else {
        $sql = "update {$g5['member_table']} set
         mb_name = '{$ptn_reprnm}'
        ,mb_hp = '{$ptn_phone}'
        ,mb_email = '{$ptn_email}'
        where mb_id = '$mb_id' ;
        ";
        $upd_mem = sql_query($sql);

        $sql1 = "update {$g5['crm_partner']} set
         ptn_tel = '{$ptn_tel}'
        ,ptn_email = '{$ptn_email}'
        ,ptn_reprnm = '{$ptn_reprnm}'
        where ptn_nm = '$ptn_nm' ;
        ";
        $result1 = sql_query($sql1);
    }
        
    //파트너 수정
    // $upd_sql = "
    // update {$g5['crm_partner']} set
    //       ptn_nm        = '{$ptn_nm}'
    //     , ptn_phone     = '{$ptn_phone}'
    //     , ptn_reprnm    = '{$ptn_reprnm}'
    //     , ptn_email     = '{$ptn_email}'
    //     , update_date   = now()
    //     , update_user   = '{$member['mb_id']}'
    // where mb_id = '{$mb_id}'
    // ";
    // $upd_ptn = sql_query($upd_sql);

    // 회원 프로필 이미지
    $msg = "";
    $image_regex = "/(\.(gif|jpe?g|png))$/i";
    $mb_icon_img = get_mb_icon_name($mb_id).'.gif';

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

    goto_url(G5_URL);
}  

    