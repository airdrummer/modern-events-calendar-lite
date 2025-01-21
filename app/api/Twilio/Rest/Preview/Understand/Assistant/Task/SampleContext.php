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


namespace Twilio\Rest\Preview\Understand\Assistant\Task;

use Twilio\Exceptions\TwilioException;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;
use Twilio\InstanceContext;


class SampleContext extends InstanceContext
    {
    /**
     * Initialize the SampleContext
     *
     * @param Version $version Version that contains the resource
     * @param string $assistantSid The unique ID of the Assistant.
     * @param string $taskSid The unique ID of the Task associated with this Sample.
     * @param string $sid A 34 character string that uniquely identifies this resource.
     */
    public function __construct(
        Version $version,
        $assistantSid,
        $taskSid,
        $sid
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        'assistantSid' =>
            $assistantSid,
        'taskSid' =>
            $taskSid,
        'sid' =>
            $sid,
        ];

        $this->uri = '/Assistants/' . \rawurlencode($assistantSid)
        .'/Tasks/' . \rawurlencode($taskSid)
        .'/Samples/' . \rawurlencode($sid)
        .'';
    }

    /**
     * Delete the SampleInstance
     *
     * @return bool True if delete succeeds, false otherwise
     * @throws TwilioException When an HTTP error occurs.
     */
    public function delete(): bool
    {

        return $this->version->delete('DELETE', $this->uri);
    }


    /**
     * Fetch the SampleInstance
     *
     * @return SampleInstance Fetched SampleInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): SampleInstance
    {

        $payload = $this->version->fetch('GET', $this->uri);

        return new SampleInstance(
            $this->version,
            $payload,
            $this->solution['assistantSid'],
            $this->solution['taskSid'],
            $this->solution['sid']
        );
    }


    /**
     * Update the SampleInstance
     *
     * @param array|Options $options Optional Arguments
     * @return SampleInstance Updated SampleInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function update(array $options = []): SampleInstance
    {

        $options = new Values($options);

        $data = Values::of([
            'Language' =>
                $options['language'],
            'TaggedText' =>
                $options['taggedText'],
            'SourceChannel' =>
                $options['sourceChannel'],
        ]);

        $payload = $this->version->update('POST', $this->uri, [], $data);

        return new SampleInstance(
            $this->version,
            $payload,
            $this->solution['assistantSid'],
            $this->solution['taskSid'],
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
        return '[Twilio.Preview.Understand.SampleContext ' . \implode(' ', $context) . ']';
    }
}
