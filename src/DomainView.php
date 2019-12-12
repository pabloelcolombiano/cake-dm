<?php


namespace CakeDomainManager;


use Cake\Core\Configure;
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

    /**
     * @param View $view
     * @return static
     */
    public static function init(View $view)
    {
        $domainViewManager = new static($view->getRequest(), $view->getResponse(), $view->getEventManager());
        $domainViewManager->masterClass = $view;
        $domainViewManager->defineType('View');

        return $domainViewManager;
    }

    /**
     * To insert in AppView.php or any View that uses the
     * CakePHP Domain Manager. This method overwrites the
     * way Cake loads the elements.
     *
     * Examples:
     *
     * $this->element('logged_in_user@User'):
     *      will load the element logged_in_user.ctp
     *      located in src/User/Template/Element/logged_in_user
     *
     * $this->element('seat_description@Planes/Seats'):
     *      will load the element seat_description.ctp
     *      located in src/Planes/Seats/Template/Element/seat_description
     *
     * $this->element('Bookings.invoice_description@Invoices'):
     *      will load the element invoice_description.ctp
     *      located in plugins/Bookings/src/Invoices/Template/Element/invoice_description
     *
     * @param string $name
     * @param bool $pluginCheck
     * @return false|string
     */
    protected function _getElementFileName($name, $pluginCheck = true)
    {
        return parent::_getElementFileName(
            $this->extractElementName($name),
            $pluginCheck
        );
    }

    /**
     * @param string $name
     * @return string
     */
    public function extractElementName(string $name): string
    {
        $cast = explode('@', $name);
        if (count($cast) === 2) {
            $template = $cast[0];
            $domain = $cast[1];
            $cast = explode('.', $template);
            if (count($cast) === 2) {
                $plugin = $cast[0];
                $template = $cast[1];
                $name = $this->rewind(true) . 'plugins' . DS . $plugin . DS . 'src' . DS . $domain . DS . 'Template' . DS . 'Element' . DS . $template;
            } elseif ($this->masterClass->getPlugin()) {
                $name = $this->rewind(true) . 'src' . DS . $domain . DS . 'Template' . DS . 'Element' . DS . $template;
            } else {
                $name = $this->rewind() . $domain . DS . 'Template' . DS . 'Element' . DS . $template;
            }
        }
        return $name;
    }

    /**
     * Calculates how many folders to rewind to catch the correct element
     * @param bool $toRoot
     * @return string
     */
    private function rewind(bool $toRoot = false)
    {
        // Rewind to APP
        $n = 2 + count(explode('/', $this->getControllerDomainLayer()));

        // Rewind to ROOT coming from a plugin
        if ($this->masterClass->getPlugin()) {
            $n += 3;
        } elseif ($toRoot) {
            $n += 1;
        }

        $res = '';
        for ($i = 0; $i < $n; $i++) {
            $res .= ".." . DS;
        }

        return $res;
    }

    /**
     * Reads in Configure the domain layer from which the controller
     * was initialy called. Views are not necessarily in the same domain layer
     * but templates and controllers should
     *
     * @return string
     */
    public function getControllerDomainLayer(): string
    {
        return Configure::readOrFail('DomainManager.controller_domain');
    }
}
