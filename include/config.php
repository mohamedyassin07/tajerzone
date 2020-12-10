<?php
if( ! defined('production') ){
    $production =  get_site_url() != 'http://tajerzone' ?  true :  false;
    define('production' , $production );
}