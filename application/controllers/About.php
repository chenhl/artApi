<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class About extends Base_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('article_model'));
    }

    /**
     * 列表
     */
    public function cate() {
        $post = $this->input->post();
        if (!$this->chkSign($post)) {
            $this->_json = array('code' => 500, 'msg' => 'fail', 'data' => array());
            util::toJson($this->_json);
        }

        $condition = array();
        $res = $this->article_model->aboutCate($condition);
        $this->_json['data'] = $res;
        echo util::toJson($this->_json);
    }

    /**
     * 内容
     */
    public function detail() {
        $post = $this->input->post();
        if (!$this->chkSign($post)) {
            $this->_json = array('code' => 500, 'msg' => 'fail', 'data' => array());
            util::toJson($this->_json);
        }

        $condition = array();
        $condition['catid'] = $post['catid'];
        $res = $this->article_model->aboutArticle($condition);
        $this->_json['data'] = $res;
        echo util::toJson($this->_json);
    }

}
