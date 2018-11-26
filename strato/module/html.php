<?php
/**
 * Stratus Mod for aLL things HTML processing.
 */
namespace BurningMoth\Stratus;

// register trait ...
$_ENV['STRATUS_TRAITS'][__NAMESPACE__.'\HTML_Module'] = true;

// declare trait ...
trait HTML_Module {

	/**
	 * Return a formatted html tag.
	 *
	 * @since 1.0
	 *
	 * @param string			$name
	 * @param null|array		$attributes
	 * @param null|string|array	$value
	 * @param bool				$eol
	 * @return string html markup
	 */
	public function html_tag( $name, $attributes = null, $value = null, $eol = false ) {

		// if passing these arguments via html_tags() method as array in the first argument ...
		if ( is_array($name) ) {
			list($name, $attributes, $value, $eol) = array_pad($name, 4, null);
		}

		// lowercase tag name ...
		$name = trim(strtolower($name));

		// open tag ...
		$op = '<' . $name;

		// append attributes ...
		if ( is_array($attributes) ) {
			$op .= ' ' . $this->html_attributes($attributes);
		}

		// null? no content will be forth coming - close now w/o closing tag ...
		if ( is_null($value) ) {
			$op .= ' />';
		}

		// bool?
		// true - leave open, content assumed to be forthcoming via other methods
		// false - close with empty content/end tag
		elseif ( is_bool($value) ) {
			$op .= ( $value ? '>' : sprintf('></%s>', $name) );
		}

		// add content and end tag ...
		else {
			$op .= sprintf('>%s</%s>', (
				is_array($value)
				? call_user_func_array(array($this, 'html_tags'), $value)
				: (string) $value
			), $name);
		}

		// append end of line character ?
		if ($eol) {
			$op .= PHP_EOL;
		}

		return $op;
	}


	/**
	 * Return a string of formatted html tags from array of html arguments.
	 *
	 * @since 1.0
	 *
	 * @param array		$html_tag
	 * @return string html markup
	 */
	public function html_tags() {
		return implode('', array_map(array($this, 'html_tag'), func_get_args()));
	}


	/**
	 * Return a string of formatted html attributes.
	 *
	 * @since 1.0
	 *
	 * @param array		$attributes
	 * @return string
	 */
	public function html_attributes( $attributes ) {
		if ( is_object($attributes) ) $attributes = (array) $attributes;
		if ( is_array($attributes) ) {
			return implode(' ', array_map([ $this, 'array_map_html_attribute' ], array_keys($attributes), array_values($attributes)));
		}
		return '';
	}


	/**
	 * Returns formatted style rules from assoc array.
	 *
	 * @since 1.0
	 *
	 * @param array		$styles
	 * @return string
	 */
	public function html_attribute_style( $styles ) {
		if (
			is_array($styles)
			&& count($styles) > 0
		) return implode('; ', array_map(array($this, 'array_map_html_attribute_style'), array_keys($styles), array_values($styles))) . ';';
		return (string) $styles;
	}


	/**
	 * Returns a formatted html tag attribute.
	 * #callback array_map()
	 *
	 * @since 1.0
	 *
	 * @param string		$name
	 * @param string|array	$value
	 * @return string
	 */
	public function array_map_html_attribute( $name, $value ) {

		$name = trim(strtolower($name));

		if ( is_array($value) ) {
			switch ($name) {
				case 'data': // value is expected to be associative array
					return implode(' ', array_map(array($this, 'array_map_html_attribute'), array_map(array($this, 'array_map_html_attribute_data'), array_keys($value)), array_values($value)));
					break;

				// #system accessibility
				case 'aria':	// value is expected to be associative array (like data) ...
					return implode(' ', array_map(array($this, 'array_map_html_attribute'), array_map(array($this, 'array_map_html_attribute_aria'), array_keys($value)), array_values($value)));
					break;

				// meta content - can be either associative or numeric arrays ...
				case 'content':
					$value = implode(', ', ($this->is_array_numeric($value) ? $value : array_map(array($this, 'array_map_html_attribute_content'), array_keys($value), array_values($value))));
					break;

				// associative array ...
				case 'style':
					$value = $this->html_attribute_style($value);
					break;

				// numeric array ...
				case 'class':
				default:
					$value = implode((preg_match('/^on/i', $name) ? '; ' : ' '), $value);
					break;
			}
		}

		// value not scalar ? assume it's to be json-encoded ...
		if ( ! is_scalar($value) ) $value = json_encode($value);

		// return encoded attribute="value" string ...
		return sprintf('%s="%s"', $name, $this->html_encode($value));

	}


	/**
	 * Returns formatted style rule.
	 * #callback array_map()
	 *
	 * @since 1.0
	 *
	 * @param string	$name
	 * @param string	$value
	 * @return string
	 */
	public function array_map_html_attribute_style( $property, $value ) {
		return $this->filter(
			'stratus:css_declaration',
			sprintf('%s:%s', trim($property), trim($value)),
			strtolower($property),
			$value
		);
	}


	/**
	 * Returns formatted meta content value.
	 * #callback array_map()
	 *
	 * @since 1.0
	 *
	 * @param string	$name
	 * @param string	$value
	 * @return string
	 */
	public function array_map_html_attribute_content( $name, $value ) {
		return sprintf('%s=%s', trim(strtolower($name)), trim($value) );
	}


	/**
	 * Returns a data-prefixed attribute name.
	 * #callback array_map()
	 *
	 * @since 1.0
	 *
	 * @param string	$name
	 * @return string
	 */
	public function array_map_html_attribute_data( $name ) {
		return 'data-'.$name;
	}


	/**
	 * Format aria-prefixed attributes.
	 * #system accessibility
	 *
	 * @since 1.0
	 *
	 * @param string	$name
	 * @return string
	 */
	public function array_map_html_attribute_aria( $name ) {
		return 'aria-'.$name;
	}


	/**
	 * Format html comment.
	 *
	 * @since 1.0
	 *
	 * @param string	$comment
	 * @param bool		$eol
	 * @return string
	 */
	public function html_comment( $comment, $eol = false ) {
		$comment = sprintf('<!-- %s -->', $comment);
		if ( $eol ) $comment = PHP_EOL . $comment . PHP_EOL;
		return $comment;
	}


	/**
	 * HTML encode string.
	 * Note: ENT_COMPAT | ENT_HTML5 = entities all over the place! Eeek!!! In the future if necessary.
	 *
	 * @since 1.0
	 *
	 * @param string	$value
	 * @return string
	 */
	public function html_encode( $value ) {
		return htmlentities(
			(string) $value,
			ENT_COMPAT | ENT_DISALLOWED | ENT_XML1,
			ini_get("default_charset"),
			false
		);
	}


	/**
	 * HTML decode string.
	 *
	 * @since 1.0
	 *
	 * @param string	$value
	 * @return string
	 */
	public function html_decode( $value ) {
		return html_entity_decode(
			(string) $value,
			ENT_COMPAT | ENT_HTML5
		);
	}

}
