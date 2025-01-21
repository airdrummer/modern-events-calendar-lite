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
use Twilio\Serialize;


class StyleSheetContext extends InstanceContext
    {
    /**
     * Initialize the StyleSheetContext
     *
     * @param Version $version Version that contains the resource
     * @param string $assistantSid The unique ID of the Assistant
     */
    public function __construct(
        Version $version,
        $assistantSid
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        'assistantSid' =>
            $assistantSid,
        ];

        $this->uri = '/Assistants/' . \rawurlencode($assistantSid)
        .'/StyleSheet';
    }

    /**
     * Fetch the StyleSheetInstance
     *
     * @return StyleSheetInstance Fetched StyleSheetInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): StyleSheetInstance
    {

        $payload = $this->version->fetch('GET', $this->uri);

        return new StyleSheetInstance(
            $this->version,
            $payload,
            $this->solution['assistantSid']
        );
    }


    /**
     * Update the StyleSheetInstance
     *
     * @param array|Options $options Optional Arguments
     * @return StyleSheetInstance Updated StyleSheetInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function update(array $options = []): StyleSheetInstance
    {

        $options = new Values($options);

        $data = Values::of([
            'StyleSheet' =>
                Serialize::jsonObject($options['styleSheet']),
        ]);

        $payload = $this->version->update('POST', $this->uri, [], $data);

        return new StyleSheetInstance(
            $this->version,
            $payload,
            $this->solution['assistantSid']
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
        return '[Twilio.Preview.Understand.StyleSheetContext ' . \implode(' ', $context) . ']';
    }
}
