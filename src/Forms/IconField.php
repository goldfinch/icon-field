<?php

namespace Goldfinch\Icon\Forms;

use InvalidArgumentException;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\LiteralField;
use Symfony\Component\Finder\Finder;
use Goldfinch\Icon\ORM\FieldType\DBIcon;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\ORM\FieldType\DBHTMLText;
use Symfony\Component\Filesystem\Filesystem;

class IconField extends FormField
{
    protected $schemaDataType = 'IconField';

    protected $iconsSet = null;

    protected $iconsSetConfig = null;

    protected $iconsList = [];

    /**
     * @var HiddenField
     */
    protected $fieldData = null;

    /**
     * @var FormField
     */
    protected $fieldKey = null;

    /**
     * Gets field for the key selector
     *
     * @return FormField
     */
    public function getKeyField()
    {
        return $this->fieldKey;
    }

    /**
     * Gets field for the data input
     *
     * @return HiddenField
     */
    public function getDataField()
    {
        return $this->fieldData;
    }

    public function getPreviewField()
    {
        return LiteralField::create(
            $this->getName() . 'Icon',
            '<div class="ggp__preview" data-goldfinch-icon="preview"></div>',
        );
    }

    public function getCurrentIcons()
    {
        $cfg = $this->iconsSetConfig;
        $value = $this->getKeyField()->dataValue();
        if ($value) {
            $values = explode(',', $value);
        }
        $iconsList = $this->iconsList;

        $html = '';

        $count = 0;

        if (isset($values)) {

            $count = count($values);

            foreach ($values as $v) {
                $icon = $this->getIconByKey($v);
                $html .= '<li data-value="'.$v.'">' . $icon['template'] . '</li>';
            }
        }

        $return = DBHTMLText::create();
        $return->setValue('<ul data-count="'.$count.'">'.$html.'</ul>');

        return $return;
    }

    public function __construct($set, $name, $title = null, $value = '')
    {
        $this->setName($name);
        $this->fieldData = HiddenField::create("{$name}[Data]", 'Data');

        $this->fieldData->setAttribute('data-goldfinch-icon', 'data');

        $this->buildKeyField();

        Requirements::css('goldfinch/icon:client/dist/icon-styles.css');
        Requirements::javascript('goldfinch/icon:client/dist/icon.js');

        $this->initSetsRequirements();

        $this->setIconsSet($set);
        $this->setIconsList();

        parent::__construct($name, $title, $value);
    }

    public function getIconsConfigJSON()
    {
        return json_encode($this->iconsSetConfig);
    }

    public function getIconsListJSON()
    {
        return json_encode($this->iconsList);
    }

    public function getIconsList()
    {
        return ArrayList::create($this->iconsList);
    }

    private function setIconsList(): void
    {
        $cfg = $this->iconsSetConfig;

        /*
            $schemaList = [
                0 => [
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

            $schema = BASE_PATH . '/app/_schema/icon-' . $this->iconsSet . '.json';

            if ($fs->exists($schema)) {
                $content = file_get_contents($schema);
                $content = json_decode($content, true);

                if ($content && is_array($content) && count($content)) {

                    $schemaList = $content;

                    foreach ($schemaList as $k => $sl) {
                        if (!isset($sl['value']) || $sl['value'] == '') {
                            $sl['value'] = $k;
                        }
                        if (!isset($sl['template']) || $sl['template'] == '') {
                            $sl['template'] = $this->renderIconTemplate($sl);
                        }


                        $schemaList[$k] = $sl;
                    }
                }
            }

        } else if ($cfg['type'] == 'dir') {

            $sourcePath = '/' . $cfg['source'];

            $finder = new Finder();
            $files = $finder->in(PUBLIC_PATH . $sourcePath)->files();

            foreach ($files as $file) {

                $filename = $file->getFilename();
                $ex = explode('.', $filename);
                $item = [
                    'title' => '',
                    'value' => $ex[0],
                    'source' => $sourcePath . '/' . $filename,
                ];

                $item['template'] = $this->renderIconTemplate($item);
                $schemaList[] = $item;
            }

        } else if ($cfg['type'] == 'upload') {

            $targetFolder = File::get()->filter(['ClassName' => Folder::class, 'Name' => $cfg['source']])->first();

            if ($targetFolder) {

                $folder = File::get()->byID(1);

                if ($folder && $folder == Folder::class) {
                    foreach ($folder->myChildren() as $file) {

                        $item = [
                            'title' => $file->Title,
                            'value' => $file->ID,
                            'source' => $file->getURL(),
                        ];

                        $item['template'] = $this->renderIconTemplate($item);
                        $schemaList[] = $item;
                    }
                }
            } else {
                // specified folder in .yml is not found
            }

        } else if ($cfg['type'] == 'json') {

            $fs = new Filesystem;

            $schema = BASE_PATH . '/app/_schema/' . $cfg['source'];

            if ($fs->exists($schema)) {
                $content = file_get_contents($schema);
                $content = json_decode($content, true);

                if ($content && is_array($content) && count($content)) {

                    $schemaList = $content;

                    foreach ($schemaList as $k => $sl) {
                        if (!isset($sl['value']) || $sl['value'] == '') {
                            $sl['value'] = $k;
                        }
                        if (!isset($sl['template']) || $sl['template'] == '') {
                            $sl['template'] = $this->renderIconTemplate($sl);
                        }


                        $schemaList[$k] = $sl;
                    }
                }
            }

        }

        $this->iconsList = $schemaList;
    }

    private function renderIconTemplate($item): string
    {
        $cfg = $this->iconsSetConfig;

        $render = '';

        $adminSubDir = 'Goldfinch/Icon/Types/Admin/';

        if ($cfg['type'] == 'font') {

            $template = $adminSubDir . 'FontItem';

        } else if ($cfg['type'] == 'dir') {

            $template = $adminSubDir . 'DirItem';

        } else if ($cfg['type'] == 'upload') {

            $template = $adminSubDir . 'UploadItem';

        } else if ($cfg['type'] == 'json') {

            $template = $adminSubDir . 'JsonItem';

        }

        if (!isset($item['title']) || !$item['title']) {
            $item['title'] = $item['value'];
        }

        return $this->customise(ArrayData::create(['Icon' => $item]))->renderWith($template)->RAW();
    }

    private function setIconsSet($set): void
    {
        $this->iconsSet = $set;

        if ($sets = $this->config()->get('icons_sets')) {
            foreach ($sets as $type => $s) {
                if (isset($s['type']) && $set == $type) {
                    $this->iconsSetConfig = $s;
                    break;
                }
            }
        }
    }

    private function initSetsRequirements(): void
    {
        $sets = $this->config()->get('icons_sets');

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

    public function __clone()
    {
        $this->fieldData = clone $this->fieldData;
        $this->fieldKey = clone $this->fieldKey;
    }

    /**
     * Builds a new icon key field
     *
     * @return FormField
     */
    protected function buildKeyField()
    {
        $name = $this->getName();

        $keyValue = $this->fieldKey
            ? $this->fieldKey->dataValue()
            : null;

        $field = HiddenField::create("{$name}[Key]", 'Key');

        $field->setReadonly($this->isReadonly());
        $field->setDisabled($this->isDisabled());
        if ($keyValue) {
            $field->setValue($keyValue);
        }

        $field->setAttribute('data-goldfinch-icon', 'key');

        $this->fieldKey = $field;
        return $field;
    }

    public function getIconByKey($key)
    {
        $list = $this->iconsList;
        $item = null;

        foreach ($list as $icon) {
            if ($icon['value'] == $key) {
                $item = $icon;
                break;
            }
        }

        return $item;
    }

    public function setSubmittedValue($value, $data = null)
    {
        if (empty($value)) {
            $this->value = null;
            $this->fieldKey->setValue(null);
            $this->fieldData->setValue(null);
            return $this;
        }

        if (is_string($value)) {
            $set = $this->iconsSetConfig;
            $item = $this->getIconByKey($value);

            $value = [
                'Key' => $value,
                'Data' => [
                    'set' => [
                        'name' => $this->iconsSet,
                        'type' => $set['type'],
                        // 'source' => $set['source'],
                    ],
                    // 'title' => $item && isset($item['title']) ? $item['title'] : null,
                    // 'value' => $item && isset($item['value']) ? $item['value'] : null,
                    // 'source' => $item && isset($item['source']) ? $item['source'] : null,
                ],
            ];
        }

        // Update each field
        $this->fieldKey->setSubmittedValue($value['Key'], $value);
        $this->fieldData->setSubmittedValue($value['Data'], $value);

        // Get data value
        $this->value = $this->dataValue();
        return $this;
    }

    public function setValue($value, $data = null)
    {
        if (empty($value)) {
            $this->value = null;
            $this->fieldKey->setValue(null);
            $this->fieldData->setValue(null);
            return $this;
        }

        if ($value instanceof DBIcon) {
            $stock = [
                'Key' => $value->getKey(),
                'Data' => $value->getData(),
            ];
        } else {
            throw new InvalidArgumentException('Invalid icon format');
        }

        if (!isset($stock['Data']) || !$stock['Data']) {
            $set = $this->iconsSetConfig;
            $item = $this->getIconByKey($value->getKey());

            $stock = [
                'Key' => $value->getKey(),
                'Data' => [
                    'set' => [
                        'name' => $this->iconsSet,
                        'type' => $set['type'],
                        // 'source' => $set['source'],
                    ],
                    // 'title' => $item && isset($item['title']) ? $item['title'] : null,
                    // 'value' => $item && isset($item['value']) ? $item['value'] : null,
                    // 'source' => $item && isset($item['source']) ? $item['source'] : null,
                ],
            ];
        }

        // Save value
        $this->fieldKey->setValue($stock['Key']);
        $this->fieldData->setValue($stock['Data']);
        $this->value = $this->dataValue();

        return $this;
    }

    /**
     * Get value as DBIcon object useful for formatting the number
     *
     * @return DBIcon
     */
    protected function getDBIcon()
    {
        return DBIcon::create_field('Icon', [
            'Key' => $this->fieldKey->dataValue(),
            'Data' => $this->fieldData->dataValue(),
        ]);
    }

    public function dataValue()
    {
        // Non-localised
        return $this->getDBIcon()->getValue();
    }

    public function Value()
    {
        // Localised
        return $this->getDBIcon()->getValue()->Nice();
    }

    /**
     * @param DataObjectInterface|Object $dataObject
     */
    public function saveInto(DataObjectInterface $dataObject)
    {
        $fieldName = $this->getName();
        if ($dataObject->hasMethod("set$fieldName")) {
            $dataObject->$fieldName = $this->getDBIcon();
        } else {
            $keyField = "{$fieldName}Key";
            $dataField = "{$fieldName}Data";

            $dataObject->$keyField = $this->fieldKey->dataValue();

            if (
                $dataObject->$keyField &&
                $dataObject->$keyField != ''
            ) {
                $dataObject->$dataField = $this->fieldData->dataValue();
            } else {
                $dataObject->$dataField = null;
            }
        }
    }

    /**
     * Returns a readonly version of this field.
     */
    public function performReadonlyTransformation()
    {
        $clone = clone $this;
        $clone->setReadonly(true);
        return $clone;
    }

    public function setReadonly($bool)
    {
        parent::setReadonly($bool);

        $this->fieldData->setReadonly($bool);
        $this->fieldKey->setReadonly($bool);

        return $this;
    }

    public function setDisabled($bool)
    {
        parent::setDisabled($bool);

        $this->fieldData->setDisabled($bool);
        $this->fieldKey->setDisabled($bool);

        return $this;
    }

    public function iconHidePreview()
    {
        $this->addExtraClass('goldfinch-google-icon-hide-preview');

        return $this;
    }

    /**
     * Validate this field
     *
     * @param Validator $validator
     * @return bool
     */
    public function validate($validator)
    {
        // return $this->extendValidationResult($result, $validator);
    }

    public function setForm($form)
    {
        $this->fieldKey->setForm($form);
        $this->fieldData->setForm($form);
        return parent::setForm($form);
    }
}
