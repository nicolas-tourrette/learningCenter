<?php
// src/Service/JsonParser.php

namespace App\Service;


class JsonParser
{

    private $jsonFile;

    public function __construct(String $jsonFile)
    {
        $this->jsonFile = $jsonFile;
    }

    public function parseJson(): ?array{
        if(file_exists($this->jsonFile)){
            $json = file_get_contents($this->jsonFile, false);
            $jsonDatas = json_decode($json, true);

            return $jsonDatas;
        }
        return null;
    }
}