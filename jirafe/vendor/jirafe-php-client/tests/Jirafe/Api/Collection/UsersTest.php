<?php

class Jirafe_Api_Collection_UsersTest extends PHPUnit_Framework_TestCase
{
    private $clientMock;
    private $users;

    protected function setUp()
    {
        $this->clientMock = $this->getMockBuilder('Jirafe_Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->users = new Jirafe_Api_Collection_Users($this->clientMock);
    }

    /**
     * @test
     */
    public function shouldProvideUserResource()
    {
        $user = $this->users->get(12);

        $this->assertInstanceOf('Jirafe_Api_Resource_User', $user);
        $this->assertEquals(12, $user->getId());
    }

    /**
     * @test
     */
    public function shouldProvideCorrectPath()
    {
        $this->assertEquals('users', $this->users->getPath());
    }

    /**
     * @test
     */
    public function shouldBeAbleToFetchAllUsers()
    {
        $this->clientMock
            ->expects($this->once())
            ->method('get')
            ->with('users', array())
            ->will($this->returnValue(new Jirafe_HttpConnection_Response('"list"', array(), 0, '')));

        $this->assertEquals('list', $this->users->fetchAll());
    }

    /**
     * @test
     */
    public function shouldBeAbleToCreateNewUser()
    {
        $this->clientMock
            ->expects($this->once())
            ->method('post')
            ->with('users', array(), array('username' => 'vjousse', 'email' => 'vjousse@knplabs.com'))
            ->will($this->returnValue(new Jirafe_HttpConnection_Response('"val"', array(), 0, '')));

        $this->assertEquals('val', $this->users->create('vjousse', 'vjousse@knplabs.com'));
    }

    /**
     * @test
     * @expectedException Jirafe_Exception
     */
    public function shouldThrowExceptionOnCreateError()
    {
        $this->clientMock
            ->expects($this->once())
            ->method('post')
            ->with('users', array(), array('username' => 'vjousse', 'email' => 'vjousse@knplabs.com'))
            ->will($this->returnValue(new Jirafe_HttpConnection_Response('"val"', array(), 2, '')));

        $this->assertEquals('val', $this->users->create('vjousse', 'vjousse@knplabs.com'));
    }
}
