-- (A) SETTINGS
CREATE TABLE `settings` (
  `setting_name` varchar(255) NOT NULL,
  `setting_description` varchar(255) DEFAULT NULL,
  `setting_value` varchar(255) NOT NULL,
  `setting_group` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`setting_name`, `setting_description`, `setting_value`, `setting_group`) VALUES
('APP_VER', 'App version', '1', 0),
('EMAIL_FROM', 'System email from', 'sys@site.com', 1),
('PAGE_PER', 'Number of entries per page', '20', 1),
('D_LONG', 'MYSQL date format (long)', '%e %M %Y', 1),
('D_SHORT', 'MYSQL date format (short)', '%Y-%m-%d', 1),
('DT_LONG', 'MYSQL date time format (long)', '%e %M %Y %l:%i:%S %p', 1),
('DT_SHORT', 'MYSQL date time format (short)', '%Y-%m-%d %H:%i:%S', 1),
('SUGGEST_LIMIT', 'Autocomplete suggestion limit', 5, 1);

ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_name`),
  ADD KEY `setting_group` (`setting_group`);

-- (B) USERS
CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL,
  `user_level` varchar(1) NOT NULL DEFAULT 'U',
  `user_name` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_email` (`user_email`),
  ADD KEY `user_name` (`user_name`),
  ADD KEY `user_level` (`user_level`);

ALTER TABLE `users`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT;

-- (C) HASH
CREATE TABLE `users_hash` (
  `user_id` bigint(20) NOT NULL,
  `hash_for` varchar(3) NOT NULL,
  `hash_code` text NOT NULL,
  `hash_time` datetime NOT NULL,
  `hash_tries` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `users_hash`
  ADD PRIMARY KEY (`user_id`, `hash_for`);

-- (D) COURSES
CREATE TABLE `courses` (
  `course_code` varchar(255) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `course_desc` text DEFAULT NULL,
  `course_start` date NOT NULL,
  `course_end` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_code`),
  ADD KEY `course_name` (`course_name`),
  ADD KEY `course_start` (`course_start`),
  ADD KEY `course_end` (`course_end`);

-- (E) COURSES-USERS
CREATE TABLE `courses_users` (
  `course_code` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `courses_users`
  ADD PRIMARY KEY (`course_code`,`user_id`);

-- (F) CLASSES
CREATE TABLE `classes` (
  `class_id` bigint(20) NOT NULL,
  `course_code` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `class_date` datetime NOT NULL DEFAULT current_timestamp(),
  `class_desc` text DEFAULT NULL,
  `class_hash` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD KEY `course_code` (`course_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `class_date` (`class_date`);

ALTER TABLE `classes`
  MODIFY `class_id` bigint(20) NOT NULL AUTO_INCREMENT;

-- (G) ATTENDANCE
CREATE TABLE `attendance` (
  `class_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `a_status` tinyint(1) NOT NULL,
  `a_by` bigint(20) NOT NULL,
  `a_date` datetime NOT NULL DEFAULT current_timestamp(),
  `a_notes` varchar(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `attendance`
  ADD PRIMARY KEY (`class_id`,`user_id`),
  ADD KEY `a_status` (`a_status`),
  ADD KEY `a_by` (`a_by`),
  ADD KEY `a_date` (`a_date`);