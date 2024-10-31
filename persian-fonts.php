<?php
/*
Plugin Name: Persian Fonts
Plugin URI: http://codev.ir/pf.html
Description: فونت‌های وب‌سایت خود را به سادگی چند کلیک تغییر دهید !
Author: Danial Hatami
Author URI: http://codev.ir
Version: 1.6
*/

/*
Copyright 2013 Danial Hatami (email : Great.emperor94@gmail.com)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

/* Special Thanks to :
- F. Zangeneh
*/  

// thanks : Chris Jean - uploader
require_once ( dirname(__FILE__) .'/uploader.php');  

add_filter( 'mce_buttons_2', 'tuts_mce_editor_buttons' );  
  
function tuts_mce_editor_buttons( $buttons ) {  
    array_unshift( $buttons, 'styleselect' );  
    return $buttons;  
}  
   
add_filter( 'tiny_mce_before_init', 'tuts_mce_before_init' );  
  
function tuts_mce_before_init( $settings ) {  
  
    $style_formats = array(   
        array(  
            'title' => 'فونت Tahoma',  
            'inline' => 'span',  
            'styles' => array(  
                'font-family' => 'tahoma',  
 
   		)
)
    );  
  
    $settings['style_formats'] = json_encode( $style_formats );  
  
    return $settings;  
  
}  

new persianfonts;
	
class persianfonts {

	function persianfonts()
	{
		$this->__construct();		
	} 

	function __construct()
	{	

		add_action( 'init', array( &$this, 'init' ) );		
		

		add_action('admin_menu', array( &$this, 'persianfonts_create_menu') );		

	
		if (get_option('persianfonts-load-css-in-tinymce' ))
			add_filter( 'mce_css', array( &$this, 'persianfonts_mce_css') );
		
	
		if (get_option('persianfonts-load-css-style-in-tinymce' ))
			add_filter( 'tiny_mce_before_init', array( &$this, 'persianfonts_tinymce_style_format') );		

		register_activation_hook(__FILE__, array( &$this, 'prefix_on_activate') );									
		register_deactivation_hook(__FILE__, array( &$this, 'prefix_on_deactivate') );									
	} 

	function prefix_on_activate() {
    update_option('persianfonts-load-in-admin', true);
    update_option('persianfonts-load-css-in-tinymce', true);
    update_option('persianfonts-gen-css-class', true);
    update_option('persianfonts-load-css-style-in-tinymce', true);		    
    update_option('persianfonts_font_list_count', 0 );
	}
		

		

	function persianfonts_tinymce_style_format( $initArray )
	{	
			if (!empty($initArray['style_formats']))
				$style_formats = json_decode( $initArray['style_formats'],true );
			
			if (count($old_style_formats) > 0)
				$style_formats[] = array('title' => 'فونت');
			
			$fonts = get_option('persianfonts_font_list');			
			foreach ($fonts as $font)
			{
					$style_formats[] = array('title' => 'فونت '.$font, 'selector' => 'p,h1,h2,h3,h4,h5,h6', 'classes' => $font);
			}								 
						     	   	   	
    	$initArray['style_formats'] = json_encode( $style_formats );
				
			return $initArray;
	}	

	function get_familyname($file)
	{
			$contents = file_get_contents($file);
			$pattern = "/font-family.*?\'(.*?)\';/is";
			if(preg_match_all($pattern, $contents, $matches)){
			  return $matches[1][0];
			}
			else{
				return false;
			}	
	}
	
	function init()
	{
		
		$fontlist = glob(plugin_dir_path(__FILE__).'fonts/*', GLOB_ONLYDIR);
		$fonts = array();
		foreach ($fontlist as $font_path)
		{					
				if (file_exists($font_path.'/stylesheet.css'))
				{
						$fontname = basename($font_path);        				
    				$fonts[] = $fontname;
				}
		}												
		
		if ( get_option('persianfonts_font_list_count') != count($fonts))
			$update_css = true;
		if (get_option('persianfonts-gen-css-class'))
			if ( !file_exists(plugin_dir_path( __FILE__ ).'gen.css') )
				$update_css = true;
		if ( !file_exists(plugin_dir_path( __FILE__ ).'def.css') )
			file_put_contents ( plugin_dir_path( __FILE__ ).'def.css' , get_option('persianfonts-css') );
		update_option( 'persianfonts_font_list_count', count($fonts) );
		update_option( 'persianfonts_font_list', $fonts );										
	
	
		if ( !is_admin() || get_option('persianfonts-load-in-admin') ) 
		{
			global $pagenow;
			if ($pagenow!='wp-login.php') 
			{
				
				if (get_option('persianfonts-gen-css-class') && $update_css)
				{		
						$file = plugin_dir_path( __FILE__ ).'gen.css';
						file_put_contents($file, '/* font face gen file */');
				}
				
				$fonts = get_option('persianfonts_font_list');
				foreach ($fonts as $font)
				{
						$cssfile_url = plugin_dir_url(__FILE__).'fonts/'.$font.'/stylesheet.css';
						wp_register_style( 'persian-fonts-'.$font, $cssfile_url);
    				wp_enqueue_style( 'persian-fonts-'.$font );						        				
    
    				if (get_option('persianfonts-gen-css-class') && $update_css)
						{		
    						$cssfile_dir = plugin_dir_path(__FILE__).'fonts/'.$font.'/stylesheet.css';
    						$familyname = $this->get_familyname($cssfile_dir);
    						file_put_contents($file, '.'.$font.' { font-family: '.$familyname.'; }', FILE_APPEND);					
    				}
				}
				
				if (get_option('persianfonts-gen-css-class'))
				{		
						wp_register_style( 'persian-fonts-gen', plugin_dir_url(__FILE__).'gen.css');
 						wp_enqueue_style( 'persian-fonts-gen' );						        				
 				}
 				
 				wp_register_style( 'persian-fonts', plugin_dir_url(__FILE__).'def.css');
 				wp_enqueue_style( 'persian-fonts' );						        				
			}
		}
	}


	function persianfonts_mce_css( $mce_css ) {
	  if ( !empty( $mce_css ) )
	    $mce_css .= ',';
	    
	    $mce_css .= plugins_url( 'def.css', __FILE__ );
	    
			if (get_option('persianfonts-gen-css-class'))
			{		
			    $mce_css .= ','.plugins_url( 'gen.css', __FILE__ );
			}
						
			$fonts = get_option('persianfonts_font_list');
			foreach ($fonts as $font)
			{
					$mce_css .= ",".plugins_url('fonts/'.$font.'/stylesheet.css',__FILE__);	
			}
						
	    return $mce_css;
	}	

	function persianfonts_create_menu() 
	{	
	add_menu_page( 'برگه تنظیمات افزونه', 'فونت پارسی', 8, __FILE__, array( &$this, 'persianfonts_settings_page'),plugins_url('persian-fonts/logo.png') );

		add_action( 'admin_init', array( &$this, 'register_persianfonts_settings') );
	}
	
	
	function register_persianfonts_settings() {
		register_setting( 'persianfonts-settings-group', 'persianfonts-css', array( &$this, 'save_persianfonts_css') );
	}
		
	function save_persianfonts_css( $options )
	{
	    print_r($options);
	    file_put_contents ( plugin_dir_path( __FILE__ ).'def.css' , $options );
	    return $options;
	}

	function persianfonts_settings_page() {
	?>
			
			<div class="wrap">
<div id="icon-themes" class="icon32"></div><h2>فونت پارسی</h2><br/><br/>
			
			<form method="post" action="options.php">
			        
<h3>برای بارگذاری فونت‌ها در هر بخش کافیست <code>font-family: 'FONT NAME';</code> را در فیلد زیر به کلاس/تگ سی‌اس‌اس خود بیافزاید ! مثال </h3>
<p><code>.main-navigation li <br>
{  <br>
   font-family: 'yekan';<br>
}</code></p>
<p>فونت‌های در دسترس : hamid, yekan, homa, titr, koodak</p>
<p>برای بارگذاری فونت کافیست نام فونت را به جای FONT NAME قرار دهید !</p>
			        	
			    <?php settings_fields('persianfonts-settings-group'); ?>
				<?php checked( get_option('persianfonts-load-in-admin'), 0 ); ?>
				<p>
			        افزودن تگ‌های سی‌اس‌اس بیش‌تر :
			        </p>
					<textarea rows=20 cols=150 dir="ltr" type="text" name="persianfonts-css" /><?php echo get_option('persianfonts-css'); ?></textarea>
			        
			    
			    <?php submit_button(); ?>
			
			</form>
			</div>
	<?php }


	
} // Have Fun !

?>