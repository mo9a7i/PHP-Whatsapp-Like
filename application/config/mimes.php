<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| MIME TYPES
| -------------------------------------------------------------------
| This file contains an array of mime types.  It is used by the
| Upload class to help identify allowed file types.
|
*/

$mimes = array(	'hqx'	=>	'application/mac-binhex40',
				'cpt'	=>	'application/mac-compactpro',
				'csv'	=>	array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'),
				'bin'	=>	'application/macbinary',
				'dms'	=>	'application/octet-stream',
				'lha'	=>	'application/octet-stream',
				'lzh'	=>	'application/octet-stream',
				'exe'	=>	array('application/octet-stream', 'application/x-msdownload'),
				'class'	=>	'application/octet-stream',
				'psd'	=>	'application/x-photoshop',
				'so'	=>	'application/octet-stream',
				'sea'	=>	'application/octet-stream',
				'dll'	=>	'application/octet-stream',
				'oda'	=>	'application/oda',
				'pdf'	=>	array('application/pdf', 'application/x-download'),
				'ai'	=>	'application/postscript',
				'eps'	=>	'application/postscript',
				'ps'	=>	'application/postscript',
				'smi'	=>	'application/smil',
				'smil'	=>	'application/smil',
				'mif'	=>	'application/vnd.mif',
				'xls'	=>	array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
				'ppt'	=>	array('application/powerpoint', 'application/vnd.ms-powerpoint'),
				'wbxml'	=>	'application/wbxml',
				'wmlc'	=>	'application/wmlc',
				'dcr'	=>	'application/x-director',
				'dir'	=>	'application/x-director',
				'dxr'	=>	'application/x-director',
				'dvi'	=>	'application/x-dvi',
				'gtar'	=>	'application/x-gtar',
				'gz'	=>	'application/x-gzip',
				'php'	=>	'application/x-httpd-php',
				'php4'	=>	'application/x-httpd-php',
				'php3'	=>	'application/x-httpd-php',
				'phtml'	=>	'application/x-httpd-php',
				'phps'	=>	'application/x-httpd-php-source',
				'js'	=>	'application/x-javascript',
				'swf'	=>	'application/x-shockwave-flash',
				'sit'	=>	'application/x-stuffit',
				'tar'	=>	'application/x-tar',
				'tgz'	=>	array('application/x-tar', 'application/x-gzip-compressed','application/octet-stream'),
				'xhtml'	=>	'application/xhtml+xml',
				'xht'	=>	'application/xhtml+xml',
				'zip'	=>  array('application/x-zip', 'application/zip', 'application/x-zip-compressed','application/octet-stream'),
				'mid'	=>	'audio/midi',
				'midi'	=>	'audio/midi',
				'mpga'	=>	'audio/mpeg',
				'mp2'	=>	'audio/mpeg',
				'mp3'	=>	array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
				'aif'	=>	'audio/x-aiff',
				'aiff'	=>	'audio/x-aiff',
				'aifc'	=>	'audio/x-aiff',
				'ram'	=>	'audio/x-pn-realaudio',
				'rm'	=>	'audio/x-pn-realaudio',
				'rpm'	=>	'audio/x-pn-realaudio-plugin',
				'ra'	=>	'audio/x-realaudio',
				'rv'	=>	'video/vnd.rn-realvideo',
				'wav'	=>	array('audio/x-wav', 'audio/wave', 'audio/wav'),
				'bmp'	=>	array('image/bmp', 'image/x-windows-bmp', 'application/octet-stream'), //Added for SWFUpload
				'gif'	=>	array('image/gif', 'application/octet-stream'), //Added for SWFUpload
				'jpeg'	=>	array('image/jpeg', 'image/pjpeg', 'application/octet-stream'), //Added for SWFUpload
				'jpg'	=>	array('image/jpeg', 'image/pjpeg', 'application/octet-stream'), //Added for SWFUpload
				'jpe'	=>	array('image/jpeg', 'image/pjpeg', 'application/octet-stream'), //Added for SWFUpload
				'png'	=>	array('image/png',  'image/x-png', 'application/octet-stream'), //Added for SWFUpload
				'tiff'	=>	array('image/tiff','application/octet-stream'),
				'tif'	=>	array('image/tiff','application/octet-stream'),
				'css'	=>	'text/css',
				'html'	=>	'text/html',
				'htm'	=>	'text/html',
				'shtml'	=>	'text/html',
				'txt'	=>	array('text/plain', 'application/octet-stream'),
				'text'	=>	'text/plain',
				'log'	=>	array('text/plain', 'text/x-log'),
				'rtx'	=>	'text/richtext',
				'rtf'	=>	'text/rtf',
				'xml'	=>	'text/xml',
				'xsl'	=>	'text/xml',
				'mpeg'	=>	array('video/mpeg', 'application/octet-stream'),
				'flv'	=>	array('video/x-flv', 'application/octet-stream'),
				'mpg'	=>	array('video/mpeg', 'application/octet-stream'),
				'mpe'	=>	array('video/mpeg', 'application/octet-stream'),
				'qt'	=>	array('video/quicktime', 'application/octet-stream'),
				'mov'	=>	array('video/quicktime', 'application/octet-stream'),
				'avi'	=>	array('video/x-msvideo', 'application/octet-stream'),
				'movie'	=>	array('video/x-sgi-movie', 'application/octet-stream'),
				'doc'	=>	array('application/msword', 'application/octet-stream'),
				'docx'	=>	array('application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/octet-stream'),
				'xlsx'	=>	array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/octet-stream'),
				'word'	=>	array('application/msword', 'application/octet-stream'),
				'xl'	=>	array('application/excel', 'application/octet-stream'),
				'eml'	=>	'message/rfc822',
				'json' => array('application/json', 'text/json')
			);


/* End of file mimes.php */
/* Location: ./application/config/mimes.php */
