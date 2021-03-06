<?php
namespace BurningMoth\Stratus;
/**
 * Process a template.
 *
 * @since 1.0
 * @since 2.0
 *	- stripped out all the fancy caching and static file processing (for now)
 *
 * @param string $___template_path
 * @param array $___template_variables
 * @return string
 */
function template( $___template_path, array $___template_variables = array() ) {

	global $tratus;

	// load from extended structure ...
	if ( preg_match('/^[\w-]+$/', $___template_path) ) $___template_path = sprintf('strato/template/%s.php', $___template_path);

	// dump vars ...
	if ( $___template_variables ) extract($___template_variables);

	// dump template ...
	ob_start();
	include $___template_path;
	return ob_get_clean();

}
