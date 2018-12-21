<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Web
 *
 * @author Administrator
 */
class Site extends Base_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('site_model'));
    }

    public function getConf() {
        $post = $this->input->post();
        if (!$this->chkSign($post)) {
            $this->_json = array('code' => 500, 'msg' => 'fail', 'data' => array());
            util::toJson($this->_json);
        }

        $condition = array();
        $res = $this->site_model->get_site_conf($condition);
        $this->_json['data'] = $res;
        echo util::toJson($this->_json);
    }

}
