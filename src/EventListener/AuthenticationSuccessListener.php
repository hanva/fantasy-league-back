<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;


class AuthenticationSuccessListener
{
    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {

        $data = $event->getData();

        $currentTimestampMilliseconds = round(microtime(true) * 1000);

        $twoHoursInMilliseconds = 2 * 60 * 60 * 1000;

        $newTimestampMilliseconds = $currentTimestampMilliseconds + $twoHoursInMilliseconds;


        $data['expiration'] = $newTimestampMilliseconds;

        $event->setData($data);
    }
}

