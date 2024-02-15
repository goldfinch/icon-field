<?php

namespace Goldfinch\Icon\ORM\FieldType;

use Goldfinch\Icon\Forms\IconField;
use PhpTek\JSONText\ORM\FieldType\JSONText;
use SilverStripe\ORM\FieldType\DBComposite;

class DBIcon extends DBComposite
{
    /**
    - $Icon
        json: img
        dir: img
        upload: img
        font: i

    - $Icon.URL
        json: +
        dir: +
        upload: +
        font: -

    - $Icon.Title
        json: +
        dir: +
        upload: +
        font: -

    - $Icon.Color
        json: + (if svg)
        dir: + (if svg)
        upload: + (if svg)
        font: +

    - $Icon.Size
        json: +
        dir: +
        upload: +
        font: +

    - $Icon.WithAttr
    - $Icon.WithClass

    - loop $Icon (multiple)
     */

    /**
     * @var string $locale
     */
    protected $locale = null;

    /**
     * @var array<string,string>
     */
    private static $composite_db = [
        'Key' => 'Varchar(255)',
        'Data' => JSONText::class,
    ];

    public function getParse($key = null)
    {
        $data = $this->getData();

        if (!$data) {
            return null;
        }

        $data = json_decode($data, true);

        $parse = [
            'set' => '',
            'type' => '',
            'title' => $data['title'],
            'source' => $data['source'],
        ];

        return $key ? (isset($parse[$key]) ? $parse[$key] : null) : $parse;
    }

    public function getIconSet()
    {
        return $this->getParse('set');
    }

    public function getIconType()
    {
        return $this->getParse('type');
    }

    public function getIconTitle()
    {
        return $this->getParse('title');
    }

    public function getIconSource()
    {
        return $this->getParse('source');
    }

    /**
     *
     * @return string
     */
    public function getValue()
    {
        if (!$this->exists()) {
            return null;
        }

        // $data = $this->getData();

        $key = $this->getKey();

        // if (empty($key)) {
        //     return $data;
        // }

        return $key; // $data . ' ' . $key;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->getField('Key');
    }

    /**
     * @param string $key
     * @param bool $markChanged
     * @return $this
     */
    public function setKey($key, $markChanged = true)
    {
        $this->setField('Key', $key, $markChanged);
        return $this;
    }

    /**
     * @return float
     */
    public function getData()
    {
        return $this->getField('Data');
    }

    /**
     * @param mixed $data
     * @param bool $markChanged
     * @return $this
     */
    public function setData($data, $markChanged = true)
    {
        // Retain nullability to mark this field as empty
        if (isset($data)) {
            $data = (float) $data;
        }
        $this->setField('Data', $data, $markChanged);
        return $this;
    }

    /**
     * @return boolean
     */
    public function exists()
    {
        return is_numeric($this->getData());
    }

    /**
     * Determine if this has a non-zero data
     *
     * @return bool
     */
    public function hasData()
    {
        $a = $this->getData();
        return !empty($a) && is_numeric($a);
    }

    /**
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale ?: i18n::get_locale();
    }

    /**
     * Returns a CompositeField instance used as a default
     * for form scaffolding.
     *
     * Used by {@link SearchContext}, {@link ModelAdmin}, {@link DataObject::scaffoldFormFields()}
     *
     * @param string $title Optional. Localized title of the generated instance
     * @param array $params
     * @return FormField
     */
    public function scaffoldFormField($title = null, $params = null)
    {
        return IconField::create($this->getName(), $title);
        // ->setLocale($this->getLocale());
    }
}
