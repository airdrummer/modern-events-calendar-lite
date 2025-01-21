<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Verify
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Verify\V2\Service\Entity;

use Twilio\Options;
use Twilio\Values;

abstract class ChallengeOptions
{
    /**
     * @param \DateTime $expirationDate The date-time when this Challenge expires, given in [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) format. The default value is five (5) minutes after Challenge creation. The max value is sixty (60) minutes after creation.
     * @param string $detailsMessage Shown to the user when the push notification arrives. Required when `factor_type` is `push`. Can be up to 256 characters in length
     * @param array[] $detailsFields A list of objects that describe the Fields included in the Challenge. Each object contains the label and value of the field, the label can be up to 36 characters in length and the value can be up to 128 characters in length. Used when `factor_type` is `push`. There can be up to 20 details fields.
     * @param array $hiddenDetails Details provided to give context about the Challenge. Not shown to the end user. It must be a stringified JSON with only strings values eg. `{\\\"ip\\\": \\\"172.168.1.234\\\"}`. Can be up to 1024 characters in length
     * @param string $authPayload Optional payload used to verify the Challenge upon creation. Only used with a Factor of type `totp` to carry the TOTP code that needs to be verified. For `TOTP` this value must be between 3 and 8 characters long.
     * @return CreateChallengeOptions Options builder
     */
    public static function create(
        
        \DateTime $expirationDate = null,
        string $detailsMessage = Values::NONE,
        array $detailsFields = Values::ARRAY_NONE,
        array $hiddenDetails = Values::ARRAY_NONE,
        string $authPayload = Values::NONE

    ): CreateChallengeOptions
    {
        return new CreateChallengeOptions(
            $expirationDate,
            $detailsMessage,
            $detailsFields,
            $hiddenDetails,
            $authPayload
        );
    }


    /**
     * @param string $factorSid The unique SID identifier of the Factor.
     * @param string $status The Status of the Challenges to fetch. One of `pending`, `expired`, `approved` or `denied`.
     * @param string $order The desired sort order of the Challenges list. One of `asc` or `desc` for ascending and descending respectively. Defaults to `asc`.
     * @return ReadChallengeOptions Options builder
     */
    public static function read(
        
        string $factorSid = Values::NONE,
        string $status = Values::NONE,
        string $order = Values::NONE

    ): ReadChallengeOptions
    {
        return new ReadChallengeOptions(
            $factorSid,
            $status,
            $order
        );
    }

    /**
     * @param string $authPayload The optional payload needed to verify the Challenge. E.g., a TOTP would use the numeric code. For `TOTP` this value must be between 3 and 8 characters long. For `Push` this value can be up to 5456 characters in length
     * @param array $metadata Custom metadata associated with the challenge. This is added by the Device/SDK directly to allow for the inclusion of device information. It must be a stringified JSON with only strings values eg. `{\\\"os\\\": \\\"Android\\\"}`. Can be up to 1024 characters in length.
     * @return UpdateChallengeOptions Options builder
     */
    public static function update(
        
        string $authPayload = Values::NONE,
        array $metadata = Values::ARRAY_NONE

    ): UpdateChallengeOptions
    {
        return new UpdateChallengeOptions(
            $authPayload,
            $metadata
        );
    }

}

class CreateChallengeOptions extends Options
    {
    /**
     * @param \DateTime $expirationDate The date-time when this Challenge expires, given in [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) format. The default value is five (5) minutes after Challenge creation. The max value is sixty (60) minutes after creation.
     * @param string $detailsMessage Shown to the user when the push notification arrives. Required when `factor_type` is `push`. Can be up to 256 characters in length
     * @param array[] $detailsFields A list of objects that describe the Fields included in the Challenge. Each object contains the label and value of the field, the label can be up to 36 characters in length and the value can be up to 128 characters in length. Used when `factor_type` is `push`. There can be up to 20 details fields.
     * @param array $hiddenDetails Details provided to give context about the Challenge. Not shown to the end user. It must be a stringified JSON with only strings values eg. `{\\\"ip\\\": \\\"172.168.1.234\\\"}`. Can be up to 1024 characters in length
     * @param string $authPayload Optional payload used to verify the Challenge upon creation. Only used with a Factor of type `totp` to carry the TOTP code that needs to be verified. For `TOTP` this value must be between 3 and 8 characters long.
     */
    public function __construct(
        
        \DateTime $expirationDate = null,
        string $detailsMessage = Values::NONE,
        array $detailsFields = Values::ARRAY_NONE,
        array $hiddenDetails = Values::ARRAY_NONE,
        string $authPayload = Values::NONE

    ) {
        $this->options['expirationDate'] = $expirationDate;
        $this->options['detailsMessage'] = $detailsMessage;
        $this->options['detailsFields'] = $detailsFields;
        $this->options['hiddenDetails'] = $hiddenDetails;
        $this->options['authPayload'] = $authPayload;
    }

    /**
     * The date-time when this Challenge expires, given in [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) format. The default value is five (5) minutes after Challenge creation. The max value is sixty (60) minutes after creation.
     *
     * @param \DateTime $expirationDate The date-time when this Challenge expires, given in [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) format. The default value is five (5) minutes after Challenge creation. The max value is sixty (60) minutes after creation.
     * @return $this Fluent Builder
     */
    public function setExpirationDate(\DateTime $expirationDate): self
    {
        $this->options['expirationDate'] = $expirationDate;
        return $this;
    }

    /**
     * Shown to the user when the push notification arrives. Required when `factor_type` is `push`. Can be up to 256 characters in length
     *
     * @param string $detailsMessage Shown to the user when the push notification arrives. Required when `factor_type` is `push`. Can be up to 256 characters in length
     * @return $this Fluent Builder
     */
    public function setDetailsMessage(string $detailsMessage): self
    {
        $this->options['detailsMessage'] = $detailsMessage;
        return $this;
    }

    /**
     * A list of objects that describe the Fields included in the Challenge. Each object contains the label and value of the field, the label can be up to 36 characters in length and the value can be up to 128 characters in length. Used when `factor_type` is `push`. There can be up to 20 details fields.
     *
     * @param array[] $detailsFields A list of objects that describe the Fields included in the Challenge. Each object contains the label and value of the field, the label can be up to 36 characters in length and the value can be up to 128 characters in length. Used when `factor_type` is `push`. There can be up to 20 details fields.
     * @return $this Fluent Builder
     */
    public function setDetailsFields(array $detailsFields): self
    {
        $this->options['detailsFields'] = $detailsFields;
        return $this;
    }

    /**
     * Details provided to give context about the Challenge. Not shown to the end user. It must be a stringified JSON with only strings values eg. `{\\\"ip\\\": \\\"172.168.1.234\\\"}`. Can be up to 1024 characters in length
     *
     * @param array $hiddenDetails Details provided to give context about the Challenge. Not shown to the end user. It must be a stringified JSON with only strings values eg. `{\\\"ip\\\": \\\"172.168.1.234\\\"}`. Can be up to 1024 characters in length
     * @return $this Fluent Builder
     */
    public function setHiddenDetails(array $hiddenDetails): self
    {
        $this->options['hiddenDetails'] = $hiddenDetails;
        return $this;
    }

    /**
     * Optional payload used to verify the Challenge upon creation. Only used with a Factor of type `totp` to carry the TOTP code that needs to be verified. For `TOTP` this value must be between 3 and 8 characters long.
     *
     * @param string $authPayload Optional payload used to verify the Challenge upon creation. Only used with a Factor of type `totp` to carry the TOTP code that needs to be verified. For `TOTP` this value must be between 3 and 8 characters long.
     * @return $this Fluent Builder
     */
    public function setAuthPayload(string $authPayload): self
    {
        $this->options['authPayload'] = $authPayload;
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
        return '[Twilio.Verify.V2.CreateChallengeOptions ' . $options . ']';
    }
}


class ReadChallengeOptions extends Options
    {
    /**
     * @param string $factorSid The unique SID identifier of the Factor.
     * @param string $status The Status of the Challenges to fetch. One of `pending`, `expired`, `approved` or `denied`.
     * @param string $order The desired sort order of the Challenges list. One of `asc` or `desc` for ascending and descending respectively. Defaults to `asc`.
     */
    public function __construct(
        
        string $factorSid = Values::NONE,
        string $status = Values::NONE,
        string $order = Values::NONE

    ) {
        $this->options['factorSid'] = $factorSid;
        $this->options['status'] = $status;
        $this->options['order'] = $order;
    }

    /**
     * The unique SID identifier of the Factor.
     *
     * @param string $factorSid The unique SID identifier of the Factor.
     * @return $this Fluent Builder
     */
    public function setFactorSid(string $factorSid): self
    {
        $this->options['factorSid'] = $factorSid;
        return $this;
    }

    /**
     * The Status of the Challenges to fetch. One of `pending`, `expired`, `approved` or `denied`.
     *
     * @param string $status The Status of the Challenges to fetch. One of `pending`, `expired`, `approved` or `denied`.
     * @return $this Fluent Builder
     */
    public function setStatus(string $status): self
    {
        $this->options['status'] = $status;
        return $this;
    }

    /**
     * The desired sort order of the Challenges list. One of `asc` or `desc` for ascending and descending respectively. Defaults to `asc`.
     *
     * @param string $order The desired sort order of the Challenges list. One of `asc` or `desc` for ascending and descending respectively. Defaults to `asc`.
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
        return '[Twilio.Verify.V2.ReadChallengeOptions ' . $options . ']';
    }
}

class UpdateChallengeOptions extends Options
    {
    /**
     * @param string $authPayload The optional payload needed to verify the Challenge. E.g., a TOTP would use the numeric code. For `TOTP` this value must be between 3 and 8 characters long. For `Push` this value can be up to 5456 characters in length
     * @param array $metadata Custom metadata associated with the challenge. This is added by the Device/SDK directly to allow for the inclusion of device information. It must be a stringified JSON with only strings values eg. `{\\\"os\\\": \\\"Android\\\"}`. Can be up to 1024 characters in length.
     */
    public function __construct(
        
        string $authPayload = Values::NONE,
        array $metadata = Values::ARRAY_NONE

    ) {
        $this->options['authPayload'] = $authPayload;
        $this->options['metadata'] = $metadata;
    }

    /**
     * The optional payload needed to verify the Challenge. E.g., a TOTP would use the numeric code. For `TOTP` this value must be between 3 and 8 characters long. For `Push` this value can be up to 5456 characters in length
     *
     * @param string $authPayload The optional payload needed to verify the Challenge. E.g., a TOTP would use the numeric code. For `TOTP` this value must be between 3 and 8 characters long. For `Push` this value can be up to 5456 characters in length
     * @return $this Fluent Builder
     */
    public function setAuthPayload(string $authPayload): self
    {
        $this->options['authPayload'] = $authPayload;
        return $this;
    }

    /**
     * Custom metadata associated with the challenge. This is added by the Device/SDK directly to allow for the inclusion of device information. It must be a stringified JSON with only strings values eg. `{\\\"os\\\": \\\"Android\\\"}`. Can be up to 1024 characters in length.
     *
     * @param array $metadata Custom metadata associated with the challenge. This is added by the Device/SDK directly to allow for the inclusion of device information. It must be a stringified JSON with only strings values eg. `{\\\"os\\\": \\\"Android\\\"}`. Can be up to 1024 characters in length.
     * @return $this Fluent Builder
     */
    public function setMetadata(array $metadata): self
    {
        $this->options['metadata'] = $metadata;
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
        return '[Twilio.Verify.V2.UpdateChallengeOptions ' . $options . ']';
    }
}

