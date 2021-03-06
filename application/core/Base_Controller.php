<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of Base_Controller
 *
 * @author Administrator
 */
class Base_Controller extends CI_Controller {

    protected $_json = array();
    protected $api_conf;

    public function __construct() {
        parent::__construct();
        if(is_cli()){
            
        }
        $this->load->library(array("util"));
        $this->_json = array('code' => 200, 'msg' => 'success', 'data' => array());
        $this->load->config('api');
        $this->api_conf = $this->config->item('api_conf');
    }

    /**
     * 生成签名
     * @param type $request
     * @return type
     */
    protected function genSign($request) {
        ksort($request);
        $sign = '';
        foreach ($request as $key => $value) {
            if ($value == '' || $key == 'api_sign') {
                continue;
            }
            $sign .= $key . $value;
        }
        $sign .= $this->api_conf['api_secret'];
        return hash("sha256", $sign);
    }

    /**
     * 验证签名
     * @param type $request
     * @return type
     */
    protected function chkSign($request) {
        return !empty($request['api_sign']) ? ($request['api_sign'] == $this->genSign($request) ? TRUE : FALSE) : FALSE;
    }

    protected function checkParam() {
        
    }

    /**
     * ajax公用返回信息
     * @param type $code
     * @param type $msg
     * @param type $data
     */
    protected function returnJson($code, $msg, $data = array()) {
        $return['code'] = $code;
        $return['msg'] = $msg;
        $return['data'] = $data;
        return json_encode($return);
    }

}
