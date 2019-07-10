<h2 align="center">WordPress GeeTest</h2>

<p align="center">
基于极验 3.0，在 WordPress 的登录和评论时加入极验验证。
</p>

## 使用

1. 下载代码包上传到插件目录
2. 编辑插件，修改`config.php` 的配置文件，填入你的[极验配置](https://gtaccount.geetest.com/sensebot/overview/)
3. 启用插件


## 注意事项

<details>
<summary>注意事项当然要点开看了，不点怎么注意，点击查看</summary>

 1. 插件没有设置页面，默认评论和登录时需要验证
 2. `layer`和`jquery`默认引用`CDN`外链，如果失效请手动修改去掉注释
 3. 极验验证码位置`css`样式不兼容的话需要自己调
 
</details>

## 自定义验证

只验证了正常`get`情况下的请求，所以机器人或恶意用户可以直接使用`post`请求跳过极验验证，所以需要自定义验证，可使用以下钩子增加验证

```php
// 增加登录时的验证
function add_geetest_login_val() {
    if (!empty($_POST)){
        // 自定义内容省略
    }
}
add_action('login_form_login','add_geetest_login_val');

// 增加提交评论时的验证
function add_geetest_comment_val($incoming_comment) {
		if (!empty($_POST)){
        // 自定义内容省略
    }
		// 评论需要返回数据
		return $incoming_comment;
}
add_filter('preprocess_comment', 'add_geetest_comment_val');
```
