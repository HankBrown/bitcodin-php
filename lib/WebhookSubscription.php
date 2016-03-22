<?php
/**
 * Created by PhpStorm.
 * User: akopper
 * Date: 09.03.16
 * Time: 10:15
 */

namespace bitcodin;


class WebhookSubscription extends ApiResource
{

    const URL_SUBSCRIPTION = '/notifications/webhook/{webhook}/subscription';
    const URL_EVENT_TRIGGER = '/notifications/webhook/{webhook}/subscription/{subscription}/event';

    /**
     * @var Webhook the associated webhook for this event
     */
    public $webhook;

    /**
     * @var string subscription id
     */
    public $id;

    /**
     * @var string callback URL
     */
    public $url;

    /**
     * @param $webhookName
     * @param $susbcriptionId
     * @return WebhookSubscription
     */
    public static function find($webhookName, $susbcriptionId)
    {
        $url = str_replace("{webhook}", $webhookName, self::URL_SUBSCRIPTION);
        $url = $url . "/" . $susbcriptionId;
        $response = self::_getRequest($url, 200);
        return new self(json_decode($response->getBody()->getContents()));
    }

    /**
     * @param Webhook $webhook
     * @param string $callbackUrl
     * @return WebhookSubscription resulting subscription object
     */
    public static function create($webhook, $callbackUrl) {
        $url = str_replace("{webhook}", $webhook->name, self::URL_SUBSCRIPTION);
        $response = self::_postRequest($url, self::createRequestBody($callbackUrl), 201);
        return new self(json_decode($response->getBody()->getContents()));
    }

    /**
     * delete all subscriptions for this webhook
     */
    public function unsubscribe()
    {
        $url = str_replace("{webhook}", $this->webhook->name, self::URL_SUBSCRIPTION);
        $url = $url . '/' . $this->id;
        self::_deleteRequest($url, 200);
    }

    /**
     * @param string $webhookName
     * @return WebhookSubscription[]
     */
    public static function listSubscriptions($webhookName)
    {
        $response = self::_getRequest(str_replace("{webhook}", $webhookName, self::URL_SUBSCRIPTION), 200);
        $subscriptionsJson = json_decode($response->getBody()->getContents());
        $subscriptions = array();
        foreach($subscriptionsJson as $subJson) {
            $subscriptions[] = new WebhookSubscription($subJson);
        }
        return $subscriptions;
    }

    /**
     * @return mixed
     */
    public function listEvents()
    {
        $url = str_replace('{webhook}', $this->webhook->name, self::URL_EVENT_TRIGGER);
        $url = str_replace('{subscription}', $this->id, $url);
        $response = self::_getRequest($url, 200);
        return json_decode($response->getBody()->getContents());
    }

    /**
     * @param string $callbackURL
     * @return array
     * @internal param string $webhookName
     */
    private static function createRequestBody($callbackURL)
    {
        $requestValues = array();
        $requestValues['url'] = $callbackURL;
        return json_encode($requestValues);
    }


}

