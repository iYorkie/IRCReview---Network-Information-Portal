<?php
	echo "/***********************************************************************\n";
	echo " *  Copyright (C) 2010 Clay Freeman\n";
	echo " * \n";
	echo " *  This program is free software: you can redistribute it and/or modify\n";
	echo " * it under the terms of the GNU General Public License as published by\n";
	echo " * the Free Software Foundation, either version 3 of the License, or\n";
	echo " * (at your option) any later version.\n";
	echo " * \n";
	echo " *  This program is distributed in the hope that it will be useful,\n";
	echo " * but WITHOUT ANY WARRANTY; without even the implied warranty of\n";
	echo " * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\n";
	echo " * GNU General Public License for more details.\n";
	echo " * \n";
	echo " *  You should have received a copy of the GNU General Public License\n";
	echo " * along with this program.  If not, see <http://www.gnu.org/licenses/>.\n";
	echo " * \n";
	echo " *  If you use this software, you agree to the open\n";
	echo " * source aspects of the policy at:\n";
	echo " * <http://www.thebestcomputerhelp.com/node/31>.\n";
	echo " * \n";
	echo " *  If any statements in the GNU General Public License conflict with\n";
	echo " * the open source aspects of the software policy listed above, my policy\n";
	echo " * will override.\n";
	echo " **********************************************************************/\n";
	
	ini_set("E_NOTICE", 0);
	$i = 0;
	$myFile = 'servers';
	$fh = fopen($myFile, 'r');
	$lines = fread($fh, filesize($myFile)) or die("No servers available for connection");
	$lines = explode("\n", $lines);
	fclose($fh);
	foreach($lines as $server)
	{
		if (trim($server) != null) {
			$servers[$i] = $server;
			$i++;
		}
	}

	foreach($servers as $list)
	{
		echo $list."\n";
	}

	set_time_limit(0);
	class IRCBot {
		var $socket = null;
		var $ex = array();
		var $motd = null;
		var $channels = null;
		var $runs = null;
		var $links = null;
		var $startTime = null;
		
		function __construct()
		{
			global $servers;
			$this->runs++;
			if ($this->runs <= count($servers)) {
				unset($this->startTime);
				sleep(1);
				$ip = gethostbyname($servers[($this->runs - 1)]);
				if ($ip) {
					$this->socket = fsockopen(gethostbyname($servers[($this->runs - 1)]), '6667');
					sleep(1);
					if (!$this->socket) {
						$this->__construct();
					}
					$this->send_data('USER', $GLOBALS['ident'].' 8 * :'.$GLOBALS['realname']);
					$this->send_data('NICK', $GLOBALS['nickname']);
					$this->startTime = time();
					$this->main();
				}
				else {
					$this->__construct();
				}
			}
			else {
				unset($this->runs);
				$servers = null;
				$i = 0;
				$myFile = 'servers';
				$fh = fopen($myFile, 'r');
				$lines = fread($fh, filesize($myFile)) or die("No servers available for connection");
				$lines = explode("\n", $lines);
				fclose($fh);
				foreach($lines as $server)
				{
					if (trim($server) != null) {
						$servers[$i] = $server;
						$i++;
					}
				}
				sleep(3600);
				$this->__construct();
			}
		}
		function main()
		{
			while (true) {
				global $servers;
				if (!$this->socket) {
					$this->__construct();
				}
				$data = fgets($this->socket, 4096);
				if (!$data) {
					$this->__construct();
				}
				flush();
				$this->ex = explode(' ', $data);
				echo $data;
				
				if ($this->ex[0] == 'PING')
				{
					$this->send_data('PONG', $this->ex[1]);
				}
				if ($this->ex[1] == '433')
				{
					$this->send_data('NICK', $GLOBALS['nickname'].'-'.rand(1,100));
				}
				if ($this->ex[1] == '266')
				{
					$users = split($this->ex[2].' :', $data);
					$users = $users[1];
					$fh = fopen($GLOBALS['initialDir'].date('m').'-'.date('d').'-'.date('Y').'-'.$servers[($this->runs - 1)]."-stats.txt", 'w');
					fwrite($fh, $users);
					fclose($fh);
				}
				if ($this->ex[1] == '322')
				{
					$count = count($this->channels);
					$this->channels[$count] = split($this->ex[2].' ', $data);
					$this->channels[$count] = $this->channels[$count][1];
				}
				if ($this->ex[1] == '323')
				{
					$fh = fopen($GLOBALS['initialDir'].date('m').'-'.date('d').'-'.date('Y').'-'.$servers[($this->runs - 1)]."-channels.txt", 'w');
					fwrite($fh, implode(NULL, $this->channels));
					fclose($fh);
					unset($this->channels);
				
					$this->send_data('QUIT', ':'.$GLOBALS['quitmsg']);
					sleep(1);
					fclose($this->socket);
					$this->motd = null;
					$this->channels = null;
					$this->links = null;
					$this->startTime = null;
					$this->__construct();
				}
				if ($this->ex[1] == '364')
				{
					$this->links[count($this->links)] = $this->ex[3];
				}
				if ($this->ex[1] == '365')
				{
					if (count($this->links) > 1) {
						$this->links = implode("\n", $this->links);
					}
					else {
						$this->links = $this->links[0];
					}
					$fh = fopen($GLOBALS['initialDir'].date('m').'-'.date('d').'-'.date('Y').'-'.$servers[($this->runs - 1)]."-servers.txt", 'w');
					fwrite($fh, $this->links);
					fclose($fh);
					unset($this->links);
					$this->send_data('LIST');
				}
				if ($this->ex[1] == '372' || $this->ex[1] == '422')
				{
					$motd = split($this->ex[2].' :- ', $data);
					$this->motd[count($this->motd)] = str_ireplace("\r", "", str_ireplace("\n", "", $motd[1]));
				}
				if ($this->ex[1] == '376' || $this->ex[1] == '422')
				{
					$this->motd = implode("\r\n", $this->motd);
					$fh = fopen($GLOBALS['initialDir'].date('m').'-'.date('d').'-'.date('Y').'-'.$servers[($this->runs - 1)]."-motd.txt", 'w');
					fwrite($fh, $this->motd);
					fclose($fh);
					unset($this->motd);
					$this->send_data('LINKS');
				}
			}
		}
		function send_data($cmd, $msg = null)
		{
			fputs($this->socket, $cmd.' '.$msg."\r\n");
			echo $cmd.' '.$msg."\r\n";
		}
	}
	
	$bot = new IRCBot();
?>