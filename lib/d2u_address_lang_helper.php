<?php
/**
 * @api
 * Offers helper functions for language issues.
 */
class d2u_address_lang_helper extends \D2U_Helper\ALangHelper
{
    /**
     * @var array<string,string> Array with englisch replacements. Key is the wildcard,
     * value the replacement.
     */
    public $replacements_english = [
        'd2u_address_contact' => 'Contact',
        'd2u_address_failure_country' => 'Please enter your country.',
        'd2u_address_fax' => 'Fax',
        'd2u_address_local_servicepartner' => 'Your local Service partner',
        'd2u_address_mobile' => 'Mobile',
        'd2u_address_nearby' => 'Close to you',
        'd2u_address_other_countries' => 'Other Countries',
        'd2u_address_specialists' => 'Our specialists worldwide are available by telephone to offer advice and support.',
    ];

    /**
     * @var array<string,string> Array with german replacements. Key is the wildcard,
     * value the replacement.
     */
    protected array $replacements_german = [
        'd2u_address_contact' => 'Kontakt',
        'd2u_address_failure_country' => 'Bitte geben Sie Ihr Land ein.',
        'd2u_address_fax' => 'Fax',
        'd2u_address_local_servicepartner' => 'Ihr lokaler Ansprechpartner',
        'd2u_address_mobile' => 'Mobil',
        'd2u_address_nearby' => 'In Ihrer Nähe',
        'd2u_address_other_countries' => 'Weitere Länder',
        'd2u_address_specialists' => 'Unsere Spezialisten sind weltweit telefonisch für Sie erreichbar und beraten Sie gerne bezüglich Ihrer individuellen Anliegen.',
    ];

    /**
     * @var array<string,string> Array with french replacements. Key is the wildcard,
     * value the replacement.
     */
    protected array $replacements_french = [
        'd2u_address_contact' => 'Contact',
        'd2u_address_failure_country' => 'Veuillez indiquer votre pays.',
        'd2u_address_fax' => 'Fax',
        'd2u_address_local_servicepartner' => 'Votre interlocuteur local',
        'd2u_address_mobile' => 'Mobile',
        'd2u_address_nearby' => 'A proximité',
        'd2u_address_other_countries' => 'Autres pays',
        'd2u_address_specialists' => 'Nos spécialistes sont joignable téléphoniquement pour vous à travers le monde entier et ils vous conseilleront volontiers sur vos propres demandes.',
    ];

    /**
     * @var array<string,string> Array with spanish replacements. Key is the wildcard,
     * value the replacement.
     */
    protected array $replacements_spanish = [
        'd2u_address_contact' => 'Contacto',
        'd2u_address_failure_country' => 'Por favor, introduzca su país.',
        'd2u_address_fax' => 'Fax',
        'd2u_address_local_servicepartner' => 'Su interlocutor local',
        'd2u_address_mobile' => 'Móvil',
        'd2u_address_nearby' => 'Cerca de usted',
        'd2u_address_other_countries' => 'Otros países',
        'd2u_address_specialists' => 'Nuestros especialistas están disponibles en todo el mundo por teléfono y estarán encantados de asesorarle en sus necesidades individuales.',
    ];

    /**
     * @var array<string,string> Array with dutch replacements. Key is the wildcard,
     * value the replacement.
     */
    protected array $replacements_dutch = [
        'd2u_address_contact' => 'Contact',
        'd2u_address_failure_country' => 'Voer uw land.',
        'd2u_address_fax' => 'Fax',
        'd2u_address_local_servicepartner' => 'Uw plaatselijke contactpersoon',
        'd2u_address_mobile' => 'Mobiel',
        'd2u_address_nearby' => 'Dicht bij jou',
        'd2u_address_other_countries' => 'Andere landen',
        'd2u_address_specialists' => 'Onze specialisten zijn wereldwijd telefonisch verkrijgbaar en adviseren u graag op uw individuele wensen.',
    ];

    /**
     * @var array<string,string> Array with russian replacements. Key is the wildcard,
     * value the replacement.
     */
    protected array $replacements_russian = [
        'd2u_address_contact' => 'контакт',
        'd2u_address_failure_country' => 'Пожалуйста, введите название страны.',
        'd2u_address_fax' => 'факс',
        'd2u_address_local_servicepartner' => 'Ваш региональный сервисный партнер',
        'd2u_address_mobile' => 'Mобильный',
        'd2u_address_nearby' => 'Ближайший сервисный центр',
        'd2u_address_other_countries' => 'Другие страны',
        'd2u_address_specialists' => 'Наши специалисты доступны по всему миру по телефону и всегда готовы проконсультировать Вас по техническим вопросам.',
    ];

    /**
     * @var array<string,string> Array with portuguese replacements. Key is the wildcard,
     * value the replacement.
     */
    protected array $replacements_portuguese = [
        'd2u_address_contact' => 'Contato',
        'd2u_address_failure_country' => 'Introduza o seu país.',
        'd2u_address_fax' => 'Fax',
        'd2u_address_local_servicepartner' => 'O seu parceiro de contacto local',
        'd2u_address_mobile' => 'Móvel',
        'd2u_address_nearby' => 'Perto de você',
        'd2u_address_other_countries' => 'Outros países',
        'd2u_address_specialists' => 'Nossos especialistas estão disponíveis em todo o mundo por telefone e ficarão satisfeitos em informá-lo sobre seus requisitos individuais.',
    ];

    /**
     * @var array<string,string> Array with chinese replacements. Key is the wildcard,
     * value the replacement.
     */
    protected array $replacements_chinese = [
        'd2u_address_contact' => '联系方式',
        'd2u_address_failure_country' => '请输入您所在的国家。',
        'd2u_address_fax' => '传真',
        'd2u_address_local_servicepartner' => '您的当地联系人',
        'd2u_address_mobile' => '移动',
        'd2u_address_nearby' => '您的附近',
        'd2u_address_other_countries' => '其他国家',
        'd2u_address_specialists' => '您可以在全球范围内致电我们的客服专员，就您个人感兴趣的话题进行咨询。',
    ];

    /**
     * Factory method.
     * @return d2u_address_lang_helper Object
     */
    public static function factory()
    {
        return new self();
    }

    /**
     * Installs the replacement table for this addon.
     */
    public function install(): void
    {
        foreach ($this->replacements_english as $key => $value) {
            foreach (rex_clang::getAllIds() as $clang_id) {
                $lang_replacement = rex_config::get('d2u_address', 'lang_replacement_'. $clang_id, '');

                // Load values for input
                if ('chinese' === $lang_replacement && isset($this->replacements_chinese) && isset($this->replacements_chinese[$key])) {
                    $value = $this->replacements_chinese[$key];
                } elseif ('dutch' === $lang_replacement && isset($this->replacements_dutch) && isset($this->replacements_dutch[$key])) {
                    $value = $this->replacements_dutch[$key];
                } elseif ('french' === $lang_replacement && isset($this->replacements_french) && isset($this->replacements_french[$key])) {
                    $value = $this->replacements_french[$key];
                } elseif ('german' === $lang_replacement && isset($this->replacements_german) && isset($this->replacements_german[$key])) {
                    $value = $this->replacements_german[$key];
                } elseif ('portuguese' === $lang_replacement && isset($this->replacements_portuguese) && isset($this->replacements_portuguese[$key])) {
                    $value = $this->replacements_portuguese[$key];
                } elseif ('russian' === $lang_replacement && isset($this->replacements_russian) && isset($this->replacements_russian[$key])) {
                    $value = $this->replacements_russian[$key];
                } elseif ('spanish' === $lang_replacement && isset($this->replacements_spanish) && isset($this->replacements_spanish[$key])) {
                    $value = $this->replacements_spanish[$key];
                } else {
                    $value = $this->replacements_english[$key];
                }

                $overwrite = 'true' === rex_config::get('d2u_address', 'lang_wildcard_overwrite', false) ? true : false;
                parent::saveValue($key, $value, $clang_id, $overwrite);
            }
        }
    }
}
