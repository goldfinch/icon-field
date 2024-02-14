<?php

namespace Goldfinch\Icon\Forms;

use DirectoryIterator;
use SilverStripe\Core\Path;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\FormField;
use SilverStripe\Control\Director;
use SilverStripe\View\Requirements;
use Symfony\Component\Finder\Finder;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use Symfony\Component\Filesystem\Filesystem;
use SilverStripe\Core\Manifest\ModuleResourceLoader;

class IconField extends OptionsetField
{
    private static $iconsSet;
    private static $iconsList;

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

    public function getCurrentIcons()
    {
        $cfg = self::$iconsSetConfig;
        $values = explode(',', self::Value());
        $source = $this->iconsList;

        $html = '';

        if ($cfg['type'] == 'font') {

            //

        } else if ($cfg['type'] == 'dir') {

            //

        } else if ($cfg['type'] == 'upload') {

            foreach ($values as $v) {

                $item = array_filter($source, function($i) use ($v) {
                    return $i['Value'] == $v;
                });

                if ($item && count($item)) {
                    $item = current($item);

                    $html .= '<li>' . $item['Template'] . '</li>';
                }
            }

        } else if ($cfg['type'] == 'json') {

            //

        }

        $return = DBHTMLText::create();
        $return->setValue('<ul>'.$html.'</ul>');

        return $return;
    }

    public function setIconsSource()
    {
        $cfg = self::$iconsSetConfig;

        /*
            $schemaList = [
                'value-icon' => [
                    'title' => '', // optional
                    'value' => 'value-icon-prior', // optional (used prior the key)
                    'source' => '', // for display purpose (can be a full link, filename with extension etc.)
                    'template' => '', // added at the backend (not for customizations)
                ],
            ];
        */
        $schemaList = [];

        if ($cfg['type'] == 'font') {

            $fs = new Filesystem;

            $schema = BASE_PATH . '/app/_schema/icon-' . self::$iconsSet . '.json';

            if ($fs->exists($schema)) {
                $content = file_get_contents($schema);
                $content = json_decode($content, true);

                if ($content && is_array($content) && count($content)) {

                    $schemaList = $content;
                }
            }

        } else if ($cfg['type'] == 'dir') {

            $finder = new Finder();
            $files = $finder->in(PUBLIC_PATH . $cfg['source'])->files();

            foreach ($files as $file) {

                $filename = $file->getFilename();
                $ex = explode('.', $filename);
                $schemaList[$ex[0]] = [
                    'Title' => '',
                    'Source' => $filename
                ];
            }

        } else if ($cfg['type'] == 'upload') {

            $targetFolder = File::get()->filter(['ClassName' => Folder::class, 'Name' => $cfg['source']])->first();

            if ($targetFolder) {

                $folder = File::get()->byID(1);

                if ($folder && $folder == Folder::class) {
                    foreach ($folder->myChildren() as $file) {

                        $item = [
                            'Title' => $file->Title,
                            'Value' => $file->ID,
                            'Source' => $file->getURL(),
                        ];

                        $item['Template'] = $this->renderIconTemplate($item);

                        $schemaList[] = $item;
                    }
                }
            } else {
                // specified folder in .yml is not found
            }

        } else if ($cfg['type'] == 'json') {

            //

        }

        $this->iconsList = $schemaList;
        $this->source = ArrayList::create($schemaList)->map('Value', 'Title')->toArray();
    }

    public function renderIconTemplate($item)
    {
        $cfg = self::$iconsSetConfig;

        $render = '';

        if ($cfg['type'] == 'font') {

            //

        } else if ($cfg['type'] == 'dir') {

            //

        } else if ($cfg['type'] == 'upload') {

            $render = $this->customise(ArrayData::create($item))->renderWith('Goldfinch/Icon/Types/Admin/UploadItem')->RAW();
        } else if ($cfg['type'] == 'json') {

            //

        }

        return $render;
    }

    public function getIconsConfig()
    {
        return ArrayData::create(self::$iconsSetConfig);
    }

    public function getIconsConfigJSON()
    {
        return json_encode(self::$iconsSetConfig);
    }

    public function getSourceJSON()
    {
        return json_encode($this->iconsList);
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

    // ! could be useless
    public function Field($properties = [])
    {
        Requirements::css('goldfinch/icon:client/dist/icon-styles.css');
        Requirements::javascript('goldfinch/icon:client/dist/icon.js');
        $source = $this->iconsList;
        $options = new ArrayList;

        // Add a clear option
        $options->push(ArrayData::create([
            'ID' => 'none',
            'Name' => $this->name,
            'Value' => '',
            'Title' => '',
            'isChecked' => !$this->value || $this->value == '',
        ]));

        if ($source) {
            foreach ($source as $key => $v) {
                $value = isset($v['value']) ? $v['value'] : $key;
                $title = isset($v['title']) && $v['title'];

                $itemID =
                    $this->ID() .
                    '_' .
                    preg_replace('/[^a-zA-Z0-9]/', '', $value);
                $options->push(ArrayData::create([
                    'ID' => $itemID,
                    'Name' => $this->name,
                    'Value' => $value,
                    'Title' => $title,
                    'isChecked' => $value == $this->Value,
                ]));
            }
        }

        $properties = array_merge($properties, [
            'Options' => $options->map('Value', 'Title'),
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
