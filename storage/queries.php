<?php

const create_table_users =
"CREATE TABLE IF NOT EXISTS `DataBaseName`.`users` ( `id` INT(11) NOT NULL AUTO_INCREMENT , `name` VARCHAR(11) NULL ,
 `email` VARCHAR(50) NOT NULL , `password` VARCHAR(100) NOT NULL, PRIMARY KEY (`id`), `token` VARCHAR(99) NULL,  `isactive` BOOL NULL) ENGINE = InnoDB";
const search_email =
"SELECT `id` FROM `users` WHERE `email`=?";
const insert_into_users =
"INSERT INTO `DataBaseName`.`users` (`email`, `password`, `token`) VALUES (?, ?, ?)";
const select_from_users =
"SELECT `password` FROM `users` WHERE `email` = ?";
const get_token =
"SELECT `id`, `token`, `name` FROM `users` WHERE `email` = ?";
const email_is_active =
"SELECT `isactive` FROM `users` WHERE `email` = ?";
const insert_isactive =
"UPDATE `DataBaseName`.`users` SET `isactive`=? WHERE  `email`=?;";
const get_datauser =
"SELECT `id`, `email`, `name`, `isactive` FROM `users` WHERE `token` = ?";
const select_all =
"SELECT * FROM `users`";
const insert_values =
'INSERT INTO `DataBaseName`.`users` (`name`, `email`, `password`) VALUES (?, ?, ?)';
