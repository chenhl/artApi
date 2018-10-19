<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Category extends Base_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('search'));
    }

    /**
     * 分类列表
     */
    public function getList() {
        $post = $this->input->post();
        $condition = array();
        $condition['categoryId'] = 1;
        $res = $this->search->getListFromSolor($condition);
        $this->_json['data'] = $res;
        echo util::toJson($this->_json);
    }

}
