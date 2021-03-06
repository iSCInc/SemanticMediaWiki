<?php

namespace SMW\MediaWiki\Specials\Ask;

use SMW\ProcessingErrorMsgHandler;
use SMW\Message;
use SMWQuery as Query;
use Html;

/**
 * @license GNU GPL v2+
 * @since   2.5
 *
 * @author mwjames
 */
class HtmlContentBuilder {

	/**
	 * @since 2.5
	 *
	 * @param Query|null $query
	 *
	 * @return string
	 */
	public function getFormattedErrorString( Query $query = null ) {

		if ( $query === null || !is_array( $query->getErrors() ) || $query->getErrors() === array() ) {
			return '';
		}

		$errors = array();

		foreach ( ProcessingErrorMsgHandler::normalizeMessages( $query->getErrors() ) as $value ) {

			if ( $value === '' ) {
				continue;
			}

			if ( is_array( $value ) ) {
				$value = implode( " ", $value );
			}

			$errors[] = $value;
		}

		if ( count( $errors ) > 1 ) {
			$error = '<ul><li>' . implode( '</li><li>', $errors ) . '</li></ul>';
		} else {
			$error =  implode( ' ', $errors ) ;
		}

		return Html::rawElement( 'div', array( 'class' => 'smw-callout smw-callout-error' ), $error );
	}

}
