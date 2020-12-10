<?php
function shipping_method_panel_link($id){
    $id=  explode('_',$id)[1];
    return admin_url('admin.php?page=pods-settings-'.$id);
}