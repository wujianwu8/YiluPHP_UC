<?php
/*
 * 地址库
 * YiluPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/10/06
 * Time: 18:56
 */

class lib_address
{
	public function __construct()
	{
	}

    /**
     * @name 根据国家名称获取其语言键名
     * @desc
     * @param string $country
     * @return array
     */
    public function getCountryLangKey($country)
    {
        if (!$country){
            return $country;
        }
        $use_key = '';
        $last_percent = 0;
        foreach($this->country as $key => $item){
            similar_text($item['cn'], $country, $percent);
            if($percent>$last_percent) {
                $use_key = $key;
                $last_percent=$percent;
            }
            similar_text($item['en'], $country, $percent);
            if($percent>$last_percent) {
                $use_key = $key;
                $last_percent=$percent;
            }
        }
        if($use_key=='') {
            return $country;
        }
        return $use_key;
    }

    /**
     * @name 获取所有国家的语言键名
     * @desc
     * @return array
     */
    public function selectCountryLangKeys()
    {
        $keys = array_keys($this->country);
        asort($keys);
        return $keys;
    }


	private $country = [
        'country_canada' =>[
            'cn'=>'加拿大',
            'en'=>'Canada',
        ],
        'country_united_states' =>[
            'cn'=>'美国',
            'en'=>'United States',
        ],
        'country_russia' =>[
            'cn'=>'俄罗斯',
            'en'=>'Russia',
        ],
        'country_russia' =>[
            'cn'=>'哈萨克斯坦',
            'en'=>'Russia',
        ],
        'country_egypt' =>[
            'cn'=>'埃及',
            'en'=>'Egypt',
        ],
        'country_south_africa' =>[
            'cn'=>'南非',
            'en'=>'South Africa',
        ],
        'country_greece' =>[
            'cn'=>'希腊',
            'en'=>'Greece',
        ],
        'country_netherlands' =>[
            'cn'=>'荷兰',
            'en'=>'Netherlands',
        ],
        'country_belgium' =>[
            'cn'=>'比利时',
            'en'=>'Belgium',
        ],
        'country_france' =>[
            'cn'=>'法国',
            'en'=>'France',
        ],
        'country_spain' =>[
            'cn'=>'西班牙',
            'en'=>'Spain',
        ],
        'country_italy' =>[
            'cn'=>'意大利',
            'en'=>'Italy',
        ],
        'country_romania' =>[
            'cn'=>'罗马尼亚',
            'en'=>'Romania',
        ],
        'country_switzerland' =>[
            'cn'=>'瑞士',
            'en'=>'Switzerland',
        ],
        'country_austria' =>[
            'cn'=>'奥地利',
            'en'=>'Austria',
        ],
        'country_united_kingdom' =>[
            'cn'=>'英国',
            'en'=>'United Kingdom',
        ],
        'country_denmark' =>[
            'cn'=>'丹麦',
            'en'=>'Denmark',
        ],
        'country_sweden' =>[
            'cn'=>'瑞典',
            'en'=>'Sweden',
        ],
        'country_norway' =>[
            'cn'=>'挪威',
            'en'=>'Norway',
        ],
        'country_poland' =>[
            'cn'=>'波兰',
            'en'=>'Poland',
        ],
        'country_germany' =>[
            'cn'=>'德国',
            'en'=>'Germany',
        ],
        'country_peru' =>[
            'cn'=>'秘鲁',
            'en'=>'Peru',
        ],
        'country_mexico' =>[
            'cn'=>'墨西哥',
            'en'=>'Mexico',
        ],
        'country_cuba' =>[
            'cn'=>'古巴',
            'en'=>'Cuba',
        ],
        'country_argentina' =>[
            'cn'=>'阿根廷',
            'en'=>'Argentina',
        ],
        'country_brazil' =>[
            'cn'=>'巴西',
            'en'=>'Brazil',
        ],
        'country_chile' =>[
            'cn'=>'智利',
            'en'=>'Chile',
        ],
        'country_colombia' =>[
            'cn'=>'哥伦比亚',
            'en'=>'Colombia',
        ],
        'country_venezuela' =>[
            'cn'=>'委内瑞拉',
            'en'=>'Venezuela',
        ],
        'country_malaysia' =>[
            'cn'=>'马来西亚',
            'en'=>'Malaysia',
        ],
        'country_australia' =>[
            'cn'=>'澳大利亚',
            'en'=>'Australia',
        ],
        'country_indonesia' =>[
            'cn'=>'印度尼西亚',
            'en'=>'Indonesia',
        ],
        'country_philippines' =>[
            'cn'=>'菲律宾',
            'en'=>'Philippines',
        ],
        'country_new_zealand' =>[
            'cn'=>'新西兰',
            'en'=>'New Zealand',
        ],
        'country_singapore' =>[
            'cn'=>'新加坡',
            'en'=>'Singapore',
        ],
        'country_thailand' =>[
            'cn'=>'泰国',
            'en'=>'Thailand',
        ],
        'country_japan' =>[
            'cn'=>'日本',
            'en'=>'Japan',
        ],
        'country_south_korea' =>[
            'cn'=>'韩国',
            'en'=>'South Korea',
        ],
        'country_vietnam' =>[
            'cn'=>'越南',
            'en'=>'Vietnam',
        ],
        'country_china' =>[
            'cn'=>'中国',
            'en'=>'China',
        ],
        'country_turkey' =>[
            'cn'=>'土耳其',
            'en'=>'Turkey',
        ],
        'country_india' =>[
            'cn'=>'印度',
            'en'=>'India',
        ],
        'country_pakistan' =>[
            'cn'=>'巴基斯坦',
            'en'=>'Pakistan',
        ],
        'country_afghanistan' =>[
            'cn'=>'阿富汗',
            'en'=>'Afghanistan',
        ],
        'country_sri_lanka' =>[
            'cn'=>'斯里兰卡',
            'en'=>'Sri Lanka',
        ],
        'country_myanmar' =>[
            'cn'=>'缅甸',
            'en'=>'Myanmar',
        ],
        'country_iran' =>[
            'cn'=>'伊朗',
            'en'=>'Iran',
        ],
        'country_morocco' =>[
            'cn'=>'摩洛哥',
            'en'=>'Morocco',
        ],
        'country_algeria' =>[
            'cn'=>'阿尔及利亚',
            'en'=>'Algeria',
        ],
        'country_tunisia' =>[
            'cn'=>'突尼斯',
            'en'=>'Tunisia',
        ],
        'country_libya' =>[
            'cn'=>'利比亚',
            'en'=>'Libya',
        ],
        'country_gambia' =>[
            'cn'=>'冈比亚',
            'en'=>'Gambia',
        ],
        'country_senegal' =>[
            'cn'=>'塞内加尔',
            'en'=>'Senegal',
        ],
        'country_mali' =>[
            'cn'=>'圣马力诺',
            'en'=>'Mali',
        ],
        'country_mali' =>[
            'cn'=>'马里',
            'en'=>'Mali',
        ],
        'country_guinea' =>[
            'cn'=>'几内亚',
            'en'=>'Guinea',
        ],
        'country_cote_divoire' =>[
            'cn'=>'科特迪瓦共和国',
            'en'=>'Cote d\'Ivoire',
        ],
        'country_burkina_faso' =>[
            'cn'=>'布基纳法索',
            'en'=>'Burkina Faso',
        ],
        'country_niger' =>[
            'cn'=>'尼日尔',
            'en'=>'Niger',
        ],
        'country_togo' =>[
            'cn'=>'多哥',
            'en'=>'Togo',
        ],
        'country_benin' =>[
            'cn'=>'贝宁',
            'en'=>'Benin',
        ],
        'country_mauritius' =>[
            'cn'=>'毛里求斯',
            'en'=>'Mauritius',
        ],
        'country_liberia' =>[
            'cn'=>'利比里亚',
            'en'=>'Liberia',
        ],
        'country_sierra_leone' =>[
            'cn'=>'塞拉利昂',
            'en'=>'Sierra Leone',
        ],
        'country_ghana' =>[
            'cn'=>'加纳',
            'en'=>'Ghana',
        ],
        'country_nigeria' =>[
            'cn'=>'尼日利亚',
            'en'=>'Nigeria',
        ],
        'country_chad' =>[
            'cn'=>'乍得',
            'en'=>'Chad',
        ],
        'country_central_african_republic' =>[
            'cn'=>'中非共和国',
            'en'=>'Central African Republic',
        ],
        'country_cameroon' =>[
            'cn'=>'喀麦隆',
            'en'=>'Cameroon',
        ],
        'country_sao_tome__principe' =>[
            'cn'=>'圣多美和普林西比',
            'en'=>'Sao Tome & Principe',
        ],
        'country_gabon' =>[
            'cn'=>'加蓬',
            'en'=>'Gabon',
        ],
        'country_congo' =>[
            'cn'=>'刚果',
            'en'=>'Congo',
        ],
        'country_congo' =>[
            'cn'=>'扎伊尔',
            'en'=>'Congo',
        ],
        'country_angola' =>[
            'cn'=>'安哥拉',
            'en'=>'Angola',
        ],
        'country_seychelles' =>[
            'cn'=>'塞舌尔',
            'en'=>'Seychelles',
        ],
        'country_sudan' =>[
            'cn'=>'苏丹',
            'en'=>'Sudan',
        ],
        'country_ethiopia' =>[
            'cn'=>'埃塞俄比亚',
            'en'=>'Ethiopia',
        ],
        'country_somalia' =>[
            'cn'=>'索马里',
            'en'=>'Somalia',
        ],
        'country_djibouti' =>[
            'cn'=>'吉布提',
            'en'=>'Djibouti',
        ],
        'country_kenya' =>[
            'cn'=>'肯尼亚',
            'en'=>'Kenya',
        ],
        'country_tanzania' =>[
            'cn'=>'坦桑尼亚',
            'en'=>'Tanzania',
        ],
        'country_uganda' =>[
            'cn'=>'乌干达',
            'en'=>'Uganda',
        ],
        'country_burundi' =>[
            'cn'=>'布隆迪',
            'en'=>'Burundi',
        ],
        'country_mozambique' =>[
            'cn'=>'莫桑比克',
            'en'=>'Mozambique',
        ],
        'country_zambia' =>[
            'cn'=>'赞比亚',
            'en'=>'Zambia',
        ],
        'country_madagascar' =>[
            'cn'=>'马达加斯加',
            'en'=>'Madagascar',
        ],
        'country_zimbabwe' =>[
            'cn'=>'津巴布韦',
            'en'=>'Zimbabwe',
        ],
        'country_namibia' =>[
            'cn'=>'纳米比亚',
            'en'=>'Namibia',
        ],
        'country_malawi' =>[
            'cn'=>'马拉维',
            'en'=>'Malawi',
        ],
        'country_lesotho' =>[
            'cn'=>'莱索托',
            'en'=>'Lesotho',
        ],
        'country_botswana' =>[
            'cn'=>'博茨瓦纳',
            'en'=>'Botswana',
        ],
        'country_swaziland' =>[
            'cn'=>'斯威士兰',
            'en'=>'Swaziland',
        ],
        'country_hungary' =>[
            'cn'=>'匈牙利',
            'en'=>'Hungary',
        ],
        'country_yugoslavia' =>[
            'cn'=>'南斯拉夫',
            'en'=>'Yugoslavia',
        ],
        'country_gibraltar' =>[
            'cn'=>'直布罗陀',
            'en'=>'Gibraltar',
        ],
        'country_portugal' =>[
            'cn'=>'葡萄牙',
            'en'=>'Portugal',
        ],
        'country_luxembourg' =>[
            'cn'=>'卢森堡',
            'en'=>'Luxembourg',
        ],
        'country_ireland' =>[
            'cn'=>'爱尔兰',
            'en'=>'Ireland',
        ],
        'country_iceland' =>[
            'cn'=>'冰岛',
            'en'=>'Iceland',
        ],
        'country_albania' =>[
            'cn'=>'阿尔巴尼亚',
            'en'=>'Albania',
        ],
        'country_malta' =>[
            'cn'=>'马耳他',
            'en'=>'Malta',
        ],
        'country_cyprus' =>[
            'cn'=>'塞浦路斯',
            'en'=>'Cyprus',
        ],
        'country_finland' =>[
            'cn'=>'芬兰',
            'en'=>'Finland',
        ],
        'country_bulgaria' =>[
            'cn'=>'保加利亚',
            'en'=>'Bulgaria',
        ],
        'country_lithuania' =>[
            'cn'=>'立陶宛',
            'en'=>'Lithuania',
        ],
        'country_latvia' =>[
            'cn'=>'拉脱维亚',
            'en'=>'Latvia',
        ],
        'country_estonia' =>[
            'cn'=>'爱沙尼亚',
            'en'=>'Estonia',
        ],
        'country_moldova' =>[
            'cn'=>'摩尔多瓦',
            'en'=>'Moldova',
        ],
        'country_armenia' =>[
            'cn'=>'亚美尼亚',
            'en'=>'Armenia',
        ],
        'country_belarus' =>[
            'cn'=>'白俄罗斯',
            'en'=>'Belarus',
        ],
        'country_andorra' =>[
            'cn'=>'安道尔共和国',
            'en'=>'Andorra',
        ],
        'country_monaco' =>[
            'cn'=>'摩纳哥',
            'en'=>'Monaco',
        ],
        'country_ukraine' =>[
            'cn'=>'乌克兰',
            'en'=>'Ukraine',
        ],
        'country_slovenia' =>[
            'cn'=>'斯洛文尼亚',
            'en'=>'Slovenia',
        ],
        'country_czech_republic' =>[
            'cn'=>'捷克',
            'en'=>'Czech Republic',
        ],
        'country_slovakia' =>[
            'cn'=>'斯洛伐克',
            'en'=>'Slovakia',
        ],
        'country_bermuda_is' =>[
            'cn'=>'百慕大群岛',
            'en'=>'Bermuda Is.',
        ],
        'country_belize' =>[
            'cn'=>'伯利兹',
            'en'=>'Belize',
        ],
        'country_guatemala' =>[
            'cn'=>'危地马拉',
            'en'=>'Guatemala',
        ],
        'country_el_salvador' =>[
            'cn'=>'萨尔瓦多',
            'en'=>'El Salvador',
        ],
        'country_honduras' =>[
            'cn'=>'洪都拉斯',
            'en'=>'Honduras',
        ],
        'country_nicaragua' =>[
            'cn'=>'尼加拉瓜',
            'en'=>'Nicaragua',
        ],
        'country_costa_rica' =>[
            'cn'=>'哥斯达黎加',
            'en'=>'Costa Rica',
        ],
        'country_panama' =>[
            'cn'=>'巴拿马',
            'en'=>'Panama',
        ],
        'country_haiti' =>[
            'cn'=>'海地',
            'en'=>'Haiti',
        ],
        'country_bolivia' =>[
            'cn'=>'玻利维亚',
            'en'=>'Bolivia',
        ],
        'country_guyana' =>[
            'cn'=>'圭亚那',
            'en'=>'Guyana',
        ],
        'country_ecuador' =>[
            'cn'=>'厄瓜多尔',
            'en'=>'Ecuador',
        ],
        'country_french_guiana' =>[
            'cn'=>'法属圭亚那',
            'en'=>'French Guiana',
        ],
        'country_paraguay' =>[
            'cn'=>'巴拉圭',
            'en'=>'Paraguay',
        ],
        'country_suriname' =>[
            'cn'=>'苏里南',
            'en'=>'Suriname',
        ],
        'country_uruguay' =>[
            'cn'=>'乌拉圭',
            'en'=>'Uruguay',
        ],
        'country_guam' =>[
            'cn'=>'关岛',
            'en'=>'Guam',
        ],
        'country_brunei_darussalam' =>[
            'cn'=>'文莱',
            'en'=>'Brunei Darussalam',
        ],
        'country_nauru' =>[
            'cn'=>'瑙鲁',
            'en'=>'Nauru',
        ],
        'country_papua_new_guinea' =>[
            'cn'=>'巴布亚新几内亚',
            'en'=>'Papua New Guinea',
        ],
        'country_tonga' =>[
            'cn'=>'汤加',
            'en'=>'Tonga',
        ],
        'country_solomon_islands' =>[
            'cn'=>'所罗门群岛',
            'en'=>'Solomon Islands',
        ],
        'country_fiji' =>[
            'cn'=>'斐济',
            'en'=>'Fiji',
        ],
        'country_cook_islands' =>[
            'cn'=>'库克群岛',
            'en'=>'Cook Islands',
        ],
        'country_french_polynesia' =>[
            'cn'=>'法属玻利尼西亚',
            'en'=>'French Polynesia',
        ],
        'country_north_korea' =>[
            'cn'=>'朝鲜',
            'en'=>'North Korea',
        ],
        'country_cambodia' =>[
            'cn'=>'柬埔寨',
            'en'=>'Cambodia',
        ],
        'country_lao' =>[
            'cn'=>'老挝',
            'en'=>'Lao',
        ],
        'country_bangladesh' =>[
            'cn'=>'孟加拉国',
            'en'=>'Bangladesh',
        ],
        'country_maldives' =>[
            'cn'=>'马尔代夫',
            'en'=>'Maldives',
        ],
        'country_lebanon' =>[
            'cn'=>'黎巴嫩',
            'en'=>'Lebanon',
        ],
        'country_jordan' =>[
            'cn'=>'约旦',
            'en'=>'Jordan',
        ],
        'country_syria' =>[
            'cn'=>'叙利亚',
            'en'=>'Syria',
        ],
        'country_iraq' =>[
            'cn'=>'伊拉克',
            'en'=>'Iraq',
        ],
        'country_kuwait' =>[
            'cn'=>'科威特',
            'en'=>'Kuwait',
        ],
        'country_saudi_arabia' =>[
            'cn'=>'沙特阿拉伯',
            'en'=>'Saudi Arabia',
        ],
        'country_yemen' =>[
            'cn'=>'也门',
            'en'=>'Yemen',
        ],
        'country_oman' =>[
            'cn'=>'阿曼',
            'en'=>'Oman',
        ],
        'country_palestine' =>[
            'cn'=>'巴勒斯坦',
            'en'=>'Palestine',
        ],
        'country_united_arab_emirates' =>[
            'cn'=>'阿拉伯联合酋长国',
            'en'=>'United Arab Emirates',
        ],
        'country_israel' =>[
            'cn'=>'以色列',
            'en'=>'Israel',
        ],
        'country_bahrain' =>[
            'cn'=>'巴林',
            'en'=>'Bahrain',
        ],
        'country_qatar' =>[
            'cn'=>'卡塔尔',
            'en'=>'Qatar',
        ],
        'country_mongolia' =>[
            'cn'=>'蒙古',
            'en'=>'Mongolia',
        ],
        'country_nepal' =>[
            'cn'=>'尼泊尔',
            'en'=>'Nepal',
        ],
        'country_tajikistan' =>[
            'cn'=>'塔吉克斯坦',
            'en'=>'Tajikistan',
        ],
        'country_turkmenistan' =>[
            'cn'=>'土库曼斯坦',
            'en'=>'Turkmenistan',
        ],
        'country_azerbaijan' =>[
            'cn'=>'阿塞拜疆',
            'en'=>'Azerbaijan',
        ],
        'country_georgia' =>[
            'cn'=>'格鲁吉亚',
            'en'=>'Georgia',
        ],
        'country_kyrgyzstan' =>[
            'cn'=>'吉尔吉斯坦',
            'en'=>'Kyrgyzstan',
        ],
        'country_uzbekistan' =>[
            'cn'=>'乌兹别克斯坦',
            'en'=>'Uzbekistan',
        ],
        'country_antigua_and_barbuda' =>[
            'cn'=>'安提瓜和巴布达',
            'en'=>'Antigua and Barbuda',
        ],
        'country_grenada' =>[
            'cn'=>'格林纳达',
            'en'=>'Grenada',
        ],
        'country_montserrat_is' =>[
            'cn'=>'蒙特塞拉特岛',
            'en'=>'Montserrat Is',
        ],
        'country_saint_vincent' =>[
            'cn'=>'圣文森特岛',
            'en'=>'Saint Vincent',
        ],
        'country_stlucia' =>[
            'cn'=>'圣卢西亚',
            'en'=>'St.Lucia',
        ],
        'country_anguilla' =>[
            'cn'=>'安圭拉岛',
            'en'=>'Anguilla',
        ],
        'country_bahamas' =>[
            'cn'=>'巴哈马',
            'en'=>'Bahamas',
        ],
        'country_barbados' =>[
            'cn'=>'巴巴多斯',
            'en'=>'Barbados',
        ],
        'country_puerto_rico' =>[
            'cn'=>'波多黎各',
            'en'=>'Puerto Rico',
        ],
        'country_jamaica' =>[
            'cn'=>'牙买加',
            'en'=>'Jamaica',
        ],
        'country_dominica_rep' =>[
            'cn'=>'多米尼加共和国',
            'en'=>'Dominica Rep.',
        ],
        'country_trinidad_and_tobago' =>[
            'cn'=>'特立尼达和多巴哥',
            'en'=>'Trinidad and Tobago',
        ],
        'country_liechtenstein' =>[
            'cn'=>'列支敦士登',
            'en'=>'Liechtenstein',
        ],
    ];
}
