<?php

class Jirafe_Api_ResourceTest extends PHPUnit_Framework_TestCase
{
    private $clientMock;
    private $collectionMock;
    private $resourceMock;

    protected function setUp()
    {
        $this->clientMock = $this->getMockBuilder('Jirafe_Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionMock = $this->getMockBuilder('Jirafe_Api_Collection')
            ->setMethods(array('getCollectionName'))
            ->setConstructorArgs(array(null, $this->clientMock))
            ->getMock();

        $this->resourceMock = $this->getMockBuilder('Jirafe_Api_Resource')
            ->setMethods(array('getClassName'))
            ->setConstructorArgs(array('beretta', $this->collectionMock, $this->clientMock))
            ->getMock();
    }

    /**
     * @test
     */
    public function shouldProvideId()
    {
        $this->assertEquals('beretta', $this->resourceMock->getId());
    }

    /**
     * @test
     */
    public function shouldProvideCollection()
    {
        $this->assertEquals($this->collectionMock, $this->resourceMock->getCollection());
    }

    /**
     * @test
     */
    public function shouldProvideClient()
    {
        $this->assertEquals($this->clientMock, $this->resourceMock->getClient());
    }

    /**
     * @test
     */
    public function shouldProvideFullPath()
    {
        $this->collectionMock
            ->expects($this->once())
            ->method('getCollectionName')
            ->will($this->returnValue('weapons'));

        $this->assertEquals('weapons/beretta', $this->resourceMock->getPath());
    }

    /**
     * @test
     */
    public function shouldUseProperPathInGetCalls()
    {
        $this->collectionMock
            ->expects($this->once())
            ->method('getCollectionName')
            ->will($this->returnValue('weapons'));

        $this->clientMock
            ->expects($this->once())
            ->method('get')
            ->with('weapons/beretta', array('username' => true))
            ->will($this->returnValue('everzet'));

        $this->assertEquals('everzet', $this->resourceMock->doGet(array('username' => true)));
    }

    /**
     * @test
     */
    public function shouldUseProperPathInHeadCalls()
    {
        $this->collectionMock
            ->expects($this->once())
            ->method('getCollectionName')
            ->will($this->returnValue('weapons'));

        $this->clientMock
            ->expects($this->once())
            ->method('head')
            ->with('weapons/beretta', array('username' => true))
            ->will($this->returnValue('everzet'));

        $this->assertEquals('everzet', $this->resourceMock->doHead(array('username' => true)));
    }

    /**
     * @test
     */
    public function shouldUseProperPathInPutCalls()
    {
        $this->collectionMock
            ->expects($this->once())
            ->method('getCollectionName')
            ->will($this->returnValue('weapons'));

        $this->clientMock
            ->expects($this->once())
            ->method('put')
            ->with('weapons/beretta', array('username' => true), array('input' => '123'))
            ->will($this->returnValue('everzet'));

        $this->assertEquals('everzet', $this->resourceMock->doPut(
            array('username' => true), array('input' => '123')
        ));
    }

    /**
     * @test
     */
    public function shouldUseProperPathInDeleteCalls()
    {
        $this->collectionMock
            ->expects($this->once())
            ->method('getCollectionName')
            ->will($this->returnValue('weapons'));

        $this->clientMock
            ->expects($this->once())
            ->method('delete')
            ->with('weapons/beretta', array('username' => true), array('indelete' => '123'))
            ->will($this->returnValue('everzet'));

        $this->assertEquals('everzet', $this->resourceMock->doDelete(
            array('username' => true), array('indelete' => '123')
        ));
    }
}
