<?php
/*
 * Rename this file to Config.php once you have finished tweaking it.
 */
class Config
{
	public $network = array(
		"hostname"  => "irc.buddy.im",
		"port"      => 6667,
		"channels"  => array(
			"#lobby",
			"#help"
		)
	);

	public $bot = array(
		"nickname"  => "p7bot",
		"username"  => "p7bot",
		"realname"  => "https://github.com/xnite/p7bot",
		"trigger"   => "!"
	);

	public $admins = array(
		"Nickname"     => array("*!*@Nickname/Registered/Member", "*!*@*.example.com")
	);

	public $antispam = array(
		"warn_score"    => 75,
		"warning_msg"   => "Your spam score is getting dangerously high. Please cool it down.",
		/* CAPS DETECTION */
		"caps_min_length"    => 10,
		"caps_scoring" => array(
			80 => 200, // if message is 80+% CAPS then add 200 to score (immediate kick+ban)
			50 => 25,
			25 => 10
		),
		/* SIMILARITY */
		"similarity_percent" => 50,
		"similarity_add_points" => 30,
		"similarity_rem_points" => 10,
		/* EXEMPTS */
		"exempts"   => array(
			"*!*@Nickname/Registered/Member",
			"*!*@*.example.com"
		)
	);
}