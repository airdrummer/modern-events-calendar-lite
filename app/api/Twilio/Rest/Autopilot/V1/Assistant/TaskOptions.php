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

namespace Twilio\Rest\Autopilot\V1\Assistant;

use Twilio\Options;
use Twilio\Values;

abstract class TaskOptions
{
    /**
     * @param string $friendlyName A descriptive string that you create to describe the new resource. It is not unique and can be up to 255 characters long.
     * @param array $actions The JSON string that specifies the [actions](https://www.twilio.com/docs/autopilot/actions) that instruct the Assistant on how to perform the task. It is optional and not unique.
     * @param string $actionsUrl The URL from which the Assistant can fetch actions.
     * @return CreateTaskOptions Options builder
     */
    public static function create(
        
        string $friendlyName = Values::NONE,
        array $actions = Values::ARRAY_NONE,
        string $actionsUrl = Values::NONE

    ): CreateTaskOptions
    {
        return new CreateTaskOptions(
            $friendlyName,
            $actions,
            $actionsUrl
        );
    }




    /**
     * @param string $friendlyName A descriptive string that you create to describe the resource. It is not unique and can be up to 255 characters long.
     * @param string $uniqueName An application-defined string that uniquely identifies the resource. This value must be 64 characters or less in length and be unique. It can be used as an alternative to the `sid` in the URL path to address the resource.
     * @param array $actions The JSON string that specifies the [actions](https://www.twilio.com/docs/autopilot/actions) that instruct the Assistant on how to perform the task.
     * @param string $actionsUrl The URL from which the Assistant can fetch actions.
     * @return UpdateTaskOptions Options builder
     */
    public static function update(
        
        string $friendlyName = Values::NONE,
        string $uniqueName = Values::NONE,
        array $actions = Values::ARRAY_NONE,
        string $actionsUrl = Values::NONE

    ): UpdateTaskOptions
    {
        return new UpdateTaskOptions(
            $friendlyName,
            $uniqueName,
            $actions,
            $actionsUrl
        );
    }

}

class CreateTaskOptions extends Options
    {
    /**
     * @param string $friendlyName A descriptive string that you create to describe the new resource. It is not unique and can be up to 255 characters long.
     * @param array $actions The JSON string that specifies the [actions](https://www.twilio.com/docs/autopilot/actions) that instruct the Assistant on how to perform the task. It is optional and not unique.
     * @param string $actionsUrl The URL from which the Assistant can fetch actions.
     */
    public function __construct(
        
        string $friendlyName = Values::NONE,
        array $actions = Values::ARRAY_NONE,
        string $actionsUrl = Values::NONE

    ) {
        $this->options['friendlyName'] = $friendlyName;
        $this->options['actions'] = $actions;
        $this->options['actionsUrl'] = $actionsUrl;
    }

    /**
     * A descriptive string that you create to describe the new resource. It is not unique and can be up to 255 characters long.
     *
     * @param string $friendlyName A descriptive string that you create to describe the new resource. It is not unique and can be up to 255 characters long.
     * @return $this Fluent Builder
     */
    public function setFriendlyName(string $friendlyName): self
    {
        $this->options['friendlyName'] = $friendlyName;
        return $this;
    }

    /**
     * The JSON string that specifies the [actions](https://www.twilio.com/docs/autopilot/actions) that instruct the Assistant on how to perform the task. It is optional and not unique.
     *
     * @param array $actions The JSON string that specifies the [actions](https://www.twilio.com/docs/autopilot/actions) that instruct the Assistant on how to perform the task. It is optional and not unique.
     * @return $this Fluent Builder
     */
    public function setActions(array $actions): self
    {
        $this->options['actions'] = $actions;
        return $this;
    }

    /**
     * The URL from which the Assistant can fetch actions.
     *
     * @param string $actionsUrl The URL from which the Assistant can fetch actions.
     * @return $this Fluent Builder
     */
    public function setActionsUrl(string $actionsUrl): self
    {
        $this->options['actionsUrl'] = $actionsUrl;
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
        return '[Twilio.Autopilot.V1.CreateTaskOptions ' . $options . ']';
    }
}




class UpdateTaskOptions extends Options
    {
    /**
     * @param string $friendlyName A descriptive string that you create to describe the resource. It is not unique and can be up to 255 characters long.
     * @param string $uniqueName An application-defined string that uniquely identifies the resource. This value must be 64 characters or less in length and be unique. It can be used as an alternative to the `sid` in the URL path to address the resource.
     * @param array $actions The JSON string that specifies the [actions](https://www.twilio.com/docs/autopilot/actions) that instruct the Assistant on how to perform the task.
     * @param string $actionsUrl The URL from which the Assistant can fetch actions.
     */
    public function __construct(
        
        string $friendlyName = Values::NONE,
        string $uniqueName = Values::NONE,
        array $actions = Values::ARRAY_NONE,
        string $actionsUrl = Values::NONE

    ) {
        $this->options['friendlyName'] = $friendlyName;
        $this->options['uniqueName'] = $uniqueName;
        $this->options['actions'] = $actions;
        $this->options['actionsUrl'] = $actionsUrl;
    }

    /**
     * A descriptive string that you create to describe the resource. It is not unique and can be up to 255 characters long.
     *
     * @param string $friendlyName A descriptive string that you create to describe the resource. It is not unique and can be up to 255 characters long.
     * @return $this Fluent Builder
     */
    public function setFriendlyName(string $friendlyName): self
    {
        $this->options['friendlyName'] = $friendlyName;
        return $this;
    }

    /**
     * An application-defined string that uniquely identifies the resource. This value must be 64 characters or less in length and be unique. It can be used as an alternative to the `sid` in the URL path to address the resource.
     *
     * @param string $uniqueName An application-defined string that uniquely identifies the resource. This value must be 64 characters or less in length and be unique. It can be used as an alternative to the `sid` in the URL path to address the resource.
     * @return $this Fluent Builder
     */
    public function setUniqueName(string $uniqueName): self
    {
        $this->options['uniqueName'] = $uniqueName;
        return $this;
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
     * The URL from which the Assistant can fetch actions.
     *
     * @param string $actionsUrl The URL from which the Assistant can fetch actions.
     * @return $this Fluent Builder
     */
    public function setActionsUrl(string $actionsUrl): self
    {
        $this->options['actionsUrl'] = $actionsUrl;
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
        return '[Twilio.Autopilot.V1.UpdateTaskOptions ' . $options . ']';
    }
}

