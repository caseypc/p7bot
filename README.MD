# About p7bot
p7bot is a bot that I've written specifically for PHP 7.x.

It contains basic plugins such as ping, fml, urban dictionary and spam protection. It can be extended by plugins with little effort.

**PULL REQUESTS ARE WELCOME!!**

# Plugin Commands
## Admin Commands
**!cmds** Lists all commands.

**!shutdown** Shuts down the bot

**!join** Joins the bot to a channel. `!join <#channel>`

**!part** Parts the bot from a channel. `!part <#channel>`

## Ping
**!ping** Returns pong

## FML
**!fml** Returns a random quote from fmylife.com

## Urban Dictionary
**!ud** Returns the definition of a given word. `!ud <word or phrase>`

## Antispam
**!spam.score** Returns the current spam rating of the given nick name.

**Usage:** `!spam.score <nickname>`

**!spam.setScore** Sets the score of the given nickname to the given score.

**Usage:** `!spam.setScore <nickname> <0-100>`
