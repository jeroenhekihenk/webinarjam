<?php
namespace Jandje\WebinarJam;

use Exception;
use Log;

class WebinarJam
{
    protected $jandje;

    public function __construct(JandjeWebinarJam $jandje)
    {
        $this->jandje = $jandje;
    }

    /**
     * Get all webinars on your account
     * @return string
     * @throws WebinarJamException
     */
    public function getWebinars()
    {
        $endpoint = 'webinars';
        $response = $this->callApi('getwebinars', $endpoint);
        return $response;
    }


    /**
     * Register the person to a webinar
     * @param $webinarId
     * @param $name
     * @param $email
     * @param $schedule
     * @return string
     * @throws WebinarJamException
     */
    public function registerToWebinar($webinarId, $name, $email, $schedule)
    {
        $endpoint = 'register';

        $data = [];
        if(!empty($webinarId)) {
            $data['webinar_id'] = $webinarId;
        }
        if(!empty($name)) {
            $data['name'] = $name;
        }
        if(!empty($email)) {
            $data['email'] = $email;
        }
        if(!empty($schedule)) {
            $data['schedule'] = $schedule;
        }

        $response = $this->callApi('registertowebinar', $endpoint, $data);
        return $response;
    }

    /**
     * Checks to see if an email address is subscribed to a list
     * Need to check the list exists first, because the response for non-existent list ID
     * and for a non-subscriber is the same
     * @param string $listId
     * @param string $emailAddress
     * @return bool
     * @throws MailchimpException
     */
    public function check($listId, $emailAddress)
    {
        $result = $this->checkStatus($listId, $emailAddress);
        if($result == 'subscribed' || $result == 'pending') {
            return true;
        }
        return false;
    }

    /**
     * Checks the status of a list subscriber
     * Possible statuses: 'subscribed', 'unsubscribed', 'cleaned', 'pending', or 'not found'
     * @param $listId
     * @param $emailAddress
     * @return string
     * @throws MailchimpException
     */
    public function checkStatus($listId, $emailAddress)
    {
        // Check the list exists
        if(!$this->checkListExists($listId)) {
            throw new MailchimpException('checkStatus called on a list that does not exist (' . $listId . ')');
        }
        // Check whether the list has the subscriber
        $id = md5(strtolower($emailAddress));
        $endpoint = "lists/{$listId}/members/{$id}";
        $response = $this->callApi('get', $endpoint);
        if (empty($response['status'])) {
            throw new MailchimpException('checkStatus return value did not contain status');
        }
        if ($response['status'] == 404) {
            $response['status'] = 'not found';
        }
        return $response['status'];
    }


    /**
     * @param $method
     * @param $endpoint
     * @param array $data = []
     * @return array $response
     * @throws MailchimpException
     */
    protected function callApi($method, $endpoint, $data = [])
    {
        try {
            $response = $this->jandje->$method($endpoint, $data);
        } catch (Exception $e) {
            throw new WebinarJamException('JandjeWebinarJam exception: ' . $e->getMessage());
        }
        if ($response === false) {
            throw new WebinarJamException('Error in JandjeWebinarJam - possible connectivity problem');
        }
        return $response;
    }
}