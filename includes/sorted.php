<?php

include_once "functions.php";

$posts = get_posts(0, true);
$title = 'Главная страница';

include_once "header.php";
include_once "tweet_form.php";
include_once "posts.php";
include_once "footer.php";