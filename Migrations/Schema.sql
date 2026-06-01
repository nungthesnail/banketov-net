START TRANSACTION;

CREATE DATABASE IF NOT EXISTS banketov_net;

USE banketov_net;

CREATE TABLE IF NOT EXISTS user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    login VARCHAR(256) NOT NULL UNIQUE,
    password_hash VARCHAR(512) NOT NULL,
    full_name VARCHAR(256) NOT NULL,
    phone CHAR(15) NOT NULL,
    email VARCHAR(256) NOT NULL,
    is_admin BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE INDEX idx_user_login ON user (login);

CREATE TABLE IF NOT EXISTS room (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(128) NOT NULL,
    description VARCHAR(256) NOT NULL,
    image_src VARCHAR(128) NOT NULL
);

CREATE INDEX idx_room_name ON room (name);

CREATE TABLE IF NOT EXISTS status (
    id INT PRIMARY KEY,
    name VARCHAR(64) NOT NULL
);

CREATE TABLE IF NOT EXISTS payment_method (
    id INT PRIMARY KEY,
    name VARCHAR(64) NOT NULL
);

CREATE TABLE IF NOT EXISTS application (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    preferred_time VARCHAR(128) NOT NULL,
    status INT NOT NULL,
    payment_method INT NOT NULL,

    CONSTRAINT fk_application_user FOREIGN KEY (user_id) REFERENCES user(id),
    CONSTRAINT fk_application_room FOREIGN KEY (room_id) REFERENCES room(id),
    CONSTRAINT fk_application_status FOREIGN KEY (status) REFERENCES status(id),
    CONSTRAINT fk_application_payment_method FOREIGN KEY (payment_method) REFERENCES payment_method(id)
);

CREATE TABLE IF NOT EXISTS feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    content VARCHAR(512) NOT NULL,
    viewed BOOLEAN NOT NULL DEFAULT FALSE,

    CONSTRAINT fk_feedback_application FOREIGN KEY (application_id) REFERENCES application(id)
);

COMMIT;
