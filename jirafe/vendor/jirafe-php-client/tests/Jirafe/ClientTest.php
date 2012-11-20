<?php

class Jirafe_ClientTest extends PHPUnit_Framework_TestCase
{
    private $connectionMock;
    private $client;

    protected function setUp()
    {
        $this->connectionMock = $this->getMockBuilder('Jirafe_HttpConnection_Interface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->client = new Jirafe_Client('SECRET_TOKEN', $this->connectionMock);
    }

    /**
     * @test
     */
    public function shouldProvideSettedToken()
    {
        $this->assertEquals('SECRET_TOKEN', $this->client->getToken());
    }

    /**
     * @test
     */
    public function shouldProvideApplicationsCollection()
    {
        $this->assertInstanceOf('Jirafe_Api_Collection_Applications', $this->client->applications());
    }

    /**
     * @test
     */
    public function shouldProvideSpecificApplicationResource()
    {
        $this->assertInstanceOf('Jirafe_Api_Resource_Application', $this->client->applications(1));
    }

    /**
     * @test
     */
    public function shouldProvideUsersCollection()
    {
        $this->assertInstanceOf('Jirafe_Api_Collection_Users', $this->client->users());
    }

    /**
     * @test
     */
    public function shouldProvideSpecificUserResource()
    {
        $this->assertInstanceOf('Jirafe_Api_Resource_User', $this->client->users(1));
    }

    /**
     * @test
     */
    public function shouldMakeGetRequestWithToken()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('get')
            ->with('/test', array(
                'token'  => 'SECRET_TOKEN',
                'custom' => '4 8 15 16 23 42'
            ));

        $this->client->get('/test', array('custom' => '4 8 15 16 23 42'));
    }

    /**
     * @test
     */
    public function shouldMakeGetRequestWithCustomToken()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('get')
            ->with('/test', array(
                'token'  => 'CUSTOM_TOKEN',
                'custom' => '4 8 15 16 23 42'
            ));

        $this->client->get('/test', array('custom' => '4 8 15 16 23 42'), 'CUSTOM_TOKEN');
    }

    /**
     * @test
     */
    public function shouldMakeGetRequestWithoutToken()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('get')
            ->with('/test', array(
                'custom' => '4 8 15 16 23 42'
            ));

        $this->client->get('/test', array('custom' => '4 8 15 16 23 42'), false);
    }

    /**
     * @test
     */
    public function shouldMakeHeadRequestWithToken()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('head')
            ->with('/test', array(
                'token'  => 'SECRET_TOKEN',
                'custom' => '4 8 15 16 23 42'
            ));

        $this->client->head('/test', array('custom' => '4 8 15 16 23 42'));
    }

    /**
     * @test
     */
    public function shouldMakeHeadRequestWithCustomToken()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('head')
            ->with('/test', array(
                'token'  => 'CUSTOM_TOKEN',
                'custom' => '4 8 15 16 23 42'
            ));

        $this->client->head('/test', array('custom' => '4 8 15 16 23 42'), 'CUSTOM_TOKEN');
    }

    /**
     * @test
     */
    public function shouldMakeHeadRequestWithoutToken()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('head')
            ->with('/test', array(
                'custom' => '4 8 15 16 23 42'
            ));

        $this->client->head('/test', array('custom' => '4 8 15 16 23 42'), false);
    }

    /**
     * @test
     */
    public function shouldMakePostRequestWithToken()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('post')
            ->with('/test', array(
                'token'  => 'SECRET_TOKEN',
                'custom' => '4 8 15 16 23 42'
            ), array('name' => 'Jacob'));

        $this->client->post('/test', array('custom' => '4 8 15 16 23 42'), array('name' => 'Jacob'));
    }

    /**
     * @test
     */
    public function shouldMakePostRequestWithCustomToken()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('post')
            ->with('/test', array(
                'token'  => 'ADMIN',
                'custom' => '4 8 15 16 23 42'
            ), array('name' => 'Jacob'));

        $this->client->post('/test', array('custom' => '4 8 15 16 23 42'), array('name' => 'Jacob'), 'ADMIN');
    }

    /**
     * @test
     */
    public function shouldMakePostRequestWithoutToken()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('post')
            ->with('/test', array(
                'custom' => '4 8 15 16 23 42'
            ), array('name' => 'Jacob'));

        $this->client->post('/test', array('custom' => '4 8 15 16 23 42'), array('name' => 'Jacob'), false);
    }

    /**
     * @test
     */
    public function shouldMakePutRequestWithToken()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('put')
            ->with('/test', array(
                'token'  => 'SECRET_TOKEN',
                'custom' => '4 8 15 16 23 42'
            ), array('name' => 'Jacob'));

        $this->client->put('/test', array('custom' => '4 8 15 16 23 42'), array('name' => 'Jacob'));
    }

    /**
     * @test
     */
    public function shouldMakePutRequestWithCustomToken()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('put')
            ->with('/test', array(
                'token'  => 'ADMIN',
                'custom' => '4 8 15 16 23 42'
            ), array('name' => 'Jacob'));

        $this->client->put('/test', array('custom' => '4 8 15 16 23 42'), array('name' => 'Jacob'), 'ADMIN');
    }

    /**
     * @test
     */
    public function shouldMakePutRequestWithoutToken()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('put')
            ->with('/test', array(
                'custom' => '4 8 15 16 23 42'
            ), array('name' => 'Jacob'));

        $this->client->put('/test', array('custom' => '4 8 15 16 23 42'), array('name' => 'Jacob'), false);
    }

    /**
     * @test
     */
    public function shouldMakeDeleteRequestWithToken()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('delete')
            ->with('/test', array(
                'token'  => 'SECRET_TOKEN',
                'custom' => '4 8 15 16 23 42'
            ), array('name' => 'Jacob'));

        $this->client->delete(
            '/test', array('custom' => '4 8 15 16 23 42'), array('name' => 'Jacob')
        );
    }

    /**
     * @test
     */
    public function shouldMakeDeleteRequestWithCustomToken()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('delete')
            ->with('/test', array(
                'token'  => 'ADMIN_TOKEN',
                'custom' => '4 8 15 16 23 42'
            ), array('name' => 'Jacob'));

        $this->client->delete(
            '/test', array('custom' => '4 8 15 16 23 42'), array('name' => 'Jacob'), 'ADMIN_TOKEN'
        );
    }

    /**
     * @test
     */
    public function shouldMakeDeleteRequestWithoutToken()
    {
        $this->connectionMock
            ->expects($this->once())
            ->method('delete')
            ->with('/test', array(
                'custom' => '4 8 15 16 23 42'
            ), array('name' => 'Jacob'));

        $this->client->delete(
            '/test', array('custom' => '4 8 15 16 23 42'), array('name' => 'Jacob'), false
        );
    }
}
