START TRANSACTION;

INSERT INTO room (name, description, image_src) VALUES
    ('Зал', 'Небольшое помещение на 20-30 человек.', 'Assets/Room1.jpg'),
    ('Ресторан', 'Большой зал на 100 человек.', 'Assets/Room2.jpg'),
    ('Летняя веранда', 'Позволит разместить гостей на свежем воздухе.', 'Assets/Room3.jpg'),
    ('Закрытая веранда', 'Подойдет для мероприятий даже в непогоду.', 'Assets/Room4.jpg');

INSERT INTO status (id, name) VALUES
    (1, 'Новая'),
    (2, 'Банкет назначен'),
    (3, 'Банкет завершен');

INSERT INTO payment_method (id, name) VALUES
    (1, 'Картой'),
    (2, 'Наличными'),
    (3, 'QR-код СБП'),
    (4, 'Расчетный счет для ЮЛ');

COMMIT;
