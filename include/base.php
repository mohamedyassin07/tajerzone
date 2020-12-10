<?php
function get_json($file){
	$content = file_get_contents("jsons/$file.json");
	return(json_decode($content, true));
};
function mk_json($fields,$json_file){
	$fp = fopen("jsons/$json_file.json", 'w');
	fwrite($fp, json_encode($fields));
	fclose($fp);
};
function json_response($response){
	header('Content-Type: application/json');
	echo  json_encode($response);
}
function wp_content($id =  0){
	if(is_object($id) && isset($id->ID)){
		$id =  $id->ID; 
	}
	if($id == 0){
		$id = get_the_ID();
	}
	$content_post = get_post($id);
	$content = $content_post->post_content;
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	$content =  $content != '' ?  $content : 'لم يتم اضافه محتوي' ; 
	return $content;
}
function get_post_by_meta($meta,$value,$retun){
	$args = array(
		'post_type'     => 'any',
		'meta_key'      => $meta,
		'meta_value'    => $value,
	);
	$posts = get_posts($args);
	if(count($posts) >  0){
		if(isset($posts[0]->$value)){
			return $posts[0]->$value;
		}else {
			return $posts[0];
		}
	}else {
		return null;
	}
}
function round_up_to_correct_num($number, $precision = 0)
{
    $fig = pow(10, $precision);
    return (ceil($number * $fig) / $fig);
}
function view($view,$data= array()){
	foreach ($data as $key => $value) {
		$$key =  $value;
	}
	include( views . $view . '.php' );
}