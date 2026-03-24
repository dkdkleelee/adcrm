<?php


function isShowListInput($deptno, $mb_no, $member, $kind) {
    $curr_dept_data = $deptno;
    $login_dept = $member['mb_deptno'];
    $login_level = (int)$member['mb_level'];

    if($curr_dept_data != $login_dept) {
        if($login_level >= 7) {
            $result = "";
        } else {
            $result = $kind;
        }
    } else {
        if($login_level == 4 && $mb_no != $member['mb_no']) {
            $result = $kind;;
        } else {
            $result = "";
        }
    }
    return $result;
}


function isCheckbox($i, $deptno, $mb_no, $member) {
    $curr_dept_data = $deptno;
    $login_dept = $member['mb_deptno'];
    $login_level = (int)$member['mb_level'];

    if($curr_dept_data != $login_dept) {
        if($login_level >= 7) {
            $result = '<input type="checkbox" id="chk_'.$i.'" name="chk[]" value="'.$i.'">';
        }else{

            if($login_dept == "9") {
                $result = '<input type="checkbox" id="chk_'.$i.'" name="chk[]" value="'.$i.'">';
            } else {
                $result = '<input type="checkbox" id="chk_'.$i.'" name="chk[]" value="'.$i.'" class="d-none">';
            }
            
        }
    } else {
        if($login_level == 4 && $mb_no != $member['mb_no']) {
            $result = '<input type="checkbox" id="chk_'.$i.'" name="chk[]" value="'.$i.'" class="d-none">';
        } else {
            $result = '<input type="checkbox" id="chk_'.$i.'" name="chk[]" value="'.$i.'">';
        }
        
    }
    return $result;
}

function isSaveBtn($mode, $deptno, $mb_no, $member, $id, $class) {

    if($mode == "") {
        return '<button type="submit" class="'.$class.'" id="'.$id.'">등록</button>';
    }
    $curr_dept_data = $deptno;
    $login_dept = $member['mb_deptno'];
    $login_level = (int)$member['mb_level'];

    //정환 요구사항 4팀은 본부 수정가능하게
    if ($deptno == 7 && $login_dept == 6 && $login_level >= 5) {
        return '<button type="submit" class="'.$class.'" id="'.$id.'">등록</button>';
    }

    if($curr_dept_data != $login_dept) {
        if($login_level >= 7) {
            $result = '<button type="submit" class="'.$class.'" id="'.$id.'">등록</button>';
        } else {

            if($login_dept == "9") {
                $result = '<button type="submit" class="'.$class.'" id="'.$id.'">등록</button>';
            } else {
                $result = '';
            }
        }
        
    } else {
        if($login_level == 4 && $mb_no != $member['mb_no']) {
            $result = '';
        } else {
            $result = '<button type="submit" class="'.$class.'" id="'.$id.'">등록</button>';
        }
    }
    return $result;
}

