<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Article extends Base_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('search_model'));
    }

    /**
     * 文章feed列表
     */
    public function feed() {

        $post = $this->input->get();
//        if (!$this->chkSign($post)) {
//            $this->_json = array('code' => 500, 'msg' => 'fail', 'data' => array());
//            util::toJson($this->_json);
//        }

        $condition = array();
        $condition['q'] = isset($post['q']) ? $post['q'] : '';
        $condition['categoryId'] = isset($post['categoryId']) ? $post['categoryId'] : 0;
        $page = isset($post['page']) ? $post['page'] : 1;
        $pageSize = isset($post['pageSize']) ? $post['pageSize'] : 20;
        $res = $this->search_model->getListFromSolor($condition, $page, $pageSize);
        
        $this->_json['data'] = $res;
        util::toJson($this->_json);
    }

    /**
     * 文章内容
     */
    public function detail() {

        $post = $this->input->post();
        if (!$this->chkSign($post)) {
            $this->_json = array('code' => 500, 'msg' => 'fail', 'data' => array());
            util::toJson($this->_json);
        }

        $condition = array();
        $condition['aid'] = $post['aid'];
        $res = $this->search_model->getDetail($condition);
        $this->_json['data'] = $res;
        util::toJson($this->_json);
    }

}
