<?php

namespace Prokl\BitrixMenuBuilderBundle\Services;

use CIBlockElement;
use CIBlockSection;
use Exception;

/**
 * Class IblockTree
 * Генерация дерева из инфоблока, включающее
 * элементы.
 * @package Prokl\BitrixMenuBuilderBundle\Services
 */
class IblockTree
{
    /**
     * @var integer $iblockId ID инфоблока.
     */
    private $iblockId;

    /**
     * @var array $arTree Результирующее дерево.
     */
    private $arTree = [];

    /**
     * IblockTree constructor.
     *
     * @param integer $iblockId ID инфоблока.
     */
    public function __construct(int $iblockId = 0)
    {
        $this->iblockId = $iblockId;
    }

    /**
     * @param integer $iblockId
     *
     * @return $this
     */
    public function setIblockId(int $iblockId): self
    {
        $this->iblockId = $iblockId;

        return $this;
    }

    /**
     * Собрать дерево, включающее элементы.
     *
     * @return array
     */
    public function get(): array
    {
        try {
            $this->makeTree($this->iblockId);

            return $this->arTree;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Получить данные об элементах меню.
     *
     * @param integer $iblockId  ID инфоблока.
     * @param integer $sectionId ID секции инфоблока.
     *
     * @return array
     */
    private function getItems(int $iblockId, int $sectionId): array
    {
        $arItems = [];

        $res = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => $iblockId, 'IBLOCK_SECTION_ID' => $sectionId, 'ACTIVE' => 'Y'],
            false,
            false,
            ['NAME', 'IBLOCK_SECTION_ID', 'PROPERTY_WEIGHT', 'PREVIEW_TEXT']
        );

        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();

            $arItems[] = [
                'NAME' => $arFields['NAME'],
                'PREVIEW_TEXT' => $arFields['PREVIEW_TEXT'],
            ];
        }

        return $arItems;
    }

    /**
     * @param integer $iblockId ID инфоблока.
     *
     * @throws Exception $e Неправильный код инфоблока.
     *
     * @return void
     */
    private function makeTree(int $iblockId) : void
    {
        /**
         * Построение дерева.
         * @internal См. https://yunaliev.ru/2014/01/razdely-infobloka-v-vide-massiva-1s-bitriks/
         */

        if (!$this->iblockId) {
            throw new Exception('Make tree: invalid ID infoblock');
        }

        $section = CIBlockSection::GetList(
            ['DEPTH_LEVEL' => 'desc', 'SORT' => 'ASC'],
            ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
            false,
            ['ID', 'NAME', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL', 'SORT', 'IBLOCK_ID']
        );

        /** Результат сборки данных в дерево. */
        $arSectionList = [];
        /** Уровни вложенности. */
        $arDepthLevel = [];

        while ($arSection = $section->GetNext(true, false)) {
            $arPush = $arSection;

            // Подмес элементов (если они существуют)
            $arItems = $this->getItems($iblockId, $arSection['ID']);
            if (!empty($arItems)) {
                $arPush['ITEMS'] = $arItems;
            }

            $arSectionList[$arSection['ID']] = $arPush;
            $arDepthLevel[] = $arSection['DEPTH_LEVEL'];
        }

        $ar_DepthLavelResult = array_unique($arDepthLevel);
        rsort($ar_DepthLavelResult);

        $maxDepthLevel = (int)$ar_DepthLavelResult[0];

        for ($i = $maxDepthLevel; $i > 1; $i--) {
            foreach ($arSectionList as $iSectionID => $arValue) {
                if ($arValue['DEPTH_LEVEL'] == $i) {
                    $arSectionList[$arValue['IBLOCK_SECTION_ID']]['SUB_SECTION'][] = $arValue;
                    unset($arSectionList[$iSectionID]);
                }
            }
        }

        // Финальная сортировка дерева по индексу SORT.
        usort(
            $arSectionList,
            function ($a, $b) {
                if ($a['SORT'] == $b['SORT']) {
                    return 0;
                }

                return ($a['SORT'] < $b['SORT']) ? -1 : 1;
            }
        );

        $this->arTree = $arSectionList; // Результат.
    }
}
