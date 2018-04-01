<?php

namespace AppBundle\Factory;

use AppBundle\Entity\Doctor;
use AppBundle\Exception\Factory\FactoryMessageNotSupportedException;
use Doctrine\ORM\EntityRepository;

class HL7ADTMessageDoctorFactory implements DoctorFactoryInterface
{
    use SegmentTrait;

    const SEGMENT_INDEX = 5;
    const SEGMENT_NAME_INDEX = 0;
    const SEGMENT_NAME = 'ROL';
    const DOCTOR_COORDINATES_SEGMENT_INDEX = 4;
    const DEFAULT_RPPS_INDEX = 0;
    const RPPS_SEGMENT_INDEX = 12;
    const RPPS_CHECK_VALUE = 'RPPS';
    const RPPS_VALUE_INDEX = 0;
    const DOCTOR_ID_INDEX = 0;
    const DOCTOR_FIRSTNAME_INDEX = 1;
    const DOCTOR_LASTNAME_INDEX = 2;

    private $entityRepository;

    /**
     * HL7ADTMessageDoctorFactory constructor.
     * @param EntityRepository $entityRepository
     */
    public function __construct(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function create($data): Doctor
    {
        if (!$this->supports($data)) {
            throw new FactoryMessageNotSupportedException($data);
        }

        $doctorSegment = $this->getSegment($data, self::SEGMENT_NAME_INDEX, self::SEGMENT_NAME);
        $doctorCoordinates = $doctorSegment[self::DOCTOR_COORDINATES_SEGMENT_INDEX];
        $RPPS = $this->getRPPSValue($doctorCoordinates);

        $firstName = $doctorCoordinates[self::DOCTOR_FIRSTNAME_INDEX];
        $lastName = $doctorCoordinates[self::DOCTOR_LASTNAME_INDEX];

        /** @var Doctor|null $doctor */
        $doctor = $this->entityRepository->findOneBy([
            'RPPS' => $RPPS,
            'firstName' => $firstName,
            'lastName' => $lastName,
        ]);

        if (null !== $doctor) {
            return $doctor;
        }

        $doctor = new Doctor();
        $doctor
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRPPS($RPPS)
        ;
        return $doctor;
    }

    private function getRPPSValue(array $doctorCoordinates)
    {
        if (
            isset($doctorCoordinates[self::RPPS_SEGMENT_INDEX]) &&
            $doctorCoordinates[self::RPPS_SEGMENT_INDEX] === self::RPPS_CHECK_VALUE
        ) {
            return $doctorCoordinates[self::RPPS_VALUE_INDEX];
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data): bool
    {
        // Check format
        if (!is_array($data) || !isset($data[self::SEGMENT_INDEX])) {
            return false;
        }

        $doctorSegment = $this->getSegment($data, self::SEGMENT_NAME_INDEX, self::SEGMENT_NAME);

        if (!$this->checkDoctorSegment($doctorSegment)) {
            return false;
        }

        $doctorCoordinates = $doctorSegment[self::DOCTOR_COORDINATES_SEGMENT_INDEX];

        if (!$this->checkCoordinates($doctorCoordinates)) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the given doctor segment is supported by the factory.
     *
     * @param array $doctorSegment
     *
     * @return bool
     */
    private function checkDoctorSegment(array $doctorSegment)
    {
        return
            !empty($doctorSegment) &&
            isset($doctorSegment[self::SEGMENT_NAME_INDEX]) &&
            isset($doctorSegment[self::DOCTOR_COORDINATES_SEGMENT_INDEX])
        ;
    }

    /**
     * Checks if the given doctor coordinates segment is  supported by the factory.
     *
     * @param array $doctorCoordinates
     *
     * @return bool
     */
    private function checkCoordinates(array $doctorCoordinates)
    {
        return
            isset($doctorCoordinates[self::DOCTOR_FIRSTNAME_INDEX]) &&
            isset($doctorCoordinates[self::DOCTOR_LASTNAME_INDEX])
        ;
    }
}