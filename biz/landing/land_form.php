<?php

require_once '../../common.php';

include_once(G5_BIZ_PATH . '/common/access_control.php');

//코드리스트

if($member['mb_level'] <= 6) {
    $add_cond = "and pg_deptno = {$member['mb_deptno']}";
}
$code_sql = "
select a.page_idx 
      ,a.pg_domain 
      ,a.pg_uri
      ,b.ptn_nm 
      ,ifnull (f_get_mb_name(a.pg_mb_emp), a.pg_mb_emp) as mb_emp_name
  from {$g5['crm_page']} a
  left join {$g5['crm_partner']} b on a.pg_ptn_idx = b.ptn_idx
 where a.use_yn = 'Y'
   and b.use_yn = 'Y'
 $add_cond
order by page_idx desc
";
$code_list = sql_query($code_sql);


if ($w == '') {
    $g5['title'] = "DB 등록";
    $title = "DB 등록";

    $code = sql_fetch_array($code_list);
    //$first_ptn_emp = 'mb_deptno = '.$ptn_emp['ptn_idx'];
    $initPgUri = $code['pg_uri'];
    sql_data_seek($code_list, 0);

} else if($w == 'u') {
    $g5['title'] = "DB 수정";
    $title = "DB 수정";

    $resultOneSql = "
    select a.land_idx
          , a.land_pg_idx
          , a.land_ptn_idx
          , a.land_deptno
          , a.land_empno
          , a.land_used_data
          , a.name
          , convert(aes_decrypt(unhex(a.tel), 'withus_secret_key') using utf8) as tel
          , a.hp
          , a.tel1
          , a.tel2
          , a.tel3
          , a.option1
          , a.option2
          , a.option3
          , a.option4
          , a.option5
          , a.option6
          , a.option7
          , a.option8
          , a.option9
          , a.db_status
          , a.land_memo
          , a.submit_pos
          , a.inflow_path
          , a.inflow_env
          , a.utm_source
          , a.utm_medium
          , a.user_agent
          , a.user_agent2
          , a.api_send_yn
          , a.sms_send_yn
          , a.use_yn
          , a.insert_date
          , a.insert_date2
          , a.update_date
          , a.insert_user
          , a.update_user
          , a.update_log
          , a.client_ip
          , a.ip
          , a.city
          , a.region
          , a.country
          , a.loc
          , a.org
          , a.postal
          , a.timezone
          ,b.page_idx 
          ,b.pg_uri 
          ,b.pg_deptno 
          ,d.deptnm 
          ,b.pg_mb_emp 
          -- ,NULLIF (b.pg_mb_emp , f_get_mb_name(b.pg_mb_emp)) as mb_emp_name
          , f_get_mb_name(b.pg_mb_emp) as mb_emp_name
          ,b.pg_ptn_idx 
          ,f.ptn_nm 
          ,b.pg_mb_ptn 
          -- ,NULLIF (b.pg_mb_ptn , f_get_mb_name(b.pg_mb_ptn)) as mb_ptn_name
          , f_get_mb_name(b.pg_mb_ptn) as mb_ptn_name
          ,c.design_name 
      from {$g5['crm_landing']} a
      left join {$g5['crm_page']}     b on a.land_pg_idx = b.page_idx
      left join {$g5['crm_design']}   c on b.pg_des_idx  = c.design_idx 
      left join {$g5['crm_depart']}   d on b.pg_deptno   = d.deptno 
      left join {$g5['member_table']} e on b.pg_mb_emp   = e.mb_no 
      left join {$g5['crm_partner']}  f on b.pg_ptn_idx  = f.ptn_idx 
     where 1=1
     and a.land_idx = {$land_idx}
    ";
    $resultOne = sql_fetch($resultOneSql);
    
    $initPgUri = $resultOne['pg_uri'];
    $pg_deptno = $resultOne['pg_deptno'];

}


$sql = "SELECT * FROM gnp_crm_db_file WHERE db_land_idx = {$land_idx}";
$result = sql_query($sql);
$file_list = array();
while ($row = sql_fetch_array($result)) {
    $file_list[] = $row;
}


$g5['title'] = $title;
include_once(G5_PATH . '/head.php');

?>


<section class="content">
    <div class="container-fluid">
        <form name="landForm" id="landForm" action="./land_form_update" method="post" onsubmit="return validateForm()" enctype="multipart/form-data">

            <div class="card card-danger">

                <div class="card-header">
                    <h3 class="card-title">고객정보</h3>

                    <div class="text-right">
                        <?php echo isSaveBtn($w, $resultOne['pg_deptno'], $resultOne['pg_mb_emp'], $member, 'btn_small', 'btn btn-primary btn-xs') ?>
                        <button type="button" class="btn btn-default btn-xs" id="btn_list" onclick="location.href='<?php echo G5_BIZ_URL; ?>/landing/land_list?<?php echo $qstr;?>'">목록</button>
                    </div>

                </div>

                <div class="card-body">
                    <input type="hidden" name="w" value="<?php echo $w ?>">
                    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
                    <input type="hidden" name="stx" value="<?php echo $stx ?>">
                    <input type="hidden" name="sst" value="<?php echo $sst ?>">
                    <input type="hidden" name="sod" value="<?php echo $sod ?>">
                    <input type="hidden" name="page" value="<?php echo $page ?>">
                    <input type="hidden" name="token" value="">
                    <input type="hidden" name="land_idx" value="<?php echo $resultOne['land_idx'] ?>">
                    <input type="hidden" name="land_pg_idx" value="<?php echo $resultOne['land_pg_idx'] ?>">
                    <input type="hidden" name="land_ptn_idx" value="<?php echo $resultOne['land_ptn_idx'] ?>">

                    <input type="hidden" name="advanced_ptn_idx" value="<?php echo $advanced_ptn_idx ?>">
                    <input type="hidden" name="advanced_pg_uri" value="<?php echo $advanced_pg_uri ?>">
                    <input type="hidden" name="advanced_from" value="<?php echo $advanced_from ?>">
                    <input type="hidden" name="advanced_to" value="<?php echo $advanced_to ?>">
                    <input type="hidden" name="advanced_db_status" value="<?php echo $advanced_db_status ?>">

                    <input type="hidden" name="pg_api_yn" value="">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>이름</label>
                                <input type="text" id="name" name="name" class="form-control border-info" value="<?php echo $resultOne['name'] ?>" placeholder="이름">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>연락처*</code></label>
                                <input type="text" id="tel" name="tel" class="form-control border-info" value="<?php echo $resultOne['tel'] ?>" placeholder="연락처" oninput="telHyphen(this);" minlength="13" maxlength="13" pattern=".{13,13}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>옵션1</label>
                                <input type="text" id="option1" name="option1" class="form-control" value="<?php echo $resultOne['option1'] ?>" placeholder="옵션1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>옵션2</label>
                                <input type="text" id="option2" name="option2" class="form-control" value="<?php echo $resultOne['option2'] ?>" placeholder="옵션2">
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>옵션3</label>
                                <input type="text" id="option3" name="option3" class="form-control" value="<?php echo $resultOne['option3'] ?>" placeholder="옵션3">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>옵션4</label>
                                <input type="text" id="option4" name="option4" class="form-control" value="<?php echo $resultOne['option4'] ?>" placeholder="옵션4">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>옵션5</label>
                                <input type="text" id="option5" name="option5" class="form-control" value="<?php echo $resultOne['option5'] ?>" placeholder="옵션5">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>옵션6</label>
                                <input type="text" id="option6" name="option6" class="form-control" value="<?php echo $resultOne['option6'] ?>" placeholder="옵션6">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>옵션7</label>
                                <input type="text" id="option7" name="option7" class="form-control" value="<?php echo $resultOne['option7'] ?>" placeholder="옵션7">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>옵션8</label>
                                <input type="text" id="option8" name="option8" class="form-control" value="<?php echo $resultOne['option8'] ?>" placeholder="옵션8">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>옵션9</label>
                                <input type="text" id="option9" name="option9" class="form-control" value="<?php echo $resultOne['option9'] ?>" placeholder="옵션9">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>아이피</label>
                                <input type="text" id="client_ip" name="client_ip" class="form-control" value="<?php echo $resultOne['client_ip'] ?>" placeholder="아이피">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>메모</label>
                                <textarea name="land_memo" class="form-control" rows="4" placeholder="메모"><?php echo $resultOne['land_memo'] ?></textarea>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="fileInput">이미지 또는 통화녹음 파일 선택 (10M)</label>
                                <input type="file" class="form-control-file" id="fileInput" name="fileInput" accept="image/*,audio/*">
                                <?php
                                if (!empty($file_list)) {
                                    foreach ($file_list as $file) {
                                        $originalFileName = htmlspecialchars($file['db_file_org_name'], ENT_QUOTES, 'UTF-8');
                                        echo '<div class="d-flex align-items-center mt-2">';
                                        echo '<span class="mr-2">파일명: ' . $originalFileName . '</span>';
                                        echo '<button type="button" class="file-delete-btn btn btn-danger btn-sm" data-file-id="' . $file['db_file_idx'] . '">삭제</button>';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                            
                            <div id="previewArea" class="mt-4">
                                <?php
                                if (!empty($file_list)) {
                                    foreach ($file_list as $file) {
                                        $file_url = G5_DATA_URL . '/file/land_file/' . $file['db_file_name'];
                                        $file_extension = strtolower(pathinfo($file['db_file_name'], PATHINFO_EXTENSION));
                                        $extension_mime_types = array(
                                            'm4a' => 'audio/mp4',
                                            'mp3' => 'audio/mpeg',
                                            'wav' => 'audio/wav',
                                            'ogg' => 'audio/ogg',
                                            'aac' => 'audio/aac',
                                            'flac' => 'audio/flac',
                                            'wma' => 'audio/x-ms-wma',
                                        );
                                        if (isset($extension_mime_types[$file_extension])) {
                                            $file_type = $extension_mime_types[$file_extension];
                                        } else {
                                            $file_type = 'application/octet-stream';
                                        }

                                        $originalFileName = htmlspecialchars($file['db_file_org_name'], ENT_QUOTES, 'UTF-8');

                                        // 파일 표시 컨테이너 시작
                                        echo '<div class="mb-3" data-file-id="' . $file['db_file_idx'] . '">';

                                        if (in_array($file_extension, array('jpg', 'jpeg', 'png', 'gif'))) {
                                            // 이미지 파일인 경우
                                            echo '<img src="' . $file_url . '" alt="' . $originalFileName . '" class="img-fluid">';
                                        } elseif (in_array($file_extension, array('m4a', 'mp3', 'wav', 'ogg', 'aac', 'flac', 'wma'))) {
                                            // 오디오 파일인 경우
                                            echo '<audio controls class="w-100">';
                                            echo '<source src="' . $file_url . '" type="' . $file_type . '">';
                                            echo 'Your browser does not support the audio element.';
                                            echo '</audio>';
                                        } elseif (in_array($file_extension, array('mp4', 'webm', 'mkv', '3gp', '3gpp'))) {
                                            // 비디오 파일인 경우
                                            echo '<video controls class="w-100" style="max-width: 500px;">';
                                            echo '<source src="' . $file_url . '" type="' . $file_type . '">';
                                            echo 'Your browser does not support the video element.';
                                            echo '</video>';
                                        } else {
                                            echo '<div class="alert alert-warning">지원하지 않는 파일 형식입니다.</div>';
                                        }
                                        echo '<p class="mt-2 mb-0">' . $originalFileName . '</p>';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">페이지&디자인</h3>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>부서*</label>
                                <select id="pg_deptno" name="pg_deptno" class="form-control bg-secondary" data-live-search="true">
                                    <option value=""><?php echo $resultOne['deptnm'] ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>직원*</label>
                                <select id="pg_mb_emp" name=pg_mb_emp class="form-control bg-secondary">
                                    <option value=""><?php echo $resultOne['mb_emp_name'] ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>고객사*</label>
                                <select id="pg_ptn_idx" name=pg_ptn_idx class="form-control bg-secondary" data-live-search="true">
                                    <option value=""><?php echo $resultOne['ptn_nm'] ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>고객사직원*</label>
                                <select id="pg_mb_ptn" name=pg_mb_ptn class="form-control bg-secondary">
                                    <option value=""><?php echo $resultOne['mb_ptn_name'] ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>디자인</label>
                                <input type="text" id="design_name" name="design_name" class="form-control bg-secondary" value="<?php echo $resultOne['design_name'] ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>페이지코드</label>
                                <select id="pg_uri" name=pg_uri class="form-control" data-live-search="true" data-style="border border-info" data-size="10" >
                                    <?php if ($pg_deptno == 3): ?>
                                        <option value="">미지정</option>
                                    <?php endif; ?>
                                    <?php for ($i = 0; $uri = sql_fetch_array($code_list); $i++) { ?>
                                        <option value="<?php echo $uri['pg_uri'] ?>" data-tokens="<?php echo $uri['pg_uri'] ?>" <?php echo get_selected($initPgUri, $uri['pg_uri']); ?>><?php echo $uri['pg_uri'] . " ["  .$uri['ptn_nm']. "|"  .$uri['mb_emp_name'] . "]"?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <?php if($w=="u") { ?>

                    <hr/>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>연동성공여부</label>
                                <input type="text" id="api_send_yn" name="api_send_yn" class="form-control bg-secondary" value="<?php echo $resultOne['api_send_yn'] ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>연동사이트</label>
                                <input type="text" id="pg_api_url" name="pg_api_url" class="form-control bg-secondary" value="<?php echo $resultOne['pg_api_url'] ?>" disabled>
                            </div>
                        </div>
                    </div>

                    <?php } ?>
                    

                </div>
                <!-- <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary" id="btn_insert">등록</button>
                    <button type="button" class="btn btn-default" id="btn_list" onclick="location.href='<?php echo G5_BIZ_URL; ?>/landing/land_list?<?php echo $qstr;?>'">목록</button>
                </div> -->
                <div class="card-footer">
                    <div class="col-12">
                        <?php echo isSaveBtn($w, $resultOne['pg_deptno'], $resultOne['pg_mb_emp'], $member, 'btn_normal', 'btn btn-primary float-right') ?>
                        <button type="button" class="btn btn-default float-right" id="btn_list" onclick="location.href='<?php echo G5_BIZ_URL; ?>/landing/land_list?<?php echo $qstr;?>'">목록</button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</section>

<script>


const fileInput = document.getElementById('fileInput');
const previewArea = document.getElementById('previewArea');

fileInput.addEventListener('change', () => {
    const file = fileInput.files[0];
    previewArea.innerHTML = ''; // 이전 미리보기 초기화

    if (file) {
        const fileType = file.type;
        const reader = new FileReader();

        reader.onload = e => {
            if (fileType.startsWith('image/')) {
                // 이미지 파일인 경우
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('img-fluid');
                previewArea.appendChild(img);
            } else if (fileType.startsWith('audio/')) {
                // 오디오 파일인 경우
                const audio = document.createElement('audio');
                audio.src = e.target.result;
                audio.controls = true;
                previewArea.appendChild(audio);
            } else {
                // 지원하지 않는 파일 타입인 경우
                const alert = document.createElement('div');
                alert.classList.add('alert', 'alert-warning');
                alert.textContent = '이미지 또는 오디오 파일만 지원합니다.';
                previewArea.appendChild(alert);
            }

            // 여기서 서버로 업로드하는 코드를 추가할 수 있습니다.
        };

        // 파일 읽기 시작
        reader.readAsDataURL(file);
    }
});



$(document).ready(function() {


    $(document).on('click', '.file-delete-btn', function() {
        var result = confirm("[주의] 삭제시 파일 복구가 불가합니다. 삭제하시겠습니까?");
        if(result){
            var act = "file_delete";
            var land_idx = <?php echo $land_idx ?>;
            var $button = $(this);
            var file_id = $button.data('file-id');

            $.ajax({
                type: "post",
                data: {
                    land_idx: land_idx,
                    act: act,
                },
                url: "land_ajax",
                dataType: "json",
                success:function(result) {
                    $button.closest('.d-flex').remove();
                    $('#previewArea').find('[data-file-id="' + file_id + '"]').remove();
                    alert("삭제가 완료 되었습니다.");
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
                }
            });
        }
    });




    $('#pg_uri').selectpicker();

    $("#pg_uri").change(function() {
        //alert($("#pg_uri").val());
        getCodeByData($("#pg_uri").val());
    });

    <?php if($w == "") { ?>
        getCodeByData('<?php echo $initPgUri ?>');
    <?php } ?>
});
function getCodeByData(param) {

    var code = param;
    var act = "codeByData";

    var $pg_deptno = $("#pg_deptno");
    $pg_deptno.empty();

    var $pg_mb_emp = $("#pg_mb_emp");
    $pg_mb_emp.empty();

    var $pg_ptn_idx = $("#pg_ptn_idx");
    $pg_ptn_idx.empty();

    var $pg_mb_ptn = $("#pg_mb_ptn");
    $pg_mb_ptn.empty();

    var $design_name = $("#design_name");
    $design_name.empty();

    $.ajax({
        type: "post",
        data: {
            code:code,
            act: act
        },
        url: "land_ajax",
        dataType: "json",
        success:function(result) {
            $pg_deptno.val(result.pg_deptno);
            $pg_deptno.append("<option>"+result.deptnm+"</option>");

            $pg_mb_emp.val(result.pg_mb_emp);
            $pg_mb_emp.append("<option>"+result.mb_emp_name+"</option>");

            $pg_ptn_idx.val(result.pg_ptn_idx);
            $pg_ptn_idx.append("<option>"+result.ptn_nm+"</option>");

            $pg_mb_ptn.val(result.pg_mb_ptn);
            $pg_mb_ptn.append("<option>"+result.mb_ptn_name+"</option>");

            $design_name.val(result.design_name);

            $("#pg_api_yn").val(result.pg_api_yn);
            
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
            return;
        }
    });
    
}

function validateForm() {

    document.getElementById("btn_small").disabled = "disabled";
    document.getElementById("btn_normal").disabled = "disabled";

    return true;
}

</script>

<?php
include_once(G5_PATH . '/tail.php');
