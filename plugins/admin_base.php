<?php
$this->bindCmd("shutdown",function($event){
	if(!$this->checkAdmin($event->user->mask))
	{
		$this->send("PRIVMSG ".$event->target." :".$event->user->nickname.": You do not have permission to shut me down.");
		return;
	}
	$this->running = false;
	$this->send("PRIVMSG ".$event->target." :".$event->user->nickname.": Shutting down...");
	$this->send("QUIT :Shutdown command called by ".$event->user->nickname);
});