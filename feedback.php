<?php

require_once 'bootstrap.php';

$app = new App();
$view = new View();
$userInfo = $app->getUserInfo();

if (!$userInfo) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = $app->getConnection();
    $stmt = $conn->prepare('INSERT INTO feedback (application_id, content) VALUES (?,?)');
    $stmt->bind_param('is', $_POST['applicationId'], $_POST['content']);
    $stmt->execute();
    header('Location: /index.php');
    exit();
}

?>

<!DOCTYPE html>

<html>
    <head>
        <title>Оставить отзыв</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    </head>
    <body>
        <h1>Оставить отзыв на заявку #<?= $_GET['applicationId'] ?></h1>
        <a class="btn btn-secondary" href="/index.php">На главную</a>

        <form method="POST">
            <div>
                <label>Отзыв: </label>
            </div>
            <div>
                <textarea name="content" rows="5" cols="100" maxlength="512" required></textarea>
            </div>
            <input type="submit" class="btn btn-primary" value="Отправить">
            <input type="hidden" name="applicationId" value="<?= $_GET['applicationId'] ?>">
        </form>

        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    </body>
</html>
