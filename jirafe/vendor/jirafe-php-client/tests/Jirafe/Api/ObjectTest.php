<?php

class Jirafe_Api_ObjectTest extends PHPUnit_Framework_TestCase
{
    private $clientMock;
    private $collectionMock;
    private $objectMock;

    protected function setUp()
    {
        $this->clientMock = $this->getMockBuilder('Jirafe_Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionMock = $this->getMockBuilder('Jirafe_Api_Collection')
            ->setMethods(array('getCollectionName'))
            ->setConstructorArgs(array(null, $this->clientMock))
            ->getMock();

        $this->objectMock = $this->getMockBuilder('Jirafe_Api_Object')
            ->setMethods(array('getClassName'))
            ->setConstructorArgs(array('beretta', $this->collectionMock, $this->clientMock))
            ->getMock();
    }

    /**
     * @test
     */
    public function shouldBeAbleToUpdateValues()
    {
        $this->collectionMock
            ->expects($this->once())
            ->method('getCollectionName')
            ->will($this->returnValue('weapons'));

        $this->clientMock
            ->expects($this->once())
            ->method('put')
            ->with('weapons/beretta', array(), array('key1' => 'val1', 'key2' => true))
            ->will($this->returnValue(new Jirafe_HttpConnection_Response('', array(), 0, '')));

        $this->assertEquals('', $this->objectMock->update(array(
            'key1' => 'val1', 'key2' => true
        )));
    }

    /**
     * @test
     * @expectedException Jirafe_Exception
     */
    public function shouldThrowExceptionOnUpdateError()
    {
        $this->collectionMock
            ->expects($this->once())
            ->method('getCollectionName')
            ->will($this->returnValue('weapons'));

        $this->clientMock
            ->expects($this->once())
            ->method('put')
            ->with('weapons/beretta', array(), array('key1' => 'val1', 'key2' => true))
            ->will($this->returnValue(new Jirafe_HttpConnection_Response(
                '', array(), 404, 'error'
            )));

        $this->assertEquals('', $this->objectMock->update(array(
            'key1' => 'val1', 'key2' => true
        )));
    }

    /**
     * @test
     */
    public function shouldBeAbleToGetValues()
    {
        $this->collectionMock
            ->expects($this->once())
            ->method('getCollectionName')
            ->will($this->returnValue('weapons'));

        $this->clientMock
            ->expects($this->once())
            ->method('get')
            ->with('weapons/beretta', array())
            ->will($this->returnValue(new Jirafe_HttpConnection_Response('"vals"', array(), 0, '')));

        $this->assertEquals('vals', $this->objectMock->fetch());
    }

    /**
     * @test
     * @expectedException Jirafe_Exception
     */
    public function shouldThrowExceptionOnValuesGettingError()
    {
        $this->collectionMock
            ->expects($this->once())
            ->method('getCollectionName')
            ->will($this->returnValue('weapons'));

        $this->clientMock
            ->expects($this->once())
            ->method('get')
            ->with('weapons/beretta', array())
            ->will($this->returnValue(new Jirafe_HttpConnection_Response(
                '', array(), 404, 'error'
            )));

        $this->assertEquals('', $this->objectMock->fetch());
    }

    /**
     * @test
     */
    public function shouldBeAbleToDeleteObject()
    {
        $this->collectionMock
            ->expects($this->once())
            ->method('getCollectionName')
            ->will($this->returnValue('weapons'));

        $this->clientMock
            ->expects($this->once())
            ->method('delete')
            ->with('weapons/beretta', array(), array())
            ->will($this->returnValue(new Jirafe_HttpConnection_Response('"vals"', array(), 0, '')));

        $this->objectMock->delete();
    }

    /**
     * @test
     * @expectedException Jirafe_Exception
     */
    public function shouldThrowExceptionOnDeletingError()
    {
        $this->collectionMock
            ->expects($this->once())
            ->method('getCollectionName')
            ->will($this->returnValue('weapons'));

        $this->clientMock
            ->expects($this->once())
            ->method('delete')
            ->with('weapons/beretta', array(), array())
            ->will($this->returnValue(new Jirafe_HttpConnection_Response(
                '', array(), 404, 'error'
            )));

        $this->objectMock->delete();
    }
}
