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
}