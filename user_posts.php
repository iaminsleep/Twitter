<?php

include_once "includes/functions.php";

$error = get_error_message();

if (isset($_SESSION['user']['id'])) {
    $id = $_SESSION['user']['id']; //если пользователь авторизован, то берем id из сессии
}
else if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id']; //если нет, то id берется из ссылки в адресной строке
}
else {
    $id = 0; //если ни то, ни другое, то id=0, которое перекидывает на главную страницу
}

$posts = get_posts($id);

$title = 'Твиты пользователя';
if(!empty($posts)) $title = 'Твиты @' . $posts[0]['login'];


include_once "includes/header.php";
include_once "includes/tweet_form.php";
include_once "includes/posts.php";
include_once "includes/footer.php";