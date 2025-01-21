<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Verify
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Verify\V2;

use Twilio\Exceptions\TwilioException;
use Twilio\Version;
use Twilio\InstanceContext;


class FormContext extends InstanceContext
    {
    /**
     * Initialize the FormContext
     *
     * @param Version $version Version that contains the resource
     * @param string $formType The Type of this Form. Currently only `form-push` is supported.
     */
    public function __construct(
        Version $version,
        $formType
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        'formType' =>
            $formType,
        ];

        $this->uri = '/Forms/' . \rawurlencode($formType)
        .'';
    }

    /**
     * Fetch the FormInstance
     *
     * @return FormInstance Fetched FormInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): FormInstance
    {

        $payload = $this->version->fetch('GET', $this->uri);

        return new FormInstance(
            $this->version,
            $payload,
            $this->solution['formType']
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
        return '[Twilio.Verify.V2.FormContext ' . \implode(' ', $context) . ']';
    }
}
