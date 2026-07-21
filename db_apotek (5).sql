-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 18 Jul 2026 pada 09.07
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_apotek`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `apotek`
--

CREATE TABLE `apotek` (
  `id_apotek` int(11) UNSIGNED NOT NULL,
  `id_kota` int(11) UNSIGNED DEFAULT NULL,
  `nama_apotek` varchar(150) NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `apotek`
--

INSERT INTO `apotek` (`id_apotek`, `id_kota`, `nama_apotek`, `alamat`, `no_telp`, `email`) VALUES
(1, NULL, 'Apotek Sehat Jakarta', 'Jl. Sudirman No. 10, Jakarta', '021-555100', 'jakarta@apoteksehat.id'),
(2, NULL, 'Apotek Sehat Tangerang', 'Jl. Ciledug Raya No. 25, Tangerang', '021-555200', 'tangerang@apoteksehat.id'),
(3, NULL, 'Apotek Sehat Depok', 'Jl. Margonda Raya No. 45, Depok', '021-555300', 'depok@apoteksehat.id'),
(4, NULL, 'Apotek Sehat Bekasi', 'Jl. Ahmad Yani No. 22, Bekasi Timur, Bekasi', '021-555400', 'bekasi@apoteksehat.id'),
(5, NULL, 'Apotek Sehat Bogor', 'Jl. Pajajaran No. 88, Bogor', '022-555500', 'bogor@apoteksehat.id'),
(6, 4, 'Apotek Blok-m', 'jl.Bulungan no 2', '123658', 'apotekblokm@sehat.id');

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking`
--

CREATE TABLE `booking` (
  `id_booking` int(11) UNSIGNED NOT NULL,
  `kode_booking` varchar(30) NOT NULL,
  `id_user` int(11) UNSIGNED NOT NULL,
  `id_dokter` int(11) UNSIGNED NOT NULL,
  `id_klinik` int(11) UNSIGNED NOT NULL,
  `id_jadwal` int(11) UNSIGNED DEFAULT NULL,
  `tanggal_booking` date NOT NULL,
  `keluhan` text DEFAULT NULL,
  `status` enum('Menunggu','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `booking`
--

INSERT INTO `booking` (`id_booking`, `kode_booking`, `id_user`, `id_dokter`, `id_klinik`, `id_jadwal`, `tanggal_booking`, `keluhan`, `status`, `created_at`) VALUES
(1, 'BKG-20250601-0001', 3, 1, 1, NULL, '2025-06-02', 'Sakit kepala', 'Selesai', '2025-06-01 08:30:00'),
(2, 'BKG-20250605-0002', 4, 2, 1, NULL, '2025-06-06', 'Demam tinggi', 'Selesai', '2025-06-05 09:00:00'),
(3, 'BKG-20250610-0003', 5, 3, 2, NULL, '2025-06-11', 'Gatal-gatal', 'Dibatalkan', '2025-06-10 10:15:00'),
(4, 'BKG-20250615-0004', 6, 4, 1, NULL, '2025-06-16', 'Nyeri dada', 'Menunggu', '2025-06-15 07:45:00'),
(5, 'BKG-20250620-0005', 7, 5, 2, NULL, '2025-06-21', 'Pusing berputar', 'Selesai', '2025-06-20 11:20:00'),
(6, 'BKG-20250701-0006', 8, 6, 3, NULL, '2025-07-02', 'Telinga berdenging', 'Menunggu', '2025-07-01 13:10:00'),
(7, 'BKG-20250710-0007', 9, 7, 4, NULL, '2025-07-11', 'Nyeri sendi', 'Selesai', '2025-07-10 14:00:00'),
(8, 'BKG-20250720-0008', 10, 8, 5, NULL, '2025-07-21', ' Mata merah', 'Menunggu', '2025-07-20 08:50:00'),
(9, 'BKG-20250801-0009', 3, 9, 1, NULL, '2025-08-02', 'Sakit gigi', 'Dibatalkan', '2025-08-01 10:30:00'),
(10, 'BKG-20250810-0010', 4, 10, 2, NULL, '2025-08-11', 'Periksa kehamilan', 'Menunggu', '2025-08-10 09:15:00'),
(11, 'BKG-20260717-0001', 13, 7, 6, 30, '2026-07-20', 'mata saya sakit dock', 'Menunggu', '2026-07-17 13:28:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int(11) UNSIGNED NOT NULL,
  `id_pesanan` int(11) UNSIGNED NOT NULL,
  `id_obat` int(11) UNSIGNED NOT NULL,
  `id_apotek` int(11) UNSIGNED DEFAULT NULL,
  `harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_obat`, `id_apotek`, `harga`, `jumlah`, `subtotal`) VALUES
(1, 1, 1, NULL, 3500.00, 2, 7000.00),
(2, 1, 8, NULL, 12000.00, 1, 12000.00),
(3, 2, 4, NULL, 8500.00, 1, 8500.00),
(4, 2, 26, NULL, 3500.00, 2, 7000.00),
(5, 3, 3, NULL, 4100.00, 1, 4100.00),
(6, 3, 16, NULL, 4200.00, 2, 8400.00),
(7, 3, 29, NULL, 4000.00, 1, 4000.00),
(8, 4, 9, NULL, 18000.00, 1, 18000.00),
(9, 4, 11, NULL, 14000.00, 2, 28000.00),
(10, 5, 19, NULL, 22000.00, 1, 22000.00),
(11, 5, 35, NULL, 15000.00, 3, 45000.00),
(12, 6, 2, NULL, 5200.00, 2, 10400.00),
(13, 6, 8, NULL, 12000.00, 2, 24000.00),
(14, 7, 37, NULL, 22000.00, 1, 22000.00),
(15, 7, 44, NULL, 9500.00, 2, 19000.00),
(16, 8, 12, NULL, 16000.00, 1, 16000.00),
(17, 8, 13, NULL, 13500.00, 1, 13500.00),
(18, 8, 14, NULL, 9000.00, 1, 9000.00),
(19, 9, 29, NULL, 4000.00, 3, 12000.00),
(20, 10, 34, NULL, 120000.00, 1, 120000.00),
(21, 10, 33, NULL, 45000.00, 1, 45000.00),
(22, 11, 5, NULL, 9200.00, 1, 9200.00),
(23, 11, 17, NULL, 6500.00, 1, 6500.00),
(24, 12, 20, NULL, 19000.00, 1, 19000.00),
(25, 12, 42, NULL, 13000.00, 1, 13000.00),
(26, 13, 6, NULL, 15000.00, 2, 30000.00),
(27, 14, 22, NULL, 4000.00, 2, 8000.00),
(28, 14, 25, NULL, 5000.00, 1, 5000.00),
(29, 14, 39, NULL, 7500.00, 1, 7500.00),
(30, 15, 10, NULL, 25000.00, 1, 25000.00),
(31, 16, 43, NULL, 85000.00, 1, 85000.00),
(32, 16, 31, NULL, 7000.00, 1, 7000.00),
(33, 17, 7, NULL, 7800.00, 1, 7800.00),
(34, 17, 21, NULL, 15000.00, 1, 15000.00),
(35, 18, 27, NULL, 4500.00, 2, 9000.00),
(36, 18, 28, NULL, 6000.00, 1, 6000.00),
(37, 19, 32, NULL, 35000.00, 1, 35000.00),
(38, 19, 35, NULL, 15000.00, 2, 30000.00),
(39, 20, 8, NULL, 12000.00, 3, 36000.00),
(40, 20, 38, NULL, 11000.00, 1, 11000.00),
(41, 21, 6, NULL, 15000.00, 1, 15000.00),
(42, 21, 37, NULL, 22000.00, 1, 22000.00),
(43, 21, 23, NULL, 6000.00, 1, 6000.00),
(44, 22, 4, NULL, 8500.00, 1, 8500.00),
(45, 22, 23, NULL, 6000.00, 1, 6000.00),
(46, 23, 37, NULL, 22000.00, 1, 22000.00),
(47, 23, 4, NULL, 8500.00, 1, 8500.00),
(48, 23, 23, NULL, 6000.00, 1, 6000.00),
(49, 24, 4, NULL, 8500.00, 1, 8500.00),
(50, 24, 23, NULL, 6000.00, 1, 6000.00),
(51, 25, 27, NULL, 4500.00, 1, 4500.00),
(52, 26, 37, NULL, 22000.00, 1, 22000.00),
(53, 26, 4, NULL, 8500.00, 1, 8500.00),
(54, 26, 23, NULL, 6000.00, 1, 6000.00),
(55, 27, 4, 5, 8500.00, 1, 8500.00),
(56, 27, 23, 5, 6000.00, 1, 6000.00),
(57, 27, 22, 5, 4000.00, 1, 4000.00),
(58, 28, 4, 4, 8500.00, 1, 8500.00),
(59, 28, 23, 4, 6000.00, 1, 6000.00),
(60, 29, 27, 4, 4500.00, 1, 4500.00),
(61, 29, 37, 4, 22000.00, 1, 22000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokter`
--

CREATE TABLE `dokter` (
  `id_dokter` int(11) UNSIGNED NOT NULL,
  `id_spesialis` int(11) UNSIGNED NOT NULL,
  `nama_dokter` varchar(150) NOT NULL,
  `gender` enum('Laki-laki','Perempuan') NOT NULL DEFAULT 'Laki-laki',
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `str_no` varchar(50) DEFAULT NULL,
  `biaya_konsultasi` decimal(12,2) NOT NULL DEFAULT 0.00,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `dokter`
--

INSERT INTO `dokter` (`id_dokter`, `id_spesialis`, `nama_dokter`, `gender`, `no_hp`, `email`, `str_no`, `biaya_konsultasi`, `foto`) VALUES
(1, 1, 'dr. Andi Wijaya, Sp.PD', 'Laki-laki', '0812-1001', 'andi.wijaya@kliniksehat.id', 'STR-001', 150000.00, NULL),
(2, 2, 'dr. Siti Nurhaliza, Sp.A', 'Perempuan', '0813-2002', 'siti.nurhaliza@klinik.id', 'STR-002', 120000.00, NULL),
(3, 3, 'dr. Budi Santoso, Sp.KK', 'Laki-laki', '0814-3003', 'budi.santoso@klinik.id', 'STR-003', 130000.00, NULL),
(4, 4, 'dr. Rina Sari, Sp.JP', 'Perempuan', '0815-4004', 'rina.sari@klinik.id', 'STR-004', 200000.00, NULL),
(5, 5, 'dr. Ahmad Fauzi, Sp.S', 'Laki-laki', '0816-5005', 'ahmad.fauzi@klinik.id', 'STR-005', 180000.00, NULL),
(6, 6, 'dr. Dewi Lestari, Sp.THT', 'Perempuan', '0817-6006', 'dewi.lestari@klinik.id', 'STR-006', 140000.00, NULL),
(7, 7, 'dr. Eko Prasetyo, Sp.M', 'Laki-laki', '0818-7007', 'eko.prasetyo@klinik.id', 'STR-007', 160000.00, NULL),
(8, 8, 'dr. Maya Putri, Sp.G', 'Perempuan', '0819-8008', 'maya.putri@klinik.id', 'STR-008', 135000.00, NULL),
(9, 9, 'dr. Fajar Nugroho, Sp.OT', 'Laki-laki', '0820-9009', 'fajar.nugroho@klinik.id', 'STR-009', 170000.00, NULL),
(10, 10, 'dr. Lisa Angraini, Sp.OG', 'Perempuan', '0821-0010', 'lisa.angraini@klinik.id', 'STR-010', 190000.00, 'dr_Lisa.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokter_klinik`
--

CREATE TABLE `dokter_klinik` (
  `id_dokter` int(11) UNSIGNED NOT NULL,
  `id_klinik` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `dokter_klinik`
--

INSERT INTO `dokter_klinik` (`id_dokter`, `id_klinik`) VALUES
(1, 1),
(1, 6),
(2, 2),
(3, 1),
(3, 3),
(4, 4),
(5, 5),
(6, 1),
(6, 6),
(7, 6),
(8, 1),
(8, 3),
(9, 4),
(9, 5),
(10, 2),
(10, 6);

-- --------------------------------------------------------

--
-- Struktur dari tabel `harga_stok_apotek`
--

CREATE TABLE `harga_stok_apotek` (
  `id_apotek` int(11) UNSIGNED NOT NULL,
  `id_obat` int(11) UNSIGNED NOT NULL,
  `harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stok` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `harga_stok_apotek`
--

INSERT INTO `harga_stok_apotek` (`id_apotek`, `id_obat`, `harga`, `stok`) VALUES
(1, 1, 3505.00, 104),
(1, 2, 5202.00, 140),
(1, 3, 4100.00, 23),
(1, 4, 8502.00, 77),
(1, 5, 9195.00, 128),
(1, 6, 15001.00, 103),
(1, 7, 7803.00, 59),
(1, 8, 11998.00, 109),
(1, 9, 18004.00, 147),
(1, 10, 25002.00, 149),
(1, 11, 14005.00, 128),
(1, 12, 16001.00, 112),
(1, 13, 13496.00, 68),
(1, 14, 8997.00, 82),
(1, 15, 11002.00, 146),
(1, 16, 4197.00, 32),
(1, 17, 6503.00, 46),
(1, 18, 2999.00, 109),
(1, 19, 22005.00, 37),
(1, 20, 19002.00, 93),
(1, 21, 15000.00, 117),
(1, 22, 4000.00, 29),
(1, 23, 5999.00, 143),
(1, 24, 9000.00, 145),
(1, 25, 5002.00, 31),
(1, 26, 3499.00, 68),
(1, 27, 4504.00, 104),
(1, 28, 6001.00, 55),
(1, 29, 4001.00, 22),
(1, 30, 4996.00, 75),
(1, 31, 6999.00, 53),
(1, 32, 35004.00, 136),
(1, 33, 45001.00, 68),
(1, 34, 120001.00, 137),
(1, 35, 14998.00, 138),
(1, 36, 28003.00, 83),
(1, 37, 21998.00, 128),
(1, 38, 11001.00, 81),
(1, 39, 7500.00, 78),
(1, 40, 11999.00, 121),
(1, 41, 17501.00, 46),
(1, 42, 12996.00, 50),
(1, 43, 84996.00, 72),
(1, 44, 9499.00, 146),
(1, 45, 5500.00, 132),
(1, 46, 9801.00, 42),
(1, 47, 21001.00, 27),
(1, 48, 17996.00, 66),
(1, 49, 16505.00, 136),
(1, 50, 6496.00, 93),
(2, 1, 3501.00, 150),
(2, 2, 5204.00, 90),
(2, 3, 4104.00, 77),
(2, 4, 8502.00, 83),
(2, 5, 9204.00, 27),
(2, 6, 14999.00, 71),
(2, 7, 7804.00, 75),
(2, 8, 11999.00, 147),
(2, 9, 18005.00, 148),
(2, 10, 24995.00, 125),
(2, 11, 13997.00, 130),
(2, 12, 15995.00, 88),
(2, 13, 13501.00, 21),
(2, 14, 8999.00, 65),
(2, 15, 10998.00, 149),
(2, 16, 4202.00, 116),
(2, 17, 6498.00, 26),
(2, 18, 2997.00, 83),
(2, 19, 21997.00, 134),
(2, 20, 18996.00, 79),
(2, 21, 14995.00, 67),
(2, 22, 3996.00, 60),
(2, 23, 5998.00, 49),
(2, 24, 9003.00, 21),
(2, 25, 4996.00, 125),
(2, 26, 3496.00, 96),
(2, 27, 4501.00, 77),
(2, 28, 5996.00, 131),
(2, 29, 4000.00, 37),
(2, 30, 4995.00, 95),
(2, 31, 7002.00, 131),
(2, 32, 34999.00, 135),
(2, 33, 44996.00, 83),
(2, 34, 119996.00, 126),
(2, 35, 14998.00, 143),
(2, 36, 27999.00, 143),
(2, 37, 21995.00, 60),
(2, 38, 10997.00, 97),
(2, 39, 7503.00, 43),
(2, 40, 11999.00, 77),
(2, 41, 17505.00, 149),
(2, 42, 12996.00, 121),
(2, 43, 84998.00, 86),
(2, 44, 9502.00, 113),
(2, 45, 5496.00, 49),
(2, 46, 9799.00, 52),
(2, 47, 20996.00, 77),
(2, 48, 18005.00, 81),
(2, 49, 16501.00, 40),
(2, 50, 6503.00, 110),
(3, 1, 3498.00, 150),
(3, 2, 5198.00, 121),
(3, 3, 4099.00, 107),
(3, 4, 8502.00, 144),
(3, 5, 9201.00, 119),
(3, 6, 14997.00, 86),
(3, 7, 7801.00, 89),
(3, 8, 11996.00, 130),
(3, 9, 17999.00, 30),
(3, 10, 24996.00, 76),
(3, 11, 13996.00, 72),
(3, 12, 16000.00, 89),
(3, 13, 13497.00, 120),
(3, 14, 9000.00, 73),
(3, 15, 11004.00, 145),
(3, 16, 4198.00, 147),
(3, 17, 6503.00, 23),
(3, 18, 2998.00, 96),
(3, 19, 21999.00, 72),
(3, 20, 19002.00, 125),
(3, 21, 14999.00, 141),
(3, 22, 3999.00, 86),
(3, 23, 5996.00, 131),
(3, 24, 9005.00, 133),
(3, 25, 4999.00, 63),
(3, 26, 3505.00, 70),
(3, 27, 4498.00, 57),
(3, 28, 6000.00, 101),
(3, 29, 3998.00, 122),
(3, 30, 4997.00, 86),
(3, 31, 7003.00, 57),
(3, 32, 35002.00, 32),
(3, 33, 45002.00, 115),
(3, 34, 120002.00, 20),
(3, 35, 14997.00, 83),
(3, 36, 28005.00, 32),
(3, 37, 22002.00, 52),
(3, 38, 11004.00, 61),
(3, 39, 7499.00, 36),
(3, 40, 11999.00, 110),
(3, 41, 17503.00, 27),
(3, 42, 12999.00, 56),
(3, 43, 84996.00, 47),
(3, 44, 9505.00, 43),
(3, 45, 5496.00, 108),
(3, 46, 9796.00, 103),
(3, 47, 21001.00, 117),
(3, 48, 18004.00, 22),
(3, 49, 16501.00, 66),
(3, 50, 6497.00, 88),
(4, 1, 3501.00, 82),
(4, 2, 5200.00, 28),
(4, 3, 4104.00, 144),
(4, 4, 8504.00, 116),
(4, 5, 9196.00, 57),
(4, 6, 14999.00, 91),
(4, 7, 7795.00, 70),
(4, 8, 12000.00, 55),
(4, 9, 17999.00, 30),
(4, 10, 25001.00, 96),
(4, 11, 13997.00, 142),
(4, 12, 16002.00, 43),
(4, 13, 13503.00, 91),
(4, 14, 9002.00, 80),
(4, 15, 11000.00, 91),
(4, 16, 4198.00, 43),
(4, 17, 6505.00, 47),
(4, 18, 3004.00, 82),
(4, 19, 22005.00, 22),
(4, 20, 18996.00, 44),
(4, 21, 15003.00, 113),
(4, 22, 3998.00, 82),
(4, 23, 6001.00, 122),
(4, 24, 9002.00, 104),
(4, 25, 5001.00, 98),
(4, 26, 3502.00, 93),
(4, 27, 4501.00, 29),
(4, 28, 5996.00, 104),
(4, 29, 3997.00, 68),
(4, 30, 4996.00, 55),
(4, 31, 6999.00, 144),
(4, 32, 34995.00, 68),
(4, 33, 44997.00, 34),
(4, 34, 120003.00, 108),
(4, 35, 15000.00, 50),
(4, 36, 27999.00, 49),
(4, 37, 22004.00, 108),
(4, 38, 11004.00, 37),
(4, 39, 7502.00, 105),
(4, 40, 11998.00, 142),
(4, 41, 17496.00, 78),
(4, 42, 13005.00, 140),
(4, 43, 85001.00, 85),
(4, 44, 9498.00, 61),
(4, 45, 5495.00, 112),
(4, 46, 9803.00, 113),
(4, 47, 21001.00, 21),
(4, 48, 17996.00, 146),
(4, 49, 16497.00, 37),
(4, 50, 6501.00, 150),
(5, 1, 3503.00, 83),
(5, 2, 5205.00, 24),
(5, 3, 4104.00, 97),
(5, 4, 8496.00, 40),
(5, 5, 9201.00, 106),
(5, 6, 14999.00, 124),
(5, 7, 7796.00, 91),
(5, 8, 12000.00, 25),
(5, 9, 18004.00, 99),
(5, 10, 24997.00, 119),
(5, 11, 13998.00, 51),
(5, 12, 16003.00, 123),
(5, 13, 13496.00, 144),
(5, 14, 9004.00, 147),
(5, 15, 11004.00, 131),
(5, 16, 4201.00, 38),
(5, 17, 6500.00, 109),
(5, 18, 3000.00, 40),
(5, 19, 22004.00, 36),
(5, 20, 19002.00, 89),
(5, 21, 15004.00, 26),
(5, 22, 4001.00, 60),
(5, 23, 5998.00, 23),
(5, 24, 9000.00, 128),
(5, 25, 5003.00, 116),
(5, 26, 3502.00, 120),
(5, 27, 4502.00, 45),
(5, 28, 6005.00, 84),
(5, 29, 4000.00, 135),
(5, 30, 4998.00, 106),
(5, 31, 6999.00, 68),
(5, 32, 34995.00, 124),
(5, 33, 44997.00, 54),
(5, 34, 119996.00, 145),
(5, 35, 15000.00, 127),
(5, 36, 28000.00, 56),
(5, 37, 22003.00, 57),
(5, 38, 10998.00, 106),
(5, 39, 7495.00, 89),
(5, 40, 11997.00, 150),
(5, 41, 17497.00, 62),
(5, 42, 13004.00, 127),
(5, 43, 85003.00, 98),
(5, 44, 9504.00, 89),
(5, 45, 5497.00, 148),
(5, 46, 9803.00, 67),
(5, 47, 21003.00, 99),
(5, 48, 18003.00, 56),
(5, 49, 16503.00, 60),
(5, 50, 6500.00, 135);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal_praktik`
--

CREATE TABLE `jadwal_praktik` (
  `id_jadwal` int(11) UNSIGNED NOT NULL,
  `id_dokter` int(11) UNSIGNED NOT NULL,
  `id_klinik` int(11) UNSIGNED NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `kuota` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jadwal_praktik`
--

INSERT INTO `jadwal_praktik` (`id_jadwal`, `id_dokter`, `id_klinik`, `hari`, `jam_mulai`, `jam_selesai`, `kuota`) VALUES
(21, 1, 1, 'Senin', '08:00:00', '12:00:00', 10),
(22, 1, 6, 'Rabu', '13:00:00', '17:00:00', 10),
(23, 2, 2, 'Selasa', '09:00:00', '13:00:00', 8),
(24, 3, 3, 'Senin', '08:00:00', '14:00:00', 12),
(25, 3, 1, 'Kamis', '15:00:00', '19:00:00', 10),
(26, 4, 4, 'Rabu', '10:00:00', '15:00:00', 6),
(27, 5, 5, 'Jumat', '08:00:00', '12:00:00', 8),
(28, 6, 1, 'Selasa', '13:00:00', '17:00:00', 8),
(29, 6, 6, 'Sabtu', '09:00:00', '12:00:00', 5),
(30, 7, 6, 'Senin', '08:00:00', '12:00:00', 10),
(31, 8, 3, 'Rabu', '09:00:00', '13:00:00', 8),
(32, 9, 4, 'Kamis', '13:00:00', '17:00:00', 6),
(33, 10, 2, 'Jumat', '09:00:00', '14:00:00', 8),
(34, 10, 6, 'Sabtu', '13:00:00', '16:00:00', 6);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_obat`
--

CREATE TABLE `kategori_obat` (
  `id_kategori` int(11) UNSIGNED NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori_obat`
--

INSERT INTO `kategori_obat` (`id_kategori`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Analgesik', 'Obat pereda nyeri dan demam'),
(2, 'Antibiotik', 'Obat untuk mengatasi infeksi bakteri'),
(3, 'Vitamin & Suplemen', 'Suplemen penunjang kesehatan tubuh'),
(4, 'Obat Batuk & Flu', 'Obat untuk meredakan batuk dan flu'),
(5, 'Antihistamin', 'Obat alergi dan gatal'),
(6, 'Salep & Kulit', 'Obat oles dan perawatan kulit'),
(7, 'Obat Pencernaan', 'Obat untuk gangguan pencernaan'),
(8, 'Obat Jantung', 'Obat untuk kesehatan jantung dan tekanan darah'),
(9, 'Diabetes', 'Obat dan alat untuk penderita diabetes'),
(10, 'Alat Kesehatan', 'Peralatan dan perlengkapan medis');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int(11) UNSIGNED NOT NULL,
  `id_user` int(11) UNSIGNED NOT NULL,
  `id_obat` int(11) UNSIGNED NOT NULL,
  `id_apotek` int(11) UNSIGNED DEFAULT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `klinik`
--

CREATE TABLE `klinik` (
  `id_klinik` int(11) UNSIGNED NOT NULL,
  `id_kota` int(11) UNSIGNED NOT NULL,
  `nama_klinik` varchar(150) NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `klinik`
--

INSERT INTO `klinik` (`id_klinik`, `id_kota`, `nama_klinik`, `alamat`, `no_telp`, `email`) VALUES
(1, 1, 'Klinik Sehat Menteng', 'Jl. MH Thamrin No. 10, Jakarta', '021-111001', 'menteng@mydkita.com'),
(2, 2, 'Klinik Sehat Bogor', 'Jl. Pajajaran No. 88, Bogor', '022-111002', 'bogor@mydkita.com'),
(3, 3, 'Klinik Sehat Bekasi', 'Jl. Ahmad Yani No. 22, Bekasi', '021-111003', 'bekasi@mydkita.com'),
(4, 4, 'Klinik Sehat Tangerang', 'Jl. Ciledug Raya No. 25, Tangerang', '021-111004', 'tangerang@mydkita.com'),
(5, 5, 'Klinik Sehat Depok', 'Jl. Margonda Raya No. 45, Depok', '021-111005', 'depok@mydkita.com'),
(6, 1, 'Klinik Sehat Jakarta Selatan', 'Jl. Cilandak Raya No. 5, Jakarta', '021-111006', 'cilandak@mydkita.com'),
(7, 4, 'Klinik Sehat Ciledug', 'Jl. Ciledug Raya No. 25, Tangerang', '021-555201', 'ciledug@mydkita.com'),
(8, 4, 'Klinik Medika Tangerang', 'Jl. Gatot Subroto No. 88, Tangerang', '021-555202', 'medika.tangerang@mydkita.com'),
(9, 5, 'Klinik Sehat Margonda', 'Jl. Margonda Raya No. 45, Depok', '021-555301', 'margonda@mydkita.com'),
(10, 5, 'Klinik Medika Depok', 'Jl. Sudirman No. 10, Depok', '021-555302', 'medika.depok@mydkita.com'),
(11, 3, 'klinik medika sumarecon bekasi', 'sumarecon bekasi', '6154849848', 'bekasismc@sehat.id');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kota`
--

CREATE TABLE `kota` (
  `id_kota` int(11) UNSIGNED NOT NULL,
  `nama_kota` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kota`
--

INSERT INTO `kota` (`id_kota`, `nama_kota`) VALUES
(1, 'Jakarta'),
(2, 'Bogor'),
(3, 'Bekasi'),
(4, 'Tangerang'),
(5, 'Depok');

-- --------------------------------------------------------

--
-- Struktur dari tabel `metode_pembayaran`
--

CREATE TABLE `metode_pembayaran` (
  `id_metode` int(11) UNSIGNED NOT NULL,
  `nama_metode` varchar(100) NOT NULL,
  `tipe` enum('bank','ewallet') NOT NULL DEFAULT 'bank',
  `nomor` varchar(50) NOT NULL,
  `pemilik` varchar(150) NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `metode_pembayaran`
--

INSERT INTO `metode_pembayaran` (`id_metode`, `nama_metode`, `tipe`, `nomor`, `pemilik`, `aktif`) VALUES
(1, 'Transfer Bank BCA', 'bank', '1234567890', 'Klinik Sehat', 1),
(2, 'Transfer Bank BNI', 'bank', '0987654321', 'Klinik Sehat', 1),
(3, 'DANA', 'ewallet', '081234000001', 'Klinik Sehat', 1),
(4, 'OVO', 'ewallet', '081234000002', 'Klinik Sehat', 1),
(5, 'GoPay', 'ewallet', '081234000003', 'Klinik Sehat', 1),
(6, 'QRIS', 'ewallet', '-', 'Klinik Sehat', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` int(11) UNSIGNED NOT NULL,
  `id_user` int(11) UNSIGNED NOT NULL,
  `judul` varchar(150) NOT NULL,
  `isi` text DEFAULT NULL,
  `status` enum('belum dibaca','dibaca') NOT NULL DEFAULT 'belum dibaca',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`id_notifikasi`, `id_user`, `judul`, `isi`, `status`, `created_at`) VALUES
(1, 3, 'Pesanan Dikirim', 'Pesanan PSN-20250605-001 telah kami kirim.', 'dibaca', '2025-06-06 09:00:00'),
(2, 4, 'Pesanan Selesai', 'Terima kasih, pesanan Anda telah selesai.', 'dibaca', '2025-06-14 09:00:00'),
(3, 6, 'Pesanan Dikirim', 'Pesanan PSN-20250715-004 sedang dikirim.', 'belum dibaca', '2025-07-16 09:00:00'),
(4, 8, 'Pembayaran Diterima', 'Pembayaran pesanan PSN-20250814-008 lunas.', 'belum dibaca', '2025-08-15 09:00:00'),
(5, 2, 'Menunggu Pembayaran', 'Segera lakukan pembayaran PSN-20250819-009.', 'belum dibaca', '2025-08-19 10:05:00'),
(6, 11, 'Pesanan Dibatalkan', 'Pesanan PSN-20250925-019 dibatalkan.', 'belum dibaca', '2025-09-25 09:05:00'),
(7, 2, 'Pesanan Dikirim', 'Pesanan PSN-20250928-020 sedang dikirim.', 'belum dibaca', '2025-09-29 09:00:00'),
(8, 2, 'Pesanan Dibuat', 'Pesanan PSN-20260716-001 berhasil dibuat, silakan bayar.', 'belum dibaca', '2026-07-16 17:39:20'),
(9, 2, 'Pesanan Dibuat', 'Pesanan PSN-20260716-002 berhasil dibuat dan sudah diproses.', 'belum dibaca', '2026-07-16 17:44:22'),
(10, 2, 'Pesanan Dibuat', 'Pesanan PSN-20260716-003 berhasil dibuat. Silakan lakukan pembayaran.', 'belum dibaca', '2026-07-16 17:58:34'),
(11, 2, 'Pembayaran Dikonfirmasi', 'Pesanan PSN-20260716-003 telah dibayar dan sedang diproses.', 'belum dibaca', '2026-07-16 17:58:43'),
(12, 2, 'Pesanan Dibuat', 'Pesanan PSN-20260716-004 berhasil dibuat. Silakan lakukan pembayaran.', 'belum dibaca', '2026-07-16 18:39:10'),
(13, 2, 'Pembayaran Dikonfirmasi', 'Pesanan PSN-20260716-004 telah dibayar dan sedang diproses.', 'belum dibaca', '2026-07-16 18:39:21'),
(14, 2, 'Pesanan Dibuat', 'Pesanan PSN-20260716-005 berhasil dibuat. Silakan lakukan pembayaran.', 'belum dibaca', '2026-07-16 20:08:29'),
(15, 2, 'Pembayaran Dikonfirmasi', 'Pesanan PSN-20260716-005 telah dibayar dan sedang diproses.', 'belum dibaca', '2026-07-16 20:08:35'),
(16, 2, 'Pesanan Dibuat', 'Pesanan PSN-20260717-001 berhasil dibuat. Silakan lakukan pembayaran.', 'belum dibaca', '2026-07-17 10:41:00'),
(17, 2, 'Pembayaran Dikonfirmasi', 'Pesanan PSN-20260717-001 telah dibayar dan sedang diproses.', 'belum dibaca', '2026-07-17 10:41:12'),
(18, 2, 'Pesanan Dibuat', 'Pesanan PSN-20260717-002 berhasil dibuat. Silakan lakukan pembayaran.', 'belum dibaca', '2026-07-17 11:26:24'),
(19, 2, 'Pembayaran Dikonfirmasi', 'Pesanan PSN-20260717-002 telah dibayar dan sedang diproses.', 'belum dibaca', '2026-07-17 11:26:33'),
(20, 5, 'Pesanan Diterima', 'Pesanan PSN-20250703-003 telah diterima.', 'belum dibaca', '2026-07-17 11:31:29'),
(21, 13, 'Booking Diterima', 'Booking BKG-20260717-0001 berhasil dibuat.', 'belum dibaca', '2026-07-17 13:28:37'),
(22, 13, 'Pesanan Dibuat', 'Pesanan PSN-20260717-003 berhasil dibuat. Silakan lakukan pembayaran.', 'belum dibaca', '2026-07-17 13:29:09'),
(23, 13, 'Pembayaran Dikonfirmasi', 'Pesanan PSN-20260717-003 telah dibayar dan sedang diproses.', 'belum dibaca', '2026-07-17 13:36:23'),
(24, 13, 'Pesanan Diterima', 'Pesanan PSN-20260717-003 telah diterima.', 'belum dibaca', '2026-07-17 13:38:02'),
(25, 13, 'Pesanan Selesai', 'Terima kasih, pesanan PSN-20260717-003 selesai.', 'belum dibaca', '2026-07-17 13:38:03'),
(26, 13, 'Pesanan Dibuat', 'Pesanan PSN-20260717-004 berhasil dibuat. Silakan lakukan pembayaran.', 'belum dibaca', '2026-07-17 19:57:12'),
(27, 13, 'Pembayaran Dikonfirmasi', 'Pesanan PSN-20260717-004 telah dibayar dan sedang diproses.', 'belum dibaca', '2026-07-17 19:57:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `obat`
--

CREATE TABLE `obat` (
  `id_obat` int(11) UNSIGNED NOT NULL,
  `kode_obat` varchar(20) NOT NULL,
  `nama_obat` varchar(150) NOT NULL,
  `id_kategori` int(11) UNSIGNED DEFAULT NULL,
  `id_supplier` int(11) UNSIGNED DEFAULT NULL,
  `harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stok` int(11) NOT NULL DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `tanggal_expired` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `obat`
--

INSERT INTO `obat` (`id_obat`, `kode_obat`, `nama_obat`, `id_kategori`, `id_supplier`, `harga`, `stok`, `deskripsi`, `gambar`, `tanggal_expired`, `created_at`) VALUES
(1, 'OB-00001', 'Paracetamol 500mg', 1, 1, 3500.00, 120, 'Pereda nyeri dan penurun demam.', 'Paracetamol 500mg.png', '2026-12-31', '2025-06-01 08:00:00'),
(2, 'OB-00002', 'Ibuprofen 400mg', 1, 3, 5200.00, 90, 'Antiinflamasi untuk nyeri ringan.', 'ibuprofen 400mg.png', '2026-10-15', '2025-06-01 08:05:00'),
(3, 'OB-00003', 'Aspirin 80mg', 1, 4, 4100.00, 75, 'Mencegah penggumpalan darah.', 'aspirin.png', '2027-03-20', '2025-06-01 08:10:00'),
(4, 'OB-00004', 'Amoxilin 500mg', 2, 2, 8500.00, 54, 'Antibiotik untuk infeksi saluran napas.', 'amoxillin.jpg', '2026-11-30', '2025-06-01 08:15:00'),
(5, 'OB-00005', 'Ciprofloxacin 500mg', 2, 5, 9200.00, 50, 'Antibiotik untuk infeksi saluran kemih.', 'ciprofloxacin500mg.png', '2027-01-25', '2025-06-01 08:20:00'),
(6, 'OB-00006', 'Azithromycin 500mg', 2, 3, 15000.00, 39, 'Antibiotik untuk infeksi pernapasan.', 'azithromycinn.png', '2026-09-18', '2025-06-01 08:25:00'),
(7, 'OB-00007', 'Doxycycline 100mg', 2, 4, 7800.00, 55, 'Antibiotik broad spectrum.', 'doxycycline100mg.png', '2027-02-10', '2025-06-01 08:30:00'),
(8, 'OB-00008', 'Vitamin C 500mg', 3, 1, 12000.00, 200, 'Menjaga daya tahan tubuh.', 'hevit vitamin.png', '2027-05-01', '2025-06-01 08:35:00'),
(9, 'OB-00009', 'Vitamin D3 1000IU', 3, 3, 18000.00, 150, 'Membantu penyerapan kalsium.', 'Vitamin-D3.png', '2027-06-15', '2025-06-01 08:40:00'),
(10, 'OB-00010', 'Multivitamin Hemaviton', 3, 5, 25000.00, 130, 'Suplemen multivitamin harian.', 'vitamin hemaviton.png', '2027-04-22', '2025-06-01 08:45:00'),
(11, 'OB-00011', 'Zinc Tablet 20mg', 3, 2, 14000.00, 100, 'Suplemen zinc untuk imunitas.', 'zinc tablet.png', '2027-03-30', '2025-06-01 08:50:00'),
(12, 'OB-00012', 'Obat Batuk Hitam', 4, 4, 25000.00, 80, 'Bebas batuk berdahak.', 'obh ika syrup 25k.png', '2026-12-05', '2025-06-01 08:55:00'),
(13, 'OB-00013', 'OBH Combi Anak', 4, 3, 13500.00, 95, 'Obat batuk untuk anak.', 'OBH combi anak.png ', '2027-01-12', '2025-06-01 09:00:00'),
(14, 'OB-00014', 'Pulse Oximeter', 10, 5, 95000.00, 70, 'Untuk mengukur kadar oksigen dalam darah.', 'pulse.png', '2027-02-28', '2025-06-01 09:05:00'),
(15, 'OB-00015', 'Panadol Flu & Cold', 4, 1, 11000.00, 85, 'Redakan gejala flu.', 'pndol.png', '2026-10-20', '2025-06-01 09:10:00'),
(16, 'OB-00016', 'Cetirizine 10mg', 5, 2, 4200.00, 110, 'Obat alergi dan gatal kulit.', 'Cetirizine 10 mg.png', '2027-07-10', '2025-06-01 09:15:00'),
(17, 'OB-00017', 'Loratadine 10mg', 5, 4, 6500.00, 90, 'Antihistamin tanpa kantuk.', 'Loratadine10mg.png', '2027-08-05', '2025-06-01 09:20:00'),
(18, 'OB-00018', 'CTM 4mg', 5, 5, 3000.00, 130, 'Redakan alergi ringan.', 'CTM 4mg.png', '2026-11-11', '2025-06-01 09:25:00'),
(19, 'OB-00019', 'Betadine Solution 60ml', 6, 1, 22000.00, 60, 'Antiseptik luka luar.', 'betadine solution12.png', '2028-01-15', '2025-06-01 09:30:00'),
(20, 'OB-00020', 'Hydrocortisone Cream', 6, 3, 19000.00, 45, 'Redakan peradangan kulit.', 'hydrocortisonecream.png', '2027-09-20', '2025-06-01 09:35:00'),
(21, 'OB-00021', 'Betamethasone Salep', 6, 4, 15000.00, 50, 'Salep untuk gatal dan ruam.', 'Betamethasone salep.png', '2027-05-30', '2025-06-01 09:40:00'),
(22, 'OB-00022', 'Antimo 4mg', 7, 2, 4000.00, 139, 'Obat mual dan pusing.', 'Antimo 4mg.png', '2027-10-10', '2025-06-01 09:45:00'),
(23, 'OB-00023', 'Antasida Doen', 7, 5, 6000.00, 113, 'Redakan nyeri lambung.', 'doen.png', '2027-11-25', '2025-06-01 09:50:00'),
(24, 'OB-00024', 'Domperidone 10mg', 7, 3, 9000.00, 80, 'Obat mual dan muntah.', 'domperidone.png', '2026-12-22', '2025-06-01 09:55:00'),
(25, 'OB-00025', 'Loperamide 2mg', 7, 4, 5000.00, 100, 'Redakan diare akut.', 'loperamid 2mg.png', '2027-03-15', '2025-06-01 10:00:00'),
(26, 'OB-00026', 'Captopril 25mg', 8, 1, 3500.00, 200, 'Obat darah tinggi.', 'captopril 25mg.png', '2027-12-01', '2025-06-01 10:05:00'),
(27, 'OB-00027', 'Amlodipine 5mg', 8, 2, 4500.00, 178, 'Pengendali tekanan darah.', 'amlodipine.png', '2027-08-18', '2025-06-01 10:10:00'),
(28, 'OB-00028', 'Bisoprolol 5mg', 8, 5, 6000.00, 150, 'Obat untuk gagal jantung.', 'Bisoprolol 5mg.png', '2027-06-30', '2025-06-01 10:15:00'),
(29, 'OB-00029', 'Metformin 500mg', 9, 3, 4000.00, 220, 'Obat diabetes tipe 2.', 'metformin 500mg.png', '2027-04-12', '2025-06-01 10:20:00'),
(30, 'OB-00030', 'Glibenclamide 5mg', 9, 4, 5000.00, 170, 'Menurunkan gula darah.', 'glibenclamide 5mg.png', '2027-02-20', '2025-06-01 10:25:00'),
(31, 'OB-00031', 'Glimepiride 2mg', 9, 5, 7000.00, 140, 'Sulfonilurea untuk diabetes.', 'GLIMEPIRIDE 2mg.png', '2027-09-05', '2025-06-01 10:30:00'),
(32, 'OB-00032', 'Termometer Digital', 10, 1, 35000.00, 60, 'Ukur suhu tubuh akurat.', 'termometer digital.png', '2030-01-01', '2025-06-01 10:35:00'),
(33, 'OB-00033', 'Omeprazole 20mg', 7, 2, 16000.00, 100, 'Mengatasi Asam Lambung, Gerd, dan tukak lambung.', 'Ome.png', '2028-06-01', '2025-06-01 10:40:00'),
(34, 'OB-00034', 'Tensimeter Digital', 10, 3, 120000.00, 30, 'Monitor tekanan darah.', 'tensimeter.png', '2030-01-01', '2025-06-01 10:45:00'),
(35, 'OB-00035', 'Handsanitizer Instance', 10, 4, 15000.00, 150, 'Pembersih tangan praktis.', 'handsanitizer 15k.png', '2028-03-15', '2025-06-01 10:50:00'),
(36, 'OB-00036', 'Paracetamol Sirup Anak', 1, 5, 28000.00, 70, 'Demam pada anak.', 'Sirup Anak.png', '2026-09-30', '2025-06-01 10:55:00'),
(37, 'OB-00037', 'Amoxiclav 625mg', 2, 1, 22000.00, 46, 'Kombinasi antibiotik kuat.', 'amoxiclav.jpg', '2026-12-18', '2025-06-01 11:00:00'),
(38, 'OB-00038', 'Vitamin B Complex', 3, 2, 11000.00, 160, 'Suplemen vitamin B.', 'Vitamin B .png', '2027-07-22', '2025-06-01 11:05:00'),
(39, 'OB-00039', 'Promag 75mg', 7, 3, 7500.00, 130, 'Redakan maag.', 'Promag 75mg.png', '2027-10-30', '2025-06-01 11:10:00'),
(40, 'OB-00040', 'Bisolvon 8mg', 4, 4, 12000.00, 90, 'Peluruh dahak.', 'Bisolvon 8mg.png', '2027-01-05', '2025-06-01 11:15:00'),
(41, 'OB-00041', 'Claritin 10mg', 5, 5, 17500.00, 65, 'Antialergi harian.', 'CLARITIN 10MG.png', '2027-11-12', '2025-06-01 11:20:00'),
(42, 'OB-00042', 'Caladine Gel', 6, 1, 13000.00, 85, 'Redakan gatal akibat gigitan.', 'caladine gel.png', '2027-08-28', '2025-06-01 11:25:00'),
(43, 'OB-00043', 'Insulin Injeksi', 9, 3, 85000.00, 25, 'Kontrol gula darah penderita diabetes.', 'insulin injeksi 85k.png', '2026-11-20', '2025-06-01 11:30:00'),
(44, 'OB-00044', 'Atorvastatin 10mg', 8, 2, 9500.00, 110, 'Penurun kolesterol.', 'Atorvastatin.png', '2027-05-18', '2025-06-01 11:35:00'),
(45, 'OB-00045', 'Ranitidine 150mg', 7, 5, 5500.00, 95, 'Obat lambung dan maag.', 'ranitidine 150mg.png', '2027-03-08', '2025-06-01 11:40:00'),
(46, 'OB-00046', 'Komet Sirup', 4, 4, 9800.00, 75, 'Batuk kering dan berdahak.', 'komet sirup.png', '2027-02-14', '2025-06-01 11:45:00'),
(47, 'OB-00047', 'Vitamin E 400iu', 3, 1, 160000.00, 100, 'Antioksidan untuk kulit.', 'Vitamin E.png', '2027-09-12', '2025-06-01 11:50:00'),
(48, 'OB-00048', 'Betadine Cream', 6, 2, 18000.00, 70, 'Antiseptik dan perawatan luka.', 'betadinecream.png', '2027-10-05', '2025-06-01 11:55:00'),
(49, 'OB-00049', 'Neurobion', 3, 3, 16500.00, 120, 'Vitamin B1 B6 B12 saraf.', 'neurobion.png', '2027-06-25', '2025-06-01 12:00:00'),
(50, 'OB-00050', 'ORS Orsedia', 7, 5, 6500.00, 140, 'Cegah dehidrasi.', 'ORS Orsedia.png', '2028-01-20', '2025-06-01 12:05:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) UNSIGNED NOT NULL,
  `id_pesanan` int(11) UNSIGNED NOT NULL,
  `id_metode` int(11) UNSIGNED DEFAULT NULL,
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `tanggal_bayar` datetime DEFAULT NULL,
  `status` enum('Menunggu','Lunas','Ditolak') NOT NULL DEFAULT 'Menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_pesanan`, `id_metode`, `bukti_transfer`, `tanggal_bayar`, `status`) VALUES
(1, 1, 1, 'bukti_001.jpg', '2025-06-05 11:00:00', 'Lunas'),
(2, 2, 3, 'bukti_002.jpg', '2025-06-12 15:00:00', 'Lunas'),
(3, 3, 4, 'bukti_003.jpg', '2025-07-03 10:00:00', 'Lunas'),
(4, 4, 5, 'bukti_004.jpg', '2025-07-15 12:00:00', 'Lunas'),
(5, 5, 6, 'bukti_005.jpg', '2025-07-22 16:00:00', 'Lunas'),
(6, 6, 1, 'bukti_006.jpg', '2025-08-02 09:00:00', 'Lunas'),
(7, 7, 3, 'bukti_007.jpg', '2025-08-08 14:00:00', 'Lunas'),
(8, 8, 4, 'bukti_008.jpg', '2025-08-14 17:00:00', 'Lunas'),
(9, 9, 1, NULL, NULL, 'Menunggu'),
(10, 10, 5, 'bukti_010.jpg', '2025-08-25 13:00:00', 'Lunas'),
(11, 11, 6, 'bukti_011.jpg', '2025-09-01 10:00:00', 'Lunas'),
(12, 12, 1, 'bukti_012.jpg', '2025-09-04 15:00:00', 'Lunas'),
(13, 13, 3, 'bukti_013.jpg', '2025-09-09 12:00:00', 'Lunas'),
(14, 14, 4, 'bukti_014.jpg', '2025-09-12 16:00:00', 'Lunas'),
(15, 15, 5, 'bukti_015.jpg', '2025-09-15 09:00:00', 'Lunas'),
(16, 16, 1, NULL, NULL, 'Menunggu'),
(17, 17, 6, 'bukti_017.jpg', '2025-09-20 11:00:00', 'Lunas'),
(18, 18, 1, 'bukti_018.jpg', '2025-09-22 14:00:00', 'Lunas'),
(19, 19, 3, 'bukti_019.jpg', NULL, 'Ditolak'),
(20, 20, 3, 'bukti_020.jpg', '2025-09-28 17:00:00', 'Lunas'),
(21, 21, 5, '6a58b509613281.01203746.png', '2026-07-16 17:40:09', 'Lunas'),
(22, 22, 3, NULL, NULL, 'Lunas'),
(23, 23, 3, NULL, '2026-07-16 17:58:43', 'Lunas'),
(24, 24, 1, NULL, '2026-07-16 18:39:21', 'Lunas'),
(25, 25, 1, NULL, '2026-07-16 20:08:35', 'Lunas'),
(26, 26, 5, NULL, '2026-07-17 10:41:12', 'Lunas'),
(27, 27, 5, NULL, '2026-07-17 11:26:33', 'Lunas'),
(28, 28, 5, NULL, '2026-07-17 13:36:23', 'Lunas'),
(29, 29, 1, NULL, '2026-07-17 19:57:24', 'Lunas');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `nama_app` varchar(100) DEFAULT 'Klinik Sehat',
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama_app`, `email`, `telepon`, `alamat`) VALUES
(1, 'Klinik Sehat', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengiriman`
--

CREATE TABLE `pengiriman` (
  `id_pengiriman` int(11) UNSIGNED NOT NULL,
  `id_pesanan` int(11) UNSIGNED NOT NULL,
  `ekspedisi` varchar(50) DEFAULT NULL,
  `nomor_resi` varchar(50) DEFAULT NULL,
  `status` enum('Dikemas','Dikirim','Diterima','Gagal') NOT NULL DEFAULT 'Dikemas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengiriman`
--

INSERT INTO `pengiriman` (`id_pengiriman`, `id_pesanan`, `ekspedisi`, `nomor_resi`, `status`) VALUES
(1, 1, 'JNE', 'JNE123456781', 'Diterima'),
(2, 2, 'J&T', 'JNT123456782', 'Diterima'),
(3, 3, 'Sicepat', 'SCP123456783', 'Diterima'),
(4, 4, 'JNE', 'JNE123456784', 'Dikirim'),
(5, 5, 'Pos Indonesia', 'POS123456785', 'Diterima'),
(6, 6, 'J&T', 'JNT123456786', 'Dikemas'),
(7, 7, 'Sicepat', 'SCP123456787', 'Dikirim'),
(8, 8, 'Ninja Express', 'NIN123456788', 'Diterima'),
(9, 9, 'JNE', NULL, 'Dikemas'),
(10, 10, 'J&T', 'JNT123456790', 'Dikirim'),
(11, 11, 'Sicepat', 'SCP123456791', 'Diterima'),
(12, 12, 'JNE', NULL, 'Dikemas'),
(13, 13, 'Pos Indonesia', 'POS123456793', 'Diterima'),
(14, 14, 'J&T', 'JNT123456794', 'Dikirim'),
(15, 15, 'Ninja Express', 'NIN123456795', 'Diterima'),
(16, 16, 'JNE', NULL, 'Dikemas'),
(17, 17, 'Sicepat', NULL, 'Dikemas'),
(18, 18, 'J&T', 'JNT123456798', 'Diterima'),
(19, 19, 'JNE', NULL, 'Gagal'),
(20, 20, 'Pos Indonesia', 'POS123456800', 'Dikirim'),
(21, 28, 'Sicepat', 'SCP123456783', 'Diterima');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) UNSIGNED NOT NULL,
  `kode_pesanan` varchar(30) NOT NULL,
  `id_user` int(11) UNSIGNED NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('Menunggu Pembayaran','Diproses','Dikirim','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu Pembayaran'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `kode_pesanan`, `id_user`, `tanggal`, `total`, `status`) VALUES
(1, 'PSN-20250605-001', 3, '2025-06-05 10:12:00', 19000.00, 'Selesai'),
(2, 'PSN-20250612-002', 4, '2025-06-12 14:30:00', 15500.00, 'Selesai'),
(3, 'PSN-20250703-003', 5, '2025-07-03 09:05:00', 16500.00, 'Selesai'),
(4, 'PSN-20250715-004', 6, '2025-07-15 11:45:00', 46000.00, 'Dikirim'),
(5, 'PSN-20250722-005', 7, '2025-07-22 15:20:00', 67000.00, 'Selesai'),
(6, 'PSN-20250802-006', 8, '2025-08-02 08:40:00', 34400.00, 'Diproses'),
(7, 'PSN-20250808-007', 9, '2025-08-08 13:10:00', 41000.00, 'Dikirim'),
(8, 'PSN-20250814-008', 10, '2025-08-14 16:55:00', 38500.00, 'Selesai'),
(9, 'PSN-20250819-009', 2, '2025-08-19 10:00:00', 12000.00, 'Menunggu Pembayaran'),
(10, 'PSN-20250825-010', 11, '2025-08-25 12:30:00', 165000.00, 'Dikirim'),
(11, 'PSN-20250901-011', 3, '2025-09-01 09:15:00', 15700.00, 'Selesai'),
(12, 'PSN-20250904-012', 4, '2025-09-04 14:00:00', 32000.00, 'Diproses'),
(13, 'PSN-20250909-013', 5, '2025-09-09 11:20:00', 30000.00, 'Selesai'),
(14, 'PSN-20250912-014', 6, '2025-09-12 15:40:00', 20500.00, 'Dikirim'),
(15, 'PSN-20250915-015', 7, '2025-09-15 08:25:00', 25000.00, 'Selesai'),
(16, 'PSN-20250918-016', 8, '2025-09-18 17:05:00', 92000.00, 'Menunggu Pembayaran'),
(17, 'PSN-20250920-017', 9, '2025-09-20 10:50:00', 22800.00, 'Diproses'),
(18, 'PSN-20250922-018', 10, '2025-09-22 13:35:00', 15000.00, 'Selesai'),
(19, 'PSN-20250925-019', 11, '2025-09-25 09:00:00', 65000.00, 'Dibatalkan'),
(20, 'PSN-20250928-020', 2, '2025-09-28 16:10:00', 47000.00, 'Dikirim'),
(21, 'PSN-20260716-001', 2, '2026-07-16 17:39:20', 43000.00, 'Diproses'),
(22, 'PSN-20260716-002', 2, '2026-07-16 17:44:22', 14500.00, 'Diproses'),
(23, 'PSN-20260716-003', 2, '2026-07-16 17:58:34', 36500.00, 'Diproses'),
(24, 'PSN-20260716-004', 2, '2026-07-16 18:39:10', 14500.00, 'Diproses'),
(25, 'PSN-20260716-005', 2, '2026-07-16 20:08:29', 4500.00, 'Diproses'),
(26, 'PSN-20260717-001', 2, '2026-07-17 10:41:00', 36500.00, 'Diproses'),
(27, 'PSN-20260717-002', 2, '2026-07-17 11:26:24', 18500.00, 'Diproses'),
(28, 'PSN-20260717-003', 13, '2026-07-17 13:29:09', 14500.00, 'Selesai'),
(29, 'PSN-20260717-004', 13, '2026-07-17 19:57:12', 26500.00, 'Diproses');

-- --------------------------------------------------------

--
-- Struktur dari tabel `review`
--

CREATE TABLE `review` (
  `id_review` int(11) UNSIGNED NOT NULL,
  `id_user` int(11) UNSIGNED NOT NULL,
  `id_obat` int(11) UNSIGNED NOT NULL,
  `rating` tinyint(1) NOT NULL DEFAULT 5,
  `komentar` text DEFAULT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `review`
--

INSERT INTO `review` (`id_review`, `id_user`, `id_obat`, `rating`, `komentar`, `tanggal`) VALUES
(1, 3, 1, 5, 'Obatnya manjur, demam langsung turun.', '2025-06-06 10:00:00'),
(2, 4, 4, 4, 'Sembut embuh setelah minum ini.', '2025-06-13 09:00:00'),
(3, 5, 3, 5, 'Sangat membantu untuk jantung.', '2025-07-04 11:00:00'),
(4, 6, 9, 5, 'Vitaminnya bagus dan original.', '2025-07-16 13:00:00'),
(5, 7, 19, 4, 'Antiseptiknya wangi dan ampuh.', '2025-07-23 15:00:00'),
(6, 8, 12, 5, 'Anak saya cepat sembuh batuknya.', '2025-08-15 10:00:00'),
(7, 2, 29, 4, 'Rutin minum gula darah stabil.', '2025-08-20 12:00:00'),
(8, 11, 34, 5, 'Tensimeter akurat dan mudah dipakai.', '2025-08-26 14:00:00'),
(9, 3, 8, 5, 'Day tahan tubuh jadi lebih kuat.', '2025-09-02 09:00:00'),
(10, 4, 20, 3, 'Lumayan, tapi agak lengket.', '2025-09-05 16:00:00'),
(11, 5, 6, 5, 'Infeksi tenggorokan cepat reda.', '2025-09-10 10:00:00'),
(12, 6, 22, 4, 'Mual saya langsung hilang.', '2025-09-13 11:00:00'),
(13, 7, 10, 5, 'Multivitamin nyaman di perut.', '2025-09-16 08:00:00'),
(14, 8, 43, 5, 'Insulin berkualitas untuk diabetes.', '2025-09-21 13:00:00'),
(15, 9, 7, 4, 'Efeknya cepat, recommended.', '2025-09-22 15:00:00'),
(16, 10, 27, 5, 'Tekanan darah jadi normal.', '2025-09-23 09:00:00'),
(17, 11, 32, 4, 'Termometer praktis dan cepat.', '2025-09-26 10:00:00'),
(18, 2, 38, 5, 'Badan jadi lebih fit.', '2025-09-29 12:00:00'),
(19, 3, 16, 4, 'Alergi kulit saya reda.', '2025-06-08 10:00:00'),
(20, 5, 47, 5, 'Kulit jadi lebih sehat.', '2025-07-05 14:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `spesialis`
--

CREATE TABLE `spesialis` (
  `id_spesialis` int(11) UNSIGNED NOT NULL,
  `nama_spesialis` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `spesialis`
--

INSERT INTO `spesialis` (`id_spesialis`, `nama_spesialis`, `deskripsi`) VALUES
(1, 'Penyakit Dalam', 'Spesialis penyakit dalam'),
(2, 'Anak', 'Spesialis kesehatan anak'),
(3, 'Kulit & Kelamin', 'Spesialis kulit dan kelamin'),
(4, 'Jantung', 'Spesialis jantung dan pembuluh darah'),
(5, 'Saraf', 'Spesialis saraf'),
(6, 'THT', 'Spesialis telinga, hidung, tenggorokan'),
(7, 'Mata', 'Spesialis mata'),
(8, 'Gigi', 'Spesialis gigi dan mulut'),
(9, 'Orthopedi', 'Spesialis tulang dan sendi'),
(10, 'Kebidanan & Kandungan', 'Spesialis kebidanan dan kandungan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` int(11) UNSIGNED NOT NULL,
  `nama_supplier` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `nama_supplier`, `alamat`, `no_hp`, `email`) VALUES
(1, 'PT Kimia Farma', 'Jl. RE Martadinata No. 10, Jakarta', '021-555001', 'info@kimiafarma.co.id'),
(2, 'PT Indofarma', 'Jl. Sukarno Hatta No. 20, Bandung', '022-555002', 'cs@indofarma.co.id'),
(3, 'PT Kalbe Farma', 'Jl. Pulo Mas Raya No. 5, Jakarta', '021-555003', 'support@kalbe.co.id'),
(4, 'PT Sanbe Farma', 'Jl. Cikutra No. 14, Bandung', '022-555004', 'halo@sanbe.co.id'),
(5, 'PT Dexa Medica', 'Jl. Industri No. 8, Cikarang', '021-555005', 'kontak@dexa.co.id');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `nama`, `email`, `password`, `no_hp`, `alamat`, `birthdate`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@apotek.com', '$2y$10$3Buaz.yIWtX4xAdA0o6AJOpSJxotottesd34OfWz4kAsWBrk.hOIu', '081234000001', 'Jl. Klinik Sehat No. 1, Jakarta', NULL, 'admin', '2025-01-01 08:00:00'),
(2, 'Budi Santoso', 'budi@gmail.com', '$2y$10$l/u7HNgOAOyMRD7DRjMHieQVm.kq0WeGcPtfeOPuJNvQ75PlmYl2a', '081234000002', 'jnjbb', NULL, 'customer', '2025-02-03 09:15:00'),
(3, 'Siti Rahayu', 'siti@gmail.com', '$2y$10$l/u7HNgOAOyMRD7DRjMHieQVm.kq0WeGcPtfeOPuJNvQ75PlmYl2a', '081234000003', 'Jl. Mawar No. 8, Surabaya', NULL, 'customer', '2025-02-10 10:00:00'),
(4, 'Ahmad Fauzi', 'ahmad@gmail.com', '$2y$10$l/u7HNgOAOyMRD7DRjMHieQVm.kq0WeGcPtfeOPuJNvQ75PlmYl2a', '081234000004', 'Jl. Pahlawan No. 45, Yogyakarta', NULL, 'customer', '2025-03-01 11:30:00'),
(5, 'Dewi Lestari', 'dewi@gmail.com', '$2y$10$l/u7HNgOAOyMRD7DRjMHieQVm.kq0WeGcPtfeOPuJNvQ75PlmYl2a', '081234000005', 'Jl. Kenanga No. 3, Semarang', NULL, 'customer', '2025-03-12 13:45:00'),
(6, 'Rina Anggraini', 'rina@gmail.com', '$2y$10$l/u7HNgOAOyMRD7DRjMHieQVm.kq0WeGcPtfeOPuJNvQ75PlmYl2a', '081234000006', 'Jl. Anggrek No. 21, Medan', NULL, 'customer', '2025-03-20 08:20:00'),
(7, 'Joko Widodo', 'joko@gmail.com', '$2y$10$l/u7HNgOAOyMRD7DRjMHieQVm.kq0WeGcPtfeOPuJNvQ75PlmYl2a', '081234000007', 'Jl. Melati No. 7, Makassar', NULL, 'customer', '2025-04-02 14:10:00'),
(8, 'Sri Wahyuni', 'sri@gmail.com', '$2y$10$l/u7HNgOAOyMRD7DRjMHieQVm.kq0WeGcPtfeOPuJNvQ75PlmYl2a', '081234000008', 'Jl. Flamboyan No. 9, Denpasar', NULL, 'customer', '2025-04-15 09:50:00'),
(9, 'Eko Prasetyo', 'eko@gmail.com', '$2y$10$l/u7HNgOAOyMRD7DRjMHieQVm.kq0WeGcPtfeOPuJNvQ75PlmYl2a', '081234000009', 'Jl. Cendana No. 14, Palembang', NULL, 'customer', '2025-05-01 16:00:00'),
(10, 'Maya Sari', 'maya@gmail.com', '$2y$10$l/u7HNgOAOyMRD7DRjMHieQVm.kq0WeGcPtfeOPuJNvQ75PlmYl2a', '081234000010', 'Jl. Dahlia No. 33, Bekasi', NULL, 'customer', '2025-05-18 10:30:00'),
(11, 'Bayu Firmansyah', 'bayu@gmail.com', '$2y$10$l/u7HNgOAOyMRD7DRjMHieQVm.kq0WeGcPtfeOPuJNvQ75PlmYl2a', '081234000011', 'Jl. Teratai No. 5, Depok', NULL, 'customer', '2025-06-01 12:00:00'),
(12, 'salsa rommero', 'salsarommero@gmail.com', '$2y$10$nQEoEdy4Fk3TJfKwmMAYhOxvpBDRUZu7JhYT0ssVJugmNF/i7FPQu', '0215484', 'blok m', NULL, 'customer', '2026-07-17 11:58:07'),
(13, 'caca', 'caca@gmail.com', '$2y$10$oa53QuwwA54wisyZK2CmiewDDu7PTiMUkp97Ve7cclV4V9wwLQ2b6', '021584', 'blok m', '2005-07-11', 'customer', '2026-07-17 12:51:22'),
(14, 'rara', 'raragemas@gmail.com', '$2y$10$dGezTXDdwYc8tdZQAavl0.xyhiEjjWX7LIK6plmLzOXlaqBg2dh6i', '0812345678', 'Jl. Borobudur No.56', '2005-08-10', 'customer', '2026-07-17 21:15:54');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `apotek`
--
ALTER TABLE `apotek`
  ADD PRIMARY KEY (`id_apotek`);

--
-- Indeks untuk tabel `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id_booking`),
  ADD UNIQUE KEY `unik_kode_booking` (`kode_booking`),
  ADD KEY `fk_booking_user` (`id_user`),
  ADD KEY `fk_booking_dokter` (`id_dokter`),
  ADD KEY `fk_booking_klinik` (`id_klinik`),
  ADD KEY `fk_booking_jadwal` (`id_jadwal`);

--
-- Indeks untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `fk_detail_pesanan` (`id_pesanan`),
  ADD KEY `fk_detail_obat` (`id_obat`);

--
-- Indeks untuk tabel `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`id_dokter`),
  ADD KEY `fk_dokter_spesialis` (`id_spesialis`);

--
-- Indeks untuk tabel `dokter_klinik`
--
ALTER TABLE `dokter_klinik`
  ADD PRIMARY KEY (`id_dokter`,`id_klinik`),
  ADD KEY `fk_dk_klinik` (`id_klinik`);

--
-- Indeks untuk tabel `harga_stok_apotek`
--
ALTER TABLE `harga_stok_apotek`
  ADD PRIMARY KEY (`id_apotek`,`id_obat`),
  ADD KEY `fk_hsa_obat` (`id_obat`);

--
-- Indeks untuk tabel `jadwal_praktik`
--
ALTER TABLE `jadwal_praktik`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `fk_jp_dokter` (`id_dokter`),
  ADD KEY `fk_jp_klinik` (`id_klinik`);

--
-- Indeks untuk tabel `kategori_obat`
--
ALTER TABLE `kategori_obat`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD KEY `fk_keranjang_user` (`id_user`),
  ADD KEY `fk_keranjang_obat` (`id_obat`);

--
-- Indeks untuk tabel `klinik`
--
ALTER TABLE `klinik`
  ADD PRIMARY KEY (`id_klinik`),
  ADD KEY `fk_klinik_kota` (`id_kota`);

--
-- Indeks untuk tabel `kota`
--
ALTER TABLE `kota`
  ADD PRIMARY KEY (`id_kota`);

--
-- Indeks untuk tabel `metode_pembayaran`
--
ALTER TABLE `metode_pembayaran`
  ADD PRIMARY KEY (`id_metode`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`),
  ADD KEY `fk_notifikasi_user` (`id_user`);

--
-- Indeks untuk tabel `obat`
--
ALTER TABLE `obat`
  ADD PRIMARY KEY (`id_obat`),
  ADD UNIQUE KEY `unik_kode_obat` (`kode_obat`),
  ADD KEY `fk_obat_kategori` (`id_kategori`),
  ADD KEY `fk_obat_supplier` (`id_supplier`);

--
-- Indeks untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD UNIQUE KEY `uq_pembayaran_pesanan` (`id_pesanan`),
  ADD KEY `fk_pembayaran_pesanan` (`id_pesanan`),
  ADD KEY `fk_pembayaran_metode` (`id_metode`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD PRIMARY KEY (`id_pengiriman`),
  ADD UNIQUE KEY `uq_pengiriman_pesanan` (`id_pesanan`),
  ADD KEY `fk_pengiriman_pesanan` (`id_pesanan`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD UNIQUE KEY `unik_kode_pesanan` (`kode_pesanan`),
  ADD KEY `fk_pesanan_user` (`id_user`);

--
-- Indeks untuk tabel `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id_review`),
  ADD KEY `fk_review_user` (`id_user`),
  ADD KEY `fk_review_obat` (`id_obat`);

--
-- Indeks untuk tabel `spesialis`
--
ALTER TABLE `spesialis`
  ADD PRIMARY KEY (`id_spesialis`);

--
-- Indeks untuk tabel `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `unik_email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `apotek`
--
ALTER TABLE `apotek`
  MODIFY `id_apotek` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `booking`
--
ALTER TABLE `booking`
  MODIFY `id_booking` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT untuk tabel `dokter`
--
ALTER TABLE `dokter`
  MODIFY `id_dokter` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `jadwal_praktik`
--
ALTER TABLE `jadwal_praktik`
  MODIFY `id_jadwal` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT untuk tabel `kategori_obat`
--
ALTER TABLE `kategori_obat`
  MODIFY `id_kategori` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `klinik`
--
ALTER TABLE `klinik`
  MODIFY `id_klinik` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `kota`
--
ALTER TABLE `kota`
  MODIFY `id_kota` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `metode_pembayaran`
--
ALTER TABLE `metode_pembayaran`
  MODIFY `id_metode` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `obat`
--
ALTER TABLE `obat`
  MODIFY `id_obat` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  MODIFY `id_pengiriman` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `review`
--
ALTER TABLE `review`
  MODIFY `id_review` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `spesialis`
--
ALTER TABLE `spesialis`
  MODIFY `id_spesialis` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `fk_booking_dokter` FOREIGN KEY (`id_dokter`) REFERENCES `dokter` (`id_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_jadwal` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal_praktik` (`id_jadwal`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_klinik` FOREIGN KEY (`id_klinik`) REFERENCES `klinik` (`id_klinik`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `fk_detail_obat` FOREIGN KEY (`id_obat`) REFERENCES `obat` (`id_obat`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `dokter`
--
ALTER TABLE `dokter`
  ADD CONSTRAINT `fk_dokter_spesialis` FOREIGN KEY (`id_spesialis`) REFERENCES `spesialis` (`id_spesialis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `dokter_klinik`
--
ALTER TABLE `dokter_klinik`
  ADD CONSTRAINT `fk_dk_dokter` FOREIGN KEY (`id_dokter`) REFERENCES `dokter` (`id_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dk_klinik` FOREIGN KEY (`id_klinik`) REFERENCES `klinik` (`id_klinik`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `harga_stok_apotek`
--
ALTER TABLE `harga_stok_apotek`
  ADD CONSTRAINT `fk_hsa_apotek` FOREIGN KEY (`id_apotek`) REFERENCES `apotek` (`id_apotek`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hsa_obat` FOREIGN KEY (`id_obat`) REFERENCES `obat` (`id_obat`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `jadwal_praktik`
--
ALTER TABLE `jadwal_praktik`
  ADD CONSTRAINT `fk_jp_dokter` FOREIGN KEY (`id_dokter`) REFERENCES `dokter` (`id_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_jp_klinik` FOREIGN KEY (`id_klinik`) REFERENCES `klinik` (`id_klinik`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `fk_keranjang_obat` FOREIGN KEY (`id_obat`) REFERENCES `obat` (`id_obat`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_keranjang_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `klinik`
--
ALTER TABLE `klinik`
  ADD CONSTRAINT `fk_klinik_kota` FOREIGN KEY (`id_kota`) REFERENCES `kota` (`id_kota`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `fk_notifikasi_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `obat`
--
ALTER TABLE `obat`
  ADD CONSTRAINT `fk_obat_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_obat` (`id_kategori`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_obat_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `fk_pembayaran_metode` FOREIGN KEY (`id_metode`) REFERENCES `metode_pembayaran` (`id_metode`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pembayaran_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD CONSTRAINT `fk_pengiriman_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `fk_pesanan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `fk_review_obat` FOREIGN KEY (`id_obat`) REFERENCES `obat` (`id_obat`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_review_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
