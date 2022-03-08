<?php

/* Icinga DB Web | (c) 2022 Icinga GmbH | GPLv2 */

namespace Icinga\Data;

use ipl\Html\Attributes;
use ipl\Html\BaseHtmlElement;
use ipl\Html\FormattedString;
use ipl\Html\FormElement\ButtonElement;
use ipl\Html\FormElement\InputElement;
use ipl\Html\HtmlElement;
use ipl\Html\Text;
use Psr\Http\Message\ServerRequestInterface;
use Traversable;

abstract class SimpleSuggestions extends BaseHtmlElement
{
    const DEFAULT_LIMIT = 10;

    protected $tag = 'ul';

    /** @var string The given input for search */
    protected $searchTerm;

    /** @var Traversable Fetched data for given input */
    protected $data;

    /** @var string Default first suggestion in the suggestion list */
    protected $default;

    /**
     * @param $searchTerm string Set the search term
     *
     * @return $this
     */
    public function setSearchTerm($searchTerm)
    {
        $this->searchTerm = $searchTerm;

        return $this;
    }

    /** Set the fetched data
     *
     * @param $data \Generator
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /** Set the default suggestion
     *
     * @param $default string
     *
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /** The given input for search
     *
     * @param $searchTerm string
     *
     * @param $exclude array Already added terms to exclude in suggestion list
     *
     * @return mixed
     */
    abstract protected function fetchSuggestions($searchTerm, $exclude);

    protected function assembleDefault()
    {
        if ($this->default === null) {
            return;
        }

        $attributes = [
            'type'          => 'button',
            'tabindex'      => -1,
            'data-label'    => $this->default,
            'data-type'     => 'value',
            'value'         => $this->default,
            'class'         => 'value'
        ];

        $button = new ButtonElement(null, $attributes);
        $button->addHtml(FormattedString::create(
            t('Add %s'),
            new HtmlElement('em', null, Text::create($this->default))
        ));


        $this->prependHtml(new HtmlElement('li', Attributes::create(['class' => 'default']), $button));
    }

    protected function assemble()
    {
        if ($this->data === null) {
            $data = [];
        } else {
            $data = $this->data;
        }

        foreach ($data as $term => $meta) {
            if (is_int($term)) {
                $term = $meta;
            }

            $attributes = [
                'type'          => 'button',
                'tabindex'      => -1,
                'data-search'   => $term
            ];

            $attributes['value'] = $meta;
            $attributes['data-label'] = $meta;

            $this->addHtml(new HtmlElement('li', null, new InputElement(null, $attributes)));
        }

        $showDefault = true;
        if ($this->searchTerm && $this->count()) {
            // The default option is not shown if the user's input result in an exact match
            $input = $this->getFirst('li')->getFirst('input');
            $showDefault = $input->getValue() != $this->searchTerm
                && $input->getAttributes()->get('data-search')->getValue() != $this->searchTerm;
        }

        if ($showDefault) {
            $this->assembleDefault();
        }
    }

    /**
     * Load suggestions as requested by the client
     *
     * @param ServerRequestInterface $request
     *
     * @return $this
     */
    public function forRequest(ServerRequestInterface $request)
    {
        if ($request->getMethod() !== 'POST') {
            return $this;
        }

        $requestData = json_decode($request->getBody()->read(8192), true);
        if (empty($requestData)) {
            return $this;
        }

        $search = $requestData['term']['search'];
        $label = $requestData['term']['label'];
        $exclude = $requestData['exclude'];

        $this->setSearchTerm($search);

        $this->setData($this->fetchSuggestions($label, $exclude));

        if ($search) {
            $this->setDefault($search);
        }

        return $this;
    }

    public function renderUnwrapped()
    {
        $this->ensureAssembled();

        if ($this->isEmpty()) {
            return '';
        }

        return parent::renderUnwrapped();
    }
}
