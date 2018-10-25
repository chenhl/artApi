<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Channel extends Base_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('channel_model'));
    }

    /**
     * 频道列表
     */
    public function index() {
        $post = $this->input->post();
        if (!$this->chkSign($post)) {
            $this->_json = array('code' => 500, 'msg' => 'fail', 'data' => array());
            util::toJson($this->_json);
        }
        
        $condition = array();
        $res = $this->channel_model->getList($condition);
        $this->_json['data'] = $res;
        echo util::toJson($this->_json);
    }

}
