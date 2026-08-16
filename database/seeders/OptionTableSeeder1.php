<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OptionTableSeeder1 extends Seeder
{
    /**
     * Seed the application's database with exact data from ipmaerp.sql.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('option_amounts')->truncate();
        DB::table('option_appraisal_categories')->truncate();
        DB::table('option_authorities')->truncate();
        DB::table('option_banks')->truncate();
        DB::table('option_branches')->truncate();
        DB::table('option_categories')->truncate();
        DB::table('option_countries')->truncate();
        DB::table('option_currencies')->truncate();
        DB::table('option_daytypes')->truncate();
        DB::table('option_departments')->truncate();
        DB::table('option_disciplinary_actions')->truncate();
        DB::table('option_disciplines')->truncate();
        DB::table('option_discount_types')->truncate();
        DB::table('option_div')->truncate();
        DB::table('option_driving_licenses')->truncate();
        DB::table('option_education_levels')->truncate();
        DB::table('option_genders')->truncate();
        DB::table('option_groups')->truncate();
        DB::table('option_halfday_type')->truncate();
        DB::table('option_health_statuses')->truncate();
        DB::table('option_infractions')->truncate();
        DB::table('option_leave_statuses')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ---- option_amounts (2 rows) ----
        DB::unprepared('INSERT INTO `option_amounts` VALUES
(1, \'Additional Amount\', \'+\', NULL, \'2018-12-03 15:53:52\', \'2018-12-03 15:53:52\'),
(2, \'Deduction Amount\', \'-\', NULL, \'2018-12-03 15:53:52\', \'2018-12-03 15:53:52\');');

        // ---- option_appraisal_categories (17 rows) ----
        DB::unprepared('INSERT INTO `option_appraisal_categories` VALUES
(1, \'Account Payable\', NULL, NULL, NULL),
(2, \'Account Receivable\', NULL, NULL, NULL),
(3, \'Engineering\', NULL, NULL, NULL),
(4, \'General\', NULL, NULL, NULL),
(5, \'Office\', NULL, NULL, NULL),
(6, \'Assembly\', NULL, NULL, NULL),
(7, \'Automation\', NULL, NULL, NULL),
(8, \'Cleaner\', NULL, NULL, NULL),
(9, \'Bending\', NULL, NULL, NULL),
(10, \'Cutting\', NULL, NULL, NULL),
(11, \'Lasercut\', NULL, NULL, NULL),
(12, \'Customer Service\', NULL, NULL, NULL),
(13, \'Dispatch And Delivery\', NULL, NULL, NULL),
(14, \'Inventory\', NULL, NULL, NULL),
(15, \'Machining\', NULL, NULL, NULL),
(16, \'Painting\', NULL, NULL, NULL),
(17, \'Welding\', NULL, NULL, NULL);');

        // ---- option_authorities (1 rows) ----
        DB::unprepared('INSERT INTO `option_authorities` VALUES
(1, \'Administrator\', \'2023-07-07 15:30:32\', \'2023-07-07 15:30:32\', NULL);');

        // ---- option_banks (18 rows) ----
        DB::unprepared('INSERT INTO `option_banks` VALUES
(1, \'CIMB Group Holdings\', NULL, NULL, NULL, NULL),
(2, \'Public Bank Berhad\', NULL, NULL, NULL, NULL),
(3, \'RHB Bank\', NULL, NULL, NULL, NULL),
(4, \'Hong Leong Bank\', NULL, NULL, NULL, NULL),
(5, \'AmBank\', NULL, NULL, NULL, NULL),
(6, \'UOB Malaysia\', NULL, NULL, NULL, NULL),
(7, \'Bank Rakyat\', NULL, NULL, NULL, NULL),
(8, \'Malayan Banking Berhad (Maybank)\', NULL, NULL, NULL, NULL),
(9, \'OCBC Bank Malaysia\', NULL, NULL, NULL, NULL),
(10, \'HSBC Bank Malaysia\', NULL, NULL, NULL, NULL),
(11, \'Bank Islam Malaysia\', NULL, NULL, NULL, NULL),
(12, \'Affin Bank\', NULL, NULL, NULL, NULL),
(13, \'Alliance Bank Malaysia Berhad\', NULL, NULL, NULL, NULL),
(14, \'Standard Chartered Bank Malaysia\', NULL, NULL, NULL, NULL),
(15, \'Citibank Malaysia\', NULL, NULL, NULL, NULL),
(16, \'Bank Simpanan Nasional (BSN)\', NULL, NULL, NULL, NULL),
(17, \'Bank Muamalat Malaysia Berhad\', NULL, NULL, NULL, NULL),
(18, \'Agrobank\', NULL, NULL, NULL, NULL);');

        // ---- option_branches (2 rows) ----
        DB::unprepared('INSERT INTO `option_branches` VALUES
(1, \'A\', \'IPMA A\', NULL, \'2018-07-28 17:06:28\', \'2018-07-28 17:06:34\'),
(2, \'B\', \'IPMA B\', NULL, \'2018-07-28 17:06:32\', \'2018-07-28 17:06:37\');');

        // ---- option_categories (2 rows) ----
        DB::unprepared('INSERT INTO `option_categories` VALUES
(1, \'O\', \'Office\', NULL, \'2018-07-28 10:50:52\', \'2018-07-28 10:50:56\'),
(2, \'P\', \'Production\', NULL, \'2018-07-28 10:51:54\', \'2018-07-28 10:51:57\');');

        // ---- option_countries (240 rows) ----
        DB::unprepared('INSERT INTO `option_countries` VALUES
(3, \'Afghanistan\', \'Afghan afghani\', \' ؋\', \'AFN \', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(4, \'Albania\', \'Albanian lek\', \'L\', \'ALL\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(5, \'Algeria\', \'Algerian dinar\', \'د.ج\', \'DZD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(6, \'American Samoa\', \'Samoan tālā\', \'T\', \'WST\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(7, \'Andorra\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(8, \'Angola\', \'Angolan kwanza\', \'Kz\', \'AOA\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(9, \'Anguilla\', \'Eastern Caribbean dollar\', \'$\', \'XCD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(11, \'Antigua And Barbuda\', \'Eastern Caribbean dollar\', \'$\', \'XCD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(12, \'Argentina\', \'Argentine peso\', \'$\', \'ARS\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(13, \'Armenia\', \'Armenian dram\', \'֏\', \'AMD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(14, \'Aruba\', \'Aruban florin\', \'ƒ\', \'AWG\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(15, \'Australia\', \'Australian dollar\', \'$\', \'AUD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(16, \'Austria\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(17, \'Azerbaijan\', \'Azerbaijani manat\', \'₼\', \'AZN\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(18, \'Bahamas\', \'Bahamian dollar\', \'$\', \'BSD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(19, \'Bahrain\', \'Bahraini dinar\', \'.د.ب\', \'BHD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(20, \'Bangladesh\', \'Bangladeshi taka\', \'৳\', \'BDT\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(21, \'Barbados\', \'Barbadian dollar\', \'$\', \'BBD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(22, \'Belarus\', \'Belarusian ruble\', \'Br\', \'BYN\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(23, \'Belgium\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(24, \'Belize\', \'Belize dollar\', \'$\', \'BZD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(25, \'Benin\', \'West African CFA franc\', \'Fr\', \'XOF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(26, \'Bermuda\', \'Bermudian dollar\', \'$\', \'BMD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(27, \'Bhutan\', \'Bhutanese ngultrum\', \'Nu.\', \'BTN\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(28, \'Bolivia\', \'Bolivian boliviano\', \'Bs.\', \'BOB\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(29, \'Bosnia And Herzegovina\', \'Bosnia and Herzegovina convertible mark\', \'KM\', \'BAM\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(30, \'Botswana\', \'Botswana pula\', \'P\', \'BWP\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(31, \'Bouvet Island\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(32, \'Brazil\', \'Brazilian real\', \'R$\', \'BRL\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(33, \'British Indian Ocean Territory\', \'United States dollar\', \'$\', \'USD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(34, \'Brunei Darussalam\', \'Brunei Dollar\', \'$\', \'BND\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(35, \'Bulgaria\', \'Bulgarian lev\', \'лв\', \'BGN\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(36, \'Burkina Faso\', \'West African CFA franc\', \'Fr\', \'XOF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(37, \'Burundi\', \'Burundian franc\', \'Fr\', \'BIF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(38, \'Cambodia\', \'Cambodian riel\', \'៛\', \'KHR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(39, \'Cameroon\', \'Central African CFA franc\', \'r\', \'XAF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(40, \'Canada\', \'Canadian dollar\', \'$\', \'CAD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(41, \'Cape Verde\', \'Cape Verdean escudo\', \'Esc\', \'CVE\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(42, \'Cayman Islands\', \'Cayman Islands dollar\', \'$\', \'KYD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(43, \'Central African Republic\', \'Central African CFA franc\', \'Fr\', \'XAF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(44, \'Chad\', \'Central African CFA franc\', \'Fr\', \'XAF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(45, \'Chile\', \'Chilean peso\', \'$\', \'CLP\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(46, \'China\', \'Chinese yuan\', \'¥\', \'CNY\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(47, \'Christmas Island\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(48, \'Cocos (keeling) Islands\', \'Australian dollar\', \'$\', \'AUD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(49, \'Colombia\', \'Colombian peso\', \'$\', \'COP\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(50, \'Comoros\', \'Comorian franc\', \'Fr\', \'KMF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(51, \'Congo\', \'Congolese Franc\', \'Fr\', \'XAF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(52, \'Congo, The Democratic Republic Of The\', \'Central African CFA franc\', \'Fr\', \'CDF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(53, \'Cook Islands\', \'New Zealand dollar\', \'$\', \'NZD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(54, \'Costa Rica\', \'Costa Rican colón\', \'₡\', \'CRC\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(55, \'Cote d\\\'Ivoire\', \'West African CFA franc\', \'Fr\', \'XOF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(56, \'Croatia\', \'Croatian kuna\', \'kn\', \'HRK\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(57, \'Cuba\', \'Cuban peso\', \'$\', \'CUP\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(58, \'Cyprus\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(59, \'Czech Republic\', \'Czech koruna\', \'Kč\', \'CZK\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(60, \'Denmark\', \'Danish krone\', \'kr\', \'DKK\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(61, \'Djibouti\', \'Djiboutian franc\', \'Fr\', \'DJF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(62, \'Dominica\', \'Eastern Caribbean dollar\', \'$\', \'XCD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(63, \'Dominican Republic\', \'Dominican peso\', \'$\', \'DOP\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(64, \'East Timor\', \'United States dollar\', \'$\', \'USD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(65, \'Ecuador\', \'Unoted States Dollar\', \'$\', \'USD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(66, \'Egypt\', \'Egyptian pound\', \'ج.م\', \'EGP\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(67, \'El Salvador\', \'United States dollar\', \'$\', \'USD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(68, \'Equatorial Guinea\', \'Central African CFA franc\', \'Fr\', \'XAF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(69, \'Eritrea\', \'Eritrean nakfa\', \'Nfk\', \'ERN\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(70, \'Estonia\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(71, \'Ethiopia\', \'Ethiopian birr\', \'Br\', \'ETB\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(72, \'Falkland Islands\', \'Falkland Islands pound\', \'£\', \'FKP\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(73, \'Faroe Islands\', \'Danish krone\', \'kr\', \'DKK\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(74, \'Fiji\', \'Fijian dollar\', \'$\', \'FJD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(75, \'Finland\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(76, \'France\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(77, \'French Guiana\', \'Guinean franc\', \'Fr\', \'GNF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(78, \'French Polynesia\', \'CFP franc\', \'₣\', \'XPF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(79, \'French Southern Territories\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(80, \'Gabon\', \'Central African CFA franc\', \'Fr\', \'XAF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(81, \'Gambia\', \'Gambian dalasi\', \'D\', \'GMD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(82, \'Georgia\', \'Georgian lari\', \'₾\', \'GEL\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(83, \'Germany\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(84, \'Ghana\', \'Ghanaian cedi\', \'₵\', \'GHS\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(85, \'Gibraltar\', \'Gibraltar pound\', \'£\', \'GIP\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(86, \'Greece\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(87, \'Greenland\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(88, \'Grenada\', \'Eastern Caribbean dollar\', \'$\', \'XCD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(89, \'Guadeloupe\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(90, \'Guam\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(91, \'Guatemala\', \'Guatemalan quetzal\', \'Q\', \'GTQ\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(92, \'Guinea\', \'Guinean franc\', \'Fr\', \'GNF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(93, \'Guinea-Bissau\', \'West African CFA franc\', \'Fr\', \'XOF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(94, \'Guyana\', \'Guyanese dollar\', \'$\', \'GYD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(95, \'Haiti\', \'Haitian gourde\', \'G\', \'HTG\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(96, \'Heard Island And Mcdonald Islands\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(97, \'Holy See (Vatican City State)\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(98, \'Honduras\', \'Honduran lempira\', \'L\', \'HNL\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(99, \'Hong Kong\', \'Hong Kong dollar\', \'$\', \'HKD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(100, \'Hungary\', \'Hungarian forint\', \'Ft\', \'HUF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(101, \'Iceland\', \'Icelandic króna\', \'kr\', \'ISK\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(102, \'India\', \'Indian rupee\', \'₹\', \'INR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(103, \'Indonesia\', \'Indonesian rupiah\', \'Rp\', \'IDR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\');');
        DB::unprepared('INSERT INTO `option_countries` VALUES
(104, \'Iran\', \'Iranian rial\', \'﷼\', \'IRR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(105, \'Iraq\', \'Iraqi dinar\', \'ع.د\', \'IQD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(106, \'Ireland\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(107, \'Israel\', \'Israeli new shekel\', \'₪\', \'ILS\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(108, \'Italy\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(109, \'Jamaica\', \'Jamaican dollar\', \'$\', \'JMD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(110, \'Japan\', \'Japanese yen\', \'¥\', \'JPY\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(111, \'Jordan\', \'Jordanian dinar\', \'د.ا\', \'JOD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(112, \'Kazakstan\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(113, \'Kenya\', \'Kenyan shilling\', \'h\', \'KES\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(114, \'Kiribati\', \'Australian dollar\', \'$\', \'AUD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(115, \'Korea, Democratic People\\\'s Republic Of\', \'Won\', \'₩\', \'KPW\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(116, \'Korea, Republic Of\', \'Won\', \'₩\', \'KRW\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(117, \'Kosovo\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(118, \'Kuwait\', \'Kuwaiti dinar\', \'د.ك\', \'KWD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(119, \'Kyrgyzstan\', \'Kyrgyzstani som\', \'с\', \'KGS\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(120, \'Lao People\\\'s Democratic Republic\', \'Lao kip\', \'₭\', \'LAK\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(121, \'Latvia\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(122, \'Lebanon\', \'Lebanese pound\', \'ل.ل\', \'LBP\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(123, \'Lesotho\', \'Lesotho loti\', \'L\', \'LSL\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(124, \'Liberia\', \'Liberian dollar\', \'$\', \'LRD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(125, \'Libyan Arab Jamahiriya\', \'Libyan dinar\', \'ل.د\', \'LYD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(126, \'Liechtenstein\', \'Swiss franc\', \'Fr\', \'CHF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(127, \'Lithuania\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(128, \'Luxembourg\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(129, \'Macau\', \'Macanese pataca\', \'P\', \'MOP\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(130, \'Macedonia, The Former Yugoslav Republic Of\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(131, \'Madagascar\', \'Malagasy ariary\', \'Ar\', \'MGA\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(132, \'Malawi\', \'Malawian kwacha\', \'MK\', \'MWK\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(133, \'Malaysia\', \'Malaysian ringgit\', \'RM\', \'MYR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(134, \'Maldives\', \'Maldivian rufiyaa\', \'.ރ\', \'MVR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(135, \'Mali\', \'West African CFA franc\', \'Fr\', \'XOF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(136, \'Malta\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(137, \'Marshall Islands\', \'United States dollar\', \'$\', \'USD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(138, \'Martinique\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(139, \'Mauritania\', \'Mauritanian ouguiya\', \'UM\', \'MRU\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(140, \'Mauritius\', \'Mauritian rupee\', \'₨\', \'MUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(141, \'Mayotte\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(142, \'Mexico\', \'Mexican peso\', \'$\', \'MXN\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(143, \'Micronesia, Federated States Of\', \'United States dollar\', \'$\', \'USD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(144, \'Moldova, Republic Of\', \'Moldovan leu\', \'L\', \'MDL\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(145, \'Monaco\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(146, \'Mongolia\', \'Mongolian tögrög\', \'₮\', \'MNT\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(147, \'Montserrat\', \'Eastern Caribbean dollar\', \'$\', \'XCD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(148, \'Montenegro\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(149, \'Morocco\', \'Moroccan dirham\', \'د.م.\', \'MAD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(150, \'Mozambique\', \'Mozambican metical\', \'MT\', \'MZN\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(151, \'Myanmar\', \'Burmese kyat\', \'Ks\', \'MMK\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(152, \'Namibia\', \'Namibian dollar\', \'$\', \'NAD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(153, \'Nauru\', \'Australian dollar\', \'$\', \'AUD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(154, \'Nepal\', \'Nepalese rupee\', \'₨\', \'NPR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(155, \'Netherlands\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(156, \'Netherlands Antilles\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(157, \'New Caledonia\', \'CFP franc\', \'₣\', \'XPF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(158, \'New Zealand\', \'New Zealand dollar\', \'$\', \'NZD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(159, \'Nicaragua\', \'Nicaraguan córdoba\', \'C$\', \'NIO\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(160, \'Niger\', \'West African CFA franc\', \'Fr\', \'XOF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(161, \'Nigeria\', \'Nigerian naira\', \'₦\', \'NGN\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(162, \'Niue\', \'New Zealand dollar\', \'$\', \'NZD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(163, \'Norfolk Island\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(164, \'Northern Mariana Islands\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(165, \'Norway\', \'Norwegian krone\', \'kr\', \'NOK\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(166, \'Oman\', \'Omani rial\', \'ر.ع.\', \'OMR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(167, \'Pakistan\', \'Pakistani rupee\', \'₨\', \'PKR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(168, \'Palau\', \'United States dollar\', \'$\', \'USD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(169, \'Palestinian Territory, Occupied\', \'Jordanian dinar\', \'د.ا\', \'JOD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(170, \'Panama\', \'Panamanian balboa\', \'B/.\', \'PAB\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(171, \'Papua New Guinea\', \'Papua New Guinean kina\', \'K\', \'PGK\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(172, \'Paraguay\', \'Paraguayan guaraní\', \'₲\', \'PYG\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(173, \'Peru\', \'Peruvian sol\', \'S/.\', \'PEN\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(174, \'Philippines\', \'Philippine peso\', \'₱\', \'PHP\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(175, \'Pitcairn\', \'New Zealand dollar\', \'$\', \'NZD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(176, \'Poland\', \'Polish złoty\', \'zł\', \'PLN\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(177, \'Portugal\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(178, \'Puerto Rico\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(179, \'Qatar\', \'Qatari riyal\', \'ر.ق\', \'QAR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(180, \'Reunion\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(181, \'Romania\', \'Romanian leu\', \'lei\', \'RON\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(182, \'Russian Federation\', \'Russian ruble\', \'₽\', \'RUB\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(183, \'Rwanda\', \'Rwandan franc\', \'Fr\', \'RWF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(184, \'Saint Helena\', \'Saint Helena pound\', \'£\', \'SHP\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(185, \'Saint Kitts And Nevis\', \'Eastern Caribbean dollar\', \'$\', \'XCD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(186, \'Saint Lucia\', \'Eastern Caribbean dollar\', \'$\', \'XCD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(187, \'Saint Pierre And Miquelon\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(188, \'Saint Vincent And The Grenadines\', \'Eastern Caribbean dollar\', \'$\', \'XCD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(189, \'Samoa\', \'Samoan tālā\', \'T\', \'WST\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(190, \'San Marino\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(191, \'Sao Tome And Principe\', \'São Tomé and Príncipe dobra\', \'Db\', \'STN\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(192, \'Saudi Arabia\', \'Saudi riyal\', \'ر.س\', \'SAR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(193, \'Senegal\', \'West African CFA franc\', \'Fr\', \'XOF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(194, \'Serbia\', \'Serbian dinar\', \'дин.\', \'RSD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(195, \'Seychelles\', \'Seychellois rupee\', \'₨\', \'SCR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(196, \'Sierra Leone\', \'Sierra Leonean leone\', \'Le\', \'SLL\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(197, \'Singapore\', \'Singapore dollar\', \'$\', \'SGD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(198, \'Slovakia\', \'Euro\', \'€\', \'\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(199, \'Slovenia\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(200, \'Solomon Islands\', \'Solomon Islands dollar\', \'$\', \'SBD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(201, \'Somalia\', \'Somali shilling\', \'Sh\', \'SOS\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(202, \'South Africa\', \'South African rand\', \'R\', \'ZAR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(203, \'South Georgia And The South Sandwich Islands\', \'British pound\', \'£\', \'GBP\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\');');
        DB::unprepared('INSERT INTO `option_countries` VALUES
(204, \'Spain\', \'Euro\', \'€\', \'EUR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(205, \'Sri Lanka\', \'Sri Lankan rupee\', \'ரூ\', \'LKR\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(206, \'Sudan\', \'Sudanese pound\', \'ج.س. \', \'SDG\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(207, \'Suriname\', \'Surinamese dollar\', \'$\', \'SRD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(208, \'Svalbard And Jan Mayen\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(209, \'Swaziland\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(210, \'Sweden\', \'Swedish krona\', \'kr\', \'SEK\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(211, \'Switzerland\', \'Swiss franc\', \'Fr.\', \'CHF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(212, \'Syrian Arab Republic\', \'Syrian pound\', \'ل.س\', \'SYP\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(213, \'Taiwan, Province Of China\', \'New Taiwan dollar\', \'$\', \'TWD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(214, \'Tajikistan\', \'Tajikistani somoni\', \'ЅМ\', \'TJS\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(215, \'Tanzania, United Republic Of\', \'Tanzanian shilling\', \'Sh\', \'TZS\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(216, \'Thailand\', \'Thai baht\', \'฿\', \'THB\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(217, \'Togo\', \'West African CFA franc\', \'Fr\', \'XOF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(218, \'Tokelau\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(219, \'Tonga\', \'Tongan paʻanga\', \'T$\', \'TOP\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(220, \'Trinidad And Tobago\', \'Trinidad and Tobago dollar\', \'$\', \'TTD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(221, \'Tunisia\', \'Tunisian dinar\', \'د.ت\', \'TND\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(222, \'Turkey\', \'Turkish lira\', \'₺\', \'TRY\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(223, \'Turkmenistan\', \'Turkmenistan manat\', \'m\', \'TMT\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(224, \'Turks And Caicos Islands\', \'United States dollar\', \'$\', \'USD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(225, \'Tuvalu\', \'Tuvaluan dollar\', \'$\', \'TVD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(226, \'Uganda\', \'Ugandan shilling\', \'Sh\', \'UGX\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(227, \'Ukraine\', \'Ukrainian hryvnia\', \'₴\', \'UAH\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(228, \'United Arab Emirates\', \'United Arab Emirates dirham\', \'د.إ\', \'AED\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(229, \'United Kingdom\', \'British pound\', \'£\', \'GBP\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(230, \'United States\', \'United States dollar\', \'$\', \'USD\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(231, \'United States Minor Outlying Islands\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(232, \'Uruguay\', \'Uruguayan peso\', \'$\', \'UYU\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(233, \'Uzbekistan\', \'Uzbekistani soʻm\', \'so\\\'m\', \'UZS\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(234, \'Vanuatu\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(235, \'Venezuela\', \'Venezuelan bolívar soberano\', \'Bs.S.\', \'VES\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(236, \'Viet Nam\', \'Vietnamese đồng\', \'₫\', \'VND\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(237, \'Virgin Islands, British\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(238, \'Virgin Islands, U.s.\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(239, \'Wallis And Futuna\', \'CFP franc\', \'₣\', \'XPF\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(240, \'Western Sahara\', NULL, NULL, NULL, NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(241, \'Yemen\', \'Yemeni rial\', \'﷼\', \'YER\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(242, \'Zambia\', \'Zambian kwacha\', \'ZK\', \'ZMW\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\'),
(243, \'Zimbabwe\', \'Zimbabwean bonds\', \'$\', \'ZWB\', NULL, \'2018-07-30 17:30:12\', \'2018-07-30 17:30:12\');');

        // ---- option_currencies (2 rows) ----
        DB::unprepared('INSERT INTO `option_currencies` VALUES
(1, \'Ringgit Malaysia\', \'MYR\', \'RM\', NULL, \'2019-03-21 15:34:57\', \'2019-03-21 15:34:57\'),
(2, \'United States Dollar\', \'USD\', \'$\', NULL, \'2019-03-21 15:34:57\', \'2019-03-21 15:34:57\');');

        // ---- option_daytypes (3 rows) ----
        DB::unprepared('INSERT INTO `option_daytypes` VALUES
(1, \'WORKDAY\', \'2023-07-07 11:36:31\', \'2023-07-07 11:36:31\', NULL),
(2, \'RESTDAY\', \'2023-07-07 11:37:12\', \'2023-07-07 11:37:12\', NULL),
(3, \'HOLIDAY\', \'2023-07-07 11:37:40\', \'2023-07-07 11:37:40\', NULL);');

        // ---- option_departments (23 rows) ----
        DB::unprepared('INSERT INTO `option_departments` VALUES
(1, \'Account\', NULL, \'2018-07-28 12:25:29\', \'2018-07-28 12:26:10\'),
(2, \'Assembly\', NULL, \'2018-07-28 13:53:01\', \'2018-07-28 13:53:04\'),
(3, \'Automation\', NULL, \'2018-07-30 11:25:49\', \'2018-07-30 11:25:51\'),
(4, \'Bending\', NULL, \'2018-07-28 13:52:42\', \'2018-07-28 13:52:46\'),
(5, \'Costing\', NULL, \'2018-07-28 12:25:46\', \'2018-07-28 12:26:45\'),
(6, \'Customer Service\', NULL, \'2018-07-28 12:25:53\', \'2018-07-28 12:26:57\'),
(8, \'Cutting\', NULL, \'2018-07-28 13:51:36\', \'2018-07-28 13:51:43\'),
(11, \'Dispatch & Delivery\', NULL, \'2023-07-11 10:40:39\', \'2023-07-11 10:40:39\'),
(12, \'Engineering\', NULL, \'2018-07-28 12:25:49\', \'2018-07-28 12:26:48\'),
(13, \'Security\', NULL, \'2023-07-11 10:40:39\', \'2023-07-11 10:40:39\'),
(14, \'Human Resource\', NULL, \'2018-07-28 12:25:36\', \'2018-07-28 12:26:35\'),
(15, \'Information Technology & Design\', NULL, \'2018-07-28 12:25:40\', \'2018-07-28 12:26:39\'),
(16, \'Inventory\', NULL, \'2023-07-11 10:40:39\', \'2023-07-11 10:40:39\'),
(18, \'Machining\', NULL, \'2018-07-28 13:51:45\', \'2018-07-28 13:51:48\'),
(19, \'Maintenance\', NULL, \'2018-07-28 13:54:05\', \'2018-07-28 13:54:08\'),
(20, \'Painting\', NULL, \'2018-07-28 13:52:55\', \'2018-07-28 13:52:58\'),
(21, \'Production Mangement\', NULL, \'2018-09-26 14:26:33\', \'2018-09-26 14:26:33\'),
(22, \'Programmer\', NULL, \'2023-07-11 10:40:39\', \'2023-07-11 10:40:39\'),
(23, \'Purchasing\', NULL, \'2018-07-28 12:25:33\', \'2018-07-28 12:26:31\'),
(24, \'Sales\', NULL, \'2018-07-28 12:25:43\', \'2018-07-28 12:26:42\'),
(25, \'Sheet Metal Processing\', NULL, \'2023-07-11 10:40:39\', \'2023-07-11 10:40:39\'),
(27, \'Welding\', NULL, \'2018-07-28 13:52:50\', \'2018-07-28 13:52:52\'),
(79, \'High Management\', NULL, \'2023-07-19 09:53:02\', \'2023-07-19 09:53:02\');');

        // ---- option_disciplinary_actions (4 rows) ----
        DB::unprepared('INSERT INTO `option_disciplinary_actions` VALUES
(1, \'Termination\', NULL, \'2019-02-27 08:54:00\', \'2019-02-27 08:54:00\'),
(2, \'Verbal Warning\', NULL, \'2023-10-02 14:36:12\', NULL),
(3, \'Warning Letter\', NULL, \'2023-10-02 14:36:25\', NULL),
(4, \'Demotion\', NULL, \'2023-10-02 14:44:23\', NULL);');

        // ---- option_disciplines (7 rows) ----
        DB::unprepared('INSERT INTO `option_disciplines` VALUES
(1, \'Late\', \'0.5m/time\', 0.50, NULL, \'2018-09-03 08:48:25\', \'2018-09-03 08:48:25\'),
(2, \'Frequency Unpaid Leave\', \'1m for more than 5 days, 2m for 10 days & above\', 1.00, NULL, \'2018-09-03 08:48:25\', \'2018-09-03 08:48:25\'),
(3, \'Frequency Medical Certificate\', \'1m for more than 8 days, 2m for 14 days & above\', 1.00, NULL, \'2018-09-03 08:48:25\', \'2018-09-03 08:48:25\'),
(4, \'Emergency Leave without Supporting Document\', \'0.5m per time\', 0.50, NULL, \'2018-09-03 08:48:25\', \'2018-09-03 08:48:25\'),
(5, \'Absent\', \'1m/time\', 1.00, NULL, \'2018-09-03 08:48:25\', \'2018-09-03 08:48:25\'),
(6, \'Late Leave Form Submission\', \'0.5m/time\', 0.50, NULL, \'2018-09-03 08:48:25\', \'2018-09-03 08:48:25\'),
(7, \'Didn\\\'t Apply Leave 3 Days In Advance\', \'0.5m/time\', 0.50, NULL, \'2018-09-03 08:48:25\', \'2018-09-03 08:48:25\');');

        // ---- option_discount_types (2 rows) ----
        DB::unprepared('INSERT INTO `option_discount_types` VALUES
(1, \'Percentage\', \'%\', NULL, \'2018-12-03 16:02:00\', \'2018-12-03 16:02:00\'),
(2, \'Amount Deduction\', \'-\', NULL, \'2018-12-03 16:02:00\', \'2018-12-03 16:02:00\');');

        // ---- option_div (4 rows) ----
        DB::unprepared('INSERT INTO `option_div` VALUES
(1, \'HOD\', \'2023-07-20 16:49:05\', \'2023-07-20 16:49:05\', NULL),
(2, \'DIRECTOR\', \'2023-07-20 16:49:05\', \'2023-07-20 16:49:05\', NULL),
(4, \'SUPERVISOR\', \'2023-07-20 16:49:05\', \'2023-07-20 16:49:05\', NULL),
(5, \'Assistant HOD\', \'2023-08-18 09:43:30\', \'2023-08-18 09:43:30\', NULL);');

        // ---- option_driving_licenses (15 rows) ----
        DB::unprepared('INSERT INTO `option_driving_licenses` VALUES
(1, \'A\', \'Kenderaan Orang Cacat (Motosikal) BTM tidak melebihi 450kg\', NULL, \'2018-07-31 10:25:34\', \'2018-07-31 10:25:34\'),
(2, \'A1\', \'Kenderaan Orang Cacat (Motokar) BTM tidak melebihi 3500 kg\', NULL, \'2018-07-31 10:25:34\', \'2018-07-31 10:25:34\'),
(3, \'B\', \'Motosikal melebihi 500 cc\', NULL, \'2018-07-31 10:25:34\', \'2018-07-31 10:25:34\'),
(4, \'B1\', \'Motosikal tidak melebihi 500 cc\', NULL, \'2018-07-31 10:25:34\', \'2018-07-31 10:25:34\'),
(5, \'B2\', \'Motosikal tidak melebihi 250 cc\', NULL, \'2018-07-31 10:25:34\', \'2018-07-31 10:25:34\'),
(6, \'C\', \'Motosikal Tiga Roda\', NULL, \'2018-07-31 10:25:34\', \'2018-07-31 10:25:34\'),
(7, \'D\', \'Motokar BTM (berat tanpa muatan) tidak melebihi 3500 kg\', NULL, \'2018-07-31 10:25:34\', \'2018-07-31 10:25:34\'),
(8, \'DA\', \'Motokar Tanpa Pedal Klac BTM tidak melebihi 3500 kg\', NULL, \'2018-07-31 10:25:34\', \'2018-07-31 10:25:34\'),
(9, \'E\', \'Motokar Berat BTM melebihi 7500 kg\', NULL, \'2018-07-31 10:25:34\', \'2018-07-31 10:25:34\'),
(10, \'E1\', \'Motokar Berat BTM tidak melebihi 7500 kg\', NULL, \'2018-07-31 10:25:34\', \'2018-07-31 10:25:34\'),
(11, \'E2\', \'Motokar Berat BTM tidak melebihi 5000 kg\', NULL, \'2018-07-31 10:25:34\', \'2018-07-31 10:25:34\'),
(12, \'F\', \'Traktor/Jentera Bergerak Ringan (Beroda) BTM tidak melebihi 5000 kg\', NULL, \'2018-07-31 10:25:34\', \'2018-07-31 10:25:34\'),
(13, \'G\', \'Traktor/Jentera Bergerak Ringan (Berantai) BTM tidak melebihi 5000 kg\', NULL, \'2018-07-31 10:25:34\', \'2018-07-31 10:25:34\'),
(14, \'H\', \'Traktor/Jentera Bergerak Berat (Beroda) BTM melebihi 5000 kg\', NULL, \'2018-07-31 10:25:34\', \'2018-07-31 10:25:34\'),
(15, \'I\', \'Traktor/Jentera Bergerak Berat (Berantai) BTM melebihi 5000 kg\', NULL, \'2018-07-31 10:25:34\', \'2018-07-31 10:25:34\');');

        // ---- option_education_levels (8 rows) ----
        DB::unprepared('INSERT INTO `option_education_levels` VALUES
(1, \'Preschool (Belum Bersekolah atau Prasekolah)\', NULL, \'2018-07-31 14:18:19\', \'2018-07-31 14:18:19\'),
(2, \'Primary School (Sekolah Rendah)\', NULL, \'2018-07-31 14:18:19\', \'2018-07-31 14:18:19\'),
(3, \'Secondary School (Sekolah Menengah)\', NULL, \'2018-07-31 14:18:19\', \'2018-07-31 14:18:19\'),
(4, \'Certificate/Matriculation (Sijil/Matrikulasi)\', NULL, \'2018-07-31 14:18:19\', \'2018-07-31 14:18:19\'),
(5, \'Local Diploma (Diploma Dalam Negara)\', NULL, \'2018-07-31 14:18:19\', \'2018-07-31 14:18:19\'),
(6, \'International Diploma (Diploma Luar Negara)\', NULL, \'2018-07-31 14:18:19\', \'2018-07-31 14:18:19\'),
(7, \'Local Degree (Ijazah Dalam Negara)\', NULL, \'2018-07-31 14:18:19\', \'2018-07-31 14:18:19\'),
(8, \'International Degree (Ijazah Luar Negara)\', NULL, \'2018-07-31 14:18:19\', \'2018-07-31 14:18:19\');');

        // ---- option_genders (2 rows) ----
        DB::unprepared('INSERT INTO `option_genders` VALUES
(1, \'M\', \'Male\', NULL, \'2018-07-31 10:33:53\', \'2018-07-31 10:33:57\'),
(2, \'F\', \'Female\', NULL, \'2018-07-31 10:34:32\', \'2018-07-31 10:34:35\');');

        // ---- option_groups (7 rows) ----
        DB::unprepared('INSERT INTO `option_groups` VALUES
(1, \'Director\', NULL, \'2018-08-14 13:41:00\', \'2018-08-14 13:41:23\'),
(2, \'Head Of Department\', NULL, \'2018-08-14 13:41:04\', \'2018-08-14 13:41:26\'),
(3, \'Assistant Head Of Department\', NULL, \'2018-08-14 13:41:08\', \'2018-08-14 13:41:29\'),
(4, \'Supervisor\', NULL, \'2018-08-14 13:41:11\', \'2018-08-14 13:41:32\'),
(5, \'Leader\', NULL, \'2018-08-14 13:41:14\', \'2018-08-14 13:41:35\'),
(6, \'Assistant Leader\', NULL, \'2018-08-14 13:41:17\', \'2018-08-14 13:41:41\'),
(7, \'Normal\', NULL, \'2018-08-14 13:41:19\', \'2018-08-14 13:41:46\');');

        // ---- option_halfday_type (2 rows) ----
        DB::unprepared('INSERT INTO `option_halfday_type` VALUES
(1, \'AM\', \'Half Day Morning\', NULL, \'2023-07-14 10:34:41\', \'2023-07-14 10:34:41\', NULL),
(2, \'PM\', \'Half Day Afternoon\', NULL, \'2023-07-14 10:34:41\', \'2023-07-14 10:34:41\', NULL);');

        // ---- option_health_statuses (2 rows) ----
        DB::unprepared('INSERT INTO `option_health_statuses` VALUES
(1, \'Healthy\', NULL, \'2018-07-31 14:18:19\', \'2018-07-31 14:18:19\'),
(2, \'Disabled Person\', NULL, \'2018-07-31 14:18:19\', \'2018-07-31 14:18:19\');');

        // ---- option_infractions (3 rows) ----
        DB::unprepared('INSERT INTO `option_infractions` VALUES
(1, \'Minor Infraction\', \'Kesalahan kecil\', \'2024-01-02 14:17:57\', NULL, NULL),
(2, \'Major Infraction\', \'Kesalahan besar\', \'2024-01-02 14:17:57\', NULL, NULL),
(3, \'Maximum Infraction\', \'Kesalahan berat\', \'2024-01-02 14:17:57\', NULL, NULL);');

        // ---- option_leave_statuses (4 rows) ----
        DB::unprepared('INSERT INTO `option_leave_statuses` VALUES
(3, \'C\', \'Cancelled\', NULL, \'2018-09-14 17:03:19\', \'2018-09-14 17:03:19\'),
(4, \'R\', \'Rejected\', NULL, \'2018-09-15 11:00:05\', \'2018-09-15 11:00:10\'),
(5, \'A\', \'Approved\', NULL, \'2023-07-14 17:57:11\', \'2023-07-14 17:57:11\'),
(6, \'W\', \'Waived\', NULL, \'2023-07-14 17:57:11\', \'2023-07-14 17:57:11\');');

    }
}
