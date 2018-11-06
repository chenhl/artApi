<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of util
 *
 * @author Administrator
 */
class Util {

    public static function toJson($data = array()) {
        header('Content-type: application/json');
        echo json_encode($data);
        exit();
    }

    public static function _json_encode($data, $option = null) {
//        return htmlspecialchars(urlencode(json_encode($data, $option)));
        if (!empty($option)) {
            json_encode($data, $option);
        } else {
            return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
    }

    public static function _json_decode($json_str, $assoc = true) {
        return json_decode($json_str, $assoc);
//        return json_decode(urldecode(htmlspecialchars_decode($json_str)), $assoc);
    }

    /**
     * 设置加密的密码
     * @param str $pwd 待加密的密码
     * @param str $key 密钥
     */
    public static function password($pwd) {
        $password_key = 'art';
        return md5($pwd . $password_key);
    }

    /**
     * 对用户的密码进行加密
     * @param $password
     * @param $encrypt //传入加密串，在修改密码时做认证
     * @return array/password
     */
    public static function setPassWord($password, $encrypt = '') {
        $pwd = array();
        $password_key = 'art';
        $pwd['encrypt'] = $encrypt ? $encrypt : self::create_randomstr();
        $pwd['password'] = md5(md5(trim($password)) . $pwd['encrypt'] . $password_key);
        return $encrypt ? $pwd['password'] : $pwd;
    }

    /**
     * 生成随机字符串
     * @param string $lenth 长度
     * @return string 字符串
     */
    public static function create_randomstr($lenth = 6) {
        return random($lenth, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
    }

    /** 将格林威治时间转换成客户端时间,生成客户端时间
     *
     * @param str $time 格林威治时间
     * @param str $timeZone 时差
     * return 明文
     */
    public static function genClientTime($timeZone = 0) {
        return time() + $timeZone * 3600;
    }

    public static function genClientDateTime($timeZone = 0) {
        return date('Y-m-d H:i:s', time() + $timeZone * 3600);
    }

    public static function genHttpTime($timeZone = 0) {
        return $_SERVER['REQUEST_TIME'] + $timeZone * 3600;
    }

    /**
     * $_SERVER['REQUEST_TIME'] 发起该请求时刻的时间戳。== baby.php里的time()
     * @param type $timeZone
     * @return type
     */
    public static function genHttpDateTime($timeZone = 0) {
        return date('Y-m-d H:i:s', ($_SERVER['REQUEST_TIME'] + $timeZone * 3600));
    }

    public static function getHttpUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    public static function getHttpAcceptLang() {
        return $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }

    public static function getHttpTimeZone() {
        $time_zone = intval($_COOKIE['timezone']);
        if ($time_zone >= -12 && $time_zone <= 12) {
            return $time_zone;
        } else {
            return 0;
        }
    }

    public static function formatDateTime($date_time, $time_zone = 0) {
        if (empty($date_time[0])) {
            return $date_time;
        }
        return date('Y-m-d H:i:s', strtotime($date_time) + $time_zone * 3600);
    }

    /**
     * 获取客户端IP地址
     * 在CDN下，$_SERVER ["REMOTE_ADDR"]所取的数据会有误
     * @return string 客户端的IP地址
     */
    public static function getIP() {
        if (!empty($_COOKIE['test_ip'])) {
            return $_COOKIE['test_ip'];
        }
        if (isset($_SERVER["HTTP_CDN_SRC_IP"])) {
            return $_SERVER["HTTP_CDN_SRC_IP"];
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match('/^[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}$/', $_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER["REMOTE_ADDR"];
    }

}
