<?php

require_once 'bootstrap.php';

$app = new App();
$view = new View();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // validate form (not completed)
    if (!isset($_POST['login']))
        $view->reloadWithFlash('Неправильный формат логина', 'register.php');
    if (!isset($_POST['password']))
        $view->reloadWithFlash('Неправильный формат пароля', 'register.php');
    if (!isset($_POST['full_name']))
        $view->reloadWithFlash('Неправильный формат ФИО', 'register.php');
    if (!isset($_POST['phone']))
        $view->reloadWithFlash('Неправильный формат номера телефона', 'register.php');
    if (!isset($_POST['email']))
        $view->reloadWithFlash('Неправильный формат электронной почты', 'register.php');

    // open connection and transaction
    $conn = $app->getConnection();
    $conn->begin_transaction();

    // check user doesn't exist
    $stmt = $conn->prepare('SELECT 1 FROM user WHERE login = ?');
    $stmt->bind_param('s', $_POST['login']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_column()) {
        $conn->rollback();
        $view->reloadWithFlash('Пользователь с таким логином уже существует', 'register.php');
    }

    // create user
    $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $stmt = $conn->prepare('INSERT INTO user (login, password_hash, full_name, phone, email) VALUES (?,?,?,?,?)');
    $stmt->bind_param('sssss', $_POST['login'], $password_hash, $_POST['full_name'], $_POST['phone'], $_POST['email']);
    if (!$stmt->execute())
        $view->reloadWithFlash('Не удалось создать пользователя', 'register.php');

    // commit transaction and redirect to login page
    $conn->commit();
    header('Location: login.php');
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
        <h1>Зарегистрироваться</h1>
        <form method="POST">
            <div>
                <label for="login">Логин: </label>
                <input type="text" id="login" name="login" pattern="[A-Za-z0-9]{6,}" required>
            </div>
            <div>
                <label for="password">Пароль: </label>
                <input type="password" id="password" name="password" minlength="8" required>
            </div>
            <div>
                <label for="full_name">ФИО: </label>
                <input type="text" id="full_name" name="full_name" maxlength="256" required>
            </div>
            <div>
                <label for="phone">Номер телефона: </label>
                <input type="text" id="phone" name="phone" placeholder="8(800)555-35-35" required>
            </div>
            <div>
                <label for="email">Электронная почта: </label>
                <input type="email" id="email" name="email" required>
            </div>
            <input type="submit" value="Зарегистрироваться">
        </form>

        <?php $view->flash() ?>
        <div class="text-center"><a href="login.php">Уже есть аккаунт? Войдите</a></div>

        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    </body>
</html>
