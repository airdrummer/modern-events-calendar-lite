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

namespace Twilio\Rest\Taskrouter\V1\Workspace;

use Twilio\Exceptions\TwilioException;
use Twilio\ListResource;
use Twilio\Options;
use Twilio\Stream;
use Twilio\Values;
use Twilio\Version;
use Twilio\InstanceContext;
use Twilio\Rest\Taskrouter\V1\Workspace\Worker\WorkersStatisticsList;


/**
 * @property WorkersStatisticsList $statistics
 * @method \Twilio\Rest\Taskrouter\V1\Workspace\Worker\WorkersStatisticsContext statistics()
 */
class WorkerList extends ListResource
    {
    protected $_statistics = null;

    /**
     * Construct the WorkerList
     *
     * @param Version $version Version that contains the resource
     * @param string $workspaceSid The SID of the Workspace that the new Worker belongs to.
     */
    public function __construct(
        Version $version,
        string $workspaceSid
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        'workspaceSid' =>
            $workspaceSid,
        
        ];

        $this->uri = '/Workspaces/' . \rawurlencode($workspaceSid)
        .'/Workers';
    }

    /**
     * Create the WorkerInstance
     *
     * @param string $friendlyName A descriptive string that you create to describe the new Worker. It can be up to 64 characters long.
     * @param array|Options $options Optional Arguments
     * @return WorkerInstance Created WorkerInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function create(string $friendlyName, array $options = []): WorkerInstance
    {

        $options = new Values($options);

        $data = Values::of([
            'FriendlyName' =>
                $friendlyName,
            'ActivitySid' =>
                $options['activitySid'],
            'Attributes' =>
                $options['attributes'],
        ]);

        $payload = $this->version->create('POST', $this->uri, [], $data);

        return new WorkerInstance(
            $this->version,
            $payload,
            $this->solution['workspaceSid']
        );
    }


    /**
     * Reads WorkerInstance records from the API as a list.
     * Unlike stream(), this operation is eager and will load `limit` records into
     * memory before returning.
     *
     * @param array|Options $options Optional Arguments
     * @param int $limit Upper limit for the number of records to return. read()
     *                   guarantees to never return more than limit.  Default is no
     *                   limit
     * @param mixed $pageSize Number of records to fetch per request, when not set
     *                        will use the default value of 50 records.  If no
     *                        page_size is defined but a limit is defined, read()
     *                        will attempt to read the limit with the most
     *                        efficient page size, i.e. min(limit, 1000)
     * @return WorkerInstance[] Array of results
     */
    public function read(array $options = [], int $limit = null, $pageSize = null): array
    {
        return \iterator_to_array($this->stream($options, $limit, $pageSize), false);
    }

    /**
     * Streams WorkerInstance records from the API as a generator stream.
     * This operation lazily loads records as efficiently as possible until the
     * limit
     * is reached.
     * The results are returned as a generator, so this operation is memory
     * efficient.
     *
     * @param array|Options $options Optional Arguments
     * @param int $limit Upper limit for the number of records to return. stream()
     *                   guarantees to never return more than limit.  Default is no
     *                   limit
     * @param mixed $pageSize Number of records to fetch per request, when not set
     *                        will use the default value of 50 records.  If no
     *                        page_size is defined but a limit is defined, stream()
     *                        will attempt to read the limit with the most
     *                        efficient page size, i.e. min(limit, 1000)
     * @return Stream stream of results
     */
    public function stream(array $options = [], int $limit = null, $pageSize = null): Stream
    {
        $limits = $this->version->readLimits($limit, $pageSize);

        $page = $this->page($options, $limits['pageSize']);

        return $this->version->stream($page, $limits['limit'], $limits['pageLimit']);
    }

    /**
     * Retrieve a single page of WorkerInstance records from the API.
     * Request is executed immediately
     *
     * @param mixed $pageSize Number of records to return, defaults to 50
     * @param string $pageToken PageToken provided by the API
     * @param mixed $pageNumber Page Number, this value is simply for client state
     * @return WorkerPage Page of WorkerInstance
     */
    public function page(
        array $options = [],
        $pageSize = Values::NONE,
        string $pageToken = Values::NONE,
        $pageNumber = Values::NONE
    ): WorkerPage
    {
        $options = new Values($options);

        $params = Values::of([
            'ActivityName' =>
                $options['activityName'],
            'ActivitySid' =>
                $options['activitySid'],
            'Available' =>
                $options['available'],
            'FriendlyName' =>
                $options['friendlyName'],
            'TargetWorkersExpression' =>
                $options['targetWorkersExpression'],
            'TaskQueueName' =>
                $options['taskQueueName'],
            'TaskQueueSid' =>
                $options['taskQueueSid'],
            'Ordering' =>
                $options['ordering'],
            'PageToken' => $pageToken,
            'Page' => $pageNumber,
            'PageSize' => $pageSize,
        ]);

        $response = $this->version->page('GET', $this->uri, $params);

        return new WorkerPage($this->version, $response, $this->solution);
    }

    /**
     * Retrieve a specific page of WorkerInstance records from the API.
     * Request is executed immediately
     *
     * @param string $targetUrl API-generated URL for the requested results page
     * @return WorkerPage Page of WorkerInstance
     */
    public function getPage(string $targetUrl): WorkerPage
    {
        $response = $this->version->getDomain()->getClient()->request(
            'GET',
            $targetUrl
        );

        return new WorkerPage($this->version, $response, $this->solution);
    }


    /**
     * Constructs a WorkerContext
     *
     * @param string $sid The SID of the Worker resource to delete.
     */
    public function getContext(
        string $sid
        
    ): WorkerContext
    {
        return new WorkerContext(
            $this->version,
            $this->solution['workspaceSid'],
            $sid
        );
    }

    /**
     * Access the statistics
     */
    protected function getStatistics(): WorkersStatisticsList
    {
        if (!$this->_statistics) {
            $this->_statistics = new WorkersStatisticsList(
                $this->version,
                $this->solution['workspaceSid']
            );
        }
        return $this->_statistics;
    }

    /**
     * Magic getter to lazy load subresources
     *
     * @param string $name Subresource to return
     * @return \Twilio\ListResource The requested subresource
     * @throws TwilioException For unknown subresources
     */
    public function __get(string $name)
    {
        if (\property_exists($this, '_' . $name)) {
            $method = 'get' . \ucfirst($name);
            return $this->$method();
        }

        throw new TwilioException('Unknown subresource ' . $name);
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
        return '[Twilio.Taskrouter.V1.WorkerList]';
    }
}
