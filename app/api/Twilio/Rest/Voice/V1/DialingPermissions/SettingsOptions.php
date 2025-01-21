<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Voice
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Voice\V1\DialingPermissions;

use Twilio\Options;
use Twilio\Values;

abstract class SettingsOptions
{

    /**
     * @param bool $dialingPermissionsInheritance `true` for the sub-account to inherit voice dialing permissions from the Master Project; otherwise `false`.
     * @return UpdateSettingsOptions Options builder
     */
    public static function update(
        
        bool $dialingPermissionsInheritance = Values::BOOL_NONE

    ): UpdateSettingsOptions
    {
        return new UpdateSettingsOptions(
            $dialingPermissionsInheritance
        );
    }

}


class UpdateSettingsOptions extends Options
    {
    /**
     * @param bool $dialingPermissionsInheritance `true` for the sub-account to inherit voice dialing permissions from the Master Project; otherwise `false`.
     */
    public function __construct(
        
        bool $dialingPermissionsInheritance = Values::BOOL_NONE

    ) {
        $this->options['dialingPermissionsInheritance'] = $dialingPermissionsInheritance;
    }

    /**
     * `true` for the sub-account to inherit voice dialing permissions from the Master Project; otherwise `false`.
     *
     * @param bool $dialingPermissionsInheritance `true` for the sub-account to inherit voice dialing permissions from the Master Project; otherwise `false`.
     * @return $this Fluent Builder
     */
    public function setDialingPermissionsInheritance(bool $dialingPermissionsInheritance): self
    {
        $this->options['dialingPermissionsInheritance'] = $dialingPermissionsInheritance;
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
        return '[Twilio.Voice.V1.UpdateSettingsOptions ' . $options . ']';
    }
}

