<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form_Plugin
 */

/**
 * Utility class used in publication edit forms.
 *
 * The methods of this class behaves as Smarty plugins.
 */
class Clip_Form_Util
{
    // state vars
    protected $alias;
    protected $tid;
    protected $id;
    protected $oldid;

    /**
     * Constructor.
     *
     * @param Zikula_Form_AbstractHandler $handler Reference to the form handler.
     */
    public function __construct(Zikula_Form_AbstractHandler &$handler)
    {
        $this->alias = $handler->getAlias();
        $this->tid   = $handler->getTid();
        $this->id    = $handler->getId();
        // non numeric autogenerated id
        $this->oldid = 'a';
    }

    /**
     * Getters.
     */
    public function getAlias()
    {
        return $this->alias;
    }

    public function getTid()
    {
        return $this->tid;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Form utilities.
     */
    public function get($params, Zikula_Form_View &$view)
    {
        $var = isset($params['var']) ? $params['var'] : 'id';

        return isset($this->$var) ? $this->$var : null;
    }

    public function set($params, Zikula_Form_View &$view)
    {
        if (!isset($params['value'])) {
            return;
        }

        $var = isset($params['var']) ? $params['var'] : 'id';

        if (!isset($this->$var)) {
            return;
        }

        $this->$var = $params['value'];
    }

    public function reset($params, Zikula_Form_View &$view)
    {
        $this->alias = $view->eventHandler->getAlias();
        $this->tid   = $view->eventHandler->getTid();
        $this->id    = $view->eventHandler->getId();
    }

    public function newId($params, Zikula_Form_View &$view)
    {
        if (is_numeric($this->id)) {
            $this->id = $this->oldid;
        }

        $this->oldid = ++$this->id;
    }
}