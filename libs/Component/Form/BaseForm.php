<?php

namespace App\Component\Form;

use App\Component\Form\Renderer\ZhodRenderer;
use Nette\Application\UI\Form;

abstract class BaseForm extends Form
{
    protected $item;

    /** @var \Kdyby\Translation\Translator */
    private $translator;

    public function create()
    {
        $this->setRenderer(new ZhodRenderer);

        $this->_create();

        $this->addHidden('id');
        $this->addProtection('Security token has expired, please submit the form again');
    }

    abstract protected function _create();

    public function setItem($item)
    {
        $this->item = $item;

        $data       = [];
        $data['id'] = $this->item->getId();

        parent::setValues($data);

        $this->_loadItem();
    }

    protected function _loadItem()
    {
        $data       = [];
        foreach ($this->getVisibleComponents(false) as $comp)
            $data[$comp->getName()] = $this->item->{$comp->getName()};

        parent::setValues($data);
    }

    public function process($item)
    {
        $this->item = $item;

        $this->_process();
    }

    private function getVisibleComponents($write)
    {
        $return = [];
        foreach ($this->getComponents() as $comp) {
            switch (get_class($comp)) {
                case 'Nette\Forms\Controls\CsrfProtection':
                case 'Nette\Forms\Controls\SubmitButton':
                case 'Nette\Forms\Controls\HiddenField':
                    $continue = false;
                    break;
                default:
                    $continue = true;
                    break;
            }
            if ($write && $comp->isDisabled())
                $continue = false;
            if (!$continue)
                continue;

            $return[] = $comp;
        }

        return $return;
    }

    protected function _process()
    {
        foreach ($this->getVisibleComponents(true) as $comp)
            $this->item->{$comp->getName()} = $comp->getValue();
    }

    protected function range($from, $to)
    {
        $x = [];
        foreach (range($from, $to) as $v)
            $x[$v] = $v;

        return $x;
    }
}
