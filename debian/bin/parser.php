#!/usr/bin/php
<?php
require_once realpath(dirname(__FILE__)).'/Parser.class.php';

if($argc < 2) return;

$In = $argv[1];

is_dir($In) ?  Parser::get($In) : new Parser($In);
?>