<?php

namespace Sqala\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class GeneralResponseValidator extends AbstractValidator
{
    public function __construct(ResultInterfaceFactory $resultFactory)
    {
        parent::__construct($resultFactory);
    }

    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $isValid = !empty($response['id']) && $response['status'] !== 'FAILED';
        $errorMessages = [];
        $errorCodes = [];

        \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)->debug('====== GeneralResponseValidator - $response: ', $response);

        if (!$isValid) {

            $errorCodes[] = $response['code'] ?? 'null';
            $errorMessages[] = $response['message'] ?? 'null';

            foreach ($response['errors'] as $error) {
                $errorCodes[] = '-1';
                $errorMessages[] = $error;
            }
        }
        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }
}
