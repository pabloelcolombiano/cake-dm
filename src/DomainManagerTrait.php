<?php


namespace CakeDomainManager;


use Cake\Core\Configure;

trait DomainManagerTrait
{
    private $type;

    public $masterClass;

    public function getLayer() : string
    {
        $reflector = new \ReflectionClass($this->masterClass);

        return $this->extractLayerFromFileName(
            $reflector->getFileName(),
            $this->masterClass->getPlugin()
        );
    }

    public function extractLayerFromFileName(string $fileName, string $plugin = null)
    {
        $srcPath = $plugin ? $this->getPluginFolder() : APP;

        if (strpos($fileName, $srcPath) !== false) {
            $fileName = str_replace(
                $srcPath ,
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

    public function getPluginFolder() : string
    {
        return ROOT . DS . 'plugins'. DS . $this->masterClass->getPlugin() . DS . 'src' . DS;
    }

    public function defineType(string $type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }
}
