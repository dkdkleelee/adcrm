<?php
if(!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if(!$config['cf_social_login_use']) return; //소셜 로그인을 사용하지 않으면

$social_pop_once = false;

$self_url = G5_BBS_URL."/login.php";

//새창을 사용한다면
if(G5_SOCIAL_USE_POPUP ) {
    $self_url = G5_SOCIAL_LOGIN_URL.'/popup.php';
}

?>
<div class="sns-wrap text-center mb-4">
	<?php if(social_service_check('naver')){     //네이버 로그인을 사용한다면 ?>
	<a href="<?php echo $self_url;?>?provider=naver&amp;url=<?php echo $urlencode;?>" class="social_link" title="네이버"><img src="<?php echo get_social_skin_url().'/img/sns_naver_s.png' ?>"></a>
	<?php } ?>

	<?php if(social_service_check('kakao')){     //카카오 로그인을 사용한다면 ?>
	<a href="<?php echo $self_url;?>?provider=kakao&amp;url=<?php echo $urlencode;?>" class="social_link" title="카카오"><img src="<?php echo get_social_skin_url().'/img/sns_kakao_s.png' ?>"></a>
	<?php }     //end if ?>
	<?php if(social_service_check('facebook')){     //페이스북 로그인을 사용한다면 ?>
	<a href="<?php echo $self_url;?>?provider=facebook&amp;url=<?php echo $urlencode;?>" class="social_link" title="페이스북"><img src="<?php echo get_social_skin_url().'/img/sns_facebook_s.png' ?>"></a>
	<?php }     //end if ?>
	<?php if(social_service_check('google')){     //구글 로그인을 사용한다면 ?>
	<a href="<?php echo $self_url;?>?provider=google&amp;url=<?php echo $urlencode;?>" class="social_link" title="구글"><img src="<?php echo get_social_skin_url().'/img/sns_google_s.png' ?>"></a>
	<?php }     //end if ?>
	<?php if(social_service_check('twitter')){     //트위터 로그인을 사용한다면 ?>
	<a href="<?php echo $self_url;?>?provider=twitter&amp;url=<?php echo $urlencode;?>" class="social_link" title="트위터"><img src="<?php echo get_social_skin_url().'/img/sns_twitter_s.png' ?>"></a>
	<?php }     //end if ?>
	<?php if(social_service_check('payco')){     //페이코 로그인을 사용한다면 ?>
	<a href="<?php echo $self_url;?>?provider=payco&amp;url=<?php echo $urlencode;?>" class="social_link" title="페이코"><img src="<?php echo get_social_skin_url().'/img/sns_payco_s.png' ?>"></a>
	<?php }     //end if ?>
</div>

<?php if(G5_SOCIAL_USE_POPUP && !$social_pop_once ){
$social_pop_once = true;
?>
<script>
	jQuery(function($){
		$(".sns-wrap").on("click", "a.social_link", function(e){
			e.preventDefault();

			var pop_url = $(this).attr("href");
			var newWin = window.open(
				pop_url, 
				"social_sing_on", 
				"location=0,status=0,scrollbars=1,width=600,height=500"
			);

			if(!newWin || newWin.closed || typeof newWin.closed=='undefined')
				 alert('브라우저에서 팝업이 차단되어 있습니다. 팝업 활성화 후 다시 시도해 주세요.');

			return false;
		});
	});
</script>
<?php } ?>