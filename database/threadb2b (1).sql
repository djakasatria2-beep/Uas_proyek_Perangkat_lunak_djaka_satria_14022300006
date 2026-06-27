-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 27, 2026 at 03:35 AM
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
  `kode_pelanggan` varchar(20) DEFAULT NULL,
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

INSERT INTO `buyer_profile` (`id_buyer`, `id_user`, `kode_pelanggan`, `nama_perusahaan`, `nama_pic`, `no_whatsapp`, `alamat`, `negara`, `contact_person`, `no_telp`, `npwp`, `nib`, `upload_dokumen`, `status_verifikasi`, `diverifikasi_oleh`, `tanggal_diblokir`, `tenor_hari`) VALUES
(1, 7, NULL, 'PT UNDAR', 'Lerr', '0976756444', 'Serang', 'Indosat', 'Bonge', '08967676', '2343542222', '9766666', NULL, 'approved', 10, NULL, 30),
(2, 20, NULL, 'PT Maju Bersama Tekstil', 'Andi Wijaya', '081234560001', 'Jl. Industri No. 12, Tangerang', 'Indonesia', 'Dewi Sartika', '02156781234', '01.234.567.8-901.000', '8120000000001', NULL, 'approved', 1, NULL, 30),
(3, 21, NULL, 'CV Sentosa Benang', 'Budi Santoso', '081234560002', 'Jl. Raya Bogor KM 25, Bogor', 'Indonesia', 'Rina Lestari', '02178901234', '01.345.678.9-012.000', '8120000000002', NULL, 'blocked', 1, '2026-06-27 00:50:51', 45),
(4, 22, NULL, 'PT Prima Garmen Indo', 'Citra Dewi', '081234560003', 'Jl. Sultan Agung No. 7, Bandung', 'Indonesia', 'Hendra Putra', '02245671234', '01.456.789.0-123.000', '8120000000003', NULL, 'pending', 1, NULL, 30);

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

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id_certificate`, `nama_sertifikat`, `gambar`, `tahun`) VALUES
(1, 'ISO 9001:2015 Quality Management', 'cert_iso9001.jpg', '2024'),
(2, 'OEKO-TEX Standard 100', 'cert_oekotex.jpg', '2025'),
(3, 'Global Recycled Standard (GRS)', 'cert_grs.jpg', '2025');

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

--
-- Dumping data for table `company_profile`
--

INSERT INTO `company_profile` (`id_profile`, `nama_pt`, `logo`, `tentang_company`, `visi`, `misi`, `sejarah`, `alamat`, `maps`, `email`, `phone`) VALUES
(1, 'PT Benang Nusantara', 'logo_bn.png', 'PT Benang Nusantara adalah produsen benang nylon dan polyester berkualitas tinggi yang melayani industri tekstil domestik dan ekspor sejak 2005.', 'Menjadi pemasok benang terpercaya dan berkelanjutan di kawasan Asia Tenggara.', 'Menghasilkan produk benang berkualitas premium, mendukung industri tekstil nasional, serta berkomitmen pada praktik produksi ramah lingkungan.', 'Didirikan pada tahun 2005 di Tangerang, PT Benang Nusantara telah berkembang dari produsen skala menengah menjadi salah satu pemain utama industri benang sintetis di Indonesia.', 'Jl. Industri Raya No. 88, Kawasan Industri Cikupa, Tangerang, Banten 15710', 'https://maps.google.com/?q=-6.2185,106.5123', 'info@benaknusantara.co.id', '021-5967-1234');

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

--
-- Dumping data for table `delivery_items`
--

INSERT INTO `delivery_items` (`id`, `sj_no`, `item_no`, `item_name`, `qty`, `unit`, `cns_count`, `box_count`, `lot_info`, `remarks`) VALUES
(1, 'SJ-2026-00001', 'NSR.70/2S59651', 'NYLON SEMI DULL YARN 70D/24FX2', 500.00, 'KG', 100, 10, '0.50+60.90 LOT-A', NULL),
(2, 'SJ-2026-00002', 'NSR.100/3S40200', 'NYLON SEMI DULL YARN 100D/36FX3', 300.00, 'KG', 60, 6, '0.75+45.00 LOT-B', 'Periksa warna sebelum diterima'),
(3, 'SJ-2026-00003', 'RNY.150/2S10050', 'RECYCLED NYLON YARN 150D/48FX2', 200.00, 'KG', 40, 4, '1.00+30.00 LOT-C', NULL);

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

--
-- Dumping data for table `delivery_notes`
--

INSERT INTO `delivery_notes` (`sj_no`, `sj_date`, `customer_id`, `po_no`, `pn_no`, `invoice_id`, `warehouse`, `printed_at`, `total_qty`, `total_cns`, `created_at`) VALUES
('SJ-2026-00001', '2026-04-14', 'CUST-001', 'PO-2026-MTB-001', 'SO-2026-00101', 'INV-2026-00001', 'GUDANGID1', '2026-04-14 07:30:00', 500.00, 100.00, '2026-06-26 20:49:25'),
('SJ-2026-00002', '2026-04-19', 'CUST-002', 'PO-2026-CVS-001', 'SO-2026-00102', 'INV-2026-00002', 'GUDANGID1', '2026-04-19 08:00:00', 300.00, 60.00, '2026-06-26 20:49:25'),
('SJ-2026-00003', '2026-04-30', 'CUST-003', 'PO-2026-PGI-001', 'SO-2026-00103', 'INV-2026-00003', 'GUDANGID2', '2026-04-30 09:15:00', 200.00, 40.00, '2026-06-26 20:49:25');

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

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id_gallery`, `judul`, `gambar`, `kategori`, `deskripsi`) VALUES
(1, 'Fasilitas Produksi Benang Nylon', 'gallery_produksi1.jpg', 'Produksi', 'Area mesin twisting dan winding terbaru dengan kapasitas tinggi'),
(2, 'Laboratorium Uji Warna', 'gallery_lab1.jpg', 'Lab', 'Laboratorium pengujian Delta-E dan konsistensi warna benang'),
(3, 'Gudang Distribusi', 'gallery_gudang1.jpg', 'Distribusi', 'Gudang penyimpanan berpendingin untuk menjaga kualitas benang');

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

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `invoice_date`, `customer_id`, `credit_days`, `due_date`, `subtotal_idr`, `ppn_pct`, `ppn_idr`, `total_idr`, `created_by`, `status`, `created_at`) VALUES
('INV-2026-00001', '2026-04-15', 'CUST-001', 30, '2026-05-15', 42500000.00, 11.00, 4675000.00, 47175000.00, 'Rosmala', 'ISSUED', '2026-06-26 20:49:25'),
('INV-2026-00002', '2026-04-20', 'CUST-002', 45, '2026-06-04', 27600000.00, 11.00, 3036000.00, 30636000.00, 'Rosmala', 'PAID', '2026-06-26 20:49:25'),
('INV-2026-00003', '2026-05-01', 'CUST-003', 30, '2026-05-31', 22000000.00, 11.00, 2420000.00, 24420000.00, 'Rosmala', 'DRAFT', '2026-06-26 20:49:25');

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

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `slip_date`, `sj_no`, `po_no`, `item_no`, `colour_no`, `qty`, `unit`, `price_idr`, `amount_idr`) VALUES
(1, 'INV-2026-00001', '2026-04-14', 'SJ-2026-00001', 'PO-2026-MTB-001', 'NSR.70/2S59651', '59651', 500.00, 'KG', 85000.00, 42500000.00),
(2, 'INV-2026-00002', '2026-04-19', 'SJ-2026-00002', 'PO-2026-CVS-001', 'NSR.100/3S40200', '40200', 300.00, 'KG', 92000.00, 27600000.00),
(3, 'INV-2026-00003', '2026-04-30', 'SJ-2026-00003', 'PO-2026-PGI-001', 'RNY.150/2S10050', '10050', 200.00, 'KG', 110000.00, 22000000.00);

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

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id_order`, `id_buyer`, `no_order`, `kode_warna`, `nama_warna`, `jenis_benang`, `ukuran_benang`, `qty`, `harga_benang`, `tanggal`, `catatan`, `status`) VALUES
(1, 1, 'ORD-2026-0001', '59651', 'TURBULENCE', 'POLYAMIDE NYLON', '70D/24FX2', 500, 85000.00, '2026-04-01', 'Pengiriman ke gudang Tangerang', 'processing'),
(2, 2, 'ORD-2026-0002', '40200', 'MIDNIGHT BLUE', 'POLYAMIDE NYLON', '100D/36FX3', 300, 92000.00, '2026-04-10', 'Harap cek kualitas sebelum kirim', 'shipped'),
(3, 3, 'ORD-2026-0003', '10050', 'NATURAL WHITE', 'RECYCLED NYLON', '150D/48FX2', 200, 110000.00, '2026-04-20', NULL, 'pending');

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

--
-- Dumping data for table `order_returns`
--

INSERT INTO `order_returns` (`id_return`, `id_order`, `no_return`, `alasan_kategori`, `alasan`, `foto`, `respons_admin`, `status`) VALUES
(1, 2, 'RET-2026-0001', 'deviasi_warna', 'Warna yang diterima tidak sesuai dengan sampel yang disetujui', NULL, 'Sedang dalam investigasi lab', 'under_review'),
(2, 1, 'RET-2026-0002', 'barang_rusak', 'Beberapa cone rusak saat tiba, kemasan tidak memadai', NULL, 'Akan dilakukan penggantian unit rusak', 'approved'),
(3, 3, 'RET-2026-0003', 'spesifikasi_salah', 'Ukuran benang yang dikirim 70D bukan 150D seperti PO', NULL, NULL, 'submitted');

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

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`item_no`, `item_name`, `denier`, `colour_no`, `colour_name`, `material_type`, `recycled`, `unit`, `price_idr`, `created_at`) VALUES
('NSR.100/3S40200', 'NYLON SEMI DULL YARN 100D/36FX3', '100D/36FX3', '40200', 'MIDNIGHT BLUE', 'POLYAMIDE NYLON', 0, 'KG', 92000.00, '2026-06-26 20:49:25'),
('NSR.70/2S59651', 'NYLON SEMI DULL YARN 70D/24FX2', '70D/24FX2', '59651', 'TURBULENCE', 'POLYAMIDE NYLON', 0, 'KG', 85000.00, '2026-06-26 20:49:25'),
('RNY.150/2S10050', 'RECYCLED NYLON YARN 150D/48FX2', '150D/48FX2', '10050', 'NATURAL WHITE', 'RECYCLED NYLON', 1, 'KG', 110000.00, '2026-06-26 20:49:25');

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

--
-- Dumping data for table `resi_pengiriman`
--

INSERT INTO `resi_pengiriman` (`resi_no`, `resi_date`, `sj_no`, `shipper_name`, `consignee`, `koli`, `berat_kg`, `vol_p`, `vol_l`, `vol_t`, `service_type`, `payment_type`, `charge_idr`, `kurir`, `created_at`) VALUES
('328-1001001', '2026-04-14', 'SJ-2026-00001', 'PT Benang Nusantara', 'PT Maju Bersama Tekstil', 10, 520.00, 120.00, 80.00, 60.00, 'DOMESTIK', 'CREDIT', 1500000.00, 'JNE Trucking', '2026-06-26 20:49:25'),
('328-1001002', '2026-04-19', 'SJ-2026-00002', 'PT Benang Nusantara', 'CV Sentosa Benang', 6, 315.00, 90.00, 70.00, 50.00, 'DOMESTIK', 'CREDIT', 950000.00, 'JNE Trucking', '2026-06-26 20:49:25'),
('328-1001003', '2026-04-30', 'SJ-2026-00003', 'PT Benang Nusantara', 'PT Prima Garmen Indo', 4, 210.00, 80.00, 60.00, 50.00, 'REGULER', 'CASH', 750000.00, 'SiCepat', '2026-06-26 20:49:25');

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

--
-- Dumping data for table `sample_requests`
--

INSERT INTO `sample_requests` (`id_request`, `id_buyer`, `jenis_benang`, `ukuran_benang`, `kode_warna_target`, `upload_sampel`, `tanggal`, `tanggal_dibutuhkan`, `catatan`, `status`) VALUES
(1, 1, 'POLYAMIDE NYLON', '70D/24FX2', 'TARGET-RED-01', NULL, '2026-03-05', '2026-03-20', 'Warna mendekati merah maroon', 'result_ready'),
(2, 2, 'POLYAMIDE NYLON', '100D/36FX3', 'TARGET-NAVY-02', NULL, '2026-03-10', '2026-03-25', 'Kilap sedang, tidak terlalu glossy', 'approved'),
(3, 3, 'RECYCLED NYLON', '150D/48FX2', 'TARGET-WHITE-03', NULL, '2026-03-15', '2026-04-01', NULL, 'pending');

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

--
-- Dumping data for table `sample_results`
--

INSERT INTO `sample_results` (`id_result`, `id_request`, `kode_warna_hasil`, `pilihan`, `gambar`, `nilai_delta_e`, `catatan`, `status_approval`) VALUES
(1, 1, 'HASIL-RED-59800', 'A', NULL, 1.25, 'Delta E sangat baik, warna mendekati target', 'approved'),
(2, 2, 'HASIL-NAVY-40250', 'A', NULL, 0.98, 'Kilap dan warna sesuai permintaan buyer', 'approved'),
(3, 3, NULL, 'pending', NULL, NULL, 'Lab sedang memproses sampel', 'pending');

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

--
-- Dumping data for table `tracking`
--

INSERT INTO `tracking` (`id_tracking`, `id_order`, `status`, `keterangan`, `updated_by`, `tanggal`) VALUES
(1, 1, 'Order Dikonfirmasi', 'Pesanan telah dikonfirmasi oleh tim marketing', 85, '2026-04-02 09:00:00'),
(2, 2, 'Sedang Dikirim', 'Barang dalam perjalanan menggunakan JNE Trucking', 85, '2026-04-12 14:00:00'),
(3, 3, 'Menunggu Konfirmasi', 'Order baru masuk, menunggu verifikasi ketersediaan stok', 85, '2026-04-21 10:30:00');

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
(1, 'admin@threadb2b.id', '$2y$10$6LwYNPtdM8fOUht3KE./xuMsw4BQsGcAPp5UtRLZGisSFrZyrZ.Z6', 'admin', '2026-01-05 08:00:00'),
(2, 'marketing@threadb2b.id', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'marketing', '2026-01-05 08:05:00'),
(3, 'buyer.cahaya@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', '2026-01-10 09:00:00'),
(4, 'buyer.surya@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', '2026-01-15 10:00:00'),
(5, 'buyer.gemilang@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', '2026-02-01 11:00:00'),
(7, 'buyyer@gmail.com', '$2y$10$tBnyRjULyxf/BQpI2Z1Q8OGD6NS945oFV0Jb2kKlJ4/tu/vVYHHqu', 'buyer', '2026-06-06 20:07:24'),
(10, 'admin@gmail.com', '$2y$10$2MrhbQa30mll8mKG6LPyjuI7CQPC4abCvqrSvczxXVRu4RVueRfoe', 'admin', '2026-06-06 19:01:40'),
(20, 'buyer.maju@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', '2026-03-01 08:00:00'),
(21, 'buyer.sentosa@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', '2026-03-05 09:00:00'),
(22, 'buyer.prima@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', '2026-03-10 10:00:00'),
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
  ADD KEY `fk_buyer_diverifikasi_oleh` (`diverifikasi_oleh`),
  ADD KEY `idx_buyer_kode_pelanggan` (`kode_pelanggan`);

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
  MODIFY `id_buyer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id_certificate` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `company_profile`
--
ALTER TABLE `company_profile`
  MODIFY `id_profile` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `delivery_items`
--
ALTER TABLE `delivery_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id_gallery` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_returns`
--
ALTER TABLE `order_returns`
  MODIFY `id_return` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sample_requests`
--
ALTER TABLE `sample_requests`
  MODIFY `id_request` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sample_results`
--
ALTER TABLE `sample_results`
  MODIFY `id_result` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tracking`
--
ALTER TABLE `tracking`
  MODIFY `id_tracking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
