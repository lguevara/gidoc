<?php
/**
 * Description of LoginModelo
 *
 * @author Sergio
 */

class Default_Model_LoginModelo
{
    const NOT_IDENTITY = 'notIdentity';

    const INVALID_CREDENTIAL = 'invalidCredential';

    const INVALID_USER = 'invalidUser';

    const INVALID_LOGIN = 'invalidLogin';

    /**
     * Mensaje de validaciones por defecto
     *
     * @var array
     */
    protected $_messages = array(
            self::NOT_IDENTITY    => "Usuario no existe.",
            self::INVALID_CREDENTIAL => "Contraseña no válida.",
            self::INVALID_USER => "Usuario no válido",
            self::INVALID_LOGIN => "Login no válido. Los campos están vacíos"
    );


    public function login($usuario, $password)
    {
        if(!empty($usuario) && !empty($password))
        {
            $autAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());

            /* Obtengo el objeto Appconfig para recoger datos de la tabla de autenticación */
            $appConfig = Zend_Registry::get('appConfig');
            
            $autAdapter->setTableName($appConfig->auth->TableName);
            $autAdapter->setIdentityColumn($appConfig->auth->IdentityColumn);
            $autAdapter->setCredentialColumn($appConfig->auth->CredentialColumn);
            $autAdapter->setIdentity($usuario);
            $autAdapter->setCredential(md5($password));

            $aut = Zend_Auth::getInstance();

            $result = $aut->authenticate($autAdapter);

            switch ($result->getCode())
            {
                case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                        throw new Exception($this->_messages[self::NOT_IDENTITY]);
                        break;

                case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                        throw new Exception($this->_messages[self::INVALID_CREDENTIAL]);
                        break;

                case Zend_Auth_Result::SUCCESS:
                    if ($result->isValid()) {
                            /* Guardo en Zend_Auth el registro del usuario que ha ingresado */
                            //$data = $autAdapter->getResultRowObject();
                            //$aut->getStorage()->write($data);
                            
                            /* Guardo en Zend_Auth el registro del usuario que ha ingresado pero con los datos de sus tablas foráneas */
                            $data = $autAdapter->getResultRowObject();
                            $datos = new Usuarios_Model_UsuarioMapper();
                            $identity = $datos->getUsuario($data->id);
                            $data = (object)$identity; /* Convierto el Array en un Objeto de la clase nativa de php stdClass, porque Zend_Auth trabaja con esta clase */
                            $aut->getStorage()->write($data);
 
                            
                    } else {
                            throw new Exception($this->_messages[self::INVALID_USER]);
                    }
                    break;

                default:
                        throw new Exception($this->_messages[self::INVALID_LOGIN]);
                        break;
            }

        } else {
            throw new Exception($this->_messages[self::INVALID_LOGIN]);
        }
        return $this;
    }

    public function logout()
    {
        Zend_Auth::getInstance()->clearIdentity();
        return $this;
    }

    public static function getIdentity()
    {
        $auth = Zend_Auth::getInstance();

        if ($auth->hasIdentity()) {
                return $auth->getIdentity();
        }
        return null;
    }

    public static function isLoggedIn()
    {
        return Zend_Auth::getInstance()->hasIdentity();
    }
}