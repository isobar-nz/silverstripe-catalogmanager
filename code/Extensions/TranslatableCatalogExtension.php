<?php

/**
 * Class TranslatableCatalogExtension
 */
class TranslatableCatalogExtension extends DataExtension
{


    /**
     * @param $form
     */
    function updateImportForm(&$form)
    {
        $form = $this->LangForm();

    }

    /**
     * Returns a form with all languages with languages already used appearing first.
     *
     * @return Form
     */
    function LangForm()
    {
        $member = Member::currentUser(); //check to see if the current user can switch langs or not
        if (Permission::checkMember($member, 'VIEW_LANGS')) {
            $field = new LanguageDropdownField(
                'Locale',
                _t('CMSMain.LANGUAGEDROPDOWNLABEL', 'Language'),
                array(),
                'SiteTree',
                'Locale-English',
                singleton('SiteTree')
            );
            $field->setValue(Translatable::get_current_locale());
        } else {
            // user doesn't have permission to switch langs
            // so just show a string displaying current language
            $field = new LiteralField(
                'Locale',
                i18n::get_locale_name(Translatable::get_current_locale())
            );
        }

        $form = new Form(
            $this->owner,
            'LangForm',
            new FieldList(
                $field
            ),
            new FieldList(
                new FormAction('selectlang', _t('CMSMain_left.GO', 'Go'))
            )
        );
        $form->unsetValidator();
        $form->addExtraClass('nostyle');

        return $form;
    }


    function updateExtraTreeTools(&$html)
    {
        $locale = $this->owner->Locale ? $this->owner->Locale : Translatable::get_current_locale();
        $html = $this->LangForm()->forTemplate() . $html;
    }

}
