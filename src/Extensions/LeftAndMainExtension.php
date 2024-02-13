<?php

namespace Goldfinch\Icon\Extensions;

use SilverStripe\Core\Extension;
use Goldfinch\Icon\Forms\IconField;
use SilverStripe\View\Requirements;
use Goldfinch\Icon\Forms\IconFontField;

class LeftAndMainExtension extends Extension
{
    public function init()
    {
        $sets = IconField::config()->get('icons_sets');

        $fonts = [];

        if ($sets) {
            foreach ($sets as $set) {
                if ($set['type'] == 'font' && isset($set['source'])) {
                    $fonts[] = $set['source'];
                }
            }
        }

        if ($fonts && is_array($fonts)) {
            foreach ($fonts as $include) {
                Requirements::css($include);
            }
        }
    }

    // public function init()
    // {
    //     $fonts = IconFontField::config()->get('icon_fonts');

    //     if ($fonts && is_array($fonts)) {
    //         foreach ($fonts as $include) {
    //             Requirements::css($include);
    //         }
    //     } elseif ($fonts && is_string($fonts)) {
    //         Requirements::css($fonts);
    //     }
    // }
}
