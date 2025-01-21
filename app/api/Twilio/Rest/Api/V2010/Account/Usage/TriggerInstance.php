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


namespace Twilio\Rest\Api\V2010\Account\Usage;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;
use Twilio\Deserialize;


/**
 * @property string|null $accountSid
 * @property string|null $apiVersion
 * @property string|null $callbackMethod
 * @property string|null $callbackUrl
 * @property string|null $currentValue
 * @property \DateTime|null $dateCreated
 * @property \DateTime|null $dateFired
 * @property \DateTime|null $dateUpdated
 * @property string|null $friendlyName
 * @property string $recurring
 * @property string|null $sid
 * @property string $triggerBy
 * @property string|null $triggerValue
 * @property string|null $uri
 * @property string $usageCategory
 * @property string|null $usageRecordUri
 */
class TriggerInstance extends InstanceResource
{
    /**
     * Initialize the TriggerInstance
     *
     * @param Version $version Version that contains the resource
     * @param mixed[] $payload The response payload
     * @param string $accountSid The SID of the [Account](https://www.twilio.com/docs/iam/api/account) that will create the resource.
     * @param string $sid The Twilio-provided string that uniquely identifies the UsageTrigger resource to delete.
     */
    public function __construct(Version $version, array $payload, string $accountSid, string $sid = null)
    {
        parent::__construct($version);

        // Marshaled Properties
        $this->properties = [
            'accountSid' => Values::array_get($payload, 'account_sid'),
            'apiVersion' => Values::array_get($payload, 'api_version'),
            'callbackMethod' => Values::array_get($payload, 'callback_method'),
            'callbackUrl' => Values::array_get($payload, 'callback_url'),
            'currentValue' => Values::array_get($payload, 'current_value'),
            'dateCreated' => Deserialize::dateTime(Values::array_get($payload, 'date_created')),
            'dateFired' => Deserialize::dateTime(Values::array_get($payload, 'date_fired')),
            'dateUpdated' => Deserialize::dateTime(Values::array_get($payload, 'date_updated')),
            'friendlyName' => Values::array_get($payload, 'friendly_name'),
            'recurring' => Values::array_get($payload, 'recurring'),
            'sid' => Values::array_get($payload, 'sid'),
            'triggerBy' => Values::array_get($payload, 'trigger_by'),
            'triggerValue' => Values::array_get($payload, 'trigger_value'),
            'uri' => Values::array_get($payload, 'uri'),
            'usageCategory' => Values::array_get($payload, 'usage_category'),
            'usageRecordUri' => Values::array_get($payload, 'usage_record_uri'),
        ];

        $this->solution = ['accountSid' => $accountSid, 'sid' => $sid ?: $this->properties['sid'], ];
    }

    /**
     * Generate an instance context for the instance, the context is capable of
     * performing various actions.  All instance actions are proxied to the context
     *
     * @return TriggerContext Context for this TriggerInstance
     */
    protected function proxy(): TriggerContext
    {
        if (!$this->context) {
            $this->context = new TriggerContext(
                $this->version,
                $this->solution['accountSid'],
                $this->solution['sid']
            );
        }

        return $this->context;
    }

    /**
     * Delete the TriggerInstance
     *
     * @return bool True if delete succeeds, false otherwise
     * @throws TwilioException When an HTTP error occurs.
     */
    public function delete(): bool
    {

        return $this->proxy()->delete();
    }

    /**
     * Fetch the TriggerInstance
     *
     * @return TriggerInstance Fetched TriggerInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): TriggerInstance
    {

        return $this->proxy()->fetch();
    }

    /**
     * Update the TriggerInstance
     *
     * @param array|Options $options Optional Arguments
     * @return TriggerInstance Updated TriggerInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function update(array $options = []): TriggerInstance
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
        return '[Twilio.Api.V2010.TriggerInstance ' . \implode(' ', $context) . ']';
    }
}

