<?php

/**
 * Created by PhpStorm.
 * User: brukrneti
 * Date: 10.12.2017.
 * Time: 11:16
 */
class MjestoSastanka extends Controller
{
    function __construct()
    {
        parent::__construct();
        $this->table = 'mjesto_sastanka';
    }


}