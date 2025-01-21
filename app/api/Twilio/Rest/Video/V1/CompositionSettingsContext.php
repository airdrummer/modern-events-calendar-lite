<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Video
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Video\V1;

use Twilio\Exceptions\TwilioException;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;
use Twilio\InstanceContext;
use Twilio\Serialize;


class CompositionSettingsContext extends InstanceContext
    {
    /**
     * Initialize the CompositionSettingsContext
     *
     * @param Version $version Version that contains the resource
     */
    public function __construct(
        Version $version
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        ];

        $this->uri = '/CompositionSettings/Default';
    }

    /**
     * Create the CompositionSettingsInstance
     *
     * @param string $friendlyName A descriptive string that you create to describe the resource and show to the user in the console
     * @param array|Options $options Optional Arguments
     * @return CompositionSettingsInstance Created CompositionSettingsInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function create(string $friendlyName, array $options = []): CompositionSettingsInstance
    {

        $options = new Values($options);

        $data = Values::of([
            'FriendlyName' =>
                $friendlyName,
            'AwsCredentialsSid' =>
                $options['awsCredentialsSid'],
            'EncryptionKeySid' =>
                $options['encryptionKeySid'],
            'AwsS3Url' =>
                $options['awsS3Url'],
            'AwsStorageEnabled' =>
                Serialize::booleanToString($options['awsStorageEnabled']),
            'EncryptionEnabled' =>
                Serialize::booleanToString($options['encryptionEnabled']),
        ]);

        $payload = $this->version->create('POST', $this->uri, [], $data);

        return new CompositionSettingsInstance(
            $this->version,
            $payload
        );
    }


    /**
     * Fetch the CompositionSettingsInstance
     *
     * @return CompositionSettingsInstance Fetched CompositionSettingsInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): CompositionSettingsInstance
    {

        $payload = $this->version->fetch('GET', $this->uri);

        return new CompositionSettingsInstance(
            $this->version,
            $payload
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
        return '[Twilio.Video.V1.CompositionSettingsContext ' . \implode(' ', $context) . ']';
    }
}
