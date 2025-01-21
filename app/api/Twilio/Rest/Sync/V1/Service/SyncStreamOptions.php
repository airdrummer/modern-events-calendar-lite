<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Sync
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Sync\V1\Service;

use Twilio\Options;
use Twilio\Stream;
use Twilio\Values;

abstract class SyncStreamOptions
{
    /**
     * @param string $uniqueName An application-defined string that uniquely identifies the resource. This value must be unique within its Service and it can be up to 320 characters long. The `unique_name` value can be used as an alternative to the `sid` in the URL path to address the resource.
     * @param int $ttl How long, [in seconds](https://www.twilio.com/docs/sync/limits#sync-payload-limits), before the Stream expires and is deleted (time-to-live).
     * @return CreateSyncStreamOptions Options builder
     */
    public static function create(
        
        string $uniqueName = Values::NONE,
        int $ttl = Values::INT_NONE

    ): CreateSyncStreamOptions
    {
        return new CreateSyncStreamOptions(
            $uniqueName,
            $ttl
        );
    }




    /**
     * @param int $ttl How long, [in seconds](https://www.twilio.com/docs/sync/limits#sync-payload-limits), before the Stream expires and is deleted (time-to-live).
     * @return UpdateSyncStreamOptions Options builder
     */
    public static function update(
        
        int $ttl = Values::INT_NONE

    ): UpdateSyncStreamOptions
    {
        return new UpdateSyncStreamOptions(
            $ttl
        );
    }

}

class CreateSyncStreamOptions extends Options
    {
    /**
     * @param string $uniqueName An application-defined string that uniquely identifies the resource. This value must be unique within its Service and it can be up to 320 characters long. The `unique_name` value can be used as an alternative to the `sid` in the URL path to address the resource.
     * @param int $ttl How long, [in seconds](https://www.twilio.com/docs/sync/limits#sync-payload-limits), before the Stream expires and is deleted (time-to-live).
     */
    public function __construct(
        
        string $uniqueName = Values::NONE,
        int $ttl = Values::INT_NONE

    ) {
        $this->options['uniqueName'] = $uniqueName;
        $this->options['ttl'] = $ttl;
    }

    /**
     * An application-defined string that uniquely identifies the resource. This value must be unique within its Service and it can be up to 320 characters long. The `unique_name` value can be used as an alternative to the `sid` in the URL path to address the resource.
     *
     * @param string $uniqueName An application-defined string that uniquely identifies the resource. This value must be unique within its Service and it can be up to 320 characters long. The `unique_name` value can be used as an alternative to the `sid` in the URL path to address the resource.
     * @return $this Fluent Builder
     */
    public function setUniqueName(string $uniqueName): self
    {
        $this->options['uniqueName'] = $uniqueName;
        return $this;
    }

    /**
     * How long, [in seconds](https://www.twilio.com/docs/sync/limits#sync-payload-limits), before the Stream expires and is deleted (time-to-live).
     *
     * @param int $ttl How long, [in seconds](https://www.twilio.com/docs/sync/limits#sync-payload-limits), before the Stream expires and is deleted (time-to-live).
     * @return $this Fluent Builder
     */
    public function setTtl(int $ttl): self
    {
        $this->options['ttl'] = $ttl;
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
        return '[Twilio.Sync.V1.CreateSyncStreamOptions ' . $options . ']';
    }
}




class UpdateSyncStreamOptions extends Options
    {
    /**
     * @param int $ttl How long, [in seconds](https://www.twilio.com/docs/sync/limits#sync-payload-limits), before the Stream expires and is deleted (time-to-live).
     */
    public function __construct(
        
        int $ttl = Values::INT_NONE

    ) {
        $this->options['ttl'] = $ttl;
    }

    /**
     * How long, [in seconds](https://www.twilio.com/docs/sync/limits#sync-payload-limits), before the Stream expires and is deleted (time-to-live).
     *
     * @param int $ttl How long, [in seconds](https://www.twilio.com/docs/sync/limits#sync-payload-limits), before the Stream expires and is deleted (time-to-live).
     * @return $this Fluent Builder
     */
    public function setTtl(int $ttl): self
    {
        $this->options['ttl'] = $ttl;
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
        return '[Twilio.Sync.V1.UpdateSyncStreamOptions ' . $options . ']';
    }
}

