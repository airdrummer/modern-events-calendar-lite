<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Notify
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Notify\V1;

use Twilio\Options;
use Twilio\Values;

abstract class ServiceOptions
{
    /**
     * @param string $friendlyName A descriptive string that you create to describe the resource. It can be up to 64 characters long.
     * @param string $apnCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for APN Bindings.
     * @param string $gcmCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for GCM Bindings.
     * @param string $messagingServiceSid The SID of the [Messaging Service](https://www.twilio.com/docs/sms/send-messages#messaging-services) to use for SMS Bindings. This parameter must be set in order to send SMS notifications.
     * @param string $facebookMessengerPageId Deprecated.
     * @param string $defaultApnNotificationProtocolVersion The protocol version to use for sending APNS notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @param string $defaultGcmNotificationProtocolVersion The protocol version to use for sending GCM notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @param string $fcmCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for FCM Bindings.
     * @param string $defaultFcmNotificationProtocolVersion The protocol version to use for sending FCM notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @param bool $logEnabled Whether to log notifications. Can be: `true` or `false` and the default is `true`.
     * @param string $alexaSkillId Deprecated.
     * @param string $defaultAlexaNotificationProtocolVersion Deprecated.
     * @param string $deliveryCallbackUrl URL to send delivery status callback.
     * @param bool $deliveryCallbackEnabled Callback configuration that enables delivery callbacks, default false
     * @return CreateServiceOptions Options builder
     */
    public static function create(
        
        string $friendlyName = Values::NONE,
        string $apnCredentialSid = Values::NONE,
        string $gcmCredentialSid = Values::NONE,
        string $messagingServiceSid = Values::NONE,
        string $facebookMessengerPageId = Values::NONE,
        string $defaultApnNotificationProtocolVersion = Values::NONE,
        string $defaultGcmNotificationProtocolVersion = Values::NONE,
        string $fcmCredentialSid = Values::NONE,
        string $defaultFcmNotificationProtocolVersion = Values::NONE,
        bool $logEnabled = Values::BOOL_NONE,
        string $alexaSkillId = Values::NONE,
        string $defaultAlexaNotificationProtocolVersion = Values::NONE,
        string $deliveryCallbackUrl = Values::NONE,
        bool $deliveryCallbackEnabled = Values::BOOL_NONE

    ): CreateServiceOptions
    {
        return new CreateServiceOptions(
            $friendlyName,
            $apnCredentialSid,
            $gcmCredentialSid,
            $messagingServiceSid,
            $facebookMessengerPageId,
            $defaultApnNotificationProtocolVersion,
            $defaultGcmNotificationProtocolVersion,
            $fcmCredentialSid,
            $defaultFcmNotificationProtocolVersion,
            $logEnabled,
            $alexaSkillId,
            $defaultAlexaNotificationProtocolVersion,
            $deliveryCallbackUrl,
            $deliveryCallbackEnabled
        );
    }



    /**
     * @param string $friendlyName The string that identifies the Service resources to read.
     * @return ReadServiceOptions Options builder
     */
    public static function read(
        
        string $friendlyName = Values::NONE

    ): ReadServiceOptions
    {
        return new ReadServiceOptions(
            $friendlyName
        );
    }

    /**
     * @param string $friendlyName A descriptive string that you create to describe the resource. It can be up to 64 characters long.
     * @param string $apnCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for APN Bindings.
     * @param string $gcmCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for GCM Bindings.
     * @param string $messagingServiceSid The SID of the [Messaging Service](https://www.twilio.com/docs/sms/send-messages#messaging-services) to use for SMS Bindings. This parameter must be set in order to send SMS notifications.
     * @param string $facebookMessengerPageId Deprecated.
     * @param string $defaultApnNotificationProtocolVersion The protocol version to use for sending APNS notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @param string $defaultGcmNotificationProtocolVersion The protocol version to use for sending GCM notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @param string $fcmCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for FCM Bindings.
     * @param string $defaultFcmNotificationProtocolVersion The protocol version to use for sending FCM notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @param bool $logEnabled Whether to log notifications. Can be: `true` or `false` and the default is `true`.
     * @param string $alexaSkillId Deprecated.
     * @param string $defaultAlexaNotificationProtocolVersion Deprecated.
     * @param string $deliveryCallbackUrl URL to send delivery status callback.
     * @param bool $deliveryCallbackEnabled Callback configuration that enables delivery callbacks, default false
     * @return UpdateServiceOptions Options builder
     */
    public static function update(
        
        string $friendlyName = Values::NONE,
        string $apnCredentialSid = Values::NONE,
        string $gcmCredentialSid = Values::NONE,
        string $messagingServiceSid = Values::NONE,
        string $facebookMessengerPageId = Values::NONE,
        string $defaultApnNotificationProtocolVersion = Values::NONE,
        string $defaultGcmNotificationProtocolVersion = Values::NONE,
        string $fcmCredentialSid = Values::NONE,
        string $defaultFcmNotificationProtocolVersion = Values::NONE,
        bool $logEnabled = Values::BOOL_NONE,
        string $alexaSkillId = Values::NONE,
        string $defaultAlexaNotificationProtocolVersion = Values::NONE,
        string $deliveryCallbackUrl = Values::NONE,
        bool $deliveryCallbackEnabled = Values::BOOL_NONE

    ): UpdateServiceOptions
    {
        return new UpdateServiceOptions(
            $friendlyName,
            $apnCredentialSid,
            $gcmCredentialSid,
            $messagingServiceSid,
            $facebookMessengerPageId,
            $defaultApnNotificationProtocolVersion,
            $defaultGcmNotificationProtocolVersion,
            $fcmCredentialSid,
            $defaultFcmNotificationProtocolVersion,
            $logEnabled,
            $alexaSkillId,
            $defaultAlexaNotificationProtocolVersion,
            $deliveryCallbackUrl,
            $deliveryCallbackEnabled
        );
    }

}

class CreateServiceOptions extends Options
    {
    /**
     * @param string $friendlyName A descriptive string that you create to describe the resource. It can be up to 64 characters long.
     * @param string $apnCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for APN Bindings.
     * @param string $gcmCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for GCM Bindings.
     * @param string $messagingServiceSid The SID of the [Messaging Service](https://www.twilio.com/docs/sms/send-messages#messaging-services) to use for SMS Bindings. This parameter must be set in order to send SMS notifications.
     * @param string $facebookMessengerPageId Deprecated.
     * @param string $defaultApnNotificationProtocolVersion The protocol version to use for sending APNS notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @param string $defaultGcmNotificationProtocolVersion The protocol version to use for sending GCM notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @param string $fcmCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for FCM Bindings.
     * @param string $defaultFcmNotificationProtocolVersion The protocol version to use for sending FCM notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @param bool $logEnabled Whether to log notifications. Can be: `true` or `false` and the default is `true`.
     * @param string $alexaSkillId Deprecated.
     * @param string $defaultAlexaNotificationProtocolVersion Deprecated.
     * @param string $deliveryCallbackUrl URL to send delivery status callback.
     * @param bool $deliveryCallbackEnabled Callback configuration that enables delivery callbacks, default false
     */
    public function __construct(
        
        string $friendlyName = Values::NONE,
        string $apnCredentialSid = Values::NONE,
        string $gcmCredentialSid = Values::NONE,
        string $messagingServiceSid = Values::NONE,
        string $facebookMessengerPageId = Values::NONE,
        string $defaultApnNotificationProtocolVersion = Values::NONE,
        string $defaultGcmNotificationProtocolVersion = Values::NONE,
        string $fcmCredentialSid = Values::NONE,
        string $defaultFcmNotificationProtocolVersion = Values::NONE,
        bool $logEnabled = Values::BOOL_NONE,
        string $alexaSkillId = Values::NONE,
        string $defaultAlexaNotificationProtocolVersion = Values::NONE,
        string $deliveryCallbackUrl = Values::NONE,
        bool $deliveryCallbackEnabled = Values::BOOL_NONE

    ) {
        $this->options['friendlyName'] = $friendlyName;
        $this->options['apnCredentialSid'] = $apnCredentialSid;
        $this->options['gcmCredentialSid'] = $gcmCredentialSid;
        $this->options['messagingServiceSid'] = $messagingServiceSid;
        $this->options['facebookMessengerPageId'] = $facebookMessengerPageId;
        $this->options['defaultApnNotificationProtocolVersion'] = $defaultApnNotificationProtocolVersion;
        $this->options['defaultGcmNotificationProtocolVersion'] = $defaultGcmNotificationProtocolVersion;
        $this->options['fcmCredentialSid'] = $fcmCredentialSid;
        $this->options['defaultFcmNotificationProtocolVersion'] = $defaultFcmNotificationProtocolVersion;
        $this->options['logEnabled'] = $logEnabled;
        $this->options['alexaSkillId'] = $alexaSkillId;
        $this->options['defaultAlexaNotificationProtocolVersion'] = $defaultAlexaNotificationProtocolVersion;
        $this->options['deliveryCallbackUrl'] = $deliveryCallbackUrl;
        $this->options['deliveryCallbackEnabled'] = $deliveryCallbackEnabled;
    }

    /**
     * A descriptive string that you create to describe the resource. It can be up to 64 characters long.
     *
     * @param string $friendlyName A descriptive string that you create to describe the resource. It can be up to 64 characters long.
     * @return $this Fluent Builder
     */
    public function setFriendlyName(string $friendlyName): self
    {
        $this->options['friendlyName'] = $friendlyName;
        return $this;
    }

    /**
     * The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for APN Bindings.
     *
     * @param string $apnCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for APN Bindings.
     * @return $this Fluent Builder
     */
    public function setApnCredentialSid(string $apnCredentialSid): self
    {
        $this->options['apnCredentialSid'] = $apnCredentialSid;
        return $this;
    }

    /**
     * The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for GCM Bindings.
     *
     * @param string $gcmCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for GCM Bindings.
     * @return $this Fluent Builder
     */
    public function setGcmCredentialSid(string $gcmCredentialSid): self
    {
        $this->options['gcmCredentialSid'] = $gcmCredentialSid;
        return $this;
    }

    /**
     * The SID of the [Messaging Service](https://www.twilio.com/docs/sms/send-messages#messaging-services) to use for SMS Bindings. This parameter must be set in order to send SMS notifications.
     *
     * @param string $messagingServiceSid The SID of the [Messaging Service](https://www.twilio.com/docs/sms/send-messages#messaging-services) to use for SMS Bindings. This parameter must be set in order to send SMS notifications.
     * @return $this Fluent Builder
     */
    public function setMessagingServiceSid(string $messagingServiceSid): self
    {
        $this->options['messagingServiceSid'] = $messagingServiceSid;
        return $this;
    }

    /**
     * Deprecated.
     *
     * @param string $facebookMessengerPageId Deprecated.
     * @return $this Fluent Builder
     */
    public function setFacebookMessengerPageId(string $facebookMessengerPageId): self
    {
        $this->options['facebookMessengerPageId'] = $facebookMessengerPageId;
        return $this;
    }

    /**
     * The protocol version to use for sending APNS notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     *
     * @param string $defaultApnNotificationProtocolVersion The protocol version to use for sending APNS notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @return $this Fluent Builder
     */
    public function setDefaultApnNotificationProtocolVersion(string $defaultApnNotificationProtocolVersion): self
    {
        $this->options['defaultApnNotificationProtocolVersion'] = $defaultApnNotificationProtocolVersion;
        return $this;
    }

    /**
     * The protocol version to use for sending GCM notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     *
     * @param string $defaultGcmNotificationProtocolVersion The protocol version to use for sending GCM notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @return $this Fluent Builder
     */
    public function setDefaultGcmNotificationProtocolVersion(string $defaultGcmNotificationProtocolVersion): self
    {
        $this->options['defaultGcmNotificationProtocolVersion'] = $defaultGcmNotificationProtocolVersion;
        return $this;
    }

    /**
     * The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for FCM Bindings.
     *
     * @param string $fcmCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for FCM Bindings.
     * @return $this Fluent Builder
     */
    public function setFcmCredentialSid(string $fcmCredentialSid): self
    {
        $this->options['fcmCredentialSid'] = $fcmCredentialSid;
        return $this;
    }

    /**
     * The protocol version to use for sending FCM notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     *
     * @param string $defaultFcmNotificationProtocolVersion The protocol version to use for sending FCM notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @return $this Fluent Builder
     */
    public function setDefaultFcmNotificationProtocolVersion(string $defaultFcmNotificationProtocolVersion): self
    {
        $this->options['defaultFcmNotificationProtocolVersion'] = $defaultFcmNotificationProtocolVersion;
        return $this;
    }

    /**
     * Whether to log notifications. Can be: `true` or `false` and the default is `true`.
     *
     * @param bool $logEnabled Whether to log notifications. Can be: `true` or `false` and the default is `true`.
     * @return $this Fluent Builder
     */
    public function setLogEnabled(bool $logEnabled): self
    {
        $this->options['logEnabled'] = $logEnabled;
        return $this;
    }

    /**
     * Deprecated.
     *
     * @param string $alexaSkillId Deprecated.
     * @return $this Fluent Builder
     */
    public function setAlexaSkillId(string $alexaSkillId): self
    {
        $this->options['alexaSkillId'] = $alexaSkillId;
        return $this;
    }

    /**
     * Deprecated.
     *
     * @param string $defaultAlexaNotificationProtocolVersion Deprecated.
     * @return $this Fluent Builder
     */
    public function setDefaultAlexaNotificationProtocolVersion(string $defaultAlexaNotificationProtocolVersion): self
    {
        $this->options['defaultAlexaNotificationProtocolVersion'] = $defaultAlexaNotificationProtocolVersion;
        return $this;
    }

    /**
     * URL to send delivery status callback.
     *
     * @param string $deliveryCallbackUrl URL to send delivery status callback.
     * @return $this Fluent Builder
     */
    public function setDeliveryCallbackUrl(string $deliveryCallbackUrl): self
    {
        $this->options['deliveryCallbackUrl'] = $deliveryCallbackUrl;
        return $this;
    }

    /**
     * Callback configuration that enables delivery callbacks, default false
     *
     * @param bool $deliveryCallbackEnabled Callback configuration that enables delivery callbacks, default false
     * @return $this Fluent Builder
     */
    public function setDeliveryCallbackEnabled(bool $deliveryCallbackEnabled): self
    {
        $this->options['deliveryCallbackEnabled'] = $deliveryCallbackEnabled;
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
        return '[Twilio.Notify.V1.CreateServiceOptions ' . $options . ']';
    }
}



class ReadServiceOptions extends Options
    {
    /**
     * @param string $friendlyName The string that identifies the Service resources to read.
     */
    public function __construct(
        
        string $friendlyName = Values::NONE

    ) {
        $this->options['friendlyName'] = $friendlyName;
    }

    /**
     * The string that identifies the Service resources to read.
     *
     * @param string $friendlyName The string that identifies the Service resources to read.
     * @return $this Fluent Builder
     */
    public function setFriendlyName(string $friendlyName): self
    {
        $this->options['friendlyName'] = $friendlyName;
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
        return '[Twilio.Notify.V1.ReadServiceOptions ' . $options . ']';
    }
}

class UpdateServiceOptions extends Options
    {
    /**
     * @param string $friendlyName A descriptive string that you create to describe the resource. It can be up to 64 characters long.
     * @param string $apnCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for APN Bindings.
     * @param string $gcmCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for GCM Bindings.
     * @param string $messagingServiceSid The SID of the [Messaging Service](https://www.twilio.com/docs/sms/send-messages#messaging-services) to use for SMS Bindings. This parameter must be set in order to send SMS notifications.
     * @param string $facebookMessengerPageId Deprecated.
     * @param string $defaultApnNotificationProtocolVersion The protocol version to use for sending APNS notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @param string $defaultGcmNotificationProtocolVersion The protocol version to use for sending GCM notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @param string $fcmCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for FCM Bindings.
     * @param string $defaultFcmNotificationProtocolVersion The protocol version to use for sending FCM notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @param bool $logEnabled Whether to log notifications. Can be: `true` or `false` and the default is `true`.
     * @param string $alexaSkillId Deprecated.
     * @param string $defaultAlexaNotificationProtocolVersion Deprecated.
     * @param string $deliveryCallbackUrl URL to send delivery status callback.
     * @param bool $deliveryCallbackEnabled Callback configuration that enables delivery callbacks, default false
     */
    public function __construct(
        
        string $friendlyName = Values::NONE,
        string $apnCredentialSid = Values::NONE,
        string $gcmCredentialSid = Values::NONE,
        string $messagingServiceSid = Values::NONE,
        string $facebookMessengerPageId = Values::NONE,
        string $defaultApnNotificationProtocolVersion = Values::NONE,
        string $defaultGcmNotificationProtocolVersion = Values::NONE,
        string $fcmCredentialSid = Values::NONE,
        string $defaultFcmNotificationProtocolVersion = Values::NONE,
        bool $logEnabled = Values::BOOL_NONE,
        string $alexaSkillId = Values::NONE,
        string $defaultAlexaNotificationProtocolVersion = Values::NONE,
        string $deliveryCallbackUrl = Values::NONE,
        bool $deliveryCallbackEnabled = Values::BOOL_NONE

    ) {
        $this->options['friendlyName'] = $friendlyName;
        $this->options['apnCredentialSid'] = $apnCredentialSid;
        $this->options['gcmCredentialSid'] = $gcmCredentialSid;
        $this->options['messagingServiceSid'] = $messagingServiceSid;
        $this->options['facebookMessengerPageId'] = $facebookMessengerPageId;
        $this->options['defaultApnNotificationProtocolVersion'] = $defaultApnNotificationProtocolVersion;
        $this->options['defaultGcmNotificationProtocolVersion'] = $defaultGcmNotificationProtocolVersion;
        $this->options['fcmCredentialSid'] = $fcmCredentialSid;
        $this->options['defaultFcmNotificationProtocolVersion'] = $defaultFcmNotificationProtocolVersion;
        $this->options['logEnabled'] = $logEnabled;
        $this->options['alexaSkillId'] = $alexaSkillId;
        $this->options['defaultAlexaNotificationProtocolVersion'] = $defaultAlexaNotificationProtocolVersion;
        $this->options['deliveryCallbackUrl'] = $deliveryCallbackUrl;
        $this->options['deliveryCallbackEnabled'] = $deliveryCallbackEnabled;
    }

    /**
     * A descriptive string that you create to describe the resource. It can be up to 64 characters long.
     *
     * @param string $friendlyName A descriptive string that you create to describe the resource. It can be up to 64 characters long.
     * @return $this Fluent Builder
     */
    public function setFriendlyName(string $friendlyName): self
    {
        $this->options['friendlyName'] = $friendlyName;
        return $this;
    }

    /**
     * The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for APN Bindings.
     *
     * @param string $apnCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for APN Bindings.
     * @return $this Fluent Builder
     */
    public function setApnCredentialSid(string $apnCredentialSid): self
    {
        $this->options['apnCredentialSid'] = $apnCredentialSid;
        return $this;
    }

    /**
     * The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for GCM Bindings.
     *
     * @param string $gcmCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for GCM Bindings.
     * @return $this Fluent Builder
     */
    public function setGcmCredentialSid(string $gcmCredentialSid): self
    {
        $this->options['gcmCredentialSid'] = $gcmCredentialSid;
        return $this;
    }

    /**
     * The SID of the [Messaging Service](https://www.twilio.com/docs/sms/send-messages#messaging-services) to use for SMS Bindings. This parameter must be set in order to send SMS notifications.
     *
     * @param string $messagingServiceSid The SID of the [Messaging Service](https://www.twilio.com/docs/sms/send-messages#messaging-services) to use for SMS Bindings. This parameter must be set in order to send SMS notifications.
     * @return $this Fluent Builder
     */
    public function setMessagingServiceSid(string $messagingServiceSid): self
    {
        $this->options['messagingServiceSid'] = $messagingServiceSid;
        return $this;
    }

    /**
     * Deprecated.
     *
     * @param string $facebookMessengerPageId Deprecated.
     * @return $this Fluent Builder
     */
    public function setFacebookMessengerPageId(string $facebookMessengerPageId): self
    {
        $this->options['facebookMessengerPageId'] = $facebookMessengerPageId;
        return $this;
    }

    /**
     * The protocol version to use for sending APNS notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     *
     * @param string $defaultApnNotificationProtocolVersion The protocol version to use for sending APNS notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @return $this Fluent Builder
     */
    public function setDefaultApnNotificationProtocolVersion(string $defaultApnNotificationProtocolVersion): self
    {
        $this->options['defaultApnNotificationProtocolVersion'] = $defaultApnNotificationProtocolVersion;
        return $this;
    }

    /**
     * The protocol version to use for sending GCM notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     *
     * @param string $defaultGcmNotificationProtocolVersion The protocol version to use for sending GCM notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @return $this Fluent Builder
     */
    public function setDefaultGcmNotificationProtocolVersion(string $defaultGcmNotificationProtocolVersion): self
    {
        $this->options['defaultGcmNotificationProtocolVersion'] = $defaultGcmNotificationProtocolVersion;
        return $this;
    }

    /**
     * The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for FCM Bindings.
     *
     * @param string $fcmCredentialSid The SID of the [Credential](https://www.twilio.com/docs/notify/api/credential-resource) to use for FCM Bindings.
     * @return $this Fluent Builder
     */
    public function setFcmCredentialSid(string $fcmCredentialSid): self
    {
        $this->options['fcmCredentialSid'] = $fcmCredentialSid;
        return $this;
    }

    /**
     * The protocol version to use for sending FCM notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     *
     * @param string $defaultFcmNotificationProtocolVersion The protocol version to use for sending FCM notifications. Can be overridden on a Binding by Binding basis when creating a [Binding](https://www.twilio.com/docs/notify/api/binding-resource) resource.
     * @return $this Fluent Builder
     */
    public function setDefaultFcmNotificationProtocolVersion(string $defaultFcmNotificationProtocolVersion): self
    {
        $this->options['defaultFcmNotificationProtocolVersion'] = $defaultFcmNotificationProtocolVersion;
        return $this;
    }

    /**
     * Whether to log notifications. Can be: `true` or `false` and the default is `true`.
     *
     * @param bool $logEnabled Whether to log notifications. Can be: `true` or `false` and the default is `true`.
     * @return $this Fluent Builder
     */
    public function setLogEnabled(bool $logEnabled): self
    {
        $this->options['logEnabled'] = $logEnabled;
        return $this;
    }

    /**
     * Deprecated.
     *
     * @param string $alexaSkillId Deprecated.
     * @return $this Fluent Builder
     */
    public function setAlexaSkillId(string $alexaSkillId): self
    {
        $this->options['alexaSkillId'] = $alexaSkillId;
        return $this;
    }

    /**
     * Deprecated.
     *
     * @param string $defaultAlexaNotificationProtocolVersion Deprecated.
     * @return $this Fluent Builder
     */
    public function setDefaultAlexaNotificationProtocolVersion(string $defaultAlexaNotificationProtocolVersion): self
    {
        $this->options['defaultAlexaNotificationProtocolVersion'] = $defaultAlexaNotificationProtocolVersion;
        return $this;
    }

    /**
     * URL to send delivery status callback.
     *
     * @param string $deliveryCallbackUrl URL to send delivery status callback.
     * @return $this Fluent Builder
     */
    public function setDeliveryCallbackUrl(string $deliveryCallbackUrl): self
    {
        $this->options['deliveryCallbackUrl'] = $deliveryCallbackUrl;
        return $this;
    }

    /**
     * Callback configuration that enables delivery callbacks, default false
     *
     * @param bool $deliveryCallbackEnabled Callback configuration that enables delivery callbacks, default false
     * @return $this Fluent Builder
     */
    public function setDeliveryCallbackEnabled(bool $deliveryCallbackEnabled): self
    {
        $this->options['deliveryCallbackEnabled'] = $deliveryCallbackEnabled;
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
        return '[Twilio.Notify.V1.UpdateServiceOptions ' . $options . ']';
    }
}

