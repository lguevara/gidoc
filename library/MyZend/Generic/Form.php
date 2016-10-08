<?php
/*
 * Controlador Genérico 
 */

abstract class MyZend_Generic_Form extends Zend_Form
{
/**
     * Disables all elements not in the excludeitems list.
     *
     * @param array - the elements to exclude from being disabled
     */
    public function disable(array $excludeitems = NULL)
    {
        $subforms = $this->getSubForms();
        foreach ($subforms as $subform)
        {
            if (is_null($excludeitems) || !in_array($subform->getName(), $excludeitems))
            {
                $subform->disable($excludeitems);
            }
        }

        $elements = $this->getElements();
        foreach ($elements as $element)
        {
            if ($element->getType() != 'Zend_Form_Element_Hidden'
                && (is_null($excludeitems) || !in_array($element->getName(), $excludeitems)))
            {
                $element->setAttrib('disabled', 'disabled');
            }
        }
    }

    /**
     * Removes the disabled status from all elements
     *
     */
    public function enable()
    {
        $subforms = $this->getSubForms();
        foreach ($subforms as $subform)
        {
            $subform->enable();
        }

        $elements = $this->getElements();
        foreach ($elements as $element)
        {
            $attribs = $element->getAttribs();
            unset($attribs['disabled']);
            $element->setAttribs($attribs);
        }
    }

    /*
     * Gets the values from all forms.
     *
     * @return array
     */
    public function getValues($suppressarraynotation = FALSE)
    {
        $values = parent::getValues($suppressarraynotation);

        $subforms = $this->getSubForms();
        foreach ($subforms as $subform)
        {
            $values = array_merge($values, $subform->getValues());
        }
        return $values;
    }

}