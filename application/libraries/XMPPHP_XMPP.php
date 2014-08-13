<?php
/**
 * XMPPHP: The PHP XMPP Library
 * Copyright (C) 2008  Nathanael C. Fritz
 * This file is part of SleekXMPP.
 * 
 * XMPPHP is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * XMPPHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with XMPPHP; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   xmpphp 
 * @package	XMPPHP
 * @author	 Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author	 Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author	 Michael Garvin <JID: gar@netflint.net>
 * @copyright  2008 Nathanael C. Fritz
 */
 
/**
 * XMPPHP Main Class
 * 
 * @category   xmpphp 
 * @package	XMPPHP
 * @author	 Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author	 Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author	 Michael Garvin <JID: gar@netflint.net>
 * @copyright  2008 Nathanael C. Fritz
 * @version	$Id$
 */
class XMPPHP_XMPP extends XMPPHP_XMLStream {
	/**
	 * @var string
	 */
	public $server;

	/**
	 * @var string
	 */
	public $user;
	
	/**
	 * @var string
	 */
	protected $password;
	
	/**
	 * @var string
	 */
	protected $resource;
	
	/**
	 * @var string
	 */
	protected $fulljid;
	
	/**
	 * @var string
	 */
	protected $basejid;
	
	/**
	 * @var boolean
	 */
	protected $authed = false;
	protected $session_started = false;
	
	/**
	 * @var boolean
	 */
	protected $auto_subscribe = false;
	
	/**
	 * @var boolean
	 */
	protected $use_encryption = true;
	
	/**
	 * @var boolean
	 */
	public $track_presence = true;
	
	/**
	 * @var object
	 */
	public $roster;

	/**
	 * Constructor
	 *
	 * @param string  $host
	 * @param integer $port
	 * @param string  $user
	 * @param string  $password
	 * @param string  $resource
	 * @param string  $server
	 * @param boolean $printlog
	 * @param string  $loglevel
	 */
	public function __construct($params) {
		$host = $params['host'];
		$port = $params['port'];
		$printlog = $params['printlog'];
		if(!isset($params['loglevel'])) $loglevel = XMPPHP_Log::LEVEL_ERROR;
		else $loglevel = $params['loglevel'];
		
		parent::__construct($host, $port, $printlog, $loglevel);
		
		$this->user	 = $params['username'];
		$this->password = $params['password'];
		$this->resource = $params['resource'];
		if(!$params['server']) $server = $host;
		else $server = $params['server'];
		
		$this->basejid = $this->user . '@' . $this->host;

		$this->roster = new Roster();
		$this->track_presence = true;

		$this->stream_start = '<stream:stream to="' . $server . '" xmlns:stream="http://etherx.jabber.org/streams" xmlns="jabber:client" version="1.0">';
		$this->stream_end   = '</stream:stream>';
		$this->default_ns   = 'jabber:client';
		
		$this->addXPathHandler('{http://etherx.jabber.org/streams}features', 'features_handler');
		$this->addXPathHandler('{urn:ietf:params:xml:ns:xmpp-sasl}success', 'sasl_success_handler');
		$this->addXPathHandler('{urn:ietf:params:xml:ns:xmpp-sasl}failure', 'sasl_failure_handler');
		$this->addXPathHandler('{urn:ietf:params:xml:ns:xmpp-tls}proceed', 'tls_proceed_handler');
		$this->addXPathHandler('{jabber:client}message', 'message_handler');
		$this->addXPathHandler('{jabber:client}presence', 'presence_handler');
		$this->addXPathHandler('iq/{jabber:iq:roster}query', 'roster_iq_handler');
	}

	/**
	 * Turn encryption on/ff
	 *
	 * @param boolean $useEncryption
	 */
	public function useEncryption($useEncryption = true) {
		$this->use_encryption = $useEncryption;
	}
	
	/**
	 * Turn on auto-authorization of subscription requests.
	 *
	 * @param boolean $autoSubscribe
	 */
	public function autoSubscribe($autoSubscribe = true) {
		$this->auto_subscribe = $autoSubscribe;
	}

	/**
	 * Send XMPP Message
	 *
	 * @param string $to
	 * @param string $body
	 * @param string $type
	 * @param string $subject
	 */
	public function message($to, $body, $type = 'chat', $subject = null, $payload = null) {
	    if(is_null($type))
	    {
	        $type = 'chat';
	    }
	    
		$to	  = htmlspecialchars($to);
		$body	= htmlspecialchars($body);
		$subject = htmlspecialchars($subject);
		
		$out = "<message from=\"{$this->fulljid}\" to=\"$to\" type='$type'>";
		if($subject) $out .= "<subject>$subject</subject>";
		$out .= "<body>$body</body>";
		if($payload) $out .= $payload;
		$out .= "</message>";
		
		$this->send($out);
	}

	/**
	 * Set Presence
	 *
	 * @param string $status
	 * @param string $show
	 * @param string $to
	 */
	public function presence($status = null, $show = 'available', $to = null, $type='available', $priority=0) {
		if($type == 'available') $type = '';
		$to	 = htmlspecialchars($to);
		$status = htmlspecialchars($status);
		if($show == 'unavailable') $type = 'unavailable';
		
		$out = "<presence";
		if($to) $out .= " to=\"$to\"";
		if($type) $out .= " type='$type'";
		if($show == 'available' and !$status) {
			$out .= "/>";
		} else {
			$out .= ">";
			if($show != 'available') $out .= "<show>$show</show>";
			if($status) $out .= "<status>$status</status>";
			if($priority) $out .= "<priority>$priority</priority>";
			$out .= "</presence>";
		}
		
		$this->send($out);
	}
	/**
	 * Send Auth request
	 *
	 * @param string $jid
	 */
	public function subscribe($jid) {
		$this->send("<presence type='subscribe' to='{$jid}' from='{$this->fulljid}' />");
		#$this->send("<presence type='subscribed' to='{$jid}' from='{$this->fulljid}' />");
	}

	/**
	 * Message handler
	 *
	 * @param string $xml
	 */
	public function message_handler($xml) {
		if(isset($xml->attrs['type'])) {
			$payload['type'] = $xml->attrs['type'];
		} else {
			$payload['type'] = 'chat';
		}
		$payload['from'] = $xml->attrs['from'];
		$payload['body'] = $xml->sub('body')->data;
		$payload['xml'] = $xml;
		$this->log->log("Message: {$xml->sub('body')->data}", XMPPHP_Log::LEVEL_DEBUG);
		$this->event('message', $payload);
	}

	/**
	 * Presence handler
	 *
	 * @param string $xml
	 */
	public function presence_handler($xml) {
		$payload['type'] = (isset($xml->attrs['type'])) ? $xml->attrs['type'] : 'available';
		$payload['show'] = (isset($xml->sub('show')->data)) ? $xml->sub('show')->data : $payload['type'];
		$payload['from'] = $xml->attrs['from'];
		$payload['status'] = (isset($xml->sub('status')->data)) ? $xml->sub('status')->data : '';
		$payload['priority'] = (isset($xml->sub('priority')->data)) ? intval($xml->sub('priority')->data) : 0;
		$payload['xml'] = $xml;
		if($this->track_presence) {
			$this->roster->setPresence($payload['from'], $payload['priority'], $payload['show'], $payload['status']);
		}
		$this->log->log("Presence: {$payload['from']} [{$payload['show']}] {$payload['status']}",  XMPPHP_Log::LEVEL_DEBUG);
		if(array_key_exists('type', $xml->attrs) and $xml->attrs['type'] == 'subscribe') {
			if($this->auto_subscribe) {
				$this->send("<presence type='subscribed' to='{$xml->attrs['from']}' from='{$this->fulljid}' />");
				$this->send("<presence type='subscribe' to='{$xml->attrs['from']}' from='{$this->fulljid}' />");
			}
			$this->event('subscription_requested', $payload);
		} elseif(array_key_exists('type', $xml->attrs) and $xml->attrs['type'] == 'subscribed') {
			$this->event('subscription_accepted', $payload);
		} else {
			$this->event('presence', $payload);
		}
	}

	/**
	 * Features handler
	 *
	 * @param string $xml
	 */
	protected function features_handler($xml) {
		if($xml->hasSub('starttls') and $this->use_encryption) {
			$this->send("<starttls xmlns='urn:ietf:params:xml:ns:xmpp-tls'><required /></starttls>");
		} elseif($xml->hasSub('bind') and $this->authed) {
			$id = $this->getId();
			$this->addIdHandler($id, 'resource_bind_handler');
			$this->send("<iq xmlns=\"jabber:client\" type=\"set\" id=\"$id\"><bind xmlns=\"urn:ietf:params:xml:ns:xmpp-bind\"><resource>{$this->resource}</resource></bind></iq>");
		} else {
			$this->log->log("Attempting Auth...");
			if ($this->password) {
			$this->send("<auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='PLAIN'>" . base64_encode("\x00" . $this->user . "\x00" . $this->password) . "</auth>");
			} else {
                        $this->send("<auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='ANONYMOUS'/>");
			}	
		}
	}

	/**
	 * SASL success handler
	 *
	 * @param string $xml
	 */
	protected function sasl_success_handler($xml) {
		$this->log->log("Auth success!");
		$this->authed = true;
		$this->reset();
	}
	
	/**
	 * SASL feature handler
	 *
	 * @param string $xml
	 */
	protected function sasl_failure_handler($xml) {
		$this->log->log("Auth failed!",  XMPPHP_Log::LEVEL_ERROR);
		$this->disconnect();
		
		throw new XMPPHP_Exception('Auth failed!');
	}

	/**
	 * Resource bind handler
	 *
	 * @param string $xml
	 */
	protected function resource_bind_handler($xml) {
		if($xml->attrs['type'] == 'result') {
			$this->log->log("Bound to " . $xml->sub('bind')->sub('jid')->data);
			$this->fulljid = $xml->sub('bind')->sub('jid')->data;
			$jidarray = explode('/',$this->fulljid);
			$this->jid = $jidarray[0];
		}
		$id = $this->getId();
		$this->addIdHandler($id, 'session_start_handler');
		$this->send("<iq xmlns='jabber:client' type='set' id='$id'><session xmlns='urn:ietf:params:xml:ns:xmpp-session' /></iq>");
	}

	/**
	* Retrieves the roster
	*
	*/
	public function getRoster() {
		$id = $this->getID();
		$this->send("<iq xmlns='jabber:client' type='get' id='$id'><query xmlns='jabber:iq:roster' /></iq>");
	}

	/**
	* Roster iq handler
	* Gets all packets matching XPath "iq/{jabber:iq:roster}query'
	*
	* @param string $xml
	*/
	protected function roster_iq_handler($xml) {
		$status = "result";
		$xmlroster = $xml->sub('query');
		foreach($xmlroster->subs as $item) {
			$groups = array();
			if ($item->name == 'item') {
				$jid = $item->attrs['jid']; //REQUIRED
				$name = $item->attrs['name']; //MAY
				$subscription = $item->attrs['subscription'];
				foreach($item->subs as $subitem) {
					if ($subitem->name == 'group') {
						$groups[] = $subitem->data;
					}
				}
				$contacts[] = array($jid, $subscription, $name, $groups); //Store for action if no errors happen
			} else {
				$status = "error";
			}
		}
		if ($status == "result") { //No errors, add contacts
			foreach($contacts as $contact) {
				$this->roster->addContact($contact[0], $contact[1], $contact[2], $contact[3]);
			}
		}
		if ($xml->attrs['type'] == 'set') {
			$this->send("<iq type=\"reply\" id=\"{$xml->attrs['id']}\" to=\"{$xml->attrs['from']}\" />");
		}
	}

	/**
	 * Session start handler
	 *
	 * @param string $xml
	 */
	protected function session_start_handler($xml) {
		$this->log->log("Session started");
		$this->session_started = true;
		$this->event('session_start');
	}

	/**
	 * TLS proceed handler
	 *
	 * @param string $xml
	 */
	protected function tls_proceed_handler($xml) {
		$this->log->log("Starting TLS encryption");
		stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT);
		$this->reset();
	}

	/**
	* Retrieves the vcard
	*
	*/
	public function getVCard($jid = Null) {
		$id = $this->getID();
		$this->addIdHandler($id, 'vcard_get_handler');
		if($jid) {
			$this->send("<iq type='get' id='$id' to='$jid'><vCard xmlns='vcard-temp' /></iq>");
		} else {
			$this->send("<iq type='get' id='$id'><vCard xmlns='vcard-temp' /></iq>");
		}
	}

	/**
	* VCard retrieval handler
	*
	* @param XML Object $xml
	*/
	protected function vcard_get_handler($xml) {
		$vcard_array = array();
		$vcard = $xml->sub('vcard');
		// go through all of the sub elements and add them to the vcard array
		foreach ($vcard->subs as $sub) {
			if ($sub->subs) {
				$vcard_array[$sub->name] = array();
				foreach ($sub->subs as $sub_child) {
					$vcard_array[$sub->name][$sub_child->name] = $sub_child->data;
				}
			} else {
				$vcard_array[$sub->name] = $sub->data;
			}
		}
		$vcard_array['from'] = $xml->attrs['from'];
		$this->event('vcard', $vcard_array);
	}
}




/**
 * XMPPHP Roster Object
 * 
 * @category   xmpphp 
 * @package	XMPPHP
 * @author	 Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author	 Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author	 Michael Garvin <JID: gar@netflint.net>
 * @copyright  2008 Nathanael C. Fritz
 * @version	$Id$
 */

class Roster {
	/**
	 * Roster array, handles contacts and presence.  Indexed by jid.
	 * Contains array with potentially two indexes 'contact' and 'presence'
	 * @var array
	 */
	protected $roster_array = array();
	/**
	 * Constructor
	 * 
	 */
	public function __construct($roster_array = array()) {
		if ($this->verifyRoster($roster_array)) {
			$this->roster_array = $roster_array; //Allow for prepopulation with existing roster
		} else {
			$this->roster_array = array();
		}
	}

	/**
	 *
	 * Check that a given roster array is of a valid structure (empty is still valid)
	 *
	 * @param array $roster_array
	 */
	protected function verifyRoster($roster_array) {
		#TODO once we know *what* a valid roster array looks like
		return True;
	}

	/**
	 *
	 * Add given contact to roster
	 *
	 * @param string $jid
	 * @param string $subscription
	 * @param string $name
	 * @param array $groups
	 */
	public function addContact($jid, $subscription, $name='', $groups=array()) {
		$contact = array('jid' => $jid, 'subscription' => $subscription, 'name' => $name, 'groups' => $groups);
		if ($this->isContact($jid)) {
			$this->roster_array[$jid]['contact'] = $contact;
		} else {
			$this->roster_array[$jid] = array('contact' => $contact);
		}
	}

	/**
	 * 
	 * Retrieve contact via jid
	 *
	 * @param string $jid
	 */
	public function getContact($jid) {
		if ($this->isContact($jid)) {
			return $this->roster_array[$jid]['contact'];
		}
	}

	/**
	 *
	 * Discover if a contact exists in the roster via jid
	 *
	 * @param string $jid
	 */
	public function isContact($jid) {
		return (array_key_exists($jid, $this->roster_array));
	}

	/**
	 *
	 * Set presence
	 *
	 * @param string $presence
	 * @param integer $priority
	 * @param string $show
	 * @param string $status
	*/
	public function setPresence($presence, $priority, $show, $status) {
		list($jid, $resource) = explode("/", $presence);
		if ($show != 'unavailable') {
			if (!$this->isContact($jid)) {
				$this->addContact($jid, 'not-in-roster');
			}
			$resource = $resource ? $resource : '';
			$this->roster_array[$jid]['presence'][$resource] = array('priority' => $priority, 'show' => $show, 'status' => $status);
		} else { //Nuke unavailable resources to save memory
			unset($this->roster_array[$jid]['resource'][$resource]);
		}
	}

	/*
	 *
	 * Return best presence for jid
	 *
	 * @param string $jid
	 */
	public function getPresence($jid) {
		$split = explode("/", $jid);
		$jid = $split[0];
		if($this->isContact($jid)) {
			$current = array('resource' => '', 'active' => '', 'priority' => -129, 'show' => '', 'status' => ''); //Priorities can only be -128 = 127
			foreach($this->roster_array[$jid]['presence'] as $resource => $presence) {
				//Highest available priority or just highest priority
				if ($presence['priority'] > $current['priority'] and (($presence['show'] == "chat" or $presence['show'] == "available") or ($current['show'] != "chat" or $current['show'] != "available"))) {
					$current = $presence;
					$current['resource'] = $resource;
				}
			}
			return $current;
		}
	}
	/**
	 *
	 * Get roster
	 *
	 */
	public function getRoster() {
		return $this->roster_array;
	}
}

/**
 * XMPPHP Exception
 *
 * @category   xmpphp 
 * @package    XMPPHP
 * @author     Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author     Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author	 Michael Garvin <JID: gar@netflint.net>
 * @copyright  2008 Nathanael C. Fritz
 * @version    $Id$
 */
class XMPPHP_Exception extends Exception {
}


/**
 * XMPPHP XML Object
 * 
 * @category   xmpphp 
 * @package	XMPPHP
 * @author	 Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author	 Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author	 Michael Garvin <JID: gar@netflint.net>
 * @copyright  2008 Nathanael C. Fritz
 * @version	$Id$
 */
class XMPPHP_XMLObj {
	/**
	 * Tag name
	 *
	 * @var string
	 */
	public $name;
	
	/**
	 * Namespace
	 *
	 * @var string
	 */
	public $ns;
	
	/**
	 * Attributes
	 *
	 * @var array
	 */
	public $attrs = array();
	
	/**
	 * Subs?
	 *
	 * @var array
	 */
	public $subs = array();
	
	/**
	 * Node data
	 * 
	 * @var string
	 */
	public $data = '';

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param string $ns
	 * @param array  $attrs
	 * @param string $data
	 */
	public function __construct($name, $ns = '', $attrs = array(), $data = '') {
		$this->name = strtolower($name);
		$this->ns   = $ns;
		if(is_array($attrs) && count($attrs)) {
			foreach($attrs as $key => $value) {
				$this->attrs[strtolower($key)] = $value;
			}
		}
		$this->data = $data;
	}

	/**
	 * Dump this XML Object to output.
	 *
	 * @param integer $depth
	 */
	public function printObj($depth = 0) {
		print str_repeat("\t", $depth) . $this->name . " " . $this->ns . ' ' . $this->data;
		print "\n";
		foreach($this->subs as $sub) {
			$sub->printObj($depth + 1);
		}
	}

	/**
	 * Return this XML Object in xml notation
	 *
	 * @param string $str
	 */
	public function toString($str = '') {
		$str .= "<{$this->name} xmlns='{$this->ns}' ";
		foreach($this->attrs as $key => $value) {
			if($key != 'xmlns') {
				$value = htmlspecialchars($value);
				$str .= "$key='$value' ";
			}
		}
		$str .= ">";
		foreach($this->subs as $sub) {
			$str .= $sub->toString();
		}
		$body = htmlspecialchars($this->data);
		$str .= "$body</{$this->name}>";
		return $str;
	}

	/**
	 * Has this XML Object the given sub?
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function hasSub($name, $ns = null) {
		foreach($this->subs as $sub) {
			if(($name == "*" or $sub->name == $name) and ($ns == null or $sub->ns == $ns)) return true;
		}
		return false;
	}

	/**
	 * Return a sub
	 *
	 * @param string $name
	 * @param string $attrs
	 * @param string $ns
	 */
	public function sub($name, $attrs = null, $ns = null) {
		#TODO attrs is ignored
		foreach($this->subs as $sub) {
			if($sub->name == $name and ($ns == null or $sub->ns == $ns)) {
				return $sub;
			}
		}
	}
}

/**
 * XMPPHP Log
 * 
 * @package	XMPPHP
 * @author	 Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author	 Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author	 Michael Garvin <JID: gar@netflint.net>
 * @copyright  2008 Nathanael C. Fritz
 * @version	$Id$
 */
class XMPPHP_Log {
	
	const LEVEL_ERROR   = 0;
	const LEVEL_WARNING = 1;
	const LEVEL_INFO	= 2;
	const LEVEL_DEBUG   = 3;
	const LEVEL_VERBOSE = 4;
	
	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var array
	 */
	protected $names = array('ERROR', 'WARNING', 'INFO', 'DEBUG', 'VERBOSE');

	/**
	 * @var integer
	 */
	protected $runlevel;

	/**
	 * @var boolean
	 */
	protected $printout;

	/**
	 * Constructor
	 *
	 * @param boolean $printout
	 * @param string  $runlevel
	 */
	public function __construct($printout = false, $runlevel = self::LEVEL_INFO) {
		$this->printout = (boolean)$printout;
		$this->runlevel = (int)$runlevel;
	}

	/**
	 * Add a message to the log data array
	 * If printout in this instance is set to true, directly output the message
	 *
	 * @param string  $msg
	 * @param integer $runlevel
	 */
	public function log($msg, $runlevel = self::LEVEL_INFO) {
		$time = time();
		#$this->data[] = array($this->runlevel, $msg, $time);
		if($this->printout and $runlevel <= $this->runlevel) {
			$this->writeLine($msg, $runlevel, $time);
		}
	}

	/**
	 * Output the complete log.
	 * Log will be cleared if $clear = true
	 *
	 * @param boolean $clear
	 * @param integer $runlevel
	 */
	public function printout($clear = true, $runlevel = null) {
		if($runlevel === null) {
			$runlevel = $this->runlevel;
		}
		foreach($this->data as $data) {
			if($runlevel <= $data[0]) {
				$this->writeLine($data[1], $runlevel, $data[2]);
			}
		}
		if($clear) {
			$this->data = array();
		}
	}
	
	protected function writeLine($msg, $runlevel, $time) {
		//echo date('Y-m-d H:i:s', $time)." [".$this->names[$runlevel]."]: ".$msg."\n";
		echo $time." [".$this->names[$runlevel]."]: ".$msg."\n";
		flush();
	}
}

/**
 * XMPPHP XML Stream
 * 
 * @category   xmpphp 
 * @package	XMPPHP
 * @author	 Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author	 Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author	 Michael Garvin <JID: gar@netflint.net>
 * @copyright  2008 Nathanael C. Fritz
 * @version	$Id$
 */
class XMPPHP_XMLStream {
	/**
	 * @var resource
	 */
	protected $socket;
	/**
	 * @var resource
	 */
	protected $parser;
	/**
	 * @var string
	 */
	protected $buffer;
	/**
	 * @var integer
	 */
	protected $xml_depth = 0;
	/**
	 * @var string
	 */
	protected $host;
	/**
	 * @var integer
	 */
	protected $port;
	/**
	 * @var string
	 */
	protected $stream_start = '<stream>';
	/**
	 * @var string
	 */
	protected $stream_end = '</stream>';
	/**
	 * @var boolean
	 */
	protected $disconnected = false;
	/**
	 * @var boolean
	 */
	protected $sent_disconnect = false;
	/**
	 * @var array
	 */
	protected $ns_map = array();
	/**
	 * @var array
	 */
	protected $current_ns = array();
	/**
	 * @var array
	 */
	protected $xmlobj = null;
	/**
	 * @var array
	 */
	protected $nshandlers = array();
	/**
	 * @var array
	 */
	protected $xpathhandlers = array();
	/**
	 * @var array
	 */
	protected $idhandlers = array();
	/**
	 * @var array
	 */
	protected $eventhandlers = array();
	/**
	 * @var integer
	 */
	protected $lastid = 0;
	/**
	 * @var string
	 */
	protected $default_ns;
	/**
	 * @var string
	 */
	protected $until = '';
	/**
	 * @var string
	 */
	protected $until_count = '';
	/**
	 * @var array
	 */
	protected $until_happened = false;
	/**
	 * @var array
	 */
	protected $until_payload = array();
	/**
	 * @var XMPPHP_Log
	 */
	protected $log;
	/**
	 * @var boolean
	 */
	protected $reconnect = true;
	/**
	 * @var boolean
	 */
	protected $been_reset = false;
	/**
	 * @var boolean
	 */
	protected $is_server;
	/**
	 * @var float
	 */
	protected $last_send = 0;
	/**
	 * @var boolean
	 */
	protected $use_ssl = false;
	/**
	 * @var integer
	 */
	protected $reconnectTimeout = 30;

	/**
	 * Constructor
	 *
	 * @param string  $host
	 * @param string  $port
	 * @param boolean $printlog
	 * @param string  $loglevel
	 * @param boolean $is_server
	 */
	public function __construct($host = null, $port = null, $printlog = false, $loglevel = null, $is_server = false) {
		$this->reconnect = !$is_server;
		$this->is_server = $is_server;
		$this->host = $host;
		$this->port = $port;
		$this->setupParser();
		$this->log = new XMPPHP_Log($printlog, $loglevel);
	}

	/**
	 * Destructor
	 * Cleanup connection
	 */
	public function __destruct() {
		if(!$this->disconnected && $this->socket) {
			$this->disconnect();
		}
	}
	
	/**
	 * Return the log instance
	 *
	 * @return XMPPHP_Log
	 */
	public function getLog() {
		return $this->log;
	}
	
	/**
	 * Get next ID
	 *
	 * @return integer
	 */
	public function getId() {
		$this->lastid++;
		return $this->lastid;
	}

	/**
	 * Set SSL
	 *
	 * @return integer
	 */
	public function useSSL($use=true) {
		$this->use_ssl = $use;
	}

	/**
	 * Add ID Handler
	 *
	 * @param integer $id
	 * @param string  $pointer
	 * @param string  $obj
	 */
	public function addIdHandler($id, $pointer, $obj = null) {
		$this->idhandlers[$id] = array($pointer, $obj);
	}

	/**
	 * Add Handler
	 *
	 * @param string $name
	 * @param string  $ns
	 * @param string  $pointer
	 * @param string  $obj
	 * @param integer $depth
	 */
	public function addHandler($name, $ns, $pointer, $obj = null, $depth = 1) {
		#TODO deprication warning
		$this->nshandlers[] = array($name,$ns,$pointer,$obj, $depth);
	}

	/**
	 * Add XPath Handler
	 *
	 * @param string $xpath
	 * @param string $pointer
	 * @param
	 */
	public function addXPathHandler($xpath, $pointer, $obj = null) {
		if (preg_match_all("/\(?{[^\}]+}\)?(\/?)[^\/]+/", $xpath, $regs)) {
			$ns_tags = $regs[0];
		} else {
			$ns_tags = array($xpath);
		}
		foreach($ns_tags as $ns_tag) {
			list($l, $r) = explode("}", $ns_tag);
			if ($r != null) {
				$xpart = array(substr($l, 1), $r);
			} else {
				$xpart = array(null, $l);
			}
			$xpath_array[] = $xpart;
		}
		$this->xpathhandlers[] = array($xpath_array, $pointer, $obj);
	}

	/**
	 * Add Event Handler
	 *
	 * @param integer $id
	 * @param string  $pointer
	 * @param string  $obj
	 */
	public function addEventHandler($name, $pointer, $obj) {
		$this->eventhandlers[] = array($name, $pointer, $obj);
	}

	/**
	 * Connect to XMPP Host
	 *
	 * @param integer $timeout
	 * @param boolean $persistent
	 * @param boolean $sendinit
	 */
	public function connect($timeout = 30, $persistent = false, $sendinit = true) {
		$this->sent_disconnect = false;
		$starttime = time();
		
		do {
			$this->disconnected = false;
			$this->sent_disconnect = false;
			if($persistent) {
				$conflag = STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT;
			} else {
				$conflag = STREAM_CLIENT_CONNECT;
			}
			$conntype = 'tcp';
			if($this->use_ssl) $conntype = 'ssl';
			$this->log->log("Connecting to $conntype://{$this->host}:{$this->port}");
			try {
				$this->socket = @stream_socket_client("$conntype://{$this->host}:{$this->port}", $errno, $errstr, $timeout, $conflag);
			} catch (Exception $e) {
				throw new XMPPHP_Exception($e->getMessage());
			}
			if(!$this->socket) {
				$this->log->log("Could not connect.",  XMPPHP_Log::LEVEL_ERROR);
				$this->disconnected = true;
				# Take it easy for a few seconds
				sleep(min($timeout, 5));
			}
		} while (!$this->socket && (time() - $starttime) < $timeout);
		
		if ($this->socket) {
			stream_set_blocking($this->socket, 1);
			if($sendinit) $this->send($this->stream_start);
		} else {
			throw new XMPPHP_Exception("Could not connect before timeout.");
		}
	}

	/**
	 * Reconnect XMPP Host
	 */
	public function doReconnect() {
		if(!$this->is_server) {
			$this->log->log("Reconnecting ($this->reconnectTimeout)...",  XMPPHP_Log::LEVEL_WARNING);
			$this->connect($this->reconnectTimeout, false, false);
			$this->reset();
			$this->event('reconnect');
		}
	}

	public function setReconnectTimeout($timeout) {
		$this->reconnectTimeout = $timeout;
	}
	
	/**
	 * Disconnect from XMPP Host
	 */
	public function disconnect() {
		$this->log->log("Disconnecting...",  XMPPHP_Log::LEVEL_VERBOSE);
		if(false == (bool) $this->socket) {
			return;
		}
		$this->reconnect = false;
		$this->send($this->stream_end);
		$this->sent_disconnect = true;
		$this->processUntil('end_stream', 5);
		$this->disconnected = true;
	}

	/**
	 * Are we are disconnected?
	 *
	 * @return boolean
	 */
	public function isDisconnected() {
		return $this->disconnected;
	}

	/**
	 * Core reading tool
	 * 0 -> only read if data is immediately ready
	 * NULL -> wait forever and ever
	 * integer -> process for this amount of time 
	 */
	
	private function __process($maximum=5) {
		
		$remaining = $maximum;
		
		do {
			$starttime = (microtime(true) * 1000000);
			$read = array($this->socket);
			$write = array();
			$except = array();
			if (is_null($maximum)) {
				$secs = NULL;
				$usecs = NULL;
			} else if ($maximum == 0) {
				$secs = 0;
				$usecs = 0;
			} else {
				$usecs = $remaining % 1000000;
				$secs = floor(($remaining - $usecs) / 1000000);
			}
			$updated = @stream_select($read, $write, $except, $secs, $usecs);
			if ($updated === false) {
				$this->log->log("Error on stream_select()",  XMPPHP_Log::LEVEL_VERBOSE);				
				if ($this->reconnect) {
					$this->doReconnect();
				} else {
					fclose($this->socket);
					$this->socket = NULL;
					return false;
				}
			} else if ($updated > 0) {
				# XXX: Is this big enough?
				$buff = @fread($this->socket, 4096);
				if(!$buff) { 
					if($this->reconnect) {
						$this->doReconnect();
					} else {
						fclose($this->socket);
						$this->socket = NULL;
						return false;
					}
				}
				$this->log->log("RECV: $buff",  XMPPHP_Log::LEVEL_VERBOSE);
				xml_parse($this->parser, $buff, false);
			} else {
				# $updated == 0 means no changes during timeout.
			}
			$endtime = (microtime(true)*1000000);
			$time_past = $endtime - $starttime;
			$remaining = $remaining - $time_past;
		} while (is_null($maximum) || $remaining > 0);
		return true;
	}
	
	/**
	 * Process
	 *
	 * @return string
	 */
	public function process() {
		$this->__process(NULL);
	}

	/**
	 * Process until a timeout occurs
	 *
	 * @param integer $timeout
	 * @return string
	 */
	public function processTime($timeout=NULL) {
		if (is_null($timeout)) {
			return $this->__process(NULL);
		} else {
			return $this->__process($timeout * 1000000);
		}
	}

	/**
	 * Process until a specified event or a timeout occurs
	 *
	 * @param string|array $event
	 * @param integer $timeout
	 * @return string
	 */
	public function processUntil($event, $timeout=-1) {
		$start = time();
		if(!is_array($event)) $event = array($event);
		$this->until[] = $event;
		end($this->until);
		$event_key = key($this->until);
		reset($this->until);
		$this->until_count[$event_key] = 0;
		$updated = '';
		while(!$this->disconnected and $this->until_count[$event_key] < 1 and (time() - $start < $timeout or $timeout == -1)) {
			$this->__process();
		}
		if(array_key_exists($event_key, $this->until_payload)) {
			$payload = $this->until_payload[$event_key];
			unset($this->until_payload[$event_key]);
			unset($this->until_count[$event_key]);
			unset($this->until[$event_key]);
		} else {
			$payload = array();
		}
		return $payload;
	}

	/**
	 * Obsolete?
	 */
	public function Xapply_socket($socket) {
		$this->socket = $socket;
	}

	/**
	 * XML start callback
	 * 
	 * @see xml_set_element_handler
	 *
	 * @param resource $parser
	 * @param string   $name
	 */
	public function startXML($parser, $name, $attr) {
		if($this->been_reset) {
			$this->been_reset = false;
			$this->xml_depth = 0;
		}
		$this->xml_depth++;
		if(array_key_exists('XMLNS', $attr)) {
			$this->current_ns[$this->xml_depth] = $attr['XMLNS'];
		} else {
			$this->current_ns[$this->xml_depth] = $this->current_ns[$this->xml_depth - 1];
			if(!$this->current_ns[$this->xml_depth]) $this->current_ns[$this->xml_depth] = $this->default_ns;
		}
		$ns = $this->current_ns[$this->xml_depth];
		foreach($attr as $key => $value) {
			if(strstr($key, ":")) {
				$key = explode(':', $key);
				$key = $key[1];
				$this->ns_map[$key] = $value;
			}
		}
		if(!strstr($name, ":") === false)
		{
			$name = explode(':', $name);
			$ns = $this->ns_map[$name[0]];
			$name = $name[1];
		}
		$obj = new XMPPHP_XMLObj($name, $ns, $attr);
		if($this->xml_depth > 1) {
			$this->xmlobj[$this->xml_depth - 1]->subs[] = $obj;
		}
		$this->xmlobj[$this->xml_depth] = $obj;
	}

	/**
	 * XML end callback
	 * 
	 * @see xml_set_element_handler
	 *
	 * @param resource $parser
	 * @param string   $name
	 */
	public function endXML($parser, $name) {
		#$this->log->log("Ending $name",  XMPPHP_Log::LEVEL_DEBUG);
		#print "$name\n";
		if($this->been_reset) {
			$this->been_reset = false;
			$this->xml_depth = 0;
		}
		$this->xml_depth--;
		if($this->xml_depth == 1) {
			#clean-up old objects
			#$found = false; #FIXME This didn't appear to be in use --Gar
			foreach($this->xpathhandlers as $handler) {
				if (is_array($this->xmlobj) && array_key_exists(2, $this->xmlobj)) {
					$searchxml = $this->xmlobj[2];
					$nstag = array_shift($handler[0]);
					if (($nstag[0] == null or $searchxml->ns == $nstag[0]) and ($nstag[1] == "*" or $nstag[1] == $searchxml->name)) {
						foreach($handler[0] as $nstag) {
							if ($searchxml !== null and $searchxml->hasSub($nstag[1], $ns=$nstag[0])) {
								$searchxml = $searchxml->sub($nstag[1], $ns=$nstag[0]);
							} else {
								$searchxml = null;
								break;
							}
						}
						if ($searchxml !== null) {
							if($handler[2] === null) $handler[2] = $this;
							$this->log->log("Calling {$handler[1]}",  XMPPHP_Log::LEVEL_DEBUG);
							$handler[2]->$handler[1]($this->xmlobj[2]);
						}
					}
				}
			}
			foreach($this->nshandlers as $handler) {
				if($handler[4] != 1 and array_key_exists(2, $this->xmlobj) and  $this->xmlobj[2]->hasSub($handler[0])) {
					$searchxml = $this->xmlobj[2]->sub($handler[0]);
				} elseif(is_array($this->xmlobj) and array_key_exists(2, $this->xmlobj)) {
					$searchxml = $this->xmlobj[2];
				}
				if($searchxml !== null and $searchxml->name == $handler[0] and ($searchxml->ns == $handler[1] or (!$handler[1] and $searchxml->ns == $this->default_ns))) {
					if($handler[3] === null) $handler[3] = $this;
					$this->log->log("Calling {$handler[2]}",  XMPPHP_Log::LEVEL_DEBUG);
					$handler[3]->$handler[2]($this->xmlobj[2]);
				}
			}
			foreach($this->idhandlers as $id => $handler) {
				if(array_key_exists('id', $this->xmlobj[2]->attrs) and $this->xmlobj[2]->attrs['id'] == $id) {
					if($handler[1] === null) $handler[1] = $this;
					$handler[1]->$handler[0]($this->xmlobj[2]);
					#id handlers are only used once
					unset($this->idhandlers[$id]);
					break;
				}
			}
			if(is_array($this->xmlobj)) {
				$this->xmlobj = array_slice($this->xmlobj, 0, 1);
				if(isset($this->xmlobj[0]) && $this->xmlobj[0] instanceof XMPPHP_XMLObj) {
					$this->xmlobj[0]->subs = null;
				}
			}
			unset($this->xmlobj[2]);
		}
		if($this->xml_depth == 0 and !$this->been_reset) {
			if(!$this->disconnected) {
				if(!$this->sent_disconnect) {
					$this->send($this->stream_end);
				}
				$this->disconnected = true;
				$this->sent_disconnect = true;
				fclose($this->socket);
				if($this->reconnect) {
					$this->doReconnect();
				}
			}
			$this->event('end_stream');
		}
	}

	/**
	 * XML character callback
	 * @see xml_set_character_data_handler
	 *
	 * @param resource $parser
	 * @param string   $data
	 */
	public function charXML($parser, $data) {
		if(array_key_exists($this->xml_depth, $this->xmlobj)) {
			$this->xmlobj[$this->xml_depth]->data .= $data;
		}
	}

	/**
	 * Event?
	 *
	 * @param string $name
	 * @param string $payload
	 */
	public function event($name, $payload = null) {
		$this->log->log("EVENT: $name",  XMPPHP_Log::LEVEL_DEBUG);
		foreach($this->eventhandlers as $handler) {
			if($name == $handler[0]) {
				if($handler[2] === null) {
					$handler[2] = $this;
				}
				$handler[2]->$handler[1]($payload);
			}
		}
		foreach($this->until as $key => $until) {
			if(is_array($until)) {
				if(in_array($name, $until)) {
					$this->until_payload[$key][] = array($name, $payload);
					if(!isset($this->until_count[$key])) {
						$this->until_count[$key] = 0;
					}
					$this->until_count[$key] += 1;
					#$this->until[$key] = false;
				}
			}
		}
	}

	/**
	 * Read from socket
	 */
	public function read() {
		$buff = @fread($this->socket, 1024);
		if(!$buff) { 
			if($this->reconnect) {
				$this->doReconnect();
			} else {
				fclose($this->socket);
				return false;
			}
		}
		$this->log->log("RECV: $buff",  XMPPHP_Log::LEVEL_VERBOSE);
		xml_parse($this->parser, $buff, false);
	}

	/**
	 * Send to socket
	 *
	 * @param string $msg
	 */
	public function send($msg, $timeout=NULL) {

		if (is_null($timeout)) {
			$secs = NULL;
			$usecs = NULL;
		} else if ($timeout == 0) {
			$secs = 0;
			$usecs = 0;
		} else {
			$maximum = $timeout * 1000000;
			$usecs = $maximum % 1000000;
			$secs = floor(($maximum - $usecs) / 1000000);
		}
		
		$read = array();
		$write = array($this->socket);
		$except = array();
		
		$select = @stream_select($read, $write, $except, $secs, $usecs);
		
		if($select === False) {
			$this->log->log("ERROR sending message; reconnecting.");
			$this->doReconnect();
			# TODO: retry send here
			return false;
		} elseif ($select > 0) {
			$this->log->log("Socket is ready; send it.", XMPPHP_Log::LEVEL_VERBOSE);
		} else {
			$this->log->log("Socket is not ready; break.", XMPPHP_Log::LEVEL_ERROR);
			return false;
		}
		
		$sentbytes = @fwrite($this->socket, $msg);
		$this->log->log("SENT: " . mb_substr($msg, 0, $sentbytes, '8bit'), XMPPHP_Log::LEVEL_VERBOSE);
		if($sentbytes === FALSE) {
			$this->log->log("ERROR sending message; reconnecting.", XMPPHP_Log::LEVEL_ERROR);
			$this->doReconnect();
			return false;
		}
		$this->log->log("Successfully sent $sentbytes bytes.", XMPPHP_Log::LEVEL_VERBOSE);
		return $sentbytes;
	}

	public function time() {
		list($usec, $sec) = explode(" ", microtime());
		return (float)$sec + (float)$usec;
	}

	/**
	 * Reset connection
	 */
	public function reset() {
		$this->xml_depth = 0;
		unset($this->xmlobj);
		$this->xmlobj = array();
		$this->setupParser();
		if(!$this->is_server) {
			$this->send($this->stream_start);
		}
		$this->been_reset = true;
	}

	/**
	 * Setup the XML parser
	 */
	public function setupParser() {
		$this->parser = xml_parser_create('UTF-8');
		xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'startXML', 'endXML');
		xml_set_character_data_handler($this->parser, 'charXML');
	}

	public function readyToProcess() {
		$read = array($this->socket);
		$write = array();
		$except = array();
		$updated = @stream_select($read, $write, $except, 0);
		return (($updated !== false) && ($updated > 0));
	}
}
