<?php

namespace SMW\SQLStore\QueryEngine\Fulltext;

use SMW\Query\Language\ValueDescription;

/**
 * @license GNU GPL v2+
 * @since 2.5
 *
 * @author mwjames
 */
class MySQLValueMatchConditionBuilder extends ValueMatchConditionBuilder {

	/**
	 * @var SearchTable
	 */
	private $searchTable;

	/**
	 * @since 2.5
	 *
	 * @param SearchTable $searchTable
	 */
	public function __construct( SearchTable $searchTable ) {
		$this->searchTable = $searchTable;
	}

	/**
	 * @since 2.5
	 *
	 * @return boolean
	 */
	public function isEnabled() {
		return $this->searchTable->isEnabled();
	}

	/**
	 * @since 2.5
	 *
	 * @return string
	 */
	public function getTableName() {
		return $this->searchTable->getTableName();
	}

	/**
	 * @since 2.5
	 *
	 * @param string $value
	 *
	 * @return boolean
	 */
	public function hasMinTokenLength( $value ) {
		return mb_strlen( $value ) >= $this->searchTable->getMinTokenSize();
	}

	/**
	 * @since 2.5
	 *
	 * @param string $temporaryTable
	 *
	 * @return string
	 */
	public function getSortIndexField( $temporaryTable = '' ) {
		return ( $temporaryTable !== '' ? $temporaryTable . '.' : '' ) . $this->searchTable->getSortField();
	}

	/**
	 * @since 2.5
	 *
	 * @param ValueDescription $description
	 *
	 * @return boolean
	 */
	public function canApplyFulltextSearchMatchCondition( ValueDescription $description ) {

		if ( !$this->isEnabled() || $description->getProperty() === null ) {
			return false;
		}

		if ( $this->searchTable->isExemptedProperty( $description->getProperty() ) ) {
			return false;
		}

		$matchableText = $this->getMatchableTextFromDescription(
			$description
		);

		$comparator = $description->getComparator();

		if ( $matchableText && ( $comparator === SMW_CMP_LIKE || $comparator === SMW_CMP_NLKE ) ) {
			// http://dev.mysql.com/doc/refman/5.7/en/fulltext-boolean.html
			// innodb_ft_min_token_size and innodb_ft_max_token_size are used
			// for InnoDB search indexes. ft_min_word_len and ft_max_word_len
			// are used for MyISAM search indexes

			// Don't count any wildcard
			return $this->hasMinTokenLength( str_replace( '*', '', $matchableText ) );
		}

		return false;
	}

	/**
	 * @since 2.5
	 *
	 * @param ValueDescription $description
	 * @param string $temporaryTable
	 *
	 * @return string
	 */
	public function getWhereCondition( ValueDescription $description, $temporaryTable = '' ) {

		$matchableText = $this->getMatchableTextFromDescription(
			$description
		);

		$value = $this->searchTable->getTextSanitizer()->sanitize(
			$matchableText,
			true
		);

		// A leading or trailing minus sign indicates that this word must not
		// be present in any of the rows that are returned.
		// InnoDB only supports leading minus signs.
		if ( $description->getComparator() === SMW_CMP_NLKE ) {
			$value = '-' . $value;
		}

		$temporaryTable = $temporaryTable !== '' ? $temporaryTable . '.' : '';
		$column = $temporaryTable . $this->searchTable->getIndexField();

		$property = $description->getProperty();
		$propertyCondition = '';

		// Full text is collected in a single table therefore limit the match
		// process by adding the PID as an additional condition
		if ( $property !== null ) {
			$propertyCondition = 'AND ' . $temporaryTable . 'p_id=' . $this->searchTable->getPropertyID( $property );
		}

		$querySearchModifier = $this->getQuerySearchModifier(
			$value
		);

		return "MATCH($column) AGAINST (" . $this->searchTable->addQuotes( $value ) . " $querySearchModifier) $propertyCondition";
	}

	/**
	 * @since 2.5
	 *
	 * @param  string &$value
	 *
	 * @return string
	 */
	public function getQuerySearchModifier( &$value ) {

		//  @see http://dev.mysql.com/doc/refman/5.7/en/fulltext-boolean.html
		// "MySQL can perform boolean full-text searches using the IN BOOLEAN
		// MODE modifier. With this modifier, certain characters have special
		// meaning at the beginning or end of words ..."
		if ( strpos( $value, '&BOL' ) !== false ) {
			$value = str_replace( '&BOL', '', $value );
			return 'IN BOOLEAN MODE';
		}

		if ( strpos( $value, '&INL' ) !== false ) {
			$value = str_replace( '&INL', '', $value );
			return 'IN NATURAL LANGUAGE MODE';
		}

		if ( strpos( $value, '&QEX' ) !== false ) {
			$value = str_replace( '&QEX', '', $value );
			return 'WITH QUERY EXPANSION';
		}

		return 'IN BOOLEAN MODE';
	}

}
