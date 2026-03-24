<?php
require_once '../../common.php';


$act = isset($_POST['act']) ? strip_tags($_POST['act']) : '';

if ($act === "design_copy") {

    $design_idx = isset($_POST['design_idx']) ? strip_tags($_POST['design_idx']) : '';
    $result = array();

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

    $dept = sql_fetch_array($dept_list);
    $cond2 = 'mb_deptno = '.$dept['deptno'];
    sql_data_seek($dept_list, 0);

    $stack = array();
    for ($i = 0; $row = sql_fetch_array($dept_list); $i++) { 
        array_push( $stack, $row );
    }
    array_push( $result, $stack );
    
    //부서별직원코드
    $member_sql = "
    select mb_no 
        , mb_id 
        , mb_name
        , mb_deptno 
    from {$g5['member_table']}
    where mb_gubun = 'E'
    and is_login = 'Y'
    and $cond2
    ";
    $member_list = sql_query($member_sql);

    $stack = array();
    for ($i = 0; $row = sql_fetch_array($member_list); $i++) { 
        array_push( $stack, $row );
    }
    
    array_push( $result, $stack );

    echo json_encode($result);

} else if ($act === "design_delete") {

    $design_idx = isset($_POST['design_idx']) ? strip_tags($_POST['design_idx']) : '';

    $delete_sql = "
    delete from {$g5['crm_design']} where design_idx = {$design_idx}
    ";
    isSqlError(sql_query($delete_sql), $delete_sql);



    if($delete_result)
        echo "삭제완료";
    else 
        echo "에러발생";
} else if ($act === "getPageList") {

    $design_idx = isset($_POST['design_idx']) ? strip_tags($_POST['design_idx']) : '';

    $getPageSql = "
select a.page_idx 
     , a.pg_uri 
     , b.design_idx 
     , b.design_name 
     , c.deptno 
     , c.deptnm 
     , ifnull (f_get_mb_name(a.pg_mb_emp), a.pg_mb_emp) as mb_emp_name
     , a.insert_date
  from {$g5['crm_page']} a
  left join {$g5['crm_design']} b on a.pg_des_idx = b.design_idx 
  left join {$g5['crm_depart']} c on a.pg_deptno = c.deptno 
 where b.design_idx = {$design_idx}
 order by a.page_idx desc
    ";
    $page_list = sql_query($getPageSql);
    
    $result = array();
    $stack = array();
    for ($i = 0; $row = sql_fetch_array($page_list); $i++) { 
        $str = "
        <tr>
            <td>".($i+1)."</td>
            <td><a href='".G5_BIZ_URL."/page/page_list?sfl=deptno&search_deptno=".$row['deptno']."' target='_self'>{$row['deptnm']}</a></td>
            <td>{$row['design_name']}</td>
            <td><a href='".G5_BIZ_URL."/page/page_list?sfl=pg_uri&stx=".$row['pg_uri']."' target='_self'>{$row['pg_uri']} {$row['mb_emp_name']}</a></td>
            <td>{$row['insert_date']}</td>
        </tr>
        ";
        array_push( $stack, $str );
        //array_push( $stack, $row );
    }
    array_push( $result, $stack );
    echo json_encode($result);

} else if ($act === "design_pick") {
    $des_brd_no = isset($_POST['des_brd_no']) ? strip_tags($_POST['des_brd_no']) : '';
    $bo_table = isset($_POST['bo_table']) ? strip_tags($_POST['bo_table']) : '';
    $pick_me = $member['mb_name'];

    $sql = "
    update {$bo_table} set
        wr_9 = '{$pick_me}' 
      , wr_10 = now()
    where wr_id = {$des_brd_no}
    ";
    isSqlError(sql_query($sql), $sql);
    echo json_encode("담당자 지정 완료");
    
}