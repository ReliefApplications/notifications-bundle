<?php

namespace RA\NotificationsBundle\Model\Notification;


class NotificationBody
{
    const PAYLOAD_ARRAY_ANDROID  = 0;
    const PAYLOAD_JSON_IOS       = 1;

    /**
     *  @var String
     *  Title of the notification
     */
    private $title;

    /**
     *  @var String
     *  Body of the notification
     */
    private $body;

    /**
     * @var string $androidChannelId
     */
    private $androidChannelId;

    /**
     *  @var Integer
     *  Badge number to display on the app
     */
    private $badge;

    /**
     *  @var Integer
     *  Id of the notification (to distinguish them)
     */
    private $uniqId;

    /**
     *  @var Array
     *  Color of the led for android
     */
    private $ledColor;

    /**
     *  @var String
     *  Path to the large icon
     */
    private $image;

    /**
     *  @var String
     *  Category of the notification, used for custom type coded on app side
     */
    private $category;

    /**
     *  @var Array
     *  List of android actions
     */
    private $actions;

    /**
     *  @var Array
     *  List of fields to add to the notification
     */
    private $additionalFields;

    public function __construct()
    {
        $this->title              = "";
        $this->body               = "";
        $this->androidChannelId   = "";
        $this->badge              = -1;
        $this->uniqId             = -1;
        $this->ledColor           = [];
        $this->image              = "";
        $this->imageType          = "";
        $this->category           = "";
        $this->actions            = [];
        $this->additionalFields   = [];
    }

    private function getiOSPayload()
    {
        $payload = array(
                "aps" => array(
                    "alert" => array(
                        "title" => $this->getTitle(),
                        "body"  => $this->getBody(),
                    ),
                )
        );
        if($this->getBadge()){
            $payload["aps"]["badge"] = $this->getBadge();
        }
        if($this->getCategory()){
            $payload["aps"]["category"] = $this->getCategory();
        }
        if($this->getAdditionalFields()){
            $additionalFields = $this->getAdditionalFields();
            foreach($additionalFields as $additionalField){
                if(array_key_exists("key", $additionalField) && array_key_exists("value", $additionalField)){
                    $payload["aps"][$additionalField["key"]] = $additionalField["value"];
                }
            }
        }

        return json_encode($payload);
    }

    /**
     * Get the value of Title
     *
     * @return String
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the value of Title
     *
     * @param String title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of Body
     *
     * @return String
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set the value of Body
     *
     * @param String body
     *
     * @return self
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return string
     */
    public function getAndroidChannelId(): string
    {
        return $this->androidChannelId;
    }

    /**
     * @param string $androidChannelId
     */
    public function setAndroidChannelId(string $androidChannelId)
    {
        $this->androidChannelId = $androidChannelId;
    }

    /**
     * Get the value of Badge
     *
     * @return Integer
     */
    public function getBadge()
    {
        return $this->badge;
    }

    /**
     * Set the value of Badge
     *
     * @param Integer badge
     *
     * @return self
     */
    public function setBadge($badge)
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * Get the value of $uniqId
     *
     * @return Integer
     */
    public function getUniqId()
    {
        return $this->uniqId;
    }

    /**
     * Set the value of uniqId
     *
     * @param Integer uniqId
     *
     * @return self
     */
    public function setUniqId($uniqId)
    {
        $this->uniqId = $uniqId;

        return $this;
    }

    /**
     * Get the value of Led Color
     *
     * @return Array
     */
    public function getLedColor()
    {
        return $this->ledColor;
    }

    /**
     * Set the value of Led Color
     *
     * @param Array ledColor
     *
     * @return self
     */
    public function setLedColor($ledColor)
    {
        $this->ledColor = $ledColor;

        return $this;
    }

    /**
     * Get the value of Image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the value of Image
     *
     * @param string image
     *
     * @return self
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get the value of imageType
     *
     * @return string
     */
    public function getImageType()
    {
        return $this->imageType;
    }

    /**
     * Set the value of imageType
     *
     * @param string imageType
     *
     * @return self
     */
    public function setImageType($imageType)
    {
        $this->imageType = $imageType;

        return $this;
    }

    /**
     * Get the value of Category
     *
     * @return String
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set the value of Category
     *
     * @param String category
     *
     * @return self
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get the value of Actions
     *
     * @return Array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Add an action
     *
     * @return self
     */
    public function addAction($action)
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * Set the value of Actions
     *
     * @param Array actions
     *
     * @return self
     */
    public function setActions($actions)
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * Get the value of additionalFields
     *
     * @return Array
     */
    public function getAdditionalFields()
    {
        return $this->additionalFields;
    }

    /**
     * Add a field
     *
     * @return self
     */
    public function addAdditionalField($additionalField)
    {
        $this->additionalFields[] = $additionalField;

        return $this;
    }

    /**
     * Set the value of additionalFields
     *
     * @param Array additionalFields
     *
     * @return self
     */
    public function setAdditionalFields($additionalFields)
    {
        $this->additionalFields = $additionalFields;

        return $this;
    }
}
