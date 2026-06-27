-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 26, 2026 at 10:14 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `threadb2b`
--

-- --------------------------------------------------------

--
-- Table structure for table `buyer_profile`
--

CREATE TABLE `buyer_profile` (
  `id_buyer` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nama_perusahaan` varchar(150) NOT NULL,
  `nama_pic` varchar(100) NOT NULL,
  `no_whatsapp` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `negara` varchar(50) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `npwp` varchar(25) DEFAULT NULL,
  `nib` varchar(20) DEFAULT NULL,
  `upload_dokumen` varchar(255) DEFAULT NULL,
  `status_verifikasi` enum('pending','approved','rejected','blocked') NOT NULL DEFAULT 'pending',
  `diverifikasi_oleh` int(11) DEFAULT NULL,
  `tanggal_diblokir` datetime DEFAULT NULL,
  `tenor_hari` int(11) NOT NULL DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buyer_profile`
--

INSERT INTO `buyer_profile` (`id_buyer`, `id_user`, `nama_perusahaan`, `nama_pic`, `no_whatsapp`, `alamat`, `negara`, `contact_person`, `no_telp`, `npwp`, `nib`, `upload_dokumen`, `status_verifikasi`, `diverifikasi_oleh`, `tanggal_diblokir`, `tenor_hari`) VALUES
(1, 7, 'PT UNDAR', 'Lerr', '0976756444', 'Serang', 'Indosat', 'Bonge', '08967676', '2343542222', '9766666', NULL, 'approved', 10, NULL, 30);

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id_certificate` int(11) NOT NULL,
  `nama_sertifikat` varchar(150) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `tahun` year(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_profile`
--

CREATE TABLE `company_profile` (
  `id_profile` int(11) NOT NULL,
  `nama_pt` varchar(150) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `tentang_company` text DEFAULT NULL,
  `visi` text DEFAULT NULL,
  `misi` text DEFAULT NULL,
  `sejarah` text DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `maps` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_items`
--

CREATE TABLE `delivery_items` (
  `id` int(11) NOT NULL,
  `sj_no` varchar(30) NOT NULL,
  `item_no` varchar(50) DEFAULT NULL,
  `item_name` text DEFAULT NULL,
  `qty` decimal(12,2) DEFAULT NULL,
  `unit` varchar(10) NOT NULL DEFAULT 'KG',
  `cns_count` int(11) DEFAULT NULL COMMENT 'jumlah cones',
  `box_count` int(11) DEFAULT NULL COMMENT 'jumlah box',
  `lot_info` varchar(100) DEFAULT NULL COMMENT 'e.g. 0.50+60.90 LOT',
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_notes`
--

CREATE TABLE `delivery_notes` (
  `sj_no` varchar(30) NOT NULL COMMENT 'e.g. SJ-2026-02268',
  `sj_date` date NOT NULL,
  `customer_id` varchar(20) NOT NULL,
  `po_no` varchar(50) DEFAULT NULL COMMENT 'e.g. SJI-LRB-P-260126-0005',
  `pn_no` varchar(30) DEFAULT NULL COMMENT 'e.g. SO-2026-00419',
  `invoice_id` varchar(30) DEFAULT NULL,
  `warehouse` varchar(50) DEFAULT NULL COMMENT 'e.g. GUDANGID1',
  `printed_at` datetime DEFAULT NULL,
  `total_qty` decimal(12,2) DEFAULT NULL,
  `total_cns` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id_gallery` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` varchar(30) NOT NULL COMMENT 'e.g. INV-2026-01906',
  `invoice_date` date NOT NULL,
  `customer_id` varchar(20) NOT NULL,
  `credit_days` int(11) NOT NULL DEFAULT 30,
  `due_date` date DEFAULT NULL,
  `subtotal_idr` decimal(18,2) NOT NULL DEFAULT 0.00,
  `ppn_pct` decimal(5,2) NOT NULL DEFAULT 11.00,
  `ppn_idr` decimal(18,2) NOT NULL DEFAULT 0.00,
  `total_idr` decimal(18,2) NOT NULL DEFAULT 0.00,
  `created_by` varchar(100) DEFAULT NULL COMMENT 'e.g. Rosmala',
  `status` enum('DRAFT','ISSUED','PAID','OVERDUE') NOT NULL DEFAULT 'ISSUED',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` varchar(30) NOT NULL,
  `slip_date` date DEFAULT NULL,
  `sj_no` varchar(30) DEFAULT NULL COMMENT 'e.g. SJ-2026-02268',
  `po_no` varchar(50) DEFAULT NULL COMMENT 'e.g. SJI-LRB-P-260126-0005',
  `item_no` varchar(50) DEFAULT NULL,
  `colour_no` varchar(20) DEFAULT NULL,
  `qty` decimal(12,2) DEFAULT NULL,
  `unit` varchar(10) NOT NULL DEFAULT 'KG',
  `price_idr` decimal(15,2) DEFAULT NULL,
  `amount_idr` decimal(18,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id_order` int(11) NOT NULL,
  `id_buyer` int(11) NOT NULL,
  `no_order` varchar(20) NOT NULL,
  `kode_warna` varchar(20) DEFAULT NULL,
  `nama_warna` varchar(100) DEFAULT NULL,
  `jenis_benang` varchar(100) NOT NULL,
  `ukuran_benang` varchar(50) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `harga_benang` decimal(15,2) NOT NULL,
  `tanggal` date NOT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('pending','processing','shipped','done','cancelled') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_returns`
--

CREATE TABLE `order_returns` (
  `id_return` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `no_return` varchar(20) NOT NULL,
  `alasan_kategori` enum('deviasi_warna','kualitas','barang_rusak','spesifikasi_salah','lainnya') NOT NULL,
  `alasan` text NOT NULL,
  `foto` varchar(255) DEFAULT NULL COMMENT 'JSON array of file paths, max 5',
  `respons_admin` text DEFAULT NULL,
  `status` enum('submitted','under_review','approved','resolved','rejected') NOT NULL DEFAULT 'submitted'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `item_no` varchar(50) NOT NULL COMMENT 'e.g. NSR.70/2S59651',
  `item_name` text NOT NULL,
  `denier` varchar(50) DEFAULT NULL COMMENT 'e.g. 70D/24FX2',
  `colour_no` varchar(20) DEFAULT NULL COMMENT 'e.g. 59651',
  `colour_name` varchar(100) DEFAULT NULL COMMENT 'e.g. TURBULENCE',
  `material_type` varchar(100) DEFAULT NULL COMMENT 'e.g. POLYAMIDE NYLON',
  `recycled` tinyint(1) NOT NULL DEFAULT 0,
  `unit` varchar(10) NOT NULL DEFAULT 'KG',
  `price_idr` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resi_pengiriman`
--

CREATE TABLE `resi_pengiriman` (
  `resi_no` varchar(30) NOT NULL COMMENT 'e.g. 328-1722545',
  `resi_date` date NOT NULL,
  `sj_no` varchar(30) DEFAULT NULL,
  `shipper_name` varchar(200) DEFAULT NULL,
  `consignee` varchar(200) DEFAULT NULL COMMENT 'e.g. Andalan BJL',
  `koli` int(11) DEFAULT NULL,
  `berat_kg` decimal(10,2) DEFAULT NULL,
  `vol_p` decimal(8,2) DEFAULT NULL,
  `vol_l` decimal(8,2) DEFAULT NULL,
  `vol_t` decimal(8,2) DEFAULT NULL,
  `service_type` enum('DOMESTIK','INTERNASIONAL','EXPRESS','REGULER','LOGISTIK') NOT NULL DEFAULT 'DOMESTIK',
  `payment_type` enum('CASH','CREDIT','COLLECT') NOT NULL DEFAULT 'CASH',
  `charge_idr` decimal(12,2) DEFAULT NULL,
  `kurir` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sample_requests`
--

CREATE TABLE `sample_requests` (
  `id_request` int(11) NOT NULL,
  `id_buyer` int(11) NOT NULL,
  `jenis_benang` varchar(100) NOT NULL,
  `ukuran_benang` varchar(50) DEFAULT NULL,
  `kode_warna_target` varchar(30) DEFAULT NULL,
  `upload_sampel` varchar(255) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `tanggal_dibutuhkan` date DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('pending','waiting_result','result_ready','approved','rejected','revision') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sample_results`
--

CREATE TABLE `sample_results` (
  `id_result` int(11) NOT NULL,
  `id_request` int(11) NOT NULL,
  `kode_warna_hasil` varchar(30) DEFAULT NULL,
  `pilihan` enum('A','B','rejected','pending') NOT NULL DEFAULT 'pending',
  `gambar` varchar(255) DEFAULT NULL,
  `nilai_delta_e` decimal(5,2) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `status_approval` enum('pending','approved','rejected','revision_requested') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tracking`
--

CREATE TABLE `tracking` (
  `id_tracking` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `status` varchar(100) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('buyer','marketing','admin') NOT NULL DEFAULT 'buyer',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin@threadb2b.id', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-01-05 08:00:00'),
(2, 'marketing@threadb2b.id', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'marketing', '2026-01-05 08:05:00'),
(3, 'buyer.cahaya@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', '2026-01-10 09:00:00'),
(4, 'buyer.surya@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', '2026-01-15 10:00:00'),
(5, 'buyer.gemilang@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', '2026-02-01 11:00:00'),
(7, 'buyyer@gmail.com', '$2y$10$2MrhbQa30mll8mKG6LPyjuI7CQPC4abCvqrSvczxXVRu4RVueRfoe', 'buyer', '2026-06-06 20:07:24'),
(10, 'admin@gmail.com', '$2y$10$2MrhbQa30mll8mKG6LPyjuI7CQPC4abCvqrSvczxXVRu4RVueRfoe', 'admin', '2026-06-06 19:01:40'),
(85, 'marketing@gmail.com', '$2y$10$2MrhbQa30mll8mKG6LPyjuI7CQPC4abCvqrSvczxXVRu4RVueRfoe', 'marketing', '2026-06-06 20:07:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buyer_profile`
--
ALTER TABLE `buyer_profile`
  ADD PRIMARY KEY (`id_buyer`),
  ADD UNIQUE KEY `uq_buyer_profile_id_user` (`id_user`),
  ADD KEY `fk_buyer_diverifikasi_oleh` (`diverifikasi_oleh`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id_certificate`);

--
-- Indexes for table `company_profile`
--
ALTER TABLE `company_profile`
  ADD PRIMARY KEY (`id_profile`);

--
-- Indexes for table `delivery_items`
--
ALTER TABLE `delivery_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_di_sj_no` (`sj_no`);

--
-- Indexes for table `delivery_notes`
--
ALTER TABLE `delivery_notes`
  ADD PRIMARY KEY (`sj_no`),
  ADD KEY `fk_dn_customer_id` (`customer_id`),
  ADD KEY `fk_dn_invoice_id` (`invoice_id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id_gallery`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `fk_invoices_customer_id` (`customer_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_invitems_invoice_id` (`invoice_id`),
  ADD KEY `fk_invitems_item_no` (`item_no`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD UNIQUE KEY `uq_orders_no_order` (`no_order`),
  ADD KEY `fk_orders_id_buyer` (`id_buyer`);

--
-- Indexes for table `order_returns`
--
ALTER TABLE `order_returns`
  ADD PRIMARY KEY (`id_return`),
  ADD UNIQUE KEY `uq_order_returns_no_return` (`no_return`),
  ADD KEY `fk_returns_id_order` (`id_order`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`item_no`);

--
-- Indexes for table `resi_pengiriman`
--
ALTER TABLE `resi_pengiriman`
  ADD PRIMARY KEY (`resi_no`),
  ADD KEY `fk_resi_sj_no` (`sj_no`);

--
-- Indexes for table `sample_requests`
--
ALTER TABLE `sample_requests`
  ADD PRIMARY KEY (`id_request`),
  ADD KEY `fk_sample_requests_id_buyer` (`id_buyer`);

--
-- Indexes for table `sample_results`
--
ALTER TABLE `sample_results`
  ADD PRIMARY KEY (`id_result`),
  ADD UNIQUE KEY `uq_sample_results_id_request` (`id_request`);

--
-- Indexes for table `tracking`
--
ALTER TABLE `tracking`
  ADD PRIMARY KEY (`id_tracking`),
  ADD KEY `fk_tracking_id_order` (`id_order`),
  ADD KEY `fk_tracking_updated_by` (`updated_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buyer_profile`
--
ALTER TABLE `buyer_profile`
  MODIFY `id_buyer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id_certificate` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_profile`
--
ALTER TABLE `company_profile`
  MODIFY `id_profile` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_items`
--
ALTER TABLE `delivery_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id_gallery` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_returns`
--
ALTER TABLE `order_returns`
  MODIFY `id_return` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sample_requests`
--
ALTER TABLE `sample_requests`
  MODIFY `id_request` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sample_results`
--
ALTER TABLE `sample_results`
  MODIFY `id_result` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tracking`
--
ALTER TABLE `tracking`
  MODIFY `id_tracking` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buyer_profile`
--
ALTER TABLE `buyer_profile`
  ADD CONSTRAINT `fk_buyer_diverifikasi_oleh` FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_buyer_id_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `delivery_items`
--
ALTER TABLE `delivery_items`
  ADD CONSTRAINT `fk_di_sj_no` FOREIGN KEY (`sj_no`) REFERENCES `delivery_notes` (`sj_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `delivery_notes`
--
ALTER TABLE `delivery_notes`
  ADD CONSTRAINT `fk_dn_invoice_id` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `fk_invitems_invoice_id` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invitems_item_no` FOREIGN KEY (`item_no`) REFERENCES `products` (`item_no`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_id_buyer` FOREIGN KEY (`id_buyer`) REFERENCES `buyer_profile` (`id_buyer`) ON UPDATE CASCADE;

--
-- Constraints for table `order_returns`
--
ALTER TABLE `order_returns`
  ADD CONSTRAINT `fk_returns_id_order` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON UPDATE CASCADE;

--
-- Constraints for table `resi_pengiriman`
--
ALTER TABLE `resi_pengiriman`
  ADD CONSTRAINT `fk_resi_sj_no` FOREIGN KEY (`sj_no`) REFERENCES `delivery_notes` (`sj_no`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `sample_requests`
--
ALTER TABLE `sample_requests`
  ADD CONSTRAINT `fk_sample_requests_id_buyer` FOREIGN KEY (`id_buyer`) REFERENCES `buyer_profile` (`id_buyer`) ON UPDATE CASCADE;

--
-- Constraints for table `sample_results`
--
ALTER TABLE `sample_results`
  ADD CONSTRAINT `fk_sample_results_id_request` FOREIGN KEY (`id_request`) REFERENCES `sample_requests` (`id_request`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tracking`
--
ALTER TABLE `tracking`
  ADD CONSTRAINT `fk_tracking_id_order` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tracking_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
