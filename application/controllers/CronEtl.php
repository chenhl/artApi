<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of etl
 *
 * @author chl
 */
class CronEtl extends Base_Controller {

    private $cate_info = array();
    private $area_info = array();
    private $exhibit_meta = array();
    private $gallery_meta = array();
    private $xml_dir = FCPATH . 'xml/';

    public function __construct() {
        parent::__construct();
        if (!is_cli()) {
            exit;
        }
        $this->load->model(array('article_model', 'category_model'));
    }

    private function _init() {
        ini_set('memory_limit', '500M');
        //初化数据
        $this->cate_info = $this->_getCate();
        $this->area_info = $this->_getArea();
        $this->exhibit_meta = $this->_getExhibitMeta();
        $this->gallery_meta = $this->_getGalleryMeta();
    }

    /**
     * 全量更新
     */
    public function full_import() {
//        $domain = 'www-test.babyonlinedress.cn';

        $this->_init();
        //文章
        $_data = $this->article_model->etl_article();
//        print_r($_data);
        //机构（）

        $data = $this->_parseData($_data, 'news');
        $this->_parse2addxml($data);
    }

    /**
     * 增量更新 (监听news表)
     * @param type $domain
     */
    public function delta_import($date_time) {
        $this->_init();
//        $date_time = date('Y-m-d H:i:s',$_date_time);
        echo "param time:" . $date_time . "\n";
        $date_time = date('Y-m-d H:i:s', strtotime($date_time) - 60);
        echo "sql time:" . $date_time . "\n";

        //增量
        $conditon = array('date_time' => $date_time);
        $_data = $this->article_model->etl_article($conditon);

        //解析生成xml
        $data = $this->_parseData($_data);
        $this->_parse2addxml($data, TRUE);
    }

    /**
     * 取所有的三级分类
     * @param type $domain
     * @return type
     */
    private function _getCate() {
        $data = $this->category_model->getList();
        $return = array();
        if ($data) {
            foreach ($data as $row) {
                $return[$row['catid']] = $row;
            }
        }
        return $return;
    }

    /**
     * 取所有的三级分类
     * @param type $domain
     * @return type
     */
    private function _getArea() {

        return array();
    }

    /**
     * 解析数据库来的数据到solr可用的字段
     * @param type $data
     */
    private function _parseData(&$data, $channel) {
        if (empty($data)) {
            return array();
        }

        $return = array();
        foreach ($data as $row) {
            $_temp = array();
            $_temp['id'] = $channel . '-' . $row['id'];
            $_temp['aid'] = $row['aid'];
            $_temp['status'] = $row['status'];

            $_temp['uid'] = !empty($row['uid']) ? $row['uid'] : 0;
            $_temp['uname'] = $row['uname'];
            $_temp['upic'] = $row['userpic'];

            $_temp['channel'] = $channel;
            $_temp['cate_id'] = $row['catid'];
            $_temp['cate_name'] = $this->cate_info[$row['catid']]['catname'];

            $_temp['collect_num'] = isset($row['collect_num']) ? $row['collect_num'] : 0;
            $_temp['comment_num'] = isset($row['comment_num']) ? $row['comment_num'] : 0;
            $_temp['like_num'] = isset($row['like_num']) ? $row['like_num'] : 0;

            $_temp['create_time'] = date("Y-m-d\TH:i:s\Z", strtotime($row['create_time']));
            $_temp['update_time'] = date("Y-m-d\TH:i:s\Z", strtotime($row['update_time']));

            $_temp['title'] = htmlspecialchars($row['title']);
            $_temp['keywords'] = htmlspecialchars($row['keywords']);
            $_temp['tags'] = isset($row['tags']) ? json_decode($row['tags'], TRUE) : array();
            $_temp['content'] = !empty($row['content_search']) ? $row['content_search'] : strip_tags($row['content']);

            $_temp['focuspic'] = isset($row['focuspic']) ? $row['focuspic'] : '';
            $_temp['image'] = isset($row['thumb']) ? $row['thumb'] : '';
            $_temp['images'] = isset($row['thumbs']) ? $row['thumbs'] : '';

            //
            $_temp['zhuban'] = isset($row['zhuban']) ? $row['zhuban'] : '';
            $_temp['xieban'] = isset($row['xieban']) ? $row['xieban'] : '';
            if (!empty($row['start_time'])) {
                $_temp['start_time'] = date("Y-m-d\TH:i:s\Z", strtotime($row['start_time']));
            } else {
                $_temp['start_time'] = '1970-01-01T08:00:00Z';
            }
            if (!empty($row['end_time'])) {
                $_temp['end_time'] = date("Y-m-d\TH:i:s\Z", strtotime($row['end_time']));
            } else {
                $_temp['end_time'] = '1970-01-01T08:00:00Z';
            }
            //attr
            if (!empty($row['spider_attr'])) {
                $spider_attr = json_encode($row['spider_attr'], TRUE);
            }

            $return[] = $_temp;
        }
        return $return;
    }

    private function _xmldatakey() {
        return array(
            'title',
            'keywords',
            'content'
        );
    }

    /**
     * 生成到xml格式
     * @param type $data
     */
    private function _parse2addxml(&$data, $delta = false) {
        $_xmldatakey = $this->_xmldatakey();
        if ($delta) {
            $file = $this->xml_dir . 'delta_import.xml';
        } else {
            $file = $this->xml_dir . 'full_import.xml';
        }
        $xml = "<add>\t\n";
        foreach ($data as $row) {
            $xml .= "<doc>\t\t\n";
            foreach ($row as $key => $value) {
                if (in_array($key, $_xmldatakey)) {
                    $xml .= "<field name=\"" . $key . "\"><![CDATA[" . $value . "]]></field>\t\n";
                } elseif (is_array($value)) {
                    foreach ($value as $_value) {
                        if (!$_value) {
                            continue;
                        }
                        $xml .= "<field name=\"" . $key . "\">" . $_value . "</field>\t\n";
                    }
                } else {
                    $xml .= "<field name=\"" . $key . "\">" . $value . "</field>\t\n";
                }
            }
            $xml .= "</doc>\t\n";
        }
        $xml .= "</add>\t\n";
        file_put_contents($file, $xml);
        echo $file . " finish\n";
    }

    private function _getExhibitMeta() {
        $meta = array(
            array('txt' => '展览名称：', 'code' => 'name'),
            array('txt' => '展览时间：', 'code' => 'times'),
            array('txt' => '开幕时间：', 'code' => 'open_time'),
            array('txt' => '展览机构：', 'code' => 'org'),
            array('txt' => '展览地址：', 'code' => 'address'),
            array('txt' => '展览备注：', 'code' => 'msg'),
            array('txt' => '展览城市：', 'code' => 'area'),
            array('txt' => '主办单位：', 'code' => 'org_main'),
            array('txt' => '承办单位：', 'code' => 'org_manager'),
            array('txt' => '策 展 人：', 'code' => 'plan'),
            array('txt' => '展览咨询：', 'code' => 'consultation'),
            array('txt' => '参展艺术家：', 'code' => 'artists'),
        );
        $return = array();
        foreach ($meta as $row) {
            $return[$row['txt']] = $row['code'];
        }
        return $return;
    }

    private function _getGalleryMeta() {
        $meta = array(
            array('txt' => '所在城市：', 'code' => 'area'),
            array('txt' => '主营项目：', 'code' => 'main_item'),
        );
        $return = array();
        foreach ($meta as $row) {
            $return[$row['txt']] = $row['code'];
        }
        return $return;
    }

}
