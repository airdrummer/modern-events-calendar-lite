<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Pricing
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Pricing;

use Twilio\Domain;
use Twilio\Exceptions\TwilioException;
use Twilio\InstanceContext;
use Twilio\Rest\Pricing\V2\CountryList;
use Twilio\Rest\Pricing\V2\NumberList;
use Twilio\Rest\Pricing\V2\VoiceList;
use Twilio\Version;

/**
 * @property CountryList $countries
 * @property NumberList $numbers
 * @property VoiceList $voice
 * @method \Twilio\Rest\Pricing\V2\CountryContext countries(string $isoCountry)
 * @method \Twilio\Rest\Pricing\V2\NumberContext numbers(string $destinationNumber)
 */
class V2 extends Version
{
    protected $_countries;
    protected $_numbers;
    protected $_voice;

    /**
     * Construct the V2 version of Pricing
     *
     * @param Domain $domain Domain that contains the version
     */
    public function __construct(Domain $domain)
    {
        parent::__construct($domain);
        $this->version = 'v2';
    }

    protected function getCountries(): CountryList
    {
        if (!$this->_countries) {
            $this->_countries = new CountryList($this);
        }
        return $this->_countries;
    }

    protected function getNumbers(): NumberList
    {
        if (!$this->_numbers) {
            $this->_numbers = new NumberList($this);
        }
        return $this->_numbers;
    }

    protected function getVoice(): VoiceList
    {
        if (!$this->_voice) {
            $this->_voice = new VoiceList($this);
        }
        return $this->_voice;
    }

    /**
     * Magic getter to lazy load root resources
     *
     * @param string $name Resource to return
     * @return \Twilio\ListResource The requested resource
     * @throws TwilioException For unknown resource
     */
    public function __get(string $name)
    {
        $method = 'get' . \ucfirst($name);
        if (\method_exists($this, $method)) {
            return $this->$method();
        }

        throw new TwilioException('Unknown resource ' . $name);
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
        return '[Twilio.Pricing.V2]';
    }
}
