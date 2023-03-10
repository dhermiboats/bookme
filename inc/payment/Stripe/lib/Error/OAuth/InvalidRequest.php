<?php

namespace Bookme\Inc\Payment\Stripe\Lib\Error\OAuth;

/**
 * InvalidRequest is raised when a code, refresh token, or grant type
 * parameter is not provided, but was required.
 */
class InvalidRequest extends OAuthBase
{
}
