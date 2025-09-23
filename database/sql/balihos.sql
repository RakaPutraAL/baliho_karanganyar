-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 23, 2025 at 01:21 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laravel`
--

-- --------------------------------------------------------

--
-- Table structure for table `balihos`
--

--
-- Dumping data for table `balihos`
--

INSERT INTO `balihos` (`id`, `opd_id`, `view`, `dimensi`, `jenis_kontruksi`, `alamat`, `kode`, `latitude`, `longitude`, `foto`, `created_at`, `updated_at`) VALUES
(1, 1034, '1 Sisi', '4x6', 'Baliho', 'Timur SD 3 Karanganyar', '331309', '-7.5982492', '110.9528791', '1756966199.png', '2025-09-03 23:09:59', '2025-09-03 23:09:59'),
(2, 1034, '1 Sisi', '4x6', 'Baliho', 'Proliman Beji', '331309', '-7.5909564', '110.9568200', '1756966295.png', '2025-09-03 23:11:35', '2025-09-03 23:11:35'),
(3, 1034, '1 Sisi', '4x6', 'Baliho', 'Pertigaan Giribangun Matesih', '331305', '-7.6417332', '111.0488204', '1757986977.png', '2025-09-03 23:13:55', '2025-09-21 01:53:22'),
(4, 1034, '1 Sisi', '4x6', 'Baliho', 'Pos Polisi Ringroad Sroyo', '331311', '-7.5426699', '110.8931062', '1756966682.png', '2025-09-03 23:18:02', '2025-09-03 23:18:02'),
(5, 1034, '1 Sisi', '4x6', 'Baliho', 'Depan Pabrik Rokok Menara Gondangrejo', '331313', '-7.4706867', '110.8072303', '1756966797.png', '2025-09-03 23:19:57', '2025-09-03 23:19:57'),
(6, 1034, '1 Sisi', '4x6', 'Baliho', 'Utara POM Bensin Menara Gondangrejo', '331313', '-7.4897244', '110.8116394', '1757902416.png', '2025-09-14 19:13:36', '2025-09-21 18:08:29'),
(7, 1034, '1 Sisi', '4x6', 'Baliho', 'Perempatan Besar Colomadu', '331312', '-7.5323856', '110.7478093', '1757903902.png', '2025-09-14 19:25:11', '2025-09-14 19:38:22'),
(8, 1034, '1 Sisi', '4x6', 'Baliho', 'Utara Waduk Lalung', '331309', '-7.6113893', '110.9405526', '1757906848.png', '2025-09-14 20:27:28', '2025-09-14 20:27:28'),
(9, 1034, '1 Sisi', '4x6', 'Baliho', 'Perempatan Papahan', '331309', '-7.5894898', '110.9262702', '1757906968.png', '2025-09-14 20:29:28', '2025-09-21 17:48:48'),
(10, 1034, '1 Sisi', '4x4', 'Baliho', 'Bomo Tawangmangu (Tiket Pintu Masuk TW)', '331306', '-7.6610470', '111.1146894', '1757907497.png', '2025-09-14 20:38:17', '2025-09-14 20:38:17'),
(11, 1034, '1 Sisi', '2x3', 'Baliho', 'Bomo Tawangmangu (Tiket Pintu Masuk TW)', '331306', '-7.6610975', '111.1146196', '1757907729.png', '2025-09-14 20:40:06', '2025-09-14 20:42:09'),
(12, 1034, '1 Sisi', '4x6', 'Baliho', 'Pertigaan Keprabon Karangpandan', '331308', '-7.6162218', '111.0817349', '1757986036.png', '2025-09-15 18:24:55', '2025-09-21 18:07:45'),
(13, 1034, '1 Sisi', '4x6', 'Baliho', 'Alun-alun Karanganyar', '331309', '-7.5938629', '110.9405469', '1757986276.png', '2025-09-15 18:31:16', '2025-09-21 18:07:59'),
(14, 1034, '1 Sisi', '2x3', 'Baliho', 'Terminal Kecil Papahan', '331309', '-7.5896053', '110.9267149', '1757986636.png', '2025-09-15 18:34:53', '2025-09-15 18:37:16'),
(15, 1034, '2 Sisi', '4x6', 'Baliho', 'Pableyan Matesih', '331305', '-7.6322454', '111.0571048', '1757986810.png', '2025-09-15 18:40:10', '2025-09-15 18:40:10'),
(16, 1498, '1 Sisi', '4x6', 'Baliho', 'Perempatan Pegadaian', '331309', '-7.5960353', '110.9474839', '1757987315.png', '2025-09-15 18:48:35', '2025-09-15 18:48:35'),
(17, 1498, '1 Sisi', '16x4', 'Baliho', 'Satlantas Polres', '331309', '-7.5959956', '110.9474032', '1757987789.png', '2025-09-15 18:56:29', '2025-09-15 18:56:29'),
(18, 1498, '1 Sisi', '6,2x2,6', 'Baliho', 'Halaman SETDA', '331309', '-7.5955110', '110.9399930', '1757987962.jpeg', '2025-09-15 18:59:22', '2025-09-21 18:08:09'),
(19, 94, '1 Sisi', '6x3', 'Baliho', 'Tugu Ngiplik Karangpandan', '331308', '-7.6193400', '111.0160669', '1758074861.png', '2025-09-16 19:07:41', '2025-09-21 18:09:08'),
(20, 30, '1 Sisi', '4x5', 'Baliho', 'Depan RM Lesehan Mbak Dwi', '331309', '-7.5922400', '110.9363173', '1758075044.png', '2025-09-16 19:10:44', '2025-09-21 18:08:57'),
(21, 1034, '1 Sisi', '8x3', 'Reklame', 'Karanganyar', '331309', '-7.5940766', '110.9543950', '1758509165.png', '2025-09-21 19:46:05', '2025-09-21 19:46:05'),
(22, 1034, '1 sisi', '2x3', 'Reklame', 'karanganyar', '331314', '-7.5852284', '111.0062130', NULL, '2025-09-22 17:53:10', '2025-09-22 17:53:10');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
