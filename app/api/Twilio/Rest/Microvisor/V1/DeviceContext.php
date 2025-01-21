<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Microvisor
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Microvisor\V1;

use Twilio\Exceptions\TwilioException;
use Twilio\ListResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;
use Twilio\InstanceContext;
use Twilio\Serialize;
use Twilio\Rest\Microvisor\V1\Device\DeviceConfigList;
use Twilio\Rest\Microvisor\V1\Device\DeviceSecretList;


/**
 * @property DeviceConfigList $deviceConfigs
 * @property DeviceSecretList $deviceSecrets
 * @method \Twilio\Rest\Microvisor\V1\Device\DeviceSecretContext deviceSecrets(string $key)
 * @method \Twilio\Rest\Microvisor\V1\Device\DeviceConfigContext deviceConfigs(string $key)
 */
class DeviceContext extends InstanceContext
    {
    protected $_deviceConfigs;
    protected $_deviceSecrets;

    /**
     * Initialize the DeviceContext
     *
     * @param Version $version Version that contains the resource
     * @param string $sid A 34-character string that uniquely identifies this Device.
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

        $this->uri = '/Devices/' . \rawurlencode($sid)
        .'';
    }

    /**
     * Fetch the DeviceInstance
     *
     * @return DeviceInstance Fetched DeviceInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): DeviceInstance
    {

        $payload = $this->version->fetch('GET', $this->uri);

        return new DeviceInstance(
            $this->version,
            $payload,
            $this->solution['sid']
        );
    }


    /**
     * Update the DeviceInstance
     *
     * @param array|Options $options Optional Arguments
     * @return DeviceInstance Updated DeviceInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function update(array $options = []): DeviceInstance
    {

        $options = new Values($options);

        $data = Values::of([
            'UniqueName' =>
                $options['uniqueName'],
            'TargetApp' =>
                $options['targetApp'],
            'LoggingEnabled' =>
                Serialize::booleanToString($options['loggingEnabled']),
        ]);

        $payload = $this->version->update('POST', $this->uri, [], $data);

        return new DeviceInstance(
            $this->version,
            $payload,
            $this->solution['sid']
        );
    }


    /**
     * Access the deviceConfigs
     */
    protected function getDeviceConfigs(): DeviceConfigList
    {
        if (!$this->_deviceConfigs) {
            $this->_deviceConfigs = new DeviceConfigList(
                $this->version,
                $this->solution['sid']
            );
        }

        return $this->_deviceConfigs;
    }

    /**
     * Access the deviceSecrets
     */
    protected function getDeviceSecrets(): DeviceSecretList
    {
        if (!$this->_deviceSecrets) {
            $this->_deviceSecrets = new DeviceSecretList(
                $this->version,
                $this->solution['sid']
            );
        }

        return $this->_deviceSecrets;
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
        return '[Twilio.Microvisor.V1.DeviceContext ' . \implode(' ', $context) . ']';
    }
}
