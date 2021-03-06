<?php
/*
 * Anti-Spam plugin for p7bot
 * Scores users based on various qualities of their messages.
 * Commands:
 * spam.score       Retrieves the score of the given user. If no user is given retrieves score for requesting user.
 *                  Example: `!spam.score Nickname`
 * spam.setScore    Sets the score of the given user to the given score
 *                  Example: `!spam.setScore Nickname 0`
 */
$this->messageBuffer = array();
$this->userScores = array();
try
{
	$this->universalSpamFilters = json_decode(file_get_contents($this->config->antispam['ufilter']));
	foreach($this->universalSpamFilters as $filter)
	{
		echo "Loaded Filter: \"".$filter->string."\";".$filter->score.";\n";
	}
} catch(Exception $e)
{
	echo "Could not get spam definitions!\n";
	$this->universalSpamFilters = null;
}

$this->bindPing(function(){
	if($this->config->antispam['ufilter'] == null) { return; }
	try
	{
		$this->universalSpamFilters = json_decode(file_get_contents($this->config->antispam['ufilter']));
		foreach($this->universalSpamFilters as $filter)
		{
			echo "Loaded Filter: \"".$filter->string."\";".$filter->score.";\n";
		}
	} catch(Exception $e)
	{
		echo "Could not get spam definitions!\n";
		$this->universalSpamFilters = null;
	}
});

$this->bindCmd("spam.score", function($event){
	if(!$this->checkAdmin($event->user->mask))
	{
		$this->send("PRIVMSG ".$event->target." :".$event->user->nickname.": You do not have permission to use this command!");
		return;
	}
	if(isset($event->arguments[0]))
	{
		$who = $event->arguments[0];
	} else {
		$who = $event->user->nickname;
	}

	if(!isset($this->userScores[$who]))
	{
		// user has no score
		$this->send("PRIVMSG ".$event->target." :".$who." has not been scored yet.");
		return;
	}
	$this->send("PRIVMSG ".$event->target." :Spam score for ".$who." is ".$this->userScores[$who]."%");
});

$this->bindCmd("spam.setscore", function($event){
	if(!$this->checkAdmin($event->user->mask))
	{
		$this->send("PRIVMSG ".$event->target." :".$event->user->nickname.": You do not have permission to use this command!");
		return;
	}

	if(isset($event->arguments[0]))
	{
		$who = $event->arguments[0];
	} else {
		$this->send("PRIVMSG ".$event->target." :".$event->user->nickname.": \002USAGE:\002 ".$this->config->bot['trigger']."spam.setscore <nickname> <score>");
		return;
	}

	if(isset($event->arguments[1]))
	{
		$value = $event->arguments[1];
	} else {
		$this->send("PRIVMSG ".$event->target." :".$event->user->nickname.": \002USAGE:\002 ".$this->config->bot['trigger']."spam.setscore <nickname> <score>");
		return;
	}

	$this->userScores[$who]=$value;
	$this->send("PRIVMSG ".$event->target." :Spam score for ".$who." is now ".$this->userScores[$who]."%");
});

$this->bindMsg(function($event){
	if(!isset($this->userScores[$event->user->nickname]) || $this->userScores[$event->user->nickname] <= -1)
	{
		$this->userScores[$event->user->nickname]=0;
	}

	// exempt admins from spam checks.
	if($this->checkAdmin($event->user->mask))
	{
		return;
	}

	// check exempts
	foreach($this->config->antispam['exempts'] as $mask)
	{
		if(fnmatch(strtolower($mask), strtolower($event->user->mask)))
		{
			return;
		}
	}

	/*
	 * Check spam definitions from configuration file, as well as universal definitions from given URL.
	 */
	if(is_array($this->universalSpamFilters))
	{
		foreach($this->universalSpamFilters as $filter)
		{
			if(preg_match($filter->string, $event->message))
			{
				$this->userScores[$event->user->nickname]=$this->userScores[$event->user->nickname]+$filter->score;
			}
		}
	}

	foreach($this->config->antispam['filters'] as $filter)
	{
		if(preg_match($filter['string'], $event->message))
		{
			$this->userScores[$event->user->nickname]=$this->userScores[$event->user->nickname]+$filter['score'];
		}
	}

	/*
	 * Check similarity between message and nick list to determine if message may be a mass-highlight.
	 * The method here will most likely be changed later on.
	 */
	similar_text($event->message, $this->nicklist[$event->target], $highlightPercent);
	if($highlightPercent >= 50)
	{
		$this->userScores[$event->user->nickname]=110;
	}

	$caps = preg_match_all("/([A-Z0-9])/", str_replace(" ", "", $event->message), $m);
	//$caps = count($m);
	$length = strlen($event->message);
	$percent_caps = (100/$length)*$caps;
	echo $event->user->nickname." / ".$caps." / ".$length." / ".$percent_caps."%\n";

	foreach($this->config->antispam['caps_scoring'] as $pcaps => $cscore)
	{
		if ($length >= $this->config->antispam['caps_min_length'] && $percent_caps >= $pcaps)
		{
			$this->userScores[$event->user->nickname]=$this->userScores[$event->user->nickname]+$cscore;
		}
	}

	if(substr($event->message, 0, 1) != $this->config->bot['trigger'])
	{
		$percent = 0;
		if (!isset($this->userScores[$event->user->nickname]))
		{
			$this->userScores[$event->user->nickname] = 0;
		}
		if (isset($this->messageBuffer[$event->user->nickname]))
		{
			similar_text($this->messageBuffer[$event->user->nickname], $event->message, $percent);
			if ($percent >= $this->config->antispam['similarity_percent'])
			{
				$this->userScores[$event->user->nickname] = $this->userScores[$event->user->nickname] + $this->config->antispam['similarity_add_points'];
			} else
			{
				$this->userScores[$event->user->nickname] = $this->userScores[$event->user->nickname] - $this->config->antispam['similarity_rem_points'];;
			}
		}
	}
	if($this->userScores[$event->user->nickname] >= 100)
	{
		$this->send("MODE ".$event->target." +b *!*@".$event->user->hostname);
		$this->send("KICK ".$event->target." ".$event->user->nickname." :You have been banned from ".$event->target." for triggering spam detection.");
		$this->userScores[$event->user->nickname]=0;
	} elseif($this->userScores[$event->user->nickname] >= $this->config->antispam['warn_score'])
	{
		$this->send("NOTICE ".$event->user->nickname." :".$this->config->antispam['warn_msg']);
	}
	$this->messageBuffer[$event->user->nickname]=$event->message;

	if(!isset($this->userScores[$event->user->nickname]) || $this->userScores[$event->user->nickname] <= -1)
	{
		$this->userScores[$event->user->nickname]=0;
	}
});