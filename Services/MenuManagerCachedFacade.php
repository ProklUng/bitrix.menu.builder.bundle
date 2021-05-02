<?php

namespace Prokl\BitrixMenuBuilderBundle\Services;

use Prokl\FacadeBundle\Services\AbstractFacade;

/**
 * Class MenuManagerCachedFacade
 * @package Prokl\BitrixMenuBuilderBundle
 *
 */
class MenuManagerCachedFacade extends AbstractFacade
{
    /**
     * Сервис фасада.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'menu.manager_cached';
    }
}
