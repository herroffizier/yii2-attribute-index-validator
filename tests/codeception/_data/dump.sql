CREATE TABLE `model` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `attribute` VARCHAR(255) NOT NULL UNIQUE KEY
);

CREATE TABLE `model_2` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `attribute_1` VARCHAR(255) NOT NULL,
    `attribute_2` VARCHAR(255) NOT NULL,
    UNIQUE KEY `unique_attributes` (`attribute_1`,`attribute_2`)
);