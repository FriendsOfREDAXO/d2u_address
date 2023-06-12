<?php
/**
 * Class managing modules published by www.design-to-use.de.
 *
 * @author Tobias Krais
 */
class D2UAddressModules
{
    /**
     * Get modules offered by this addon.
     * @return D2UModule[] Modules offered by this addon
     */
    public static function getModules()
    {
        $modules = [];
        $modules[] = new D2UModule('20-1',
            'D2U Adressen - Adressausgabe',
            10);
        $modules[] = new D2UModule('20-2',
            'D2U Adressen - Kontaktbox',
            5);
        $modules[] = new D2UModule('20-3',
            'D2U Adressen - Weltkarte',
            1);
        return $modules;
    }
}
