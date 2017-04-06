<?php
/**
 * @file
 * Contains \LushDigital\MicroServiceAggregatorCore\Traits\MicroServiceAggregatorControllerTrait.
 */

namespace LushDigital\MicroServiceAggregatorCore\Traits;

use GuzzleHttp\Exception\RequestException;

/**
 * A trait for MicroService aggregator controllers.
 *
 * @package LushDigital\MicroServiceAggregatorCore\Traits
 */
trait MicroServiceAggregatorControllerTrait
{
    /**
     * A HTTP response code.
     *
     * @var int
     */
    protected $responseCode = 200;

    /**
     * The response data.
     *
     * @var null|array
     */
    protected $responseData = NULL;

    /**
     * A short response status message.
     *
     * @var string
     */
    protected $responseStatus = 'ok';

    /**
     * The long response status message.
     *
     * @var string
     */
    protected $responseMessage;

    /**
     * Handle a RequestException thrown when calling a microservice.
     *
     * @param RequestException $exception
     * @return void
     */
    protected function handleServiceRequestException(RequestException $exception)
    {
        // Set the generic failure information.
        $this->responseCode = $exception->getCode();
        $this->responseStatus = 'fail';

        // Attempt to get a JSON response.
        $serviceResponse = json_decode($exception->getResponse()->getBody());
        if (!empty($serviceResponse)) {
            $this->responseMessage = $serviceResponse->message;

            // Display any errors if they are present.
            if (!empty($serviceResponse->data->errors)) {
                $this->responseData = [
                    'errors' => $serviceResponse->data->errors
                ];
            }
        }
        else {
            $this->responseMessage = $exception->getResponse()->getReasonPhrase();
        }
    }
}