<?php

include_once "config.php";

function debug($var, $stop = false) {
    echo "<pre>";
    print_r($var);
    echo "<pre>";
    if($stop) die;
}

//функция редиректа, которая значительно облегчает верстку
function redirect($location = '') {
    header("Location: ".get_url($location)); //если значение пустое, перекидывает на главную страницу
    die;
}

function get_url($page = '') {
    return HOST . "/$page";
}

function get_page_title($title = '') {
    if(!empty($title)) {
        return SITE_NAME . " - $title";
    }
    else {
        return SITE_NAME;
    }
}

function db() {
    try {
        return new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS,
        [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
    catch (PDOException $err) {
        die($err->getMessage());
    }
}

function db_query($sql, $exec = false) {
    if(empty($sql)) return false;

    if($exec) return db()->exec($sql);

    return db()->query($sql);
}

function get_posts($user_id = 0, $sort = false) {
    $sorting = 'DESC';
    if($sort) $sorting = 'ASC';

    if($user_id > 0) {
        return db_query("SELECT posts.*, users.name, users.login, 
       users.avatar FROM `posts` JOIN `users` ON users.id = posts.user_id WHERE posts.user_id = $user_id;")->fetchAll();
    }
    else {
        return db_query("SELECT posts.*, users.name, users.login, 
       users.avatar FROM `posts` JOIN `users` ON users.id = posts.user_id ORDER BY `posts`.`date` $sorting")->fetchAll();
    }
}

function get_user_info($login) {
    return db_query("SELECT * FROM `users` WHERE `login` = '$login';")->fetch(); //fetch превращает полученные данные в массив
}

//добавляет пользователя в бд (можно объединить функции add_user и register по желанию)
function add_user($login , $pass) {
    $login = trim($login); //trim удаляет пробелы
    $name = ucfirst($login); //ucfirst делает первую букву логина заглавной
    $password = password_hash($pass, PASSWORD_DEFAULT);
    return db_query("INSERT INTO `users` (`id`, `login`, `pass`, `name`, `avatar`) 
                            VALUES (NULL, '$login', '$password', '$name', 'images/no_avatar.png');", true);
}

//$auth_data - переменная, куда будут вводиться данные из $_POST формы регистрации после отправки (name = login , pass и pass2)
function register_user($auth_data) {
    if(empty($auth_data)
        || !isset($auth_data['login']) || empty($auth_data['login'])
        || !isset($auth_data['pass']) || empty($auth_data['pass'])
        || !isset($auth_data['pass2']) || empty($auth_data['pass2'])
    ) return false;

    $user = get_user_info($auth_data['login']);

    //ошибки, если пользователь уже существует или пароли не совпадают
    if(!empty($user)) {
        $_SESSION['error-message'] = 'Пользователь с логином '.$auth_data['login'].' уже существует';
        redirect('register.php');
    }

    if($auth_data['pass'] !== $auth_data['pass2']) {
        $_SESSION['error-message'] = 'Пароли не совпадают';
        redirect('register.php');
    }

    if(strlen($auth_data['login']) > 25) {
        $_SESSION['error-message'] = 'Логин слишком длинный';
        redirect('register.php');
    }

    if(strlen($auth_data['login']) < 3) {
        $_SESSION['error-message'] = 'Логин слишком короткий';
        redirect('register.php');
    }

    if(strlen($auth_data['pass']) <= 5) {
        $_SESSION['error-message'] = 'Пароль слишком короткий';
        redirect('register.php');
    }

    if(strlen($auth_data['pass']) > 50) {
        $_SESSION['error-message'] = 'Пароль слишком длинный';
        redirect('register.php');
    }

    //если пользователь добавлен, перекинет на главную страницу
    if(add_user($auth_data['login'], $auth_data['pass'])) {
        $_SESSION['error-message'] = '';
        redirect();
    }
}

//авторизация
function login($auth_data) {
    if(empty($auth_data) || !isset($auth_data['login']) || empty($auth_data['login'])) return false;

    $user = get_user_info($auth_data['login']);

    if(empty($user)) {
        $_SESSION['error-message'] = 'Пользователь '.$auth_data['login'].' не найден';
        redirect();
    }

    //проверка двух зашифрованных паролей на идентичность, если совпадает то происходит переход на страницу пользователя
    if(password_verify($auth_data['pass'], $user['pass'])) {
        $_SESSION['user'] = $user;
        $_SESSION['error-message'] = '';
        redirect('user_posts.php?id='.$user['id']);
    }
    else {
        $_SESSION['error-message'] = 'Пароль неверный';
        redirect();
    }
}

function get_error_message() {
    $error = '';

    if(isset($_SESSION['error-message']) && !empty($_SESSION['error-message'])) {
        $error = $_SESSION['error-message'];
        $_SESSION['error-message'] = '';
    }

    return $error;
}

//переменная, которая проверяет, авторизирован ли пользователь
$isAuth = isset($_SESSION['user']['id']);

function add_post($text, $image) {
    $text = trim($text);

    if(mb_strlen($text) > 255 || str_word_count($text, 0, null) > 50) {
        $text = mb_substr($text , 0, 250).' ...';
    }

    $user_id = $_SESSION['user']['id'];
    $sql = "INSERT INTO `posts` (`id`, `user_id`, `text`, `image`) VALUES (NULL, $user_id, '$text', '$image');";
    return db_query($sql, true);
}

function delete_post($id) {
    if(is_numeric($id)) {
        if($id != 0) {
            $user_id = $_SESSION['user']['id'];
            $sql = "DELETE FROM `posts` WHERE `posts`.`id` = $id AND `user_id` = $user_id";
            return db_query($sql, true);
        }
    }
    else {
        redirect('user_posts.php');
    }
}

function get_likes_count($post_id) {
    //fetchColumn выдаст сразу число из столбца COUNT(*), вместо массива, что заметно облегчит работу
    if(empty($post_id)) return 0;

    return db_query("SELECT COUNT(*) FROM `likes` WHERE `post_id` = $post_id")->fetchColumn();

}

function is_post_liked($post_id) {
    $user_id = $_SESSION['user']['id'];
    if(empty($post_id)) return false;

    //rowCount() возвращает кол-во строк
    return db_query("SELECT * FROM `likes` WHERE `post_id` = $post_id AND `user_id` = $user_id;")->rowCount() > 0; //если строк из результата больше чем 0 (то есть лайк поставлен)
    //,то вернётся true, если лайк не поставлен то строки не будет (false)
}

function add_like($post_id) {
    $user_id = $_SESSION['user']['id'];
    if(empty($post_id)) return false;

    $sql = "INSERT INTO `likes` (`post_id`, `user_id`) VALUES ($post_id, $user_id);";
    return db_query($sql, true);
}

function delete_like($post_id) {
    if(empty($post_id)) return false;
    $user_id = $_SESSION['user']['id'];
    return db_query("DELETE FROM `likes` WHERE `post_id` = $post_id AND `user_id` = $user_id", true);
}

function get_liked_posts() {
    $user_id = $_SESSION['user']['id'];

    $sql = "SELECT posts.*, users.name, users.login, users.avatar FROM `likes` 
    JOIN `posts` ON posts.id = likes.post_id 
    JOIN `users` ON users.id = posts.user_id  
            WHERE likes.user_id = $user_id;";

    return db_query($sql)->fetchAll();
}