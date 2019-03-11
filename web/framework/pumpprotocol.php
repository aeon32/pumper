<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.09.18
 * Time: 17:41
 */
require_once("monitoringinfo.php");
abstract class PumpMessageConsts
{
    public static $COMMAND_CHECK_REQUEST = 0x30;
    public static $NO_COMMAND_RESPONSE = 0x31; //nothing to do
    public static $GET_INFO_RESPONSE = 0x32;   //controller must return information
    public static $SEND_INFO_REQUEST = 0x33;   //controller return information about self
    public static $SWITCH_TO_MONITORING_MODE_RESPONSE = 0x34; //controller must switch for full monitoring mode response
    public static $COMMAND_CHECK_WITH_INFO_REQUEST = 0x35;   //check command with full monitoring info


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


}

;

/**
 *  Basic request from controlServer
 *  Format:
 *  struct BasicRequest
 *  {
 *    uint8_t messageType; //one of PumpMessageCodes
 *    char token[];        //tokenSize
 * }
 **/
class BasicRequest extends PumpMessageBase
{
    protected static $MAX_TOKEN_SIZE = 20;
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

    static public function deserialize($type, $data)
    {
        if (is_string($data) && strlen($data) <= BasicRequest::$MAX_TOKEN_SIZE)
            return new BasicRequest($type, $data);
        else
            return null;

    }

}

/**
 * Class SendMonitoringInfoRequest
 * Returns breef monitoring info from controller
 * Format:
 *  struct SendMonitoringInfoRequest
 *  {
 *    uint8_t     messageType; //one of PumpMessageCodes::$SWITCH_TO_MONITORING_MODE_RESPONSE
 *    char token[];        //tokenSize
 * }
 *
 */

class SendInfoRequest extends BasicRequest
{

    private $commandId;
    public $pumpingTable = NULL;

    public function __construct($type, $token, $commandId)
    {
        parent::__construct($type, $token);
        $this->commandId = $commandId;
    }

    public function getCommandId()
    {
        return $this->commandId;

    }

    static public function deserialize($type, $data)
    {
        if (!is_string($data))
            return null;


        $offset = 0;
        $len = strlen($data);
        $commandId = null;
        $token = null;

        $mailformed = false;
        if ($len > $offset + 4) {
            $commandId = unpack("N", substr($data, $offset, 4))[1];
        } else {
            $mailformed = true;
        };

        $offset += 4;

        if (!$mailformed && $len >= $offset + 1 && ($len - $offset) < BasicRequest::$MAX_TOKEN_SIZE) {
            $token = substr($data, $offset, $len - $offset);

        } else {

            $mailformed = true;
        };

        $res = null;
        if (!$mailformed) {
            $res = new  SendInfoRequest($type, $token, $commandId);

        };

        return $res;

    }

}

/**
 * Class SendMonitoringInfoRequest
 * Returns breef monitoring info from controller
 * Format:
 *  struct SendMonitoringInfoRequest
 *  {
 *    uint8_t     messageType; //one of PumpMessageCodes::$SWITCH_TO_MONITORING_MODE_RESPONSE
 *    uint32_t    pressure;    //current pressure
 *    bool is_working;         //is controller working
 *    uint8_t currentValve;    //current valve opened
 *    uint8_t currentStep;     //current step
 *    char token[];        //tokenSize
 * }
 *
 */
class SendMonitoringInfoRequest extends BasicRequest
{
    public $monitoringInfo = null;

    public function __construct($type, $monitoringInfo, $token)
    {
        parent::__construct($type, $token);
        $this->monitoringInfo = $monitoringInfo;
    }


    static public function deserialize($type, $data)
    {
        if (!is_string($data))
            return null;


        $offset = 0;
        $len = strlen($data);
        $monitoring_info = null;
        $token = null;

        $mailformed = false;
        if ($len > $offset + MonitoringInfo::getStructFMTSize()) {
            $monitoring_info = MonitoringInfo::deserialize( substr($data, $offset, MonitoringInfo::getStructFMTSize() ));
        } else {
            $mailformed = true;
        };

        $offset += MonitoringInfo::getStructFMTSize();

        if (!$mailformed && $len >= $offset + 1 && ($len - $offset) < BasicRequest::$MAX_TOKEN_SIZE) {
            $token = substr($data, $offset, $len - $offset);

        } else {

            $mailformed = true;
        };

        $res = null;
        if (!$mailformed) {
            $res = new  SendMonitoringInfoRequest($type, $monitoring_info, $token);

        };

        return $res;


    }
}


class BasicResponse extends PumpMessageBase
{

    public function __construct($type)
    {
        parent::__construct($type);
    }


}

/**
 * Controller must to the command
 */
class PendingCommandResponse extends BasicResponse
{
    public $command_id;

    public function __construct($type, $command_id)
    {
        parent::__construct($type);
        $this->command_id = $command_id;

    }

    public function serialize()
    {
        return pack("CN", $this->getType(), $this->command_id);
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
    if (is_string($data) && $len >= $offset + 1) {
        $messageType = ord($data[0]);
    } else
        $mailfomed = true;

    $offset += 1;
    if (!$mailfomed) {
        switch ($messageType) {
            case  PumpMessageConsts::$COMMAND_CHECK_REQUEST :
                $res = BasicRequest::deserialize($messageType, substr($data, $offset, $len - $offset));
                break;

            case PumpMessageConsts::$SEND_INFO_REQUEST:
                $res = SendInfoRequest::deserialize($messageType, substr($data, $offset, $len - $offset));
                break;

            case PumpMessageConsts::$COMMAND_CHECK_WITH_INFO_REQUEST:
                $res = SendMonitoringInfoRequest::deserialize($messageType, substr($data, $offset, $len - $offset));
                break;


        };


    };
    return $res;


};

?>