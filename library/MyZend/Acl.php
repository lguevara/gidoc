<?php

/**
 * Description of Acl
 *
 * @author luis
 */
class MyZend_Acl extends Zend_Acl {

    public function __construct(Zend_Config $config) {

        $rolesEnIni = false;
        
        if($rolesEnIni){
            /* Obtengo la configuración acl desde application.ini */
            $roles = $config->acl->roles;
            $resources = $config->acl->resources;
            $permisos = $config->acl->permisos;

            /* Creo el entorno ACL */
            $this->_addRoles($roles);
            $this->_addResources($resources);
            $this->_addPermisos($permisos);

        } else { /* Roles en Db */
            
            /* Obtengo la configuración acl desde La Base de datos */
            $db = Zend_Db::Factory($config->resources->db);

            /* Obtengo el rol del usuario que ha ingresado */
            $usuario = Zend_Auth::getInstance()->getIdentity(); /* Obtengo el dato desde mi modelo que a su vez llama al Zend_Auth */        
            if($usuario){
                $rol_id = $usuario->rol_id;
                $rol = $usuario->rol;
            }else{
                $rol_id = 0;
                $rol = 'Visitante';
            }

            /* Obtengo los recursos */

            $sql = "SELECT DISTINCT recurso FROM permisos";
            $resources = $db->fetchAssoc($sql);
            
            /* Obtengo los permisos */
            $sql = "SELECT recurso FROM permisos WHERE rol_id = $rol_id";
            $permisos = $db->fetchAll($sql);

            /* Obtengo los roles */    
            $sql = "SELECT nombre FROM roles";
            $roles = $db->fetchAll($sql);

            /* Creo el entorno ACL */
            $this->_addRolesDb($roles);
            $this->_addResources($resources);
            $this->_addPermisosDb($permisos, $rol, $db);

            /* Registro el ACL */
            Zend_Registry::set('acl', $this);
            
        }
        
        
    }

    private function _addRoles($roles) {

        foreach ($roles as $name => $parents) {
            if (!$this->hasRole($name)) {
                if (empty($parents)) {
                    $parents = array();
                } else {
                    $parents = explode(',', $parents);
                }

                $this->addRole(new Zend_Acl_Role($name), $parents);
            }
        }
    }

    private function _addResources($resources) {

        foreach ($resources as $name => $parents) {
            $this->add(new Zend_Acl_Resource($name));
        }
    }

    private function _addPermisos($permisos) {

        foreach ($permisos as $regla => $resources) {

            foreach ($resources as $resource => $role) {

                if ($regla == 'allow') {
                    $this->allow($role, $resource);
                }
                if ($regla == 'deny') {
                    $this->deny($role, $resource);
                }
            }
        }
    }

    private function _addRolesDb($roles) {
        
        $parents = array();        
        /* Agrego los roles */
        foreach ($roles as $rol) {
            $this->addRole(new Zend_Acl_Role($rol['nombre']), $parents);
        }
    }
    
    private function _addPermisosDb($permisos, $rol) {
        /* Agrego los permisos */
        foreach ($permisos as $resource) {
            $this->allow($rol, $resource);
        }
    }
    
    
    
}

?>
