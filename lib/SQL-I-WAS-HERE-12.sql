ALTER TABLE `classes`
  ADD COLUMN `class_room` varchar(100) DEFAULT NULL,
  ADD COLUMN `class_group` varchar(100) DEFAULT NULL,
  ADD KEY `class_room_date` (`class_room`,`class_date`),
  ADD KEY `class_group_date` (`class_group`,`class_date`);
