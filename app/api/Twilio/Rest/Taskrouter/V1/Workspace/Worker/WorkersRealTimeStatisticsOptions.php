<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Taskrouter
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Taskrouter\V1\Workspace\Worker;

use Twilio\Options;
use Twilio\Values;

abstract class WorkersRealTimeStatisticsOptions
{
    /**
     * @param string $taskChannel Only calculate real-time statistics on this TaskChannel. Can be the TaskChannel's SID or its `unique_name`, such as `voice`, `sms`, or `default`.
     * @return FetchWorkersRealTimeStatisticsOptions Options builder
     */
    public static function fetch(
        
        string $taskChannel = Values::NONE

    ): FetchWorkersRealTimeStatisticsOptions
    {
        return new FetchWorkersRealTimeStatisticsOptions(
            $taskChannel
        );
    }

}

class FetchWorkersRealTimeStatisticsOptions extends Options
    {
    /**
     * @param string $taskChannel Only calculate real-time statistics on this TaskChannel. Can be the TaskChannel's SID or its `unique_name`, such as `voice`, `sms`, or `default`.
     */
    public function __construct(
        
        string $taskChannel = Values::NONE

    ) {
        $this->options['taskChannel'] = $taskChannel;
    }

    /**
     * Only calculate real-time statistics on this TaskChannel. Can be the TaskChannel's SID or its `unique_name`, such as `voice`, `sms`, or `default`.
     *
     * @param string $taskChannel Only calculate real-time statistics on this TaskChannel. Can be the TaskChannel's SID or its `unique_name`, such as `voice`, `sms`, or `default`.
     * @return $this Fluent Builder
     */
    public function setTaskChannel(string $taskChannel): self
    {
        $this->options['taskChannel'] = $taskChannel;
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
        return '[Twilio.Taskrouter.V1.FetchWorkersRealTimeStatisticsOptions ' . $options . ']';
    }
}

