<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CiTableSeeder extends Seeder
{
    /**
     * Seed the application's database with exact data from ipmaerp.sql.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('ci_categories')->truncate();
        DB::table('ci_category_items')->truncate();
        DB::table('ci_staffcicategoryitemcheck')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ---- ci_categories (7 rows) ----
        DB::unprepared('INSERT INTO `ci_categories` VALUES
(1, \'General\', NULL, \'2024-02-27 17:51:27\', \'2024-02-27 17:51:27\'),
(2, \'Produktiviti\', NULL, \'2024-03-12 11:43:09\', \'2024-03-12 11:43:09\'),
(3, \'Disiplin\', NULL, \'2024-03-12 11:43:22\', \'2024-03-12 11:43:22\'),
(4, \'Kebersihan\', NULL, \'2024-03-12 11:43:35\', \'2024-03-12 11:43:35\'),
(5, \'Kedatangan - HR\', NULL, \'2024-03-12 11:43:58\', \'2024-03-12 11:43:58\'),
(6, \'Disiplin - HR\', NULL, \'2024-03-12 11:44:29\', \'2024-03-12 11:44:29\'),
(7, \'HR SOP - HR\', NULL, \'2024-03-12 11:44:43\', \'2024-03-12 11:44:43\');');

        // ---- ci_category_items (96 rows) ----
        DB::unprepared('INSERT INTO `ci_category_items` VALUES
(1, 2, \'80% >(Tiada Potongan)\', 0, \'Tiada Potongan\', \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(2, 2, \'50% - 79% (Potong Setengah Dari CI)\', 0, \'Potong Setengah Dari CI\', \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(3, 2, \'49% < (Tiada CI)\', 0, \'Tiada CI\', \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(4, 2, \'Tidak bersungguh-sungguh dan fokus sepenuhnya pada kerja\', 20, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(5, 2, \'Tidak membuat OT apabila diminta\', 20, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(6, 2, \'Tidak menerima tugasan yang baru/ selain dgn tugasan sedia ada sekiranya perlu\', 20, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(7, 3, \'Curi tulang dan kerja lambat tanpa jagaan Leader\', 20, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(8, 3, \'Bersembang, berangan-angan dan mengantuk semasa bekerja\', 20, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(9, 3, \'Melanggar peraturan syarikat\', 20, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(10, 4, \'Tidak menjaga kebersihan tempat kerja\', 20, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(11, 4, \'Ambil kesempatan henti kerja awal dan bersembang sebelum waktu habis kerja\', 20, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(12, 5, \'Tidak datang kerja 4 hari\', 20, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(13, 5, \'Tidak datang kerja 5 hari\', 40, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(14, 5, \'Tidak datang kerja 6 hari\', 60, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(15, 5, \'Tidak datang kerja 7 hari ke atas (Tiada CI)\', 0, \'Tiada CI\', \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(16, 6, \'Lewat masuk ke tempat kerja termasuk selepas waktu rehat\', 20, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(17, 7, \'Ambil cuti pada hari Sabtu tanpa kebenaran\', 20, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(18, 7, \'Cuti yang diambil tidak mengikuti SOP\', 20, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(19, 7, \'Menerima surat amaran pada bulan tersebut (Tiada CI)\', 0, \'Tiada CI\', \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(20, 1, \'Mesti pastikan phone sentiasa dijawab apabila diatur membuat penghantaran. Selalu susah untuk dihubungi. Berinitiatif untuk dapatkan telephone semasa membuat penghantaran. \', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(21, 1, \'Sampaikan maklumat dengan betul dan mendengar arahan dengan baik semasa penghantaran. Sentiasa berinteraksi dengan pihak syarikat jika tidak faham atau tidak pasti.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(22, 1, \'Tidak melengahkan dan membuang masa semasa penghantaran.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(23, 1, \'Perlu initiative menjalankan tugasan dan siapkan kerja dengan cepat dan berkualiti walaupun tanpa jagaan supervisor.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(24, 1, \'Berkomunikasi secara jelas baik secara lisan mahupun bertulis antara department.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(25, 1, \'Meningkatkan leadership skill untuk menguruskan department dengan lebih berkesan.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(26, 1, \'Menyusun kerja untuk mencapai target yang ditetapkan oleh syarikat. \', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(27, 1, \'Meningkatkan pengetahuan dan mengagihkan kerja mengikut kesesuaian mesin perlu ambil perhatian untuk mencapai process yang efisien.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(28, 1, \'Melaksana dan memastikan kualiti dipatuhi oleh setiap pekerja bawahan dengan menjalani pemeriksaan kualiti pada setiap hasil kerja, terutamanya QC sebelum parts dihantar keluar.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(29, 1, \'Pastikan pekerja bawahan melakukan penyelenggaraan mesin mengikut jadual dan mengemaskini maintenance check list.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(30, 1, \'Membaik pulih mesin untuk menyelesaikan masalah mesin atau peralatan yang tidak berfungsi.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(31, 1, \'Menyusun kerja untuk mencapai target yang ditetapkan oleh syarikat.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(32, 1, \'Melaksana dan memastikan kualiti dipatuhi dan menjalani pemeriksaan kualiti pada setiap hasil kerja, terutamanya QC sebelum parts dihantar keluar.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(33, 1, \'Melakukan penyelenggaraan mesin mengikut jadual dan mengemaskini maintenance check list.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(34, 1, \'Melakukan kerja supaya mencapai tempoh yang ditetapkan oleh syarikat.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(35, 1, \'Berdikari untuk siapkan kerja dengan cepat dan efficient secara sendiri tanpa jagaan orang.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(36, 1, \'Memastikan kualiti dan hasil kerja terbaik. QC dibuat pada setiap part yang dihasilkan.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(37, 1, \'Menjaga mesin welding sendiri dan membuat housekeeping kerja setiap hari.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(38, 1, \'Melatih pekerja baru kemahiran menjalani mesin bending dan membuat parts yang berkualiti.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(39, 1, \'Memastikan kualiti dan hasil kerja terbaik. QC dibuat pada setiap hasil kerja dan pemasangan.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(40, 1, \'Sentiasa mencari atau mencuba cara baru untuk meningkatkan produktiviti.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(41, 1, \'Lakukan penyelenggaraan mesin mengikut jadual dan mengemaskini maintenance check list.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(42, 1, \'Memberi laporan kerja harian apabila berlaku seperti :  RM50\\na)Masalah mesin b) Masalah Material c) Reject yang berlaku d) Masalah kerja dan cara mengatasi.\\ne) Maintenance check list Mesin f) Maintenance Air Compressor g) Mesin spare part list \\nh) Update Job status.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(43, 1, \'Memastikan faham kerja yang perlu dibuat dengan sangat jelas sebelum mulakan kerja itu.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(44, 1, \'Sampaikan maklumat dengan betul dan mendengar arahan dengan baik semasa bertugas. Sentiasa berinteraksi dengan pihak syarikat jika tidak faham atau tidak pasti. Jangan buat keputusan sendiri tanpa berbincang.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(45, 1, \'Perlu meningkatkan housekeeping, kekemasan dan kebersihan tempat kerja.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(46, 1, \'Belajar menggunakan balancing machine.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(47, 1, \'Mempertingkatkan leasdership skill dan menjadi team leader dalam welding.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(48, 1, \'Melatih dan memberi tunjuk ajar kepada pekerja lain yang perlu.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(49, 1, \'Belajar supaya mempunyai kemahiran untuk buat program tube best dan plate laser cutting. RM100\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(50, 1, \'Belajar supaya mempunyai kemahiran menjalani bending mesin. RM100\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(51, 1, \'Bertangguangjwab mengatur kerja dan meningkatkan leasership skill untuk jadi leader.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(52, 1, \'Check stock dan order plate dan pipe.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(53, 1, \'Memastikan kualiti dan hasil kerja terbaik. QC dibuat dan buang burr setiap part. Terutamanya pipe.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(54, 1, \'Menyusun dan organize parts yang dihasilkan untuk dihantar ke department seterusnya. \', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(55, 1, \'Tingkatkan pengetahuan dan kemahiran teknikal pemasangan mesin dan faham concept kerja.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(56, 1, \'Belajar supaya mempunyai kemahiran menjalani bending mesin.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(57, 1, \'Melatih pekerja baru untuk menjalan mesin, membuat maintenance mesin dan housekeeping tempat kerja. \', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(58, 1, \'Mengeratkan hubungan baik diantar rakan sekerja dan penolong leader.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(59, 1, \'Menegur pekerja di painting department yang curi tulang.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(60, 1, \'Melatih dan mengajar orang baru untuk cat powder dan spray.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(61, 1, \'Pastikan painting department tidak sapu sisa cat dan powder ke dalam longkang.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(62, 1, \'Pastikan longkang dicuci setiap minggu supaya bebas dari kesan cat.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(63, 1, \'Menjalankan tugasan dan siapkan kerja dengan cepat dan berkualiti.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(64, 1, \'Meningkatkan kualiti kerja. Pastikan pemeriksaan QC pada setiap barang yang di cat.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(65, 1, \'Membantu untuk mengedalikan scheduled waste.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(66, 1, \'Mesti rujuk pada spec drawing semasa kerja pemasangan. Jangan buat anggapan sendiri.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(67, 1, \'Meningkatkan kualiti kerja. Pastikan pemeriksaan QC pada setiap hasil kerja atau pemasangan. (Banyak kali PC70 yang dipasang ada masalah eccentric lari semasa test di site)\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(68, 1, \'Mengeratkan hubungan baik diantar rakan sekerja dan leader.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(69, 1, \'Bersungguh-sunguh dan focus sepenuhnya pada kerja. Tidak berangan angan dan bersembang.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(70, 1, \'Ikhlas dan jujur semasa bekerja. Tidak curi tulang walaupun tanpa jagaan leader.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(71, 1, \'Belajar untuk spray cat dan powder coat.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(72, 1, \'Menjaga dan membimbing pekerja bawahan supaya mematuhi displin kerja di automation. Terutamanya tidak bersembang semasa kerja.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(73, 1, \'Aturkan tempat kerja, terutamanya control panel supaya tempat kerja tidak tersembunyi dan dapat dilihat dengan jelas.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(74, 1, \'Bertanggungjawab menguruskan kerja di automation supaya mencapai target pengeluaran syarikat.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(75, 1, \'Membantu untuk membaiki peralatan elektrik di kilang bila berlaku kerosakan.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(76, 1, \'Melaksana dan memastikan kualiti dipatuhi oleh setiap pekerja bawahan dengan menjalani pemeriksaan kualiti pada setiap hasil kerja.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(77, 1, \'Perlu percepatkan pergerakkan semasa kerja dan lebih focus semasa kerja.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(78, 1, \'Bertanggungjawab sepenuhnya untuk kerja yang diberi, terutamnya untuk pengaturan parts.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(79, 1, \'Peningkatan kemahiran kepimpinan dan kebolehan membimbing, mempengaruhi dan memotivasikan pekerja bawahan supaya mencapai matlamat atau objektif bersama.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(80, 1, \'Mengaturkan kerja supaya mencapai target pengeluaran syarikat.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(81, 1, \'Melatih dan menunjuk pekerja cara membuat kerja. Beri target kepada mereka. Bukan buat sendiri.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(82, 1, \'Memikirkan cara baru yang kreatif untuk mempercepatkan proses kerja dan tingkatkan produktiviti.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(83, 1, \'Bersedia untuk kerja outstation. Tidak memilih tugasan dan rakan sekerja bila bekerja di outstation.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(84, 1, \'Berkomunikasi secara jelas, secara lisan atau bertulis. Memberi feedback sama ada di kilang atau onsite.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(85, 1, \'Memastikan barang penghantaran betul dan disusun dengan baik supaya tidak berlaku kerosakan. \', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(86, 1, \'Tingkatkan pengetahuan nama dan parts mesin, dan juga kemahiran teknikal proses kerja dispatch, terutamanya dalam penghantaran barang dan mesin.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(87, 1, \'Barang atau mesin yang dikutip mesti betul, disusun/diikat dengan baik diatas lori supaya tidak berlaku kerosakan semasa penghantaran. \', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(88, 1, \'Tidak bersembang semasa kerja. Didapati perangai ini masih tidak ubah walaupun banyak kali diberi teguran. Ini telah mempengaruhi kerja rakan sekerja lain.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(89, 1, \'Membantu untuk menguruskan kerja supaya mencapai target pengeluaran syarikat. \', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(90, 1, \'Mengikuti setiap SOP store dengan ketat. Tidak cuai dan pastikan setiap item dan quantity IN dan OUT parts direkodkan.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(91, 1, \'Memastikan SOP store IN dan OUT diikuti selalu.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(92, 1, \'Tegas dalam mengambil tindakan untuk menegur team member apabila tidak mengikut SOP.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(93, 1, \'Membuat system penyimpanan yang simtematik.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(94, 1, \'Housekeeping di buat setiap masa dan pastikan tiada barang yang selalu kumpul di lantai.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(95, 1, \'Membuat incoming QC dan outgoing QC untuk memastikan spec dan quantity yang betul.\', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\'),
(96, 1, \'Memastikan parts dan barang dihantar kepada production tepat pada masa untuk mengelakkan kelewatan di bahagian production. \', NULL, NULL, \'2024-04-25 12:27:48\', \'2024-04-25 12:27:48\');');

    }
}
