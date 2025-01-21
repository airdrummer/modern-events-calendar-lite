<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Supersim
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Supersim\V1;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Values;
use Twilio\Version;


/**
 * @property string|null $accountSid
 * @property string|null $simSid
 * @property string|null $networkSid
 * @property string|null $fleetSid
 * @property string|null $isoCountry
 * @property array|null $period
 * @property int|null $dataUpload
 * @property int|null $dataDownload
 * @property int|null $dataTotal
 * @property string|null $dataTotalBilled
 * @property string|null $billedUnit
 */
class UsageRecordInstance extends InstanceResource
{
    /**
     * Initialize the UsageRecordInstance
     *
     * @param Version $version Version that contains the resource
     * @param mixed[] $payload The response payload
     */
    public function __construct(Version $version, array $payload)
    {
        parent::__construct($version);

        // Marshaled Properties
        $this->properties = [
            'accountSid' => Values::array_get($payload, 'account_sid'),
            'simSid' => Values::array_get($payload, 'sim_sid'),
            'networkSid' => Values::array_get($payload, 'network_sid'),
            'fleetSid' => Values::array_get($payload, 'fleet_sid'),
            'isoCountry' => Values::array_get($payload, 'iso_country'),
            'period' => Values::array_get($payload, 'period'),
            'dataUpload' => Values::array_get($payload, 'data_upload'),
            'dataDownload' => Values::array_get($payload, 'data_download'),
            'dataTotal' => Values::array_get($payload, 'data_total'),
            'dataTotalBilled' => Values::array_get($payload, 'data_total_billed'),
            'billedUnit' => Values::array_get($payload, 'billed_unit'),
        ];

        $this->solution = [];
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
        return '[Twilio.Supersim.V1.UsageRecordInstance]';
    }
}

