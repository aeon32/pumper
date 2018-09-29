<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.09.18
 * Time: 17:41
 */

abstract class PumpMessageConsts
{
    public static $PUMP_COMMAND_CHECK = 0x30;


}



abstract class PumpMessageBase
{
    protected $type;
    protected $token;

    public function __construct($type, $token)
    {
        $this->type = $type;
        $this->token = $token;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getToken()
    {
        return $this->token;
    }
};


class PumpCommandCheckRequest extends  PumpMessageBase
{
    public function __construct ($type, $token)
    {
        parent::__construct($type, $token);


    }

}

/**
 * @param $data protocol-packed binary string
 *
 *
 * Protocol description:
 * struct PumpMessage
 * {
 *   uint8_t messageType; //one of PumpMessageCodes
 *   uint8_t tokenSize;             // tokenSize
 *   char clientToken [tokenSize];  // identifier of session
 *   //optional
 *   uint16_t dataSize              // dataSize
 *   char data[dataSize]            // data
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
    if (is_string($data) && $len >= $offset + 2)
    {
        $messageType = ord($data[0]);
        $tokenSize = ord($data[1]);
    } else
        $mailfomed = true;

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

    if (!$mailfomed)
    {
        switch ($messageType)
        {
            case  PumpMessageConsts::$PUMP_COMMAND_CHECK:
                return new PumpCommandCheckRequest($messageType, $clientToken);

        };


    };


    return $res;


};

/**
 * @param $decoded
 * returns token
 */

function pumpProtocolEchoTest($decoded)
{
    $res = null;
    $message = pumpProtocolMessageFromBytes($decoded);
    if ($message)
    {
        $res = $message->getToken();
    }
    return $res;
};

?>