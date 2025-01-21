<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Messaging
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Messaging;

use Twilio\Domain;
use Twilio\Exceptions\TwilioException;
use Twilio\InstanceContext;
use Twilio\Rest\Messaging\V1\BrandRegistrationList;
use Twilio\Rest\Messaging\V1\DeactivationsList;
use Twilio\Rest\Messaging\V1\DomainCertsList;
use Twilio\Rest\Messaging\V1\DomainConfigList;
use Twilio\Rest\Messaging\V1\DomainConfigMessagingServiceList;
use Twilio\Rest\Messaging\V1\ExternalCampaignList;
use Twilio\Rest\Messaging\V1\LinkshorteningMessagingServiceList;
use Twilio\Rest\Messaging\V1\ServiceList;
use Twilio\Rest\Messaging\V1\TollfreeVerificationList;
use Twilio\Rest\Messaging\V1\UsecaseList;
use Twilio\Version;

/**
 * @property BrandRegistrationList $brandRegistrations
 * @property DeactivationsList $deactivations
 * @property DomainCertsList $domainCerts
 * @property DomainConfigList $domainConfig
 * @property DomainConfigMessagingServiceList $domainConfigMessagingService
 * @property ExternalCampaignList $externalCampaign
 * @property LinkshorteningMessagingServiceList $linkshorteningMessagingService
 * @property ServiceList $services
 * @property TollfreeVerificationList $tollfreeVerifications
 * @property UsecaseList $usecases
 * @method \Twilio\Rest\Messaging\V1\BrandRegistrationContext brandRegistrations(string $sid)
 * @method \Twilio\Rest\Messaging\V1\LinkshorteningMessagingServiceContext linkshorteningMessagingService(string $domainSid, string $messagingServiceSid)
 * @method \Twilio\Rest\Messaging\V1\ServiceContext services(string $sid)
 * @method \Twilio\Rest\Messaging\V1\TollfreeVerificationContext tollfreeVerifications(string $sid)
 */
class V1 extends Version
{
    protected $_brandRegistrations;
    protected $_deactivations;
    protected $_domainCerts;
    protected $_domainConfig;
    protected $_domainConfigMessagingService;
    protected $_externalCampaign;
    protected $_linkshorteningMessagingService;
    protected $_services;
    protected $_tollfreeVerifications;
    protected $_usecases;

    /**
     * Construct the V1 version of Messaging
     *
     * @param Domain $domain Domain that contains the version
     */
    public function __construct(Domain $domain)
    {
        parent::__construct($domain);
        $this->version = 'v1';
    }

    protected function getBrandRegistrations(): BrandRegistrationList
    {
        if (!$this->_brandRegistrations) {
            $this->_brandRegistrations = new BrandRegistrationList($this);
        }
        return $this->_brandRegistrations;
    }

    protected function getDeactivations(): DeactivationsList
    {
        if (!$this->_deactivations) {
            $this->_deactivations = new DeactivationsList($this);
        }
        return $this->_deactivations;
    }

    protected function getDomainCerts(): DomainCertsList
    {
        if (!$this->_domainCerts) {
            $this->_domainCerts = new DomainCertsList($this);
        }
        return $this->_domainCerts;
    }

    protected function getDomainConfig(): DomainConfigList
    {
        if (!$this->_domainConfig) {
            $this->_domainConfig = new DomainConfigList($this);
        }
        return $this->_domainConfig;
    }

    protected function getDomainConfigMessagingService(): DomainConfigMessagingServiceList
    {
        if (!$this->_domainConfigMessagingService) {
            $this->_domainConfigMessagingService = new DomainConfigMessagingServiceList($this);
        }
        return $this->_domainConfigMessagingService;
    }

    protected function getExternalCampaign(): ExternalCampaignList
    {
        if (!$this->_externalCampaign) {
            $this->_externalCampaign = new ExternalCampaignList($this);
        }
        return $this->_externalCampaign;
    }

    protected function getLinkshorteningMessagingService(): LinkshorteningMessagingServiceList
    {
        if (!$this->_linkshorteningMessagingService) {
            $this->_linkshorteningMessagingService = new LinkshorteningMessagingServiceList($this);
        }
        return $this->_linkshorteningMessagingService;
    }

    protected function getServices(): ServiceList
    {
        if (!$this->_services) {
            $this->_services = new ServiceList($this);
        }
        return $this->_services;
    }

    protected function getTollfreeVerifications(): TollfreeVerificationList
    {
        if (!$this->_tollfreeVerifications) {
            $this->_tollfreeVerifications = new TollfreeVerificationList($this);
        }
        return $this->_tollfreeVerifications;
    }

    protected function getUsecases(): UsecaseList
    {
        if (!$this->_usecases) {
            $this->_usecases = new UsecaseList($this);
        }
        return $this->_usecases;
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
        return '[Twilio.Messaging.V1]';
    }
}
