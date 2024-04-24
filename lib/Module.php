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
            'D2U Adressen - Adressausgabe',
            11);
        $modules[] = new \TobiasKrais\D2UHelper\Module('20-2',
            'D2U Adressen - Kontaktbox',
            6);
        $modules[] = new \TobiasKrais\D2UHelper\Module('20-3',
            'D2U Adressen - Weltkarte',
            2);
        return $modules;
    }
}
