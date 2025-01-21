<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Numbers
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Numbers\V2\RegulatoryCompliance;

use Twilio\Options;
use Twilio\Values;

abstract class EndUserOptions
{
    /**
     * @param array $attributes The set of parameters that are the attributes of the End User resource which are derived End User Types.
     * @return CreateEndUserOptions Options builder
     */
    public static function create(
        
        array $attributes = Values::ARRAY_NONE

    ): CreateEndUserOptions
    {
        return new CreateEndUserOptions(
            $attributes
        );
    }




    /**
     * @param string $friendlyName The string that you assigned to describe the resource.
     * @param array $attributes The set of parameters that are the attributes of the End User resource which are derived End User Types.
     * @return UpdateEndUserOptions Options builder
     */
    public static function update(
        
        string $friendlyName = Values::NONE,
        array $attributes = Values::ARRAY_NONE

    ): UpdateEndUserOptions
    {
        return new UpdateEndUserOptions(
            $friendlyName,
            $attributes
        );
    }

}

class CreateEndUserOptions extends Options
    {
    /**
     * @param array $attributes The set of parameters that are the attributes of the End User resource which are derived End User Types.
     */
    public function __construct(
        
        array $attributes = Values::ARRAY_NONE

    ) {
        $this->options['attributes'] = $attributes;
    }

    /**
     * The set of parameters that are the attributes of the End User resource which are derived End User Types.
     *
     * @param array $attributes The set of parameters that are the attributes of the End User resource which are derived End User Types.
     * @return $this Fluent Builder
     */
    public function setAttributes(array $attributes): self
    {
        $this->options['attributes'] = $attributes;
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
        return '[Twilio.Numbers.V2.CreateEndUserOptions ' . $options . ']';
    }
}




class UpdateEndUserOptions extends Options
    {
    /**
     * @param string $friendlyName The string that you assigned to describe the resource.
     * @param array $attributes The set of parameters that are the attributes of the End User resource which are derived End User Types.
     */
    public function __construct(
        
        string $friendlyName = Values::NONE,
        array $attributes = Values::ARRAY_NONE

    ) {
        $this->options['friendlyName'] = $friendlyName;
        $this->options['attributes'] = $attributes;
    }

    /**
     * The string that you assigned to describe the resource.
     *
     * @param string $friendlyName The string that you assigned to describe the resource.
     * @return $this Fluent Builder
     */
    public function setFriendlyName(string $friendlyName): self
    {
        $this->options['friendlyName'] = $friendlyName;
        return $this;
    }

    /**
     * The set of parameters that are the attributes of the End User resource which are derived End User Types.
     *
     * @param array $attributes The set of parameters that are the attributes of the End User resource which are derived End User Types.
     * @return $this Fluent Builder
     */
    public function setAttributes(array $attributes): self
    {
        $this->options['attributes'] = $attributes;
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
        return '[Twilio.Numbers.V2.UpdateEndUserOptions ' . $options . ']';
    }
}

