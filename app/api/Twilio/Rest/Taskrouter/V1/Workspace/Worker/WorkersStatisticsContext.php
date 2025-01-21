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

use Twilio\Exceptions\TwilioException;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;
use Twilio\InstanceContext;
use Twilio\Serialize;


class WorkersStatisticsContext extends InstanceContext
    {
    /**
     * Initialize the WorkersStatisticsContext
     *
     * @param Version $version Version that contains the resource
     * @param string $workspaceSid The SID of the Workspace with the Worker to fetch.
     */
    public function __construct(
        Version $version,
        $workspaceSid
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        'workspaceSid' =>
            $workspaceSid,
        ];

        $this->uri = '/Workspaces/' . \rawurlencode($workspaceSid)
        .'/Workers/Statistics';
    }

    /**
     * Fetch the WorkersStatisticsInstance
     *
     * @param array|Options $options Optional Arguments
     * @return WorkersStatisticsInstance Fetched WorkersStatisticsInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(array $options = []): WorkersStatisticsInstance
    {

        $options = new Values($options);

        $params = Values::of([
            'Minutes' =>
                $options['minutes'],
            'StartDate' =>
                Serialize::iso8601DateTime($options['startDate']),
            'EndDate' =>
                Serialize::iso8601DateTime($options['endDate']),
            'TaskQueueSid' =>
                $options['taskQueueSid'],
            'TaskQueueName' =>
                $options['taskQueueName'],
            'FriendlyName' =>
                $options['friendlyName'],
            'TaskChannel' =>
                $options['taskChannel'],
        ]);

        $payload = $this->version->fetch('GET', $this->uri, $params);

        return new WorkersStatisticsInstance(
            $this->version,
            $payload,
            $this->solution['workspaceSid']
        );
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
        return '[Twilio.Taskrouter.V1.WorkersStatisticsContext ' . \implode(' ', $context) . ']';
    }
}
