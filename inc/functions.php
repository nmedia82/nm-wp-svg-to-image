<?php
/**
 * Helper functions
 * 
 * */
 
function nmsvgphp_load_template( $template_name, $vars = null) {

    if( $vars != null && is_array($vars) ){
        extract( $vars );
    };

    $template_path =  NMSVG_PATH."/templates/{$template_name}";
    if( file_exists( $template_path ) ){
        require ( $template_path );
    } else {
        die( "Error while loading file {$template_path}" );
    }
}

function nmsvgphp_pa($arr){
    echo '<pre>'; print_r($arr); echo '</pre>';
}