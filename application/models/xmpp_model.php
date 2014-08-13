<?php
class Xmpp_model extends CI_Model{
	
	var $params;
	var $conn;
	
	public function __construct() {
		parent::__construct();
				
		$this->params = array(
			'username' => "boy1@mohannadotaibi.com",
			'password' => "123123",
			'host' => 'mohannadotaibi.com',
			'port' => 5222,
			'resource' => 'xmpphp',
			'server' => 'mohannadotaibi.com',
			'printlog' => true,
		);
		$this->conn = $this->load->library('XMPPHP_XMPP', $this->params,'xmpp');
	}
	
	public function send_message($to,$message)
	{
		try 
		{
			$this->xmpp->useEncryption(true);
			$this->xmpp->connect();
			$this->xmpp->processUntil('session_start');
			$this->xmpp->presence();
			$this->xmpp->message($to,$message);

			$this->xmpp->disconnect();
        } 
		catch(XMPPHP_Exception $e) 
		{
			//somewhere in between, error occurs
			echo "Error occur while sending message. Message: " . $e->getMessage() ;
		} 
	}
	
}
?>