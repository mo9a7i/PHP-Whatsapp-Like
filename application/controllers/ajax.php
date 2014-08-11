<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Ajax extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	//redirect if needed, otherwise display the user list
	function index()
	{
		redirect(base_url());
	}

}