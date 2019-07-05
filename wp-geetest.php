<?php
/*
Plugin Name:  wp-geetest
Plugin URI:   https://github.com/sy-records/wp-geetest
Description:  基于极验3.0，在WordPress的登录和评论时加入极验验证。
Version:      1.0.1
Author:       沈唁志
Author URI:   https://qq52o.me
License:      Apache 2.0
*/

// login
function add_captcha_env(){
	// 如果外链失效的话就手动转为本地
//	wp_enqueue_script( 'jquery.js', plugins_url('js/jquery.js',__FILE__) , array(), '1.9.1', false );
	wp_enqueue_script( 'jquery.js', "//cdn.staticfile.org/jquery/3.3.1/jquery.min.js", array(), '3.3.1', false );
	wp_enqueue_script( 'gt.js', plugins_url('js/gt.js',__FILE__) , array(), '3.0.0', false );
//	wp_enqueue_script( 'layer.js', plugins_url('js/layer/layer.js',__FILE__) , array(), '3.1.1', false );
	wp_enqueue_script( 'layer.js', "//cdn.staticfile.org/layer/2.3/layer.js" , array(), '2.3', false );
}
add_action('login_enqueue_scripts','add_captcha_env');

function add_captcha_style(){
    echo '<div id="embed-captcha" style="margin: 0 0 1em 0"></div>';
}
add_action('login_form','add_captcha_style');

// post登录验证实例
// function add_geetest_login_val() {
//     if (!empty($_POST)){
//         if (!$_POST['geetest_challenge'] || !$_POST['geetest_validate'] || !$_POST['geetest_seccode']) {
//             return  new WP_Error('broke', __("验证未通过"));
//         }
//     }
// }
// add_action('login_form_login','add_geetest_login_val');

function add_login_captcha_API1(){
    echo '<script>
    var handlerEmbed = function (captchaObj) {
        $("#wp-submit").click(function (e) {
            var validate = captchaObj.getValidate();
            if (!validate) {
                layer.msg("请先点击按钮完成验证～");
                e.preventDefault();
            }
        });
        captchaObj.appendTo("#embed-captcha");
    };
    $.ajax({
        url: "'.plugins_url('',__FILE__).'/web/StartCaptchaServlet.php?t=" + (new Date()).getTime(), 
        type: "get",
        dataType: "json",
        success: function (data) {
            initGeetest({
                gt: data.gt,
                challenge: data.challenge,
                new_captcha: data.new_captcha,
                width: "100%",
                product: "embed", 
                offline: !data.success 
            }, handlerEmbed);
        }
    });
</script>';
}
add_action('login_footer','add_login_captcha_API1');


// comment
function add_comment_captcha_style(){
    echo '<p id="embed-captcha" style="float:left;width:100%;"></p>';
}
function add_comment_captcha_API1(){
    echo '<script>
    var handlerEmbed = function (captchaObj) {
        $("#submit").click(function (e) {
            var validate = captchaObj.getValidate();
            if (!validate) {
                layer.msg("请先点击按钮完成验证～");
                e.preventDefault();
            }
        });
        captchaObj.appendTo("#embed-captcha");
    };
    $.ajax({
        // 获取id，challenge，success（是否启用failback）
        url: "'.plugins_url('',__FILE__).'/web/StartCaptchaServlet.php?t=" + (new Date()).getTime(),
        type: "get",
        dataType: "json",
        success: function (data) {
            initGeetest({
                gt: data.gt,
                challenge: data.challenge,
                new_captcha: data.new_captcha,
                width: "20rem",
                product: "embed", 
                offline: !data.success
            }, handlerEmbed);
        }
    });
</script>';
}

// 登录态无需验证
if(!function_exists('is_user_logged_in')) {
	require (ABSPATH . WPINC . '/pluggable.php');
}
if (!is_user_logged_in()) {
	add_action('wp_enqueue_scripts', 'add_captcha_env');
	add_action('comment_form_after_fields', 'add_comment_captcha_style');
	add_action('comment_form_after', 'add_comment_captcha_API1');
}

//二次验证
function add_captcha_API2($user){
    require_once dirname(__FILE__). '/lib/class.geetestlib.php';
    require_once dirname(__FILE__). '/config/config.php';
    $GtSdk = new GeetestLib(CAPTCHA_ID, PRIVATE_KEY);

    session_start();
    $data = array(
            "user_id" => $_SESSION['user_id'], 
            "client_type" => "web",
            "ip_address" => $_SERVER["REMOTE_ADDR"]
        );


    if ($_SESSION['gtserver'] == 1) {   //服务器正常
        $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $data);
        if ($result) {
            return $user;
        } else{
            return  new WP_Error('broke', __("验证未通过")); 
        }
    }else{  //服务器宕机,走failback模式
        if ($GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode'])) {
            return $user;
        }else{
            return  new WP_Error('broke', __("验证未通过")); 
        }
    }
}
add_filter('wp_authenticate_user','add_captcha_API2',100,1);
?>
