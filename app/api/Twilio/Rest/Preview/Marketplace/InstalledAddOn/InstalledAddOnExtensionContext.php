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


namespace Twilio\Rest\Preview\Marketplace\InstalledAddOn;

use Twilio\Exceptions\TwilioException;
use Twilio\Values;
use Twilio\Version;
use Twilio\InstanceContext;
use Twilio\Serialize;


class InstalledAddOnExtensionContext extends InstanceContext
    {
    /**
     * Initialize the InstalledAddOnExtensionContext
     *
     * @param Version $version Version that contains the resource
     * @param string $installedAddOnSid The SID of the InstalledAddOn resource with the extension to fetch.
     * @param string $sid The SID of the InstalledAddOn Extension resource to fetch.
     */
    public function __construct(
        Version $version,
        $installedAddOnSid,
        $sid
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        'installedAddOnSid' =>
            $installedAddOnSid,
        'sid' =>
            $sid,
        ];

        $this->uri = '/InstalledAddOns/' . \rawurlencode($installedAddOnSid)
        .'/Extensions/' . \rawurlencode($sid)
        .'';
    }

    /**
     * Fetch the InstalledAddOnExtensionInstance
     *
     * @return InstalledAddOnExtensionInstance Fetched InstalledAddOnExtensionInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): InstalledAddOnExtensionInstance
    {

        $payload = $this->version->fetch('GET', $this->uri);

        return new InstalledAddOnExtensionInstance(
            $this->version,
            $payload,
            $this->solution['installedAddOnSid'],
            $this->solution['sid']
        );
    }


    /**
     * Update the InstalledAddOnExtensionInstance
     *
     * @param bool $enabled Whether the Extension should be invoked.
     * @return InstalledAddOnExtensionInstance Updated InstalledAddOnExtensionInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function update(bool $enabled): InstalledAddOnExtensionInstance
    {

        $data = Values::of([
            'Enabled' =>
                Serialize::booleanToString($enabled),
        ]);

        $payload = $this->version->update('POST', $this->uri, [], $data);

        return new InstalledAddOnExtensionInstance(
            $this->version,
            $payload,
            $this->solution['installedAddOnSid'],
            $this->solution['sid']
        );
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
        return '[Twilio.Preview.Marketplace.InstalledAddOnExtensionContext ' . \implode(' ', $context) . ']';
    }
}
