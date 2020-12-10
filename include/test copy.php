<?php 
function send_uae_sms_msg($mobile,$lang='en'){
    if(!is_numeric($mobile)){
        return;
    }
    $code =  '987d7x7s9';
    $lang = $lang == 'en' ? 1: 2;

    if($lang == 1){ // English
        $msg = "This is your confirmation code for AbdoAdz $code";
    }elseif ($lang == 2) { //  Arabic
        $msg = "كود التفعيل لموقع عبدو ادز  $code";
    }

    $user = 'fandxb1';
    $pass = 'fandxb1547';


    $balance_url = add_query_arg( array(
        'username' => $user,
        'password' => $pass,
        'language' => $lang,
        'message'  => $msg,
        'mobile'   => $mobile,
        'sender'   =>'TEST'
    ), 'https://api-server3.com/api/send.aspx' );
    //return $balance_url ;
    //return esc_url($balance_url);
    //return wp_remote_get($balance_url)['body'];
}
$number =  '00971050703504' ;
send_uae_sms_msg('00971050703504');