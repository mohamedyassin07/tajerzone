<?php
    add_action('init' , 'start_session' , 1);
    
    function start_session()
    {
        if(!session_id())
        {
            session_start();
        }
    }
    
    function awb_admin_message_clear()
    {
        unset($_SESSION['awb_admin_success_message']);
        unset($_SESSION['awb_admin_error_message']);
    }
    
    add_action('wp_logout' , 'end_session');
    add_action('wp_login' , 'end_session');
    add_action('end_session_action' , 'end_session');
    
    function end_session()
    {
        session_destroy();
    }