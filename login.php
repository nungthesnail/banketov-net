<?php

require_once 'bootstrap.php';

$app = new App();
$view = new View();

// handle authentication & authorization
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // validate form (not completed)
    if (!isset($_POST['login']))
        $view->reloadWithFlash('Не указан логин', 'login.php');
    if (!isset($_POST['password']))
        $view->reloadWithFlash('Не указан пароль', 'login.php');
    
    // open connection and try to get user data
    $conn = $app->getConnection();
    $stmt = $conn->prepare('SELECT id, password_hash, is_admin FROM user WHERE login = ?');
    $stmt->bind_param('s', $_POST['login']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    // verify auth data
    if (!$result || !password_verify($_POST['password'], $result['password_hash']))
        $view->reloadWithFlash('Пользователь не найден или неправильный пароль', 'login.php');

    // issue claims and redirect to main page
    $_SESSION['userId'] = $result['id'];
    $_SESSION['isAdmin'] = $result['is_admin'];
    header('Location: index.php');
    exit();
}

?>

<!DOCTYPE html>

<html>
    <head>
        <title>Регистрация</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    </head>
    <body>
        <h1>Войти</h1>
        <form method="POST">
            <div>
                <label for="login">Логин: </label>
                <input type="text" id="login" name="login" minlength="6" required>
            </div>
            <div>
                <label for="password">Пароль: </label>
                <input type="password" id="password" name="password" minlength="8" required>
            </div>
            <input type="submit" value="Войти">
        </form>

        <?php $view->flash() ?>
        <div class="text-center"><a href="register.php">Зарегистрироваться</a></div>

        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    </body>
</html>
