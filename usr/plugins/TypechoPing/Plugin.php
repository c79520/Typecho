<?php
/**
 * 自动更新服务，文章或页面更新时自动通知搜索引擎，有利于SEO。
 * 
 * @package Typecho Ping 
 * @author 蓝飞
 * @version 1.1.0
 * @link http://lanfei.sinaapp.com
 */
class TypechoPing_Plugin implements Typecho_Plugin_Interface
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
		Typecho_Plugin::factory('Widget_Contents_Post_Edit')->write = array('TypechoPing_Plugin', 'postRender');
		Typecho_Plugin::factory('Widget_Contents_Page_Edit')->write = array('TypechoPing_Plugin', 'PageRender');
        return _t('请配置此插件的RPC地址, 以使您的通告服务生效。');
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
        $services = new Typecho_Widget_Helper_Form_Element_Textarea('services', NULL, NULL, _t('RPC地址'), _t('当您更新一篇文章时，将向这些地址发出通告，多个通告服务地址请使用换行符隔开。'));
        $form->addInput($services);
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
	 * 发送Pingback
	 *
     * @access public
	 * @param string $blog_name 博客名称
	 * @param string $home 博客地址
	 * @param string $new_post_url 新文章地址
	 * @param string $rss_url RSS地址
     * @return void
	 */
	public static function ping($blog_name, $home, $url, $rss_url)
	{
		$services = Typecho_Widget::widget('Widget_Options')->plugin('TypechoPing')->services;
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<methodCall>
<methodName>weblogUpdates.extendedPing</methodName>
<params>
<param><value>' . $blog_name . '</value></param>
<param><value>' . $home . '</value></param>
<param><value>' . $url . '</value></param>
<param><value>' . $rss_url . '</value></param>
</params>
</methodCall>';
		$xml_baidu = '<?xml version="1.0" encoding="UTF-8"?>
<methodCall>
<methodName>weblogUpdates.extendedPing</methodName>
<params>
<param><value><string>' . $blog_name . '</string></value></param>
<param><value><string>' . $home . '</string></value></param>
<param><value><string>' . $url . '</string></value></param>
<param><value><string>' . $rss_url . '</string></value></param>
</params>
</methodCall>';
		$services = explode("\n", $services);
		foreach ( (array) $services as $server ) {
			$server = trim($server);
			if ($server != ''){
				$ch = curl_init();
				$headers = array(
					"Content-type: text/xml;charset=\"utf-8\"",
					"Accept: text/xml"
				);
				curl_setopt_array(
					$ch,
					array(
						CURLOPT_URL => $server,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_POST => true,
						CURLOPT_HTTPHEADER => $headers,
						CURLOPT_POSTFIELDS => $server == 'http://ping.baidu.com/ping/RPC2' ? $xml_baidu : $xml
					)
				);
				$content=curl_exec($ch);
				//echo $server . '：<br />';
				//if(curl_errno($ch)) echo curl_error($ch);
				//else echo $content . '<br />';
				curl_close($ch);
			}
		}
		//exit();
	}
 
	/**
     * 获取所需信息
     * 
     * @access public
     * @return void
     */
	public static function render($p)
	{
		$options = Typecho_Widget::widget('Widget_Options');	
		$blog_name = $options->title;
		$home = $options->siteUrl;
		$url = $p->permalink;
		$rss_url = $options->feedUrl;
		self::ping($blog_name, $home, $url, $rss_url);
	}

	/**
     * 文章实现方法
     * 
     * @access public
     * @return array
     */
	public static function postRender($contents, $inst)
	{
		if($inst->request->is('do=publish')){
			Typecho_Widget::widget('Widget_Contents_Post_Edit')->to($post);	
			self::render($post);
		}
		return $contents;
	}
 
	/**
     * 页面实现方法
     * 
     * @access public
     * @return array
     */
	public static function pageRender($contents, $inst)
	{
		if($inst->request->is('do=publish')){
			Typecho_Widget::widget('Widget_Contents_Page_Edit')->to($page);	
			self::render($page);
		}
		return $contents;
	}
}