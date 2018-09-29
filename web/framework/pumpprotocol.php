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
    public static $NO_COMMAND_RESPONSE = 0x31;


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
 *  Request to controlserver to retrieve pending command for execution
 *  Format:
 *  struct PumpCommandCheckRequest
 *  {
 *    uint8_t messageType; //one of PumpMessageCodes
 *    char tokenSize;      //tokenSize
**/

class PumpCommandCheckRequest extends  PumpMessageBase
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
        if (is_string($data) && strlen($data) > PumpCommandCheckRequest::$MAX_TOKEN_SIZE )
            return new PumpCommandCheckRequest($type, $data);
        else
            return null;

    }

}

/**
 * @param $data protocol-packed binary string
 *
 *
 * Protocol description:
 * struct  PumpMessageBase
 * {
 *   uint8_t messageType; //one of PumpMessageCodes
 *   //optional:
 *   char data[];          //command-specific data
 *
 * };
 */

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
                $res = new PumpCommandCheckRequest($messageType, substr($data, $offset, $len - $offset));

        };


    };
    return $res;

    /*
    $offset += 2;
    //extract token
    if (!$mailfomed && $len >= ($offset + $tokenSize)  )
    {
        $clientToken = substr($data, $offset, $tokenSize);

    } else
        $mailfomed = true;



    $offset += $tokenSize;

    //extract data
    $mailfomed = $mailfomed || ( $len > $offset && $len < ($offset + 2));

    if (!$mailfomed && $len >= $offset + 2)
        $dataSize = unpack("n", $data, $offset);

    $offset +=2;
    if($dataSize > 0)
        $data = substr($data, $offset, $dataSize);

    */





};

?>