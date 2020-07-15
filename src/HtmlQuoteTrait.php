<?php
/**
 * @package evas-php\evas-validate
 */
namespace Evas\Validate;

/**
 * Константы для свойств трейта по умолчанию.
 */
if (!defined('EVAS_ALLOWABLE_HTML_TAGS_DEFAULT')) define('EVAS_ALLOWABLE_HTML_TAGS_DEFAULT', null);
if (!defined('EVAS_HTMLENTITIES_DEFAULT')) define('EVAS_HTMLENTITIES_DEFAULT', false);

/**
 * Трейт экранирования html-тегов.
 * @author Egor Vasyakin <egor@evas-php.com>
 * @since 1.0
 */
trait HtmlQuoteTrait
{
    // Локальные настройки сущности

    /**
     * @var string|null общее правило поддерживаемых html-тегов
     */
    public $allowableHtmlTags = EVAS_ALLOWABLE_HTML_TAGS_DEFAULT;

    /**
     * @var bool общее правило замены html-тегов на html-сущности
     */
    public $htmlentities = EVAS_HTMLENTITIES_DEFAULT;

    /**
     * @var array маппинг правил поддерживания html-тегов для конкретных полей
     */
    public $allowableHtmlTagsMap = [];

    /**
     * @var array маппинг правил замены на html-сущности для конкретных полей
     */
    public $htmlentitiesMap = [];


    // Глобавльные настройки для класса

    /**
     * @var string|null глобальное общее правило поддерживаемых html-тегов
     */
    public static $allowableHtmlTagsGlobal = EVAS_ALLOWABLE_HTML_TAGS_DEFAULT;

    /**
     * @var bool глобальное общее правило замены html-тегов на html-сущности
     */
    public static $htmlentitiesGlobal = EVAS_HTMLENTITIES_DEFAULT;

    /**
     * @var array глобальный маппинг правил поддерживания html-тегов для конкретных полей
     */
    public static $allowableHtmlTagsGlobalMap = [];

    /**
     * @var array глобальный маппинг правил замены на html-сущности для конкретных полей
     */
    public static $htmlentitiesGlobalMap = [];


    // Локальная установка

    /**
     * Установка поддерживаемых html-тегов для strip_tags.
     * @param string
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
     * Установка замены html-тего на html-сущности.
     * @param bool
     * @param array|null поля к которым применить эти настройки
     * @return self
     */
    public function htmlentities(bool $value = false, array $names = null)
    {
        if (!empty($names)) foreach ($names as &$subname) {
            $this->htmlentitiesMap[$subname] = $value;
        } else {
            $this->htmlentities = $value;
        }
        return $this;
    }

    // Глобальная установка.

    /**
     * Глобальная установка поддерживаемых html-тегов для strip_tags.
     * @param string
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
     * @param bool
     * @param array|null поля к которым применить эти настройки
     */
    public static function htmlentitiesGlobal(bool $value = false, array $names = null)
    {
        if (!empty($names)) foreach ($names as &$subname) {
            static::$htmlentitiesGlobalMap[$subname] = $value;
        } else {
            static::$htmlentitiesGlobal = $value;
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
    public static function mergeMaps(array &$innerMap, array $outerMap)
    {
        $innerMap = array_merge($outerMap, $innerMap);
    }

    /**
     * Наследование параметров экранирования во вложенный объект.
     * @param object вложенныё объект
     * @return object вложенный объект
     */
    public function extendsQuote(object &$inner, string $name = null): object
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
     * Экранирование html-тегов.
     * @param mixed значение
     * @param string|null имя поля
     * @return mixed значение
     */
    public function quoteHtmlValue($value, string $name = null)
    {
        if (is_string($value)) {
            $allowableHtmlTagsMap = $this->getAllowableHtmlTagsMap();
            $htmlentitiesMap = $this->getHtmlentitiesMap();

            $allowableHtmlTags = !empty($name)
                ? $allowableHtmlTagsMap[$name] ?? $this->getAllowableHtmlTags()
                : $this->getAllowableHtmlTags();

            $htmlentities = !empty($name)
                ? $htmlentitiesMap[$name] ?? $this->getHtmlentities()
                : $this->getHtmlentities();

            if (null !== $allowableHtmlTags) {
                $value = strip_tags($value, $allowableHtmlTags);
            }
            if (true === $htmlentities) {
                $value = htmlentities($value);
            }
        }
        return $value;
    }

    /**
     * Экранирования html-тегов в массиве или объекте.
     * @param array|object значения
     * @return array|object значения
     */
    public function quoteHtmlValues(&$values)
    {
        if (is_array($values) || is_object($values)) {
            foreach ($values as $name => &$value) {
                $value = $this->quoteHtmlValue($value, $name);
            }
        }
        return $values;
    }
}
