<?php
namespace Account\Document;

use Zend\InputFilter\Factory as FilterFactory, Zend\InputFilter\InputFilter;
use Core\AbstractDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** 
 * @ODM\Document(
 * 		collection="user"
 * )
 * 
 * */
class User extends AbstractDocument
{
	/** @ODM\Id */
	protected $id;
	
	/** @ODM\Field(type="string")  */
	protected $loginName;
	
	/** @ODM\Field(type="string")  */
	protected $password;
	
	/** @ODM\Field(type="string")  */
	protected $roleId;
	
	/** @ODM\Field(type="string")  */
	protected $email;
	
	/** @ODM\Field(type="string")  */
	protected $avatar;
	
	/** @ODM\Field(type="date")  */
	protected $created;
	
	/** @ODM\Field(type="date")  */
	protected $lastLogin;
	
	protected $inputFilter;
	
	public function getInputFilter()
	{
		if(!$this->inputFilter) {
			$inputFilter = new InputFilter();
			$inputFactory = new FilterFactory();
			
			$dm = $this->getObjectManager();
			$inputFilter->add($inputFactory->createInput(array(
				'name'		=> 'loginName',
				'requried'	=> true,
				'filters'	=> array(
					array('name' => 'StringTrim')
				),
				'validators' => array(
					new \Core\Validator\InDb(
						array(
							'dm' => $dm,
							'repository' => 'Account\Document\User',
							'field' => 'LoginName',
							'excludeId' => $this->id
						)
					)
				)
			)));
			$inputFilter->add($inputFactory->createInput(array(
				'name'		=> 'password',
				'requried'	=> true,
				'filters'	=> array(
					array('name' => 'StringTrim')
				),
			)));
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}
	
	public function exchangeArray($data)
	{
		$this->loginName = $data['loginName'];
		$this->password  = $data['password'];
		if($this->created == null) {
			$this->created = new \DateTime();
		}
		$this->lastLogin = new \DateTime();
	}
	
	public function getArrayCopy()
	{
		return array(
			'id' => $this->id,
			'loginName'	=> $this->loginName,
			'password' => $this->password
		);
	}
}