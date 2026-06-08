<?php

namespace Garest\FirebaseSender\Push;

class Push
{
    public ?string $title;

    public ?string $titleLocKey;

    /** @var string[]|null */
    public ?array $titleLocArgs;

    public ?string $body;

    public ?string $bodyLocKey;

    /** @var string[]|null */
    public ?array $bodyLocArgs;

    public ?string $image;

    /**
     * Constructor to initialize push notification data.
     *
     * @param string|null $title Set the notification title.
     * @param string|null $titleLocKey Set the localization key for the title.
     * @param array|null $titleLocArgs Set the localization arguments for the title.
     * @param string|null $body Set the notification body text.
     * @param string|null $bodyLocKey Set the localization key for the body.
     * @param array|null $bodyLocArgs Set the localization arguments for the body.
     * @param string|null $image Set a link to the image
     */
    public function __construct(
        ?string $title,
        ?string $titleLocKey,
        ?array $titleLocArgs,
        ?string $body,
        ?string $bodyLocKey,
        ?array $bodyLocArgs,
        ?string $image = null,
    ) {
        $this->title = $title;
        $this->titleLocKey = $titleLocKey;
        $this->titleLocArgs = $titleLocArgs;
        $this->body = $body;
        $this->bodyLocKey = $bodyLocKey;
        $this->bodyLocArgs = $bodyLocArgs;
        $this->image = $image;
    }

    /**
     * Set the notification title.
     *
     * @param string|null $title The title text.
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * Set the localization key for the title.
     *
     * @param string|null $titleLocKey The localization key.
     */
    public function setTitleLocKey(?string $titleLocKey): void
    {
        $this->titleLocKey = $titleLocKey;
    }

    /**
     * Set the localization arguments for the title.
     *
     * @param array|null $titleLocArgs Array of localization arguments.
     */
    public function setTitleLocArgs(?array $titleLocArgs): void
    {
        $this->titleLocArgs = $titleLocArgs;
    }

    /**
     * Set the notification body text.
     *
     * @param string|null $body The body text.
     */
    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    /**
     * Set the localization key for the body.
     *
     * @param string|null $bodyLocKey The localization key.
     */
    public function setBodyLocKey(?string $bodyLocKey): void
    {
        $this->bodyLocKey = $bodyLocKey;
    }

    /**
     * Set the localization arguments for the body.
     *
     * @param array|null $bodyLocArgs Array of localization arguments.
     */
    public function setBodyLocArgs(?array $bodyLocArgs): void
    {
        $this->bodyLocArgs = $bodyLocArgs;
    }

    /**
     *Set a link to the image
     *
     * @param string|null $url
     */
    public function setImage(?string $url): void
    {
        $this->image = $url;
    }
}
