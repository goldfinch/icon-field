<?php

namespace Goldfinch\Icon\Forms;

use DirectoryIterator;
use SilverStripe\Core\Path;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\FormField;
use SilverStripe\Control\Director;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Core\Manifest\ModuleResourceLoader;

class IconFileField extends OptionsetField
{
    private static $folder_name;

    public function __construct($name, $title = null, $sourceFolder = null)
    {
        if ($sourceFolder) {
            $this->setFolderName($sourceFolder);
        }
        parent::__construct($name, $title, []);

        $this->setSourceIcons();
    }

    public function getFolderName()
    {
        if (is_null(self::$folder_name)) {
          self::$folder_name = self::config()->get('icon_folder');
        }
        return self::$folder_name;
    }

    public function setFolderName($folder_name)
    {
        self::$folder_name = $folder_name;
        return $this;
    }

    public function setSourceIcons()
    {
        $icons = [];
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'];
        $relative_folder_path = $this->getFolderName();
        $absolute_folder_path = $this->getAbsolutePathFromRelative($relative_folder_path);


        // Scan each directory for files
        if (file_exists($absolute_folder_path)) {
            $directory = new DirectoryIterator($absolute_folder_path);
            foreach ($directory as $fileinfo) {
                if ($fileinfo->isFile()) {
                    $extension = strtolower(pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION));

                    // Only add to our available icons if it's an extension we're after
                    if (in_array($extension, $extensions)) {
                        $value = Path::join($relative_folder_path, $fileinfo->getFilename());
                        $title = $fileinfo->getFilename();
                        $icons[$value] = $title;
                    }
                }
            }
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

        // Add a clear option
        $options[] = ArrayData::create([
            'ID' => 'none',
            'Name' => $this->name,
            'Value' => '',
            'Title' => '',
            'isChecked' => (!$this->value || $this->value == '')
        ]);

        if ($source) {
            foreach ($source as $value => $title) {
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

        $this->setTemplate('IconFileField');

        return FormField::Field($properties);
    }

    public function extraClass()
    {
        $classes = ['field', 'IconFileField', parent::extraClass()];

        if (($key = array_search('icon', $classes)) !== false) {
            unset($classes[$key]);
        }

        return implode(' ', $classes);
    }
}
