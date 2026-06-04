-- e_pharma sudah dibuat otomatis dari MYSQL_DATABASE di docker-compose
-- Buat database untuk auth_service dan report_service
CREATE DATABASE IF NOT EXISTS `db_auth`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE DATABASE IF NOT EXISTS `db_report`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER DATABASE `e_pharma`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON `e_pharma`.* TO 'root'@'%';
GRANT ALL PRIVILEGES ON `db_auth`.* TO 'root'@'%';
GRANT ALL PRIVILEGES ON `db_report`.* TO 'root'@'%';
FLUSH PRIVILEGES;