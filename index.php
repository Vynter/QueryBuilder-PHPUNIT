<?php

use App\QueryBuilder;

require 'vendor/autoload.php';
/* test */
$int = new QueryBuilder();
$z = $int->from('users', 'usr')->orderBy('name', 'DESC')->orderBy('id', 'ASC')->limit(5)->toSQL(); //pour appelé une function dans une autre sur la meme ligne il faut que la premier retourne l'instance en elle meme(return $this);
var_dump($z);