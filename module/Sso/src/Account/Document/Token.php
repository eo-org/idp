<?php
namespace Account\Document;

use Core\AbstractDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** 
 * @ODM\Document(
 * 		collection="token"
 * )
 * 
 * */
class Token extends AbstractDocument
{
	/** @ODM\Id */
	protected $id;

	/** @ODM\Field(type="string")  */
	protected $tokenId;
	
	/** @ODM\Field(type="hash")  */
	protected $userData;
	
	/** @ODM\Field(type="date")  */
	protected $created;
	
	public function exchangeArray($data)
	{
		$this->tokenId = $data['tokenId'];
		$this->userData = $data['userData'];
		if($this->created === null) {
			$this->created = new \DateTime();
		}
	}
}