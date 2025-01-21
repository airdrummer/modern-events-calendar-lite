<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Preview
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Preview\Sync\Service;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;
use Twilio\Deserialize;
use Twilio\Rest\Preview\Sync\Service\Document\DocumentPermissionList;


/**
 * @property string|null $sid
 * @property string|null $uniqueName
 * @property string|null $accountSid
 * @property string|null $serviceSid
 * @property string|null $url
 * @property array|null $links
 * @property string|null $revision
 * @property array|null $data
 * @property \DateTime|null $dateCreated
 * @property \DateTime|null $dateUpdated
 * @property string|null $createdBy
 */
class DocumentInstance extends InstanceResource
{
    protected $_documentPermissions;

    /**
     * Initialize the DocumentInstance
     *
     * @param Version $version Version that contains the resource
     * @param mixed[] $payload The response payload
     * @param string $serviceSid 
     * @param string $sid 
     */
    public function __construct(Version $version, array $payload, string $serviceSid, string $sid = null)
    {
        parent::__construct($version);

        // Marshaled Properties
        $this->properties = [
            'sid' => Values::array_get($payload, 'sid'),
            'uniqueName' => Values::array_get($payload, 'unique_name'),
            'accountSid' => Values::array_get($payload, 'account_sid'),
            'serviceSid' => Values::array_get($payload, 'service_sid'),
            'url' => Values::array_get($payload, 'url'),
            'links' => Values::array_get($payload, 'links'),
            'revision' => Values::array_get($payload, 'revision'),
            'data' => Values::array_get($payload, 'data'),
            'dateCreated' => Deserialize::dateTime(Values::array_get($payload, 'date_created')),
            'dateUpdated' => Deserialize::dateTime(Values::array_get($payload, 'date_updated')),
            'createdBy' => Values::array_get($payload, 'created_by'),
        ];

        $this->solution = ['serviceSid' => $serviceSid, 'sid' => $sid ?: $this->properties['sid'], ];
    }

    /**
     * Generate an instance context for the instance, the context is capable of
     * performing various actions.  All instance actions are proxied to the context
     *
     * @return DocumentContext Context for this DocumentInstance
     */
    protected function proxy(): DocumentContext
    {
        if (!$this->context) {
            $this->context = new DocumentContext(
                $this->version,
                $this->solution['serviceSid'],
                $this->solution['sid']
            );
        }

        return $this->context;
    }

    /**
     * Delete the DocumentInstance
     *
     * @return bool True if delete succeeds, false otherwise
     * @throws TwilioException When an HTTP error occurs.
     */
    public function delete(): bool
    {

        return $this->proxy()->delete();
    }

    /**
     * Fetch the DocumentInstance
     *
     * @return DocumentInstance Fetched DocumentInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): DocumentInstance
    {

        return $this->proxy()->fetch();
    }

    /**
     * Update the DocumentInstance
     *
     * @param array $data 
     * @param array|Options $options Optional Arguments
     * @return DocumentInstance Updated DocumentInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function update(array $data, array $options = []): DocumentInstance
    {

        return $this->proxy()->update($data, $options);
    }

    /**
     * Access the documentPermissions
     */
    protected function getDocumentPermissions(): DocumentPermissionList
    {
        return $this->proxy()->documentPermissions;
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
        return '[Twilio.Preview.Sync.DocumentInstance ' . \implode(' ', $context) . ']';
    }
}

