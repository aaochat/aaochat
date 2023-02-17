<?php
namespace OCA\AaoChat\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class Apiauth extends Entity implements JsonSerializable {

    protected $userId;
    protected $authBase;
    protected $authToken;
    protected $timestamp;
    
    /**
    *   /lib/public/AppFramework/Db/Entity.php#118
    *   https://www.php.net/manual/en/function.settype.php
    *   Possibles values of type are:
    *   "boolean" or "bool"
    *   "integer" or "int"
    *   "float" or "double"
    *   "string"
    *   "array"
    *   "object"
    *   "null"
    */
    public function __construct() {
        $this->addType('id','integer');
        $this->addType('user_id','string');
        $this->addType('auth_base','string');
        $this->addType('auth_token','string');
        $this->addType('auth_key','string');
        $this->addType('timestamp','integer');
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'auth_base' => $this->authBase,
            'auth_token' => $this->authToken,
            'auth_key' => $this->authToken,
            'timestamp' => $this->timestamp
        ];
    }
}
