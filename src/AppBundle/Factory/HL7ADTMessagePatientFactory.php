<?php

namespace AppBundle\Factory;

use AppBundle\Entity\Patient;
use AppBundle\Exception\Factory\FactoryMessageNotSupportedException;
use Doctrine\ORM\EntityRepository;

class HL7ADTMessagePatientFactory implements PatientFactoryInterface
{
    use SegmentTrait;

    const SEGMENT_INDEX = 2;
    const SEGMENT_NAME_INDEX = 0;
    const SEGMENT_NAME = 'PID';

    const RPPS_SEGMENT_INDEX = 12;
    const RPPS_CHECK_VALUE = 'RPPS';
    const RPPS_VALUE_INDEX = 0;

    const NAMES_SEGMENT_INDEX = 5;
    const FIRSTNAME_INDEX = 0;
    const LASTNAME_INDEX = 1;

    const BIRTHDATE_INDEX = 7;
    const BIRTHDATE_FORMAT = 'Ymd';

    const COORDINATES_SEGMENT_INDEX = 11;
    const STREET_INDEX = 0;
    const ZIPCODE_INDEX = 4;
    const CITY_INDEX = 2;

    const GENDER_INDEX = 8;

    private $entityRepository;

    /**
     * HL7ADTMessagePatientFactory constructor.
     * @param EntityRepository $entityRepository
     */
    public function __construct(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function create($data): Patient
    {
        if (!$this->supports($data)) {
            throw new FactoryMessageNotSupportedException($data);
        }

        $patientSegment = $this->getSegment($data, self::SEGMENT_NAME_INDEX, self::SEGMENT_NAME);
        $namesSegment = $patientSegment[self::NAMES_SEGMENT_INDEX];
        $coordinatesSegment = $patientSegment[self::COORDINATES_SEGMENT_INDEX];

        $lastName = $namesSegment[self::LASTNAME_INDEX];
        $firstName = $namesSegment[self::FIRSTNAME_INDEX];
        $city = $coordinatesSegment[self::CITY_INDEX];
        $zipCode = $coordinatesSegment[self::ZIPCODE_INDEX];
        $street = $coordinatesSegment[self::STREET_INDEX];
        $gender = $patientSegment[self::GENDER_INDEX];
        $birthDate = date_create_from_format(self::BIRTHDATE_FORMAT, $patientSegment[self::BIRTHDATE_INDEX]);

        $patient = $this->entityRepository->findOneBy([
            'lastName' => $lastName,
            'firstName' => $firstName,
            'gender' => $gender,
            'birthDate' => $birthDate,
        ]);

        if (null === $patient) {
            $patient = new Patient();
            $patient
                ->setLastName($lastName)
                ->setFirstName($firstName)
                ->setBirthDate($birthDate)
                ->setGender($gender)
            ;
        }

        $patient
            ->setCity($city)
            ->setZipCode($zipCode)
            ->setStreet($street)
        ;

        return $patient;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data): bool
    {
        if (!is_array($data) || !isset($data[self::SEGMENT_INDEX])) {
            return false;
        }

        $patientSegment = $this->getSegment($data, self::SEGMENT_NAME_INDEX, self::SEGMENT_NAME);

        if (!$this->checkPatientSegment($patientSegment)) {
            return false;
        }

        $namesSegment = $patientSegment[self::NAMES_SEGMENT_INDEX];

        if (!$this->checkNamesSegment($namesSegment)) {
            return false;
        }

        $coordinatesSegment = $patientSegment[self::COORDINATES_SEGMENT_INDEX];

        if (!$this->checkCoordinates($coordinatesSegment)) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the given patient segment is supported by the factory.
     *
     * @param array $patientSegment
     *
     * @return bool
     */
    private function checkPatientSegment(array $patientSegment)
    {
        return !empty($patientSegment) &&
            isset($patientSegment[self::SEGMENT_NAME_INDEX]) &&
            isset($patientSegment[self::NAMES_SEGMENT_INDEX]) &&
            isset($patientSegment[self::COORDINATES_SEGMENT_INDEX]) &&
            isset($patientSegment[self::GENDER_INDEX]) &&
            isset($patientSegment[self::BIRTHDATE_INDEX])
        ;
    }

    /**
     * Checks if the given names segment is supported by the factory.
     *
     * @param array $namesSegment
     *
     * @return bool
     */
    private function checkNamesSegment(array $namesSegment)
    {
        return
            isset($namesSegment[self::FIRSTNAME_INDEX]) &&
            isset($namesSegment[self::LASTNAME_INDEX])
        ;
    }

    /**
     * Checks if the given coordinates segment is supported by the factory.
     *
     * @param array $coordinatesSegment
     *
     * @return bool
     */
    private function checkCoordinates(array $coordinatesSegment)
    {
        return
            isset($coordinatesSegment[self::STREET_INDEX]) &&
            isset($coordinatesSegment[self::ZIPCODE_INDEX]) &&
            isset($coordinatesSegment[self::CITY_INDEX])
        ;
    }
}