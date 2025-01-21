<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Insights
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Insights\V1\Call;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Values;
use Twilio\Version;


/**
 * @property string|null $timestamp
 * @property string|null $callSid
 * @property string|null $accountSid
 * @property string $edge
 * @property string $direction
 * @property array|null $carrierEdge
 * @property array|null $sipEdge
 * @property array|null $sdkEdge
 * @property array|null $clientEdge
 */
class MetricInstance extends InstanceResource
{
    /**
     * Initialize the MetricInstance
     *
     * @param Version $version Version that contains the resource
     * @param mixed[] $payload The response payload
     * @param string $callSid 
     */
    public function __construct(Version $version, array $payload, string $callSid)
    {
        parent::__construct($version);

        // Marshaled Properties
        $this->properties = [
            'timestamp' => Values::array_get($payload, 'timestamp'),
            'callSid' => Values::array_get($payload, 'call_sid'),
            'accountSid' => Values::array_get($payload, 'account_sid'),
            'edge' => Values::array_get($payload, 'edge'),
            'direction' => Values::array_get($payload, 'direction'),
            'carrierEdge' => Values::array_get($payload, 'carrier_edge'),
            'sipEdge' => Values::array_get($payload, 'sip_edge'),
            'sdkEdge' => Values::array_get($payload, 'sdk_edge'),
            'clientEdge' => Values::array_get($payload, 'client_edge'),
        ];

        $this->solution = ['callSid' => $callSid, ];
    }

    /**
     * Magic getter to access properties
     *
     * @param string $name Property to access
     * @return mixed The requested property
     * @throws TwilioException For unknown properties
     */
    public function __get(string $name)
    {
        if (\array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }

        if (\property_exists($this, '_' . $name)) {
            $method = 'get' . \ucfirst($name);
            return $this->$method();
        }

        throw new TwilioException('Unknown property: ' . $name);
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string
    {
        return '[Twilio.Insights.V1.MetricInstance]';
    }
}

