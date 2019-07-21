<?php

namespace AppBundle\Services;

use Firebase\JWT\JWT;
use BackendBundle\Entity\Users;

class JwtAuth
{
    public $manager;
    public $key;

    public function __construct($manager)
    {
        $this->manager = $manager;
        $this->key = '123459';
    }
    
    public function signup($email, $password, $getHash = null)
    { 
        //$this->getDoctrine()->getManager();
        //$repository = $this->getDoctrine()->getRepository(Product::class);
        //$user = $this->getDoctrine()->getManager();
        $user = $this->manager->getRepository(Users::class);
        //$user = $user->findAll();
        $user = $user->findOneBy(array(
            "email" => $email,
            "password" => $password
        ));

        //var_dump($user[0]->getName());
        $signup = false;
        if (is_object($user)){
            $signup = true;
        }

        if ($signup == true) {
            //GENERAR TOKEN JWT

            $token = array(
                "id"      => $user->getId(), 
                "email"   => $user->getEmail(),
                "name"    => $user->getName(),
                "surname" => $user->getSurname(),
                "iat"     => time(),
                "exp"     => time() + (7 * 24 * 60 * 60)
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, array('HS256'));

            if ($getHash == null) {
                $data = $jwt;   
            }else{
                $data = $decoded;
            }

        } else {
            $data = array(
                'status' => 'error',
                'data' => 'login fail'
            );
        }
        return $data;
    }

    public function checkToken($jwt, $getIdentity = false)
    {
        $auth = false;
        try {
            $decoded = JWT::decode($jwt, $this->key, array('HS256'));
        } catch (\UnexpectedValueException $e) {
            $auth = false;
            var_dump($e->getMessage());
        } catch (\DomainException $e) {
            $auth = false;
            var_dump($e->getMessage());
        }
        if (isset($decoded) && is_object($decoded)) { //VALIDA SI EL TOKEN ES CORRECTO
            $auth = true;
        } else {
            $auth = false;
        }

        if ($getIdentity == false) {
            return $auth;
        } else {
            return $decoded;
        }
        
            
    }
}