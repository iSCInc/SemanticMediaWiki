<?php

namespace SMW\Tests\DataValues\ValueFormatters;

use SMW\DataValues\ReferenceValue;
use SMW\DataValues\ValueFormatters\ReferenceValueFormatter;
use SMW\DataItemFactory;
use SMW\Tests\TestEnvironment;

/**
 * @covers \SMW\DataValues\ValueFormatters\ReferenceValueFormatter
 * @group semantic-mediawiki
 *
 * @license GNU GPL v2+
 * @since 2.5
 *
 * @author mwjames
 */
class ReferenceValueFormatterTest extends \PHPUnit_Framework_TestCase {

	private $testEnvironment;
	private $dataItemFactory;

	protected function setUp() {
		parent::setUp();

		$this->testEnvironment = new TestEnvironment();
		$this->dataItemFactory = new DataItemFactory();

		$this->propertySpecificationLookup = $this->getMockBuilder( '\SMW\PropertySpecificationLookup' )
			->disableOriginalConstructor()
			->getMock();

		$this->testEnvironment->registerObject( 'PropertySpecificationLookup', $this->propertySpecificationLookup );
	}

	protected function tearDown() {
		$this->testEnvironment->tearDown();
		parent::tearDown();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SMW\DataValues\ValueFormatters\ReferenceValueFormatter',
			new ReferenceValueFormatter()
		);
	}

	public function testIsFormatterForValidation() {

		$referenceValue = $this->getMockBuilder( '\SMW\DataValues\ReferenceValue' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new ReferenceValueFormatter();

		$this->assertTrue(
			$instance->isFormatterFor( $referenceValue )
		);
	}

	public function testToUseCaptionOutput() {

		$referenceValue = new ReferenceValue();
		$referenceValue->setCaption( 'ABC' );

		$instance = new ReferenceValueFormatter( $referenceValue );

		$this->assertEquals(
			'ABC',
			$instance->format( ReferenceValueFormatter::WIKI_SHORT )
		);

		$this->assertEquals(
			'ABC',
			$instance->format( ReferenceValueFormatter::HTML_SHORT )
		);
	}

	/**
	 * @dataProvider stringValueProvider
	 */
	public function testFormat( $suserValue, $type, $linker, $expected ) {

		$referenceValue = new ReferenceValue();

		$referenceValue->setFieldProperties( array(
			$this->dataItemFactory->newDIProperty( 'Foo' ),
			$this->dataItemFactory->newDIProperty( 'Bar' ),
			$this->dataItemFactory->newDIProperty( 'Foobar' )
		) );

		$referenceValue->setUserValue( $suserValue );

		$instance = new ReferenceValueFormatter( $referenceValue );

		$this->assertEquals(
			$expected,
			$instance->format( $type, $linker )
		);
	}

	public function testTryToFormatOnMissingDataValueThrowsException() {

		$instance = new ReferenceValueFormatter();

		$this->setExpectedException( 'RuntimeException' );
		$instance->format( ReferenceValueFormatter::VALUE );
	}

	public function stringValueProvider() {

		$provider[] = array(
			'abc;12;3',
			ReferenceValueFormatter::VALUE,
			null,
			'Abc;12;3'
		);

		$provider[] = array(
			'abc',
			ReferenceValueFormatter::WIKI_SHORT,
			null,
			'Abc<span class="smw-reference smw-reference-indicator smw-highlighter smwttinline" data-title="Reference" data-content="&lt;ul&gt;&lt;li&gt;Bar: ?&lt;/li&gt;&lt;li&gt;Foobar: ?&lt;/li&gt;&lt;/ul&gt;" title="Bar: ?, Foobar: ?"></span>'
		);

		$provider[] = array(
			'abc',
			ReferenceValueFormatter::HTML_SHORT,
			null,
			'Abc<span class="smw-reference smw-reference-indicator smw-highlighter smwttinline" data-title="Reference" data-content="&lt;ul&gt;&lt;li&gt;Bar: ?&lt;/li&gt;&lt;li&gt;Foobar: ?&lt;/li&gt;&lt;/ul&gt;" title="Bar: ?, Foobar: ?"></span>'
		);

		$provider[] = array(
			'abc',
			ReferenceValueFormatter::WIKI_LONG,
			null,
			'Abc<span class="smw-reference smw-reference-indicator smw-highlighter smwttinline" data-title="Reference" data-content="&lt;ul&gt;&lt;li&gt;Bar: ?&lt;/li&gt;&lt;li&gt;Foobar: ?&lt;/li&gt;&lt;/ul&gt;" title="Bar: ?, Foobar: ?"></span>'
		);

		$provider[] = array(
			'abc',
			ReferenceValueFormatter::HTML_LONG,
			null,
			'Abc<span class="smw-reference smw-reference-indicator smw-highlighter smwttinline" data-title="Reference" data-content="&lt;ul&gt;&lt;li&gt;Bar: ?&lt;/li&gt;&lt;li&gt;Foobar: ?&lt;/li&gt;&lt;/ul&gt;" title="Bar: ?, Foobar: ?"></span>'
		);

		return $provider;
	}

}
