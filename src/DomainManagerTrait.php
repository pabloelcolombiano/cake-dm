<?php


namespace CakeDomainManager;


use ReflectionClass;

/**
 * DomainManagerTrait
 *
 * Methods that are useful for both the DomainController and DomainView
 *
 */
trait DomainManagerTrait
{
    /**
     * In DomainController, the associated controller
     * In DomainView, the associated View
     */
    public $masterClass;

    /**
     * @var string
     */
    private $type;

    /**
     * Get the domain layer in which the masterClass is located
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getDomainLayer(): string
    {
        $reflector = new ReflectionClass($this->masterClass);

        return $this->extractDomainLayerFromFileName(
            $reflector->getFileName(),
            $this->masterClass->getPlugin()
        );
    }

    /**
     * Parses the class name of the master class to determine the
     * domain layer of the master class (controller or view)
     *
     * @param string $fileName
     * @param string|null $plugin
     * @return mixed|string
     */
    public function extractDomainLayerFromFileName(string $fileName, string $plugin = null)
    {
        $srcPath = $plugin ? $this->getPluginFolder() : APP;

        if (strpos($fileName, $srcPath) !== false) {
            $fileName = str_replace(
                $srcPath,
                '',
                $fileName
            );
        } else {
            return '';
        }

        $arr = explode(DS . $this->getType() . DS, $fileName);

        if (count($arr) === 2) {
            $layer = $arr[0];
        } else {
            return '';
        }

        return $layer;
    }

    /**
     * @return string
     */
    private function getPluginFolder(): string
    {
        return ROOT . DS . 'plugins' . DS . $this->masterClass->getPlugin() . DS . 'src' . DS;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function defineType(string $type)
    {
        $this->type = $type;
    }
}
