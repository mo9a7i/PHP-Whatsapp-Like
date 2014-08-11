<?php
/**
* All Shared Functions will be located here
*/
class Mo9 extends CI_Model{
	
	#Application level variables should be set here.
	################################################
	function __construct()
    {
        parent::__construct();
        $CI = &get_instance();
		$this->run_cronjobs();
    }
	
	#Youtube Stuff
	function get_youtube_video_thumb($url)
	{
		preg_match('/[\\?\\&]v=([^\\?\\&]+)/',$url,$matches);
		$video_id = $matches[1];
		return 'http://img.youtube.com/vi/'.$video_id.'/1.jpg';
	}
	
	#General Use Functions
	##########################################################################################
	##########################################################################################

	#Text Manipulation stuff
	function sanitize_text($text,$new_line = TRUE,$he_says = TRUE)
	{
		if($new_line)
		{
			$text = preg_replace('/\s\s+/', ' ', $text);
		}
		$text = str_replace("ـ","",$text);
		$text = str_replace(","," ",$text);
		$text = str_replace("،"," ",$text);
		$text = str_replace(":"," ",$text);
		$text = str_replace("."," ",$text);
		$text = str_replace("="," ",$text);
		$text = str_replace(")"," ",$text);
		$text = str_replace("("," ",$text);
		$text = str_replace("\""," ",$text);
		$text = str_replace('\''," ",$text);
		if($he_says)
		{
			$text = str_replace("يقول لك","",$text);
		}
		if($new_line)
		{
			$text = nl2br($text);
			$text = $this->br2nl($text);
			$text = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $text);
		}
		$text = trim($text);
		return $text;
	}
	
	function br2nl($text)
	{
		return preg_replace('/<br(\s+)?\/?>/i', "\n", $text);
	}
	
	function strip_html_tags( $text )
	{
		$text = preg_replace(
			array(
			  // Remove invisible content
				'@<head[^>]*?>.*?</head>@siu',
				'@<style[^>]*?>.*?</style>@siu',
				'@<script[^>]*?.*?</script>@siu',
				'@<object[^>]*?.*?</object>@siu',
				'@<embed[^>]*?.*?</embed>@siu',
				'@<applet[^>]*?.*?</applet>@siu',
				'@<noframes[^>]*?.*?</noframes>@siu',
				'@<noscript[^>]*?.*?</noscript>@siu',
				'@<noembed[^>]*?.*?</noembed>@siu',
			  // Add line breaks before and after blocks
				'@</?((address)|(blockquote)|(center)|(del))@iu',
				'@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
				'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
				'@</?((table)|(th)|(td)|(caption))@iu',
				'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
				'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
				'@</?((frameset)|(frame)|(iframe))@iu',
			),
			array(
				' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
				"\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
				"\n\$0", "\n\$0",
			),
			$text );
		return strip_tags( $text );
	}
	
	function get_timestamp($date)
	{
		$a = '44';
		$timestamp = mktime(0, 0, 0, $a['tm_mon']+1, $a['tm_mday'], $a['tm_year']+1900);
		return $a;
	}

	function appendstr($string1,$string2){
		if((!empty($string2)))
		{
			if(empty($string1))
			{
				$string1 = $string2;
			}
			else{
				$string1 = $string1 . ',' . $string2;
			}
		}
		return $string1;
	}
	
	//Send a file to the browser
	function send_to_browser($name,$file) 
	{
		force_download( $name , read_file($file) );
	}

	function run_cronjobs($force = FALSE)
	{
	
	}
	
	function dump_database($location = 'databases/database.sql')
	{
		// Load the DB utility class
		$this->load->dbutil();
		
		// Backup your entire database and assign it to a variable
		$prefs = array('format'=> 'txt','add_drop'=> TRUE,'add_insert'=> TRUE,'newline'=> "\n");
		$backup =& $this->dbutil->backup($prefs); 
		
		// Load the file helper and write the file to your server
		write_file($location, $backup);
		return "dumped successfully to ".$location;
	}
	
	// _default method combines the options array with a set of defaults giving the values in the options array priority.
	function _default($defaults, $options)
	{
		return array_merge($defaults, $options);
	}
	
	// _required method returns false if the $data array does not contain all of the keys assigned by the $required array.
	function _required($required, $data)
	{
		foreach($required as $field) if(!isset($data[$field])) return false;
		return true;
	}
}
/* End of file mo9.php */
/* Location: ./application/models/mo9.php */