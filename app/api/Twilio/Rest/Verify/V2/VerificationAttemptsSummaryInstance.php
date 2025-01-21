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


namespace Twilio\Rest\Verify\V2;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;


/**
 * @property int|null $totalAttempts
 * @property int|null $totalConverted
 * @property int|null $totalUnconverted
 * @property string|null $conversionRatePercentage
 * @property string|null $url
 */
class VerificationAttemptsSummaryInstance extends InstanceResource
{
    /**
     * Initialize the VerificationAttemptsSummaryInstance
     *
     * @param Version $version Version that contains the resource
     * @param mixed[] $payload The response payload
     */
    public function __construct(Version $version, array $payload)
    {
        parent::__construct($version);

        // Marshaled Properties
        $this->properties = [
            'totalAttempts' => Values::array_get($payload, 'total_attempts'),
            'totalConverted' => Values::array_get($payload, 'total_converted'),
            'totalUnconverted' => Values::array_get($payload, 'total_unconverted'),
            'conversionRatePercentage' => Values::array_get($payload, 'conversion_rate_percentage'),
            'url' => Values::array_get($payload, 'url'),
        ];

        $this->solution = [];
    }

    /**
     * Generate an instance context for the instance, the context is capable of
     * performing various actions.  All instance actions are proxied to the context
     *
     * @return VerificationAttemptsSummaryContext Context for this VerificationAttemptsSummaryInstance
     */
    protected function proxy(): VerificationAttemptsSummaryContext
    {
        if (!$this->context) {
            $this->context = new VerificationAttemptsSummaryContext(
                $this->version
            );
        }

        return $this->context;
    }

    /**
     * Fetch the VerificationAttemptsSummaryInstance
     *
     * @param array|Options $options Optional Arguments
     * @return VerificationAttemptsSummaryInstance Fetched VerificationAttemptsSummaryInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(array $options = []): VerificationAttemptsSummaryInstance
    {

        return $this->proxy()->fetch($options);
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
        return '[Twilio.Verify.V2.VerificationAttemptsSummaryInstance ' . \implode(' ', $context) . ']';
    }
}

