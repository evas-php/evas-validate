<?php
/**
 * Трейт экранирования html-тегов.
 * @package evas-php\evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate;

/**
 * Константы для свойств трейта по умолчанию.
 */
if (!defined('EVAS_ALLOWABLE_HTML_TAGS_DEFAULT')) 
    define('EVAS_ALLOWABLE_HTML_TAGS_DEFAULT', '');
if (!defined('EVAS_HTMLENTITIES_DEFAULT')) 
    define('EVAS_HTMLENTITIES_DEFAULT', false);

trait HtmlEscapingTrait
{
    // Локальные настройки сущности

    /** @var string|null общее правило поддерживаемых html-тегов */
    public $allowableHtmlTags = EVAS_ALLOWABLE_HTML_TAGS_DEFAULT;

    /** @var bool общее правило замены html-тегов на html-сущности */
    public $htmlentities = EVAS_HTMLENTITIES_DEFAULT;

    /** @var array маппинг правил поддерживания html-тегов для конкретных полей */
    public $allowableHtmlTagsMap = [];

    /** @var array маппинг правил замены на html-сущности для конкретных полей */
    public $htmlentitiesMap = [];


    // Глобальные настройки для класса

    /** @var string|null глобальное общее правило поддерживаемых html-тегов */
    public static $allowableHtmlTagsGlobal = EVAS_ALLOWABLE_HTML_TAGS_DEFAULT;

    /** @var bool глобальное общее правило замены html-тегов на html-сущности */
    public static $htmlentitiesGlobal = EVAS_HTMLENTITIES_DEFAULT;

    /** @var array глобальный маппинг правил поддерживания html-тегов для конкретных полей */
    public static $allowableHtmlTagsGlobalMap = [];

    /** @var array глобальный маппинг правил замены на html-сущности для конкретных полей */
    public static $htmlentitiesGlobalMap = [];


    // Локальная установка

    /**
     * Установка поддерживаемых html-тегов для strip_tags.
     * @param string|null поддерживаемые html-теги или null для поддержки всех
     * @param array|null поля к которым применить эти настройки
     * @return self
     */
    public function allowableHtmlTags(string $value = null, array $names = null)
    {
        if (!empty($names)) foreach ($names as &$subname) {
            $this->allowableHtmlTagsMap[$subname] = $value;
        } else {
            $this->allowableHtmlTags = $value;
        }
        return $this;
    }

    /**
     * Установка/сброс замены html-тегов на html-сущности.
     * @param bool использовать замену
     * @param array|null поля к которым применить эти настройки
     * @return self
     */
    public function htmlentities(bool $using = false, array $names = null)
    {
        if (!empty($names)) foreach ($names as &$subname) {
            $this->htmlentitiesMap[$subname] = $using;
        } else {
            $this->htmlentities = $using;
        }
        return $this;
    }

    // Глобальная установка.

    /**
     * Глобальная установка поддерживаемых html-тегов для strip_tags.
     * @param string|null поддерживаемые html-теги или null для поддержки всех
     * @param array|null поля к которым применить эти настройки
     */
    public static function allowableHtmlTagsGlobal(string $value = null, array $names = null)
    {
        if (!empty($names)) foreach ($names as &$subname) {
            static::$allowableHtmlTagsGlobalMap[$subname] = $value;
        } else {
            static::$allowableHtmlTagsGlobal = $value;
        }
    }

    /**
     * Глобавльная установка замены html-тего на html-сущности.
     * @param bool использовать замену
     * @param array|null поля к которым применить эти настройки
     */
    public static function htmlentitiesGlobal(bool $using = false, array $names = null)
    {
        if (!empty($names)) foreach ($names as &$subname) {
            static::$htmlentitiesGlobalMap[$subname] = $using;
        } else {
            static::$htmlentitiesGlobal = $using;
        }
    }

    // Получение настроек с учетом глобальных настроек

    /**
     * Получение общего правила поддерживаемых html-тегов для strip_tags.
     * @return string|null
     */
    public function getAllowableHtmlTags(): ?string
    {
        return $this->allowableHtmlTags !== EVAS_ALLOWABLE_HTML_TAGS_DEFAULT 
            ? $this->allowableHtmlTags : static::$allowableHtmlTagsGlobal;
    }

    /**
     * Получение общего правила экранирования htmlentities.
     * @return bool
     */
    public function getHtmlentities(): bool
    {
        return $this->htmlentities !== EVAS_HTMLENTITIES_DEFAULT 
            ? $this->htmlentities : static::$htmlentitiesGlobal;
    }

    /**
     * Получение маппинга правил поддерживаемых тегов strip_tags.
     * @return array
     */
    public function getAllowableHtmlTagsMap(): array
    {
        return array_merge(static::$allowableHtmlTagsGlobalMap, $this->allowableHtmlTagsMap);
    }

    /**
     * Получение маппинга правил htmlentities.
     * @return array
     */
    public function getHtmlentitiesMap(): array
    {
        return array_merge(static::$htmlentitiesGlobalMap, $this->htmlentitiesMap);
    }

    // Методы для наследования

    /**
     * Склеивание мапов внешнего и вложенного объекта.
     * @param array маппинг вложенного объекта
     * @param array маппинг внешнего объекта
     */
    protected static function mergeMaps(array &$innerMap, array $outerMap)
    {
        $innerMap = array_merge($outerMap, $innerMap);
    }

    /**
     * Наследование параметров экранирования html во вложенный объект.
     * @param object вложенныё объект
     * @return object вложенный объект
     */
    public function inheritHtmlEscaping(object &$inner): object
    {
        if (EVAS_ALLOWABLE_HTML_TAGS_DEFAULT === $inner->getAllowableHtmlTags()) {
            $inner->allowableHtmlTags = $this->getAllowableHtmlTags();
        }
        if (EVAS_HTMLENTITIES_DEFAULT === $inner->getHtmlentities()) {
            $inner->htmlentities = $this->getHtmlentities();
        }
        static::mergeMaps($inner->allowableHtmlTagsMap, $this->getAllowableHtmlTagsMap());
        static::mergeMaps($inner->htmlentitiesMap, $this->getHtmlentitiesMap());
        return $inner;
    }


    // Применение настроек

    /**
     * Экранирование html-тегов в значении.
     * @param mixed значение
     * @param string|null имя поля
     * @return mixed значение
     */
    public function escapeHtml($value, string $name = null)
    {
        if (is_string($value)) {
            $allowableHtmlTagsMap = $this->getAllowableHtmlTagsMap();
            $htmlentitiesMap = $this->getHtmlentitiesMap();

            if (empty($name) && '0' !== $name) {
                $allowableHtmlTags = $this->getAllowableHtmlTags();
                $htmlentities = $this->getHtmlentities();
            } else {
                $allowableHtmlTags = $allowableHtmlTagsMap[$name] ?? $this->getAllowableHtmlTags();
                $htmlentities = $htmlentitiesMap[$name] ?? $this->getHtmlentities();
            }

            if (null !== $allowableHtmlTags) {
                $value = strip_tags($value, $allowableHtmlTags);
            }
            if (true === $htmlentities) {
                $value = htmlentities($value);
            }
        } else if (is_array($value) || is_object($value)) {
            $this->escapeHtmlInValues($value);
        }
        return $value;
    }

    /**
     * Экранирование html-тегов в массиве или объекте.
     * @param array|object значение
     * @return array|object значение
     */
    public function escapeHtmlInValues($values)
    {
        if (is_array($values) || is_object($values)) {
            foreach ($values as $name => &$value) {
                $value = $this->escapeHtml($value, $name);
            }
        }
        return $values;
    }
}
