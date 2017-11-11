<?php

/*
 * category: <a href=\"\/browse\/.*?\" title=\"More from this category\">Games</a>
 * <a href="/torrent/18630646/Football_Manager_2016_[Test_Game]_-_Update_Version" class="detLink" title="Details for Football Manager 2016 [Test Game] - Update Version">Football Manager 2016 [Test Game] - Update Version</a>
 */
$this->bindCmd("tpb",function($event){
	try
	{
		$page = file_get_contents("https://thepiratebay.org/search/".urlencode(implode(" ", $event->arguments)));
		if(preg_match("/\<a href=\"\/browse\/.*?\" title=\"More from this category\"\>(.*?)<\/a>/i", $page, $m))
		{
			$category = $m[1];
		}
		if(preg_match("/\<a href=\"(.*?)\" class=\"detLink\" title=\".*?\"\>(.*?)\<\/a\>/i", $page, $m))
		{
			$link = "https://thepiratebay.org".$m[1];
			$title = $m[2];
		}

		if(preg_match("/<td align=\"right\">(.*?)<\/td>\n[\s]+<td align=\"right\">(.*?)<\/td>/i", $page, $m))
		{
			$seeds = $m[1];
			$leechs = $m[2];
		}


		if(isset($title) && isset($category) && isset($link) && isset($seeds) && isset($leechs))
		{
			$this->send("PRIVMSG ".$event->target." :\002[⤊:".$seeds." / ⤋:".$leechs."][".$category."]\002 ".$title." @ ".$link);
		} else {
			$this->send("PRIVMSG ".$event->target." :".$event->user->nickname.": Sorry I could not find that torrent.");
		}
	} catch(Exception $e)
	{
		$this->send("PRIVMSG ".$event->target." :An unknown error occurred");
	}
});