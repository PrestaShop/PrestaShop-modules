<?php

class Jirafe_HttpConnection_ResponseTest extends PHPUnit_Framework_TestCase
{
    private $response;

    protected function setUp()
    {
        $this->response = new Jirafe_HttpConnection_Response(
            '{"message": "nice to see you here", "date": [21,6,2011]}',
            array('header1', 'header2', 'header3'),
            404,
            'no errors'
        );
    }

    /**
     * @test
     */
    public function shouldProvideResponseBody()
    {
        $this->assertEquals(
            '{"message": "nice to see you here", "date": [21,6,2011]}',
            $this->response->getBody()
        );

        $this->assertEquals(
            '{"message": "nice to see you here", "date": [21,6,2011]}',
            (string) $this->response
        );

    }

    /**
     * @test
     */
    public function shouldProvideDecodedResponseBody()
    {
        $this->assertEquals(array(
            'message' => 'nice to see you here',
            'date' => array(21,6,2011)
        ), $this->response->getJson());
    }

    /**
     * @test
     */
    public function shouldProvideHeaders()
    {
        $this->assertEquals(array('header1', 'header2', 'header3'), $this->response->getHeaders());
    }

    /**
     * @test
     */
    public function shouldProvideErrorInformationIfHasOne()
    {
        $this->assertEquals(404, $this->response->getErrorCode());
        $this->assertEquals('no errors', $this->response->getErrorMessage());
        $this->assertTrue($this->response->hasError());
    }

    /**
     * @test
     */
    public function shouldNotProvideErrorInformationIfHasNotAny()
    {
        $response = new Jirafe_HttpConnection_Response(
            $this->response->getBody(), $this->response->getHeaders(), 0, ''
        );

        $this->assertFalse($response->hasError());
    }
}
