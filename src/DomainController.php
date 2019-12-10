<?php


namespace CakeDomainManager;


use Cake\Controller\Controller;
use Cake\Core\Configure;

/**
 * DomainController
 *
 * @property Controller $masterClass
 */
class DomainController
{
    use DomainManagerTrait;

    public function __construct(Controller $controller)
    {
        $this->masterClass = $controller;
        $this->defineType('Controller');
        $this->defineControllerDomain();
    }

    public static function init(Controller $controller)
    {
         return new static($controller);
    }

    public function setViewPaths()
    {
        $layer = $this->getLayer();

        $templatePaths = Configure::read('App.paths.templates', []);

        $baseAppPath = 'Domain' . DS . Configure::read('App.namespace', 'App');

        array_unshift($templatePaths, APP . $baseAppPath . DS . 'Template' . DS);

        if ($layer) {
            array_unshift($templatePaths, APP . $layer . DS . 'Template' . DS);
        }

        if ($this->masterClass->getPlugin()) {
            array_unshift($templatePaths, $this->getPluginFolder() . 'Domain' . DS . 'Plugin' . DS . 'Template' . DS);
            if ($layer) {
                array_unshift($templatePaths, $this->getPluginFolder() . $layer . DS . 'Template' . DS);
            }
        }
 
        Configure::write(
            'App.paths.templates',
            $templatePaths
        );
    }

    public function defineControllerDomain()
    {
        Configure::write('DomainManager.domain', $this->getLayer());
    }
}
