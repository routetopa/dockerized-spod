<?php

namespace LucaVicidomini\BladeMaterialize;

class HtmlElement
{

    /**
     * The element tag (a, input, button, ...).
     *
     * @var
     */
    protected $tag;

    /**
     * HTML-Element's attributes.
     *
     * @var
     */
    protected $attributes = [];

    /**
     * CSS Classes
     *
     * @var
     */
    protected $cssClasses = [];

    /**
     * HTML Content of the element.
     *
     * @var string
     */
    protected $htmlContent = '';

    /**
     * true if should automatically generate an HTML label when output.
     *
     * @var bool
     */
    public $label = null;

    /**
     * true if validation is positive, false if validation is negative, null if validation is
     * not performed
     *
     * @var bool
     */
    public $valid = null;

    /**
     * If the element is a checkbox and $ghostCheckboxValue is not null, the "proper" checkbox
     * will be preceded by a "ghost" checkbox that will carry the $ghostCheckboxValue value
     * when the "proper" checkbox is not checked.
     *
     * @var mixed
     */
    public $ghostCheckboxValue = null;

	/**
	 * If user explicitly calls ghost( null ), force not to use a ghost value.
	 * @var bool
	 */
	protected $avoidGhost = false;

    /**
     * HtmlElement constructor.
     *
     * @param $tag
     */
    public function __construct( $tag )
    {
        $this->tag = strtolower( $tag );
    }

	/**
	 * Check if this element is a checkox.
	 *
	 * @return bool
	 */
	protected function isCheckbox()
	{
		return $this->tag == 'input'
		       && $this->attributes[ 'type' ]
		       && $this->attributes[ 'type' ] == 'checkbox';
	}

    /**
     * Set an attribute value. If $value is null, the attribute is removed from the element.
     *
     * @param $attribute The attribute name
     * @param $value The attribute value.
     *
     * @return $this
     */
    public function attribute( $attribute, $value )
    {
        if ( null === $value )
        {
            unset( $this->attributes[ $attribute ] );
        } else {
            $this->attributes[ $attribute ] = $value;
        }

        return $this;
    }

    /**
     * Set the HTML content of the element.
     *
     * @param $content
     *
     * @return $this
     */
    public function content( $content = '' )
    {
        $this->htmlContent = $content;

        return $this;
    }

    /**
     * Add a CSS class to the element. Multiple classes can be passed as a space-separated string.
     *
     * @param $cssClasses
     *
     * @return $this
     */
    public function css( $cssClasses )
    {
        if ( ! $cssClasses )
        {
            return $this;
        }

        $cssClasses = explode( ' ', $cssClasses );

        foreach ( $cssClasses as $cssClass )
        {
            if ( $cssClass && false === in_array( $cssClass, $this->cssClasses ) )
            {
                $this->cssClasses[] = $cssClass;
            }
        }

        return $this;
    }

	/**
	 * Set the id attribute for the element.
	 *
	 * @param $value
	 *
	 * @return $this
	 */
    public function id( $value )
    {
        return $this->attribute( 'id', $value );
    }

	/**
	 * Set the name attribute for the element.
	 *
	 * @param $value
	 *
	 * @return $this
	 */
    public function name( $value )
    {
        return $this->attribute( 'name', $value );
    }

	/**
	 * Set the placeholder attribute for the element.
	 *
	 * @param $value
	 *
	 * @return $this
	 */
    public function placeholder( $value )
    {
        return $this->attribute( 'placeholder', $value );
    }

	/**
	 * Set the value attribute for the element.
	 *
	 * @param $value
	 *
	 * @return $this
	 */
    public function value( $value )
    {
        return $this->attribute( 'value', $value );
    }

	/**
	 * Set the disabled attribute for the element.
	 *
	 * @return $this
	 */
    public function disabled()
    {
        return $this->attribute( 'disabled', 'disabled' );
    }

	/**
	 * Add a label to the element.
	 *
	 * @param $value
	 *
	 * @return $this
	 */
    public function label( $value )
    {
        $this->label = $value;
        return $this;
    }

	/**
	 * Add a localizable label to the element.
	 *
	 * @param $trans_key
	 *
	 * @return $this
	 */
	public function tlabel( $trans_key )
	{
		return $this->label( trans( $trans_key ) );
	}

	/**
	 * Add a checked attribute to the checkbox.
	 *
	 * @param $checked
	 *
	 * @return $this
	 */
    public function checked( $checked )
    {
        if ( $checked && $this->isCheckbox() )
        {
            $this->attribute( 'checked', 'checked' );
        }

        return $this;
    }

	/**
	 * Set the "ghost value" for the checkbox.
	 *
	 * @see $ghostCheckboxValue
	 *
	 * @param $ghostValue
	 */
    public function ghost( $ghostValue )
    {
        if ( $this->isCheckbox() )
        {
            $this->ghostCheckboxValue = $ghostValue;
	        $this->avoidGhost = ( null === $ghostValue );
        }

        return $this;
    }

	/**
	 * Return the checkbox ghost value, or null if there is no ghost value;
	 */
    protected function getGhost()
    {
	    // A value is set for this checkbox, use it
	    if ( null !== $this->ghostCheckboxValue ) {
		    return $this->ghostCheckboxValue;
	    }

	    // No value is set, and it is not forced to null, so use default ghost (if any)
	    if ( ! $this->avoidGhost ) {
		    return config( 'blade-materialize.checkbox_ghost' );
	    }

	    // No value is set, user didn't force ghost
	    return null;
    }

    /**
     * Converts the HTML object to a HTML string.
     *
     * @return string
     */
    public function __toString()
    {
        $attributes = '';
        foreach ( $this->attributes as $k => $v )
        {
            $v = htmlspecialchars( $v, ENT_QUOTES );
            $attributes .= " {$k}=\"{$v}\"";
        }

        if ( false === $this->valid )
        {
            $this->css( 'invalid' );
        }
        $cssClasses = count( $this->cssClasses ) ? ' class="' . join( ' ', $this->cssClasses ) .'"' : '';

        $ghost = '';
	    $ghostValue = $this->getGhost();
        if ( $this->isCheckbox() && ( null !== $ghostValue ) && $this->attributes[ 'name' ] )
        {
            $ghost = "<input type=\"hidden\" name=\"{$this->attributes[ 'name' ]}\" value=\"{$ghostValue}\" />";
        }

        $label = '';
        if ( $this->label ) {
            $label = new HtmlElement( 'label' );
            $label->content( $this->label );
            $label->attribute( 'for', $this->attributes[ 'name' ] );
        }

        return "{$ghost}<{$this->tag}{$attributes}{$cssClasses}>{$this->htmlContent}</{$this->tag}>{$label}";
    }
}