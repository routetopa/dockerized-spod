<?php

namespace LucaVicidomini\BladeMaterialize;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Routing\UrlGenerator;
use LucaVicidomini\BladeMaterialize\Collections\HtmlElementsCollection;

class FormBuilder
{
    use Macroable {
        Macroable::__call as macroCall;
    }

    /**
     * The HTML builder instance.
     *
     * @var \LucaVicidomini\BladeMaterialize\HtmlBuilder
     */
    protected $html;

    /**
     * The URL generator instance.
     *
     * @var \Illuminate\Contracts\Routing\UrlGenerator
     */
    protected $url;

    /**
     * The View factory instance.
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $view;

    /**
     * The CSRF token used by the form builder.
     *
     * @var string
     */
    protected $csrfToken;

    /**
     * The errors MesageBag (extracted from the View factory).
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $errors;

    /**
     * Create a new form builder instance.
     *
     * @param  \LucaVicidomini\BladeMaterialize\HtmlBuilder $html
     * @param  \Illuminate\Contracts\Routing\UrlGenerator   $url
     * @param  \Illuminate\Contracts\View\Factory           $view
     * @param  string                                       $csrfToken
     */
    public function __construct(HtmlBuilder $html, UrlGenerator $url, Factory $view, $csrfToken)
    {
        $this->url = $url;
        $this->html = $html;
        $this->view = $view;
        $this->csrfToken = $csrfToken;

        $this->errors = $this->view->getShared()['errors'];
    }

    public function element( $tag, $name_id, $type = 'text' )
    {
        $el = new HtmlElement( $tag );
        if ( $name_id ) {
            $el->attribute( 'name', $name_id);
            $el->attribute( 'id', $name_id);
        }
        $el->attribute( 'type', $type );
        return $el;
    }

    /**
     * Creates a 'label' element.
     *
     * @param $name
     * @param $content
     * @return $this
     */
    public function label( $name, $content )
    {
        return $this->element( 'label' )
            ->content( $content )
            ->attribute( 'for', $name );
    }

	/**
	 * Creates a 'label' element, with support to localization.
	 *
	 * @param $name
	 * @param $content
	 * @return $this
	 */
	public function tlabel( $name, $trans_key )
	{
		return $this->label( $name, trans( $trans_key ) );
	}

    public function input( $name_id ) {
        return $this->element( 'input', $name_id );
    }

    /**
     * Creates a 'password' field and the associated label.
     *
     * @param $name
     * @param $label
     * @param null $cssClasses
     * @param null $placeholder
     * @return array|HtmlElementsCollection
     */
    public function password( $name_id )
    {
        return $this->element( 'input', $name_id, 'password' );
        return $el;
    }

    /**
     * Creates a checkbox.
     *
     * @param $name
     * @param $label
     * @param null $cssClasses
     * @param bool $checked
     * @param null $value
     * @return array|HtmlElementsCollection     */
    public function checkbox( $name_id, $checked = false )
    {
        $el = $this->element( 'input', $name_id, 'checkbox' )
            ->value( 1 )
            ->attribute( 'checked', $checked ? 'checked' : null );
        return $el;
    }

    /**
     * Creates a submit button.
     *
     * @param $label
     * @param null $name
     * @return $this
     */
    public function submit( $caption = null )
    {
        $el = $this->element( 'button', null, 'submit'  )
            ->css( 'btn waves-effect waves-light' )
            ->content( null !== $caption ? "{$caption}<i class=\"material-icons right\">send</i>" : '' );
        return $el;
    }



}