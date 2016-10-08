<?php
return array(
    Zend_Validate_Alnum::NOT_ALNUM => "'%value%' debe contener unicamente digitos alfanumericos",
    Zend_Validate_Alnum::STRING_EMPTY => "'%value%' es una cadena vacia",
    Zend_Validate_Alpha::NOT_ALPHA => "'%value%' debe contener unicamente caracteres alfabeticos",
    Zend_Validate_Alpha::STRING_EMPTY => "'%value%' es una cadena vacia",
    Zend_Validate_EmailAddress::INVALID => "'%value%' no es una direccion de email valida",
    Zend_Validate_EmailAddress::INVALID_FORMAT => "'%value%' no es una direccion de email valida",
    Zend_Validate_EmailAddress::INVALID_HOSTNAME => "'%hostname%' no es un nombre de host valido para la direccion '%value%'",
    Zend_Validate_Float::INVALID => "Tipo no valido, debe ser numero entero, de punto flotante o una cadena",
    Zend_Validate_Float::NOT_FLOAT => "	'%value%' no parece ser un numero de punto flotante",
    Zend_Validate_Hostname::INVALID_HOSTNAME => "'%value%' no coincide con la estructura esperada para un nombre de host DNS",
    Zend_Validate_Hostname::UNDECIPHERABLE_TLD => "'%value%' parece ser un nombre de host de DNS pero no es puede extraer la parte TLD",
    Zend_Validate_Hostname::LOCAL_NAME_NOT_ALLOWED => "'%value%' parece ser un nombre de red local, pero los nombres de redes locales no se permiten",
    Zend_Validate_Int::NOT_INT => "'%value%' no parece ser un numero entero",
    Zend_Validate_NotEmpty::IS_EMPTY => "El valor es requerido y no puede estar vacio",
    Zend_Validate_Regex::INVALID => "Tipo no v�lido, el valor debe ser como en el ejemplo",
    Zend_Validate_StringLength::TOO_SHORT => "'%value%' tiene menos de %min% caracteres de longitud",
    Zend_Validate_StringLength::TOO_LONG => "'%value%' tiene mas de %max% caracteres de longitud",
    Zend_Validate_Date::INVALID_DATE => "'%value%' No parece ser una fecha válida"
    //Zend_Validate_ => ""
);