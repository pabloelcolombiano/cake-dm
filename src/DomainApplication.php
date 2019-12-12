<?php


namespace CakeDomainManager;


use Cake\Error\FatalErrorException;
use Cake\Http\BaseApplication;
use HaydenPierce\ClassFinder\ClassFinder;

/**
 * DomainApplication
 */
class DomainApplication
{
    /**
     * @var BaseApplication
     */
    private $application;

    public function __construct(BaseApplication $application)
    {
        $this->defineApplication($application);
    }

    /**
     * @param BaseApplication
     * @return static
     */
    public static function init(BaseApplication $application)
    {
        return new static($application);
    }

    /**
     * @param string $namespace
     * @param string $directory
     * @throws \ReflectionException
     */
    public function trackDuplicatedClassesInNamespace(string $namespace, string $directory) : self
    {
        $classes = ClassFinder::getClassesInNamespace($namespace, ClassFinder::RECURSIVE_MODE);

        $allPHPFiles = [];
        $this->getDirPHPContent($directory, $allPHPFiles, true);

        $classFiles = [];
        foreach ($classes as $class) {
            if (strpos($class, 'App\Test') === 0) {
                continue;
            }

            $reflector = new \ReflectionClass($class);
            $classFileName = $reflector->getFileName();
            if (!in_array($classFileName, $allPHPFiles)) {
                $this->throwFatalErrorException("The class $class with source $classFileName was not found in $directory");
            } else {
                $classFiles[] = $classFileName;
            }
        }

        $unassignedPHPFiles = array_diff($allPHPFiles, $classFiles);
        if (count($unassignedPHPFiles) > 0) {
            $unassignedPHPFiles = implode(', ', $unassignedPHPFiles);
            $this->throwFatalErrorException("The file(s) $unassignedPHPFiles in $directory point to a class with the same name as another class in the same namespace.");
        }
    }

    /**
     * @param string $directory
     * @return $this
     */
    public function trackDuplicatedPHPFilesInDirectory(string $directory)
    {
        $duplicatedPHPFiles = $this->findDuplicates(
            $this->getDirPHPContent($directory)
        );

        if (!empty($duplicatedPHPFiles)) {
            $duplicatedPHPFiles = implode(', ', $duplicatedPHPFiles);
            $this->throwFatalErrorException("The file(s) $duplicatedPHPFiles were found at several places in the $directory directory. This is critical when using the webrider/cake-dm package.");
        }

        return $this;
    }

    /**
     * @param string $dir
     * @param array $results
     * @return array
     */
    private function getDirPHPContent(string $dir, &$results = [], $withFullPath = false) : array
    {
        $files = scandir($dir);

        foreach($files as $key => $value){
            $path = realpath($dir.DS.$value);
            if (!is_dir($path)) {
                if (preg_match('/php$/', $value)) {
                    $results[] = $withFullPath ? $path : $value;
                }
            } elseif($value != "." && $value != "..") {
                $this->getDirPHPContent($path, $results, $withFullPath);
            }
        }

        return $results;
    }

    /**
     * @return BaseApplication
     */
    public function getApplication(): BaseApplication
    {
        return $this->application;
    }

    /**
     * @param BaseApplication $application
     */
    public function defineApplication(BaseApplication $application)
    {
        $this->application = $application;
    }

    /**
     * @param string $message
     */
    public function throwFatalErrorException(string $message)
    {
        throw new FatalErrorException($message);
    }

    /**
     * @param array $array
     * @return array
     */
    private function findDuplicates(array $array) : array
    {
        $dups = [];
        foreach(array_count_values($array) as $val => $c) {
            if ($c > 1) {
                $dups[] = $val;
            }
        }

        return $dups;
    }
}