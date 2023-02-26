<?php

namespace Bookme\Inc\Payment\Stripe\Lib;

abstract class Webhook
{
    const DEFAULT_TOLERANCE = 300;

    /**
     * Returns an Event instance using the provided JSON payload. Throws a
     * \UnexpectedValueException if the payload is not valid JSON, and a
     * \Bookme\Inc\Payment\Stripe\Lib\SignatureVerificationException if the signature verification
     * fails for any reason.
     *
     * @param string $payload the payload sent by Stripe.
     * @param string $sigHeader the contents of the signature header sent by
     *  Stripe.
     * @param string $secret secret used to generate the signature.
     * @param int $tolerance maximum difference allowed between the header's
     *  timestamp and the current time
     * @return \Bookme\Inc\Payment\Stripe\Lib\Event the Event instance
     * @throws \UnexpectedValueException if the payload is not valid JSON,
     * @throws \Bookme\Inc\Payment\Stripe\Lib\Error\SignatureVerification if the verification fails.
     */
    public static function constructEvent($payload, $sigHeader, $secret, $tolerance = self::DEFAULT_TOLERANCE)
    {
        $data = json_decode($payload, true);
        $jsonError = json_last_error();
        if ($data === null && $jsonError !== JSON_ERROR_NONE) {
            $msg = "Invalid payload: $payload "
              . "(json_last_error() was $jsonError)";
            throw new \UnexpectedValueException($msg);
        }
        $event = Event::constructFrom($data);

        WebhookSignature::verifyHeader($payload, $sigHeader, $secret, $tolerance);

        return $event;
    }
}