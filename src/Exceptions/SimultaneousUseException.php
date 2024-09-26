<?php

namespace MrGarest\FirebaseSender\Exceptions;


final class SimultaneousUseException extends \Exception
{
    protected $message = 'You cannot use `token` and `topic` at the same time.';

}
