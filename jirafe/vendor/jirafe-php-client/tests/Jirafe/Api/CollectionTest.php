<?php

class Jirafe_Api_CollectionTest extends PHPUnit_Framework_TestCase
{
    private $clientMock;
    private $collectionMock;

    protected function setUp()
    {
        $this->clientMock = $this->getMockBuilder('Jirafe_Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionMock = $this->getMockBuilder('Jirafe_Api_Collection')
            ->setMethods(array('getCollectionName'))
            ->setConstructorArgs(array(null, $this->clientMock))
            ->getMock();
    }

    /**
     * @test
     */
    public function shouldProvideParentIfHasOne()
    {
        $resourceMock = $this->getMockBuilder('Jirafe_Api_Resource')
            ->disableOriginalConstructor()
            ->getMock();

        $collectionMock = $this->getMockBuilder('Jirafe_Api_Collection')
            ->setMethods(array('getCollectionName'))
            ->setConstructorArgs(array($resourceMock, $this->clientMock))
            ->getMock();

        $this->assertSame($resourceMock, $collectionMock->getParent());
    }

    /**
     * @test
     */
    public function shouldNotProvideParentIfHasNotGotAny()
    {
        $this->assertNull($this->collectionMock->getParent());
    }

    /**
     * @test
     */
    public function shouldProvideClient()
    {
        $this->assertEquals($this->clientMock, $this->collectionMock->getClient());
    }

    /**
     * @test
     */
    public function shouldProvideShortPathIfHasNoParent()
    {
        $this->collectionMock
            ->expects($this->once())
            ->method('getCollectionName')
            ->will($this->returnValue('users'));

        $this->assertEquals('users', $this->collectionMock->getPath());
    }

    /**
     * @test
     */
    public function shouldProvideFullPathIfHasParent()
    {
        $resourceMock = $this->getMockBuilder('Jirafe_Api_Resource')
            ->disableOriginalConstructor()
            ->getMock();

        $collectionMock = $this->getMockBuilder('Jirafe_Api_Collection')
            ->setMethods(array('getCollectionName'))
            ->setConstructorArgs(array($resourceMock, $this->clientMock))
            ->getMock();

        $resourceMock
            ->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('passengers/kate'));

        $collectionMock
            ->expects($this->once())
            ->method('getCollectionName')
            ->will($this->returnValue('weapons'));

        $this->assertEquals('passengers/kate/weapons', $collectionMock->getPath());
    }

    /**
     * @test
     */
    public function shouldUseProperPathInGetCalls()
    {
        $this->collectionMock
            ->expects($this->once())
            ->method('getCollectionName')
            ->will($this->returnValue('flights'));

        $this->clientMock
            ->expects($this->once())
            ->method('get')
            ->with('flights', array('flight' => 815))
            ->will($this->returnValue('Oceanic'));

        $this->assertEquals('Oceanic', $this->collectionMock->doGet(array('flight' => 815)));
    }

    /**
     * @test
     */
    public function shouldUseProperPathInPostCalls()
    {
        $this->collectionMock
            ->expects($this->once())
            ->method('getCollectionName')
            ->will($this->returnValue('users'));

        $this->clientMock
            ->expects($this->once())
            ->method('post')
            ->with('users', array('seat' => '20H'))
            ->will($this->returnValue('Hugo'));

        $this->assertEquals('Hugo', $this->collectionMock->doPost(array('seat' => '20H')));
    }
}
