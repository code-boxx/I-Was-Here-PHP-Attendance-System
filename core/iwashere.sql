CREATE TABLE `attendence` (
  `class_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `sign_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `classes` (
  `class_id` bigint(20) NOT NULL,
  `course_code` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `class_date` datetime NOT NULL DEFAULT current_timestamp(),
  `class_desc` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `courses` (
  `course_code` varchar(255) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `course_desc` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `courses_students` (
  `course_code` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_role` varchar(1) NOT NULL DEFAULT 'T',
  `user_password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `users` (`user_id`, `user_name`, `user_email`, `user_role`, `user_password`) VALUES
(1, 'Administrator', 'admin@iwh.com', 'T', '$2y$10$riU8ND8XPEcP15WZRqXomOu5fTCL8YTKutW7W5Ko9LohJG5ZQN0NG');

ALTER TABLE `attendence`
  ADD PRIMARY KEY (`class_id`,`user_id`),
  ADD KEY `sign_date` (`sign_date`) USING BTREE;

ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD KEY `course_code` (`course_code`),
  ADD KEY `class_date` (`class_date`),
  ADD KEY `user_id` (`class_id`);

ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_code`),
  ADD KEY `course_name` (`course_name`);

ALTER TABLE `courses_students`
  ADD PRIMARY KEY (`course_code`,`user_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_email` (`user_email`),
  ADD KEY `user_name` (`user_name`),
  ADD KEY `user_role` (`user_role`);

ALTER TABLE `classes`
  MODIFY `class_id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;