<?php

namespace App\AdminModule\Presenters;

use App\Model\Factory\EntityFactory;
use Nette;

abstract class AdminFormPresenter extends AdminPresenter
{
    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    public function actionDefault()
    {
        $this->template->all_items = $this->repo->repository()->findAll();
    }

    public function renderDetail($id)
    {
        if ($id) {
            $this->template->item = $this->repo->repository()->findOneById($id);
        } else {
            $this->template->item = $this->entityFactory->create($this->getEntityName());
        }

        if (is_null($this->template->item)) {
            throw new \Nette\Application\BadRequestException($this->getEntityName() . '_not_found');
        }

        $this['editItemForm']->setItem($this->template->item);

        $this->template->class = $this->getEntityName();
    }

    public function createComponentEditItemForm()
    {
        $formLabel = $this->getFormName();
        $form      = new $formLabel($this, 'editItemForm');
        $form->create();
        $form->addSubmit('send', 'Odeslat')
            ->onClick[] = [$this, 'doItemForm'];

        return $form;
    }

    public function doItemForm(Nette\Forms\Controls\SubmitButton $button, $redirectDestination = 'default', $redirectParameters = [])
    {
        $data    = $button->getForm()->getValues();
        $id      = $data['id'];
        $item    = $this->repo->repository()->findOneById($id);
        $newItem = false;
        if (is_null($item)) {
            $item    = $this->entityFactory->create($this->getEntityName());
            $newItem = true;
        }
        try {
            $button->getForm()->process($item);
        } catch (Exception $e) {
            $this->flashMessage($e->getMessage(), 'danger');

            return;
        }
        try {
            $this->repo->repository()->save($item);
            if ($newItem) {
                $this->flashMessage('item_added');
            } else {
                $this->flashMessage('item_edited');
            }
        } catch (Exception $e) {
            $this->flashMessage($e->getMessage(), 'danger');
        }
        $this->redirect($redirectDestination, $redirectParameters);
    }

    public function injectEntityFactory(EntityFactory $factory)
    {
        $this->entityFactory = $factory;
    }

    abstract protected function getEntityName();
    abstract protected function getFormName();
}
