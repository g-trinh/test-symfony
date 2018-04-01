<?php

namespace AppBundle\Factory;

use AppBundle\Entity\Doctor;

interface DoctorFactoryInterface
{
    /**
     * Creates a Doctor from the given parameters
     *
     * @param $data
     *
     * @return Doctor
     */
    public function create($data): Doctor;

    /**
     * Checks if the factory can handle the given parameters
     *
     * @param $data
     *
     * @return bool
     */
    public function supports($data): bool;
}