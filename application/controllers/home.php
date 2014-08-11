<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Home extends CI_Controller {

	public function index()
	{
		$data['header_include'] = '<!-- nothing -->';
		$data['keywords'] = array();
		$data['description'] = '';
		$data['title'] = "الرئيسية";
		$data['main_content'] = 'home';
		$this->load->view('template/template', $data);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */