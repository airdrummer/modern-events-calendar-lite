<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Conversations
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Conversations\V1\Service\Conversation;

use Twilio\Options;
use Twilio\Values;

abstract class MessageOptions
{
    /**
     * @param string $author The channel specific identifier of the message's author. Defaults to `system`.
     * @param string $body The content of the message, can be up to 1,600 characters long.
     * @param \DateTime $dateCreated The date that this resource was created.
     * @param \DateTime $dateUpdated The date that this resource was last updated. `null` if the message has not been edited.
     * @param string $attributes A string metadata field you can use to store any data you wish. The string value must contain structurally valid JSON if specified.  **Note** that if the attributes are not set \\\"{}\\\" will be returned.
     * @param string $mediaSid The Media SID to be attached to the new Message.
     * @param string $contentSid The unique ID of the multi-channel [Rich Content](https://www.twilio.com/docs/content-api) template, required for template-generated messages.  **Note** that if this field is set, `Body` and `MediaSid` parameters are ignored.
     * @param string $contentVariables A structurally valid JSON string that contains values to resolve Rich Content template variables.
     * @param string $xTwilioWebhookEnabled The X-Twilio-Webhook-Enabled HTTP request header
     * @return CreateMessageOptions Options builder
     */
    public static function create(
        
        string $author = Values::NONE,
        string $body = Values::NONE,
        \DateTime $dateCreated = null,
        \DateTime $dateUpdated = null,
        string $attributes = Values::NONE,
        string $mediaSid = Values::NONE,
        string $contentSid = Values::NONE,
        string $contentVariables = Values::NONE,
        string $xTwilioWebhookEnabled = Values::NONE

    ): CreateMessageOptions
    {
        return new CreateMessageOptions(
            $author,
            $body,
            $dateCreated,
            $dateUpdated,
            $attributes,
            $mediaSid,
            $contentSid,
            $contentVariables,
            $xTwilioWebhookEnabled
        );
    }

    /**
     * @param string $xTwilioWebhookEnabled The X-Twilio-Webhook-Enabled HTTP request header
     * @return DeleteMessageOptions Options builder
     */
    public static function delete(
        
        string $xTwilioWebhookEnabled = Values::NONE

    ): DeleteMessageOptions
    {
        return new DeleteMessageOptions(
            $xTwilioWebhookEnabled
        );
    }


    /**
     * @param string $order The sort order of the returned messages. Can be: `asc` (ascending) or `desc` (descending), with `asc` as the default.
     * @return ReadMessageOptions Options builder
     */
    public static function read(
        
        string $order = Values::NONE

    ): ReadMessageOptions
    {
        return new ReadMessageOptions(
            $order
        );
    }

    /**
     * @param string $author The channel specific identifier of the message's author. Defaults to `system`.
     * @param string $body The content of the message, can be up to 1,600 characters long.
     * @param \DateTime $dateCreated The date that this resource was created.
     * @param \DateTime $dateUpdated The date that this resource was last updated. `null` if the message has not been edited.
     * @param string $attributes A string metadata field you can use to store any data you wish. The string value must contain structurally valid JSON if specified.  **Note** that if the attributes are not set \\\"{}\\\" will be returned.
     * @param string $xTwilioWebhookEnabled The X-Twilio-Webhook-Enabled HTTP request header
     * @return UpdateMessageOptions Options builder
     */
    public static function update(
        
        string $author = Values::NONE,
        string $body = Values::NONE,
        \DateTime $dateCreated = null,
        \DateTime $dateUpdated = null,
        string $attributes = Values::NONE,
        string $xTwilioWebhookEnabled = Values::NONE

    ): UpdateMessageOptions
    {
        return new UpdateMessageOptions(
            $author,
            $body,
            $dateCreated,
            $dateUpdated,
            $attributes,
            $xTwilioWebhookEnabled
        );
    }

}

class CreateMessageOptions extends Options
    {
    /**
     * @param string $author The channel specific identifier of the message's author. Defaults to `system`.
     * @param string $body The content of the message, can be up to 1,600 characters long.
     * @param \DateTime $dateCreated The date that this resource was created.
     * @param \DateTime $dateUpdated The date that this resource was last updated. `null` if the message has not been edited.
     * @param string $attributes A string metadata field you can use to store any data you wish. The string value must contain structurally valid JSON if specified.  **Note** that if the attributes are not set \\\"{}\\\" will be returned.
     * @param string $mediaSid The Media SID to be attached to the new Message.
     * @param string $contentSid The unique ID of the multi-channel [Rich Content](https://www.twilio.com/docs/content-api) template, required for template-generated messages.  **Note** that if this field is set, `Body` and `MediaSid` parameters are ignored.
     * @param string $contentVariables A structurally valid JSON string that contains values to resolve Rich Content template variables.
     * @param string $xTwilioWebhookEnabled The X-Twilio-Webhook-Enabled HTTP request header
     */
    public function __construct(
        
        string $author = Values::NONE,
        string $body = Values::NONE,
        \DateTime $dateCreated = null,
        \DateTime $dateUpdated = null,
        string $attributes = Values::NONE,
        string $mediaSid = Values::NONE,
        string $contentSid = Values::NONE,
        string $contentVariables = Values::NONE,
        string $xTwilioWebhookEnabled = Values::NONE

    ) {
        $this->options['author'] = $author;
        $this->options['body'] = $body;
        $this->options['dateCreated'] = $dateCreated;
        $this->options['dateUpdated'] = $dateUpdated;
        $this->options['attributes'] = $attributes;
        $this->options['mediaSid'] = $mediaSid;
        $this->options['contentSid'] = $contentSid;
        $this->options['contentVariables'] = $contentVariables;
        $this->options['xTwilioWebhookEnabled'] = $xTwilioWebhookEnabled;
    }

    /**
     * The channel specific identifier of the message's author. Defaults to `system`.
     *
     * @param string $author The channel specific identifier of the message's author. Defaults to `system`.
     * @return $this Fluent Builder
     */
    public function setAuthor(string $author): self
    {
        $this->options['author'] = $author;
        return $this;
    }

    /**
     * The content of the message, can be up to 1,600 characters long.
     *
     * @param string $body The content of the message, can be up to 1,600 characters long.
     * @return $this Fluent Builder
     */
    public function setBody(string $body): self
    {
        $this->options['body'] = $body;
        return $this;
    }

    /**
     * The date that this resource was created.
     *
     * @param \DateTime $dateCreated The date that this resource was created.
     * @return $this Fluent Builder
     */
    public function setDateCreated(\DateTime $dateCreated): self
    {
        $this->options['dateCreated'] = $dateCreated;
        return $this;
    }

    /**
     * The date that this resource was last updated. `null` if the message has not been edited.
     *
     * @param \DateTime $dateUpdated The date that this resource was last updated. `null` if the message has not been edited.
     * @return $this Fluent Builder
     */
    public function setDateUpdated(\DateTime $dateUpdated): self
    {
        $this->options['dateUpdated'] = $dateUpdated;
        return $this;
    }

    /**
     * A string metadata field you can use to store any data you wish. The string value must contain structurally valid JSON if specified.  **Note** that if the attributes are not set \\\"{}\\\" will be returned.
     *
     * @param string $attributes A string metadata field you can use to store any data you wish. The string value must contain structurally valid JSON if specified.  **Note** that if the attributes are not set \\\"{}\\\" will be returned.
     * @return $this Fluent Builder
     */
    public function setAttributes(string $attributes): self
    {
        $this->options['attributes'] = $attributes;
        return $this;
    }

    /**
     * The Media SID to be attached to the new Message.
     *
     * @param string $mediaSid The Media SID to be attached to the new Message.
     * @return $this Fluent Builder
     */
    public function setMediaSid(string $mediaSid): self
    {
        $this->options['mediaSid'] = $mediaSid;
        return $this;
    }

    /**
     * The unique ID of the multi-channel [Rich Content](https://www.twilio.com/docs/content-api) template, required for template-generated messages.  **Note** that if this field is set, `Body` and `MediaSid` parameters are ignored.
     *
     * @param string $contentSid The unique ID of the multi-channel [Rich Content](https://www.twilio.com/docs/content-api) template, required for template-generated messages.  **Note** that if this field is set, `Body` and `MediaSid` parameters are ignored.
     * @return $this Fluent Builder
     */
    public function setContentSid(string $contentSid): self
    {
        $this->options['contentSid'] = $contentSid;
        return $this;
    }

    /**
     * A structurally valid JSON string that contains values to resolve Rich Content template variables.
     *
     * @param string $contentVariables A structurally valid JSON string that contains values to resolve Rich Content template variables.
     * @return $this Fluent Builder
     */
    public function setContentVariables(string $contentVariables): self
    {
        $this->options['contentVariables'] = $contentVariables;
        return $this;
    }

    /**
     * The X-Twilio-Webhook-Enabled HTTP request header
     *
     * @param string $xTwilioWebhookEnabled The X-Twilio-Webhook-Enabled HTTP request header
     * @return $this Fluent Builder
     */
    public function setXTwilioWebhookEnabled(string $xTwilioWebhookEnabled): self
    {
        $this->options['xTwilioWebhookEnabled'] = $xTwilioWebhookEnabled;
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
        return '[Twilio.Conversations.V1.CreateMessageOptions ' . $options . ']';
    }
}

class DeleteMessageOptions extends Options
    {
    /**
     * @param string $xTwilioWebhookEnabled The X-Twilio-Webhook-Enabled HTTP request header
     */
    public function __construct(
        
        string $xTwilioWebhookEnabled = Values::NONE

    ) {
        $this->options['xTwilioWebhookEnabled'] = $xTwilioWebhookEnabled;
    }

    /**
     * The X-Twilio-Webhook-Enabled HTTP request header
     *
     * @param string $xTwilioWebhookEnabled The X-Twilio-Webhook-Enabled HTTP request header
     * @return $this Fluent Builder
     */
    public function setXTwilioWebhookEnabled(string $xTwilioWebhookEnabled): self
    {
        $this->options['xTwilioWebhookEnabled'] = $xTwilioWebhookEnabled;
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
        return '[Twilio.Conversations.V1.DeleteMessageOptions ' . $options . ']';
    }
}


class ReadMessageOptions extends Options
    {
    /**
     * @param string $order The sort order of the returned messages. Can be: `asc` (ascending) or `desc` (descending), with `asc` as the default.
     */
    public function __construct(
        
        string $order = Values::NONE

    ) {
        $this->options['order'] = $order;
    }

    /**
     * The sort order of the returned messages. Can be: `asc` (ascending) or `desc` (descending), with `asc` as the default.
     *
     * @param string $order The sort order of the returned messages. Can be: `asc` (ascending) or `desc` (descending), with `asc` as the default.
     * @return $this Fluent Builder
     */
    public function setOrder(string $order): self
    {
        $this->options['order'] = $order;
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
        return '[Twilio.Conversations.V1.ReadMessageOptions ' . $options . ']';
    }
}

class UpdateMessageOptions extends Options
    {
    /**
     * @param string $author The channel specific identifier of the message's author. Defaults to `system`.
     * @param string $body The content of the message, can be up to 1,600 characters long.
     * @param \DateTime $dateCreated The date that this resource was created.
     * @param \DateTime $dateUpdated The date that this resource was last updated. `null` if the message has not been edited.
     * @param string $attributes A string metadata field you can use to store any data you wish. The string value must contain structurally valid JSON if specified.  **Note** that if the attributes are not set \\\"{}\\\" will be returned.
     * @param string $xTwilioWebhookEnabled The X-Twilio-Webhook-Enabled HTTP request header
     */
    public function __construct(
        
        string $author = Values::NONE,
        string $body = Values::NONE,
        \DateTime $dateCreated = null,
        \DateTime $dateUpdated = null,
        string $attributes = Values::NONE,
        string $xTwilioWebhookEnabled = Values::NONE

    ) {
        $this->options['author'] = $author;
        $this->options['body'] = $body;
        $this->options['dateCreated'] = $dateCreated;
        $this->options['dateUpdated'] = $dateUpdated;
        $this->options['attributes'] = $attributes;
        $this->options['xTwilioWebhookEnabled'] = $xTwilioWebhookEnabled;
    }

    /**
     * The channel specific identifier of the message's author. Defaults to `system`.
     *
     * @param string $author The channel specific identifier of the message's author. Defaults to `system`.
     * @return $this Fluent Builder
     */
    public function setAuthor(string $author): self
    {
        $this->options['author'] = $author;
        return $this;
    }

    /**
     * The content of the message, can be up to 1,600 characters long.
     *
     * @param string $body The content of the message, can be up to 1,600 characters long.
     * @return $this Fluent Builder
     */
    public function setBody(string $body): self
    {
        $this->options['body'] = $body;
        return $this;
    }

    /**
     * The date that this resource was created.
     *
     * @param \DateTime $dateCreated The date that this resource was created.
     * @return $this Fluent Builder
     */
    public function setDateCreated(\DateTime $dateCreated): self
    {
        $this->options['dateCreated'] = $dateCreated;
        return $this;
    }

    /**
     * The date that this resource was last updated. `null` if the message has not been edited.
     *
     * @param \DateTime $dateUpdated The date that this resource was last updated. `null` if the message has not been edited.
     * @return $this Fluent Builder
     */
    public function setDateUpdated(\DateTime $dateUpdated): self
    {
        $this->options['dateUpdated'] = $dateUpdated;
        return $this;
    }

    /**
     * A string metadata field you can use to store any data you wish. The string value must contain structurally valid JSON if specified.  **Note** that if the attributes are not set \\\"{}\\\" will be returned.
     *
     * @param string $attributes A string metadata field you can use to store any data you wish. The string value must contain structurally valid JSON if specified.  **Note** that if the attributes are not set \\\"{}\\\" will be returned.
     * @return $this Fluent Builder
     */
    public function setAttributes(string $attributes): self
    {
        $this->options['attributes'] = $attributes;
        return $this;
    }

    /**
     * The X-Twilio-Webhook-Enabled HTTP request header
     *
     * @param string $xTwilioWebhookEnabled The X-Twilio-Webhook-Enabled HTTP request header
     * @return $this Fluent Builder
     */
    public function setXTwilioWebhookEnabled(string $xTwilioWebhookEnabled): self
    {
        $this->options['xTwilioWebhookEnabled'] = $xTwilioWebhookEnabled;
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
        return '[Twilio.Conversations.V1.UpdateMessageOptions ' . $options . ']';
    }
}

