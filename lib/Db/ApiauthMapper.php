<?php
namespace OCA\AaoChat\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception as DbException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\ISession;
use OCA\AaoChat\Db\Apiauth;

class ApiauthMapper extends QBMapper {

    private $session;
    

    public function __construct(IDBConnection $db, ISession $session) {
        parent::__construct($db, 'aaochat_api_auth', Apiauth::class);

        $this->session = $session;
    }


    public function getApiAuthByToken(string $authToken) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->getTableName())
           ->where(
            $qb->expr()->eq('auth_token', $qb->createNamedParameter($authToken))
           );
        
        return $this->findEntity($qb);
    }

    public function getApiAuthByUsername(string $userName) {
        $qb = $this->db->getQueryBuilder();

        $select = $qb->select('*')
           ->from($this->getTableName())
           ->where(
            $qb->expr()->eq('user_id', $qb->createNamedParameter($userName))
           );
        $cursor = $select->execute(); 
        return $row = $cursor->fetch();
        //return $this->findEntity($qb);
    }

    public function isTokenExists(string $authToken )
    {  
        $qb = $this->db->getQueryBuilder();

        $select = $qb->select('auth_base')
           ->from($this->getTableName()) 
           ->where( $qb->expr()->eq('auth_token', $qb->createNamedParameter($authToken)) ) 
           ;
        $cursor = $select->execute(); 
        $row = $cursor->fetch();

        $isTokenExists = false;
        if(isset($row['auth_base'])) {
          $isTokenExists = true;
        }
        return $isTokenExists;
    }

    public function isApiUserExists(string $userName )
    {  
        $qb = $this->db->getQueryBuilder();

        $select = $qb->select('auth_base')
           ->from($this->getTableName()) 
           ->where( $qb->expr()->eq('user_id', $qb->createNamedParameter($userName)) ) 
           ;
        $cursor = $select->execute(); 
        $row = $cursor->fetch();

        $isApiUserExists = false;
        if(isset($row['auth_base'])) {
          $isApiUserExists = true;
        }
        return $isApiUserExists;
    }

    public function updateApiAuth($userName, $authBase, $authToken) { 
        $qb = $this->db->getQueryBuilder();

        $response = $qb->update($this->getTableName())
          ->set('auth_base', $qb->createNamedParameter($authBase))
          ->set('auth_token', $qb->createNamedParameter($authToken))
          ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userName)))
          ->execute();
       return $response;
    }

    public function updateAuthKey($userName, $authKey) { 
        $qb = $this->db->getQueryBuilder();

        $response = $qb->update($this->getTableName())
          ->set('auth_key', $qb->createNamedParameter($authKey))
          ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userName)))
          ->execute();
       return $response;
    }
    
}