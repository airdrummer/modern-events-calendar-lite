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

namespace Twilio\Rest\Verify\V2\Service;

use Twilio\Exceptions\TwilioException;
use Twilio\ListResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;
use Twilio\Serialize;


class VerificationList extends ListResource
    {
    /**
     * Construct the VerificationList
     *
     * @param Version $version Version that contains the resource
     * @param string $serviceSid The SID of the verification [Service](https://www.twilio.com/docs/verify/api/service) to create the resource under.
     */
    public function __construct(
        Version $version,
        string $serviceSid
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        'serviceSid' =>
            $serviceSid,
        
        ];

        $this->uri = '/Services/' . \rawurlencode($serviceSid)
        .'/Verifications';
    }

    /**
     * Create the VerificationInstance
     *
     * @param string $to The phone number or [email](https://www.twilio.com/docs/verify/email) to verify. Phone numbers must be in [E.164 format](https://www.twilio.com/docs/glossary/what-e164).
     * @param string $channel The verification method to use. One of: [`email`](https://www.twilio.com/docs/verify/email), `sms`, `whatsapp`, `call`, `sna` or `auto`.
     * @param array|Options $options Optional Arguments
     * @return VerificationInstance Created VerificationInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function create(string $to, string $channel, array $options = []): VerificationInstance
    {

        $options = new Values($options);

        $data = Values::of([
            'To' =>
                $to,
            'Channel' =>
                $channel,
            'CustomFriendlyName' =>
                $options['customFriendlyName'],
            'CustomMessage' =>
                $options['customMessage'],
            'SendDigits' =>
                $options['sendDigits'],
            'Locale' =>
                $options['locale'],
            'CustomCode' =>
                $options['customCode'],
            'Amount' =>
                $options['amount'],
            'Payee' =>
                $options['payee'],
            'RateLimits' =>
                Serialize::jsonObject($options['rateLimits']),
            'ChannelConfiguration' =>
                Serialize::jsonObject($options['channelConfiguration']),
            'AppHash' =>
                $options['appHash'],
            'TemplateSid' =>
                $options['templateSid'],
            'TemplateCustomSubstitutions' =>
                $options['templateCustomSubstitutions'],
            'DeviceIp' =>
                $options['deviceIp'],
        ]);

        $payload = $this->version->create('POST', $this->uri, [], $data);

        return new VerificationInstance(
            $this->version,
            $payload,
            $this->solution['serviceSid']
        );
    }


    /**
     * Constructs a VerificationContext
     *
     * @param string $sid The Twilio-provided string that uniquely identifies the Verification resource to fetch.
     */
    public function getContext(
        string $sid
        
    ): VerificationContext
    {
        return new VerificationContext(
            $this->version,
            $this->solution['serviceSid'],
            $sid
        );
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string
    {
        return '[Twilio.Verify.V2.VerificationList]';
    }
}
