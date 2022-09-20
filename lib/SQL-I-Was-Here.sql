-- (A) SETTINGS
CREATE TABLE `settings` (
  `setting_name` varchar(255) NOT NULL,
  `setting_description` varchar(255) DEFAULT NULL,
  `setting_value` varchar(255) NOT NULL,
  `setting_group` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_name`),
  ADD KEY `setting_group` (`setting_group`);

INSERT INTO `settings` (`setting_name`, `setting_description`, `setting_value`, `setting_group`) VALUES
  ('EMAIL_FROM', 'System email from.', 'sys@site.com', 1),
  ('PAGE_PER', 'Number of entries per page.', '20', 1),
  ('USER_ROLES', 'User role names.', '{"A":"Admin","T":"Teacher","S":"Student","I":"Inactive"}', 0);

-- (B) USERS
CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_role` varchar(1) NOT NULL DEFAULT 'S',
  `user_password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_email` (`user_email`),
  ADD KEY `user_name` (`user_name`),
  ADD KEY `user_role` (`user_role`);

ALTER TABLE `users`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT;

-- (C) PASSWORD RESET
CREATE TABLE `password_reset` (
  `user_id` bigint(20) NOT NULL,
  `reset_hash` varchar(64) NOT NULL,
  `reset_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`user_id`);

-- (D) COURSES
CREATE TABLE `courses` (
  `course_id` bigint(20) NOT NULL,
  `course_code` varchar(255) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `course_desc` text DEFAULT NULL,
  `course_start` date NOT NULL,
  `course_end` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `coourse_code` (`course_code`),
  ADD KEY `course_name` (`course_name`) USING BTREE,
  ADD KEY `course_start` (`course_start`),
  ADD KEY `course_end` (`course_end`);

ALTER TABLE `courses`
  MODIFY `course_id` bigint(20) NOT NULL AUTO_INCREMENT;

-- (E) COURSES-USERS
CREATE TABLE `courses_users` (
  `course_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `courses_users`
  ADD PRIMARY KEY (`course_id`,`user_id`);

-- (F) CLASSES
CREATE TABLE `classes` (
  `class_id` bigint(20) NOT NULL,
  `course_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `class_date` datetime NOT NULL DEFAULT current_timestamp(),
  `class_desc` text DEFAULT NULL,
  `class_hash` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `class_date` (`class_date`);

ALTER TABLE `classes`
  MODIFY `class_id` bigint(20) NOT NULL AUTO_INCREMENT;

-- (G) ATTENDANCE
CREATE TABLE `attendance` (
  `class_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `sign_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `attendance`
  ADD PRIMARY KEY (`class_id`,`user_id`),
  ADD KEY `sign_date` (`sign_date`) USING BTREE;