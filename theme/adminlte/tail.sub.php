<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>





<script src="<?php echo G5_THEME_URL; ?>/plugins/datatables/jquery.dataTables.min.js"></script>

<!-- <script src="<?php echo G5_THEME_URL; ?>/plugins/select2/js/select2.full.min.js"></script> -->
<script src="<?php echo G5_THEME_URL; ?>/plugins/bootstrap-select/js/bootstrap-select.js"></script>

<script src="<?php echo G5_THEME_URL; ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?php echo G5_THEME_URL; ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?php echo G5_THEME_URL; ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="<?php echo G5_THEME_URL; ?>/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="<?php echo G5_THEME_URL; ?>/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="<?php echo G5_THEME_URL; ?>/plugins/jszip/jszip.min.js"></script>
<script src="<?php echo G5_THEME_URL; ?>/plugins/pdfmake/pdfmake.min.js"></script>
<script src="<?php echo G5_THEME_URL; ?>/plugins/pdfmake/vfs_fonts.js"></script>
<script src="<?php echo G5_THEME_URL; ?>/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="<?php echo G5_THEME_URL; ?>/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="<?php echo G5_THEME_URL; ?>/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<script src="<?php echo G5_THEME_URL; ?>/dist/js/adminlte.min.js"></script>
<script src="<?php echo G5_THEME_URL; ?>/dist/js/demo3.js"></script>

<script src="<?php echo G5_THEME_URL; ?>/js/custom.js"></script>


<?php if ($is_admin == 'super') {  ?><!-- <div style='float:left; text-align:center;'>RUN TIME : <?php echo get_microtime()-$begin_time; ?><br></div> --><?php }  ?>

<!-- ie6,7에서 사이드뷰가 게시판 목록에서 아래 사이드뷰에 가려지는 현상 수정 -->
<!--[if lte IE 7]>
<script>
$(function() {
    var $sv_use = $(".sv_use");
    var count = $sv_use.length;

    $sv_use.each(function() {
        $(this).css("z-index", count);
        $(this).css("position", "relative");
        count = count - 1;
    });
});
</script>
<![endif]-->

</body>
</html>
<?php echo html_end(); // HTML 마지막 처리 함수 : 반드시 넣어주시기 바랍니다. ?>