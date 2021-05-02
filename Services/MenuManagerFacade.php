<?php

namespace Prokl\BitrixMenuBuilderBundle\Services;

use Prokl\FacadeBundle\Services\AbstractFacade;

/**
 * Class MenuManagerCachedFacade
 * @package Prokl\BitrixMenuBuilderBundle
 *
 */
class MenuManagerFacade extends AbstractFacade
{
    /**
     * Сервис фасада.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'bitrix_menu_bundle.manager';
    }
}
