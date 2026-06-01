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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['action']))
        $view->reloadWithFlash('Не удалось обработать действие', 'admin.php');

    if ($_POST['action'] === 'changeAppStatus') {
        $stmt = $conn->prepare('UPDATE application SET status = status + 1 WHERE id = ?');
        $stmt->bind_param('i', $_POST['applicationId']);
        if (!$stmt->execute())
            $view->reloadWithFlash('Не удалось обновить статус', 'admin.php');
    } else if ($_POST['action'] === 'viewFb') {
        $stmt = $conn->prepare('UPDATE feedback SET viewed = true WHERE id = ?');
        $stmt->bind_param('i', $_POST['fbId']);
        if (!$stmt->execute())
            $view->reloadWithFlash('Не удалось отметить отзыв прочитанным', 'admin.php');
    } else {
        $view->reloadWithFlash('Такого действия не существует', 'admin.php');
    }
}

$applications = $conn
    ->query(<<<SQL
        SELECT
            a.id a_id,
            a.preferred_time a_time,
            r.name r_name,
            u.login u_login,
            u.phone u_phone,
            u.email u_email,
            u.full_name u_full_name,
            s.id s_id,
            s.name s_name,
            pm.name pm_name
        FROM application a
        JOIN room r ON a.room_id = r.id
        JOIN user u ON a.user_id = u.id
        JOIN status s ON a.status = s.id
        JOIN payment_method pm ON a.payment_method = pm.id
        ORDER BY a.status
        LIMIT 25
    SQL)
    ->fetch_all(MYSQLI_ASSOC);

$feedbacks = $conn
    ->query(<<<SQL
        SELECT
            f.id f_id,
            f.content f_content,
            a.id a_id,
            r.name r_name,
            u.login u_login,
            u.phone u_phone,
            u.email u_email,
            u.full_name u_full_name
        FROM feedback f
        JOIN application a ON f.application_id = a.id
        JOIN user u ON a.user_id = u.id
        JOIN room r On a.room_id = r.id
        WHERE !f.viewed
        LIMIT 10;
    SQL)
    ->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>

<html>
    <head>
        <title>Админская панель</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    </head>
    <body>
        <h1>Админская панель</h1>
        <a class="btn btn-secondary" href="index.php">На главную</a>

        <?php $view->flash() ?>
        
        <h2>Заявки</h2>
        <hr>
        <div>
            <?php if (empty($applications)): ?>
                <p>Заявок еще нет</p>
            <?php else: ?>
                <?php foreach ($applications as $app): ?>
                    <div>
                        <p>
                            <b>Пользователь: </b>
                            <?= "{$app['u_login']} {$app['u_full_name']} {$app['u_phone']} {$app['u_email']}" ?>
                        </p>
                        <p><b>Помещение: </b><?= $app['r_name'] ?></p>
                        <p><b>Желаемое время: </b><?= $app['a_time'] ?></p>
                        <p><b>Способ оплаты: </b><?= $app['pm_name'] ?></p>
                        <p><b>Статус: </b><?= $app['s_name'] ?></p>
                        <?php if ($app['s_id'] < 3): ?>
                            <form method="POST">
                                <input class="btn btn-secondary" type="submit" value="Продвинуть статус">
                                <input type="hidden" name="applicationId" value="<?= $app['a_id'] ?>">
                                <input type="hidden" name="action" value="changeAppStatus">
                            </form>
                        <?php endif ?>
                        <hr>
                    </div>
                <?php endforeach ?>
            <?php endif ?>
        </div>
        
        <h2>Отзывы</h2>
        <hr>
        <div>
            <?php if (empty($feedbacks)): ?>
                <p>Отзывов еще нет</p>
            <?php else: ?>
                <?php foreach ($feedbacks as $fb): ?>
                    <div>
                        <p>
                            <b>Пользователь: </b>
                            <?= "{$fb['u_login']} {$fb['u_full_name']} {$fb['u_phone']} {$fb['u_email']}" ?>
                        </p>
                        <p><b>ID заявки: </b><?= $fb['a_id'] ?></p>
                        <p><b>Помещение: </b><?= $fb['r_name'] ?></p>
                        <p><b>Текст: </b><?= $fb['f_content'] ?></p>
                        <form method="POST">
                            <input class="btn btn-secondary" type="submit" value="Отметить просмотренным">
                            <input type="hidden" name="fbId" value="<?= $fb['f_id'] ?>">
                            <input type="hidden" name="action" value="viewFb">
                        </form>
                        <hr>
                    </div>
                <?php endforeach ?>
            <?php endif ?>
        </div>

        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    </body>
</html>
