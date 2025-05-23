<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Preview
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Preview\Understand\Assistant;

use Twilio\Exceptions\TwilioException;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;
use Twilio\InstanceContext;


class ModelBuildContext extends InstanceContext
    {
    /**
     * Initialize the ModelBuildContext
     *
     * @param Version $version Version that contains the resource
     * @param string $assistantSid 
     * @param string $sid 
     */
    public function __construct(
        Version $version,
        $assistantSid,
        $sid
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        'assistantSid' =>
            $assistantSid,
        'sid' =>
            $sid,
        ];

        $this->uri = '/Assistants/' . \rawurlencode($assistantSid)
        .'/ModelBuilds/' . \rawurlencode($sid)
        .'';
    }

    /**
     * Delete the ModelBuildInstance
     *
     * @return bool True if delete succeeds, false otherwise
     * @throws TwilioException When an HTTP error occurs.
     */
    public function delete(): bool
    {

        return $this->version->delete('DELETE', $this->uri);
    }


    /**
     * Fetch the ModelBuildInstance
     *
     * @return ModelBuildInstance Fetched ModelBuildInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): ModelBuildInstance
    {

        $payload = $this->version->fetch('GET', $this->uri);

        return new ModelBuildInstance(
            $this->version,
            $payload,
            $this->solution['assistantSid'],
            $this->solution['sid']
        );
    }


    /**
     * Update the ModelBuildInstance
     *
     * @param array|Options $options Optional Arguments
     * @return ModelBuildInstance Updated ModelBuildInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function update(array $options = []): ModelBuildInstance
    {

        $options = new Values($options);

        $data = Values::of([
            'UniqueName' =>
                $options['uniqueName'],
        ]);

        $payload = $this->version->update('POST', $this->uri, [], $data);

        return new ModelBuildInstance(
            $this->version,
            $payload,
            $this->solution['assistantSid'],
            $this->solution['sid']
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
        return '[Twilio.Preview.Understand.ModelBuildContext ' . \implode(' ', $context) . ']';
    }
}
