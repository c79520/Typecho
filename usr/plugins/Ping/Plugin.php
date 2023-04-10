<?php
/**
 * Typecho Ping插件,文章更新时自动通知搜索引擎，对SEO有极大好处
 * 
 * @package Typecho ping 
 * @author 圆梦理想
 * @version 1.0.6
 * @link http://ee19.com
 */
class ping_Plugin implements Typecho_Plugin_Interface
{
	/**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
	public static function activate()
	{
		Typecho_Plugin::factory('Widget_Contents_Post_Edit')->write = array('ping_Plugin', 'render');
 
	}

	/**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
	public static function deactivate(){}

	/**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
	public static function config(Typecho_Widget_Helper_Form $form)
	{
 
	}

	/**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
	public static function personalConfig(Typecho_Widget_Helper_Form $form){}
	
	/**
     * 插件实现方法
     * 
     * @access public
     * @return void
     */
 

/**
 * Send a pingback.
 *
 * @since 1.2.0
 * @uses $wp_version
 * @uses IXR_Client
 * @param string $new_post_url 新文章url.
 * @param string $server Host of blog to connect to.
 * @param string $path Path to send the ping.
 */
public static function ping($new_post_url,$blogname,$home,$rss_url,$server = '', $path = '') {

//========================================================
//ping服务器地址，可以在这里添加或删除，不要太多，太多会影响发表文章速度。
 	$services = "
http://ping.baidu.com/ping/RPC2
http://blogsearch.google.com/ping/RPC2
http://blog.youdao.com/ping/RPC2"
;
//========================================================
	include_once('class-IXR.php');

	$services = explode("\n", $services);
	foreach ( (array) $services as $server ) {
		$server = trim($server);
		if ( '' != $server )
			//echo $service;
 {//开始ping

 	// using a timeout of 3 seconds should be enough to cover slow servers
	$client = new IXR_Client($server, ((!strlen(trim($path)) || ('/' == $path)) ? false : $path));
	$client->timeout = 1;
	$client->useragent .= ' -- Typecho/';

	// when set to true, this outputs debug messages by itself，调试模式开关1。
	$client->debug = false;
	if ( !$client->query('weblogUpdates.extendedPing', $blogname, $home, $new_post_url,$rss_url ) ) 
	// then try a normal ping
		$client->query('weblogUpdates.ping', $blogname, $home);
 //结束ping
 } 
 
	}//结束for循环	

	//return $contents;
}

//
 
	/**
     * 插件实现方法
     * 
     * @access public
     * @return void
     */
	public static function render($contents, $inst)
	{
	//ping通知搜索引擎

	Typecho_Widget::widget('Widget_Contents_Post_Edit')->to($post);	
	$new_post_url = $post->permalink;//新文章url
	$options = Typecho_Widget::widget('Widget_Options');	
	$home = $options->siteUrl;//博客地址
	$blogname = $options->title ;	//博客名字
	$rss_url = $options->feedUrl;//RSS地址
	//一切OK 开始执行

	self::ping($new_post_url,$blogname,$home,$rss_url);
	//soso通知结果，采用网页提交的方式
	echo <<< html
<iframe src="http://blog.soso.com/qz.q/default/notice/ping?pingurl=$home&ty=ping" width="750" height="180" frameborder="no" border="0" marginwidth="0" marginheight="0" scrolling="no" allowtransparency="yes"></iframe>
html;
	//调试模式开关2 调试模式下 需要把开关1 设置true 把下面这句注释掉
	return $contents;
}	
 
}
