<?php

namespace Goldfinch\Icon\ORM\FieldType;

use SilverStripe\ORM\DB;
use SilverStripe\Core\Path;
use SilverStripe\Control\Director;
use Goldfinch\Icon\Forms\IconFileField;
use Goldfinch\Icon\Forms\IconFontField;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Core\Manifest\ModuleResourceLoader;

class DBIcon extends DBField
{
    private static $casting = [
        'URL' => 'HTMLFragment',
        'IMG' => 'HTMLFragment',
        'SVG' => 'HTMLFragment'
    ];

    public function requireField()
    {
        DB::require_field($this->tableName, $this->name, 'Varchar(255)');
    }

    public function forTemplate()
    {
        return $this->getTag();
    }

    public function getTag()
    {
        $url = $this->URL() ?? '';

        if (substr($url, strlen($url) - 4) === '.svg') {
            return $this->SVG();
        } else {
            return $this->IMG();
        }
    }

    public function URL()
    {
        return $this->getValue();
    }

    public function IMG()
    {
        $url = ModuleResourceLoader::singleton()->resolveURL($this->URL());

        return '<img class="icon" src="'.$url.'" />';
    }

    public function SVG()
    {
        $url = $this->URL() ?? '';

        if (substr($url, strlen($url) - 4) !== '.svg') {
            user_error('Deprecation notice: Direct access to $Icon.SVG in templates is deprecated, please use $Icon', E_USER_WARNING);
        }

        $filePath = Path::join(
            Director::baseFolder(),
            $url
        );

        if (!file_exists($filePath)) {
            return false;
        }

        $svg = file_get_contents($filePath);
        return '<span class="icon svg">'.$svg.'</span>';
    }

    public function scaffoldFormField($title = null, $params = null)
    {
        // return IconFontField::create($this->name, $title);
        return IconFileField::create($this->name, $title);
    }
}
