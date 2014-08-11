<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class MY_Router extends CI_Router {
 
	var $error_controller = 'error';
	var $error_method_404 = 'error_404';
 
    function My_Router()
    {
        parent::CI_Router();
    }
 
	// this is just the same method as in Router.php, with show_404() replaced by $this->error_404();
	function _validate_request($segments)
	{
		// Does the requested controller exist in the root folder?
		if (file_exists(APPPATH.'controllers/'.$segments[0].EXT))
		{
			return $segments;
		}
 
		// Is the controller in a sub-folder?
		if (is_dir(APPPATH.'controllers/'.$segments[0]))
		{		
			// Set the directory and remove it from the segment array
			$this->set_directory($segments[0]);
			$segments = array_slice($segments, 1);
 
			if (count($segments) > 0)
			{
				// Does the requested controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$segments[0].EXT))
				{
					return $this->error_404();
				}
			}
			else
			{
				$this->set_class($this->default_controller);
				$this->set_method('index');
 
				// Does the default controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$this->default_controller.EXT))
				{
					$this->directory = '';
					return array();
				}
			}
 
			return $segments;
		}
 
		// Can't find the requested controller...
		return $this->error_404();
	}
 
	function error_404()
	{
		$this->directory = "";
		$segments = array();
		$segments[] = $this->error_controller;
		$segments[] = $this->error_method_404;
		return $segments;
	}
 
	function fetch_class()
	{
		// if method doesn't exist in class, change
		// class to error and method to error_404
		$this->check_method();
 
		return $this->class;
	}
 
	function check_method()
	{
		$ignore_remap = true;
 
		$class = $this->class;
		if (class_exists($class))
		{	
			// methods for this class
			$class_methods = array_map('strtolower', get_class_methods($class));
 
			// ignore controllers using _remap()
			if($ignore_remap && in_array('_remap', $class_methods))
			{
				return;
			}
 
			if (! in_array(strtolower($this->method), $class_methods))
			{
				$this->directory = "";
				$this->class = $this->error_controller;
				$this->method = $this->error_method_404;
				include(APPPATH.'controllers/'.$this->fetch_directory().$this->error_controller.EXT);
			}
		}
	}
 
	function show_404()
	{
		include(APPPATH.'controllers/'.$this->fetch_directory().$this->error_controller.EXT);
		call_user_func(array($this->error_controller, $this->error_method_404));
	}
 
}
 
/* End of file MY_Router.php */
/* Location: ./system/application/libraries/MY_Router.php */