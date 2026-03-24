<?php

require_once '../../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

if ($w == '') {
    $title = "DESIGN 등록";

    $sel_code1 = "1";
    $sel_dept = $member['mb_deptno'];
    $sel_emp = $member['mb_no'];

} elseif ($w == 'u') {
    $title = "DESIGN 수정";

    $resultOneSql = "
    select a.*
         , b.deptnm 
         , c.mb_name 
         , d.comm_idx
         , d.comm_pcd
    from {$g5['crm_design']} a
    left join {$g5['crm_depart']}   b on a.des_deptno = b.deptno
    left join {$g5['member_table']} c on a.des_mb_no = c.mb_no 
    left join {$g5['crm_common']}   d on a.des_cate_code = d.comm_idx
    where 1=1
    and design_idx = {$design_idx}
    ";
    $resultOne = sql_fetch($resultOneSql);

    $sel_code1 = $resultOne['comm_pcd'];
    $sel_code2 = $resultOne['comm_idx'];
    $sel_dept = $resultOne['des_deptno'];
    $sel_emp = $resultOne['des_mb_no'];
}

$g5['title'] = $title;
include_once(G5_PATH . '/head.php');

//공통코드리스트
$code_sql = "
select comm_idx
     , comm_pcd 
     , comm_pnm 
     , comm_cd 
     , comm_nm 
     , comm_bigo
 from {$g5['crm_common']}
 where 1=1 
 and use_yn = 'Y' 
 and comm_pcd = {$sel_code1}
 order by comm_cd
";
$code_list = sql_query($code_sql);

//부서코드
$dept_sql = "
select deptno
     , deptnm
     , parent_deptno
  from {$g5['crm_depart']} a
  left join {$g5['member_table']} b on a.deptno = b.mb_deptno 
 where use_yn = 'Y'
   and b.mb_gubun = 'E'
   and is_login = 'Y'
   and parent_deptno != 1
group by a.deptno 
order by coalesce(parent_deptno, deptno), parent_deptno is not null, deptno
";
$dept_list = sql_query($dept_sql);

//부서별직원코드
$member_sql = "
select mb_no 
     , mb_id 
     , mb_name
     , mb_deptno 
  from {$g5['member_table']}
 where mb_gubun = 'E'
   and is_login = 'Y'
   and mb_deptno = {$sel_dept}
 order by mb_name asc
";
$member_list = sql_query($member_sql);


?>

<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/ace/1.4.1/ace.js"></script>

<section class="content">
    <div class="container-fluid">
        <form name="designForm" id="designForm" action="./design_form_update" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">

            <div class="card card-danger">

                <div class="card-header">
                    <h3 class="card-title">디자인 기본설정</h3>
                    <div class="text-right">
                        <?php echo isSaveBtn($w, $resultOne['des_deptno'], $resultOne['des_mb_no'], $member, 'btn_small', 'btn btn-primary btn-xs') ?>
                        <button type="button" class="btn btn-default btn-xs" id="btn_list" onclick="location.href='<?php echo G5_BIZ_URL; ?>/design/design_list?<?php echo $qstr;?>'">목록</button>
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
                    <input type="hidden" name="design_idx" value="<?php echo $resultOne['design_idx'] ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>디자인</code></label>
                                <input type="text" id="design_name" name="design_name" class="form-control border-info" value="<?php echo $resultOne['design_name'] ?>" placeholder="DESIGN 이름" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>디자인설명*</code></label>
                                <input type="text" id="des_memo" name="des_memo" class="form-control border-info" value="<?php echo $resultOne['des_memo'] ?>" placeholder="DESIGN 설명" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>카테고리*</code></label>
                                <select id="category" name=category class="custom-select border-info">
                                    <option value="1" <?php echo get_selected($resultOne['comm_pcd'], '1'); ?>>1:광고문의</option>
                                    <option value="2" <?php echo get_selected($resultOne['comm_pcd'], '2'); ?>>2:스토어</option>
                                    <option value="3" <?php echo get_selected($resultOne['comm_pcd'], '3'); ?>>3:DB</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>업태*</code></label>
                                <select id="des_cate_code" name="des_cate_code" class="custom-select border-info">
                                    <?php for ($i = 0; $code = sql_fetch_array($code_list); $i++) { ?>
                                        <option value="<?php echo $code['comm_idx'] ?>" <?php echo get_selected($sel_code2, $code['comm_idx']); ?>><?php echo $code['comm_nm'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>부서*</code></label>
                                <select id="des_deptno" name="des_deptno" class="form-control" data-live-search="true" data-style="border border-info"s>
                                    <?php for ($i = 0; $dept = sql_fetch_array($dept_list); $i++) { ?>
                                        <option value="<?php echo $dept['deptno'] ?>" data-tokens="<?php echo $dept['deptnm'] ?>" <?php echo get_selected($sel_dept, $dept['deptno']); ?>  ><?php echo $dept['deptnm'] ?></option>

                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>직원*</code></label>
                                <select id="des_mb_no" name="des_mb_no" class="custom-select border-info">
                                    <option value="">미지정</option>
                                    <?php for ($i = 0; $emp = sql_fetch_array($member_list); $i++) { ?>
                                        <option value="<?php echo $emp['mb_no'] ?>" <?php echo get_selected($sel_emp, $emp['mb_no']); ?> ><?php echo $emp['mb_name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>상태*</code></label>
                                <select id="des_status" name="des_status" class="custom-select border-info">
                                    <option value="1" <?php echo get_selected($resultOne['des_status'], 1); ?>>수정중</option>
                                    <option value="2" <?php echo get_selected($resultOne['des_status'], 2); ?>>운영중</option>
                                    <option value="3" <?php echo get_selected($resultOne['des_status'], 3); ?>>미사용</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>디자인 구분</code></label>
                                <select id="des_gubun" name="des_gubun" class="custom-select border-info">
                                    <option value=""  <?php echo get_selected($resultOne['des_gubun'], ''); ?>> 미지정</option>
                                    <option value="1" <?php echo get_selected($resultOne['des_gubun'], 1); ?>> PC/모바일 분리형</option>
                                    <option value="2" <?php echo get_selected($resultOne['des_gubun'], 2); ?>> 단일 이벤트형</option>
                                    <option value="3" <?php echo get_selected($resultOne['des_gubun'], 3); ?>> 기사형</option>
                                    <option value="5" <?php echo get_selected($resultOne['des_gubun'], 5); ?>> 복사제작</option>
                                    <option value="4" <?php echo get_selected($resultOne['des_gubun'], 4); ?>> 기타</option>
                                </select>
                            </div>
                        </div>
                        
                    </div>

                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">파일서버</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label><code>아이콘</code></label>
                            <div class="custom-file">
                                <input type="file" name="des_shortcut" id="des_shortcut" >
                                <?php if($w == "u" && $resultOne['des_shortcut'] != "") { ?>
                                    <a data-toggle="tooltip" title="<img src='<?php echo $resultOne['des_shortcut'] ?>' />"><i class="fas fa-images"></i></a>
                                <?php } ?>
                                
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label><code>스크린샷</code></label>
                            <div class="custom-file">
                                <input type="file" name="des_screen" id="des_screen" >
                                <?php if($w == "u" && $resultOne['des_screen'] != "") { ?>
                                    <a data-toggle="tooltip" title="<img src='<?php echo $resultOne['des_screen'] ?>' height='180' width='280'/>"><i class="fas fa-images"></i></a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 mb-2">
                        <span class="badge badge-warning">제목 : {contTitle}</span>
                        <span class="badge badge-warning">대표자 : {contReprnm}</span>
                        <span class="badge badge-warning">업체명 : {contCompName}</span>
                        <span class="badge badge-warning">연락처 : {contTel}</span>
                        <span class="badge badge-warning">이메일 : {contEmail}</span>
                        <span class="badge badge-warning">주소 : {contAddr}</span>
                        <span class="badge badge-warning">사업자번호 : {contCompNum}</span>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="iframe-wrap">
                                <iframe width="853" height="480" src="<?php echo G5_LAND_URL?>/file/" allow="clipboard-write" frameborder="0" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">HTML</h3>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                                <i class="fas fa-expand"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <textarea  rows="50" name="des_html" data-editor="markdown" id="des_html" style="height:1005px"><?php echo htmlspecialchars($resultOne['des_html']); ?></textarea>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">PREVIEW</h3>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                                <i class="fas fa-expand"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <iframe id="div_preview" name="div_preview" src="" frameborder="0" width="100%" height="1000px"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="col-12">
                    <?php if($w == "u") { ?>
                        <a href="#" class="btn btn-default">최초등록 <span class="badge badge-primary"><?php echo $resultOne['insert_user_name'] ?></span><span class="badge badge-primary"><?php echo substr($resultOne['insert_date'],0,16) ?></span></a>
                        <a href="#" class="btn btn-default">최종수정 <span class="badge badge-warning"><?php echo $resultOne['update_user_name'] ?></span><span class="badge badge-warning"><?php echo substr($resultOne['update_date'],0,16) ?></span></a>
                    <?php } ?>
                        <?php echo isSaveBtn($w, $resultOne['des_deptno'], $resultOne['des_mb_no'], $member, 'btn_normal', 'btn btn-primary float-right') ?>
                        <button type="button" class="btn btn-default float-right" id="btn_list" onclick="location.href='<?php echo G5_BIZ_URL; ?>/design/design_list?<?php echo $qstr;?>'">목록</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>


<script>
    $(document).ready(function() {
        
        $('textarea[data-editor]').each(function() {
            var textarea = $(this);
            var mode = textarea.data('editor');
            // create the editor div
            var div = $('<div>', {
                'width': textarea.outerWidth("100%"),
                'height': textarea.outerHeight(),
                'class': textarea.attr('class')
            }).insertBefore(textarea);
            // hide the original text area
            textarea.hide();
            // configure the editor
            var editor = ace.edit(div[0], {
                value: textarea.val().replace("", ""),
                selectionStyle: 'line',// "line"|"text"
                highlightActiveLine: true, // boolean
                highlightSelectedWord: true, // boolean
                readOnly: false, // boolean: true if read only
                cursorStyle: 'ace', // "ace"|"slim"|"smooth"|"wide"
                mergeUndoDeltas: true, // false|true|"always"
                behavioursEnabled: true, // boolean: true if enable custom behaviours
                wrapBehavioursEnabled: true, // boolean
                autoScrollEditorIntoView: undefined, // boolean: this is needed if editor is inside scrollable page
                keyboardHandler: null, // function: handle custom keyboard events
                
                // renderer options
                animatedScroll: false, // boolean: true if scroll should be animated
                displayIndentGuides: false, // boolean: true if the indent should be shown. See 'showInvisibles'
                showInvisibles: false, // boolean -> displayIndentGuides: true if show the invisible tabs/spaces in indents
                showPrintMargin: true, // boolean: true if show the vertical print margin
                printMarginColumn: 80, // number: number of columns for vertical print margin
                printMargin: undefined, // boolean | number: showPrintMargin | printMarginColumn
                showGutter: true, // boolean: true if show line gutter
                fadeFoldWidgets: false, // boolean: true if the fold lines should be faded
                showFoldWidgets: true, // boolean: true if the fold lines should be shown ?
                showLineNumbers: true,
                highlightGutterLine: false, // boolean: true if the gutter line should be highlighted
                hScrollBarAlwaysVisible: false, // boolean: true if the horizontal scroll bar should be shown regardless
                vScrollBarAlwaysVisible: false, // boolean: true if the vertical scroll bar should be shown regardless
                fontSize: 13, // number | string: set the font size to this many pixels
                fontFamily: undefined, // string: set the font-family css value
                maxLines: undefined, // number: set the maximum lines possible. This will make the editor height changes
                minLines: undefined, // number: set the minimum lines possible. This will make the editor height changes
                maxPixelHeight: 0, // number -> maxLines: set the maximum height in pixel, when 'maxLines' is defined. 
                scrollPastEnd: 0, // number -> !maxLines: if positive, user can scroll pass the last line and go n * editorHeight more distance 
                fixedWidthGutter: false, // boolean: true if the gutter should be fixed width
                theme: 'ace/theme/tomorrow_night_eighties', // theme string from ace/theme or custom?
                
                // mouseHandler options
                scrollSpeed: 2, // number: the scroll speed index
                dragDelay: 0, // number: the drag delay before drag starts. it's 150ms for mac by default 
                dragEnabled: true, // boolean: enable dragging
                focusTimout: 0, // number: the focus delay before focus starts.
                tooltipFollowsMouse: true, // boolean: true if the gutter tooltip should follow mouse

                // session options
                firstLineNumber: 1, // number: the line number in first line
                overwrite: false, // boolean
                newLineMode: 'auto', // "auto" | "unix" | "windows"
                useWorker: true, // boolean: true if use web worker for loading scripts
                useSoftTabs: true, // boolean: true if we want to use spaces than tabs
                tabSize: 4, // number
                wrap: true, // boolean | string | number: true/'free' means wrap instead of horizontal scroll, false/'off' means horizontal scroll instead of wrap, and number means number of column before wrap. -1 means wrap at print margin
                indentedSoftWrap: true, // boolean
                foldStyle: 'manual', // enum: 'manual'/'markbegin'/'markbeginend'.
                mode: 'ace/mode/html' // string: path to language mode 

            }); 
            textarea[0].form.addEventListener("submit", function() {
                textarea.val(editor.getSession().getValue());
            });
            var text = editor.getSession().getValue();
            preview(text, '<?php echo G5_LAND_URL?>', 'div_preview');
            editor.on("change", function() {
                var text = editor.getSession().getValue();
                var url = "<?php echo G5_LAND_URL?>";
                var target = "div_preview";
                var obj = $('<form action="'+url+'" method="post" id="'+target+'" target="'+target+'"/>');

                var inval = $('<textarea name="preview"/>');
                inval.val(text);
                obj.append(inval);
                $('body').prepend(obj);
                obj.submit();
                obj.remove();
            });
        });

        $('[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            placement: 'bottom',
            html: true
        });

        $('#des_deptno').selectpicker();
        $("#des_deptno").change(function() {
            var deptno = $(this).val();
            var act = "deptByEmp";

            var $target = $("#des_mb_no");
            $target.empty();

            $.ajax({
                type: "post",
                data: {
                    deptno: deptno ,
                    act: act
                },
                url: "<?php echo G5_BIZ_URL?>/common/code_ajax",
                dataType: "json", //전송받는 데이터형태 json
                success:function(result) {
                    $target.append(result);
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
                }
            });
        });

        $("#category").change(function() {
            var comm_pcd = $(this).val();
            var act = "commonCode";

            var $target = $("#des_cate_code");
            $target.empty();

            $.ajax({
                type: "post",
                data: {
                comm_pcd: comm_pcd ,
                act: act
                },
                url: "<?php echo G5_BIZ_URL?>/common/code_ajax",
                dataType: "json", //전송받는 데이터형태 json
                success:function(result) {
                    $target.append(result);
                },
                error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
                }
            });
        });

        $(".inpt").change(function(){
            $(this).removeClass("is-invalid");
        });

        $('.selc').focusout(function() {
            $(this).removeClass("is-invalid");
        });
    });

    
    function validateForm() {
        document.getElementById("btn_small").disabled = "disabled";
        document.getElementById("btn_normal").disabled = "disabled"; 

        // alert($editor);
        // if ($editor.length > 0) {
        //     var editor = ace.edit('editor');
        //     editor.session.setMode("ace/mode/css");
        //     $editor.closest('form').submit(function() {
        //         var code = editor.getValue();
        //         $editor.prev('input[type=hidden]').val(code);                
        //     });
        // }

        return true;
    }

    function preview(value, url, targetStr) {
        if(value == "") {
            return false;
        }
        var formObj = $('<form action="'+url+'" method="post" id="'+targetStr+'Form" target="'+targetStr+'"></form>');

        var inputObj = $('<textarea name="preview"></textarea>');
        inputObj.val(value);

        formObj.append(inputObj);

        $('body').prepend(formObj);

        formObj.submit();
        formObj.remove();
    }
</script>

<?php
include_once(G5_PATH . '/tail.php');