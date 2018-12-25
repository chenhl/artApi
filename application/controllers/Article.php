<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Article extends Base_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('search_model', 'collection_model'));
    }

    /**
     * 文章feed列表
     */
    public function feed() {
        $post = $this->input->get();

        if (!$this->chkSign($post)) {
            $this->_json = array('code' => 500, 'msg' => 'fail', 'data' => array());
            util::toJson($this->_json);
        }

        $condition = array();
        $condition['q'] = isset($post['q']) ? $post['q'] : '';
        $condition['uid'] = isset($post['uid']) ? $post['uid'] : '';
        $condition['aid'] = isset($post['aid']) ? $post['aid'] : '';
        $condition['aids'] = isset($post['aids']) ? $post['aids'] : '';
        $condition['cate_id'] = isset($post['cate_id']) ? $post['cate_id'] : 0;
//        print_r($condition);
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
        $this->load->model(array('article_model'));
        $condition = array();
        $condition['aid'] = intval($post['aid']);
        $res = $this->article_model->getDetail($condition);
        if ($res) {
            $res['is_collected'] = 0;
            if (isset($post['user_id'])) {//登录用户
                $is_collected = $this->collection_model->isCollected($post['user_id'], $condition['aid']);
                if ($is_collected) {
                    $res['is_collected'] = 1;
                }
            }
        }

        $this->_json['data'] = $res;
        util::toJson($this->_json);
    }

}
