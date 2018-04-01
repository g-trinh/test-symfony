<?php

namespace AppBundle\Factory;

trait SegmentTrait
{
    protected function getSegment($data, $segmentNameIndex, $segmentName)
    {
        $index = -1;
        foreach ($data as $key => $segment) {
            if ($segment[$segmentNameIndex] === $segmentName) {
                $index = $key;
                break;
            }
        }

        if (-1 === $index) {
            return [];
        }

        return $data[$index];
    }
}