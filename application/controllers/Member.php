<?php

defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 内部用户接口
 */
class Member extends Base_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('member_model'));
    }

    /**
     * 登录
     */
    public function login() {
        $post = $this->input->post();
        if (!$this->chkSign($post)) {
            $this->_json = array('code' => 500, 'msg' => 'fail', 'data' => array());
            util::toJson($this->_json);
        }
        
        $data = $post;
        
        $res = $this->member_model->doLogin($data);
        $this->_json['data'] = $res;
        echo util::toJson($this->_json);
    }
    
}
