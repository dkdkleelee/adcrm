<?php

// install후 처리해야할 ddl dml문 처리


$sql = "
create function f_getcode(p_code integer) returns varchar(100) charset utf8mb4 collate utf8mb4_general_ci
begin
	declare return_val varchar(100);
	select concat(comm_pnm , '-' , comm_nm) into return_val
	from ".$table_prefix."crm_common
   where comm_idx= p_code
   limit 1;
  return return_val;
end;
";
$result = sql_query($sql, false);

$sql = "
create function f_get_mb_name(p_code integer) returns varchar(100) charset utf8mb4 collate utf8mb4_general_ci
begin
	declare return_val varchar(100);
	select mb_name into return_val
	  from ".$table_prefix."member 
	 where mb_no = p_code
   limit 1;
  return return_val;
end
";
$result = sql_query($sql, false);

/* ---------------------------------- */
/* crm_partner > crm_common.comm_idx  */
/* ---------------------------------- */
$sql = "ALTER TABLE ".$table_prefix."crm_partner ADD CONSTRAINT ".$table_prefix."crm_partner_FK FOREIGN KEY (cate_code) REFERENCES ".$table_prefix."crm_common(comm_idx)";
$result = sql_query($sql, false);


/* ---------------------------------- */
/* crm_design > member.mb_no          */
/* crm_design > crm_common.comm_idx   */
/* crm_design > crm_depart.deptno     */
/* ---------------------------------- */

$sql = "ALTER TABLE ".$table_prefix."crm_design ADD CONSTRAINT ".$table_prefix."crm_design_FK_2 FOREIGN KEY (des_mb_no) REFERENCES ".$table_prefix."member(mb_no) ON DELETE SET NULL";
$result = sql_query($sql, false);

$sql = "ALTER TABLE ".$table_prefix."crm_design ADD CONSTRAINT ".$table_prefix."crm_design_FK FOREIGN KEY (des_cate_code) REFERENCES ".$table_prefix."crm_common(comm_idx)";
$result = sql_query($sql, false);

$sql = "ALTER TABLE ".$table_prefix."crm_design ADD CONSTRAINT ".$table_prefix."crm_design_FK_1 FOREIGN KEY (des_deptno) REFERENCES ".$table_prefix."crm_depart(deptno)";
$result = sql_query($sql, false);






/* ---------------------------------- */
/* crm_partner_hist > ptn_idx & deptno */
/* ---------------------------------- */
//$sql = "ALTER TABLE ".$table_prefix."crm_partner_hist ADD CONSTRAINT ".$table_prefix."crm_partner_hist_FK FOREIGN KEY (ptn_idx) REFERENCES ".$table_prefix."crm_partner(ptn_idx); ";
//$result = sql_query($sql, false);


/* ---------------------------------- */
/* member > crm_depart.deptno         */
/* member > crm_partner.ptn_idx       */
/* ---------------------------------- */
$sql = "ALTER TABLE ".$table_prefix."member ADD CONSTRAINT ".$table_prefix."member_FK FOREIGN KEY (mb_deptno) REFERENCES ".$table_prefix."crm_depart(deptno);";
$result = sql_query($sql, false);

$sql = "ALTER TABLE ".$table_prefix."member ADD CONSTRAINT ".$table_prefix."member_FK_1 FOREIGN KEY (mb_ptnidx) REFERENCES ".$table_prefix."crm_partner(ptn_idx);";
$result = sql_query($sql, false);


/* ---------------------------------- */
/* 5.design */
/* ---------------------------------- */
//$sql = "ALTER TABLE .$table_prefix.crm_design ADD CONSTRAINT .$table_prefix.crm_design_pk PRIMARY KEY (design_idx)";
// $sql = "ALTER TABLE .$table_prefix.crm_design ADD CONSTRAINT .$table_prefix.crm_design_FK FOREIGN KEY (des_deptno) REFERENCES .$table_prefix.crm_depart(deptno)";
// $result = sql_query($sql, false);
// $sql = "ALTER TABLE .$table_prefix.crm_design ADD CONSTRAINT .$table_prefix.crm_design_FK_1 FOREIGN KEY (des_ptn_idx) REFERENCES .$table_prefix.crm_partner(ptn_idx)";
// sql_query($sql, false);



/* ---------------------------------- */
/* 5.page  */
/* ---------------------------------- */
// $sql = "ALTER TABLE ".$table_prefix."crm_page ADD CONSTRAINT gnp_crm_page_FK FOREIGN KEY (pg_deptno) REFERENCES ".$table_prefix."crm_depart(deptno) ON DELETE CASCADE ON UPDATE CASCADE";

$sql = "ALTER TABLE ".$table_prefix."crm_page ADD CONSTRAINT gnp_crm_page_FK FOREIGN KEY (pg_deptno) REFERENCES ".$table_prefix."crm_depart(deptno)";
$result = sql_query($sql, false);

$sql = "ALTER TABLE ".$table_prefix."crm_page ADD CONSTRAINT gnp_crm_page_FK_1 FOREIGN KEY (pg_ptn_idx) REFERENCES ".$table_prefix."crm_partner(ptn_idx)";
$result = sql_query($sql, false);

$sql = "ALTER TABLE ".$table_prefix."crm_page ADD CONSTRAINT gnp_crm_page_FK_2 FOREIGN KEY (pg_des_idx) REFERENCES ".$table_prefix."crm_design(design_idx)";
$result = sql_query($sql, false);

$sql = "ALTER TABLE ".$table_prefix."crm_page ADD CONSTRAINT gnp_crm_page_FK_3 FOREIGN KEY (pg_mb_emp) REFERENCES ".$table_prefix."member(mb_no)";
$result = sql_query($sql, false);

$sql = "ALTER TABLE ".$table_prefix."crm_page ADD CONSTRAINT gnp_crm_page_FK_4 FOREIGN KEY (pg_mb_ptn) REFERENCES ".$table_prefix."member(mb_no)";
$result = sql_query($sql, false);

$sql = "CREATE INDEX ".$table_prefix."crm_page_pg_uri_IDX USING BTREE ON ".$table_prefix."crm_page (pg_uri,pg_domain);";
$result = sql_query($sql, false);

//추가 cascade restrict 고려해야함
//CREATE INDEX ".$table_prefix."crm_page_pg_uri_IDX USING BTREE ON ".$table_prefix."crm_page (pg_uri);
//CREATE INDEX ".$table_prefix."crm_landing_land_used_data_IDX USING BTREE ON ".$table_prefix."crm_landing (land_used_data);

?>