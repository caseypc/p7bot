<?php
/*
 * Anti-Spam plugin for FactoryBot
 * Scores users based on various qualities of their messages.
 * Commands:
 * ss   Retrieves the score of the given user. If no user is given retrieves score for requesting user.
 *      Example: `!ss Nickname`
 */
$this->messageBuffer = array();
$this->userScores = array();

$this->bindCmd("ss", function($event){
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
	$this->send("PRIVMSG ".$event->target." :Spam score for ".$who." is ".$this->userScores[$who]);
});

$this->bindMsg(function($event){
	if($this->userScores[$event->user->nickname] <= -1)
	{
		$this->userScores[$event->user->nickname]=0;
	}
	$caps = preg_match_all("/([A-Z0-9])/", str_replace(" ", "", $event->message), $m);
	//$caps = count($m);
	$length = strlen($event->message);
	$percent_caps = (100/$length)*$caps;
	echo $event->user->nickname." / ".$caps." / ".$length." / ".$percent_caps."%\n";
	if($length >= 10 && $percent_caps >= 90)
	{
		$this->userScores[$event->user->nickname]=$this->userScores[$event->user->nickname]+100;
	}elseif($length >= 10 && $percent_caps >= 50)
	{
		$this->userScores[$event->user->nickname]=$this->userScores[$event->user->nickname]+25;
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
			if ($percent >= 50)
			{
				$this->userScores[$event->user->nickname] = $this->userScores[$event->user->nickname] + 25;
			} else
			{
				$this->userScores[$event->user->nickname] = $this->userScores[$event->user->nickname] - 10;
			}
		}
	}
	if($this->userScores[$event->user->nickname] >= 100)
	{
		$this->send("MODE ".$event->target." +b *!*@".$event->user->hostname);
		$this->send("KICK ".$event->target." ".$event->user->nickname." :You have been banned from ".$event->target." for triggering spam detection.");
		$this->userScores[$event->user->nickname]=0;
	} elseif($this->userScores[$event->user->nickname] >= 70)
	{
		$this->send("NOTICE ".$event->user->nickname." :Your spam score is getting dangerously high, please cool it down.");
	}
	$this->messageBuffer[$event->user->nickname]=$event->message;
	//echo "[PROTECT]\tUser=".$event->user->nickname.";Score=".$this->userScores[$event->user->nickname].";Similarity=".$percent.";\n";
});