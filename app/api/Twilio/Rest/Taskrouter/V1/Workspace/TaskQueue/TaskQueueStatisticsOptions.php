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

namespace Twilio\Rest\Taskrouter\V1\Workspace\TaskQueue;

use Twilio\Options;
use Twilio\Values;

abstract class TaskQueueStatisticsOptions
{
    /**
     * @param \DateTime $endDate Only calculate statistics from this date and time and earlier, specified in GMT as an [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) date-time.
     * @param int $minutes Only calculate statistics since this many minutes in the past. The default is 15 minutes.
     * @param \DateTime $startDate Only calculate statistics from this date and time and later, specified in [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) format.
     * @param string $taskChannel Only calculate real-time and cumulative statistics for the specified TaskChannel. Can be the TaskChannel's SID or its `unique_name`, such as `voice`, `sms`, or `default`.
     * @param string $splitByWaitTime A comma separated list of values that describes the thresholds, in seconds, to calculate statistics on. For each threshold specified, the number of Tasks canceled and reservations accepted above and below the specified thresholds in seconds are computed.
     * @return FetchTaskQueueStatisticsOptions Options builder
     */
    public static function fetch(
        
        \DateTime $endDate = null,
        int $minutes = Values::INT_NONE,
        \DateTime $startDate = null,
        string $taskChannel = Values::NONE,
        string $splitByWaitTime = Values::NONE

    ): FetchTaskQueueStatisticsOptions
    {
        return new FetchTaskQueueStatisticsOptions(
            $endDate,
            $minutes,
            $startDate,
            $taskChannel,
            $splitByWaitTime
        );
    }

}

class FetchTaskQueueStatisticsOptions extends Options
    {
    /**
     * @param \DateTime $endDate Only calculate statistics from this date and time and earlier, specified in GMT as an [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) date-time.
     * @param int $minutes Only calculate statistics since this many minutes in the past. The default is 15 minutes.
     * @param \DateTime $startDate Only calculate statistics from this date and time and later, specified in [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) format.
     * @param string $taskChannel Only calculate real-time and cumulative statistics for the specified TaskChannel. Can be the TaskChannel's SID or its `unique_name`, such as `voice`, `sms`, or `default`.
     * @param string $splitByWaitTime A comma separated list of values that describes the thresholds, in seconds, to calculate statistics on. For each threshold specified, the number of Tasks canceled and reservations accepted above and below the specified thresholds in seconds are computed.
     */
    public function __construct(
        
        \DateTime $endDate = null,
        int $minutes = Values::INT_NONE,
        \DateTime $startDate = null,
        string $taskChannel = Values::NONE,
        string $splitByWaitTime = Values::NONE

    ) {
        $this->options['endDate'] = $endDate;
        $this->options['minutes'] = $minutes;
        $this->options['startDate'] = $startDate;
        $this->options['taskChannel'] = $taskChannel;
        $this->options['splitByWaitTime'] = $splitByWaitTime;
    }

    /**
     * Only calculate statistics from this date and time and earlier, specified in GMT as an [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) date-time.
     *
     * @param \DateTime $endDate Only calculate statistics from this date and time and earlier, specified in GMT as an [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) date-time.
     * @return $this Fluent Builder
     */
    public function setEndDate(\DateTime $endDate): self
    {
        $this->options['endDate'] = $endDate;
        return $this;
    }

    /**
     * Only calculate statistics since this many minutes in the past. The default is 15 minutes.
     *
     * @param int $minutes Only calculate statistics since this many minutes in the past. The default is 15 minutes.
     * @return $this Fluent Builder
     */
    public function setMinutes(int $minutes): self
    {
        $this->options['minutes'] = $minutes;
        return $this;
    }

    /**
     * Only calculate statistics from this date and time and later, specified in [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) format.
     *
     * @param \DateTime $startDate Only calculate statistics from this date and time and later, specified in [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) format.
     * @return $this Fluent Builder
     */
    public function setStartDate(\DateTime $startDate): self
    {
        $this->options['startDate'] = $startDate;
        return $this;
    }

    /**
     * Only calculate real-time and cumulative statistics for the specified TaskChannel. Can be the TaskChannel's SID or its `unique_name`, such as `voice`, `sms`, or `default`.
     *
     * @param string $taskChannel Only calculate real-time and cumulative statistics for the specified TaskChannel. Can be the TaskChannel's SID or its `unique_name`, such as `voice`, `sms`, or `default`.
     * @return $this Fluent Builder
     */
    public function setTaskChannel(string $taskChannel): self
    {
        $this->options['taskChannel'] = $taskChannel;
        return $this;
    }

    /**
     * A comma separated list of values that describes the thresholds, in seconds, to calculate statistics on. For each threshold specified, the number of Tasks canceled and reservations accepted above and below the specified thresholds in seconds are computed.
     *
     * @param string $splitByWaitTime A comma separated list of values that describes the thresholds, in seconds, to calculate statistics on. For each threshold specified, the number of Tasks canceled and reservations accepted above and below the specified thresholds in seconds are computed.
     * @return $this Fluent Builder
     */
    public function setSplitByWaitTime(string $splitByWaitTime): self
    {
        $this->options['splitByWaitTime'] = $splitByWaitTime;
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
        return '[Twilio.Taskrouter.V1.FetchTaskQueueStatisticsOptions ' . $options . ']';
    }
}

