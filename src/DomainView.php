<?php


namespace CakeDomainManager;


use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\View\View;

/**
 * DomainView
 *
 * @property View $masterClass
 */
class DomainView extends View
{
    use DomainManagerTrait;

    /**
     * @var View
     */
    private $view;

    public static function init(View $view)
    {
        $domainViewManager = new static($view->getRequest(), $view->getResponse(), $view->getEventManager());
        $domainViewManager->masterClass = $view;
        $domainViewManager->defineType('View');

        return $domainViewManager;
    }

    protected function _getElementFileName($name, $pluginCheck = true)
    {
        return parent::_getElementFileName(
            $this->extractElementName($name),
            $pluginCheck
        );
    }

    public function extractElementName(string $name) : string
    {
        $cast = explode('@', $name);
        if (count($cast) === 2) {
            $template = $cast[0];
            $domain = $cast[1];
            $cast = explode('.', $template);
            if (count($cast) === 2) {
                $plugin = $cast[0];
                $template = $cast[1];
                $name = $this->rewind(true) . 'plugins' . DS . $plugin . DS . 'src' . DS . "Domain" . DS . $domain . DS . 'Template' . DS . 'Element' . DS . $template;
            } elseif ($this->masterClass->getPlugin()) {
                $name = $this->rewind(true) . 'src' . DS . "Domain" . DS . $domain . DS . 'Template' . DS . 'Element' . DS . $template;
            } else {
                $name = $this->rewind() . $domain . DS . 'Template' . DS . 'Element' . DS . $template;
            }
        }

        return $name;
    }

    public function getControllerDomain() : string
    {
        return Configure::readOrFail('DomainManager.domain');
    }

    private function rewind(bool $toRoot = false)
    {
        $n = count(explode('/', $this->getControllerDomain())) + 1;
        if ($toRoot) {
            $n += 2;
        }
        if ($this->masterClass->getPlugin()) {
            $n += 2;
        }

        $res = '';
        for ($i = 0; $i < $n; $i++) {
            $res .=  ".." . DS;
        }

        return $res;
    }
}
