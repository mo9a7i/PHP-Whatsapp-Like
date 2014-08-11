<?php
// application/helpers/mo9a7i_helper.php
/**
* F6sny.com 
* ===========
* code
* Email: 		mohannad.otaibi@gmail.com
* Website:		http://www.mohannadotaibi.com
* Date:			3/20/2012 *My 26th Birthday
*/

if (!function_exists('print_header'))
{
	function print_title($title)
	{
		if(!isset($title))
		{
			$title = '';
		}
		$CI =& get_instance();
		echo ( empty($title)?  '':$title.' | ' ); 
	}
}

if (!function_exists('print_description'))
{
	function print_description($description)
	{
		if(!isset($description))
		{
			$description = '';
		}
		$CI =& get_instance();
		echo ( empty($description)?  "" : $description ) ; 
	}
}

if (!function_exists('print_keywords'))
{
	function print_keywords($keywords)
	{
		if(!isset($keywords))
		{
			$keywords = '';
		}
		$CI =& get_instance();
		echo (empty($keywords))? "" : implode(",",$keywords) ;
	}
}

//Just an asset path producer, this is changable in the configs
if (!function_exists('asset_url'))
{   
    function asset_url()
    {
        $CI =& get_instance();

        // return the asset_url
        return base_url() . $CI->config->item('asset_path');
    }	
}

//Upload folder, changable in the configs.
if (!function_exists('uploads_url'))
{  
	function uploads_url()
	{
		$CI =& get_instance();

		// return the uploads_url
		return base_url() . $CI->config->item('uploads_path');
	}
}
