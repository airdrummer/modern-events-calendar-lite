<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Events
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Events\V1\Subscription;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;


/**
 * @property string|null $accountSid
 * @property string|null $type
 * @property int|null $schemaVersion
 * @property string|null $subscriptionSid
 * @property string|null $url
 */
class SubscribedEventInstance extends InstanceResource
{
    /**
     * Initialize the SubscribedEventInstance
     *
     * @param Version $version Version that contains the resource
     * @param mixed[] $payload The response payload
     * @param string $subscriptionSid The unique SID identifier of the Subscription.
     * @param string $type Type of event being subscribed to.
     */
    public function __construct(Version $version, array $payload, string $subscriptionSid, string $type = null)
    {
        parent::__construct($version);

        // Marshaled Properties
        $this->properties = [
            'accountSid' => Values::array_get($payload, 'account_sid'),
            'type' => Values::array_get($payload, 'type'),
            'schemaVersion' => Values::array_get($payload, 'schema_version'),
            'subscriptionSid' => Values::array_get($payload, 'subscription_sid'),
            'url' => Values::array_get($payload, 'url'),
        ];

        $this->solution = ['subscriptionSid' => $subscriptionSid, 'type' => $type ?: $this->properties['type'], ];
    }

    /**
     * Generate an instance context for the instance, the context is capable of
     * performing various actions.  All instance actions are proxied to the context
     *
     * @return SubscribedEventContext Context for this SubscribedEventInstance
     */
    protected function proxy(): SubscribedEventContext
    {
        if (!$this->context) {
            $this->context = new SubscribedEventContext(
                $this->version,
                $this->solution['subscriptionSid'],
                $this->solution['type']
            );
        }

        return $this->context;
    }

    /**
     * Delete the SubscribedEventInstance
     *
     * @return bool True if delete succeeds, false otherwise
     * @throws TwilioException When an HTTP error occurs.
     */
    public function delete(): bool
    {

        return $this->proxy()->delete();
    }

    /**
     * Fetch the SubscribedEventInstance
     *
     * @return SubscribedEventInstance Fetched SubscribedEventInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): SubscribedEventInstance
    {

        return $this->proxy()->fetch();
    }

    /**
     * Update the SubscribedEventInstance
     *
     * @param array|Options $options Optional Arguments
     * @return SubscribedEventInstance Updated SubscribedEventInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function update(array $options = []): SubscribedEventInstance
    {

        return $this->proxy()->update($options);
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
        return '[Twilio.Events.V1.SubscribedEventInstance ' . \implode(' ', $context) . ']';
    }
}

