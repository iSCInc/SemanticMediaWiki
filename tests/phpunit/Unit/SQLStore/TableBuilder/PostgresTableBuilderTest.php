<?php

namespace SMW\Tests\SQLStore\TableBuilder;

use SMW\SQLStore\TableBuilder\PostgresTableBuilder;

/**
 * @covers \SMW\SQLStore\TableBuilder\PostgresTableBuilder
 * @group semantic-mediawiki
 *
 * @license GNU GPL v2+
 * @since 2.5
 *
 * @author mwjames
 */
class PostgresTableBuilderTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$connection = $this->getMockBuilder( '\DatabaseBase' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$connection->expects( $this->any() )
			->method( 'getType' )
			->will( $this->returnValue( 'postgres' ) );

		$this->assertInstanceOf(
			'\SMW\SQLStore\TableBuilder\PostgresTableBuilder',
			PostgresTableBuilder::factory( $connection )
		);
	}

	public function testCreateTableOnNewTable() {

		$connection = $this->getMockBuilder( '\DatabaseBase' )
			->disableOriginalConstructor()
			->setMethods( array( 'tableExists', 'query' ) )
			->getMockForAbstractClass();

		$connection->expects( $this->any() )
			->method( 'getType' )
			->will( $this->returnValue( 'postgres' ) );

		$connection->expects( $this->any() )
			->method( 'tableExists' )
			->will( $this->returnValue( false ) );

		$connection->expects( $this->once() )
			->method( 'query' )
			->with( $this->stringContains( 'CREATE TABLE' ) );

		$instance = PostgresTableBuilder::factory( $connection );

		$tableOptions = array(
			'fields' => array( 'bar' => 'text' )
		);

		$instance->createTable( 'foo', $tableOptions );
	}

	public function testUpdateTableOnOldTable() {

		$connection = $this->getMockBuilder( '\DatabaseBase' )
			->disableOriginalConstructor()
			->setMethods( array( 'tableExists', 'query' ) )
			->getMockForAbstractClass();

		$connection->expects( $this->any() )
			->method( 'getType' )
			->will( $this->returnValue( 'postgres' ) );

		$connection->expects( $this->any() )
			->method( 'tableExists' )
			->will( $this->returnValue( true ) );

		$connection->expects( $this->at( 2 ) )
			->method( 'query' )
			->with( $this->stringContains( 'SELECT a.attname as' ) )
			->will( $this->returnValue( array() ) );

		$connection->expects( $this->at( 3 ) )
			->method( 'query' )
			->with( $this->stringContains( 'ALTER TABLE "foo" ADD "bar" TEXT' ) );

		$instance = PostgresTableBuilder::factory( $connection );

		$tableOptions = array(
			'fields' => array( 'bar' => 'text' )
		);

		$instance->createTable( 'foo', $tableOptions );
	}

	public function testCreateIndex() {

		$connection = $this->getMockBuilder( '\DatabaseBase' )
			->disableOriginalConstructor()
			->setMethods( array( 'tableExists', 'query', 'indexInfo' ) )
			->getMockForAbstractClass();

		$connection->expects( $this->any() )
			->method( 'getType' )
			->will( $this->returnValue( 'postgres' ) );

		$connection->expects( $this->any() )
			->method( 'tableExists' )
			->will( $this->returnValue( false ) );

		$connection->expects( $this->any() )
			->method( 'indexInfo' )
			->will( $this->returnValue( false ) );

		$connection->expects( $this->at( 1 ) )
			->method( 'query' )
			->with( $this->stringContains( 'SELECT  i.relname AS indexname' ) )
			->will( $this->returnValue( array() ) );

		$connection->expects( $this->at( 3 ) )
			->method( 'query' )
			->with( $this->stringContains( 'CREATE INDEX foo_index0 ON foo (bar)' ) );

		$instance = PostgresTableBuilder::factory( $connection );

		$indexOptions = array(
			'indicies' => array( 'bar' )
		);

		$instance->createIndex( 'foo', $indexOptions );
	}

	public function testDropTable() {

		$connection = $this->getMockBuilder( '\DatabaseBase' )
			->disableOriginalConstructor()
			->setMethods( array( 'tableExists', 'query' ) )
			->getMockForAbstractClass();

		$connection->expects( $this->any() )
			->method( 'getType' )
			->will( $this->returnValue( 'postgres' ) );

		$connection->expects( $this->once() )
			->method( 'tableExists' )
			->will( $this->returnValue( true ) );

		$connection->expects( $this->once() )
			->method( 'query' )
			->with( $this->stringContains( 'DROP TABLE IF EXISTS "foo"' ) );

		$instance = PostgresTableBuilder::factory( $connection );

		$instance->dropTable( 'foo' );
	}

}
