<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Studio
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Studio\V2\Flow\Execution\ExecutionStep;

use Twilio\Exceptions\TwilioException;
use Twilio\Version;
use Twilio\InstanceContext;


class ExecutionStepContextContext extends InstanceContext
    {
    /**
     * Initialize the ExecutionStepContextContext
     *
     * @param Version $version Version that contains the resource
     * @param string $flowSid The SID of the Flow with the Step to fetch.
     * @param string $executionSid The SID of the Execution resource with the Step to fetch.
     * @param string $stepSid The SID of the Step to fetch.
     */
    public function __construct(
        Version $version,
        $flowSid,
        $executionSid,
        $stepSid
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        'flowSid' =>
            $flowSid,
        'executionSid' =>
            $executionSid,
        'stepSid' =>
            $stepSid,
        ];

        $this->uri = '/Flows/' . \rawurlencode($flowSid)
        .'/Executions/' . \rawurlencode($executionSid)
        .'/Steps/' . \rawurlencode($stepSid)
        .'/Context';
    }

    /**
     * Fetch the ExecutionStepContextInstance
     *
     * @return ExecutionStepContextInstance Fetched ExecutionStepContextInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): ExecutionStepContextInstance
    {

        $payload = $this->version->fetch('GET', $this->uri);

        return new ExecutionStepContextInstance(
            $this->version,
            $payload,
            $this->solution['flowSid'],
            $this->solution['executionSid'],
            $this->solution['stepSid']
        );
    }


    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string
    {
        $context = [];
        foreach ($this->solution as $key => $value) {
            $context[] = "$key=$value";
        }
        return '[Twilio.Studio.V2.ExecutionStepContextContext ' . \implode(' ', $context) . ']';
    }
}
