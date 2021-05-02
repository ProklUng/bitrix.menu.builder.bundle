# Битриксовое меню (бандл для Битрикс + Symfony)

Получение сносного по формату - вложенного, - массива с данными на меню. Плюс кэширующее прокси.

***INTERNAL***

Рефактор конторского легаси (избавление от статики и т.д).

## Установка

`composer require proklung/bitrix-menu-builder-bundle`

## Использование

1) Сервис `bitrix_menu_bundle.manager` (он же `Prokl\BitrixMenuBuilderBundle\Services\MenuManager`)
2) Кэширующее прокси - `bitrix_menu_bundle.manager_cached` (он же `Prokl\BitrixMenuBuilderBundle\Services\MenuManagerCached`)
3) Фасад - `Prokl\BitrixMenuBuilderBundle\Services\MenuManagerCachedFacade`. Если подключен соответствующий [бандл](https://github.com/ProklUng/facade.bundle).
4) Фасад - `Prokl\BitrixMenuBuilderBundle\Services\MenuManagerFacade`

Время жизни кэша и директория управляются в конфигурации бандла (`/local/config/packages/bitrix-menu-builder.yaml`) ключами
`ttl_cache` и `cache_dir` (относительно папки `bitrix`). 

По умолчанию значения 9600 и `/cache/bitrix_models_bundle`.

```php
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
    ): array {}

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
    ) { }
```

