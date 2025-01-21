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
use Twilio\ListResource;
use Twilio\Values;
use Twilio\Version;


class SafelistList extends ListResource
    {
    /**
     * Construct the SafelistList
     *
     * @param Version $version Version that contains the resource
     */
    public function __construct(
        Version $version
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        ];

        $this->uri = '/SafeList/Numbers';
    }

    /**
     * Create the SafelistInstance
     *
     * @param string $phoneNumber The phone number to be added in SafeList. Phone numbers must be in [E.164 format](https://www.twilio.com/docs/glossary/what-e164).
     * @return SafelistInstance Created SafelistInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function create(string $phoneNumber): SafelistInstance
    {

        $data = Values::of([
            'PhoneNumber' =>
                $phoneNumber,
        ]);

        $payload = $this->version->create('POST', $this->uri, [], $data);

        return new SafelistInstance(
            $this->version,
            $payload
        );
    }


    /**
     * Constructs a SafelistContext
     *
     * @param string $phoneNumber The phone number to be removed from SafeList. Phone numbers must be in [E.164 format](https://www.twilio.com/docs/glossary/what-e164).
     */
    public function getContext(
        string $phoneNumber
        
    ): SafelistContext
    {
        return new SafelistContext(
            $this->version,
            $phoneNumber
        );
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string
    {
        return '[Twilio.Verify.V2.SafelistList]';
    }
}
