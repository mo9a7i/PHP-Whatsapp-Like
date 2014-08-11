<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$config['useragent']			= "Mohannad_website";
$config['protocol'] = 'sendmail';
$config['mailpath'] = '/usr/sbin/sendmail';
$config['charset'] = 'utf-8';
$config['wordwrap'] = FALSE;

// $config['protocol'] = 'smtp';
// $config['smtp_host'] = 'localhost';
// $config['email_address'] = 'sender@localhost.com';
// $config['email_name'] = 'Localhost test';
$config['mailtype'] = 'html';
$config['crlf'] = "\r\n";
$config['newline'] = "\r\n";