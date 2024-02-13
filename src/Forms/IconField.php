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
use Symfony\Component\Filesystem\Filesystem;
use SilverStripe\Core\Manifest\ModuleResourceLoader;

class IconField extends OptionsetField
{
    private static $iconsSet;

    private static $iconsSetConfig;

    public function __construct($name, $set, $title = null)
    {
        $this->setIconsSet($set);

        parent::__construct($name, $title, []);

        $this->setIconsSource();
    }

    public function setIconsSet($set)
    {
        self::$iconsSet = $set;

        if ($sets = self::config()->get('icons_sets')) {
            foreach ($sets as $type => $s) {
                if (isset($s['type']) && $set == $type) {
                    self::$iconsSetConfig = $s;
                    break;
                }
            }
        }

        return $this;
    }

    public function setIconsSource()
    {
        $cfg = self::$iconsSetConfig;

        if ($cfg['type'] == 'font') {

            $fs = new Filesystem;

            $schema = BASE_PATH . '/app/_schema/icon-' . self::$iconsSet . '.json';

            if ($fs->exists($schema)) {
                $content = file_get_contents($schema);
                $content = json_decode($content, true);

                if ($content && is_array($content) && count($content)) {

                    $this->source = $content;
                }
            }


        } else if ($cfg['type'] == 'dir') {
            //
        } else if ($cfg['type'] == 'upload') {
            //
        }
    }

    public function getIconsConfig()
    {
        return self::$iconsSetConfig;
    }

    public function getIconsConfigJSON()
    {
        return json_encode(self::$iconsSetConfig);
    }

    public function getSourceJSON()
    {
        return json_encode($this->source);
    }

    // public function setIconsSource()
    // {
    //     $icons = [];
    //     $extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'];
    //     $relative_folder_path = $this->getFolderName();
    //     $absolute_folder_path = $this->getAbsolutePathFromRelative(
    //         $relative_folder_path,
    //     );

    //     // Scan each directory for files
    //     if (file_exists($absolute_folder_path)) {
    //         $directory = new DirectoryIterator($absolute_folder_path);
    //         foreach ($directory as $fileinfo) {
    //             if ($fileinfo->isFile()) {
    //                 $extension = strtolower(
    //                     pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION),
    //                 );

    //                 // Only add to our available icons if it's an extension we're after
    //                 if (in_array($extension, $extensions)) {
    //                     $value = Path::join(
    //                         $relative_folder_path,
    //                         $fileinfo->getFilename(),
    //                     );
    //                     $title = $fileinfo->getFilename();
    //                     $icons[$value] = $title;
    //                 }
    //             }
    //         }
    //     }

    //     $this->source = $icons;
    //     return $this;
    // }

    // public function getAbsolutePathFromRelative($relative_path)
    // {
    //     return Path::join(
    //         Director::publicDir()
    //             ? Director::publicFolder()
    //             : Director::baseFolder(),
    //         ModuleResourceLoader::singleton()->resolveURL($relative_path),
    //     );
    // }

    // public function getFullRelativePath($path)
    // {
    //     return ModuleResourceLoader::singleton()->resolveURL($path);
    // }

    // public function SVG($path)
    // {
    //     $filePath = $this->getAbsolutePathFromRelative($path);
    //     $filePath = current(explode('?m', $filePath));

    //     $svg = file_get_contents($filePath);

    //     $html = DBHTMLText::create();
    //     $html->setValue($svg);
    //     return $html;
    // }

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
            'isChecked' => !$this->value || $this->value == '',
        ]);

        if ($source) {
            foreach ($source as $key => $v) {
                $value = $key;
                $title = isset($v['title']) && $v['title'];

                $itemID =
                    $this->ID() .
                    '_' .
                    preg_replace('/[^a-zA-Z0-9]/', '', $value);
                $options[] = ArrayData::create([
                    'ID' => $itemID,
                    'Name' => $this->name,
                    'Value' => $value,
                    'Title' => $title,
                    'isChecked' => $value == $this->value,
                ]);
            }
        }

        $properties = array_merge($properties, [
            'Options' => ArrayList::create($options),
        ]);

        $this->setTemplate('IconField');

        return FormField::Field($properties);
    }

    // public function extraClass()
    // {
    //     $classes = ['field', 'IconFileField', parent::extraClass()];

    //     if (($key = array_search('icon', $classes)) !== false) {
    //         unset($classes[$key]);
    //     }

    //     return implode(' ', $classes);
    // }
}
