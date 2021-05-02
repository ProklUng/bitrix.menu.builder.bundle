<?php

namespace Prokl\BitrixMenuBuilderBundle\Services;

use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;
use CHTTP;
use Prokl\CacheProxificator\CacheProxificator;

/**
 * Class MenuManager
 * @package Prokl\BitrixMenuBuilderBundle\Services
 *
 * @internal Refactor from legacy.
 */
class MenuManagerCached extends CacheProxificator
{
    /**
     * @inheritDoc
     */
    protected function getCacheKey(string $src) : string
    {
        $keyCache = parent::getCacheKey($src);

        // Текущая страница. Важно, потому что параметр selected на пунктах меню
        // иначе не обрабатывается.
        $url = $this->getCurrentUrl();

        // Учесть 404, чтобы предотвратить замусоривание кэша.
        $process404 = (CHTTP::GetLastStatus() === '404 Not Found') ?
            md5('404 Not Found')
            :
            md5($url);

        return $keyCache . $process404;
    }

    /**
     * Текущий URL.
     *
     * @return string
     */
    private function getCurrentUrl() : string
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $uri = new Uri($request->getRequestUri());

        return $uri->getUri();
    }
}
