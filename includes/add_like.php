<?php
include_once "functions.php";

if (!$isAuth) {
    redirect();
}

if (isset($_GET['id']) && !empty($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0) {
    if (!add_like($_GET['id'])) {
        $_SESSION['error-message'] = 'Во время добавления лайка что-то пошло не так :(';
    }
}

redirect();
