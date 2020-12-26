<?php

/**
 * PHPUnit wrapper with patch for >=v6.0
 * @link https://secure.phabricator.com/T12785
 */
final class PatchedPhpUnitTestEngine extends ArcanistUnitTestEngine {

    protected $enableCoverage;

    private $configFile;
    private $phpunitBinary = 'phpunit';
    private $testPaths;
    private $projectRoot;

    public function setProjectRoot($projectRoot)
    {
        $this->projectRoot = $projectRoot;
        return $this;
    }

    public function setTestPaths(array $paths)
    {
        $this->testPaths = $paths;
        return $this;
    }

    public function run() {
        $this->projectRoot = $this->projectRoot ?? $this->getWorkingCopy()->getProjectRoot();
        $this->testPaths = $this->testPaths ?? [$this->projectRoot . '/tests'];

        $this->loadConfig();

        $this->prepareConfigFile();
        $futures = array();
        $tmpfiles = array();
        foreach ($this->testPaths as $test_path) {
            if (!Filesystem::pathExists($test_path)) {
                continue;
            }
            $xml_tmp = new TempFile();
            $clover_tmp = null;
            $clover = null;
            if ($this->getEnableCoverage()) {
                $clover_tmp = new TempFile();
                $clover = csprintf('--coverage-clover %s', $clover_tmp);
            }

            $config = $this->configFile ? csprintf('-c %s', $this->configFile) : null;

            $stderr = '-d display_errors=stderr';

            $cmd = "{$this->phpunitBinary} $config $stderr --log-junit $xml_tmp $clover $test_path";

            $futures[$test_path] = $cmd;
            $tmpfiles[$test_path] = array(
                'xml' => $xml_tmp,
                'clover' => $clover_tmp,
            );
        }

        $results = array();
        foreach ($futures as $test => $future) {
            $output = [];
            exec($future, $output);
            list($err, $stdout, $stderr) = $output;

            $results[] = $this->parseTestResults(
                $test,
                $tmpfiles[$test]['xml'],
                $tmpfiles[$test]['clover'],
                $stderr);
        }

        return array_mergev($results);
    }

    private function loadConfig() {
        $this->setEnableCoverage(
            $this->getConfigurationManager()->getConfigFromAnySource('enable_coverage') !== "false"
        );
    }

    /**
     * Parse test results from phpunit json report.
     *
     * @param string $path Path to test
     * @param string $json_tmp Path to phpunit json report
     * @param string $clover_tmp Path to phpunit clover report
     * @param string $stderr Data written to stderr
     *
     * @return array
     */
    private function parseTestResults($path, $xml_tmp, $clover_tmp, $stderr) {
        $test_results = Filesystem::readFile($xml_tmp);
        return id(new PatchedPHPUnitArcanistUnitTestResultParser())
            ->setEnableCoverage($this->getEnableCoverage())
            ->setProjectRoot($this->projectRoot)
            ->setCoverageFile($clover_tmp)
            ->setAffectedTests($this->testPaths)
            ->setStderr($stderr)
            ->parseTestResults($path, $test_results);
    }

    /**
     * Get places to look for PHP Unit tests that cover a given file. For some
     * file "/a/b/c/X.php", we look in the same directory:
     *
     *  /a/b/c/
     *
     * We then look in all parent directories for a directory named "tests/"
     * (or "Tests/"):
     *
     *  /a/b/c/tests/
     *  /a/b/tests/
     *  /a/tests/
     *  /tests/
     *
     * We also try to replace each directory component with "tests/":
     *
     *  /a/b/tests/
     *  /a/tests/c/
     *  /tests/b/c/
     *
     * We also try to add "tests/" at each directory level:
     *
     *  /a/b/c/tests/
     *  /a/b/tests/c/
     *  /a/tests/b/c/
     *  /tests/a/b/c/
     *
     * This finds tests with a layout like:
     *
     *  docs/
     *  src/
     *  tests/
     *
     * ...or similar. This list will be further pruned by the caller; it is
     * intentionally filesystem-agnostic to be unit testable.
     *
     * @param   string        PHP file to locate test cases for.
     * @return  list<string>  List of directories to search for tests in.
     */
    public static function getSearchLocationsForTests($path) {
        $file = basename($path);
        $dir  = dirname($path);

        $test_dir_names = array('tests', 'Tests');

        $try_directories = array();

        // Try in the current directory.
        $try_directories[] = array($dir);

        // Try in a tests/ directory anywhere in the ancestry.
        foreach (Filesystem::walkToRoot($dir) as $parent_dir) {
            if ($parent_dir == '/') {
                // We'll restore this later.
                $parent_dir = '';
            }
            foreach ($test_dir_names as $test_dir_name) {
                $try_directories[] = array($parent_dir, $test_dir_name);
            }
        }

        // Try replacing each directory component with 'tests/'.
        $parts = trim($dir, DIRECTORY_SEPARATOR);
        $parts = explode(DIRECTORY_SEPARATOR, $parts);
        foreach (array_reverse(array_keys($parts)) as $key) {
            foreach ($test_dir_names as $test_dir_name) {
                $try = $parts;
                $try[$key] = $test_dir_name;
                array_unshift($try, '');
                $try_directories[] = $try;
            }
        }

        // Try adding 'tests/' at each level.
        foreach (array_reverse(array_keys($parts)) as $key) {
            foreach ($test_dir_names as $test_dir_name) {
                $try = $parts;
                $try[$key] = $test_dir_name.DIRECTORY_SEPARATOR.$try[$key];
                array_unshift($try, '');
                $try_directories[] = $try;
            }
        }

        $results = array();
        foreach ($try_directories as $parts) {
            $results[implode(DIRECTORY_SEPARATOR, $parts).DIRECTORY_SEPARATOR] = true;
        }

        return array_keys($results);
    }

    /**
     * Tries to find and update phpunit configuration file based on
     * `phpunit_config` option in `.arcconfig`.
     */
    private function prepareConfigFile() {
        $project_root = $this->projectRoot.DIRECTORY_SEPARATOR;
        $config = $this->getConfigurationManager()->getConfigFromAnySource('phpunit_config');

        if ($config) {
            if (Filesystem::pathExists($project_root.$config)) {
                $this->configFile = $project_root.$config;
            } else {
                throw new Exception(
                    pht(
                        'PHPUnit configuration file was not found in %s',
                        $project_root.$config));
            }
        }
        $bin = $this->getConfigurationManager()->getConfigFromAnySource(
            'unit.phpunit.binary');
        if ($bin) {
            if (Filesystem::binaryExists($bin)) {
                $this->phpunitBinary = $bin;
            } else {
                $this->phpunitBinary = Filesystem::resolvePath($bin, $project_root);
            }
        }
    }

}
