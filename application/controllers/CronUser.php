<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 整理抓取的文章发布者
 *
 * @author Administrator
 */
class CronUser extends Base_Controller {

    public function __construct() {
        parent::__construct();
        if (!is_cli()) {
            exit;
        }
        $this->load->model(array('article_model', 'member_model'));
    }
    
    public function index() {
        
    }
}
