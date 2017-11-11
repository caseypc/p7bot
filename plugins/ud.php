<?php
$this->bindCmd("ud",function($event){
	try
	{
		$json = json_decode(file_get_contents("https://api.urbandictionary.com/v0/define?term=".urlencode(implode(" ", $event->arguments))));
		if(is_object($json) && isset($json->list[0]->word))
		{
			$this->send("PRIVMSG " . $event->target . " :\002" . $json->list[0]->word . "\002: " . $json->list[0]->definition);
		} else {
			$this->send("PRIVMSG " . $event->target . " :\002Definition not found!\002");
		}
	} catch(Exception $e)
	{
		$this->send("PRIVMSG ".$event->target." :An unknown error occurred");
	}
});