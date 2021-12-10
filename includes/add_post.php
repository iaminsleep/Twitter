<?php
include_once "functions.php";

if(!$isAuth) {
    redirect();
}

if(isset($_POST['text']) && !empty($_POST['text']) && isset($_POST['image'])) {
    if(!add_post($_POST['text'], $_POST['image'])) {
        $_SESSION['error-message'] = 'Во время добавления поста что-то пошло не так :(';
    }
}

redirect('user_posts.php');