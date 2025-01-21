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


namespace Twilio\Rest\Verify\V2\Service\Entity;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;
use Twilio\Deserialize;


/**
 * @property string|null $sid
 * @property string|null $accountSid
 * @property string|null $serviceSid
 * @property string|null $entitySid
 * @property string|null $identity
 * @property \DateTime|null $dateCreated
 * @property \DateTime|null $dateUpdated
 * @property string|null $friendlyName
 * @property string $status
 * @property string $factorType
 * @property array|null $config
 * @property array|null $metadata
 * @property string|null $url
 */
class FactorInstance extends InstanceResource
{
    /**
     * Initialize the FactorInstance
     *
     * @param Version $version Version that contains the resource
     * @param mixed[] $payload The response payload
     * @param string $serviceSid The unique SID identifier of the Service.
     * @param string $identity Customer unique identity for the Entity owner of the Factor. This identifier should be immutable, not PII, length between 8 and 64 characters, and generated by your external system, such as your user's UUID, GUID, or SID. It can only contain dash (-) separated alphanumeric characters.
     * @param string $sid A 34 character string that uniquely identifies this Factor.
     */
    public function __construct(Version $version, array $payload, string $serviceSid, string $identity, string $sid = null)
    {
        parent::__construct($version);

        // Marshaled Properties
        $this->properties = [
            'sid' => Values::array_get($payload, 'sid'),
            'accountSid' => Values::array_get($payload, 'account_sid'),
            'serviceSid' => Values::array_get($payload, 'service_sid'),
            'entitySid' => Values::array_get($payload, 'entity_sid'),
            'identity' => Values::array_get($payload, 'identity'),
            'dateCreated' => Deserialize::dateTime(Values::array_get($payload, 'date_created')),
            'dateUpdated' => Deserialize::dateTime(Values::array_get($payload, 'date_updated')),
            'friendlyName' => Values::array_get($payload, 'friendly_name'),
            'status' => Values::array_get($payload, 'status'),
            'factorType' => Values::array_get($payload, 'factor_type'),
            'config' => Values::array_get($payload, 'config'),
            'metadata' => Values::array_get($payload, 'metadata'),
            'url' => Values::array_get($payload, 'url'),
        ];

        $this->solution = ['serviceSid' => $serviceSid, 'identity' => $identity, 'sid' => $sid ?: $this->properties['sid'], ];
    }

    /**
     * Generate an instance context for the instance, the context is capable of
     * performing various actions.  All instance actions are proxied to the context
     *
     * @return FactorContext Context for this FactorInstance
     */
    protected function proxy(): FactorContext
    {
        if (!$this->context) {
            $this->context = new FactorContext(
                $this->version,
                $this->solution['serviceSid'],
                $this->solution['identity'],
                $this->solution['sid']
            );
        }

        return $this->context;
    }

    /**
     * Delete the FactorInstance
     *
     * @return bool True if delete succeeds, false otherwise
     * @throws TwilioException When an HTTP error occurs.
     */
    public function delete(): bool
    {

        return $this->proxy()->delete();
    }

    /**
     * Fetch the FactorInstance
     *
     * @return FactorInstance Fetched FactorInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): FactorInstance
    {

        return $this->proxy()->fetch();
    }

    /**
     * Update the FactorInstance
     *
     * @param array|Options $options Optional Arguments
     * @return FactorInstance Updated FactorInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function update(array $options = []): FactorInstance
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
        return '[Twilio.Verify.V2.FactorInstance ' . \implode(' ', $context) . ']';
    }
}

