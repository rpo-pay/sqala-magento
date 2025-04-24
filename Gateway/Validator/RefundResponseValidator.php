<?php

namespace Sqala\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class RefundResponseValidator extends AbstractValidator
{
    public function __construct(ResultInterfaceFactory $resultFactory)
    {
        parent::__construct($resultFactory);
    }

    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $isValid = $response['status'] === 200;
        $errorMessages = [];
        $errorCodes = [];

        if (!$isValid) {

            $errorCodes[] = $response['code'];
            $errorMessages[] = $response['message'];
            $errorsList = $response['errors'] ?? [];

            foreach ($errorsList as $error) {
                $errorCodes[] = '-1';
                $errorMessages[] = $error;
            }
        }
        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }
}
