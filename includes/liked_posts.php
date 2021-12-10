<?php

if($isAuth) {
    redirect();
}

include_once "functions.php";

$posts = get_liked_posts();
$title = 'Понравившиеся твиты';

include_once "header.php";
include_once "posts.php";
include_once "footer.php";