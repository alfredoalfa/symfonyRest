<?php
namespace AppBundle\Services;

use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;

class Helpers
{
    public $manager;

    public function __construct($manager)
    {
        $this->manager = $manager;

    }
    
    public function json($data)
    {
        Echo "LLEGO";
        $normalizers = [new GetSetMethodNormalizer()]; //normaliza la informacion que llega
        $encoders = ["json" => new JsonEncode()]; //codificar json

        $serializer = new Serializer($normalizers, $encoders); // combierte los datos correctamente.
        $json = $serializer->serialize($data, 'json'); //se pasan los datos $data para que los convierta a json
        
        $response = new Response();
        $response->setContent($json);
        $response->headers->set('Content-Type','application/json');

        return $response;

        // var_dump($serializer);
        //  die(); 
    }
}