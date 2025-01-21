<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Proxy
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Proxy\V1\Service\Session\Participant;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Values;
use Twilio\Version;
use Twilio\Deserialize;


/**
 * @property string|null $sid
 * @property string|null $sessionSid
 * @property string|null $serviceSid
 * @property string|null $accountSid
 * @property string|null $data
 * @property string $type
 * @property string|null $participantSid
 * @property string|null $inboundParticipantSid
 * @property string|null $inboundResourceSid
 * @property string $inboundResourceStatus
 * @property string|null $inboundResourceType
 * @property string|null $inboundResourceUrl
 * @property string|null $outboundParticipantSid
 * @property string|null $outboundResourceSid
 * @property string $outboundResourceStatus
 * @property string|null $outboundResourceType
 * @property string|null $outboundResourceUrl
 * @property \DateTime|null $dateCreated
 * @property \DateTime|null $dateUpdated
 * @property string|null $url
 */
class MessageInteractionInstance extends InstanceResource
{
    /**
     * Initialize the MessageInteractionInstance
     *
     * @param Version $version Version that contains the resource
     * @param mixed[] $payload The response payload
     * @param string $serviceSid The SID of the parent [Service](https://www.twilio.com/docs/proxy/api/service) resource.
     * @param string $sessionSid The SID of the parent [Session](https://www.twilio.com/docs/proxy/api/session) resource.
     * @param string $participantSid The SID of the [Participant](https://www.twilio.com/docs/proxy/api/participant) resource.
     * @param string $sid The Twilio-provided string that uniquely identifies the MessageInteraction resource to fetch.
     */
    public function __construct(Version $version, array $payload, string $serviceSid, string $sessionSid, string $participantSid, string $sid = null)
    {
        parent::__construct($version);

        // Marshaled Properties
        $this->properties = [
            'sid' => Values::array_get($payload, 'sid'),
            'sessionSid' => Values::array_get($payload, 'session_sid'),
            'serviceSid' => Values::array_get($payload, 'service_sid'),
            'accountSid' => Values::array_get($payload, 'account_sid'),
            'data' => Values::array_get($payload, 'data'),
            'type' => Values::array_get($payload, 'type'),
            'participantSid' => Values::array_get($payload, 'participant_sid'),
            'inboundParticipantSid' => Values::array_get($payload, 'inbound_participant_sid'),
            'inboundResourceSid' => Values::array_get($payload, 'inbound_resource_sid'),
            'inboundResourceStatus' => Values::array_get($payload, 'inbound_resource_status'),
            'inboundResourceType' => Values::array_get($payload, 'inbound_resource_type'),
            'inboundResourceUrl' => Values::array_get($payload, 'inbound_resource_url'),
            'outboundParticipantSid' => Values::array_get($payload, 'outbound_participant_sid'),
            'outboundResourceSid' => Values::array_get($payload, 'outbound_resource_sid'),
            'outboundResourceStatus' => Values::array_get($payload, 'outbound_resource_status'),
            'outboundResourceType' => Values::array_get($payload, 'outbound_resource_type'),
            'outboundResourceUrl' => Values::array_get($payload, 'outbound_resource_url'),
            'dateCreated' => Deserialize::dateTime(Values::array_get($payload, 'date_created')),
            'dateUpdated' => Deserialize::dateTime(Values::array_get($payload, 'date_updated')),
            'url' => Values::array_get($payload, 'url'),
        ];

        $this->solution = ['serviceSid' => $serviceSid, 'sessionSid' => $sessionSid, 'participantSid' => $participantSid, 'sid' => $sid ?: $this->properties['sid'], ];
    }

    /**
     * Generate an instance context for the instance, the context is capable of
     * performing various actions.  All instance actions are proxied to the context
     *
     * @return MessageInteractionContext Context for this MessageInteractionInstance
     */
    protected function proxy(): MessageInteractionContext
    {
        if (!$this->context) {
            $this->context = new MessageInteractionContext(
                $this->version,
                $this->solution['serviceSid'],
                $this->solution['sessionSid'],
                $this->solution['participantSid'],
                $this->solution['sid']
            );
        }

        return $this->context;
    }

    /**
     * Fetch the MessageInteractionInstance
     *
     * @return MessageInteractionInstance Fetched MessageInteractionInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): MessageInteractionInstance
    {

        return $this->proxy()->fetch();
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
        return '[Twilio.Proxy.V1.MessageInteractionInstance ' . \implode(' ', $context) . ']';
    }
}

