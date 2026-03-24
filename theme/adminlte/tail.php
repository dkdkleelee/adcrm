<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/tail.php');
    return;
}
?>
  </div>
  
  
  
  


  <footer class="main-footer">
    <strong>Copyright &copy; 2022 <a href="#">With Us</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0.0
    </div>
  </footer>


  <aside class="control-sidebar control-sidebar-dark">
  
  </aside>

  

<?php
if(G5_DEVICE_BUTTON_DISPLAY && !G5_IS_MOBILE) { ?>
<?php
}

if ($config['cf_analytics']) {
    echo $config['cf_analytics'];
}
?>
<!-- } 하단 끝 -->

<script>
// $(function() {
//     // 폰트 리사이즈 쿠키있으면 실행
//     font_resize("container", get_cookie("ck_font_resize_rmv_class"), get_cookie("ck_font_resize_add_class"));
// });
</script>

<?php
include_once(G5_THEME_PATH."/tail.sub.php");
?>