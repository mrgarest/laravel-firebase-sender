<?php

namespace MrGarest\FirebaseSender\Exceptions;


final class AccessTokenMissingException extends \Exception
{
    protected $message = 'Cannot get an OAuth2 access token';

}
