<?php

require_once 'bootstrap.php';

$app = new App();
$view = new View();
$userInfo = $app->getUserInfo();

if (!$userInfo) {
    header('Location: login.php');
    exit();
}

$conn = $app->getConnection();

// create application
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['roomId']) || !isset($_POST['preferredTime']) || !isset($_POST['paymentMethod']))
        $view->reloadWithFlash('Не указано помещение, желаемое время или способ оплаты', 'submit.php');

    $stmt = $conn->prepare('INSERT INTO application (user_id, room_id, status, preferred_time, payment_method) VALUES (?,?,1,?,?)');
    $stmt->bind_param('iisi', $userInfo['userId'], $_POST['roomId'], $_POST['preferredTime'], $_POST['paymentMethod']);
    if (!$stmt->execute())
        $view->reloadWithFlash('Не удалось оставить заявку', 'submit.php');

    header('Location: index.php');
    exit();
}

$rooms = $conn->query('SELECT id, name FROM room ORDER BY id')->fetch_all(MYSQLI_ASSOC);
$paymentMethods = $conn->query('SELECT id, name FROM payment_method ORDER BY id')->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>

<html>
    <head>
        <title>Оставить заявку</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    </head>
    <body>
        <h1>Оставить заявку</h1>

        <form method="POST">
            <div>
                <label>Выберите помещение: </label>
                <select class="form-select" name="roomId">
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= $room['id'] ?>"><?= $room['name'] ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div>
                <label>Желаемое время: </label>
                <input type="datetime" name="preferredTime" required>
            </div>
            <div>
                <label>Способ оплаты: </label>
                <select class="form-select" name="paymentMethod">
                    <?php foreach ($paymentMethods as $method): ?>
                        <option value="<?= $method['id'] ?>"><?= $method['name'] ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <input type="submit" class="btn btn-primary" value="Отправить заявку">
        </form>

        <?php $view->flash() ?>

        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    </body>
</html>
