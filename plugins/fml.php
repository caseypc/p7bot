<?php
$this->bindCmd("fml",function($event){
	$html = file_get_contents("http://www.fmylife.com/random");
	if(preg_match("/Today(.*?)FML/i", $html, $m))
	{
		$this->send("PRIVMSG ".$event->target." :Today".html_entity_decode($m[1])."FML!");
	}
});