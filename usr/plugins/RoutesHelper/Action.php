<?php

class RoutesHelper_Action extends Typecho_Widget implements Widget_Interface_Do
{
    private $default;   

    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);
        $this->default = Helper::options()->routingTable;
    }

    /**
     * 修改路由
     * 
     */
    public function edit()
    {
        $modified = false;
        if ($this->request->isPost()) {
            foreach ($this->default as $key => $value) {
                if ($this->request->__isSet($key) && $this->request->{$key}!=$this->default[$key]['url']) {
                    Helper::removeRoute($key);
                    Helper::addRoute($key, $this->request->{$key}, $this->default[$key]['widget'], $this->default[$key]['action']);
                    $modified = true;
                }
            }
        }
        if ($modified) {
        $this->widget('Widget_Notice')->set(_t("路由变更已经保存"), NULL, 'success');
        } else {
        $this->widget('Widget_Notice')->set(_t("路由未变更"), NULL, 'notice');
        }
    }

    /**
     * 绑定动作
     *
     * @access public
     * @return void
     */
    public function action(){
        $this->widget('Widget_User')->pass('administrator');
        $this->on($this->request->is('edit'))->edit();
        $this->response->goBack();
    }
}
?>
