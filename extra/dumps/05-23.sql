DROP TABLE IF EXISTS bookings;

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `people_num` int(11) NOT NULL,
  `comments` text DEFAULT NULL,
  `checkin` date NOT NULL,
  `checkout` date NOT NULL,
  `state` enum('pending','confirmed') NOT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO bookings VALUES("1","1","1","2",NULL,"2024-05-19","2024-05-21","confirmed",NULL);



DROP TABLE IF EXISTS images;

CREATE TABLE `images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image` blob NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




DROP TABLE IF EXISTS room_img;

CREATE TABLE `room_img` (
  `img_id` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




DROP TABLE IF EXISTS rooms;

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_num` tinytext NOT NULL,
  `capacity` int(11) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO rooms VALUES("1","101","3","Habitación de tres presonas");
INSERT INTO rooms VALUES("2","102","2","Habitación de 2 personas");



DROP TABLE IF EXISTS users;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `dni` varchar(9) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `card` varchar(16) NOT NULL,
  `type` enum('client','recepcionist','admin') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sk_dni` (`dni`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users VALUES("1","Jordi","Pereira","3564007G","j@j.com","hola","111222333","client");
INSERT INTO users VALUES("3","Jordi 2","Pereira","3564007D","j@j.com","hola","111222333","client");



