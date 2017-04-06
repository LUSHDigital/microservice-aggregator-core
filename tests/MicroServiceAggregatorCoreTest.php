<?php
/**
 * @file
 * Contains \MicroServiceAggregatorCoreTest.
 */

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use LushDigital\MicroServiceAggregatorCore\Traits\MicroServiceAggregatorControllerTrait;

/**
 * Test the core microservice aggregator functionality.
 */
class MicroServiceAggregatorCoreTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test the aggregator exception handler.
     *
     * @return void
     */
    public function testAggregatorExceptionHandler()
    {

        $testController = new TestController();

        // Test a simple error.
        $testController->fakeException();
        $this->assertEquals(HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR, $testController->getResponseCode());
        $this->assertEquals(null, $testController->getResponseData());
        $this->assertEquals('fail', $testController->getResponseStatus());
        $this->assertEquals(HttpFoundationResponse::$statusTexts[HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR], $testController->getResponseMessage());

        // Test a json error.
        $testController->fakeJsonException(json_encode($this->getJsonErrorData()));
        $this->assertEquals(HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY, $testController->getResponseCode());
        $this->assertEquals((array) $this->getJsonErrorData()->data, $testController->getResponseData());
        $this->assertEquals('fail', $testController->getResponseStatus());
        $this->assertEquals('The given data failed to pass validation.', $testController->getResponseMessage());
    }

    /**
     * Prepare a set of example JSON error data.
     *
     * @return stdClass
     */
    protected function getJsonErrorData()
    {
        $error = new stdClass();
        $error->status = 'fail';
        $error->code = 422;
        $error->message = 'The given data failed to pass validation.';

        $error->data = new stdClass();
        $error->data->errors = new stdClass();
        $error->data->errors->transaction_number = [
            'The transaction number field is required.'
        ];

        return $error;
    }
}

/**
 * An test controller.
 */
class TestController
{
    use MicroServiceAggregatorControllerTrait;

    /**
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @return array|null
     */
    public function getResponseData()
    {
        return $this->responseData;
    }

    /**
     * @return string
     */
    public function getResponseStatus()
    {
        return $this->responseStatus;
    }

    /**
     * @return string
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * Fire off a fake exception to test with.
     */
    public function fakeException()
    {
        // Prepare a fake request and response.
        $req = new Request('GET', '/');
        $res = new Response(HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);

        // Create the exception.
        $this->handleServiceRequestException(new RequestException('foo', $req, $res));
    }

    /**
     * Fire off a fake JSON exception to test with.
     *
     * @param string
     *     A JSON body to place in the fake response.
     */
    public function fakeJsonException($json)
    {
        // Prepare a fake request and response.
        $req = new Request('GET', '/');
        $res = new Response(HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY, ['Content-Type' => 'application/json'], $json);

        // Create the exception.
        $this->handleServiceRequestException(new RequestException('foo', $req, $res));
    }
}