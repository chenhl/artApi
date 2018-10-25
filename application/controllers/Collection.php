<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Collection extends Base_Controller {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 添加收藏
     */
    public function add() {
        $post = $this->input->post();
        if (!$this->chkSign($post)) {
            $this->_json = array('code' => 500, 'msg' => 'fail', 'data' => array());
            util::toJson($this->_json);
        }
        $this->load->model(array('collection_model'));
        $data = array();
        $data['aid'] = $post['aid'];
        $data['uid'] = $post['uid'];
        if ($this->collection_model->add($data)) {
            $this->_json['data'] = TRUE;
        } else {
            $this->_json['data'] = FALSE;
        }
        echo util::toJson($this->_json);
    }

    /**
     * 用户文章列表
     */
    public function getList() {
        $post = $this->input->post();
        if (!$this->chkSign($post)) {
            $this->_json = array('code' => 500, 'msg' => 'fail', 'data' => array());
            util::toJson($this->_json);
        }
        $this->load->model(array('search_model'));
        $condition = array();
        $condition['uid'] = $post['uid'];
        $page = isset($post['page']) ? $post['page'] : 1;
        $pageSize = isset($post['pageSize']) ? $post['pageSize'] : 20;
        $res = $this->search_model->getListFromSolor($condition, $page, $pageSize);
        $this->_json['data'] = $res;
        util::toJson($this->_json);
    }

}
