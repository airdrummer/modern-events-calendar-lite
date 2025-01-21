<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Video
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Video\V1;

use Twilio\Options;
use Twilio\Values;

abstract class CompositionOptions
{
    /**
     * @param array $videoLayout An object that describes the video layout of the composition in terms of regions. See [Specifying Video Layouts](https://www.twilio.com/docs/video/api/compositions-resource#specifying-video-layouts) for more info. Please, be aware that either video_layout or audio_sources have to be provided to get a valid creation request
     * @param string[] $audioSources An array of track names from the same group room to merge into the new composition. Can include zero or more track names. The new composition includes all audio sources specified in `audio_sources` except for those specified in `audio_sources_excluded`. The track names in this parameter can include an asterisk as a wild card character, which will match zero or more characters in a track name. For example, `student*` includes `student` as well as `studentTeam`. Please, be aware that either video_layout or audio_sources have to be provided to get a valid creation request
     * @param string[] $audioSourcesExcluded An array of track names to exclude. The new composition includes all audio sources specified in `audio_sources` except for those specified in `audio_sources_excluded`. The track names in this parameter can include an asterisk as a wild card character, which will match zero or more characters in a track name. For example, `student*` excludes `student` as well as `studentTeam`. This parameter can also be empty.
     * @param string $resolution A string that describes the columns (width) and rows (height) of the generated composed video in pixels. Defaults to `640x480`.  The string's format is `{width}x{height}` where:   * 16 <= `{width}` <= 1280 * 16 <= `{height}` <= 1280 * `{width}` * `{height}` <= 921,600  Typical values are:   * HD = `1280x720` * PAL = `1024x576` * VGA = `640x480` * CIF = `320x240`  Note that the `resolution` imposes an aspect ratio to the resulting composition. When the original video tracks are constrained by the aspect ratio, they are scaled to fit. See [Specifying Video Layouts](https://www.twilio.com/docs/video/api/compositions-resource#specifying-video-layouts) for more info.
     * @param string $format
     * @param string $statusCallback The URL we should call using the `status_callback_method` to send status information to your application on every composition event. If not provided, status callback events will not be dispatched.
     * @param string $statusCallbackMethod The HTTP method we should use to call `status_callback`. Can be: `POST` or `GET` and the default is `POST`.
     * @param bool $trim Whether to clip the intervals where there is no active media in the composition. The default is `true`. Compositions with `trim` enabled are shorter when the Room is created and no Participant joins for a while as well as if all the Participants leave the room and join later, because those gaps will be removed. See [Specifying Video Layouts](https://www.twilio.com/docs/video/api/compositions-resource#specifying-video-layouts) for more info.
     * @return CreateCompositionOptions Options builder
     */
    public static function create(
        
        array $videoLayout = Values::ARRAY_NONE,
        array $audioSources = Values::ARRAY_NONE,
        array $audioSourcesExcluded = Values::ARRAY_NONE,
        string $resolution = Values::NONE,
        string $format = Values::NONE,
        string $statusCallback = Values::NONE,
        string $statusCallbackMethod = Values::NONE,
        bool $trim = Values::BOOL_NONE

    ): CreateCompositionOptions
    {
        return new CreateCompositionOptions(
            $videoLayout,
            $audioSources,
            $audioSourcesExcluded,
            $resolution,
            $format,
            $statusCallback,
            $statusCallbackMethod,
            $trim
        );
    }



    /**
     * @param string $status Read only Composition resources with this status. Can be: `enqueued`, `processing`, `completed`, `deleted`, or `failed`.
     * @param \DateTime $dateCreatedAfter Read only Composition resources created on or after this [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) date-time with time zone.
     * @param \DateTime $dateCreatedBefore Read only Composition resources created before this ISO 8601 date-time with time zone.
     * @param string $roomSid Read only Composition resources with this Room SID.
     * @return ReadCompositionOptions Options builder
     */
    public static function read(
        
        string $status = Values::NONE,
        \DateTime $dateCreatedAfter = null,
        \DateTime $dateCreatedBefore = null,
        string $roomSid = Values::NONE

    ): ReadCompositionOptions
    {
        return new ReadCompositionOptions(
            $status,
            $dateCreatedAfter,
            $dateCreatedBefore,
            $roomSid
        );
    }

}

class CreateCompositionOptions extends Options
    {
    /**
     * @param array $videoLayout An object that describes the video layout of the composition in terms of regions. See [Specifying Video Layouts](https://www.twilio.com/docs/video/api/compositions-resource#specifying-video-layouts) for more info. Please, be aware that either video_layout or audio_sources have to be provided to get a valid creation request
     * @param string[] $audioSources An array of track names from the same group room to merge into the new composition. Can include zero or more track names. The new composition includes all audio sources specified in `audio_sources` except for those specified in `audio_sources_excluded`. The track names in this parameter can include an asterisk as a wild card character, which will match zero or more characters in a track name. For example, `student*` includes `student` as well as `studentTeam`. Please, be aware that either video_layout or audio_sources have to be provided to get a valid creation request
     * @param string[] $audioSourcesExcluded An array of track names to exclude. The new composition includes all audio sources specified in `audio_sources` except for those specified in `audio_sources_excluded`. The track names in this parameter can include an asterisk as a wild card character, which will match zero or more characters in a track name. For example, `student*` excludes `student` as well as `studentTeam`. This parameter can also be empty.
     * @param string $resolution A string that describes the columns (width) and rows (height) of the generated composed video in pixels. Defaults to `640x480`.  The string's format is `{width}x{height}` where:   * 16 <= `{width}` <= 1280 * 16 <= `{height}` <= 1280 * `{width}` * `{height}` <= 921,600  Typical values are:   * HD = `1280x720` * PAL = `1024x576` * VGA = `640x480` * CIF = `320x240`  Note that the `resolution` imposes an aspect ratio to the resulting composition. When the original video tracks are constrained by the aspect ratio, they are scaled to fit. See [Specifying Video Layouts](https://www.twilio.com/docs/video/api/compositions-resource#specifying-video-layouts) for more info.
     * @param string $format
     * @param string $statusCallback The URL we should call using the `status_callback_method` to send status information to your application on every composition event. If not provided, status callback events will not be dispatched.
     * @param string $statusCallbackMethod The HTTP method we should use to call `status_callback`. Can be: `POST` or `GET` and the default is `POST`.
     * @param bool $trim Whether to clip the intervals where there is no active media in the composition. The default is `true`. Compositions with `trim` enabled are shorter when the Room is created and no Participant joins for a while as well as if all the Participants leave the room and join later, because those gaps will be removed. See [Specifying Video Layouts](https://www.twilio.com/docs/video/api/compositions-resource#specifying-video-layouts) for more info.
     */
    public function __construct(
        
        array $videoLayout = Values::ARRAY_NONE,
        array $audioSources = Values::ARRAY_NONE,
        array $audioSourcesExcluded = Values::ARRAY_NONE,
        string $resolution = Values::NONE,
        string $format = Values::NONE,
        string $statusCallback = Values::NONE,
        string $statusCallbackMethod = Values::NONE,
        bool $trim = Values::BOOL_NONE

    ) {
        $this->options['videoLayout'] = $videoLayout;
        $this->options['audioSources'] = $audioSources;
        $this->options['audioSourcesExcluded'] = $audioSourcesExcluded;
        $this->options['resolution'] = $resolution;
        $this->options['format'] = $format;
        $this->options['statusCallback'] = $statusCallback;
        $this->options['statusCallbackMethod'] = $statusCallbackMethod;
        $this->options['trim'] = $trim;
    }

    /**
     * An object that describes the video layout of the composition in terms of regions. See [Specifying Video Layouts](https://www.twilio.com/docs/video/api/compositions-resource#specifying-video-layouts) for more info. Please, be aware that either video_layout or audio_sources have to be provided to get a valid creation request
     *
     * @param array $videoLayout An object that describes the video layout of the composition in terms of regions. See [Specifying Video Layouts](https://www.twilio.com/docs/video/api/compositions-resource#specifying-video-layouts) for more info. Please, be aware that either video_layout or audio_sources have to be provided to get a valid creation request
     * @return $this Fluent Builder
     */
    public function setVideoLayout(array $videoLayout): self
    {
        $this->options['videoLayout'] = $videoLayout;
        return $this;
    }

    /**
     * An array of track names from the same group room to merge into the new composition. Can include zero or more track names. The new composition includes all audio sources specified in `audio_sources` except for those specified in `audio_sources_excluded`. The track names in this parameter can include an asterisk as a wild card character, which will match zero or more characters in a track name. For example, `student*` includes `student` as well as `studentTeam`. Please, be aware that either video_layout or audio_sources have to be provided to get a valid creation request
     *
     * @param string[] $audioSources An array of track names from the same group room to merge into the new composition. Can include zero or more track names. The new composition includes all audio sources specified in `audio_sources` except for those specified in `audio_sources_excluded`. The track names in this parameter can include an asterisk as a wild card character, which will match zero or more characters in a track name. For example, `student*` includes `student` as well as `studentTeam`. Please, be aware that either video_layout or audio_sources have to be provided to get a valid creation request
     * @return $this Fluent Builder
     */
    public function setAudioSources(array $audioSources): self
    {
        $this->options['audioSources'] = $audioSources;
        return $this;
    }

    /**
     * An array of track names to exclude. The new composition includes all audio sources specified in `audio_sources` except for those specified in `audio_sources_excluded`. The track names in this parameter can include an asterisk as a wild card character, which will match zero or more characters in a track name. For example, `student*` excludes `student` as well as `studentTeam`. This parameter can also be empty.
     *
     * @param string[] $audioSourcesExcluded An array of track names to exclude. The new composition includes all audio sources specified in `audio_sources` except for those specified in `audio_sources_excluded`. The track names in this parameter can include an asterisk as a wild card character, which will match zero or more characters in a track name. For example, `student*` excludes `student` as well as `studentTeam`. This parameter can also be empty.
     * @return $this Fluent Builder
     */
    public function setAudioSourcesExcluded(array $audioSourcesExcluded): self
    {
        $this->options['audioSourcesExcluded'] = $audioSourcesExcluded;
        return $this;
    }

    /**
     * A string that describes the columns (width) and rows (height) of the generated composed video in pixels. Defaults to `640x480`.  The string's format is `{width}x{height}` where:   * 16 <= `{width}` <= 1280 * 16 <= `{height}` <= 1280 * `{width}` * `{height}` <= 921,600  Typical values are:   * HD = `1280x720` * PAL = `1024x576` * VGA = `640x480` * CIF = `320x240`  Note that the `resolution` imposes an aspect ratio to the resulting composition. When the original video tracks are constrained by the aspect ratio, they are scaled to fit. See [Specifying Video Layouts](https://www.twilio.com/docs/video/api/compositions-resource#specifying-video-layouts) for more info.
     *
     * @param string $resolution A string that describes the columns (width) and rows (height) of the generated composed video in pixels. Defaults to `640x480`.  The string's format is `{width}x{height}` where:   * 16 <= `{width}` <= 1280 * 16 <= `{height}` <= 1280 * `{width}` * `{height}` <= 921,600  Typical values are:   * HD = `1280x720` * PAL = `1024x576` * VGA = `640x480` * CIF = `320x240`  Note that the `resolution` imposes an aspect ratio to the resulting composition. When the original video tracks are constrained by the aspect ratio, they are scaled to fit. See [Specifying Video Layouts](https://www.twilio.com/docs/video/api/compositions-resource#specifying-video-layouts) for more info.
     * @return $this Fluent Builder
     */
    public function setResolution(string $resolution): self
    {
        $this->options['resolution'] = $resolution;
        return $this;
    }

    /**
     * @param string $format
     * @return $this Fluent Builder
     */
    public function setFormat(string $format): self
    {
        $this->options['format'] = $format;
        return $this;
    }

    /**
     * The URL we should call using the `status_callback_method` to send status information to your application on every composition event. If not provided, status callback events will not be dispatched.
     *
     * @param string $statusCallback The URL we should call using the `status_callback_method` to send status information to your application on every composition event. If not provided, status callback events will not be dispatched.
     * @return $this Fluent Builder
     */
    public function setStatusCallback(string $statusCallback): self
    {
        $this->options['statusCallback'] = $statusCallback;
        return $this;
    }

    /**
     * The HTTP method we should use to call `status_callback`. Can be: `POST` or `GET` and the default is `POST`.
     *
     * @param string $statusCallbackMethod The HTTP method we should use to call `status_callback`. Can be: `POST` or `GET` and the default is `POST`.
     * @return $this Fluent Builder
     */
    public function setStatusCallbackMethod(string $statusCallbackMethod): self
    {
        $this->options['statusCallbackMethod'] = $statusCallbackMethod;
        return $this;
    }

    /**
     * Whether to clip the intervals where there is no active media in the composition. The default is `true`. Compositions with `trim` enabled are shorter when the Room is created and no Participant joins for a while as well as if all the Participants leave the room and join later, because those gaps will be removed. See [Specifying Video Layouts](https://www.twilio.com/docs/video/api/compositions-resource#specifying-video-layouts) for more info.
     *
     * @param bool $trim Whether to clip the intervals where there is no active media in the composition. The default is `true`. Compositions with `trim` enabled are shorter when the Room is created and no Participant joins for a while as well as if all the Participants leave the room and join later, because those gaps will be removed. See [Specifying Video Layouts](https://www.twilio.com/docs/video/api/compositions-resource#specifying-video-layouts) for more info.
     * @return $this Fluent Builder
     */
    public function setTrim(bool $trim): self
    {
        $this->options['trim'] = $trim;
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
        return '[Twilio.Video.V1.CreateCompositionOptions ' . $options . ']';
    }
}



class ReadCompositionOptions extends Options
    {
    /**
     * @param string $status Read only Composition resources with this status. Can be: `enqueued`, `processing`, `completed`, `deleted`, or `failed`.
     * @param \DateTime $dateCreatedAfter Read only Composition resources created on or after this [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) date-time with time zone.
     * @param \DateTime $dateCreatedBefore Read only Composition resources created before this ISO 8601 date-time with time zone.
     * @param string $roomSid Read only Composition resources with this Room SID.
     */
    public function __construct(
        
        string $status = Values::NONE,
        \DateTime $dateCreatedAfter = null,
        \DateTime $dateCreatedBefore = null,
        string $roomSid = Values::NONE

    ) {
        $this->options['status'] = $status;
        $this->options['dateCreatedAfter'] = $dateCreatedAfter;
        $this->options['dateCreatedBefore'] = $dateCreatedBefore;
        $this->options['roomSid'] = $roomSid;
    }

    /**
     * Read only Composition resources with this status. Can be: `enqueued`, `processing`, `completed`, `deleted`, or `failed`.
     *
     * @param string $status Read only Composition resources with this status. Can be: `enqueued`, `processing`, `completed`, `deleted`, or `failed`.
     * @return $this Fluent Builder
     */
    public function setStatus(string $status): self
    {
        $this->options['status'] = $status;
        return $this;
    }

    /**
     * Read only Composition resources created on or after this [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) date-time with time zone.
     *
     * @param \DateTime $dateCreatedAfter Read only Composition resources created on or after this [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) date-time with time zone.
     * @return $this Fluent Builder
     */
    public function setDateCreatedAfter(\DateTime $dateCreatedAfter): self
    {
        $this->options['dateCreatedAfter'] = $dateCreatedAfter;
        return $this;
    }

    /**
     * Read only Composition resources created before this ISO 8601 date-time with time zone.
     *
     * @param \DateTime $dateCreatedBefore Read only Composition resources created before this ISO 8601 date-time with time zone.
     * @return $this Fluent Builder
     */
    public function setDateCreatedBefore(\DateTime $dateCreatedBefore): self
    {
        $this->options['dateCreatedBefore'] = $dateCreatedBefore;
        return $this;
    }

    /**
     * Read only Composition resources with this Room SID.
     *
     * @param string $roomSid Read only Composition resources with this Room SID.
     * @return $this Fluent Builder
     */
    public function setRoomSid(string $roomSid): self
    {
        $this->options['roomSid'] = $roomSid;
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
        return '[Twilio.Video.V1.ReadCompositionOptions ' . $options . ']';
    }
}

