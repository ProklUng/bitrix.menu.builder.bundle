<?php

namespace Prokl\BitrixMenuBuilderBundle\Services;

use CIBlock;
use CIBlockSection;
use CMain;
use CMenu;
use CSite;
use Exception;

/**
 * Class MenuManager
 * @package Prokl\MenuBuilder\BitrixMenuBuilderBundle\Services
 *
 * @internal Refactor from legacy.
 */
class MenuManager
{
    /**
     * @var array $defaultMenuKeys Массив ключей для битриксового меню.
     */
    private $defaultMenuKeys = [
        'NAME',
        'LINK',
        'ADDITIONAL_LINKS',
        'PARAMS',
        'CONDITION',
    ];

    /**
     * Возвращает рекурсивный массив пунктов меню.
     *
     * @param string  $dir              Директория, с которой начинать рекурсию.
     * @param string  $menuType         Тип меню.
     * @param boolean $bDisableRootLink Заменить ссылки корневого меню на
     * ссылку из первого дочернего элемента.
     * @param boolean $bUseExt          Подключать файлы расширений.
     * @param integer $maxLevel         Количество уровней для сканирования.
     * @param boolean $bCheckSelected   Отмечать выбранные пункты.
     *
     * @return array
     * @throws Exception Error.
     */
    public function getTreeMenuByDir(
        string $dir = '/',
        string $menuType = 'top',
        bool $bDisableRootLink = true,
        bool $bUseExt = true,
        int $maxLevel = 4,
        bool $bCheckSelected = true
    ): array {
        // Привести каталог в канонизированный абсолютный путь
        $path = realpath(
            strpos($_SERVER['DOCUMENT_ROOT'], $dir) === false ? $_SERVER['DOCUMENT_ROOT'].$dir : $dir
        );
        // Проверяем корректный путь до дректории
        if (!is_dir($path)) {
            return [];
        } else {
            $arMenu = $this->getRecursiveSubmenu(
                $dir,
                $menuType,
                $bUseExt,
                $bCheckSelected,
                $maxLevel
            );
        }

        // Если есть дочерние элементы, то родительскому элементу
        // присваиваем ссылку из первого дочернего элемента
        if ($bDisableRootLink) {
            foreach ($arMenu as $iKey => $arItem) {
                if (!empty($arItem['CHILD'])) {
                    $arMenu[$iKey]['LINK'] = current($arItem['CHILD'])['LINK'];
                }
            }
        }

        return $arMenu;
    }

    /**
     * Получает один уровень меню из пути.
     *
     * @param string  $dir      Директория, с которой начинать рекурсию.
     * @param integer $level    Уровень, который необходимо получить.
     * @param string  $menuType Тип меню.
     * @param boolean $bUseExt  Подключать файлы расширений.
     *
     * @return array
     */
    public function getOneLevelMenu(
        string $dir = '/',
        int $level = 1,
        string $menuType = 'top',
        bool $bUseExt = true
    ) {
        $arPath = explode('/', $dir);

        return $this->getRecursiveSubmenu($arPath[$level], $menuType, $bUseExt, true, $level);
    }

    /**
     * Рекурсивно обходит дочерние директории и формирует массив подменю.
     *
     * @param string  $dir            Директория, с которой начинать рекурсию.
     * @param string  $menuType       Тип меню.
     * @param boolean $bUseExt        Подключать файлы расширений.
     * @param boolean $bCheckSelected Отмечать выбранные пункты.
     * @param integer $maxLevel       Количество уровней для сканирования.
     * @param integer $iCurrentLevel  Текущий уровень меню {@internal root = 0 }}.
     *
     * @return array
     */
    private function getRecursiveSubmenu(
        string $dir = '/',
        string $menuType = 'top',
        bool $bUseExt = false,
        bool $bCheckSelected = true,
        int $maxLevel = 4,
        int $iCurrentLevel = 0
    ): array {
        $obMain = new CMain();

        $currentPage = $obMain->GetCurPage();

        $iCurrentLevel++;

        // Получаем текущий список меню.
        $menu = new CMenu($menuType);
        $menu->Init($dir, $bUseExt, false, true);

        $menu->RecalcMenu($bUseExt, $bCheckSelected);
        $arMenus = $menu->arMenu;

        // Добавляем дочерние пункты меню рекурсивно.
        if (count($arMenus) > 0 && $maxLevel >= $iCurrentLevel) {
            foreach ($arMenus as $key => $arMenu) {
                // Присваеваем ключи массиву
                $arMenus[$key] = array_combine($this->defaultMenuKeys, $arMenu);
                $arMenus[$key]['CHILD'] = $this->getRecursiveSubmenu(
                    $arMenu[1],
                    $menuType,
                    $bUseExt,
                    $bCheckSelected,
                    $maxLevel,
                    $iCurrentLevel
                );

                $arMenus[$key]['DEPTH_LEVEL'] = $iCurrentLevel;

                // Указываем признак того, что элемент является родительским.
                $arMenus[$key]['PARAMS']['IS_PARENT'] = !empty($arMenus[$key]['CHILD']) && 0 < count(
                    $arMenus[$key]['CHILD']
                );

                // Указываем признак того, что элемент активен.
                $arMenus[$key]['PARAMS']['SELECTED'] = strpos($currentPage, $arMenus[$key]['LINK']) !== false;
            }
        }

        return $arMenus;
    }


    /**
     * Генерирует меню на основе типов инфоблоков и директорий.
     *
     * @param array $arParams Параметры.
     *
     * @return array
     */
    public function getMenuByIBlockType(array $arParams = ['IBLOCK_TYPE' => 'blocks'])
    {
        $cMain = new CMain();

        $isMainPage = CSite::InDir('/index.php');
        $currentPageUrl = $cMain->GetCurDir();

        $aMenuLinks = [];

        $rs = CIBlock::GetList([], ['TYPE' => $arParams['IBLOCK_TYPE']]);

        while ($arIBlock = $rs->Fetch()) {
            $iblockPageUrl = str_replace(
                ['#SITE_DIR#', '#IBLOCK_TYPE_ID#', '#IBLOCK_CODE#'],
                ['', $arIBlock['IBLOCK_TYPE_ID'], $arIBlock['CODE']],
                $arIBlock['LIST_PAGE_URL']
            );

            $iblockSelected = ((strpos($currentPageUrl, $iblockPageUrl) === 0) ||
                (strpos($iblockPageUrl, $currentPageUrl) === 0));

            //если на главной, то нет выделенных.
            if ($isMainPage) {
                $iblockSelected = false;
            }

            $aMenuLinks[] = [
                $arIBlock['NAME'],
                $iblockPageUrl,
                [],
                [
                    'IBLOCK_ID' => $arIBlock['ID'],
                    'IS_PARENT' => true,
                    'DEPTH_LEVEL' => 1,
                    'SELECTED' => $iblockSelected,
                ],
                '',
            ];
            $rsSubMenu = CIBlockSection::GetList(
                ['SORT' => 'ASC'],
                ['IBLOCK_ID' => $arIBlock['ID'], 'ACTIVE' => 'Y']
            );

            while ($arSection = $rsSubMenu->fetch()) {
                $aMenuLinks[] = [
                    $arSection['NAME'],
                    str_replace(
                        ['#SITE_DIR#', '#IBLOCK_TYPE_ID#', '#IBLOCK_CODE#', '#SECTION_CODE#'],
                        ['', $arSection['IBLOCK_TYPE_ID'], $arIBlock['CODE'], $arSection['CODE']],
                        $arSection['SECTION_PAGE_URL']
                    ),
                    [],
                    ['IBLOCK_ID' => $arSection['ID'], 'IS_PARENT' => false, 'DEPTH_LEVEL' => 2],
                    '',
                ];
            }
        }

        return $aMenuLinks;
    }
}
