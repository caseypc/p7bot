# About p7bot
p7bot is a bot that I've written specifically for PHP 7.x.

It contains basic plugins such as ping, fml, urban dictionary and spam protection. It can be extended by plugins with little effort.

# Plugin Commands
## Ping
**!ping** Returns pong

## FML
**!fml** Returns a random quote from fmylife.com

## Urban Dictionary
**!ud** Returns the definition of a given word.

**Usage:** `!ud <word or phrase>`

## Antispam
**!spam.score** Returns the current spam rating of the given nick name.

**Usage:** `!spam.score <nickname>`

**!spam.setScore** Sets the score of the given nickname to the given score.

**Usage:** `!spam.setScore <nickname> <0-100>`