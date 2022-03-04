<?php
/* Icinga Web 2 | (c) 2022 Icinga Development Team | GPLv2+ */

namespace Icinga\Forms\Config\UserGroup;

use ipl\Html\Attributes;
use ipl\Html\Form;
use ipl\Html\FormElement\InputElement;
use ipl\Html\FormElement\SubmitElement;
use ipl\Html\HtmlElement;
use ipl\Web\Url;

/**
 * Form for adding one or more group members
 */
abstract class SimpleSearchField extends Form
{
    protected $defaultAttributes = [
        'class'                 => 'icinga-form icinga-controls search-field',
        'name'                  => 'search-field',
        'role'                  => 'search-field'
    ];

    /** @var string The term separator */
    const TERM_SEPARATOR = ',';

    /** @var string The search parameter */
    protected $searchParameter;

    /** @var Url The suggestion url */
    protected $suggestionUrl;

    /** @var string Submit label */
    protected $submitLabel;



    /**
     * Set the search parameter to use
     *
     * @param   string $name
     *
     * @return  $this
     */
    public function setSearchParameter($name)
    {
        $this->searchParameter = $name;

        return $this;
    }

    /**
     * Get the search parameter in use
     *
     * @return string
     */
    public function getSearchParameter()
    {
        return $this->searchParameter ?: 'q';
    }

    /**
     * Set the suggestion url
     *
     * @param   Url $url
     *
     * @return  $this
     */
    public function setSuggestionUrl(Url $url)
    {
        $this->suggestionUrl = $url;

        return $this;
    }

    /**
     * Get the suggestion url
     *
     * @return Url
     */
    public function getSuggestionUrl()
    {
        return $this->suggestionUrl;
    }

    /**
     * Set the submit label
     *
     * @param   string $label
     *
     * @return  $this
     */
    public function setSubmitLabel($label)
    {
        $this->submitLabel = $label;

        return $this;
    }

    /**
     * Get the submit label
     *
     * @return string
     */
    public function getSubmitLabel()
    {
        return $this->submitLabel ?? 'Submit';
    }


    public function assemble()
    {
        $filterInput = new InputElement(null, [
            'type'                  => 'text',
            'placeholder'           => 'Type to search',
            'class'                 => 'search',
            'id'                    => 'search-input',
            'autocomplete'          => 'off',
            'required'              => true,
            'data-no-auto-submit'   => true,
            'data-enrichment-type'  => 'terms',
            'data-term-separator'   => self::TERM_SEPARATOR,
            'data-term-mode'        => 'read-only',
            'data-term-direction'   => 'vertical',
            'data-data-input'       => '#data-input',
            'data-term-input'       => '#term-input',
            'data-term-container'   => '#term-container',
            'data-term-suggestions' => '#term-suggestions',
            'data-suggest-url'      => $this->getSuggestionUrl()
        ]);

        $dataInput = new InputElement('data', ['type' => 'hidden', 'id' => 'data-input']);

        $termInput = new InputElement('q', ['type' => 'hidden', 'id' => 'term-input']);
        $this->registerElement($termInput);

        $termContainer = new HtmlElement(
            'div',
            Attributes::create(['id' => 'term-container', 'class' => 'term-container'])
        );

        $termSuggestions = new HtmlElement(
            'div',
            Attributes::create(['id' => 'term-suggestions', 'class' => 'search-suggestions'])
        );

        $submitButton = new SubmitElement(
            'submit',
            ['label' => $this->getSubmitLabel(), 'class' => 'btn-primary']
        );

        $this->registerElement($submitButton);

        $this->add([
            HtmlElement::create(
                'div',
                ['class' => 'control-group'],
                [$filterInput, $termSuggestions, $dataInput, $termInput, $submitButton]
            ),
            $termContainer
        ]);
    }
}
