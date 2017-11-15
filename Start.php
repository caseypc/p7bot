<?php
class Start
{
	private $config;
	private $socket;
	private $command_binds  = array();
	private $privmsg_binds  = array();
	private $nick_binds     = array();
	private $ping_binds     = array();
	private $nicklist       = array();
	private $running        = true;
	private $me;
	public function __construct()
	{
		require_once("Config.php");
		$this->config = new Config();
		$this->me = new class(){};
		$this->me->nickname = $this->config->bot['nickname'];
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

		while($this->running == true)
		{
			try
			{
				/*
				$this->socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
				socket_connect($this->socket, $this->config->network['hostname'], $this->config->network['port']) or die("Could not connect to host: " . $this->config->network['hostname'] . ":" . $this->config->network['port'] . "\n");
				*/
				echo "Connecting";
				while (!is_resource($this->socket))
				{
					echo ".";
					sleep(3);
					if (isset($this->config->bot['ssl']) && $this->config->bot['ssl'] == true)
					{
						$context = stream_context_create();
						$result = stream_context_set_option($context, 'ssl', $this->config->bot['ssl_cert'], $this->config->bot['ssl_key']);
						$this->socket = fsockopen("ssl://" . $this->config->network['hostname'], $this->config->network['port'], $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
					} else
					{
						$this->socket = fsockopen("tcp://" . $this->config->network['hostname'], $this->config->network['port'], $errno, $errstr, 15);
					}
				}
				$this->send("NICK " . $this->config->bot['nickname']);
				$this->send("USER " . $this->config->bot['username'] . " " . $this->config->bot['username'] . " * " . $this->config->bot['realname']);
			}catch(Exception $e)
			{
				trigger_error($e->getMessage());
				exit();
			}

			while ($data = stream_get_line($this->socket, 1024, "\r\n"))
			{
				echo "[IN]\t" . $data."\n";
				$data = str_replace("\r\n", "", $data);
				// Handle PING
				if (preg_match("/^PING(.*?)$/i", $data, $m))
				{
					$this->send("PONG" . $m[1]);
					foreach ($this->ping_binds as $callback)
					{
						$callback();
					}
				}

				// Handle nick changes
				if(preg_match("/^:(.*?)!(.*?)@(.*?) NICK (.*?)$/i", $data, $m))
				{
					$eventObject = new class(){};
					$eventObject->user = new class(){};
					$eventObject->user->nickname = $m[1];
					$eventObject->user->username = $m[2];
					$eventObject->user->hostname = $m[3];
					$eventObject->oldNick = $m[1];
					$eventObject->newNick = $m[4];
					if(strtolower($m[1]) == strtolower($this->me->nickname))
					{
						$this->me->nickname = $m[4];
					}

					foreach ($this->nick_binds as $callback)
					{
						$callback($eventObject);
					}
				}

				// nick already in use
				if(preg_match("/^:(.*?) 433 * (.*?) :(.*?)$/i", $data, $m))
				{
					$this->me->nickname = $this->me->nickname."_";
					$this->send("NICK ".$this->me->nickname);
				}
				// handle end of /motd
				if(preg_match("/^:(.*?) 376 (.*?) :(.*?)$/i", $data, $m))
				{
					$this->me->server = $m[1];
					$this->send("JOIN " . implode(",", $this->config->network['channels']));
				}

				// handle /names
				if (preg_match("/^:(.*?) 353 (.*?) (.*?) (.*?) :(.*?)$/i", $data, $m))
				{
					$this->nicklist[$m[4]] = $m[5];
				}
				// Handle PRIVMSG
				if (preg_match("/^:(.*?)!(.*?)@(.*?) PRIVMSG (.*?) :(.*?)$/i", $data, $m))
				{
					$eventObject = new class(){};
					$eventObject->user = new class(){};
					$eventObject->user->nickname = $m[1];
					$eventObject->user->username = $m[2];
					$eventObject->user->hostname = $m[3];
					$eventObject->user->mask = $m[1] . "!" . $m[2] . "@" . $m[3];
					$eventObject->target = $m[4];
					$eventObject->message = $m[5];
					$args = explode(" ", $m[5]);
					$eventObject->command = $args[0];
					array_shift($args);
					$eventObject->arguments = $args;
					foreach ($this->privmsg_binds as $callback)
					{
						$callback($eventObject);
					}
					foreach ($this->command_binds as $command => $callback)
					{
						if (strtolower($command) == strtolower($eventObject->command))
						{
							$callback($eventObject);
						}
					}
				}
			}
			fclose($this->socket);
		}
	}

	public function send($message)
	{
		try
		{
			fwrite($this->socket, $message."\r\n", strlen($message."\r\n"));
			echo "[OUT]\t".$message."\n";
		} catch(Exception $e)
		{
			trigger_error($e->getMessage());
			return false;
		}
		return true;
	}

	public function bindPing($callback)
	{
		array_push($this->ping_binds, $callback);
	}

	public function bindNick($callback)
	{
		array_push($this->nick_binds, $callback);
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