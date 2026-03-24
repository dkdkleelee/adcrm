<?php


$phpSelf = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
$path_parts = pathinfo($phpSelf);
$basename = $path_parts['basename']; // Use this variable for action='':
$pageName = ucfirst($path_parts['filename']);
//echo $basename;
//echo $pageName;

$menu_AdminActive = "";
$menu_AdminActive2 = "";
$menu_BoardActive = "";
$menu_BoardActive2 = "";

if ($basename != "board.php") {
    $menu_AdminActive = "active ";
    $menu_AdminActive2 = ' class="active"';
}

$mb_level = $member['mb_level'];
$mb_gubun = ($member['mb_gubun'] != "E") ? "P" : "E" ;
$cur_url = G5_URL.$_SERVER['REQUEST_URI']; 
//$currpath = basename($_SERVER['PHP_SELF']);

//temp deptname
$dept_nm_sql = "
select *
  from {$g5['crm_depart']}
where deptno = {$member['mb_deptno']}
";
$dept = sql_fetch($dept_nm_sql);

$sql = " 
SELECT * 
FROM {$g5['menu_table']}
WHERE me_use = '1'
AND length(me_code) = '2'
and me_level <= {$mb_level}
and me_gubun = '{$mb_gubun}'
ORDER BY me_order, me_id ";

$result = sql_query($sql, false);
$gnb_zindex = 999;
$menu_datas = array();

$mem_deptno = $member['mb_deptno'];
$mb_level = $member['mb_level'];
$mb_no = $member['mb_no'];


$add_cond = "
and b.pg_deptno = {$mem_deptno}
";
if($mb_level <= 4) {
$add_cond .= "
and b.pg_mb_emp = {$mb_no}";
}

for ($i = 0; $row = sql_fetch_array($result); $i++) {
    
    //parent 메뉴그룹과 다르면 skip
    if($row['me_parent_grp'] == null) {
        
    } else if($row['me_code'] == 60) {
        
    } else if($member['mb_deptno'] == 9) {
        if($row['me_parent_grp'] == 10) {
            continue;
        }
    }
    
    else if($row['me_parent_grp'] != $member['parent_deptno']) {
        continue;
    }

    
   

    



    $menu_datas[$i] = $row;

    $sql2 = " 
    SELECT * 
    FROM {$g5['menu_table']}
    WHERE me_use = '1'
    AND length(me_code) = '4'
    AND substring(me_code, 1, 2) = '{$row['me_code']}'
    and me_level <= $mb_level
    ORDER BY me_order, me_id ";
    $result2 = sql_query($sql2);

    for ($k = 0; $row2 = sql_fetch_array($result2); $k++) {
        
        if($row['me_name'] == "랜딩") {

            if($row2['me_name'] == "정상 DB") {
                $use_yn = 'Y';
            } else if($row2['me_name'] == "중복 DB") {
                $use_yn = 'E';
            } else if($row2['me_name'] == "API중복 DB (IN)") {
                $use_yn = 'D';
            } else if($row2['me_name'] == "API중복 DB (OUT)") {
                $use_yn = 'R';
            }
            $today_db_sql = "
            select count(*) as today_cnt
            from gnp_crm_landing a
            left join gnp_crm_page b on a.land_pg_idx = b.page_idx 
            where 1=1 
            {$add_cond}
            and a.insert_date >= curdate()
            and a.use_yn = '{$use_yn}'
            ";
            $cnt = sql_fetch($today_db_sql);
            $today_cnt = $cnt['today_cnt'];

            if($today_cnt > 0) {
                $row2['today_cnt'] = $today_cnt;
            }
        }
        $menu_datas[$i]['sub'][$k] = $row2;
    }
}

$showInfo = "";
if($dept['deptnm'] != "") {
    $showInfo = '['.$dept['deptnm'].'] '.$member['mb_name'];
} else {
    $sql = "
    select *
      from {$g5['crm_partner']}
     where ptn_idx = {$member['mb_ptnidx']}
    ";
    $result = sql_fetch($sql);
    $showInfo = '['.$member['mb_name'].'] '.$result['ptn_nm'];
}
$i = 0;

?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="/" class="brand-link">
        <img src="<?php echo G5_THEME_URL; ?>/dist/img/withUs1.png" alt="gonPlanLogo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">With Us</span>
    </a>

    <div class="sidebar">
        <?php if($member['mb_gubun'] == "E") { ?>
            <a href="<?php echo G5_BIZ_URL; ?>/hr/employee_form?w=u&mb_id=<?php echo $member['mb_id']; ?>">
        <?php } else { ?>
            <a href="<?php echo G5_BBS_URL; ?>/member_confirm?url=register_form">
        <?php }  ?>

        
        
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <?php echo get_member_profile_image($member['mb_id'], '22', '22', 'User Image'); ?>
            </div>
            <div class="info">
                <a href="#" class="d-block"><?php echo $showInfo; ?></a>
            </div>
        </div>
        </a>

        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>



        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">


                <?php
                //1레벨
                foreach ($menu_datas as $row) {
                    if (empty($row)) continue; ?>
                    <li class="nav-item">
                        <a href="<?php echo $row['me_link']; ?>" class="nav-link <?php echo ($row['me_link']==$cur_url)?'active':''; ?>">
                            <i class="nav-icon <?php echo $row['me_faicon']; ?>"></i>
                            <p><?php echo $row['me_name']; ?> <?php echo empty($row['sub']) ? '' : '<i class="fas fa-angle-left right">' ?></i> </p>
                        </a>

                        <?php if (empty($row['sub'])) continue; ?>
                        <ul class="nav nav-treeview">
                            <?php
                            $k = 0;

                            foreach ((array) $row['sub'] as $row2) {
                                //2레벨
                                if (empty($row2)) continue; ?>
                                <li class="nav-item">
                                    <a href="<?php echo $row2['me_link']; ?>" target="_<?php echo $row2['me_target']; ?>" class="nav-link">
                                        <i class="fas fa-minus nav-icon"></i>
                                        <p><?php echo $row2['me_name'] . "<span class='badge badge-info right'>".$row2['today_cnt']."</span>" ?></p>
                                    </a>

                                </li>
                            <?php
                                $k++;
                            }

                            ?>
                        </ul>
                    <?php
                    $i++;
                }

                if ($i == 0) {  ?>

                <?php } ?>

                </li>
            </ul>
        </nav>
    </div>
</aside>


<script>
$(document).ready(function() {
    const url = window.location;
    $('ul.nav-sidebar a').filter(function() {
        return this.href == url;
    }).parent().addClass('active');
    $('ul.nav-treeview a').filter(function() {
        return this.href == url;
    }).parentsUntil(".sidebar-menu > .nav-treeview").addClass('menu-open');
    $('ul.nav-treeview a').filter(function() {
        return this.href == url;
    }).addClass('active');
    $('li.has-treeview a').filter(function() {
        return this.href == url;
    }).addClass('active');
    $('ul.nav-treeview a').filter(function() {
        return this.href == url;
    }).parentsUntil(".sidebar-menu > .nav-treeview").children(0).addClass('active');
});

</script>