<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$route['default_controller'] = "home";
$route['admin'] = "admin/dashboard";
$route['404_override'] = '';
$route['jokes/(:any)'] = "posts/by_slug/$1";
$route['tag/(:any)'] = "tags/by_slug/$1";
$route['page/(:any)'] = "pages/by_slug/$1";
$route['members/(:any)'] = "users/by_name/$1";

/* End of file routes.php */
/* Location: ./application/config/routes.php */