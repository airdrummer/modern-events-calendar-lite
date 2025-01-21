<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Flex
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\FlexApi\V1;

use Twilio\Options;
use Twilio\Values;

abstract class InsightsConversationsOptions
{
    /**
     * @param string $segmentId Unique Id of the segment for which conversation details needs to be fetched
     * @param string $token The Token HTTP request header
     * @return ReadInsightsConversationsOptions Options builder
     */
    public static function read(
        
        string $segmentId = Values::NONE,
        string $token = Values::NONE

    ): ReadInsightsConversationsOptions
    {
        return new ReadInsightsConversationsOptions(
            $segmentId,
            $token
        );
    }

}

class ReadInsightsConversationsOptions extends Options
    {
    /**
     * @param string $segmentId Unique Id of the segment for which conversation details needs to be fetched
     * @param string $token The Token HTTP request header
     */
    public function __construct(
        
        string $segmentId = Values::NONE,
        string $token = Values::NONE

    ) {
        $this->options['segmentId'] = $segmentId;
        $this->options['token'] = $token;
    }

    /**
     * Unique Id of the segment for which conversation details needs to be fetched
     *
     * @param string $segmentId Unique Id of the segment for which conversation details needs to be fetched
     * @return $this Fluent Builder
     */
    public function setSegmentId(string $segmentId): self
    {
        $this->options['segmentId'] = $segmentId;
        return $this;
    }

    /**
     * The Token HTTP request header
     *
     * @param string $token The Token HTTP request header
     * @return $this Fluent Builder
     */
    public function setToken(string $token): self
    {
        $this->options['token'] = $token;
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
        return '[Twilio.FlexApi.V1.ReadInsightsConversationsOptions ' . $options . ']';
    }
}

