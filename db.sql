DROP TABLE IF EXISTS `avia`;
CREATE TABLE `avia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` varchar(25) NOT NULL,
  `coef` varchar(100) NOT NULL,
  `num` int(11) NOT NULL,
  `class` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

LOCK TABLES `avia` WRITE;
/*!40000 ALTER TABLE `avia` DISABLE KEYS */;
INSERT INTO `avia` VALUES (1,'OA','-0.00686',18,'B'),(2,'OA','-0.00686',16,'E'),(3,'B','-0.00091',42,'E'),(4,'C','0.00452',42,'E'),(5,'D','0.00931',33,'E');
/*!40000 ALTER TABLE `avia` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `result`;

CREATE TABLE `result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `params` json NOT NULL,
  `avia_index` float NOT NULL,
  `result` json NOT NULL,
  `surplus` int(11) DEFAULT '0' COMMENT 'Лишние пассажиры',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
