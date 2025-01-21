<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Insights
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Insights\V1\Call;

use Twilio\Options;
use Twilio\Values;

abstract class AnnotationOptions
{

    /**
     * @param string $answeredBy
     * @param string $connectivityIssue
     * @param string $qualityIssues Specify if the call had any subjective quality issues. Possible values, one or more of:  no_quality_issue, low_volume, choppy_robotic, echo, dtmf, latency, owa, static_noise. Use comma separated values to indicate multiple quality issues for the same call
     * @param bool $spam Specify if the call was a spam call. Use this to provide feedback on whether calls placed from your account were marked as spam, or if inbound calls received by your account were unwanted spam. Is of type Boolean: true, false. Use true if the call was a spam call.
     * @param int $callScore Specify the call score. This is of type integer. Use a range of 1-5 to indicate the call experience score, with the following mapping as a reference for rating the call [5: Excellent, 4: Good, 3 : Fair, 2 : Poor, 1: Bad].
     * @param string $comment Specify any comments pertaining to the call. This of type string with a max limit of 100 characters. Twilio does not treat this field as PII, so don’t put any PII in here.
     * @param string $incident Associate this call with an incident or support ticket. This is of type string with a max limit of 100 characters. Twilio does not treat this field as PII, so don’t put any PII in here.
     * @return UpdateAnnotationOptions Options builder
     */
    public static function update(
        
        string $answeredBy = Values::NONE,
        string $connectivityIssue = Values::NONE,
        string $qualityIssues = Values::NONE,
        bool $spam = Values::BOOL_NONE,
        int $callScore = Values::INT_NONE,
        string $comment = Values::NONE,
        string $incident = Values::NONE

    ): UpdateAnnotationOptions
    {
        return new UpdateAnnotationOptions(
            $answeredBy,
            $connectivityIssue,
            $qualityIssues,
            $spam,
            $callScore,
            $comment,
            $incident
        );
    }

}


class UpdateAnnotationOptions extends Options
    {
    /**
     * @param string $answeredBy
     * @param string $connectivityIssue
     * @param string $qualityIssues Specify if the call had any subjective quality issues. Possible values, one or more of:  no_quality_issue, low_volume, choppy_robotic, echo, dtmf, latency, owa, static_noise. Use comma separated values to indicate multiple quality issues for the same call
     * @param bool $spam Specify if the call was a spam call. Use this to provide feedback on whether calls placed from your account were marked as spam, or if inbound calls received by your account were unwanted spam. Is of type Boolean: true, false. Use true if the call was a spam call.
     * @param int $callScore Specify the call score. This is of type integer. Use a range of 1-5 to indicate the call experience score, with the following mapping as a reference for rating the call [5: Excellent, 4: Good, 3 : Fair, 2 : Poor, 1: Bad].
     * @param string $comment Specify any comments pertaining to the call. This of type string with a max limit of 100 characters. Twilio does not treat this field as PII, so don’t put any PII in here.
     * @param string $incident Associate this call with an incident or support ticket. This is of type string with a max limit of 100 characters. Twilio does not treat this field as PII, so don’t put any PII in here.
     */
    public function __construct(
        
        string $answeredBy = Values::NONE,
        string $connectivityIssue = Values::NONE,
        string $qualityIssues = Values::NONE,
        bool $spam = Values::BOOL_NONE,
        int $callScore = Values::INT_NONE,
        string $comment = Values::NONE,
        string $incident = Values::NONE

    ) {
        $this->options['answeredBy'] = $answeredBy;
        $this->options['connectivityIssue'] = $connectivityIssue;
        $this->options['qualityIssues'] = $qualityIssues;
        $this->options['spam'] = $spam;
        $this->options['callScore'] = $callScore;
        $this->options['comment'] = $comment;
        $this->options['incident'] = $incident;
    }

    /**
     * @param string $answeredBy
     * @return $this Fluent Builder
     */
    public function setAnsweredBy(string $answeredBy): self
    {
        $this->options['answeredBy'] = $answeredBy;
        return $this;
    }

    /**
     * @param string $connectivityIssue
     * @return $this Fluent Builder
     */
    public function setConnectivityIssue(string $connectivityIssue): self
    {
        $this->options['connectivityIssue'] = $connectivityIssue;
        return $this;
    }

    /**
     * Specify if the call had any subjective quality issues. Possible values, one or more of:  no_quality_issue, low_volume, choppy_robotic, echo, dtmf, latency, owa, static_noise. Use comma separated values to indicate multiple quality issues for the same call
     *
     * @param string $qualityIssues Specify if the call had any subjective quality issues. Possible values, one or more of:  no_quality_issue, low_volume, choppy_robotic, echo, dtmf, latency, owa, static_noise. Use comma separated values to indicate multiple quality issues for the same call
     * @return $this Fluent Builder
     */
    public function setQualityIssues(string $qualityIssues): self
    {
        $this->options['qualityIssues'] = $qualityIssues;
        return $this;
    }

    /**
     * Specify if the call was a spam call. Use this to provide feedback on whether calls placed from your account were marked as spam, or if inbound calls received by your account were unwanted spam. Is of type Boolean: true, false. Use true if the call was a spam call.
     *
     * @param bool $spam Specify if the call was a spam call. Use this to provide feedback on whether calls placed from your account were marked as spam, or if inbound calls received by your account were unwanted spam. Is of type Boolean: true, false. Use true if the call was a spam call.
     * @return $this Fluent Builder
     */
    public function setSpam(bool $spam): self
    {
        $this->options['spam'] = $spam;
        return $this;
    }

    /**
     * Specify the call score. This is of type integer. Use a range of 1-5 to indicate the call experience score, with the following mapping as a reference for rating the call [5: Excellent, 4: Good, 3 : Fair, 2 : Poor, 1: Bad].
     *
     * @param int $callScore Specify the call score. This is of type integer. Use a range of 1-5 to indicate the call experience score, with the following mapping as a reference for rating the call [5: Excellent, 4: Good, 3 : Fair, 2 : Poor, 1: Bad].
     * @return $this Fluent Builder
     */
    public function setCallScore(int $callScore): self
    {
        $this->options['callScore'] = $callScore;
        return $this;
    }

    /**
     * Specify any comments pertaining to the call. This of type string with a max limit of 100 characters. Twilio does not treat this field as PII, so don’t put any PII in here.
     *
     * @param string $comment Specify any comments pertaining to the call. This of type string with a max limit of 100 characters. Twilio does not treat this field as PII, so don’t put any PII in here.
     * @return $this Fluent Builder
     */
    public function setComment(string $comment): self
    {
        $this->options['comment'] = $comment;
        return $this;
    }

    /**
     * Associate this call with an incident or support ticket. This is of type string with a max limit of 100 characters. Twilio does not treat this field as PII, so don’t put any PII in here.
     *
     * @param string $incident Associate this call with an incident or support ticket. This is of type string with a max limit of 100 characters. Twilio does not treat this field as PII, so don’t put any PII in here.
     * @return $this Fluent Builder
     */
    public function setIncident(string $incident): self
    {
        $this->options['incident'] = $incident;
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
        return '[Twilio.Insights.V1.UpdateAnnotationOptions ' . $options . ']';
    }
}

