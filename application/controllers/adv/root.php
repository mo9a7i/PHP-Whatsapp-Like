<?php
class Root extends CI_Controller{

	var $file;
	var $path;
	
	function Root()
	{
		parent::__construct();
		$this->path = "databases" . DIRECTORY_SEPARATOR;
		$this->file =  $this->path . "database.sql";
	}
	
	function index()
	{
		echo "You are not allowed to view this page!";
	}
	
	function current_millis() 
	{
		list($usec, $sec) = explode(" ", microtime());
		echo round(((float)$usec + (float)$sec) * 1000) - 86400000;
	}
	
	function myip()
	{
		echo $_SERVER['REMOTE_ADDR'];
	}
	
	function test_send_mail()
	{
		$this->mo9->send_email("DoIt" , "Mohannad.otaibi@gmail.com" , "Mohannad.otaibi@gmail.com", "Testy", "ياسلام عليك");
	}
	
	function dump()
	{
		// Load the DB utility class
		$this->load->dbutil();
		
		// Backup your entire database and assign it to a variable
		$prefs = array('format'=> 'txt','add_drop'=> TRUE,'add_insert'=> TRUE,'newline'=> "\n");
		$backup =& $this->dbutil->backup($prefs); 
		
		// Load the file helper and write the file to your server
		write_file($this->file, $backup);
		echo "dumped successfully to ".$this->file;
	}

}