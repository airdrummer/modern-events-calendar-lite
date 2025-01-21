<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Messaging
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Messaging\V1;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Values;
use Twilio\Version;


/**
 * @property string|null $domainSid
 * @property string|null $messagingServiceSid
 * @property string|null $url
 */
class LinkshorteningMessagingServiceInstance extends InstanceResource
{
    /**
     * Initialize the LinkshorteningMessagingServiceInstance
     *
     * @param Version $version Version that contains the resource
     * @param mixed[] $payload The response payload
     * @param string $domainSid The domain SID to associate with a messaging service. With URL shortening enabled, links in messages sent with the associated messaging service will be shortened to the provided domain
     * @param string $messagingServiceSid A messaging service SID to associate with a domain. With URL shortening enabled, links in messages sent with the provided messaging service will be shortened to the associated domain
     */
    public function __construct(Version $version, array $payload, string $domainSid = null, string $messagingServiceSid = null)
    {
        parent::__construct($version);

        // Marshaled Properties
        $this->properties = [
            'domainSid' => Values::array_get($payload, 'domain_sid'),
            'messagingServiceSid' => Values::array_get($payload, 'messaging_service_sid'),
            'url' => Values::array_get($payload, 'url'),
        ];

        $this->solution = ['domainSid' => $domainSid ?: $this->properties['domainSid'], 'messagingServiceSid' => $messagingServiceSid ?: $this->properties['messagingServiceSid'], ];
    }

    /**
     * Generate an instance context for the instance, the context is capable of
     * performing various actions.  All instance actions are proxied to the context
     *
     * @return LinkshorteningMessagingServiceContext Context for this LinkshorteningMessagingServiceInstance
     */
    protected function proxy(): LinkshorteningMessagingServiceContext
    {
        if (!$this->context) {
            $this->context = new LinkshorteningMessagingServiceContext(
                $this->version,
                $this->solution['domainSid'],
                $this->solution['messagingServiceSid']
            );
        }

        return $this->context;
    }

    /**
     * Create the LinkshorteningMessagingServiceInstance
     *
     * @return LinkshorteningMessagingServiceInstance Created LinkshorteningMessagingServiceInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function create(): LinkshorteningMessagingServiceInstance
    {

        return $this->proxy()->create();
    }

    /**
     * Delete the LinkshorteningMessagingServiceInstance
     *
     * @return bool True if delete succeeds, false otherwise
     * @throws TwilioException When an HTTP error occurs.
     */
    public function delete(): bool
    {

        return $this->proxy()->delete();
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
        $context = [];
        foreach ($this->solution as $key => $value) {
            $context[] = "$key=$value";
        }
        return '[Twilio.Messaging.V1.LinkshorteningMessagingServiceInstance ' . \implode(' ', $context) . ']';
    }
}

