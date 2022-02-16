<?php declare(strict_types=1);

/**
 * Class ilSrConfigForm is responsible for the configuration form.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrConfigForm extends ilSrAbstractForm
{
    /**
     * @inheritDoc
     */
    protected function validateFormData(array $form_data) : bool
    {
        // the submitted form_data is always valid, as it's
        // possible all inputs were unchecked or removed.
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function handleFormData(array $form_data) : void
    {
        foreach ($form_data as $identifier => $value) {
            // try to find an existing database entry for current
            // $identifier or create a new instance.
            $config = ilSrConfig::find($identifier) ?? new ilSrConfig();
            $config
                // this may be redundant, but more performant than if-else
                ->setIdentifier($identifier)
                ->setValue($value)
                ->store()
            ;
        }
    }
}