<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of Base_model
 *
 * @author Administrator
 */
class Base_model extends CI_Model {

    protected $solr_conf = array();
    protected $solr_url = '';

    public function __construct() {
        parent::__construct();
    }

    protected function conDB($base = 'default', $return = FALSE) {
        $this->load->database($base, $return);
        //不使用CI框架自带的连接方式时，设置编码格式
        $this->db->query("SET NAMES 'UTF8'");
    }

    protected function conSOLR() {
        $this->load->config('solr');
        $this->solr_conf = $this->config->item('solr_conf');
        $this->solr_url = $this->solr_conf['url'];
    }

    /**
     * PDO 开启事务
     */
    public function begin_transaction() {
        // 关闭 PDO 的自动提交
//        $this->db->conn_id->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
        // 开启异常处理
        $this->db->conn_id->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // 开启一个事务
        $this->db->conn_id->beginTransaction();
    }

    /**
     * PDO 事务提交
     */
    public function begin_commit() {
        $this->db->conn_id->commit();
    }

    /**
     * PDO 事务回滚
     */
    public function begin_back() {
        $this->db->conn_id->rollBack();
    }

    /**
     * 通用方法 在不方便的时候手动执行sql查询，返回多条-数组形式
     * @param  [type] $sql [description]
     * @return [type]      [description]
     */
    public function query($sql) {
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function query_update($sql) {
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    /**
     * 通用方法 取一列
     * @param  [type] $sql [description]
     * @return [type]      [description]
     */
    public function query_column($sql, $filed = "") {
        $query = $this->db->query($sql)->result_array();
        if (empty($query)) {
            return array();
        }
        return array_column($query, $filed);
    }

    /**
     * 通用方法 在不方便的时候手动执行sql查询，返回单条-数组形式
     * @param  [type] $sql [description]
     * @param  [str]  $field 可以返回指定的字段
     * @return [type]      [description]
     */
    public function query_row($sql) {
        $query = $this->db->query($sql);
        return $query->row_array();
    }

}
