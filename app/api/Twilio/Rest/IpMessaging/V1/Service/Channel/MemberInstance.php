<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Ip_messaging
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\IpMessaging\V1\Service\Channel;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;
use Twilio\Deserialize;


/**
 * @property string|null $sid
 * @property string|null $accountSid
 * @property string|null $channelSid
 * @property string|null $serviceSid
 * @property string|null $identity
 * @property \DateTime|null $dateCreated
 * @property \DateTime|null $dateUpdated
 * @property string|null $roleSid
 * @property int|null $lastConsumedMessageIndex
 * @property \DateTime|null $lastConsumptionTimestamp
 * @property string|null $url
 */
class MemberInstance extends InstanceResource
{
    /**
     * Initialize the MemberInstance
     *
     * @param Version $version Version that contains the resource
     * @param mixed[] $payload The response payload
     * @param string $serviceSid 
     * @param string $channelSid 
     * @param string $sid 
     */
    public function __construct(Version $version, array $payload, string $serviceSid, string $channelSid, string $sid = null)
    {
        parent::__construct($version);

        // Marshaled Properties
        $this->properties = [
            'sid' => Values::array_get($payload, 'sid'),
            'accountSid' => Values::array_get($payload, 'account_sid'),
            'channelSid' => Values::array_get($payload, 'channel_sid'),
            'serviceSid' => Values::array_get($payload, 'service_sid'),
            'identity' => Values::array_get($payload, 'identity'),
            'dateCreated' => Deserialize::dateTime(Values::array_get($payload, 'date_created')),
            'dateUpdated' => Deserialize::dateTime(Values::array_get($payload, 'date_updated')),
            'roleSid' => Values::array_get($payload, 'role_sid'),
            'lastConsumedMessageIndex' => Values::array_get($payload, 'last_consumed_message_index'),
            'lastConsumptionTimestamp' => Deserialize::dateTime(Values::array_get($payload, 'last_consumption_timestamp')),
            'url' => Values::array_get($payload, 'url'),
        ];

        $this->solution = ['serviceSid' => $serviceSid, 'channelSid' => $channelSid, 'sid' => $sid ?: $this->properties['sid'], ];
    }

    /**
     * Generate an instance context for the instance, the context is capable of
     * performing various actions.  All instance actions are proxied to the context
     *
     * @return MemberContext Context for this MemberInstance
     */
    protected function proxy(): MemberContext
    {
        if (!$this->context) {
            $this->context = new MemberContext(
                $this->version,
                $this->solution['serviceSid'],
                $this->solution['channelSid'],
                $this->solution['sid']
            );
        }

        return $this->context;
    }

    /**
     * Delete the MemberInstance
     *
     * @return bool True if delete succeeds, false otherwise
     * @throws TwilioException When an HTTP error occurs.
     */
    public function delete(): bool
    {

        return $this->proxy()->delete();
    }

    /**
     * Fetch the MemberInstance
     *
     * @return MemberInstance Fetched MemberInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): MemberInstance
    {

        return $this->proxy()->fetch();
    }

    /**
     * Update the MemberInstance
     *
     * @param array|Options $options Optional Arguments
     * @return MemberInstance Updated MemberInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function update(array $options = []): MemberInstance
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
        return '[Twilio.IpMessaging.V1.MemberInstance ' . \implode(' ', $context) . ']';
    }
}

