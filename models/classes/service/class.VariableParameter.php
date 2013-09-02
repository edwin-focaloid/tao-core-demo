<?php
/**  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */

/**
 * Represents tao service parameter
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 * @subpackage models_classes_service
 */
class tao_models_classes_service_VariableParameter
extends tao_models_classes_service_Parameter
{
	private $variable;
	
	public function __construct(core_kernel_classes_Resource $definition, core_kernel_classes_Resource $variable) {
	    parent::__construct($definition);
	    $this->variable = $variable;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see tao_models_classes_service_Parameter::serialize()
	 */
	public function serialize() {
	    $serviceCallClass = new core_kernel_classes_Class(CLASS_ACTUALPARAMETER);
	    $resource = $serviceCallClass->createInstanceWithProperties(array(
	        PROPERTY_ACTUALPARAMETER_FORMALPARAMETER    => $this->getDefinition(),
	        PROPERTY_ACTUALPARAMETER_PROCESSVARIABLE    => $this->variable
	    ));
	    return $resource;
	}
}
