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
use Twilio\Version;
use Twilio\InstanceContext;


class DialogueContext extends InstanceContext
    {
    /**
     * Initialize the DialogueContext
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
        .'/Dialogues/' . \rawurlencode($sid)
        .'';
    }

    /**
     * Fetch the DialogueInstance
     *
     * @return DialogueInstance Fetched DialogueInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): DialogueInstance
    {

        $payload = $this->version->fetch('GET', $this->uri);

        return new DialogueInstance(
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
        return '[Twilio.Preview.Understand.DialogueContext ' . \implode(' ', $context) . ']';
    }
}
