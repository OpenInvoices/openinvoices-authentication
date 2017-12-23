<?php
namespace OpenInvoices\Authentication\Adapter\DbTable;

use Zend\Authentication\Adapter\DbTable\AbstractAdapter;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Predicate\Operator as SqlOp;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Sql;

class CredentialAdapter extends AbstractAdapter
{
    private $passwordHashOptions = ['cost' => 10];
    
    /**
     * A password algorithm constant denoting the algorithm to use when hashing the password.
     * 
     * @var integer
     */
    private $algo = PASSWORD_DEFAULT;
    
    /**
     * _authenticateCreateSelect() - This method creates a Zend\Db\Sql\Select object that
     * is completely configured to be queried against the database.
     *
     * @return Select
     */
    protected function authenticateCreateSelect()
    {
        // get select
        $dbSelect = clone $this->getDbSelect();
        $dbSelect->from($this->tableName)
                 ->columns(['*'])
                 ->where(new SqlOp($this->identityColumn, '=', $this->identity));
        
        return $dbSelect;
    }
    
    /**
     * _authenticateValidateResult() - This method attempts to validate that
     * the record in the resultset is indeed a record that matched the
     * identity provided to this adapter.
     *
     * @param  array $resultIdentity
     * @return AuthenticationResult
     */
    protected function authenticateValidateResult($resultIdentity)
    {
        // Check the user status
        if (isset($resultIdentity['status'])) {
            if ($resultIdentity['status'] == 'Blocked') {
                $this->authenticateResultInfo['code']       = AuthenticationResult::FAILURE;
                $this->authenticateResultInfo['messages'][] = 'The user is blocked.';
                return $this->authenticateCreateAuthResult();
            }
            
            if ($resultIdentity['status'] !== 'Active') {
                $this->authenticateResultInfo['code']       = AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND;
                $this->authenticateResultInfo['messages'][] = 'The user not exist.';
                return $this->authenticateCreateAuthResult();
            }
        }
        
        // Check the password
        if (!password_verify($this->credential, $resultIdentity[$this->credentialColumn])) {
            $this->authenticateResultInfo['code']       = AuthenticationResult::FAILURE_CREDENTIAL_INVALID;
            $this->authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            return $this->authenticateCreateAuthResult();
        }
         
        // Chec if there is a new hash algorithm or if the cost has changed
        if (password_needs_rehash($resultIdentity[$this->credentialColumn], $this->algo, $this->passwordHashOptions)) {
            $newHash = password_hash($this->credential, $this->algo, $this->passwordHashOptions);
            
            $sql    = new Sql($this->zendDb);
            $update = new Update();
            $update->table($this->tableName)
                   ->set([$this->credentialColumn => $newHash])
                   ->where(new SqlOp($this->identityColumn, '=', $this->identity));
            
            $statement = $sql->prepareStatementForSqlObject($update);
            
            $result = $statement->execute();
        }
        
        unset($resultIdentity[$this->credentialColumn]);
        $this->resultRow = $resultIdentity;
        
        $this->authenticateResultInfo['code']       = AuthenticationResult::SUCCESS;
        $this->authenticateResultInfo['messages'][] = 'Authentication successful.';
        return $this->authenticateCreateAuthResult();
    }
}