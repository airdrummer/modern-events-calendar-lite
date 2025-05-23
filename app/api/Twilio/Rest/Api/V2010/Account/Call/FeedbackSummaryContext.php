<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Api
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Api\V2010\Account\Call;

use Twilio\Exceptions\TwilioException;
use Twilio\Version;
use Twilio\InstanceContext;


class FeedbackSummaryContext extends InstanceContext
    {
    /**
     * Initialize the FeedbackSummaryContext
     *
     * @param Version $version Version that contains the resource
     * @param string $accountSid The unique id of the [Account](https://www.twilio.com/docs/iam/api/account) responsible for this resource.
     * @param string $sid A 34 character string that uniquely identifies this resource.
     */
    public function __construct(
        Version $version,
        $accountSid,
        $sid
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        'accountSid' =>
            $accountSid,
        'sid' =>
            $sid,
        ];

        $this->uri = '/Accounts/' . \rawurlencode($accountSid)
        .'/Calls/FeedbackSummary/' . \rawurlencode($sid)
        .'.json';
    }

    /**
     * Delete the FeedbackSummaryInstance
     *
     * @return bool True if delete succeeds, false otherwise
     * @throws TwilioException When an HTTP error occurs.
     */
    public function delete(): bool
    {

        return $this->version->delete('DELETE', $this->uri);
    }


    /**
     * Fetch the FeedbackSummaryInstance
     *
     * @return FeedbackSummaryInstance Fetched FeedbackSummaryInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): FeedbackSummaryInstance
    {

        $payload = $this->version->fetch('GET', $this->uri);

        return new FeedbackSummaryInstance(
            $this->version,
            $payload,
            $this->solution['accountSid'],
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
        return '[Twilio.Api.V2010.FeedbackSummaryContext ' . \implode(' ', $context) . ']';
    }
}
