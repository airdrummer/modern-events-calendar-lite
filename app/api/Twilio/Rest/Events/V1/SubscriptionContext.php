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


namespace Twilio\Rest\Events\V1;

use Twilio\Exceptions\TwilioException;
use Twilio\ListResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;
use Twilio\InstanceContext;
use Twilio\Rest\Events\V1\Subscription\SubscribedEventList;


/**
 * @property SubscribedEventList $subscribedEvents
 * @method \Twilio\Rest\Events\V1\Subscription\SubscribedEventContext subscribedEvents(string $type)
 */
class SubscriptionContext extends InstanceContext
    {
    protected $_subscribedEvents;

    /**
     * Initialize the SubscriptionContext
     *
     * @param Version $version Version that contains the resource
     * @param string $sid A 34 character string that uniquely identifies this Subscription.
     */
    public function __construct(
        Version $version,
        $sid
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        'sid' =>
            $sid,
        ];

        $this->uri = '/Subscriptions/' . \rawurlencode($sid)
        .'';
    }

    /**
     * Delete the SubscriptionInstance
     *
     * @return bool True if delete succeeds, false otherwise
     * @throws TwilioException When an HTTP error occurs.
     */
    public function delete(): bool
    {

        return $this->version->delete('DELETE', $this->uri);
    }


    /**
     * Fetch the SubscriptionInstance
     *
     * @return SubscriptionInstance Fetched SubscriptionInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): SubscriptionInstance
    {

        $payload = $this->version->fetch('GET', $this->uri);

        return new SubscriptionInstance(
            $this->version,
            $payload,
            $this->solution['sid']
        );
    }


    /**
     * Update the SubscriptionInstance
     *
     * @param array|Options $options Optional Arguments
     * @return SubscriptionInstance Updated SubscriptionInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function update(array $options = []): SubscriptionInstance
    {

        $options = new Values($options);

        $data = Values::of([
            'Description' =>
                $options['description'],
            'SinkSid' =>
                $options['sinkSid'],
        ]);

        $payload = $this->version->update('POST', $this->uri, [], $data);

        return new SubscriptionInstance(
            $this->version,
            $payload,
            $this->solution['sid']
        );
    }


    /**
     * Access the subscribedEvents
     */
    protected function getSubscribedEvents(): SubscribedEventList
    {
        if (!$this->_subscribedEvents) {
            $this->_subscribedEvents = new SubscribedEventList(
                $this->version,
                $this->solution['sid']
            );
        }

        return $this->_subscribedEvents;
    }

    /**
     * Magic getter to lazy load subresources
     *
     * @param string $name Subresource to return
     * @return ListResource The requested subresource
     * @throws TwilioException For unknown subresources
     */
    public function __get(string $name): ListResource
    {
        if (\property_exists($this, '_' . $name)) {
            $method = 'get' . \ucfirst($name);
            return $this->$method();
        }

        throw new TwilioException('Unknown subresource ' . $name);
    }

    /**
     * Magic caller to get resource contexts
     *
     * @param string $name Resource to return
     * @param array $arguments Context parameters
     * @return InstanceContext The requested resource context
     * @throws TwilioException For unknown resource
     */
    public function __call(string $name, array $arguments): InstanceContext
    {
        $property = $this->$name;
        if (\method_exists($property, 'getContext')) {
            return \call_user_func_array(array($property, 'getContext'), $arguments);
        }

        throw new TwilioException('Resource does not have a context');
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
        return '[Twilio.Events.V1.SubscriptionContext ' . \implode(' ', $context) . ']';
    }
}
