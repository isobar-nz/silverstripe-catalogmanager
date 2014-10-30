<?php

class CatalogDataObjectGridFieldDetailForm extends GridFieldDetailForm
{

}

class CatalogDataObjectGridFieldDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest
{

    private static $allowed_actions = array(
        'ItemEditForm'
    );

    function ItemEditForm()
    {
        $form = parent::ItemEditForm();

        if ($this->record->has_extension('CatalogPageExtension')
            || $this->record->has_extension('CatalogDataObjectExtension')
        ) {

            $actions = $form->Actions();

            if ($this->record->ID) {
                if ($this->record->isPublished()) {
                    $actions->push(
                        FormAction::create('doDisable', 'Disable')
                            ->setUseButtonTag(true)
                            ->addExtraClass('ss-ui-action-destructive')
                    );
                } else {
                    $actions->push(
                        FormAction::create('doEnable', 'Enable')
                            ->setUseButtonTag(true)
                            ->addExtraClass('ss-ui-action-constructive')
                            ->setAttribute('data-icon', 'accept')
                    );
                }

                if ($this->record->canCreate() && $this->record->stat('can_duplicate') == true) {
                    $actions->push(
                        FormAction::create('doDuplicate', 'Duplicate')
                            ->setUseButtonTag(true)
                            ->addExtraClass('ss-ui-action-constructive')
                            ->setAttribute('data-icon', 'accept')
                    );
                }

            }

            $form->setActions($actions);

        }

        $this->extend('updateItemEditForm', $form);

        return $form;
    }

    public function doEnable($data, $form)
    {
        $this->publish($data, $form);
        return $this->edit(Controller::curr()->getRequest());
    }

    public function doDisable($data, $form)
    {
        $this->unpublish($data, $form);
        return $this->edit(Controller::curr()->getRequest());
    }

    public function doSave($data, $form)
    {
        $currentStage = Versioned::current_stage();
        Versioned::reading_stage('Stage');
        $action = parent::doSave($data, $form);
        Versioned::reading_stage($currentStage);

        if ($this->record->isPublished()) {
            $this->publish($data, $form);
        }

        return $action;
    }

    public function doDelete($data, $form)
    {
        $currentStage = Versioned::current_stage();
        if ($this->record->isPublished()) {
            $this->unpublish($data, $form);
        }

        Versioned::reading_stage('Stage');
        $action = parent::doDelete($data, $form);
        Versioned::reading_stage($currentStage);

        return $action;
    }

    private function publish($data, $form)
    {
        $currentStage = Versioned::current_stage();
        Versioned::reading_stage('Stage');

        $class = $this->record->ClassName;
        $page = $class::get()->byID($this->record->ID);

        if ($page) {
            $page->doPublish();
            $form->sessionMessage($this->record->getTitle() . ' has been enabled.', 'good');
        } else {
            $form->sessionMessage('Something failed, please refresh your browser.', 'bad');
        }

        Versioned::reading_stage($currentStage);
    }

    private function unpublish($data, $form)
    {
        $currentStage = Versioned::current_stage();
        Versioned::reading_stage('Stage');
        $class = $this->record->ClassName;
        $page = $class::get()->byID($this->record->ID);

        if ($page) {
            $page->doUnpublish();
            $form->sessionMessage($this->record->getTitle() . ' has been disabled.', 'good');
        } else {
            $form->sessionMessage('Something failed, please refresh your browser.', 'bad');
        }

        Versioned::reading_stage($currentStage);
    }

    public function doDuplicate($data, $form)
    {
        $this->duplicate($data, $form);
        return $this->edit(Controller::curr()->getRequest());
    }

    private function duplicate($data, $form)
    {
        Versioned::reading_stage('Stage');

        $class = $this->record->ClassName;
        $object = $class::get()->byID($this->record->ID);
        $newObject = false;
        if ($object) {
            $object->Title = "Copy of " . $object->Title;
            $newObject = $object->duplicate();
            $form->sessionMessage($this->record->getTitle() . ' has been duplicated.', 'good');
        } else {
            $form->sessionMessage('Something failed, please refresh your browser.', 'bad');
        }

        Versioned::reading_stage('Live');

        if ($newObject) {
            Controller::curr()->redirect(Controller::curr()->Link() . '/EditForm/field/' . $this->record->ClassName .'/item/' . $newPage->ID);
        }
    }


}
