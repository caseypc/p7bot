<?php
class Config
{
	public $network = array(
		"hostname"  => "irc.buddy.im",
		"port"      => 6667,
		"ssl"       => false,
		"ssl_cert"  => "ssl/cert",
		"ssl_key"   => "ssl/key",
		"channels"  => array(
			"#lobby"
		)
	);

	public $bot = array(
		"nickname"  => "p7bot",
		"username"  => "p7bot",
		"realname"  => "https://github.com/xnite/p7bot",
		"trigger"   => "!"
	);

	public $admins = array(
		"Nickname"     => array(
			"*!*@*.example.com",
			"*!*@example.com"
		)
	);

	public $antispam = array(
		"warn_score"    => 75,
		"warning_msg"   => "Your spam score is getting dangerously high. Please cool it down.",
		/* FILTERS */
		"ufilter" => "example-filters.json", // You can define a URL here to the definitions file. Please see the example-filters.json file to learn how to format your definitions
		"filters" => array(
			array(
				"string"    => "/^saying this exact string will bring down the ban hammer$/i",
				"score"     => 110
			)
		),

		/* CAPS DETECTION */
		"caps_min_length"    => 10,
		"caps_scoring" => array(
			80 => 110, // if message is 80+% CAPS then add 110 to score (immediate kick+ban)
			50 => 35,
			25 => 20
		),
		/* SIMILARITY */
		"similarity_percent" => 30,
		"similarity_add_points" => 40,
		"similarity_rem_points" => 20,
		/* EXEMPTS */
		"exempts"   => array(
			"*!*@*.example.com",
			"*!*@example.com"
		)
	);
}