<?php

namespace Goldfinch\Icon\Forms;

use SilverStripe\Core\Path;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\FormField;
use SilverStripe\Control\Director;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Core\Manifest\ModuleResourceLoader;

class IconFontField extends OptionsetField
{
    public function __construct($name, $title = null)
    {
        parent::__construct($name, $title, []);

        $this->setSourceIcons();
    }

    public function setSourceIcons()
    {
        $icons = self::config()->get('icon_list');

        if (array_is_list($icons))
        {
            $icons = array_combine($icons, $icons);
        }

        $this->source = $icons;
        return $this;
    }

    public function getAbsolutePathFromRelative($relative_path)
    {
        return Path::join(
            (Director::publicDir() ? Director::publicFolder() : Director::baseFolder()),
            ModuleResourceLoader::singleton()->resolveURL($relative_path)
        );
    }

    public function getFullRelativePath($path)
    {
        return ModuleResourceLoader::singleton()->resolveURL($path);
    }

    public function SVG($path)
    {
        $filePath = $this->getAbsolutePathFromRelative($path);
        $filePath = current(explode('?m', $filePath));

        $svg = file_get_contents($filePath);

        $html = DBHTMLText::create();
        $html->setValue($svg);
        return $html;
    }

    public function Field($properties = [])
    {
        Requirements::css('goldfinch/icon:client/dist/icon-styles.css');
        Requirements::javascript('goldfinch/icon:client/dist/icon.js');
        $source = $this->getSource();
        $options = [];

        $options[] = ArrayData::create([
            'ID' => 'none',
            'Name' => $this->name,
            'Value' => '',
            'Title' => '',
            'isChecked' => (!$this->value || $this->value == '')
        ]);

        if ($source)
        {
            foreach ($source as $value => $title)
            {
                $itemID = $this->ID() . '_' . preg_replace('/[^a-zA-Z0-9]/', '', $value);

                $options[] = ArrayData::create([
                    'ID' => $itemID,
                    'Name' => $this->name,
                    'Value' => $value,
                    'Title' => $title,
                    'isChecked' => $value == $this->value
                ]);
            }
        }

        $properties = array_merge($properties, [
            'Options' => ArrayList::create($options)
        ]);

        $this->setTemplate('IconFontField');

        return FormField::Field($properties);
    }

    public function extraClass()
    {
        $classes = ['field', 'IconFontField', parent::extraClass()];

        if (($key = array_search('icon', $classes)) !== false) {
            unset($classes[$key]);
        }

        return implode(' ', $classes);
    }
}
