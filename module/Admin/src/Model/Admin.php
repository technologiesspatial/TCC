<?php
namespace Admin\Model;

// Add the following import statements:
use DomainException;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\StringLength;

class Admin implements InputFilterAwareInterface
{
    //public $id;
    public $client_first_name;
    public $client_last_name;
	public $client_email;
	public $client_address;
	public $client_city;
	public $client_country;
	public $client_postal_code;

    // Add this property:
    private $inputFilter;

    public function exchangeArray2(array $data)
    {
        $this->client_email     = !empty($data['client_email']) ? $data['client_email'] : null;
		$this->client_password =  !empty($data['client_password']) ? $data['client_password'] : null;
    }

    public function exchangeArray(array $data)
    {
        $this->client_id     = !empty($data['client_id']) ? $data['client_id'] : null;
		$this->client_image =  !empty($data['client_image']) ? $data['client_image'] : null;
		$this->client_password =  !empty($data['client_password']) ? $data['client_password'] : null;
        $this->client_first_name = !empty($data['client_first_name']) ? $data['client_first_name'] : null;
        $this->client_last_name  = !empty($data['client_last_name']) ? $data['client_last_name'] : null;
		$this->client_email  = !empty($data['client_email']) ? $data['client_email'] : null;
		$this->client_address  = !empty($data['client_address']) ? $data['client_address'] : null;
		$this->client_city  = !empty($data['client_city']) ? $data['client_city'] : null;
		$this->client_country  = !empty($data['client_country']) ? $data['client_country'] : null;
		$this->client_postal_code  = !empty($data['client_postal_code']) ? $data['client_postal_code'] : null;
    }

    /* Add the following methods: */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(sprintf(
            '%s does not allow injection of an alternate input filter',
            __CLASS__
        ));
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }	

}