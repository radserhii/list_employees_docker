CREATE DATABASE IF NOT EXISTS `test` COLLATE 'utf8_bin' ;
CREATE USER 'test'@'localhost' IDENTIFIED BY 'password';
GRANT ALL ON `test`.* TO 'test'@'%' ;

FLUSH PRIVILEGES ;
