<?php

require_once 'bootstrap.php';

$app = new App();
$userInfo = $app->getUserInfo();

if (!$userInfo) {
    header('Location: register.php');
    exit();
}

header('Content-Type: text/html; charset=UTF-8');

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

// get rooms for carousel
$rooms = $conn->query('SELECT * FROM room')->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>

<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Главная</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <style>
            #roomCarousel {
                max-height: 40vh;
                overflow: hidden;
            }

            #roomCarousel .carousel-item,
            #roomCarousel .carousel-item img {
                height: 40vh;
            }

            #roomCarousel .carousel-item img {
                object-fit: cover;
            }
        </style>
    </head>
    <body>
        <h1>Личный кабинет</h1>
        <div class="text-center">
            <div id="roomCarousel" class="carousel slide mb-3" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <?php foreach ($rooms as $key => $room): ?>
                        <button
                            type="button"
                            data-bs-target="#roomCarousel"
                            data-bs-slide-to="<?= $key ?>"
                            <?php if ($key === 0): ?>
                                class="active"
                                aria-current="true"
                            <?php endif ?>
                            aria-label="Слайд <?= $key + 1 ?>">
                        </button>
                    <?php endforeach ?>
                </div>
                <div class="carousel-inner">
                    <?php foreach ($rooms as $key => $room): ?>
                        <div class="carousel-item <?= $key === 0 ? 'active' : '' ?>">
                            <img class="d-block w-100" src="/<?= $room['image_src'] ?>" alt="<?= htmlspecialchars($room['name']) ?>">
                            <div class="carousel-caption d-none d-sm-block">
                                <h5><?= $room['name'] ?></h5>
                                <p><?= $room['description'] ?></p>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#roomCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Предыдущий</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#roomCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Следующий</span>
                </button>
            </div>

            <a href="submit.php" class="btn btn-primary">Оставить заявку</a>
            <?php if ($userInfo['isAdmin']): ?>
                <a href="admin.php" class="btn btn-secondary">Админская панель</a>
            <?php endif ?>
        </div>
        <h2>Мои заявки:</h2>
        <hr>
        <div>
            <?php if (empty($applications)): ?>
                <p>Вы еще не создали ни одной заявки</p>
            <?php else: ?>
                <?php foreach ($applications as $app): ?>
                    <div>
                        <p class="h3"><u><?= $app['room_name'] ?></u></p>
                        <p><b>Желаемое время:</b> <?= $app['preferred_time'] ?></p>
                        <p><b>Статус:</b> <?= $app['status_name'] ?></p>
                        <?php if ($app['status_id'] >= 2): ?>
                            <a href="feedback.php/?applicationId=<?= $app['id'] ?>" class="btn btn-secondary">Оставить отзыв</a>
                        <?php endif ?>
                        <hr>
                    </div>
                <?php endforeach ?>
            <?php endif ?>
        </div>
        <h2>Мои отзывы:</h2>
        <hr>
        <div>
            <?php if (empty($feedbacks)): ?>
                <p>Вы еще не оставили ни одного отзыва</p>
            <?php else: ?>
                <?php foreach ($feedbacks as $fb): ?>
                    <div>
                        <p><b>ID заявки:</b> <?= $fb['a_id'] ?></p>
                        <p><b>Помещение:</b> <?= $fb['r_name'] ?></p>
                        <p><b>Текст:</b> <?= $fb['f_content'] ?></p>
                        <p><b>Просмотрено:</b> <?= $fb['f_viewed'] ? 'Да' : 'Нет' ?></p>
                        <hr>
                    </div>
                <?php endforeach ?>
            <?php endif ?>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>
