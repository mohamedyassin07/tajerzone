<?php
    function option_val($option_name , $new_value = false){
        if($new_value){
            if ( get_option( $option_name ) !== false ) {
                update_option( $option_name, $new_value );
            } else {
                $deprecated = null;
                $autoload = 'no';
                add_option( $option_name, $new_value, $deprecated, $autoload );
            }
        }else{
            $value = get_option($option_name);
            if ( is_array($value) && count($value) == 1 ) {
                return $value[0];
            }else{
                return $value;
            }
        }
    }