<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Base_inte
 *
 * @author Administrator
 */
interface Base_interface {

    public function CheckParam($param);

    public function getCacheKey();

    public function ParseParam();

    public function getResult();
}
