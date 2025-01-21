<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Frontline
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\FrontlineApi\V1;

use Twilio\Options;
use Twilio\Values;

abstract class UserOptions
{

    /**
     * @param string $friendlyName The string that you assigned to describe the User.
     * @param string $avatar The avatar URL which will be shown in Frontline application.
     * @param string $state
     * @param bool $isAvailable Whether the User is available for new conversations. Set to `false` to prevent User from receiving new inbound conversations if you are using [Pool Routing](https://www.twilio.com/docs/frontline/handle-incoming-conversations#3-pool-routing).
     * @return UpdateUserOptions Options builder
     */
    public static function update(
        
        string $friendlyName = Values::NONE,
        string $avatar = Values::NONE,
        string $state = Values::NONE,
        bool $isAvailable = Values::BOOL_NONE

    ): UpdateUserOptions
    {
        return new UpdateUserOptions(
            $friendlyName,
            $avatar,
            $state,
            $isAvailable
        );
    }

}


class UpdateUserOptions extends Options
    {
    /**
     * @param string $friendlyName The string that you assigned to describe the User.
     * @param string $avatar The avatar URL which will be shown in Frontline application.
     * @param string $state
     * @param bool $isAvailable Whether the User is available for new conversations. Set to `false` to prevent User from receiving new inbound conversations if you are using [Pool Routing](https://www.twilio.com/docs/frontline/handle-incoming-conversations#3-pool-routing).
     */
    public function __construct(
        
        string $friendlyName = Values::NONE,
        string $avatar = Values::NONE,
        string $state = Values::NONE,
        bool $isAvailable = Values::BOOL_NONE

    ) {
        $this->options['friendlyName'] = $friendlyName;
        $this->options['avatar'] = $avatar;
        $this->options['state'] = $state;
        $this->options['isAvailable'] = $isAvailable;
    }

    /**
     * The string that you assigned to describe the User.
     *
     * @param string $friendlyName The string that you assigned to describe the User.
     * @return $this Fluent Builder
     */
    public function setFriendlyName(string $friendlyName): self
    {
        $this->options['friendlyName'] = $friendlyName;
        return $this;
    }

    /**
     * The avatar URL which will be shown in Frontline application.
     *
     * @param string $avatar The avatar URL which will be shown in Frontline application.
     * @return $this Fluent Builder
     */
    public function setAvatar(string $avatar): self
    {
        $this->options['avatar'] = $avatar;
        return $this;
    }

    /**
     * @param string $state
     * @return $this Fluent Builder
     */
    public function setState(string $state): self
    {
        $this->options['state'] = $state;
        return $this;
    }

    /**
     * Whether the User is available for new conversations. Set to `false` to prevent User from receiving new inbound conversations if you are using [Pool Routing](https://www.twilio.com/docs/frontline/handle-incoming-conversations#3-pool-routing).
     *
     * @param bool $isAvailable Whether the User is available for new conversations. Set to `false` to prevent User from receiving new inbound conversations if you are using [Pool Routing](https://www.twilio.com/docs/frontline/handle-incoming-conversations#3-pool-routing).
     * @return $this Fluent Builder
     */
    public function setIsAvailable(bool $isAvailable): self
    {
        $this->options['isAvailable'] = $isAvailable;
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
        return '[Twilio.FrontlineApi.V1.UpdateUserOptions ' . $options . ']';
    }
}

