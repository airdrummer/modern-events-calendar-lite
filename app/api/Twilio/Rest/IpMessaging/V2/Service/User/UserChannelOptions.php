<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Ip_messaging
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\IpMessaging\V2\Service\User;

use Twilio\Options;
use Twilio\Values;

abstract class UserChannelOptions
{



    /**
     * @param string $notificationLevel
     * @param int $lastConsumedMessageIndex 
     * @param \DateTime $lastConsumptionTimestamp 
     * @return UpdateUserChannelOptions Options builder
     */
    public static function update(
        
        string $notificationLevel = Values::NONE,
        int $lastConsumedMessageIndex = Values::INT_NONE,
        \DateTime $lastConsumptionTimestamp = null

    ): UpdateUserChannelOptions
    {
        return new UpdateUserChannelOptions(
            $notificationLevel,
            $lastConsumedMessageIndex,
            $lastConsumptionTimestamp
        );
    }

}




class UpdateUserChannelOptions extends Options
    {
    /**
     * @param string $notificationLevel
     * @param int $lastConsumedMessageIndex 
     * @param \DateTime $lastConsumptionTimestamp 
     */
    public function __construct(
        
        string $notificationLevel = Values::NONE,
        int $lastConsumedMessageIndex = Values::INT_NONE,
        \DateTime $lastConsumptionTimestamp = null

    ) {
        $this->options['notificationLevel'] = $notificationLevel;
        $this->options['lastConsumedMessageIndex'] = $lastConsumedMessageIndex;
        $this->options['lastConsumptionTimestamp'] = $lastConsumptionTimestamp;
    }

    /**
     * @param string $notificationLevel
     * @return $this Fluent Builder
     */
    public function setNotificationLevel(string $notificationLevel): self
    {
        $this->options['notificationLevel'] = $notificationLevel;
        return $this;
    }

    /**
     * 
     *
     * @param int $lastConsumedMessageIndex 
     * @return $this Fluent Builder
     */
    public function setLastConsumedMessageIndex(int $lastConsumedMessageIndex): self
    {
        $this->options['lastConsumedMessageIndex'] = $lastConsumedMessageIndex;
        return $this;
    }

    /**
     * 
     *
     * @param \DateTime $lastConsumptionTimestamp 
     * @return $this Fluent Builder
     */
    public function setLastConsumptionTimestamp(\DateTime $lastConsumptionTimestamp): self
    {
        $this->options['lastConsumptionTimestamp'] = $lastConsumptionTimestamp;
        return $this;
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string
    {
        $options = \http_build_query(Values::of($this->options), '', ' ');
        return '[Twilio.IpMessaging.V2.UpdateUserChannelOptions ' . $options . ']';
    }
}

