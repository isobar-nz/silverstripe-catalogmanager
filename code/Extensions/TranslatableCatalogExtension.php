<?php

/**
 * Class TranslatableCatalogExtension
 */
class TranslatableCatalogExtension extends Extension
{

    /**
     * @return mixed
     */
    public function language()
    {
        $request = $this->owner->getRequest();
        $locale = $request->postVar('Locale');
        $this->owner->Locale = $locale;
        return $this->owner->redirect($this->owner->Link("?locale=$locale"));
    }

    /**
     * @param $locale
     * @return mixed
     */
    public function getLanguageDropdownField($locale)
    {
        $locale = Translatable::get_current_locale();
        $translation = _t('CMSMain.LANGUAGEDROPDOWNLABEL', 'Language');
        $field = LanguageDropdownField::create(
            'Locale',
            $translation,
            array(),
            'SiteTree',
            'Locale-English',
            singleton('SiteTree')
        );
        $field->setValue($locale);
        return $field;
    }

    /**
     * @return mixed
     */
    public function getLanguageField()
    {
        $locale = Translatable::get_current_locale();

        if ($member = Member::currentUser()) {
            if (Permission::checkMember($member, 'VIEW_LANGS')) {
                return $this->getLanguageDropdownField($locale);
            }
        }

        return LiteralField::create(
            'Locale',
            i18n::get_locale_name($locale)
        );

    }

    /**
     * @return mixed
     */
    public function getLanguageSelectorForm()
    {
        $fields = FieldList::create($this->getLanguageField());
        return Form::create(
            $this->owner,
            'LangForm',
            $fields,
            FieldList::create(FormAction::create(
                'language',
                'Go'
            ))
        );
    }
}
