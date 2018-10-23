<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Category extends Base_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('category_model'));
    }

    /**
     * 分类列表
     */
    public function getList() {
        $post = $this->input->post();
        $condition = array();
        $res = $this->category_model->getList($condition);
        $this->_json['data'] = $res;
        echo util::toJson($this->_json);
    }

}
