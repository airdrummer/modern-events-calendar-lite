<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Media
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Media\V1;

use Twilio\Exceptions\TwilioException;
use Twilio\Version;
use Twilio\InstanceContext;


class MediaRecordingContext extends InstanceContext
    {
    /**
     * Initialize the MediaRecordingContext
     *
     * @param Version $version Version that contains the resource
     * @param string $sid The SID of the MediaRecording resource to delete.
     */
    public function __construct(
        Version $version,
        $sid
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        'sid' =>
            $sid,
        ];

        $this->uri = '/MediaRecordings/' . \rawurlencode($sid)
        .'';
    }

    /**
     * Delete the MediaRecordingInstance
     *
     * @return bool True if delete succeeds, false otherwise
     * @throws TwilioException When an HTTP error occurs.
     */
    public function delete(): bool
    {

        return $this->version->delete('DELETE', $this->uri);
    }


    /**
     * Fetch the MediaRecordingInstance
     *
     * @return MediaRecordingInstance Fetched MediaRecordingInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): MediaRecordingInstance
    {

        $payload = $this->version->fetch('GET', $this->uri);

        return new MediaRecordingInstance(
            $this->version,
            $payload,
            $this->solution['sid']
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
        return '[Twilio.Media.V1.MediaRecordingContext ' . \implode(' ', $context) . ']';
    }
}
