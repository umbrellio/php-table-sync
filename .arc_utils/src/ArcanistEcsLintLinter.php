<?php

final class ArcanistEcsLintLinter extends ArcanistExternalLinter
{
    private $config = 'easy-coding-standard.yml';

    public function getInfoName()
    {
        return 'ECS-Lint';
    }

    public function getInfoURI()
    {
        return 'https://github.com/Symplify/EasyCodingStandard';
    }

    public function getInfoDescription()
    {
        return pht(
            '%s is a tool to help keep your files clean and readable.',
            'ECS');
    }

    public function getLinterName()
    {
        return 'ECS';
    }

    public function getLinterConfigurationName()
    {
        return 'ecs';
    }

    public function getDefaultBinary()
    {
        return 'ecs';
    }

    public function getVersion()
    {
        list($stdout) = execx('%C --version', $this->getExecutableCommand());

        return explode(' ', $stdout)[1] ?? false;
    }

    public function getInstallInstructions()
    {
        return pht(
            'Install %s using `%s`.',
            'ECS',
            'composer require --dev symplify/easy-coding-standard');
    }

    public function getLinterConfigurationOptions()
    {
        $options = array(
            'config' => array(
                'type' => 'optional string',
                'help' => pht('A custom configuration file.'),
            ),
        );

        return $options + parent::getLinterConfigurationOptions();
    }

    public function setLinterConfigurationValue($key, $value)
    {
        switch ($key) {
            case 'config':
                $this->config = $value;
                return;
        }

        return parent::setLinterConfigurationValue($key, $value);
    }

    protected function getMandatoryFlags()
    {
        $fix = isset($_SERVER['FIX_FLAG']) ? ['--fix'] : [];
        return array_merge([
            'check',
            '--no-progress-bar',
            '--no-error-table',
            '--no-interaction',
            '--config=' . $this->config
        ], $fix);
    }

    protected function parseLinterOutput($path, $err, $stdout, $stderr)
    {

        if (strpos($stdout, '[WARNING]') !== false) {
            $severity = ArcanistLintSeverity::SEVERITY_WARNING;
        } elseif (strpos($stdout, '[ERROR]') !== false) {
            $severity = ArcanistLintSeverity::SEVERITY_ERROR;
        } else {
            return [];
        }

        return [
            id(new ArcanistLintMessage())
                ->setPath($path)
                ->setName($this->getLinterName())
                ->setDescription($stdout)
                ->setSeverity($severity)
        ];
    }
}
