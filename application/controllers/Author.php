<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 内部用户接口
 */
class Author extends Base_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('member_model'));
    }

    /**
     * authorlist 
     * 关注
     */
    public function getList() {
        $post = $this->input->post();
        if (!$this->chkSign($post)) {
            $this->_json = array('code' => 500, 'msg' => 'fail', 'data' => array());
            util::toJson($this->_json);
        }


        $condition = array();
        $condition['uid'] = intval($post['uid']);
        $page = isset($post['page']) ? $post['page'] : 1;
        $pageSize = isset($post['pageSize']) ? $post['pageSize'] : 20;
        $res = $this->member_model->getFollowList($condition, $page, $pageSize);
        $this->_json['data'] = $res;
        echo util::toJson($this->_json);
    }

    /**
     * 添加关注
     */
    public function add() {
        $post = $this->input->post();
        if (!$this->chkSign($post)) {
            $this->_json = array('code' => 500, 'msg' => 'fail', 'data' => array());
            util::toJson($this->_json);
        }
        $data = array();
        $data['fuid'] = $post['fuid'];
        $data['uid'] = $post['uid'];
        if ($this->member_model->addFollow($data)) {
            $this->_json['data'] = TRUE;
        } else {
            $this->_json['data'] = FALSE;
        }
        echo util::toJson($this->_json);
    }

    /**
     * 取消关注
     */
    public function cancel() {
        $post = $this->input->post();
        if (!$this->chkSign($post)) {
            $this->_json = array('code' => 500, 'msg' => 'fail', 'data' => array());
            util::toJson($this->_json);
        }

        $data = array();
        $data['fuid'] = $post['fuid'];
        $data['uid'] = $post['uid'];
        if ($this->member_model->cancelFollow($data)) {
            $this->_json['data'] = TRUE;
        } else {
            $this->_json['data'] = FALSE;
        }
        echo util::toJson($this->_json);
    }

}
