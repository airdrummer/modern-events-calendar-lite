<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Autopilot
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Autopilot\V1\Assistant\Task;

use Twilio\Options;
use Twilio\Values;

abstract class TaskActionsOptions
{

    /**
     * @param array $actions The JSON string that specifies the [actions](https://www.twilio.com/docs/autopilot/actions) that instruct the Assistant on how to perform the task.
     * @return UpdateTaskActionsOptions Options builder
     */
    public static function update(
        
        array $actions = Values::ARRAY_NONE

    ): UpdateTaskActionsOptions
    {
        return new UpdateTaskActionsOptions(
            $actions
        );
    }

}


class UpdateTaskActionsOptions extends Options
    {
    /**
     * @param array $actions The JSON string that specifies the [actions](https://www.twilio.com/docs/autopilot/actions) that instruct the Assistant on how to perform the task.
     */
    public function __construct(
        
        array $actions = Values::ARRAY_NONE

    ) {
        $this->options['actions'] = $actions;
    }

    /**
     * The JSON string that specifies the [actions](https://www.twilio.com/docs/autopilot/actions) that instruct the Assistant on how to perform the task.
     *
     * @param array $actions The JSON string that specifies the [actions](https://www.twilio.com/docs/autopilot/actions) that instruct the Assistant on how to perform the task.
     * @return $this Fluent Builder
     */
    public function setActions(array $actions): self
    {
        $this->options['actions'] = $actions;
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
        return '[Twilio.Autopilot.V1.UpdateTaskActionsOptions ' . $options . ']';
    }
}

