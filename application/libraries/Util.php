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
        $pwd['password'] = md5(md5(trim($password)) . $pwd['encrypt'].$password_key);
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

}
