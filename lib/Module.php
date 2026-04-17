<?php

namespace FriendsOfRedaxo\D2UAddress;
/**
 * Class managing modules published by www.design-to-use.de.
 *
 * @author Tobias Krais
 */
class Module
{
    /**
     * Get modules offered by this addon.
     * @return \TobiasKrais\D2UHelper\Module[] Modules offered by this addon
     */
    public static function getModules()
    {
        $modules = [];
        $modules[] = new \TobiasKrais\D2UHelper\Module('20-1',
            'D2U Adressen - Adressausgabe (BS4, deprecated)',
            11);
        $modules[] = new \TobiasKrais\D2UHelper\Module('20-2',
            'D2U Adressen - Kontaktbox (BS4, deprecated)',
            6);
        $modules[] = new \TobiasKrais\D2UHelper\Module('20-3',
            'D2U Adressen - Weltkarte (BS4, deprecated)',
            2);
        $modules[] = new \TobiasKrais\D2UHelper\Module('20-4',
            'D2U Adressen - Adressausgabe (BS5)',
            1);
        $modules[] = new \TobiasKrais\D2UHelper\Module('20-5',
            'D2U Adressen - Kontaktbox (BS5)',
            1);
        $modules[] = new \TobiasKrais\D2UHelper\Module('20-6',
            'D2U Adressen - Weltkarte (BS5)',
            1);
        return $modules;
    }
}
