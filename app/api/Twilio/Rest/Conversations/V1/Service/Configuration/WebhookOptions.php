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

namespace Twilio\Rest\Conversations\V1\Service\Configuration;

use Twilio\Options;
use Twilio\Values;

abstract class WebhookOptions
{

    /**
     * @param string $preWebhookUrl The absolute url the pre-event webhook request should be sent to.
     * @param string $postWebhookUrl The absolute url the post-event webhook request should be sent to.
     * @param string[] $filters The list of events that your configured webhook targets will receive. Events not configured here will not fire. Possible values are `onParticipantAdd`, `onParticipantAdded`, `onDeliveryUpdated`, `onConversationUpdated`, `onConversationRemove`, `onParticipantRemove`, `onConversationUpdate`, `onMessageAdd`, `onMessageRemoved`, `onParticipantUpdated`, `onConversationAdded`, `onMessageAdded`, `onConversationAdd`, `onConversationRemoved`, `onParticipantUpdate`, `onMessageRemove`, `onMessageUpdated`, `onParticipantRemoved`, `onMessageUpdate` or `onConversationStateUpdated`.
     * @param string $method The HTTP method to be used when sending a webhook request. One of `GET` or `POST`.
     * @return UpdateWebhookOptions Options builder
     */
    public static function update(
        
        string $preWebhookUrl = Values::NONE,
        string $postWebhookUrl = Values::NONE,
        array $filters = Values::ARRAY_NONE,
        string $method = Values::NONE

    ): UpdateWebhookOptions
    {
        return new UpdateWebhookOptions(
            $preWebhookUrl,
            $postWebhookUrl,
            $filters,
            $method
        );
    }

}


class UpdateWebhookOptions extends Options
    {
    /**
     * @param string $preWebhookUrl The absolute url the pre-event webhook request should be sent to.
     * @param string $postWebhookUrl The absolute url the post-event webhook request should be sent to.
     * @param string[] $filters The list of events that your configured webhook targets will receive. Events not configured here will not fire. Possible values are `onParticipantAdd`, `onParticipantAdded`, `onDeliveryUpdated`, `onConversationUpdated`, `onConversationRemove`, `onParticipantRemove`, `onConversationUpdate`, `onMessageAdd`, `onMessageRemoved`, `onParticipantUpdated`, `onConversationAdded`, `onMessageAdded`, `onConversationAdd`, `onConversationRemoved`, `onParticipantUpdate`, `onMessageRemove`, `onMessageUpdated`, `onParticipantRemoved`, `onMessageUpdate` or `onConversationStateUpdated`.
     * @param string $method The HTTP method to be used when sending a webhook request. One of `GET` or `POST`.
     */
    public function __construct(
        
        string $preWebhookUrl = Values::NONE,
        string $postWebhookUrl = Values::NONE,
        array $filters = Values::ARRAY_NONE,
        string $method = Values::NONE

    ) {
        $this->options['preWebhookUrl'] = $preWebhookUrl;
        $this->options['postWebhookUrl'] = $postWebhookUrl;
        $this->options['filters'] = $filters;
        $this->options['method'] = $method;
    }

    /**
     * The absolute url the pre-event webhook request should be sent to.
     *
     * @param string $preWebhookUrl The absolute url the pre-event webhook request should be sent to.
     * @return $this Fluent Builder
     */
    public function setPreWebhookUrl(string $preWebhookUrl): self
    {
        $this->options['preWebhookUrl'] = $preWebhookUrl;
        return $this;
    }

    /**
     * The absolute url the post-event webhook request should be sent to.
     *
     * @param string $postWebhookUrl The absolute url the post-event webhook request should be sent to.
     * @return $this Fluent Builder
     */
    public function setPostWebhookUrl(string $postWebhookUrl): self
    {
        $this->options['postWebhookUrl'] = $postWebhookUrl;
        return $this;
    }

    /**
     * The list of events that your configured webhook targets will receive. Events not configured here will not fire. Possible values are `onParticipantAdd`, `onParticipantAdded`, `onDeliveryUpdated`, `onConversationUpdated`, `onConversationRemove`, `onParticipantRemove`, `onConversationUpdate`, `onMessageAdd`, `onMessageRemoved`, `onParticipantUpdated`, `onConversationAdded`, `onMessageAdded`, `onConversationAdd`, `onConversationRemoved`, `onParticipantUpdate`, `onMessageRemove`, `onMessageUpdated`, `onParticipantRemoved`, `onMessageUpdate` or `onConversationStateUpdated`.
     *
     * @param string[] $filters The list of events that your configured webhook targets will receive. Events not configured here will not fire. Possible values are `onParticipantAdd`, `onParticipantAdded`, `onDeliveryUpdated`, `onConversationUpdated`, `onConversationRemove`, `onParticipantRemove`, `onConversationUpdate`, `onMessageAdd`, `onMessageRemoved`, `onParticipantUpdated`, `onConversationAdded`, `onMessageAdded`, `onConversationAdd`, `onConversationRemoved`, `onParticipantUpdate`, `onMessageRemove`, `onMessageUpdated`, `onParticipantRemoved`, `onMessageUpdate` or `onConversationStateUpdated`.
     * @return $this Fluent Builder
     */
    public function setFilters(array $filters): self
    {
        $this->options['filters'] = $filters;
        return $this;
    }

    /**
     * The HTTP method to be used when sending a webhook request. One of `GET` or `POST`.
     *
     * @param string $method The HTTP method to be used when sending a webhook request. One of `GET` or `POST`.
     * @return $this Fluent Builder
     */
    public function setMethod(string $method): self
    {
        $this->options['method'] = $method;
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
        return '[Twilio.Conversations.V1.UpdateWebhookOptions ' . $options . ']';
    }
}

