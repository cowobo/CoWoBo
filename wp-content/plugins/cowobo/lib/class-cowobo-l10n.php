<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * L10n
 *
 * Manual localization
 */
class CoWoBo_Localization
{

    public function __construct() {
        global $cowobo, $lang;

        $this->set_langnames();
        //Update language session
        if ( $lang = $cowobo->query->lang ) $this->set_lang_cookie ( $lang );
        else $lang = $this->get_lang_cookie();
    }

    /**
     * Return translated content if available
     */
    public function the_content($postid) {
        global $lang;
        if ($translated = get_post_meta($postid, 'content-'.$lang, true)) {
            return $translated;
        }
        return get_the_content($postid);
    }

    /**
     * Return translated title if available
     */
    public function the_title($postid) {
        global $lang;
        if ($translated = get_post_meta($postid, 'title-'.$lang, true)) {
            return $translated;
        }
        return get_the_title($postid);
    }

    /**
     * Add translated versions of post as custom fields
     */
    public function correct_translation() {
        global $lang, $post;
        update_post_meta($post->ID, 'title-'.$lang, $_POST['title-'.$lang]);
        update_post_meta($post->ID, 'content-'.$lang, $_POST['content-'.$lang]);
        return $notices;
    }

    /**
     * Set language cookie using the cookie duration for the remembered login cookie
     *
     * @param string $lang
     */
    public function set_lang_cookie ( $lang = '' ) {
        global $cowobo;

        if ( empty ( $lang ) ) return;
        $cowobo->query->set_cookie( 'cowobo_lang', $lang );

    }

    public function get_lang_cookie() {
        global $cowobo;
        return $cowobo->query->get_cookie('cowobo_lang');
    }

    private function set_langnames() {
        global $langnames;
        //store different translating messages
        $langnames = array(
            'en' => array('English', 'Welcome to Coders Without Borders', 'Translating page..'),
            'ar' => array('دي', 'مرحبا بكم في المبرمجون بلا حدود', 'متابعة باللغة العربية', '..ترجمة الصفحة'),
            'ca' => array('Català', 'Benvingut als Codificadors Sense Fronteres', ' Traduint pàgina..'),
            'cs' => array('Ceské', 'Vítejte na programátory bez hranic', "Překlady stránku .."),
            'da' => array('Dansk', 'Velkommen til Programmører Uden Grænser', 'Oversætter siden ..'),
            'de' => array('Deutsch', 'Welkom bei Programmierer ohne Grenzen', 'Seite Verarbeitung ..'),
            'el' => array('Ελληνική', 'Καλώς ήλθατε στο Coders Χωρίς Σύνορα', 'Μεταφράζοντας σελίδα ..'),
            'es' => array('Españoles', 'Bienvenido a Codificadores Sin Fronteras', ' Traduciendo página .. '),
            'fa' => array('فارسی', 'به برنامه نویسان بدون مرز خوش آمدید', 'صفحه ترجمه ..'),
            'fi' => array('Suomalainen', 'Tervetuloa Ohjelmoijat Ilman Rajoja', "Käännetään sivua .."),
            'fr' => array('Français', 'Bienvenue à Codeurs Sans Frontières', ' Page traduire .. '),
            'id' => array('Indonesia', 'Selamat Datang programmer tanpa batas', 'Menerjemahkan halaman ..'),
            'it' => array('Italiano', 'Benvenuto a Coders Senza Frontiere', 'Tradurre la pagina'),
            'iw' => array('Hebrew', 'ברוכים באים למקודדים ללא גבולות', 'דף תרגום ..'),
            'ja' => array('日本', '国境なきコーダーへようこそ', '日本語で継続', 'ページを翻訳する..'),
            'hu' => array('Magyar', 'Üdvözöljük a programozóknak Határok Nélkül', 'Fordítás oldal ..'),
            'hr' => array('Hrvatskom', 'Dobrodošli Programera Bez Granica', ' Prevođenje stranica .. '),
            'lt' => array('Lietuvos', 'Sveiki atvykę į Programuotojams be Sienų ', 'ulkojot lapu ..'),
            'no' => array('Norsk', 'Velkommen til Programmerere Uten Grenser', ' Oversett siden ..'),
            'pl' => array('Polish', 'Witamy Programistów Bez Granic', 'Przełożenie stronę ..'),
            'pt' => array('Português', 'Bem-vindo ao Coders Sem Fronteiras', ' Página Traduzindo ..'),
            'nl' => array('Nederlands', 'Welkom bij Codeurs Zonder Grenzen', 'Pagina wordt vertaald..'),
            'ro' => array('Român', 'Bine ați venit la Programatori Fără Frontiere ', 'Traducerea pagina ..'),
            'ru' => array('Русский', 'Добро пожаловать в Coders без границ', "Перевод страницы .."),
            'sk' => array('Slovak', 'Welcome to Coders Without Borders', 'Prevod stránky ..'),
            'sl' => array('Slovenskega', 'Dobrodošli na Kodiranje Brez Meja', 'Translating page..'),
            'sr' => array('Српске', 'Добродошли у Цодерса без граница', ' Превођење страна .. '),
            'sv' => array('Svenska', 'Välkommen till Coders Utan Gränser', "Översättning sida .."),
            'th' => array('ไทย', 'ยินดีต้อนรับสู่โปรแกรมเมอร์ไร้พรมแดน', 'หน้าแปล .. '),
            'tr' => array('Türk', 'Sınır Tanımayan Coders hoşgeldiniz', 'Tercüme sayfası .. '),
            'uk' => array('Український', 'Ласкаво просимо в кодери без кордонів', 'Переклад сторінці ..'),
            'vi' => array('Việt Nam', 'Chào mừng bạn đến với các lập trình viên không biên giới', 'trang Dịch ..'),
            'vko' => array('한국어', '국경을 초월한 코더에 오신 것을 환영합니다', '한국어로 계속', '번역 페이지를 ..'),
            'zh-CN' => array('中国', '欢迎到编码器无国界', '继续在中国', '网页翻译。'),
        );
    }

}