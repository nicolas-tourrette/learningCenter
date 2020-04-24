<?php
// src/Service/JsonParser.php

namespace App\Service;


class JsonParser
{

    private $jsonFile;
    private $jsonErrors = "assets/datas/errors.json";

    public function __construct(?String $jsonFile)
    {
        $this->jsonFile = $jsonFile;
    }

    public function parseJson(){
        if(file_exists($this->jsonFile)){
            $json = file_get_contents($this->jsonFile, false);
            $datas = json_decode($json, true);

            if($datas == null){
                throw new \ErrorException($this->throwErrorMessage("e1002"));
            }
            return $datas;
        }
        return $this->throwErrorMessage("e1001");
    }

    public function parseJsonFirst(){
        if(file_exists($this->jsonFile)){
            return $this->parseJson()[0];
        }
        
        return $this->throwErrorMessage("e2001");
    }

    public function throwErrorMessage(String $error){
        if(file_exists($this->jsonErrors)){
            $json = file_get_contents($this->jsonErrors, false);
            $errors = json_decode($json, true);

            if($errors == null){
                throw new \ErrorException("Error code #1002 — Erreur lors de la lecture de la ressource.");
            }

            if(isset($errors[$error])){
                return "Error code #".$errors[$error]["code"]." — ".$errors[$error]["message"];
            }
        }
        throw new \ErrorException("Error code #1000 — Erreur inconnue.");
    }
}