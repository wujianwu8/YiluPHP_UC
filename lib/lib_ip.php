<?php
/*
 * IP库（17monipdb.datx）
 * YiluPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/10/21
 * Time: 20:33
 */

class lib_ip
{
    private static $ip     = NULL;
    private static $fp     = NULL;
    private static $offset = NULL;
    private static $index  = NULL;

    /**
     * @name 根据语言获取国家（地区）的编码、区号和名称
     * @desc
     * @return array
     */
    public static function getByLang($lang)
    {
        $all = self::getCodeJson();
        $res = [];
        foreach($all as $item){
            if(!empty($item['lang'])) {
                foreach ($item['lang'] as $value) {
                    if (strtolower($value) == strtolower($lang)) {
                        $res[] = $item;
                    }
                }
            }
        }
        return $res;
    }

    /**
     * @name 获取所有国家（地区）的编码、区号和名称
     * @desc
     * @return array
     */
    public static function getCodeJson()
    {
        return json_decode(self::$codeJson, true);
//        $code = array_column($data, 'code_number');
//        asort($code);
//        array_multisort($data, SORT_ASC, $code );
//        echo json_encode($data);die;
//        return $data;
    }

    /**
     * @name 根据国家（地区）的编码获取其它相关信息（编码、区号和名称）
     * @desc
     * @param string $code 编码
     * @return array 国家（地区）的信息（编码、区号和名称）
     */
    public static function getByCountryAbbreviationCode($code)
    {
        $code = strtoupper($code);
        $codeJson = json_decode(self::$codeJson, true);
        if(isset($codeJson[$code])){
            return $codeJson[$code];
        }
        return null;
    }

    /**
     * @name 根据IP获取其它相关信息（编码、区号和名称）
     * @desc
     * @param string $ip IP地址
     * @return array 国家（地区）的信息（编码、区号和名称）
     */
    public static function findCode($ip)
    {
        $str = self::find($ip);
        $fun = function ($country){
            $codeJson = json_decode(self::$codeJson, true);
            foreach ($codeJson as $value){
                if(strpos($country, $value['name_cn'])===0 || strpos($value['name_cn'], $country)===0){
                    return $value;
                }
            }
            return null;
        };
        if(is_array($str)){
            $res = null;
            if (isset($str[1])){
                $res = $fun($str[1]);
            }
            if (empty($res) && isset($str[0])){
                $res = $fun($str[0]);
            }
            return $res;
        }
        else{
            return null;
        }
    }

    public static function find($ip)
    {
        if (empty($ip) === TRUE)
        {
            return 'N/A';
        }

        $nip   = gethostbyname($ip);
        $ipdot = explode('.', $nip);

        if ($ipdot[0] < 0 || $ipdot[0] > 255 || count($ipdot) !== 4)
        {
            return 'N/A';
        }

        if (self::$fp === NULL)
        {
            self::init();
        }

        $nip2 = pack('N', ip2long($nip));

        $tmp_offset = ((int)$ipdot[0] * 256 + (int)$ipdot[1]) * 4;
        $start      = unpack('Vlen', self::$index[$tmp_offset] . self::$index[$tmp_offset + 1] . self::$index[$tmp_offset + 2] . self::$index[$tmp_offset + 3]);

        $index_offset = $index_length = NULL;
        $max_comp_len = self::$offset['len'] - 262144 - 4;
        for ($start = $start['len'] * 9 + 262144; $start < $max_comp_len; $start += 9)
        {
            if (self::$index{$start} . self::$index{$start + 1} . self::$index{$start + 2} . self::$index{$start + 3} >= $nip2)
            {
                $index_offset = unpack('Vlen', self::$index{$start + 4} . self::$index{$start + 5} . self::$index{$start + 6} . "\x0");
                $index_length = unpack('nlen', self::$index{$start + 7} . self::$index{$start + 8});

                break;
            }
        }

        if ($index_offset === NULL)
        {
            return 'N/A';
        }

        fseek(self::$fp, self::$offset['len'] + $index_offset['len'] - 262144);

        return explode("\t", fread(self::$fp, $index_length['len']));
    }

    private static function init()
    {
        if (self::$fp === NULL)
        {
            self::$ip = new self();

            self::$fp = fopen(__DIR__ . '/lib_ip.datx', 'rb');
            if (self::$fp === FALSE)
            {
                throw new Exception('Invalid lib_ip.datx file!');
            }

            self::$offset = unpack('Nlen', fread(self::$fp, 4));
            if (self::$offset['len'] < 4)
            {
                throw new Exception('Invalid lib_ip.datx file!');
            }

            self::$index = fread(self::$fp, self::$offset['len'] - 4);
        }
    }

    public function __destruct()
    {
        if (self::$fp !== NULL)
        {
            fclose(self::$fp);

            self::$fp = NULL;
        }
    }

    /**
     * @name 智能获取地区列表
     * @desc 根据客户端IP、支持的语言判断用户所需地区，并把它排在前面
     * @return array
     */
    public static function getAutoAreaList()
    {
        $area_list = self::getCodeJson();
        $data = [];
        $ip = client_ip();
        if($ip) {
            $ip_code = self::findCode($ip);
        }
        else{
            $ip_code = null;
        }
        $have_short_code = [];
        $first_two_area = [];
        $correct_area = null;
        $current_lang = $GLOBALS['app']->current_lang();
        //获取客户端的语言
        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && $client_lang_str = $_SERVER['HTTP_ACCEPT_LANGUAGE']){
            $client_lang_str = preg_replace('/\s+/', '', $client_lang_str);
            $client_lang_str = preg_split('[;|,]', $client_lang_str);
            $index = 0;
            foreach ($client_lang_str as $item){
                $item = strtolower($item);
                if(empty($item) || strpos($item, 'q=')===0) {
                    continue;
                }
                $tmp = $GLOBALS['app']->lib_ip->getByLang($item);
                if($tmp){
                    foreach($tmp as $value) {
                        if($tmp && !in_array($value['short_code'], $have_short_code)) {
                            $have_short_code[] = $value['short_code'];
                            $item_data = [
                                'name' => isset($value['name_'.$current_lang])?$value['name_'.$current_lang]:$value['name_cn'],
                                'short_code' => $value['short_code'],
                                'code_number' => $value['code_number'],
                                'recommend' => 1,
                            ];
                            if($ip_code && $ip_code['short_code']==$item_data['short_code']){
                                $correct_area = $item_data;
                                continue;
                            }
                            if($index>1){
                                $data[] = $item_data;
                            }
                            else{
                                $first_two_area[] = $item_data;
                            }
                            $index++;
                            unset($item_data);
                        }
                    }
                }
                unset($tmp);
            }
            unset($client_lang_str);
        }
        if($first_two_area){
            $data = array_merge($first_two_area, $data);
        }
        if($correct_area){
            array_unshift($data, $correct_area);
        }
        $index = 0;
        foreach($area_list as $item){
            if($index>3 || !in_array($item['code_number'], $have_short_code)){
                $data[] = [
                    'name' => isset($item['name_'.$current_lang])?$item['name_'.$current_lang]:$item['name_cn'],
                    'short_code' => $item['short_code'],
                    'code_number' => $item['code_number'],
                ];
            }
            $index++;
        }
        unset($ip, $ip_code, $index, $area_list, $item, $have_short_code);

        return $data;
    }

    private static $codeJson = '{
    "CA": {
        "code_number": 1, 
        "name_cn": "加拿大", 
        "name_en": "Canada", 
        "short_code": "CA", 
        "lang": [
            "en-CA", 
            "fr-CA"
        ]
    }, 
    "US": {
        "code_number": 1, 
        "name_cn": "美国", 
        "name_en": "United States", 
        "short_code": "US", 
        "lang": [
            "en-US"
        ]
    }, 
    "RU": {
        "code_number": 7, 
        "name_cn": "俄罗斯", 
        "name_en": "Russia", 
        "short_code": "RU", 
        "lang": [
            "be", 
            "ru", 
            "ru-RU", 
            "tt-RU"
        ]
    }, 
    "KZ": {
        "code_number": 7, 
        "name_cn": "哈萨克斯坦", 
        "name_en": "Russia", 
        "short_code": "KZ", 
        "lang": [ ]
    }, 
    "EG": {
        "code_number": 20, 
        "name_cn": "埃及", 
        "name_en": "Egypt", 
        "short_code": "EG", 
        "lang": [
            "ar-EG"
        ]
    }, 
    "ZA": {
        "code_number": 27, 
        "name_cn": "南非", 
        "name_en": "South Africa", 
        "short_code": "ZA", 
        "lang": [
            "af-ZA"
        ]
    }, 
    "GR": {
        "code_number": 30, 
        "name_cn": "希腊", 
        "name_en": "Greece", 
        "short_code": "GR", 
        "lang": [
            "el", 
            "el-GR"
        ]
    }, 
    "NL": {
        "code_number": 31, 
        "name_cn": "荷兰", 
        "name_en": "Netherlands", 
        "short_code": "NL", 
        "lang": [
            "af", 
            "nl", 
            "nl-NL"
        ]
    }, 
    "BE": {
        "code_number": 32, 
        "name_cn": "比利时", 
        "name_en": "Belgium", 
        "short_code": "BE", 
        "lang": [
            "nl-BE", 
            "fr-BE"
        ]
    }, 
    "FR": {
        "code_number": 33, 
        "name_cn": "法国", 
        "name_en": "France", 
        "short_code": "FR", 
        "lang": [
            "fr", 
            "fr-FR"
        ]
    }, 
    "ES": {
        "code_number": 34, 
        "name_cn": "西班牙", 
        "name_en": "Spain", 
        "short_code": "ES", 
        "lang": [
            "es", 
            "es-ES"
        ]
    }, 
    "IT": {
        "code_number": 39, 
        "name_cn": "意大利", 
        "name_en": "Italy", 
        "short_code": "IT", 
        "lang": [
            "it", 
            "it-IT"
        ]
    }, 
    "RO": {
        "code_number": 40, 
        "name_cn": "罗马尼亚", 
        "name_en": "Romania", 
        "short_code": "RO", 
        "lang": [
            "ro", 
            "ro-RO"
        ]
    }, 
    "CH": {
        "code_number": 41, 
        "name_cn": "瑞士", 
        "name_en": "Switzerland", 
        "short_code": "CH", 
        "lang": [
            "fr-CH", 
            "de-CH", 
            "it-CH"
        ]
    }, 
    "AT": {
        "code_number": 43, 
        "name_cn": "奥地利", 
        "name_en": "Austria", 
        "short_code": "AT", 
        "lang": [
            "de-AT"
        ]
    }, 
    "GB": {
        "code_number": 44, 
        "name_cn": "英国", 
        "name_en": "United Kingdom", 
        "short_code": "GB", 
        "lang": [
            "en"
        ]
    }, 
    "DK": {
        "code_number": 45, 
        "name_cn": "丹麦", 
        "name_en": "Denmark", 
        "short_code": "DK", 
        "lang": [
            "da", 
            "da-DK"
        ]
    }, 
    "SE": {
        "code_number": 46, 
        "name_cn": "瑞典", 
        "name_en": "Sweden", 
        "short_code": "SE", 
        "lang": [
            "sv", 
            "sv-FI", 
            "sv-SE"
        ]
    }, 
    "NO": {
        "code_number": 47, 
        "name_cn": "挪威", 
        "name_en": "Norway", 
        "short_code": "NO", 
        "lang": [
            "no", 
            "nb-NO", 
            "nn-NO"
        ]
    }, 
    "PL": {
        "code_number": 48, 
        "name_cn": "波兰", 
        "name_en": "Poland", 
        "short_code": "PL", 
        "lang": [
            "pl", 
            "pl-PL"
        ]
    }, 
    "DE": {
        "code_number": 49, 
        "name_cn": "德国", 
        "name_en": "Germany", 
        "short_code": "DE", 
        "lang": [
            "de", 
            "de-DE"
        ]
    }, 
    "PE": {
        "code_number": 51, 
        "name_cn": "秘鲁", 
        "name_en": "Peru", 
        "short_code": "PE", 
        "lang": [
            "es-PE"
        ]
    }, 
    "MX": {
        "code_number": 52, 
        "name_cn": "墨西哥", 
        "name_en": "Mexico", 
        "short_code": "MX", 
        "lang": [
            "es-MX"
        ]
    }, 
    "CU": {
        "code_number": 53, 
        "name_cn": "古巴", 
        "name_en": "Cuba", 
        "short_code": "CU", 
        "lang": [ ]
    }, 
    "AR": {
        "code_number": 54, 
        "name_cn": "阿根廷", 
        "name_en": "Argentina", 
        "short_code": "AR", 
        "lang": [
            "es-AR"
        ]
    }, 
    "BR": {
        "code_number": 55, 
        "name_cn": "巴西", 
        "name_en": "Brazil", 
        "short_code": "BR", 
        "lang": [
            "pt-BR"
        ]
    }, 
    "CL": {
        "code_number": 56, 
        "name_cn": "智利", 
        "name_en": "Chile", 
        "short_code": "CL", 
        "lang": [
            "es-CL"
        ]
    }, 
    "CO": {
        "code_number": 57, 
        "name_cn": "哥伦比亚", 
        "name_en": "Colombia", 
        "short_code": "CO", 
        "lang": [
            "es-CO"
        ]
    }, 
    "VE": {
        "code_number": 58, 
        "name_cn": "委内瑞拉", 
        "name_en": "Venezuela", 
        "short_code": "VE", 
        "lang": [
            "es-VE"
        ]
    }, 
    "MY": {
        "code_number": 60, 
        "name_cn": "马来西亚", 
        "name_en": "Malaysia", 
        "short_code": "MY", 
        "lang": [
            "ms-MY"
        ]
    }, 
    "AU": {
        "code_number": 61, 
        "name_cn": "澳大利亚", 
        "name_en": "Australia", 
        "short_code": "AU", 
        "lang": [
            "de-AT"
        ]
    }, 
    "ID": {
        "code_number": 62, 
        "name_cn": "印度尼西亚", 
        "name_en": "Indonesia", 
        "short_code": "ID", 
        "lang": [
            "id", 
            "id-ID"
        ]
    }, 
    "PH": {
        "code_number": 63, 
        "name_cn": "菲律宾", 
        "name_en": "Philippines", 
        "short_code": "PH", 
        "lang": [
            "en-PH"
        ]
    }, 
    "NZ": {
        "code_number": 64, 
        "name_cn": "新西兰", 
        "name_en": "New Zealand", 
        "short_code": "NZ", 
        "lang": [
            "en-NZ"
        ]
    }, 
    "SG": {
        "code_number": 65, 
        "name_cn": "新加坡", 
        "name_en": "Singapore", 
        "short_code": "SG", 
        "lang": [
            "zh-SG"
        ]
    }, 
    "TH": {
        "code_number": 66, 
        "name_cn": "泰国", 
        "name_en": "Thailand", 
        "short_code": "TH", 
        "lang": [
            "th", 
            "th-TH"
        ]
    }, 
    "JP": {
        "code_number": 81, 
        "name_cn": "日本", 
        "name_en": "Japan", 
        "short_code": "JP", 
        "lang": [
            "ja", 
            "ja-JP"
        ]
    }, 
    "KR": {
        "code_number": 82, 
        "name_cn": "韩国", 
        "name_en": "South Korea", 
        "short_code": "KR", 
        "lang": [
            "ko", 
            "ko-KR"
        ]
    }, 
    "VN": {
        "code_number": 84, 
        "name_cn": "越南", 
        "name_en": "Vietnam", 
        "short_code": "VN", 
        "lang": [
            "vi", 
            "vi-VN"
        ]
    }, 
    "CN": {
        "code_number": 86, 
        "name_cn": "中国", 
        "name_en": "China", 
        "short_code": "CN", 
        "lang": [
            "zh", 
            "zh-CN"
        ]
    }, 
    "TR": {
        "code_number": 90, 
        "name_cn": "土耳其", 
        "name_en": "Turkey", 
        "short_code": "TR", 
        "lang": [
            "tr", 
            "tr-TR"
        ]
    }, 
    "IN": {
        "code_number": 91, 
        "name_cn": "印度", 
        "name_en": "India", 
        "short_code": "IN", 
        "lang": [
            "gu-IN", 
            "hi", 
            "hi-IN", 
            "kn-IN", 
            "kok-IN", 
            "mr-IN", 
            "pa-IN", 
            "sa-IN", 
            "ta-IN", 
            "te-IN", 
            "", 
            "", 
            "", 
            ""
        ]
    }, 
    "PK": {
        "code_number": 92, 
        "name_cn": "巴基斯坦", 
        "name_en": "Pakistan", 
        "short_code": "PK", 
        "lang": [
            "ur-PK"
        ]
    }, 
    "AF": {
        "code_number": 93, 
        "name_cn": "阿富汗", 
        "name_en": "Afghanistan", 
        "short_code": "AF", 
        "lang": [ ]
    }, 
    "LK": {
        "code_number": 94, 
        "name_cn": "斯里兰卡", 
        "name_en": "Sri Lanka", 
        "short_code": "LK", 
        "lang": [ ]
    }, 
    "MM": {
        "code_number": 95, 
        "name_cn": "缅甸", 
        "name_en": "Myanmar", 
        "short_code": "MM", 
        "lang": [ ]
    }, 
    "IR": {
        "code_number": 98, 
        "name_cn": "伊朗", 
        "name_en": "Iran", 
        "short_code": "IR", 
        "lang": [
            "fa-IR"
        ]
    }, 
    "MA": {
        "code_number": 210, 
        "name_cn": "摩洛哥", 
        "name_en": "Morocco", 
        "short_code": "MA", 
        "lang": [
            "ar-MA"
        ]
    }, 
    "DZ": {
        "code_number": 213, 
        "name_cn": "阿尔及利亚", 
        "name_en": "Algeria", 
        "short_code": "DZ", 
        "lang": [
            "ar-DZ"
        ]
    }, 
    "TN": {
        "code_number": 216, 
        "name_cn": "突尼斯", 
        "name_en": "Tunisia", 
        "short_code": "TN", 
        "lang": [ ]
    }, 
    "LY": {
        "code_number": 218, 
        "name_cn": "利比亚", 
        "name_en": "Libya", 
        "short_code": "LY", 
        "lang": [
            "ar-LY"
        ]
    }, 
    "GM": {
        "code_number": 220, 
        "name_cn": "冈比亚", 
        "name_en": "Gambia", 
        "short_code": "GM", 
        "lang": [ ]
    }, 
    "SN": {
        "code_number": 221, 
        "name_cn": "塞内加尔", 
        "name_en": "Senegal", 
        "short_code": "SN", 
        "lang": [ ]
    }, 
    "SM": {
        "code_number": 223, 
        "name_cn": "圣马力诺", 
        "name_en": "Mali", 
        "short_code": "SM", 
        "lang": [ ]
    }, 
    "ML": {
        "code_number": 223, 
        "name_cn": "马里", 
        "name_en": "Mali", 
        "short_code": "ML", 
        "lang": [ ]
    }, 
    "GN": {
        "code_number": 224, 
        "name_cn": "几内亚", 
        "name_en": "Guinea", 
        "short_code": "GN", 
        "lang": [ ]
    }, 
    "KT": {
        "code_number": 225, 
        "name_cn": "科特迪瓦共和国", 
        "name_en": "Cote d\'Ivoire", 
        "short_code": "KT", 
        "lang": [ ]
    }, 
    "BF": {
        "code_number": 226, 
        "name_cn": "布基纳法索", 
        "name_en": "Burkina Faso", 
        "short_code": "BF", 
        "lang": [ ]
    }, 
    "NE": {
        "code_number": 227, 
        "name_cn": "尼日尔", 
        "name_en": "Niger", 
        "short_code": "NE", 
        "lang": [ ]
    }, 
    "TG": {
        "code_number": 228, 
        "name_cn": "多哥", 
        "name_en": "Togo", 
        "short_code": "TG", 
        "lang": [ ]
    }, 
    "BJ": {
        "code_number": 229, 
        "name_cn": "贝宁", 
        "name_en": "Benin", 
        "short_code": "BJ", 
        "lang": [ ]
    }, 
    "MU": {
        "code_number": 230, 
        "name_cn": "毛里求斯", 
        "name_en": "Mauritius", 
        "short_code": "MU", 
        "lang": [ ]
    }, 
    "LR": {
        "code_number": 231, 
        "name_cn": "利比里亚", 
        "name_en": "Liberia", 
        "short_code": "LR", 
        "lang": [ ]
    }, 
    "SL": {
        "code_number": 232, 
        "name_cn": "塞拉利昂", 
        "name_en": "Sierra Leone", 
        "short_code": "SL", 
        "lang": [ ]
    }, 
    "GH": {
        "code_number": 233, 
        "name_cn": "加纳", 
        "name_en": "Ghana", 
        "short_code": "GH", 
        "lang": [ ]
    }, 
    "NG": {
        "code_number": 234, 
        "name_cn": "尼日利亚", 
        "name_en": "Nigeria", 
        "short_code": "NG", 
        "lang": [ ]
    }, 
    "TD": {
        "code_number": 235, 
        "name_cn": "乍得", 
        "name_en": "Chad", 
        "short_code": "TD", 
        "lang": [ ]
    }, 
    "CF": {
        "code_number": 236, 
        "name_cn": "中非共和国", 
        "name_en": "Central African Republic", 
        "short_code": "CF", 
        "lang": [ ]
    }, 
    "CM": {
        "code_number": 237, 
        "name_cn": "喀麦隆", 
        "name_en": "Cameroon", 
        "short_code": "CM", 
        "lang": [ ]
    }, 
    "ST": {
        "code_number": 239, 
        "name_cn": "圣多美和普林西比", 
        "name_en": "Sao Tome & Principe", 
        "short_code": "ST", 
        "lang": [ ]
    }, 
    "GA": {
        "code_number": 241, 
        "name_cn": "加蓬", 
        "name_en": "Gabon", 
        "short_code": "GA", 
        "lang": [ ]
    }, 
    "CG": {
        "code_number": 242, 
        "name_cn": "刚果", 
        "name_en": "Congo", 
        "short_code": "CG", 
        "lang": [ ]
    }, 
    "ZR": {
        "code_number": 243, 
        "name_cn": "扎伊尔", 
        "name_en": "Congo", 
        "short_code": "ZR", 
        "lang": [ ]
    }, 
    "AO": {
        "code_number": 244, 
        "name_cn": "安哥拉", 
        "name_en": "Angola", 
        "short_code": "AO", 
        "lang": [ ]
    }, 
    "SC": {
        "code_number": 248, 
        "name_cn": "塞舌尔", 
        "name_en": "Seychelles", 
        "short_code": "SC", 
        "lang": [ ]
    }, 
    "SD": {
        "code_number": 249, 
        "name_cn": "苏丹", 
        "name_en": "Sudan", 
        "short_code": "SD", 
        "lang": [ ]
    }, 
    "ET": {
        "code_number": 251, 
        "name_cn": "埃塞俄比亚", 
        "name_en": "Ethiopia", 
        "short_code": "ET", 
        "lang": [ ]
    }, 
    "SO": {
        "code_number": 252, 
        "name_cn": "索马里", 
        "name_en": "Somalia", 
        "short_code": "SO", 
        "lang": [ ]
    }, 
    "DJ": {
        "code_number": 253, 
        "name_cn": "吉布提", 
        "name_en": "Djibouti", 
        "short_code": "DJ", 
        "lang": [ ]
    }, 
    "KE": {
        "code_number": 254, 
        "name_cn": "肯尼亚", 
        "name_en": "Kenya", 
        "short_code": "KE", 
        "lang": [
            "sw-KE"
        ]
    }, 
    "TZ": {
        "code_number": 255, 
        "name_cn": "坦桑尼亚", 
        "name_en": "Tanzania", 
        "short_code": "TZ", 
        "lang": [ ]
    }, 
    "UG": {
        "code_number": 256, 
        "name_cn": "乌干达", 
        "name_en": "Uganda", 
        "short_code": "UG", 
        "lang": [ ]
    }, 
    "BI": {
        "code_number": 257, 
        "name_cn": "布隆迪", 
        "name_en": "Burundi", 
        "short_code": "BI", 
        "lang": [ ]
    }, 
    "MZ": {
        "code_number": 258, 
        "name_cn": "莫桑比克", 
        "name_en": "Mozambique", 
        "short_code": "MZ", 
        "lang": [ ]
    }, 
    "ZM": {
        "code_number": 260, 
        "name_cn": "赞比亚", 
        "name_en": "Zambia", 
        "short_code": "ZM", 
        "lang": [ ]
    }, 
    "MG": {
        "code_number": 261, 
        "name_cn": "马达加斯加", 
        "name_en": "Madagascar", 
        "short_code": "MG", 
        "lang": [ ]
    }, 
    "ZW": {
        "code_number": 263, 
        "name_cn": "津巴布韦", 
        "name_en": "Zimbabwe", 
        "short_code": "ZW", 
        "lang": [
            "en-ZW"
        ]
    }, 
    "NA": {
        "code_number": 264, 
        "name_cn": "纳米比亚", 
        "name_en": "Namibia", 
        "short_code": "NA", 
        "lang": [ ]
    }, 
    "MW": {
        "code_number": 265, 
        "name_cn": "马拉维", 
        "name_en": "Malawi", 
        "short_code": "MW", 
        "lang": [ ]
    }, 
    "LS": {
        "code_number": 266, 
        "name_cn": "莱索托", 
        "name_en": "Lesotho", 
        "short_code": "LS", 
        "lang": [ ]
    }, 
    "BW": {
        "code_number": 267, 
        "name_cn": "博茨瓦纳", 
        "name_en": "Botswana", 
        "short_code": "BW", 
        "lang": [ ]
    }, 
    "SZ": {
        "code_number": 268, 
        "name_cn": "斯威士兰", 
        "name_en": "Swaziland", 
        "short_code": "SZ", 
        "lang": [ ]
    }, 
    "HU": {
        "code_number": 336, 
        "name_cn": "匈牙利", 
        "name_en": "Hungary", 
        "short_code": "HU", 
        "lang": [
            "hu", 
            "hu-HU"
        ]
    }, 
    "YU": {
        "code_number": 338, 
        "name_cn": "南斯拉夫", 
        "name_en": "Yugoslavia", 
        "short_code": "YU", 
        "lang": [ ]
    }, 
    "GI": {
        "code_number": 350, 
        "name_cn": "直布罗陀", 
        "name_en": "Gibraltar", 
        "short_code": "GI", 
        "lang": [ ]
    }, 
    "PT": {
        "code_number": 351, 
        "name_cn": "葡萄牙", 
        "name_en": "Portugal", 
        "short_code": "PT", 
        "lang": [
            "pt", 
            "pt-PT"
        ]
    }, 
    "LU": {
        "code_number": 352, 
        "name_cn": "卢森堡", 
        "name_en": "Luxembourg", 
        "short_code": "LU", 
        "lang": [
            "fr-LU", 
            "de-LU"
        ]
    }, 
    "IE": {
        "code_number": 353, 
        "name_cn": "爱尔兰", 
        "name_en": "Ireland", 
        "short_code": "IE", 
        "lang": [
            "en-IE"
        ]
    }, 
    "IS": {
        "code_number": 354, 
        "name_cn": "冰岛", 
        "name_en": "Iceland", 
        "short_code": "IS", 
        "lang": [
            "is", 
            "is-IS"
        ]
    }, 
    "AL": {
        "code_number": 355, 
        "name_cn": "阿尔巴尼亚", 
        "name_en": "Albania", 
        "short_code": "AL", 
        "lang": [
            "sq", 
            "sq-AL"
        ]
    }, 
    "MT": {
        "code_number": 356, 
        "name_cn": "马耳他", 
        "name_en": "Malta", 
        "short_code": "MT", 
        "lang": [ ]
    }, 
    "CY": {
        "code_number": 357, 
        "name_cn": "塞浦路斯", 
        "name_en": "Cyprus", 
        "short_code": "CY", 
        "lang": [ ]
    }, 
    "FI": {
        "code_number": 358, 
        "name_cn": "芬兰", 
        "name_en": "Finland", 
        "short_code": "FI", 
        "lang": [
            "sv-FI", 
            "fi", 
            "fi-FI"
        ]
    }, 
    "BG": {
        "code_number": 359, 
        "name_cn": "保加利亚", 
        "name_en": "Bulgaria", 
        "short_code": "BG", 
        "lang": [
            "bg", 
            "bg-BG"
        ]
    }, 
    "LT": {
        "code_number": 370, 
        "name_cn": "立陶宛", 
        "name_en": "Lithuania", 
        "short_code": "LT", 
        "lang": [
            "lt", 
            "lt-LT"
        ]
    }, 
    "LV": {
        "code_number": 371, 
        "name_cn": "拉脱维亚", 
        "name_en": "Latvia", 
        "short_code": "LV", 
        "lang": [
            "lv", 
            "lv-LV"
        ]
    }, 
    "EE": {
        "code_number": 372, 
        "name_cn": "爱沙尼亚", 
        "name_en": "Estonia", 
        "short_code": "EE", 
        "lang": [
            "et", 
            "et-EE"
        ]
    }, 
    "MD": {
        "code_number": 373, 
        "name_cn": "摩尔多瓦", 
        "name_en": "Moldova", 
        "short_code": "MD", 
        "lang": [ ]
    }, 
    "AM": {
        "code_number": 374, 
        "name_cn": "亚美尼亚", 
        "name_en": "Armenia", 
        "short_code": "AM", 
        "lang": [
            "hy", 
            "hy-AM"
        ]
    }, 
    "BY": {
        "code_number": 375, 
        "name_cn": "白俄罗斯", 
        "name_en": "Belarus", 
        "short_code": "BY", 
        "lang": [
            "be", 
            "be-BY"
        ]
    }, 
    "AD": {
        "code_number": 376, 
        "name_cn": "安道尔共和国", 
        "name_en": "Andorra", 
        "short_code": "AD", 
        "lang": [ ]
    }, 
    "MC": {
        "code_number": 377, 
        "name_cn": "摩纳哥", 
        "name_en": "Monaco", 
        "short_code": "MC", 
        "lang": [
            "fr-MC"
        ]
    }, 
    "UA": {
        "code_number": 380, 
        "name_cn": "乌克兰", 
        "name_en": "Ukraine", 
        "short_code": "UA", 
        "lang": [
            "uk", 
            "uk-UA"
        ]
    }, 
    "SI": {
        "code_number": 386, 
        "name_cn": "斯洛文尼亚", 
        "name_en": "Slovenia", 
        "short_code": "SI", 
        "lang": [
            "sl", 
            "sl-SI"
        ]
    }, 
    "CZ": {
        "code_number": 420, 
        "name_cn": "捷克", 
        "name_en": "Czech Republic", 
        "short_code": "CZ", 
        "lang": [
            "cs", 
            "cs-CZ"
        ]
    }, 
    "SK": {
        "code_number": 421, 
        "name_cn": "斯洛伐克", 
        "name_en": "Slovakia", 
        "short_code": "SK", 
        "lang": [
            "sk", 
            "sk-SK"
        ]
    }, 
    "BM": {
        "code_number": 441, 
        "name_cn": "百慕大群岛", 
        "name_en": "Bermuda Is.", 
        "short_code": "BM", 
        "lang": [ ]
    }, 
    "BZ": {
        "code_number": 501, 
        "name_cn": "伯利兹", 
        "name_en": "Belize", 
        "short_code": "BZ", 
        "lang": [
            "en-BZ"
        ]
    }, 
    "GT": {
        "code_number": 502, 
        "name_cn": "危地马拉", 
        "name_en": "Guatemala", 
        "short_code": "GT", 
        "lang": [
            "es-GT"
        ]
    }, 
    "SV": {
        "code_number": 503, 
        "name_cn": "萨尔瓦多", 
        "name_en": "El Salvador", 
        "short_code": "SV", 
        "lang": [
            "es-SV"
        ]
    }, 
    "HN": {
        "code_number": 504, 
        "name_cn": "洪都拉斯", 
        "name_en": "Honduras", 
        "short_code": "HN", 
        "lang": [
            "es-HN"
        ]
    }, 
    "NI": {
        "code_number": 505, 
        "name_cn": "尼加拉瓜", 
        "name_en": "Nicaragua", 
        "short_code": "NI", 
        "lang": [
            "es-NI"
        ]
    }, 
    "CR": {
        "code_number": 506, 
        "name_cn": "哥斯达黎加", 
        "name_en": "Costa Rica", 
        "short_code": "CR", 
        "lang": [
            "es-CR"
        ]
    }, 
    "PA": {
        "code_number": 507, 
        "name_cn": "巴拿马", 
        "name_en": "Panama", 
        "short_code": "PA", 
        "lang": [
            "es-PA"
        ]
    }, 
    "HT": {
        "code_number": 509, 
        "name_cn": "海地", 
        "name_en": "Haiti", 
        "short_code": "HT", 
        "lang": [ ]
    }, 
    "BO": {
        "code_number": 591, 
        "name_cn": "玻利维亚", 
        "name_en": "Bolivia", 
        "short_code": "BO", 
        "lang": [
            "es-BO"
        ]
    }, 
    "GY": {
        "code_number": 592, 
        "name_cn": "圭亚那", 
        "name_en": "Guyana", 
        "short_code": "GY", 
        "lang": [ ]
    }, 
    "EC": {
        "code_number": 593, 
        "name_cn": "厄瓜多尔", 
        "name_en": "Ecuador", 
        "short_code": "EC", 
        "lang": [
            "es-EC"
        ]
    }, 
    "GF": {
        "code_number": 594, 
        "name_cn": "法属圭亚那", 
        "name_en": "French Guiana", 
        "short_code": "GF", 
        "lang": [ ]
    }, 
    "PY": {
        "code_number": 595, 
        "name_cn": "巴拉圭", 
        "name_en": "Paraguay", 
        "short_code": "PY", 
        "lang": [
            "es-PY"
        ]
    }, 
    "SR": {
        "code_number": 597, 
        "name_cn": "苏里南", 
        "name_en": "Suriname", 
        "short_code": "SR", 
        "lang": [ ]
    }, 
    "UY": {
        "code_number": 598, 
        "name_cn": "乌拉圭", 
        "name_en": "Uruguay", 
        "short_code": "UY", 
        "lang": [
            "es-UY"
        ]
    }, 
    "GU": {
        "code_number": 671, 
        "name_cn": "关岛", 
        "name_en": "Guam", 
        "short_code": "GU", 
        "lang": [ ]
    }, 
    "BN": {
        "code_number": 673, 
        "name_cn": "文莱", 
        "name_en": "Brunei Darussalam", 
        "short_code": "BN", 
        "lang": [ ]
    }, 
    "NR": {
        "code_number": 674, 
        "name_cn": "瑙鲁", 
        "name_en": "Nauru", 
        "short_code": "NR", 
        "lang": [ ]
    }, 
    "PG": {
        "code_number": 675, 
        "name_cn": "巴布亚新几内亚", 
        "name_en": "Papua New Guinea", 
        "short_code": "PG", 
        "lang": [ ]
    }, 
    "TO": {
        "code_number": 676, 
        "name_cn": "汤加", 
        "name_en": "Tonga", 
        "short_code": "TO", 
        "lang": [ ]
    }, 
    "SB": {
        "code_number": 677, 
        "name_cn": "所罗门群岛", 
        "name_en": "Solomon Islands", 
        "short_code": "SB", 
        "lang": [ ]
    }, 
    "FJ": {
        "code_number": 679, 
        "name_cn": "斐济", 
        "name_en": "Fiji", 
        "short_code": "FJ", 
        "lang": [ ]
    }, 
    "CK": {
        "code_number": 682, 
        "name_cn": "库克群岛", 
        "name_en": "Cook Islands", 
        "short_code": "CK", 
        "lang": [ ]
    }, 
    "PF": {
        "code_number": 689, 
        "name_cn": "法属玻利尼西亚", 
        "name_en": "French Polynesia", 
        "short_code": "PF", 
        "lang": [ ]
    }, 
    "KP": {
        "code_number": 850, 
        "name_cn": "朝鲜", 
        "name_en": "North Korea", 
        "short_code": "KP", 
        "lang": [ ]
    }, 
    "HK": {
        "code_number": 852, 
        "name_cn": "中国香港特别行政区", 
        "name_en": "Hong Kong China", 
        "short_code": "HK", 
        "lang": [
            "zh-HK"
        ]
    }, 
    "MO": {
        "code_number": 853, 
        "name_cn": "中国澳门", 
        "name_en": "Macao China", 
        "short_code": "MO", 
        "lang": [
            "zh-MO"
        ]
    }, 
    "KH": {
        "code_number": 855, 
        "name_cn": "柬埔寨", 
        "name_en": "Cambodia", 
        "short_code": "KH", 
        "lang": [ ]
    }, 
    "LA": {
        "code_number": 856, 
        "name_cn": "老挝", 
        "name_en": "Lao", 
        "short_code": "LA", 
        "lang": [ ]
    }, 
    "BD": {
        "code_number": 880, 
        "name_cn": "孟加拉国", 
        "name_en": "Bangladesh", 
        "short_code": "BD", 
        "lang": [ ]
    }, 
    "TW": {
        "code_number": 886, 
        "name_cn": "中国台湾", 
        "name_en": "Taiwan China", 
        "short_code": "TW", 
        "lang": [
            "zh-TW"
        ]
    }, 
    "MV": {
        "code_number": 960, 
        "name_cn": "马尔代夫", 
        "name_en": "Maldives", 
        "short_code": "MV", 
        "lang": [
            "div-MV"
        ]
    }, 
    "LB": {
        "code_number": 961, 
        "name_cn": "黎巴嫩", 
        "name_en": "Lebanon", 
        "short_code": "LB", 
        "lang": [
            "ar-LB"
        ]
    }, 
    "JO": {
        "code_number": 962, 
        "name_cn": "约旦", 
        "name_en": "Jordan", 
        "short_code": "JO", 
        "lang": [
            "ar-JO"
        ]
    }, 
    "SY": {
        "code_number": 963, 
        "name_cn": "叙利亚", 
        "name_en": "Syria", 
        "short_code": "SY", 
        "lang": [
            "syr", 
            "syr-SY"
        ]
    }, 
    "IQ": {
        "code_number": 964, 
        "name_cn": "伊拉克", 
        "name_en": "Iraq", 
        "short_code": "IQ", 
        "lang": [
            "ar-IQ"
        ]
    }, 
    "KW": {
        "code_number": 965, 
        "name_cn": "科威特", 
        "name_en": "Kuwait", 
        "short_code": "KW", 
        "lang": [
            "ar-KW"
        ]
    }, 
    "SA": {
        "code_number": 966, 
        "name_cn": "沙特阿拉伯", 
        "name_en": "Saudi Arabia", 
        "short_code": "SA", 
        "lang": [
            "ar-SA"
        ]
    }, 
    "YE": {
        "code_number": 967, 
        "name_cn": "也门", 
        "name_en": "Yemen", 
        "short_code": "YE", 
        "lang": [
            "ar-YE"
        ]
    }, 
    "OM": {
        "code_number": 968, 
        "name_cn": "阿曼", 
        "name_en": "Oman", 
        "short_code": "OM", 
        "lang": [
            "ar-OM"
        ]
    }, 
    "BL": {
        "code_number": 970, 
        "name_cn": "巴勒斯坦", 
        "name_en": "Palestine", 
        "short_code": "BL", 
        "lang": [ ]
    }, 
    "AE": {
        "code_number": 971, 
        "name_cn": "阿拉伯联合酋长国", 
        "name_en": "United Arab Emirates", 
        "short_code": "AE", 
        "lang": [
            "ar-AE"
        ]
    }, 
    "IL": {
        "code_number": 972, 
        "name_cn": "以色列", 
        "name_en": "Israel", 
        "short_code": "IL", 
        "lang": [
            "he-IL"
        ]
    }, 
    "BH": {
        "code_number": 973, 
        "name_cn": "巴林", 
        "name_en": "Bahrain", 
        "short_code": "BH", 
        "lang": [
            "ar-BH"
        ]
    }, 
    "QA": {
        "code_number": 974, 
        "name_cn": "卡塔尔", 
        "name_en": "Qatar", 
        "short_code": "QA", 
        "lang": [
            "ar-QA"
        ]
    }, 
    "MN": {
        "code_number": 976, 
        "name_cn": "蒙古", 
        "name_en": "Mongolia", 
        "short_code": "MN", 
        "lang": [
            "mn", 
            "mn-MN"
        ]
    }, 
    "NP": {
        "code_number": 977, 
        "name_cn": "尼泊尔", 
        "name_en": "Nepal", 
        "short_code": "NP", 
        "lang": [ ]
    }, 
    "TJ": {
        "code_number": 992, 
        "name_cn": "塔吉克斯坦", 
        "name_en": "Tajikistan", 
        "short_code": "TJ", 
        "lang": [ ]
    }, 
    "TM": {
        "code_number": 993, 
        "name_cn": "土库曼斯坦", 
        "name_en": "Turkmenistan", 
        "short_code": "TM", 
        "lang": [ ]
    }, 
    "AZ": {
        "code_number": 994, 
        "name_cn": "阿塞拜疆", 
        "name_en": "Azerbaijan", 
        "short_code": "AZ", 
        "lang": [
            "az", 
            "az-AZ-Cyrl", 
            "az-AZ-Latn"
        ]
    }, 
    "GE": {
        "code_number": 995, 
        "name_cn": "格鲁吉亚", 
        "name_en": "Georgia", 
        "short_code": "GE", 
        "lang": [
            "ka", 
            "ka-GE"
        ]
    }, 
    "KG": {
        "code_number": 996, 
        "name_cn": "吉尔吉斯坦", 
        "name_en": "Kyrgyzstan", 
        "short_code": "KG", 
        "lang": [ ]
    }, 
    "UZ": {
        "code_number": 998, 
        "name_cn": "乌兹别克斯坦", 
        "name_en": "Uzbekistan", 
        "short_code": "UZ", 
        "lang": [
            "uz", 
            "uz-UZ-Cyrl", 
            "uz-UZ-Latn"
        ]
    }, 
    "AG": {
        "code_number": 1268, 
        "name_cn": "安提瓜和巴布达", 
        "name_en": "Antigua and Barbuda", 
        "short_code": "AG", 
        "lang": [ ]
    }, 
    "GD": {
        "code_number": 1473, 
        "name_cn": "格林纳达", 
        "name_en": "Grenada", 
        "short_code": "GD", 
        "lang": [ ]
    }, 
    "MS": {
        "code_number": 1664, 
        "name_cn": "蒙特塞拉特岛", 
        "name_en": "Montserrat Is", 
        "short_code": "MS", 
        "lang": [ ]
    }, 
    "VC": {
        "code_number": 1784, 
        "name_cn": "圣文森特岛", 
        "name_en": "Saint Vincent", 
        "short_code": "VC", 
        "lang": [ ]
    }, 
    "LC": {
        "code_number": 1809, 
        "name_cn": "圣卢西亚", 
        "name_en": "St.Lucia", 
        "short_code": "LC", 
        "lang": [ ]
    }, 
    "AI": {
        "code_number": 1809, 
        "name_cn": "安圭拉岛", 
        "name_en": "Anguilla", 
        "short_code": "AI", 
        "lang": [ ]
    }, 
    "BS": {
        "code_number": 1809, 
        "name_cn": "巴哈马", 
        "name_en": "Bahamas", 
        "short_code": "BS", 
        "lang": [ ]
    }, 
    "BB": {
        "code_number": 1809, 
        "name_cn": "巴巴多斯", 
        "name_en": "Barbados", 
        "short_code": "BB", 
        "lang": [ ]
    }, 
    "PR": {
        "code_number": 1809, 
        "name_cn": "波多黎各", 
        "name_en": "Puerto Rico", 
        "short_code": "PR", 
        "lang": [
            "es-PR"
        ]
    }, 
    "JM": {
        "code_number": 1809, 
        "name_cn": "牙买加", 
        "name_en": "Jamaica", 
        "short_code": "JM", 
        "lang": [
            "en-JM"
        ]
    }, 
    "DO": {
        "code_number": 1849, 
        "name_cn": "多米尼加共和国", 
        "name_en": "Dominica Rep.", 
        "short_code": "DO", 
        "lang": [
            "es-DO"
        ]
    }, 
    "TT": {
        "code_number": 1868, 
        "name_cn": "特立尼达和多巴哥", 
        "name_en": "Trinidad and Tobago", 
        "short_code": "TT", 
        "lang": [ ]
    }, 
    "LI": {
        "code_number": 4175, 
        "name_cn": "列支敦士登", 
        "name_en": "Liechtenstein", 
        "short_code": "LI", 
        "lang": [
            "de-LI"
        ]
    }
}';
}
