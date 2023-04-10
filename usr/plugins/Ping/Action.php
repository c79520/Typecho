<?php
/**
 * Ping_Action
 * 
 * author: Liudon
 */
class Ping_Action extends Widget_Contents_Post_Edit {

    /**
     * 发布文章
     *
     * @access public
     * @return void
     */
    public function writePost()
    {
        $contents = $this->request->from('password', 'allowComment',
            'allowPing', 'allowFeed', 'slug', 'category', 'tags', 'text', 'visibility');

        $contents['title'] = $this->request->get('title', _t('未命名文档'));
        $contents['created'] = $this->getCreated();

        if ($this->request->markdown && $this->options->markdown) {
            $contents['text'] = '<!--markdown-->' . $contents['text'];
        }

        $contents = $this->pluginHandle()->write($contents, $this);

        if ($this->request->is('do=publish')) {
            /** 重新发布已经存在的文章 */
            $contents['type'] = 'post';
            $this->publish($contents);

            /** 发送ping */
            $trackback = array_unique(preg_split("/(\r|\n|\r\n)/", trim($this->request->trackback)));
            $this->widget('Widget_Service')->sendPing($this->cid, $trackback);

            /** 设置提示信息 */
            $this->widget('Widget_Notice')->set('post' == $this->type ?
            _t('文章 "<a href="%s">%s</a>" 已经发布', $this->permalink, $this->title) :
            _t('文章 "%s" 等待审核', $this->title), 'success');

            /** 设置高亮 */
            $this->widget('Widget_Notice')->highlight($this->theId);

            /** 获取页面偏移 */
            $pageQuery = $this->getPageOffsetQuery($this->created);

            $servers = Helper::options()->plugin('Ping')->servers;

            if ($servers) {
                $servers = explode("\r\n", $servers);
                foreach ($servers as $server) {
                    $server = trim($server);
                    if (!$server) {
                        continue;
                    }
                    $validator = new Typecho_Validate();
                    if (!$validator->url($server)) {
                        continue;
                    }
                    try {
                        $client = new IXR_Client($server, false, 80, IXR_Client::DEFAULT_USERAGENT, 'weblogUpdates.');
                        $res = $client->extendedPing($this->options->title, $this->options->siteUrl, $this->permalink, $this->options->feedUrl);
                        unset($client);
                    } catch (Exception $e) {
                        continue;
                    }
                }
            }

            /** 页面跳转 */
            $this->response->redirect(Typecho_Common::url('manage-posts.php?' . $pageQuery, $this->options->adminUrl));
        } else {
            /** 保存文章 */
            $contents['type'] = 'post_draft';
            $this->save($contents);

            if ($this->request->isAjax()) {
                $created = new Typecho_Date($this->options->gmtTime);
                $this->response->throwJson(array(
                    'success'   =>  1,
                    'time'      =>  $created->format('H:i:s A'),
                    'cid'       =>  $this->cid
                ));
            } else {
                /** 设置提示信息 */
                $this->widget('Widget_Notice')->set(_t('草稿 "%s" 已经被保存', $this->title), 'success');

                /** 返回原页面 */
                $this->response->redirect(Typecho_Common::url('write-post.php?cid=' . $this->cid, $this->options->adminUrl));
            }
        }
    }
}