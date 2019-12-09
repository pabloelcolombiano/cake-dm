<?php


namespace CakeDomainManager;


use Cake\Controller\Controller;
use Cake\Core\Configure;

class DomainController
{
    /**
     * @var Controller
     */
    private $controller;

    /**
     * @var string
     */
    private $rootFolder;

    public function __construct(Controller $controller)
    {
        $this->defineController($controller);
    }

    public static function init(Controller $controller)
    {
         return new static($controller);
    }

    public function getLayer()
    {
        $reflector = new \ReflectionClass($this->getController());

        return $this->extractLayerFromFileName(
            $reflector->getFileName(),
            $this->getController()->getPlugin()
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
            return null;
        }

        if (strpos($fileName, DS . 'Controller' . DS . $this->getController()->getName() . 'Controller.php') !== false) {
            $layer = str_replace(
                DS . 'Controller' . DS . $this->getController()->getName() . 'Controller.php' ,
                '',
                $fileName
            );
        } else {
            return null;
        }

        return $layer;
    }

    public function setViewPaths()
    {
        $layer = $this->getLayer();
        if ($layer) {

            $templatePaths = Configure::read('App.paths.templates', []);
            $baseAppPath = 'Domain' . DS . Configure::read('App.namespace', 'App');

            array_unshift($templatePaths, APP . $baseAppPath . DS . 'Template' . DS);
            array_unshift($templatePaths,APP . $layer . DS . 'Template' . DS);

            if ($this->getController()->getPlugin()) {
                array_unshift($templatePaths, $this->getPluginFolder() . $baseAppPath . DS . 'Template' . DS);
                array_unshift($templatePaths, $this->getPluginFolder() . $layer . DS . 'Template' . DS);
            }

            Configure::write(
                'App.paths.templates',
                $templatePaths
            );
        }
    }

    /**
     * @return mixed
     */
    public function getController() : Controller
    {
        return $this->controller;
    }

    /**
     * @param mixed $controller
     */
    public function defineController($controller)
    {
        $this->controller = $controller;
    }

    public function getPluginFolder() : string
    {
        return ROOT . DS . 'plugins'. DS . $this->getController()->getPlugin() . DS . 'src' . DS;
    }
}
