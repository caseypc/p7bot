<?php
class Start
{
	private $config;
	private $socket;
	private $command_binds = array();
	private $privmsg_binds = array();
	private $nicklist = array();
	public function __construct()
	{
		require_once("Config.php");
		$this->config = new Config();

		try
		{
			foreach(glob("plugins/*.php") as $plugin)
			{
				require_once($plugin);
			}
		} catch(Exception $e)
		{
			trigger_error($e->getMessage());
		}

		try
		{
			$this->socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
			socket_connect($this->socket, $this->config->network['hostname'], $this->config->network['port']) or die("Could not connect to host: " . $this->config->network['hostname'] . ":" . $this->config->network['port'] . "\n");
			$this->send("NICK ".$this->config->bot['nickname']);
			$this->send("USER ".$this->config->bot['username']." ".$this->config->bot['username']." * ".$this->config->bot['realname']);
			sleep(5);
			$this->send("JOIN ".implode(",", $this->config->network['channels']));
		} catch(Exception $e)
		{
			trigger_error($e->getMessage());
			exit();
		}

		while($data = socket_read($this->socket, 4096))
		{
			echo "[IN]\t".$data;
			$data = str_replace("\r\n", "", $data);
			// Handle PING
			if(preg_match("/^PING(.*?)$/i", $data, $m))
			{
				$this->send("PONG".$m[1]);
			}

			// handle /names
			if(preg_match("/:(.*?) 353 (.*?) (.*?) (.*?) :(.*?)/i", $data, $m))
			{
				$this->nicklist[$m[4]]=$m[5];
			}
			// Handle PRIVMSG
			if(preg_match("/^:(.*?)!(.*?)@(.*?) PRIVMSG (.*?) :(.*?)$/i", $data, $m))
			{
				$eventObject = new class(){};
				$eventObject->user = new class(){};
				$eventObject->user->nickname = $m[1];
				$eventObject->user->username = $m[2];
				$eventObject->user->hostname = $m[3];
				$eventObject->user->mask = $m[1]."!".$m[2]."@".$m[3];
				$eventObject->target = $m[4];
				$eventObject->message = $m[5];
				$args = explode(" ", $m[5]);
				$eventObject->command = $args[0];
				array_shift($args);
				$eventObject->arguments = $args;
				foreach($this->privmsg_binds as $callback)
				{
					$callback($eventObject);
				}
				foreach($this->command_binds as $command => $callback)
				{
					if(strtolower($command) == strtolower($eventObject->command))
					{
						$callback($eventObject);
					}
				}
			}
		}
		socket_close($this->socket);
	}

	public function send($message)
	{
		try
		{
			socket_write($this->socket, $message."\r\n", strlen($message."\r\n"));
			echo "[OUT]\t".$message."\n";
		} catch(Exception $e)
		{
			trigger_error($e->getMessage());
			return false;
		}
		return true;
	}

	public function bindCmd($command, $callback)
	{
		$this->command_binds[$this->config->bot['trigger'].$command] = $callback;
	}
	public function bindMsg($callback)
	{
		array_push($this->privmsg_binds, $callback);
	}

	public function checkAdmin($usermask)
	{
		foreach($this->config->admins as $admin)
		{
			foreach($admin as $mask)
			{
				if(fnmatch(strtolower($mask), strtolower($usermask)))
				{
					return true;
				}
			}
		}
		return false;
	}
}
$Bot = new Start();