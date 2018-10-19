<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Article extends Base_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('search'));
    }
    
    /**
     * 文章feed列表
     */
    public function feed() {
        $post = $this->input->post();
        $condition = array();
        $condition['categoryId'] = 1;
        $res = $this->search->getListFromSolor($condition);
        $this->_json['data'] = $res;
        echo util::toJson($this->_json);
    }

}
