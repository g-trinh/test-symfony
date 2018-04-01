<?php

namespace AppBundle\Factory;

use AppBundle\Entity\Patient;

interface PatientFactoryInterface
{
    /**
     * Creates a Patient from the given parameters
     *
     * @param $data
     *
     * @return Patient
     */
    public function create($data): Patient;

    /**
     * Checks if the factory can handle the given parameters
     *
     * @param $data
     *
     * @return bool
     */
    public function supports($data): bool;
}