<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Trusthub
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Trusthub\V1;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;
use Twilio\Deserialize;
use Twilio\Rest\Trusthub\V1\CustomerProfiles\CustomerProfilesChannelEndpointAssignmentList;
use Twilio\Rest\Trusthub\V1\CustomerProfiles\CustomerProfilesEntityAssignmentsList;
use Twilio\Rest\Trusthub\V1\CustomerProfiles\CustomerProfilesEvaluationsList;


/**
 * @property string|null $sid
 * @property string|null $accountSid
 * @property string|null $policySid
 * @property string|null $friendlyName
 * @property string $status
 * @property \DateTime|null $validUntil
 * @property string|null $email
 * @property string|null $statusCallback
 * @property \DateTime|null $dateCreated
 * @property \DateTime|null $dateUpdated
 * @property string|null $url
 * @property array|null $links
 */
class CustomerProfilesInstance extends InstanceResource
{
    protected $_customerProfilesChannelEndpointAssignment;
    protected $_customerProfilesEntityAssignments;
    protected $_customerProfilesEvaluations;

    /**
     * Initialize the CustomerProfilesInstance
     *
     * @param Version $version Version that contains the resource
     * @param mixed[] $payload The response payload
     * @param string $sid The unique string that we created to identify the Customer-Profile resource.
     */
    public function __construct(Version $version, array $payload, string $sid = null)
    {
        parent::__construct($version);

        // Marshaled Properties
        $this->properties = [
            'sid' => Values::array_get($payload, 'sid'),
            'accountSid' => Values::array_get($payload, 'account_sid'),
            'policySid' => Values::array_get($payload, 'policy_sid'),
            'friendlyName' => Values::array_get($payload, 'friendly_name'),
            'status' => Values::array_get($payload, 'status'),
            'validUntil' => Deserialize::dateTime(Values::array_get($payload, 'valid_until')),
            'email' => Values::array_get($payload, 'email'),
            'statusCallback' => Values::array_get($payload, 'status_callback'),
            'dateCreated' => Deserialize::dateTime(Values::array_get($payload, 'date_created')),
            'dateUpdated' => Deserialize::dateTime(Values::array_get($payload, 'date_updated')),
            'url' => Values::array_get($payload, 'url'),
            'links' => Values::array_get($payload, 'links'),
        ];

        $this->solution = ['sid' => $sid ?: $this->properties['sid'], ];
    }

    /**
     * Generate an instance context for the instance, the context is capable of
     * performing various actions.  All instance actions are proxied to the context
     *
     * @return CustomerProfilesContext Context for this CustomerProfilesInstance
     */
    protected function proxy(): CustomerProfilesContext
    {
        if (!$this->context) {
            $this->context = new CustomerProfilesContext(
                $this->version,
                $this->solution['sid']
            );
        }

        return $this->context;
    }

    /**
     * Delete the CustomerProfilesInstance
     *
     * @return bool True if delete succeeds, false otherwise
     * @throws TwilioException When an HTTP error occurs.
     */
    public function delete(): bool
    {

        return $this->proxy()->delete();
    }

    /**
     * Fetch the CustomerProfilesInstance
     *
     * @return CustomerProfilesInstance Fetched CustomerProfilesInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): CustomerProfilesInstance
    {

        return $this->proxy()->fetch();
    }

    /**
     * Update the CustomerProfilesInstance
     *
     * @param array|Options $options Optional Arguments
     * @return CustomerProfilesInstance Updated CustomerProfilesInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function update(array $options = []): CustomerProfilesInstance
    {

        return $this->proxy()->update($options);
    }

    /**
     * Access the customerProfilesChannelEndpointAssignment
     */
    protected function getCustomerProfilesChannelEndpointAssignment(): CustomerProfilesChannelEndpointAssignmentList
    {
        return $this->proxy()->customerProfilesChannelEndpointAssignment;
    }

    /**
     * Access the customerProfilesEntityAssignments
     */
    protected function getCustomerProfilesEntityAssignments(): CustomerProfilesEntityAssignmentsList
    {
        return $this->proxy()->customerProfilesEntityAssignments;
    }

    /**
     * Access the customerProfilesEvaluations
     */
    protected function getCustomerProfilesEvaluations(): CustomerProfilesEvaluationsList
    {
        return $this->proxy()->customerProfilesEvaluations;
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
        return '[Twilio.Trusthub.V1.CustomerProfilesInstance ' . \implode(' ', $context) . ']';
    }
}

