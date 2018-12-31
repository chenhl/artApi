<?php
return array (
  1 => 
  array (
    'siteid' => '1',
    'name' => '默认站点',
    'dirname' => '',
    'domain' => 'http://www.te.com/',
    'site_title' => 'PHPCMS演示站',
    'keywords' => 'PHPCMS演示站',
    'description' => 'PHPCMS演示站',
    'release_point' => '',
    'default_style' => 'default',
    'template' => 'default',
    'setting' => 'array (
  \'upload_maxsize\' => \'2048\',
  \'upload_allowext\' => \'jpg|jpeg|gif|bmp|png|doc|docx|xls|xlsx|ppt|pptx|pdf|txt|rar|zip|swf\',
  \'watermark_enable\' => \'1\',
  \'watermark_minwidth\' => \'300\',
  \'watermark_minheight\' => \'300\',
  \'watermark_img\' => \'/statics/images/water/mark.png\',
  \'watermark_pct\' => \'85\',
  \'watermark_quality\' => \'80\',
  \'watermark_pos\' => \'9\',
)',
    'uuid' => 'f76b51b8-a116-11e8-af7c-fcaa14276d23',
    'url' => 'http://www.te.com/',
  ),
);
?>
<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Site_model
 *
 * @author Administrator
 */
class Site_model extends Base_model {

    public function __construct() {
        parent::__construct();
        $this->conDB();
    }

    /**
     * 文章列表 cron etl 使用
     * @param type $condition
     * @return type
     */
    public function get_site_conf($condition = array()) {
        
        $param = array();
        $where = ' s.siteid=:siteid';
        $param[':siteid'] = 1;
        $fields_n = 's.name,s.domain,s.site_title,s.keywords,s.description';
        $query = 'select ' . $fields_n
                . ' from v9_site as s'
                . ' where ' . $where;
        $db = $this->db->conn_id->prepare($query);
        $db->execute($param);
        $return = $db->fetch(PDO::FETCH_ASSOC);
        
        $return['icp']='';//备案号
        $return['report']=''; //违法和不良信息举报
        $return['company_name']='';//公司名称
        $return['license'] = '';//网络文化经营许可证
        $return['self_discipline'] = '';//跟帖评论自律管理承诺书
        return $return;
    }

    public function getDetail($condition) {
        $where = ' c.status=99 ';
        $param = array();
        if (!empty($condition['aid'])) {
            $where .= ' and c.aid=:aid';
            $param[':aid'] = $condition['aid'];
        }
        $query = 'select c.id,c.aid,c.uid,c.uname,c.userpic,c.collect_num,c.like_num,c.comment_num,c.status,c.catid,c.title,c.thumb,c.thumbs,c.keywords,c.tags,c.description,c.create_time,c.update_time,'
                . 'd.content,d.content_search,'
                . 'm.nickname,m.username,m.userid,m.userpic as m_userpic '
                . ' from v9_news as n'
                . ' left join v9_news_data as d on c.id=d.id'
                . ' left join v9_member as m on c.uname=m.nickname'
                . ' where ' . $where;
        $db = $this->db->conn_id->prepare($query);
        $db->execute($param);
        $return = $db->fetch(PDO::FETCH_ASSOC);
        if (!empty($return['tags'])) {
            $return['tags'] = json_decode($return['tags'], TRUE);
        }
        return $return;
//        return $this->mockDetail();
    }

    private function mockDetail() {

        $data = '{
  "aid": 6093075,
  "ud": 7669697,
  "uname": "authorName",
  "upic": "//5b0988e595225.cdc.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/43feefdab91d46f2802c10d1f6102e71.jpeg",
  "title": "刚刚！云南省级机构改革首批6部门挂牌成立！",
  "mobile_title": "刚刚！云南省级机构改革首批6部门挂牌成立！",
  "tags": [
    {
      "id": 19031,
      "name": "艺术家"
    }
  ],
  "cate_id": "111",
  "cate_name": "news",
  "content": "刚刚！云南省级机构改革首批6部门挂牌成立！刚刚！云南省级机构改革首批6部门挂牌成立！刚刚！云南省级机构改革首批6部门挂牌成立！刚刚！云南省级机构改革首批6部门挂牌成立！刚刚！云南省级机构改革首批6部门挂牌成立！",
  "create_time": "2018-10-24 10:11:13"
}';
        $return = json_decode($data, TRUE);
        return $return;
//        return array(json_decode($data, TRUE));
    }

}
