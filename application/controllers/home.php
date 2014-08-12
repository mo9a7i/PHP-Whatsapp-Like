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
	
	public function test()
	{
		$this->load->library('XMPPHP/XMPPHP_XMPP');
		$gtalk_username = "boy1@mohannadotaibi.com"; //fake email
		$gtalk_password = "123123"; //fake password
		$check_notification_interval = 10; //frequency to check for unsent message
		
		$conn = new XMPPHP_XMPP(
			'mohannadotaibi.com',
			5222, //port
			$gtalk_username , //username
			$gtalk_password, //password
			'xmpphp', //client
			'mohannadotaibi.com', //domain
			$printlog=true, //echo out the log
			$loglevel=XMPPHP_Log::LEVEL_ERROR //only on error, we echo out
		);
		
		
		try 
		{
			//connect to gtalk server
			$conn->useEncryption(true);
			$conn->connect();
			$conn->processUntil('session_start');
			$conn->presence();

			//send messages
			//$receipient contains your receipent GTalk email, eg. 'receipient@gmail.com'
			$conn->message("lady2@mohannadotaibi.com", "Test Message");

			//disconnect from gtalk server
			$conn->disconnect();
             
        } 
		catch(XMPPHP_Exception $e) 
		{
            //somewhere in between, error occurs
            echo "Error occur while sending message. Message: " . $e->getMessage() ;
        } 
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */