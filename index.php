<?php

require_once 'bootstrap.php';

$app = new App();
$userInfo = $app->getUserInfo();

if (!$userInfo) {
    header('Location: register.php');
    exit();
}

// get applications
$conn = $app->getConnection();
$stmt = $conn->prepare(<<<SQL
    SELECT a.id id, r.name room_name, s.id status_id, s.name status_name, a.preferred_time preferred_time FROM application a
    JOIN room r ON a.room_id = r.id
    JOIN status s ON s.id = a.status
    WHERE a.user_id = ?
    ORDER BY a.preferred_time
    SQL);
$stmt->bind_param('i', $userInfo['userId']);
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// get feedbacks
$stmt = $conn->prepare(<<<SQL
        SELECT
            f.id f_id,
            f.content f_content,
            a.id a_id,
            r.name r_name,
            f.viewed f_viewed
        FROM feedback f
        JOIN application a ON f.application_id = a.id
        JOIN user u ON a.user_id = u.id
        JOIN room r On a.room_id = r.id
        WHERE u.id = ?
        LIMIT 10;
    SQL);
$stmt->bind_param('i', $userInfo['userId']);
$stmt->execute();
$feedbacks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>

<html>
    <head>
        <title>Главная</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    </head>
    <body>
        <h1>Личный кабинет</h1>
        <div class="text-center">
            <a href="submit.php" class="btn btn-primary">Оставить заявку</a>
            <?php if ($userInfo['isAdmin']): ?>
                <a href="admin.php" class="btn btn-secondary">Админская панель</a>
            <?php endif ?>
        </div>
        <h2>Мои заявки: </h2>
        <hr>
        <div>
            <?php if (empty($applications)): ?>
                <p>Вы еще не создали ни одной заявки</p>
            <?php else: ?>
                <?php foreach ($applications as $app): ?>
                    <div>
                        <p class="h3"><u><?= $app['room_name'] ?></u></p>
                        <p><b>Желаемое время:<b> <?= $app['preferred_time'] ?></p>
                        <p><b>Статус: </b> <?= $app['status_name'] ?></p>
                        <?php if ($app['status_id'] >= 2): ?>
                            <a href="feedback.php/?applicationId=<?= $app['id'] ?>" class="btn btn-secondary">Оставить отзыв</a>
                        <?php endif ?>
                        <hr>
                    </div>
                <?php endforeach ?>
            <?php endif ?>
        </div>
        <h2>Мои отзывы: </h2>
        <hr>
        <div>
            <?php if (empty($feedbacks)): ?>
                <p>Вы еще не оставили ни одного отзыва</p>
            <?php else: ?>
                <?php foreach ($feedbacks as $fb): ?>
                    <div>
                        <p><b>ID заявки: <b> <?= $fb['a_id'] ?></p>
                        <p><b>Помещение: <b> <?= $fb['r_name'] ?></p>
                        <p><b>Текст: <b> <?= $fb['f_content'] ?></p>
                        <p><b>Просмотрено: <b> <?= $fb['f_viewed'] ? 'Да' : 'Нет' ?></p>
                        <hr>
                    </div>
                <?php endforeach ?>
            <?php endif ?>
        </div>
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    </body>
</html>
