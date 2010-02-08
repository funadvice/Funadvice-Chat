<?php

require 'lib/redis.php';
require 'lib/parser.php';
require 'lib/HTMLPurifier.standalone.php';

class Chat {
	
	public $redis;
	public $room;
	public $people;
	public $user;
	public $expires;
	
	function __construct($room='public', $name='anon') {
		// Connect ot redis
		$this->redis = new Redis('db');
		$this->redis->select_db(9);
		
		// Set expire time for a session
		$this->expires = 300;

		// Fix the room name
		$this->user = $this->cleanup_name($name);		
		$this->room = $this->cleanup_name($room);
		
		// Remove any chat request we might have
		if ($this->redis->get('chat_alert:' . $this->user)) {
			$this->redis->delete('chat_alert:' . $this->user);
		}
		
		// Build a list of people in this room, and add me if needed 
		$this->roster($this->user);
	}
		
	// Cleanup room names and people names:
	function cleanup_name($name) {
		return(strtolower(trim(preg_replace('/[\W]+/x', '', $name))));
	}
	
	// Build the roster of people in the current room
	function roster($join_user = false) {
		$keys = "room:{$this->room}:*";
		if ($res = $this->redis->keys($keys)) {
			$this->people = $res;
		}

		// See if we need to add this current person
		if ($join_user) {
			$nperson = "room:{$this->room}:{$join_user}";
			if (!in_array($nperson, $this->people)) {
				$this->people[] = $nperson;
				$this->write('has entered the room', true);
			}
		}

		// Remove people no longer here
		if ($this->people) {
			reset($this->people);
			foreach ($this->people as $p) {
				// echo "-- {$p}<br/>";	
				if (!$this->redis->exists("e{$p}")) {
					$this->redis->delete("e{$p}");
				}
			}
		}
	}

	// Write a message to all the rooms;
	function write($msg='', $all = false) {
		
		$ukey = "room:{$this->room}:{$this->user}";
		
		$a = array('t'=>date('h:i'), 'u' => $this->user, 'm' => $msg);
		$msg = serialize($a);
		
		reset($this->people);
		foreach ($this->people as $u) {
			
			// deal with empty elements
			if (!$u) {
				continue;
			}
			
			// Add to the list, keeping it no longer than 100 elements
			$this->redis->lpush($u, $msg);
		}
	}
	
	// Pull everything from my queue - nondestructive
	function list_queue() {
		$ukey = "room:{$this->room}:{$this->user}";
		if ($this->redis->exists($ukey)) {
			return($this->redis->lrange($ukey, 0, 99));
		}
		return(false);
	}
 
	// Destructive pull from my queue
	function read() {
		$ukey = "room:{$this->room}:{$this->user}";
		if ($this->redis->exists($ukey)) {
			$out = array();
			while ($line = $this->redis->pop($ukey, true)) {
				if (!$line) {
					continue;
				}
				$out[] = unserialize($line);
			}
			return($out);
		}
		return(false);		
	}
	
	// Leave and end the chat
	function leave() {
		
		$this->write('has left the room. Bye!');
		
		$ukey = "room:{$this->room}:{$this->user}";
		if ($this->redis->exists($ukey)) {
			$key = array_search($ukey, $this->people);			
			// Delete the person from this array
			if ($key >= 0) {
				$this->redis->delete($ukey);
				$this->redis->delete('e' , $ukey);				
				unset($this->people[$key]);
			}
		}
	}
	
	// I am alive
	function ping() {
		// Write a flag for this person in this room
		$ukey = "eroom:{$this->room}:{$this->user}";

		$this->redis->set($ukey, time());
		$this->redis->expire($ukey, $this->expires);
	}
	
}

header('Content-type: text/javascript; charset=UTF-8');

// Use HTML purifier
$purifier = new HTMLPurifier();

// COnfig it
$config = HTMLPurifier_Config::createDefault();
$config->set('Core.Encoding', 'UTF-8'); // replace with your encoding
$config->set('HTML.Doctype', 'HTML 4.01 Transitional'); // replace with your doctype
$config->set('HTML.Allowed', '');
$config->set('AutoFormat.AutoParagraph', false);
$purifier = new HTMLPurifier($config);

// Startup
$c = new Chat($purifier->purify($_REQUEST['r']), $purifier->purify($_REQUEST['u']));
$c->ping();

if ($_REQUEST['bye'] == '1') {
	$c->leave();
	echo 'ok';
	exit;
}

if ($_REQUEST['leave']) {
	$c->leave();
}

if ($_REQUEST['msg']) {
	$c->write(parser($purifier->purify($_REQUEST['msg'])));
}

// Output all stuff from my queue
if ($q = $c->read()) {
	// output the data in json
	echo json_encode($q);
} else {
	echo '{}' . time();
}

if ($_REQUEST['test']) {
	header('Content-type: text/html');
?>
<form method="GET">
	Test output:<br/>
	<input type="text" name="msg" size="40" /> <input type="submit" value="Send" />
	<input type="hidden" name="u" value="<?= $_REQUEST['u'] ?>" />
	<input type="hidden" name="r" value="<?= $_REQUEST['r'] ?>" />
	<input type="hidden" name="test" value="1" />
</form>
<?
}

?>
