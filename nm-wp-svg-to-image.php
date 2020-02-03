<?php 
/**
 * Plugin Name: SVG to Image Service
 * Plugin URI: http://najeebmedia.com
 * Description: Convert SVG to image and return to client
 * Version: 1.0
 * Author: N-Media
 * Author URI: http://najeedmedia.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: nm-svgtoimage
 */
 
 define("NMSVG_PATH", untrailingslashit(plugin_dir_path( __FILE__ )) );
 define("NMSVG_URL", untrailingslashit(plugin_dir_url( __FILE__ )) );
 
 require_once NMSVG_PATH.'/inc/functions.php';
 require_once NMSVG_PATH.'/lib/svg-php/vendor/autoload.php';
// echo __DIR__.'/vendor/autoloader.php'; exit;

use SVG\SVG;
use SVG\Nodes\Shapes\SVGRect;
 
class NM_SVG_Service {
    
    function __construct() {
        
        // REST API
        add_action( 'rest_api_init', array($this, 'register_routes'));
        
        // Frontend
        add_shortcode( 'nm-svgphp', array($this , 'render_image') );
        
    }
    
    function register_routes(){
	
		register_rest_route( 'nmsvgphp/v1', '/start/', array(
		    'methods' => 'GET',
		    'callback' => array($this, 'generate_image_from_svg'),
		) );
	}
	
	function generate_image_from_svg() {
	    
	    $this->set_headers();
	    
        $all_meta  = isset($_GET['meta']) ? $_GET['meta'] : '';
        
	    
	    $text_value     = isset($_GET['text_value']) ? $_GET['text_value'] : '';
        $class_value    = isset($_GET['class_value']) ? $_GET['class_value'] : '';
        $template_name  = isset($_GET['template_name']) ? $_GET['template_name'] : '';
        
        if( ! $template_name ) wp_send_json_error(__("Template name not defined", "nm-svgtoimage" ));
        
        $this->renderSVG($text_value, $class_value, $template_name, $all_meta);
	}
	
	function render_image($attr) {
	    
	    wp_enqueue_script('nmsvgphp-js' , NMSVG_URL .'/js/nmsvgphp.js', array('jquery'), true);
	    // localizing invoice js file
		$args = array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'site_url'=>site_url() );
	    wp_localize_script('nmsvgphp-js', 'nmsvgphp_vars', $args);
        // wp_enqueue_style( 'nmsvgphp-css', NMSVG_URL.'/css/bootstrap.min.css');
        
        
		ob_start();
            nmsvgphp_load_template('svg-request.php');
    	$html = ob_get_contents(); 
		ob_end_clean();
		return $html;
	}
	
	function renderSVG($text_value, $class_value, $template_name, $all_meta = array() ) {
	    
	    
	    $svg_file = NMSVG_PATH."/assets/{$template_name}";
        if( ! file_exists($svg_file) ) wp_send_json_error(__("{$svg_file} Not Found", "nm-svgtoimage" ));
        
        $svg = SVG::fromFile($svg_file);
        
        $doc = $svg->getDocument();
        
        foreach ($all_meta as $key => $val) {
            
            $nodes = $doc->getElementsByClassName($key);
            
            if( $nodes ) {
                foreach($nodes as $node){
                    $node->setValue($val);
                }
            }
        }
        
        $filename = time().'-'.$template_name;
        $output_svg = NMSVG_PATH."/output/{$filename}";
        file_put_contents($output_svg, $svg->toXMLString());
        
        // header('Content-Type: image/svg');
        $output_url = NMSVG_URL."/output/{$filename}";
        $response = array('output_svg'=>$output_url, 'filename'=>$filename);
        wp_send_json($response);
	}
    
    function circleText(){
    
        // $file = NMSVG_PATH.'/assets/circle.text.svg';
        $font = isset($_GET['font']) ? $_GET['font'] : 'openGost';
        $font = new \SVG\Nodes\Structures\SVGFont($font, 'OpenGostTypeA-Regular.ttf');
        $text_value = isset($_GET['text_value']) ? $_GET['text_value'] : 'Hello World';
        $svg = SVG::fromFile($file);
        // circle.text
        // $text = $svg->getDocument()->getChild(1)->getChild(0)->getChild(0);
        // $text = $svg->getDocument()->getChild(1)->getChild(1);
        // $image->setAttribute('xlink:href', NMSVG_URL.'/assets/Mug-PNG-Image.png');
        // $text = $svg->getDocument()->getChild(2)->getChild(0);
        // nmsvgphp_pa($text);
        $class = 'dillard-class';
        $doc = $svg->getDocument();
        $node = $doc->getElementsByClassName($class);
        if($node > 0) {
            // $nodeFound = $node->getChild(0);
            $node[0]->setValue($text_value);
        }
        // nmsvgphp_pa($text);
        // $this->findNode($id, $svg->getDocument());
        
        $output_svg = NMSVG_PATH.'/output/svg-ready.svg';
        file_put_contents($output_svg, $svg->toXMLString());
        
        header('Content-Type: image/svg');
        echo NMSVG_URL.'/output/svg-ready.svg?nocache='.time();
        // $rasterImage = $svg->toRasterImage(200, 200);
        // header('Content-Type: image/png');
        // imagepng($rasterImage);
        // echo $rasterImage;
        
        exit;
    }
    
    
    public function set_headers(){
        
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400'); // cache for 1 day
        }
        
        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            
            exit(0);
        }
        
    }
    
}

$SVG = new NM_SVG_Service();

