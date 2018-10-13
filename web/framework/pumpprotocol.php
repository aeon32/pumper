<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.09.18
 * Time: 17:41
 */

abstract class PumpMessageConsts
{
    public static $COMMAND_CHECK_REQUEST = 0x30;
    public static $NO_COMMAND_RESPONSE = 0x31; //nothing to do
    public static $GET_INFO_RESPONSE = 0x32;   //controller must return information


}



class PumpMessageBase
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function serialize()
    {
        return chr($this->type);

    }


};

/**
 *  Basic request from controlServer
 *  Format:
 *  struct BasicRequest
 *  {
 *    uint8_t messageType; //one of PumpMessageCodes
 *    char token[];      //tokenSize
 * }
**/

class BasicRequest extends  PumpMessageBase
{
    private static $MAX_TOKEN_SIZE = 20;
    protected $token;

    public function __construct($type, $token)
    {
        parent::__construct($type);
        $this->token = $token;


    }

    public function getToken()
    {
        return $this->token;

    }

    static public  function deserialize($type, $data)
    {
        if (is_string($data) && strlen($data) <= BasicRequest::$MAX_TOKEN_SIZE )
            return new BasicRequest($type, $data);
        else
            return null;

    }

}



class BasicResponse extends  PumpMessageBase
{

    public function __construct($type)
    {
        parent::__construct($type);
    }


}


function pumpProtocolMessageFromBytes($data)
{
    $res = null;
    $messageType = null;
    $clientToken = null;
    $len = strlen($data);
    $mailfomed = false;
    $tokenSize = 0;
    $dataSize = 0;
    $offset = 0;

    //extract messageType
    $t = is_string($data);
    if (is_string($data) && $len >= $offset + 1)
    {
        $messageType = ord($data[0]);
    } else
        $mailfomed = true;

    $offset += 1;
    if (!$mailfomed)
    {
        switch ($messageType)
        {
            case  PumpMessageConsts::$COMMAND_CHECK_REQUEST :
                $res = BasicRequest::deserialize($messageType, substr($data, $offset, $len - $offset));

        };


    };
    return $res;






};

?>