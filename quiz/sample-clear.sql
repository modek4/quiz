SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
--
-- analytics table
--
-- id: A numeric identifier for each entry.
-- code: An alphanumeric user code associated with the data entry.
-- subject: A textual representation of the subject of the data entry.
-- analytic: A large text field that is expected to contain valid JSON data, representing various -- analytics-related information.
--
-- --------------------------------------------------------

CREATE TABLE `analytics` (
  `id` int(11) NOT NULL,
  `code` text NOT NULL,
  `subject` text NOT NULL,
  `analytic` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`analytic`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- codes table
--
-- id: A numeric identifier for each entry.
-- code: An alphanumeric user code associated with the data entry.
-- code_use: An integer representing the usage frequency or count of the code.
-- term: A textual representation of a semester to access.
-- question_order: An integer representing the order or sequence of a question (e.g. random: 1, numeric: 0).
-- question_analytic: An integer representing whether a question is analytic (e.g. analytic: 1, non-analytic: 0).
--
-- --------------------------------------------------------

CREATE TABLE `codes` (
  `id` int(11) NOT NULL,
  `code` text NOT NULL,
  `code_use` int(11) NOT NULL,
  `term` text NOT NULL,
  `question_order` int(11) NOT NULL,
  `question_analytic` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- DEFAULT VALUES FOR codes table
-- Code for administrator account.
-- --------------------------------------------------------

INSERT INTO `codes` (`id`, `code`, `code_use`, `term`, `question_order`, `question_analytic`) VALUES
(1, '777F74BJ14', 0, '1,2,3,4,5,6,7,8,9,10', 0, 1);

-- --------------------------------------------------------
--
-- devices table
--
-- id: A numeric identifier for each entry.
-- udevices: Textual data representing device information, ("name | ip address")
-- open: An integer representing the state of the device (e.g. open: 1, closed: 0).
-- email: Textual data representing email addresses associated with the device or its users.
-- last_login: A timestamp representing the date and time of the last login or activity related to the device. The default value is set to the current timestamp.
--
-- --------------------------------------------------------

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `udevices` text NOT NULL,
  `open` int(11) NOT NULL,
  `email` text NOT NULL,
  `last_login` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- notification table
--
-- id: A numeric identifier for each notification entry.
-- email: Textual data representing the email address of the notification recipient(s).
-- title: Textual data representing the title of the notification.
-- text: Textual data representing the main content or body of the notification.
-- textread: An integer representing the read status of the notification (e.g. read: 0 or unread: 1).
--
-- --------------------------------------------------------

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `email` text NOT NULL,
  `title` text NOT NULL,
  `text` text NOT NULL,
  `textread` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- questions table
--
-- id: A numeric identifier for each question entry.
-- id_question: A numeric identifier for a specific question in a subject.
-- subject: Textual data representing the subject or topic associated with the question.
-- question: Textual data representing the main content or text of the question.
--          (e.g. QUESTION ``` CODE BLOCKS ```)
--          (e.g. QUESTION ``` IMAGE LINK ```)
-- answers: Textual data representing the possible answer choices related to the question (e.g. "answer1 ♥ answer2 ♥ answer3 ♥ answer4").
-- correct_answers: Textual data representing the correct answer(s) to the question. (e.g. a;b;c).
--
-- --------------------------------------------------------

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `id_question` int(11) NOT NULL,
  `subject` text NOT NULL,
  `question` text NOT NULL,
  `answers` text NOT NULL,
  `correct_answers` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- quiz_admin table
--
-- id: A numeric identifier for administration entry.
-- points_correct: A floating-point number representing the points awarded for a correct answer.
-- points_incorrect: A floating-point number representing the points deducted for an incorrect answer.
-- points_halfcorrect: A floating-point number representing the points awarded for partially correct answers.
-- device_limit_good: An integer representing the limit for the number of good (allowed) devices that can access the quiz.
-- device_limit_bad: An integer representing the limit for the number of bad (restricted) devices that are not allowed to access the quiz.
-- location_limit_good: An integer representing the limit for the number of good (allowed) locations that can access the quiz.
-- location_limit_bad: An integer representing the limit for the number of bad (restricted) locations that are not allowed to access the quiz.
--
-- --------------------------------------------------------

CREATE TABLE `quiz_admin` (
  `id` int(11) NOT NULL,
  `points_correct` float NOT NULL,
  `points_incorrect` float NOT NULL,
  `points_halfcorrect` float NOT NULL,
  `device_limit_good` int(11) NOT NULL,
  `device_limit_bad` int(11) NOT NULL,
  `location_limit_good` int(11) NOT NULL,
  `location_limit_bad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- DEFAULT VALUES FOR quiz_admin table
-- --------------------------------------------------------

INSERT INTO `quiz_admin` (`id`, `points_correct`, `points_incorrect`, `points_halfcorrect`, `device_limit_good`, `device_limit_bad`, `location_limit_good`, `location_limit_bad`) VALUES
(1, -1.75, 2.5, 1.25, 3, 6, 7, 14);

-- --------------------------------------------------------
--
-- quiz_users table
--
-- id: A numeric identifier for each user entry.
-- email: Textual data representing the email address of the user.
-- password: Textual data representing the encrypted or hashed password of the user for authentication.
-- dark: An integer representing the user's preference for dark mode (e.g. darkmode: 1, lightmode: 0, account blocked: -1).
-- code: Alphanumeric user code associated with the account since registration.
-- last_login: A timestamp representing the date and time of the user's last login or activity. The default value is set to the current timestamp, updated on each 10 second interval (script.js:41).
-- status: Textual data representing the status or state of the user account (e.g. "online", "offline").
--
-- --------------------------------------------------------

CREATE TABLE `quiz_users` (
  `id` int(11) NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `dark` int(11) NOT NULL,
  `code` text NOT NULL,
  `last_login` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` text NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- DEFAULT VALUES FOR quiz_users table
-- Value for administrator account.
-- --------------------------------------------------------

INSERT INTO `quiz_users` (`id`, `email`, `password`, `dark`, `code`, `last_login`, `status`) VALUES
(1, 'admin@admin.com', '$2y$10$1QnrK0tzmufyveXFzSXeYOTo9GUj1HEdUbX4Z2.bptnzVaki75G46', 0, '777F74BJ14', '2023-01-01 23:59:59', 'offline');

-- --------------------------------------------------------
--
-- reports table
--
-- id: A numeric identifier for each report entry.
-- subject: Textual data representing the subject or topic associated with the report.
-- question_id: Textual data representing the identifier of the questions associated with the report.
-- user_answers: Textual data representing the user's answers or responses to the questions in the report (e.g. "a,b,c").
-- email: Textual data representing the email address of the user who generated the report.
-- report_date: A timestamp representing the date and time when the report was generated. The default value is set to the current timestamp when the row is inserted and updated when the row is modified.
--
-- --------------------------------------------------------

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `subject` text NOT NULL,
  `question_id` text NOT NULL,
  `user_answers` text NOT NULL,
  `email` text NOT NULL,
  `report_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- scores table
--
-- id: A numeric identifier for each score entry.
-- email: Textual data representing the email address of the user who achieved the score.
-- subject: Textual data representing the subject or topic associated with the score.
-- score: A floating-point number representing the numerical score achieved by the user.
-- question_count: An integer representing the total count of questions in the subject or quiz.
-- end_date: A datetime representing the date and time when the score entry was created. The default value is set to the current timestamp.
-- total_time: A time representing the total time taken by the user to complete the subject or quiz.
-- answers: Large textual data in JSON format, representing the user's answers to questions.
--
-- --------------------------------------------------------

CREATE TABLE `scores` (
  `id` int(11) NOT NULL,
  `email` text NOT NULL,
  `subject` text NOT NULL,
  `score` float NOT NULL,
  `question_count` int(11) NOT NULL,
  `end_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_time` time NOT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`answers`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- subjects table
--
-- id: A numeric identifier for each subject entry.
-- subject: Textual data representing the name or title of the subject.
-- share: An integer representing the sharing status of the subject (e.g. shared: 1, not shared: 0).
-- term: An integer representing the semester or period associated with the subject.
-- loaded: Large textual data in JSON format, they store the number of quiz calls broken down by day.
--
-- --------------------------------------------------------

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject` text NOT NULL,
  `share` int(11) NOT NULL,
  `term` int(11) NOT NULL,
  `loaded` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- users table
--
-- id: A numeric identifier for each user entry.
-- email: Textual data representing the email address of the user.
-- password: Textual data representing the encrypted or hashed password of the user for authentication.
-- dark: Insignificant cell. Available for future development.
-- moderator: An integer representing whether the user has moderator privileges (e.g., moderator: 1, admin: 0).
--
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `dark` int(11) NOT NULL DEFAULT 0,
  `moderator` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- DEFAULT VALUES FOR users table
-- Value for administrator account.
-- --------------------------------------------------------

INSERT INTO `users` (`id`, `email`, `password`, `dark`, `moderator`) VALUES
(1, 'admin@admin.com', '$2y$10$1QnrK0tzmufyveXFzSXeYOTo9GUj1HEdUbX4Z2.bptnzVaki75G46', 0, 0);

--
-- analytics index
--
ALTER TABLE `analytics`
  ADD PRIMARY KEY (`id`);

--
-- codes index
--
ALTER TABLE `codes`
  ADD PRIMARY KEY (`id`);

--
-- devices index
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`);

--
-- notification index
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`);

--
-- questions index
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`);

--
-- quiz_admin index
--
ALTER TABLE `quiz_admin`
  ADD PRIMARY KEY (`id`);

--
-- quiz_users index
--
ALTER TABLE `quiz_users`
  ADD PRIMARY KEY (`id`);

--
-- reports index
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);

--
-- scores index
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`id`);

--
-- subjects index
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- users index
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
--  analytics AUTO_INCREMENT
--
ALTER TABLE `analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- codes AUTO_INCREMENT
--
ALTER TABLE `codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- devices AUTO_INCREMENT
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- notification AUTO_INCREMENT
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=701;

--
-- questions AUTO_INCREMENT
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21266;

--
-- quiz_admin AUTO_INCREMENT
--
ALTER TABLE `quiz_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- quiz_users AUTO_INCREMENT
--
ALTER TABLE `quiz_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- reports AUTO_INCREMENT
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- scores AUTO_INCREMENT
--
ALTER TABLE `scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=275;

--
-- subjects AUTO_INCREMENT
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- users AUTO_INCREMENT
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
