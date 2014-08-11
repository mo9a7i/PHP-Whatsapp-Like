<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
/  In this file, all admin panel validation rules are set, so no need to set them around the application
*/
$config = array(
	'site_settings' => array(
		array(
                     'field'   => 'site_title', 
                     'label'   => 'Site Title', 
                     'rules'   => 'trim|required'
                  ),
               array(
                     'field'   => 'admin_email', 
                     'label'   => 'Admin Email', 
                     'rules'   => 'trim|required|valid_email'
                  ),
               array(
                     'field'   => 'default_points', 
                     'label'   => 'Default Points', 
                     'rules'   => 'trim|required'
                  )
	),
	'email' => array(
		array(
		'field' => 'emailaddress',
		'label' => 'EmailAddress',
		'rules' => 'required|valid_email'
		),
		array(
		'field' => 'name',
		'label' => 'Name',
		'rules' => 'required|alpha'
		),
		array(
		'field' => 'title',
		'label' => 'Title',
		'rules' => 'required'
		),
		array(
		'field' => 'message',
		'label' => 'MessageBody',
		'rules' => 'required'
		)
	)                          
);