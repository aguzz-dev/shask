-- ============================================================
-- Shask – esquema de negocio
-- Ejecutar en HeidiSQL sobre la base de datos "sshask"
-- después de haber corrido: php artisan migrate
-- ============================================================

-- 1. Ampliar la tabla users con las columnas del negocio
ALTER TABLE `users`
    ADD COLUMN `full_name`  VARCHAR(100)  NOT NULL DEFAULT '' AFTER `id`,
    ADD COLUMN `username`   VARCHAR(50)   NOT NULL DEFAULT '' AFTER `full_name`,
    ADD COLUMN `age`        INT           NULL AFTER `username`,
    ADD COLUMN `avatar`     TEXT          NULL AFTER `age`,
    ADD COLUMN `hype`       INT           NOT NULL DEFAULT 0 AFTER `avatar`,
    ADD COLUMN `fcm_token`  TEXT          NULL AFTER `hype`,
    ADD COLUMN `status`     TINYINT       NOT NULL DEFAULT 0 AFTER `fcm_token`,
    ADD COLUMN `code`       INT           NULL AFTER `status`,
    ADD UNIQUE KEY `users_username_unique` (`username`);

-- 2. Reemplazar personal_access_tokens por la estructura propia del proyecto
--    (la migración de Laravel crea la de Sanctum, que es incompatible)
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `token`      VARCHAR(150)    NOT NULL,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `pat_token_unique` (`token`),
    KEY `pat_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Posts
CREATE TABLE IF NOT EXISTS `posts` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`      VARCHAR(255)    NOT NULL,
    `asset_id`   INT             NOT NULL DEFAULT 0,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `status`     TINYINT         NOT NULL DEFAULT 0,
    `created_at` DATE            NULL,
    `updated_at` TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `posts_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Public posts (post compartido con URL pública)
CREATE TABLE IF NOT EXISTS `public_posts` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `post_id`    BIGINT UNSIGNED NOT NULL,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `url`        VARCHAR(10)     NOT NULL,
    `created_at` TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `public_posts_url_unique` (`url`),
    KEY `public_posts_post_id_index` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Questions
CREATE TABLE IF NOT EXISTS `questions` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `public_post_id` BIGINT UNSIGNED NOT NULL,
    `text`           TEXT            NOT NULL,
    `hint`           TEXT            NULL,
    `ip`             VARCHAR(45)     NULL,
    `status`         TINYINT         NOT NULL DEFAULT 0,
    `created_at`     TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `questions_public_post_id_index` (`public_post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Public assets (assets integrados, id <= 10000)
CREATE TABLE IF NOT EXISTS `public_assets` (
    `id`    INT          NOT NULL AUTO_INCREMENT,
    `color` JSON         NULL,
    `icon`  VARCHAR(255) NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Assets (subidos por usuarios, id > 10000)
CREATE TABLE IF NOT EXISTS `assets` (
    `id`    INT          NOT NULL AUTO_INCREMENT,
    `color` JSON         NULL,
    `icon`  VARCHAR(255) NULL,
    `price` INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8mb4;

-- 8. Relación usuario-asset (compras)
CREATE TABLE IF NOT EXISTS `asset_user` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `asset_id`   INT             NOT NULL,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `created_at` DATETIME        NULL,
    PRIMARY KEY (`id`),
    KEY `asset_user_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Lista negra por IP
CREATE TABLE IF NOT EXISTS `blacklist_user` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     BIGINT UNSIGNED NOT NULL,
    `ip`          VARCHAR(45)     NOT NULL,
    `random_user` VARCHAR(20)     NOT NULL,
    `created_at`  TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `blacklist_user_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Preguntas aleatorias para el formulario web
CREATE TABLE IF NOT EXISTS `preguntas_random` (
    `id`       INT  NOT NULL AUTO_INCREMENT,
    `pregunta` TEXT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos de ejemplo para preguntas_random (opcional)
INSERT INTO `preguntas_random` (`pregunta`) VALUES
    ('¿Cuál es tu mayor miedo?'),
    ('¿Qué harías si solo te quedara un día de vida?'),
    ('¿Cuál es tu recuerdo favorito?'),
    ('¿Qué es lo que más valoras en una persona?'),
    ('¿Cuál sería tu superpoder ideal?');

-- ============================================================
-- Perfil v3: logros
-- ============================================================

-- 11. Catálogo de logros (el peso ordena del más al menos importante)
CREATE TABLE IF NOT EXISTS `achievements` (
    `id`      INT          NOT NULL AUTO_INCREMENT,
    `code`    VARCHAR(50)  NOT NULL,
    `weight`  INT          NOT NULL DEFAULT 0,
    `emoji`   VARCHAR(16)  NOT NULL,
    `name_es` VARCHAR(100) NOT NULL,
    `name_en` VARCHAR(100) NOT NULL,
    `active`  TINYINT      NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `achievements_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Logros desbloqueados por usuario (un desbloqueo es para siempre)
CREATE TABLE IF NOT EXISTS `achievement_user` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`        BIGINT UNSIGNED NOT NULL,
    `achievement_id` INT             NOT NULL,
    `unlocked_at`    TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `achievement_user_unique` (`user_id`, `achievement_id`),
    KEY `achievement_user_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `achievements` (`code`,`weight`,`emoji`,`name_es`,`name_en`) VALUES
('hype_10k',         90, '👑', '10K de hype',          '10K hype'),
('mailbox_exploded', 80, '💥', 'Buzón explotado',      'Exploded mailbox'),
('received_100',     70, '🚀', '100 recibidas',        '100 received'),
('answered_50',      60, '🤐', '50 respondidas',       '50 answered'),
('hype_1k',          50, '🔥', '1K de hype',           '1K hype'),
('answered_10',      40, '💬', '10 respondidas',       '10 answered'),
('first_question',   30, '📨', 'Primera pregunta',     'First question'),
('first_mailbox',    20, '📬', 'Primer buzón',         'First mailbox'),
('custom_avatar',    10, '🎨', 'Avatar personalizado', 'Custom avatar')
ON DUPLICATE KEY UPDATE `weight` = VALUES(`weight`);
