<?php
$this->bindCmd('cmds',function($event)
{
	try
	{
		$cmds = array();
		foreach ($this->command_binds as $command => $callback)
		{
			$cmds[] = $command;
		}
		$this->send("PRIVMSG ".$event->target." :\x0310[- \x0315COMMANDS\x0310 -]\x03 ".implode(', ', $cmds));
	} catch(Exception $e)
	{
		$this->send("PRIVMSG ".$event->target." :An unknown error occurred");
	}
});
