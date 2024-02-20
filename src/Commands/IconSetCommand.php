<?php

namespace Goldfinch\IconField\Commands;

use Goldfinch\Taz\Console\GeneratorCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Question\ChoiceQuestion;

#[AsCommand(name: 'iconset')]
class IconSetCommand extends GeneratorCommand
{
    protected static $defaultName = 'iconset';

    protected $description = 'Add new icon set';

    protected $no_arguments = true;

    protected function execute($input, $output): int
    {
        $setName = $this->askClassNameQuestion('Name of the set? (eg: font_awesome, primary_set)', $input, $output);

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'What type of source this set is going to use?',
            ['font', 'dir', 'upload', 'json'],
        );
        $question->setErrorMessage('The selection %s is invalid.');
        $setType = $helper->ask($input, $output, $question);

        if ($setType == 'font') {
            $sourceExample = 'https://cdn.myicons.net/icons.min.css';
        } else if ($setType == 'dir') {
            $sourceExample = 'icons';
        } else if ($setType == 'upload') {
            $sourceExample = 'icons';
        }

        if ($setType == 'json') {
            $source = 'icon-' . $setName . '.json';
        } else {
            $source = $this->askStringQuestion('Specify the source for this set (eg: '.$sourceExample.')', $input, $output);
        }

        $setOptions = [
            'type' => $setType,
            'source' => $source,
        ];

        // find config
        $config = $this->findYamlConfigFileByName('app-icons');

        // create new config if not exists
        if (!$config) {

            $command = $this->getApplication()->find('make:config');
            $command->run(new ArrayInput([
                'name' => 'icons',
                '--plain' => true,
                '--after' => 'goldfinch/icon-field',
                '--nameprefix' => 'app-',
            ]), $output);

            $config = $this->findYamlConfigFileByName('app-icons');
        }

        // update config
        $this->updateYamlConfig(
            $config,
            'Goldfinch\IconField\Forms\IconField' . '.icons_sets.' . $setName,
            $setOptions,
        );

        $config = $this->findYamlConfigFileByName('app-icons');

        if ($setType == 'font' || $setType == 'dir' || $setType == 'json') {
            $fs = new Filesystem();

            if ($setType == 'json') {
                $schemaTemplate = 'schema-json.json';
            } else {
                $schemaTemplate = 'schema.json';
            }

            $fs->copy(
                BASE_PATH .
                    '/vendor/goldfinch/icon-field/components/' . $schemaTemplate,
                'app/_schema/icon-'.$setName.'.json',
            );
        }

        return Command::SUCCESS;
    }
}
