-- PHP: 7.1.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `test_db`
--

-- --------------------------------------------------------

--
-- Table `users`
--
CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` char(32) NOT NULL,
  `balance` double(10,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `ip_reg` bigint(20) NOT NULL,
  `date_reg` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump `users`
--
INSERT INTO `users` (`id`, `name`, `email`, `password`, `balance`, `ip_reg`, `date_reg`) VALUES
(1, 'Peter Parker', 'parker@mail.ru', 'e10adc3949ba59abbe56e057f20f883e', 0.00, 0, 1484915948),
(2, 'Edward Bill', 'bill@mail.ru', '202cb962ac59075b964b07152d234b70', 5.20, 0, 1484915974),
(3, 'Batman', 'batman@mail.ru', '202cb962ac59075b964b07152d234b70', 0.00, 2130706433, 1485164081),
(4, 'Superman', 'man16@mail.ru', '202cb962ac59075b964b07152d234b70', 5.00, 2130706433, 1485164483),
(5, 'Tarasov', 'tarasov2@mail.ru', '202cb962ac59075b964b07152d234b70', 0.00, 2130706433, 1485164495),
(6, 'Rock', 'rock@mail.ru', '202cb962ac59075b964b07152d234b70', 0.00, 2130706433, 1485165154),
(7, 'Dante', 'dante@mail.ru', 'e10adc3949ba59abbe56e057f20f883e', 0.00, 2130706433, 1485165179);

--
-- Index `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `balance` (`balance`,`date_reg`);


ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
