<?php
$this->bindCmd("ping",function($event){
	$this->send("PRIVMSG ".$event->target." :".$event->user->nickname.": PONG!");
});