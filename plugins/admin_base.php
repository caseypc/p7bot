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

$this->bindCmd("join",function($event){
	if(!$this->checkAdmin($event->user->mask))
	{
		$this->send("PRIVMSG ".$event->target." :".$event->user->nickname.": You do not have permission to do that!");
		return;
	}
	$this->send("JOIN ".$event->arguments[0]);
});

$this->bindCmd("part",function($event){
	if(!$this->checkAdmin($event->user->mask))
	{
		$this->send("PRIVMSG ".$event->target." :".$event->user->nickname.": You do not have permission to do that!");
		return;
	}
	$this->send("PART ".$event->arguments[0]." /PART command called by ".$event->user->nickname);
});

$this->bindCmd("nick",function($event){
	if(!$this->checkAdmin($event->user->mask))
	{
		$this->send("PRIVMSG ".$event->target." :".$event->user->nickname.": You do not have permission to do that!");
		return;
	}
	$this->send("NICK ".$event->arguments[0]);
});